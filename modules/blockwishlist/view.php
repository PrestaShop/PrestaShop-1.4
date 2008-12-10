<?php

require(dirname(__FILE__).'/../../config/config.inc.php');
require(dirname(__FILE__).'/../../header.php');
require(dirname(__FILE__).'/WishList.php');

$token = Tools::getValue('token');
if (empty($token) === false)
{
	$wishlist = WishList::getByToken($token);
	if (empty($result) === true || $result === false)
		$errors[] = Tools::displayError('Invalid wishlist token');
	WishList::refreshWishList($wishlist['id_wishlist']);
	$products = WishList::getProductByIdCustomer(intval($wishlist['id_wishlist']), intval($wishlist['id_customer']), intval($cookie->id_lang), null, true);
	for ($i = 0; $i < sizeof($products); ++$i)
	{
		$obj = new Product(intval($products[$i]['id_product']), false, intval($cookie->id_lang));
		if (!Validate::isLoadedObject($obj))
			continue;
		else
		{
			if ($products[$i]['id_product_attribute'] != 0)
			{
				$attrgrps = $obj->getAttributesGroups(intval($cookie->id_lang));
				foreach ($attrgrps AS $attrgrp)
					if ($attrgrp['id_product_attribute'] == intval($products[$i]['id_product_attribute']))
					{
						$products[$i]['cover'] = $obj->id.'-'.$attrgrp['id_image'];
						break;
					}
			}
			else
			{
				$images = $obj->getImages(intval($cookie->id_lang));
				foreach ($images AS $k => $image)
				{
					if ($image['cover'])
					{
						$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
						break;
					}
				}
				if (!isset($products[$i]['cover']))
					$products[$i]['cover'] = Language::getIsoById(intval($cookie->id_lang)).'-default';
			}
		}
	}
	WishList::incCounter(intval($wishlist['id_wishlist']));
	$ajax = Configuration::get('PS_BLOCK_CART_AJAX');
	$smarty->assign('current_wishlist', $wishlist);
	$smarty->assign('token', $token);
	$smarty->assign('ajax', (isset($ajax) AND intval($ajax) == 1) ? '1' : '0' );
	$smarty->assign('wishlists', WishList::getByIdCustomer(intval($wishlist['id_customer'])));
	$smarty->assign('products', $products);
}

$smarty->display(dirname(__FILE__).'/view.tpl');

require(dirname(__FILE__).'/../../footer.php');
