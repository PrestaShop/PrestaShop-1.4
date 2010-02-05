<?php

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'paypalapi/paypalapi.php');
$pp = new PaypalApi();

if (!$transaction_id = strval(Tools::getValue('txn_id')))
	die('No transaction id');
if (!$id_order = $pp->getOrder($transaction_id))
	die('No order');

$order = new Order(intval($id_order));
if (!Validate::isLoadedObject($order) OR !$order->id)
	die('Invalid order');
if (!$amount = floatval(Tools::getValue('mc_gross')) OR $amount != $order->total_paid)
	die('Incorrect amount');

if (!$status = strval(Tools::getValue('payment_status')))
	die('Incorrect order status');

// Getting params
$params = 'cmd=_notify-validate';
foreach ($_POST AS $key => $value)
	$params .= '&'.$key.'='.urlencode(stripslashes($value));

// Checking params by asking PayPal
include(_PS_MODULE_DIR_.'paypalapi/api/paypallib.php');
$ppAPI = new PaypalLib();
$result = $ppAPI->makeSimpleCall($ppAPI->getPayPalURL(), $ppAPI->getPayPalScript(), $params);
if (!$result OR (Tools::strlen($result) < 8) OR (!$statut = substr($result, -8)) OR $statut != 'VERIFIED')
	die('Incorrect PayPal verified');

// Getting order status
switch ($status)
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

if ($order->getCurrentState() == $id_order_state)
	die('Same status');

// Set order state in order history
$history = new OrderHistory();
$history->id_order = intval($order->id);
$history->changeIdOrderState(intval($id_order_state), intval($order->id));
$history->addWithemail(true, $extraVars);
