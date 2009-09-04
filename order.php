<?php

/* SSL Management */
$useSSL = true;

include_once(dirname(__FILE__).'/config/config.inc.php');
/* Step number is needed on some modules */
$step = intval(Tools::getValue('step'));
include_once(dirname(__FILE__).'/init.php');

/* Disable some cache related bugs on the cart/order */
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

$errors = array();

/* Class FreeOrder to use PaymentModule (abstract class, cannot be instancied) */
class	FreeOrder extends PaymentModule {}

/* If some products have disappear */
if (!$cart->checkQuantities())
{
	$step = 0;
	$errors[] = Tools::displayError('An item in your cart is no longer available, you cannot proceed with your order');
}

/* Check minimal account */
$orderTotal = $cart->getOrderTotal();

$orderTotalDefaultCurrency = Tools::convertPrice($cart->getOrderTotal(true, 1), Currency::getCurrency(intval(Configuration::get('PS_CURRENCY_DEFAULT'))));
$minimalPurchase = floatval(Configuration::get('PS_PURCHASE_MINIMUM'));
if ($orderTotalDefaultCurrency < $minimalPurchase)
{
	$step = 0;
	$errors[] = Tools::displayError('A minimum purchase total of').' '.Tools::displayPrice($minimalPurchase, Currency::getCurrency(intval($cart->id_currency))).
	' '.Tools::displayError('is required in order to validate your order');
}

if (!$cookie->isLogged() AND in_array($step, array(1, 2, 3)))
	Tools::redirect('authentication.php?back=order.php?step='.$step);

if ($cart->nbProducts())
{
	/* Manage discounts */
	if ((Tools::isSubmit('submitDiscount') OR isset($_GET['submitDiscount'])) AND Tools::getValue('discount_name'))
	{
		$discountName = Tools::getValue('discount_name');
		if (!Validate::isDiscountName($discountName))
			$errors[] = Tools::displayError('voucher name not valid');
		else
		{
			$discount = new Discount(intval(Discount::getIdByName($discountName)));
			if (is_object($discount) AND $discount->id)
			{
				if ($tmpError = $cart->checkDiscountValidity($discount, $cart->getDiscounts(), $cart->getOrderTotal(), $cart->getProducts(), true))
					$errors[] = $tmpError;
			}
			else
				$errors[] = Tools::displayError('voucher name not valid');
			if (!sizeof($errors))
			{
				$cart->addDiscount(intval($discount->id));
				Tools::redirect('order.php');
			}
			else
			{
				$smarty->assign(array(
					'errors' => $errors,
					'discount_name' => Tools::safeOutput($discountName)));
			}
		}
	}
	elseif (isset($_GET['deleteDiscount']) AND Validate::isUnsignedId($_GET['deleteDiscount']))
	{
		$cart->deleteDiscount(intval($_GET['deleteDiscount']));
		Tools::redirect('order.php');
	}

	/* Is there only virtual product in cart */
	if ($isVirtualCart = $cart->isVirtualCart())
		setNoCarrier();
	$smarty->assign('virtual_cart', $isVirtualCart);

	/* 4 steps to the order */
	switch (intval($step))
	{
		case 1:
			displayAddress();
			break;
		case 2:
			if(Tools::isSubmit('processAddress'))
				processAddress();
			autoStep(2);
			displayCarrier();
			break;
		case 3:
			if(Tools::isSubmit('processCarrier'))
				processCarrier();
			autoStep(3);
			checkFreeOrder();
			displayPayment();
			break;
		default:
			$smarty->assign('errors', $errors);
			displaySummary();
			break;
	}
}
else
{
	/* Default page */
	$smarty->assign('empty', 1);
	Tools::safePostVars();
	include_once(dirname(__FILE__).'/header.php');
	$smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
}

include(dirname(__FILE__).'/footer.php');

/* Order process controller */
function autoStep($step)
{
	global $cart, $isVirtualCart;

	if ($step >= 2 AND (!$cart->id_address_delivery OR !$cart->id_address_invoice))
		Tools::redirect('order.php?step=1');
	$delivery = new Address(intval($cart->id_address_delivery));
	$invoice = new Address(intval($cart->id_address_invoice));
	if ($delivery->deleted OR $invoice->deleted)
	{
		if ($delivery->deleted)
			unset($cart->id_address_delivery);
		if ($invoice->deleted)
			unset($cart->id_address_invoice);
		Tools::redirect('order.php?step=1');
	}
	elseif ($step >= 3 AND !$cart->id_carrier AND !$isVirtualCart)
		Tools::redirect('order.php?step=2');
}

/* Bypass payment step if total is 0 */
function checkFreeOrder()
{
	global $cart;

	if ($cart->getOrderTotal() <= 0)
	{
		$order = new FreeOrder();
		$order->validateOrder(intval($cart->id), _PS_OS_PAYMENT_, 0, Tools::displayError('Free order', false));
		Tools::redirect('history.php');
	}
}

/**
 * Set id_carrier to 0 (no shipping price)
 *
 */
function setNoCarrier()
{
	global $cart;
	$cart->id_carrier = 0;
	$cart->update();
}

/*
 * Manage address
 */
function processAddress()
{
	global $cart, $smarty;
	$errors = array();

	if (!isset($_POST['id_address_delivery']) OR !Address::isCountryActiveById(intval($_POST['id_address_delivery'])))
		$errors[] = 'this address is not in a valid area';
	else
	{
		$cart->id_address_delivery = intval($_POST['id_address_delivery']);
		$cart->id_address_invoice = isset($_POST['same']) ? intval($_POST['id_address_delivery']) : intval($_POST['id_address_invoice']);
		if (!$cart->update())
			$errors[] = Tools::displayError('an error occured while updating your cart');
		
		if (isset($_POST['message']) AND !empty($_POST['message']))
		{
			if (!Validate::isMessage($_POST['message']))
				$errors[] = Tools::displayError('invalid message');
			elseif ($oldMessage = Message::getMessageByCartId(intval($cart->id)))
			{
				$message = new Message(intval($oldMessage['id_message']));
				$message->message = htmlentities($_POST['message'], ENT_COMPAT, 'UTF-8');
				$message->update();
			}
			else
			{
				$message = new Message();
				$message->message = htmlentities($_POST['message'], ENT_COMPAT, 'UTF-8');
				$message->id_cart = intval($cart->id);
				$message->id_customer = intval($cart->id_customer);
				$message->add();
			}
		}
	}
	if (sizeof($errors))
	{
		if (Tools::getValue('ajax'))
			die('{\'hasError\' : true, errors : [\''.implode('\',\'', $errors).'\']}');
		$smarty->assign('errors', $errors);
		displayAddress();
		include_once(dirname(__FILE__).'/footer.php');
		exit;
	}
	if (Tools::getValue('ajax'))
		die(true);
}

/* Carrier step */
function processCarrier()
{
	global $cart, $smarty, $isVirtualCart, $orderTotal;

	$errors = array();

	$cart->recyclable = (isset($_POST['recyclable']) AND !empty($_POST['recyclable'])) ? 1 : 0;

	if (isset($_POST['gift']) AND !empty($_POST['gift']))
	{
	 	if (!Validate::isMessage($_POST['gift_message']))
			$errors[] = Tools::displayError('invalid gift message');
		else
		{
			$cart->gift = 1;
			$cart->gift_message = strip_tags($_POST['gift_message']);
		}
	}
	else
		$cart->gift = 0;

	$address = new Address(intval($cart->id_address_delivery));
	if (!Validate::isLoadedObject($address))
		die(Tools::displayError());
	if (!$id_zone = Address::getZoneById($address->id))
		$errors[] = Tools::displayError('no zone match with your address');
	if (isset($_POST['id_carrier']) AND Validate::isInt($_POST['id_carrier']) AND sizeof(Carrier::checkCarrierZone(intval($_POST['id_carrier']), intval($id_zone))))
		$cart->id_carrier = intval($_POST['id_carrier']);
	elseif (!$isVirtualCart)
		$errors[] = Tools::displayError('invalid carrier or no carrier selected');

	$cart->update();

	if (sizeof($errors))
	{
		$smarty->assign('errors', $errors);
		displayCarrier();
		include(dirname(__FILE__).'/footer.php');
		exit;
	}
	$orderTotal = $cart->getOrderTotal();
}

/* Address step */
function displayAddress()
{
	global $smarty, $cookie, $cart;

	if (!Customer::getAddressesTotalById(intval($cookie->id_customer)))
		Tools::redirect('address.php?back=order.php?step=1');
	$customer = new Customer(intval($cookie->id_customer));
	if (Validate::isLoadedObject($customer))
	{
		/* Getting customer addresses */
		$customerAddresses = $customer->getAddresses(intval($cookie->id_lang));
		$smarty->assign('addresses', $customerAddresses);

		/* Setting default addresses for cart */
		if ((!isset($cart->id_address_delivery) OR empty($cart->id_address_delivery)) AND sizeof($customerAddresses))
		{
			$cart->id_address_delivery = intval($customerAddresses[0]['id_address']);
			$update = 1;
		}
		if ((!isset($cart->id_address_invoice) OR empty($cart->id_address_invoice)) AND sizeof($customerAddresses))
		{
			$cart->id_address_invoice = intval($customerAddresses[0]['id_address']);
			$update = 1;
		}
		/* Update cart addresses only if needed */
		if (isset($update) AND $update)
			$cart->update();

		/* If delivery address is valid in cart, assign it to Smarty */
		if (isset($cart->id_address_delivery))
		{
			$deliveryAddress = new Address(intval($cart->id_address_delivery));
			if (Validate::isLoadedObject($deliveryAddress) AND ($deliveryAddress->id_customer == $customer->id))
				$smarty->assign('delivery', $deliveryAddress);
		}

		/* If invoice address is valid in cart, assign it to Smarty */
		if (isset($cart->id_address_invoice))
		{
			$invoiceAddress = new Address(intval($cart->id_address_invoice));
			if (Validate::isLoadedObject($invoiceAddress) AND ($invoiceAddress->id_customer == $customer->id))
				$smarty->assign('invoice', $invoiceAddress);
		}
	}
	if ($oldMessage = Message::getMessageByCartId(intval($cart->id)))
		$smarty->assign('oldMessage', $oldMessage['message']);
	$smarty->assign('cart', $cart);
	$smarty->assign('back', strval(Tools::getValue('back')));

	Tools::safePostVars();
	include_once(dirname(__FILE__).'/header.php');
	$smarty->display(_PS_THEME_DIR_.'order-address.tpl');
}

/* Carrier step */
function displayCarrier()
{
	global $smarty, $cart, $cookie, $defaultCountry;

	$address = new Address(intval($cart->id_address_delivery));
	$id_zone = Address::getZoneById($address->id);
	$result = Carrier::getCarriers(intval($cookie->id_lang), true, false, intval($id_zone));
	$resultsArray = array();
	foreach ($result AS $k => $row)
	{
		$carrier = new Carrier(intval($row['id_carrier']));
		if ((Configuration::get('PS_SHIPPING_METHOD') AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
		OR (!Configuration::get('PS_SHIPPING_METHOD') AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
		{
			unset($result[$k]);
			continue ;
		}
		if ($row['range_behavior'])
		{
			// Get id zone
	        if (isset($cart->id_address_delivery) AND $cart->id_address_delivery)
				$id_zone = Address::getZoneById(intval($cart->id_address_delivery));
			else
				$id_zone = intval($defaultCountry->id_zone);
			if ((Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone)))
			OR (!Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, 4), $id_zone))))
				{
					unset($result[$k]);
					continue ;
				}
		}
		$row['name'] = (strval($row['name']) != '0' ? $row['name'] : Configuration::get('PS_SHOP_NAME'));
		$row['price'] = $cart->getOrderShippingCost(intval($row['id_carrier']));
		$row['price_tax_exc'] = $cart->getOrderShippingCost(intval($row['id_carrier']), false);
		$row['img'] = file_exists(_PS_SHIP_IMG_DIR_.intval($row['id_carrier']).'.jpg') ? _THEME_SHIP_DIR_.intval($row['id_carrier']).'.jpg' : '';
		$resultsArray[] = $row;
	}

	// Wrapping fees
	$wrapping_fees = floatval(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
	$wrapping_fees_tax = new Tax(intval(Configuration::get('PS_GIFT_WRAPPING_TAX')));
	$wrapping_fees_tax_exc = $wrapping_fees / (1 + ((floatval($wrapping_fees_tax->rate) / 100)));

	if (Validate::isUnsignedInt($cart->id_carrier))
	{
		$carrier = new Carrier(intval($cart->id_carrier));
		if ($carrier->active AND !$carrier->deleted)
			$checked = intval($cart->id_carrier);
	}
	if (!isset($checked))
		$checked = intval(Configuration::get('PS_CARRIER_DEFAULT'));
	$smarty->assign(array(
		'checkedTOS' => intval($cookie->checkedTOS),
		'recyclablePackAllowed' => intval(Configuration::get('PS_RECYCLABLE_PACK')),
		'giftAllowed' => intval(Configuration::get('PS_GIFT_WRAPPING')),
		'conditions' => intval(Configuration::get('PS_CONDITIONS')),
		'recyclable' => intval($cart->recyclable),
		'gift_wrapping_price' => floatval(Configuration::get('PS_GIFT_WRAPPING_PRICE')),
		'carriers' => $resultsArray,
		'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $address)),
		'checked' => intval($checked),
		'back' => strval(Tools::getValue('back')),
		'total_wrapping' => number_format($wrapping_fees, 2, '.', ''),
		'total_wrapping_tax_exc' => number_format($wrapping_fees_tax_exc, 2, '.', '')));
	Tools::safePostVars();
	$css_files = array(__PS_BASE_URI__.'css/thickbox.css' => 'all');
	$js_files = array(__PS_BASE_URI__.'js/jquery/thickbox-modified.js');
	include_once(dirname(__FILE__).'/header.php');
	$smarty->display(_PS_THEME_DIR_.'order-carrier.tpl');
}

/* Payment step */
function displayPayment()
{
	global $smarty, $cart, $currency, $cookie, $orderTotal;

	// Redirect instead of displaying payment modules if any module are grefted on
	Hook::backBeforePayment(strval(Tools::getValue('back')));

	/* We may need to display an order summary */
	$smarty->assign($cart->getSummaryDetails());

	$cookie->checkedTOS = '1';
	$smarty->assign(array('HOOK_PAYMENT' => Module::hookExecPayment(), 'total_price' => floatval($orderTotal)));

	Tools::safePostVars();
	include_once(dirname(__FILE__).'/header.php');
	$smarty->display(_PS_THEME_DIR_.'order-payment.tpl');
}

/* Confirmation step */
function displaySummary()
{
	global $smarty, $cart;
	
	if (file_exists(_PS_SHIP_IMG_DIR_.intval($cart->id_carrier).'.jpg'))
		$smarty->assign('carrierPicture', 1);
	$summary = $cart->getSummaryDetails();
	$customizedDatas = Product::getAllCustomizedDatas(intval($cart->id));
	Product::addCustomizationPrice($summary['products'], $customizedDatas);
	
	if ($free_ship = floatval(Configuration::get('PS_SHIPPING_FREE_PRICE')))
	{
		$discounts = $cart->getDiscounts();
		$total_free_ship =  $free_ship - ($summary['total_products_wt'] + $summary['total_discounts']);
		foreach ($discounts as $discount)
			if ($discount['id_discount_type'] == 3)
			{
				$total_free_ship = 0;
				break ;
			}
		$smarty->assign('free_ship', $total_free_ship);
	}
	$smarty->assign($summary);
	$token = Tools::getToken(false);
	$smarty->assign(array(
		'token_cart' => $token,
		'productNumber' => $cart->nbProducts(),
		'voucherAllowed' => Configuration::get('PS_VOUCHERS'),
		'HOOK_SHOPPING_CART' => Module::hookExec('shoppingCart', $summary),
		'HOOK_SHOPPING_CART_EXTRA' => Module::hookExec('shoppingCartExtra', $summary),
		'shippingCost' => $cart->getOrderTotal(true, 5),
		'shippingCostTaxExc' => $cart->getOrderTotal(false, 5),
		'customizedDatas' => $customizedDatas,
		'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
		'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
		'lastProductAdded' => $cart->getLastProduct()
		));
	Tools::safePostVars();
	include_once(dirname(__FILE__).'/header.php');
	$smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
}

?>
