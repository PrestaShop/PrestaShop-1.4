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

class UpsCarrier extends CarrierModule
{
	private $_html = '';
	private $_postErrors = array();
	private $_pickupTypeList = array();
	private $_packagingTypeList = array();
	private $_dimensionUnit = '';
	private $_weightUnit = '';
	private $_dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
	private $_weightUnitList = array('KG' => 'KGS', 'KGS' => 'KGS', 'LB' => 'LBS', 'LBS' => 'LBS');
	private $_config = array(
		'name' => 'UPS Carrier',
		'id_tax' => 0,
		'active' => true,
		'deleted' => 0,
		'shipping_handling' => false,
		'range_behavior' => 0,
		'is_module' => false,
		'delay' => array('fr' => 'Transporteur UPS', 'en' => 'UPS Carrier'),
		'id_zone' => 1,
		'is_module' => true,
		'shipping_external' => true,
		'external_module_name' => 'UpsCarrier',
		'need_range' => true
		);


	/*** Construct Method ***/

	public function __construct()
	{
		global $cookie;
		
		$this->name = 'upscarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '0.3';

		parent::__construct ();

		$this->displayName = $this->l('UPS Carrier');
		$this->description = $this->l('Offer to your customers, different delivery methods with UPS');

		if (self::isInstalled($this->name))
		{
			$ids = array();

			// Loading Carriers
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriers as $carrier)
				$ids[] = $carrier['id_carrier'];
			$warning = array();

			// Check configuration values
			if (!Configuration::get('UPS_CARRIER_LOGIN'))
				$warning[] = $this->l('\'UPS Login\'').' ';
			if (!Configuration::get('UPS_CARRIER_PASSWORD'))
				$warning[] = $this->l('\'UPS Password\'').' ';
			if (!Configuration::get('UPS_CARRIER_SHIPPER_ID'))
				$warning[] = $this->l('\'MyUps ID\'').' ';
			if (!Configuration::get('UPS_CARRIER_API_KEY'))
				$warning[] = $this->l('\'UPS API Key ID\'').' ';
			if (!Configuration::get('UPS_CARRIER_PICKUP_TYPE'))
				$warning[] = $this->l('\'UPS Pickup Type ID\'').' ';
			if (!Configuration::get('UPS_CARRIER_PACKAGING_TYPE'))
				$warning[] = $this->l('\'UPS Packaging Type ID\'').' ';

			// Check shop configuration
			if (!Configuration::get('PS_SHOP_CODE'))
				$warning[] = $this->l('\'Shop postal code (in Preferences Tab).\'').' ';
			if (!Configuration::get('PS_SHOP_COUNTRY_ID'))
				$warning[] = $this->l('\'Shop country (in Preferences Tab).\'').' ';

			// Check webservice ups availibility
			if (!$this->webserviceTest())
				$warning[] = $this->l('Could not connect to UPS Webservices, check your API Key').', ';

			// Checking Unit
			$this->_dimensionUnit = $this->_dimensionUnitList[strtoupper(Configuration::get('PS_DIMENSION_UNIT'))];
			$this->_weightUnit = $this->_weightUnitList[strtoupper(Configuration::get('PS_WEIGHT_UNIT'))];
			if (!$this->_weightUnit)
				$warning[] = $this->l('\'Weight Unit must be LB or KG (in Preferences > Localization Tab).\'').' ';
			if (!$this->_dimensionUnit)
				$warning[] = $this->l('\'Dimension Unit must be CM or IN (in Preferences > Localization Tab).\'').' ';

			// Generate warnings
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';

			// Loading pickup type list
			$this->_pickupTypeList = array(
				'01' => $this->l('Daily Pickup'),
				'03' => $this->l('Customer Counter'),
				'06' => $this->l('One Time Pickup'),
				'07' => $this->l('On Call Air'),
				'11' => $this->l('Suggested Retail Rates'),
				'19' => $this->l('Letter Center'),
				'20' => $this->l('Air Service Center'),
			);

			// Loading packaging type list
			$this->_packagingTypeList = array(
				'00' => $this->l('UNKNOWN'),
				'01' => $this->l('UPS Letter'),
				'02' => $this->l('Package'),
				'03' => $this->l('Tube'),
				'04' => $this->l('Pak'),
				'21' => $this->l('Express Box'),
				'24' => $this->l('25KG Box'),
				'25' => $this->l('10KG Box'),
				'30' => $this->l('Pallet'),
				'2a' => $this->l('Small Express Box'),
				'2b' => $this->l('Medium Express Box'),
				'2c' => $this->l('Large Express Box'),
			);
		}
	}


	/*** Install / Uninstall Methods ***/

	public function install()
	{
		global $cookie;

		// Install carrier and config values
		if (!parent::install() OR
			!Configuration::updateValue('UPS_CARRIER_LOGIN', '') OR
			!Configuration::updateValue('UPS_CARRIER_PASSWORD', '') OR
			!Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', '') OR
			!Configuration::updateValue('UPS_CARRIER_API_KEY', '') OR
			!Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', '01') OR
			!Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', '02') OR
			!$this->registerHook('updateCarrier'))
			return false;

		// Create cache table in database
		$sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ups_cache` (
				  `id_ups_cache` int(10) NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) NOT NULL,
				  `id_category` int(10) NOT NULL,
				  `pickup_type_code` varchar(64) NOT NULL,
				  `total_charges` double(10,2) NOT NULL,
				  `currency_code` varchar(3) NOT NULL,
				  `date_add` datetime NOT NULL,
				  `date_upd` datetime NOT NULL,
				  PRIMARY KEY  (`id_ups_cache`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		// Create cache table in database
		$sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ups_config` (
				  `id_ups_config` int(10) NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) NOT NULL,
				  `id_category` int(10) NOT NULL,
				  `pickup_type_code` varchar(64) NOT NULL,
				  `additionnal_charges` double(6,2) NOT NULL,
				  `total_charges` double(10,2) NOT NULL,
				  `currency_code` varchar(3) NOT NULL,
				  `date_add` datetime NOT NULL,
				  `date_upd` datetime NOT NULL,
				  PRIMARY KEY  (`id_ups_config`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		// Executing sql
		if (!Db::getInstance()->Execute($sql1) OR !Db::getInstance()->Execute($sql2))
			return false;

		// If already installed in the past the carrier is being reactivated, else the carrier is being created
		if (Configuration::get('UPS_CARRIER_ID'))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 0), 'UPDATE', '`id_carrier` = '.(int)(Configuration::get('UPS_CARRIER_ID')));
		else if (!$this->createExternalCarrier($this->_config))
			return false;

		return true; 					
	}
	
	public function uninstall()
	{
		global $cookie;

		// Delete cache and config tables in database
		$sql1 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ups_cache`;';
		$sql2 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ups_config`;';

		// Uninstall Script
		if (!parent::uninstall() OR
			!Configuration::deleteByName('UPS_CARRIER_LOGIN') OR
			!Configuration::deleteByName('UPS_CARRIER_PASSWORD') OR
			!Configuration::deleteByName('UPS_CARRIER_SHIPPER_ID') OR
			!Configuration::deleteByName('UPS_CARRIER_API_KEY') OR
			!Configuration::deleteByName('UPS_CARRIER_PICKUP_TYPE') OR
			!Configuration::deleteByName('UPS_CARRIER_PACKAGING_TYPE') OR
			!Db::getInstance()->Execute($sql1) OR
			!Db::getInstance()->Execute($sql2) OR
			!$this->unregisterHook('updateCarrier'))
			return false;
		
		// Delete External Carrier
		$extCarrier = new Carrier((int)(Configuration::get('UPS_CARRIER_ID')));

		// If external carrier is default set other one as default
		if (Configuration::get('PS_CARRIER_DEFAULT') == (int)($extCarrier->id))
		{
			$carriersD = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriersD as $carrierD)
				if ($carrierD['active'] AND !$carrierD['deleted'] AND ($carrierD['name'] != $this->_config['name']))
					Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
		}
		$extCarrier->deleted = 1;
		if (!$extCarrier->update())
			return false;

		return true;
	}

	public static function createExternalCarrier($config)
	{
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax = $config['id_tax'];
		$carrier->id_zone = $config['id_zone'];
		$carrier->active = $config['active'];
		$carrier->deleted = $config['deleted'];
		$carrier->delay = $config['delay'];
		$carrier->shipping_handling = $config['shipping_handling'];
		$carrier->range_behavior = $config['range_behavior'];
		$carrier->is_module = $config['is_module'];
		$carrier->shipping_external = $config['shipping_external'];
		$carrier->external_module_name = $config['external_module_name'];
		$carrier->need_range = $config['need_range'];
		$carrier->deleted = 1;
	
		$languages = Language::getLanguages(true);
		foreach ($languages as $language) 
		{
			if ($language['iso_code'] == 'fr')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
		}
		
		if ($carrier->add())
		{				
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.(int)($carrier->id).'\',\''.(int)($group['id_group']).'\')');

			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = '0';
			$rangePrice->delimiter2 = '10000';
			$rangePrice->add();
		
			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = '0';
			$rangeWeight->delimiter2 = '10000';
			$rangeWeight->add();
			
			$zones = Zone::getZones(true);
			foreach ($zones as $zone)
			{
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_zone VALUE (\''.(int)($carrier->id).'\',\''.(int)($zone['id_zone']).'\')');
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'delivery VALUE (\'\',\''.(int)($carrier->id).'\',\''.(int)($rangePrice->id).'\',NULL,\''.(int)($zone['id_zone']).'\',\'1\')');
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'delivery VALUE (\'\',\''.(int)($carrier->id).'\',NULL,\''.(int)($rangeWeight->id).'\',\''.(int)($zone['id_zone']).'\',\'1\')');
			}

			// Saving ID Carrier
			Configuration::updateValue('UPS_CARRIER_ID', (int)($carrier->id));

			// Copy logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			return true;
		}
		else
			return false;
	}


	/*** Form Config Methods ***/

	public function getContent()
	{
		$this->_html .= '<h2>' . $this->l('UPS Carrier').'</h2>';
		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		$this->_displayForm();
		return $this->_html;
	}

	private function _displayForm()
	{
		global $cookie;
		
		$this->_html .= '<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Description').'</legend>'.
		$this->l('UPS Carrier Configurator.').'<br />'.$this->l('On this version, the UPS module will automatically choose the cheapest delivery service available.').'
		</fieldset>
		<div class="clear">&nbsp;</div>

			<ul id="menuTab">
				<li id="menuTab1" class="menuTabButton selected">1. '.$this->l('General Settings').'</li>
			</ul>
			<div id="tabList">
				<div id="menuTab1Sheet" class="tabItem selected">'.$this->_displayFormGeneral().'</div>
			</div>
			<br clear="left" />
			<br />

			<style>
				#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
				#menuTab li
				{
					text-align: left;
					float: left;
					display: inline;
					padding: 5px;
					padding-right: 10px;
					background: #EFEFEF;
					font-weight: bold;
					cursor: pointer;
					border-left: 1px solid #EFEFEF;
					border-right: 1px solid #EFEFEF;
					border-top: 1px solid #EFEFEF;
				}
				#menuTab li.menuTabButton.selected
				{
					background : #FFF6D3;
					border-left: 1px solid #CCCCCC;
					border-right: 1px solid #CCCCCC;
					border-top: 1px solid #CCCCCC;
				}
				#tabList { clear: left; }
				.tabItem { display: none; }
				.tabItem.selected
				{
					display: block;
					background: #FFFFF0;
					border: 1px solid #CCCCCC;
					padding: 10px;
					padding-top: 20px;
				}
			</style>
			<script>
				$(".menuTabButton").click(function () { 
				  $(".menuTabButton.selected").removeClass("selected");
				  $(this).addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#" + this.id + "Sheet").addClass("selected");
				});
			</script>
		';
		if (isset($_GET['id_tab']))
			$this->_html .= '<script>
				  $(".menuTabButton.selected").removeClass("selected");
				  $("#menuTab'.$_GET['id_tab'].'").addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#menuTab'.$_GET['id_tab'].'Sheet").addClass("selected");
			</script>';
	}

	private function _displayFormGeneral()
	{
		$html = '<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=1" method="post" class="form">				
					<label>'.$this->l('Your UPS Login').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ups_carrier_login" value="'.Tools::getValue('ups_carrier_login', Configuration::get('UPS_CARRIER_LOGIN')).'" />
					</div>
					<label>'.$this->l('Your UPS Password').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ups_carrier_password" value="'.Tools::getValue('ups_carrier_password', Configuration::get('UPS_CARRIER_PASSWORD')).'" />
					</div>
					<label>'.$this->l('Your MyUps ID').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ups_carrier_shipper_id" value="'.Tools::getValue('ups_carrier_shipper_id', Configuration::get('UPS_CARRIER_SHIPPER_ID')).'" />
					</div>
					<label>'.$this->l('Your UPS API Key').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ups_carrier_api_key" value="'.Tools::getValue('ups_carrier_api_key', Configuration::get('UPS_CARRIER_API_KEY')).'" />
						<p><a href="https://www.ups.com/upsdeveloperkit" target="_blank">' . $this->l('Please click here to get your UPS API Key.') . '</a></p>
					</div>
					<label>'.$this->l('Pickup Type').' : </label>
						<div class="margin-form">
							<select name="ups_carrier_pickup_type">
								<option value="0">'.$this->l('Select a pickup type ...').'</option>';
								$idpickups = array();
								foreach($this->_pickupTypeList as $kpickup => $vpickup)
								{
									$html .= '<option value="'.$kpickup.'" '.($kpickup == (int)(Configuration::get('UPS_CARRIER_PICKUP_TYPE')) ? 'selected="selected"' : '').'>'.$vpickup.'</option>';
									$idpickups[] = $kpickup;
								}
					$html .= '</select>
					<p>' . $this->l('Choose in pickup type list the one.') . '</p>
					'.(!in_array((int)(Configuration::get('UPS_CARRIER_PICKUP_TYPE')), $idpickups) ? '<div class="warning">'.$this->l('Pickup Type is not set').'</div>' : '').'
					</div>
					<label>'.$this->l('Packaging Type').' : </label>
						<div class="margin-form">
							<select name="ups_carrier_packaging_type">
								<option value="0">'.$this->l('Select a packaging type ...').'</option>';
								$idpackagings = array();
								foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
								{
									$html .= '<option value="'.$kpackaging.'" '.($kpackaging == (int)(Configuration::get('UPS_CARRIER_PACKAGING_TYPE')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
									$idpackagings[] = $kpackaging;
								}
					$html .= '</select>
					<p>' . $this->l('Choose in packaging type list the one.') . '</p>
					'.(!in_array((int)(Configuration::get('UPS_CARRIER_PACKAGING_TYPE')), $idpackagings) ? '<div class="warning">'.$this->l('Packaging Type is not set').'</div>' : '').'
					</div>
					<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
				</form>';
		return $html;
	}

	
	private function _postValidation()
	{
		// Check configuration values
		if (!Configuration::get('PS_SHOP_CODE'))
			$this->_postErrors[]  = $this->l('Shop postal code is not set in Preferences Tab');
		elseif (!Configuration::get('PS_SHOP_COUNTRY_ID'))
			$this->_postErrors[]  = $this->l('Shop country is not set in Preferences Tab');
		elseif (Tools::getValue('ups_carrier_login') == NULL)
			$this->_postErrors[]  = $this->l('Your UPS login is not specified');
		elseif (Tools::getValue('ups_carrier_password') == NULL)
			$this->_postErrors[]  = $this->l('Your UPS password is not specified');
		elseif (Tools::getValue('ups_carrier_shipper_id') == NULL)
			$this->_postErrors[]  = $this->l('Your MyUps ID is not specified');
		elseif (Tools::getValue('ups_carrier_api_key') == NULL)
			$this->_postErrors[]  = $this->l('Your UPS API Key is not specified');
		elseif (Tools::getValue('ups_carrier_pickup_type') == NULL OR Tools::getValue('ups_carrier_pickup_type') == 0)
			$this->_postErrors[]  = $this->l('Your pickup type is not specified');
		elseif (Tools::getValue('ups_carrier_packaging_type') == NULL OR Tools::getValue('ups_carrier_packaging_type') == 0)
			$this->_postErrors[]  = $this->l('Your packaging type is not specified');

		// Check ups webservice availibity
		if (!$this->_postErrors)
		{
			// All new configurations values are saved to be sure to test webservices with it
			Configuration::updateValue('UPS_CARRIER_LOGIN', Tools::getValue('ups_carrier_login'));
			Configuration::updateValue('UPS_CARRIER_PASSWORD', Tools::getValue('ups_carrier_password'));
			Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', Tools::getValue('ups_carrier_shipper_id'));
			Configuration::updateValue('UPS_CARRIER_API_KEY', Tools::getValue('ups_carrier_api_key'));
			Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', Tools::getValue('ups_carrier_pickup_type'));
			Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', Tools::getValue('ups_carrier_packaging_type'));
			if (!$this->webserviceTest())
				$this->_postErrors[]  = $this->l('Prestashop could not connect to UPS webservices, check your API Key');
		}

		// If no errors appear, the carrier is being activated, else, the carrier is being deactivated
		$extCarrier = new Carrier((int)(Configuration::get('UPS_CARRIER_ID')));
		if (!$this->_postErrors)
			$extCarrier->deleted = 0;
		else
			$extCarrier->deleted = 1;
		if (!$extCarrier->update())
			$this->_postErrors[]  = $this->l('An error occurred, please try again.');
	}
	
	private function _postProcess()
	{
		// Saving new configurations
		if (Configuration::updateValue('UPS_CARRIER_LOGIN', Tools::getValue('ups_carrier_login')) AND
			Configuration::updateValue('UPS_CARRIER_PASSWORD', Tools::getValue('ups_carrier_password')) AND
			Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', Tools::getValue('ups_carrier_shipper_id')) AND
			Configuration::updateValue('UPS_CARRIER_API_KEY', Tools::getValue('ups_carrier_api_key')) AND
			Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', Tools::getValue('ups_carrier_pickup_type')) AND
			Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', Tools::getValue('ups_carrier_packaging_type')))
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings failed'));
	}
	
	public function hookupdateCarrier($params)
	{
	}


	/*** Front Methods ***/

	public function getOrderShippingCost($params, $shipping_cost)
	{
		// Init var
		$ups_cost = 0;
		$address = new Address($params->id_address_delivery);
		$cartProducts = $params->getProducts();
		$recipient_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)($address->id_country));
		$recipient_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)($address->id_state));
		$shipper_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)(Configuration::get('PS_SHOP_COUNTRY_ID')));
		$shipper_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)(Configuration::get('PS_SHOP_STATE_ID')));

		// Getting shipping cost for each product
		foreach ($cartProducts as $product)
		{
			if ($product['weight'] < '0.1')
				$product['weight'] = '0.1';
			$upsParams = array(
				'recipient_address1' => $address->address1,
				'recipient_address2' => $address->address2,
				'recipient_postalcode' => $address->postcode,
				'recipient_city' => $address->city,
				'recipient_country_iso' => $recipient_country['iso_code'],
				'recipient_state_iso' => $recipient_state['iso_code'],
				'shipper_address1' => Configuration::get('PS_SHOP_ADDR1'),
				'shipper_address2' => Configuration::get('PS_SHOP_ADDR2'),
				'shipper_postalcode' => Configuration::get('PS_SHOP_CODE'),
				'shipper_city' => Configuration::get('PS_SHOP_CITY'),
				'shipper_country_iso' => $shipper_country['iso_code'],
				'shipper_state_iso' => $shipper_state['iso_code'],
				'pickuptype' => Configuration::get('UPS_CARRIER_PICKUP_TYPE'),
				'width' => $product['width'],
				'height' => $product['height'],
				'depth' => $product['depth'],
				'weight' => $product['weight']
			);

			// If webservice return a cost, we add it, else, we return the original shipping cost
			$result = $this->getUpsShippingCost($upsParams);
			if ($result['connect'] && $result['cost'] > 0)
				$ups_cost += $result['cost'];
			else
				return $shipping_cost;
		}
		
		if ($ups_cost > 0)
			return $ups_cost;
		return $shipping_cost;
	}

	public function getOrderShippingCostExternal($params)
	{
		return 23;
	}


	/*** Webservices Methods ***/

	public function parseXML($valTab)
	{
		$level = 0;
		$levelTab = array();
		$resultTab = array();
		foreach ($valTab as $tmp)
		{
			if ($tmp['level'] > $level)
				$levelTab[] = $tmp['tag'];
			elseif ($tmp['level'] < $level)
				array_pop($levelTab);
			elseif ($tmp['level'] == $level)
			{
				array_pop($levelTab);
				$levelTab[] = $tmp['tag'];
			}
			$level = $tmp['level'];

			if ($tmp['type'] == 'complete' && isset($tmp['value']))
				$this->recurseTab($resultTab, $levelTab, 0, $tmp['value']);
		}
		return $resultTab;
	}

	public function recurseTab(&$resultTab, $levelTab, $index, $value)
	{
		if (isset($levelTab[$index]))
			$this->recurseTab($resultTab[$levelTab[$index]], $levelTab, $index + 1, $value);
		else
			$resultTab = $value;
	}

	public function webserviceTest()
	{
		// Example Params for testing
		$shipper_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)(Configuration::get('PS_SHOP_COUNTRY_ID')));
		$shipper_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)(Configuration::get('PS_SHOP_STATE_ID')));
		$upsParams = array(
			'recipient_address1' => '13450 Farmcrest Ct',
			'recipient_address2' => '',
			'recipient_postalcode' => '20171',
			'recipient_city' => 'Herndon',
			'recipient_country_iso' => 'US',
			'recipient_state_iso' => 'VA',
			'shipper_address1' => Configuration::get('PS_SHOP_ADDR1'),
			'shipper_address2' => Configuration::get('PS_SHOP_ADDR2'),
			'shipper_postalcode' => Configuration::get('PS_SHOP_CODE'),
			'shipper_city' => Configuration::get('PS_SHOP_CITY'),
			'shipper_country_iso' => $shipper_country['iso_code'],
			'shipper_state_iso' => $shipper_state['iso_code'],
			'pickuptype' => Configuration::get('UPS_CARRIER_PICKUP_TYPE'),
			'width' => 10,
			'height' => 3,
			'depth' => 10,
			'weight' => 2.0
		);
	
		// Curl Request
		$xml = $this->getXml($upsParams);
		$ch = curl_init("https://www.ups.com/ups.app/xml/Rate");  
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		curl_setopt($ch,CURLOPT_POST,1);  
		curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);  
		$result = curl_exec($ch);

		// Get xml from Curl Result
		$data = strstr($result, '<?');  
		$xml_parser = xml_parser_create();  
		xml_parse_into_struct($xml_parser, $data, $valTab, $indexTab);  
		xml_parser_free($xml_parser);

		// Parsing XML
		$resultTab = $this->parseXML($valTab);

		// Return results
		// echo '<pre>'.htmlentities(str_replace('><', ">\n<", $xml)).'</pre>';
		// echo '<pre>'; print_r($resultTab); echo '</pre>';
		if ($resultTab['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSDESCRIPTION'] == 'Success')
			return true;
		$this->_postErrors[]  = $resultTab['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['ERROR']['ERRORDESCRIPTION'];
		return false;
	}

	public function getUpsShippingCost($upsParams)
	{
		// Check Arguments
		if (!$upsParams)
			return array('connect' => false, 'cost' => 0);

		// Curl Request
		$xml = $this->getXml($upsParams);
		$ch = curl_init("https://www.ups.com/ups.app/xml/Rate");  
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		curl_setopt($ch,CURLOPT_POST,1);  
		curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);  
		$result = curl_exec($ch);

		// Get xml from Curl Result
		$data = strstr($result, '<?');  
		$xml_parser = xml_parser_create();  
		xml_parse_into_struct($xml_parser, $data, $valTab, $indexTab);  
		xml_parser_free($xml_parser);

		// Parsing XML
		$resultTab = $this->parseXML($valTab);

		// Check currency
		global $cookie;
		$conversionRate = 1;
		if (isset($resultTab['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['CURRENCYCODE']))
		{
			$id_currency_return = Db::getInstance()->getValue('SELECT `id_currency` FROM `'._DB_PREFIX_.'currency` WHERE `iso_code` = \''.pSQL($resultTab['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['CURRENCYCODE']).'\'');
			if ($cookie->id_currency != $id_currency_return)
			{
				$currencyReturn = new Currency((int)$id_currency_return);
				$conversionRate /= $currencyReturn->conversion_rate;
				$currencySelect = new Currency((int)$cookie->id_currency);
				$conversionRate *= $currencySelect->conversion_rate;
			}
		}

		// Return results
		// echo '<pre>'.htmlentities(str_replace('><', ">\n<", $xml)).'</pre>';
		// echo '<pre>'; print_r($resultTab); echo '</pre>';
		if ($resultTab['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSDESCRIPTION'] == 'Success')
			return array('connect' => true, 'cost' => $resultTab['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['MONETARYVALUE'] * $conversionRate);
		$this->_postErrors[]  = $resultTab['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['ERROR']['ERRORDESCRIPTION'];
		return array('connect' => false, 'cost' => 0);
	}

	public function getXml($upsParams = array())
	{		
		$search = array(
			'[[AccessLicenseNumber]]',
			'[[UserId]]',
			'[[Password]]',
			'[[PickupTypeCode]]',
			'[[ShipperNumber]]',
			'[[ShipperAddressLine1]]',
			'[[ShipperAddressLine2]]',
			'[[ShipperCity]]',
			'[[ShipperPostalCode]]',
			'[[ShipperCountryCode]]',
			'[[ShipperStateCode]]',
			'[[ShipToAddressLine1]]',
			'[[ShipToAddressLine2]]',
			'[[ShipToCity]]',
			'[[ShipToPostalCode]]',
			'[[ShipToCountryCode]]',
			'[[ShipToStateCode]]',
			'[[ShipFromAddressLine1]]',
			'[[ShipFromAddressLine2]]',
			'[[ShipFromCity]]',
			'[[ShipFromPostalCode]]',
			'[[ShipFromCountryCode]]',
			'[[ShipFromStateCode]]',
			'[[PackagingTypeCode]]',
			'[[PackageWeight]]',
			'[[WeightUnit]]',
			'[[Width]]',
			'[[Height]]',
			'[[Length]]',
			'[[DimensionUnit]]'
		);

		$replace = array(
			Configuration::get('UPS_CARRIER_API_KEY'),
			Configuration::get('UPS_CARRIER_LOGIN'),
			Configuration::get('UPS_CARRIER_PASSWORD'),
			Configuration::get('UPS_CARRIER_PICKUP_TYPE'),
			Configuration::get('UPS_CARRIER_SHIPPER_ID'),
			$upsParams['shipper_address1'],
			$upsParams['shipper_address2'],
			$upsParams['shipper_city'],
			$upsParams['shipper_postalcode'],
			$upsParams['shipper_country_iso'],
			$upsParams['shipper_state_iso'],
			$upsParams['recipient_address1'],
			$upsParams['recipient_address2'],
			$upsParams['recipient_city'],
			$upsParams['recipient_postalcode'],
			$upsParams['recipient_country_iso'],
			$upsParams['recipient_state_iso'],
			$upsParams['shipper_address1'],
			$upsParams['shipper_address2'],
			$upsParams['shipper_city'],
			$upsParams['shipper_postalcode'],
			$upsParams['shipper_country_iso'],
			$upsParams['shipper_state_iso'],
			Configuration::get('UPS_CARRIER_PACKAGING_TYPE'),
			$upsParams['weight'],
			$this->_weightUnit,
			$upsParams['width'],
			$upsParams['height'],
			$upsParams['depth'],
			$this->_dimensionUnit
		);

		$dir = dirname(__FILE__);
		if (preg_match('/classes/i', $dir))
			$dir .= '/../modules/upscarrier/';
		$xml = @file_get_contents($dir.'/xml.tpl');
		$xml = str_replace($search, $replace, $xml);

		return $xml;
	}

}

?>
