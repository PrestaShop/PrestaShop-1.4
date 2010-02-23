<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/paypal.php');

$paypal = new Paypal();
$cart = new Cart(intval($cookie->id_cart));

$address = new Address(intval($cart->id_address_invoice));
$country = new Country(intval($address->id_country));
$customer = new Customer(intval($cart->id_customer));
$business = Configuration::get('PAYPAL_BUSINESS');
$header = Configuration::get('PAYPAL_HEADER');
$currency_order = new Currency($cart->id_currency);
$currency_module = $paypal->getCurrency();

if (!Validate::isEmail($business))
	die($paypal->l('Paypal error: (invalid or undefined business account email)'));

if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency_module))
	die($paypal->l('Paypal error: (invalid address or customer)'));

// check currency of payment
if ($currency_order->id != $currency_module->id)
{
	$cookie->id_currency = $currency_module->id;
	$cart->id_currency = $currency_module->id;
	$cart->update();
}

$amount = $cart->getOrderTotal(true, 4);
$total = $cart->getOrderTotal(true, 3);

echo '
<html><head><script type="text/javascript" src="http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__.'/js/jquery/jquery-1.2.6.pack.js"></script></head><body>
<form action="'.$paypal->getPaypalUrl().'" method="post" id="paypal_form" class="hidden">
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="first_name" value="'.$address->firstname.'" />
	<input type="hidden" name="last_name" value="'.$address->lastname.'" />
	<input type="hidden" name="address1" value="'.$address->address1.'" />
	'.($address->address2 ? '<input type="hidden" name="address2" value="'.$address->address2.'" />' : '').'
	<input type="hidden" name="city" value="'.$address->city.'" />
	<input type="hidden" name="zip" value="'.$address->postcode.'" />
	<input type="hidden" name="country" value="'.$country->iso_code.'" />
	<input type="hidden" name="amount" value="'.$amount.'" />
	<input type="hidden" name="email" value="'.$customer->email.'" />
	<input type="hidden" name="item_name_1" value="'.$paypal->l('My cart').'" />
	<input type="hidden" name="amount_1" value="'.$total.'" />
	<input type="hidden" name="quantity_1" value="1" />
	<input type="hidden" name="business" value="'.$business.'" />
	<input type="hidden" name="receiver_email" value="'.$business.'" />
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="charset" value="utf-8" />
	<input type="hidden" name="currency_code" value="'.$currency_module->iso_code.'" />
	<input type="hidden" name="payer_id" value="'.$customer->id.'" />
	<input type="hidden" name="payer_email" value="'.$customer->email.'" />
	<input type="hidden" name="custom" value="'.$cart->id.'" />
	<input type="hidden" name="return" value="'.Tools::getHttpHost(true, true).__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.intval($cart->id).'&id_module='.intval($paypal->id).'" />
	<input type="hidden" name="cancel_return" value="http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__.'index.php" />
	<input type="hidden" name="notify_url" value="http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__.'modules/paypal/validation.php" />
	'.($header ? '<input type="hidden" name="cpp_header_image" value="'.$header.'" />' : '').'
    <input type="hidden" name="rm" value="2" />
	<input type="hidden" name="bn" value="PRESTASHOP_WPS" />
	<input type="hidden" name="cbt" value="'.$paypal->l('Return to shop').'" />
</form>
<script type="text/javascript">$(\'#paypal_form\').submit();</script>
</body></html>
';

?>
