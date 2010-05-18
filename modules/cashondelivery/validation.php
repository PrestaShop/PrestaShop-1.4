<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cashondelivery.php');

$cashOnDelivery = new CashOnDelivery();
if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$cashOnDelivery->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

/* Validate order */
if (Tools::getValue('confirm'))
{
	$customer = new Customer(intval($cart->id_customer));
	$total = $cart->getOrderTotal(true, 3);
	$cashOnDelivery->validateOrder(intval($cart->id), _PS_OS_PREPARATION_, $total, $cashOnDelivery->displayName);
	$order = new Order(intval($cashOnDelivery->currentOrder));
	Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.intval($cart->id).'&id_module='.intval($cashOnDelivery->id).'&id_order='.intval($cashOnDelivery->currentOrder));
}
else
{
	/* or ask for confirmation */ 
	$smarty->assign(array(
		'total' => $cart->getOrderTotal(true, 3),
		'this_path_ssl' => Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/cashondelivery/'
	));

	$smarty->assign('this_path', __PS_BASE_URI__.'modules/cashondelivery/');
	$template = 'validation.tpl';
	if (file_exists(_PS_THEME_DIR_.'modules/cashondelivery/'.$template))
		echo Module::display('cashondelivery', $template);
	else
		echo Module::display(__FILE__, $template);
}

include(dirname(__FILE__).'/../../footer.php');