<?php

/**
  * Categories class, Category.php
  * Categories management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Category extends ObjectModel
{
	public 		$id;

	/** @var integer category ID */
	public $id_category;

	/** @var string Name */
	public 		$name;

	/** @var boolean Status for display */
	public 		$active = 1;

	/** @var string Description */
	public 		$description;

	/** @var integer Parent category ID */
	public 		$id_parent;

	/** @var integer Parents number */
	public 		$level_depth;

	/** @var string string used in rewrited URL */
	public 		$link_rewrite;

	/** @var string Meta title */
	public 		$meta_title;

	/** @var string Meta keywords */
	public 		$meta_keywords;

	/** @var string Meta description */
	public 		$meta_description;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;
	
	private static $_links = array();


	protected $tables = array ('category', 'category_lang');

	protected 	$fieldsRequired = array('id_parent', 'active');
 	protected 	$fieldsSize = array('id_parent' => 10, 'active' => 1);
 	protected 	$fieldsValidate = array('active' => 'isBool');
	protected 	$fieldsRequiredLang = array('name', 'link_rewrite');
 	protected 	$fieldsSizeLang = array('name' => 64, 'link_rewrite' => 64, 'meta_title' => 128, 'meta_description' => 128, 'meta_description' => 128);
 	protected 	$fieldsValidateLang = array('name' => 'isCatalogName', 'link_rewrite' => 'isLinkRewrite', 'description' => 'isCleanHtml',
											'meta_title' => 'isGenericName', 'meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName');

	protected 	$table = 'category';
	protected 	$identifier = 'id_category';

	/** @var string id_image is the category ID when an image exists and 'default' otherwise */
	public		$id_image = 'default';

	public function __construct($id_category = NULL, $id_lang = NULL)
	{
		parent::__construct($id_category, $id_lang);
		$this->id_image = ($this->id AND file_exists(_PS_CAT_IMG_DIR_.intval($this->id).'.jpg')) ? intval($this->id) : false;
	}

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_category'] = intval($this->id);		
		$fields['active'] = intval($this->active);
		$fields['id_parent'] = intval($this->id_parent);
		$fields['level_depth'] = intval($this->level_depth);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}

	/**
	  * Check then return multilingual fields for database interaction
	  *
	  * @return array Multilingual fields
	  */
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name', 'description', 'link_rewrite', 'meta_title', 'meta_keywords', 'meta_description'));
	}

	public	function add($autodate = true, $nullValues = false)
	{
		$this->level_depth = $this->calcLevelDepth();
		foreach ($this->name AS $k => $value)
			if (preg_match('/^[1-9]\./', $value))
				$this->name[$k] = '0'.$value;
		$ret = parent::add($autodate);
		$this->updateGroup(Tools::getValue('groupBox'));
		return $ret;
	}

	public	function update($nullValues = false)
	{
		$this->level_depth = $this->calcLevelDepth();
		foreach ($this->name AS $k => $value)
			if (preg_match('/^[1-9]\./', $value))
				$this->name[$k] = '0'.$value;
		return parent::update();
	}

	/**
	  * Recursive scan of subcategories
	  *
	  * @param integer $maxDepth Maximum depth of the tree (i.e. 2 => 3 levels depth)
 	  * @param integer $currentDepth specify the current depth in the tree (don't use it, only for rucursivity!)
	  * @param array $excludedIdsArray specify a list of ids to exclude of results
 	  * @param integer $idLang Specify the id of the language used
	  *
 	  * @return array Subcategories lite tree
	  */
	function recurseLiteCategTree($maxDepth = 3, $currentDepth = 0, $idLang = NULL, $excludedIdsArray = NULL)
	{
		global $link;

		//get idLang
		$idLang = is_null($idLang) ? _USER_ID_LANG_ : intval($idLang);

		//recursivity for subcategories
		$children = array();
		$subcats = $this->getSubCategories($idLang, true);
		if (sizeof($subcats) AND ($maxDepth == 0 OR $currentDepth < $maxDepth))
			foreach ($subcats as &$subcat)
			{
				if (!$subcat['id_category'])
					break;
				elseif ( !is_array($excludedIdsArray) || !in_array($subcat['id_category'], $excludedIdsArray) )
				{
					$categ = new Category($subcat['id_category'] ,$idLang);
					$categ->name = Category::hideCategoryPosition($categ->name);
					$children[] = $categ->recurseLiteCategTree($maxDepth, $currentDepth + 1, $idLang, $excludedIdsArray);
				}
			}


		return array(
			'id' => $this->id_category,
			'link' => $link->getCategoryLink($this->id, $this->link_rewrite),
			'name' => $this->name,
			'desc'=> $this->description,
			'children' => $children
		);
	}

	static public function recurseCategory($categories, $current, $id_category = 1, $id_selected = 1)
	{
		global $currentIndex;
		echo '<option value="'.$id_category.'"'.(($id_selected == $id_category) ? ' selected="selected"' : '').'>'.
		str_repeat('&nbsp;', $current['infos']['level_depth'] * 5).self::hideCategoryPosition(stripslashes($current['infos']['name'])).'</option>';
		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] AS $key => $row)
				self::recurseCategory($categories, $categories[$id_category][$key], $key, $id_selected);
	}


	/**
	  * Recursively add specified category childs to $toDelete array
	  *
	  * @param array &$toDelete Array reference where categories ID will be saved
	  * @param array $id_category Parent category ID
	  */
	private function recursiveDelete(&$toDelete, $id_category)
	{
	 	if (!is_array($toDelete))
	 		die(Tools::displayError());

		$result = Db::getInstance()->ExecuteS('
		SELECT `id_category`
		FROM `'._DB_PREFIX_.'category`
		WHERE `id_parent` = '.intval($id_category));
		foreach ($result AS $k => $row)
		{
			$toDelete[] = intval($row['id_category']);
			$this->recursiveDelete($toDelete, intval($row['id_category']));
		}
	}

	public function delete()
	{
		if ($this->id == 1) return false;

		/* Get childs categories */
		$toDelete = array(intval($this->id));
		$this->recursiveDelete($toDelete, intval($this->id));
		$toDelete = array_unique($toDelete);

		/* Delete category and its child from database */
		$list = sizeof($toDelete) > 1 ? implode(',', $toDelete) : intval($this->id);
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category` WHERE `id_category` IN ('.$list.')');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category_lang` WHERE `id_category` IN ('.$list.')');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` IN ('.$list.')');

		/* Delete categories images */
		foreach ($toDelete AS $id_category)
			deleteImage(intval($id_category));

		/* Delete products which were\'t in others categories */
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_product`
		FROM `'._DB_PREFIX_.'product`
		WHERE `id_product` NOT IN (SELECT `id_product` FROM `'._DB_PREFIX_.'category_product`)');
		foreach ($result as $p)
		{
			$product = new Product(intval($p['id_product']));
			if (Validate::isLoadedObject($product))
				$product->delete();
		}
		
		/* Set category default to 1 where categorie no more exists */
		$result = Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'product`
		SET `id_category_default` = 1
		WHERE `id_category_default`
		NOT IN (SELECT `id_category` FROM `'._DB_PREFIX_.'category`)');
		
		return true;
	}

	/**
	  * Get the number of parent categories
	  *
	  * @return integer Level depth
	  */
	public function calcLevelDepth()
	{
		$parentCategory = new Category(intval($this->id_parent));
		if (!$parentCategory)
			die('parent category does not exist');
		return $parentCategory->level_depth + 1;
	}

	/**
	  * Return available categories
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active categories
	  * @return array Categories
	  */
	static public function getCategories($id_lang, $active = true, $order = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
		WHERE `id_lang` = '.intval($id_lang).'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		if (!$order)
			return $result;

		$categories = array();
		foreach ($result AS $row)
			$categories[$row['id_parent']][$row['id_category']]['infos'] = $row;

		return $categories;
	}

	static public function getSimpleCategories($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT c.`id_category`, cl.`name`
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`)
		WHERE cl.`id_lang` = '.intval($id_lang).'
		ORDER BY cl.`name`');
	}

	/**
	  * Return current category childs
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active categories
	  * @return array Categories
	  */
	public function getSubCategories($id_lang, $active = true)
	{
	 	global $cookie;
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance()->ExecuteS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description 
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.intval($id_lang).')
		WHERE `id_parent` = '.intval($this->id).'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		/* Modify SQL result */
		foreach ($result AS &$row)
		{
			$row['name'] = Category::hideCategoryPosition($row['name']);
			$row['id_image'] = (file_exists(_PS_CAT_IMG_DIR_.$row['id_category'].'.jpg')) ? $row['id_category'] : Language::getIsoById($cookie->id_lang).'-default';
			$row['legend'] = 'no picture';
		}
		return $result;
	}
	
	private static function getAllSubCats(&$all_cats, $id_cat, $id_lang)
	{
		$category = new Category(intval($id_cat));
		$sub_cats = $category->getSubcategories($id_lang);
		if(count($sub_cats) > 0)
			foreach ($sub_cats AS $sub_cat)
			{
				$all_cats[] = $sub_cat['id_category'];
				self::getAllSubCats($all_cats, $sub_cat['id_category'], $id_lang);
			}
	}
	
	public static function countNbProductAndSub($id_category, $id_lang)
	{
		$tab = array(intval($id_category));
		Category::getAllSubCats($tab, intval($id_category), intval($id_lang));
		$listCategories = implode(',', $tab);
		$sql = '
			SELECT SUM(IFNULL(pa.`quantity`, p.`quantity`)) AS nb
			FROM `'._DB_PREFIX_.'category` c
			INNER JOIN `'._DB_PREFIX_.'category_product` pc ON (pc.`id_category` = c.`id_category` AND c.`id_category` IN ('.$listCategories.'))
			INNER JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = pc.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product`)';
		$result = Db::getInstance()->getRow($sql);
		return $result['nb'];
	}

	/**
	  * Return current category products
	  *
	  * @param integer $id_lang Language ID
	  * @param integer $p Page number
	  * @param integer $n Number of products per page
	  * @param boolean $getTotal return the number of results instead of the results themself
	  * @param boolean $active return only active products
	  * @param boolean $random active a random filter for returned products
	  * @param int $randomNumberProducts number of products to return if random is activated
	  * @return mixed Products or number of products
	  */
	public function getProducts($id_lang, $p, $n, $orderBy = NULL, $orderWay = NULL, $getTotal = false, $active = true, $random = false, $randomNumberProducts = 1 )
	{
		global $cookie;

		if ($p < 1) $p = 1;
		if (empty($orderBy))
			$orderBy = 'position';
		if (empty($orderWay))
			$orderWay = 'ASC';
		if ($orderBy == 'id_product' OR	$orderBy == 'date_add')
			$orderByPrefix = 'p';
		elseif ($orderBy == 'name')
			$orderByPrefix = 'pl';
		elseif ($orderBy == 'manufacturer')
		{
			$orderByPrefix = 'm';
			$orderBy = 'name';
		}
		elseif ($orderBy == 'position')
			$orderByPrefix = 'cp';
		
		if ($orderBy == 'price')
			$orderBy = 'orderprice';
      
		if (!Validate::isBool($active) OR !Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die (Tools::displayError());

		$id_supplier = intval(Tools::getValue('id_supplier'));

		/* Return only the number of products */
		if ($getTotal)
		{
			$result = Db::getInstance()->getRow('
			SELECT COUNT(cp.`id_product`) AS total
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`
			WHERE cp.`id_category` = '.intval($this->id).($active ? ' AND p.`active` = 1' : '').'
			'.($id_supplier ? 'AND p.id_supplier = '.$id_supplier : '').'');
			return isset($result) ? $result['total'] : 0;
		}

		$sql = '
		SELECT p.*, pa.`id_product_attribute`, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`, cl.`name` AS category_default, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
					(p.price - IF((DATEDIFF(reduction_from, CURDATE()) <= 0 AND DATEDIFF(reduction_to, CURDATE()) >=0) OR reduction_from = reduction_to, IFNULL(reduction_price, (p.price * reduction_percent / 100)),0)) AS orderprice 
		FROM `'._DB_PREFIX_.'category_product` cp
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND default_on = 1)
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
		WHERE cp.`id_category` = '.intval($this->id).($active ? ' AND p.`active` = 1' : '').'
		'.($id_supplier ? 'AND p.id_supplier = '.$id_supplier : '');
		
		if ($random === true)
		{
			$sql .= 'ORDER BY RAND()';
			$sql .= 'LIMIT 0, '.intval($randomNumberProducts);
		}
		else
		{
			$sql .= 'ORDER BY '.(isset($orderByPrefix) ? $orderByPrefix.'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
			LIMIT '.((intval($p) - 1) * intval($n)).','.intval($n);
		}
		
		$result = Db::getInstance()->ExecuteS($sql);
		
		if ($orderBy == 'orderprice')
		{
			Tools::orderbyPrice($result, $orderWay);
		}
		if (!$result)
			return false;

		/* Modify SQL result */
		return Product::getProductsProperties($id_lang, $result);
	}

	/**
	  * Hide category prefix used for position
	  *
	  * @param string $name Category name
	  * @return string Name without position
	  */
	static public function hideCategoryPosition($name)
	{
		return preg_replace('/^[0-9]+\./', '', $name);
	}

	/**
	  * Return main categories
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active categories
	  * @return array categories
	  */
	static public function getHomeCategories($id_lang, $active = true)
	{
		return self::getChildren(1, $id_lang, $active);
	}

	static public function getRootCategory($id_lang = NULL)
	{
		//get idLang
		$id_lang = is_null($id_lang) ? _USER_ID_LANG_ : intval($id_lang);
		return new Category (1, $id_lang);
	}

	static public function getChildren($id_parent, $id_lang, $active = true)
	{
		if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance()->ExecuteS('
		SELECT c.`id_category`, cl.`name`, cl.`link_rewrite`
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
		WHERE `id_lang` = '.intval($id_lang).'
		AND c.`id_parent` = '.intval($id_parent).'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		/* Modify SQL result */
		$resultsArray = array();
		foreach ($result AS $row)
		{
			$row['name'] = Category::hideCategoryPosition($row['name']);
			$resultsArray[] = $row;
		}
		return $resultsArray;
	}

	/**
	  * Copy products from a category to another
	  *
	  * @param integer $id_old Source category ID
	  * @param boolean $id_new Destination category ID
	  * @return boolean Duplication result
	  */
	public static function duplicateProductCategories($id_old, $id_new)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_category`
		FROM `'._DB_PREFIX_.'category_product`
		WHERE `id_product` = '.intval($id_old));

		$row = array();
		if ($result)
			foreach ($result AS $i)
				$row[] = '('.implode(', ', array(intval($id_new), $i['id_category'], '(SELECT tmp.max + 1 FROM (SELECT MAX(cp.`position`) AS max FROM `'._DB_PREFIX_.'category_product` cp WHERE cp.`id_category`='.intval($i['id_category']).') AS tmp)')).')';

		$flag = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'category_product` (`id_product`, `id_category`, `position`) VALUES '.implode(',', $row));
		return $flag;
	}

	/**
	  * Check if category can be moved in another one
	  *
	  * @param integer $id_parent Parent candidate
	  * @return boolean Parent validity
	  */
	public static function checkBeforeMove($id_category, $id_parent)
	{
		if ($id_category == $id_parent) return false;
		if ($id_parent == 1) return true;
		$i = intval($id_parent);

		while (42)
		{
			$result = Db::getInstance()->getRow('SELECT `id_parent` FROM `'._DB_PREFIX_.'category` WHERE `id_category` = '.intval($i));
			if (!isset($result['id_parent'])) return false;
			if ($result['id_parent'] == $id_category) return false;
			if ($result['id_parent'] == 1) return true;
			$i = $result['id_parent'];
		}
	}

	public static function getLinkRewrite($id_category, $id_lang)
	{
		if (!Validate::isUnsignedId($id_category) OR !Validate::isUnsignedId($id_lang))
			return false;
			
		if (isset(self::$_links[$id_category.'-'.$id_lang]))
			return self::$_links[$id_category.'-'.$id_lang];
		
		$result = Db::getInstance()->getRow('
		SELECT cl.`link_rewrite`
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
		WHERE `id_lang` = '.intval($id_lang).'
		AND c.`id_category` = '.intval($id_category));
		self::$_links[$id_category.'-'.$id_lang] = $result['link_rewrite'];
		return $result['link_rewrite'];
	}
	
	public function getLink()
	{
		global $link;
		return $link->getCategoryLink($this->id, $this->link_rewrite);
	}

	public function getName($id_lang = NULL)
	{
		if (!$id_lang)
		{
			global $cookie;

			if (isset($this->name[$cookie->id_lang]))
				$id_lang = $cookie->id_lang;
			else
				$id_lang = intval(Configuration::get('PS_LANG_DEFAULT'));
		}
		return isset($this->name[$id_lang]) ? $this->name[$id_lang] : '';
	}

	/**
	  * Light back office search for categories
	  *
	  * @param integer $id_lang Language ID
	  * @param string $query Searched string
	  * @param boolean $unrestricted allows search without lang and includes first category and exact match
	  * @return array Corresponding categories
	  */
	static public function searchByName($id_lang, $query, $unrestricted = false)
	{
	 	if (!Validate::isCatalogName($query))
	 		die(Tools::displayError());
		
		if ($unrestricted === true)
			return Db::getInstance()->getRow('
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'category` c
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`)
			WHERE `name` LIKE \''.pSQL($query).'\'');
		else
			return Db::getInstance()->ExecuteS('
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'category` c
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.intval($id_lang).')
			WHERE `name` LIKE \'%'.pSQL($query).'%\' AND c.`id_category` != 1');
	}
	
	/**
	  * Get Each parent category of this category until the root category
	  *
	  * @param integer $id_lang Language ID
	  * @return array Corresponding categories
	  */
	public function getParentsCategories($idLang = null)
	{
		//get idLang
		$idLang = is_null($idLang) ? _USER_ID_LANG_ : intval($idLang);
		
		$categories = null;
		$idCurrent = intval($this->id);
		while (true)
		{
			$query = '
				SELECT c.*, cl.*
				FROM `'._DB_PREFIX_.'category` c
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.intval($idLang).')
				WHERE c.`id_category` = '.$idCurrent.' AND c.`id_parent` != 0
			';
			$result = Db::s($query);
		
			$categories[] = $result[0];
			if(!$result OR $result[0]['id_parent'] == 1)
				return $categories;
			$idCurrent = $result[0]['id_parent'];
		}
	}
	/**
	* Specify if a category already in base
	*
	* @param $id_category Category id
	* @return boolean
	*/	
	static public function categoryExists($id_category)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_category`
		FROM '._DB_PREFIX_.'category c
		WHERE c.`id_category` = '.intval($id_category));
		
		return isset($row['id_category']);
	}
	
	
	public function cleanGroups()
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category_group` WHERE `id_category` = '.intval($this->id));
	}
	
	public function addGroups($groups)
	{
		foreach ($groups as $group)
		{
			$row = array('id_category' => intval($this->id), 'id_group' => intval($group));
			Db::getInstance()->AutoExecute(_DB_PREFIX_.'category_group', $row, 'INSERT');
		}
	}
	
	public function getGroups()
	{
		$groups = array();
		$result = Db::getInstance()->ExecuteS('
		SELECT cg.`id_group`
		FROM '._DB_PREFIX_.'category_group cg
		WHERE cg.`id_category` = '.intval($this->id));
		foreach ($result as $group)
			$groups[] = $group['id_group'];
		return $groups;
	}
	
	public function checkAccess($id_customer)
	{
		if (!$id_customer)
		{
			$result = Db::getInstance()->getRow('
			SELECT ctg.`id_group`
			FROM '._DB_PREFIX_.'category_group ctg
			WHERE ctg.`id_category` = '.intval($this->id).' AND ctg.`id_group` = 1');
		} else {
			$result = Db::getInstance()->getRow('
			SELECT ctg.`id_group`
			FROM '._DB_PREFIX_.'category_group ctg
			INNER JOIN '._DB_PREFIX_.'customer_group cg on (cg.`id_group` = ctg.`id_group` AND cg.`id_customer` = '.intval($id_customer).')
			WHERE ctg.`id_category` = '.intval($this->id));
		}
		if ($result AND isset($result['id_group']) AND $result['id_group'])
			return true;
		return false;
	}
	
	public function updateGroup($list)
	{
		$this->cleanGroups();
		if ($list AND sizeof($list))
			$this->addGroups($list);
	}
}

?>
