<?php

class PaypalAPI extends PaymentModule
{
	const		INSTALL_SQL_FILE = 'install.sql';

	protected	$_header;
	protected	$_apiUser;
	protected	$_apiPassword;
	protected	$_apiSignature;
	protected	$_sandbox;
	protected	$_expressCheckout;
	protected	$_pp_integral;

	public function __construct()
	{
		$this->name = 'paypalapi';
		$this->tab = 'Payment';
		$this->version = '1.0';

		$this->currencies = true;
		$this->currencies_mode = 'radio';

		parent::__construct();

		$this->displayName = $this->l('PayPalAPI');
		$this->description = $this->l('Accepts payments by PayPal using API');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

		$this->_init();
		if (!isset($this->_apiUser) OR !isset($this->_apiPassword) OR !isset($this->_apiSignature) OR !$this->_apiUser OR !$this->_apiPassword OR !$this->_apiSignature)
			$this->warning = $this->l('You need to configure your PayPal API login (username, password and signature)');
	}

	protected function _init()
	{
		$config = Configuration::getMultiple(array('PAYPAL_HEADER', 'PAYPAL_SANDBOX', 'PAYPAL_API_USER', 'PAYPAL_API_PASSWORD', 'PAYPAL_API_SIGNATURE', 'PAYPAL_EXPRESS_CHECKOUT', 'PAYPAL_INTEGRAL', 'PAYPAL_OPTION_PLUS'));
		if (isset($config['PAYPAL_HEADER']))
			$this->_header = $config['PAYPAL_HEADER'];
		if (isset($config['PAYPAL_SANDBOX']))
			$this->_sandbox = $config['PAYPAL_SANDBOX'];
		if (isset($config['PAYPAL_API_USER']))
			$this->_apiUser = $config['PAYPAL_API_USER'];
		if (isset($config['PAYPAL_API_PASSWORD']))
			$this->_apiPassword = $config['PAYPAL_API_PASSWORD'];
		if (isset($config['PAYPAL_API_SIGNATURE']))
			$this->_apiSignature = $config['PAYPAL_API_SIGNATURE'];
		if (isset($config['PAYPAL_EXPRESS_CHECKOUT']))
			$this->_expressCheckout = $config['PAYPAL_EXPRESS_CHECKOUT'];
		if (isset($config['PAYPAL_INTEGRAL']))
			$this->_pp_integral = $config['PAYPAL_INTEGRAL'];
	}

	public function install()
	{
		// SQL Table
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			die('lol');
		elseif (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			die('lal');
		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($sql as $query)
			if ($query AND sizeof($query) AND !Db::getInstance()->Execute(trim($query)))
				return false;

		// Next
		if (!parent::install()
			OR !Configuration::updateValue('PAYPAL_HEADER', 0)
			OR !Configuration::updateValue('PAYPAL_SANDBOX', 1)
			OR !Configuration::updateValue('PAYPAL_API_USER', 0)
			OR !Configuration::updateValue('PAYPAL_API_PASSWORD', 0)
			OR !Configuration::updateValue('PAYPAL_API_SIGNATURE', 0)
			OR !Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT', 0)
			OR !Configuration::updateValue('PAYPAL_INTEGRAL', 0)
			OR !$this->registerHook('payment')
			OR !$this->registerHook('shoppingCartExtra')
			OR !$this->registerHook('backBeforePayment')
			OR !$this->registerHook('paymentReturn')
			OR !$this->registerHook('rightColumn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall()
			OR !Configuration::deleteByName('PAYPAL_HEADER')
			OR !Configuration::deleteByName('PAYPAL_SANDBOX')
			OR !Configuration::deleteByName('PAYPAL_API_USER')
			OR !Configuration::deleteByName('PAYPAL_API_PASSWORD')
			OR !Configuration::deleteByName('PAYPAL_API_SIGNATURE')
			OR !Configuration::deleteByName('PAYPAL_EXPRESS_CHECKOUT')
			OR !Configuration::deleteByName('PAYPAL_INTEGRAL')
			)
			return false;
		return true;
	}

	public function displayError($message, $log = false)
	{
		global $cookie, $smarty;

		// Sanitinize log
		foreach ($log as $key => $string)
			if (substr($string, 0, 6) == 'METHOD')
			{
				$values = explode('&', $string);
				foreach ($values as $key2 => $value)
				{
					$values2 = explode('=', $value);
					foreach ($values2 as $key3 => $value2)
						if ($value2 == 'PWD' || $value2 == 'SIGNATURE')
							$values2[$key3 + 1] = '*********';
					$values[$key2] = implode('=', $values2);
				}
				$log[$key] = implode('&', $values);
			}

		include(dirname(__FILE__).'/../../header.php');
		$smarty->assign('message', $message);
		$smarty->assign('logs', $log);
		echo $this->display(__FILE__, 'error.tpl');
		include_once(dirname(__FILE__).'/../../footer.php');
		die ;
	}

	/************************************************************/
	/************************** GETTERS **************************/
	/************************************************************/

	public function getPayPalURL()
	{
		return 'www'.($this->_sandbox ? '.sandbox' : '').'.paypal.com';
	}

	public function getPayPalScript()
	{
		return '/cgi-bin/webscr';
	}

	public function getAPIURL()
	{
		return 'api-3t'.($this->_sandbox ? '.sandbox' : '').'.paypal.com';
	}
	
	public function getAPIScript()
	{
		return '/nvp';
	}

	/************************************************************/
	/******************** ADMIN CONFIGURATION ********************/
	/************************************************************/

	public function getContent()
	{
		include(_PS_MODULE_DIR_.'/paypalapi/admin/PaypalAdmin.php');
		$ppAdmin = new PaypalAdmin();
		return $ppAdmin->home();
	}

	/************************************************************/
	/************************** HOOKS ****************************/
	/************************************************************/

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;

		include(_PS_MODULE_DIR_.'/paypalapi/payment/PaypalPayment.php');
		$ppPayment = new PaypalPayment();
		return $ppPayment->home($params);
	}

	public function hookShoppingCartExtra($params)
	{
		global $cookie;

		if (!$this->active)
			return ;

		if (Configuration::get('PAYPAL_EXPRESS_CHECKOUT') AND !$cookie->isLogged())
		{
			include(_PS_MODULE_DIR_.'/paypalapi/express/PaypalExpress.php');
			$ppExpress = new PaypalExpress();
			return $ppExpress->home($params);
		}
	}

	public function hookBackBeforePayment($params)
	{
		if (!$this->active)
			return ;

		global $cookie;

		if ($params['module'] != $this->name)
			return false;
		if (!$token = strval($cookie->paypal_token))
			return false;
		if (!$payerID = strval($cookie->paypal_payer_id))
			return false;
		Tools::redirect('modules/paypalapi/express/submit.php?confirm=1&token='.$token.'&payerID='.$payerID);
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;

		global $smarty;

		$state = $params['objOrder']->getCurrentState();
		if ($state == _PS_OS_PAYMENT_ OR $state == _PS_OS_OUTOFSTOCK_)
			$smarty->assign('status', 'ok');
		elseif ($state == _PS_OS_PAYPAL_)
			$smarty->assign('status', 'pending');
		else
			$smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}

	function hookRightColumn($params)
	{
		global $smarty, $cookie;

		$iso_code = Tools::strtoupper(Language::getIsoById($cookie->id_lang ? intval($cookie->id_lang) : 1));
		if ($iso_code == 'FR')
			$logo = _MODULE_DIR_.$this->name.'/img/vertical_FR_large.png';
		else
			$logo = _MODULE_DIR_.$this->name.'/img/vertical_US_large.png';
		$smarty->assign('logo', $logo);
		return $this->display(__FILE__, 'column.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	/************************************************************/
	/************************ ORDER TABLE ************************/
	/************************************************************/

	protected function addOrder($id_transaction)
	{
		return Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'paypal_order` (`id_order`, `id_transaction`)
		VALUES('.intval($this->currentOrder).', \''.pSQL($id_transaction).'\')');
	}

	public function getOrder($id_transaction)
	{
		$rq = Db::getInstance()->getRow('
		SELECT `id_order` FROM `'._DB_PREFIX_.'paypal_order`
		WHERE id_transaction = \''.pSQL($id_transaction).'\'');
		return $rq['id_order'];
	}

	/************************************************************/
	/**************************** MISC ***************************/
	/************************************************************/
	
	protected function displayFinal($id_cart)
	{
		if (!$this->active)
			return ;

		global $cookie;

		unset($cookie->paypal_token);
		$order = new Order(intval($this->currentOrder));
		Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.intval($id_cart).'&id_module='.intval($this->id).'&id_order='.intval($this->currentOrder).'&key='.$order->secure_key);
	}

	public function validOrder($cookie, $cart, $id_currency, $payerID, $type)
	{
		if (!$this->active)
			return ;

		// Filling-in vars
		$id_cart = intval($cart->id);
		$currency = new Currency(intval($id_currency));
		$iso_currency = $currency->iso_code;
		$token = strval($cookie->paypal_token);
		$total = number_format(floatval($cart->getOrderTotal(true, 3)), 2, '.', '');
		$payerID = strval($payerID);
		$paymentType = 'Sale';
		$serverName = urlencode($_SERVER['SERVER_NAME']);
		$bn = ($type == 'express' ? 'ECS' : 'ECM');
		$notifyURL = urlencode('http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/paypalapi/ipn.php');

		// Making request
		$request='&TOKEN='.urlencode($token).'&PAYERID='.urlencode($payerID).'&PAYMENTACTION='.$paymentType.'&AMT='.$total.'&CURRENCYCODE='.$iso_currency.'&IPADDRESS='.$serverName.'&NOTIFYURL='.$notifyURL.'&BUTTONSOURCE=PRESTASHOP_'.$bn;

		// Calling PayPal API
		include(_PS_MODULE_DIR_.'paypalapi/api/PaypalLib.php');
		$ppAPI = new PaypalLib();
		$result = $ppAPI->makeCall($this->getAPIURL(), $this->getAPIScript(), 'DoExpressCheckoutPayment', $request);
		$this->_logs = array_merge($this->_logs, $ppAPI->getLogs());

		// Checking PayPal result
		if (!is_array($result) OR !sizeof($result))
			$this->displayError($this->l('Authorisation to PayPal failed'), $this->_logs);
		elseif (!isset($result['ACK']) OR  strtoupper($result['ACK']) != 'SUCCESS')
			$this->displayError($this->l('PayPal returned error'), $this->_logs);
		elseif (!isset($result['TOKEN']) OR $result['TOKEN'] != $cookie->paypal_token)
		{
			$logs[] = '<b>'.$ppExpress->l('Token given by PayPal is not the same that cookie one', 'submit').'</b>';
			$ppExpress->displayError($ppExpress->l('PayPal returned error', 'submit'), $logs);
		}

		// Making log
		$id_transaction = strval($result['TRANSACTIONID']);
		$this->_logs[] = $this->l('Order finished with PayPal!');
		$message = Tools::htmlentitiesUTF8(strip_tags(implode("\n", $this->_logs)));

		// Order status
		switch ($result['PAYMENTSTATUS'])
		{
			case 'Completed':
				$id_order_state = _PS_OS_PAYMENT_;
				break;
			case 'Pending':
				$id_order_state = _PS_OS_PAYPAL_;
				break;
			default:
				$id_order_state = _PS_OS_ERROR_;
		}

		// Execute Module::validateOrder()
		$this->validateOrder($id_cart, $id_order_state, floatval($cart->getOrderTotal(true, 3)), $this->displayName, $message, array(), $id_currency);

		// Filling PayPal table
		$this->addOrder($id_transaction);

		// Displaying output
		$this->displayFinal($id_cart);
	}

	public function getCountryCode()
	{
		global $cookie;

		$cart = new Cart(intval($cookie->id_cart));
		$address = new Address(intval($cart->id_address_invoice));
		$country = new Country(intval($address->id_country));
		return $country->iso_code;
	}

	public function getLocaleCode()
	{
		global $cookie;

		return Language::getIsoById(intval($cookie->id_lang)).'_'.Tools::strtoupper($this->getCountryCode());
	}
	
	public function getLogo($ppExpress = false)
	{
		global $cookie;

		if ($ppExpress)
		{
			$iso_code = Tools::strtoupper(Language::getIsoById($cookie->id_lang ? intval($cookie->id_lang) : 1));
			$logo = array(
				'FR' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=fr_FR',
				'DE' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=de_DE',
				'US' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=en_US',
				'UK' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=en_EN',
				'IT' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=it_IT',
				'ES' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=es_ES',
				'PL' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=pl_PL',
				'NL' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=nl_NL',
				'AU' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=au_AU',
				'CA' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=ca_CA',
				'CN' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=cn_CN',
				'JP' => 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=jp_JP'
			);
			if (isset($logo[$iso_code]))
				return $logo[$iso_code];
			return $logo['US'];
		}

		if ($this->_pp_integral)
		{
			$country_code = $this->getCountryCode();
			$logo = array(
				'FR' => 'https://www.paypal.com/en_US/FR/i/bnr/bnr_horizontal_solution_PP_327wx80h.gif',
				'DE' => 'https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x65.gif',
				'US' => 'https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif',
				'UK' => 'https://www.paypal.com/en_GB/i/bnr/horizontal_solution_PP.gif',
				'IT' => 'https://www.paypal.com/en_US/IT/i/bnr/bnr_horizontal_solution_PP_178wx80h.gif',
				'ES' => 'https://www.paypal.com/es_ES/ES/i/bnr/horizontal_solution_PP.gif',
				'PL' => 'https://www.paypal.com/en_US/PL/i/bnr/horizontal_solution_PP.gif',
				'NL' => 'https://www.paypal.com/nl_NL/NL/i/bnr/horizontal_solution_PP.gif',
				'AU' => 'https://www.paypal.com/en_AU/AU/i/bnr/horizontal_solution_PP.gif',
				'CA' => 'https://www.paypal.com/fr_XC/i/bnr/horizontal_solution_PPeCheck.gif',
				'CN' => 'https://www.paypal.com/zh_CN/i/bnr/horizontal_solution_PP.gif',
				'JP' => 'https://www.paypal.com/en_US/JP/i/bnr/horizontal_solution_3_noamex_jcb.gif'
			);
			if (isset($logo[$country_code]))
				return $logo[$country_code];
			return $logo['US'];
		}
		else
			return 'https://www.paypal.com/en_US/i/logo/PayPal_mark_60x38.gif';
	}
}
