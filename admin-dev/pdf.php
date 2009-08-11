<?php

/**
  * PDF generation for admin, pdf.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');

/* Header can't be included, so cookie must be created here */
$cookie = new Cookie('psAdmin');
if (!$cookie->id_employee)
	Tools::redirect('login.php');

if (isset($_GET['pdf']))
{
	if (!isset($_GET['id_order']))
		die (Tools::displayError('order ID is missing'));
	$order = new Order(intval($_GET['id_order']));
	if (!Validate::isLoadedObject($order))
		die(Tools::displayError('cannot find order in database'));
	PDF::invoice($order);
}
elseif (isset($_GET['id_order_slip']))
{
	$orderSlip = new OrderSlip(intval($_GET['id_order_slip']));
	$order = new Order(intval($orderSlip->id_order));
	if (!Validate::isLoadedObject($order))
		die(Tools::displayError('cannot find order in database'));
	$order->products = OrderSlip::getOrdersSlipProducts($orderSlip->id, $order);
	$tmp = NULL;
	PDF::invoice($order, 'D', false, $tmp, $orderSlip);
}
elseif (isset($_GET['id_delivery']))
{
	$order = Order::getByDelivery(intval($_GET['id_delivery']));
	if (!Validate::isLoadedObject($order))
		die(Tools::displayError('cannot find order in database'));
	$tmp = NULL;
	PDF::invoice($order, 'D', false, $tmp, false, $order->delivery_number);
}
elseif (isset($_GET['invoices']))
{
	$invoices = unserialize(urldecode($_GET['invoices']));
	if (is_array($invoices))
		PDF::multipleInvoices($invoices);
}
elseif (isset($_GET['deliveryslips']))
{
	$slips = unserialize(urldecode($_GET['deliveryslips']));
	if (is_array($slips))
		PDF::multipleDelivery($slips);
}

?>