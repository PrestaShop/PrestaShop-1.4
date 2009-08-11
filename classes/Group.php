<?php

/**
  * Customer class, Customer.php
  * Customers management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Group extends ObjectModel
{
	public 		$id;

	/** @var string Lastname */
	public 		$name;
	
	/** @var string Reduction */
	public 		$reduction;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('group');

 	protected 	$fieldsRequired = array();
 	protected 	$fieldsSize = array();
 	protected 	$fieldsValidate = array('reduction' => 'isFloat');
	
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
		$fields['reduction'] =floatval($this->reduction);
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
		SELECT g.`id_group`, g.`reduction`, gl.`name`
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
		ORDER BY cg.`id_customer` ASC');
	}
	
	static public function getReduction($id_customer)
	{
		$result = Db::getInstance()->getRow('
		SELECT g.`reduction`
		FROM `'._DB_PREFIX_.'group` g
		LEFT JOIN `'._DB_PREFIX_.'customer_group` cg ON (cg.`id_group` = g.`id_group`)
		WHERE g.`reduction` > 0 AND cg.`id_customer` = '.intval($id_customer).'
		ORDER BY g.`reduction` DESC');
		return $result['reduction'];
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