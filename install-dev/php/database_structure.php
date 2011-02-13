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
*  International Registered Trademark & Property of PrestaShop SA
*/

function group_reduction_column_fix()
{
	if (!Db::getInstance()->execute('SELECT `group_reduction` FROM `'._DB_PREFIX_.'order_detail` LIMIT 1'))
		return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'order_detail` ADD `group_reduction` DECIMAL(10, 2) NOT NULL AFTER `reduction_amount`');
	return true;
}

function ecotax_tax_application_fix()
{
	if (!Db::getInstance()->execute('SELECT `ecotax_tax_rate` FROM `'._DB_PREFIX_.'order_detail` LIMIT 1'))
		return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'order_detail` ADD `ecotax_tax_rate` DECIMAL(5, 3) NOT NULL AFTER `ecotax`');
	return true;
}

function id_currency_country_fix()
{
	if (!Db::getInstance()->execute('SELECT `id_currency` FROM `'._DB_PREFIX_.'country` LIMIT 1'))
		return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'country` ADD `id_currency` INT NOT NULL DEFAULT \'0\' AFTER `id_zone`');
	return true;
}