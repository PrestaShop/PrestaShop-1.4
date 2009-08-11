<?php

/**
  * Language class, Language.php
  * Languages management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Language extends ObjectModel
{
	public 		$id;
	
	/** @var string Name */
	public 		$name;
	
	/** @var string 2 letters iso code */
	public 		$iso_code;
	
	/** @var boolean Status */
	public 		$active = true;
	
	protected 	$fieldsRequired = array('name', 'iso_code');
	protected 	$fieldsSize = array('name' => 32, 'iso_code' => 3);
	protected 	$fieldsValidate = array('name' => 'isGenericName', 'iso_code' => 'isLanguageIsoCode', 'active' => 'isBool');
	
	protected 	$table = 'lang';
	protected 	$identifier = 'id_lang';
	
	/** @var array Languages cache */
	private static $_checkedLangs;
	private static $_LANGUAGES;
	
	public	function __construct($id_address = NULL, $id_lang = NULL)
	{
		parent::__construct($id_address);
		
		// Check if all files needed are here, if not, disabled language
		if ($this->iso_code != 'en' AND !$this->checkFiles())
			$this->active = false;
	}
	
	public function checkFiles()
	{
		return self::checkFilesWithIsoCode($this->iso_code);
	}
	
	
	static public function checkFilesWithIsoCode($iso_code)
	{
		if (isset(self::$_checkedLangs[$iso_code]) AND self::$_checkedLangs[$iso_code])
			return true;
		foreach (self::getFilesList($iso_code, _THEME_NAME_, false, false, false, true) as $key => $file)
			if (!file_exists($key))
				return false;
		self::$_checkedLangs[$iso_code] = true;
		return true;
	}
	
	static public function	getFilesList($iso_from, $theme_from, $iso_to = false, $theme_to = false, $select = false, $check = false, $modules = false)
	{
		$copy = ($iso_to AND $theme_to) ? true : false;
		
		$lPath_from = _PS_TRANSLATIONS_DIR_.strval($iso_from).'/';
		$tPath_from = _PS_ROOT_DIR_.'/themes/'.strval($theme_from).'/lang/';
		$mPath_from = _PS_MAIL_DIR_.strval($iso_from).'/';
		
		if ($copy)
		{
			$lPath_to = _PS_TRANSLATIONS_DIR_.strval($iso_to).'/';
			$tPath_to = _PS_ROOT_DIR_.'/themes/'.strval($theme_to).'/lang/';
			$mPath_to = _PS_MAIL_DIR_.strval($iso_to).'/';
		}
		
		$lFiles = array('admin'.'.php', 'errors'.'.php', 'fields'.'.php', 'pdf'.'.php');
		$mFiles =  array(
			'account.html',					'account.txt',
			'bankwire.html',				'bankwire.txt',
			'cheque.html',					'cheque.txt',
			'contact.html',					'contact.txt',
			'credit_slip.html',				'credit_slip.txt',
			'download_product.html',		'download_product.txt',
			'download-product.tpl',
			'employee_password.html',		'employee_password.txt',
			'newsletter.html',				'newsletter.txt',
			'order_canceled.html',			'order_canceled.txt',
			'order_conf.html',				'order_conf.txt',
			'order_customer_comment.html',	'order_customer_comment.txt',
			'order_return_state.html',		'order_return_state.txt',
			'outofstock.html',				'outofstock.txt',
			'password.html',				'password.txt',
			'payment.html',					'payment.txt',
			'payment_error.html',			'payment_error.txt',
			'preparation.html',				'preparation.txt',
			'refund.html',					'refund.txt',
			'shipped.html',					'shipped.txt',
			'in_transit.txt',				'in_transit.html',
			'test.html',					'test.txt',
			'voucher.html',					'voucher.txt',
		);

		$number = -1;
		
		$files = array();
		$files_tr = array();
		$files_theme = array();
		$files_mail = array();
		$files_modules = array();
		
		// Translations files
		if (!$check OR ($check AND strval($iso_from) != 'en'))
			foreach ($lFiles as $file)
				$files_tr[$lPath_from.$file] = ($copy ? $lPath_to.$file : ++$number);
		if ($select == 'tr')
			return $files_tr;
		$files = array_merge($files, $files_tr);
		
		// Theme files
		if (!$check OR ($check AND strval($iso_from) != 'en'))
			$files_theme[$tPath_from.strval($iso_from).'.php'] = ($copy ? $tPath_to.strval($iso_to).'.php' : ++$number);
		if ($select == 'theme')
			return $files_theme;
		$files = array_merge($files, $files_theme);
		
		// Mail files
		if (!$check OR ($check AND strval($iso_from) != 'en'))
			$files_mail[$mPath_from.'lang.php'] = ($copy ? $mPath_to.'lang.php' : ++$number);
		foreach ($mFiles as $file)
			$files_mail[$mPath_from.$file] = ($copy ? $mPath_to.$file : ++$number);
		if ($select == 'mail')
			return $files_mail;
		$files = array_merge($files, $files_mail);

		// Modules
		if ($modules)
		{
			$modList = Module::getModulesDirOnDisk();
			foreach ($modList as $k => $mod)
			{
				// Lang file
				$modDir = _PS_MODULE_DIR_.$mod;
				if (file_exists($modDir.'/'.strval($iso_from).'.php'))
					$files_modules[$modDir.'/'.strval($iso_from).'.php'] = ($copy ? $modDir.'/'.strval($iso_to).'.php' : ++$number);
				// Mails files
				$modMailDirFrom = $modDir.'/mails/'.strval($iso_from);
				$modMailDirTo = $modDir.'/mails/'.strval($iso_to);
				if (file_exists($modMailDirFrom))
				{
					$dirFiles = scandir($modMailDirFrom);
					foreach ($dirFiles as $file)
						if (file_exists($modMailDirFrom.'/'.$file) AND $file != '.' AND $file != '..' AND $file != '.svn')
							$files_modules[$modMailDirFrom.'/'.$file] = ($copy ? $modMailDirTo.'/'.$file : ++$number);
				}
			}
			if ($select == 'modules')
				return $files_modules;
			$files = array_merge($files, $files_modules);
		}
		
		// Return
		return $files;
	}

	public function getFields()
	{
		parent::validateFields();
		
		$fields['name'] = pSQL($this->name);
		$fields['iso_code'] = pSQL(strtolower($this->iso_code));
		$fields['active'] = intval($this->active);
		
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		if (!parent::add($autodate))
			return false;
		return $this->loadUpdateSQL();
	}
	
	public function loadUpdateSQL()
	{
		$file = _PS_TOOL_DIR_.'/languages/updateLanguages.sql';
		if (!file_exists($file))
			Tools::dieObject($file);
		if (!$sqlContent = file_get_contents($file))
			Tools::dieObject(file_get_contents($file));
		$sqlContent .= "\n";
		$sqlContent = str_replace('PREFIX_', _DB_PREFIX_, $sqlContent);
		$sqlContent = preg_split("/;\s*[\r\n]+/", $sqlContent);
		foreach ($sqlContent as $query)
		{
			$query = trim($query);
			if (!empty($query))
				if (!Db::getInstance()->Execute($query))
					Tools::dieObject($query);
		}
		return true;
	}
	
	public function delete()
	{
		// Database translations deletion
		$result = Db::getInstance()->ExecuteS('SHOW TABLES FROM `'._DB_NAME_.'`');
		foreach ($result AS $row)
			if (preg_match('/_lang/', $row['Tables_in_'._DB_NAME_]))
				if (!Db::getInstance()->Execute('DELETE FROM `'.$row['Tables_in_'._DB_NAME_].'` WHERE `id_lang` = '.intval($this->id)))
					return false;

		//Files deletion
		foreach (self::getFilesList($this->iso_code, _THEME_NAME_, false, false, false, true, true) as $key => $file)
			unlink($key);
		if (file_exists(_PS_MAIL_DIR_.$this->iso_code))
			rmdir(_PS_TRANSLATIONS_DIR_.$this->iso_code);
		return parent::delete();
	}
	
	/**
	  * Return available languages
	  *
	  * @return array Languages
	  */
	static public function getLanguages($active = true)
	{
		$languages = array();
		foreach (self::$_LANGUAGES AS $language)
			if (!$active OR ($active AND intval($language['active'])))
				$languages[] = $language;
		return $languages;
	}

	static public function getLanguage($id_lang)
	{
		if (!array_key_exists(intval($id_lang), self::$_LANGUAGES))
			return false;
		return self::$_LANGUAGES[intval($id_lang)];
	}

	/**
	  * Return iso code from id
	  *
	  * @param integer $id_lang Language ID
	  * @return string Iso code
	  */
	static public function getIsoById($id_lang)
	{
		if (isset(self::$_LANGUAGES[intval($id_lang)]['iso_code']))
			return self::$_LANGUAGES[intval($id_lang)]['iso_code'];
		return false;
	}
	
	/**
	  * Return id from iso code
	  *
	  * @param string $iso_code Iso code
	  * @return integer Language ID
	  */
	static public function getIdByIso($iso_code)
	{
	 	if (!Validate::isLanguageIsoCode($iso_code))
	 		die(Tools::displayError());

		$result = Db::getInstance()->getRow('
		SELECT `id_lang`
		FROM `'._DB_PREFIX_.'lang`
		WHERE `iso_code` = \''.pSQL(strtolower($iso_code)).'\'');
		if (isset($result['id_lang']))
			return intval($result['id_lang']);
	}

	/**
	  * Return array (id_lang, iso_code)
	  *
	  * @param string $iso_code Iso code
	  * @return array  Language (id_lang, iso_code)
	  */
	static public function getIsoIds() 
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_lang`, `iso_code`
		FROM `'._DB_PREFIX_.'lang`');

		return $result;
	}
	
	static public function copyLanguageData($from, $to)
	{
		$result = Db::getInstance()->ExecuteS('SHOW TABLES FROM `'._DB_NAME_.'`');
		foreach ($result AS $row)
			if (preg_match('/_lang/', $row['Tables_in_'._DB_NAME_]) AND $row['Tables_in_'._DB_NAME_] != _DB_PREFIX_.'lang')
			{
				$result2 = Db::getInstance()->ExecuteS('SELECT * FROM `'.$row['Tables_in_'._DB_NAME_].'` WHERE `id_lang` = '.intval($from));
				if (!sizeof($result2))
					continue;
				Db::getInstance()->Execute('DELETE FROM `'.$row['Tables_in_'._DB_NAME_].'` WHERE `id_lang` = '.intval($to));
				$query = 'INSERT INTO `'.$row['Tables_in_'._DB_NAME_].'` VALUES ';
				foreach ($result2 AS $row2)
				{
					$query .= '(';
					$row2['id_lang'] = $to;
					foreach ($row2 AS $field)
						$query .= '\''.pSQL($field, true).'\',';
					$query = rtrim($query, ',').'),';
				}
				$query = rtrim($query, ',');
				Db::getInstance()->Execute($query);
			}
		return true;
	}
	
	/**
	  * Load all languages in memory for caching
	  */
	static public function loadLanguages()
	{
		self::$_LANGUAGES = array();
		$result = Db::getInstance()->ExecuteS('SELECT `id_lang`, `name`, `iso_code`, `active` FROM `'._DB_PREFIX_.'lang`');
		if ($result === false)
			die(Tools::displayError('Invalid loadLanguage() SQL query!'));
		foreach ($result AS $row)
		{
			if (!self::checkFilesWithIsoCode($row['iso_code']))
				$row['active'] = 0;
			self::$_LANGUAGES[intval($row['id_lang'])] = array('id_lang' => intval($row['id_lang']), 'name' => $row['name'], 'iso_code' => $row['iso_code'], 'active' => intval($row['active']));
		}
	}
}

?>
