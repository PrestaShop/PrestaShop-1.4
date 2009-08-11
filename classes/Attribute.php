<?php

/**
  * Attributes class, Attribute.php
  * Attributes management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Attribute extends ObjectModel
{	
	/** @var integer Group id which attribute belongs */
	public		$id_attribute_group;
	
	/** @var string Name */
	public 		$name;
	public		$color;
	
	public		$default;
	
 	protected 	$fieldsRequired = array('id_attribute_group');
	protected 	$fieldsValidate = array('id_attribute_group' => 'isUnsignedId', 'color' => 'isColor');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 64);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');
		
	protected 	$table = 'attribute';
	protected 	$identifier = 'id_attribute';

	public function getFields()
	{
		parent::validateFields();

		$fields['id_attribute_group'] = intval($this->id_attribute_group);
		$fields['color'] = pSQL($this->color);

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
		return parent::getTranslationsFields(array('name'));
	}

	public function delete()
	{
		if (($result = Db::getInstance()->ExecuteS('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `'.$this->identifier.'` = '.intval($this->id))) === false)
			return false;
		$combinationIds = array();
		if (Db::getInstance()->numRows())
		{
			foreach ($result AS $row)
				$combinationIds[] = intval($row['id_product_attribute']);
			if (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `'.$this->identifier.'` = '.intval($this->id)) === false)
				return false;
			if (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product_attribute` IN ('.implode(', ', $combinationIds).')') === false)
				return false;
		}
		return parent::delete();
	}

	/**
	 * Get all attributes for a given language
	 *
	 * @param integer $id_lang Language id
	 * @param boolean $notNull Get only not null fields if true
	 * @return array Attributes
	 */
	static public function getAttributes($id_lang, $notNull = false)
	{
		return Db::getInstance()->ExecuteS('
		SELECT ag.*, agl.*, a.`id_attribute`, al.`name`, agl.`name` AS `attribute_group`
		FROM `'._DB_PREFIX_.'attribute_group` ag
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute_group` = ag.`id_attribute_group`
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.intval($id_lang).')
		'.($notNull ? 'WHERE a.`id_attribute` IS NOT NULL AND al.`name` IS NOT NULL' : '').'
		ORDER BY agl.`name` ASC, al.`name` ASC');
	}
	
	/**
	 * Get quantity for a given attribute combinaison
	 * Check if quantity is enough to deserve customer
	 *
	 * @param integer $id_product_attribute Product attribute combinaison id
	 * @param integer $qty Quantity needed
	 * @return boolean Quantity is available or not
	 */
	static public function checkAttributeQty($id_product_attribute, $qty)
	{ 		
		$result = Db::getInstance()->getRow('
		SELECT `quantity`
		FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product_attribute` = '.intval($id_product_attribute));

		return ($result AND ($qty <= $result['quantity']));
	}

	/**
	 * Get quantity for product with attributes quantity
	 *
	 * @acces public static
	 * @param integer $id_product
	 * @return mixed Quantity or false
	 */
	static public function getAttributeQty($id_product)
	{
		$row = Db::getInstance()->getRow('
		SELECT SUM(quantity) as quantity
		FROM `'._DB_PREFIX_.'product_attribute` 
		WHERE `id_product` = '.intval($id_product));
		
		if ($row['quantity'] !== NULL)
			return intval($row['quantity']);
		return false;
	}

	/**
	 * Update array with veritable quantity
	 *
	 * @acces public static
	 * @param array &$arr
	 * return bool
	 */
	static public function updateQtyProduct(&$arr)
	{
		$id_product = intval($arr['id_product']);
		$qty = self::getAttributeQty($id_product);
		
		if ($qty !== false)
		{
			$arr['quantity'] = intval($qty);
			return true;
		}
		return false;
	}

	public function isColorAttribute()
	{
		if (!Db::getInstance()->getRow('
			SELECT `is_color_group` FROM `'._DB_PREFIX_.'attribute_group` WHERE `id_attribute_group` = (
				SELECT `id_attribute_group` FROM `'._DB_PREFIX_.'attribute` WHERE `id_attribute` = '.intval($this->id).')
				AND is_color_group = 1'))
			return false;
		return Db::getInstance()->NumRows();
	}
}

?>