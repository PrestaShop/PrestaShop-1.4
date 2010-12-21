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

class AddressControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->guestAllowed = true;
		$this->authRedirection = 'addresses.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	public function preProcess()
	{
		parent::preProcess();
		
		if ($back = Tools::getValue('back'))
			$this->smarty->assign('back', Tools::safeOutput($back));
		if ($mod = Tools::getValue('mod'))
			$this->smarty->assign('mod', Tools::safeOutput($mod));
		
		if (Tools::isSubmit('ajax') AND Tools::isSubmit('type'))
		{
			if (Tools::getValue('type') == 'delivery')
				$id_address = isset($this->cart->id_address_delivery) ? (int)$this->cart->id_address_delivery : 0;
			elseif (Tools::getValue('type') == 'invoice')
				$id_address = (isset($this->cart->id_address_invoice) AND $this->cart->id_address_invoice != $this->cart->id_address_delivery) ? (int)$this->cart->id_address_invoice : 0;
			else
				exit;
		}
		else
			$id_address = (int)Tools::getValue('id_address', 0);
		
		if ($id_address)
		{
			$address = new Address((int)($id_address));
			if (Validate::isLoadedObject($address) AND Customer::customerHasAddress((int)($this->cookie->id_customer), (int)($id_address)))
			{
				if (Tools::isSubmit('delete'))
				{
					if ($this->cart->id_address_invoice == $address->id)
						unset($this->cart->id_address_invoice);
					if ($this->cart->id_address_delivery == $address->id)
						unset($this->cart->id_address_delivery);
					if ($address->delete())
						Tools::redirect('addresses.php');
					$this->errors[] = Tools::displayError('this address cannot be deleted');
				}
				$this->smarty->assign(array(
					'address' => $address,
					'id_address' => (int)($id_address)
				));
			}
			elseif (Tools::isSubmit('ajax'))
				exit;
			else
				Tools::redirect('addresses.php');
		}
		if (Tools::isSubmit('submitAddress'))
		{
			$address = new Address();
			$this->errors = $address->validateControler();
			$address->id_customer = (int)($this->cookie->id_customer);

			if (!Tools::getValue('phone') AND !Tools::getValue('phone_mobile'))
				$this->errors[] = Tools::displayError('You must register at least one phone number');
			if (!$country = new Country((int)($address->id_country)) OR !Validate::isLoadedObject($country))
				die(Tools::displayError());
			$zip_code_format = $country->zip_code_format;
			if ($country->need_zip_code)
			{
				if (($postcode = Tools::getValue('postcode')) AND $zip_code_format)
				{
					$zip_regexp = '/^'.$zip_code_format.'$/ui';
					$zip_regexp = str_replace(' ', '( |)', $zip_regexp);
					$zip_regexp = str_replace('-', '(-|)', $zip_regexp);
					$zip_regexp = str_replace('N', '[0-9]', $zip_regexp);
					$zip_regexp = str_replace('L', '[a-zA-Z]', $zip_regexp);
					$zip_regexp = str_replace('C', $country->iso_code, $zip_regexp);
					if (!preg_match($zip_regexp, $postcode))
						$this->errors[] = Tools::displayError('Your postal code/zip code is incorrect.').'<br />'.Tools::displayError('It must be typed as follows :').' '.str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $zip_code_format)));
				}
				elseif ($zip_code_format)
					$this->errors[] = Tools::displayError('postcode is required.');
				elseif ($postcode AND !preg_match('/^[0-9a-zA-Z -]{4,9}$/ui', $postcode))
					$this->errors[] = Tools::displayError('Your postal code/zip code is incorrect.');
			}

			if (Configuration::get('PS_TOKEN_ENABLE') == 1 AND
				strcmp(Tools::getToken(false), Tools::getValue('token')) AND
				$this->cookie->isLogged(true) === true)
				$this->errors[] = Tools::displayError('invalid token');

			if ((int)($country->contains_states) AND !(int)($address->id_state))
				$this->errors[] = Tools::displayError('this country requires a state selection');
			
			if (!sizeof($this->errors))
			{
				if (isset($id_address))
				{
					$country = new Country((int)($address->id_country));
					if (Validate::isLoadedObject($country) AND !$country->contains_states)
						$address->id_state = 0;
					$address_old = new Address((int)($id_address));
					if (Validate::isLoadedObject($address_old) AND Customer::customerHasAddress((int)($this->cookie->id_customer), (int)($address_old->id)))
					{
						if (!Tools::isSubmit('ajax'))
						{
							if ($this->cart->id_address_invoice == $address_old->id)
								unset($this->cart->id_address_invoice);
							if ($this->cart->id_address_delivery == $address_old->id)
								unset($this->cart->id_address_delivery);
						}
						
						if ($address_old->isUsed())
							$address_old->delete();
						else
						{
							$address->id = (int)($address_old->id);
							$address->date_add = $address_old->date_add;
						}
					}
				}
				elseif ($this->cookie->is_guest)
					Tools::redirect('addresses.php');
				
				if ($result = $address->save())
				{
					if ((bool)(Tools::getValue('select_address', false)) == true OR (Tools::isSubmit('ajax') AND Tools::getValue('type') == 'invoice'))
					{
						/* This new adress is for invoice_adress, select it */
						$this->cart->id_address_invoice = (int)($address->id);
						$this->cart->update();
					}
					if (Tools::isSubmit('ajax'))
					{
						$return = array(
							'hasError' => !empty($this->errors), 
							'errors' => $this->errors,
							'id_address_delivery' => $this->cart->id_address_delivery,
							'id_address_invoice' => $this->cart->id_address_invoice
						);
						die(Tools::jsonEncode($return));
					}
					Tools::redirect($back ? ($mod ? $back.'&back='.$mod : $back) : 'addresses.php');
				}
				$this->errors[] = Tools::displayError('an error occurred while updating your address');
			}
		}
		elseif (!$id_address)
		{
			$customer = new Customer((int)($this->cookie->id_customer));
			if (Validate::isLoadedObject($customer))
			{
				$_POST['firstname'] = $customer->firstname;
				$_POST['lastname'] = $customer->lastname;
			}
		}
		if (Tools::isSubmit('ajax') AND sizeof($this->errors))
		{
			$return = array(
				'hasError' => !empty($this->errors), 
				'errors' => $this->errors
			);
			die(Tools::jsonEncode($return));
		}
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addJS(_THEME_JS_DIR_.'tools/statesManagement.js');
	}
	
	public function process()
	{
		parent::process();

		/* Secure restriction for guest */
		if ($this->cookie->is_guest)
			Tools::redirect('addresses.php');
		
		if (Tools::isSubmit('id_country') AND Tools::getValue('id_country') != NULL AND is_numeric(Tools::getValue('id_country')))
			$selectedCountry = (int)(Tools::getValue('id_country'));
		elseif (isset($address) AND isset($address->id_country) AND !empty($address->id_country) AND is_numeric($address->id_country))
			$selectedCountry = (int)($address->id_country);
		elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$array = preg_split('/,|-/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if (!Validate::isLanguageIsoCode($array[0]) OR !($selectedCountry = Country::getByIso($array[0])))
				$selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
		}
		else
			$selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));

		$countries = Country::getCountries((int)($this->cookie->id_lang), true);
		$countriesList = '';
		foreach ($countries AS $country)
			$countriesList .= '<option value="'.(int)($country['id_country']).'" '.($country['id_country'] == $selectedCountry ? 'selected="selected"' : '').'>'.htmlentities($country['name'], ENT_COMPAT, 'UTF-8').'</option>';

		$this->smarty->assign(array(
			'countries_list' => $countriesList,
			'countries' => $countries,
			'errors' => $this->errors,
			'token' => Tools::getToken(false),
			'select_address' => (int)(Tools::getValue('select_address'))
		));
	}
	
	public function displayHeader()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayHeader();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'address.tpl');
	}
	
	public function displayFooter()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayFooter();
	}
}

