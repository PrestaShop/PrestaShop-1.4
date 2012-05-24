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

/*
	1. Check if the array of products in cart sent with AJAX is already cached
	2. If not cached: retrieve it from Avalara and put it in the cache table
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
ini_set('max_execution_time', 0);

// Check if the AJAX call is valid
if (!(isset($_POST['ajax'])
&& $_POST['ajax'] == 'getProductTaxRate'
&& isset($_POST['token'])
&& md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')) == $_POST['token']))
	die('error');

// Get variables...
$id_cart = (int)$_POST['id_cart'];
$id_lang = (int)$_POST['id_lang'];
$id_address = (int)$_POST['id_address'];

// Make the products list
$cart = new Cart($id_cart);
$ids_product = array();
foreach ($cart->getProducts() as $product)
	$ids_product[] = (int)$product['id_product'];
$ids_product = implode(', ', $ids_product);

// Stop if cart is empty
if (empty($ids_product))
{
	echo 'ok';
	exit;
}

// Check cache before asking Avalara webservice
if ($id_address)
	$region = Db::getInstance()->getValue('SELECT s.`iso_code`
									FROM '._DB_PREFIX_.'address a
									LEFT JOIN '._DB_PREFIX_.'state s ON (s.`id_state` = a.`id_state`)
									WHERE a.`id_address` = '.(int)$id_address);
if (empty($region))
	$region = Configuration::get('AVALARATAX_STATE');
$result = Db::getInstance()->ExecuteS('SELECT ac.`tax_rate`, ac.`update_date` FROM '._DB_PREFIX_.'avalara_product_cache ac
									WHERE ac.`id_product` IN ('.pSQL($ids_product).')
									AND ac.`region` = \''.pSQL($region).'\'');

if (count($result) && count($result) == count($cart->getProducts()) && (float)$result[0]['tax_rate'] > 0)
{
	$result = $result[0];
	// Compare date/time
	date_default_timezone_set(@date_default_timezone_get());
	$date1 = new DateTime($result['update_date']);
	$date2 = new DateTime(date('Y-m-d H:i:s'));
	$dateTimeComparison = $date1->diff($date2);

	// Check if the cached tax is not expired
	if ($dateTimeComparison->y == 0 
	&& $dateTimeComparison->m == 0 
	&& $dateTimeComparison->d == 0 
	&& (int)$dateTimeComparison->h == 0 
	&& (int)$dateTimeComparison->i < (int)Configuration::get('AVALARA_CACHE_MAX_LIMIT') 
	&& (float)$result['tax_rate'] > 0)
	{
		echo 'ok_c'; //$result['tax_rate'];
		return;
	}
}

// The tax rate for the requested product was not found in cache, 
// or cache expired, or tax_rate is 0. 
// Then cache it again using getTax()

if (!class_exists('AvalaraTax'))
	include(dirname(__FILE__).'/avalaratax.php');
$avalaraModule = new AvalaraTax();
if ($avalaraModule->active)
{
	foreach ($cart->getProducts() as $product)
	{
		$avalaraProducts[] = array('id_product' => (int)$product['id_product'],
					'name' => $product['name'],
					'description_short' => $product['description_short'],
					'quantity' => 1, // This is a per product, so qty is 1
					'total' => $product['price'],
					'tax_code' => $avalaraModule->getProductTaxCode($product['id_product']));

		// Call Avalara
		$getTaxResult = $avalaraModule->getTax($avalaraProducts, array('type' => 'SalesOrder', 'DocCode' => 1));

		// Store the taxrate in cache
		// If taxrate exists (but it's outdated), then update, else insert (REPLACE INTO)
		if (isset($getTaxResult['TotalTax'])
		&& $getTaxResult['TotalTax']
		&& isset($getTaxResult['TotalAmount'])
		&& $getTaxResult['TotalAmount'])
			Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'avalara_product_cache (`id_product`, `tax_rate`, `region`, `update_date`)
									VALUES ('.(int)$product['id_product'].'
									,'.(float)($getTaxResult['TotalTax'] * 100 / $getTaxResult['TotalAmount']).'
									,\''.pSQL($region).'\'
									,"'.date('Y-m-d H:i:s').'")');
	}
}
echo 'ok';