<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/bankwire.php');

$bankwire = new BankWire();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$bankwire->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval($cart->getOrderTotal(true, 3));
$mailVars = array(
	'{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
	'{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
	'{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
);

$bankwire->validateOrder($cart->id, _PS_OS_BANKWIRE_, $total, $bankwire->displayName, NULL, $mailVars, $currency->id);
$order = new Order($bankwire->currentOrder);
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$bankwire->id.'&id_order='.$bankwire->currentOrder.'&key='.$order->secure_key);
?>