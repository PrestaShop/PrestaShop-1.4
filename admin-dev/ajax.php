<?php

define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');
/* Getting cookie or logout */
require_once(dirname(__FILE__).'/init.php');

if (isset($_GET['changeParentUrl']))
	echo '<script type="text/javascript">parent.parent.document.location.href = "'.addslashes(urldecode(Tools::getValue('changeParentUrl'))).'";</script>';
if (isset($_GET['installBoughtModule']))
{
	if (!class_exists('ZipArchive', false))
		die(displayJavascriptAlert('Host does not handle Zip files'));
	$zip = new ZipArchive();
	$file = false;
	while ($file === false OR file_exists(_PS_MODULE_DIR_.$file))
		$file = uniqid();
	$file = _PS_MODULE_DIR_.$file.'.zip';
	if (!copy('http://addons.prestashop.com/iframe/getboughtfile.php?id_order_detail='.Tools::getValue('id_order_detail').'&token='.Tools::getValue('token'), $file))
		die(displayJavascriptAlert('Cannot copy file'));
	$first6 = fread($fd = fopen($file, 'r'), 6);
	if (!strncmp($first6, 'Error:', 6))
	{
		fclose($fd);
		unlink($file);
		die(displayJavascriptAlert(fread($fd, 1024)));
	}
	fclose($fd);
	if ($zip->open($file) !== true OR !$zip->extractTo(_PS_MODULE_DIR_) OR !$zip->close())
	{
		unlink($file);
		die(displayJavascriptAlert('Cannot unzip file'));
	}
	unlink($file);
	die(displayJavascriptAlert('Module copied to disk'));
}

function displayJavascriptAlert($s){echo '<script type="text/javascript">alert(\''.addslashes($s).'\');</script>';}

if (isset($_GET['ajaxProductManufacturers']))
{
	$currentIndex = 'index.php?tab=AdminCatalog';
	$manufacturers = Manufacturer::getManufacturers();
	if ($manufacturers)
	{
		$jsonArray = array();
		foreach ($manufacturers AS $manufacturer)
			$jsonArray[] = '{"optionValue": "'.$manufacturer['id_manufacturer'].'", "optionDisplay": "'.addslashes($manufacturer['name']).'"}';
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
			$jsonArray[] = '{"optionValue": "'.$supplier['id_supplier'].'", "optionDisplay": "'.addslashes($supplier['name']).'"}';
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
	WHERE pl.`id_lang` = '.(int)(Tools::getValue('id_lang')).'
	AND p.`id_product` != '.(int)(Tools::getValue('id_product')).'
	AND p.`id_product` NOT IN (
		SELECT a.`id_product_2`
		FROM `'._DB_PREFIX_.'accessory` a
		WHERE a.`id_product_1` = '.(int)(Tools::getValue('id_product')).')
	ORDER BY pl.`name`');
	
	foreach ($products AS $accessory)
		$jsonArray[] = '{"value: "'.(int)($accessory['id_product']).'-'.addslashes($accessory['name']).'", "text":"'.(int)($accessory['id_product']).' - '.addslashes($accessory['name']).'"}';
	die('['.implode(',', $jsonArray).']');
}

if (isset($_GET['ajaxDiscountCustomers']))
{
	global $cookie;

	$currentIndex = 'index.php?tab=AdminDiscounts';
	$jsonArray = array();
	$filter = Tools::getValue('filter');
	
	if (Validate::isBool_Id($filter))
		$filterArray = explode('_', $filter); 
	
	$customers = Db::getInstance()->ExecuteS('
	SELECT `id_customer`, `email`, CONCAT(`lastname`, \' \', `firstname`) as name
	FROM `'._DB_PREFIX_.'customer`
	WHERE `deleted` = 0
	AND '.(Validate::isUnsignedInt($filter) ? '`id_customer` = '.(int)($filter) : '(`email` LIKE "%'.pSQL($filter).'%"
	'.((Validate::isBool_Id($filter) AND $filterArray[0] == 0) ? 'OR `id_customer` = '.(int)($filterArray[1]) : '').'
	'.(Validate::isUnsignedInt($filter) ? '`id_customer` = '.(int)($filter) : '').'
	OR CONCAT(`firstname`, \' \', `lastname`) LIKE "%'.pSQL($filter).'%"
	OR CONCAT(`lastname`, \' \', `firstname`) LIKE "%'.pSQL($filter).'%")').'
	ORDER BY CONCAT(`lastname`, \' \', `firstname`) ASC
	LIMIT 50');
	
	$groups = Db::getInstance()->ExecuteS('
	SELECT g.`id_group`, gl.`name`
	FROM `'._DB_PREFIX_.'group` g
	LEFT JOIN `'._DB_PREFIX_.'group_lang` AS gl ON (g.`id_group` = gl.`id_group` AND gl.`id_lang` = '.(int)($cookie->id_lang).')
	WHERE '.(Validate::isUnsignedInt($filter) ? 'g.`id_group` = '.(int)($filter) : 'gl.`name` LIKE "%'.pSQL($filter).'%"
	'.((Validate::isBool_Id($filter) AND $filterArray[0] == 1) ? 'OR g.`id_group` = '.(int)($filterArray[1]) : '')).'
	ORDER BY gl.`name` ASC
	LIMIT 50');
	
	$json = '{"customers" : ';
	foreach ($customers AS $customer)
		$jsonArray[] = '{"value":"0_'.(int)($customer['id_customer']).'", "text":"'.addslashes($customer['name']).' ('.addslashes($customer['email']).')"}';
	$json .= '['.implode(',', $jsonArray).'],
		"groups" : ';
	$jsonArray = array();
	foreach ($groups AS $group)
		$jsonArray[] = '{"value":"1_'.(int)($group['id_group']).'", "text":"'.addslashes($group['name']).'"}';
	$json .= '['.implode(',', $jsonArray).']}';
	die($json);
}

if (Tools::getValue('page') == 'prestastore')
	readfile('http://addons.prestashop.com/adminmodules.php?lang='.Language::getIsoById($cookie->id_lang));
if (Tools::getValue('page') == 'themes')
	readfile('http://addons.prestashop.com/adminthemes.php?lang='.Language::getIsoById($cookie->id_lang));

if ($step = (int)(Tools::getValue('ajaxProductTab')))
{
	require_once(dirname(__FILE__).'/tabs/AdminCatalog.php');
	$catalog = new AdminCatalog();
	$admin = new AdminProducts();
	
	$languages = Language::getLanguages(false);
	$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	$product = new Product((int)(Tools::getValue('id_product')));
	if (!Validate::isLoadedObject($product))
		die (Tools::displayError('product cannot be loaded'));
	
	$switchArray = array(3 => 'displayFormPrices', 4 => 'displayFormAttributes', 5 => 'displayFormFeatures', 6 => 'displayFormCustomization', 7 => 'displayFormAttachments');
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

	$languages = Language::getLanguages(false);
	$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	$fields = $import->getAvailableFields(true);
	foreach ($fields AS $field)
		$jsonArray[] = '{field: \''.addslashes($field).'\'}';
	die('['.implode(',', $jsonArray).']');
}

if (array_key_exists('ajaxModulesPositions', $_POST))
{				
	$id_module = (int)(Tools::getValue('id_module'));
	$id_hook = (int)(Tools::getValue('id_hook'));
	$way = (int)(Tools::getValue('way'));	
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

if (array_key_exists('ajaxCategoriesPositions', $_POST))
{	
	$id_category_to_move = (int)(Tools::getValue('id_category_to_move'));
	$id_category_parent = (int)(Tools::getValue('id_category_parent'));
	$way = (int)(Tools::getValue('way'));
	$positions = Tools::getValue('category');
	if (is_array($positions))
		foreach ($positions AS $key => $value)
		{
			$pos = explode('_', $value);
			if ((isset($pos[1]) AND isset($pos[2])) AND ($pos[1] == $id_category_parent AND $pos[2] == $id_category_to_move))
			{
				$position = $key;
				break;
			}
		}
	$category = new Category($id_category_to_move);
	if (Validate::isLoadedObject($category))
	{
		if (isset($position) && $category->updatePosition($way, $position))
			die(true);
		else
			die('{\'hasError\' : true, errors : \'Can not update categories position\'}');
	}
	else
		die('{\'hasError\' : true, errors : \'This category can not be loaded\'}');
}

if (array_key_exists('ajaxCMSCategoriesPositions', $_POST))
{
	$id_cms_category_to_move = (int)(Tools::getValue('id_cms_category_to_move'));
	$id_cms_category_parent = (int)(Tools::getValue('id_cms_category_parent'));
	$way = (int)(Tools::getValue('way'));
	$positions = Tools::getValue('cms_category');
	if (is_array($positions))
		foreach ($positions AS $key => $value)
		{
			$pos = explode('_', $value);
			if ((isset($pos[1]) AND isset($pos[2])) AND ($pos[1] == $id_cms_category_parent AND $pos[2] == $id_cms_category_to_move))
			{
				$position = $key;
				break;
			}
		}
	$cms_category = new CMSCategory($id_cms_category_to_move);
	if (Validate::isLoadedObject($cms_category))
	{
		if (isset($position) && $cms_category->updatePosition($way, $position))
			die(true);
		else
			die('{\'hasError\' : true, errors : \'Can not update cms categories position\'}');
	}
	else
		die('{\'hasError\' : true, errors : \'This cms category can not be loaded\'}');
}

if (array_key_exists('ajaxCMSPositions', $_POST))
{				
	$id_cms = (int)(Tools::getValue('id_cms'));
	$id_category = (int)(Tools::getValue('id_cms_category'));
	$way = (int)(Tools::getValue('way'));
	$positions = Tools::getValue('cms');
	if (is_array($positions))
		foreach ($positions AS $key => $value)
		{
			$pos = explode('_', $value);
			if ((isset($pos[1]) AND isset($pos[2])) AND ($pos[1] == $id_category AND $pos[2] == $id_cms))
			{
				$position = $key;
				break;
			}
		}
	$cms = new CMS($id_cms);
	if (Validate::isLoadedObject($cms))
	{
		if (isset($position) && $cms->updatePosition($way, $position))
			die(true);
		else
			die('{\'hasError\' : true, errors : \'Can not update cms position\'}');
	}
	else
		die('{\'hasError\' : true, errors : \'This cms can not be loaded\'}');
}

if (array_key_exists('ajaxProductsPositions', $_POST))
{
	$way = (int)(Tools::getValue('way'));
	$id_product = (int)(Tools::getValue('id_product'));
	$id_category = (int)(Tools::getValue('id_category'));
	$positions = Tools::getValue('product');

	if (is_array($positions))
		foreach ($positions AS $key => $value)
		{
			$pos = explode('_', $value);
			if ((isset($pos[1]) AND isset($pos[2])) AND ($pos[1] == $id_category AND $pos[2] == $id_product))
			{
				$position = $key;
				break;
			}
		}

	$product = new Product($id_product);
	if (Validate::isLoadedObject($product))
	{
		if (isset($position) && $product->updatePosition($way, $position))
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
	WHERE pl.`id_lang` = '.(int)(Tools::getValue('id_lang')).'
	AND p.`id_product` NOT IN (SELECT DISTINCT id_product_pack FROM `'._DB_PREFIX_.'pack`)
	AND p.`id_product` != '.(int)(Tools::getValue('id_product')));
	
	foreach ($products AS $packItem)
		$jsonArray[] = '{"value": "'.(int)($packItem['id_product']).'-'.addslashes($packItem['name']).'", "text":"'.(int)($packItem['id_product']).' - '.addslashes($packItem['name']).'"}';
	die('['.implode(',', $jsonArray).']');
}

if (isset($_GET['ajaxStates']) AND isset($_GET['id_country']))
{
	$states = Db::getInstance()->ExecuteS('
	SELECT s.id_state, s.name
	FROM '._DB_PREFIX_.'state s
	LEFT JOIN '._DB_PREFIX_.'country c ON (s.`id_country` = c.`id_country`)
	WHERE s.id_country = '.(int)(Tools::getValue('id_country')).' AND s.active = 1 AND c.`contains_states` = 1
	ORDER BY s.`name` ASC');
	
	$list = '<option value="0">-----------</option>'."\n";
	foreach ($states AS $state)
		$list .= '<option value="'.(int)($state['id_state']).'"'.((isset($_GET['id_state']) AND $_GET['id_state'] == $state['id_state']) ? ' selected="selected"' : '').'>'.$state['name'].'</option>'."\n";
		
	die($list);
}

if (Tools::isSubmit('submitCustomerNote') AND $id_customer = (int)Tools::getValue('id_customer') AND $note = html_entity_decode(Tools::getValue('note')))
{
	if (!Validate::isCleanHtml($note))
		die ('error:validation');
	if (!Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'customer SET `note` = "'.pSQL($note, true).'" WHERE id_customer = '.(int)$id_customer.' LIMIT 1'))
		die ('error:update');
	die('ok');
}

if (Tools::getValue('form_language_id'))
{
	if (!($cookie->employee_form_lang = (int)(Tools::getValue('form_language_id'))))
		die ('Error while updating cookie.');
	die ('Form language updated.');
}

if (Tools::getValue('submitPublishProduct'))
{
	global $cookie;

	if (Tools::getIsset('id_product'))
	{	
		$id_product = (int)(Tools::getValue('id_product'));
		$id_tab_catalog = (int)(Tab::getIdFromClassName('AdminCatalog'));
		$token = Tools::getAdminToken('AdminCatalog'.(int)($id_tab_catalog).(int)($cookie->id_employee));
		$bo_product_url = dirname($_SERVER['PHP_SELF']).'/index.php?tab=AdminCatalog&id_product='.$id_product.'&updateproduct&token='.$token;

		if (Tools::getValue('redirect'))
			die($bo_product_url);
			
		$profileAccess = Profile::getProfileAccess((int)($cookie->profile), $id_tab_catalog);
		if($profileAccess['edit'])
		{
			$product = new Product((int)(Tools::getValue('id_product')));
			if (!Validate::isLoadedObject($product))
				die('error: invalid id');
		
			$product->active = 1;
		
			if ($product->save())
				die($bo_product_url);
			else 
				die('error: saving');

		} else {
			die('error: permissions');
		}
	}
	else 
		die ('error: parameters');	
}

if (Tools::getValue('submitPublishCMS'))
{
	global $cookie;

	if (Tools::getIsset('id_cms'))
	{	
		$id_cms = (int)(Tools::getValue('id_cms'));
		$id_tab_cms = (int)(Tab::getIdFromClassName('AdminCMSContent'));
		$token = Tools::getAdminToken('AdminCMSContent'.(int)($id_tab_cms).(int)($cookie->id_employee));
		$bo_cms_url = dirname($_SERVER['PHP_SELF']).'/index.php?tab=AdminCMSContent&id_cms='.$id_cms.'&updatecms&token='.$token;

		if (Tools::getValue('redirect'))
			die($bo_cms_url);
			
		$profileAccess = Profile::getProfileAccess((int)($cookie->profile), $id_tab_cms);
		if($profileAccess['edit'])
		{
			$cms = new CMS((int)(Tools::getValue('id_cms')));
			if (!Validate::isLoadedObject($cms))
				die('error: invalid id');
		
			$cms->active = 1;
		
			if ($cms->save())
				die($bo_cms_url);
			else 
				die('error: saving');

		} else {
			die('error: permissions');
		}
	}
	else 
		die ('error: parameters');	
}

?>
