<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');
$errors = array();

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=history.php');


if (!isset($_GET['id_order_return']) OR !Validate::isUnsignedId($_GET['id_order_return']))
	$errors[] = Tools::displayError('order ID is required');
else
{
	$orderRet = new OrderReturn(intval($_GET['id_order_return']));
	if (Validate::isLoadedObject($orderRet) AND $orderRet->id_customer == $cookie->id_customer)
	{
		$order = new Order(intval($orderRet->id_order));
		if (Validate::isLoadedObject($order))
		{
			$state = new OrderReturnState(intval($orderRet->state));
			$smarty->assign(array(
				'orderRet' => $orderRet,
				'order' => $order,
				'state_name' => $state->name[intval($cookie->id_lang)],
				'return_allowed' => false,
				'products' => OrderReturn::getOrdersReturnProducts(intval($orderRet->id), $order),
				'returnedCustomizations' => OrderReturn::getReturnedCustomizedProducts(intval($orderRet->id_order)),
				'customizedDatas' => Product::getAllCustomizedDatas(intval($order->id_cart))
			));
		}
		else
			$errors[] = Tools::displayError('cannot find this order return');
	}
	else
		$errors[] = Tools::displayError('cannot find this order return');
}

$smarty->assign(array(
	'errors' => $errors,
	'nbdaysreturn' => intval(Configuration::get('PS_ORDER_RETURN_NB_DAYS'))
));

if (Tools::getValue('ajax') == 'true')
	$smarty->display(_PS_THEME_DIR_.'order-return.tpl');
else
{
	include(dirname(__FILE__).'/header.php');
	$smarty->display(_PS_THEME_DIR_.'order-return.tpl');
	include(dirname(__FILE__).'/footer.php');
}

?>