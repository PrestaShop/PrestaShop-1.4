<?php
/*
* 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class LanguageCore extends ObjectModel
{
	public 		$id;
	
	/** @var string Name */
	public 		$name;
	
	/** @var string 2-letter iso code */
	public 		$iso_code;
	
	/** @var string 5-letter iso code */
	public 		$language_code;
	
	/** @var boolean Status */
	public 		$active = true;
	
	protected 	$fieldsRequired = array('name', 'iso_code');
	protected 	$fieldsSize = array('name' => 32, 'iso_code' => 2, 'language_code' => 5);
	protected 	$fieldsValidate = array('name' => 'isGenericName', 'iso_code' => 'isLanguageIsoCode', 'language_code' => 'isLanguageCode', 'active' => 'isBool');
	
	protected 	$table = 'lang';
	protected 	$identifier = 'id_lang';
	
	/** @var array Languages cache */
	private static $_checkedLangs;
	private static $_LANGUAGES;
	
	protected	$webserviceParameters = array(
	);
	
	public	function __construct($id = NULL, $id_lang = NULL)
	{
		parent::__construct($id);
	}

	public function getFields()
	{
		parent::validateFields();
		$fields['name'] = pSQL($this->name);
		$fields['iso_code'] = pSQL(strtolower($this->iso_code));
		$fields['language_code'] = pSQL(strtolower($this->language_code));
		if (empty($fields['language_code']))
			$fields['language_code'] = $fields['iso_code'];
		$fields['active'] = (int)($this->active);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		if (!parent::add($autodate))
			return false;
		
		$translationsFiles = array(
			'fields' => '_FIELDS',
			'errors' => '_ERRORS',
			'admin' => '_LANGADM',
			'pdf' => '_LANGPDF',
		);
		if (!file_exists(_PS_TRANSLATIONS_DIR_.$this->iso_code))
			mkdir(_PS_TRANSLATIONS_DIR_.$this->iso_code);
		foreach ($translationsFiles as $file => $var)
			if (!file_exists(_PS_TRANSLATIONS_DIR_.$this->iso_code.'/'.$file.'.php'))
				file_put_contents(_PS_TRANSLATIONS_DIR_.$this->iso_code.'/'.$file.'.php', '<?php
	global $'.$var.';
	$'.$var.' = array();
?>');
		
		return ($this->loadUpdateSQL() AND Tools::generateHtaccess(dirname(__FILE__).'/../.htaccess',
			(int)(Configuration::get('PS_REWRITING_SETTINGS')),		
			(int)(Configuration::get('PS_HTACCESS_CACHE_CONTROL')), 
			Configuration::get('PS_HTACCESS_SPECIFIC')
		));
	}

	public function toggleStatus()
	{
		if (!parent::toggleStatus())
			return false;
			
		return (Tools::generateHtaccess(dirname(__FILE__).'/../.htaccess',
			(int)(Configuration::get('PS_REWRITING_SETTINGS')),		
			(int)(Configuration::get('PS_HTACCESS_CACHE_CONTROL')), 
			Configuration::get('PS_HTACCESS_SPECIFIC')
		));
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
	
	static public function getFilesList($iso_from, $theme_from, $iso_to = false, $theme_to = false, $select = false, $check = false, $modules = false)
	{
		if (empty($iso_from))
			die(Tools::displayError());
		
		$copy = ($iso_to AND $theme_to) ? true : false;
		
		$lPath_from = _PS_TRANSLATIONS_DIR_.(string)$iso_from.'/';
		$tPath_from = _PS_ROOT_DIR_.'/themes/'.(string)$theme_from.'/';
		$mPath_from = _PS_MAIL_DIR_.(string)$iso_from.'/';
		
		if ($copy)
		{
			$lPath_to = _PS_TRANSLATIONS_DIR_.(string)$iso_to.'/';
			$tPath_to = _PS_ROOT_DIR_.'/themes/'.(string)$theme_to.'/';
			$mPath_to = _PS_MAIL_DIR_.(string)$iso_to.'/';
		}
		
		$lFiles = array('admin'.'.php', 'errors'.'.php', 'fields'.'.php', 'pdf'.'.php');
		$mFiles =  array(
			'account.html',					'account.txt',
			'bankwire.html',				'bankwire.txt',
			'cheque.html',					'cheque.txt',
			'contact.html',					'contact.txt',
			'contact_form.html',			'contact_form.txt',
			'credit_slip.html',				'credit_slip.txt',
			'download_product.html',		'download_product.txt',
			'download-product.tpl',
			'employee_password.html',		'employee_password.txt',
			'forward_msg.html',				'forward_msg.txt',
			'guest_to_customer.html',		'guest_to_customer.txt',
			'in_transit.html',				'in_transit.txt',
			'newsletter.html',				'newsletter.txt',
			'order_canceled.html',			'order_canceled.txt',
			'order_conf.html',				'order_conf.txt',
			'order_customer_comment.html',	'order_customer_comment.txt',
			'order_merchant_comment.html',	'order_merchant_comment.txt',
			'order_return_state.html',		'order_return_state.txt',
			'outofstock.html',				'outofstock.txt',
			'password.html',				'password.txt',
			'password_query.html',			'password_query.txt',
			'payment.html',					'payment.txt',
			'payment_error.html',			'payment_error.txt',
			'preparation.html',				'preparation.txt',
			'refund.html',					'refund.txt',
			'reply_msg.html',				'reply_msg.txt',
			'shipped.html',					'shipped.txt',
			'test.html',					'test.txt',
			'voucher.html',					'voucher.txt',
		);

		$number = -1;
		
		$files = array();
		$files_tr = array();
		$files_theme = array();
		$files_mail = array();
		$files_modules = array();
		
		
		// When a copy is made from a theme in specific language 
		// to an other theme for the same language,
		// it's avoid to copy Translations, Mails files
		// and modules files which are not override by theme.
		if (!$copy OR $iso_from != $iso_to)
		{
			// Translations files
			if (!$check OR ($check AND (string)$iso_from != 'en'))
				foreach ($lFiles as $file)
					$files_tr[$lPath_from.$file] = ($copy ? $lPath_to.$file : ++$number);
			if ($select == 'tr')
				return $files_tr;
			$files = array_merge($files, $files_tr);
			
			// Mail files
			if (!$check OR ($check AND (string)$iso_from != 'en'))
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
					$modDir = _PS_MODULE_DIR_.$mod;
					// Lang file
					if (file_exists($modDir.'/'.(string)$iso_from.'.php'))
						$files_modules[$modDir.'/'.(string)$iso_from.'.php'] = ($copy ? $modDir.'/'.(string)$iso_to.'.php' : ++$number);
					// Mails files
					$modMailDirFrom = $modDir.'/mails/'.(string)$iso_from;
					$modMailDirTo = $modDir.'/mails/'.(string)$iso_to;
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
		}
		else if ($select == 'mail' OR $select == 'tr')
		{
			return $files;
		}
		
		// Theme files
		if (!$check OR ($check AND (string)$iso_from != 'en'))
		{
			$files_theme[$tPath_from.'lang/'.(string)$iso_from.'.php'] = ($copy ? $tPath_to.'lang/'.(string)$iso_to.'.php' : ++$number);
			$module_theme_files = (file_exists($tPath_from.'modules/') ? scandir($tPath_from.'modules/') : array());
			foreach ($module_theme_files as $module)
				if ($module !== '.' AND $module != '..' AND $module !== '.svn' AND file_exists($tPath_from.'modules/'.$module.'/'.(string)$iso_from.'.php'))
					$files_theme[$tPath_from.'modules/'.$module.'/'.(string)$iso_from.'.php'] = ($copy ? $tPath_to.'modules/'.$module.'/'.(string)$iso_to.'.php' : ++$number);
		}
		if ($select == 'theme')
			return $files_theme;
		$files = array_merge($files, $files_theme);
		
		// Return
		return $files;
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
	
	public static function recurseDeleteDir($dir)
	{
		if (!is_dir($dir))
			return false;
		if ($handle = @opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
				if ($file != '.' && $file != '..')
				{
					if (is_dir($dir.'/'.$file))
						self::recurseDeleteDir($dir.'/'.$file);
					elseif (file_exists($dir.'/'.$file))
						@unlink($dir.'/'.$file);
				}
			closedir($handle);
		}
		rmdir($dir);
	}
	
	public function delete()
	{
		if (empty($this->iso_code)) 
			$this->iso_code = self::getIsoById($this->id);		
		
		/* Database translations deletion */
		$result = Db::getInstance()->ExecuteS('SHOW TABLES FROM `'._DB_NAME_.'`');
		foreach ($result AS $row)
			if (preg_match('/_lang/', $row['Tables_in_'._DB_NAME_]))
				if (!Db::getInstance()->Execute('DELETE FROM `'.$row['Tables_in_'._DB_NAME_].'` WHERE `id_lang` = '.(int)($this->id)))
					return false;
					
		/* Delete tags */
		Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'tag WHERE id_lang = '.(int)($this->id));
		
		/* Delete search words */
		Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'search_word WHERE id_lang = '.(int)($this->id));

		/* Files deletion */
		foreach (self::getFilesList($this->iso_code, _THEME_NAME_, false, false, false, true, true) as $key => $file)
			unlink($key);
		$modList = Module::getModulesDirOnDisk();
		foreach ($modList as $k => $mod)
			self::recurseDeleteDir(_PS_MODULE_DIR_.$mod.'/mails/'.$this->iso_code);
		if (file_exists(_PS_MAIL_DIR_.$this->iso_code))
			self::recurseDeleteDir(_PS_MAIL_DIR_.$this->iso_code);
		if (file_exists(_PS_TRANSLATIONS_DIR_.$this->iso_code))
			self::recurseDeleteDir(_PS_TRANSLATIONS_DIR_.$this->iso_code);
		if (!parent::delete())
			return false;
		
		return Tools::generateHtaccess(dirname(__FILE__).'/../.htaccess',
									(int)(Configuration::get('PS_REWRITING_SETTINGS')),		
									(int)(Configuration::get('PS_HTACCESS_CACHE_CONTROL')), 
									Configuration::get('PS_HTACCESS_SPECIFIC')
								);		
	}
	
	
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
		
		Tools::generateHtaccess(dirname(__FILE__).'/../.htaccess',
								(int)(Configuration::get('PS_REWRITING_SETTINGS')),		
								(int)(Configuration::get('PS_HTACCESS_CACHE_CONTROL')), 
								Configuration::get('PS_HTACCESS_SPECIFIC')
							);	
		
		return $result;
	}
	
	/**
	  * Return available languages
	  *
	  * @param boolean $active Select only active languages
	  * @return array Languages
	  */
	static public function getLanguages($active = true)
	{
		$languages = array();
		foreach (self::$_LANGUAGES AS $language)
		{
			if ($active AND !$language['active'])
				continue;
			$languages[] = $language;
		}
		return $languages;
	}

	static public function getLanguage($id_lang)
	{
		if (!array_key_exists((int)($id_lang), self::$_LANGUAGES))
			return false;
		return self::$_LANGUAGES[(int)($id_lang)];
	}

	/**
	  * Return iso code from id
	  *
	  * @param integer $id_lang Language ID
	  * @return string Iso code
	  */
	static public function getIsoById($id_lang)
	{
		if (isset(self::$_LANGUAGES[(int)($id_lang)]['iso_code']))
			return self::$_LANGUAGES[(int)($id_lang)]['iso_code'];
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

		return Db::getInstance()->getValue('SELECT `id_lang` FROM `'._DB_PREFIX_.'lang` WHERE `iso_code` = \''.pSQL(strtolower($iso_code)).'\'');
	}
	
	static public function getLanguageCodeByIso($iso_code)
	{
	 	if (!Validate::isLanguageIsoCode($iso_code))
	 		die(Tools::displayError());

		return Db::getInstance()->getValue('SELECT `language_code` FROM `'._DB_PREFIX_.'lang` WHERE `iso_code` = \''.pSQL(strtolower($iso_code)).'\'');
	}

	/**
	  * Return array (id_lang, iso_code)
	  *
	  * @param string $iso_code Iso code
	  * @return array  Language (id_lang, iso_code)
	  */
	static public function getIsoIds($active = true) 
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `id_lang`, `iso_code` FROM `'._DB_PREFIX_.'lang` '.($active ? 'WHERE active = 1' : ''));
	}
	
	static public function copyLanguageData($from, $to)
	{
		$result = Db::getInstance()->ExecuteS('SHOW TABLES FROM `'._DB_NAME_.'`');
		foreach ($result AS $row)
			if (preg_match('/_lang/', $row['Tables_in_'._DB_NAME_]) AND $row['Tables_in_'._DB_NAME_] != _DB_PREFIX_.'lang')
			{
				$result2 = Db::getInstance()->ExecuteS('SELECT * FROM `'.$row['Tables_in_'._DB_NAME_].'` WHERE `id_lang` = '.(int)($from));
				if (!sizeof($result2))
					continue;
				Db::getInstance()->Execute('DELETE FROM `'.$row['Tables_in_'._DB_NAME_].'` WHERE `id_lang` = '.(int)($to));
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
		
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_lang`, `name`, `iso_code`, `active` 
		FROM `'._DB_PREFIX_.'lang`');
		
		foreach ($result AS $row)
			self::$_LANGUAGES[(int)($row['id_lang'])] = array('id_lang' => (int)($row['id_lang']), 'name' => $row['name'], 'iso_code' => $row['iso_code'], 'active' => (int)($row['active']));
	}
		
	public function update($nullValues = false)
	{
		if (!parent::update($nullValues))
			return false;
			
		return Tools::generateHtaccess(dirname(__FILE__).'/../.htaccess',
							(int)(Configuration::get('PS_REWRITING_SETTINGS')),		
							(int)(Configuration::get('PS_HTACCESS_CACHE_CONTROL')), 
							Configuration::get('PS_HTACCESS_SPECIFIC')
							);
	}	

	static public function checkAndAddLanguage($iso_code)
	{
		if (Language::getIdByIso($iso_code))
			return true;
		else
		{
			if(@fsockopen('www.prestashop.com', 80))
			{
				$lang = new Language();
				$lang->iso_code = $iso_code;
				$lang->active = true;

				if ($lang_pack = json_decode(@file_get_contents('http://www.prestashop.com/download/lang_packs/get_language_pack.php?version='._PS_VERSION_.'&iso_lang='.$iso_code)))
				{
					if (isset($lang_pack->name)
					&& isset($lang_pack->version)
					&& isset($lang_pack->iso_code))
						$lang->name = $lang_pack->name;
				}
				if (!$lang->name OR !$lang->add())
					return false;
				$insert_id = (int)($lang->id);
				
				if ($lang_pack)
				{
					$flag = file_get_contents('http://www.prestashop.com/download/lang_packs/flags/jpeg/'.$iso_code.'.jpg');
					if ($flag != NULL && !preg_match('/<body>/', $flag))
					{
						$file = fopen(dirname(__FILE__).'/../img/l/'.$insert_id.'.jpg', 'w');
						if ($file)
						{
							fwrite($file, $flag);
							fclose($file);
						}
						else
							self::_copyNoneFlag($insert_id);
					}
					else
						self::_copyNoneFlag($insert_id);
				}
				else
					self::_copyNoneFlag($insert_id);
				
				$files_copy = array('/en.jpg', '/en-default-thickbox.jpg', '/en-default-home.jpg', '/en-default-large.jpg', '/en-default-medium.jpg', '/en-default-small.jpg', '/en-default-large_scene.jpg');
				$tos = array(_PS_CAT_IMG_DIR_, _PS_MANU_IMG_DIR_, _PS_PROD_IMG_DIR_, _PS_SUPP_IMG_DIR_);
				foreach($tos AS $to)
					foreach($files_copy AS $file)
					{
						$name = str_replace('/en', '/'.$iso_code, $file);
						copy(dirname(__FILE__).'/../img/l/'.$file, $to.$name);
					}
				return true;
			}
			else
				return false;
		}
	}

	static private function _copyNoneFlag($id)
	{
		return copy(dirname(__FILE__).'/../img/l/none.jpg', dirname(__FILE__).'/../img/l/'.$id.'.jpg');
	}
	
	static public function isInstalled($iso_code)
	{
		return Db::getInstance()->getValue('SELECT `id_lang` FROM `'._DB_PREFIX_.'lang` WHERE `iso_code` = "'.pSQL($iso_code).'"');
	}
}


