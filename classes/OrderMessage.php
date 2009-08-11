<?php

/**
  * Order Messages class, OrderMessage.php
  * Messages management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
class		OrderMessage extends ObjectModel
{
	/** @var string name name */
	public 		$name;
	
	/** @var string message content */
	public 		$message;
	
	/** @var string Object creation date */
	public 		$date_add;
	
	protected	$fieldsRequired = array();
	protected	$fieldsValidate = array();
	protected   $fieldsSize = array();

	protected	$fieldsRequiredLang = array('name', 'message');
	protected	$fieldsSizeLang = array('name' => 128, 'message' => 1200);
	protected	$fieldsValidateLang = array('name' => 'isGenericName', 'message' => 'isMessage');
	
	protected 	$table = 'order_message';
	protected 	$identifier = 'id_order_message';

	public function getFields()
	{
		parent::validateFields();
		return array('date_add' => pSQL($this->date_add));
	}

	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name', 'message'));
	}

	static public function getOrderMessages($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT om.id_order_message, oml.name, oml.message
		FROM '._DB_PREFIX_.'order_message om
		LEFT JOIN '._DB_PREFIX_.'order_message_lang oml ON (oml.id_order_message = om.id_order_message)
		WHERE oml.id_lang = '.intval($id_lang).'
		ORDER BY name ASC');
	}
}
