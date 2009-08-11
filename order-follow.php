<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=history.php');

/* JS files calls */
$js_files = array(__PS_BASE_URI__.'js/jquery/jquery.scrollto.js', _THEME_JS_DIR_.'history.js');

if (Tools::isSubmit('submitReturnMerchandise'))
{
	if (!$id_order = intval(Tools::getValue('id_order')))
		Tools::redirect('history.php');
	if (!$order_qte_input = Tools::getValue('order_qte_input'))
		Tools::redirect('order-follow.php?errorDetail1');
	if ($customizationIds = Tools::getValue('customization_ids') AND !$customizationQtyInput = Tools::getValue('customization_qty_input'))
		Tools::redirect('order-follow.php?errorDetail1');
	if (!$ids_order_detail = Tools::getValue('ids_order_detail') AND !$customizationIds)
		Tools::redirect('order-follow.php?errorDetail2');

	$orderReturn = new OrderReturn();
	$orderReturn->id_customer = intval($cookie->id_customer);
	$orderReturn->id_order = $id_order;
	$orderReturn->question = strval(Tools::getValue('returnText'));
	if (empty($orderReturn->question))
		Tools::redirect('order-follow.php?errorMsg');
	if (!$orderReturn->checkEnoughProduct($ids_order_detail, $order_qte_input, $customizationIds, $customizationQtyInput))
		Tools::redirect('order-follow.php?errorQuantity');

	$orderReturn->state = 1;
	$orderReturn->add();
	$orderReturn->addReturnDetail($ids_order_detail, $order_qte_input, $customizationIds, $customizationQtyInput);
	Module::hookExec('orderReturn', array('orderReturn' => $orderReturn));
	Tools::redirect('order-follow.php');
}

$ordersReturn = OrderReturn::getOrdersReturn(intval($cookie->id_customer));
if (Tools::isSubmit('errorQuantity'))
	$smarty->assign('errorQuantity', true);
elseif (Tools::isSubmit('errorMsg'))
	$smarty->assign('errorMsg', true);
elseif (Tools::isSubmit('errorDetail1'))
	$smarty->assign('errorDetail1', true);
elseif (Tools::isSubmit('errorDetail2'))
	$smarty->assign('errorDetail2', true);

$smarty->assign('ordersReturn', $ordersReturn);
include(dirname(__FILE__).'/header.php');
$smarty->display(_PS_THEME_DIR_.'order-follow.tpl');
include(dirname(__FILE__).'/footer.php');

?>
