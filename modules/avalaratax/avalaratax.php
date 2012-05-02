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

// Security
if (!defined('_PS_VERSION_'))
	exit;

spl_autoload_register('avalaraAutoload');

class AvalaraTax extends Module
{
	private $_overrideFilesInModule = array(
									'Tax.php' => 'override/classes/Tax.php',
									'AddressController.php' => 'override/controllers/AddressController.php',
									'AuthController.php' => 'override/controllers/AuthController.php',
								);

	/******************************************************************/
	/** Construct Method **********************************************/
	/******************************************************************/

	public function __construct()
	{
		global $cookie;

		$this->name = 'avalaratax';
		$this->tab = 'billing_invoicing';
		$this->version = '1.1';
		$this->author = 'PrestaShop';
		$this->limited_countries = array('us', 'ca');
		parent::__construct();
		
		$this->displayName = $this->l('Avalara - AvaTax');
		$this->description = $this->l('Sales Tax is complicated. AvaTax makes it easy.');
		
		$timeout = Configuration::get('AVALARATAX_TIMEOUT');
		if ((int)$timeout > 0)
			ini_set('max_execution_time', (int)$timeout);
	}

	/******************************************************************/
	/** Install / Uninstall Methods ***********************************/
	/******************************************************************/

	public function install()
	{
		Configuration::updateValue('AVALARATAX_URL', 'https://development.avalara.net');
		Configuration::updateValue('AVALARATAX_ADDRESS_VALIDATION', 1);
		Configuration::updateValue('AVALARATAX_TAX_CALCULATION', 1);
		Configuration::updateValue('AVALARATAX_TIMEOUT', 300);

		// Value possible : Development / Production
		Configuration::updateValue('AVALARATAX_MODE', 'Development'); /* @todo : Before module release, change to: Production */
		Configuration::updateValue('AVALARATAX_ADDRESS_NORMALIZATION', 1);
		Configuration::updateValue('AVALARATAX_COMMIT_ID', 5);
		Configuration::updateValue('AVALARATAX_CANCEL_ID', 6);
		Configuration::updateValue('AVALARATAX_REFUND_ID', 7);
		Configuration::updateValue('AVALARATAX_POST_ID', 2);
		Configuration::updateValue('AVALARATAX_STATE', 0);
		Configuration::updateValue('AVALARATAX_COUNTRY', 0);
		Configuration::updateValue('AVALARA_CACHE_MAX_LIMIT', 1); /* The values in cache will be refreshed every 1 minute by default */
		
		// Make sure Avalara Tables don't exist before installation
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'avalara_product_cache`;');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'avalara_carrier_cache`;');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'avalara_returned_products`;');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'avalara_temp`;');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'avalara_taxcodes`;');
		
		if (!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'avalara_product_cache` (
		`id_cache` int(10) unsigned NOT NULL auto_increment,
		`id_product` int(10) unsigned NOT NULL,
		`tax_rate` float(8, 2) unsigned NOT NULL,
		`region` varchar(2) NOT NULL,
		`update_date` datetime,
		PRIMARY KEY (`id_cache`),
		UNIQUE (`id_product`, `region`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') ||
		!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'avalara_carrier_cache` (
		`id_cache` int(10) unsigned NOT NULL auto_increment,
		`id_carrier` int(10) unsigned NOT NULL,
		`tax_rate` float(8, 2) unsigned NOT NULL,
		`region` varchar(2) NOT NULL,
		`update_date` datetime,
		PRIMARY KEY (`id_cache`),
		UNIQUE (`id_carrier`, `region`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')||
		!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'avalara_returned_products` (
		`id_returned_product` int(10) unsigned NOT NULL auto_increment,
		`id_order` int(10) unsigned NOT NULL,
		`id_product` int(10) unsigned NOT NULL,
		`total` float(8, 2) unsigned NOT NULL,
		`quantity` int(10) unsigned NOT NULL,
		`name` varchar(255) NOT NULL,
		`description_short` varchar(255) NULL,
		`tax_code` varchar(255) NULL,
		PRIMARY KEY (`id_returned_product`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')||
		!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'avalara_temp` (
		`id_order` int(10) unsigned NOT NULL,
		`id_order_detail` int(10) unsigned NOT NULL)
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')||
		!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'avalara_taxcodes` (
		`id_taxcode` int(10) unsigned NOT NULL auto_increment,
		`id_product` int(10) unsigned NOT NULL,
		`tax_code`  varchar(30) NOT NULL,
		PRIMARY KEY (`id_taxcode`),
		UNIQUE (`id_product`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
		
		if (!parent::install() || !$this->registerHook('leftColumn')
			|| !$this->registerHook('updateOrderStatus')
			|| !$this->registerHook('cancelProduct')
			|| !$this->registerHook('adminOrder')
			|| !$this->registerHook('backOfficeTop')
			|| !$this->registerHook('header')
		 )
			return false;
		
		/* Check the files to override */
		$filesToOverride = array();
		if (file_exists(dirname(__FILE__).'/../../override/classes/Tax.php'))
			$filesToOverride[] = '/override/classes/Tax.php';
		if (file_exists(dirname(__FILE__).'/../../override/controllers/AddressController.php'))
			$filesToOverride[] = '/override/controllers/AddressController.php';
		if (file_exists(dirname(__FILE__).'/../../override/controllers/AuthController.php'))
			$filesToOverride[] = '/override/controllers/AuthController.php';
		if (count($filesToOverride))
			die($this->_displayConfirmation($this->l('The module was successfully installed but the following file(s) already exist. Please, merge file(s) manually.').
			'<br />'.implode('<br />', $filesToOverride), 'warn'));
		else 
		{
			if (!is_dir(dirname(__FILE__).'/../../override/classes/'))
				mkdir(dirname(__FILE__).'/../../override/classes/', 0777, true);
			if (!is_dir(dirname(__FILE__).'/../../override/controllers/'))
				mkdir(dirname(__FILE__).'/../../override/controllers/', 0777, true);
			
			foreach ($this->_overrideFilesInModule as $path)
				copy(dirname(__FILE__).'/'.$path, dirname(__FILE__).'/../../'.$path);
		}
		return true;
	}

	public function uninstall()
	{
		// Before deleting the files, make sure the md5_file matches
		// We don't want to delete a file that might have other custom modifications that the user
		// might have done.
		foreach ($this->_overrideFilesInModule as $path)
			if (md5_file(dirname(__FILE__).'/'.$path) == md5_file(dirname(__FILE__).'/../../'.$path))
				@unlink(dirname(__FILE__).'/../../'.$path);

		if (!parent::uninstall() OR
		!Configuration::deleteByName('AVALARATAX_URL') OR
		!Configuration::deleteByName('AVALARATAX_ADDRESS_VALIDATION') OR
		!Configuration::deleteByName('AVALARATAX_TAX_CALCULATION') OR
		!Configuration::deleteByName('AVALARATAX_TIMEOUT') OR
		!Configuration::deleteByName('AVALARATAX_MODE') OR
		!Configuration::deleteByName('AVALARATAX_ACCOUNT_NUMBER') OR
		!Configuration::deleteByName('AVALARATAX_COMPANY_CODE') OR
		!Configuration::deleteByName('AVALARATAX_LICENSE_KEY') OR
		!Configuration::deleteByName('AVALARATAX_ADDRESS_NORMALIZATION') OR
		!Configuration::deleteByName('AVALARATAX_ADDRESS_LINE1') OR
		!Configuration::deleteByName('AVALARATAX_ADDRESS_LINE2') OR
		!Configuration::deleteByName('AVALARATAX_CITY') OR
		!Configuration::deleteByName('AVALARATAX_STATE') OR
		!Configuration::deleteByName('AVALARATAX_ZIP_CODE') OR
		!Configuration::deleteByName('AVALARATAX_COUNTRY') OR
		!Configuration::deleteByName('AVALARATAX_COMMIT_ID') OR
		!Configuration::deleteByName('AVALARATAX_CANCEL_ID') OR
		!Configuration::deleteByName('AVALARATAX_REFUND_ID') OR
		!Configuration::deleteByName('AVALARA_CACHE_MAX_LIMIT') OR
		!Configuration::deleteByName('AVALARATAX_POST_ID') OR 
		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'avalara_product_cache`') OR
		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'avalara_carrier_cache`') OR
		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'avalara_returned_products`') OR
		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'avalara_temp`') OR
		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'avalara_taxcodes`'))
			return false;

		return true;
	}
	
	/******************************************************************/
	/** Hook Methods **************************************************/
	/******************************************************************/
	
	public function hookAdminOrder($params)
	{
		$this->purgeTempTable();
	}
	
	public function hookCancelProduct($params)
	{
		if(isset($_POST['cancelProduct']))
		{
			$order = new Order((int)$_POST['id_order']);
			
			if ($order->invoice_number)
			{
				// Get all the cancel product's IDs
				$cancelledIdsOrderDetail = array();
				foreach ($_POST['cancelQuantity'] as $idOrderDetail => $qty)
					if ($qty > 0)
						$cancelledIdsOrderDetail[] = (int)$idOrderDetail;
				$cancelledIdsOrderDetail = implode(', ', $cancelledIdsOrderDetail);
				
				// Fill temp table
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'avalara_temp (`id_order`, `id_order_detail`) 
										VALUES ('.(int)$_POST['id_order'].', '.(int)$params['id_order_detail'].')');
				// Check if we are at the end of the loop
				$totalLoop = Db::getInstance()->ExecuteS('SELECT COUNT(`id_order`) as totalLines 
														FROM '._DB_PREFIX_.'avalara_temp 
														WHERE `id_order_detail` IN ('.pSQL($cancelledIdsOrderDetail).')');
				
				if ($totalLoop[0]['totalLines'] != count(array_filter($_POST['cancelQuantity'])))
					return false;
				// Clean the temp table because we are at the end of the loop
				$this->purgeTempTable();
				
				// Get details for cancelledIdsOrderDetail (Grab the info to post to Avalara in English.)
				$cancelledProdIdsDetails = Db::getInstance()->ExecuteS('SELECT od.`product_id` as id_product, od.`id_order_detail`, pl.`name`, 
																		pl.`description_short`, od.`product_price` as price, od.`reduction_percent`, 
																		od.`reduction_amount`, od.`product_quantity` as quantity, atc.`tax_code`
																		FROM '._DB_PREFIX_.'order_detail od
																		LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
																		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
																		LEFT JOIN '._DB_PREFIX_.'avalara_taxcodes atc ON (atc.id_product = p.id_product)
																		WHERE pl.`id_lang` = 1 AND od.`id_order` = '.(int)$_POST['id_order'].' 
																		AND od.`id_order_detail` IN ('.pSQL($cancelledIdsOrderDetail).')');
				// Build the product list
				$products = array();
				foreach ($cancelledProdIdsDetails as $cancelProd)
					$products[] = array('id_product' => (int)$cancelProd['id_product'], 
										'quantity' => (int)$_POST['cancelQuantity'][$cancelProd['id_order_detail']], 
										'total' => pSQL($_POST['cancelQuantity'][$cancelProd['id_order_detail']] * ($cancelProd['price'] - ($cancelProd['price'] * ($cancelProd['reduction_percent'] / 100)) - $cancelProd['reduction_amount'])), // Including those product with discounts
										'name' => pSQL(Tools::safeOutput($cancelProd['name'])), 
										'description_short' => pSQL(Tools::safeOutput($cancelProd['description_short']), true), 
										'tax_code' => pSQL(Tools::safeOutput($cancelProd['tax_code'])));
				// Send to Avalara
				$commitResult = $this->getTax($products, array('type' => 'ReturnInvoice', 'DocCode' => (int)$_POST['id_order']));
				if ($commitResult['ResultCode'] == 'Warning' 
				|| $commitResult['ResultCode'] == 'Error' 
				|| $commitResult['ResultCode'] == 'Exception')
					echo $this->_displayConfirmation($this->l('The following error was generated while cancelling the orders you selected. <br /> - '.
						Tools::safeOutput($commitResult['Messages']['Summary'])), 'error');
				else
				{
					$this->commitToAvalara(array('id_order' => (int)$_POST['id_order']));
					echo $this->_displayConfirmation($this->l('The products you selected were cancelled.'));
				}
			}
		}
	}

	public function hookUpdateOrderStatus($params)
	{
		if ($params['newOrderStatus']->id == (int)Configuration::get('AVALARATAX_COMMIT_ID'))
			return $this->commitToAvalara($params);
		elseif ($params['newOrderStatus']->id == (int)Configuration::get('AVALARATAX_CANCEL_ID'))
		{
			$params['CancelCode'] = 'V';
			$this->cancelFromAvalara($params);
			return $this->cancelFromAvalara($params);
		}
		elseif ($params['newOrderStatus']->id == (int)Configuration::get('AVALARATAX_POST_ID'))
		{
			return $this->postToAvalara($params);
		}
		elseif ($params['newOrderStatus']->id == (int)Configuration::get('AVALARATAX_REFUND_ID'))
		{
			// Commit main order
			$this->commitToAvalara($params);
		}
	}

	public function hookBackOfficeTop()
	{
		if (Tools::isSubmit('submitAddproduct') || Tools::isSubmit('submitAddproductAndStay'))
			Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'avalara_taxcodes (`id_product`, `tax_code`) 
				VALUES ('.(isset($_GET['id_product']) ? (int)$_GET['id_product'] : 0).', "'.pSQL(Tools::safeOutput($_POST['tax_code'])).'")');
		
		if (isset($_GET['updateproduct']))
			$productTaxCode = Db::getInstance()->ExecuteS('SELECT `tax_code` 
														FROM '._DB_PREFIX_.'avalara_taxcodes atc 
														WHERE atc.`id_product` = '.(isset($_GET['id_product']) ? (int)$_GET['id_product'] : 0));
			
		return '
		<script type="text/javascript">
			$(function(){
				if ($(\'#Taxes\').size() || $(\'#submitFiltertax_rules_group\').size())
					$(\'#content\').prepend(\'<div class="warn"><img src="../img/admin/warn2.png">'.
					$this->l('Tax rules for USA are overwritten by Avalara Tax Module.').'</div>\');
				
				$(\'<tr><td class="col-left">'.$this->l('Tax Code (Avalara)').
				':</td><td style="padding-bottom:5px;"><input type="text" style="width: 130px; margin-right: 5px;" value="'.
				(isset($productTaxCode[0]) ? Tools::safeOutput($productTaxCode[0]['tax_code']) : 0 ).
				'" name="tax_code" maxlength="13" size="55"></td></tr>\').appendTo(\'#product #step1 table:eq(0) tbody\');
			});
		</script>';
	}
	
	public function hookHeader()
	{
		global $cookie, $cart;
		
		if (!$cart || !$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
			$id_address = (int)(Db::getInstance()->getValue('SELECT `id_address` FROM `'._DB_PREFIX_.'address` WHERE `id_customer` = '.(int)($cart->id_customer).' AND `deleted` = 0 ORDER BY `id`'));
		else
			$id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
		
		$buffer = '<script type="text/javascript">
		$(function(){
			/* ajax call to cache taxes product taxes that exist in the current cart */
			$.ajax(
			{
				type : \'POST\',
				url : \''.$this->_path.'\' + \'ajax.php\',
				data :	{
					\'id_cart\': "'.$cart->id.'",
					\'id_lang\': "'.$cookie->id_lang.'",
					\'id_address\': "'.$id_address.'",
					\'ajax\': "getProductTaxRate",
					\'token\': "'.md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')).'",
					
				},
				dataType: \'html\'
			});
		});
		</script>';
		
		return $buffer;
	}

	
	/******************************************************************/
	/** Main Form Methods *********************************************/
	/******************************************************************/

	public function getContent()
	{
		global $cookie;
		
		if (Tools::isSubmit('SubmitAvalaraTaxSettings'))
		{
			Configuration::updateValue('AVALARATAX_ACCOUNT_NUMBER', Tools::getValue('avalaratax_account_number'));
			Configuration::updateValue('AVALARATAX_LICENSE_KEY', Tools::getValue('avalaratax_license_key'));
			Configuration::updateValue('AVALARATAX_URL', Tools::getValue('avalaratax_url'));
			Configuration::updateValue('AVALARATAX_COMPANY_CODE', Tools::getValue('avalaratax_company_code'));
			
			echo $this->_displayConfirmation();
		}
		elseif (Tools::isSubmit('SubmitAvalaraTaxOptions'))
		{
			Configuration::updateValue('AVALARATAX_ADDRESS_VALIDATION', Tools::getValue('avalaratax_address_validation'));
			Configuration::updateValue('AVALARATAX_TAX_CALCULATION', Tools::getValue('avalaratax_tax_calculation'));
			Configuration::updateValue('AVALARATAX_TIMEOUT', (int)Tools::getValue('avalaratax_timeout'));
			Configuration::updateValue('AVALARATAX_ADDRESS_NORMALIZATION', Tools::getValue('avalaratax_address_normalization'));
			Configuration::updateValue('AVALARA_CACHE_MAX_LIMIT', Tools::getValue('avalara_cache_max_limit') < 1 ?
				1 : Tools::getValue('avalara_cache_max_limit') > 23 ? 23 : Tools::getValue('avalara_cache_max_limit'));
			
			echo $this->_displayConfirmation();
		}
		elseif (Tools::isSubmit('SubmitAvalaraTestConnection'))
			$connectionTestResult = $this->_testConnection();
		elseif (Tools::isSubmit('SubmitAvalaraAddressOptions'))
		{
			/* Validate address*/
			$address = new Address();
			$address->address1 = Tools::getValue('avalaratax_address_line1');
			$address->address2 = Tools::getValue('avalaratax_address_line2');
			$address->city = Tools::getValue('avalaratax_city');
			$address->region = Tools::getValue('avalaratax_state');
			$address->id_country = Tools::getValue('avalaratax_country');
			$address->postcode =  Tools::getValue('avalaratax_zip_code');
			
			$normalizedAddress = $this->validateAddress($address);
			if (isset($normalizedAddress['ResultCode']) && $normalizedAddress['ResultCode'] == 'Success')
			{
				echo $this->_displayConfirmation($this->l('The address you submitted has been validated.'));
				Configuration::updateValue('AVALARATAX_ADDRESS_LINE1', $normalizedAddress['Normalized']['Line1']);
				Configuration::updateValue('AVALARATAX_ADDRESS_LINE2', $normalizedAddress['Normalized']['Line2']);
				Configuration::updateValue('AVALARATAX_CITY', $normalizedAddress['Normalized']['City']);
				Configuration::updateValue('AVALARATAX_STATE', $normalizedAddress['Normalized']['Region']);
				Configuration::updateValue('AVALARATAX_COUNTRY', $normalizedAddress['Normalized']['Country']);
				Configuration::updateValue('AVALARATAX_ZIP_CODE', $normalizedAddress['Normalized']['PostalCode']);
			}
			else
			{
				echo $this->_displayConfirmation($this->l('The following error was generated while validating your address').
					':<br /> - '.Tools::safeOutput($normalizedAddress['Messages']['Summary']), 'error');
				Configuration::updateValue('AVALARATAX_ADDRESS_LINE1', Tools::getValue('avalaratax_address_line1'));
				Configuration::updateValue('AVALARATAX_ADDRESS_LINE2', Tools::getValue('avalaratax_address_line2'));
				Configuration::updateValue('AVALARATAX_CITY', Tools::getValue('avalaratax_city'));
				Configuration::updateValue('AVALARATAX_STATE', Tools::getValue('avalaratax_state'));
				Configuration::updateValue('AVALARATAX_ZIP_CODE', Tools::getValue('avalaratax_zip_code'));
			}
		}
		elseif (Tools::isSubmit('SubmitAvalaraTaxClearCache'))
		{
			Db::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.'avalara_product_cache');
			Db::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.'avalara_carrier_cache');
			
			echo $this->_displayConfirmation('Cache cleared!');
		}
		
		$confValues = Configuration::getMultiple(array(
		// Configuration
		'AVALARATAX_ACCOUNT_NUMBER', 'AVALARATAX_LICENSE_KEY', 'AVALARATAX_URL', 'AVALARATAX_COMPANY_CODE',
		// Options
		'AVALARATAX_ADDRESS_VALIDATION', 'AVALARATAX_TAX_CALCULATION', 'AVALARATAX_TIMEOUT', 
		'AVALARATAX_ADDRESS_NORMALIZATION', 'AVALARATAX_COMMIT_ID', 'AVALARATAX_CANCEL_ID', 
		'AVALARATAX_REFUND_ID', 'AVALARATAX_POST_ID', 'AVALARA_CACHE_MAX_LIMIT',
		// Default Address
		'AVALARATAX_ADDRESS_LINE1', 'AVALARATAX_ADDRESS_LINE2', 'AVALARATAX_CITY', 'AVALARATAX_STATE', 
		'AVALARATAX_ZIP_CODE', 'AVALARATAX_COUNTRY'));
		
		$stateList = array();
		$stateList[] = array('id' => '0', 'name' => $this->l('Choose your state (if applicable)'), 'iso_code' => '--');
		foreach (State::getStates(intval($cookie->id_lang)) as $state)
			$stateList[] = array('id' => $state['id_state'], 'name' => $state['name'], 'iso_code' => $state['iso_code']);
		
		$countryList = array();
		$countryList[] = array('id' => '0', 'name' => $this->l('Choose your country'), 'iso_code' => '--');
		foreach (Country::getCountries(intval($cookie->id_lang)) as $country)
			$countryList[] = array('id' => $country['id_country'], 'name' => $country['name'], 'iso_code' => $country['iso_code']);
		
		$buffer = '
		<style type="text/css">
			fieldset.avalaratax_fieldset td.avalaratax_column { padding: 0 18px; text-align: right; vertical-align: top;}
			fieldset.avalaratax_fieldset input[type=text] { width: 250px; }
			fieldset.avalaratax_fieldset input.avalaratax_button { margin-top: 10px; }
			fieldset.avalaratax_fieldset div#test_connection { margin-left: 18px; border: 1px solid #DFD5C3; padding: 5px; font-size: 11px; margin-bottom: 10px; width: 90%; }
			fieldset.avalaratax_fieldset a { color: #0000CC; font-weight: bold; text-decoration: underline; }
			.avalara-pagination {display: inline-block; float: right; }
			.avalara-pagination ul {list-style: none; display: inline-block; margin: 0; padding: 0;}
			.avalara-pagination ul li {margin-left: 10px; display: inline-block;}
			.clear {clear: both; margin: 0 auto;}
			.current-page {border: 1px solid #000; padding: 3px;}
			.orders-table {border-collapse:collapse;}
			.orders-table tr { vertical-align: top;}
			.orders-table tr td { border-top: 1px solid #000;}
		</style>
		<script type="text/javascript">
			$(function(){
				/* Add video */
				$(\'<div style="left: 567px; top: 11px; position: relative; width: 361px; height: 0px"><iframe width="360" height="215" src="http://www.youtube.com/embed/tm1tENVdcQ8" frameborder="0" allowfullscreen></iframe></div>\').prependTo(\'#content form:eq(0)\');
			});
		</script>
		
		<h2>'.Tools::safeOutput($this->displayName).'</h2>
		<div class="hint clear" style="display:block;">
				<a style="float: right;" target="_blank" href="http://www.prestashop.com/en/industry-partners/management/avalara"><img alt="" src="../modules/avalaratax/avalaratax_logo.png"></a>
				<div style="width: 700px; margin: 0 auto; text-align: center"><h3 style="color: red">'.$this->l('This module is intended to work ONLY in United States of America and Canada').'</h3></div>
				<h3>'.$this->l('How to configure Avalara Tax Module:').'</h3>
				- '.$this->l('Fill the Account Number, License Key, and Company Code fields with those provided by Avalara.').' <br />
				- '.$this->l('Specify your origin address. This is FROM where you are shipping the products (It has to be a ').'<b>'.$this->l('VALID UNITED STATES ADDRESS').'</b>)<br /><br />
				<h3>'.$this->l('Module goal:').'</h3>
				'.$this->l('This cloud-based service is the fastest, easiest, most accurate and affordable way to calculate sales and use tax; manage exemption certificates; file returns; and remit payments across North America and beyond.').'<br /><br />
				<h3>'.$this->l('What modifications does the module do on my store?').'</h3>
				- '.$this->l('Tax.php, AddressController.php, and AuthController.php will be overriden.').'<br />
				- '.$this->l('[Payment Tab -> Taxes] and [Payment Tab -> Tax Rules] configurations will be overriden for the US.').'<br />
				- '.$this->l('On product details (product in edit mode) an optional "Tax Code" field will be added allowing you to specify a valid tax code for each of your products.').'<br />
		</div>
		<br />
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="width2 avalaratax_fieldset">
				<legend><img src="../img/admin/cog.gif" alt="" />'.$this->l('Configuration').'</legend>
				<h3>'.$this->l('AvaTax Credentials').'</h3>';
		
		if (isset($connectionTestResult))
			$buffer .= '
			<div id="test_connection" style="background: '.$connectionTestResult[1].';">
				'.$connectionTestResult[0].'
			</div>';
		
		$buffer .= '
				<table border="0" cellspacing="5">
					<tr>
						<td class="avalaratax_column">'.$this->l('Account Number').'</td>
						<td><input type="text" name="avalaratax_account_number" value="'.(isset($confValues['AVALARATAX_ACCOUNT_NUMBER']) ? Tools::safeOutput($confValues['AVALARATAX_ACCOUNT_NUMBER']) : '').'" /></td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('License Key').'</td>
						<td><input type="text" name="avalaratax_license_key" value="'.(isset($confValues['AVALARATAX_LICENSE_KEY']) ? Tools::safeOutput($confValues['AVALARATAX_LICENSE_KEY']) : '').'" /></td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('URL').'</td>
						<td><input type="text" name="avalaratax_url" value="'.(isset($confValues['AVALARATAX_URL']) ? Tools::safeOutput($confValues['AVALARATAX_URL']) : '').'" /></td>
					</tr>
					<tr>
						<td class="avalaratax_column" style="vertical-align: top; padding-top: 3px;">'.$this->l('Company Code').'</td>
						<td><input type="text" name="avalaratax_company_code" value="'.(isset($confValues['AVALARATAX_COMPANY_CODE']) ? Tools::safeOutput($confValues['AVALARATAX_COMPANY_CODE']) : '').'" /><br />
						<span style="color: #7F7F7F; font-size: 10px;">'.$this->l('Located in the top-right corner of your AvaTax Admin Console').'</span></td>
					</tr>
				</table>
				<center><input type="submit" class="button avalaratax_button" name="SubmitAvalaraTaxSettings" value="'.$this->l('Save Settings').'" /></center>
				<hr size="1" style="margin: 14px auto;" noshade />
				<center><img src="../img/admin/exchangesrate.gif" alt="" /> <input type="submit" id="avalaratax_test_connection" class="button avalaratax_button" name="SubmitAvalaraTestConnection" value="'.$this->l('Click here to Test Connection').'" style="margin-top: 0;" /></center>
			</fieldset>
		</form>
		<br />
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="width2 avalaratax_fieldset">
				<legend><img src="../img/admin/cog.gif" alt="" />'.$this->l('Options').'</legend>
				<table border="0">
					<tr>
						<td class="avalaratax_column">'.$this->l('Enable address validation').'</td>
						<td><input type="checkbox" name="avalaratax_address_validation" value="1"'.(isset($confValues['AVALARATAX_ADDRESS_VALIDATION']) && $confValues['AVALARATAX_ADDRESS_VALIDATION'] ? ' checked="checked"' : '').' /></td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Enable tax calculation').'</td>
						<td><input type="checkbox" name="avalaratax_tax_calculation" value="1" '.(isset($confValues['AVALARATAX_TAX_CALCULATION']) && $confValues['AVALARATAX_TAX_CALCULATION'] ? ' checked="checked"' : '').' /></td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Enable address normalization in uppercase').'</td>
						<td><input type="checkbox" name="avalaratax_address_normalization" value="1" '.(isset($confValues['AVALARATAX_ADDRESS_NORMALIZATION']) && $confValues['AVALARATAX_ADDRESS_NORMALIZATION'] ? ' checked="checked"' : '').' /></td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Request timeout').'</td>
						<td><input type="text" name="avalaratax_timeout" value="'.(isset($confValues['AVALARATAX_TIMEOUT']) ? Tools::safeOutput($confValues['AVALARATAX_TIMEOUT']) : '').'" style="width: 50px;" /> <span style="font-size: 11px;">'.$this->l('seconds').'</span></td>
					</tr>
					<tr style="display: none;">

						<td class="avalaratax_column">'.$this->l('Refresh tax rate cache every: ').'</td>
						<td><input type="text" name="avalara_cache_max_limit" value="'.(isset($confValues['AVALARA_CACHE_MAX_LIMIT']) ? Tools::safeOutput($confValues['AVALARA_CACHE_MAX_LIMIT']) : '').'" style="width: 50px;" /> <span style="font-size: 11px;">'.$this->l('minutes').'</span></td>
					</tr>
					
				</table>
				<center><input type="submit" class="button avalaratax_button" name="SubmitAvalaraTaxOptions" value="'.$this->l('Save Settings').'" /> 
				<input type="submit" class="button avalaratax_button" name="SubmitAvalaraTaxClearCache" value="'.$this->l('Clear Cache').'" style="display: none"/>
				</center>
				<h3 style="margin: 10px 0px 0px; padding-top: 3px;border-top: 1px solid #000;">'.$this->l('Default Post/Commit/Cancel/Refund Options').'</h3>
				<span style="font-style: italic; font-size: 11px; color: #888;">'.$this->l('When an order\'s status is updated, the following options will be used to update Avalara\'s records.').'</span><br /><br />';
				
				// Check if the order status exist
				$orderStatus = Db::getInstance()->ExecuteS('SELECT `id_order_state`, `name` 
															FROM '._DB_PREFIX_.'order_state_lang 
															WHERE `id_lang` = '.(int)$cookie->id_lang);
				$orderStatusList = array();
				foreach ($orderStatus as $v)
					$orderStatusList[$v['id_order_state']] = Tools::safeOutput($v['name']);
				$buffer .= '
				<table>
					<th style="text-align: right; padding-right: 65px; border: 1px solid #000;">'.$this->l('Action').'</th>
					<th style="text-align: left; border: 1px solid #000; padding: 0px 15px;">'.$this->l('Order status in your store').'</th>
					<tr>
						<td class="avalaratax_column">'.$this->l('Post order to Avalara').':</td>
						<td>'.(isset($orderStatusList[Configuration::get('AVALARATAX_POST_ID')]) ? Tools::safeOutput($orderStatusList[Configuration::get('AVALARATAX_POST_ID')]) : 
							'<div style="color: red">'.$this->l('[ERROR] A default value was not found. Please, restore PrestaShop\'s default statuses.').'</div>').'
						</td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Commit order to Avalara').':</td>
						<td>'.(isset($orderStatusList[Configuration::get('AVALARATAX_COMMIT_ID')]) ? Tools::safeOutput($orderStatusList[Configuration::get('AVALARATAX_COMMIT_ID')]) : 
							'<div style="color: red">'.$this->l('[ERROR] A default value was not found. Please, restore PrestaShop\'s default statuses.').'</div>').'
						</td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Delete order from Avalara').':</td>
						<td>'.(isset($orderStatusList[Configuration::get('AVALARATAX_CANCEL_ID')]) ? Tools::safeOutput($orderStatusList[Configuration::get('AVALARATAX_CANCEL_ID')]) : 
							'<div style="color: red">'.$this->l('[ERROR] A default value was not found. Please, restore PrestaShop\'s default statuses.').'</div>').'
						</td>
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Void order in Avalara').':</td>
						<td>'.(isset($orderStatusList[Configuration::get('AVALARATAX_REFUND_ID')]) ? Tools::safeOutput($orderStatusList[Configuration::get('AVALARATAX_REFUND_ID')]) : 
							'<div style="color: red">'.$this->l('[ERROR] A default value was not found. Please, restore PrestaShop\'s default statuses.').'</div>').'
						</td>
					</tr>
				</table>
				<div class="clear"></div>
			</fieldset>
		</form>
		<br />
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="width2 avalaratax_fieldset">
				<legend><img src="../img/admin/delivery.gif" alt="" />'.$this->l('Default Origin Address').'</legend>
				<table border="0">
					<tr>
						<td class="avalaratax_column">'.$this->l('Address Line 1').'</td>
						<td><input type="text" name="avalaratax_address_line1" value="'.(isset($confValues['AVALARATAX_ADDRESS_LINE1']) ? Tools::safeOutput($confValues['AVALARATAX_ADDRESS_LINE1']) : '').'" /><br />
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Address Line 2').'</td>
						<td><input type="text" name="avalaratax_address_line2" value="'.(isset($confValues['AVALARATAX_ADDRESS_LINE2']) ? Tools::safeOutput($confValues['AVALARATAX_ADDRESS_LINE2']) : '').'" /><br />
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('City').'</td>
						<td><input type="text" name="avalaratax_city" value="'.(isset($confValues['AVALARATAX_CITY']) ? Tools::safeOutput($confValues['AVALARATAX_CITY']) : '').'" /><br />
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('State').'</td>
						<td>
							<select name="avalaratax_state" id="avalaratax_state">';
							foreach ($stateList as $state)
								$buffer .= '<option value="'.substr(strtoupper($state['iso_code']), 0, 2).'" '.($state['iso_code'] == $confValues['AVALARATAX_STATE'] ? ' selected="selected"' : '').'>'.
												Tools::safeOutput($state['name']).
											'</option>';
							$buffer .= '
							</select>
						</td>
						<br />
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Zip Code').'</td>
						<td><input type="text" name="avalaratax_zip_code" value="'.(isset($confValues['AVALARATAX_ZIP_CODE']) ? Tools::safeOutput($confValues['AVALARATAX_ZIP_CODE']) : '').'" /><br />
					</tr>
					<tr>
						<td class="avalaratax_column">'.$this->l('Country').'</td>
						<td>
							<select name="avalaratax_country" id="avalaratax_country">';
							foreach ($countryList as $country)
								$buffer .= '<option value="'.substr(strtoupper($country['iso_code']), 0, 2).'" '.($country['iso_code'] == $confValues['AVALARATAX_COUNTRY'] ? ' selected="selected"' : '').'>'.
												Tools::safeOutput($country['name']).
											'</option>';
							$buffer .= '
							</select>
						</td>
					</tr>
				</table>
				<center><input type="submit" class="button avalaratax_button" name="SubmitAvalaraAddressOptions" value="'.$this->l('Save Settings').'" /></center>
			</fieldset>
		</form>
		<br />
		<fieldset class="width2 avalaratax_fieldset">
		<legend><img src="../img/admin/statsettings.gif" alt="" />'.$this->l('AvaTax Admin Console').'</legend>
			<p><a href="https://admin-avatax.avalara.net/" target="_blank">'.$this->l('Log-in to AvaTax Admin Console').'</a></p>
		</fieldset>
		<br />';
		
		return $buffer;
	}

	/* 
	** Display a custom message for settings update
	** $text string Text to be displayed in the message
	** $type string (confirm|warn|error) Decides what color will the message have (green|yellow)
	*/
	private function _displayConfirmation($text = '', $type = 'confirm')
	{
		if ($type == 'confirm')
			$img = 'ok.gif';
		elseif ($type == 'warn')
			$img = 'warn2.png';
		elseif ($type == 'error')
			$img = 'disabled.gif';
		else
			die('Invalid type.');
		
		return '
		<div class="conf '.Tools::safeOutput($type).'">
			<img src="../img/admin/'.$img.'" alt="" title="" />
			'.(empty($text) ? $this->l('Settings updated') : $text).
			'<img src="http://www.prestashop.com/modules/avalaratax.png?sid='.
			urlencode(Configuration::get('AVALARATAX_ACCOUNT_NUMBER')).'" style="float: right;" />
		</div>';
	}

	/******************************************************************/
	/** Web-service methods *******************************************/
	/******************************************************************/
	
	private function _connectToAvalara()
	{
		include_once(dirname(__FILE__).'/sdk/AvaTax.php');
		
		new ATConfig(Configuration::get('AVALARATAX_MODE'), array('url' => Configuration::get('AVALARATAX_URL'), 'account' => Configuration::get('AVALARATAX_ACCOUNT_NUMBER'),
		'license' => Configuration::get('AVALARATAX_LICENSE_KEY'), 'trace' => true));
	}

	private function _testConnection()
	{
		$this->_connectToAvalara();
		$client = new TaxServiceSoap(Configuration::get('AVALARATAX_MODE'));
		try
		{
			$connectionTest = $client->ping();
			if ($connectionTest->getResultCode() == SeverityLevel::$Success)
			{
				try
				{
					$authorizedTest = $client->isAuthorized('GetTax');
					if ($authorizedTest->getResultCode() == SeverityLevel::$Success)
						$expirationDate = $authorizedTest->getexpires();
				}
				catch (SoapFault $exception) {}
				
				return array('<img src="../img/admin/ok.gif" alt="" /><b style="color: green;">'.$this->l('Connection Test performed successfully.').'</b><br /><br />'.$this->l('Ping version is:').' '.Tools::safeOutput($connectionTest->getVersion()).(isset($expirationDate) ? '<br /><br />'.$this->l('License Expiration Date:').' '.Tools::safeOutput($expirationDate) : ''), '#D6F5D6');
			}
		}
		catch (SoapFault $exception)
		{
			$errors = '';
			if ($exception)
				$errors .= $exception->faultstring;
			return array('<img src="../img/admin/forbbiden.gif" alt="" /><b style="color: #CC0000;">'.$this->l('Connection Test Failed.').'</b><br /><br />'.$this->l('Either the Account or License Key is incorrect. Please confirm the Account and License Key before testing the connection again.').'<br /><br /><b style="color: #CC0000;">'.$this->l('Error(s):').' '.Tools::safeOutput($errors).'</b>', '#FFD8D8');
		}
	}

	/*
	** Validates a given address
	*/
	public function validateAddress($address)
	{
		$this->_connectToAvalara();
		$client = new AddressServiceSoap(Configuration::get('AVALARATAX_MODE'));
		
		if (!($address instanceof Address))
			return false;
		
		if (!empty($address->id_state))
			$state = new State((int)$address->id_state);
		if (!empty($address->id_country))
			$country = new Country((int)$address->id_country);
		
		$avalaraAddress = new AvalaraAddress($address->address1, $address->address2, NULL, $address->city, 
			(isset($state) ? $state->iso_code : NULL), $address->postcode, (isset($country) ? $country->iso_code : NULL), 0);
		
		$buffer = array();
		try
		{
			$request = new ValidateRequest($avalaraAddress, TextCase::$Upper, false);
			$result = $client->Validate($request);
			$addresses = $result->ValidAddresses;
			
			$buffer['ResultCode'] = Tools::safeOutput($result->getResultCode());
			if($result->getResultCode() != SeverityLevel::$Success)
			{
				foreach($result->getMessages() as $msg)
				{
					$buffer['Messages']['Name'] = Tools::safeOutput($msg->getName());
					$buffer['Messages']['Summary'] = Tools::safeOutput($msg->getSummary());
				}
			}
			else
			{
				foreach($result->getvalidAddresses() as $valid)
				{
					$buffer['Normalized']['Line1'] = Tools::safeOutput($valid->getline1());
					$buffer['Normalized']['Line2'] = Tools::safeOutput($valid->getline2());
					$buffer['Normalized']['City']= Tools::safeOutput($valid->getcity());
					$buffer['Normalized']['Region'] = Tools::safeOutput($valid->getregion());
					$buffer['Normalized']['PostalCode'] = Tools::safeOutput($valid->getpostalCode());
					$buffer['Normalized']['Country'] = Tools::safeOutput($valid->getcountry());
					$buffer['Normalized']['County'] = Tools::safeOutput($valid->getcounty());
					$buffer['Normalized']['FIPS'] = Tools::safeOutput($valid->getfipsCode());
					$buffer['Normalized']['PostNet'] = Tools::safeOutput($valid->getpostNet());
					$buffer['Normalized']['CarrierRoute'] = Tools::safeOutput($valid->getcarrierRoute());
					$buffer['Normalized']['AddressType'] = Tools::safeOutput($valid->getaddressType());
				}
			}
		}
		catch(SoapFault $exception)
		{
			if($exception)
				$buffer['Exception']['FaultString'] = Tools::safeOutput($exception->faultstring);
			$buffer['Exception']['LastRequest'] = Tools::safeOutput($client->__getLastRequest());
			$buffer['Exception']['LastResponse'] = Tools::safeOutput($client->__getLastResponse());
		}
		return $buffer;
	}

	/*
	** Executes tax actions on documents
	** $params array
	** 		type : (default SalesOrder) SalesOrder|SalesInvoice|ReturnInvoice
	** 		cart : (required for SalesOrder and SalesInvoice) Cart object
	** 		DocCode : (required in ReturnInvoice, and when 'cart' is not set) Specify the Document Code
	*/
	public function getTax($products = array(), $params = array())
	{
		global $cookie;
		$addressDest = array();
		
		$confValues = Configuration::getMultiple(array('AVALARATAX_COMPANY_CODE', 'AVALARATAX_ADDRESS_LINE1', 
			'AVALARATAX_ADDRESS_LINE2', 'AVALARATAX_CITY', 'AVALARATAX_STATE', 'AVALARATAX_ZIP_CODE'));
		if (!isset($params['type']))
			$params['type'] = 'SalesOrder';
		
		$this->_connectToAvalara();
		$client = new TaxServiceSoap(Configuration::get('AVALARATAX_MODE'));
		$request= new GetTaxRequest();
		
		// Get the address from customer profile
		if (isset($cookie) && isset($cookie->id_customer) && $cookie->id_customer)
		{
			$addressId = Db::getInstance()->ExecuteS('SELECT `id_address` 
													FROM '._DB_PREFIX_.'address
													WHERE id_customer = '.(int)$cookie->id_customer);
			
			$address = new Address((int)$addressId[0]['id_address']);
			if (!empty($address->id_state))
				$state = new State((int)$address->id_state);
			$addressDest['Line1'] = $address->address1;
			$addressDest['Line2'] = $address->address2;
			$addressDest['City'] = $address->city;
			$addressDest['Region'] = (isset($state)) ? $state->iso_code : '';
			$addressDest['PostalCode'] = $address->postcode;
			
			// Try to normalize the address depending on option in the BO
			if (Configuration::get('AVALARATAX_ADDRESS_NORMALIZATION'))
				$normalizedAddress = $this->validateAddress($address);
			if (isset($normalizedAddress['Normalized']))
				$addressDest = $normalizedAddress['Normalized'];
			
			// Add Destination address (Customer address)
			$destination = new AvalaraAddress();
			$destination->setLine1($addressDest['Line1']); 
			$destination->setLine2($addressDest['Line2']); 
			$destination->setCity($addressDest['City']);
			$destination->setRegion($addressDest['Region']);
			$destination->setPostalCode($addressDest['PostalCode']);
			$request->setDestinationAddress($destination);
		}
		
		// Origin Address (Store Address or address setup in BO)
		$origin = new AvalaraAddress();
		$origin->setLine1(isset($confValues['AVALARATAX_ADDRESS_LINE1']) ? $confValues['AVALARATAX_ADDRESS_LINE1'] : '');
		$origin->setLine2(isset($confValues['AVALARATAX_ADDRESS_LINE2']) ? $confValues['AVALARATAX_ADDRESS_LINE2'] : '');
		$origin->setCity(isset($confValues['AVALARATAX_CITY']) ? $confValues['AVALARATAX_CITY'] : '');
		$origin->setRegion(isset($confValues['AVALARATAX_STATE']) ? $confValues['AVALARATAX_STATE'] : '');
		$origin->setPostalCode(isset($confValues['AVALARATAX_ZIP_CODE']) ? $confValues['AVALARATAX_ZIP_CODE'] : '');
		$request->setOriginAddress($origin);
		
		$request->setCompanyCode(isset($confValues['AVALARATAX_COMPANY_CODE']) ? $confValues['AVALARATAX_COMPANY_CODE'] : '');
		$orderId = isset($params['cart']) ? $params['cart']->id : (int)$params['DocCode'];
		$nowTime = date('mdHis');
		
		// Type: Only supported types are SalesInvoice or SalesOrder
		if ($params['type'] == 'SalesOrder')						// SalesOrder: Occurs when customer adds product to the cart (generally to check how much the tax will be)
			$request->setDocType(DocumentType::$SalesOrder);
		elseif ($params['type']  == 'SalesInvoice')					// SalesInvoice: Occurs when customer places an order (It works like commitToAvalara()).
		{
			$request->setDocType(DocumentType::$SalesInvoice);
			$orderId = Db::getInstance()->ExecuteS('SELECT `id_order` FROM '._DB_PREFIX_.'orders WHERE `id_cart` = '.(int)$params['cart']->id);
			$orderId = $orderId[0]['id_order']; // Make sure we got the orderId, even if it was/wasn't passed in $params['DocCode']
		}
		elseif ($params['type']  == 'ReturnInvoice')
		{
			$orderId = isset($params['type']) && $params['type'] == 'ReturnInvoice' ? $orderId.'.'.$nowTime : $orderId;
			$orderDate = Db::getInstance()->ExecuteS('SELECT `id_order`, `date_add` 
													FROM '._DB_PREFIX_.'orders 
													WHERE '.(isset($params['cart']) ? '`id_cart` = '.(int)$params['cart']->id : 
													'`id_order` = '.(int)$params['DocCode']));
			
			$request->setDocType(DocumentType::$ReturnInvoice);
			$request->setCommit(true);
			$taxOverride = new TaxOverride;
			$taxOverride->setTaxOverrideType(TaxOverrideType::$TaxDate);
			$taxOverride->setTaxDate(date('Y-m-d', strtotime($orderDate[0]['date_add'])));
			$taxOverride->setReason('Refund');
			$request->setTaxOverride($taxOverride);
		}
		
		if (isset($cookie->id_customer))
			$customerCode = $cookie->id_customer;
		else
		{
			if (isset($params['DocCode']))
				$id_order = $params['DocCode'];
			elseif (isset($_POST['id_order']))
				$id_order = $_POST['id_order'];
			elseif (isset($params['id_order']))
				$id_order = (int)$params['id_order'];
			else
				$id_order = 0;
			
			$customerCode = Db::getInstance()->ExecuteS('SELECT `id_customer`
														FROM '._DB_PREFIX_.'orders
														WHERE `id_order` = '.(int)$id_order);
			
			if (count($customerCode) && isset($customerCode[0]['id_customer']))
				$customerCode = (int)$customerCode[0]['id_customer'];
		}
		
		$request->setDocCode('Order '.Tools::safeOutput($orderId)); // Order Id - has to be float due to the . and more numbers for returns
		$request->setDocDate(date('Y-m-d'));					// date
		$request->setCustomerCode('CustomerID: '.(int)$customerCode); // string Required
		$request->setCustomerUsageType('');						// string Entity Usage
		$request->setDiscount(0.00);							// decimal
		$request->setDetailLevel(DetailLevel::$Tax);			// Summary or Document or Line or Tax or Diagnostic
		
		// Add line
		$lines = array();
		$i = 0;
		foreach ($products as $product)
		{
			// Retrieve the tax_code for the current product if not defined
			$taxCode = !isset($product['tax_code']) ? $this->getProductTaxCode((int)$product['id_product']) : $product['tax_code'];
			if (isset($product['id_product']))
			{
				$line = new Line();
				$line->setNo($i++); 		// string line Number of invoice ($i)
				$line->setItemCode((int)$product['id_product'].' - '.substr($product['name'], 0, 20));
				$line->setDescription(substr(Tools::safeOutput($product['name'].' - '.$product['description_short']), 0, 250));
				$line->setTaxCode($taxCode);
				$line->setQty(isset($product['quantity']) ? (float)$product['quantity'] : 1);
				$line->setAmount($params['type'] == 'ReturnInvoice' && (float)$product['total'] > 0 ? (float)$product['total'] * -1 : (float)$product['total']);
				$line->setDiscounted(false);
				
				$lines[] = $line;
			}
		}
		
		// Send shipping as new line
		if (isset($params['cart']))
		{
			$line = new Line();
			$line->setNo ('Shipping');			// string line Number of invoice ($i)
			$line->setItemCode('Shipping');
			$line->setDescription('Shipping costs');
			$line->setTaxCode('FR020100'); 		// Default TaxCode for Shipping. Avalara will decide depending on the State if taxes should be charged or not
			$line->setQty(1);
			$line->setAmount((float)$params['cart']->getOrderTotal(false, Cart::ONLY_SHIPPING));
			$line->setDiscounted(false);
			$lines[] = $line;
		}
		
		$request->setLines($lines);
		$buffer = array();
		try
		{
			$result = $client->getTax($request);
			$buffer['ResultCode'] = Tools::safeOutput($result->getResultCode());
			if ($result->getResultCode() == SeverityLevel::$Success)
			{
				$buffer['DocCode'] = Tools::safeOutput($request->getDocCode());
				$buffer['TotalAmount'] = Tools::safeOutput($result->getTotalAmount());
				$buffer['TotalTax'] = Tools::safeOutput($result->getTotalTax());
				$buffer['NowTime'] = $nowTime;
				foreach($result->getTaxLines() as $ctl)
				{
					$buffer['TaxLines'][$ctl->getNo()]['GetTax'] = Tools::safeOutput($ctl->getTax());
					$buffer['TaxLines'][$ctl->getNo()]['TaxCode'] = Tools::safeOutput($ctl->getTaxCode());
					
					foreach($ctl->getTaxDetails() as $ctd)
					{
						$buffer['TaxLines'][$ctl->getNo()]['TaxDetails']['JurisType'] = Tools::safeOutput($ctd->getJurisType());
						$buffer['TaxLines'][$ctl->getNo()]['TaxDetails']['JurisName'] = Tools::safeOutput($ctd->getJurisName());
						$buffer['TaxLines'][$ctl->getNo()]['TaxDetails']['Region'] = Tools::safeOutput($ctd->getRegion());
						$buffer['TaxLines'][$ctl->getNo()]['TaxDetails']['Rate'] = Tools::safeOutput($ctd->getRate());
						$buffer['TaxLines'][$ctl->getNo()]['TaxDetails']['Tax'] = Tools::safeOutput($ctd->getTax());
					}
				}
			}
			else
			{
				foreach($result->getMessages() as $msg)
				{
					$buffer['Messages']['Name'] = Tools::safeOutput($msg->getName());
					$buffer['Messages']['Summary'] = Tools::safeOutput($msg->getSummary());
				}
			}
		}
		catch(SoapFault $exception)
		{
			if($exception)
				$buffer['Exception']['FaultString'] = Tools::safeOutput($exception->faultstring);
			$buffer['Exception']['LastRequest'] = Tools::safeOutput($client->__getLastRequest());
			$buffer['Exception']['LastResponse'] = Tools::safeOutput($client->__getLastResponse());
		}
		return $buffer;
	}

	/*
	** Make changes to an order, get order history or checks if the module is authorized
	**
	** $type string commit|post|cancel|history Transaction type
	** $params array Key=>Values depending on the transaction type
	**		DocCode: (required for ALL except for isAuthorized) Document unique identifier
	** 		DocDate: (required for post) Date in which the transaction was made (today's date if post)
	** 		IdCustomer: (required for post) Customer ID 
	**		TotalAmount: (required for post) Order total amount in case of Post type
	**		TotalTax: (required for post) Total tax amount for current order
	**		CancelCode: (required for cancel only) D|P Sets the cancel code (D: Document Deleted | P: Post Failed)
	*/
	public function tax($type, $params = array())
	{
		global $cookie;
		
		$this->_connectToAvalara();
		$client = new TaxServiceSoap(Configuration::get('AVALARATAX_MODE'));
		if ($type == 'commit')
			$request= new CommitTaxRequest();
		elseif ($type == 'post')
		{
			$request= new PostTaxRequest();
			$request->setDocDate($params['DocDate']);
			$request->setTotalAmount($params['TotalAmount']);
			$request->setTotalTax($params['TotalTax']);
		}
		elseif ($type == 'cancel')
		{
			$request= new CancelTaxRequest();
			if ($params['CancelCode'] == 'D')
				$code = CancelCode::$DocDeleted;
			elseif ($params['CancelCode'] == 'P')
				$code = CancelCode::$PostFailed;
			elseif ($params['CancelCode'] == 'V')
				$code = CancelCode::$DocVoided;
			else
				die('Invalid cancel code.');
			
			$request->setCancelCode($code);
		}
		elseif ($type == 'history')
		{
			$request= new GetTaxHistoryRequest();
			$request->setDetailLevel(DetailLevel::$Document);
		}
		
		if ($type != 'isAuthorized')
		{
			$request->setDocCode('Order '.(int)$params['DocCode']);
			$request->setDocType(DocumentType::$SalesInvoice);
			$request->setCompanyCode(Configuration::get('AVALARATAX_COMPANY_CODE'));
		}
		
		$buffer = array();
		try
		{
			if ($type == 'commit')
				$result = $client->commitTax($request);
			elseif ($type == 'post')
				$result = $client->postTax($request);
			elseif ($type == 'cancel')
				$result = $client->cancelTax($request);
			elseif ($type == 'isAuthorized')
				$result = $client->isAuthorized('GetTax');
			elseif ($type == 'history')
			{
				$result = $client->getTaxHistory($request);
				$buffer['Invoice'] = $result->getGetTaxRequest()->getDocCode();
				$buffer['Status'] = $result->getGetTaxResult()->getDocStatus();
			}
			
			$buffer['ResultCode'] = $result->getResultCode();
			
			if ($result->getResultCode()!= SeverityLevel::$Success)
			{
				foreach($result->getMessages() as $msg)
				{
					$buffer['Messages']['Name'] = Tools::safeOutput($msg->getName());
					$buffer['Messages']['Summary'] = Tools::safeOutput($msg->getSummary());
				}
			}
		}
		catch(SoapFault $exception)
		{
			if($exception)
				$buffer['Exception']['FaultString'] = Tools::safeOutput($exception->faultstring);
			$buffer['Exception']['LastRequest'] =  Tools::safeOutput($client->__getLastRequest());
			$buffer['Exception']['LastResponse'] =  Tools::safeOutput($client->__getLastResponse());
		}
		return $buffer;
	}

	public function postToAvalara($params)
	{
		$commitResult = $this->tax('history', array('DocCode' => (int)$params['id_order']));
		if (isset($commitResult['ResultCode']) && $commitResult['ResultCode'] == 'Success')
		{
			$params['CancelCode'] = 'D';
			$this->cancelFromAvalara($params);
			$this->cancelFromAvalara($params); // Twice because first call only voids the order, and 2nd call deletes it
		}
		
		// Grab the info to post to Avalara in English.
		$order = new Order((isset($_POST['id_order']) ? (int)$_POST['id_order'] : (int)$params['id_order']));
		$allProducts = Db::getInstance()->ExecuteS('SELECT p.`id_product`, pl.`name`, pl.`description_short`, 
													od.`product_price` as price, od.`reduction_percent`, 
													od.`reduction_amount`, od.`product_quantity` as quantity, atc.`tax_code`
													FROM '._DB_PREFIX_.'order_detail od
													LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
													LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
													LEFT JOIN '._DB_PREFIX_.'avalara_taxcodes atc ON (atc.id_product = p.id_product)
													WHERE pl.`id_lang` = 1 AND od.`id_order` = '.(isset($_POST['id_order']) ? (int)$_POST['id_order'] : 
													(int)$params['id_order']));
		$products = array();
		foreach ($allProducts as $v)
		{
			$products[] = array('id_product' => $v['id_product'],
							'name' => $v['name'],
							'description_short' => $v['description_short'],
							'quantity' => $v['quantity'],
							'total' => $v['quantity'] * ($v['price'] - ($v['price'] * ($v['reduction_percent'] / 100)) - ($v['reduction_amount'])), // Including those products with discounts
							'tax_code' => $v['tax_code']);
		}
		
		$cart = new Cart((int)$order->id_cart);
		$getTaxResult = $this->getTax($products, array('type' => 'SalesInvoice', 
													'cart' => $cart, 
													'id_order' => (isset($_POST['id_order']) ? (int)$_POST['id_order'] : (int)$params['id_order'])));
		$commitResult = $this->tax('post', array('DocCode' => (isset($_POST['id_order']) ? (int)$_POST['id_order'] : (int)$params['id_order']), 
									'DocDate' => date('Y-m-d'), 
									'IdCustomer' => (int)$cart->id_customer,
									'TotalAmount' => (float)$getTaxResult['TotalAmount'],
									'TotalTax' => (float)$getTaxResult['TotalTax']));
		if (isset($commitResult['ResultCode']) 
			&& ( $commitResult['ResultCode'] == 'Warning' 
			|| $commitResult['ResultCode'] == 'Error' 
			|| $commitResult['ResultCode'] == 'Exception'))
				return $this->_displayConfirmation($this->l('The following error was generated while cancelling the orders you selected.'.
					'<br /> - '.Tools::safeOutput($commitResult['Messages']['Summary'])), 'error');
		else
			return $this->_displayConfirmation($this->l('The orders you selected were posted.'));
	}

	public function commitToAvalara($params)
	{
		// Create the order before commiting to Avalara
		$this->postToAvalara($params);
		$commitResult = $this->tax('history', array('DocCode' => $params['id_order']));
		if (isset($commitResult['ResultCode']) && $commitResult['ResultCode'] == 'Success')
		{
			$commitResult = $this->tax('commit', array('DocCode' => (int)$params['id_order']));
			if (isset($commitResult['Exception']) 
				|| isset($commitResult['ResultCode']) 
				&& ( $commitResult['ResultCode'] == 'Warning' 
				|| $commitResult['ResultCode'] == 'Error' 
				|| $commitResult['ResultCode'] == 'Exception'))
					return ($this->_displayConfirmation($this->l('The following error was generated while committing the orders you selected to Avalara.').
						(isset($commitResult['Messages']) ? '<br /> - '.Tools::safeOutput($commitResult['Messages']['Summary']) : '').
						(isset($commitResult['Exception']) ? '<br /> - '.Tools::safeOutput($commitResult['Exception']['FaultString']) : ''), 'error'));
			else
				return $this->_displayConfirmation($this->l('The orders you selected were committed.'));
		}
		
		// Orders prior Avalara module installation will trigger an "Invalid Status" error. For this reason, the user won't be alerted here.
	}

	public function cancelFromAvalara($params)
	{
		$commitResult = $this->tax('history', array('DocCode' => $params['id_order']));
		$hasRefund = Db::getInstance()->ExecuteS('SELECT COUNT(`id_order`) as qtyProductRefunded
												FROM `ps_order_detail`
												WHERE `id_order` = '.(int)$params['id_order'].'
												AND (`product_quantity_refunded` IS NOT NULL AND `product_quantity_refunded` > 0)');
		
		if (!($commitResult['Status'] == 'Committed' && (int)$hasRefund[0]['qtyProductRefunded'] > 0))
		{
			if (isset($commitResult['Status']) && $commitResult['Status'] == 'Temporary')
				$this->postToAvalara($params);
			$commitResult = $this->tax('cancel', array('DocCode' => (int)$params['id_order'], 
													'CancelCode' => isset($params['CancelCode']) ? $params['CancelCode'] : 'V' ));
			if (isset($commitResult['ResultCode']) 
				&& ( $commitResult['ResultCode'] == 'Warning' 
				|| $commitResult['ResultCode'] == 'Error' 
				|| $commitResult['ResultCode'] == 'Exception'))
					return $this->_displayConfirmation($this->l('The following error was generated while cancelling the orders you selected.').
						' <br /> - '.Tools::safeOutput($commitResult['Messages']['Summary']), 'error');
			else
				return $this->_displayConfirmation($this->l('The orders you selected were cancelled.'));
		}
	}

	/*
	** Fix $_POST to validate/normalize the address on address creation/update
	*/
	public function fixPOST()
	{
		$address = new Address((int)$_POST['id_address']);
		$address->address1 = $_POST['address1'];
		$address->address2 = $_POST['address2'];
		$address->city = $_POST['city'];
		$address->region = $_POST['region'];
		$address->postcode = $_POST['postcode'];
		$address->id_country = $_POST['id_country'];
		
		// US customer: normalize the address
		if ($address->id_country == Country::getByIso('US'))
		{
			if ($this->tax('isAuthorized') && Configuration::get('AVALARATAX_ADDRESS_VALIDATION'))
			{
				// Validate address
				$normalizedAddress = $this->validateAddress($address);
				if (isset($normalizedAddress['ResultCode']) && $normalizedAddress['ResultCode'] == 'Success')
				{
					$_POST['address1'] = Tools::safeOutput($normalizedAddress['Normalized']['Line1']);
					$_POST['address2'] = Tools::safeOutput($normalizedAddress['Normalized']['Line2']);
					$_POST['city'] = Tools::safeOutput($normalizedAddress['Normalized']['City']);
					$_POST['postcode'] =  Tools::safeOutput(substr($normalizedAddress['Normalized']['PostalCode'], 0, strpos($normalizedAddress['Normalized']['PostalCode'], '-')));
				}
			}
			else
			{
				include_once(_PS_TAASC_PATH_.'AddressStandardizationSolution.php');
				$normalize = new AddressStandardizationSolution;
				$_POST['address1'] = Tools::safeOutput($normalize->AddressLineStandardization($address->address1));
				$_POST['address2'] = Tools::safeOutput($normalize->AddressLineStandardization($address->address2));
			}
		}
	}
	
	public function getProductTaxCode($idProduct)
	{
		$result = Db::getInstance()->ExecuteS('SELECT `tax_code` 
											FROM '._DB_PREFIX_.'avalara_taxcodes atc 
											WHERE atc.`id_product` = '.(int)$idProduct);
		return isset($result[0]) ? Tools::safeOutput($result[0]['tax_code']) : 0;
	}

	private function purgeTempTable()
	{
		return Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'avalara_temp`');
	}
}


function avalaraAutoload($className)
{
	$className = str_replace(chr(0), '', $className);
	if (!preg_match('/^\w+$/', $className))
		die('Invalid classname.');
	
	$moduleDir = dirname(__FILE__).'/';
	
	if (file_exists($moduleDir.'sdk/classes/'.$className.'.class.php'))
		require_once($moduleDir.'sdk/classes/'.$className.'.class.php');
	elseif (file_exists($moduleDir.'sdk/classes/BatchSvc/'.$className.'.class.php'))
		require_once($moduleDir.'sdk/classes/BatchSvc/'.$className.'.class.php');
	elseif (function_exists('__autoload'))
		__autoload($className);
}