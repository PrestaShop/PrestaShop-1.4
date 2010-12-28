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
	public  $id_carrier;

	private $_html = '';
	private $_postErrors = array();
	private $_fieldsList = array();
	private $_pickupTypeList = array();
	private $_packagingTypeList = array();
	private $_dimensionUnit = '';
	private $_weightUnit = '';
	private $_dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
	private $_weightUnitList = array('KG' => 'KGS', 'KGS' => 'KGS', 'LB' => 'LBS', 'LBS' => 'LBS');
	private $_moduleName = 'UpsCarrier';



	/*
	** Construct Method
	**
	*/

	public function __construct()
	{
		global $cookie;

		$this->name = 'upscarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '0.8';

		parent::__construct ();

		$this->displayName = $this->l('UPS Carrier');
		$this->description = $this->l('Offer to your customers, different delivery methods with UPS');

		if (self::isInstalled($this->name))
		{
			// Loading Var
			$warning = array();
			$this->loadingVar();

			// Check Configuration Values
			foreach ($this->_fieldsList as $keyConfiguration => $name)
				if (!Configuration::get($keyConfiguration))
					$warning[] = '\''.$name.'\' ';

			// Check Curl Availibility
			if (!extension_loaded('curl'))
				$warning[] = $this->l('\'Curl extension\'').', ';

			// Checking Unit
			$this->_dimensionUnit = $this->_dimensionUnitList[strtoupper(Configuration::get('PS_DIMENSION_UNIT'))];
			$this->_weightUnit = $this->_weightUnitList[strtoupper(Configuration::get('PS_WEIGHT_UNIT'))];
			if (!$this->_weightUnit || !$this->_weightUnitList[$this->_weightUnit])
				$warning[] = $this->l('\'Weight Unit (LB or KG in Preferences > Localization Tab).\'').' ';
			if (!$this->_dimensionUnit || !$this->_dimensionUnitList[$this->_dimensionUnit])
				$warning[] = $this->l('\'Dimension Unit (CM or IN in Preferences > Localization Tab).\'').' ';

			// Generate Warnings
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
	}

	public function loadingVar()
	{
		// Loading Fields List
		$this->_fieldsList = array(
			'UPS_CARRIER_LOGIN' => $this->l('UPS Login'),
			'UPS_CARRIER_PASSWORD' => $this->l('UPS Password'),
			'UPS_CARRIER_SHIPPER_ID' => $this->l('MyUps ID'),
			'UPS_CARRIER_API_KEY' => $this->l('UPS API Key'),
			'UPS_CARRIER_PICKUP_TYPE' => $this->l('UPS Pickup Type'),
			'UPS_CARRIER_PACKAGING_TYPE' => $this->l('UPS Packaging Type'),
		);

		// Loading Pickup Type List
		$this->_pickupTypeList = array(
			'01' => $this->l('Daily Pickup'),
			'03' => $this->l('Customer Counter'),
			'06' => $this->l('One Time Pickup'),
			'07' => $this->l('On Call Air'),
			'11' => $this->l('Suggested Retail Rates'),
			'19' => $this->l('Letter Center'),
			'20' => $this->l('Air Service Center'),
		);

		// Loading Packaging Type List
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



	/*
	** Install / Uninstall Methods
	**
	*/

	public function install()
	{
		global $cookie;

		// Install SQL
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		// Install Module
		if (!parent::install() OR !$this->registerHook('updateCarrier'))
			return false;

		// Install Flag
		if (!Configuration::updateValue('UPS_INSTALLED_FLAG', 1))
			return false;

		return true;
	}

	public function uninstall()
	{
		global $cookie;

		// Uninstall Config
		foreach ($this->_fieldsList as $keyConfiguration => $name)
			if (!Configuration::deleteByName($keyConfiguration))
				return false;

		// Uninstall SQL
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		// Uninstall Module
		if (!parent::uninstall() OR !$this->unregisterHook('updateCarrier'))
			return false;

		return true;
	}

	public function installCarriers($id_ups_rate_service_group)
	{
		// Unactive all UPS Carriers
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_service_code', array('active' => 0), 'UPDATE');

		// Get all services availables for this group
		$rateServiceList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)($id_ups_rate_service_group));
		foreach ($rateServiceList as $rateService)
			if (!$rateService['id_carrier'])
			{
				$config = array(
					'name' => $rateService['service'],
					'id_tax_rules_group' => 0,
					'active' => true,
					'deleted' => 0,
					'shipping_handling' => false,
					'range_behavior' => 0,
					'delay' => array('fr' => $rateService['service'], 'en' => $rateService['service']),
					'id_zone' => 1,
					'is_module' => true,
					'shipping_external' => true,
					'external_module_name' => $this->_moduleName,
					'need_range' => true
				);
				$id_carrier = $this->installExternalCarrier($config);
				Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_service_code', array('id_carrier' => (int)($id_carrier)), 'UPDATE', '`id_ups_rate_service_code` = '.(int)($rateService['id_ups_rate_service_code']));
			}
	}
	
	public static function installExternalCarrier($config)
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

			// Copy Logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}



	/*
	** Global Form Config Methods
	**
	*/

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
		$this->_html .= '<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Description').'</legend>'.$this->l('UPS Carrier Configurator.').'</fieldset><div class="clear">&nbsp;</div>';
		if (!Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'))
			$this->_html .= $this->_displayFormRateServiceGroup();
		else
			$this->_html .= $this->_displayFormConfig();
	}

	private function _postValidation()
	{
		if (!Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'))
			$this->_postValidationRateServiceGroup();
		elseif (Tools::getValue('section') == 'general')
			$this->_postValidationGeneral();
		elseif (Tools::getValue('section') == 'category')
			$this->_postValidationCategory();
		elseif (Tools::getValue('section') == 'product')
			$this->_postValidationProduct();
	}

	private function _postProcess()
	{
		if (!Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'))
			$this->_postProcessRateServiceGroup();
		elseif (Tools::getValue('section') == 'general')
			$this->_postProcessGeneral();
		elseif (Tools::getValue('section') == 'category')
			$this->_postProcessCategory();
		elseif (Tools::getValue('section') == 'product')
			$this->_postProcessProduct();
	}



	/*
	** First Form Config Methods
	** Rate Service Group Config
	**
	*/

	private function _displayFormRateServiceGroup()
	{
		global $cookie;

		$html = '
		<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&section=start" method="post" class="form">
		<fieldset>
			<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Rate Service Group').'</legend>
			'.$this->l('You must set the starting point of your package before configuring the module.').'<br /><br />

			<label>'.$this->l('Zip / Postal Code').' : </label>
			<div class="margin-form"><input type="text" size="20" name="ups_carrier_postal_code" value="'.Tools::getValue('ups_carrier_postal_code', Configuration::get('UPS_CARRIER_POSTAL_CODE')).'" /></div><br />

			<label>'.$this->l('Country').' : </label>
			<div class="margin-form">
				<select name="ups_carrier_country">
					<option value="0">'.$this->l('Select a country ...').'</option>';
					$idcountries = array();
					foreach (Country::getCountries($cookie->id_lang) as $v)
					{
						$html .= '<option value="'.$v['id_country'].'" '.($v['id_country'] == (int)(Configuration::get('UPS_CARRIER_COUNTRY')) ? 'selected="selected"' : '').'>'.$v['name'].'</option>';
						$idcountries[] = $v['id_country'];
					}
				$html .= '</select>
				<p>' . $this->l('Choose in country list the one.') . '</p>
				'.(!in_array((int)(Configuration::get('UPS_CARRIER_COUNTRY')), $idcountries) ? '<div class="warning">'.$this->l('Country is not set').'</div>' : '').'
			</div>

			<label>'.$this->l('Rate service group').' : </label>
			<div class="margin-form">
				<select name="ups_carrier_rate_service_group">
					<option value="0">'.$this->l('Select a rate service group ...').'</option>';
					$idrateservicegroups = array();
					$rateServiceGroupList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_group` ORDER BY `id_ups_rate_service_group`');
					foreach ($rateServiceGroupList as $v)
					{
						$html .= '<option value="'.$v['id_ups_rate_service_group'].'" '.($v['id_ups_rate_service_group'] == (int)(Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP')) ? 'selected="selected"' : '').'>'.$v['name'].'</option>';
						$idrateservicegroups[] = $v['id_ups_rate_service_group'];
					}
				$html .= '</select>
				<p>' . $this->l('Choose in rate service group list the one.') . '</p>
				'.(!in_array((int)(Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP')), $idrateservicegroups) ? '<div class="warning">'.$this->l('Rate service group is not set').'</div>' : '').'
			</div>

			<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
			
		</fieldset>
		</form>
		<div class="clear">&nbsp;</div>';

		return $html;
	}

	private function _postValidationRateServiceGroup()
	{
		// Check configuration values
		if (Tools::getValue('ups_carrier_postal_code') == NULL)
			$this->_postErrors[]  = $this->l('Your UPS postal code is not specified');
		elseif (Tools::getValue('ups_carrier_country') == NULL OR Tools::getValue('ups_carrier_country') == '0')
			$this->_postErrors[]  = $this->l('Your UPS country is not specified');
		elseif (Tools::getValue('ups_carrier_rate_service_group') == NULL OR Tools::getValue('ups_carrier_rate_service_group') == '0')
			$this->_postErrors[]  = $this->l('Your UPS service group is not specified');
	}

	private function _postProcessRateServiceGroup()
	{
		// Install Config
		$flag = false;
		if (Configuration::updateValue('UPS_CARRIER_POSTAL_CODE', Tools::getValue('ups_carrier_postal_code')) AND
			Configuration::updateValue('UPS_CARRIER_COUNTRY', Tools::getValue('ups_carrier_country')) AND
			Configuration::updateValue('UPS_CARRIER_RATE_SERVICE_GROUP', Tools::getValue('ups_carrier_rate_service_group')))
			$flag = true;

		// Install Carriers
		$this->installCarriers((int)Tools::getValue('ups_carrier_rate_service_group'));

		// Display Results
		if ($flag === true)
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings failed'));
	}



	/*
	** General Form Config Methods
	**
	*/

	private function _displayFormConfig()
	{
		$html = '
		<ul id="menuTab">
				<li id="menuTab1" class="menuTabButton selected">1. '.$this->l('General Settings').'</li>
				<li id="menuTab2" class="menuTabButton">2. '.$this->l('Categories Settings').'</li>
				<li id="menuTab3" class="menuTabButton">3. '.$this->l('Products Settings').'</li>
				<li id="menuTab4" class="menuTabButton">4. '.$this->l('Help').'</li>
			</ul>
			<div id="tabList">
				<div id="menuTab1Sheet" class="tabItem selected">'.$this->_displayFormGeneral().'</div>
				<div id="menuTab2Sheet" class="tabItem">'.$this->_displayFormCategory().'</div>
				<div id="menuTab3Sheet" class="tabItem">'.$this->_displayFormProduct().'</div>
				<div id="menuTab4Sheet" class="tabItem">'.$this->_displayHelp().'</div>
			</div>
			<br clear="left" />
			<br />
			<style>
				#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
				#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #EFEFEF; border-right: 1px solid #EFEFEF; border-top: 1px solid #EFEFEF; }
				#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
				#tabList { clear: left; }
				.tabItem { display: none; }
				.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
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
			$html .= '<script>
				  $(".menuTabButton.selected").removeClass("selected");
				  $("#menuTab'.$_GET['id_tab'].'").addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#menuTab'.$_GET['id_tab'].'Sheet").addClass("selected");
			</script>';
		return $html;
	}

	private function _displayFormGeneral()
	{
		global $cookie;
	
		$html = '<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=1&section=general" method="post" class="form">
					<label>'.$this->l('Your UPS Login').' : </label>
					<div class="margin-form"><input type="text" size="20" name="ups_carrier_login" value="'.Tools::getValue('ups_carrier_login', Configuration::get('UPS_CARRIER_LOGIN')).'" /></div>
					<label>'.$this->l('Your UPS Password').' : </label>
					<div class="margin-form"><input type="text" size="20" name="ups_carrier_password" value="'.Tools::getValue('ups_carrier_password', Configuration::get('UPS_CARRIER_PASSWORD')).'" /></div>
					<label>'.$this->l('Your MyUps ID').' : </label>
					<div class="margin-form"><input type="text" size="20" name="ups_carrier_shipper_id" value="'.Tools::getValue('ups_carrier_shipper_id', Configuration::get('UPS_CARRIER_SHIPPER_ID')).'" /></div>
					<label>'.$this->l('Your UPS API Key').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ups_carrier_api_key" value="'.Tools::getValue('ups_carrier_api_key', Configuration::get('UPS_CARRIER_API_KEY')).'" />
						<p><a href="https://www.ups.com/upsdeveloperkit" target="_blank">' . $this->l('Please click here to get your UPS API Key.') . '</a></p>
					</div>
					<label>'.$this->l('Zip / Postal Code').' : </label>
					<div class="margin-form"><input type="text" size="20" name="ups_carrier_postal_code" value="'.Tools::getValue('ups_carrier_postal_code', Configuration::get('UPS_CARRIER_POSTAL_CODE')).'" /></div><br />
					<label>'.$this->l('Country').' : </label>
					<div class="margin-form">
						<select name="ups_carrier_country">
							<option value="0">'.$this->l('Select a country ...').'</option>';
							$idcountries = array();
							foreach (Country::getCountries($cookie->id_lang) as $v)
							{
								$html .= '<option value="'.$v['id_country'].'" '.($v['id_country'] == (int)(Configuration::get('UPS_CARRIER_COUNTRY')) ? 'selected="selected"' : '').'>'.$v['name'].'</option>';
								$idcountries[] = $v['id_country'];
							}
						$html .= '</select>
						<p>' . $this->l('Choose in country list the one.') . '</p>
						'.(!in_array((int)(Configuration::get('UPS_CARRIER_COUNTRY')), $idcountries) ? '<div class="warning">'.$this->l('Country is not set').'</div>' : '').'
					</div>
					<label>'.$this->l('Rate service group').' : </label>
					<div class="margin-form">
						<select name="ups_carrier_rate_service_group">
							<option value="0">'.$this->l('Select a rate service group ...').'</option>';
							$idrateservicegroups = array();
							$rateServiceGroupList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_group` ORDER BY `id_ups_rate_service_group`');
							foreach ($rateServiceGroupList as $v)
							{
								$html .= '<option value="'.$v['id_ups_rate_service_group'].'" '.($v['id_ups_rate_service_group'] == (int)(Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP')) ? 'selected="selected"' : '').'>'.$v['name'].'</option>';
								$idrateservicegroups[] = $v['id_ups_rate_service_group'];
							}
						$html .= '</select>
						<p>' . $this->l('Shipments originating.') . '</p>
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
					<label>'.$this->l('Delivery Service').' : </label>
						<div class="margin-form">';
							$rateServiceList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'));	
							foreach($rateServiceList as $rateService)
								$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_ups_rate_service_code'].'" '.(($rateService['active'] == 1) ? 'checked="checked"' : '').' /> '.$rateService['service'].'<br />';
					$html .= '
					<p>' . $this->l('Choose the delivery service which will be available for customers.') . '</p>
					</div>
					<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
				</form>';
		return $html;
	}

	private function _postValidationGeneral()
	{
		// Check configuration values
		if (Tools::getValue('ups_carrier_login') == NULL)
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
		elseif (Tools::getValue('ups_carrier_postal_code') == NULL)
			$this->_postErrors[]  = $this->l('Your Zip / Postal code is not specified');
		elseif (Tools::getValue('ups_carrier_country') == NULL OR Tools::getValue('ups_carrier_country') == 0)
			$this->_postErrors[]  = $this->l('Your country is not specified');

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
			Configuration::updateValue('UPS_CARRIER_POSTAL_CODE', Tools::getValue('ups_carrier_postal_code'));
			Configuration::updateValue('UPS_CARRIER_COUNTRY', Tools::getValue('ups_carrier_country'));
			if (!$this->webserviceTest())
				$this->_postErrors[]  = $this->l('Prestashop could not connect to UPS webservices, check your API Key');
		}

		// Install Carriers (if not)
		Configuration::updateValue('UPS_CARRIER_RATE_SERVICE_GROUP', Tools::getValue('ups_carrier_rate_service_group'));
		$this->installCarriers((int)Tools::getValue('ups_carrier_rate_service_group'));

		// If no errors appear, the carrier is being activated, else, the carrier is being deactivated
		if (!$this->_postErrors)
		{
			// Get available services
			$serviceSelected = Tools::getValue('service');

			// Active available carrier
			if ($serviceSelected)
				foreach ($serviceSelected as $ss)
				{
					$id_carrier = Db::getInstance()->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_code` = '.(int)($ss));
					Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_service_code', array('active' => 1), 'UPDATE', '`id_ups_rate_service_code` = '.(int)($ss));
				}
		}
	}

	private function _postProcessGeneral()
	{
		// Saving new configurations
		if (Configuration::updateValue('UPS_CARRIER_LOGIN', Tools::getValue('ups_carrier_login')) AND
			Configuration::updateValue('UPS_CARRIER_PASSWORD', Tools::getValue('ups_carrier_password')) AND
			Configuration::updateValue('UPS_CARRIER_SHIPPER_ID', Tools::getValue('ups_carrier_shipper_id')) AND
			Configuration::updateValue('UPS_CARRIER_API_KEY', Tools::getValue('ups_carrier_api_key')) AND
			Configuration::updateValue('UPS_CARRIER_PICKUP_TYPE', Tools::getValue('ups_carrier_pickup_type')) AND
			Configuration::updateValue('UPS_CARRIER_PACKAGING_TYPE', Tools::getValue('ups_carrier_packaging_type')) AND
			Configuration::updateValue('UPS_CARRIER_POSTAL_CODE', Tools::getValue('ups_carrier_postal_code')) AND
			Configuration::updateValue('UPS_CARRIER_COUNTRY', Tools::getValue('ups_carrier_country')))
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings failed'));
	}



	/*
	** Category Form Config Methods
	**
	*/

	private function _getPathInTab($id_category)
	{
		global $cookie;

		$category = Db::getInstance()->getRow('
		SELECT id_category, level_depth, nleft, nright
		FROM '._DB_PREFIX_.'category
		WHERE id_category = '.(int)$id_category);

		if (isset($category['id_category']))
		{
			$categories = Db::getInstance()->ExecuteS('
			SELECT c.id_category, cl.name, cl.link_rewrite
			FROM '._DB_PREFIX_.'category c
			LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category)
			WHERE c.nleft <= '.(int)$category['nleft'].' AND c.nright >= '.(int)$category['nright'].' AND cl.id_lang = '.(int)($cookie->id_lang).'
			ORDER BY c.level_depth ASC
			LIMIT '.(int)($category['level_depth'] + 1));

			$n = 1;
			$pathTab = array();
			$nCategories = (int)sizeof($categories);
			foreach ($categories AS $category)
				$pathTab[] = htmlentities($category['name'], ENT_NOQUOTES, 'UTF-8');
		
			return $pathTab;
		}
	}
	
	private function _getChildCategories($categories, $id, $path = array(), $pathAdd = '')
	{
		$html = '';
		if ($pathAdd != '')
			$path[] = $pathAdd;
		if (isset($categories[$id]))
			foreach ($categories[$id] as $idc => $cc)
			{
				$html .= '<option value="'.$cc['infos']['id_category'].'" '.($cc['infos']['id_category'] == (int)(Tools::getValue('id_category')) ? 'selected="selected"' : '').'>';
				if ($path)
					foreach ($path as $p)
						$html .= $p.' > ';
				$html .= $cc['infos']['name'];
				$html .= '</option>';
				$html .= $this->_getChildCategories($categories, $idc, $path, $cc['infos']['name']);
			}
		return $html;
	}

	private function _isPostCheck($id_ups_rate_service_code)
	{
		$services = Tools::getValue('service');
		if ($services)
			foreach ($services as $s)
				if ($s == $id_ups_rate_service_code)
					return 1;
		return 0;
	}
	
	private function _displayFormCategory()
	{
		global $cookie;

		// Display header
		$html = '<p><b>'.$this->l('In this tab, you can set a specific configuration for each category.').'</b></p><br />
		<table class="table tableDnD" cellpadding="0" cellspacing="0" width="90%">
			<thead>
				<tr class="nodrag nodrop">
					<th>'.$this->l('ID Config').'</th>
					<th>'.$this->l('Category').'</th>
					<th>'.$this->l('Packaging type').'</th>
					<th>'.$this->l('Additionnal charges').'</th>
					<th>'.$this->l('Services').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>
			</thead>
			<tbody>';

		// Loading config list
		$configCategoryList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_category` > 0');
		if (!$configCategoryList)
			$html .= '<tr><td colspan="6">'.$this->l('There is no specific UPS configuration for categories at this point.').'</td></tr>';
		foreach ($configCategoryList as $k => $c)
		{
			// Category Path
			$path = '';
			$pathTab = $this->_getPathInTab($c['id_category']);
			foreach ($pathTab as $p)
			{
				if (!empty($path)) { $path .= ' > '; }
				$path .= $p;
			}

			// Loading config currency
			$configCurrency = new Currency($c['id_currency']);

			// Loading services attached to this config
			$services = '';
			$servicesTab = Db::getInstance()->ExecuteS('
			SELECT ursc.`service`
			FROM `'._DB_PREFIX_.'ups_rate_config_service` urcs
			LEFT JOIN `'._DB_PREFIX_.'ups_rate_service_code` ursc ON (ursc.`id_ups_rate_service_code` = urcs.`id_ups_rate_service_code`)
			WHERE ursc.`id_ups_rate_service_group` = '.(int)(Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP')).'
			AND urcs.`id_ups_rate_config` = '.(int)$c['id_ups_rate_config']);
			foreach ($servicesTab as $s)
				$services .= $s['service'].'<br />';

			// Display line
			$alt = 0;
			if ($k % 2 != 0)
				$alt = ' class="alt_row"';
			$html .= '
				<tr'.$alt.'>
					<td>'.$c['id_ups_rate_config'].'</td>
					<td>'.$path.'</td>
					<td>'.(isset($this->_packagingTypeList[$c['packaging_type_code']]) ? $this->_packagingTypeList[$c['packaging_type_code']] : '-').'</td>
					<td>'.$c['additionnal_charges'].' '.$configCurrency->sign.'</td>
					<td>'.$services.'</td>
					<td>
						<a href="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=2&section=category&action=edit&id_ups_rate_config='.(int)($c['id_ups_rate_config']).'" style="float: left;">
							<img src="../img/admin/edit.gif" />
						</a>
						<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=2&section=category&action=delete&id_ups_rate_config='.(int)($c['id_ups_rate_config']).'&id_category='.(int)($c['id_category']).'" method="post" class="form" style="float: left;">
							<input name="submitSave" type="image" src="../img/admin/delete.gif" OnClick="return confirm(\''.$this->l('Are you sure you want to delete this specific UPS configuration for this category ?').'\');" />
						</form>
					</td>
				</tr>';
		}

		$html .= '
			</tbody>
		</table><br /><br />';

		// Add or Edit Category Configuration
		if (Tools::getValue('action') == 'edit' && Tools::getValue('section') == 'category')
		{
			// Loading config
			$configSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_ups_rate_config` = '.(int)(Tools::getValue('id_ups_rate_config')));
			
			// Category Path
			$path = '';
			$pathTab = $this->_getPathInTab($configSelected['id_category']);
			foreach ($pathTab as $p)
			{
				if (!empty($path)) { $path .= ' > '; }
				$path .= $p;
			}

			$html .= '<p align="center"><b>'.$this->l('Update a rule').' (<a href="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=2&section=category&action=add">'.$this->l('Add a rule').' ?</a>)</b></p>
					<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=2&section=category&action=edit&id_ups_rate_config='.(int)(Tools::getValue('id_ups_rate_config')).'" method="post" class="form">
						<label>'.$this->l('Category').' :</label>
						<div class="margin-form" style="padding: 0.2em 0.5em 0 0; font-size: 12px;">'.$path.' <input type="hidden" name="id_category" value="'.(int)($configSelected['id_category']).'" /></div><br clear="left" />
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_type_code', $configSelected['packaging_type_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additionnal charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additionnal_charges" value="'.Tools::getValue('additionnal_charges', $configSelected['additionnal_charges']).'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'));	
								foreach($rateServiceList as $rateService)
								{
									$configServiceSelected = Db::getInstance()->getValue('SELECT `id_ups_rate_service_code` FROM `'._DB_PREFIX_.'ups_rate_config_service` WHERE `id_ups_rate_config` = '.(int)(Tools::getValue('id_ups_rate_config')).' AND `id_ups_rate_service_code` = '.(int)($rateService['id_ups_rate_service_code']));
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_ups_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_ups_rate_service_code']) == 1 || $configServiceSelected > 0) ? 'checked="checked"' : '').' /> '.$rateService['service'].'<br />';
								}
						$html .= '
						<p>' . $this->l('Choose the delivery service which will be available for customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}
		else
		{
			$html .= '<p align="center"><b>'.$this->l('Add a rule').'</b></p>
					<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=2&section=category&action=add" method="post" class="form">
						<label>'.$this->l('Category').' : </label>
						<div class="margin-form">
							<select name="id_category">
								<option value="0">'.$this->l('Select a category ...').'</option>
								'.$this->_getChildCategories(Category::getCategories($cookie->id_lang), 0).'
							</select>
						</div>
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging === pSQL(Tools::getValue('packaging_type_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additionnal charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additionnal_charges" value="'.Tools::getValue('additionnal_charges').'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'));	
								foreach($rateServiceList as $rateService)
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_ups_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_ups_rate_service_code']) == 1) ? 'checked="checked"' : '').' /> '.$rateService['service'].'<br />';
						$html .= '
						<p>' . $this->l('Choose the delivery service which will be available for customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}

		return $html;
	}

	private function _postValidationCategory()
	{
		// Check post values
		if (Tools::getValue('id_category') == NULL)
			$this->_postErrors[]  = $this->l('You have to select a category.');

		if (!$this->_postErrors)
		{
			$id_ups_rate_config = Db::getInstance()->getValue('SELECT `id_ups_rate_config` FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_category` = '.(int)Tools::getValue('id_category'));

			// Check if a config does not exist in Add case
			if ($id_ups_rate_config > 0 && Tools::getValue('action') == 'add')
				$this->_postErrors[]  = $this->l('This category already has a specific UPS configuration.');

			// Check if a config exists and if the IDs config correspond in Upd case
			if (Tools::getValue('action') == 'edit' && (!isset($id_ups_rate_config) || $id_ups_rate_config != Tools::getValue('id_ups_rate_config')))
				$this->_postErrors[]  = $this->l('An error occured, please try again.');

			// Check if a config exists in Delete case
			if (Tools::getValue('action') == 'delete' && !isset($id_ups_rate_config))
				$this->_postErrors[]  = $this->l('An error occured, please try again.');
		}
	}

	private function _postProcessCategory()
	{
		// Init Var
		$date = date('Y-m-d H:i:s');
		$services = Tools::getValue('service');

		// Add Script
		if (Tools::getValue('action') == 'add')
		{
			$addTab = array(
				'id_product' => 0,
				'id_category' => (int)(Tools::getValue('id_category')),
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'additionnal_charges' => pSQL(Tools::getValue('additionnal_charges')),
				'date_add' => pSQL($date),
				'date_upd' => pSQL($date)
			);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config', $addTab, 'INSERT');
			$id_ups_rate_config = Db::getInstance()->Insert_ID();
			foreach ($services as $s)
			{
				$addTab = array('id_ups_rate_service_code' => pSQL($s), 'id_ups_rate_config' => (int)$id_ups_rate_config, 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($id_ups_rate_config > 0)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Update Script
		if (Tools::getValue('action') == 'edit' && Tools::getValue('id_ups_rate_config'))
		{
			$updTab = array(
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'additionnal_charges' => pSQL(Tools::getValue('additionnal_charges')),
				'date_upd' => pSQL($date)
			);
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config', $updTab, 'UPDATE', '`id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'ups_rate_config_service` WHERE `id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));
			foreach ($services as $s)
			{
				$addTab = array('id_ups_rate_service_code' => pSQL($s), 'id_ups_rate_config' => (int)Tools::getValue('id_ups_rate_config'), 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($result)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Delete Script
		if (Tools::getValue('action') == 'delete' && Tools::getValue('id_ups_rate_config'))
		{
			$result1 = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));
			$result2 = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'ups_rate_config_service` WHERE `id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));

			// Display Results
			if ($result1)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));			
		}
	}



	/*
	** Product Form Config Methods
	**
	*/

	private function _displayFormProduct()
	{
		global $cookie;

		// Display header
		$html = '<p><b>'.$this->l('In this tab, you can set a specific configuration for each product.').'</b></p><br />
		<table class="table tableDnD" cellpadding="0" cellspacing="0" width="90%">
			<thead>
				<tr class="nodrag nodrop">
					<th>'.$this->l('ID Config').'</th>
					<th>'.$this->l('Product').'</th>
					<th>'.$this->l('Packaging type').'</th>
					<th>'.$this->l('Additionnal charges').'</th>
					<th>'.$this->l('Services').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>
			</thead>
			<tbody>';

		// Loading config list
		$configProductList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_product` > 0');
		if (!$configProductList)
			$html .= '<tr><td colspan="6">'.$this->l('There is no specific UPS configuration for products at this point.').'</td></tr>';
		foreach ($configProductList as $k => $c)
		{
			// Loading Product
			$product = new Product((int)$c['id_product'], false, (int)$cookie->id_lang);

			// Loading config currency
			$configCurrency = new Currency($c['id_currency']);

			// Loading services attached to this config
			$services = '';
			$servicesTab = Db::getInstance()->ExecuteS('
			SELECT ursc.`service`
			FROM `'._DB_PREFIX_.'ups_rate_config_service` urcs
			LEFT JOIN `'._DB_PREFIX_.'ups_rate_service_code` ursc ON (ursc.`id_ups_rate_service_code` = urcs.`id_ups_rate_service_code`)
			WHERE ursc.`id_ups_rate_service_group` = '.(int)(Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP')).'
			AND urcs.`id_ups_rate_config` = '.(int)$c['id_ups_rate_config']);
			foreach ($servicesTab as $s)
				$services .= $s['service'].'<br />';

			// Display line
			$alt = 0;
			if ($k % 2 != 0)
				$alt = ' class="alt_row"';
			$html .= '
				<tr'.$alt.'>
					<td>'.$c['id_ups_rate_config'].'</td>
					<td>'.$product->name.'</td>
					<td>'.(isset($this->_packagingTypeList[$c['packaging_type_code']]) ? $this->_packagingTypeList[$c['packaging_type_code']] : '-').'</td>
					<td>'.$c['additionnal_charges'].' '.$configCurrency->sign.'</td>
					<td>'.$services.'</td>
					<td>
						<a href="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=3&section=product&action=edit&id_ups_rate_config='.(int)($c['id_ups_rate_config']).'" style="float: left;">
							<img src="../img/admin/edit.gif" />
						</a>
						<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=3&section=product&action=delete&id_ups_rate_config='.(int)($c['id_ups_rate_config']).'&id_product='.(int)($c['id_product']).'" method="post" class="form" style="float: left;">
							<input name="submitSave" type="image" src="../img/admin/delete.gif" OnClick="return confirm(\''.$this->l('Are you sure you want to delete this specific UPS configuration for this product ?').'\');" />
						</form>
					</td>
				</tr>';
		}

		$html .= '
			</tbody>
		</table><br /><br />';

		// Add or Edit Product Configuration
		if (Tools::getValue('action') == 'edit' && Tools::getValue('section') == 'product')
		{
			// Loading config
			$configSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_ups_rate_config` = '.(int)(Tools::getValue('id_ups_rate_config')));
			$product = new Product((int)$configSelected['id_product'], false, (int)$cookie->id_lang);

			$html .= '<p align="center"><b>'.$this->l('Update a rule').' (<a href="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=3&section=product&action=add">'.$this->l('Add a rule').' ?</a>)</b></p>
					<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=3&section=product&action=edit&id_ups_rate_config='.(int)(Tools::getValue('id_ups_rate_config')).'" method="post" class="form">
						<label>'.$this->l('Product').' :</label>
						<div class="margin-form" style="padding: 0.2em 0.5em 0 0; font-size: 12px;">'.$product->name.' <input type="hidden" name="id_product" value="'.(int)($configSelected['id_product']).'" /></div><br clear="left" />
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_type_code', $configSelected['packaging_type_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additionnal charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additionnal_charges" value="'.Tools::getValue('additionnal_charges', $configSelected['additionnal_charges']).'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'));	
								foreach($rateServiceList as $rateService)
								{
									$configServiceSelected = Db::getInstance()->getValue('SELECT `id_ups_rate_service_code` FROM `'._DB_PREFIX_.'ups_rate_config_service` WHERE `id_ups_rate_config` = '.(int)(Tools::getValue('id_ups_rate_config')).' AND `id_ups_rate_service_code` = '.(int)($rateService['id_ups_rate_service_code']));
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_ups_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_ups_rate_service_code']) == 1 || $configServiceSelected > 0) ? 'checked="checked"' : '').' /> '.$rateService['service'].'<br />';
								}
						$html .= '
						<p>' . $this->l('Choose the delivery service which will be available for customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}
		else
		{
			$html .= '<p align="center"><b>'.$this->l('Add a rule').'</b></p>
					<form action="index.php?tab='.$_GET['tab'].'&configure='.$_GET['configure'].'&token='.$_GET['token'].'&tab_module='.$_GET['tab_module'].'&module_name='.$_GET['module_name'].'&id_tab=3&section=product&action=add" method="post" class="form">
						<label>'.$this->l('Product').' : </label>
						<div class="margin-form">
							<select name="id_product">
								<option value="0">'.$this->l('Select a product ...').'</option>';
						$productsList = Db::getInstance()->ExecuteS('
						SELECT pl.* FROM `'._DB_PREFIX_.'product` p
						LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = 2)
						WHERE p.`active` = 1
						ORDER BY pl.`name`');
						foreach ($productsList as $product)
							$html .= '<option value="'.$product['id_product'].'" '.($product['id_product'] == (int)(Tools::getValue('id_product')) ? 'selected="selected"' : '').'>'.$product['name'].'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging === pSQL(Tools::getValue('packaging_type_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additionnal charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additionnal_charges" value="'.Tools::getValue('additionnal_charges').'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP'));	
								foreach($rateServiceList as $rateService)
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_ups_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_ups_rate_service_code']) == 1) ? 'checked="checked"' : '').' /> '.$rateService['service'].'<br />';
						$html .= '
						<p>' . $this->l('Choose the delivery service which will be available for customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}

		return $html;
	}
	
	private function _postValidationProduct()
	{
		// Check post values
		if (Tools::getValue('id_product') == NULL)
			$this->_postErrors[]  = $this->l('You have to select a product.');

		if (!$this->_postErrors)
		{
			$id_ups_rate_config = Db::getInstance()->getValue('SELECT `id_ups_rate_config` FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_product` = '.(int)Tools::getValue('id_product'));

			// Check if a config does not exist in Add case
			if ($id_ups_rate_config > 0 && Tools::getValue('action') == 'add')
				$this->_postErrors[]  = $this->l('This product already has a specific UPS configuration.');

			// Check if a config exists and if the IDs config correspond in Upd case
			if (Tools::getValue('action') == 'edit' && (!isset($id_ups_rate_config) || $id_ups_rate_config != Tools::getValue('id_ups_rate_config')))
				$this->_postErrors[]  = $this->l('An error occured, please try again.');

			// Check if a config exists in Delete case
			if (Tools::getValue('action') == 'delete' && !isset($id_ups_rate_config))
				$this->_postErrors[]  = $this->l('An error occured, please try again.');
		}
	}
	
	private function _postProcessProduct()
	{
		// Init Var
		$date = date('Y-m-d H:i:s');
		$services = Tools::getValue('service');

		// Add Script
		if (Tools::getValue('action') == 'add')
		{
			$addTab = array(
				'id_product' => (int)(Tools::getValue('id_product')),
				'id_category' => 0,
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'additionnal_charges' => pSQL(Tools::getValue('additionnal_charges')),
				'date_add' => pSQL($date),
				'date_upd' => pSQL($date)
			);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config', $addTab, 'INSERT');
			$id_ups_rate_config = Db::getInstance()->Insert_ID();
			foreach ($services as $s)
			{
				$addTab = array('id_ups_rate_service_code' => pSQL($s), 'id_ups_rate_config' => (int)$id_ups_rate_config, 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($id_ups_rate_config > 0)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Update Script
		if (Tools::getValue('action') == 'edit' && Tools::getValue('id_ups_rate_config'))
		{
			$updTab = array(
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'additionnal_charges' => pSQL(Tools::getValue('additionnal_charges')),
				'date_upd' => pSQL($date)
			);
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config', $updTab, 'UPDATE', '`id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'ups_rate_config_service` WHERE `id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));
			foreach ($services as $s)
			{
				$addTab = array('id_ups_rate_service_code' => pSQL($s), 'id_ups_rate_config' => (int)Tools::getValue('id_ups_rate_config'), 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($result)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Delete Script
		if (Tools::getValue('action') == 'delete' && Tools::getValue('id_ups_rate_config'))
		{
			$result1 = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));
			$result2 = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'ups_rate_config_service` WHERE `id_ups_rate_config` = '.(int)Tools::getValue('id_ups_rate_config'));

			// Display Results
			if ($result1)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));			
		}
	}



	/*
	** Help Config Methods
	**
	*/

	private function _displayHelp()
	{
		return '<p><b>'.$this->l('Welcome in the PrestaShop UPS Module configurator !').'</b></p>
		<p>'.$this->l('This section will help you to understand how to configure this module correctly.').'</p>
		<br />
		<p><b><u>1. '.$this->l('General Settings').'</u></b></p>
		<p>'.$this->l('See below for the description of each field :').'</p>
		<p><b>'.$this->l('Your UPS Login, UPS Password, MyUps ID, UPS API Key').' :</b> '.$this->l('You have to subscribe on UPS website at this address').' <a href="https://www.ups.com/upsdeveloperkit" target="_blank">https://www.ups.com/upsdeveloperkit</a></p>
		<p><b>'.$this->l('Zip / Postal Code').' :</b> '.$this->l('This field must be the Zip / Postal code of your package starting point.').'</p>
		<p><b>'.$this->l('Country').' :</b> '.$this->l('This field must be the country of your package starting point.').'</p>
		<p><b>'.$this->l('Rate service group').' :</b> '.$this->l('This field must be in adequation with the two fields above.').'</p>
		<p><b>'.$this->l('Pickup Type').' :</b> '.$this->l('This field corresponds to the pickup type you want to have with UPS.').'</p>
		<p><b>'.$this->l('Packaging Type').' :</b> '.$this->l('This field corresponds to the default packaging type (when there is no specific configuration for the product or the category product).').'</p>
		<p><b>'.$this->l('Delivery Service').' :</b> '.$this->l('This checkboxes correspond to the delivery services who want to be avalaible (when there is no specific configuration for the product or the category product).').'</p>
		<br />
		<p><b><u>2. '.$this->l('Categories Settings').'</u></b></p>
		<p>'.$this->l('This section will permit you to define specific UPS configuration for each product category (such as Packaging Type and Additionnal Charges).').'</p>
		<br />
		<p><b><u>3. '.$this->l('Products Settings').'</u></b></p>
		<p>'.$this->l('This section will permit you to define specific UPS configuration for each product (such as Packaging Type and Additionnal Charges).').'</p>
		<br />
		';
	}

	public function hookupdateCarrier($params)
	{
		/*
		if ((int)($params['id_carrier']) == (int)(Configuration::get('SOCOLISSIMO_CARRIER_ID')))
		{
			Configuration::updateValue('SOCOLISSIMO_CARRIER_ID', (int)($params['carrier']->id));
			Configuration::updateValue('SOCOLISSIMO_CARRIER_ID_HIST', Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST').'|'.(int)($params['carrier']->id));
		}
		*/
	}

	public function displayInfoByCart()
	{
	}



	/*
	** Front Methods
	**
	*/

	public function getCookieCurrencyRate($id_currency_origin)
	{
		global $cookie;
		$conversionRate = 1;
		if ($cookie->id_currency != $id_currency_origin)
		{
			$currencyOrigin = new Currency((int)$id_currency_origin);
			$conversionRate /= $currencyOrigin->conversion_rate;
			$currencySelect = new Currency((int)$cookie->id_currency);
			$conversionRate *= $currencySelect->conversion_rate;
		}
		return $conversionRate;
	}
	
	public function getOrderShippingCostHash($wsParams)
	{
		$paramHash = '';
		$productHash = '';
		foreach ($wsParams['products'] as $product)
		{
			if (!empty($productHash))
				$productHash .= '|';
			$productHash .= $product['id_product'].':'.$product['id_product_attribute'].':'.$product['cart_quantity'];
		}
		foreach ($wsParams as $k => $v)
			if ($k != 'products')
			$paramHash .= '/'.$v;
		return md5($productHash.$paramHash);
	}

	public function getOrderShippingCostCache($wsParams)
	{
		// Get Cache
		$row = Db::getInstance()->getRow("
		SELECT * FROM `"._DB_PREFIX_."ups_cache`
		WHERE `id_cart` = ".(int)($wsParams['id_cart'])."
		AND `id_carrier` = ".(int)($this->id_carrier)."
		AND `hash` = '".pSQL($wsParams['hash'])."'");

		if ($row['id_currency'])
		{
			// Check Currency Rate And Calcul
			$conversionRate = $this->getCookieCurrencyRate($row['id_currency']);
			$row['total_charges'] = $row['total_charges'] * $conversionRate;

			// Return Cache
			return $row;
		}
		
		return false;
	}

	public function saveOrderShippingCostCache($wsParams, $wscost)
	{
		global $cookie;
		$is_available = 1;
		if (!$wscost)
			$is_available = 0;
		$date = date('Y-m-d H:i:s');
		$insert = array(
			'id_cart' => (int)($wsParams['id_cart']),
			'id_carrier' => (int)($this->id_carrier),
			'hash' => pSQL($wsParams['hash']),
			'id_currency' => (int)($cookie->id_currency),
			'total_charges' => pSQL($wscost),
			'is_available' => (int)($is_available),
			'date_add' => pSQL($date),
			'date_upd' => pSQL($date)
		);
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ups_cache', $insert, 'INSERT');
	}

	public function loadShippingCostConfig($product)
	{
		// Init var
		$config = array();
	
		// Check if there is a specific product configuration
		if ($product['id_product'] > 0)
		{
			$productConfiguration = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_product` = '.(int)($product['id_product']));
			if ($productConfiguration['id_ups_rate_config'])
			{
				$servicesConfiguration = Db::getInstance()->ExecuteS('
				SELECT urcs.*, ursc.`id_carrier`
				FROM `'._DB_PREFIX_.'ups_rate_config_service` urcs
				LEFT JOIN `'._DB_PREFIX_.'ups_rate_service_code` ursc ON (ursc.`id_ups_rate_service_code` = urcs.`id_ups_rate_service_code`)
				WHERE `id_ups_rate_config` = '.(int)($productConfiguration['id_ups_rate_config']));
				foreach ($servicesConfiguration as $service)
					$productConfiguration['services'][$service['id_ups_rate_service_code']] = $service;
				return $productConfiguration;
			}
		}

		// Check if there is a specific category configuration
		if ($product['id_category_default'] > 0)
		{
			$categoryConfiguration = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ups_rate_config` WHERE `id_category` = '.(int)($product['id_category_default']));
			if ($categoryConfiguration['id_ups_rate_config'])
			{
				$servicesConfiguration = Db::getInstance()->ExecuteS('
				SELECT urcs.*, ursc.`id_carrier`
				FROM `'._DB_PREFIX_.'ups_rate_config_service` urcs
				LEFT JOIN `'._DB_PREFIX_.'ups_rate_service_code` ursc ON (ursc.`id_ups_rate_service_code` = urcs.`id_ups_rate_service_code`)
				WHERE `id_ups_rate_config` = '.(int)($categoryConfiguration['id_ups_rate_config']));
				foreach ($servicesConfiguration as $service)
					$categoryConfiguration['services'][$service['id_ups_rate_service_code']] = $service;
				return $categoryConfiguration;
			}
		}

		// Return general config
		$config['packaging_type_code'] = Configuration::get('UPS_CARRIER_PACKAGING_TYPE');
		$servicesConfiguration = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_ups_rate_service_group` = '.(int)(Configuration::get('UPS_CARRIER_RATE_SERVICE_GROUP')).' AND `active` = 1');
		foreach ($servicesConfiguration as $service)
			$config['services'][$service['id_ups_rate_service_code']] = $service;
		return $config;
	}

	public function getWebserviceShippingCost($wsParams)
	{
		// Init var
		$cost = 0;

		// Getting shipping cost for each product
		foreach ($wsParams['products'] as $product)
		{
			// Load specific configuration
			$config = $this->loadShippingCostConfig($product);

			// Get service in adequation with carrier and check if available
			$serviceSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ups_rate_service_code` WHERE `id_carrier` = '.(int)($this->id_carrier));
			if (!isset($config['services'][$serviceSelected['id_ups_rate_service_code']]))
				return false;

			// Load param product
			if ($product['weight'] < '0.1')
				$product['weight'] = '0.1';
			$wsParams['width'] = $product['width'];
			$wsParams['height'] = $product['height'];
			$wsParams['depth'] = $product['depth'];
			$wsParams['weight'] = $product['weight'];
			$wsParams['service'] = $serviceSelected['code'];

			// If Additionnal Charges
			if (isset($config['id_currency']) && isset($config['additionnal_charges']))
			{
				$conversionRate = 1;
				$conversionRate = $this->getCookieCurrencyRate((int)($config['id_currency']));
				$cost += ($config['additionnal_charges'] * $conversionRate);
			}

			// If webservice return a cost, we add it, else, we return the original shipping cost
			$result = $this->getUpsShippingCost($wsParams);
			if ($result['connect'] && $result['cost'] > 0)
				$cost += $result['cost'];
			else
				return false;
		}

		if ($cost > 0)
			return $cost;
		return false;
	}

	public function getOrderShippingCost($params, $shipping_cost)
	{	
		// Init var
		$address = new Address($params->id_address_delivery);
		$recipient_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)($address->id_country));
		$recipient_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)($address->id_state));
		$shipper_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)(Configuration::get('UPS_CARRIER_COUNTRY')));
		$products = $params->getProducts();

		// Webservices Params
		$wsParams = array(
			'id_cart' => $params->id,
			'id_address_delivery' => $params->id_address_delivery,
			'recipient_address1' => $address->address1,
			'recipient_address2' => $address->address2,
			'recipient_postalcode' => $address->postcode,
			'recipient_city' => $address->city,
			'recipient_country_iso' => $recipient_country['iso_code'],
			'recipient_state_iso' => $recipient_state['iso_code'],
			'shipper_address1' => '',
			'shipper_address2' => '',
			'shipper_postalcode' => Configuration::get('UPS_CARRIER_POSTAL_CODE'),
			'shipper_city' => '',
			'shipper_country_iso' => $shipper_country['iso_code'],
			'shipper_state_iso' => '',
			'pickup_type' => Configuration::get('UPS_CARRIER_PICKUP_TYPE'),
			'packaging_type' => Configuration::get('UPS_CARRIER_PACKAGING_TYPE'),
			'products' => $params->getProducts()
		);
		$wsParams['hash'] = $this->getOrderShippingCostHash($wsParams);

		// Check cache
		$cache = $this->getOrderShippingCostCache($wsParams);
		if ($cache['id_ups_cache'] > 0)
		{
			if ($cache['is_available'] == 0)
				return false;
			if ($cache['total_charges'])
				return $cache['total_charges'];
		}

		// Get Webservices Cost and Cache it
		$wscost = $this->getWebserviceShippingCost($wsParams);
		$this->saveOrderShippingCostCache($wsParams, $wscost);

		return $wscost;
	}

	public function getOrderShippingCostExternal($params)
	{
		return $this->getOrderShippingCost($params, 23);
	}



	/*
	** Webservices Methods
	**
	*/

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
		// Check Curl Availibility
		if (!extension_loaded('curl'))
			return false;

		// Example Params for testing
		$shipper_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)(Configuration::get('UPS_CARRIER_COUNTRY')));
		$upsParams = array(
			'recipient_address1' => '13450 Farmcrest Ct',
			'recipient_address2' => '',
			'recipient_postalcode' => '20171',
			'recipient_city' => 'Herndon',
			'recipient_country_iso' => 'US',
			'recipient_state_iso' => 'VA',
			'shipper_address1' => '',
			'shipper_address2' => '',
			'shipper_postalcode' => Configuration::get('UPS_CARRIER_POSTAL_CODE'),
			'shipper_city' => '',
			'shipper_country_iso' => $shipper_country['iso_code'],
			'shipper_state_iso' => '',
			'packaging_type' => Configuration::get('UPS_CARRIER_PACKAGING_TYPE'),
			'service' => 65,
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
		if ($resultTab['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSDESCRIPTION'] == 'Success')
			return true;
		$this->_postErrors[]  = $resultTab['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['ERROR']['ERRORDESCRIPTION'];
		return false;
	}

	public function getUpsShippingCost($wsParams)
	{
		// Check Curl Availibility
		if (!extension_loaded('curl'))
			return false;

		// Check Arguments
		if (!$wsParams)
			return array('connect' => false, 'cost' => 0);

		// Curl Request
		$xml = $this->getXml($wsParams);
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
		$conversionRate = 1;
		if (isset($resultTab['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['CURRENCYCODE']))
		{
			$id_currency_return = Db::getInstance()->getValue('SELECT `id_currency` FROM `'._DB_PREFIX_.'currency` WHERE `iso_code` = \''.pSQL($resultTab['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['CURRENCYCODE']).'\'');
			$conversionRate = $this->getCookieCurrencyRate($id_currency_return);
		}

		// Return results
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
			'[[Service]]',
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
			$upsParams['packaging_type'],
			$upsParams['service'],
			$upsParams['weight'],
			$this->_weightUnit,
			$upsParams['width'],
			$upsParams['height'],
			$upsParams['depth'],
			$this->_dimensionUnit
		);

		$xml = @file_get_contents(dirname(__FILE__).'/xml.tpl');
		$xml = str_replace($search, $replace, $xml);

		return $xml;
	}

}

?>

