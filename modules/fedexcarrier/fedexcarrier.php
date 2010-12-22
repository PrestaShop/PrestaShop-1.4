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

class FedexCarrier extends CarrierModule
{
	private $_html = '';
	private $_postErrors = array();
	private $_pickupTypeList = array();
	private $_serviceTypeList = array();
	private $_dimensionUnit = '';
	private $_weightUnit = '';
	private $_dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
	private $_weightUnitList = array('KG' => 'KG', 'KGS' => 'KG', 'LB' => 'LB', 'LBS' => 'LB');
	private $_config = array(
		'name' => 'Fedex Carrier',
		'id_tax_rules_group' => 1,
		'active' => true,
		'deleted' => 0,
		'shipping_handling' => false,
		'range_behavior' => 0,
		'is_module' => false,
		'delay' => array('fr'=>'Transporteur Fedex', 'en'=>'Fedex Carrier'),
		'id_zone' => 1,
		'is_module' => true,
		'shipping_external'=> true,
		'external_module_name'=> 'FedexCarrier',
		'need_range' => true
	);


	/*** Construct Method ***/

	public function __construct()
	{
		global $cookie;
		
		$this->name = 'fedexcarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '0.3';

		parent::__construct ();

		$this->displayName = $this->l('Fedex Carrier');
		$this->description = $this->l('Offer to your customers, different delivery methods with Fedex');

		if (self::isInstalled($this->name))
		{
			$ids = array();

			// Loading Carriers
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriers as $carrier)
				$ids[] = $carrier['id_carrier'];
			$warning = array();

			// Check configuration values
			if (!Configuration::get('FEDEX_CARRIER_ACCOUNT'))
				$warning[] = $this->l('\'Fedex account\'').' ';
			if (!Configuration::get('FEDEX_CARRIER_METER'))
				$warning[] = $this->l('\'Fedex meter number\'').' ';
			if (!Configuration::get('FEDEX_CARRIER_PASSWORD'))
				$warning[] = $this->l('\'Fedex password\'').' ';
			if (!Configuration::get('FEDEX_CARRIER_API_KEY'))
				$warning[] = $this->l('\'Fedex API Key\'').' ';
			if (!Configuration::get('FEDEX_CARRIER_PICKUP_TYPE'))
				$warning[] = $this->l('\'Fedex default pickup type\'').' ';

			// Check shop configuration
			if (!Configuration::get('PS_SHOP_CODE'))
				$warning[] = $this->l('\'Shop postal code (in Preferences Tab).\'').' ';
			if (!Configuration::get('PS_SHOP_COUNTRY_ID'))
				$warning[] = $this->l('\'Shop country (in Preferences Tab).\'').' ';

			// Check webservice fedex availibility
			if (!$this->webserviceTest())
				$warning[] = $this->l('Could not connect to Fedex Webservices, check your API Key').', ';

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
				'BUSINESS_SERVICE_CENTER' => $this->l('Business service center'),
				'DROP_BOX' => $this->l('Drop box'),
				'REGULAR_PICKUP' => $this->l('Regular pickup'),
				'REQUEST_COURIER' => $this->l('Request courier'),
				'STATION' => $this->l('Station')
			);

			// Loading service type list
			$this->_serviceTypeList = array(
				'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => $this->l('Europe first international priority'),
				'FEDEX_1_DAY_FREIGHT' => $this->l('Fedex 1 day freight'),
				'FEDEX_2_DAY' => $this->l('Fedex 2 day'),
				'FEDEX_2_DAY_FREIGHT' => $this->l('Fedex 2 day freight'),
				'FEDEX_3_DAY_FREIGHT' => $this->l('Fedex 3 day freight'),
				'FEDEX_EXPRESS_SAVER' => $this->l('Fedex express saver'),
				'FEDEX_FREIGHT' => $this->l('Fedex freight'),
				'FEDEX_GROUND' => $this->l('Fedex ground'),
				'FEDEX_NATIONAL_FREIGHT' => $this->l('Fedex national freight'),
				'FIRST_OVERNIGHT' => $this->l('First overnight'),
				'GROUND_HOME_DELIVERY' => $this->l('Ground home delivery'),
				'INTERNATIONAL_ECONOMY' => $this->l('International economy'),
				'INTERNATIONAL_ECONOMY_FREIGHT' => $this->l('International economy freight'),
				'INTERNATIONAL_FIRST' => $this->l('International first'),
				'INTERNATIONAL_GROUND' => $this->l('International ground'),
				'INTERNATIONAL_PRIORITY' => $this->l('International priority'),
				'INTERNATIONAL_PRIORITY_FREIGHT' => $this->l('International priority freight'),
				'PRIORITY_OVERNIGHT' => $this->l('Priority overnight'),
				'SMART_POST' => $this->l('Smart post'),
				'STANDARD_OVERNIGHT' => $this->l('Standard overnight')
			);
		}
	}


	/*** Install / Uninstall Methods ***/

	public function install()
	{
		global $cookie;
		
		// Install carrier and config values
		if (!parent::install() OR
			!Configuration::updateValue('FEDEX_CARRIER_ACCOUNT', '') OR
			!Configuration::updateValue('FEDEX_CARRIER_METER', '') OR
			!Configuration::updateValue('FEDEX_CARRIER_PASSWORD', '') OR
			!Configuration::updateValue('FEDEX_CARRIER_API_KEY', '') OR
			!Configuration::updateValue('FEDEX_CARRIER_PICKUP_TYPE', '') OR
			!$this->registerHook('updateCarrier'))
			return false;

		// Create cache table in database
		$sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fedex_cache` (
				  `id_fedex_cache` int(10) NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) NOT NULL,
				  `id_category` int(10) NOT NULL,
				  `pickup_type_code` varchar(64) NOT NULL,
				  `total_charges` double(10,2) NOT NULL,
				  `currency_code` varchar(3) NOT NULL,
				  `date_add` datetime NOT NULL,
				  `date_upd` datetime NOT NULL,
				  PRIMARY KEY  (`id_fedex_cache`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		// Create cache table in database
		$sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fedex_config` (
				  `id_fedex_config` int(10) NOT NULL AUTO_INCREMENT,
				  `id_product` int(10) NOT NULL,
				  `id_category` int(10) NOT NULL,
				  `pickup_type_code` varchar(64) NOT NULL,
				  `additionnal_charges` double(6,2) NOT NULL,
				  `total_charges` double(10,2) NOT NULL,
				  `currency_code` varchar(3) NOT NULL,
				  `date_add` datetime NOT NULL,
				  `date_upd` datetime NOT NULL,
				  PRIMARY KEY  (`id_fedex_config`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		// Executing sql
		if (!Db::getInstance()->Execute($sql1) OR !Db::getInstance()->Execute($sql2))
			return false;

		// If already installed in the past the carrier is being reactivated, else the carrier is being created
		if (Configuration::get('FEDEX_CARRIER_ID'))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 0), 'UPDATE', '`id_carrier` = '.(int)(Configuration::get('FEDEX_CARRIER_ID')));
		else if (!$this->createExternalCarrier($this->_config))
			return false;

		return true;
	}

	public function uninstall()
	{
		global $cookie;

		// Delete cache and config tables in database
		$sql1 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fedex_cache`;';
		$sql2 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fedex_config`;';

		// Uninstall Script
		if (!parent::uninstall() OR
			!Configuration::deleteByName('FEDEX_CARRIER_ACCOUNT') OR
			!Configuration::deleteByName('FEDEX_CARRIER_METER') OR
			!Configuration::deleteByName('FEDEX_CARRIER_PASSWORD') OR
			!Configuration::deleteByName('FEDEX_CARRIER_API_KEY') OR
			!Configuration::deleteByName('FEDEX_CARRIER_PICKUP_TYPE') OR
			!Db::getInstance()->Execute($sql1) OR
			!Db::getInstance()->Execute($sql2) OR
			!$this->unregisterHook('updateCarrier'))
			return false;
			
		// Delete External Carrier
		$extCarrier = new Carrier((int)(Configuration::get('FEDEX_CARRIER_ID')));
		
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
			Configuration::updateValue('FEDEX_CARRIER_ID', (int)($carrier->id));

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
		$this->_html .= '<h2>' . $this->l('Fedex Carrier').'</h2>';
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
		$this->l('Fedex Carrier Configurator.').'<br />'.$this->l('On this version, the Fedex module will automatically choose the cheapest delivery service available.').'
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
					<label>'.$this->l('Your Fedex account').' : </label>
					<div class="margin-form"><input type="text" size="20" name="fedex_carrier_account" value="'.Tools::getValue('fedex_carrier_account', Configuration::get('FEDEX_CARRIER_ACCOUNT')).'" /></div>
					<label>'.$this->l('Your Fedex meter number').' : </label>
					<div class="margin-form"><input type="text" size="20" name="fedex_carrier_meter" value="'.Tools::getValue('fedex_carrier_meter', Configuration::get('FEDEX_CARRIER_METER')).'" /></div>
					<label>'.$this->l('Your Fedex password').' : </label>
					<div class="margin-form"><input type="text" size="20" name="fedex_carrier_password" value="'.Tools::getValue('fedex_carrier_password', Configuration::get('FEDEX_CARRIER_PASSWORD')).'" /></div>
					<label>'.$this->l('Your Fedex API Key').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="fedex_carrier_api_key" value="'.Tools::getValue('fedex_carrier_api_key', Configuration::get('FEDEX_CARRIER_API_KEY')).'" />
						<p><a href="http://fedex.com/us/developer/index.html" target="_blank">' . $this->l('Please click here to get your Fedex API Key.') . '</a></p>
					</div>
					<label>'.$this->l('Default pickup type').' : </label>
					<div class="margin-form">
						<select name="fedex_carrier_pickup_type">
							<option value="0">'.$this->l('Select a default pickup type ...').'</option>';
							$idpickups = array();
							foreach($this->_pickupTypeList as $kpickup => $vpickup)
							{
								$html .= '<option value="'.$kpickup.'" '.($kpickup == pSQL(Configuration::get('FEDEX_CARRIER_PICKUP_TYPE')) ? 'selected="selected"' : '').'>'.$vpickup.'</option>';
								$idpickups[] = $kpickup;
							}
						$html .= '</select>
					<p>' . $this->l('Choose in pickup type list the default one.') . '</p>
					'.(!in_array((int)(Configuration::get('FEDEX_CARRIER_PICKUP_TYPE')), $idpickups) ? '<div class="warning">'.$this->l('Default pickup type is not set').'</div>' : '').'
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
		elseif (!$this->_weightUnit)
			$this->_postErrors[]  = $this->l('Weight Unit must be LB or KG (in Preferences > Localization Tab).').' ';
		elseif (!$this->_dimensionUnit)
			$this->_postErrors[]  = $this->l('Dimension Unit must be CM or IN (in Preferences > Localization Tab).').' ';
		elseif (Tools::getValue('fedex_carrier_account') == NULL)
			$this->_postErrors[]  = $this->l('Your Fedex account is not specified');
		elseif (Tools::getValue('fedex_carrier_meter') == NULL)
			$this->_postErrors[]  = $this->l('Your Fedex meter is not specified');
		elseif (Tools::getValue('fedex_carrier_password') == NULL)
			$this->_postErrors[]  = $this->l('Your Fedex password is not specified');
		elseif (Tools::getValue('fedex_carrier_api_key') == NULL)
			$this->_postErrors[]  = $this->l('Your Fedex API Key is not specified');
		elseif (Tools::getValue('fedex_carrier_pickup_type') == NULL OR Tools::getValue('fedex_carrier_pickup_type') == '0')
			$this->_postErrors[]  = $this->l('Your pickup type is not specified');

		// Check fedex webservice availibity
		if (!$this->_postErrors)
		{
			// All new configurations values are saved to be sure to test webservices with it
			Configuration::updateValue('FEDEX_CARRIER_ACCOUNT', Tools::getValue('fedex_carrier_account'));
			Configuration::updateValue('FEDEX_CARRIER_METER', Tools::getValue('fedex_carrier_meter'));
			Configuration::updateValue('FEDEX_CARRIER_PASSWORD', Tools::getValue('fedex_carrier_password'));
			Configuration::updateValue('FEDEX_CARRIER_API_KEY', Tools::getValue('fedex_carrier_api_key'));
			Configuration::updateValue('FEDEX_CARRIER_PICKUP_TYPE', Tools::getValue('fedex_carrier_pickup_type'));
			if (!$this->webserviceTest())
				$this->_postErrors[]  = $this->l('Prestashop could not connect to Fedex webservice, check your API Key');
		}

		// If no errors appear, the carrier is being activated, else, the carrier is being deactivated
		$extCarrier = new Carrier((int)(Configuration::get('FEDEX_CARRIER_ID')));
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
		if (Configuration::updateValue('FEDEX_CARRIER_ACCOUNT', Tools::getValue('fedex_carrier_account')) AND
			Configuration::updateValue('FEDEX_CARRIER_METER', Tools::getValue('fedex_carrier_meter')) AND
			Configuration::updateValue('FEDEX_CARRIER_PASSWORD', Tools::getValue('fedex_carrier_password')) AND
			Configuration::updateValue('FEDEX_CARRIER_API_KEY', Tools::getValue('fedex_carrier_api_key')) AND
			Configuration::updateValue('FEDEX_CARRIER_PICKUP_TYPE', Tools::getValue('fedex_carrier_pickup_type')))
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
		$fedex_cost = 0;
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
			$fedexParams = array(
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
				'pickuptype' => Configuration::get('FEDEX_CARRIER_PICKUP_TYPE'),
				'width' => $product['width'],
				'height' => $product['height'],
				'depth' => $product['depth'],
				'weight' => $product['weight']
			);

			// If webservice return a cost, we add it, else, we return the original shipping cost
			$result = $this->getFedexShippingCost($fedexParams);
			if ($result['connect'] && $result['cost'] > 0)
				$fedex_cost += $result['cost'];
			else
				return $shipping_cost;
		}

		if ($fedex_cost > 0)
			return $fedex_cost;
		return $shipping_cost;
	}

	public function getOrderShippingCostExternal($params)
	{
		return 23;
	}


	/*** Webservices Methods ***/

	public function webserviceTest()
	{
		// Getting module directory
		$dir = dirname(__FILE__);
		if (preg_match('/classes/i', $dir))
			$dir .= '/../modules/fedexcarrier/';

		// Enable Php Soap
		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient($dir.'/RateService_v9.wsdl', array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

		// Generating soap request
		$request['WebAuthenticationDetail']['UserCredential'] = array('Key' => Configuration::get('FEDEX_CARRIER_API_KEY'), 'Password' => Configuration::get('FEDEX_CARRIER_PASSWORD')); 
		$request['ClientDetail'] = array('AccountNumber' => Configuration::get('FEDEX_CARRIER_ACCOUNT'), 'MeterNumber' => Configuration::get('FEDEX_CARRIER_METER'));
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Available Services Request v9 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '9', 'Intermediate' => '0', 'Minor' => '0');
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = Configuration::get('FEDEX_CARRIER_PICKUP_TYPE'); // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');

		// Service Type and Packaging Type are not passed in the request
		$request['RequestedShipment']['Shipper']['Address'] = array('StreetLines' => array('10 Fed Ex Pkwy'), 'City' => 'Memphis', 'StateOrProvinceCode' => 'TN', 'PostalCode' => '38115', 'CountryCode' => 'US');
		$request['RequestedShipment']['Recipient']['Address'] = array('StreetLines' => array('13450 Farmcrest Ct'), 'City' => 'Herndon', 'StateOrProvinceCode' => 'VA', 'PostalCode' => '20171', 'CountryCode' => 'US');
		$request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER', 'Payor' => array('AccountNumber' => Configuration::get('FEDEX_CARRIER_ACCOUNT'), 'CountryCode' => 'US'));
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$request['RequestedShipment']['PackageCount'] = '2';
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
		$request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => 2.0, 'Units' => 'LB'), 'Dimensions' => array('Length' => 10, 'Width' => 10, 'Height' => 3, 'Units' => 'IN')));

		// Webservice authentication test
		$result = $client->getRates($request);
        if ($result->HighestSeverity != 'SUCCESS')
			return false;

		return true;
	}

	public function webservicePrintResult($result)
	{
		if ($result->HighestSeverity != 'FAILURE' && $result->HighestSeverity != 'ERROR')
		{
			echo '<br /><br />--------------------------------<br /><br />Rates for following service type(s) were returned.';
			echo '<table border="1">';
			echo '<tr><td>Service Type</td><td>Amount</td><td>Delivery Date</td>';
			foreach ($result->RateReplyDetails as $rateReply)
			{           
				echo '<tr>';
				$serviceType = '<td>'.$rateReply -> ServiceType . '</td>';
				$amount = '<td>$' . number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",") . '</td>';
				if(array_key_exists('DeliveryTimestamp',$rateReply)){
					$deliveryDate= '<td>' . $rateReply->DeliveryTimestamp . '</td>';
				}else{
					$deliveryDate= '<td>' . $rateReply->TransitTime . '</td>';
				}
				echo $serviceType . $amount. $deliveryDate;
				echo '</tr>';
			}
			echo '</table><br />';
		}
	}

	public function getFedexShippingCost($fedexParams)
	{
		// Check Arguments
		if (!$fedexParams)
			return array('connect' => false, 'cost' => 0);

		// Check currency
		global $cookie;
		$conversionRate = 1;
		if ($cookie->id_currency != '2')
		{
			$currencyDollar = new Currency(2);
			$conversionRate /= $currencyDollar->conversion_rate;
			$currencySelect = new Currency((int)$cookie->id_currency);
			$conversionRate *= $currencySelect->conversion_rate;
		}

		// Getting module directory
		$dir = dirname(__FILE__);
		if (preg_match('/classes/i', $dir))
			$dir .= '/../modules/fedexcarrier/';
	
		// Enable Php Soap
		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient($dir.'/RateService_v9.wsdl', array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

		// Generating soap request
		$request['WebAuthenticationDetail']['UserCredential'] = array('Key' => Configuration::get('FEDEX_CARRIER_API_KEY'), 'Password' => Configuration::get('FEDEX_CARRIER_PASSWORD')); 
		$request['ClientDetail'] = array('AccountNumber' => Configuration::get('FEDEX_CARRIER_ACCOUNT'), 'MeterNumber' => Configuration::get('FEDEX_CARRIER_METER'));
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Available Services Request v9 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '9', 'Intermediate' => '0', 'Minor' => '0');
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = $fedexParams['pickuptype']; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');

		// Service Type and Packaging Type are not passed in the request
		$request['RequestedShipment']['Shipper']['Address'] = array(
			'StreetLines' => array($fedexParams['shipper_address1']),
			'City' => $fedexParams['shipper_city'],
			'StateOrProvinceCode' => $fedexParams['shipper_state_iso'],
			'PostalCode' => $fedexParams['shipper_postalcode'],
			'CountryCode' => $fedexParams['shipper_country_iso']
		);
		$request['RequestedShipment']['Recipient']['Address'] = array(
			'StreetLines' => array($fedexParams['recipient_address1']),
			'City' => $fedexParams['recipient_city'],
			'StateOrProvinceCode' => $fedexParams['recipient_state_iso'],
			'PostalCode' => $fedexParams['recipient_postalcode'],
			'CountryCode' => $fedexParams['recipient_country_iso']
		);
		$request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER', 'Payor' => array('AccountNumber' => Configuration::get('FEDEX_CARRIER_ACCOUNT'), 'CountryCode' => 'US'));
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$request['RequestedShipment']['PackageCount'] = '2';
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
		$request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array(
			'Weight' => array('Value' => $fedexParams['weight'], 'Units' => $this->_weightUnit),
			'Dimensions' => array('Length' => $fedexParams['depth'], 'Width' => $fedexParams['width'], 'Height' => $fedexParams['height'], 'Units' => $this->_dimensionUnit)
		));

		// Webservice request
		$result = $client->getRates($request);
		//$this->webservicePrintResult($result);
        if ($result->HighestSeverity != 'SUCCESS')
			return false;

		// Return the cheaper transport
		$amountReturn = 0;
		foreach ($result->RateReplyDetails as $rateReply)
			if ($amountReturn == 0 || $amountReturn > number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount, 2, ".", ","))
				$amountReturn = number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount, 2, ".", ",");
		return array('connect' => true, 'cost' => $amountReturn * $conversionRate);
	}
	
}

?>
