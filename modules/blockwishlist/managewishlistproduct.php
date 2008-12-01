<?php
/*
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/WishList.php');

if ($cookie->isLogged())
{
	$action = Tools::getValue('action');
	$id_wishlist = Tools::getValue('id_wishlist');
	$id_product = Tools::getValue('id_product');
	$id_product_attribute = Tools::getValue('id_product_attribute');
	$quantity = Tools::getValue('quantity');
	$priority = Tools::getValue('priority');
	$wishlist = new WishList(intval($id_wishlist));
	if (empty($wishlist->id_customer) === true OR
		$wishlist->id_customer != $cookie->id_customer)
		exit;
	else if (!strcmp($action, 'delete'))
		WishList::removeProduct($id_wishlist, intval($cookie->id_customer), $id_product, $id_product_attribute);
	else if (!strcmp($action, 'update'))
	{
		WishList::updateProduct($id_wishlist, $id_product, $id_product_attribute, $priority, $quantity);
		$products = WishList::getProductByIdCustomer($id_wishlist, $cookie->id_customer, $cookie->id_lang, $id_product);
		for ($i = 0; $i < sizeof($products); ++$i)
		{
			if ($products[$i]['id_product_attribute'] != $id_product_attribute)
				continue;
			$obj = new Product(intval($products[$i]['id_product']), false, intval($cookie->id_lang));
			if (!Validate::isLoadedObject($obj))
				continue;
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
					$products[$i]['cover'] = Language::getIsoById($cookie->id_lang).'-default';
			}
		}
		$smarty->assign('products', $products);
		$smarty->assign('id_product_attribute', $id_product_attribute);
	}
}

$smarty->display(dirname(__FILE__).'/managewishlistproduct.tpl');
*/?>
