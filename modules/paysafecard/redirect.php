<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/paysafecard.php');

$module = new PaysafeCard();

if (!$cart->id OR $cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$module->active)
	Tools::redirect('order.php?step=3');
	
$currency = new Currency($cart->id_currency);
if (!$module->isCurrencyActive($currency->iso_code))
	Tools::redirect('order.php?step=3');

$result = $module->createDisposition($cart);

if ($result['return_code'] != 0)
{
	include(dirname(__FILE__).'/../../header.php');
	echo $module->getL('cant_create_dispo');
	include(dirname(__FILE__).'/../../footer.php');
}
else
	Tools::redirectLink($result['message']);

?>