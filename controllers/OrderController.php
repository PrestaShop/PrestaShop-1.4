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

/* Class FreeOrder to use PaymentModule (abstract class, cannot be instancied) */
class FreeOrder extends PaymentModule {}

class OrderControllerCore extends FrontController
{
	public $step;
	public $nbProducts;

	public function __construct()
	{
		parent::__construct();

		/* Disable some cache related bugs on the cart/order */
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		
		$this->step = (int)(Tools::getValue('step'));
		$this->nbProducts = $this->cart->nbProducts();
		if (!$this->nbProducts)
			$this->step = -1;
	}
	
	public function preProcess()
	{
		global $isVirtualCart;

		parent::preProcess();
        
		if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
			Tools::redirect('order-opc.php');
			
		/* If some products have disappear */
		if (!$this->cart->checkQuantities())
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('An item in your cart is no longer available, you cannot proceed with your order');
		}

		/* Check minimal account */
		$orderTotal = $this->cart->getOrderTotal();

		$orderTotalDefaultCurrency = Tools::convertPrice($this->cart->getOrderTotal(true, 1), Currency::getCurrency((int)(Configuration::get('PS_CURRENCY_DEFAULT'))));
		$minimalPurchase = (float)(Configuration::get('PS_PURCHASE_MINIMUM'));
		if ($orderTotalDefaultCurrency < $minimalPurchase)
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('A minimum purchase total of').' '.Tools::displayPrice($minimalPurchase, Currency::getCurrency((int)($this->cart->id_currency))).
			' '.Tools::displayError('is required in order to validate your order');
		}

		if (!$this->cookie->isLogged() AND in_array($this->step, array(1, 2, 3)))
			Tools::redirect('authentication.php?back=order.php?step='.$this->step);

		$this->smarty->assign('back', Tools::safeOutput(Tools::getValue('back')));
		
		if ($this->nbProducts)
		{
			/* Manage discounts */
			if ((Tools::isSubmit('submitDiscount') OR Tools::isSubmit('submitDiscount')) AND Tools::getValue('discount_name'))
			{
				$discountName = Tools::getValue('discount_name');
				if (!Validate::isDiscountName($discountName))
					$this->errors[] = Tools::displayError('voucher name not valid');
				else
				{
					$discount = new Discount((int)(Discount::getIdByName($discountName)));
					if (Validate::isLoadedObject($discount))
					{
						if ($tmpError = $this->cart->checkDiscountValidity($discount, $this->cart->getDiscounts(), $this->cart->getOrderTotal(), $this->cart->getProducts(), true))
							$this->errors[] = $tmpError;
					}
					else
						$this->errors[] = Tools::displayError('voucher name not valid');
					if (!sizeof($this->errors))
					{
						$this->cart->addDiscount((int)($discount->id));
						Tools::redirect('order.php');
					}
				}
				$this->smarty->assign(array(
					'errors' => $this->errors,
					'discount_name' => Tools::safeOutput($discountName)
				));
			}
			elseif (isset($_GET['deleteDiscount']) AND Validate::isUnsignedId($_GET['deleteDiscount']))
			{
				$this->cart->deleteDiscount((int)($_GET['deleteDiscount']));
				Tools::redirect('order.php');
			}

			/* Is there only virtual product in cart */
			if ($isVirtualCart = $this->cart->isVirtualCart())
				$this->setNoCarrier();
			$this->smarty->assign('virtual_cart', $isVirtualCart);
		}
	}
	
	public function setMedia()
	{
		parent::setMedia();
		
		Tools::addJS(_THEME_JS_DIR_.'tools.js');
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		
		if ($this->step == 1)
			Tools::addJS(_THEME_JS_DIR_.'order-address.js');
		if ($this->step == 2)
		{
			Tools::addCSS(_PS_CSS_DIR_.'thickbox.css', 'all');
			Tools::addJS(_PS_JS_DIR_.'jquery/thickbox-modified.js');
		}
		if (!in_array($this->step, array(1, 2, 3)))
			if ((int)(Configuration::get('PS_BLOCK_CART_AJAX')))
			{
				Tools::addJS(_THEME_JS_DIR_.'cart-summary.js');
				Tools::addJS(_PS_JS_DIR_.'jquery/jquery-typewatch.pack.js');
			}
	}
	
	public function displayHeader()
	{
		if (!Tools::getValue('ajax'))
			parent::displayHeader();
	}
	
	public function process()
	{
		parent::process();
		
		global $currency;
		
		/* 4 steps to the order */
		switch ((int)($this->step))
		{
			case -1;
				$this->smarty->assign('empty', 1);
				break;
			case 1:
				$this->assignAddress();
				break;
			case 2:
				if(Tools::isSubmit('processAddress'))
					$this->processAddress();
				$this->autoStep();
				$this->assignCarrier();
				break;
			case 3:
				if(Tools::isSubmit('processCarrier'))
					$this->processCarrier();
				$this->autoStep();
				$this->checkFreeOrder();
				$this->assignPayment();
				break;
			default:
				if (file_exists(_PS_SHIP_IMG_DIR_.(int)($this->cart->id_carrier).'.jpg'))
					$this->smarty->assign('carrierPicture', 1);
				$summary = $this->cart->getSummaryDetails();
				$customizedDatas = Product::getAllCustomizedDatas((int)($this->cart->id));
				Product::addCustomizationPrice($summary['products'], $customizedDatas);

				if ($free_ship = Tools::convertPrice((float)(Configuration::get('PS_SHIPPING_FREE_PRICE')), new Currency((int)($this->cart->id_currency))))
				{
					$discounts = $this->cart->getDiscounts();
					$total_free_ship =  $free_ship - ($summary['total_products_wt'] + $summary['total_discounts']);
					foreach ($discounts as $discount)
						if ($discount['id_discount_type'] == 3)
						{
							$total_free_ship = 0;
							break;
						}
					$this->smarty->assign('free_ship', $total_free_ship);
				}
				// for compatibility with 1.2 themes
				foreach($summary['products'] AS $key => $product)
					$summary['products'][$key]['quantity'] = $product['cart_quantity'];
				$this->smarty->assign($summary);
				$token = Tools::getToken(false);
				
				$this->smarty->assign(array(
					'token_cart' => $token,
					'isVirtualCart' => $this->cart->isVirtualCart(),
					'productNumber' => $this->cart->nbProducts(),
					'voucherAllowed' => Configuration::get('PS_VOUCHERS'),
					'HOOK_SHOPPING_CART' => Module::hookExec('shoppingCart', $summary),
					'HOOK_SHOPPING_CART_EXTRA' => Module::hookExec('shoppingCartExtra', $summary),
					'shippingCost' => $this->cart->getOrderTotal(true, 5),
					'shippingCostTaxExc' => $this->cart->getOrderTotal(false, 5),
					'customizedDatas' => $customizedDatas,
					'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
					'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
					'lastProductAdded' => $this->cart->getLastProduct(),
					'displayVouchers' => Discount::getVouchersToCartDisplay((int)($this->cookie->id_lang), (isset($this->cookie->id_customer) ? (int)($this->cookie->id_customer) : 0)),
					'currencySign' => $currency->sign,
					'currencyRate' => $currency->conversion_rate,
					'currencyFormat' => $currency->format,
					'currencyBlank' => $currency->blank
					));
				break;
		}
	}
	
	public function displayContent()
	{
		parent::displayContent();
		
		switch ((int)($this->step))
		{
			case -1:
				$this->smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
				break;
			case 1:
				$this->smarty->display(_PS_THEME_DIR_.'order-address.tpl');
				break;
			case 2:
				$this->smarty->display(_PS_THEME_DIR_.'order-carrier.tpl');
				break;
			case 3:
				$this->smarty->display(_PS_THEME_DIR_.'order-payment.tpl');
				break;
			default:
				$this->smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
				break;
		}
	}
	
	public function displayFooter()
	{
		if (!Tools::getValue('ajax'))
			parent::displayFooter();
	}
	
	/* Order process controller */
	public function autoStep()
	{
		global $isVirtualCart;

		if ($this->step >= 2 AND (!$this->cart->id_address_delivery OR !$this->cart->id_address_invoice))
			Tools::redirect('order.php?step=1');
		$delivery = new Address((int)($this->cart->id_address_delivery));
		$invoice = new Address((int)($this->cart->id_address_invoice));
		if ($delivery->deleted OR $invoice->deleted)
		{
			if ($delivery->deleted)
				unset($this->cart->id_address_delivery);
			if ($invoice->deleted)
				unset($this->cart->id_address_invoice);
			Tools::redirect('order.php?step=1');
		}
		elseif ($this->step >= 3 AND !$this->cart->id_carrier AND !$isVirtualCart)
			Tools::redirect('order.php?step=2');
	}

	/* Bypass payment step if total is 0 */
	public function checkFreeOrder()
	{
		if ($this->cart->getOrderTotal() <= 0)
		{
			$order = new FreeOrder();
			$order->free_order_class = true;
			$order->validateOrder((int)($this->cart->id), _PS_OS_PAYMENT_, 0, Tools::displayError('Free order', false));
			Tools::redirect('history.php');
		}
	}

	/**
	 * Set id_carrier to 0 (no shipping price)
	 *
	 */
	public function setNoCarrier()
	{
		$this->cart->id_carrier = 0;
		$this->cart->update();
	}

	/*
	 * Manage address
	 */
	public function processAddress()
	{
		if (!isset($_POST['id_address_delivery']) OR !Address::isCountryActiveById((int)($_POST['id_address_delivery'])))
			$this->errors[] = Tools::displayError('this address is not in a valid area');
		else
		{
			$this->cart->id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
			$this->cart->id_address_invoice = Tools::isSubmit('same') ? $this->cart->id_address_delivery : (int)(Tools::getValue('id_address_invoice'));
			if (!$this->cart->update())
				$this->errors[] = Tools::displayError('an error occurred while updating your cart');

			if (Tools::isSubmit('message') AND !empty($_POST['message']))
			{
				if (!Validate::isMessage($_POST['message']))
					$this->errors[] = Tools::displayError('invalid message');
				elseif ($oldMessage = Message::getMessageByCartId((int)($this->cart->id)))
				{
					$message = new Message((int)($oldMessage['id_message']));
					$message->message = htmlentities($_POST['message'], ENT_COMPAT, 'UTF-8');
					$message->update();
				}
				else
				{
					$message = new Message();
					$message->message = htmlentities($_POST['message'], ENT_COMPAT, 'UTF-8');
					$message->id_cart = (int)($this->cart->id);
					$message->id_customer = (int)($this->cart->id_customer);
					$message->add();
				}
			}
		}
		if (sizeof($this->errors))
		{
			if (Tools::getValue('ajax'))
				die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
			$this->step = 1;
		}
		if (Tools::getValue('ajax'))
			die(true);
	}

	/* Carrier step */
	public function processCarrier()
	{
		global $isVirtualCart, $orderTotal;

		$this->errors = array();

		$this->cart->recyclable = (isset($_POST['recyclable']) AND !empty($_POST['recyclable'])) ? 1 : 0;

		if (isset($_POST['gift']) AND !empty($_POST['gift']))
		{
			if (!Validate::isMessage($_POST['gift_message']))
				$this->errors[] = Tools::displayError('invalid gift message');
			else
			{
				$this->cart->gift = 1;
				$this->cart->gift_message = strip_tags($_POST['gift_message']);
			}
		}
		else
			$this->cart->gift = 0;

		$address = new Address((int)($this->cart->id_address_delivery));
		if (!Validate::isLoadedObject($address))
			die(Tools::displayError());
		if (!$id_zone = Address::getZoneById($address->id))
			$this->errors[] = Tools::displayError('no zone match with your address');
		if (isset($_POST['id_carrier']) AND Validate::isInt($_POST['id_carrier']) AND sizeof(Carrier::checkCarrierZone((int)($_POST['id_carrier']), (int)($id_zone))))
			$this->cart->id_carrier = (int)($_POST['id_carrier']);
		elseif (!$isVirtualCart)
			$this->errors[] = Tools::displayError('invalid carrier or no carrier selected');

		Module::hookExec('processCarrier', array('cart' => $this->cart));

		$this->cart->update();

		if (sizeof($this->errors))
		{
			$this->smarty->assign('errors', $this->errors);
			$this->assignCarrier();
			include(dirname(__FILE__).'/../footer.php');
			exit;
		}
		$orderTotal = $this->cart->getOrderTotal();
	}

	/* Address step */
	public function assignAddress()
	{
		if (!Customer::getAddressesTotalById((int)($this->cookie->id_customer)))
			Tools::redirect('address.php?back=order.php?step=1');
		$customer = new Customer((int)($this->cookie->id_customer));
		if (Validate::isLoadedObject($customer))
		{
			/* Getting customer addresses */
			$customerAddresses = $customer->getAddresses((int)($this->cookie->id_lang));
			$this->smarty->assign('addresses', $customerAddresses);

			/* Setting default addresses for cart */
			if ((!isset($this->cart->id_address_delivery) OR empty($this->cart->id_address_delivery)) AND sizeof($customerAddresses))
			{
				$this->cart->id_address_delivery = (int)($customerAddresses[0]['id_address']);
				$update = 1;
			}
			if ((!isset($this->cart->id_address_invoice) OR empty($this->cart->id_address_invoice)) AND sizeof($customerAddresses))
			{
				$this->cart->id_address_invoice = (int)($customerAddresses[0]['id_address']);
				$update = 1;
			}
			/* Update cart addresses only if needed */
			if (isset($update) AND $update)
				$this->cart->update();

			/* If delivery address is valid in cart, assign it to Smarty */
			if (isset($this->cart->id_address_delivery))
			{
				$deliveryAddress = new Address((int)($this->cart->id_address_delivery));
				if (Validate::isLoadedObject($deliveryAddress) AND ($deliveryAddress->id_customer == $customer->id))
					$this->smarty->assign('delivery', $deliveryAddress);
			}

			/* If invoice address is valid in cart, assign it to Smarty */
			if (isset($this->cart->id_address_invoice))
			{
				$invoiceAddress = new Address((int)($this->cart->id_address_invoice));
				if (Validate::isLoadedObject($invoiceAddress) AND ($invoiceAddress->id_customer == $customer->id))
					$this->smarty->assign('invoice', $invoiceAddress);
			}
		}
		if ($oldMessage = Message::getMessageByCartId((int)($this->cart->id)))
			$this->smarty->assign('oldMessage', $oldMessage['message']);
		$this->smarty->assign('cart', $this->cart);
	}

	/* Carrier step */
	public function assignCarrier()
	{
		global $defaultCountry;

		$address = new Address((int)($this->cart->id_address_delivery));
		$id_zone = Address::getZoneById((int)($address->id));
		if (isset($this->cookie->id_customer))
			$customer = new Customer((int)($this->cookie->id_customer));
		else
			die(Tools::displayError('Fatal error: No customer'));
		$result = Carrier::getCarriers((int)($this->cookie->id_lang), true, false, (int)($id_zone), $customer->getGroups(), PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		if (!$result)
			$result = Carrier::getCarriers((int)($this->cookie->id_lang), true, false, (int)($id_zone), array(), PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		$resultsArray = array();
		foreach ($result AS $k => $row)
		{
			$carrier = new Carrier((int)($row['id_carrier']), Configuration::get('PS_LANG_DEFAULT'));

			// Get only carriers that are compliant with shipping method
			if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
			OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
			{
				unset($result[$k]);
				continue ;
			}
			
			// If out-of-range behavior carrier is set on "Desactivate carrier"
			if ($row['range_behavior'])
			{
				// Get id zone
				if (isset($this->cart->id_address_delivery) AND $this->cart->id_address_delivery)
					$id_zone = Address::getZoneById((int)($this->cart->id_address_delivery));
				else
					$id_zone = (int)($defaultCountry->id_zone);

				// Get only carriers that have a range compatible with cart
				if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $this->cart->getTotalWeight(), $id_zone)))
				OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $this->cart->getOrderTotal(true, 4), $id_zone, $this->cart->id_currency))))
					{
						unset($result[$k]);
						continue ;
					}
			}
			$row['name'] = (strval($row['name']) != '0' ? $row['name'] : Configuration::get('PS_SHOP_NAME'));
			$row['price'] = $this->cart->getOrderShippingCost((int)($row['id_carrier']));
			$row['price_tax_exc'] = $this->cart->getOrderShippingCost((int)($row['id_carrier']), false);
			$row['img'] = file_exists(_PS_SHIP_IMG_DIR_.(int)($row['id_carrier']).'.jpg') ? _THEME_SHIP_DIR_.(int)($row['id_carrier']).'.jpg' : '';
			$resultsArray[] = $row;
		}

		// Wrapping fees
		$wrapping_fees = (float)(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
		$wrapping_fees_tax = new Tax((int)(Configuration::get('PS_GIFT_WRAPPING_TAX')));
		$wrapping_fees_tax_inc = $wrapping_fees * (1 + (((float)($wrapping_fees_tax->rate) / 100)));

		if (Validate::isUnsignedInt($this->cart->id_carrier) AND $this->cart->id_carrier)
		{
			$carrier = new Carrier((int)($this->cart->id_carrier));
			if ($carrier->active AND !$carrier->deleted)
				$checked = (int)($this->cart->id_carrier);
		}
		$cms = new CMS((int)(Configuration::get('PS_CONDITIONS_CMS_ID')), (int)($this->cookie->id_lang));
		$this->link_conditions = $this->link->getCMSLink($cms, $cms->link_rewrite, true);
		if (!strpos($this->link_conditions, '?'))
			$this->link_conditions .= '?content_only=1&TB_iframe=true&width=450&height=500&thickbox=true';
		else
			$this->link_conditions .= '&content_only=1&TB_iframe=true&width=450&height=500&thickbox=true';
		if (!isset($checked) OR (int)($checked) == 0)
			$checked = (int)(Configuration::get('PS_CARRIER_DEFAULT'));
		$this->smarty->assign(array(
			'checkedTOS' => (int)($this->cookie->checkedTOS),
			'recyclablePackAllowed' => (int)(Configuration::get('PS_RECYCLABLE_PACK')),
			'giftAllowed' => (int)(Configuration::get('PS_GIFT_WRAPPING')),
			'cms_id' => (int)(Configuration::get('PS_CONDITIONS_CMS_ID')),
			'conditions' => (int)(Configuration::get('PS_CONDITIONS')),
			'link_conditions' => $this->link_conditions,
			'recyclable' => (int)($this->cart->recyclable),
			'gift_wrapping_price' => (float)(Configuration::get('PS_GIFT_WRAPPING_PRICE')),
			'carriers' => $resultsArray,
			'default_carrier' => (int)(Configuration::get('PS_CARRIER_DEFAULT')),
			'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $address)),
			'HOOK_BEFORECARRIER' => Module::hookExec('beforeCarrier', array('carriers' => $resultsArray)),
			'checked' => (int)($checked),
			'total_wrapping' => Tools::convertPrice($wrapping_fees_tax_inc, new Currency((int)($this->cookie->id_currency))),
			'total_wrapping_tax_exc' => Tools::convertPrice($wrapping_fees, new Currency((int)($this->cookie->id_currency)))));
	}

	/* Payment step */
	public function assignPayment()
	{
		global $orderTotal;

		// Redirect instead of displaying payment modules if any module are grefted on
		Hook::backBeforePayment('order.php?step=3');

		/* We may need to display an order summary */
		$this->smarty->assign($this->cart->getSummaryDetails());

		$this->cookie->checkedTOS = '1';
		$this->smarty->assign(array(
			'HOOK_PAYMENT' => Module::hookExecPayment(), 
			'total_price' => (float)($orderTotal),
			'taxes_enabled' => (int)(Configuration::get('PS_TAX'))
		));
	}
}

