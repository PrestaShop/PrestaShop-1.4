<?php

/**
  * Group reduction class, GroupReduction.php
  * Group reduction management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class GroupReductionCore extends ObjectModel
{
	public	$id_group;
	public	$id_category;
	public	$reduction;

 	protected 	$fieldsRequired = array('id_group', 'id_category', 'reduction');
 	protected 	$fieldsValidate = array('id_group' => 'isUnsignedId', 'id_category' => 'isUnsignedId', 'reduction' => 'isPrice');

	protected 	$table = 'group_reduction';
	protected 	$identifier = 'id_group_reduction';

	private static $reductionCache = array();
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_group'] = (int)($this->id_group);
		$fields['id_category'] = (int)($this->id_category);
		$fields['reduction'] = floatval($this->reduction);
		return $fields;
	}

	public function add($autodate = true, $nullValues = false)
	{
		return (parent::add($autodate, $nullValues) AND $this->_setCache());
	}

	public function update($nullValues = false)
	{
		return (parent::update($nullValues) AND Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_group_reduction_cache` WHERE `id_group` = '.(int)($this->id_group)) AND $this->_setCache());
	}

	private function _setCache()
	{
		$resource = Db::getInstance()->ExecuteS('
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_product` cp
			INNER JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = cp.`id_product`)
			WHERE cp.`id_category` = '.(int)($this->id_category)
		, false);
		$query = 'INSERT INTO `'._DB_PREFIX_.'product_group_reduction_cache` (`id_product`, `id_group`, `reduction`) VALUES ';
		while ($row = Db::getInstance()->nextRow($resource))
			$query .= '('.(int)($row['id_product']).', '.(int)($this->id_group).', '.floatval($this->reduction).'), ';
		return Db::getInstance()->Execute(rtrim($query, ', '));
	}

	static public function getGroupReductions($id_group, $id_lang)
	{
		return Db::getInstance()->ExecuteS('
			SELECT gr.`id_group_reduction`, gr.`id_group`, gr.`id_category`, gr.`reduction`, cl.`name` AS category_name
			FROM `'._DB_PREFIX_.'group_reduction` gr
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = gr.`id_category` AND cl.`id_lang` = '.(int)($id_lang).')
			WHERE `id_group` = '.(int)($id_group)
		);
	}

	static public function getValueForProduct($id_product, $id_group)
	{
		if (!isset(self::$reductionCache[$id_product.'-'.$id_group]))
			self::$reductionCache[$id_product.'-'.$id_group] = Db::getInstance()->getValue('SELECT `reduction` FROM `'._DB_PREFIX_.'product_group_reduction_cache` WHERE `id_product` = '.(int)($id_product).' AND `id_group` = '.(int)($id_group));
		return self::$reductionCache[$id_product.'-'.$id_group];
	}

	static public function doesExist($id_group, $id_category)
	{
		return (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_group` FROM `'._DB_PREFIX_.'group_reduction` WHERE `id_group` = '.(int)($id_group).' AND `id_category` = '.(int)($id_category));
	}
}

?>
