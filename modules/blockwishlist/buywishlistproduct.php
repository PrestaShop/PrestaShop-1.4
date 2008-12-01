<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/WishList.php');

$error = '';

$token = Tools::getValue('token');
$id_product = intval(Tools::getValue('id_product'));
$id_product_attribute = intval(Tools::getValue('id_product_attribute'));
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
	$error = Tools::displayError('You need to login');

if (empty($error) === false)
	echo $error;
?>
