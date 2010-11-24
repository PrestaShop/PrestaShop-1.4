<?php

class PaypalExpress extends Paypal
{
	protected $_logs = array();

	public function getAuthorisation()
	{
		global $cookie;

		// Getting cart informations
		$cart = new Cart((int)($cookie->id_cart));
		if (!Validate::isLoadedObject($cart))
			$this->_logs[] = $this->l('Not a valid cart');
		$currency = new Currency((int)($cart->id_currency));
		if (!Validate::isLoadedObject($currency))
			$this->_logs[] = $this->l('Not a valid currency');

		if (sizeof($this->_logs))
			return false;

		// Making request
		$returnURL = Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/paypal/express/submit.php';
		$cancelURL = Tools::getHttpHost(true, true).__PS_BASE_URI__.'order.php';
		$paymentAmount = floatval($cart->getOrderTotal());
		$currencyCodeType = strval($currency->iso_code);
		$paymentType = Configuration::get('PAYPAL_CAPTURE') == 1 ? 'Authorization' : 'Sale';
		$request = '&Amt='.urlencode($paymentAmount).'&PAYMENTACTION='.urlencode($paymentType).'&ReturnUrl='.urlencode($returnURL).'&CANCELURL='.urlencode($cancelURL).'&CURRENCYCODE='.urlencode($currencyCodeType);
		if ($this->_pp_integral)
			$request .= '&SOLUTIONTYPE=Sole&LANDINGPAGE=Billing';
		else
			$request .= '&SOLUTIONTYPE=Mark&LANDINGPAGE=Login';
		$request .= '&LOCALECODE='.strval($this->getCountryCode());
		if ($this->_header) $request .= '&HDRIMG='.urlencode($this->_header);

		// Calling PayPal API
		include(_PS_MODULE_DIR_.'paypal/api/paypallib.php');
		$ppAPI = new PaypalLib();
		$result = $ppAPI->makeCall($this->getAPIURL(), $this->getAPIScript(), 'SetExpressCheckout', $request);
		$this->_logs = array_merge($this->_logs, $ppAPI->getLogs());
		return $result;
	}

	public function getCustomerInfos()
	{
		global $cookie;

		// Making request
		$request = '&TOKEN='.urlencode(strval($cookie->paypal_token));

		// Calling PayPal API
		include(_PS_MODULE_DIR_.'paypal/api/paypallib.php');
		$ppAPI = new PaypalLib();
		$result = $ppAPI->makeCall($this->getAPIURL(), $this->getAPIScript(), 'GetExpressCheckoutDetails', $request);
		$this->_logs = array_merge($this->_logs, $ppAPI->getLogs());
		return $result;
	}

	public function getLogs()
	{
		return $this->_logs;
	}
}
