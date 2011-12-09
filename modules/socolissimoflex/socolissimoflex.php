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
*  @version  Release: $Revision: 9074 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class SocolissimoFlex extends CarrierModule
{
	public  $id_carrier;

	private $_html = '';
	private $_postErrors = array();
	private $_fieldsList = array();
	private $_serviceTypeList = array();
	private $_hooksRegistration = array();
	private $_moduleName = 'socolissimoflex';

	private $_webserviceSupervision = 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp';
	private $_webserviceTestResult = '';
	private $_webserviceError = '';

	/*
	** Construct Method
	**
	*/

	public function __construct()
	{
		$this->name = 'socolissimoflex';
		$this->tab = 'shipping_logistics';
		$this->version = '1.0';
		$this->author = 'PrestaShop';
		$this->limited_countries = array('fr');

		parent::__construct ();

		$this->displayName = $this->l('Socolissimo Flexibility');
		$this->description = $this->l('Offer your customers, different delivery methods with Socolissimo Flex');

		// Loading Var
		$warning = array();
		$this->loadingVar();

		if (self::isInstalled($this->name))
		{
			// Check Class Soap availibility
			if (!extension_loaded('soap'))
				$warning[] = "'".$this->l('Class Soap')."', ";

			// Check Configuration Values
			foreach ($this->_fieldsList as $keyConfiguration => $name)
				if (!Configuration::get($keyConfiguration) && !empty($name))
					$warning[] = '\''.$name.'\' ';

			// Generate Warnings
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
	}

	public function loadingVar()
	{
		// Loading Fields List
		$this->_fieldsList = array(
			'SOCOFLEX_ID' => $this->l('SoColissimo Flex ID'),
			'SOCOFLEX_PASSWORD' => $this->l('SoColissimo Flex Password'),
			'SOCOFLEX_HOME_DELIVERY' => '',
			'SOCOFLEX_APP_DELIVERY' => '',
			'SOCOFLEX_DELIVERY_POINT' => '',
			'SOCOFLEX_HOME_DELIVERY_HIST' => '',
			'SOCOFLEX_APP_DELIVERY_HIST' => '',
			'SOCOFLEX_DELIVERY_POINT_HIST' => '',
			'SOCOFLEX_HOME_DELIVERY_OVERCOST' => '',
			'SOCOFLEX_APP_DELIVERY_OVERCOST' => '',
			'SOCOFLEX_DELIVERY_POINT_OVERCOST' => '',
		);

		// Loading service type list
		$this->_serviceTypeList = array(
			'SOCOFLEX_HOME_DELIVERY' => array('name' => $this->l('Home delivery'), 'description' => $this->l('Socolissimo flexibilité : À l\'adresse saisie'), 'image' => 'carrier-home-delivery.jpg'),
			'SOCOFLEX_APP_DELIVERY' => array('name' => $this->l('Appointment delivery'), 'description' => $this->l('Socolissimo flexibilité : Sur rendez-vous entre 17h et 21h30 à l\'adresse saisie'), 'image' => 'carrier-appointment-delivery.jpg'),
			'SOCOFLEX_DELIVERY_POINT' => array('name' => $this->l('Choose a delivery point'), 'description' => $this->l('Socolissimo flexibilité : À proximité de l\'adresse saisie'), 'image' => 'carrier-delivery-point.jpg')
		);

		// Loading hooks to register
		$this->_hooksRegistration = array('updateCarrier', 'extraCarrier', 'AdminOrder', 'paymentTop', 'newOrder');
	}



	/*
	** Install / Uninstall Methods
	**
	*/

	public function install()
	{
		// Install Module
		if (!parent::install())
			return false;

		// Install SQL
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		// Add configuration values
		foreach ($this->_fieldsList as $keyConfiguration => $name)
			Configuration::updateValue($keyConfiguration, '');
		Configuration::updateValue('SOCOFLEX_HOME_DELIVERY_OVERCOST', 1);
		Configuration::updateValue('SOCOFLEX_APP_DELIVERY_OVERCOST', 1);
		Configuration::updateValue('SOCOFLEX_DELIVERY_POINT_OVERCOST', 1);

		// Install Carriers
		$this->installCarriers();

		// Register Hooks
		foreach ($this->_hooksRegistration as $hook_name)
			if (!$this->registerHook($hook_name))
				return false;

		return true;
	}

	public function uninstall()
	{
		// Uninstall Carriers
		Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 1), 'UPDATE', '`external_module_name` = \''.pSQL($this->_moduleName).'\'');

		// Uninstall Config
		foreach ($this->_fieldsList as $keyConfiguration => $name)
			if (!Configuration::deleteByName($keyConfiguration))
				return false;

		// Uninstall SQL
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		// Unregister Hooks
		foreach ($this->_hooksRegistration as $hook_name)
			if (!$this->unregisterHook($hook_name))
				return false;

		// Uninstall Module
		if (!parent::uninstall())
			return false;

		return true;
	}

	public function installCarriers()
	{
		// Get all services availables
		foreach ($this->_serviceTypeList as $key => $service)
		{
			$config = array(
				'name' => $service['name'],
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => 0,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array('fr' => $service['description'], 'en' => $service['description'], Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => $service['description']),
				'id_zone' => 1,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true
			);
			$id_carrier = $this->installExternalCarrier($config, $service['image']);
			Configuration::updateValue($key, $id_carrier);
			Configuration::updateValue($key.'_HIST', $id_carrier);
		}
	}
	
	public static function installExternalCarrier($config, $image)
	{
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax_rules_group = $config['id_tax_rules_group'];
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

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
		}

		if ($carrier->add())
		{
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])), 'INSERT');

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
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
			}

			// Copy Logo
			if (!copy(dirname(__FILE__).'/'.$image, _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}



	/*
	** Form Config Methods
	**
	*/

	public function getContent()
	{
		$this->_html .= '<h2>' . $this->l('Socolissimo Flex Carrier').'</h2>';
		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		$this->_displayForm();
		return $this->_html;
	}

	private function _displayForm()
	{
		$this->_html .= '<fieldset>
		<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Socolissimo Flexibility Module Status').'</legend>';

		$alert = array();
		$this->_webserviceTestResult = $this->webserviceTest();
		if (!Configuration::get('SOCOFLEX_ID') || !Configuration::get('SOCOFLEX_PASSWORD'))
			$alert['generalSettings'] = 1;
		if (!$this->_webserviceTestResult)
			$alert['webserviceTest'] = 1;
		if (!extension_loaded('curl'))
			$alert['curl'] = 1;
		if (!ini_get('allow_url_fopen'))
			$alert['url_fopen'] = 1;

		if (!count($alert))
			$this->_html .= '<img src="'._PS_IMG_.'admin/module_install.png" /><strong>'.$this->l('Socolissimo Flexibility Carrier is configured and online!').'</strong>';
		else
		{
			$this->_html .= '<img src="'._PS_IMG_.'admin/warn2.png" /><strong>'.$this->l('Socolissimo Flexibility Carrier is not configured yet, you must:').'</strong>';
			$this->_html .= '<br />'.(isset($alert['generalSettings']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 1) '.$this->l('Fill the "General Settings" form');
			$this->_html .= '<br />'.(isset($alert['webserviceTest']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 2) '.$this->l('Webservice test connection').($this->_webserviceError ? ' : '.$this->_webserviceError : '');
			$this->_html .= '<br />'.(isset($alert['curl']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 3) '.$this->l('cURL is enabled');
			$this->_html .= '<br />'.(isset($alert['url_fopen']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 4) '.$this->l('Url fopen is enabled');
		}


		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>
			<style>label{width:300px}</style>
			<form action="index.php?tab='.Tools::safeOutput(Tools::getValue('tab')).'&configure='.Tools::safeOutput(Tools::getValue('configure')).'&token='.Tools::safeOutput(Tools::getValue('token')).'&tab_module='.Tools::safeOutput(Tools::getValue('tab_module')).'&module_name='.Tools::safeOutput(Tools::getValue('module_name')).'" method="post" class="form" id="configForm">

				<fieldset style="border: 0px;">
					<h4>'.$this->l('Configuration').' :</h4>
					<label>'.$this->l('Your Socolissimo Flexibility ID').' : </label>
					<div class="margin-form"><input type="text" size="20" name="socoflex_id" value="'.Tools::safeOutput(Tools::getValue('socoflex_id', Configuration::get('SOCOFLEX_ID'))).'" /></div>
					<label>'.$this->l('Your Socolissimo Flexibility password').' : </label>
					<div class="margin-form"><input type="text" size="20" name="socoflex_password" value="'.Tools::safeOutput(Tools::getValue('socoflex_password', Configuration::get('SOCOFLEX_PASSWORD'))).'" /></div>
					<label>'.$this->l('Home delivery overcost').' : </label>
					<div class="margin-form"><input type="text" size="8" name="socoflex_home_delivery_overcost" value="'.Tools::safeOutput(Tools::getValue('socoflex_home_delivery_overcost', Configuration::get('SOCOFLEX_HOME_DELIVERY_OVERCOST'))).'" /> &euro;</div>
					<label>'.$this->l('Appointment delivery overcost').' : </label>
					<div class="margin-form"><input type="text" size="8" name="socoflex_app_delivery_overcost" value="'.Tools::safeOutput(Tools::getValue('socoflex_app_delivery_overcost', Configuration::get('SOCOFLEX_APP_DELIVERY_OVERCOST'))).'" /> &euro;</div>
					<label>'.$this->l('Delivery point overcost').' : </label>
					<div class="margin-form"><input type="text" size="8" name="socoflex_delivery_point_overcost" value="'.Tools::safeOutput(Tools::getValue('socoflex_delivery_point_overcost', Configuration::get('SOCOFLEX_DELIVERY_POINT_OVERCOST'))).'" /> &euro;</div>
				</fieldset>

				<div class="margin-form"><input class="button" name="submitSave" type="submit" value="'.$this->l('Configure').'"></div>
			</form>';
	}

	private function _postValidation()
	{
		// Check configuration values
		if (Tools::getValue('socoflex_id') == NULL || Tools::getValue('socoflex_id') == '')
			$this->_postErrors[]  = $this->l('Your Socolissimo Flexibility ID is not specified');
		elseif (Tools::getValue('socoflex_password') == NULL || Tools::getValue('socoflex_password') == '')
			$this->_postErrors[]  = $this->l('Your Socolissimo Flexibility Password is not specified');

		// Check socolissimo flexibility webservice availibity
		if (!$this->_postErrors)
		{
			// All new configurations values are saved to be sure to test webservices with it
			Configuration::updateValue('SOCOFLEX_ID', pSQL(trim(Tools::getValue('socoflex_id'))));
			Configuration::updateValue('SOCOFLEX_PASSWORD', pSQL(trim(Tools::getValue('socoflex_password'))));
			if (!$this->webserviceTest())
				$this->_postErrors[]  = $this->l('Prestashop could not connect to Socolissimo Flexibility webservices').' :<br />'.($this->_webserviceError ? $this->_webserviceError : $this->l('No error description found'));
		}
	}

	private function _postProcess()
	{
		// Saving new configurations
		if (Configuration::updateValue('SOCOFLEX_ID', pSQL(trim(Tools::getValue('socoflex_id')))) AND
		    Configuration::updateValue('SOCOFLEX_PASSWORD', pSQL(trim(Tools::getValue('socoflex_password')))) AND
		    Configuration::updateValue('SOCOFLEX_HOME_DELIVERY_OVERCOST', pSQL(trim(Tools::getValue('socoflex_home_delivery_overcost')))) AND
		    Configuration::updateValue('SOCOFLEX_APP_DELIVERY_OVERCOST', pSQL(trim(Tools::getValue('socoflex_app_delivery_overcost')))) AND
		    Configuration::updateValue('SOCOFLEX_DELIVERY_POINT_OVERCOST', pSQL(trim(Tools::getValue('socoflex_delivery_point_overcost')))))
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings failed'));
	}



	/*
	** Hook Methods
	**
	*/


	public function hookupdateCarrier($params)
	{
		if ((int)($params['id_carrier']) == (int)(Configuration::get('SOCOFLEX_HOME_DELIVERY')))
		{
			Configuration::updateValue('SOCOFLEX_HOME_DELIVERY', (int)($params['carrier']->id));
			Configuration::updateValue('SOCOFLEX_HOME_DELIVERY_HIST', pSQL(Configuration::get('SOCOFLEX_HOME_DELIVERY_HIST').'|'.(int)($params['carrier']->id)));
		}
		if ((int)($params['id_carrier']) == (int)(Configuration::get('SOCOFLEX_APP_DELIVERY')))
		{
			Configuration::updateValue('SOCOFLEX_APP_DELIVERY', (int)($params['carrier']->id));
			Configuration::updateValue('SOCOFLEX_APP_DELIVERY_HIST', pSQL(Configuration::get('SOCOFLEX_APP_DELIVERY_HIST').'|'.(int)($params['carrier']->id)));
		}
		if ((int)($params['id_carrier']) == (int)(Configuration::get('SOCOFLEX_DELIVERY_POINT')))
		{
			Configuration::updateValue('SOCOFLEX_DELIVERY_POINT', (int)($params['carrier']->id));
			Configuration::updateValue('SOCOFLEX_DELIVERY_POINT_HIST', pSQL(Configuration::get('SOCOFLEX_DELIVERY_POINT_HIST').'|'.(int)($params['carrier']->id)));
		}
	}

	public function hookExtraCarrier($params)
	{
		// Check config
		if (!Configuration::get('SOCOFLEX_ID') || !Configuration::get('SOCOFLEX_PASSWORD'))
			return '';

		// Retrieve global
		global $smarty, $cookie;

		// Init Var
		$carrierDeliveryPoint = new Carrier((int)(Configuration::get('SOCOFLEX_DELIVERY_POINT')));

		// Is delivery point selected
		$delivery_point_selected = true;
		if (isset($carrierDeliveryPoint->active) && $carrierDeliveryPoint->active == 1 && (int)$params['cart']->id_carrier == (int)$carrierDeliveryPoint->id)
			$delivery_point_selected = false;

		// Display
		$smarty->assign('id_carrier_homedelivery', Configuration::get('SOCOFLEX_HOME_DELIVERY'));
		$smarty->assign('id_carrier_appdelivery', Configuration::get('SOCOFLEX_APP_DELIVERY'));
		$smarty->assign('id_carrier_deliverypoint', Configuration::get('SOCOFLEX_DELIVERY_POINT'));
		$smarty->assign('delivery_point_selected', $delivery_point_selected);
		return $this->display(__FILE__, 'socolissimoflex_extracarrier.tpl');
	}

	public function hookExtraCarrierAjax($params)
	{
		// Check config
		if (!Configuration::get('SOCOFLEX_ID') || !Configuration::get('SOCOFLEX_PASSWORD'))
			return '';

		// Retrieve global
		global $smarty, $cookie;
		$cart = new Cart((int)$cookie->id_cart);

		// Retrieve Delivery List
		$address = new Address((int)$cart->id_address_delivery);
		$deliveryPointList = $this->getDeliveryLocation($address);

		// Display
		$smarty->assign('deliveryPointList', $deliveryPointList);
		return $this->display(__FILE__, 'socolissimoflex_extracarrier_ajax.tpl');
	}

	public function displayInfoByCart()
	{
	}



	/*
	** Front Methods
	**
	*/

	public function getOrderShippingCost($params, $shipping_cost)
	{
		// Check config
		if (!Configuration::get('SOCOFLEX_ID') || !Configuration::get('SOCOFLEX_PASSWORD'))
			return false;

		// Add overcost
		if ((int)($this->id_carrier) == (int)(Configuration::get('SOCOFLEX_HOME_DELIVERY')))
			$shipping_cost += (float)Configuration::get('SOCOFLEX_HOME_DELIVERY_OVERCOST');
		if ((int)($this->id_carrier) == (int)(Configuration::get('SOCOFLEX_APP_DELIVERY')))
			$shipping_cost += (float)Configuration::get('SOCOFLEX_APP_DELIVERY_OVERCOST');
		if ((int)($this->id_carrier) == (int)(Configuration::get('SOCOFLEX_DELIVERY_POINT')))
			$shipping_cost += (float)Configuration::get('SOCOFLEX_DELIVERY_POINT_OVERCOST');

		// Return overcost
		return $shipping_cost;
	}

	public function getOrderShippingCostExternal($params)
	{
		return false;
	}



	/*
	** Webservices Methods
	**
	*/

	public function webserviceTest()
	{
		// Check config
		if (!Configuration::get('SOCOFLEX_ID') || !Configuration::get('SOCOFLEX_PASSWORD'))
			return false;

		// Send test request
		$params = array(
			'address' => '41%20boulevard%20des%20capucines',
			'zipCode' => '75002',
			'city' => 'Paris',
			'weight' => 1,
			'shippingDate' => date('d/m/Y'),
			'requestId' => date('YmdHis').rand(),
		);
		$xml = $this->sendRequest('findRDVPointRetraitAcheminement', $params);
		if (isset($xml->soapBody->ns1findRDVPointRetraitAcheminementResponse->return->listePointRetraitAcheminement) && count($xml->soapBody->ns1findRDVPointRetraitAcheminementResponse->return->listePointRetraitAcheminement) > 0)
			return true;

		// If get any result, webservices not working (webservices down, wrong login, ...)
		return false;
	}

	public function getDeliveryLocation($address)
	{
		// Check config
		if (!Configuration::get('SOCOFLEX_ID') || !Configuration::get('SOCOFLEX_PASSWORD'))
			return array();

		// Todo Gerer le poids et le temps de preparation
		// Send test request
		$params = array(
			'address' => $address->address1,
			'zipCode' => $address->postcode,
			'city' => $address->city,
			'weight' => 1,
			'shippingDate' => date('d/m/Y'),
			'requestId' => date('YmdHis').rand(),
		);
		$xml = $this->sendRequest('findRDVPointRetraitAcheminement', $params);
		if (!isset($xml->soapBody->ns1findRDVPointRetraitAcheminementResponse->return->listePointRetraitAcheminement) && count($xml->soapBody->ns1findRDVPointRetraitAcheminementResponse->return->listePointRetraitAcheminement) > 0)
			return array();

		// Get delivery point list
		$deliveryPointList = array();
		$nbDeliveryPoint = count($xml->soapBody->ns1findRDVPointRetraitAcheminementResponse->return->listePointRetraitAcheminement);
		for ($i = 0; $i < $nbDeliveryPoint; $i++)
			$deliveryPointList[] = (array)$xml->soapBody->ns1findRDVPointRetraitAcheminementResponse->return->listePointRetraitAcheminement[$i];

		// If get any result, webservices not working (webservices down, wrong login, ...)
		return $deliveryPointList;
	}

	/*
	** Request Methods
	**
	*/

	public function sendRequest($function, $params)
	{
		/*
		$client = new SoapClient(dirname(__FILE__).'/PointRetraitServiceWS.wsdl');
		//$client = new SoapClient('http://ws.colissimo.fr/pointretrait-ws-cxf/PointRetraitServiceWS?wsdl');
		$params = array(
			'accountNumber' => Configuration::get('SOCOFLEX_ID'),
			'password' => Configuration::get('SOCOFLEX_PASSWORD'),
			'address' => '41%20boulevard%20des%20capucines',
			'zipCode' => '75002',
			'city' => 'Paris',
			'weight' => '1',
			'shippingDate' => date('d/m/Y'),
			'requestId' => date('YmdHis').rand(),
		);
		$test =  $client->findPointRetraitAcheminement($params);
		*/
		$url = 'http://217.108.161.163/pointretrait-ws-cxf/PointRetraitServiceWS/'.$function.'?';
		$url .= 'accountNumber='.Configuration::get('SOCOFLEX_ID').'&password='.Configuration::get('SOCOFLEX_PASSWORD');
		foreach ($params as $key => $param)
			$url .= '&'.$key.'='.$param;
		$content = '<?xml version="1.0" encoding="UTF-8" ?>'.Tools::file_get_contents($url);
		$content = str_replace(array('soap:', 'ns1:'), array('soap', 'ns1'), $content);
		return simplexml_load_string($content);
	}
}

