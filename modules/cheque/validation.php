<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cheque.php');

$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval($cart->getOrderTotal(true, 3));

$mailVars =	array(
	'{cheque_name}' => Configuration::get('CHEQUE_NAME'),
	'{cheque_address}' => Configuration::get('CHEQUE_ADDRESS'),
	'{cheque_address_html}' => str_replace("\n", '<br />', Configuration::get('CHEQUE_ADDRESS')));

$cheque = new Cheque();
$cheque->validateOrder(intval($cart->id), _PS_OS_CHEQUE_, $total, $cheque->displayName, NULL, $mailVars, intval($currency->id));

$order = new Order($cheque->currentOrder);
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.intval($cart->id).'&id_module='.intval($cheque->id).'&id_order='.$cheque->currentOrder.'&key='.$order->secure_key);

?>