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

class OrderOpcControllerCore extends ParentOrderController
{
	public $isLogged;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->isLogged = (bool)((int)($this->cookie->id_customer) AND Customer::customerIdExistsStatic((int)($this->cookie->id_customer)));
	}
	
	public function preProcess()
	{
		parent::preProcess();
		
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
								$this->_updateMessage($txtMessage);
						    	if (sizeof($this->errors))
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								die(true);
							}
							break;
						case 'updateCarrier':
							if (Tools::isSubmit('id_carrier') AND Tools::isSubmit('recyclable') AND Tools::isSubmit('gift') AND Tools::isSubmit('gift_message'))
							{
								if ($this->_processCarrier())
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
								$carriers = Carrier::getCarriersForOrder((int)Address::getZoneById((int)($address_delivery->id)), $groups);
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
							
							/* Bypass payment step if total is 0 */
							if ($this->_checkFreeOrder())
								die('freeorder');
							
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
							$carriers = Carrier::getCarriersForOrder((int)Address::getZoneById((int)($address_delivery->id)), $groups);
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
		}
		elseif (Tools::isSubmit('ajax'))
			exit;
	}
	
	public function setMedia()
	{
		parent::setMedia();
		
		// Adding CSS style sheet
		Tools::addCSS(_THEME_CSS_DIR_.'order-opc.css');
		// Adding JS files
		Tools::addJS(_THEME_JS_DIR_.'order-opc.js');
		Tools::addJS(_THEME_JS_DIR_.'tools/statesManagement.js');
	}
	
	public function process()
	{
		// SHOPPING CART
		$this->_assignSummaryInformations();
		// WRAPPING AND TOS
		$this->_assignWrappingAndTOS();

		$selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
		$countries = Country::getCountries((int)($this->cookie->id_lang), true);
		$this->smarty->assign(array(
			'isLogged' => $this->isLogged,
			'isGuest' => isset($this->cookie->is_guest) ? $this->cookie->is_guest : 0,
			'countries' => $countries,
			'sl_country' => (isset($selectedCountry) ? $selectedCountry : 0),
			'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED')
		));
		
		/* Load guest informations */
		if ($this->isLogged AND $this->cookie->is_guest)
			$this->smarty->assign('guestInformations', $this->_getGuestInformations());
		
		if ($this->isLogged)
		{
			// ADDRESS
			$this->_assignAddress();
			
			// CARRIER
			$this->_assignCarrier();
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
	
	private function _getGuestInformations()
	{
		$customer = new Customer((int)($this->cookie->id_customer));
		$address_delivery = new Address((int)$this->cart->id_address_delivery);
		
		return array(
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
	}
	
}


