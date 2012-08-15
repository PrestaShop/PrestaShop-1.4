<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

abstract class ObjectModelCore
{
	/** @var integer Object id */
	public $id;

	/** @var integer lang id */
	protected $id_lang = null;

	/** @var string SQL Table name */
	protected $table = null;

	/** @var string SQL Table identifier */
	protected $identifier = null;

	/** @var array Required fields for admin panel forms */
 	protected $fieldsRequired = array();

	/** @var fieldsRequiredDatabase */
	protected static $fieldsRequiredDatabase = null;

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

 	/** @var array tables */
 	protected $webserviceParameters = array();

	protected static $_cache = array();

	/** @var  string path to image directory. Used for image deletion. */
	protected $image_dir = null;

	/** @var string file type of image files. Used for image deletion. */
	protected $image_format = 'jpg';

	/**
	 * Returns object validation rules (fields validity)
	 *
	 * @param string $className Child class name for static use (optional)
	 * @return array Validation rules (fields validity)
	 */
	public static function getValidationRules($className = __CLASS__)
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
	public function __construct($id = null, $id_lang = null)
	{
		if ($id_lang != null)
		{
			$this->id_lang = Language::getLanguage((int)$id_lang);
			$id_lang = $this->id_lang;
		}

	 	/* Connect to database and check SQL table/identifier */
	 	if (!Validate::isTableOrIdentifier($this->identifier.$this->table))
			die(Tools::displayError());

		/* Load object from database if object id is present */
		if ($id)
		{
			$this->identifier = pSQL($this->identifier);

			if (!isset(self::$_cache[$this->table][(int)$id][(int)$id_lang]))
				self::$_cache[$this->table][(int)$id][(int)$id_lang] = Db::getInstance()->getRow('
				SELECT *
				FROM `'._DB_PREFIX_.$this->table.'` a '.
				($id_lang ? ('LEFT JOIN `'.pSQL(_DB_PREFIX_.$this->table).'_lang` b ON (a.`'.$this->identifier.'` = b.`'.$this->identifier).'` AND `id_lang` = '.(int)$id_lang.')' : '')
				.' WHERE a.`'.$this->identifier.'` = '.(int)$id);

			$result = self::$_cache[$this->table][(int)($id)][(int)($id_lang)];
			if ($result)
			{
				$this->id = (int)$id;
				foreach ($result as $key => $value)
					if (property_exists($this, $key))
						$this->{$key} = $value;

				/* Join multilingual tables */
				if (!$id_lang && method_exists($this, 'getTranslationsFieldsChild'))
				{
					$result = Db::getInstance()->ExecuteS('
					SELECT *
					FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang`
					WHERE `'.$this->identifier.'` = '.(int)$id);
					if ($result)
						foreach ($result as $row)
							foreach ($row as $key => $value)
								if (property_exists($this, $key) && $key != $this->identifier)
								{
									if (!is_array($this->{$key}))
										$this->{$key} = array();
									$this->{$key}[(int)$row['id_lang']] = $value;
								}
				}
			}
		}

		if (!is_array(self::$fieldsRequiredDatabase))
		{
			$fields = $this->getfieldsRequiredDatabase(true);
			if ($fields)
				foreach ($fields as $row)
					self::$fieldsRequiredDatabase[$row['object_name']][(int)$row['id_required_field']] = pSQL($row['field_name']);
			else
				self::$fieldsRequiredDatabase = array();
		}
	}

	/**
	 * Save current object to database (add or update)
	 *
	 * return boolean Insertion result
	 */
	public function save($nullValues = false, $autodate = true)
	{
		return (int)($this->id) > 0 ? $this->update($nullValues) : $this->add($autodate, $nullValues);
	}

	/**
	 * Add current object to database
	 *
	 * return boolean Insertion result
	 */
	public function add($autodate = true, $nullValues = false)
	{
	 	if (!Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError('not table or identifier : ').$this->table);

		/* Automatically fill dates */
		if ($autodate && (isset($this->date_add) || property_exists($this, 'date_add')))
			$this->date_add = date('Y-m-d H:i:s');
		if ($autodate && (isset($this->date_upd) || property_exists($this, 'date_upd')))
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
					foreach (array_keys($field) AS $key)
					 	if (!Validate::isTableOrIdentifier($key))
			 				die(Tools::displayError('key is not table or identifier, ').Tools::safeOutput($key));
					$field[$this->identifier] = (int)$this->id;
					$result &= Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'INSERT');
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
	 	if (!Validate::isTableOrIdentifier($this->identifier) || !Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());

		$this->clearCache();

		/* Automatically fill dates */
		if (property_exists($this, 'date_upd'))
			$this->date_upd = date('Y-m-d H:i:s');

		/* Database update */
		if ($nullValues)
			$result = Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.$this->table, $this->getFields(), 'UPDATE', '`'.pSQL($this->identifier).'` = '.(int)($this->id));
		else
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.$this->table, $this->getFields(), 'UPDATE', '`'.pSQL($this->identifier).'` = '.(int)($this->id));
		if (!$result)
			return false;

		// Database update for multilingual fields related to the object
		if (method_exists($this, 'getTranslationsFieldsChild'))
		{
			$fields = $this->getTranslationsFieldsChild();
			if (is_array($fields))
				foreach ($fields as $field)
				{
					foreach (array_keys($field) as $key)
						if (!Validate::isTableOrIdentifier($key))
							die(Tools::displayError());

					// used to insert missing lang entries
					$where_lang = '`'.pSQL($this->identifier).'` = '.(int)($this->id).' AND `id_lang` = '.(int)($field['id_lang']);

					$lang_found = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE '. $where_lang);

					if (!$lang_found)
						$result &= Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'INSERT');
					else
						$result &= Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'UPDATE', $where_lang);
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
	 	if (!Validate::isTableOrIdentifier($this->identifier) || !Validate::isTableOrIdentifier($this->table))
	 		die(Tools::displayError());

		$this->clearCache();

		/* Database deletion */
		$result = Db::getInstance()->Execute('DELETE FROM `'.pSQL(_DB_PREFIX_.$this->table).'` WHERE `'.pSQL($this->identifier).'` = '.(int)($this->id));
		if (!$result)
			return false;

		/* Database deletion for multilingual fields related to the object */
		if (method_exists($this, 'getTranslationsFieldsChild'))
			Db::getInstance()->Execute('DELETE FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE `'.pSQL($this->identifier).'` = '.(int)($this->id));
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
			$this->id = (int)($id);
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

	 	/* Update active status on object */
	 	$this->active = (int)(!$this->active);

		/* Change status to active/inactive */
		return Db::getInstance()->Execute('
		UPDATE `'.pSQL(_DB_PREFIX_.$this->table).'`
		SET `active` = !`active`
		WHERE `'.pSQL($this->identifier).'` = '.(int)($this->id));
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
	 		die(Tools::displayError('identifier is not table or identifier : ').Tools::safeOutput($this->identifier));

		$fields = array();

		if ($this->id_lang == NULL)
			foreach (Language::getLanguages(false) as $language)
				$this->makeTranslationFields($fields, $fieldsArray, $language['id_lang']);
		else
			$this->makeTranslationFields($fields, $fieldsArray, $this->id_lang);

		return $fields;
	}

	protected function makeTranslationFields(&$fields, &$fieldsArray, $id_language)
	{
		$fields[$id_language]['id_lang'] = $id_language;
		$fields[$id_language][$this->identifier] = (int)$this->id;
		foreach ($fieldsArray as $field)
		{
			/* Check fields validity */
			if (!Validate::isTableOrIdentifier($field))
				die(Tools::displayError());

			/* Copy the field, or the default language field if it's both required and empty */
			if ((!$this->id_lang && isset($this->{$field}[$id_language]) && !empty($this->{$field}[$id_language]))
				|| ($this->id_lang && isset($this->$field) && !empty($this->$field)))
				$fields[$id_language][$field] = $this->id_lang ? pSQL($this->$field) : pSQL($this->{$field}[$id_language]);
			elseif (in_array($field, $this->fieldsRequiredLang))
				$fields[$id_language][$field] = $this->id_lang ? pSQL($this->$field) : pSQL($this->{$field}[Configuration::get('PS_LANG_DEFAULT')]);
			else
				$fields[$id_language][$field] = '';
		}
	}

	/**
	 * Check for fields validity before database interaction
	 */
	public function validateFields($die = true, $errorReturn = false)
	{
		$fieldsRequired = array_merge($this->fieldsRequired, (isset(self::$fieldsRequiredDatabase[get_class($this)]) ? self::$fieldsRequiredDatabase[get_class($this)] : array()));
		foreach ($fieldsRequired as $field)
			if (Tools::isEmpty($this->{$field}) AND (!is_numeric($this->{$field})))
			{
				if ($die) die (Tools::displayError().' ('.Tools::safeOutput(get_class($this)).' -> '.Tools::safeOutput($field).' is empty)');
				return $errorReturn ? get_class($this).' -> '.$field.' is empty' : false;
			}
		foreach ($this->fieldsSize as $field => $size)
			if (isset($this->{$field}) AND Tools::strlen($this->{$field}) > $size)
			{
				if ($die) die (Tools::displayError().' ('.Tools::safeOutput(get_class($this)).' -> '.Tools::safeOutput($field).' Length '.Tools::safeOutput($size).')');
				return $errorReturn ? get_class($this).' -> '.$field.' Length '.$size : false;
			}
		$validate = new Validate();
		foreach ($this->fieldsValidate as $field => $method)
			if (!method_exists($validate, $method))
				die (Tools::displayError('Validation function not found.').' '.$method);
			elseif (!empty($this->{$field}) AND !call_user_func(array('Validate', $method), $this->{$field}))
			{
				if ($die) die (Tools::displayError().' ('.Tools::safeOutput(get_class($this)).' -> '.Tools::safeOutput($field).' = '.Tools::safeOutput($this->{$field}).')');
				return $errorReturn ? get_class($this).' -> '.$field.' = '.$this->{$field} : false;
			}
		return true;
	}

	/**
	 * Check for multilingual fields validity before database interaction
	 */
	public function validateFieldsLang($die = true, $errorReturn = false)
	{
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		foreach ($this->fieldsRequiredLang as $fieldArray)
		{
			if (!is_array($this->{$fieldArray}))
				continue ;
			if (!$this->{$fieldArray} OR !sizeof($this->{$fieldArray}) OR ($this->{$fieldArray}[$defaultLanguage] !== '0' AND empty($this->{$fieldArray}[$defaultLanguage])))
			{
				if ($die) die (Tools::displayError().' ('.Tools::safeOutput(get_class($this)).'->'.Tools::safeOutput($fieldArray).' '.Tools::displayError('is empty for default language.').')');
				return $errorReturn ? get_class($this).'->'.$fieldArray.' '.Tools::displayError('is empty for default language.') : false;
			}
		}
		foreach ($this->fieldsSizeLang as $fieldArray => $size)
		{
			if (!is_array($this->{$fieldArray}))
				continue ;
			foreach ($this->{$fieldArray} as $k => $value)
				if (Tools::strlen($value) > $size)
				{
					if ($die) die (Tools::displayError().' ('.Tools::safeOutput(get_class($this)).'->'.Tools::safeOutput($fieldArray).' '.Tools::displayError('Length').' '.Tools::safeOutput($size).' '.Tools::displayError('for language').')');
					return $errorReturn ? get_class($this).'->'.$fieldArray.' '.Tools::displayError('Length').' '.$size.' '.Tools::displayError('for language') : false;
				}
		}
		$validate = new Validate();
		foreach ($this->fieldsValidateLang as $fieldArray => $method)
		{
			if (!is_array($this->{$fieldArray}))
				continue ;
			foreach ($this->{$fieldArray} as $k => $value)
				if (!method_exists($validate, $method))
					die (Tools::displayError('Validation function not found.').' '.Tools::safeOutput($method));
				elseif (!empty($value) AND !call_user_func(array('Validate', $method), $value))
				{
					if ($die) die (Tools::displayError('The following field is invalid according to the validate method ').'<b>'.Tools::safeOutput($method).'</b>:<br/> ('.Tools::safeOutput(get_class($this)).'->'.Tools::safeOutput($fieldArray).' = '.Tools::safeOutput($value).' '.Tools::displayError('for language').' '.Tools::safeOutput($k).')');
					return $errorReturn ? Tools::displayError('The following field is invalid according to the validate method ').'<b>'.$method.'</b>:<br/> ('. get_class($this).'->'.$fieldArray.' = '.$value.' '.Tools::displayError('for language').' '.$k : false;
				}
		}
		return true;
	}

	public static function displayFieldName($field, $className = __CLASS__, $htmlentities = true)
	{
		global $_FIELDS, $cookie;
		$iso = strtolower(Language::getIsoById($cookie->id_lang ? (int)$cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')));
		@include(_PS_TRANSLATIONS_DIR_.$iso.'/fields.php');

		$key = $className.'_'.md5($field);
		return ((is_array($_FIELDS) AND array_key_exists($key, $_FIELDS)) ? ($htmlentities ? htmlentities($_FIELDS[$key], ENT_QUOTES, 'utf-8') : $_FIELDS[$key]) : $field);
	}

	/**
	* TODO: refactor rename all calls to this to validateController
	*/
	public function validateControler($htmlentities = true, $copy_post = false)
	{
		return $this->validateController($htmlentities, $copy_post);
	}

	public function validateController($htmlentities = true, $copy_post = false)
	{
		$errors = array();

		/* Checking for required fields */
		$fieldsRequired = array_merge($this->fieldsRequired, (isset(self::$fieldsRequiredDatabase[get_class($this)]) ? self::$fieldsRequiredDatabase[get_class($this)] : array()));
		foreach ($fieldsRequired AS $field)
		if (($value = Tools::getValue($field, $this->{$field})) == false AND (string)$value != '0')
			if (!$this->id OR $field != 'passwd')
				$errors[] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is required.');


		/* Checking for maximum fields sizes */
		foreach ($this->fieldsSize AS $field => $maxLength)
			if (($value = Tools::getValue($field, $this->{$field})) AND Tools::strlen($value) > $maxLength)
				$errors[] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is too long.').' ('.Tools::displayError('Maximum length:').' '.$maxLength.')';

		/* Checking for fields validity */
		foreach ($this->fieldsValidate AS $field => $function)
		{

			if ($copy_post && is_array($this->exclude_copy_post) && in_array($field, $this->exclude_copy_post))
				continue;

			// Hack for postcode required for country which does not have postcodes
			if ($value = Tools::getValue($field, $this->{$field}) OR ($field == 'postcode' AND $value == '0'))
			{
				if (!Validate::$function($value))
					$errors[] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is invalid.');
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

	public function getWebserviceParameters($wsParamsAttributeName = NULL)
	{
		$defaultResourceParameters = array(
			'objectSqlId' => $this->identifier,
			'retrieveData' => array(
				'className' => get_class($this),
				'retrieveMethod' => 'getWebserviceObjectList',
				'params' => array(),
				'table' => $this->table,
			),
			'fields' => array(
				'id' => array('sqlId' => $this->identifier, 'i18n' => false),
			),
		);

		if (is_null($wsParamsAttributeName))
			$wsParamsAttributeName = 'webserviceParameters';


		if (!isset($this->{$wsParamsAttributeName}['objectNodeName']))
			$defaultResourceParameters['objectNodeName'] = $this->table;
		if (!isset($this->{$wsParamsAttributeName}['objectsNodeName']))
			$defaultResourceParameters['objectsNodeName'] = $this->table.'s';

		if (isset($this->{$wsParamsAttributeName}['associations']))
			foreach ($this->{$wsParamsAttributeName}['associations'] as $assocName => &$association)
			{
				if (!array_key_exists('setter', $association) || (isset($association['setter']) && !$association['setter']))
					$association['setter'] = Tools::toCamelCase('set_ws_'.$assocName);
				if (!array_key_exists('getter', $association))
					$association['getter'] = Tools::toCamelCase('get_ws_'.$assocName);
			}


		if (isset($this->{$wsParamsAttributeName}['retrieveData']) && isset($this->{$wsParamsAttributeName}['retrieveData']['retrieveMethod']))
			unset($defaultResourceParameters['retrieveData']['retrieveMethod']);

		$resourceParameters = array_merge_recursive($defaultResourceParameters, $this->{$wsParamsAttributeName});
		if (isset($this->fieldsSize))
			foreach ($this->fieldsSize as $fieldName => $maxSize)
			{
				if (!isset($resourceParameters['fields'][$fieldName]))
					$resourceParameters['fields'][$fieldName] = array('required' => false);
				$resourceParameters['fields'][$fieldName] = array_merge(
					$resourceParameters['fields'][$fieldName],
					$resourceParameters['fields'][$fieldName] = array('sqlId' => $fieldName, 'maxSize' => $maxSize, 'i18n' => false)
				);
			}
		if (isset($this->fieldsValidate))
			foreach ($this->fieldsValidate as $fieldName => $validateMethod)
			{
				if (!isset($resourceParameters['fields'][$fieldName]))
					$resourceParameters['fields'][$fieldName] = array('required' => false);
				$resourceParameters['fields'][$fieldName] = array_merge(
					$resourceParameters['fields'][$fieldName],
					$resourceParameters['fields'][$fieldName] = array(
						'sqlId' => $fieldName,
						'validateMethod' => (
								array_key_exists('validateMethod', $resourceParameters['fields'][$fieldName]) ?
								array_merge($resourceParameters['fields'][$fieldName]['validateMethod'], array($validateMethod)) :
								array($validateMethod)
							),
						'i18n' => false
					)
				);
			}
		if (isset($this->fieldsRequired))
		{
			$fieldsRequired = array_merge($this->fieldsRequired, (isset(self::$fieldsRequiredDatabase[get_class($this)]) ? self::$fieldsRequiredDatabase[get_class($this)] : array()));
			foreach ($fieldsRequired as $fieldRequired)
			{
				if (!isset($resourceParameters['fields'][$fieldRequired]))
					$resourceParameters['fields'][$fieldRequired] = array();
				$resourceParameters['fields'][$fieldRequired] = array_merge(
					$resourceParameters['fields'][$fieldRequired],
					$resourceParameters['fields'][$fieldRequired] = array('sqlId' => $fieldRequired, 'required' => true, 'i18n' => false)
				);
			}
		}
		if (isset($this->fieldsSizeLang))
			foreach ($this->fieldsSizeLang as $fieldName => $maxSize)
			{
				if (!isset($resourceParameters['fields'][$fieldName]))
					$resourceParameters['fields'][$fieldName] = array('required' => false);
				$resourceParameters['fields'][$fieldName] = array_merge(
					$resourceParameters['fields'][$fieldName],
					$resourceParameters['fields'][$fieldName] = array('sqlId' => $fieldName, 'maxSize' => $maxSize, 'i18n' => true)
				);
			}
		if (isset($this->fieldsValidateLang))
			foreach ($this->fieldsValidateLang as $fieldName => $validateMethod)
			{
				if (!isset($resourceParameters['fields'][$fieldName]))
					$resourceParameters['fields'][$fieldName] = array('required' => false);
				$resourceParameters['fields'][$fieldName] = array_merge(
					$resourceParameters['fields'][$fieldName],
					$resourceParameters['fields'][$fieldName] = array(
						'sqlId' => $fieldName,
						'validateMethod' => (
								array_key_exists('validateMethod', $resourceParameters['fields'][$fieldName]) ?
								array_merge($resourceParameters['fields'][$fieldName]['validateMethod'], array($validateMethod)) :
								array($validateMethod)
							),
						'i18n' => true
					)
				);
			}

		if (isset($this->fieldsRequiredLang))
			foreach ($this->fieldsRequiredLang as $field)
			{
				if (!isset($resourceParameters['fields'][$field]))
					$resourceParameters['fields'][$field] = array();
				$resourceParameters['fields'][$field] = array_merge(
					$resourceParameters['fields'][$field],
					$resourceParameters['fields'][$field] = array('sqlId' => $field, 'required' => true, 'i18n' => true)
				);
			}

		if (isset($this->date_add))
			$resourceParameters['fields']['date_add']['setter'] = false;
		if (isset($this->date_upd))
			$resourceParameters['fields']['date_upd']['setter'] = false;

		foreach ($resourceParameters['fields'] as $key => &$resourceParametersField)
			if (!isset($resourceParametersField['sqlId']))
				$resourceParametersField['sqlId'] = $key;
		return $resourceParameters;
	}

	public function getWebserviceObjectList($sql_join, $sql_filter, $sql_sort, $sql_limit)
	{
		$query = '
		SELECT DISTINCT main.`'.$this->identifier.'` FROM `'._DB_PREFIX_.$this->table.'` AS main
		'.$sql_join.'
		WHERE 1 '.$sql_filter.'
		'.($sql_sort != '' ? $sql_sort : '').'
		'.($sql_limit != '' ? $sql_limit : '').'
		';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
	}

	public function getFieldsRequiredDatabase($all = false)
	{
		return Db::getInstance()->ExecuteS('
		SELECT id_required_field, object_name, field_name
		FROM '._DB_PREFIX_.'required_field
		'.(!$all ? 'WHERE object_name = \''.pSQL(get_class($this)).'\'' : ''));
	}

	public function addFieldsRequiredDatabase($fields)
	{
		if (!is_array($fields))
			return false;

		if (!Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'required_field WHERE object_name = \''.pSQL(get_class($this)).'\''))
			return false;

		foreach ($fields AS $field)
			if (!Db::getInstance()->AutoExecute(_DB_PREFIX_.'required_field', array('object_name' => pSQL(get_class($this)), 'field_name' => pSQL($field)), 'INSERT'))
				return false;
		return true;
	}

	public function clearCache($all = false)
	{
		if ($all && isset(self::$_cache[$this->table]))
			unset(self::$_cache[$this->table]);
		elseif ($this->id && isset(self::$_cache[$this->table][(int)$this->id]))
			unset(self::$_cache[$this->table][(int)$this->id]);
	}

	/**
	 * Delete images associated with the object
	 *
	 * @return bool success
	 */
	public function deleteImage()
	{
		if (!$this->id)
			return false;

		/* Deleting object images and thumbnails (cache) */
		if ($this->image_dir)
		{
			if (file_exists($this->image_dir.$this->id.'.'.$this->image_format)
				&& !unlink($this->image_dir.$this->id.'.'.$this->image_format))
				return false;
		}
		if (file_exists(_PS_TMP_IMG_DIR_.$this->table.'_'.$this->id.'.'.$this->image_format)
			&& !unlink(_PS_TMP_IMG_DIR_.$this->table.'_'.$this->id.'.'.$this->image_format))
			return false;
		if (file_exists(_PS_TMP_IMG_DIR_.$this->table.'_mini_'.$this->id.'.'.$this->image_format)
			&& !unlink(_PS_TMP_IMG_DIR_.$this->table.'_mini_'.$this->id.'.'.$this->image_format))
			return false;

		$types = ImageType::getImagesTypes();
		foreach ($types as $image_type)
			if (file_exists($this->image_dir.$this->id.'-'.stripslashes($image_type['name']).'.'.$this->image_format)
			&& !unlink($this->image_dir.$this->id.'-'.stripslashes($image_type['name']).'.'.$this->image_format))
				return false;
		return true;
	}

	/**
	* Specify if an ObjectModel is already in database
	*
	* @param $id_entity Entity id
	* @param $table Object type
	* @return boolean
	*/
	public static function existsInDatabase($id_entity, $table)
	{
		$field = $table == 'orders' ? 'order' : $table;

		return (bool)Db::getInstance()->getValue('
		SELECT `id_'.pSQL($field).'` id
		FROM `'._DB_PREFIX_.pSQL($table).'` e
		WHERE e.`id_'.pSQL($field).'` = '.(int)$id_entity);
	}
}
