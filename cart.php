<?php

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');
$errors = array();

$orderTotal = $cart->getOrderTotal(true, 1);

$cartDiscounts = $cart->getDiscounts();
foreach ($cartDiscounts AS $k => $cartDiscount)
	if ($error = $cart->checkDiscountValidity(new Discount(intval($cartDiscount['id_discount'])), $cartDiscounts, $orderTotal, $cart->getProducts()))
		$cart->deleteDiscount(intval($cartDiscount['id_discount']));

$add = Tools::getIsset('add') ? 1 : 0;
$delete = Tools::getIsset('delete') ? 1 : 0;

if (Configuration::get('PS_TOKEN_ENABLE') == 1 &&
	strcasecmp(Tools::getToken(false), strval(Tools::getValue('token'))) &&
	$cookie->isLogged() === true)
	$errors[] = Tools::displayError('invalid token');

//update the cart...
if ($add OR Tools::getIsset('update') OR $delete)
{
	//get the values
 	$idProduct = intval(Tools::getValue('id_product', NULL));
	$idProductAttribute = intval(Tools::getValue('id_product_attribute', Tools::getValue('ipa')));
	$customizationId = intval(Tools::getValue('id_customization', 0));
	$qty = intval(abs(Tools::getValue('qty', 1)));

	if ($qty == 0)
		$errors[] = Tools::displayError('null quantity');
	elseif (!$idProduct)
		$errors[] = Tools::displayError('product not found');
	else
	{
		$producToAdd = new Product(intval($idProduct), false, intval($cookie->id_lang));
		if ((!$producToAdd->id OR !$producToAdd->active) AND !$delete)
			$errors[] = Tools::displayError('product is no longer available');
		else
		{
			/* Check the quantity availability */
			if ($idProductAttribute AND is_numeric($idProductAttribute))
			{
				if (!$delete AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) AND !Attribute::checkAttributeQty(intval($idProductAttribute), intval($qty)))
					$errors[] = Tools::displayError('product is no longer available');
			}
			elseif ($producToAdd->hasAttributes() AND !$delete)
			{
				$idProductAttribute = Product::getDefaultAttribute(intval($producToAdd->id), intval($producToAdd->out_of_stock) == 2 ? !intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')) : !intval($producToAdd->out_of_stock));
				if (!$idProductAttribute)
					Tools::redirectAdmin($link->getProductLink($producToAdd));
				elseif (!$delete AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) AND !Attribute::checkAttributeQty(intval($idProductAttribute), intval($qty)))
					$errors[] = Tools::displayError('product is no longer available');
			}
			elseif (!$delete AND !$producToAdd->checkQty(intval($qty)))
				$errors[] = Tools::displayError('product is no longer available');
			/* Check vouchers compatibility */
			if ($add AND (intval($producToAdd->reduction_price) OR intval($producToAdd->reduction_percent) OR $producToAdd->on_sale))
			{
				$discounts = $cart->getDiscounts();
				foreach($discounts as $discount)
					if (!$discount['cumulable_reduction'])
						$errors[] = Tools::displayError('cannot add this product because current voucher doesn\'t allow additional discounts');
			}

			if (!sizeof($errors))
			{
				if ($add AND $qty >= 0)
				{
					/* Product addition to the cart */
					if (!isset($cart->id) OR !$cart->id)
					{
						$cart->id_address_delivery = intval($cookie->id_address_delivery);
						$cart->id_address_invoice = intval($cookie->id_address_invoice);
					    $cart->add();
					    if ($cart->id)
							$cookie->id_cart = intval($cart->id);
					}
					if ($add AND !$cart->containsProduct(intval($idProduct), intval($idProductAttribute), $customizationId) AND !$producToAdd->hasAllRequiredCustomizableFields())
						$errors[] = Tools::displayError('Please fill all required fields, then save the customization.');
					if (!sizeof($errors) AND !$cart->updateQty(intval($qty), intval($idProduct), intval($idProductAttribute), $customizationId, Tools::getValue('op', 'up')))
						$errors[] = Tools::displayError('you already have the maximum quantity available for this product')
							.((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php') ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- '.
							Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
				}
				elseif ($delete)
				{
					if (Cart::getNbProducts($cart->id) == 1)
					{
						$discounts = $cart->getDiscounts();
						foreach($discounts AS $discount)
						{
							$discountObj = new Discount(intval($discount['id_discount']), intval($cookie->id_lang));
							if ($tmpError = $cart->checkDiscountValidity($discountObj, $discounts, $cart->getOrderTotal(true, 1), $cart->getProducts()))
								$errors[] = $tmpError;
							else
								$cart->deleteDiscount(intval($discount['id_discount']));
						}
					}
					$cart->deleteProduct(intval($idProduct), intval($idProductAttribute), $customizationId);
					if (!Cart::getNbProducts(intval($cart->id)))
					{
						$cart->id_carrier = 0;
						$cart->gift = 0;
						$cart->gift_message = '';
						$cart->update();
					}
				}
			}
			if (!sizeof($errors))
			{
			 	$queryString = Tools::safeOutput(Tools::getValue('query', NULL));
				if ($queryString AND !Configuration::get('PS_CART_REDIRECT'))
					Tools::redirect('search.php?search='.$queryString);
				if (isset($_SERVER['HTTP_REFERER']))
				{
					// Redirect to previous page
					preg_match('!http(s?)://(.*)/(.*)!', $_SERVER['HTTP_REFERER'], $regs);
					if (isset($regs[3]) AND !Configuration::get('PS_CART_REDIRECT') AND Tools::getValue('ajax') != 'true')
						Tools::redirect($regs[3]);
				}
			}
		}
	}
}

//if cart.php is called by ajax
if (Tools::getValue('ajax') == 'true')
	require_once(_PS_MODULE_DIR_.'/blockcart/blockcart-ajax.php');
else
{
	if (sizeof($errors))
	{
		require_once(dirname(__FILE__).'/header.php');
		$smarty->assign('errors', $errors);
		$smarty->display(_PS_THEME_DIR_.'errors.tpl');
		require_once(dirname(__FILE__).'/footer.php');
	}
	else
		Tools::redirect('order.php?'.(isset($idProduct) ? 'ipa='.intval($idProduct) : ''));
}
?>
