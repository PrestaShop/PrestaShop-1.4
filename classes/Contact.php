<?php

/**
  * Contact class, Contact.php
  * Contacts management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */
  
class ContactCore extends ObjectModel
{
	public 		$id;
	
	/** @var string Name */
	public 		$name;
	
	/** @var string e-mail */
	public 		$email;

	/** @var string Detailed description */
	public 		$description;
	
	public 		$customer_service;
	
 	protected 	$fieldsRequired = array();
 	protected 	$fieldsSize = array('email' => 128);
 	protected 	$fieldsValidate = array('email' => 'isEmail', 'customer_service' => 'isBool');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName', 'description' => 'isCleanHtml');
	
	protected 	$table = 'contact';
	protected 	$identifier = 'id_contact';

	public function getFields()
	{
		parent::validateFields();
		$fields['email'] = pSQL($this->email);
		$fields['customer_service'] = (int)($this->customer_service);
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
		return parent::getTranslationsFields(array('name', 'description'));
	}
	
	/**
	  * Return available contacts
	  *
	  * @param integer $id_lang Language ID
	  * @return array Contacts
	  */
	static public function getContacts($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'contact` c
		LEFT JOIN `'._DB_PREFIX_.'contact_lang` cl ON c.`id_contact` = cl.`id_contact`
		WHERE cl.`id_lang` = '.(int)($id_lang).'
		ORDER BY `name` ASC');
	}
}

?>