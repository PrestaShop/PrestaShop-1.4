<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cheque.php');

$cheque = new Cheque();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$cheque->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

$currency = new Currency((int)(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval($cart->getOrderTotal(true, 3));

$mailVars =	array(
	'{cheque_name}' => Configuration::get('CHEQUE_NAME'),
	'{cheque_address}' => Configuration::get('CHEQUE_ADDRESS'),
	'{cheque_address_html}' => str_replace("\n", '<br />', Configuration::get('CHEQUE_ADDRESS')));

$cheque->validateOrder((int)($cart->id), _PS_OS_CHEQUE_, $total, $cheque->displayName, NULL, $mailVars, (int)($currency->id));

$order = new Order($cheque->currentOrder);
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)($cart->id).'&id_module='.(int)($cheque->id).'&id_order='.$cheque->currentOrder.'&key='.$order->secure_key);

?>