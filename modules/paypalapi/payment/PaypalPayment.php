<?php

class PaypalPayment extends PaypalAPI
{
	protected $_logs = array();

	public function getAuthorisation()
	{
		global $cookie, $cart;

		// Getting cart informations
		$currency = new Currency(intval($cookie->id_currency));
		if (!Validate::isLoadedObject($currency))
			$this->_logs[] = $this->l('Not a valid currency');
		if (sizeof($this->_logs))
			return false;

		// Making request
		$vars = '?fromPayPal=1';
		$returnURL = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/paypalapi/payment/submit.php'.$vars;
		$cancelURL = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php';
		$paymentAmount = number_format(floatval($cart->getOrderTotal()), 2, '.', '');
		$currencyCodeType = strval($currency->iso_code);
		$paymentType = 'Sale';
		$request = '&Amt='.urlencode($paymentAmount).'&PAYMENTACTION='.urlencode($paymentType).'&ReturnUrl='.urlencode($returnURL).'&CANCELURL='.urlencode($cancelURL).'&CURRENCYCODE='.urlencode($currencyCodeType).'&NOSHIPPING=1';
		if ($this->_header)
			$request .= '&HDRIMG='.urlencode($this->_header);

		// Calling PayPal API
		include(_PS_MODULE_DIR_.'paypalapi/api/PaypalLib.php');
		$ppAPI = new PaypalLib();
		$result = $ppAPI->makeCall($this->getAPIURL(), $this->getAPIScript(), 'SetExpressCheckout', $request);
		$this->_logs = array_merge($this->_logs, $ppAPI->getLogs());
		return $result;
	}

	public function home($params)
	{
		return $this->display(__FILE__.'../', 'payment.tpl');
	}

	public function getLogs()
	{
		return $this->_logs;
	}
}