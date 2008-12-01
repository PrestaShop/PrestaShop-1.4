<?php

define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');

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

if ($step = intval(Tools::getValue('ajaxProductTab')))
{
	require_once(dirname(__FILE__).'/init.php');
	require_once(dirname(__FILE__).'/tabs/AdminCatalog.php');
	$catalog = new AdminCatalog();
	$admin = new AdminProducts();
	
	$languages = Language::getLanguages();
	$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
	$product = new Product(intval(Tools::getValue('id_product')));
	if (!Validate::isLoadedObject($product))
		die (Tools::displayError('product cannot be loaded'));
	
	$switchArray = array(3 => 'displayFormAttributes', 4 => 'displayFormFeatures', 5 => 'displayFormCustomization', 6 => 'displayFormQuantityDiscount');
	$currentIndex = 'index.php?tab=AdminCatalog';
	if (key_exists($step, $switchArray))
		$admin->{$switchArray[$step]}($product, $languages, $defaultLanguage);
}

if (isset($_GET['getAvailableFields']) and isset($_GET['entity']))
{
	$currentIndex = 'index.php?tab=AdminImport';
	$jsonArray = array();
	require_once(dirname(__FILE__).'/init.php');
	require_once(dirname(__FILE__).'/tabs/AdminImport.php');
	$import = new AdminImport();

	$languages = Language::getLanguages();
	$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
	$fields = $import->getAvailableFields(true);
	foreach ($fields AS $field)
		$jsonArray[] = '{field: \''.addslashes($field).'\'}';
	die('['.implode(',', $jsonArray).']');
}

?>