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
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');
include_once(_PS_MODULE_DIR_.'paypal/api/paypal_lib.php');

class PaypalExpressCheckout extends Paypal
{
	public $logs = array();

	public $method_version = '84';

	public $method;

	/** @var result Contains the last request result **/
	public $result;

	/** @var result Contains the last token result **/
	public $token;

	// Depending of the type set, id_cart or id_product will be set
	public $id_cart;

	// Depending of the type set, id_cart or id_product will be set
	public $id_product;

	public $id_p_attr;

	public $quantity;

	public $payer_id;

	public $available_type = array('cart', 'product', 'payment_cart');

	public $total_different_product;

	public $product_list = array();

	// Used to know if user can validated his payment after shipping / address selection
	public $ready = false;

	// Take for now cart or product value
	public $type = false;

	static public $COOKIE_NAME = 'express_checkout';

	public $cookie_key = array(
		'token', 'id_product', 'id_p_attr', 'quantity', 'type', 'total_different_product',
		'secure_key', 'ready', 'payer_id'
	);

	public function __construct($type = false)
	{
		parent::__construct();

		// If type is sent, the cookie has to be delete
		if ($type)
		{
			unset($this->context->cookie->{PaypalExpressCheckout::$COOKIE_NAME});
			$this->setExpressCheckoutType($type);
		}

		// Store back the paypal data if present under the cookie
		if (isset($this->context->cookie->{PaypalExpressCheckout::$COOKIE_NAME}))
		{
			$paypal = unserialize($this->context->cookie->{PaypalExpressCheckout::$COOKIE_NAME});
			foreach ($this->cookie_key as $key)
				$this->{$key} = $paypal[$key];
		}
	}

	// Will build the product_list depending of the type
	private function initParameters($need_init = false)
	{
		switch($this->type)
		{
			case 'product':

				if ($need_init)
				{
					$this->id_product = (int)Tools::getValue('id_product');
					if (!($this->quantity = (int)Tools::getValue('quantity')))
						return false;
					$this->id_p_attr = Tools::getValue('id_p_attr') ? Tools::getValue('id_p_attr') : $this->id_p_attr;
				}

				if (!($product = new Product($this->id_product)))
					return false;

				// Build a product array with necessaries values
				$this->product_list[] = array(
					'id_product' => $product->id,
					'id_product_attribute' => $this->id_p_attr,
					'quantity' => $this->quantity,
					'price' => $product->getPrice(false, $this->id_p_attr, 2),
					'description_short' => $product->description[$this->context->language->id],
					'name' => $product->name[$this->context->language->id],
					'price_wt' => $product->getPrice(true, $this->id_p_attr, 2),
				);
				$this->product_list[0]['total_wt'] = $this->product_list[0]['price_wt'] * (int)($this->quantity);
				$this->product_list[0]['total'] = bcmul(Tools::ps_round($this->product_list[0]['price'], 2), (int)$this->quantity, 2);
				break;

			case ('cart' || 'payment_cart') :
				if (!$this->context->cart || !$this->context->cart->id)
					return false;
				$this->product_list = $this->context->cart->getProducts();
				break;
		}
		if (!count($this->product_list))
			return false;
		return true;
	}

	public function setExpressCheckout()
	{
		$this->method = 'SetExpressCheckout';
		$fields = array();

		// Only this call need to get the value from the $_GET / $_POST array
		if (!$this->initParameters(true))
			return false;

		if (!($fields['CANCELURL'] = Tools::getValue('current_shop_url')))
			return false;

		// Set payment detail (reference)
		$this->_setFieldsPaymentDetail($fields);

		// Seller
		$fields['PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID'] = Configuration::get('PS_SHOP_EMAIL');

		$this->callAPI($fields);
		$this->_storeToken();
	}

	public function getExpressCheckout()
	{
		$this->method = 'GetExpressCheckoutDetails';

		$this->initParameters();

		$fields = array();
		$fields['TOKEN'] = $this->token;

		$this->callAPI($fields);

		// The same token of SetExpressCheckout
		$this->_storeToken();
	}

	public function doExpressCheckout()
	{
		$this->method = 'DoExpressCheckoutPayment';
	
		$fields = array();
		$fields['TOKEN'] = $this->token;
		$fields['PAYERID'] = $this->payer_id;

		if (!count($this->product_list))
			$this->initParameters();

		// Set payment detail (reference)
		$this->_setFieldsPaymentDetail($fields);
		$this->callAPI($fields);
	}

	private function callAPI($fields)
	{
		$this->logs = array();

		$pp = new PaypalLib();
		$this->result = $pp->makeCall($this->getAPIURL(), $this->getAPIScript(), $this->method, $fields, $this->method_version);
		$this->logs = array_merge($this->logs, $pp->getLogs());
		$this->_storeToken();
	}

	private function _setFieldsPaymentDetail(&$fields)
	{
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

		if (_PS_VERSION_ < '1.5')
			$shipping_cost = $this->context->cart->getOrderShippingCost();
		else
			$shipping_cost = $this->context->cart->getTotalShippingCost();

		// Express Checkout back url

		if (method_exists('Tools', 'getShopDomainSsl'))
			$shop_url = Tools::getShopDomainSsl(true, true);
		else
			$shop_url = PayPal::getShopDomainSsl(true, true);

		$fields['RETURNURL'] = $shop_url . _MODULE_DIR_ . $this->name . '/express_checkout/submit.php';

		// Required field
		$fields['REQCONFIRMSHIPPING'] = '0';
		$fields['NOSHIPPING'] = '2';
		$fields['LOCALECODE'] = '2';

		// Product
		$num = 0;
		$total = 0;

		foreach ($this->product_list as $product)
		{
			$fields['L_PAYMENTREQUEST_0_NAME'.$num] = $product['name'];
			$fields['L_PAYMENTREQUEST_0_NUMBER'.$num] = $product['id_product'];
			$fields['L_PAYMENTREQUEST_0_DESC'.$num] = substr(strip_tags($product['description_short']), 0, 120).'...';
			$fields['L_PAYMENTREQUEST_0_AMT'.$num] = Tools::ps_round($product['total_wt'], $decimals);
			$fields['L_PAYMENTREQUEST_0_QTY'.$num] = Tools::ps_round($product['quantity'], $decimals);
			$total = bcadd($total, Tools::ps_round($product['total_wt'], $decimals), 2);
			++$num;
		}

		if ($this->context->cart->gift == 1)
		{
			$gift_wrapping_price = (float)Configuration::get('PS_GIFT_WRAPPING_PRICE');
			$fields['L_PAYMENTREQUEST_0_NAME'.$num] = $this->l('Gift wrapping');
			$fields['L_PAYMENTREQUEST_0_AMT'.$num] = Tools::ps_round($gift_wrapping_price, $decimals);
			$fields['L_PAYMENTREQUEST_0_QTY'.$num] = 1;
			$total = bcadd($total, Tools::ps_round($gift_wrapping_price, $decimals), 2);
		}

		// Payement
		$fields['PAYMENTREQUEST_0_ITEMAMT'] = $total;
		$fields['PAYMENTREQUEST_0_SHIPPINGAMT'] = Tools::ps_round($shipping_cost, $decimals);
		$fields['PAYMENTREQUEST_0_AMT'] = Tools::ps_round($total, $decimals) + Tools::ps_round($shipping_cost, $decimals);

		$fields['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
		$fields['PAYMENTREQUEST_0_TAXAMT'] = 0;
		$fields['PAYMENTREQUEST_0_CURRENCYCODE'] = $currency->iso_code;

		foreach ($fields as &$field)
		{
			if (is_numeric($field))
			{
				$field = str_replace(',', '.', $field);
			}
		}
	}

	public function rightPaymentProcess()
	{
		$total = $this->getTotalPaid();

		// float problem with php, have to use the string cast.
		if ((isset($this->result['AMT']) && ((string)$this->result['AMT'] != (string)$total)) ||
				(isset($this->result['PAYMENTINFO_0_AMT']) && ((string)$this->result['PAYMENTINFO_0_AMT'] != (string)$total)))
			return false;
		return true;
	}

	/**
	 * @return mixed
	 */
	public function getTotalPaid()
	{
		$total = 0.0;
		foreach ($this->product_list as $product)
			$total += $product['total_wt'];

		if ($this->context->cart->gift == 1)
			$total += Configuration::get('PS_GIFT_WRAPPING_PRICE');

		if (_PS_VERSION_ < '1.5')
			return $this->context->cart->getOrderShippingCost() + $total;
		else
			return $this->context->cart->getTotalShippingCost() + $total;
	}

	private function _storeToken()
	{
		if (is_array($this->result) && isset($this->result['TOKEN']))
			$this->token = strval($this->result['TOKEN']);
	}

	// Store data for the next reloading page
	private function _storeCookieInfo()
	{
		$tab = array();

		foreach ($this->cookie_key as $key)
			$tab[$key] = $this->{$key};

		$this->context->cookie->{PaypalExpressCheckout::$COOKIE_NAME} = serialize($tab);
	}

	public function hasSucceedRequest()
	{
		$ack_list = array('ACK', 'PAYMENTINFO_0_ACK');

		if (is_array($this->result))
			foreach($ack_list as $key)
				if (isset($this->result[$key]) && strtoupper($this->result[$key]) == 'SUCCESS')
			return true;
		return false;
	}

	private function getSecureKey()
	{
		if (!count($this->product_list))
			$this->initParameters();

		$key = array();
		foreach($this->product_list as $product)
			$key[] = $product['id_product'].$product['id_product_attribute'].$product['quantity']._COOKIE_KEY_;

		return MD5(serialize($key));
	}

	public function isProductsListStillRight()
	{
		$key = $this->getSecureKey();
		if ($this->secure_key != $key)
			return false;
		return true;
	}

	public function setExpressCheckoutType($type)
	{
		if (in_array($type, $this->available_type))
		{
			$this->type = $type;
			return true;
		}
		return false;
	}

	public function redirectToAPI()
	{
		$this->secure_key = $this->getSecureKey();
		$this->_storeCookieInfo();
		header('Location: https://'.$this->getPayPalURL().
			'/websc&cmd=_express-checkout&token='.urldecode($this->token));
		exit(0);
	}

	public function redirectToCheckout($customer, $redirect = false)
	{
		$link = new Link();

		$this->ready = true;
		$this->_storeCookieInfo();

		$this->context->cookie->id_customer = (int)$customer->id;
		$this->context->cookie->customer_lastname = $customer->lastname;
		$this->context->cookie->customer_firstname = $customer->firstname;
		$this->context->cookie->passwd = $customer->passwd;
		$this->context->cookie->logged = 1;
		$this->context->cookie->email = $customer->email;
		$this->context->cookie->is_guest = $customer->isGuest();
		$this->context->cookie->id_cart = (int)Cart::lastNoneOrderedCart((int)$customer->id);

		if (_PS_VERSION_ < '1.5')
			Module::hookExec('authentication');
		else
			Hook::exec('authentication');

		if ($redirect)
		{
			header('Location: '.$link->getPageLink('order.php?step=1'));
			exit(0);
		}
	}
}
