<?php

/**
  * Tags class, Tag.php
  * Tags management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class Tag extends ObjectModel
{
 	/** @var integer Language id */
	public 		$id_lang;
	
 	/** @var string Name */
	public 		$name;
	
 	protected 	$fieldsRequired = array('id_lang', 'name');
 	protected 	$fieldsValidate = array('id_lang' => 'isUnsignedId', 'name' => 'isGenericName');

	protected 	$table = 'tag';
	protected 	$identifier = 'id_tag';
	
	public function __construct($id = NULL, $name = NULL, $id_lang = NULL)
	{
		if ($id)
			parent::__construct($id);
		elseif ($name AND Validate::isGenericName($name) AND $id_lang AND Validate::isUnsignedId($id_lang))
		{
			$row = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'tag` t
			WHERE `name` LIKE \''.pSQL($name).'\' AND `id_lang` = '.intval($id_lang));
			
			if ($row)
			{
			 	$this->id = intval($row['id_tag']);
			 	$this->id_lang = intval($row['id_lang']);
				$this->name = $row['name'];
			}
		}
	}
		
	public function getFields()
	{
		parent::validateFields();
		$fields['id_lang'] = intval($this->id_lang);
		$fields['name'] = pSQL($this->name);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		if (!parent::add($autodate, $nullValues))
			return false;
		elseif (isset($_POST['products']))
			return $this->setProducts($_POST['products']);
		return true;		
	}
	
	/**
	* Add several tags in database and link it to a product
	*
	* @param integer $id_lang Language id
	* @param integer $id_product Product id to link tags with
	* @param string $string Tags separated by commas
	*
	* @return boolean Operation success
	*/
	static public function addTags($id_lang, $id_product, $string)
	{
	 	if (!Validate::isUnsignedId($id_lang) OR Validate::isTagsList($string))
	 		Tools::displayError();
	 	
	 	$tmpTab = array_unique(array_map('trim', explode(',', $string)));
	 	$list = array();
	 	foreach ($tmpTab AS $tag)
	 	{
	 	 	if (!Validate::isGenericName($tag))
	 	 		return false;
			$tagObj = new Tag(NULL, trim($tag), intval($id_lang));
			
			/* Tag does not exist in database */
			if (!Validate::isLoadedObject($tagObj))
			{
				$tagObj->name = trim($tag);
				$tagObj->id_lang = intval($id_lang);
				$tagObj->add();
			}
			if (!in_array($tagObj->id, $list))
				$list[] = $tagObj->id;
		}
		$data = '';
		foreach ($list AS $tag)
			$data .= '('.intval($tag).','.intval($id_product).'),';
		$data = rtrim($data, ',');

		if (!Validate::isValuesList($list))
			Tools::displayError();

		return Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'product_tag` (`id_tag`, `id_product`) 
		VALUES '.$data);
	}
	
	static public function getMainTags($id_lang, $nb = 10)
	{
		global $cookie;

		return Db::getInstance()->ExecuteS('
		SELECT t.name, COUNT(pt.id_tag) AS times
		FROM `'._DB_PREFIX_.'product_tag` pt
		LEFT JOIN `'._DB_PREFIX_.'tag` t ON t.id_tag = pt.id_tag
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = pt.id_product
		WHERE id_lang = '.intval($id_lang).'
		AND p.`active` = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
		)
		GROUP BY t.id_tag
		ORDER BY times DESC
		LIMIT 0, '.intval($nb));
	}
	
	static public function getProductTags($id_product)
	{
	 	if (!$tmp = Db::getInstance()->ExecuteS('
		SELECT t.`id_lang`, t.`name` 
		FROM '._DB_PREFIX_.'tag t 
		LEFT JOIN '._DB_PREFIX_.'product_tag pt ON (pt.id_tag = t.id_tag) 
		WHERE pt.`id_product`='.intval($id_product)))
	 		return false;
	 	$result = array();
	 	foreach ($tmp AS $tag)
	 		$result[$tag['id_lang']][] = $tag['name'];
	 	return $result;
	}
	
	public function getProducts($associated = true)
	{
		global $cookie;
		$id_lang = $this->id_lang ? $this->id_lang : $cookie->id_lang;
		
		if (!$this->id AND $associated)
			return array();
		
		return Db::getInstance()->ExecuteS('
		SELECT pl.name, pl.id_product
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON p.id_product = pl.id_product
		WHERE pl.id_lang = '.intval($id_lang).'
		AND p.active = 1
		'.($this->id ? ('AND p.id_product '.($associated ? 'IN' : 'NOT IN').' (SELECT pt.id_product FROM `'._DB_PREFIX_.'product_tag` pt WHERE pt.id_tag = '.intval($this->id).')') : '').'
		ORDER BY pl.name');
	}
	
	public function setProducts($array)
	{
		Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'product_tag WHERE id_tag = '.intval($this->id));
		if (is_array($array))
		{
			$array = array_map('intval', $array);
			$result1 = Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product SET indexed = 0 WHERE id_product IN ('.implode(',', $array).')');
			$ids = array();
			foreach ($array as $id_product)
				$ids[] = '('.intval($id_product).','.intval($this->id).')';
			return ($result1 && Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'product_tag (id_product, id_tag) VALUES '.implode(',',$ids)) && Search::indexation(false));
		}
		return $result1;
	}
}

?>