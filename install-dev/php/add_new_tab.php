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

function add_new_tab($className, $name, $id_parent)
{
	
	Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tab` (`id_parent`, `class_name`, `module`, `position`) VALUES ('.(int)$id_parent.', \''.pSQL($className).'\', \'\', 
								(SELECT MAX(t.position)+ 1 FROM `'._DB_PREFIX_.'tab` t WHERE t.id_parent = '.(int)$id_parent.'))');
	
	
	Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tab_lang` (`id_lang`, `id_tab`, `name`) 
								(SELECT `id_lang`, (
								SELECT `id_tab`
								FROM `'._DB_PREFIX_.'tab`
								WHERE `class_name` = \''.pSQL($className).'\' LIMIT 0,1
								), \''.pSQL($name).'\' FROM `'._DB_PREFIX_.'lang`)');
	
	Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) 
								(SELECT `id_profile`, (
									SELECT `id_tab`
									FROM `'._DB_PREFIX_.'tab`
									WHERE `class_name` = \''.pSQL($className).'\' LIMIT 0,1
								), 1, 1, 1, 1 FROM `'._DB_PREFIX_.'profile` )');
}