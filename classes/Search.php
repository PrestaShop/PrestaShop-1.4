<?php

/**
  * Search class, Search.php
  * Search management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

class Search
{	
	/**
	* @param string $expr Expression to find
	* @return string Filter
	*/
	private function _getFilter(&$expr)
	{
		$filter = '';
		switch (_DB_TYPE_)
		{
			case 'MySQL':
				$filter = '
				MATCH (pl.`name`, `description`, `description_short`, `ean13`, `reference`)
				AGAINST (\''.$expr.'\' IN BOOLEAN MODE)';
				break;
			default:
				$expr = preg_replace('/\*/', '%', $expr);
				$keywords = split(' ', $expr);
				foreach($keywords as $key => $value)
				{
					$filter .= ($key != 0 ? ' AND ' : '').(($value[0] == '-')
					? '
					(pl.`name` NOT LIKE \'%'.substr($value, 1).'%\'
					AND `description` NOT LIKE \'%'.substr($value, 1).'%\'
					AND `description_short` NOT LIKE \'%'.substr($value, 1).'%\'
					AND `reference` NOT LIKE \'%'.substr($value, 1).'%\'
					AND `ean13` NOT LIKE \'%'.substr($value, 1).'%\')'
					
					: '
					(pl.`name` LIKE \'%'.$value.'%\'
					OR `description` LIKE \'%'.$value.'%\'
					OR `description_short` LIKE \'%'.$value.'%\'
					OR `reference` LIKE \'%'.$value.'%\'
					OR `ean13` LIKE \'%'.$value.'%\')');
				}
				$filter = '('.$filter.')';
		}
		return $filter;
	}
	
	/**
	* @param string $expr Expression to find
	* @return string SQL query
	*/
	private function _getScore(&$expr)
	{		
		switch (_DB_TYPE_)
		{
			case 'MySQL':
				return '
				(MATCH (pl.`name`) AGAINST (\''.$expr.'\' IN BOOLEAN MODE)) * 10
				+ (MATCH (`description`, `description_short`) AGAINST (\''.$expr.'\' IN BOOLEAN MODE)) + 1 AS score';
			default:
				return '1 AS score';
		}
	}
	
	/**
	* @param integer $id_lang Language id for results
	* @param string $expr Expression to find
	* @param boolean $count Only to get number of results (optional)
	* @param string $pageNumber Current page (optional)
	* @param string $pageSize Results per page (optional)
	* @return array Search results
	*/
	public function find($id_lang, $expr, $count = false, $pageNumber = 1, $pageSize = 10, $orderBy = false, $orderWay = false)
	{
	 	global $link;
		
		if (!is_numeric($pageNumber) OR !is_numeric($pageSize) 
		OR !Validate::isBool($count) OR !Validate::isValidSearch($expr)
		OR $orderBy AND !$orderWay)
			die(Tools::displayError());
		
		$id_supplier = intval(Tools::getValue('id_supplier'));
		$id_category = intval(Tools::getValue('id_category'));
		$alias = new Alias(NULL, $expr);
		if (Validate::isLoadedObject($alias))
			$expr = $alias->search;
		if (!Validate::isValidSearch($expr))
			die(Tools::displayError());
		if ($pageNumber < 1) $pageNumber = 1;
		if ($pageSize < 1) $pageSize = 10;

		$expr = str_replace(' ', '* ', pSQL(str_replace('\'', ' ', $expr))).'*';
		/* Only if we need total results number */
		if ($count)
		{
			$result = Db::getInstance()->getRow('
			SELECT COUNT(p.`id_product`) AS nb
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
			'.($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (p.id_product = cp.id_product AND id_category = '.intval($id_category).')' : '').'
			WHERE `active` = 1
			'.($id_supplier ? 'AND id_supplier = '.intval($id_supplier) : '').'
			AND '.$this->_getFilter($expr));
			return isset($result['nb']) ? $result['nb'] : 0;
		}
		/* else we search for the expression */
		$result = Db::getInstance()->ExecuteS('
		SELECT p.*, pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`, t.`rate`, i.`id_image`, il.`legend`, '.$this->_getScore($expr).' 
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT OUTER JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON p.`id_tax` = t.`id_tax`
		'.($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (p.id_product = cp.id_product AND id_category = '.intval($id_category).')' : '').'
		WHERE p.`active` = 1
		'.($id_supplier ? 'AND id_supplier = '.intval($id_supplier) : '').'
		AND '.$this->_getFilter($expr).'
		ORDER BY score DESC'.($orderBy ? ', '.$orderBy : '').($orderWay ? ' '.$orderWay : '').'
		LIMIT '.intval(($pageNumber - 1) * $pageSize).','.intval($pageSize));
		
		if (!$result) return false;

		return Product::getProductsProperties($id_lang, $result);
	}
	
	/**
	* @param integer $id_lang Language id for results
	* @param string $tag Tag to find
	* @param boolean $count Only to get number of results (optional)
	* @param string $pageNumber Current page (optional)
	* @param string $pageSize Results per page (optional)
	* @return array Tag search results
	*/
	public function tag($id_lang, $tag, $count = false, $pageNumber = 0, $pageSize = 10)
	{
	 	global $link;
		if (!is_numeric($pageNumber) OR !is_numeric($pageSize) 
		OR !Validate::isBool($count) OR !Validate::isValidSearch($tag))
			die(Tools::displayError());
		
		if ($pageNumber < 0) $pageNumber = 0;
		if ($pageSize < 1) $pageSize = 10;

		/* Only if we need total results number */
		if ($count)
		{
			$result = Db::getInstance()->getRow('
			SELECT COUNT(pt.`id_product`) AS nb
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'product_tag` pt ON p.`id_product` = pt.`id_product`
			LEFT JOIN `'._DB_PREFIX_.'tag` t ON (pt.`id_tag` = t.`id_tag` AND t.`id_lang` = '.intval($id_lang).')
			WHERE p.`active` = 1
			AND t.`name` LIKE \'%'.pSQL($tag).'%\'');
			return isset($result['nb']) ? $result['nb'] : 0;
		}
		/* Else we search for the expression */
		$result = Db::getInstance()->ExecuteS('
		SELECT p.*, pl.`description_short`, pl.`link_rewrite`, pl.`name`, tax.`rate`, i.`id_image`, il.`legend` 
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT OUTER JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` tax ON p.`id_tax` = tax.`id_tax`
		LEFT JOIN `'._DB_PREFIX_.'product_tag` pt ON p.`id_product` = pt.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'tag` t ON (pt.`id_tag` = t.`id_tag` AND t.`id_lang` = '.intval($id_lang).')
		WHERE p.`active` = 1
		AND t.`name` LIKE \'%'.pSQL($tag).'%\'
		GROUP BY pt.`id_product`');
		if (!$result) return false;

		return Product::getProductsProperties($id_lang, $result);
	}
	
	/**
	* @param integer $id_lang Language id for results
	* @param string $ref Reference to find
	* @param boolean $count Only to get number of results (optional)
	* @param string $pageNumber Current page (optional)
	* @param string $pageSize Results per page (optional)
	* @return array Tag search results
	*/
	public function ref($id_lang, $ref, $count = false, $pageNumber = 0, $pageSize = 10)
	{
		global $link;
		if (!is_numeric($pageNumber) OR !is_numeric($pageSize) 
		OR !Validate::isBool($count) OR !Validate::isValidSearch($ref))
			die(Tools::displayError());
		
		if ($pageNumber < 0) $pageNumber = 0;
		if ($pageSize < 1) $pageSize = 10;
		/* Only if we need total results number */
		if ($count === true)
		{
			$result = Db::getInstance()->getRow('
			SELECT COUNT(p.`id_product`) AS nb
			FROM `'._DB_PREFIX_.'product` p
			WHERE p.`active` = 1
			AND p.`reference` LIKE \'%'.pSQL($ref).'%\'');
			return isset($result['nb']) ? $result['nb'] : 0;
		}
		/* Else we search for the expression */
		$result = Db::getInstance()->ExecuteS('
		SELECT p.*, pl.`description_short`, pl.`link_rewrite`, pl.`name`, tax.`rate`, i.`id_image`, il.`legend` 
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT OUTER JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` tax ON p.`id_tax` = tax.`id_tax`
		LEFT JOIN `'._DB_PREFIX_.'product_tag` pt ON p.`id_product` = pt.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'tag` t ON (pt.`id_tag` = t.`id_tag` AND t.`id_lang` = '.intval($id_lang).')
		WHERE p.`active` = 1
		AND p.`reference` LIKE \'%'.pSQL($ref).'%\'
		GROUP BY pt.`id_product`');
		if (!$result) return false;

		return Product::getProductsProperties($id_lang, $result);
	}
}
	
?>
