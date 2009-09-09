<?php

/**
  * ProductSale class, ProductSale.php
  * Product sale management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		ProductSale
{
	/*
	** Fill the `product_sale` SQL table with data from `order_detail`
	** @return bool True on success	  
	*/
	static public function fillProductSales()
	{
		return Db::getInstance()->Execute('
		REPLACE INTO '._DB_PREFIX_.'product_sale
		(`id_product`, `quantity`, `sale_nbr`, `date_upd`)
		SELECT od.product_id, COUNT(od.product_id), SUM(od.product_quantity), NOW()
					FROM '._DB_PREFIX_.'order_detail od GROUP BY od.product_id');
	}
	
	/*
	** Get number of actives products sold
	** @return int number of actives products listed in product_sales 	
	*/	
	static public function getNbSales()
	{
		$result = Db::getInstance()->getRow('
			SELECT COUNT(ps.`id_product`) AS nb
			FROM `'._DB_PREFIX_.'product_sale` ps
			LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = ps.`id_product`
			WHERE p.`active` = 1');
		return intval($result['nb']);
	}
	
	/*
	** Get required informations on best sales products
	**	
	** @param integer $id_lang Language id
	** @param integer $pageNumber Start from (optional)
	** @param integer $nbProducts Number of products to return (optional)
	** @return array from Product::getProductProperties
	*/
	static public function getBestSales($id_lang, $pageNumber = 0, $nbProducts = 10, $orderBy=NULL, $orderWay=NULL)
	{
		global $link, $cookie;

		if ($pageNumber < 0) $pageNumber = 0;
		if ($nbProducts < 1) $nbProducts = 10;
		if (empty($orderBy) || $orderBy == 'position') $orderBy = 'sales';
		if (empty($orderWay)) $orderWay = 'DESC';
		
		$result = Db::getInstance()->ExecuteS('
		SELECT p.*,
			pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`,
			i.`id_image`, il.`legend`,
			ps.`quantity` AS sales, t.`rate`, pl.`meta_keywords`, pl.`meta_title`, pl.`meta_description`
		FROM `'._DB_PREFIX_.'product_sale` ps 
		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
		WHERE p.`active` = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
		)
		ORDER BY '.(isset($orderByPrefix) ? $orderByPrefix.'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
		LIMIT '.intval($pageNumber * $nbProducts).', '.intval($nbProducts));

		if($orderBy == 'price')
		{	
			Tools::orderbyPrice($result,$orderWay);
		}
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}

	/*
	** Get required informations on best sales products
	**				
	** @param integer $id_lang Language id
	** @param integer $pageNumber Start from (optional)
	** @param integer $nbProducts Number of products to return (optional)
	** @return array keys : id_product, link_rewrite, name, id_image, legend, sales, ean13 , link
	*/
	static public function getBestSalesLight($id_lang, $pageNumber = 0, $nbProducts = 10)
	{
	 	global $link, $cookie;
	 	
		if ($pageNumber < 0) $pageNumber = 0;
		if ($nbProducts < 1) $nbProducts = 10;
		
		$result = Db::getInstance()->ExecuteS('
		SELECT p.id_product, pl.`link_rewrite`, pl.`name`, pl.`description_short`, i.`id_image`, il.`legend`, ps.`quantity` AS sales, p.`ean13`, cl.`link_rewrite` AS category
		FROM `'._DB_PREFIX_.'product_sale` ps 
		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = '.intval($id_lang).')
		WHERE p.`active` = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
		)
		GROUP BY p.`id_product`
		ORDER BY sales DESC
		LIMIT '.intval($pageNumber * $nbProducts).', '.intval($nbProducts));
		if (!$result)
			return $result;
		
		foreach ($result AS &$row)
		{
		 	$row['link'] = $link->getProductLink($row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);
		 	$row['id_image'] = Product::defineProductImage($row);
		}
		return $result;
	}

	static public function addProductSale($product_id, $qty = 1)
	{
		return Db::getInstance()->Execute('
			INSERT INTO '._DB_PREFIX_.'product_sale
			(`id_product`, `quantity`, `sale_nbr`, `date_upd`)
			VALUES ('.intval($product_id).', '.intval($qty).', 1, NOW())
			ON DUPLICATE KEY UPDATE `quantity` = `quantity` + '.intval($qty).', `sale_nbr` = `sale_nbr` + 1, `date_upd` = NOW()');
	}

	static public function getNbrSales($id_product)
	{
		$result = Db::getInstance()->getRow('SELECT `sale_nbr` FROM '._DB_PREFIX_.'product_sale WHERE `id_product` = '.intval($id_product));
		if (!$result OR empty($result) OR !key_exists('sale_nbr', $result))
			return -1;
		return intval($result['sale_nbr']);
	}

	static public function removeProductSale($id_product, $qty = 1)
	{
		$nbrSales = self::getNbrSales($id_product);
		if ($nbrSales > 1)
			return Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product_sale SET `quantity` = `quantity` - '.intval($qty).', `sale_nbr` = `sale_nbr` - 1, `date_upd` = NOW() WHERE `id_product` = '.intval($id_product));
		elseif ($nbrSales == 1)
			return Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'product_sale WHERE `id_product` = '.intval($id_product));
		return true;
	}
}	
?>
