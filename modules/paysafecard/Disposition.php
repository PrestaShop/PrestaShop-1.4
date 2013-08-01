<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PSCDisposition
{
	const TABLE_NAME = 'disposition';
	
	public static function create($id_cart, $mtid, $amount, $currency)
	{
		return Db::getInstance()->execute(
		'INSERT INTO `'._DB_PREFIX_.self::TABLE_NAME.'` (`id_cart`, `mtid`, `amount`, `currency`)
		 VALUES ('.(int)($id_cart).',\''.pSQL($mtid).'\','.(float)($amount).',\''.pSQL($currency).'\')');
	}
	
	public static function delete($id)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.self::TABLE_NAME.'` WHERE `id_disposition` = '.(int)($id));
	}
	
	public static function deleteByCartId($id_cart)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.self::TABLE_NAME.'` WHERE `id_cart` = '.(int)($id_cart));
	}
	
	public static function getByCartId($id_cart)
	{
		return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.self::TABLE_NAME.'` WHERE `id_cart` = '.(int)($id_cart));
	}
	
	public static function createTable()
	{
		return Db::getInstance()->execute(
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'` (
		  `id_disposition` int(11) NOT NULL AUTO_INCREMENT,
		  `id_cart` int(11) NOT NULL,
		  `mtid` varchar(20) NOT NULL,
		  `amount` float NOT NULL,
		  `currency` varchar(3) NOT NULL,
		  PRIMARY KEY (`id_disposition`),
		  UNIQUE KEY `id_cart` (`id_cart`)
		)  ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
	}
	
	public static function dropTable()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'`');
	}
	
	public static function updateAmount($id_disposition, $amount)
	{
		return Db::getInstance()->execute(
		'UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'` SET `amount` = `amount` - '.(float)($amount).' WHERE `id_disposition` = '.(int)($id_disposition));
	}
	 

}


