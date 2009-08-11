<?php

/**
  * Attribute groups class, AttributeGroup.php
  * Attribute groups management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		AttributeGroup extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	public		$is_color_group;
	
	/** @var string Public Name */
	public 		$public_name;	
	
	protected	$fieldsRequired = array();
	protected	$fieldsValidate = array('is_color_group' => 'isBool');
 	protected 	$fieldsRequiredLang = array('name', 'public_name');
 	protected 	$fieldsSizeLang = array('name' => 64, 'public_name' => 64);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName', 'public_name' => 'isGenericName');
		
	protected 	$table = 'attribute_group';
	protected 	$identifier = 'id_attribute_group';

	public function getFields()
	{
		parent::validateFields();

		$fields['is_color_group'] = intval($this->is_color_group);

		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
	 	return parent::add($autodate, true);
	}
	
	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name', 'public_name'));
	}

	static public function cleanDeadCombinations()
	{
		$attributeCombinations = Db::getInstance()->ExecuteS('SELECT pac.`id_attribute`, pa.`id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` pa LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pa.`id_product_attribute` = pac.`id_product_attribute`)');
		$toRemove = array();
		foreach ($attributeCombinations AS $attributeCombination)
			if (intval($attributeCombination['id_attribute']) == 0)
				$toRemove[] = intval($attributeCombination['id_product_attribute']);
		if (!empty($toRemove) AND Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product_attribute` IN ('.implode(', ', $toRemove).')') === false)
			return false;
		return true;
	}

	public function delete()
	{
		/* Select children in order to find linked combinations */
		$attributeIds = Db::getInstance()->ExecuteS('SELECT `id_attribute` FROM `'._DB_PREFIX_.'attribute` WHERE `id_attribute_group` = '.intval($this->id));
		if ($attributeIds === false)
			return false;
		/* Removing attributes to the found combinations */
		$toRemove = array();
		foreach ($attributeIds AS $attribute)
			$toRemove[] = intval($attribute['id_attribute']);
		if (!empty($toRemove) AND Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `id_attribute` IN ('.implode(', ', $toRemove).')') === false)
			return false;
		/* Remove combinations if they do not possess attributes anymore */
		if (!self::cleanDeadCombinations())
			return false;
	 	/* Also delete related attributes */
		if (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'attribute_lang` WHERE `id_attribute` IN (SELECT id_attribute FROM `'._DB_PREFIX_.'attribute` WHERE `id_attribute_group` = '.intval($this->id).')') === false OR Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'attribute` WHERE `id_attribute_group` = '.intval($this->id)) === false)
			return false;
		return parent::delete();
	}
	
	/**
	 * Get all attributes for a given language / group
	 *
	 * @param integer $id_lang Language id
	 * @param boolean $id_attribute_group Attribute group id
	 * @return array Attributes
	 */
	static public function getAttributes($id_lang, $id_attribute_group)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'attribute` a
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.intval($id_lang).')
		WHERE a.`id_attribute_group` = '.intval($id_attribute_group).'
		ORDER BY `name`');
	}
	
	/**
	 * Get all attributes groups for a given language
	 *
	 * @param integer $id_lang Language id
	 * @return array Attributes groups
	 */
	static public function getAttributesGroups($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'attribute_group` ag
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND `id_lang` = '.intval($id_lang).')
		ORDER BY `name` ASC');
	}
	
	/**
	 * Delete several objects from database
	 *
	 * return boolean Deletion result
	 */
	public function deleteSelection($selection)
	{
		/* Also delete Attributes */
		foreach ($selection AS $value) {
			$obj = new AttributeGroup($value);
			if (!$obj->delete())
				return false;
		}
		return true;
	}
}

?>