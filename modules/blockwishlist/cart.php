<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/WishList.php');

$errors = array();

$action = Tools::getValue('action');
$add = (!strcmp($action, 'add') ? 1 : 0);
$delete = (!strcmp($action, 'delete') ? 1 : 0);
$id_wishlist = intval(Tools::getValue('id_wishlist'));
$id_product = intval(Tools::getValue('id_product'));
$quantity = intval(Tools::getValue('quantity'));
$id_product_attribute = intval(Tools::getValue('id_product_attribute'));
if (Configuration::get('PS_TOKEN_ENABLE') == 1 AND
	strcmp(Tools::getToken(false), Tools::getValue('token')) AND
	$cookie->isLogged() === true)
	$errors[] = Tools::displayError('invalid token');
if ($cookie->isLogged())
{
	if ($id_wishlist AND WishList::exists($id_wishlist, $cookie->id_customer) === true)
		$cookie->id_wishlist = intval($id_wishlist);
	if (empty($cookie->id_wishlist) === true OR $cookie->id_wishlist == false)
		$smarty->assign('error', true);
	if (($add OR $delete) AND empty($id_product) === false)
	{
		if(!isset($cookie->id_wishlist) OR $cookie->id_wishlist == '')
		{
			$wishlist = new WishList();
			$wishlist->name = 'My WishList';
			$wishlist->id_customer = intval($cookie->id_customer);
			list($us, $s) = explode(' ', microtime());
			srand($s * $us);
			$wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$cookie->id_customer), 0, 16));
			$wishlist->add();
			$cookie->id_wishlist = intval($wishlist->id);
		}
		if ($add AND $quantity)
			WishList::addProduct($cookie->id_wishlist, $cookie->id_customer, $id_product, $id_product_attribute, $quantity);
		else if ($delete)
			WishList::removeProduct($cookie->id_wishlist, $cookie->id_customer, $id_product, $id_product_attribute);
	}
	$smarty->assign('products', WishList::getProductByIdCustomer($cookie->id_wishlist, $cookie->id_customer, $cookie->id_lang, null, true));
	$smarty->display(dirname(__FILE__).'/blockwishlist-ajax.tpl');
}
else
	$errors[] = Tools::displayError('You need to be logged to manage your wishlist');
	
if (sizeof($errors))
{
	$smarty->assign('errors', $errors);
	$smarty->display(_PS_THEME_DIR_.'errors.tpl');
}
