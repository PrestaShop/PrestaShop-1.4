<?php

/* SSL Management */
$useSSL = true;

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
	$refresh = (($_GET['refresh'] == 'true') ? 1 : 0);
	if (empty($id_wishlist) === false)
	{
		 if (!strcmp($action, 'update'))
		{
			WishList::updateProduct($id_wishlist, $id_product, $id_product_attribute, $priority, $quantity);
		}
		else
		{
			if (!strcmp($action, 'delete'))
				WishList::removeProduct($id_wishlist, intval($cookie->id_customer), $id_product, $id_product_attribute);
	
			$products = WishList::getProductByIdCustomer($id_wishlist, $cookie->id_customer, $cookie->id_lang);
			$bought = WishList::getBoughtProduct($id_wishlist);
		
			for ($i = 0; $i < sizeof($products); ++$i)
			{
				$obj = new Product(intval($products[$i]['id_product']), false, intval($cookie->id_lang));
				if (!Validate::isLoadedObject($obj))
					continue;
				else
				{
					if ($products[$i]['id_product_attribute'] != 0)
					{
						$combination_imgs = $obj->getCombinationImages(intval($cookie->id_lang));
						$products[$i]['cover'] = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image'];
					}
					else
					{
						$images = $obj->getImages(intval($cookie->id_lang));
						foreach ($images AS $k => $image)
							if ($image['cover'])
							{
								$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
								break;
							}
					}
					if (!isset($products[$i]['cover']))
						$products[$i]['cover'] = Language::getIsoById($cookie->id_lang).'-default';
				}
				$products[$i]['bought'] = false;
				for ($j = 0, $k = 0; $j < sizeof($bought); ++$j)
				{
					if ($bought[$j]['id_product'] == $products[$i]['id_product'] AND
						$bought[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
						$products[$i]['bought'][$k++] = $bought[$j];
				}
			}
		
			$productBoughts = array();
		
			foreach ($products as $product)
				if (sizeof($product['bought']))
					$productBoughts[] = $product;
			$smarty->assign('products', $products);
			$smarty->assign('productsBoughts', $productBoughts);
			$smarty->assign('id_wishlist', $id_wishlist);
			$smarty->assign('refresh',  $refresh);
			$smarty->display(dirname(__FILE__).'/managewishlist.tpl');
		}
	}
}

