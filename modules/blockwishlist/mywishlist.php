<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/WishList.php');

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
			if (empty($name))
				$errors[] = Tools::displayError('you must specify a name');
			if (WishList::isExistsByNameForUser($name))
				$errors[] = Tools::displayError('this name is already used by an other list');
			
			if(!sizeof($errors))
			{
				$wishlist = new WishList();
				$wishlist->name = $name;
				$wishlist->id_customer = $cookie->id_customer;
				list($us, $s) = explode(' ', microtime());
				srand($s * $us);
				$wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$cookie->id_customer), 0, 16));
				$wishlist->add();
			}
		}
	}
	else if ($add)
		WishList::addCardToWishlist(intval($cookie->id_customer), intval(Tools::getValue('id_wishlist')), intval($cookie->id_lang));
	else if ($delete AND empty($id_wishlist) === false)
	{
		$wishlist = new WishList(intval($id_wishlist));
		if (Validate::isLoadedObject($wishlist))
			$wishlist->delete();
		else
			$errors[] = Tools::displayError('can`t delete this whislist');
	}
	$smarty->assign('wishlists', WishList::getByIdCustomer(intval($cookie->id_customer)));
	$smarty->assign('nbProducts', WishList::getInfosByIdCustomer(intval($cookie->id_customer)));
}
else
{
	Tools::redirect('authentication.php?back=modules/blockwishlist/mywishlist.php');
}

$smarty->assign(array(
	'id_customer' => intval($cookie->id_customer),
	'errors' => $errors
));

if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/blockwishlist/mywishlist.tpl'))
	$smarty->display(_PS_THEME_DIR_.'modules/blockwishlist/mywishlist.tpl');
elseif (Tools::file_exists_cache(dirname(__FILE__).'/mywishlist.tpl'))
	$smarty->display(dirname(__FILE__).'/mywishlist.tpl');
else
	echo Tools::displayError('No template found');

include(dirname(__FILE__).'/../../footer.php');

?>
