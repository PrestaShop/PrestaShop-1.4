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

class OrderOpcControllerCore extends FrontController
{
	public $isLogged;
	
	public function __construct()
	{
		parent::__construct();
		
		/* Disable some cache related bugs on the cart/order */
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		
		$this->isLogged = (bool)((int)($this->cookie->id_customer) AND Customer::customerIdExistsStatic((int)($this->cookie->id_customer)));
	}
	
	public function preProcess()
	{
		parent::preProcess();
		
		if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 0)
			Tools::redirect('order.php');
		
		if ($this->cart->nbProducts())
		{
			if (Tools::isSubmit('ajax') AND $this->isLogged)
			{
				if (Tools::isSubmit('method'))
				{
					switch (Tools::getValue('method'))
					{
						case 'updateMessage':
							if (Tools::isSubmit('message'))
							{
								$txtMessage = urldecode(Tools::getValue('message'));
								$oldMessage = Message::getMessageByCartId((int)($this->cart->id));
								if ($txtMessage)
								{
									if (!Validate::isMessage($txtMessage))
						    			$this->errors[] = Tools::displayError('invalid message');
						    		elseif ($oldMessage)
						    		{
						    			$message = new Message((int)($oldMessage['id_message']));
						    			$message->message = htmlentities($txtMessage, ENT_COMPAT, 'UTF-8');
						    			$message->update();
						    		}
						    		else
						    		{
						    			$message = new Message();
						    			$message->message = htmlentities($txtMessage, ENT_COMPAT, 'UTF-8');
						    			$message->id_cart = (int)($this->cart->id);
						    			$message->id_customer = (int)($this->cart->id_customer);
						    			$message->add();
						    		}
						    	}
						    	else
						    	{
						    		if ($oldMessage)
						    		{
						    			$message = new Message((int)($oldMessage['id_message']));
						    			$message->delete();
						    		}
						    	}
						    	if (sizeof($this->errors))
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								die(true);
							}
							break;
						case 'updateCarrier':
							if (Tools::isSubmit('id_carrier') AND Tools::isSubmit('recyclable') AND Tools::isSubmit('gift') AND Tools::isSubmit('gift_message'))
							{
								$this->cart->recyclable = (int)(Tools::getValue('recyclable'));
								$this->cart->gift = (int)(Tools::getValue('gift'));
								if ((int)(Tools::getValue('gift')))
								{
									if (!Validate::isMessage($_POST['gift_message']))
										$this->errors[] = Tools::displayError('invalid gift message');
									else
										$this->cart->gift_message = strip_tags($_POST['gift_message']);
								}
								
								$address = new Address((int)($this->cart->id_address_delivery)); // dynamise for id country
								if (!($id_zone = Country::getIdZone((int)($address->id_country))))
									$this->errors[] = Tools::displayError('no zone match with your address');
								if (Validate::isInt(Tools::getValue('id_carrier')) AND sizeof(Carrier::checkCarrierZone((int)(Tools::getValue('id_carrier')), (int)($id_zone))))
									$this->cart->id_carrier = (int)(Tools::getValue('id_carrier'));
								elseif (!$this->cart->isVirtualCart() AND (int)(Tools::getValue('id_carrier')) != 0)
									$this->errors[] = Tools::displayError('invalid carrier or no carrier selected');
								if (sizeof($this->errors))
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								
								Module::hookExec('ProcessCarrier', array('cart' => $this->cart));
								if ($this->cart->update())
								{
									$summary = $this->cart->getSummaryDetails();
									die(Tools::jsonEncode($summary));
								}
								else
									$this->errors[] = Tools::displayError('error occurred on update of cart');
								if (sizeof($this->errors))
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								exit;
							}
							break;
						case 'updateTOSStatus':
							if (Tools::isSubmit('checked'))
							{
								$this->cookie->checkedTOS = (int)(Tools::getValue('checked'));
								die(true);
							}
							break;
						case 'getCarrierList':
							$address_delivery = new Address($this->cart->id_address_delivery);
							if ($this->cookie->id_customer)
							{
								$customer = new Customer((int)($this->cookie->id_customer));
								$groups = $customer->getGroups();
							}
							else
								$groups = array(1);
							if (!Address::isCountryActiveById((int)($this->cart->id_address_delivery)))
								$this->errors[] = Tools::displayError('this address is not in a valid area');
							elseif (!Validate::isLoadedObject($address_delivery) OR $address_delivery->deleted)
								$this->errors[] = Tools::displayError('this address is not valid');
							else
							{
								$this->cart->id_carrier = 0;
								$this->cart->update();
								$carriers = Carrier::getCarriersOpc((int)($address_delivery->id_country), $groups);
								$result = array(
									'carriers' => $carriers,
									'HOOK_BEFORECARRIER' => Module::hookExec('beforeCarrier', array('carriers' => $carriers)),
									'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $address_delivery))
								);
								die (Tools::jsonEncode($result));
							}
							if (sizeof($this->errors))
								die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
							break;
						case 'getPaymentModule':
							if ($this->cart->OrderExists())
								die('<p class="warning">'.Tools::displayError('Error: this order is already validated').'</p>');
							if (!$this->cart->id_customer OR !Customer::customerIdExistsStatic($this->cart->id_customer) OR Customer::isBanned($this->cart->id_customer))
								die('<p class="warning">'.Tools::displayError('Error: no customer').'</p>');
							$address_delivery = new Address($this->cart->id_address_delivery);
							$address_invoice = ($this->cart->id_address_delivery == $this->cart->id_address_invoice ? $address_delivery : new Address($this->cart->id_address_invoice));
							if (!$this->cart->id_address_delivery OR !$this->cart->id_address_invoice OR !Validate::isLoadedObject($address_delivery) OR !Validate::isLoadedObject($address_invoice) OR $address_invoice->deleted OR $address_delivery->deleted)
								die('<p class="warning">'.Tools::displayError('Error: please choose an address').'</p>');
							if (!$this->cart->id_carrier AND !$this->cart->isVirtualCart())
								die('<p class="warning">'.Tools::displayError('Error: please choose a carrier').'</p>');
							elseif ($this->cart->id_carrier != 0)
							{
								$carrier = new Carrier((int)($this->cart->id_carrier));
								if (!Validate::isLoadedObject($carrier) OR $carrier->deleted OR !$carrier->active)
									die('<p class="warning">'.Tools::displayError('Error: the carrier is invalid').'</p>');
							}
							if (!$this->cart->id_currency)
								die('<p class="warning">'.Tools::displayError('Error: no currency has been selected').'</p>');
							if (!$this->cookie->checkedTOS AND Configuration::get('PS_CONDITIONS'))
								die('<p class="warning">'.Tools::displayError('Error: please accept Terms of Service').'</p>');
							
							/* If some products have disappear */
							if (!$this->cart->checkQuantities())
								die('<p class="warning">'.Tools::displayError('An item in your cart is no longer available, you cannot proceed with your order').'</p>');
							
							/* Check minimal account */
							$orderTotalDefaultCurrency = Tools::convertPrice($this->cart->getOrderTotal(true, 1), Currency::getCurrency((int)(Configuration::get('PS_CURRENCY_DEFAULT'))));
							$minimalPurchase = (float)(Configuration::get('PS_PURCHASE_MINIMUM'));
							if ($orderTotalDefaultCurrency < $minimalPurchase)
								die('<p class="warning">'.Tools::displayError('A minimum purchase total of').' '.Tools::displayPrice($minimalPurchase, Currency::getCurrency((int)($this->cart->id_currency))).
								' '.Tools::displayError('is required in order to validate your order').'</p>');
							
							if ($this->cart->getOrderTotal() <= 0)
							{
								$order = new FreeOrder();
								$order->validateOrder((int)($this->cart->id), _PS_OS_PAYMENT_, 0, Tools::displayError('Free order', false));
								die('freeorder');
							}
							
							die(Module::hookExec('payment'));
							break;
						case 'editCustomer':
							$customer = new Customer((int)$this->cookie->id_customer);
							$this->errors = $customer->validateControler();
							$customer->newsletter = (int)Tools::isSubmit('newsletter');
							$customer->optin = (int)Tools::isSubmit('optin');
							$return = array(
								'hasError' => !empty($this->errors), 
								'errors' => $this->errors,
								'id_customer' => (int)$this->cookie->id_customer,
								'token' => Tools::getToken(false)
							);
							if (!sizeof($this->errors))
								$return['isSaved'] = (bool)$customer->update();
							else
								$return['isSaved'] = false;
							die(Tools::jsonEncode($return));
							break;
						default:
							exit;
					}
				}
				elseif (Tools::isSubmit('processAddress') AND Tools::getValue('id_address_delivery') AND Tools::getValue('id_address_invoice'))
				{
					$id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
					$id_address_invoice = (int)(Tools::getValue('id_address_invoice'));
					$address_delivery = new Address((int)(Tools::getValue('id_address_delivery')));
					$address_invoice = ((int)(Tools::getValue('id_address_delivery')) == (int)(Tools::getValue('id_address_invoice')) ? $address_delivery : new Address((int)(Tools::getValue('id_address_invoice'))));
					
					if (!Address::isCountryActiveById((int)(Tools::getValue('id_address_delivery'))))
						$this->errors[] = Tools::displayError('this address is not in a valid area');
					elseif (!Validate::isLoadedObject($address_delivery) OR !Validate::isLoadedObject($address_invoice) OR $address_invoice->deleted OR $address_delivery->deleted)
						$this->errors[] = Tools::displayError('this address is not valid');
					else
					{
						$this->cart->id_carrier = 0;
						$this->cart->id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
						$this->cart->id_address_invoice = Tools::isSubmit('same') ? $this->cart->id_address_delivery : (int)(Tools::getValue('id_address_invoice'));
						if (!$this->cart->update())
							$this->errors[] = Tools::displayError('an error occurred while updating your cart');
						if (!sizeof($this->errors))
						{
							if ($this->cookie->id_customer)
							{
								$customer = new Customer((int)($this->cookie->id_customer));
								$groups = $customer->getGroups();
							}
							else
								$groups = array(1);
							$carriers = Carrier::getCarriersOpc((int)($address_delivery->id_country), $groups);
							$result = array(
								'carriers' => $carriers,
								'summary' => $this->cart->getSummaryDetails(),
								'HOOK_BEFORECARRIER' => Module::hookExec('beforeCarrier', array('carriers' => $carriers)),
								'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $address_delivery))
							);
							die(Tools::jsonEncode($result));
						}
					}
					if (sizeof($this->errors))
						die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
					exit;
				}
				exit;
			}
			elseif (Tools::isSubmit('submitAddDiscount') AND Tools::getValue('discount_name'))
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
						Tools::redirect('order-opc.php');
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
				Tools::redirect('order-opc.php');
			}
		}
		elseif (Tools::isSubmit('ajax'))
			exit;
	}
	
	public function setMedia()
	{
		parent::setMedia();
		
		// Adding CSS style sheet
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		Tools::addCSS(_THEME_CSS_DIR_.'order-opc.css');
		Tools::addCSS(_PS_CSS_DIR_.'thickbox.css', 'all');
		// Adding JS files
		Tools::addJS(_THEME_JS_DIR_.'tools.js');
		Tools::addJS(_THEME_JS_DIR_.'order-address.js');
		Tools::addJS(_THEME_JS_DIR_.'order-opc.js');
		if ((int)(Configuration::get('PS_BLOCK_CART_AJAX')))
			Tools::addJS(_THEME_JS_DIR_.'cart-summary.js');
		Tools::addJS(_PS_JS_DIR_.'jquery/thickbox-modified.js');
		Tools::addJS(_PS_JS_DIR_.'jquery/jquery-typewatch.pack.js');
		Tools::addJS(_THEME_JS_DIR_.'tools/statesManagement.js');
	}
	
	public function process()
	{
		// SHOPPING CART
		$summary = $this->cart->getSummaryDetails();
		$customizedDatas = Product::getAllCustomizedDatas((int)($this->cart->id));
		Product::addCustomizationPrice($summary['products'], $customizedDatas);
		$currency = new Currency((int)($this->cookie->id_currency));
		
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
		
		// Wrapping fees
		$wrapping_fees = floatval(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
    	$wrapping_fees_tax = new Tax((int)(Configuration::get('PS_GIFT_WRAPPING_TAX')));
    	$wrapping_fees_tax_inc = $wrapping_fees * (1 + (((float)($wrapping_fees_tax->rate) / 100)));
    
		$cms = new CMS((int)(Configuration::get('PS_CONDITIONS_CMS_ID')), (int)($this->cookie->id_lang));
		$this->link_conditions = $this->link->getCMSLink($cms, $cms->link_rewrite, true);
    	if (!strpos($this->link_conditions, '?'))
    		$this->link_conditions .= '?content_only=1&TB_iframe=true&width=450&height=500&thickbox=true';
    	else
    		$this->link_conditions .= '&content_only=1&TB_iframe=true&width=450&height=500&thickbox=true';
		
		$selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
		$countries = Country::getCountries((int)($this->cookie->id_lang), true);
		
		// for compatibility with 1.2 themes
		foreach($summary['products'] AS $key => $product)
		    $summary['products'][$key]['quantity'] = $product['cart_quantity'];
		$this->smarty->assign($summary);
		$this->smarty->assign(array(
		    'token_cart' => Tools::getToken(false),
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
			'isLogged' => $this->isLogged,
			'isGuest' => isset($this->cookie->is_guest) ? $this->cookie->is_guest : 0,
			'currencySign' => $currency->sign,
			'currencyRate' => $currency->conversion_rate,
			'currencyFormat' => $currency->format,
			'currencyBlank' => $currency->blank,
			'displayVouchers' => Discount::getVouchersToCartDisplay((int)($this->cookie->id_lang), (isset($this->cookie->id_customer) ? (int)($this->cookie->id_customer) : 0)),
			'checkedTOS' => (int)($this->cookie->checkedTOS),
			'recyclablePackAllowed' => (int)(Configuration::get('PS_RECYCLABLE_PACK')),
	    	'giftAllowed' => (int)(Configuration::get('PS_GIFT_WRAPPING')),
	    	'cms_id' => (int)(Configuration::get('PS_CONDITIONS_CMS_ID')),
	    	'conditions' => (int)(Configuration::get('PS_CONDITIONS')),
	    	'link_conditions' => $this->link_conditions,
	    	'recyclable' => (int)($this->cart->recyclable),
	    	'gift_wrapping_price' => (float)(Configuration::get('PS_GIFT_WRAPPING_PRICE')),
			'total_wrapping' => Tools::convertPrice($wrapping_fees_tax_inc, new Currency((int)($this->cookie->id_currency))),
			'total_wrapping_tax_exc' => Tools::convertPrice($wrapping_fees, new Currency((int)($this->cookie->id_currency))),
			'countries' => $countries,
			'sl_country' => (isset($selectedCountry) ? $selectedCountry : 0),
			'vat_management' => Configuration::get('VATNUMBER_MANAGEMENT'),
			'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED')
		));
		
		/* Load guest informations */
		if ($this->isLogged AND $this->cookie->is_guest)
		{
			$customer = new Customer((int)($this->cookie->id_customer));
			$address_delivery = new Address((int)$this->cart->id_address_delivery);
			
			$guestInformations = array(
				'id_customer' => (int)($this->cookie->id_customer),
				'email' => Tools::htmlentitiesUTF8($customer->email),
				'lastname' => Tools::htmlentitiesUTF8($customer->lastname),
				'firstname' => Tools::htmlentitiesUTF8($customer->firstname),
				'newsletter' => (int)$customer->newsletter,
				'optin' => (int)$customer->optin,
				'id_address_delivery' => (int)$this->cart->id_address_delivery,
				'address1' => Tools::htmlentitiesUTF8($address_delivery->address1),
				'postcode' => Tools::htmlentitiesUTF8($address_delivery->postcode),
				'city' => Tools::htmlentitiesUTF8($address_delivery->city),
				'phone' => Tools::htmlentitiesUTF8($address_delivery->phone),
				'phone_mobile' => Tools::htmlentitiesUTF8($address_delivery->phone_mobile),
				'id_country' => (int)($address_delivery->id_country),
				'id_state' => (int)($address_delivery->id_state),
				'id_gender' => (int)$customer->id_gender
			);
			$this->smarty->assign('guestInformations', $guestInformations);
		}
		
		if ($this->isLogged)
		{
			// ADDRESS
			if (!Customer::getAddressesTotalById((int)($this->cookie->id_customer)))
				Tools::redirect('address.php?back=order.php?step=1');
			$customer = new Customer((int)($this->cookie->id_customer));
			/* Getting customer addresses */
			$customerAddresses = $customer->getAddresses((int)($this->cookie->id_lang));
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
			$this->smarty->assign('addresses', $customerAddresses);
			if ($oldMessage = Message::getMessageByCartId((int)($this->cart->id)))
				$this->smarty->assign('oldMessage', $oldMessage['message']);
			
			// CARRIER
			$carriers = Carrier::getCarriersOpc((int)($deliveryAddress->id_country), $customer->getGroups());
			
			$checked = 0;
			if (Validate::isUnsignedInt($this->cart->id_carrier) AND $this->cart->id_carrier)
			{
				$carrier = new Carrier((int)($this->cart->id_carrier));
				if ($carrier->active AND !$carrier->deleted)
					$checked = (int)($this->cart->id_carrier);
			}
			$this->smarty->assign(array(
				'checked' => (int)($checked),
				'carriers' => $carriers,
				'default_carrier' => (int)(Configuration::get('PS_CARRIER_DEFAULT')),
				'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $deliveryAddress)),
				'HOOK_BEFORECARRIER' => Module::hookExec('beforeCarrier', array('carriers' => $carriers))
			));
		}
		Tools::safePostVars();
	}
	
	public function displayHeader()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayHeader();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		
		$this->smarty->display(_PS_THEME_DIR_.'errors.tpl');
		$this->smarty->display(_PS_THEME_DIR_.'order-opc.tpl');
	}
	
	public function displayFooter()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayFooter();
	}
	
}


