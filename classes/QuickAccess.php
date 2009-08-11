<?php

/**
  * QuickAccesses class, QuickAccess.php
  * QuickAccesses management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class QuickAccess extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
	/** @var string Link */
	public 		$link;
	
	/** @var boolean New windows or not */
	public 		$new_window;
	
 	protected 	$fieldsRequired = array('link', 'new_window');
 	protected 	$fieldsSize = array('link' => 128);
 	protected 	$fieldsValidate = array('link' => 'isUrl', 'new_window' => 'isBool');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'quick_access';
	protected 	$identifier = 'id_quick_access';
		
	public function getFields()
	{
		parent::validateFields();
		$fields['link'] = pSQL($this->link);
		$fields['new_window'] = intval($this->new_window);
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
	
	/**
	* Get all available quick_accesses
	*
	* @return array QuickAccesses
	*/
	static public function getQuickAccesses($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'quick_access` qa
		LEFT JOIN `'._DB_PREFIX_.'quick_access_lang` qal ON (qa.`id_quick_access` = qal.`id_quick_access` AND qal.`id_lang` = '.intval($id_lang).')
		ORDER BY `name` ASC');
	}
}

?>