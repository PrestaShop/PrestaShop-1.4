<?php

/**
  * Manufacturer class, Manufacturer.php
  * Manufacturers management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Manufacturer extends ObjectModel
{
	public 		$id;
	
	/** @var integer manufacturer ID */
	public $id_manufacturer;	
	
	/** @var string Name */
	public 		$name;
	
	/** @var string A description */
	public 		$description;
	
	/** @var string A short description */
	public 		$short_description;

	/** @var int Address */
	public 		$id_address;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;
	
	/** @var string Friendly URL */
	public 		$link_rewrite;
	
	/** @var string Meta title */
	public 		$meta_title;

	/** @var string Meta keywords */
	public 		$meta_keywords;

	/** @var string Meta description */
	public 		$meta_description;	
	
 	protected 	$fieldsRequired = array('name');
 	protected 	$fieldsSize = array('name' => 64);
 	protected 	$fieldsValidate = array('name' => 'isCatalogName');

	protected	$fieldsSizeLang = array('short_description' => 100, 'meta_title' => 255, 'meta_description' => 255, 'meta_description' => 255);
	protected	$fieldsValidateLang = array('description' => 'isCleanHtml', 'short_description' => 'isCleanHtml', 'meta_title' => 'isGenericName', 'meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName');
	
	protected 	$table = 'manufacturer';
	protected 	$identifier = 'id_manufacturer';

	public function __construct($id = NULL, $id_lang = NULL)
	{
		parent::__construct($id, $id_lang);

		/* Get the manufacturer's id_address */
		$this->id_address = $this->getManufacturerAddress();
		
		$this->link_rewrite = $this->getLink();
	}

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_manufacturer'] = intval($this->id);
		$fields['name'] = pSQL($this->name);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}
	
	public function getTranslationsFieldsChild()
	{
		$fieldsArray = array('description', 'short_description', 'meta_title', 'meta_keywords', 'meta_description');
		$fields = array();
		$languages = Language::getLanguages();
		$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		foreach ($languages as $language)
		{		
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = intval($this->id);
			$fields[$language['id_lang']]['description'] = (isset($this->description[$language['id_lang']])) ? Tools::htmlentitiesDecodeUTF8(pSQL($this->description[$language['id_lang']], true)) : '';
			$fields[$language['id_lang']]['short_description'] = (isset($this->short_description[$language['id_lang']])) ? Tools::htmlentitiesDecodeUTF8(pSQL($this->short_description[$language['id_lang']], true)) : '';
			
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());
				
				/* Check fields validity */
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']], true);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
				else
					$fields[$language['id_lang']][$field] = '';
									
			}
		}
		return $fields;
	}

	public function delete()
	{
		$address = new Address($this->id_address);
		if (!$address->delete())
			return false;
		return parent::delete();
	}

	/**
	 * Delete several objects from database
	 *
	 * return boolean Deletion result
	 */
	public function deleteSelection($selection)
	{
		if (!is_array($selection) OR !Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());
		$result = true;
		foreach ($selection AS $id)
		{
			$this->id = intval($id);
			$this->id_address = self::getManufacturerAddress();
			$result = $result AND $this->delete();
		}
		return $result;
	}

	protected function getManufacturerAddress()
	{
		if (!intval($this->id))
			return false;
		$result = Db::GetInstance()->getRow('SELECT `id_address` FROM '._DB_PREFIX_.'address WHERE `id_manufacturer` = '.intval($this->id));
		if (!$result)
			return false;
		return $result['id_address'];
	}

	/**
	  * Return manufacturers
	  *
	  * @param boolean $getNbProducts [optional] return products numbers for each
	  * @return array Manufacturers
	  */
	static public function getManufacturers($getNbProducts = false, $id_lang = 0, $active = false, $p = false, $n = false)
	{
		global $cookie;

		if (!$id_lang)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$sql = 'SELECT m.*, ml.`description`';
		$sql.= ' FROM `'._DB_PREFIX_.'manufacturer` as m
		LEFT JOIN `'._DB_PREFIX_.'manufacturer_lang` ml ON (m.`id_manufacturer` = ml.`id_manufacturer` AND ml.`id_lang` = '.intval($id_lang).')';
		$sql.= ' ORDER BY m.`name` ASC'.($p ? ' LIMIT '.((intval($p) - 1) * intval($n)).','.intval($n) : '');
		$manufacturers = Db::getInstance()->ExecuteS($sql);
		if ($manufacturers === false)
			return false;
		if ($getNbProducts)
			foreach ($manufacturers as $key => $manufacturer)
			{
				$sql = '
					SELECT p.`id_product`
					FROM `'._DB_PREFIX_.'product` p
					LEFT JOIN `'._DB_PREFIX_.'manufacturer` as m ON (m.`id_manufacturer`= p.`id_manufacturer`)
					WHERE m.`id_manufacturer` = '.intval($manufacturer['id_manufacturer']).'
					AND p.`id_product` IN (
						SELECT cp.`id_product`
						FROM `'._DB_PREFIX_.'category_group` cg
						LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
						WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
					)';
				$result = Db::getInstance()->ExecuteS($sql);
				$manufacturers[$key]['nb_products'] = sizeof($result);
			}
		for ($i = 0; $i < sizeof($manufacturers); $i++)
			if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
				$manufacturers[$i]['link_rewrite'] = Tools::link_rewrite($manufacturers[$i]['name'], false);
			else
				$manufacturers[$i]['link_rewrite'] = 0;
		return $manufacturers;
	}
	
	static public function getManufacturersWithoutAddress()
	{
		$sql = 'SELECT m.* FROM `'._DB_PREFIX_.'manufacturer` m
				LEFT JOIN `'._DB_PREFIX_.'address` a ON (a.`id_manufacturer` = m.`id_manufacturer` AND a.`deleted` = 0)
				WHERE a.`id_manufacturer` IS NULL';
		return Db::getInstance()->ExecuteS($sql);
	}
	
	/**
	  * Return name from id
	  *
	  * @param integer $id_manufacturer Manufacturer ID
	  * @return string name
	  */
	static public function getNameById($id_manufacturer)
	{
		$result = Db::getInstance()->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'manufacturer`
		WHERE `id_manufacturer` = '.intval($id_manufacturer));
		if (isset($result['name']))
			return $result['name'];
		return false;
	}
	
	static public function getIdByName($name)
	{
		$result = Db::getInstance()->getRow('
		SELECT `id_manufacturer`
		FROM `'._DB_PREFIX_.'manufacturer`
		WHERE `name` = \''.pSQL($name).'\'');
		if (isset($result['id_manufacturer']))
			return intval($result['id_manufacturer']);
		return false;
	}
	
	public function getLink()
	{
		return Tools::link_rewrite($this->name, false);
	}

	static public function getProducts($id_manufacturer, $id_lang, $p, $n, $orderBy = NULL, $orderWay = NULL, $getTotal = false, $active = true)
	{
		global $cookie;
		
		if ($p < 1) $p = 1;
	 	if (empty($orderBy) ||$orderBy == 'position') $orderBy = 'name';
	 	if (empty($orderWay)) $orderWay = 'ASC';
			
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die (Tools::displayError());
			
		/* Return only the number of products */
		if ($getTotal)
		{
			$sql = '
				SELECT p.`id_product`
				FROM `'._DB_PREFIX_.'product` p
				WHERE p.id_manufacturer = '.intval($id_manufacturer)
				.($active ? ' AND p.`active` = 1' : '').'
				AND p.`id_product` IN (
					SELECT cp.`id_product`
					FROM `'._DB_PREFIX_.'category_group` cg
					LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
					WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
				)';
			$result = Db::getInstance()->ExecuteS($sql);
			return intval(sizeof($result));
		}
		$sql = '
		SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
		WHERE p.`id_manufacturer` = '.intval($id_manufacturer).($active ? ' AND p.`active` = 1' : '').'
		AND p.`id_product` IN (
					SELECT cp.`id_product`
					FROM `'._DB_PREFIX_.'category_group` cg
					LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
					WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
				)
		ORDER BY '.(($orderBy == 'id_product') ? 'p.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).' 
		LIMIT '.((intval($p) - 1) * intval($n)).','.intval($n);
		$result = Db::getInstance()->ExecuteS($sql);
		if (!$result)
			return false;
		if ($orderBy == 'price')
			Tools::orderbyPrice($result, $orderWay);
		return Product::getProductsProperties($id_lang, $result);
	}
	
	public function getProductsLite($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT p.`id_product`,  pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		WHERE p.`id_manufacturer` = '.intval($this->id));
	}
	/*
	* Specify if a manufacturer already in base
	*
	* @param $id_manufacturer Manufacturer id
	* @return boolean
	*/	
	static public function manufacturerExists($id_manufacturer)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_manufacturer`
		FROM '._DB_PREFIX_.'manufacturer m
		WHERE m.`id_manufacturer` = '.intval($id_manufacturer));
		
		return isset($row['id_manufacturer']);
	}
	
	public function getAddresses($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT a.*, cl.name AS `country`, s.name AS `state`
		FROM `'._DB_PREFIX_.'address` AS a
		LEFT JOIN `'._DB_PREFIX_.'country_lang` AS cl ON (cl.`id_country` = a.`id_country` AND cl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'state` AS s ON (s.`id_state` = a.`id_state`)
		WHERE `id_manufacturer` = '.intval($this->id).'
		AND a.`deleted` = 0');
	}
}

?>
