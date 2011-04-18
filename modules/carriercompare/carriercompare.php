<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class CarrierCompare extends Module
{
	private $_html = '';
	private $_postErrors = array();
	private $customerInfos = array();
	private $_userSession = array();

	public function __construct()
	{
		$this->name = 'carriercompare';
		$this->tab = 'carrier_compare';
		$this->version = '0.1';
		$this->author = 'PrestaShop';
		
		parent::__construct();

		$this->displayName = $this->l('Carrier Compare');
		$this->description = $this->l('Module to Compare carrier possibilities before going under the checkout process');

		$this->_storeUserSessionInformation();
	}

	public function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('shoppingCart'))
			return false;
		return true;
	}

	public function uninstall()
	{
		return parent::uninstall();
	}

	private function _storeUserSessionInformation()
	{
		global $cookie;

		// Default values
		$this->_userSession['isLogged'] = false;
		$this->_userSession['id_lang'] = 1;
		$this->_userSession['id_state'] = '';
		$this->_userSession['id_country'] = '';
		$this->_userSession['zipcode'] = '';
		$this->_userSession['id_zone'] = '';
		$this->_userSession['id_carrier'] = '';

		if ($cookie)
		{
			if (isset($cookie->id_customer))
			{
				$this->_userSession['id_customer'] = $cookie->id_customer;
				$this->_userSession['isLogged'] = true;
			}
			$this->_userSession['id_lang'] = $cookie->id_lang;
		}
	}

	private function _printCss()
	{
		echo '
			<style>
				#compare_shipping {margin:20px 0 20px 0; line-height:30px;}
				#compare_shipping label {width:220px; float:left; padding-left:20px;}
				#submit 
				{
					text-align:right;
					margin-left:40px; 
					cursor:pointer; 
					padding:3px; 
					font-size:10px;
					font-weight:bold;
				}
				#submitForm {text-align:center; margin-top:10px;}
				.warningCarrierCompare 
				{
					font-size:10px; 
					height:20px; 
					text-align:center; 
					padding:0; 
					padding-bottom:10px; 
				}
			</style>
			';
	}

	private function _printJS()
	{
		echo '
			<script type="text/javascript" text="javascript">
				function updateStateByIdCountry()
				{
					id_country = $("#id_country").val();
					
					$.ajax({
						type: \'POST\',
						url: \''._MODULE_DIR_.'carriercompare/ajax/getStatesByIdCountry.php\',
						data: "id_country=" + id_country + "&id_lang='.
							(int)$this->_userSession['id_lang'].'&id_state='.$this->_userSession['id_state'].'",
						success: function(msg) {
							$("#availableStateProvince").html(msg);
							updateCarriersList();

							if ($("#id_country").length)
							{
								$("#id_state").change(function()
								{
									updateCarriersList();
								});
							}
						}
					});
				}

				function updateCarriersList()
				{
					id_zone = $("#id_zone").val();
					id_state = $("#id_state").val();
					
					$.ajax({
						type: \'POST\',
						url: \''._MODULE_DIR_.'carriercompare/ajax/getCarriersListByZoneId.php\',
						data: "id_zone=" + id_zone + "&id_state='.(($this->_userSession['id_state']) ? 
						$this->_userSession['id_state'] : '" + id_state + "').'&id_lang='.
							(int)$this->_userSession['id_lang'].'&id_carrier='.$this->_userSession['id_carrier'].'",
						success: function(msg) {
							$("#availableCarriers").html(msg);
						}
					});

				}

				$(document).ready(function()
				{
					$("#id_country").change(function()
					{
						updateStateByIdCountry();
					});
					updateStateByIdCountry();
				});
			</script>
		';
	}

	/*
	** Display the guest form user to retrieve his information
	*/
	private function _getGuestFormInformation()
	{
		$this->_html .= '<form method="POST" action="'.$this->_path.'redirect.php?redirect='.$_SERVER['PHP_SELF'].'">';
		$countries = Country::getCountries($this->_userSession['id_lang']);
		$this->_html .= '<div id="compare_shipping">
			<h2>'.$this->l('Estimate your shipping').'</h2>';
		if (count($this->_postErrors))
		{
			$this->_html .= '<div class="error">';
			foreach($this->_postErrors as $msgError)
				$this->_html .= $msgError.'<br />';
			$this->_html .= '</div>';
		}
		$this->_html .= '
			<label for="country">'.$this->l('Select your Country').'</label>
			<select name="id_country" id="id_country">';
		foreach($countries as $country)
		{
			$selected = '';
			if ($country['id_country'] == $this->_userSession['id_country'])
				$selected = 'selected="selected"';
			$this->_html .= '<option value="'.$country['id_country'].
				'" '.$selected.'>'.$country['name'].'</option>';
		}
		$this->_html.= '
			</select>
			<div id="availableStateProvince"></div>
			<label for="zipcode">'.$this->l('Zipcode').'</label>
			<input type="text" name="zipcode" id="zipcode" value="'.$this->_userSession['zipcode'].'"/>
			<input type="hidden" name="redirect" id="redirect" value="'.$_SERVER['PHP_SELF'].'" />
			<div id="availableCarriers"></div>
			<div id="submitForm">
				<input type="submit" id="submit" name="submitFormInformation" value="'.$this->l('Submit').'"/>
			</div>
		</div>';	
	}

	/*
	** Store the guest form request
	*/
	private function _setGuestFormInformation()
	{
		if (Validate::isInt(Tools::getValue('id_state')))
			$this->_userSession['id_state'] = Tools::getValue('id_state');
		else
			$this->_postErrors[] = $this->l('Please don\'t try to modify the value manually');
		if (Validate::isInt(Tools::getValue('id_country')))
			$this->_userSession['id_country'] = Tools::getValue('id_country');
		else
			$this->_postErrors[] = $this->l('Please don\'t try to modify the value manually');
		if ($this->_checkZipcode(Tools::getValue('zipcode')))
			$this->_userSession['zipcode'] = Tools::getValue('zipcode');
		else
			$this->_postErrors[] = $this->l('Please use a valid zipcode depending of your country selection');
		if (Validate::isInt(Tools::getValue('id_carrier')))
			$this->_userSession['id_carrier'] = Tools::getValue('id_carrier');
		else
			$this->_postErrors[] = $this->l('Please don\'t try to modify the value manually');
	}

	/*
	** Update the cart and cookie is none erros occured
	*/
	private function _updateCart($cart)
	{
		global $cookie;

		$cookie->id_country = $this->_userSession['id_country'];
		$cookie->id_state = $this->_userSession['id_state'];
		$cookie->postcode = $this->_userSession['zipcode'];
		if ($this->_userSession['id_carrier'])
		{
			$cart->id_carrier = $this->_userSession['id_carrier'];
			$cart->update();
		}
	}

	/*
	 ** Hook Shopping Cart Process
	 */
	public function hookShoppingCart($params)
	{
		$this->_printCss();
		
		if (!$this->_userSession['isLogged'])
		{
			if (Tools::getIsset('result'))
				$this->_setGuestFormInformation();
			$this->_getGuestFormInformation();
		}
		$this->_printJS();
		return $this->_html;
	}

	/*
	** Build the redirect URL depending of the post keys
	*/
	private function _buildRedirectURL()
	{
		$redirect = Tools::getValue('redirect').'?id_country='.
			$this->_userSession['id_country'];
		if (strlen($this->_userSession['id_state']))
			$redirect .= '&id_state='.$this->_userSession['id_state'];
		if (strlen($this->_userSession['zipcode']))
			$redirect .= '&zipcode='.$this->_userSession['zipcode'];
		if (strlen($this->_userSession['id_carrier']))
			$redirect .= '&id_carrier='.$this->_userSession['id_carrier'];
		$redirect .= '&result='.count($this->_postErrors);
		return $redirect;
	}

	/*
	** Make the redirect process
	*/
	public function redirectProcess($cart)
	{
		if (!$this->_userSession['isLogged'] && 
				Tools::getValue('submitFormInformation'))
		{
			$this->_setGuestFormInformation();
			if (!count($this->_postErrors))
				$this->_updateCart($cart);
		}
		$redirect = $this->_buildRedirectURL();
		header('Location: '.$redirect);
	}

	/*
	** Get states by Country id, called by the ajax process
	** id_state allow to preselect the selection option
	*/
	public function getStatesByIdCountry($id_country, $id_state = '')
	{
		$html = '';

		$states = State::getStatesByIdCountry($id_country);
		if ($states && count($states))
		{
			$html = '<label for="states">'.$this->l('Select your State/Province').'</label>';
			$html .= '<select name="id_state" id="id_state">';

			foreach($states as $state)
			{
				$selected = '';
				if ($state['id_state'] == $id_state)
					$selected = 'selected="selected"';
				$html .= '<option value="'.$state['id_state'].'" '.$selected.'>'.
					$state['name'].'</option>';
			}
			$html.= '</select>';
		}
		else
			$html .= '<input type="hidden" name="id_zone" id="id_zone" value="'.
				Country::getIdZone($id_country).'" />';;
		return $html.'<div class="clear"></div>';
	}

	/*
	** Get carriers by country id, called by the ajax process
	*/
	public function getCarriersListByIdZone($id_zone, $id_carrier = '')
	{
		$html = '';

		$carriers = Carrier::getCarriersForOrder($id_zone);
		if ($carriers && count($carriers))
		{
			$html = '<label for="carriers">'.$this->l('Select your Carrier').'</label>';
			$html .= '<select name="id_carrier" id="id_carrier">';

			foreach($carriers as $carrier)
			{
				$selected = '';
				if ($carrier['id_carrier'] == $id_carrier)
					$selected = 'selected="selected"';
			$html .= '<option value="'.$carrier['id_carrier'].'" '.$selected.'>'.
					$carrier['name'].'</option>';
			}
			$html.= '</select>';
		}
		else
			$html .= '<div class="warning warningCarrierCompare">'.
				$this->l('There isn\'t carriers for this selection').'</div>';
		return $html;
	}

	private function _checkZipcode($zipcode)
	{
		$zipcodeFormat = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT `zip_code_format`
				FROM `'._DB_PREFIX_.'country`
				WHERE `id_country` = '.(int)($this->_userSession['id_country']));
		
		if (!$zipcodeFormat)
			return false;
		
		$regxMask = str_replace(
				array('N', 'C', 'L'), 
				array(
					'[0-9]', 
					Country::getIsoById($this->_userSession['id_country']),
					'[a-zA-Z]'),
				$zipcodeFormat);
		if (preg_match('/'.$regxMask.'/', $zipcode))
			return true;
		return false;
	}
}
