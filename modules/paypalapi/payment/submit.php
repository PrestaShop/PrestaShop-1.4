<?php

$useSSL = true;

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');

include_once(_PS_MODULE_DIR_.'paypalapi/paypalapi.php');
include_once(_PS_MODULE_DIR_.'paypalapi/payment/PaypalPayment.php');

$ppPayment = new PaypalPayment();
$errors = array();

// #####
// Functions

function getAuthorization()
{
	global $ppPayment, $cookie, $cart;

	$result = $ppPayment->getAuthorisation();
	$logs = $ppPayment->getLogs();
	if (is_array($result) AND sizeof($result))
	{
		if (strtoupper($result['ACK']) == 'SUCCESS')
		{
			if (isset($result['TOKEN']))
			{

				$cookie->paypal_token = strval($result['TOKEN']);
				header('Location: https://'.$ppPayment->getPayPalURL().'/webscr&cmd=_express-checkout&token='.urldecode(strval($cookie->paypal_token)).'&useraction=commit');
			}
			else
				$logs[] = '<b>'.$ppPayment->l('No token given by PayPal', 'submit').'</b>';
		} else
			$logs[] = '<b>'.$ppPayment->l('PayPal returned error', 'submit').'</b>';
	}
	$ppPayment->displayError($ppPayment->l('Authorisation to PayPal failed', 'submit'), $logs);
}

function displayConfirm()
{
	global $cookie, $smarty, $ppPayment, $cart;

	if (!$cookie->isLogged())
		die('Not logged');
	unset($cookie->paypal_token);

	// Display all and exit
	include(_PS_ROOT_DIR_.'/header.php');

	$smarty->assign(array(
		'cust_currency' => $cookie->id_currency,
		'currency' => $ppPayment->getCurrency(),
		'total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
		'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'. $ppPayment->name.'/',
		'mode' => 'payment/'
	));

	echo $ppPayment->display(_PS_MODULE_DIR_.'paypalapi/payment/PaypalPayment.php', '../confirm.tpl');
	include(_PS_ROOT_DIR_.'/footer.php');
	die ;
}

function submitConfirm()
{
	global $cookie, $smarty, $ppPayment, $cart;

	if (!$cookie->isLogged())
		die('Not logged');
	elseif (!$id_currency = intval(Tools::getValue('currency_payement')))
		die('No currency');
	elseif (!$cart->getOrderTotal(true, 3))
		die('Empty cart');
	$currency = new Currency(intval($id_currency));
	if (!Validate::isLoadedObject($currency))
		die('Invalid currency');
	$cookie->id_currency = intval($id_currency);
	getAuthorization();
}

function validOrder()
{
	global $cookie, $cart, $ppPayment;

	if (!$cookie->isLogged())
		die('Not logged');
	elseif (!$cart->getOrderTotal(true, 3))
		die('Empty cart');
	if (!$token = Tools::htmlentitiesUTF8(strval(Tools::getValue('token'))))
		die('Invalid token');
	if ($token != strval($cookie->paypal_token))
		die('Invalid cookie token');
	if (!$payerID = Tools::htmlentitiesUTF8(strval(Tools::getValue('PayerID'))))
		die('Invalid payerID');
	$ppPayment->validOrder($cookie, $cart, $cookie->id_currency, $payerID, 'payment');
}

// #####
// Process !!

// No submit, confirmation page
if (!Tools::isSubmit('submitPayment') AND !Tools::getValue('fromPayPal'))
	displayConfirm();
else
{
	if (!isset($cookie->paypal_token) OR !$cookie->paypal_token)
		submitConfirm();
	validOrder();
}