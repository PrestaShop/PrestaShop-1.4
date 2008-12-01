<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cashondelivery.php');

$confirm = Tools::getValue('confirm');

/* Validate order */
if ($confirm)
{
 	$cashOnDelivery = new CashOnDelivery();
	$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
	$cashOnDelivery->validateOrder(intval($cart->id), _PS_OS_PREPARATION_, $total, $cashOnDelivery->displayName);
	$order = new Order(intval($cashOnDelivery->currentOrder));
	Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.intval($cart->id).'&id_module='.intval($cashOnDelivery->id).'&id_order='.intval($cashOnDelivery->currentOrder));
}
else
{
	/* or ask for confirmation */ 
	$smarty->assign(array(
		'currency_default' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
		'total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
		'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/cashondelivery/'
	));

    $smarty->assign('this_path', __PS_BASE_URI__.'modules/cashondelivery/');
    echo Module::display(__FILE__, 'validation.tpl');
}

include(dirname(__FILE__).'/../../footer.php');
?>