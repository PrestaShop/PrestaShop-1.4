<?php

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
						$customer = new Customer(intval($this->cookie->id_customer));
						$groups = $customer->getGroups();
					}
					else
						$groups = array(1);
					$address_delivery = new Address(intval($this->cart->id_address_delivery));
					$result = array('carriers' => Carrier::getCarriersOpc($address_delivery->id_country, $groups));
				}
				$result['summary'] = $this->cart->getSummaryDetails();
				die(Tools::jsonEncode($result));
			}
			else
				require_once(_PS_MODULE_DIR_.'/blockcart/blockcart-ajax.php');
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

	public function preProcess()
	{
		parent::preProcess();
		
		$orderTotal = $this->cart->getOrderTotal(true, 1);

		$this->cartDiscounts = $this->cart->getDiscounts();
		foreach ($this->cartDiscounts AS $k => $this->cartDiscount)
			if ($error = $this->cart->checkDiscountValidity(new Discount(intval($this->cartDiscount['id_discount'])), $this->cartDiscounts, $orderTotal, $this->cart->getProducts()))
				$this->cart->deleteDiscount(intval($this->cartDiscount['id_discount']));

		$add = Tools::getIsset('add') ? 1 : 0;
		$delete = Tools::getIsset('delete') ? 1 : 0;

		if (Configuration::get('PS_TOKEN_ENABLE') == 1 &&
			strcasecmp(Tools::getToken(false), strval(Tools::getValue('token'))) &&
			$this->$this->cookie->isLogged() === true)
			$this->errors[] = Tools::displayError('invalid token');

		// Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
		if (($add OR Tools::getIsset('update') OR $delete) AND isset($this->cookie->date_add))
		{
			//get the values
			$idProduct = intval(Tools::getValue('id_product', NULL));
			$idProductAttribute = intval(Tools::getValue('id_product_attribute', Tools::getValue('ipa')));
			$customizationId = intval(Tools::getValue('id_customization', 0));
			$qty = intval(abs(Tools::getValue('qty', 1)));
			if ($qty == 0)
				$this->errors[] = Tools::displayError('null quantity');
			elseif (!$idProduct)
				$this->errors[] = Tools::displayError('product not found');
			else
			{
				$producToAdd = new Product(intval($idProduct), true, intval($this->cookie->id_lang));
				if ((!$producToAdd->id OR !$producToAdd->active) AND !$delete)
					$this->errors[] = Tools::displayError('product is no longer available');
				else
				{
					/* Check the quantity availability */
					if ($idProductAttribute AND is_numeric($idProductAttribute))
					{
						if (!$delete AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) AND !Attribute::checkAttributeQty(intval($idProductAttribute), intval($qty)))
							$this->errors[] = Tools::displayError('there is not enough product in stock');
					}
					elseif ($producToAdd->hasAttributes() AND !$delete)
					{
						$idProductAttribute = Product::getDefaultAttribute(intval($producToAdd->id), intval($producToAdd->out_of_stock) == 2 ? !intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')) : !intval($producToAdd->out_of_stock));
						if (!$idProductAttribute)
							Tools::redirectAdmin($link->getProductLink($producToAdd));
						elseif (!$delete AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) AND !Attribute::checkAttributeQty(intval($idProductAttribute), intval($qty)))
							$this->errors[] = Tools::displayError('there is not enough product in stock');
					}
					elseif (!$delete AND !$producToAdd->checkQty(intval($qty)))
						$this->errors[] = Tools::displayError('there is not enough product in stock');
					/* Check vouchers compatibility */
					if ($add AND (($producToAdd->specificPrice AND floatval($producToAdd->specificPrice['reduction'])) OR $producToAdd->on_sale))
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
									$this->cookie->id_cart = intval($this->cart->id);
							}
							if ($add AND !$producToAdd->hasAllRequiredCustomizableFields() AND !$customizationId)
								$this->errors[] = Tools::displayError('Please fill all required fields, then save the customization.');
							if (!sizeof($this->errors))
							{
								$updateQuantity = $this->cart->updateQty(intval($qty), intval($idProduct), intval($idProductAttribute), $customizationId, Tools::getValue('op', 'up'));

								if ($updateQuantity < 0)
									$this->errors[] = Tools::displayError('you need add').' '.$producToAdd->minimal_quantity.' '.Tools::displayError('quantity minimum')
										.((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php' OR (!Tools::isSubmit('ajax') AND substr(basename($_SERVER['REQUEST_URI']),0, strlen('cart.php')) == 'cart.php')) ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- '.
										Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
								elseif (!$updateQuantity)
								{
									if (Tools::getValue('ajax') == 'true')
										die('{\'hasError\' : true, errors : [\''.Tools::displayError('you already have the maximum quantity available for this product').'\']}');
									else
										$this->errors[] = Tools::displayError('you already have the maximum quantity available for this product')
										.((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php' OR (!Tools::isSubmit('ajax') AND substr(basename($_SERVER['REQUEST_URI']),0, strlen('cart.php')) == 'cart.php')) ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- '.
										Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
								}
							}
						}
						elseif ($delete)
						{
							if (Cart::getNbProducts($this->cart->id) == 1)
							{
								$discounts = $this->cart->getDiscounts();
								foreach($discounts as $discount)
								{
									$discountObj = new Discount(intval($discount['id_discount']), intval($this->cookie->id_lang));
									if ($tmpError = $this->cart->checkDiscountValidity($discountObj, $discounts, $this->cart->getOrderTotal(true, 1), $this->cart->getProducts()))
										$this->errors[] = $tmpError;
									else
										$this->cart->deleteDiscount(intval($discount['id_discount']));
								}
							}
							$this->cart->deleteProduct(intval($idProduct), intval($idProductAttribute), $customizationId);
							if (!Cart::getNbProducts(intval($this->cart->id)))
							{
								$this->cart->id_carrier = 0;
								$this->cart->gift = 0;
								$this->cart->gift_message = '';
								$this->cart->update();
							}
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
					Tools::redirect('order.php?'.(isset($idProduct) ? 'ipa='.intval($idProduct) : ''));
			}
		}
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'errors.tpl');
	}
}