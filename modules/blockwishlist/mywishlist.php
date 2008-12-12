<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/WishList.php');

$errors = array();

if ($cookie->isLogged())
{
	$add = Tools::getIsset('add');
	$add = (empty($add) === false ? 1 : 0);
	$delete = Tools::getIsset('deleted');
	$delete = (empty($delete) === false ? 1 : 0);
	$id_wishlist = Tools::getValue('id_wishlist');
	if (Tools::isSubmit('submitWishlist'))
	{
		if (Configuration::get('PS_TOKEN_ACTIVATED') == 1 AND
			strcmp(Tools::getToken(), Tools::getValue('token')))
			$errors[] = Tools::displayError('invalid token');
		if (!sizeof($errors))
		{
			$name = Tools::getValue('name');
			$wishlist = new WishList();
			$wishlist->name = $name;
			$wishlist->id_customer = $cookie->id_customer;
			list($us, $s) = explode(' ', microtime());
			srand($s * $us);
			$wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$cookie->id_customer), 0, 16));
			$wishlist->add();
		}
	}
	else if ($add)
		WishList::addCardToWishlist(intval($cookie->id_customer), intval(Tools::getValue('id_wishlist')), intval($cookie->id_lang));
	else if ($delete AND empty($id_wishlist) === false)
	{
		$wishlist = new WishList(intval($id_wishlist));
		$wishlist->delete();
	}
	$smarty->assign('wishlists', WishList::getByIdCustomer(intval($cookie->id_customer)));
	$smarty->assign('nbProducts', WishList::getInfosByIdCustomer(intval($cookie->id_customer)));
}
else
{
	$errors[] = Tools::displayError('You need to be logged to manage your wishlist'); 
}

$smarty->assign('id_customer', intval($cookie->id_customer));
$smarty->assign('errors', $errors);
$smarty->display(dirname(__FILE__).'/mywishlist.tpl');

include(dirname(__FILE__).'/../../footer.php');

?>
