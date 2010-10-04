<?php

class PaypalPayment extends Paypal
{
	protected $_logs = array();

	public function getAuthorisation()
	{
		global $cookie, $cart;

		// Getting cart informations
		$currency = new Currency(intval($cart->id_currency));
		if (!Validate::isLoadedObject($currency))
			$this->_logs[] = $this->l('Not a valid currency');
		if (sizeof($this->_logs))
			return false;

		// Making request
		$vars = '?fromPayPal=1';
		$returnURL = Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/paypal/payment/submit.php'.$vars;
		$cancelURL = Tools::getHttpHost(true, true).__PS_BASE_URI__.'order.php';
		$paymentAmount = floatval($cart->getOrderTotal());
		$currencyCodeType = strval($currency->iso_code);
		$paymentType = Configuration::get('PAYPAL_CAPTURE') == 1 ? 'Authorization' : 'Sale';
		$request = '&Amt='.urlencode($paymentAmount).'&PAYMENTACTION='.urlencode($paymentType).'&ReturnUrl='.urlencode($returnURL).'&CANCELURL='.urlencode($cancelURL).'&CURRENCYCODE='.urlencode($currencyCodeType).'&NOSHIPPING=1';
		if (Configuration::get('PAYPAL_PAYMENT_METHOD') == 0)
			$request .= '&SOLUTIONTYPE=Sole&LANDINGPAGE=Billing';
		else
			$request .= '&SOLUTIONTYPE=Mark&LANDINGPAGE=Login';
		$request .= '&LOCALECODE='.Language::getIsoById($cart->id_lang);
		if (Configuration::get('PAYPAL_HEADER'))
			$request .= '&HDRIMG='.urlencode(Configuration::get('PAYPAL_HEADER'));

		// Calling PayPal API
		include(_PS_MODULE_DIR_.'paypal/api/paypallib.php');
		$ppAPI = new PaypalLib();
		$result = $ppAPI->makeCall($this->getAPIURL(), $this->getAPIScript(), 'SetExpressCheckout', $request);
		$this->_logs = array_merge($this->_logs, $ppAPI->getLogs());
		return $result;
	}

	public function getLogs()
	{
		return $this->_logs;
	}
}
