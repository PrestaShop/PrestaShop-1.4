<?php

/**
  * Main abstract class, ObjectModel.php
  * All objects are extending this abstract class
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

abstract class ObjectModel
{
	/** @var integer Object id */
	public $id;

	/** @var string SQL Table name */
	protected $table = NULL;

	/** @var string SQL Table identifier */
	protected $identifier = NULL;

	/** @var array Required fields for admin panel forms */
 	protected $fieldsRequired = array();

 	/** @var array Maximum fields size for admin panel forms */
 	protected $fieldsSize = array();

 	/** @var array Fields validity functions for admin panel forms */
 	protected $fieldsValidate = array();

	/** @var array Multilingual required fields for admin panel forms */
 	protected $fieldsRequiredLang = array();

 	/** @var array Multilingual maximum fields size for admin panel forms */
 	protected $fieldsSizeLang = array();

 	/** @var array Multilingual fields validity functions for admin panel forms */
 	protected $fieldsValidateLang = array();

	/** @var array tables */
 	protected $tables = array();

	/**
	 * Returns object validation rules (fields validity)
	 *
	 * @param string $className Child class name for static use (optional)
	 * @return array Validation rules (fields validity)
	 */
	static public function getValidationRules($className = __CLASS__)
	{
		$object = new $className();
		return array(
		'required' => $object->fieldsRequired,
		'size' => $object->fieldsSize,
		'validate' => $object->fieldsValidate,
		'requiredLang' => $object->fieldsRequiredLang,
		'sizeLang' => $object->fieldsSizeLang,
		'validateLang' => $object->fieldsValidateLang);
	}

	/**
	 * Prepare fields for ObjectModel class (add, update)
	 * All fields are verified (pSQL, intval...)
	 *
	 * @return array All object fields
	 */
	public function getFields()	{ return array(); }

	/**
	 * Build object
	 *
	 * @param integer $id Existing object id in order to load object (optional)
	 * @param integer $id_lang Required if object is multilingual (optional)
	 */
	public function __construct($id = NULL, $id_lang = NULL)
	{
	 	/* Connect to database and check SQL table/identifier */
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());
		$this->identifier = pSQL($this->identifier);

		/* Load object from database if object id is present */
		if ($id)
		{
			$result = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.$this->table.'` a '.
			($id_lang ? ('LEFT JOIN `'.pSQL(_DB_PREFIX_.$this->table).'_lang` b ON (a.`'.$this->identifier.'` = b.`'.$this->identifier).'` AND `id_lang` = '.intval($id_lang).')' : '')
			.' WHERE a.`'.$this->identifier.'` = '.intval($id));
			if (!$result) return false;
			$this->id = intval($id);
			foreach ($result AS $key => $value)
				if (key_exists($key, $this))
					$this->{$key} = stripslashes($value);

			/* Join multilingual tables */
			if (!$id_lang AND method_exists($this, 'getTranslationsFieldsChild'))
			{
				$sql = 'SELECT * FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE `'.$this->identifier.'` = '.intval($id);
				$result = Db::getInstance()->ExecuteS($sql);
				$defaultLang = intval(Configuration::get('PS_LANG_DEFAULT'));
				if ($result)
					foreach ($result as $row)
						foreach ($row AS $key => $value)
							if (key_exists($key, $this) AND $key != $this->identifier)
								$this->{$key}[$row['id_lang']] = stripslashes($value);
			}
		}
	}

	/**
	 * Save current object to database (add or update)
	 *
	 * return boolean Insertion result
	 */
	public function save($nullValues = false, $autodate = true)
	{
		return intval($this->id) > 0 ? $this->update($nullValues) : $this->add($autodate, $nullValues);
	}

	/**
	 * Add current object to database
	 *
	 * return boolean Insertion result
	 */
	public function add($autodate = true, $nullValues = false)
	{
	 	if (!Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());

		/* Automatically fill dates */
		if ($autodate AND key_exists('date_add', $this))
			$this->date_add = date('Y-m-d H:i:s');
		if ($autodate AND key_exists('date_upd', $this))
			$this->date_upd = date('Y-m-d H:i:s');

		/* Database insertion */
		if ($nullValues)
			$result = Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.$this->table, $this->getFields(), 'INSERT');
		else
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.$this->table, $this->getFields(), 'INSERT');
		if (!$result)
			return false;

		/* Get object id in database */
		$this->id = Db::getInstance()->Insert_ID();
		/* Database insertion for multilingual fields related to the object */
		if (method_exists($this, 'getTranslationsFieldsChild'))
		{
			$fields = $this->getTranslationsFieldsChild();
			if ($fields AND is_array($fields))
				foreach ($fields AS $field)
				{
					foreach ($field AS $key => $value)
					 	if (!Validate::isTableOrIdentifier($key))
			 				die(Tools::displayError());
					$field[$this->identifier] = intval($this->id);
					$result = Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'INSERT') && $result;
				}
		}
		return $result;
	}

	/**
	 * Update current object to database
	 *
	 * return boolean Update result
	 */
	public function update($nullValues = false)
	{
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());

		/* Automatically fill dates */
		if (key_exists('date_upd', $this))
			$this->date_upd = date('Y-m-d H:i:s');

		/* Database update */
		if ($nullValues)
			$result = Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.$this->table, $this->getFields(), 'UPDATE', '`'.pSQL($this->identifier).'` = '.intval($this->id));
		else
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.$this->table, $this->getFields(), 'UPDATE', '`'.pSQL($this->identifier).'` = '.intval($this->id));
		if (!$result)
			return false;

		/* Database update for multilingual fields related to the object */
		if (method_exists($this, 'getTranslationsFieldsChild'))
		{
			$fields = $this->getTranslationsFieldsChild();
			foreach ($fields as $field)
			{
				foreach ($field as $key => $value)
				 	if (!Validate::isTableOrIdentifier($key))
		 				die(Tools::displayError());
				$mode = Db::getInstance()->getRow('SELECT `id_lang` FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE `'.pSQL($this->identifier).
				'` = '.intval($this->id).' AND `id_lang` = '.intval($field['id_lang']));
				$result *= (!Db::getInstance()->NumRows()) ? Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'INSERT') :
				Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'UPDATE', '`'.
				pSQL($this->identifier).'` = '.intval($this->id).' AND `id_lang` = '.intval($field['id_lang']));
			}
		}
		return $result;
	}

	/**
	 * Delete current object from database
	 *
	 * return boolean Deletion result
	 */
	public function delete()
	{
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
	 		die(Tools::displayError());

		/* Database deletion */
		$result = Db::getInstance()->Execute('DELETE FROM `'.pSQL(_DB_PREFIX_.$this->table).'` WHERE `'.pSQL($this->identifier).'` = '.intval($this->id));
		if (!$result)
			return false;

		/* Database deletion for multilingual fields related to the object */
		if (method_exists($this, 'getTranslationsFieldsChild'))
			Db::getInstance()->Execute('DELETE FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE `'.pSQL($this->identifier).'` = '.intval($this->id));
		return $result;
	}

	/**
	 * Delete several objects from database
	 *
	 * return boolean Deletion result
	 */
	public function deleteSelection($selection)
	{
		if (!is_array($selection) OR !Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());
		$result = true;
		foreach ($selection AS $id)
		{
			$this->id = intval($id);
			$result = $result AND $this->delete();
		}
		return $result;
	}

	/**
	 * Toggle object status in database
	 *
	 * return boolean Update result
	 */
	public function toggleStatus()
	{
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
	 		die(Tools::displayError());

	 	/* Object must have a variable called 'active' */
	 	elseif (!key_exists('active', $this))
	 		die(Tools::displayError());

		/* Change status to active/inactive */
		return Db::getInstance()->Execute('
		UPDATE `'.pSQL(_DB_PREFIX_.$this->table).'`
		SET `active` = !`active`
		WHERE `'.pSQL($this->identifier).'` = '.intval($this->id));
	}

	/**
	 * Prepare multilingual fields for database insertion
	 *
	 * @param array $fieldsArray Multilingual fields to prepare
	 * return array Prepared fields for database insertion
	 */
	protected function getTranslationsFields($fieldsArray)
	{
		/* WARNING : Product do not use this function, so do not forget to report any modification if necessary */
	 	if (!Validate::isTableOrIdentifier($this->identifier))
	 		die(Tools::displayError());

		$fields = array();
		$languages = Language::getLanguages();
		$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = intval($this->id);
			foreach ($fieldsArray as $field)
			{
	 			/* Check fields validity */
			 	if (!Validate::isTableOrIdentifier($field))
	 				die(Tools::displayError());

				/* Copy the field, or the default language field if it's both required and empty */
				if (isset($this->{$field}[$language['id_lang']]) AND !Tools::isEmpty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
				else
					$fields[$language['id_lang']][$field] = '';
			}
		}

		return $fields;
	}

	/**
	 * Check for fields validity before database interaction
	 */
	public function validateFields($die = true)
	{
		foreach ($this->fieldsRequired as $field)
			if (Tools::isEmpty($this->{$field}) AND (!is_numeric($this->{$field})))
			{
				if ($die) die (Tools::displayError().' ('.get_class($this).' -> '.$field.' is empty)');
				return false;
			}
		foreach ($this->fieldsSize as $field => $size)
			if (isset($this->{$field}) AND Tools::strlen($this->{$field}) > $size)
			{
				if ($die) die (Tools::displayError().' ('.get_class($this).' -> '.$field.' length > '.$size.')');
				return false;
			}
		$validate = new Validate();
		foreach ($this->fieldsValidate as $field => $method)
			if (!method_exists($validate, $method))
				die (Tools::displayError('validation function not found').' '.$method);
			elseif (!Tools::isEmpty($this->{$field}) AND !call_user_func(array('Validate', $method), $this->{$field}))
			{
				if ($die) die (Tools::displayError().' ('.get_class($this).' -> '.$field.' = '.$this->{$field}.')');
				return false;
			}
		return true;
	}

	/**
	 * Check for multilingual fields validity before database interaction
	 */
	public function validateFieldsLang($die = true, $errorReturn = false)
	{
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		foreach ($this->fieldsRequiredLang as $fieldArray)
		{
			if (!is_array($this->{$fieldArray}))
				continue ;
			if (!$this->{$fieldArray} OR !sizeof($this->{$fieldArray}) OR ($this->{$fieldArray}[$defaultLanguage] !== '0' AND empty($this->{$fieldArray}[$defaultLanguage])))
			{
				if ($die) die (Tools::displayError().' ('.get_class($this).'->'.$fieldArray.' '.Tools::displayError('is empty for default language').')');
				return $errorReturn ? get_class($this).'->'.$fieldArray.' '.Tools::displayError('is empty for default language') : false;
			}
		}
		foreach ($this->fieldsSizeLang as $fieldArray => $size)
		{
			if (!is_array($this->{$fieldArray}))
				continue ;
			foreach ($this->{$fieldArray} as $k => $value)
				if (Tools::strlen($value) > $size)
				{
					if ($die) die (Tools::displayError().' ('.get_class($this).'->'.$fieldArray.' '.Tools::displayError('length >').' '.$size.' '.Tools::displayError('for language').')');
					return $errorReturn ? get_class($this).'->'.$fieldArray.' '.Tools::displayError('length >').' '.$size.' '.Tools::displayError('for language') : false;
				}
		}
		$validate = new Validate();
		foreach ($this->fieldsValidateLang as $fieldArray => $method)
		{
			if (!is_array($this->{$fieldArray}))
				continue ;
			foreach ($this->{$fieldArray} as $k => $value)
				if (!method_exists($validate, $method))
					die (Tools::displayError('validation function not found').' '.$method);
				elseif (!Tools::isEmpty($value) AND !call_user_func(array('Validate', $method), $value))
				{
					if ($die) die (Tools::displayError().' ('.get_class($this).'->'.$fieldArray.' = '.$value.' '.Tools::displayError('for language').' '.$k.')');
					return $errorReturn ? get_class($this).'->'.$fieldArray.' = '.$value.' '.Tools::displayError('for language').' '.$k : false;
				}
		}
		return true;
	}

	static public function displayFieldName($field, $className = __CLASS__, $htmlentities = true)
	{
		global $_FIELDS;
		$key = $className.'_'.md5($field);
		return ((is_array($_FIELDS) AND array_key_exists($key, $_FIELDS)) ? ($htmlentities ? htmlentities($_FIELDS[$key], ENT_QUOTES, 'utf-8') : $_FIELDS[$key]) : $field);
	}

	public function validateControler($htmlentities = true)
	{
		$errors = array();

		/* Checking for required fields */
		foreach ($this->fieldsRequired AS $field)
		if (($value = Tools::getValue($field, $this->{$field})) == false AND (string)$value != '0')
			if (!$this->id OR $field != 'passwd')
				$errors[] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is required');


		/* Checking for maximum fields sizes */
		foreach ($this->fieldsSize AS $field => $maxLength)
			if (($value = Tools::getValue($field, $this->{$field})) AND Tools::strlen($value) > $maxLength)
				$errors[] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is too long').' ('.Tools::displayError('maximum length:').' '.$maxLength.')';

		/* Checking for fields validity */
		foreach ($this->fieldsValidate AS $field => $function)
		{
			// Hack for postcode required for country which does not have postcodes
			if ($value = Tools::getValue($field, $this->{$field}) OR ($field == 'postcode' AND $value == '0'))
			{
				if (!Validate::$function($value))
					$errors[] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is invalid');
				else
				{
					if ($field == 'passwd')
					{
						if ($value = Tools::getValue($field))
							$this->{$field} = Tools::encrypt($value);
					}
					else	
						$this->{$field} = $value;
				}
			}
		}
		return $errors;
	}
}

?>
