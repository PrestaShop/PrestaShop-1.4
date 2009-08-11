<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$cookie = new Cookie('ps');
if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=order-follow.php');

if (isset($_GET['id_order_slip']) AND Validate::isUnsignedId($_GET['id_order_slip']))
	$orderSlip = new OrderSlip(intval($_GET['id_order_slip']));
if (!isset($orderSlip) OR !Validate::isLoadedObject($orderSlip))
    die(Tools::displayError('order return not found'));
elseif ($orderSlip->id_customer != $cookie->id_customer)
    die(Tools::displayError('order return not found'));
$order = new Order(intval($orderSlip->id_order));
if (!Validate::isLoadedObject($order))
    die(Tools::displayError('order not found'));
$order->products = OrderSlip::getOrdersSlipProducts(intval($orderSlip->id), $order);
$ref = NULL;
PDF::invoice($order, 'D', false, $ref, $orderSlip);

?>