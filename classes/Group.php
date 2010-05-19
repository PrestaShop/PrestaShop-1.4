<?php

/**
  * Customer class, Customer.php
  * Customers management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class		Group extends ObjectModel
{
	public 		$id;

	/** @var string Lastname */
	public 		$name;
	
	/** @var string Reduction */
	public 		$reduction;

	/** @var int Price display method (tax inc/tax exc) */
	public		$price_display_method;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('group');

 	protected 	$fieldsRequired = array('price_display_method');
 	protected 	$fieldsSize = array();
 	protected 	$fieldsValidate = array('reduction' => 'isFloat', 'price_display_method' => 'isPriceDisplayMethod');
	
	protected	$fieldsRequiredLang = array('name');
	protected	$fieldsSizeLang = array('name' => 32);
	protected	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'group';
	protected 	$identifier = 'id_group';

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_group'] = intval($this->id);
		$fields['reduction'] = floatval($this->reduction);
		$fields['price_display_method'] = intval($this->price_display_method);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);

		return $fields;
	}
	
	public function getTranslationsFieldsChild()
	{
		if (!parent::validateFieldsLang())
			return false;
		return parent::getTranslationsFields(array('name'));
	}
	
	static public function getGroups($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT g.`id_group`, g.`reduction`, g.`price_display_method`, gl.`name`
		FROM `'._DB_PREFIX_.'group` g
		LEFT JOIN `'._DB_PREFIX_.'group_lang` AS gl ON (g.`id_group` = gl.`id_group` AND gl.`id_lang` = '.intval($id_lang).')
		ORDER BY g.`id_group` ASC');
	}
	
	public function getCustomers()
	{
		return Db::getInstance()->ExecuteS('
		SELECT cg.`id_customer`, c.*
		FROM `'._DB_PREFIX_.'customer_group` cg
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (cg.`id_customer` = c.`id_customer`)
		WHERE cg.`id_group` = '.intval($this->id).' 
		AND c.`deleted` != 1 
		ORDER BY cg.`id_customer` ASC');
	}
	
	static public function getReduction($id_customer = NULL)
	{
		if ($id_customer)
			$customer = new Customer(intval($id_customer));
		return Db::getInstance()->getValue('
		SELECT `reduction`
		FROM `'._DB_PREFIX_.'group`
		WHERE `id_group` = '.((isset($customer) AND Validate::isLoadedObject($customer)) ? intval($customer->id_default_group) : 1));
	}

	static public function getPriceDisplayMethod($id_group)
	{
		return Db::getInstance()->getValue('
		SELECT `price_display_method`
		FROM `'._DB_PREFIX_.'group`
		WHERE `id_group` = '.intval($id_group));
	}

	static public function getDefaultPriceDisplayMethod()
	{
		return Db::getInstance()->getValue('
		SELECT `price_display_method`
		FROM `'._DB_PREFIX_.'group`
		WHERE `id_group` = 1');
	}

	public function add($autodate = true, $nullValues = false)
	{
		return parent::add() && Category::setNewGroupForHome(intval($this->id));
	}

	public function delete()
	{
		if (parent::delete())
		{
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customer_group` WHERE `id_group` = '.intval($this->id));
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category_group` WHERE `id_group` = '.intval($this->id));
			return true;
		}
		return false;
	}
}

?>
