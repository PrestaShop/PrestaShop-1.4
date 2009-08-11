<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

$id_cart = intval(Tools::getValue('id_cart', 0));
$id_module = intval(Tools::getValue('id_module', 0));
$id_order = Order::getOrderByCartId(intval($id_cart));
$secure_key = isset($_GET['key']) ? $_GET['key'] : false;
if (!$id_order OR !$id_module OR !$secure_key OR empty($secure_key))
	Tools::redirect('history.php');
$order = new Order(intval($id_order));
if (!Validate::isLoadedObject($order) OR $order->id_customer != $cookie->id_customer OR $secure_key != $order->secure_key)
	Tools::redirect('history.php');
$module = Module::getInstanceById(intval($id_module));
if ($order->payment != $module->displayName)
	Tools::redirect('history.php');
$smarty->assign(array(
	'HOOK_ORDER_CONFIRMATION' => Hook::orderConfirmation(intval($id_order)),
	'HOOK_PAYMENT_RETURN' => Hook::paymentReturn(intval($id_order), intval($id_module))));

$smarty->display(_PS_THEME_DIR_.'order-confirmation.tpl');

include(dirname(__FILE__).'/footer.php');
