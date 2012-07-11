<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14390 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
{
	exit;
}

abstract class PayPalAbstract extends PaymentModule
{
	protected $_html = '';
	protected $_errors = array();

	public $default_country;
	public $iso_code;

	public $context;

	const DEFAULT_COUNTRY_ISO = 'GB';

	public function __construct()
	{
		$this->name = 'paypal';
		$this->tab = 'payments_gateways';
		$this->version = '3.0.4';

		$this->currencies = true;
		$this->currencies_mode = 'radio';

		parent::__construct();

		/** Backward compatibility */
		require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('PayPal');
		$this->description = $this->l('Accepts payments by credit cards (CB, Visa, MasterCard, Amex, Aurore, Cofinoga, 4 stars) with PayPal.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

        // Default methods (initialization & checks)
        $this->loadLangDefault();
		$this->paypal_logos = new PayPalLogos($this->iso_code);

		$this->runUpgrades();
		$this->compatibilityCheck();
        $this->warningsCheck();
	}

	public function install()
	{
		/* Install and register on hook */
		if (!parent::install() ||
            !$this->registerHook('payment') ||
            !$this->registerHook('paymentReturn') ||
            !$this->registerHook('shoppingCartExtra') ||
            !$this->registerHook('backBeforePayment') ||
            !$this->registerHook('rightColumn') ||
            !$this->registerHook('cancelProduct') ||
            !$this->registerHook('productFooter') ||
            !$this->registerHook('header') ||
            !$this->registerHook('adminOrder') ||
            !$this->registerHook('backOfficeHeader'))
		{
			return false;
		}

		if (file_exists(_PS_MODULE_DIR_ . $this->name . '/paypal_tools.php'))
		{
			include_once(_PS_MODULE_DIR_ . $this->name . '/paypal_tools.php');
			$paypal_tools = new PayPalTools($this->name);
			$paypal_tools->moveTopPayments(1);
			$paypal_tools->moveRightColumn(3);
		}

		if (file_exists(_PS_MODULE_DIR_ . '/paypalapi/paypalapi.php') && !Configuration::get('PAYPAL_NEW'))
		{
			include_once(_PS_MODULE_DIR_ . '/paypalapi/paypalapi.php');
			new PaypalAPI();
			$this->runUpgrades(true);
		}

		/* Set database */
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paypal_order` (
			`id_order` int(10) unsigned NOT NULL,
			`id_transaction` varchar(255) NOT NULL,
			`id_invoice` varchar(255) DEFAULT NULL,
			`currency` varchar(10) NOT NULL,
			`total_paid` varchar(50) NOT NULL,
			`shipping` varchar(50) NOT NULL,
			`capture` int(2) NOT NULL,
			`payment_date` varchar(50) NOT NULL,
			`payment_method` int(2) unsigned NOT NULL,
			`payment_status` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id_order`)
			) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->Execute($sql))
		{
			return false;
		}

		/* Set database */
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paypal_customer` (
				`id_paypal_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_customer` int(10) unsigned NOT NULL,
				`paypal_email` varchar(255) NOT NULL,
				PRIMARY KEY (`id_paypal_customer`)
				) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

		if (!Db::getInstance()->Execute($sql))
		{
			return false;
		}

		/* Set configuration */
		Configuration::updateValue('PAYPAL_SANDBOX', 0);
		Configuration::updateValue('PAYPAL_HEADER', '');
		Configuration::updateValue('PAYPAL_BUSINESS', 0);
		Configuration::updateValue('PAYPAL_BUSINESS_ACCOUNT', 'paypal@prestashop.com');
		Configuration::updateValue('PAYPAL_API_USER', '');
		Configuration::updateValue('PAYPAL_API_PASSWORD', '');
		Configuration::updateValue('PAYPAL_API_SIGNATURE', '');
		Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT', 0);
		Configuration::updateValue('PAYPAL_CAPTURE', 0);
		Configuration::updateValue('PAYPAL_PAYMENT_METHOD', WPS);
		Configuration::updateValue('PAYPAL_NEW', 1);
		Configuration::updateValue('PAYPAL_DEBUG_MODE', 0);
		Configuration::updateValue('PAYPAL_SHIPPING_COST', 20.00);
		Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT', 1);
		Configuration::updateValue('PAYPAL_VERSION', $this->version);
		Configuration::updateValue('PAYPAL_COUNTRY_DEFAULT', (int)Configuration::get('PS_COUNTRY_DEFAULT'));

		if (!Configuration::get('PAYPAL_OS_AUTHORIZATION'))
		{
			$orderState = new OrderState();
			$orderState->name = array();

			foreach (Language::getLanguages() as $language)
			{
				if (strtolower($language['iso_code']) == 'fr')
				{
					$orderState->name[$language['id_lang']] = 'Autorisation acceptée par PayPal';
				}
				else
				{
					$orderState->name[$language['id_lang']] = 'Authorization accepted from PayPal';
				}
			}

			$orderState->send_email = false;
			$orderState->color = '#DDEEFF';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = true;
			$orderState->invoice = true;

			if ($orderState->add())
			{
				copy(dirname(__FILE__) . '/../../img/os/' . Configuration::get('PS_OS_PAYPAL') . '.gif', dirname(__FILE__) . '/../../img/os/' . (int)$orderState->id . '.gif');
			}
			Configuration::updateValue('PAYPAL_OS_AUTHORIZATION', (int)$orderState->id);
		}

		return true;
	}

	public function uninstall()
	{
		/* Delete all configurations */
		Configuration::deleteByName('PAYPAL_SANDBOX');
		Configuration::deleteByName('PAYPAL_HEADER');
		Configuration::deleteByName('PAYPAL_BUSINESS');
		Configuration::deleteByName('PAYPAL_API_USER');
		Configuration::deleteByName('PAYPAL_API_PASSWORD');
		Configuration::deleteByName('PAYPAL_API_SIGNATURE');
		Configuration::deleteByName('PAYPAL_BUSINESS_ACCOUNT');
		Configuration::deleteByName('PAYPAL_EXPRESS_CHECKOUT');
		Configuration::deleteByName('PAYPAL_PAYMENT_METHOD');
		Configuration::deleteByName('PAYPAL_TEMPLATE');
		Configuration::deleteByName('PAYPAL_CAPTURE');
		Configuration::deleteByName('PAYPAL_DEBUG_MODE');
		Configuration::deleteByName('PAYPAL_COUNTRY_DEFAULT');

		// PayPal v3 configuration
		Configuration::deleteByName('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT');

		return parent::uninstall();
	}

	/**
	 * Launch upgrade process
	 */
	public function runUpgrades($install = false)
	{
		// Upgrade versions
		$files_version = array('2.8', '3.0');
		$upgrade_path = dirname(__FILE__).'/upgrade/';
		$this->installed_version = Configuration::get('PAYPAL_VERSION');

		if (_PS_VERSION_ < '1.5')
		{
			foreach ($files_version as $version)
			{
				$file = $upgrade_path.'install-'.$version.'.php';

				if ($this->installed_version < $version && file_exists($file))
				{
					include_once($file);
					call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this, $install);
				}
			}
		}
	}

	private function compatibilityCheck()
	{
		if (file_exists(_PS_ROOT_DIR_ . '/modules/paypalapi/paypalapi.php') && $this->active)
		{
			$this->warning = $this->l('All features of Paypal API module are be include in the new Paypal module. In order to do not have any conflict, please do not use and remove PayPalAPI module.');
		}

		/* For 1.4.3 and less compatibility */
		$updateConfig = array('PS_OS_CHEQUE'  => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4, 'PS_OS_DELIVERED' => 5, 'PS_OS_CANCELED' => 6, 'PS_OS_REFUND' => 7, 'PS_OS_ERROR'       => 8, 'PS_OS_OUTOFSTOCK'  => 9, 'PS_OS_BANKWIRE'    => 10, 'PS_OS_PAYPAL' => 11, 'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $key => $value)
		{
			if (!Configuration::get($key) || (int)Configuration::get($key) < 1)
			{
				if (defined('_' . $key . '_') && (int)constant('_' . $key . '_') > 0)
				{
					Configuration::updateValue($key, constant('_' . $key . '_'));
				}
				else
				{
					Configuration::updateValue($key, $value);
				}
			}
		}
	}

	public function isPayPalAPIAvailable()
	{
		$payment_method = Configuration::get('PAYPAL_PAYMENT_METHOD');

		$username = Configuration::get('PAYPAL_API_USER');
		$password = Configuration::get('PAYPAL_API_PASSWORD');
		$signature = Configuration::get('PAYPAL_API_SIGNATURE');
		$business_account = Configuration::get('PAYPAL_BUSINESS_ACCOUNT');

		if (($payment_method != HSS) && !is_null($username) && !is_null($password) && is_null($signature))
		{
			return true;
		}
		else if (($payment_method == HSS) && !is_null($business_account))
		{
			return true;
		}

		return false;
	}

	public function fetchTemplate($path, $name, $extension = false)
	{
		if (_PS_VERSION_ < '1.4')
		{
			$this->context->smarty->currentTemplate = $name;
		}

        $extension = ($extension != false) ? $extension : 'tpl';

		return $this->context->smarty->fetch(dirname(__FILE__) . $path . $name . '.' . $extension);
	}

	public function getContent()
	{
		$this->_postProcess();

		if (($id_lang = Language::getIdByIso('EN')) == 0)
			$english_language_id = (int)$this->context->employee->id_lang;
		else
			$english_language_id = (int)$id_lang;

		$values = array(
			'Countries' => Country::getCountries($english_language_id),
			'PP_errors' => $this->_errors,
			'PayPal_WPS' => (int)WPS,
			'PayPal_HSS' => (int)HSS,
			'PayPal_ECS' => (int)ECS,
			'PayPal_logo' => $this->paypal_logos->getLogos(),
			'PayPal_allowed_methods' => $this->getPaymentMethods(),
			'PayPal_country' => Country::getNameById((int)$english_language_id, (int)$this->default_country),
			'PayPal_business' => Configuration::get('PAYPAL_BUSINESS'),
			'PayPal_payment_method' => (int)Configuration::get('PAYPAL_PAYMENT_METHOD'),
			'PayPal_api_username' => Configuration::get('PAYPAL_API_USER'),
			'PayPal_api_password' => Configuration::get('PAYPAL_API_PASSWORD'),
			'PayPal_api_signature' => Configuration::get('PAYPAL_API_SIGNATURE'),
			'PayPal_api_business_account' => Configuration::get('PAYPAL_BUSINESS_ACCOUNT'),
			'PayPal_express_checkout_shortcut' => (int)Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT'),
			'PayPal_sandbox_mode' => (int)Configuration::get('PAYPAL_SANDBOX'),
			'PayPal_payment_capture' => (int)Configuration::get('PAYPAL_CAPTURE'),
			'PayPal_country_default' => (int)$this->default_country,
			'PayPal_change_country_url' => 'index.php?tab=AdminCountries&token=' . Tools::getAdminTokenLite('AdminCountries') . '#footer'
		);

		$this->context->smarty->assign($values);
		$this->getTranslations();

		return $this->fetchTemplate('/views/templates/back/', 'back_office');
	}

	/*
	** Added to be used properly with OPC
	*/
	public function hookHeader()
	{
		$this->context->smarty->assign(
			array(
				'base_uri' => __PS_BASE_URI__,
				'id_cart'  => $this->context->cart->id
			)
		);

		// Backward compatibility
		if (_PS_VERSION_ < '1.5')
			Tools::addCSS(_MODULE_DIR_ . $this->name . '/css/paypal.css');
		else
			$this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/css/paypal.css');

		$output = $this->fetchTemplate('/js/', 'front_office', 'js');
			
		return '<script type="text/javascript">' . $output . '</script>';
	}

	public function hookProductFooter()
	{
		$business = (int)Configuration::get('PAYPAL_BUSINESS');
		$payment_method = (int)Configuration::get('PAYPAL_PAYMENT_METHOD');

		if (!Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT') || !in_array(ECS, $this->getPaymentMethods()) || (($business == 1) && ($payment_method == HSS)))
		{
			return '';
		}

		$iso_lang = array('en' => 'en_US', 'fr' => 'fr_FR');

		$current_lang_code = (isset($iso_lang[$this->context->language->iso_code])) ? $iso_lang[$this->context->language->iso_code] : 'en_US';

		if (method_exists('Tools', 'getShopDomainSsl'))
			$shop_url = Tools::getShopDomainSsl(true, true);
		else
			$shop_url = PayPal::getShopDomainSsl(true, true);

		$this->context->smarty->assign(
			array(
				'PayPal_payment_type'     => 'product',
				'PayPal_lang_code'        => $current_lang_code,
				'PayPal_current_shop_url' => $shop_url . $_SERVER['REQUEST_URI']
			)
		);

		return $this->fetchTemplate('/views/templates/front/express_checkout/', 'express_checkout');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
		{
			return;
		}

		$method = Configuration::get('PAYPAL_PAYMENT_METHOD');

		if (method_exists('Tools', 'getShopDomainSsl'))
		{
			$shop_url = Tools::getShopDomainSsl(true, true);
		}
		else
		{
			$shop_url = PayPal::getShopDomainSsl(true, true);
		}

		if (isset($this->context->cookie->express_checkout))
		{
			// Check if user went through the payment preparation detail and completed it
			$detail = unserialize($this->context->cookie->express_checkout);

			if (!empty($detail['payer_id']) && !empty($detail['token']))
			{
				$values = array('get_confirmation' => true);
				$link = $shop_url . _MODULE_DIR_ . $this->name .'/express_checkout/submit.php';

				Tools::redirectLink($link . '?' . http_build_query($values, '', '&'));
			}
		}

		$this->context->smarty->assign(
			array(
				'logos'        => $this->paypal_logos->getLogos(),
				'sandbox_mode' => Configuration::get('PAYPAL_SANDBOX')
			)
		);

		if ($method == HSS)
		{
			$billing_address = new Address($this->context->cart->id_address_invoice);
			$delivery_address = new Address($this->context->cart->id_address_delivery);
			$billing_address->country = new Country($billing_address->id_country);
			$billing_address->state = new State($billing_address->id_state);
			$delivery_address->country = new Country($delivery_address->id_country);
			$delivery_address->state = new State($delivery_address->id_state);
			$cart_details = $this->context->cart->getSummaryDetails();
			$hash = sha1(serialize($this->context->cart->getProducts()));

			// Backward compatibility
			if (_PS_VERSION_ < '1.5')
			{
				$shipping = $this->context->cart->getOrderShippingCost();
			}
			else
			{
				$shipping = $this->context->cart->getTotalShippingCost();
			}

			if (((int)Configuration::get('PAYPAL_SANDBOX')) == 1)
				$action_url = "https://securepayments.sandbox.paypal.com/acquiringweb";
			else
				$action_url = "https://securepayments.paypal.com/acquiringweb";

			$this->context->smarty->assign(array(
				'action_url'		=> $action_url,
				'cart'              => $this->context->cart,
				'cart_details'      => $cart_details,
				'currency'          => new Currency($this->context->cart->id_currency),
				'customer'          => $this->context->customer,
				'business_account'  => Configuration::get('PAYPAL_BUSINESS_ACCOUNT'),
				'custom'            => json_encode(
					array('id_cart' => $this->context->cart->id, 'hash'    => $hash)
				),
				'gift_price'        => (float)Configuration::get('PS_GIFT_WRAPPING_PRICE'),
				'billing_address'   => $billing_address,
				'delivery_address'  => $delivery_address,
				'shipping'          => $shipping,
				'cancel_return'     => $shop_url . '/order.php',
				'notify_url'        => $shop_url . _MODULE_DIR_ . $this->name . '/integral_evolution/notifier.php',
				'return_url'        => $shop_url . _MODULE_DIR_ . $this->name . '/integral_evolution/submit.php?id_cart=' . $this->context->cart->id,
				'tracking_code'     => TRACKING_CODE)
			);

			return $this->fetchTemplate('/views/templates/front/integral_evolution/', 'iframe');
		}
		else if ($method == WPS || $method == ECS)
		{
			$this->getTranslations();

			$this->context->smarty->assign(array(
				'PayPal_integral' => WPS,
				'PayPal_express_checkout' => ECS,
				'PayPal_payment_method'   => $method,
				'PayPal_payment_type'     => 'payment_cart',
				'PayPal_current_shop_url' => $shop_url . $_SERVER['REQUEST_URI'],
				'PayPal_tracking_code'    => TRACKING_CODE)
			);

			return $this->fetchTemplate('/views/templates/front/express_checkout/', 'paypal');
		}
		return '';
	}

	public function hookShoppingCartExtra()
	{
		// No active or ajax request, drop it
		if (!$this->active || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			return;
		}

		if (!Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT'))
		{
			return '';
		}

		if (!in_array(ECS, $this->getPaymentMethods()))
		{
			return '';
		}

		$values = array('en' => 'en_US', 'fr' => 'fr_FR');
		$lang_iso_code = $this->context->language->iso_code;
		$current_lang_code = (isset($values[$lang_iso_code])) ? $values[$lang_iso_code] : 'en_US';

		if (method_exists('Tools', 'getShopDomainSsl'))
			$shop_url = Tools::getShopDomainSsl(true, true);
		else
			$shop_url = PayPal::getShopDomainSsl(true, true);

		$this->context->smarty->assign(
			array(
				'PayPal_payment_type'     => 'cart',
				'PayPal_lang_code'        => $current_lang_code,
				'PayPal_current_shop_url' => $shop_url . $_SERVER['REQUEST_URI'],
				'PayPal_tracking_code'    => TRACKING_CODE));

		return $this->fetchTemplate('/views/templates/front/express_checkout/', 'express_checkout');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
		{
			return;
		}

		return $this->fetchTemplate('/views/templates/front/', 'confirmation');
	}

	public function hookRightColumn($params)
	{
		$this->context->smarty->assign('logo', $this->paypal_logos->getCardsLogo(true));
		return $this->fetchTemplate('/views/templates/front/', 'column');
	}

	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	public function hookBackBeforePayment($params)
	{
		if (!$this->active)
		{
			return;
		}

		/* Only execute if you use PayPal API for payment */
		$payment_method = Configuration::get('PAYPAL_PAYMENT_METHOD');
		if (($payment_method != HSS) && $this->isPayPalAPIAvailable())
		{
			global $cookie;

			if ($params['module'] != $this->name)
			{
				return false;
			}
			if (!$token = $cookie->paypal_token)
			{
				return false;
			}
			if (!$payerID = $cookie->paypal_payer_id)
			{
				return false;
			}
			Tools::redirect('modules/paypal/express/submit.php?confirm=1&token=' . $token . '&payerID=' . $payerID);
		}
	}

	public function hookAdminOrder($params)
	{
		if (Tools::isSubmit('paypal'))
		{
			switch (Tools::getValue('paypal'))
			{
				case 'captureOk':
					$message = $this->l('Funds have been recovered.');
					break;
				case 'captureError':
					$message = $this->l('Recovery of funds request unsuccessful. Please see log message!');
					break;
				case 'validationOk':
					$message = $this->l('Validation successful. Please see log message!');
					break;
				case 'refundOk':
					$message = $this->l('Refund has been made.');
					break;
				case 'refundError':
					$message = $this->l('Refund request unsuccessful. Please see log message!');
					break;
			}
			if (isset($message) && $message)
			{
				$this->_html .= '
				<br />
				<div class="module_confirmation conf confirm" style="width: 400px;">
					<img src="' . _PS_IMG_ . 'admin/ok.gif" alt="" title="" /> ' . $message . '
				</div>';
			}
		}

		$adminTemplates = array();

		if ($this->_needValidation((int)$params['id_order']) && $this->isPayPalAPIAvailable())
		{
			$adminTemplates[] = 'validation';
		}
		if ($this->_needCapture((int)$params['id_order']) && $this->isPayPalAPIAvailable())
		{
			$adminTemplates[] = 'capture';
		}
		if ($this->_canRefund((int)$params['id_order']) && $this->isPayPalAPIAvailable())
		{
			$adminTemplates[] = 'refund';
		}

		if (count($adminTemplates) > 0)
		{
			$id_order = (int)$params['id_order'];
			if (_PS_VERSION_ >= '1.5')
			{
				$order_state = OrderHistory::getLastOrderState((int)$id_order)->id;
			} // Backward compatibility
			else
			{
				$order = new Order((int)$id_order);
				$order_state = $order->current_state->id;
			}

			$this->context->smarty->assign(array('authorization' => (int)Configuration::get('PAYPAL_OS_AUTHORIZATION'), 'base_url'      => _PS_BASE_URL_ . __PS_BASE_URI__, 'module_name'   => $this->name, 'order_state'   => $order_state, 'params'        => $params));

			foreach ($adminTemplates as $adminTemplate)
			{
				$this->_html .= $this->fetchTemplate('/views/templates/back/admin_order/', $adminTemplate);
				$this->_postProcess();
				$this->_html .= '</fieldset>';
			}
		}

		return $this->_html;
	}

	public function hookCancelProduct($params)
	{
		if (Tools::isSubmit('generateDiscount'))
		{
			return false;
		}
		if (!$this->isPayPalAPIAvailable())
		{
			return false;
		}
		if ($params['order']->module != $this->name)
		{
			return false;
		}
		if (!($order = $params['order']) || !Validate::isLoadedObject($order))
		{
			return false;
		}
		if (!$order->hasBeenPaid())
		{
			return false;
		}
		if (!($order_detail = new OrderDetail((int)$params['id_order_detail'])) || !Validate::isLoadedObject($order_detail))
		{
			return false;
		}

		$id_transaction = $this->_getTransactionId((int)$order->id);
		if (!$id_transaction)
		{
			return false;
		}

		$products = $order->getProducts();
		$amt = $products[(int)$order_detail->id]['product_price_wt'] * (int)($_POST['cancelQuantity'][(int)$order_detail->id]);

		$response = $this->_makeRefund($id_transaction, $order->id, (float)($amt));
		$message = $this->l('Cancel products result:') . '<br>';
		foreach ($response as $key => $value)
		{
			$message .= $key . ': ' . $value . '<br>';
		}
		$this->_addNewPrivateMessage((int)$order->id, $message);
	}

	public function hookBackOfficeHeader()
	{
		if (_PS_VERSION_ < '1.5')
			$getName = (int)strcmp(Tools::getValue('configure'), $this->name);
		else
			$getName = (int)strcmp(Tools::getValue('module_name'), $this->name);

		if ($getName != 0)
			return '';
		else
        {
            $this->getContext()->smarty->assign(
                array(
                    'PayPal_module_dir' => _MODULE_DIR_ . $this->name,
                    'PayPal_WPS' => (int)WPS,
                    'PayPal_HSS' => (int)HSS,
                    'PayPal_ECS' => (int)ECS
                )
            );

			return $this->fetchTemplate('/views/templates/back/', 'header');
		}
	}

	public function getTranslations()
	{
		$file = dirname(__FILE__) . '/' . _PAYPAL_TRANSLATIONS_XML_;

		if (file_exists($file))
		{
			$xml = simplexml_load_file($file);
		}
		else
		{
			return false;
		}

		if (isset($xml) && ($xml != false))
		{
			$index = -1;
			$content = array();
			$default = array();

			while (isset($xml->country[++$index]))
			{
				$country = $xml->country[$index];
				$country_iso = $country->attributes()->iso_code;

				if (($this->iso_code != 'default') && ($country_iso == $this->iso_code))
				{
					$content = (array)$country;
				}
				if ($country_iso == 'default')
				{
					$default = (array)$country;
				}
			}

			$content += $default;
			$this->context->smarty->assign('PayPal_content', $content);

			return true;
		}
		return false;
	}

	public function getPayPalURL()
	{
		return 'www' . (Configuration::get('PAYPAL_SANDBOX') ? '.sandbox' : '') . '.paypal.com';
	}

	public function getPaypalIntegralEvolutionUrl()
	{
		if (Configuration::get('PAYPAL_SANDBOX'))
		{
			return 'https://' . $this->getPayPalURL() . '/cgi-bin/acquiringweb';
		}

		return 'https://securepayments.paypal.com/acquiringweb?cmd=_hosted-payment';
	}

	public function getPaypalStandardUrl()
	{
		return 'https://' . $this->getPayPalURL() . '/cgi-bin/webscr';
	}

	public function getAPIURL()
	{
		return 'api-3t' . (Configuration::get('PAYPAL_SANDBOX') ? '.sandbox' : '') . '.paypal.com';
	}

	public function getAPIScript()
	{
		return '/nvp';
	}

	public function getCountryDependency($iso_code)
	{
		$localizations = array('AU' => array('AU'), 'BE' => array('BE'), 'CN' => array('CN', 'MO'), 'CZ' => array('CZ'), 'DE' => array('DE'), 'ES' => array('ES'), 'FR' => array('FR'), 'GB' => array('GB'), 'HK' => array('HK'), 'IL' => array('IL'), 'IN' => array('IN'), 'IT' => array('IT', 'VA'), 'JP' => array('JP'), 'MY' => array('MY'), 'NL' => array('AN', 'NL'), 'NZ' => array('NZ'), 'PL' => array('PL'), 'RA' => array('AF', 'AS', 'BD', 'BN', 'BT', 'CC', 'CK', 'CX', 'FM', 'HM', 'ID', 'KH', 'KI', 'KN', 'KP', 'KR', 'KZ', 'LA', 'LK', 'MH', 'MM', 'MN', 'MV', 'MX', 'NF', 'NP', 'NU', 'OM', 'PG', 'PH', 'PW', 'QA', 'SB', 'TJ', 'TK', 'TL', 'TM', 'TO', 'TV', 'TZ', 'UZ', 'VN', 'VU', 'WF', 'WS'), 'RE' => array('IE', 'ZA', 'GP', 'GG', 'JE', 'MC', 'MS', 'MP', 'PA', 'PY', 'PE', 'PN', 'PR', 'LC', 'SR', 'TT', 'UY', 'VE', 'VI', 'AG', 'AR', 'CA', 'BO', 'BS', 'BB', 'BZ', 'BR', 'CL', 'CO', 'CR', 'CU', 'SV', 'GD', 'GT', 'HN', 'JM', 'NI', 'AD', 'AE', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AT', 'AW', 'AX', 'AZ', 'BA', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BV', 'BW', 'BY', 'CD', 'CF', 'CG', 'CH', 'CI', 'CM', 'CV', 'CY', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ET', 'FI', 'FJ', 'FK', 'FO', 'GA', 'GE', 'GF', 'GH', 'GI', 'GL', 'GM', 'GN', 'GQ', 'GR', 'GS', 'GU', 'GW', 'GY', 'HR', 'HT', 'HU', 'IM', 'IO', 'IQ', 'IR', 'IS', 'JO', 'KE', 'KM', 'KW', 'KY', 'LB', 'LI', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MD', 'ME', 'MF', 'MG', 'MK', 'ML', 'MQ', 'MR', 'MT', 'MU', 'MW', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NO', 'NR', 'PF', 'PK', 'PM', 'PS', 'PT', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SC', 'SD', 'SE', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'ST', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TN', 'UA', 'UG', 'VC', 'VG', 'YE', 'YT', 'ZM', 'ZW'), 'SG' => array('SG'), 'TH' => array('TH'), 'TR' => array('TR'), 'TW' => array('TW'), 'US' => array('US'));

		foreach ($localizations as $key => $value)
		{
			if (in_array($iso_code, $value))
			{
				return $key;
			}
		}

		return false;
	}

	public function getPaymentMethods()
	{
		// WPS -> Web Payment Standard
		// HSS -> Web Payment Pro || Integral Evolution
		// ECS -> Express Checkout Solution

		$paymentMethod = array('AU' => array(WPS, HSS, ECS), 'BE' => array(WPS, ECS), 'CN' => array(WPS, ECS), 'CZ' => array(), 'DE' => array(WPS), 'ES' => array(WPS, HSS, ECS), 'FR' => array(WPS, HSS, ECS), 'GB' => array(WPS, HSS, ECS), 'HK' => array(WPS, HSS, ECS), 'IL' => array(WPS, ECS), 'IN' => array(WPS, ECS), 'IT' => array(WPS, HSS, ECS), 'JP' => array(WPS, HSS, ECS), 'MY' => array(WPS, ECS), 'NL' => array(WPS, ECS), 'NZ' => array(WPS, ECS), 'PL' => array(WPS, ECS), 'RA' => array(WPS, ECS), 'RE' => array(WPS, ECS), 'SG' => array(WPS, ECS), 'TH' => array(WPS, ECS), 'TR' => array(WPS, ECS), 'TW' => array(WPS, ECS), 'US' => array(WPS, ECS), 'ZA' => array(WPS, ECS));

		return isset($paymentMethod[$this->iso_code]) ? $paymentMethod[$this->iso_code] : $paymentMethod[self::DEFAULT_COUNTRY_ISO];
	}

	public function getCountryCode()
	{
		global $cookie;

		$cart = new Cart((int)$cookie->id_cart);
		$address = new Address((int)$cart->id_address_invoice);
		$country = new Country((int)$address->id_country);

		return $country->iso_code;
	}

	public function displayPayPalAPIError($message, $log = false)
	{
		global $cookie;

		$send = true;
		// Sanitize log
		foreach ($log as $key => $string)
		{
			if ($string == 'ACK -> Success')
			{
				$send = false;
			}
			else if (substr($string, 0, 6) == 'METHOD')
			{
				$values = explode('&', $string);
				foreach ($values as $key2 => $value)
				{
					$values2 = explode('=', $value);
					foreach ($values2 as $key3 => $value2)
					{
						if ($value2 == 'PWD' || $value2 == 'SIGNATURE')
						{
							$values2[$key3 + 1] = '*********';
						}
					}
					$values[$key2] = implode('=', $values2);
				}
				$log[$key] = implode('&', $values);
			}
		}

		$this->context->smarty->assign('message', $message);
		$this->context->smarty->assign('logs', $log);
		$data = array('{logs}' => implode('<br />', $log));

		if ($send)
		{
			$id_lang = (int)$cookie->id_lang;
			$shop_email = Configuration::get('PS_SHOP_EMAIL');
			$mail_dir = _PS_MODULE_DIR_ . $this->name . '/mails/';
			$error_message = Mail::l('Error reporting from your PayPal module', (int)$cookie->id_lang);

			Mail::Send($id_lang, 'error_reporting', $error_message, $data, $shop_email, null, null, null, null, null, $mail_dir);
		}

		return $this->fetchTemplate('/views/templates/front/', 'error');
	}

	public function _saveTransaction($id_order, $transaction)
	{
		$id_order = (int)$id_order;
		$id_transaction = pSQL($transaction['id_transaction']);
		$id_invoice = pSQL($transaction['id_invoice']);
		$currency = pSQL($transaction['currency']);
		$shipping = (float)$transaction['shipping'];
		$payment_date = pSQL($transaction['payment_date']);
		$capture = (int)Configuration::get('PAYPAL_CAPTURE');
		$payment_method = (int)Configuration::get('PAYPAL_PAYMENT_METHOD');

		$order = new Order($id_order);

		if ($order->gift)
		{
			$total_paid = (float)$transaction['total_paid'] + (float)Configuration::get('PS_GIFT_WRAPPING_PRICE');
		}
		else
		{
			$total_paid = (float)$transaction['total_paid'];
		}

		$sql = 'INSERT INTO `' . _DB_PREFIX_ . 'paypal_order`
				(`id_order`, `id_transaction`, `id_invoice`, `currency`, `total_paid`, `shipping`, `payment_date`, `payment_method`, `capture`)
				VALUES (' . $id_order . ', \'' . $id_transaction . '\', \'' . $id_invoice . '\', \'' . $currency . '\', \'' . $total_paid . '\', \'' . $shipping . '\', \'' . $payment_date . '\', ' . $payment_method . ', ' . $capture . ')';

		Db::getInstance()->Execute($sql);
	}

	private function _canRefund($id_order)
	{
		if (!(int)$id_order)
		{
			return false;
		}

		$sql = 'SELECT *
				FROM `' . _DB_PREFIX_ . 'paypal_order`
				WHERE `id_order` = ' . (int)$id_order;

		$paypal_order = Db::getInstance()->getRow($sql);

		if (!is_array($paypal_order) || !sizeof($paypal_order))
		{
			return false;
		}
		else if (($paypal_order['payment_status'] != 'Completed') || ($paypal_order['capture'] != 0))
		{
			return false;
		}

		return true;
	}

	private function _needValidation($id_order)
	{
		if (!(int)$id_order)
		{
			return false;
		}

		$sql = 'SELECT `payment_method`, `payment_status`
				FROM `' . _DB_PREFIX_ . 'paypal_order`
				WHERE `id_order` = ' . (int)$id_order;
		$order = Db::getInstance()->getRow($sql);

		if (!$order)
		{
			return false;
		}

		return ($order['payment_status'] == 'Pending') && ($order['payment_method'] == HSS);
	}

	private function _needCapture($id_order)
	{
		if (!(int)$id_order)
		{
			return false;
		}

		$sql = 'SELECT `payment_method`, `payment_status`, `capture`
			FROM `' . _DB_PREFIX_ . 'paypal_order`
			WHERE `id_order` = ' . (int)$id_order . ' AND `capture` = 1';
		$result = Db::getInstance()->getRow($sql);

		if (!isset($result['payment_method']))
		{
			return false;
		}
		else if (($result['payment_status'] != 'Pending_authorization') && ($result['payment_status'] != 'Completed'))
		{
			return false;
		}
		return true;
	}

	private function _preProcess()
	{
		if (Tools::isSubmit('submitPaypal'))
		{
			$business = Tools::getValue('business') !== false ? (int)Tools::getValue('business') : false;
			$payment_method = Tools::getValue('paypal_payment_method') !== false ? (int)Tools::getValue('paypal_payment_method') : false;
			$payment_capture = Tools::getValue('payment_capture') !== false ? (int)Tools::getValue('payment_capture') : false;
			$sandbox_mode = Tools::getValue('sandbox_mode') !== false ? (int)Tools::getValue('sandbox_mode') : false;

			$api_username = Tools::getValue('api_username');
			$api_password = Tools::getValue('api_password');
			$api_signature = Tools::getValue('api_signature');
			$api_business_account = Tools::getValue('api_business_account');

			if ($this->default_country === false || $sandbox_mode === false || $payment_capture === false || $business === false || $payment_method === false)
			{
				$this->_errors[] = $this->l('Some fields are empty.');
			}
			else if ((($business == 0) || (($business == 1) && ($payment_method != HSS))) && (empty($api_username) || empty($api_password) || empty($api_signature)))
			{
				$this->_errors[] = $this->l('Credentials fields cannot be empty');
			}
			else if (($business == 1) && ($payment_method == HSS) && empty($api_business_account))
			{
				$this->_errors[] = $this->l('Business e-mail field cannot be empty');
			}
		}

		if (sizeof($this->_errors) > 0)
		{
			return false;
		}

		return true;
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submitPaypal'))
		{
			if (Tools::getValue('paypal_country_only'))
			{
				Configuration::updateValue('PAYPAL_COUNTRY_DEFAULT', (int)Tools::getValue('paypal_country_only'));
			}
			else if ($this->_preProcess())
			{
				Configuration::updateValue('PAYPAL_BUSINESS', (int)Tools::getValue('business'));
				Configuration::updateValue('PAYPAL_PAYMENT_METHOD', (int)Tools::getValue('paypal_payment_method'));
				Configuration::updateValue('PAYPAL_API_USER', trim(Tools::getValue('api_username')));
				Configuration::updateValue('PAYPAL_API_PASSWORD', trim(Tools::getValue('api_password')));
				Configuration::updateValue('PAYPAL_API_SIGNATURE', trim(Tools::getValue('api_signature')));
				Configuration::updateValue('PAYPAL_BUSINESS_ACCOUNT', trim(Tools::getValue('api_business_account')));
				Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT', (int)Tools::getValue('express_checkout_shortcut'));
				Configuration::updateValue('PAYPAL_SANDBOX', (int)Tools::getValue('sandbox_mode'));
				Configuration::updateValue('PAYPAL_CAPTURE', (int)Tools::getValue('payment_capture'));

				$this->context->smarty->assign('PayPal_save_success', true);
			}
			else
			{
				$error_msg = '';

				foreach ($this->_errors as $error)
				{
					$error_msg .= $error . '<br />';
				}

				$this->_html = $this->displayError($error_msg); // Not displayed at this time
				$this->context->smarty->assign('PayPal_save_failure', true);
			}
		}

		$this->loadLangDefault();

		return;
	}

	private function _getTransactionId($id_order)
	{
		if (!(int)$id_order)
		{
			return false;
		}

		$sql = 'SELECT `id_transaction`
				FROM `' . _DB_PREFIX_ . 'paypal_order`
				WHERE `id_order` = ' . (int)$id_order;

		return Db::getInstance()->getValue($sql);
	}

	private function _makeRefund($id_transaction, $id_order, $amt = false)
	{
		include_once(_PS_MODULE_DIR_ . 'paypal/api/paypal_lib.php');

		if (!$this->isPayPalAPIAvailable())
		{
			die(Tools::displayError('Fatal Error: no API Credentials are available'));
		}
		if (!$id_transaction)
		{
			die(Tools::displayError('Fatal Error: id_transaction is null'));
		}

		if (!$amt)
		{
			$request = array('TRANSACTIONID' => $id_transaction, 'REFUNDTYPE'    => 'Full');
		}
		else
		{
			$sql = 'SELECT `iso_code`
					FROM `' . _DB_PREFIX_ . 'orders` o
					LEFT JOIN `' . _DB_PREFIX_ . 'currency` c ON (o.`id_currency` = c.`id_currency`)
					WHERE o.`id_order` = ' . (int)$id_order;

			$isoCurrency = Db::getInstance()->getValue($sql);

			$request = array('TRANSACTIONID' => $id_transaction, 'REFUNDTYPE' => 'Partial', 'AMT' => (float)$amt, 'CURRENCYCODE' => Tools::strtoupper($isoCurrency));
		}

		$paypal_lib = new PaypalLib();
		$request = '&' . http_build_query($request, '', '&');

		return $paypal_lib->makeCall($this->getAPIURL(), $this->getAPIScript(), 'RefundTransaction', $request);
	}

	private function _addNewPrivateMessage($id_order, $message)
	{
		if (!$id_order)
		{
			return false;
		}
		$msg = new Message();
		$message = strip_tags($message, '<br>');
		if (!Validate::isCleanHtml($message))
		{
			$message = $this->l('Payment message is not valid, please check your module.');
		}
		$msg->message = $message;
		$msg->id_order = (int)$id_order;
		$msg->private = 1;

		return $msg->add();
	}

	private function _doTotalRefund($id_order)
	{
		if (!$this->isPayPalAPIAvailable() || !$id_order)
		{
			return false;
		}

		$id_transaction = $this->_getTransactionId((int)$id_order);
		if (!$id_transaction)
		{
			return false;
		}

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
		{
			return false;
		}
		$products = $order->getProducts();

		$currency = new Currency((int)$this->context->cart->id_currency);
		if (!Validate::isLoadedObject($currency))
		{
			$this->_errors[] = $this->l('Not a valid currency');
		}
		if (sizeof($this->_errors))
		{
			return false;
		}

		$currency_decimals = is_array($currency) ? (int)$currency['decimals'] : (int)$currency->decimals;
		$decimals = $currency_decimals * _PS_PRICE_DISPLAY_PRECISION_;

		// Amount for refund
		$amt = 0.00;

		foreach ($products as $product)
		{
			$amt += (float)($product['product_price_wt']) * ($product['product_quantity'] - $product['product_quantity_refunded']);
		}
		$amt += (float)($order->total_shipping) + (float)($order->total_wrapping) - (float)($order->total_discounts);

		// check if total or partial
		if (Tools::ps_round($order->total_paid_real, $decimals) == Tools::ps_round($amt, $decimals))
		{
			$response = $this->_makeRefund($id_transaction, $id_order);
		}
		else
		{
			$response = $this->_makeRefund($id_transaction, $id_order, (float)($amt));
		}

		$message = $this->l('Refund operation result:') . '<br>';
		foreach ($response AS $k => $value)
		{
			$message .= $k . ': ' . $value . '<br>';
		}
		if (array_key_exists('ACK', $response) && $response['ACK'] == 'Success' && $response['REFUNDTRANSACTIONID'] != '')
		{
			$message .= $this->l('PayPal refund successful!');
			if (!Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'paypal_order` SET `payment_status` = \'Refunded\' WHERE `id_order` = ' . (int)$id_order))
			{
				die(Tools::displayError('Error when updating PayPal database'));
			}
			$history = new OrderHistory();
			$history->id_order = (int)$id_order;
			$history->changeIdOrderState(Configuration::get('PS_OS_REFUND'), (int)$id_order);
			$history->addWithemail();
		}
		else
		{
			$message .= $this->l('Transaction error!');
		}
		$this->_addNewPrivateMessage((int)$id_order, $message);

		return $response;
	}

	private function _doCapture($id_order)
	{
		include_once(_PS_MODULE_DIR_ . 'paypal/api/paypal_lib.php');

		if (!$this->isPayPalAPIAvailable())
		{
			return false;
		}
		if (!$id_order)
		{
			return false;
		}

		$id_transaction = $this->_getTransactionId((int)$id_order);
		if (!$id_transaction)
		{
			return false;
		}

		$order = new Order((int)$id_order);
		$currency = new Currency((int)$order->id_currency);
		$request = array('AUTHORIZATIONID' => $id_transaction, 'AMT'             => (float)$order->total_paid, 'CURRENCYCODE'    => $currency->iso_code, 'COMPLETETYPE'    => 'Complete');
		$request = '&' . http_build_query($request, '', '&');

		$paypalLib = new PaypalLib();
		$response = $paypalLib->makeCall($this->getAPIURL(), $this->getAPIScript(), 'DoCapture', $request);

		$message = $this->l('Capture operation result:') . '<br>';
		foreach ($response AS $k => $value)
		{
			$message .= $k . ': ' . $value . '<br>';
		}

		if ((array_key_exists('ACK', $response)) && ($response['ACK'] == 'Success') && ($response['PAYMENTSTATUS'] == 'Completed'))
		{
			$order_history = new OrderHistory();
			$order_history->id_order = (int)$id_order;
			$order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int)$id_order);
			$order_history->addWithemail();
			$message .= $this->l('Order finished with PayPal!');
		}
		else if (isset($response['PAYMENTSTATUS']))
		{
			$message .= $this->l('Transaction error!');
		}

		$sql = 'UPDATE `' . _DB_PREFIX_ . 'paypal_order`
				SET `capture` = 0, `payment_status` = \'' . pSQL($response['PAYMENTSTATUS']) . '\', `id_transaction` = \'' . pSQL($response['TRANSACTIONID']) . '\'
				WHERE `id_order` = ' . (int)$id_order;

		if (!Db::getInstance()->Execute($sql))
		{
			die(Tools::displayError('Error when updating PayPal database'));
		}
		$this->_addNewPrivateMessage((int)$id_order, $message);

		return $response;
	}

	private function _updatePaymentStatusOfOrder($id_order)
	{
		include_once(_PS_MODULE_DIR_ . 'paypal/api/paypal_lib.php');

		if (!$this->isPayPalAPIAvailable())
		{
			return false;
		}
		if (!$id_order)
		{
			return false;
		}

		$id_transaction = $this->_getTransactionId((int)$id_order);
		if (!$id_transaction)
		{
			return false;
		}

		$request = array('TRANSACTIONID' => $id_transaction);
		$request = '&' . http_build_query($request, '', '&');

		$paypalLib = new PaypalLib();
		$response = $paypalLib->makeCall($this->getAPIURL(), $this->getAPIScript(), 'GetTransactionDetails', $request);
		if (array_key_exists('ACK', $response))
		{
			if ($response['ACK'] == 'Success')
			{
				if (isset($response['PAYMENTSTATUS']))
				{
					if ($response['PAYMENTSTATUS'] == 'Completed')
					{
						$history = new OrderHistory();
						$history->id_order = (int)$id_order;
						$history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int)$id_order);
						$history->addWithemail();
					}
					else if (($response['PAYMENTSTATUS'] == 'Pending') && ($response['PENDINGREASON'] == 'authorization'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$id_order;
						$history->changeIdOrderState((int)(Configuration::get('PAYPAL_OS_AUTHORIZATION')), (int)$id_order);
						$history->addWithemail();
					}
					else if ($response['PAYMENTSTATUS'] == 'Reversed')
					{
						$history = new OrderHistory();
						$history->id_order = (int)$id_order;
						$history->changeIdOrderState(Configuration::get('PS_OS_ERROR'), (int)$id_order);
						$history->addWithemail();
					}

					$sql = 'UPDATE `' . _DB_PREFIX_ . 'paypal_order`
							SET `payment_status` = \'' . pSQL($response['PAYMENTSTATUS']) . ($response['PENDINGREASON'] == 'authorization' ? '_authorization' : '') . '\'
							WHERE `id_order` = ' . (int)$id_order;
					if (!Db::getInstance()->Execute($sql))
					{
						die(Tools::displayError('Error when updating PayPal database'));
					}
				}
			}

			$message = $this->l('Verification status:') . '<br>';
			foreach ($response AS $k => $value)
			{
				$message .= $k . ': ' . $value . '<br>';
			}

			$this->_addNewPrivateMessage((int)$id_order, $message);
			return $response;
		}
		return false;
	}

	public function getOrder($id_transaction)
	{
		return Db::getInstance()->getValue('
		SELECT `id_order`
		FROM `' . _DB_PREFIX_ . 'paypal_order`
		WHERE `id_transaction` = \'' . pSQL($id_transaction) . '\'');
	}

	// Retrocompatibility to 1.3
	const ONLY_PRODUCTS = 1;
	const ONLY_DISCOUNTS = 2;
	const BOTH = 3;
	const BOTH_WITHOUT_SHIPPING = 4;
	const ONLY_SHIPPING = 5;
	const ONLY_WRAPPING = 6;
	const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;

	/**
	 * Return the complete URI for a module
	 * Could be use for return URL process or ajax call
	 *
	 * @param string $file of the module you want to target
	 * @param array $options (value=key)
	 *
	 * @return string
	 */
	public function getURI($file = '', $options = array())
	{
		$url = __PS_BASE_URI__ . 'modules/' . $this->name . '/' . (!empty($file) ? $file : '');

		$query = null;
		if (is_array($options))
		{
			$query = '?' . http_build_query($options, '', '&');
		}

		if (method_exists('Tools', 'getShopDomainSsl'))
		{
			return Tools::getShopDomainSsl() . $url . $query;
		}
		else
		{
			return PayPal::getShopDomainSsl() . url . $query;
		}
	}

	public static function getIdCustomerWithPPEmail($email)
	{
		$sql = 'SELECT `id_customer`
				FROM `' . _DB_PREFIX_ . 'paypal_customer`
				WHERE paypal_email ="' . pSQL($email) . '"';

		return Db::getInstance()->getValue($sql);
	}

	public static function getPPEMailByIdCustomer($id_customer)
	{
		$sql = 'SELECT `paypal_email`
				FROM `' . _DB_PREFIX_ . 'paypal_customer`
				WHERE `id_customer` ="' . (int)$id_customer . '"';

		return Db::getInstance()->getValue($sql);
	}

	public static function addPPCustomer($id_customer, $email)
	{
		if (!PayPal::getPPEMailByIdCustomer($id_customer))
		{
			$sql = 'INSERT INTO `' . _DB_PREFIX_ . 'paypal_customer`
					(`id_customer`, `paypal_email`)
					VALUES(' . (int)$id_customer . ', "' . pSQL($email) . '")';

			Db::getInstance()->execute($sql);

			return Db::getInstance()->Insert_ID();
		}

		return false;
	}

    private function warningsCheck()
    {
        if (Configuration::get('PAYPAL_BUSINESS_ACCOUNT') == 'paypal@prestashop.com')
        {
            $this->warning = $this->l('You are currently using the default PayPal e-mail address, please enter your own e-mail address.');
        }
        /* Check preactivation warning */
        if (Configuration::get('PS_PREACTIVATION_PAYPAL_WARNING'))
        {
            if (!empty($this->warning))
            {
                $this->warning .= ', ';
            }
            $this->warning .= Configuration::get('PS_PREACTIVATION_PAYPAL_WARNING');
        }
    }

    private function loadLangDefault()
    {
        // Get values
        $ps_country_default = Configuration::get('PS_COUNTRY_DEFAULT');
        $paypal_country_default = (int)Configuration::get('PAYPAL_COUNTRY_DEFAULT');

        $this->default_country = (int)($paypal_country_default ? $paypal_country_default : $ps_country_default);
        $this->iso_code = $this->getCountryDependency(Country::getIsoById((int)$this->default_country));
    }

	public function getContext()
	{
		return $this->context;
	}

	public static function getShopDomain($http = false, $entities = false)
	{
		if (!($domain = Configuration::get('PS_SHOP_DOMAIN')))
			$domain = self::getHttpHost();
		if ($entities)
			$domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
		if ($http)
			$domain = 'http://'.$domain;
		return $domain;
	}

	public static function getShopDomainSsl($http = false, $entities = false)
	{
		if (!($domain = Configuration::get('PS_SHOP_DOMAIN_SSL')))
			$domain = self::getHttpHost();
		if ($entities)
			$domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
		if ($http)
			$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$domain;
		return $domain;
	}
}