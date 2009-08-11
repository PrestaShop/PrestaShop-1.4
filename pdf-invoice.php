<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$cookie = new Cookie('ps');
if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=pdf-invoice.php');
if (!intval(Configuration::get('PS_INVOICE')))
	die(Tools::displayError('invoices are disabled on this shop'));
if (isset($_GET['id_order']) AND Validate::isUnsignedId($_GET['id_order']))
	$order = new Order(intval($_GET['id_order']));
if (!isset($order) OR !Validate::isLoadedObject($order))
    die(Tools::displayError('invoice not found'));
elseif ($order->id_customer != $cookie->id_customer)
    die(Tools::displayError('invoice not found'));
elseif (!OrderState::invoiceAvailable($order->getCurrentState()) AND !$order->invoice_number)
	die(Tools::displayError('current order state doesn\'t allow to edit this invoice'));
else
		PDF::invoice($order);

?>