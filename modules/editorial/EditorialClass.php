<?php

/**
  * Editorial class, EditorialClass.php
  * Editorial management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class		EditorialClass extends ObjectModel
{
	/** @var integer editorial id*/
	public		$id = 1;
	
	/** @var string body_title*/
	public		$body_home_logo_link;

	/** @var string body_title*/
	public		$body_title;

	/** @var string body_title*/
	public		$body_subheading;

	/** @var string body_title*/
	public		$body_paragraph;

	/** @var string body_title*/
	public		$body_logo_subheading;
	
	protected 	$table = 'editorial';
	protected 	$identifier = 'id_editorial';
	
	protected 	$fieldsValidate = array('body_home_logo_link' => 'isUrl');
	protected 	$fieldsValidateLang = array(
		'body_title' => 'isGenericName',
		'body_subheading' => 'isGenericName',
		'body_paragraph' => 'isCleanHtml',
		'body_logo_subheading' => 'isGenericName');
	
	/**
	  * Check then return multilingual fields for database interaction
	  *
	  * @return array Multilingual fields
	  */
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();

		$fieldsArray = array('body_title', 'body_subheading', 'body_paragraph', 'body_logo_subheading');
		$fields = array();
		$languages = Language::getLanguages(false);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = intval($language['id_lang']);
			$fields[$language['id_lang']][$this->identifier] = intval($this->id);
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']], true);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage], true);
				else
					$fields[$language['id_lang']][$field] = '';
			}
		}
		return $fields;
	}
	
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (key_exists($key, $this) AND $key != 'id_'.$this->table)
				$this->{$key} = $value;

		/* Multilingual fields */
		if (sizeof($this->fieldsValidateLang))
		{
			$languages = Language::getLanguages(false);
			foreach ($languages AS $language)
				foreach ($this->fieldsValidateLang AS $field => $validation)
					if (isset($_POST[$field.'_'.intval($language['id_lang'])]))
						$this->{$field}[intval($language['id_lang'])] = $_POST[$field.'_'.intval($language['id_lang'])];
		}
	}
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_editorial'] = intval($this->id);
		$fields['body_home_logo_link'] = pSQL($this->body_home_logo_link);
		return $fields;
	}
}
