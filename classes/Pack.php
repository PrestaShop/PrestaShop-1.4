<?php

class Pack extends Product
{
	private static $cachePack = array();
	private static $cachePackItems = array();
	
	public static function isPack($id_product)
	{
		$result = Db::getInstance()->getRow('SELECT COUNT(*) AS items FROM '._DB_PREFIX_.'pack WHERE id_product_pack = '.intval($id_product));
		return ($result['items'] > 0);
	}
	
	public static function isPacked($id_product)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT COUNT(*) AS packs FROM '._DB_PREFIX_.'pack WHERE id_product_item = '.intval($id_product));
		return ($result['packs'] > 0);
	}
	
	public static function noPackPrice($id_product)
	{
		global $cookie;
		
		$sum = 0;

		$price_display_method = !self::$_taxCalculationMethod;
		$items = self::getItems($id_product, Configuration::get('PS_LANG_DEFAULT'));
		foreach ($items as $item)
			$sum += $item->getPrice($price_display_method) * $item->pack_quantity;
		return $sum;		
	}
	
	public static function getItems($id_product, $id_lang)
	{
		if (in_array($id_product, self::$cachePackItems))
			return self::$cachePackItems[$id_product];
		$result = Db::getInstance()->ExecuteS('SELECT id_product_item, quantity FROM '._DB_PREFIX_.'pack where id_product_pack = '.intval($id_product));
		$arrayResult = array();
		foreach ($result AS $row)
		{
			$p = new Product($row['id_product_item'], false, intval($id_lang));
			$p->pack_quantity = $row['quantity'];
			$arrayResult[] = $p;
		}
		self::$cachePackItems[$id_product] = $arrayResult;
		return self::$cachePackItems[$id_product];
	}
	
	public static function isInStock($id_product, $id_lang)
	{
		$items = self::getItems(intval($id_product), intval($id_lang));
		foreach ($items AS $item)
			if ($item->quantity == 0 AND !$item->isAvailableWhenOutOfStock(intval($item->out_of_stock)))
				return false;
		return true;
	}
	
	public static function getItemTable($id_product, $id_lang, $full = false)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT p.*, pl.*, i.`id_image`, il.`legend`, t.`rate`, cl.`name` AS category_default, a.quantity AS pack_quantity
		FROM `'._DB_PREFIX_.'pack` a
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = a.id_product_item
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.id_product = pl.id_product AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
		WHERE a.`id_product_pack` = '.intval($id_product));
		if (!$full)
			return $result;
			
		$arrayResult = array();
		foreach ($result as $row)
			if (!Pack::isPack($row['id_product']))
				$arrayResult[] = Product::getProductProperties($id_lang, $row);
		return $arrayResult;
	}
	
	public static function getPacksTable($id_product, $id_lang, $full = false, $limit = NULL)
	{
		$sql = '
		SELECT p.*, pl.*, i.`id_image`, il.`legend`, t.`rate`
		FROM `'._DB_PREFIX_.'product` p
		NATURAL LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
		WHERE pl.`id_lang` = '.intval($id_lang).'
		AND p.`id_product` IN (
			SELECT a.`id_product_pack`
			FROM `'._DB_PREFIX_.'pack` a
			WHERE a.`id_product_item` = '.intval($id_product).')
		';
		if ($limit)
			$sql .= ' LIMIT '.intval($limit);
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		if (!$full)
			return $result;
			
		$arrayResult = array();
		foreach ($result as $row)
			if (!Pack::isPacked($row['id_product']))
				$arrayResult[] = Product::getProductProperties($id_lang, $row);
		return $arrayResult;
	}
	
	public static function deleteItems($id_product)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'pack` WHERE `id_product_pack` = '.intval($id_product));
	}
	
	public static function addItems($id_product, $ids)
	{
		array_pop($ids);
		foreach ($ids as $id_product_item)
		{
			$idQty = explode('x', $id_product_item);
			if (!self::addItem($id_product, $idQty[1], $idQty[0]))
				return false;
		}
		return true;
	}
	
	/**
	* Add an item to the pack
	*
	* @param integer $id_product 
	* @param integer $id_item 
	* @param integer $qty 
	* @return boolean true if everything was fine
	*/
	public static function addItem($id_product, $id_item, $qty)
	{
		return Db::getInstance()->AutoExecute(_DB_PREFIX_.'pack', array('id_product_pack' => intval($id_product), 'id_product_item' => intval($id_item), 'quantity' => intval($qty)), 'INSERT');
	}
	
	public static function duplicate($id_product_old, $id_product_new)
	{
		Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'pack (id_product_pack, id_product_item, quantity)
		(SELECT '.intval($id_product_new).', id_product_item, quantity FROM '._DB_PREFIX_.'pack WHERE id_product_pack = '.intval($id_product_old).')');
		
		// If return query result, a non-pack product will return false
		return true;
	}
}

?>
