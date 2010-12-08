<?php
/*
* Copyright (C) 2007-2010 PrestaShop 
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
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class UpsCarrier extends CarrierModule
{
	private $_html = '';
	private $_postErrors = array();
	private $url = '';
	public $_errors = array();
	public $errorMessage = array();
	
	private $_config = array(
		'name' => 'UPS Carrier',
		'id_tax' => 1,
		'active' => true,
		'deleted' => 0,
		'shipping_handling' => false,
		'range_behavior' => 0,
		'is_module' => false,
		'delay' => array('fr'=>'Transporteur UPS',
						 'en'=>'UPS Carrier'),
		'id_zone' => 1,
		'url' => '',
		'is_module' => true,
		'shipping_external'=> true,
		'external_module_name'=> 'UpsCarrier',
		'need_range' => true
		);
		
	public function __construct()
	{
		global $cookie;
		
		$this->name = 'UpsCarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '0.1';

		parent::__construct ();

		$this->displayName = $this->l('UPS Carrier');
		$this->description = $this->l('Offer to your customers, different delivery methods with UPS');
		$this->url = '';

		if (self::isInstalled($this->name))
		{
			$ids = array();
			
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriers as $carrier)
				$ids[] .= $carrier['id_carrier'];
			$warning = array();
			if (!in_array((int)(Configuration::get('UPS_CARRIER_ID')),$ids))
				$warning[] .= $this->l('\'Need range\'').' ';
			if (!Configuration::get('UPS_CARRIER_LOGIN'))
				$warning[] .= $this->l('\'UPS Login\'').' ';
			if (!Configuration::get('UPS_CARRIER_PASSWORD'))
				$warning[] .= $this->l('\'UPS Password\'').' ';
			if (!Configuration::get('UPS_CARRIER_SHIPPER_ID'))
				$warning[] .= $this->l('\'MyUps ID\'').' ';
			if (!Configuration::get('UPS_CARRIER_API_KEY'))
				$warning[] .= $this->l('\'UPS API Key ID\'').' ';
			else
			{
				$result = $this->getUPSShippingCost();
				if ($result['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSDESCRIPTION'] != 'Success')
					$warning[] .= $this->l('UPS API Key is not working').', ';
			}
			if (!Configuration::get('UPS_CARRIER_PICKUP_TYPE'))
				$warning[] .= $this->l('\'UPS Pickup Type ID\'').' ';
			if (!Configuration::get('UPS_CARRIER_PACKAGING_TYPE'))
				$warning[] .= $this->l('\'UPS Packaging Type ID\'').' ';

			if (!Configuration::get('PS_SHOP_CODE'))
				$warning[] .= $this->l('\'Shop postal code is not set in Preferences Tab.\'').' ';
			if (!Configuration::get('PS_SHOP_COUNTRY'))
				$warning[] .= $this->l('\'Shop country is not set in Preferences Tab.\'').' ';

			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
	}
	
	public function install()
	{
		global $cookie;
		
		if (!parent::install() OR
			!Configuration::updateValue('UPS_CARRIER_LOGIN', '') OR
			!Configuration::updateValue('UPS_CARRIER_PASSWORD', '') OR
			!Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', '') OR
			!Configuration::updateValue('UPS_CARRIER_API_KEY', '') OR
			!Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', '01') OR
			!Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', '02') OR
			!$this->registerHook('updateCarrier'))
			return false;

		if (Configuration::get('UPS_CARRIER_ID'))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 0), 'UPDATE', '`id_carrier` = '.intval(Configuration::get('UPS_CARRIER_ID')));
		else
		{
			// Create cache table in database
			$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ups_cache` (
					  `id_ups_cache` int(10) NOT NULL AUTO_INCREMENT,
					  `pickup_type_code` varchar(2) NOT NULL,
					  `packaging_type_code` varchar(2) NOT NULL,
					  `dimensions_length` double(6,2) NOT NULL,
					  `dimensions_width` double(6,2) NOT NULL,
					  `dimensions_height` double(6,2) NOT NULL,
					  `dimensions_measurement` varchar(2) NOT NULL,
					  `weight` decimal(3,1) NOT NULL,
					  `weight_measurement` varchar(3) NOT NULL,
					  `total_charges` double(10,2) NOT NULL,
					  `currency_code` varchar(3) NOT NULL,
					  `date_add` datetime NOT NULL,
					  `date_upd` datetime NOT NULL,
					  PRIMARY KEY  (`id_ups_cache`)
					) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

			if(!Db::getInstance()->Execute($sql) OR !$this->createExternalCarrier($this->_config))
				return false;
		}

		return true; 					
	}
	
	public function uninstall()
	{
		global $cookie;
				
		if (!parent::uninstall() OR
			!Configuration::deleteByName('UPS_CARRIER_LOGIN') OR
			!Configuration::deleteByName('UPS_CARRIER_PASSWORD') OR
			!Configuration::deleteByName('UPS_CARRIER_SHIPPER_ID') OR
			!Configuration::deleteByName('UPS_CARRIER_API_KEY') OR
			!Configuration::deleteByName('UPS_CARRIER_PICKUP_TYPE') OR
			!Configuration::deleteByName('UPS_CARRIER_PACKAGING_TYPE') OR
			!$this->unregisterHook('updateCarrier'))
			return false;
		
		//Delete External Carrier
		$extCarrier = new Carrier(intval(Configuration::get('UPS_CARRIER_ID')));
		//if external carrier is default set other one as default
		
		if(Configuration::get('PS_CARRIER_DEFAULT') == (int)($extCarrier->id))
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
		print_r($config);
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax = $config['id_tax'];
		$carrier->id_zone = $config['id_zone'];
		$carrier->url = $config['url'];
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
		
		if($carrier->add())
		{				
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
			{
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.(int)($carrier->id).'\',\''.(int)($group['id_group']).'\')');
			}
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
			
			Configuration::updateValue('UPS_CARRIER_ID', (int)($carrier->id));

			//copy logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			return true;
		}
		else
			return false;
	}

	
	
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

		$pickupTypeList = array(
			'01' => $this->l('Daily Pickup'),
			'03' => $this->l('Customer Counter'),
			'06' => $this->l('One Time Pickup'),
			'07' => $this->l('On Call Air'),
			'11' => $this->l('Suggested Retail Rates'),
			'19' => $this->l('Letter Center'),
			'20' => $this->l('Air Service Center'),
		);

		$packagingTypeList = array(
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
		
		$this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" class="form">
		<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Description').'</legend>'.
		$this->l('UPS Carrier Configurator.').'
		</fieldset>
		<div class="clear">&nbsp;</div>
				
		<fieldset>
			<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Settings').'</legend>
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
				</div>
				<label>'.$this->l('Pickup Type').' : </label>
					<div class="margin-form">
						<select name="ups_carrier_pickup_type">
							<option value="0">'.$this->l('Select a pickup type ...').'</option>';
							$idpickups = array();
							foreach($pickupTypeList as $kpickup => $vpickup)
							{
								$this->_html .= '<option value="'.$kpickup.'" '.($kpickup == (int)(Configuration::get('UPS_CARRIER_PICKUP_TYPE')) ? 'selected="selected"' : '').'>'.$vpickup.'</option>';
								$idpickups[] .= $kpickup;
							}
				$this->_html .= '</select>
				<p>' . $this->l('Choose in pickup type list the one.') . '</p>
				'.(!in_array((int)(Configuration::get('UPS_CARRIER_PICKUP_TYPE')), $idpickups) ? '<div class="warning">'.$this->l('Pickup Type is not set').'</div>' : '').'
				</div>
				<label>'.$this->l('Packaging Type').' : </label>
					<div class="margin-form">
						<select name="ups_carrier_packaging_type">
							<option value="0">'.$this->l('Select a packaging type ...').'</option>';
							$idpackagings = array();
							foreach($packagingTypeList as $kpackaging => $vpackaging)
							{
								$this->_html .= '<option value="'.$kpackaging.'" '.($kpackaging == (int)(Configuration::get('UPS_CARRIER_PACKAGING_TYPE')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
								$idpackagings[] .= $kpackaging;
							}
				$this->_html .= '</select>
				<p>' . $this->l('Choose in packaging type list the one.') . '</p>
				'.(!in_array((int)(Configuration::get('UPS_CARRIER_PACKAGING_TYPE')), $idpackagings) ? '<div class="warning">'.$this->l('Packaging Type is not set').'</div>' : '').'
				</div>
				<div class="margin-form">
					<input class="button" name="submitSave" type="submit">
				</div>
				</fieldset>';
	}
	
	private function _postValidation()
	{
		if (!Configuration::get('PS_SHOP_CODE'))
			$this->_postErrors[]  = $this->l('Shop postal code is not set in Preferences Tab');
		elseif (!Configuration::get('PS_SHOP_COUNTRY'))
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

		if (!$this->_postErrors)
		{
			Configuration::updateValue('UPS_CARRIER_LOGIN', Tools::getValue('ups_carrier_login'));
			Configuration::updateValue('UPS_CARRIER_PASSWORD', Tools::getValue('ups_carrier_password'));
			Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', Tools::getValue('ups_carrier_shipper_id'));
			Configuration::updateValue('UPS_CARRIER_API_KEY', Tools::getValue('ups_carrier_api_key'));
			Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', Tools::getValue('ups_carrier_pickup_type'));
			Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', Tools::getValue('ups_carrier_packaging_type'));
			$result = $this->getUPSShippingCost();
			if ($result['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSDESCRIPTION'] != 'Success')
				$this->_postErrors[]  = $this->l('Prestashop could not connect to UPS webservice, check your API Key');
		}
		
		if (!$this->_postErrors)
		{
			$extCarrier = new Carrier(intval(Configuration::get('UPS_CARRIER_ID')));
			$extCarrier->deleted = 0;
			if (!$extCarrier->update())
				$this->_postErrors[]  = $this->l('An error occured, please try again.');
		}
	}
	
	private function _postProcess()
	{
		if (Configuration::updateValue('UPS_CARRIER_LOGIN', Tools::getValue('ups_carrier_login')) AND
			Configuration::updateValue('UPS_CARRIER_PASSWORD', Tools::getValue('ups_carrier_password')) AND
			Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', Tools::getValue('ups_carrier_shipper_id')) AND
			Configuration::updateValue('UPS_CARRIER_API_KEY', Tools::getValue('ups_carrier_api_key')) AND
			Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', Tools::getValue('ups_carrier_pickup_type')) AND
			Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', Tools::getValue('ups_carrier_packaging_type')))
			$this->_html .= $this->displayConfirmation($this->l('Configuration updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings faild'));
	}
	
	public function hookupdateCarrier($params)
	{	
		/*
		if ((int)($params['id_carrier']) == (int)(Configuration::get('UPS_CARRIER_ID')))
			Configuration::updateValue('UPS_CARRIER_ID', (int)($params['carrier']->id));
		*/
	}
	
	
	public function getOrderShippingCost($params, $shipping_cost)
	{
		// for exemple the module return shipping cost with overcost set in the back-office, but you can call a webservice or calculat what you want before return value to the Cart
		//return (float)(Configuration::get('EXTERNAL_CARRIER_OVERCOST') + $shipping_cost);

		$ups_cost = '0';
		$address = new Address($params->id_address_delivery);
		$cartProducts = $params->getProducts();

		$country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.intval($address->id_country));
		foreach ($cartProducts as $product)
		{		
			$ups_params = array(
				'address1' => $address->address1,
				'address2' => $address->address2,
				'postcode' => $address->postcode,
				'city' => $address->city,
				'country_iso_code' => $country['iso_code'],
				'weight' => $product['weight'],
			);
			$result = $this->getUPSShippingCost($ups_params);
			if ($result['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSDESCRIPTION'] == 'Success' && $result['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['MONETARYVALUE'] > 0)
				$ups_cost += $result['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['MONETARYVALUE'];
		}
		
		if ($ups_cost && $ups_cost > 0)
			return $ups_cost;
		return $shipping_cost;
	}
	
	public function getOrderShippingCostExternal($params)
	{
		return 18;
	}

	
	
	
	
	
	
	
	
	
	public function recurseTab(&$resultTab, $levelTab, $index, $value)
	{
		if (isset($levelTab[$index]))
			$this->recurseTab($resultTab[$levelTab[$index]], $levelTab, $index + 1, $value);
		else
			$resultTab = $value;
	}
		
	public function getUPSShippingCost($ups_params = array())
	{
		// Curl Request
		$xml = $this->getXml($ups_params);
		$ch = curl_init("https://www.ups.com/ups.app/xml/Rate");  
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		curl_setopt($ch,CURLOPT_POST,1);  
		curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);  
		$result = curl_exec ($ch);

		// Get xml from Curl Result
		$data = strstr($result, '<?');  
		$xml_parser = xml_parser_create();  
		xml_parse_into_struct($xml_parser, $data, $valTab, $indexTab);  
		xml_parser_free($xml_parser);

		// Parsing XML
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
	
	
	
	
	public function getXml($ups_params = array())
	{
		if (!$ups_params)
			$ups_params = array(
				'address1' => 'Rue lacepede',
				'address2' => '',
				'postcode' => '75005',
				'city' => 'Paris',
				'weight' => '0.23',
				'country_iso_code' => 'FR',
			);
	
		$country = Db::getInstance()->getRow("
		SELECT c.`iso_code`
		FROM `"._DB_PREFIX_."country_lang` cl
		LEFT JOIN `"._DB_PREFIX_."country` c ON (c.`id_country` = cl.`id_country`)
		WHERE cl.`name` LIKE '".pSQL(Configuration::get('PS_SHOP_COUNTRY'))."'");
	
		$xml = '<?xml version="1.0" ?>
	<AccessRequest xml:lang="en-US">
		<AccessLicenseNumber>'.Configuration::get('UPS_CARRIER_API_KEY').'</AccessLicenseNumber>
		<UserId>'.Configuration::get('UPS_CARRIER_LOGIN').'</UserId>
		<Password>'.Configuration::get('UPS_CARRIER_PASSWORD').'</Password>
	</AccessRequest>
	<?xml version="1.0" ?>
		<RatingServiceSelectionRequest>
			<Request>
				<TransactionReference>
					<CustomerContext>Rating and Service</CustomerContext>
					<XpciVersion>1.0</XpciVersion>
				</TransactionReference>
				<RequestAction>Rate</RequestAction>
				<RequestOption>Rate</RequestOption>
			</Request>
			<PickupType>
				<Code>'.Configuration::get('UPS_CARRIER_PICKUP_TYPE').'</Code>
				<Description>Pickup Description</Description>
			</PickupType>
			<Shipment>
				<Description>Rate Shopping - Domestic</Description>
				<Shipper>
					<ShipperNumber>'.Configuration::get('UPS_CARRIER_SHIPPER_ID').'</ShipperNumber>
					<Address>
						<AddressLine1>'.Configuration::get('PS_SHOP_ADDR1').'</AddressLine1>
						<AddressLine2>'.Configuration::get('PS_SHOP_ADDR2').'</AddressLine2>
						<AddressLine3 />
						<City>'.Configuration::get('PS_SHOP_CITY').'</City>
						<PostalCode>'.Configuration::get('PS_SHOP_CODE').'</PostalCode>
						<CountryCode>'.$country['iso_code'].'</CountryCode>
					</Address>
				</Shipper>
				<ShipTo>
					<Address>
						<AddressLine1>'.$ups_params['address1'].'</AddressLine1>
						<AddressLine2>'.$ups_params['address2'].'</AddressLine2>
						<AddressLine3 />
						<City>'.$ups_params['city'].'</City>
						<PostalCode>'.$ups_params['postcode'].'</PostalCode>
						<CountryCode>'.$ups_params['country_iso_code'].'</CountryCode>
					</Address>
				</ShipTo>
				<ShipFrom>
					<Address>
						<AddressLine1>'.Configuration::get('PS_SHOP_ADDR1').'</AddressLine1>
						<AddressLine2>'.Configuration::get('PS_SHOP_ADDR2').'</AddressLine2>
						<AddressLine3 />
						<City>'.Configuration::get('PS_SHOP_CITY').'</City>
						<PostalCode>'.Configuration::get('PS_SHOP_CODE').'</PostalCode>
						<CountryCode>'.$country['iso_code'].'</CountryCode>
					</Address>
				</ShipFrom>
				<Service><Code>65</Code></Service>
				<Package>
					<PackagingType>
						<Code>'.Configuration::get('UPS_CARRIER_PACKAGING_TYPE').'</Code>
						<Description>Packaging Description</Description>
					</PackagingType>
					<Description>Rate</Description>
					<PackageWeight>
						<UnitOfMeasurement>
							<Code>KGS</Code>
						</UnitOfMeasurement>
						<Weight>'.$ups_params['weight'].'</Weight>
					</PackageWeight>
				</Package>
				<ShipmentServiceOptions />
			</Shipment>
		</RatingServiceSelectionRequest>';

		return $xml;
	}

}

?>
