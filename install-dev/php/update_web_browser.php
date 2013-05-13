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

function update_web_browser()
{
	$step = 3000;
	$count_guests = Db::getInstance()->getValue('SELECT count(id_guest) FROM '._DB_PREFIX_.'guest WHERE id_web_browser IN (2, 8)');
	$nb_loop = $start = 0;
	if($count_guests > 0)
		$nb_loop = ceil($count_guests / $step);
	for($i = 0; $i < $nb_loop; $i++)
	{
		$sql = 'SELECT id_guest, id_web_browser FROM `'._DB_PREFIX_.'guest` WHERE id_web_browser IN (2, 8) LIMIT '.(int)$start.', '.(int)$step;
		if ($guests = Db::getInstance()->query($sql))
			while ($guest = Db::getInstance()->nextRow($guests))
			{
				if(is_array($guest))
				{
					$sql = 'UPDATE `'._DB_PREFIX_.'guest` SET id_web_browser = '.(((int)$guest['id_web_browser'] == 2)? '3' : '10').'
							WHERE id_guest = '.(int)$guest['id_guest'];
					$result = Db::getInstance()->execute($sql);
				}
			}
	}
	
	$count_guests = Db::getInstance()->getValue('SELECT count(id_guest) FROM '._DB_PREFIX_.'guest WHERE id_operating_system IN (3, 4)');
	$nb_loop = $start = 0;
	if($count_guests > 0)
		$nb_loop = ceil($count_guests / $step);
	for($i = 0; $i < $nb_loop; $i++)
	{
		$sql = 'SELECT id_guest, id_operating_system FROM `'._DB_PREFIX_.'guest` WHERE id_operating_system IN (3, 4) LIMIT '.(int)$start.', '.(int)$step;
		if ($guests = Db::getInstance()->query($sql))
			while ($guest = Db::getInstance()->nextRow($guests))
			{
				if(is_array($guest))
				{
					$sql = 'UPDATE `'._DB_PREFIX_.'guest` SET id_operating_system = '.(((int)$guest['id_operating_system'] == 3)? '5' : '6').'
							WHERE id_guest = '.(int)$guest['id_guest'];
					$result = Db::getInstance()->execute($sql);
				}
			}
	}	
}