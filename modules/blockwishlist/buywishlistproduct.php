<?php
/*
* Copyright (C) 2007-2010 PrestaShop 
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
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/WishList.php');

$error = '';

$token = Tools::getValue('token');
$id_product = (int)(Tools::getValue('id_product'));
$id_product_attribute = (int)(Tools::getValue('id_product_attribute'));
if (Configuration::get('PS_TOKEN_ENABLE') == 1 &&
strcmp(Tools::getToken(false), Tools::getValue('static_token')))
	$error = Tools::displayError('invalid token');

if (!strlen($error) &&
	empty($token) === false &&
	empty($id_product) === false)
{
	$wishlist = WishList::getByToken($token);
	if ($wishlist !== false)
		WishList::addBoughtProduct($wishlist['id_wishlist'], $id_product, $id_product_attribute, $cart->id, 1);
}
else
	$error = Tools::displayError('You need to log in');

if (empty($error) === false)
	echo $error;

