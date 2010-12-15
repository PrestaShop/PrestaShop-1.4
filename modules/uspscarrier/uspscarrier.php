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

class UspsCarrier extends CarrierModule
{
	private $_html = '';
	private $_postErrors = array();
	private $_packagingSizeTypeList = array();
	private $_packagingTypeList = array();
	private $_serviceTypeList = array();
	private $_machinableList = array();
	private $_dimensionUnit = '';
	private $_weightUnit = '';
	private $_dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
	private $_weightUnitList = array('KG' => 'KGS', 'KGS' => 'KGS', 'LB' => 'LBS', 'LBS' => 'LBS');
	private $_config = array(
		'name' => 'USPS Carrier',
		'id_tax' => 0,
		'active' => true,
		'deleted' => 0,
		'shipping_handling' => false,
		'range_behavior' => 0,
		'is_module' => false,
		'delay' => array('fr' => 'Transporteur USPS', 'en' => 'USPS Carrier'),
		'id_zone' => 1,
		'is_module' => true,
		'shipping_external' => true,
		'external_module_name' => 'UspsCarrier',
		'need_range' => true
		);


	/*** Construct Method ***/

	public function __construct()
	{
		global $cookie;
		
		$this->name = 'uspscarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '0.3';

		parent::__construct ();

		$this->displayName = $this->l('USPS Carrier');
		$this->description = $this->l('Offer to your customers, different delivery methods with USPS');

		if (self::isInstalled($this->name))
		{
			$ids = array();

			// Loading Carriers
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriers as $carrier)
				$ids[] = $carrier['id_carrier'];
			$warning = array();

			// Check configuration values
			if (!Configuration::get('USPS_CARRIER_USER_ID'))
				$warning[] = $this->l('\'USPS User ID\'').' ';
			if (!Configuration::get('USPS_CARRIER_PACKAGING_SIZE_TYPE'))
				$warning[] = $this->l('\'USPS Packaging Size Type\'').' ';
			if (!Configuration::get('USPS_CARRIER_PACKAGING_TYPE'))
				$warning[] = $this->l('\'USPS Packaging Type\'').' ';
			if (!Configuration::get('USPS_CARRIER_SERVICE_TYPE'))
				$warning[] = $this->l('\'USPS Service Type\'').' ';
			if (!Configuration::get('USPS_CARRIER_MACHINABLE'))
				$warning[] = $this->l('\'USPS Machinable\'').' ';
				
			// Check shop configuration
			if (!Configuration::get('PS_SHOP_CODE'))
				$warning[] = $this->l('\'Shop postal code (in Preferences Tab).\'').' ';
			if (!Configuration::get('PS_SHOP_COUNTRY_ID'))
				$warning[] = $this->l('\'Shop country (in Preferences Tab).\'').' ';

			// Check webservice usps availibility
			if (!$this->webserviceTest())
				$warning[] = $this->l('Could not connect to USPS Webservices, check your API Key').', ';

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

			// Loading packaging size type list
			$this->_packagingSizeTypeList = array(
				'REGULAR' => $this->l('Regular'),
				'LARGE' => $this->l('Large'),
				'OVERSIZE' => $this->l('Oversize')
			);

			// Loading packaging type list
			$this->_packagingTypeList = array(
				'VARIABLE' => $this->l('Variable'),
				'FLAT RATE BOX' => $this->l('Flat rate box'),
				'MD FLAT RATE BOX' => $this->l('MD flat rate box'),
				'FLAT RATE ENVELOPE' => $this->l('Flat rate envelope'),
				'SM FLAT RATE BOX' => $this->l('SM flat rate box'),
				'LG FLAT RATE BOX' => $this->l('LG flat rate box'),
				'RECTANGULAR' => $this->l('Rectangular'),
				'NONRECTANGULAR' => $this->l('Non rectangular')
			);

			// Loading service type list
			$this->_serviceTypeList = array(
				'FIRST CLASS' => $this->l('First Class'),
				'PRIORITY' => $this->l('Priority'),
				'PRIORITY COMMERCIAL' => $this->l('Priority Commercial'),
				'EXPRESS' => $this->l('Express'),
				'EXPRESS COMMERCIAL' => $this->l('Express Commercial'),
				'EXPRESS SH' => $this->l('Express SH'),
				'EXPRESS SH COMMERCIAL' => $this->l('Express SH Commercial'),
				'EXPRESS HFP' => $this->l('Express HFP'),
				'EXPRESS HFP COMMERCIAL' => $this->l('Express HFP Commercial'),
				'BPM' => $this->l('BPM'),
				'PARCEL' => $this->l('Parcel'),
				'MEDIA' => $this->l('Media'),
				'LIBRARY' => $this->l('Library'),
				'ALL' => $this->l('All'),
				'ONLINE  ' => $this->l('Online')
			);

			// Loading machinable list
			$this->_machinableList = array(
				'FALSE' => $this->l('False'),
				'TRUE' => $this->l('True')
			);
		}
	}


	/*** Install / Uninstall Methods ***/

	public function install()
	{
		global $cookie;

		// Install carrier and config values
		if (!parent::install() OR
			!Configuration::updateValue('USPS_CARRIER_USER_ID', '') OR
			!Configuration::updateValue('USPS_CARRIER_PACKAGING_SIZE_TYPE', 'REGULAR') OR
			!Configuration::updateValue('USPS_CARRIER_PACKAGING_TYPE', 'VARIABLE') OR
			!Configuration::updateValue('USPS_CARRIER_SERVICE_TYPE', 'PRIORITY') OR
			!Configuration::updateValue('USPS_CARRIER_MACHINABLE', 'TRUE') OR
			!$this->registerHook('updateCarrier'))
			return false;

		// Create cache table in database
		$sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'usps_cache` (
				  `id_usps_cache` int(10) NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) NOT NULL,
				  `id_category` int(10) NOT NULL,
				  `total_charges` double(10,2) NOT NULL,
				  `currency_code` varchar(3) NOT NULL,
				  `date_add` datetime NOT NULL,
				  `date_upd` datetime NOT NULL,
				  PRIMARY KEY  (`id_usps_cache`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		// Create cache table in database
		$sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'usps_config` (
				  `id_usps_config` int(10) NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) NOT NULL,
				  `id_category` int(10) NOT NULL,
				  `additionnal_charges` double(6,2) NOT NULL,
				  `total_charges` double(10,2) NOT NULL,
				  `currency_code` varchar(3) NOT NULL,
				  `date_add` datetime NOT NULL,
				  `date_upd` datetime NOT NULL,
				  PRIMARY KEY  (`id_usps_config`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		// Executing sql
		if (!Db::getInstance()->Execute($sql1) OR !Db::getInstance()->Execute($sql2))
			return false;

		// If already installed in the past the carrier is being reactivated, else the carrier is being created
		if (Configuration::get('USPS_CARRIER_ID'))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 0), 'UPDATE', '`id_carrier` = '.(int)(Configuration::get('USPS_CARRIER_ID')));
		else if (!$this->createExternalCarrier($this->_config))
			return false;

		return true; 					
	}
	
	public function uninstall()
	{
		global $cookie;

		// Delete cache and config tables in database
		$sql1 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'usps_cache`;';
		$sql2 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'usps_config`;';

		// Uninstall Script
		if (!parent::uninstall() OR
			!Configuration::deleteByName('USPS_CARRIER_USER_ID') OR
			!Configuration::deleteByName('USPS_CARRIER_PACKAGING_SIZE_TYPE') OR
			!Configuration::deleteByName('USPS_CARRIER_PACKAGING_TYPE') OR
			!Configuration::deleteByName('USPS_CARRIER_SERVICE_TYPE') OR
			!Configuration::deleteByName('USPS_CARRIER_MACHINABLE') OR
			!Db::getInstance()->Execute($sql1) OR
			!Db::getInstance()->Execute($sql2) OR
			!$this->unregisterHook('updateCarrier'))
			return false;
		
		// Delete External Carrier
		$extCarrier = new Carrier((int)(Configuration::get('USPS_CARRIER_ID')));

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
			Configuration::updateValue('USPS_CARRIER_ID', (int)($carrier->id));

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
		$this->_html .= '<h2>' . $this->l('USPS Carrier').'</h2>';
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
		$this->l('USPS Carrier Configurator.').'
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
					<label>'.$this->l('Your USPS User ID').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="usps_carrier_user_id" value="'.Tools::getValue('usps_carrier_user_id', Configuration::get('USPS_CARRIER_USER_ID')).'" />
					</div>
					<label>'.$this->l('Packaging Size Type').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_packaging_size_type">
								<option value="0">'.$this->l('Select a packaging size type ...').'</option>';
								$idpackagingsizes = array();
								foreach($this->_packagingSizeTypeList as $kpackagingsize => $vpackagingsize)
								{
									$html .= '<option value="'.$kpackagingsize.'" '.($kpackagingsize == (Configuration::get('USPS_CARRIER_PACKAGING_SIZE_TYPE')) ? 'selected="selected"' : '').'>'.$vpackagingsize.'</option>';
									$idpackagingsizes[] = $kpackagingsize;
								}
					$html .= '</select>
					<p>' . $this->l('Choose in packaging size type list the one.') . '</p>
					'.(!in_array((int)(Configuration::get('USPS_CARRIER_PACKAGING_SIZE_TYPE')), $idpackagingsizes) ? '<div class="warning">'.$this->l('Packaging Size Type is not set').'</div>' : '').'
					</div>
					<label>'.$this->l('Packaging Type').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_packaging_type">
								<option value="0">'.$this->l('Select a packaging type ...').'</option>';
								$idpackagings = array();
								foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
								{
									$html .= '<option value="'.$kpackaging.'" '.($kpackaging == (Configuration::get('USPS_CARRIER_PACKAGING_TYPE')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
									$idpackagings[] = $kpackaging;
								}
					$html .= '</select>
					<p>' . $this->l('Choose in packaging type list the one.') . '</p>
					'.(!in_array((int)(Configuration::get('USPS_CARRIER_PACKAGING_TYPE')), $idpackagings) ? '<div class="warning">'.$this->l('Packaging Type is not set').'</div>' : '').'
					</div>
					<label>'.$this->l('Service Type').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_service_type">
								<option value="0">'.$this->l('Select a service type ...').'</option>';
								$idservices = array();
								foreach($this->_serviceTypeList as $kservice => $vservice)
								{
									$html .= '<option value="'.$kservice.'" '.($kservice == (Configuration::get('USPS_CARRIER_SERVICE_TYPE')) ? 'selected="selected"' : '').'>'.$vservice.'</option>';
									$idservices[] = $kservice;
								}
					$html .= '</select>
					<p>' . $this->l('Choose in service type list the one.') . '</p>
					'.(!in_array((int)(Configuration::get('USPS_CARRIER_SERVICE_TYPE')), $idservices) ? '<div class="warning">'.$this->l('Service Type is not set').'</div>' : '').'
					</div>
					<label>'.$this->l('Machinable').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_machinable">';
								$idmachinables = array();
								foreach($this->_machinableList as $kmachinable => $vmachinable)
								{
									$html .= '<option value="'.$kmachinable.'" '.($kmachinable == (int)(Configuration::get('USPS_CARRIER_MACHINABLE')) ? 'selected="selected"' : '').'>'.$vmachinable.'</option>';
									$idmachinables[] = $kmachinable;
								}
					$html .= '</select>
					'.(!in_array((int)(Configuration::get('USPS_CARRIER_MACHINABLE')), $idservices) ? '<div class="warning">'.$this->l('Machinable is not set').'</div>' : '').'
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
		elseif (Tools::getValue('usps_carrier_user_id') == NULL)
			$this->_postErrors[]  = $this->l('Your USPS user ID is not specified');
		elseif (Tools::getValue('usps_carrier_packaging_size_type') == NULL OR Tools::getValue('usps_carrier_packaging_size_type') == '0')
			$this->_postErrors[]  = $this->l('Your packaging size type is not specified');
		elseif (Tools::getValue('usps_carrier_packaging_type') == NULL OR Tools::getValue('usps_carrier_packaging_type') == '0')
			$this->_postErrors[]  = $this->l('Your packaging type is not specified');
		elseif (Tools::getValue('usps_carrier_service_type') == NULL OR Tools::getValue('usps_carrier_service_type') == '0')
			$this->_postErrors[]  = $this->l('Your service type is not specified');
		elseif (Tools::getValue('usps_carrier_machinable') == NULL OR Tools::getValue('usps_carrier_machinable') == '0')
			$this->_postErrors[]  = $this->l('Your machinable field is not set');

		// Check usps webservice availibity
		if (!$this->_postErrors)
		{
			// All new configurations values are saved to be sure to test webservices with it
			Configuration::updateValue('USPS_CARRIER_USER_ID', Tools::getValue('usps_carrier_user_id'));
			Configuration::updateValue('USPS_CARRIER_PACKAGING_SIZE_TYPE', Tools::getValue('usps_carrier_packaging_size_type'));
			Configuration::updateValue('USPS_CARRIER_PACKAGING_TYPE', Tools::getValue('usps_carrier_packaging_type'));
			Configuration::updateValue('USPS_CARRIER_SERVICE_TYPE', Tools::getValue('usps_carrier_service_type'));
			Configuration::updateValue('USPS_CARRIER_MACHINABLE', Tools::getValue('usps_carrier_machinable'));
			if (!$this->webserviceTest())
				$this->_postErrors[]  = $this->l('Prestashop could not connect to USPS webservices, check your API Key');
		}

		// If no errors appear, the carrier is being activated, else, the carrier is being deactivated
		$extCarrier = new Carrier((int)(Configuration::get('USPS_CARRIER_ID')));
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
		if (Configuration::updateValue('USPS_CARRIER_USER_ID', Tools::getValue('usps_carrier_user_id')) AND
			Configuration::updateValue('USPS_CARRIER_PACKAGING_SIZE_TYPE', Tools::getValue('usps_carrier_packaging_size_type')) AND
			Configuration::updateValue('USPS_CARRIER_PACKAGING_TYPE', Tools::getValue('usps_carrier_packaging_type')) AND
			Configuration::updateValue('USPS_CARRIER_SERVICE_TYPE', Tools::getValue('usps_carrier_service_type')) AND
			Configuration::updateValue('USPS_CARRIER_MACHINABLE', Tools::getValue('usps_carrier_machinable')))
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
		$usps_cost = 0;
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
			$uspsParams = array(
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
				'packagingsizetype' => Configuration::get('USPS_CARRIER_PACKAGING_SIZE_TYPE'),
				'packagingtype' => Configuration::get('USPS_CARRIER_PACKAGING_TYPE'),
				'servicetype' => Configuration::get('USPS_CARRIER_SERVICE_TYPE'),
				'machinable' => Configuration::get('USPS_CARRIER_MACHINABLE'),
				'width' => $product['width'],
				'height' => $product['height'],
				'depth' => $product['depth'],
				'weight' => $product['weight']
			);

			// If webservice return a cost, we add it, else, we return the original shipping cost
			$result = $this->getUspsShippingCost($uspsParams);
			if ($result['connect'] && $result['cost'] > 0)
				$usps_cost += $result['cost'];
			else
				return $shipping_cost;
		}
		
		if ($usps_cost > 0)
			return $usps_cost;
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
		$uspsParams = array(
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
			'packagingsizetype' => Configuration::get('USPS_CARRIER_PACKAGING_SIZE_TYPE'),
			'packagingtype' => Configuration::get('USPS_CARRIER_PACKAGING_TYPE'),
			'servicetype' => Configuration::get('USPS_CARRIER_SERVICE_TYPE'),
			'machinable' => Configuration::get('USPS_CARRIER_MACHINABLE'),
			'width' => 10,
			'height' => 3,
			'depth' => 10,
			'weight' => 2.0
		);
	
		// Curl Request
		$xml = $this->getXml($uspsParams);
		$xml = str_replace(array("\r\n", "\n", "\t"), array('', '', ''), $xml);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://production.shippingapis.com/ShippingAPI.dll');
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'API=RateV3&XML='.$xml);
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
		if (isset($resultTab['RATEV3RESPONSE']['PACKAGE']['POSTAGE']['RATE']))
			return true;
		return false;
	}

	public function getUspsShippingCost($uspsParams)
	{
		// Check Arguments
		if (!$uspsParams)
			return array('connect' => false, 'cost' => 0);

		// Curl Request
		$xml = $this->getXml($uspsParams);
		$xml = str_replace(array("\r\n", "\n", "\t"), array('', '', ''), $xml);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://production.shippingapis.com/ShippingAPI.dll');
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'API=RateV3&XML='.$xml);
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
		if (isset($resultTab['RATEV3RESPONSE']['PACKAGE']['POSTAGE']['RATE']))
		{
			$id_currency_return = 2;
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
		if (isset($resultTab['RATEV3RESPONSE']['PACKAGE']['POSTAGE']['RATE']))
			return array('connect' => true, 'cost' => $resultTab['RATEV3RESPONSE']['PACKAGE']['POSTAGE']['RATE'] * $conversionRate);

		return array('connect' => false, 'cost' => 0);
	}

	public function getXml($uspsParams = array())
	{		
		// KG, LB, OU conversions
		$uspsParams['weight_pounds'] = $uspsParams['weight'];
		if ($this->_weightUnit == 'KG' || $this->_weightUnit == 'KGS')
			$uspsParams['weight_pounds'] = round($uspsParams['weight'] * 2.20462262);
		$uspsParams['weight_ounces'] = $uspsParams['weight_pounds'] * 16;

		// Var assigns
		$search = array(
			'[[USERID]]',
			'[[Service]]',
			'[[ZipOrigination]]',
			'[[ZipDestination]]',
			'[[Pounds]]',
			'[[Ounces]]',
			'[[Container]]',
			'[[Size]]',
			'[[Width]]',
			'[[Height]]',
			'[[Length]]',
			'[[Machinable]]'
		);
		$replace = array(
			Configuration::get('USPS_CARRIER_USER_ID'),
			$uspsParams['servicetype'],
			$uspsParams['shipper_postalcode'],
			$uspsParams['recipient_postalcode'],
			$uspsParams['weight_pounds'],
			$uspsParams['weight_ounces'],
			$uspsParams['packagingtype'],
			$uspsParams['packagingsizetype'],
			$uspsParams['width'],
			$uspsParams['height'],
			$uspsParams['depth'],
			$uspsParams['machinable']
		);

		$dir = dirname(__FILE__);
		if (preg_match('/classes/i', $dir))
			$dir .= '/../modules/uspscarrier/';
		$xml = @file_get_contents($dir.'/xml.tpl');
		$xml = str_replace($search, $replace, $xml);

		return $xml;
	}

}

?>
