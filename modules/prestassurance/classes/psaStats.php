<?php
/*
* 2007-2011 PrestaShop 
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7540 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class psaStats
{
	static public function getTotalOrder()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(o.`id_order`)
		FROM `'._DB_PREFIX_.'orders` o
		WHERE o.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
	}
	
	static public function getTotalOrderWithInsurance()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(`date_add`) as total 
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` op on (o.id_order = op.id_order)
		WHERE op.`product_id` = '.(int)Configuration::get('PSA_ID_PRODUCT').'
		AND o.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
	}
	
	static public function getTotalDisaster()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(`date_add`) as total 
		FROM `'._DB_PREFIX_.'psa_disaster` d
		WHERE d.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
	}
}