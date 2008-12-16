<?php

/**
  * Tags class, Tag.php
  * Tags management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

class Tag extends ObjectModel
{
 	/** @var integer Language id */
	public 		$id_lang;
	
 	/** @var string Name */
	public 		$name;
	
 	protected 	$fieldsRequired = array('id_lang');
 	protected 	$fieldsValidate = array('id_lang' => 'isUnsignedId');
 	protected	$fieldsRequiredLang = array('name');
 	protected	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'tag';
	protected 	$identifier = 'id_tag';
	
	public function __construct($id = NULL, $name = NULL, $id_lang = NULL)
	{
		if ($id)
			parent::__construct($id);
		elseif($name AND Validate::isGenericName($name) AND $id_lang AND Validate::isUnsignedId($id_lang))
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
	 	$list = '';
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

			$list .= '('.intval($tagObj->id).','.intval($id_product).'),';
		}
		$list = rtrim($list, ',');
		
		if (!Validate::isValuesList($list))
			Tools::displayError();

		return Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'product_tag` (`id_tag`, `id_product`) 
		VALUES '.$list);
	}
	
	static public function getMainTags($id_lang, $nb = 10)
	{
		return Db::getInstance()->ExecuteS('
		SELECT t.name, COUNT(pt.id_tag) AS times
		FROM `'._DB_PREFIX_.'product_tag` pt
		LEFT JOIN `'._DB_PREFIX_.'tag` t ON t.id_tag = pt.id_tag
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = pt.id_product
		WHERE id_lang = '.intval($id_lang).'
		AND p.active = 1
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
}

?>