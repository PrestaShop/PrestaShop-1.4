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

class ConfigurationCore extends ObjectModel
{
	public 		$id;

	/** @var string Key */
	public 		$name;

	/** @var mixed Value */
	public 		$value;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected	$fieldsRequired = array('name');
	protected	$fieldsSize = array('name' => 32);
	protected	$fieldsValidate = array('name' => 'isConfigName');

	protected	$table = 'configuration';
	protected 	$identifier = 'id_configuration';

	/** @var array Configuration cache */
	protected static $_CONF;
	/** @var array Configuration multilang cache */
	protected static $_CONF_LANG;
	/** @var array Configuration IDs cache */
	protected static $_CONF_IDS;

	protected $webserviceParameters = array(
		'fields' => array(
			'value' => array(),
		)
	);

	public function getFields()
	{
		parent::validateFields();
		$fields['name'] = pSQL($this->name);
		$fields['value'] = pSQL($this->value);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}

	/**
	  * Check then return multilingual fields for database interaction
	  *
	  * @return array Multilingual fields
	  */
	public function getTranslationsFieldsChild()
	{
		if (!is_array($this->value))
			return true;
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('value'));
	}

	/**
	  * Delete a configuration key in database (with or without language management)
	  *
	  * @param string $key Key to delete
	  * @return boolean Deletion result
	  */
	public static function deleteByName($key)
	{
	 	if (!Validate::isConfigName($key))
			return false;

		/* Delete the key from the main configuration table */
		if (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `id_configuration` = '.(int)self::$_CONF_IDS[$key].' LIMIT 1'))
			unset(self::$_CONF[$key]);
		else
			return false;

		/* Determine if the key is present in the multi-lingual table */
		$is_multilingual = false;
		foreach (self::$_CONF_LANG as $id_lang => $values)
			if (isset(self::$_CONF_LANG[(int)$id_lang][$key]))
			{
				unset(self::$_CONF_LANG[(int)$id_lang][$key]);
				$is_multilingual |= true;
			}

		if ($is_multilingual && !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration_lang` WHERE `id_configuration` = '.(int)self::$_CONF_IDS[$key]))
			return false;

		unset(self::$_CONF_IDS[$key]);

		return true;
	}

	/**
	  * Get a single configuration value (in one language only)
	  *
	  * @param string $key Key wanted
	  * @param integer $id_lang Language ID
	  * @return string Value
	  */
	public static function get($key, $id_lang = NULL)
	{
		if ($id_lang && isset(self::$_CONF_LANG[(int)$id_lang][$key]))
			return self::$_CONF_LANG[(int)$id_lang][$key];
		elseif (isset(self::$_CONF[$key]))
			return self::$_CONF[$key];
		return false;
	}

	/**
	  * Set TEMPORARY a single configuration value
	  *
	  * @param string $key Key wanted
	  * @param mixed $values $values is an array if the configuration is multilingual, a single string else.
	  */
	public static function set($key, $values)
	{
		if (!Validate::isConfigName($key))
	 		die(Tools::displayError());
	 	/* Update classic values */
		if (!is_array($values))
		{
			self::$_CONF[$key] = $values;
			self::$_CONF_IDS[$key] = Db::getInstance()->getValue('SELECT `id_configuraton` FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \''.pSQL($key).'\'');
		}
		/* Update multilingual values */
		else
			/* Add multilingual values */
			foreach ($values as $k => $value)
				self::$_CONF_LANG[(int)($k)][$key] = $value;
	}

	/**
	  * Get a single configuration value (in multiple languages)
	  *
	  * @param string $key Key wanted
	  * @return array Values in multiple languages
	  */
	public static function getInt($key)
	{
		foreach (Language::getLanguages() as $language)
			$resultsArray[$language['id_lang']] = self::get($key, $language['id_lang']);
		return isset($resultsArray) ? $resultsArray : array();
	}

	/**
	  * Get several configuration values (in one language only)
	  *
	  * @param array $keys Keys wanted
	  * @param integer $id_lang Language ID
	  * @return array Values
	  */
	public static function getMultiple($keys, $id_lang = NULL)
	{
	 	if (!is_array($keys) || !is_array(self::$_CONF) || ($id_lang && !is_array(self::$_CONF_LANG)))
	 		die(Tools::displayError());

		$resTab = array();
		if (!$id_lang)
			foreach ($keys as $key)
				if (array_key_exists($key, self::$_CONF))
					$resTab[$key] = self::$_CONF[$key];
		elseif (array_key_exists($id_lang, self::$_CONF_LANG))
			foreach ($keys as $key)
				if (array_key_exists($key, self::$_CONF_LANG[(int)$id_lang]))
					$resTab[$key] = self::$_CONF_LANG[(int)($id_lang)][$key];
		return $resTab;
	}

	/**
	  * Get several configuration values (in multiple languages)
	  *
	  * @param array $keys Keys wanted
	  * @return array Values in multiple languages
	  * @deprecated
	  */
	public static function getMultipleInt($keys)
	{
		Tools::displayAsDeprecated();
		foreach (Language::getLanguages() as $language)
			$resultsArray[$language['id_lang']] = self::getMultiple($keys, $language['id_lang']);
		return isset($resultsArray) ? $resultsArray : array();
	}

	/**
	  * Insert configuration key and value into database
	  *
	  * @param string $key Key
	  * @param string $value Value
	  * @eturn boolean Insert result
	  */
	protected static function _addConfiguration($key, $value = null)
	{
		$newConfig = new Configuration();
		$newConfig->name = $key;
		if (!is_null($value))
			$newConfig->value = $value;
		return $newConfig->add() ? (int)$newConfig->id : false;
	}

	/**
	  * Update configuration key and value into database (automatically insert if key does not exist)
	  *
	  * @param string $key Key
	  * @param mixed $values $values is an array if the configuration is multilingual, a single string else.
	  * @param boolean $html Specify if html is authorized in value
		*
	  * @return boolean Update result
	  */
	public static function updateValue($key, $values, $html = false)
	{
		if ($key == null)
			return;

		if (!Validate::isConfigName($key))
	 		die(Tools::displayError());

		$db = Db::getInstance();
		$current_value = Configuration::get($key);

		/* Update classic values */
		if (!is_array($values))
		{
			/* If the current value exists but the _CONF_IDS[$key] does not, it mean the value has been set but not save, we need to add */
		 	if ($current_value !== false && isset(self::$_CONF_IDS[$key]) && self::$_CONF_IDS[$key])
		 	{
		 		$values = pSQL($values, $html);

				/* Do not update the database if the current value is the same one than the new one */
				if ($values == $current_value)
					$result = true;
				else
				{
					$result = $db->AutoExecute(_DB_PREFIX_.'configuration', array('value' => $values, 'date_upd' => date('Y-m-d H:i:s')),
					'UPDATE', '`id_configuration` = \''.(int)self::$_CONF_IDS[$key].'\'', true, true);
					if ($result)
						self::$_CONF[$key] = stripslashes($values);
				}
			}
			else
			{
				$result = self::_addConfiguration($key, $values);
				if ($result)
				{
					self::$_CONF[$key] = stripslashes($values);
					self::$_CONF_IDS[$key] = (int)$result;
				}
			}
		}

		/* Update multilingual values */
		else
		{
			$result = true;

			/* Add the key in the configuration table if it does not already exist... */
			$id_configuration = $current_value === false || !isset(self::$_CONF_IDS[$key]) || !self::$_CONF_IDS[$key] ? self::_addConfiguration($key) : (int)self::$_CONF_IDS[$key];

			$to_insert = '';
			$current_date = date('Y-m-d H:i:s');
			$update_main_key = false;
			foreach ($values as $id_lang => $value)
			{
				$value = pSQL($value, $html);
				if (!isset(self::$_CONF_LANG[(int)$id_lang][$key]))
					$to_insert .= '('.(int)$id_configuration.', '.(int)$id_lang.', \''.$value.'\', NOW()),';
				elseif (isset(self::$_CONF_LANG[(int)$id_lang][$key]) && self::$_CONF_LANG[(int)$id_lang][$key] != $value)
				{
					$update_main_key |= true;
					$result &= $db->AutoExecute(_DB_PREFIX_.'configuration_lang', array('value' => $value, 'date_upd' => $current_date),
					'UPDATE', 'id_configuration = '.(int)$id_configuration.' AND id_lang = '.(int)$id_lang, true, true);
				}
				self::$_CONF_LANG[(int)$id_lang][$key] = stripslashes($value);
			}
			if ($to_insert != '')
			{
				$result &= $db->Execute('INSERT INTO `'._DB_PREFIX_.'configuration_lang` (`id_configuration`, `id_lang`, `value`, `date_upd`) VALUES '.rtrim($to_insert, ','));
				$update_main_key |= true;
			}

			/* Update the date_upd in the main configuration table too */
			if ($result && $update_main_key)
				$result &= $db->AutoExecute(_DB_PREFIX_.'configuration', array('date_upd' => $current_date), 'UPDATE', 'id_configuration = '.(int)$id_configuration, true, true);
		}
		return (bool)$result;
	}

	public static function loadConfiguration()
	{
		self::$_CONF = array();
		self::$_CONF_LANG = array();
		self::$_CONF_IDS = array();

		$db = Db::getInstance();
		$result = $db->ExecuteS('
		SELECT c.`id_configuration`, c.`name`, cl.`id_lang`, cl.`value` cl_value, c.`value` c_value
		FROM `'._DB_PREFIX_.'configuration` c
		LEFT JOIN `'._DB_PREFIX_.'configuration_lang` cl ON (c.id_configuration = cl.id_configuration)', false);

		if ($result)
			while ($row = $db->nextRow($result))
			{
				self::$_CONF_IDS[$row['name']] = (int)$row['id_configuration'];
				self::$_CONF[$row['name']] = $row['c_value'];
				if ($row['id_lang'])
					self::$_CONF_LANG[(int)$row['id_lang']][$row['name']] = $row['cl_value'];
			}
	}

	/**
	 * This method is override to allow TranslatedConfiguration entity
	 *
	 * @param $sql_join
	 * @param $sql_filter
	 * @param $sql_sort
	 * @param $sql_limit
	 * @return array
	 */
	public function getWebserviceObjectList($sql_join, $sql_filter, $sql_sort, $sql_limit)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT DISTINCT main.`'.$this->identifier.'` FROM `'._DB_PREFIX_.$this->table.'` main
		'.$sql_join.'
		WHERE id_configuration NOT IN
		(	SELECT id_configuration
			FROM '._DB_PREFIX_.$this->table.'_lang
		) '.$sql_filter.'
		'.($sql_sort != '' ? $sql_sort : '').'
		'.($sql_limit != '' ? $sql_limit : '').'
		');
	}
}
