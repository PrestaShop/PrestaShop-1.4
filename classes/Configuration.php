<?php

/**
  * Configuration class, Configuration.php
  * Allow to set, get and delete configuration values in the database
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Configuration extends ObjectModel
{
	public 		$id;

	/** @var string Key */
	public 		$name;

	/** @var string Value */
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
	private static $_CONF;
	/** @var array Configuration multilang cache */
	private static $_CONF_LANG;

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
	static public function deleteByName($key)
	{
	 	if (!Validate::isConfigName($key))
	 		die(Tools::displayError());

		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration_lang` WHERE `id_configuration` =
		(SELECT `id_configuration` FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \''.pSQL($key).'\')');
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \''.pSQL($key).'\'');
	}

	/**
	  * Get a single configuration value (in one language only)
	  *
	  * @param string $key Key wanted
	  * @param integer $id_lang Language ID
	  * @return string Value
	  */
	static public function get($key, $id_lang = NULL)
	{
	 	if (!is_array(self::$_CONF) OR !is_array(self::$_CONF_LANG) OR !Validate::isConfigName($key))
	 		die(Tools::displayError());

		if ($id_lang)
		{
			if (key_exists(intval($id_lang), self::$_CONF_LANG) AND key_exists($key, self::$_CONF_LANG[intval($id_lang)]))
				return self::$_CONF_LANG[intval($id_lang)][$key];
		}
		elseif (key_exists($key, self::$_CONF))
			return self::$_CONF[$key];

		$result = Db::getInstance()->GetRow('
		SELECT IFNULL('.($id_lang ? 'cl' : 'c').'.`value`, c.`value`) AS value
		FROM `'._DB_PREFIX_.'configuration` c
		'.($id_lang ? ('LEFT JOIN `'._DB_PREFIX_.'configuration_lang` cl ON (c.`id_configuration` = cl.`id_configuration` AND cl.`id_lang` = '.intval($id_lang).')') : '').'
		WHERE `name` = \''.pSQL($key).'\'');
		
		if ($id_lang)
		{
			self::$_CONF_LANG[intval($id_lang)][$key] = ($result ? $result['value'] : false);
			return self::$_CONF_LANG[intval($id_lang)][$key];
		}
		else
		{
			self::$_CONF[$key] = ($result ? $result['value'] : false);
			return self::$_CONF[$key];
		}
	}

	/**
	  * Set TEMPORARY a single configuration value (in one language only)
	  *
	  * @param string $key Key wanted
	  * @param mixed $values $values is an array if the configuration is multilingual, a single string else.
	  * @param boolean $html Specify if html is authorized in value
	  */
	static public function set($key, $values, $html = false)
	{
		if (!Validate::isConfigName($key))
	 		die(Tools::displayError());
	 	/* Update classic values */
		if (!is_array($values))
			self::$_CONF[$key] = $values;
		/* Update multilingual values */
		else
			/* Add multilingual values */
			foreach ($values as $k => $value)
				self::$_CONF_LANG[intval($k)][$key] = $value;
	}

	/**
	  * Get a single configuration value (in multiple languages)
	  *
	  * @param string $key Key wanted
	  * @return array Values in multiple languages
	  */
	static public function getInt($key)
	{
		$languages = Language::getLanguages();
		$resultsArray = array();
		foreach($languages as $language)
			$resultsArray[$language['id_lang']] = self::get($key, $language['id_lang']);
		return $resultsArray;
	}

	/**
	  * Get several configuration values (in one language only)
	  *
	  * @param array $keys Keys wanted
	  * @param integer $id_lang Language ID
	  * @return array Values
	  */
	static public function getMultiple($keys, $id_lang = NULL)
	{
	 	if (!is_array($keys) OR !is_array(self::$_CONF) OR ($id_lang AND !is_array(self::$_CONF_LANG)))
	 		die(Tools::displayError());

		$resTab = array();
		if (!$id_lang)
		{
			foreach ($keys AS $key)
				if (key_exists($key, self::$_CONF))
					$resTab[$key] = self::$_CONF[$key];
		}
		elseif (key_exists($id_lang, self::$_CONF_LANG))
			foreach ($keys AS $key)
				if (key_exists($key, self::$_CONF_LANG[intval($id_lang)]))
					$resTab[$key] = self::$_CONF_LANG[intval($id_lang)][$key];
		return $resTab;
	}

	/**
	  * Get several configuration values (in multiple languages)
	  *
	  * @param array $keys Keys wanted
	  * @return array Values in multiple languages
	  */
	static public function getMultipleInt($keys)
	{
		$languages = Language::getLanguages();
		$resultsArray = array();
		foreach($languages as $language)
			$resultsArray[$language['id_lang']] = self::getMultiple($keys, $language['id_lang']);
		return $resultsArray;
	}

	/**
	  * Insert configuration key and value into database
	  *
	  * @param string $key Key
	  * @param string $value Value
	  * @eturn boolean Insert result
	  */
	static private function _addConfiguration($key, $value = NULL)
	{
		$newConfig = new Configuration();
		$newConfig->name = $key;
		if (!is_null($value))
			$newConfig->value = $value;
		return $newConfig->add();
	}

	/**
	  * Update configuration key and value into database (automatically insert if key does not exist)
	  *
	  * @param string $key Key
	  * @param mixed $values $values is an array if the configuration is multilingual, a single string else.
	  * @param boolean $html Specify if html is authorized in value
	  * @eturn boolean Update result
	  */
	static public function updateValue($key, $values, $html = false)
	{
		if ($key == NULL) return;
		if (!Validate::isConfigName($key))
	 		die(Tools::displayError());
		$db = Db::getInstance();

		/* Update classic values */
		if (!is_array($values))
		{
		 	if (Configuration::get($key) !== false)
		 	{
				$result = $db->AutoExecute(
					_DB_PREFIX_.'configuration',
					array('value' => pSQL($values, $html), 'date_upd' => date('Y-m-d H:i:s')),
					'UPDATE', '`name` = \''.pSQL($key).'\'', true);
				self::$_CONF[$key] = $values;
			}
			else
			{
				return self::_addConfiguration($key, $values);
			}
		}

		/* Update multilingual values */
		else
		{
			$result = 1;
			/* Add the key in the configuration table if it does not already exist... */
			$conf = $db->getRow('SELECT `id_configuration` FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \''.pSQL($key).'\'');
			if (!is_array($conf) OR !array_key_exists('id_configuration', $conf))
			{
				self::_addConfiguration($key);
				$conf = $db->getRow('SELECT `id_configuration` FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \''.pSQL($key).'\'');
			}
			/* ... then add multilingual values into configuration_lang table */
			if (!array_key_exists('id_configuration', $conf) OR !intval($conf['id_configuration']))
				return false;
			foreach ($values as $id_lang => $value)
			{
				$result &= $db->Execute('INSERT INTO `'._DB_PREFIX_.'configuration_lang` (`id_configuration`, `id_lang`, `value`, `date_upd`)
										VALUES ('.$conf['id_configuration'].', '.intval($id_lang).', \''.pSQL($value, $html).'\', NOW())
										ON DUPLICATE KEY UPDATE `value` = \''.pSQL($value, $html).'\', `date_upd` = NOW()');
				self::$_CONF_LANG[intval($id_lang)][$key] = $value;
			}
		}
		return $result;
	}

	static public function loadConfiguration()
	{
		/* Configuration */
		self::$_CONF = array();
		$result = Db::getInstance()->ExecuteS('SELECT `name`, `value` FROM `'._DB_PREFIX_.'configuration`');
		if ($result)
			foreach ($result AS $row)
				self::$_CONF[$row['name']] = $row['value'];

		/* Multilingual configuration */
		self::$_CONF_LANG = array();
		$result = Db::getInstance()->ExecuteS('
		SELECT c.`name`, cl.`id_lang`, IFNULL(cl.`value`, c.`value`) AS value
		FROM `'._DB_PREFIX_.'configuration_lang` cl
		LEFT JOIN `'._DB_PREFIX_.'configuration` c ON c.id_configuration = cl.id_configuration');
		if ($result === false)
			die(Tools::displayError('Invalid loadConfiguration() SQL query!'));
		foreach ($result AS $row)
			self::$_CONF_LANG[intval($row['id_lang'])][$row['name']] = $row['value'];
	}
}

?>