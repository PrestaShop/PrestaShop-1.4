<?php

define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');
/* Getting cookie or logout */
require_once(dirname(__FILE__).'/init.php');

if (isset($_GET['ajaxProductManufacturers']))
{
	$currentIndex = 'index.php?tab=AdminCatalog';
	$manufacturers = Manufacturer::getManufacturers();
	if ($manufacturers)
	{
		$jsonArray = array();
		foreach ($manufacturers AS $manufacturer)
			$jsonArray[] = '{optionValue: '.$manufacturer['id_manufacturer'].', optionDisplay: \''.addslashes($manufacturer['name']).'\'}';
		die('['.implode(',', $jsonArray).']');
	}
}

if (isset($_GET['ajaxProductSuppliers']))
{
	$currentIndex = 'index.php?tab=AdminCatalog';
	$suppliers = Supplier::getSuppliers();
	if ($suppliers)
	{
		$jsonArray = array();
		foreach ($suppliers AS $supplier)
			$jsonArray[] = '{optionValue: '.$supplier['id_supplier'].', optionDisplay: \''.addslashes($supplier['name']).'\'}';
		die('['.implode(',', $jsonArray).']');
	}
}

if (isset($_GET['ajaxProductAccessories']))
{
	$currentIndex = 'index.php?tab=AdminCatalog';
	$jsonArray = array();
	
	$products = Db::getInstance()->ExecuteS('
	SELECT p.`id_product`, pl.`name`
	FROM `'._DB_PREFIX_.'product` p
	NATURAL LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
	WHERE pl.`id_lang` = '.intval(Tools::getValue('id_lang')).'
	AND p.`id_product` != '.intval(Tools::getValue('id_product')).'
	AND p.`id_product` NOT IN (
		SELECT a.`id_product_2`
		FROM `'._DB_PREFIX_.'accessory` a
		WHERE a.`id_product_1` = '.intval(Tools::getValue('id_product')).')');
	
	foreach ($products AS $accessory)
		$jsonArray[] = '{value: \''.intval($accessory['id_product']).'-'.addslashes($accessory['name']).'\', text:\''.intval($accessory['id_product']).' - '.addslashes($accessory['name']).'\'}';
	die('['.implode(',', $jsonArray).']');
}

if (isset($_GET['ajaxDiscountCustomers']))
{
	$currentIndex = 'index.php?tab=AdminDiscounts';
	$jsonArray = array();
	$filter = Tools::getValue('filter');
	
	$customers = Db::getInstance()->ExecuteS('
	SELECT `id_customer`, `email`, CONCAT(`lastname`, \' \', `firstname`) as name
	FROM `'._DB_PREFIX_.'customer`
	WHERE email LIKE "%'.pSQL($filter).'%"
	'.(intval($filter) ? 'OR id_customer = '.intval($filter) : '').'
	OR CONCAT(`firstname`, \' \', `lastname`) LIKE "%'.pSQL($filter).'%"
	OR CONCAT(`lastname`, \' \', `firstname`) LIKE "%'.pSQL($filter).'%"
	ORDER BY CONCAT(`lastname`, \' \', `firstname`) ASC
	LIMIT 50');
	
	foreach ($customers AS $customer)
		$jsonArray[] = '{value:'.intval($customer['id_customer']).', text:\''.addslashes($customer['name']).' ('.addslashes($customer['email']).')\'}';
	die('['.implode(',', $jsonArray).']');
}

if (Tools::getValue('page') == 'prestastore')
	readfile('http://www.prestastore.com/adminmodules.php');

if ($step = intval(Tools::getValue('ajaxProductTab')))
{
	require_once(dirname(__FILE__).'/tabs/AdminCatalog.php');
	$catalog = new AdminCatalog();
	$admin = new AdminProducts();
	
	$languages = Language::getLanguages();
	$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
	$product = new Product(intval(Tools::getValue('id_product')));
	if (!Validate::isLoadedObject($product))
		die (Tools::displayError('product cannot be loaded'));
	
	$switchArray = array(3 => 'displayFormAttributes', 4 => 'displayFormFeatures', 5 => 'displayFormCustomization', 6 => 'displayFormQuantityDiscount', 7 => 'displayFormAttachments');
	$currentIndex = 'index.php?tab=AdminCatalog';
	if (key_exists($step, $switchArray))
		$admin->{$switchArray[$step]}($product, $languages, $defaultLanguage);
}

if (isset($_GET['getAvailableFields']) and isset($_GET['entity']))
{
	$currentIndex = 'index.php?tab=AdminImport';
	$jsonArray = array();
	require_once(dirname(__FILE__).'/tabs/AdminImport.php');
	$import = new AdminImport();

	$languages = Language::getLanguages();
	$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
	$fields = $import->getAvailableFields(true);
	foreach ($fields AS $field)
		$jsonArray[] = '{field: \''.addslashes($field).'\'}';
	die('['.implode(',', $jsonArray).']');
}

if (array_key_exists('ajaxModulesPositions', $_POST))
{				
		$id_module = intval(Tools::getValue('id_module'));
		$id_hook = intval(Tools::getValue('id_hook'));
		$way = intval(Tools::getValue('way'));	
		$positions = Tools::getValue(strval($id_hook));
		$position = (is_array($positions)) ? array_search($id_hook.'_'.$id_module, $positions) : null;
		$module = Module::getInstanceById($id_module);
		if (Validate::isLoadedObject($module))
			if ($module->updatePosition($id_hook, $way, $position))
				die(true);
			else
				die('{\'hasError\' : true, errors : \'Can not update module position\'}');	
		else
			die('{\'hasError\' : true, errors : \'This module can not be loaded\'}');
}

if (array_key_exists('ajaxProductsPositions', $_POST))
{				
		$way = intval(Tools::getValue('way'));
		$alternate = intval(Tools::getValue('alternate'));
		$id_product = intval(Tools::getValue('id_product'));
		$id_category = intval(Tools::getValue('id_category'));
		$positions = Tools::getValue(strval($id_category));

		if (is_array($positions))
		{
			foreach ($positions AS $key => $value)
			{
				$pos = explode('_', $value);
				if ((isset($pos[1]) AND isset($pos[2])) AND ($pos[1] == $id_category AND $pos[2] == $id_product))
				{
					if ($way == 0)
					{
						$pos = explode('_', $positions[$key + 1]);
						$position = $pos[3];
					}
					else
					{
						$pos = explode('_', $positions[$key - 1]);
						$position = $pos[3];
					}
					break;
				}
			}
		}

		$product = new Product($id_product);
		if (Validate::isLoadedObject($product))
		{
			if($product->updatePosition($alternate, $position))
				die(true);
			else
				die('{\'hasError\' : true, errors : \'Can not update product position\'}');
		}
		else
			die('{\'hasError\' : true, errors : \'This product can not be loaded\'}');
}

if (isset($_GET['ajaxProductPackItems']))
{
	$jsonArray = array();
	$products = Db::getInstance()->ExecuteS('
	SELECT p.`id_product`, pl.`name`
	FROM `'._DB_PREFIX_.'product` p
	NATURAL LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
	WHERE pl.`id_lang` = '.intval(Tools::getValue('id_lang')).'
	AND p.`id_product` NOT IN (SELECT DISTINCT id_product_pack FROM `'._DB_PREFIX_.'pack`)
	AND p.`id_product` != '.intval(Tools::getValue('id_product')));
	
	foreach ($products AS $packItem)
		$jsonArray[] = '{value: \''.intval($packItem['id_product']).'-'.addslashes($packItem['name']).'\', text:\''.intval($packItem['id_product']).' - '.addslashes($packItem['name']).'\'}';
	die('['.implode(',', $jsonArray).']');
}

?>
