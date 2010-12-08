<?php
/*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class PaypalPayment extends Paypal
{
	protected $_logs = array();

	public function getAuthorisation()
	{
		global $cookie, $cart;

		// Getting cart informations
		$currency = new Currency((int)($cart->id_currency));
		if (!Validate::isLoadedObject($currency))
			$this->_logs[] = $this->l('Not a valid currency');
		if (sizeof($this->_logs))
			return false;

		// Making request
		$vars = '?fromPayPal=1';
		$returnURL = Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/paypal/payment/submit.php'.$vars;
		$cancelURL = Tools::getHttpHost(true, true).__PS_BASE_URI__.'order.php';
		$paymentAmount = (float)($cart->getOrderTotal());
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
