<?php

/**
  * Contact class, Contact.php
  * Contacts management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
class		Contact extends ObjectModel
{
	public 		$id;
	
	/** @var string Name */
	public 		$name;
	
	/** @var string e-mail */
	public 		$email;
	
	/** @var string Detailed description */
	public 		$description;
	
 	protected 	$fieldsRequired = array('email');
 	protected 	$fieldsSize = array('email' => 128);
 	protected 	$fieldsValidate = array('email' => 'isEmail');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName', 'description' => 'isCleanHtml');
	
	protected 	$table = 'contact';
	protected 	$identifier = 'id_contact';

	public function getFields()
	{
		parent::validateFields();
		$fields['email'] = pSQL($this->email);
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
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'contact` c
		LEFT JOIN `'._DB_PREFIX_.'contact_lang` cl ON c.`id_contact` = cl.`id_contact`
		WHERE cl.`id_lang` = '.intval($id_lang).'
		ORDER BY `name` ASC');
	}
}

?>