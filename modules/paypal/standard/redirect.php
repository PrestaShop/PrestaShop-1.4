<?php

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../paypal.php');

$paypal = new Paypal();
$cart = new Cart((int)($cookie->id_cart));

$address = new Address((int)($cart->id_address_invoice));
$country = new Country((int)($address->id_country));
$state = NULL;
if ($address->id_state)
	$state = new State((int)($address->id_state));
$customer = new Customer((int)($cart->id_customer));
$business = Configuration::get('PAYPAL_BUSINESS');
$header = Configuration::get('PAYPAL_HEADER');
$currency_order = new Currency((int)($cart->id_currency));
$currency_module = $paypal->getCurrency();

if (!Validate::isEmail($business))
	die($paypal->getL('Paypal error: (invalid or undefined business account email)'));

if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency_module))
	die($paypal->getL('Paypal error: (invalid address or customer)'));

// check currency of payment
if ($currency_order->id != $currency_module->id)
{
	$cookie->id_currency = $currency_module->id;
	$cart->id_currency = $currency_module->id;
	$cart->update();
}

$smarty->assign(array(
	'redirect_text' => $paypal->getL('Please wait, redirecting to Paypal... Thanks.'),
	'cancel_text' => $paypal->getL('Cancel'),
	'cart_text' => $paypal->getL('My cart'),
	'return_text' => $paypal->getL('Return to shop'),
	'paypal_url' => $paypal->getPaypalStandardUrl(),
	'address' => $address,
	'country' => $country,
	'state' => $state,
	'amount' => (float)($cart->getOrderTotal(true, 4)),
	'customer' => $customer,
	'total' => (float)($cart->getOrderTotal(true, 3)),
	'shipping' => Tools::ps_round((float)($cart->getOrderShippingCost()) + (float)($cart->getOrderTotal(true, 6)), 2),
	'discount' => $cart->getOrderTotal(true, 2),
	'business' => $business,
	'currency_module' => $currency_module,
	'cart_id' => (int)($cart->id).'_'.pSQL($cart->secure_key),
	'products' => $cart->getProducts(),
	'paypal_id' => (int)($paypal->id),
	'header' => $header,
	'url' => Tools::getHttpHost(false, true).__PS_BASE_URI__
));


if (is_file(_PS_THEME_DIR_.'modules/paypal/standard/redirect.tpl'))
	$smarty->display(_PS_THEME_DIR_.'modules/'.$paypal->name.'/standard/redirect.tpl');
else
	$smarty->display(_PS_MODULE_DIR_.$paypal->name.'/standard/redirect.tpl');


