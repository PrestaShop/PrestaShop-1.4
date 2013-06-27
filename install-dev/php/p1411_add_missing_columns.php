<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
function p1411_add_missing_columns()
{	
	$errors = array();
	$key_exists = Db::getInstance()->executeS('SHOW INDEX FROM `'._DB_PREFIX_.'stock_mvt` WHERE KEY_NAME = "id_product"');;
	if (is_array($key_exists))
		if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'stock_mvt` DROP INDEX `id_product`'))
			$errors[] = Db::getInstance()->getMsgError();
			
	$key_exists = Db::getInstance()->executeS('SHOW INDEX FROM `'._DB_PREFIX_.'stock_mvt` WHERE KEY_NAME = "id_product_attribute"');;
	if (is_array($key_exists))
		if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'stock_mvt` DROP INDEX `id_product_attribute`'))
			$errors[] = Db::getInstance()->getMsgError();
	
	if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'stock_mvt` ADD INDEX `id_product_id_product_attribute` ( `id_product` , `id_product_attribute` )'))
			$errors[] = Db::getInstance()->getMsgError();
	if (isset($errors) && count($errors))
		return array('error' => 1, 'msg' => implode(',', $errors)) ;	
}