<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class CartControllerCore extends FrontController
{
	public function run()
	{
		$this->preProcess();

		if (Tools::getValue('ajax') == 'true')
		{
			if (Tools::getIsset('summary'))
			{
				if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
				{
					if ($this->cookie->id_customer)
					{
						$customer = new Customer((int)($this->cookie->id_customer));
						$groups = $customer->getGroups();
					}
					else
						$groups = array(1);
					$deliveryAddress = new Address((int)($this->cart->id_address_delivery));
					$result = array('carriers' => Carrier::getCarriersForOrder((int)Address::getZoneById((int)($deliveryAddress->id)), $groups));
				}
				$result['summary'] = $this->cart->getSummaryDetails();
				$result['customizedDatas'] = Product::getAllCustomizedDatas((int)($this->cart->id));
				$result['HOOK_SHOPPING_CART'] = Module::hookExec('shoppingCart', $result['summary']);
				$result['HOOK_SHOPPING_CART_EXTRA'] = Module::hookExec('shoppingCartExtra', $result['summary']);
				die(Tools::jsonEncode($result));
			}
			else
				$this->includeCartModule();
		}
		else
		{
			$this->setMedia();
			$this->displayHeader();
			$this->process();
			$this->displayContent();
			$this->displayFooter();
		}
	}

	public function includeCartModule()
	{
		require_once(_PS_MODULE_DIR_.'/blockcart/blockcart-ajax.php'); 
	}

	public function preProcess()
	{
		parent::preProcess();

		$orderTotal = $this->cart->getOrderTotal(true, 1);

		$this->cartDiscounts = $this->cart->getDiscounts();
		foreach ($this->cartDiscounts AS $k => $this->cartDiscount)
			if ($error = $this->cart->checkDiscountValidity(new Discount((int)($this->cartDiscount['id_discount'])), $this->cartDiscounts, $orderTotal, $this->cart->getProducts()))
				$this->cart->deleteDiscount((int)($this->cartDiscount['id_discount']));

		$add = Tools::getIsset('add') ? 1 : 0;
		$delete = Tools::getIsset('delete') ? 1 : 0;

		if (Configuration::get('PS_TOKEN_ENABLE') == 1 &&
			strcasecmp(Tools::getToken(false), strval(Tools::getValue('token'))) &&
			$this->cookie->isLogged() === true)
			$this->errors[] = Tools::displayError('invalid token');

		// Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
		if (($add OR Tools::getIsset('update') OR $delete) AND isset($this->cookie->date_add))
		{
			//get the values
			$idProduct = (int)(Tools::getValue('id_product', NULL));
			$idProductAttribute = (int)(Tools::getValue('id_product_attribute', Tools::getValue('ipa')));
			$customizationId = (int)(Tools::getValue('id_customization', 0));
			$qty = (int)(abs(Tools::getValue('qty', 1)));
			if ($qty == 0)
				$this->errors[] = Tools::displayError('null quantity');
			elseif (!$idProduct)
				$this->errors[] = Tools::displayError('product not found');
			else
			{
				$producToAdd = new Product((int)($idProduct), true, (int)($this->cookie->id_lang));
				if ((!$producToAdd->id OR !$producToAdd->active) AND !$delete)
					if (Tools::getValue('ajax') == 'true')
						die('{"hasError" : true, "errors" : ["'.Tools::displayError('product is no longer available', false).'"]}');
					else
						$this->errors[] = Tools::displayError('product is no longer available', false);
				else
				{
					/* Check the quantity availability */
					if ($idProductAttribute AND is_numeric($idProductAttribute))
					{
						if (!$delete AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) AND !Attribute::checkAttributeQty((int)($idProductAttribute), (int)($qty)))
							if (Tools::getValue('ajax') == 'true')
								die('{"hasError" : true, "errors" : ["'.Tools::displayError('there is not enough product in stock', false).'"]}');
							else
								$this->errors[] = Tools::displayError('there is not enough product in stock');
					}
					elseif ($producToAdd->hasAttributes() AND !$delete)
					{
						$idProductAttribute = Product::getDefaultAttribute((int)($producToAdd->id), (int)($producToAdd->out_of_stock) == 2 ? !(int)(Configuration::get('PS_ORDER_OUT_OF_STOCK')) : !(int)($producToAdd->out_of_stock));
						if (!$idProductAttribute)
							Tools::redirectAdmin($link->getProductLink($producToAdd));
						elseif (!$delete AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) AND !Attribute::checkAttributeQty((int)($idProductAttribute), (int)($qty)))
							if (Tools::getValue('ajax') == 'true')
								die('{"hasError" : true, "errors" : ["'.Tools::displayError('there is not enough product in stock', false).'"]}');
							else
								$this->errors[] = Tools::displayError('there is not enough product in stock');
					}
					elseif (!$delete AND !$producToAdd->checkQty((int)($qty)))
						if (Tools::getValue('ajax') == 'true')
								die('{"hasError" : true, "errors" : ["'.Tools::displayError('there is not enough product in stock').'"]}');
							else
								$this->errors[] = Tools::displayError('there is not enough product in stock');
					/* Check vouchers compatibility */
					if ($add AND (($producToAdd->specificPrice AND (float)($producToAdd->specificPrice['reduction'])) OR $producToAdd->on_sale))
					{
						$discounts = $this->cart->getDiscounts();
						foreach($discounts as $discount)
							if (!$discount['cumulable_reduction'])
								$this->errors[] = Tools::displayError('cannot add this product because current voucher doesn\'t allow additional discounts');
					}
					if (!sizeof($this->errors))
					{
						if ($add AND $qty >= 0)
						{
							/* Product addition to the cart */
							if (!isset($this->cart->id) OR !$this->cart->id)
							{
								$this->cart->add();
								if ($this->cart->id)
									$this->cookie->id_cart = (int)($this->cart->id);
							}
							if ($add AND !$producToAdd->hasAllRequiredCustomizableFields() AND !$customizationId)
								$this->errors[] = Tools::displayError('Please fill all required fields, then save the customization.');
							if (!sizeof($this->errors))
							{
								$updateQuantity = $this->cart->updateQty((int)($qty), (int)($idProduct), (int)($idProductAttribute), $customizationId, Tools::getValue('op', 'up'));

								if ($updateQuantity < 0)
								{
									/* if product has attribute, minimal quantity is set with minimal quantity of attribute*/
									if ((int)$idProductAttribute)
										$minimal_quantity = Attribute::getAttributeMinimalQty((int)$idProductAttribute);
									else
										$minimal_quantity = $producToAdd->minimal_quantity;
									if (Tools::getValue('ajax') == 'true')
										die('{"hasError" : true, "errors" : ["'.Tools::displayError('you need to add', false).' '.$minimal_quantity.' '.Tools::displayError('quantity minimum', false).'"]}');
									else
										$this->errors[] = Tools::displayError('you need to add').' '.$minimal_quantity.' '.Tools::displayError('quantity minimum')
										.((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php' OR (!Tools::isSubmit('ajax') AND substr(basename($_SERVER['REQUEST_URI']),0, strlen('cart.php')) == 'cart.php')) ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- '.
										Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
								}
								elseif (!$updateQuantity)
								{
									if (Tools::getValue('ajax') == 'true')
										die('{"hasError" : true, "errors" : ["'.Tools::displayError('you already have the maximum quantity available for this product', false).'"]}');
									else
										$this->errors[] = Tools::displayError('you already have the maximum quantity available for this product')
										.((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php' OR (!Tools::isSubmit('ajax') AND substr(basename($_SERVER['REQUEST_URI']),0, strlen('cart.php')) == 'cart.php')) ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- '.
										Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
								}
							}
						}
						elseif ($delete)
						{
							$this->cart->deleteProduct((int)($idProduct), (int)($idProductAttribute), (int)($customizationId));
							if (!Cart::getNbProducts((int)($this->cart->id)))
							{
								$this->cart->id_carrier = 0;
								$this->cart->gift = 0;
								$this->cart->gift_message = '';
								$this->cart->update();
							}
						}
					}
					$discounts = $this->cart->getDiscounts();
					foreach($discounts AS $discount)
					{
						$discountObj = new Discount((int)($discount['id_discount']), (int)($this->cookie->id_lang));
						if ($error = $this->cart->checkDiscountValidity($discountObj, $discounts, $this->cart->getOrderTotal(true, 1), $this->cart->getProducts()))
						{
							$this->cart->deleteDiscount((int)($discount['id_discount']));
							$this->cart->update();
							$errors[] = $error;
						}
					}
					if (!sizeof($this->errors))
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
				if (Tools::getValue('ajax') != 'true' AND !sizeof($this->errors))
					Tools::redirect('order.php?'.(isset($idProduct) ? 'ipa='.(int)($idProduct) : ''));

			}
		}
	}

	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'errors.tpl');
	}
}
