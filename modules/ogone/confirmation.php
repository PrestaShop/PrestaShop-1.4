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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/ogone.php');

ini_set('display_errors', 'on');

$ogone = new Ogone();
$id_module = $ogone->id;
$id_cart = Tools::getValue('orderID');
$key = Db::getInstance()->getValue('SELECT secure_key FROM '._DB_PREFIX_.'customer WHERE id_customer = '.(int)$cookie->id_customer);

$smarty->assign(array('id_module' => $id_module, 'id_cart' => $id_cart, 'key' => $key));
echo $ogone->display(__FILE__, 'waiting.tpl');

include(dirname(__FILE__).'/../../footer.php');


