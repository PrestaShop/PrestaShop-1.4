<?php

/**
  * Supplier class, Supplier.php
  * Suppliers management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

class		Supplier extends ObjectModel
{
	public 		$id;
	
	/** @var integer supplier ID */
	public $id_supplier;
	
	/** @var string Name */
	public 		$name;
	
	/** @var string A short description for the discount */
	public 		$description;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;
	
 	protected 	$fieldsRequired = array('name');
 	protected 	$fieldsSize = array('name' => 64);
 	protected 	$fieldsValidate = array('name' => 'isCatalogName');
	
	protected	$fieldsSizeLang = array('description' => 128);
	protected	$fieldsValidateLang = array('description' => 'isGenericName');
	
	protected 	$table = 'supplier';
	protected 	$identifier = 'id_supplier';
		
	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_supplier'] = intval($this->id);			
		$fields['name'] = pSQL($this->name);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd); 
		return $fields;
	}
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('description'));
	}
	
	/**
	  * Return suppliers
	  *
	  * @return array Suppliers
	  */
	static public function getSuppliers($getNbProducts = false, $id_lang = 0, $active = false, $p = false, $n = false)
	{
		if (!$id_lang)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$query = 'SELECT s.*, sl.`description`';
		if ($getNbProducts) $query .= ' , COUNT(p.`id_product`) as nb_products';
		$query .= ' FROM `'._DB_PREFIX_.'supplier` as s
		LEFT JOIN `'._DB_PREFIX_.'supplier_lang` sl ON (s.`id_supplier` = sl.`id_supplier` AND sl.`id_lang` = '.intval($id_lang).')';
		if ($getNbProducts)
			$query .= ' LEFT JOIN `'._DB_PREFIX_.'product` as p ON (p.`id_supplier` = s.`id_supplier`)
			'.($active ? 'WHERE p.`active` = 1' : '').'
			GROUP BY s.`id_supplier`';
		$query .= ' ORDER BY s.`name` ASC'.($p ? ' LIMIT '.((intval($p) - 1) * intval($n)).','.intval($n) : '');
		$suppliers = Db::getInstance()->ExecuteS($query);
		if ($suppliers === false)
			return false;

		for ($i = 0; $i < sizeof($suppliers); $i++)
			if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
				$suppliers[$i]['link_rewrite'] = Tools::link_rewrite($suppliers[$i]['name'], false);
			else
				$suppliers[$i]['link_rewrite'] = 0;
		return $suppliers;
	}
	
	/**
	  * Return name from id
	  *
	  * @param integer $id_supplier Supplier ID
	  * @return string name
	  */
	static public function getNameById($id_supplier)
	{
		$result = Db::getInstance()->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'supplier`
		WHERE `id_supplier` = '.intval($id_supplier));
		if (isset($result['name']))
			return $result['name'];
		return false;
	}
	
	static public function getProducts($id_supplier, $id_lang, $p, $n, $orderBy = NULL, $orderWay = NULL, $getTotal = false, $active = true)
	{
	 	if (empty($orderBy)) $orderBy = 'name';
	 	if (empty($orderWay)) $orderWay = 'ASC';
			
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die (Tools::displayError());
			
		/* Return only the number of products */
		if ($getTotal)
		{
			$result = Db::getInstance()->getRow('
			SELECT COUNT(p.`id_product`) AS total 
			FROM `'._DB_PREFIX_.'product` p
			WHERE p.id_supplier = '.intval($id_supplier)
			.($active ? ' AND p.`active` = 1' : ''));
			return isset($result) ? $result['total'] : 0;
		}
	 
		$sql = '
			SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, s.`name` AS supplier_name, tl.`name` AS tax_name, t.`rate`
			FROM `'._DB_PREFIX_.'product` p
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
				LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'supplier` s ON s.`id_supplier` = p.`id_supplier`
			WHERE p.`id_supplier` = '.intval($id_supplier).($active ? ' AND p.`active` = 1' : '').'
			ORDER BY '.(($orderBy == 'id_product') ? 'p.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).' 
			LIMIT '.((intval($p) - 1) * intval($n)).','.intval($n);
		$result = Db::getInstance()->ExecuteS($sql);
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}
	
	public function getProductsLite($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT p.`id_product`,  pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		WHERE p.`id_supplier` = '.intval($this->id));
	}
	/*
	* Specify if a supplier already in base
	*
	* @param $id_supplier Supplier id
	* @return boolean
	*/	
	static public function supplierExists($id_supplier)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_supplier`
		FROM '._DB_PREFIX_.'supplier s
		WHERE c.`id_supplier` = '.intval($id_supplier));
		
		return isset($row['id_supplier']);
	}
}
?>