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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('prestassurance.php');

global $cookie;

$cartObj = new Cart((int)$cookie->id_cart);

$id_cart = Tools::getValue('id_cart');
$id_product = Tools::getValue('id_product');
$id_product_attribute = Tools::getValue('id_product_attribute');
$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');

if (Tools::getValue('id_psa_product'))
{
	$id_psa_attribute = explode('_', Tools::getValue('id_psa_product'));
	$id_psa_attribute = $id_psa_attribute[1];
}
$qty = (int)Tools::getValue('qty');
$psa = new prestassurance();


$return = true;

if (Tools::isSubmit('refreshPsaCart'))
	die($psa->hookTop(array('cart' => $cartObj, 'cookie' => $cookie)));


if (Tools::getValue('token') != sha1(_COOKIE_KEY_.$id_cart.$id_product.$id_product_attribute))
	die('INVALID TOKEN');


$return &= Db::getInstance()->Execute('
							UPDATE `'._DB_PREFIX_.'psa_cart` SET `deleted` = '.(int)Tools::getValue('deleted').' 
							WHERE `id_cart`='.(int)$id_cart.' 
							AND `id_product`='.(int)$id_product.' 
							AND`id_product_attribute`='.(int)$id_product_attribute);

//update qty in ps cart
if (Tools::getValue('deleted'))
	$return &= $cartObj->updateQty($qty, $psa_product_id, (int)$id_psa_attribute, false, 'down');
else
	$return &= $cartObj->updateQty($qty, $psa_product_id, (int)$id_psa_attribute, false, 'up');
if ($return)
{
	if (Tools::getValue('ajax'))
		die('{"hasError" : false }');
	else
		Tools::redirect($_SERVER['HTTP_REFERER']);

}
else
	Tools::redirect($_SERVER['HTTP_REFERER']);
