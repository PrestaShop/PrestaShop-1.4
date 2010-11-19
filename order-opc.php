<?php

/* SSL Management */
$useSSL = true;

require_once(dirname(__FILE__).'/config/config.inc.php');
/* Step number is needed on some modules */
require_once(dirname(__FILE__).'/init.php');

if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 0)
	Tools::redirect('order.php');

/* Class FreeOrder to use PaymentModule (abstract class, cannot be instancied) */
class	FreeOrder extends PaymentModule {}

/* Disable some cache related bugs on the cart/order */
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

$errors = array();
$isLogged = (bool)(intval($cookie->id_customer) AND Customer::customerIdExistsStatic(intval($cookie->id_customer)));

if ($cart->nbProducts() AND $isLogged)
{
	if (Tools::isSubmit('ajax'))
	{
		if (Tools::isSubmit('method'))
		{
			switch (Tools::getValue('method'))
			{
				case 'updateMessage':
					if (Tools::isSubmit('message'))
					{
						$txtMessage = urldecode(Tools::getValue('message'));
						$oldMessage = Message::getMessageByCartId(intval($cart->id));
						if ($txtMessage)
						{
							if (!Validate::isMessage($txtMessage))
				    			$errors[] = Tools::displayError('invalid message');
				    		elseif ($oldMessage)
				    		{
				    			$message = new Message(intval($oldMessage['id_message']));
				    			$message->message = htmlentities($txtMessage, ENT_COMPAT, 'UTF-8');
				    			$message->update();
				    		}
				    		else
				    		{
				    			$message = new Message();
				    			$message->message = htmlentities($txtMessage, ENT_COMPAT, 'UTF-8');
				    			$message->id_cart = intval($cart->id);
				    			$message->id_customer = intval($cart->id_customer);
				    			$message->add();
				    		}
				    	}
				    	else
				    	{
				    		if ($oldMessage)
				    		{
				    			$message = new Message(intval($oldMessage['id_message']));
				    			$message->delete();
				    		}
				    	}
				    	if (sizeof($errors))
							die('{\'hasError\' : true, errors : [\''.implode('\',\'', $errors).'\']}');
						die(true);
					}
					break;
				case 'updateCarrier':
					if (Tools::isSubmit('id_carrier') AND Tools::isSubmit('recyclable') AND Tools::isSubmit('gift') AND Tools::isSubmit('gift_message'))
					{
						$cart->recyclable = intval(Tools::getValue('recyclable'));
						$cart->gift = intval(Tools::getValue('gift'));
						if (intval(Tools::getValue('gift')))
						{
							if (!Validate::isMessage($_POST['gift_message']))
								$errors[] = Tools::displayError('invalid gift message');
							else
								$cart->gift_message = strip_tags($_POST['gift_message']);
						}
						
						$address = new Address(intval($cart->id_address_delivery)); // dynamise for id country
						if (!($id_zone = Country::getIdZone(intval($address->id_country))))
							$errors[] = Tools::displayError('no zone match with your address');
						if (Validate::isInt(Tools::getValue('id_carrier')) AND sizeof(Carrier::checkCarrierZone(intval(Tools::getValue('id_carrier')), intval($id_zone))))
							$cart->id_carrier = intval(Tools::getValue('id_carrier'));
						elseif (!$cart->isVirtualCart() AND intval(Tools::getValue('id_carrier')) != 0)
							$errors[] = Tools::displayError('invalid carrier or no carrier selected');
						if (sizeof($errors))
							die('{\'hasError\' : true, errors : [\''.implode('\',\'', $errors).'\']}');
						
						Module::hookExec('ProcessCarrier', array('cart' => $cart));
						if ($cart->update())
						{
							$summary = $cart->getSummaryDetails();
							die(Tools::jsonEncode($summary));
						}
						else
							$errors[] = Tools::displayError('error occured on update of cart');
						if (sizeof($errors))
							die('{\'hasError\' : true, errors : [\''.implode('\',\'', $errors).'\']}');
						exit;
					}
					break;
				case 'updateTOSStatus':
					if (Tools::isSubmit('checked'))
					{
						$cookie->checkedTOS = intval(Tools::getValue('checked'));
						die(true);
					}
					break;
				case 'getCarrierList':
					$address_delivery = new Address($cart->id_address_delivery);
					if ($cookie->id_customer)
					{
						$customer = new Customer(intval($cookie->id_customer));
						$groups = $customer->getGroups();
					}
					else
						$groups = array(1);
					if (!Address::isCountryActiveById(intval($cart->id_address_delivery)))
						$errors[] = Tools::displayError('this address is not in a valid area');
					elseif (!Validate::isLoadedObject($address_delivery) OR $address_delivery->deleted)
						$errors[] = Tools::displayError('this address is not valid');
					else
					{
						$cart->id_carrier = 0;
						$cart->update();
						$result = array('carriers' => Carrier::getCarriersOpc(intval($address_delivery->id_country), $groups));
						die (Tools::jsonEncode($result));
					}
					if (sizeof($errors))
						die('{\'hasError\' : true, errors : [\''.implode('\',\'', $errors).'\']}');
					break;
				case 'getPaymentModule':
					if ($cart->OrderExists())
						die('<p class="warning">'.Tools::displayError('Error: this order is already validated').'</p>');
					if (!$cart->id_customer OR !Customer::customerIdExistsStatic($cart->id_customer) OR Customer::isBanned($cart->id_customer))
						die('<p class="warning">'.Tools::displayError('Error: no customer').'</p>');
					$address_delivery = new Address($cart->id_address_delivery);
					$address_invoice = ($cart->id_address_delivery == $cart->id_address_invoice ? $address_delivery : new Address($cart->id_address_invoice));
					if (!$cart->id_address_delivery OR !$cart->id_address_invoice OR !Validate::isLoadedObject($address_delivery) OR !Validate::isLoadedObject($address_invoice) OR $address_invoice->deleted OR $address_delivery->deleted)
						die('<p class="warning">'.Tools::displayError('Error: please choose an address').'</p>');
					if (!$cart->id_carrier AND !$cart->isVirtualCart())
						die('<p class="warning">'.Tools::displayError('Error: please choose a carrier').'</p>');
					elseif ($cart->id_carrier != 0)
					{
						$carrier = new Carrier(intval($cart->id_carrier));
						if (!Validate::isLoadedObject($carrier) OR $carrier->deleted OR !$carrier->active)
							die('<p class="warning">'.Tools::displayError('Error: the carrier is invalid').'</p>');
					}
					if (!$cart->id_currency)
						die('<p class="warning">'.Tools::displayError('Error: no currency has been selected').'</p>');
					if (!$cookie->checkedTOS AND Configuration::get('PS_CONDITIONS'))
						die('<p class="warning">'.Tools::displayError('Error: please accept Terms of Service').'</p>');
					
					/* If some products have disappear */
					if (!$cart->checkQuantities())
						die('<p class="warning">'.Tools::displayError('An item in your cart is no longer available, you cannot proceed with your order').'</p>');
					
					/* Check minimal account */
					$orderTotalDefaultCurrency = Tools::convertPrice($cart->getOrderTotal(true, 1), Currency::getCurrency(intval(Configuration::get('PS_CURRENCY_DEFAULT'))));
					$minimalPurchase = floatval(Configuration::get('PS_PURCHASE_MINIMUM'));
					if ($orderTotalDefaultCurrency < $minimalPurchase)
						die('<p class="warning">'.Tools::displayError('A minimum purchase total of').' '.Tools::displayPrice($minimalPurchase, Currency::getCurrency(intval($cart->id_currency))).
						' '.Tools::displayError('is required in order to validate your order').'</p>');
					
					if ($cart->getOrderTotal() <= 0)
					{
						$order = new FreeOrder();
						$order->validateOrder(intval($cart->id), _PS_OS_PAYMENT_, 0, Tools::displayError('Free order', false));
						die('freeorder');
					}
					
					die(Module::hookExec('payment'));
					break;
				default:
					exit;
			}
		}
		elseif (Tools::isSubmit('processAddress') AND Tools::getValue('id_address_delivery') AND Tools::getValue('id_address_invoice'))
		{
			$id_address_delivery = intval(Tools::getValue('id_address_delivery'));
			$id_address_invoice = intval(Tools::getValue('id_address_invoice'));
			$address_delivery = new Address(intval(Tools::getValue('id_address_delivery')));
			$address_invoice = (intval(Tools::getValue('id_address_delivery')) == intval(Tools::getValue('id_address_invoice')) ? $address_delivery : new Address(intval(Tools::getValue('id_address_invoice'))));
			
			if (!Address::isCountryActiveById(intval(Tools::getValue('id_address_delivery'))))
				$errors[] = Tools::displayError('this address is not in a valid area');
			elseif (!Validate::isLoadedObject($address_delivery) OR !Validate::isLoadedObject($address_invoice) OR $address_invoice->deleted OR $address_delivery->deleted)
				$errors[] = Tools::displayError('this address is not valid');
			else
			{
				$cart->id_carrier = 0;
				$cart->id_address_delivery = intval(Tools::getValue('id_address_delivery'));
				$cart->id_address_invoice = Tools::isSubmit('same') ? $cart->id_address_delivery : intval(Tools::getValue('id_address_invoice'));
				if (!$cart->update())
					$errors[] = Tools::displayError('an error occured while updating your cart');
				if (!sizeof($errors))
				{
					if ($cookie->id_customer)
					{
						$customer = new Customer(intval($cookie->id_customer));
						$groups = $customer->getGroups();
					}
					else
						$groups = array(1);
					$address = new Address(intval($cart->id_address_delivery));
					$result = array(
						'carriers' => Carrier::getCarriersOpc(intval($address_delivery->id_country), $groups),
						'summary' => $cart->getSummaryDetails()
					);
					die(Tools::jsonEncode($result));
				}
			}
			if (sizeof($errors))
				die('{\'hasError\' : true, errors : [\''.implode('\',\'', $errors).'\']}');
			exit;
		}
		exit;
	}
	elseif (Tools::isSubmit('submitAddDiscount') AND Tools::getValue('discount_name'))
	{
		$discountName = Tools::getValue('discount_name');
		if (!Validate::isDiscountName($discountName))
			$errors[] = Tools::displayError('voucher name not valid');
		else
		{
			$discount = new Discount(intval(Discount::getIdByName($discountName)));
			if (Validate::isLoadedObject($discount))
			{
				if ($tmpError = $cart->checkDiscountValidity($discount, $cart->getDiscounts(), $cart->getOrderTotal(), $cart->getProducts(), true))
					$errors[] = $tmpError;
			}
			else
				$errors[] = Tools::displayError('voucher name not valid');
			if (!sizeof($errors))
			{
				$cart->addDiscount(intval($discount->id));
				Tools::redirect('order-opc.php');
			}
		}
		$smarty->assign(array(
			'errors' => $errors,
			'discount_name' => Tools::safeOutput($discountName)
		));
	}
	elseif (isset($_GET['deleteDiscount']) AND Validate::isUnsignedId($_GET['deleteDiscount']))
	{
		$cart->deleteDiscount(intval($_GET['deleteDiscount']));
		Tools::redirect('order-opc.php');
	}
	
}
elseif (Tools::isSubmit('ajax'))
	exit;

// SHOPPING CART
$summary = $cart->getSummaryDetails();
$customizedDatas = Product::getAllCustomizedDatas(intval($cart->id));
Product::addCustomizationPrice($summary['products'], $customizedDatas);
$currency = new Currency(intval($cookie->id_currency));

if ($free_ship = Tools::convertPrice(floatval(Configuration::get('PS_SHIPPING_FREE_PRICE')), new Currency(intval($cart->id_currency))))
{
    $discounts = $cart->getDiscounts();
    $total_free_ship =  $free_ship - ($summary['total_products_wt'] + $summary['total_discounts']);
    foreach ($discounts as $discount)
    	if ($discount['id_discount_type'] == 3)
    	{
    		$total_free_ship = 0;
    		break;
    	}
    $smarty->assign('free_ship', $total_free_ship);
}


// for compatibility with 1.2 themes
foreach($summary['products'] AS $key => $product)
    $summary['products'][$key]['quantity'] = $product['cart_quantity'];
$smarty->assign($summary);
$smarty->assign(array(
    'token_cart' => Tools::getToken(false),
    'isVirtualCart' => $cart->isVirtualCart(),
    'productNumber' => $cart->nbProducts(),
    'voucherAllowed' => Configuration::get('PS_VOUCHERS'),
    'HOOK_SHOPPING_CART' => Module::hookExec('shoppingCart', $summary),
    'HOOK_SHOPPING_CART_EXTRA' => Module::hookExec('shoppingCartExtra', $summary),
    'shippingCost' => $cart->getOrderTotal(true, 5),
    'shippingCostTaxExc' => $cart->getOrderTotal(false, 5),
    'customizedDatas' => $customizedDatas,
    'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
    'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
	'isLogged' => $isLogged,
	'currencySign' => $currency->sign,
	'currencyRate' => $currency->conversion_rate,
	'currencyFormat' => $currency->format,
	'currencyBlank' => $currency->blank,
	'displayVouchers' => Discount::getVouchersToCartDisplay(intval($cookie->id_lang), (isset($cookie->id_customer) ? intval($cookie->id_customer) : 0))
));

if (intval($cookie->id_customer) AND Customer::customerIdExistsStatic(intval($cookie->id_customer)))
{
	// ADDRESS
	if (!Customer::getAddressesTotalById(intval($cookie->id_customer)))
		Tools::redirect('address.php?back=order.php?step=1');
	$customer = new Customer(intval($cookie->id_customer));
	/* Getting customer addresses */
	$customerAddresses = $customer->getAddresses(intval($cookie->id_lang));
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
	$smarty->assign('addresses', $customerAddresses);
	if ($oldMessage = Message::getMessageByCartId(intval($cart->id)))
		$smarty->assign('oldMessage', $oldMessage['message']);
	
	// CARRIER
	
	$carriers = Carrier::getCarriersOpc(intval($deliveryAddress->id_country), $customer->getGroups());
	
	// Wrapping fees
	$wrapping_fees = floatval(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
	$wrapping_fees_tax = new Tax(intval(Configuration::get('PS_GIFT_WRAPPING_TAX')));
	$wrapping_fees_tax_inc = $wrapping_fees * (1 + ((floatval($wrapping_fees_tax->rate) / 100)));
	
	$checked = 0;
	if (Validate::isUnsignedInt($cart->id_carrier) AND $cart->id_carrier)
	{
		$carrier = new Carrier(intval($cart->id_carrier));
		if ($carrier->active AND !$carrier->deleted)
			$checked = intval($cart->id_carrier);
	}
	$cms = new CMS(intval(Configuration::get('PS_CONDITIONS_CMS_ID')), intval($cookie->id_lang));
	$link_conditions = $link->getCMSLink($cms, $cms->link_rewrite, true);
	if (!strpos($link_conditions, '?'))
		$link_conditions .= '?content_only=1&TB_iframe=true&width=450&height=500&thickbox=true';
	else
		$link_conditions .= '&content_only=1&TB_iframe=true&width=450&height=500&thickbox=true';
	$smarty->assign(array(
		'checkedTOS' => intval($cookie->checkedTOS),
		'recyclablePackAllowed' => intval(Configuration::get('PS_RECYCLABLE_PACK')),
		'giftAllowed' => intval(Configuration::get('PS_GIFT_WRAPPING')),
		'cms_id' => intval(Configuration::get('PS_CONDITIONS_CMS_ID')),
		'conditions' => intval(Configuration::get('PS_CONDITIONS')),
		'link_conditions' => $link_conditions,
		'recyclable' => intval($cart->recyclable),
		'gift_wrapping_price' => floatval(Configuration::get('PS_GIFT_WRAPPING_PRICE')),
		'carriers' => $carriers,
		'default_carrier' => intval(Configuration::get('PS_CARRIER_DEFAULT')),
		'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $deliveryAddress)),
		'HOOK_BEFORECARRIER' => Module::hookExec('beforeCarrier', array('carriers' => $carriers)),
		'checked' => intval($checked),
		'total_wrapping' => Tools::convertPrice($wrapping_fees_tax_inc, new Currency(intval($cookie->id_currency))),
		'total_wrapping_tax_exc' => Tools::convertPrice($wrapping_fees, new Currency(intval($cookie->id_currency)))
	));
}
Tools::safePostVars();

// Adding CSS style sheet
Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
Tools::addCSS(_THEME_CSS_DIR_.'order-opc.css');
Tools::addCSS(_PS_CSS_DIR_.'thickbox.css', 'all');
// Adding JS files
Tools::addJS(_THEME_JS_DIR_.'tools.js');
Tools::addJS(_THEME_JS_DIR_.'order-address.js');
Tools::addJS(_THEME_JS_DIR_.'order-opc.js');
if (intval(Configuration::get('PS_BLOCK_CART_AJAX')))
	Tools::addJS(_THEME_JS_DIR_.'cart-summary.js');
Tools::addJS(_PS_JS_DIR_.'jquery/thickbox-modified.js');
Tools::addJS(_PS_JS_DIR_.'jquery/jquery-typewatch.pack.js');
include(dirname(__FILE__).'/header.php');
$smarty->display(_PS_THEME_DIR_.'errors.tpl');
$smarty->display(_PS_THEME_DIR_.'order-opc.tpl');
include(dirname(__FILE__).'/footer.php');

?>