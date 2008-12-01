<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cheque.php');

$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
$mailVars =	array(
	'{cheque_name}' => Configuration::get('CHEQUE_NAME'),
	'{cheque_address}' => Configuration::get('CHEQUE_ADDRESS'),
	'{cheque_address_html}' => nl2br(Configuration::get('CHEQUE_ADDRESS')));

$cheque = new Cheque();
$cheque->validateOrder($cart->id, _PS_OS_CHEQUE_, $total, $cheque->displayName, NULL, $mailVars, $currency->id);
$order = new Order($cheque->currentOrder);
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$cheque->id.'&id_order='.$cheque->currentOrder.'&key='.$order->secure_key);
?>