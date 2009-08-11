<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$cookie = new Cookie('ps');
if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=order-follow.php');

if (isset($_GET['id_order_return']) AND Validate::isUnsignedId($_GET['id_order_return']))
	$orderReturn = new OrderReturn(intval($_GET['id_order_return']));
if (!isset($orderReturn) OR !Validate::isLoadedObject($orderReturn))
    die(Tools::displayError('order return not found'));
elseif ($orderReturn->id_customer != $cookie->id_customer)
    die(Tools::displayError('order return not found'));
elseif ($orderReturn->state < 2)
    die(Tools::displayError('order return not confirmed'));
else
	PDF::orderReturn($orderReturn);

?>