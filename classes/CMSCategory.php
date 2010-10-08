<?php

/**
  * Categories class, CMSCategory.php
  * Categories management
  * @CMSCategory classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class		CMSCategory extends ObjectModel
{
	public 		$id;

	/** @var integer CMSCategory ID */
	public 		$id_cms_category;

	/** @var string Name */
	public 		$name;

	/** @var boolean Status for display */
	public 		$active = 1;

	/** @var string Description */
	public 		$description;

	/** @var integer Parent CMSCategory ID */
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


	protected $tables = array ('cms_category', 'cms_category_lang');

	protected 	$fieldsRequired = array('id_parent', 'active');
 	protected 	$fieldsSize = array('id_parent' => 10, 'active' => 1);
 	protected 	$fieldsValidate = array('active' => 'isBool');
	protected 	$fieldsRequiredLang = array('name', 'link_rewrite');
 	protected 	$fieldsSizeLang = array('name' => 64, 'link_rewrite' => 64, 'meta_title' => 128, 'meta_description' => 255, 'meta_keywords' => 255);
 	protected 	$fieldsValidateLang = array('name' => 'isCatalogName', 'link_rewrite' => 'isLinkRewrite', 'description' => 'isCleanHtml',
											'meta_title' => 'isGenericName', 'meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName');

	protected 	$table = 'cms_category';
	protected 	$identifier = 'id_cms_category';

	public function __construct($id_cms_category = NULL, $id_lang = NULL)
	{
		parent::__construct($id_cms_category, $id_lang);
	}

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_cms_category'] = intval($this->id);
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
				if (!$subcat['id_cms_category'])
					break;
				elseif ( !is_array($excludedIdsArray) || !in_array($subcat['id_cms_category'], $excludedIdsArray) )
				{
					$categ = new CMSCategory($subcat['id_cms_category'] ,$idLang);
					$categ->name = CMSCategory::hideCMSCategoryPosition($categ->name);
					$children[] = $categ->recurseLiteCategTree($maxDepth, $currentDepth + 1, $idLang, $excludedIdsArray);
				}
			}


		return array(
			'id' => $this->id_cms_category,
			'link' => $link->getCMSCategoryLink($this->id, $this->link_rewrite),
			'name' => $this->name,
			'desc'=> $this->description,
			'children' => $children
		);
	}

	static public function getRecurseCategory($id_lang, $current = 1)
	{
		$category = Db::getInstance()->getRow('
		SELECT c.`id_cms_category`, c.`id_parent`, c.`level_depth`, cl.`name`
		FROM `'._DB_PREFIX_.'cms_category` c
		JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON c.`id_cms_category` = cl.`id_cms_category`
		WHERE c.`id_cms_category` = '.intval($current).'
		AND `id_lang` = '.intval($id_lang));
		$result = Db::getInstance()->ExecuteS('
		SELECT c.`id_cms_category`, c.`id_parent`, c.`level_depth`, cl.`name`
		FROM `'._DB_PREFIX_.'cms_category` c
		JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON c.`id_cms_category` = cl.`id_cms_category`
		WHERE c.`id_parent` = '.intval($current).'
		AND c.`active` = 1
		AND cl.`id_lang` = '.intval($id_lang));
		foreach ($result as $row)
			$category['children'][] = self::getRecurseCategory($id_lang, $row['id_cms_category']);
		$category['cms'] = Db::getInstance()->ExecuteS('
		SELECT c.`id_cms`, cl.`meta_title`
		FROM `'._DB_PREFIX_.'cms` c
		JOIN `'._DB_PREFIX_.'cms_lang` cl ON c.`id_cms` = cl.`id_cms`
		WHERE `id_cms_category` = '.intval($current).'
		AND cl.`id_lang` = '.intval($id_lang));
		return $category;
	}

	static public function recurseCMSCategory($categories, $current, $id_cms_category = 1, $id_selected = 1, $is_html = 0)
	{
		global $currentIndex;
		$html = '<option value="'.$id_cms_category.'"'.(($id_selected == $id_cms_category) ? ' selected="selected"' : '').'>'.
		str_repeat('&nbsp;', $current['infos']['level_depth'] * 5).self::hideCMSCategoryPosition(stripslashes($current['infos']['name'])).'</option>';
		if ($is_html == 0)
			echo $html;
		if (isset($categories[$id_cms_category]))
			foreach ($categories[$id_cms_category] AS $key => $row)
				$html .= self::recurseCMSCategory($categories, $categories[$id_cms_category][$key], $key, $id_selected, $is_html);
		return $html;
	}
	
	

	/**
	  * Recursively add specified CMSCategory childs to $toDelete array
	  *
	  * @param array &$toDelete Array reference where categories ID will be saved
	  * @param array $id_cms_category Parent CMSCategory ID
	  */
	private function recursiveDelete(&$toDelete, $id_cms_category)
	{
	 	if (!is_array($toDelete) OR !$id_cms_category)
	 		die(Tools::displayError());

		$result = Db::getInstance()->ExecuteS('
		SELECT `id_cms_category`
		FROM `'._DB_PREFIX_.'cms_category`
		WHERE `id_parent` = '.intval($id_cms_category));
		foreach ($result AS $k => $row)
		{
			$toDelete[] = intval($row['id_cms_category']);
			$this->recursiveDelete($toDelete, intval($row['id_cms_category']));
		}
	}

	public function delete()
	{
		if ($this->id == 1) return false;

		/* Get childs categories */
		$toDelete = array(intval($this->id));
		$this->recursiveDelete($toDelete, intval($this->id));
		$toDelete = array_unique($toDelete);

		/* Delete CMS Category and its child from database */
		$list = sizeof($toDelete) > 1 ? implode(',', $toDelete) : intval($this->id);
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_category` WHERE `id_cms_category` IN ('.$list.')');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_category_lang` WHERE `id_cms_category` IN ('.$list.')');

		/* Delete pages which are in categories to delete */
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_cms`
		FROM `'._DB_PREFIX_.'cms`
		WHERE `id_cms_category` IN ('.$list.')');
		foreach ($result as $p)
		{
			$product = new CMS(intval($p['id_cms']));
			if (Validate::isLoadedObject($product))
				$product->delete();
		}
		return true;
	}

	/**
	  * Get the number of parent categories
	  *
	  * @return integer Level depth
	  */
	public function calcLevelDepth()
	{
		$parentCMSCategory = new CMSCategory(intval($this->id_parent));
		if (!$parentCMSCategory)
			die('parent CMS Category does not exist');
		return $parentCMSCategory->level_depth + 1;
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

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON c.`id_cms_category` = cl.`id_cms_category`
		WHERE `id_lang` = '.intval($id_lang).'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		if (!$order)
			return $result;

		$categories = array();
		foreach ($result AS $row)
			$categories[$row['id_parent']][$row['id_cms_category']]['infos'] = $row;
		return $categories;
	}

	static public function getSimpleCategories($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.`id_cms_category`, cl.`name`
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category`)
		WHERE cl.`id_lang` = '.intval($id_lang).'
		ORDER BY cl.`name`');
	}

	/**
	  * Return current CMSCategory childs
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

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category` AND `id_lang` = '.intval($id_lang).')
		WHERE `id_parent` = '.intval($this->id).'
		'.($active ? 'AND `active` = 1' : '').'
		GROUP BY c.`id_cms_category`
		ORDER BY `name` ASC');

		/* Modify SQL result */
		foreach ($result AS &$row)
		{
			$row['name'] = CMSCategory::hideCMSCategoryPosition($row['name']);
		}
		return $result;
	}

	private static function getAllSubCats(&$all_cats, $id_cat, $id_lang)
	{
		$CMSCategory = new CMSCategory(intval($id_cat));
		$sub_cats = $CMSCategory->getSubcategories($id_lang);
		if(count($sub_cats) > 0)
			foreach ($sub_cats AS $sub_cat)
			{
				$all_cats[] = $sub_cat['id_cms_category'];
				self::getAllSubCats($all_cats, $sub_cat['id_cms_category'], $id_lang);
			}
	}

	/**
	  * Hide CMSCategory prefix used for position
	  *
	  * @param string $name CMSCategory name
	  * @return string Name without position
	  */
	static public function hideCMSCategoryPosition($name)
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

	static public function getRootCMSCategory($id_lang = NULL)
	{
		//get idLang
		$id_lang = is_null($id_lang) ? _USER_ID_LANG_ : intval($id_lang);
		return new CMSCategory (1, $id_lang);
	}

	static public function getChildren($id_parent, $id_lang, $active = true)
	{
		if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.`id_cms_category`, cl.`name`, cl.`link_rewrite`
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON c.`id_cms_category` = cl.`id_cms_category`
		WHERE `id_lang` = '.intval($id_lang).'
		AND c.`id_parent` = '.intval($id_parent).'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		/* Modify SQL result */
		$resultsArray = array();
		foreach ($result AS $row)
		{
			$row['name'] = CMSCategory::hideCMSCategoryPosition($row['name']);
			$resultsArray[] = $row;
		}
		return $resultsArray;
	}

	/**
	  * Check if CMSCategory can be moved in another one
	  *
	  * @param integer $id_parent Parent candidate
	  * @return boolean Parent validity
	  */
	public static function checkBeforeMove($id_cms_category, $id_parent)
	{
		if ($id_cms_category == $id_parent) return false;
		if ($id_parent == 1) return true;
		$i = intval($id_parent);

		while (42)
		{
			$result = Db::getInstance()->getRow('SELECT `id_parent` FROM `'._DB_PREFIX_.'cms_category` WHERE `id_cms_category` = '.intval($i));
			if (!isset($result['id_parent'])) return false;
			if ($result['id_parent'] == $id_cms_category) return false;
			if ($result['id_parent'] == 1) return true;
			$i = $result['id_parent'];
		}
	}

	public static function getLinkRewrite($id_cms_category, $id_lang)
	{
		if (!Validate::isUnsignedId($id_cms_category) OR !Validate::isUnsignedId($id_lang))
			return false;

		if (isset(self::$_links[$id_cms_category.'-'.$id_lang]))
			return self::$_links[$id_cms_category.'-'.$id_lang];

		$result = Db::getInstance()->getRow('
		SELECT cl.`link_rewrite`
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON c.`id_cms_category` = cl.`id_cms_category`
		WHERE `id_lang` = '.intval($id_lang).'
		AND c.`id_cms_category` = '.intval($id_cms_category));
		self::$_links[$id_cms_category.'-'.$id_lang] = $result['link_rewrite'];
		return $result['link_rewrite'];
	}

	public function getLink()
	{
		global $link;
		return $link->getCMSCategoryLink($this->id, $this->link_rewrite);
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
	  * @param boolean $unrestricted allows search without lang and includes first CMSCategory and exact match
	  * @return array Corresponding categories
	  */
	static public function searchByName($id_lang, $query, $unrestricted = false)
	{
		if ($unrestricted === true)
			return Db::getInstance()->getRow('
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'cms_category` c
			LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category`)
			WHERE `name` LIKE \''.pSQL($query).'\'');
		else
			return Db::getInstance()->ExecuteS('
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'cms_category` c
			LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category` AND `id_lang` = '.intval($id_lang).')
			WHERE `name` LIKE \'%'.pSQL($query).'%\' AND c.`id_cms_category` != 1');
	}
	
	/**
	  * Retrieve CMSCategory by name and parent CMSCategory id
	  *
	  * @param integer $id_lang Language ID
	  * @param string  $CMSCategory_name Searched CMSCategory name
	  * @param integer $id_parent_CMSCategory parent CMSCategory ID
	  * @return array Corresponding CMSCategory
	  */
	static public function searchByNameAndParentCMSCategoryId($id_lang, $CMSCategory_name, $id_parent_CMSCategory)
	{
		return Db::getInstance()->getRow('
		SELECT c.*, cl.*
	    FROM `'._DB_PREFIX_.'cms_category` c
	    LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category` AND `id_lang` = '.intval($id_lang).') 
	    WHERE `name`  LIKE \''.pSQL($CMSCategory_name).'\' 
		AND c.`id_cms_category` != 1 
		AND c.`id_parent` = '.intval($id_parent_CMSCategory));
	}

	/**
	  * Get Each parent CMSCategory of this CMSCategory until the root CMSCategory
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
				FROM `'._DB_PREFIX_.'cms_category` c
				LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category` AND `id_lang` = '.intval($idLang).')
				WHERE c.`id_cms_category` = '.$idCurrent.' AND c.`id_parent` != 0
			';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

			$categories[] = $result[0];
			if(!$result OR $result[0]['id_parent'] == 1)
				return $categories;
			$idCurrent = $result[0]['id_parent'];
		}
	}
	/**
	* Specify if a CMSCategory already in base
	*
	* @param $id_cms_category CMSCategory id
	* @return boolean
	*/
	static public function CMSCategoryExists($id_cms_category)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_cms_category`
		FROM '._DB_PREFIX_.'cms_category c
		WHERE c.`id_cms_category` = '.intval($id_cms_category));

		return isset($row['id_cms_category']);
	}

}

?>
