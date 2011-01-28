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

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/../tools/tar/Archive_Tar.php');
include_once(PS_ADMIN_DIR.'/../tools/pear/PEAR.php');
define ('TEXTAREA_SIZED', 70);

class AdminTranslations extends AdminTab
{
	private $total_expression = 0;
	private $all_iso_lang = array();
	private $modules_translations = array();
	const DEFAULT_THEME_NAME = 'default';
	private static $tpl_regexp = '';
	private static $php_regexp = '';
	
	public function __construct()
	{
		parent::__construct();
		self::$tpl_regexp = '/\{l s=\''._PS_TRANS_PATTERN_.'\'( mod=\'.+\')?( js=1)?\}/U';
		self::$php_regexp = '/->l\(\''._PS_TRANS_PATTERN_.'\'(, \'(.+)\')?(, (.+))?\)/U';
	}
	
	/**
	 * This method merge each arrays of modules translation in 
	 * the array of modules translations
	 * 
	 * @param boolean $is_default if true a prefix is set before each keys in global $_MODULES array
	 */
	private function getModuleTranslations($is_default = false)
	{
		global $_MODULES, $_MODULE;

		if (!isset($_MODULE) AND !isset($_MODULES))
			$_MODULES = array();
		elseif (isset($_MODULE))
		{
			if(is_array($_MODULE) AND $is_default === true)
			{
				$_NEW_MODULE = array();
				foreach($_MODULE as $key=>$value)
				{
					$_NEW_MODULE[self::DEFAULT_THEME_NAME.$key] = $value;
				}
				$_MODULE = $_NEW_MODULE;
			}
			$_MODULES = (is_array($_MODULES) AND is_array($_MODULE)) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
		}
	}
	
	/**
	 * This method is only used by AdminTranslations::submitCopyLang().
	 * 
	 * It try to create folder in new theme.
	 * 
	 * When a translation file is copied for a module, its translation key is wrong.
	 * We have to change the translation key and rewrite the file.
	 * 
	 * @param string $dest file name
	 * @return bool
	 */
	private function checkDirAndCreate($dest)
	{
		$bool = true;
		// To get only folder path
		$path = dirname($dest);
		// If folder wasn't already added
		if (!file_exists($path))
		{
			if(!mkdir($path, 0777, true))
			{
				$bool &= false;
				$this->_errors[] = $this->l('Cannot create the folder').' "'.$path.'". '.$this->l('Check directory writing permisions.');
			}
		}
		return $bool;
	}

	private function writeTranslationFile($type, $path, $mark = false, $fullmark = false)
	{
		global $currentIndex;

		if ($fd = fopen($path, 'w'))
		{
			unset($_POST['submitTranslations'.$type], $_POST['lang']);
			unset($_POST['token']);
			$toInsert = array();
			foreach($_POST AS $key => $value)
				if (!empty($value))
					$toInsert[$key] = /*htmlentities(*/$value/*, ENT_COMPAT, 'UTF-8')*/;

			$tab = ($fullmark ? Tools::strtoupper($fullmark) : 'LANG').($mark ? Tools::strtoupper($mark) : '');
			fwrite($fd, "<?php\n\nglobal \$_".$tab.";\n\$_".$tab." = array();\n");
			foreach($toInsert AS $key => $value)
				fwrite($fd, '$_'.$tab.'[\''.pSQL($key, true).'\'] = \''.pSQL($value, true).'\';'."\n");
			fwrite($fd, "\n?>");
			fclose($fd);
			Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
		}
		else
			die('Cannot write language file');
	}

	public function submitCopyLang()
	{
		global $currentIndex;

		if (!($fromLang = strval(Tools::getValue('fromLang'))) OR !($toLang = strval(Tools::getValue('toLang'))))
			$this->_errors[] = $this->l('you must select 2 languages in order to copy data from one to another');
		elseif (!($fromTheme = strval(Tools::getValue('fromTheme'))) OR !($toTheme = strval(Tools::getValue('toTheme'))))
			$this->_errors[] = $this->l('you must select 2 themes in order to copy data from one to another');
		elseif (!Language::copyLanguageData(Language::getIdByIso($fromLang), Language::getIdByIso($toLang)))
			$this->_errors[] = $this->l('an error occurred while copying data');
		elseif ($fromLang == $toLang AND $fromTheme == $toTheme)
			$this->_errors[] = $this->l('nothing to copy! (same language and theme)');
		if (sizeof($this->_errors))
			return ;

		$bool = true;
		$items = Language::getFilesList($fromLang, $fromTheme, $toLang, $toTheme, false, false, true);
		foreach ($items AS $source => $dest)
		{
			$bool &= $this->checkDirAndCreate($dest);
			$bool &= @copy($source, $dest);
			
			if (strpos($dest, 'modules') AND basename($source) === $fromLang.'.php' AND $bool !== false)
			{
				$bool &= $this->changeModulesKeyTranslation($dest, $fromTheme, $toTheme);
			}
		}
		if ($bool)
			Tools::redirectLink($currentIndex.'&conf=14&token='.$this->token);
		$this->_errors[] = $this->l('a part of the data has been copied but some language files could not be found or copied');
	}
	
	/**
	 * Change the key translation to according it to theme name.
	 * 
	 * @param string $path
	 * @param string $theme_from
	 * @param string $theme_to
	 * @return boolean
	 */
	public function changeModulesKeyTranslation ($path, $theme_from, $theme_to)
	{
		$content = file_get_contents($path);
		$arr_replace = array();
		$bool_flag = true;
		if(preg_match_all('#\$_MODULE\[\'([^\']+)\'\]#Ui', $content, $matches))
		{
			foreach ($matches[1] as $key=>$value)
			{
				$arr_replace[$value] = str_replace($theme_from, $theme_to, $value);
			}
			$content = str_replace(array_keys($arr_replace), array_values($arr_replace), $content);
			$bool_flag = (file_put_contents($path, $content) === false) ? false : true;
		}
		return $bool_flag;
	}
	public function submitExportLang()
	{
		global $currentIndex;

		$lang = strtolower(Tools::getValue('iso_code'));
		$theme = strval(Tools::getValue('theme'));
		if ($lang AND $theme)
		{
			$items = array_flip(Language::getFilesList($lang, $theme, false, false, false, false, true));
			$gz = new Archive_Tar(_PS_TRANSLATIONS_DIR_.'/export/'.$lang.'.gzip', true);
			if ($gz->createModify($items, NULL, _PS_ROOT_DIR_));
				Tools::redirect('translations/export/'.$lang.'.gzip');
			$this->_errors[] = Tools::displayError('an error occurred while creating archive');
		}
		$this->_errors[] = Tools::displayError('please choose a language and a theme');
	}
	
	public function checkAndAddMailsFiles ($iso_code, $files_list)
	{
		$mails = scandir(_PS_MAIL_DIR_.'en/');
		$mails_new_lang = array();
		foreach ($files_list as $file)
		{
			if (preg_match('#^mails\/([a-z0-9]+)\/#Ui', $file['filename'], $matches))
			{
				$slash_pos = strrpos($file['filename'], '/');
				$mails_new_lang[] = substr($file['filename'], -(strlen($file['filename'])-$slash_pos-1));
			}
		}
		$arr_mails_needed = array_diff($mails, $mails_new_lang);
		foreach ($arr_mails_needed as $mail_to_add)
		{
			if ($mail_to_add !== '.' && $mail_to_add !== '..' && $mail_to_add !== '.svn')
			{
				@copy(_PS_MAIL_DIR_.'en/'.$mail_to_add, _PS_MAIL_DIR_.$iso_code.'/'.$mail_to_add);
			}
		}
	}
	public function submitImportLang()
	{
		global $currentIndex;

		if (!isset($_FILES['file']['tmp_name']) OR !$_FILES['file']['tmp_name'])
			$this->_errors[] = Tools::displayError('no file selected');
		else
		{
			$gz = new Archive_Tar($_FILES['file']['tmp_name'], true);
			$iso_code = str_replace('.gzip', '', $_FILES['file']['name']);
			$files_list = $gz->listContent();
			if ($gz->extract(_PS_TRANSLATIONS_DIR_.'../', false))
			{
				$this->checkAndAddMailsFiles($iso_code, $files_list);
				if (Validate::isLanguageFileName($_FILES['file']['name']))
				{
					if (!Language::checkAndAddLanguage($iso_code))
						$conf = 20;
				}
				Tools::redirectAdmin($currentIndex.'&conf='.(isset($conf) ? $conf : '15').'&token='.$this->token);
			}
			$this->_errors[] = Tools::displayError('archive cannot be extracted');
		}
	}
	
	public function submitAddLang()
	{
		global $currentIndex;
		
		// $arr_import_lang[0] = iso lang
		// $arr_import_lang[1] = prestashop version
		$arr_import_lang = explode('|',Tools::getValue('params_import_language'));
		
		if (Validate::isLangIsoCode($arr_import_lang[0]))
		{
			if (@fsockopen('www.prestashop.com', 80))
			{
				if ($content = Tools::file_get_contents('http://www.prestashop.com/download/lang_packs/gzip/'.$arr_import_lang[1].'/'.$arr_import_lang[0].'.gzip'))
				{
					$file = _PS_TRANSLATIONS_DIR_.$arr_import_lang[0].'.gzip';
					if (file_put_contents($file, $content))
					{
						$gz = new Archive_Tar($file, true);
						$files_list = $gz->listContent();
						if ($gz->extract(_PS_TRANSLATIONS_DIR_.'../', false))
						{
							$this->checkAndAddMailsFiles($arr_import_lang[0], $files_list);
							if (!Language::checkAndAddLanguage($arr_import_lang[0]))
								$conf = 20;
							if (!unlink($file))
								$this->_errors[] = Tools::displayError('Cannot delete archive');
							Tools::redirectAdmin($currentIndex.'&conf='.(isset($conf) ? $conf : '15').'&token='.$this->token);
						}
						$this->_errors[] = Tools::displayError('archive cannot be extracted');
						if (!unlink($file))
							$this->_errors[] = Tools::displayError('Cannot delete archive');
					}
					else
						$this->_errors[] = Tools::displayError('Server does not have permissions for writing');
				}
				else
					$this->_errors[] = Tools::displayError('language not found');
			}
			else
				$this->_errors[] = Tools::displayError('archive cannot be downloaded from prestashop.com');
		}
		else
			$this->_errors[] = Tools::displayError('Invalid parameter');
	}
	
	/**
	 * This method check each file (tpl or php file), get its sentences to translate,
	 * compare with posted values and write in iso code translation file.
	 * 
	 * @param string $file_name
	 * @param array $files
	 * @param string $theme_name
	 * @param string $module_name
	 * @param string|boolean $dir
	 * @return void
	 */
	private function findAndWriteTranslationsIntoFile($file_name, $files, $theme_name, $module_name, $dir = false)
	{
		// These static vars allow to use file to write just one time.
		static $_cache_file = array();
		static $str_write = '';
		static $_tmp = array();
		
		// Default translations and Prestashop overriding themes are distinguish
		$is_default = $theme_name === self::DEFAULT_THEME_NAME ? true : false;
		
		// Set file_name in static var, this allow to open and wright the file just one time
		if (!isset($_cache_file[($is_default ? self::DEFAULT_THEME_NAME : $theme_name).'-'.$file_name]) )
		{
			$str_write = '';
			$_cache_file[($is_default ? self::DEFAULT_THEME_NAME : $theme_name).'-'.$file_name] = true;
			if(!file_exists($file_name))
				file_put_contents($file_name, '');
			if (!is_writable($file_name))
				die ($this->l('Cannot write the theme\'s language file ').'('.$file_name.')'.$this->l('. Please check write permissions.'));
				
			// this string is initialized one time for a file
			$str_write .= "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
			$_tmp = array();
		}
			
		if (!$dir)
			$dir = ($theme_name == self::DEFAULT_THEME_NAME ? _PS_MODULE_DIR_.$module_name.'/' : _PS_ALL_THEMES_DIR_.$theme_name.'/modules/'.$module_name.'/');
		
		foreach ($files AS $template_file)
		{
			if ((preg_match('/^(.*).tpl$/', $template_file) OR ($is_default AND preg_match('/^(.*).php$/', $template_file))) AND file_exists($tpl = $dir.$template_file))
			{
				// Get translations key
				$content = file_get_contents($tpl);
				preg_match_all(substr($template_file, -4) == '.tpl' ? self::$tpl_regexp : self::$php_regexp, $content, $matches);
				
				// Write each translation on its module file
				$template_name = substr(basename($template_file), 0, -4);
				
				foreach ($matches[1] AS $key)
				{
					$post_key = md5($module_name.'_'.($is_default ? self::DEFAULT_THEME_NAME : $theme_name).'_'.Tools::strtolower($template_name).'_'.md5($key));
					$pattern = '\'<{'.Tools::strtolower($module_name).'}'.($is_default ? 'prestashop' : $theme_name).'>'.Tools::strtolower($template_name).'_'.md5($key).'\'';
					
					if (array_key_exists($post_key, $_POST) AND !empty($_POST[$post_key]) AND !in_array($pattern, $_tmp))
					{
						$_tmp[] = $pattern;
						$str_write .= '$_MODULE['.$pattern.'] = \''.pSQL($_POST[$post_key]).'\';'."\n";
						$this->total_expression++;
					}
				}
			}
		}
		if (isset($_cache_file[($is_default ? self::DEFAULT_THEME_NAME : $theme_name).'-'.$file_name]) AND $str_write != "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n")
			file_put_contents($file_name, $str_write);
	}
	public function clearModuleFiles ($files, $type_clear = 'file', $path = '')
	{
		$arr_exclude = array('img', 'js', 'mails');
		$arr_good_ext = array('.tpl', '.php');
		foreach ($files as $key=>$file)
		{
			if ($file{0} === '.' OR in_array(substr($file, 0, strrpos($file,'.')), $this->all_iso_lang))
				unset($files[$key]);
			else if ($type_clear === 'file' AND !in_array(substr($file, strrpos($file,'.')),$arr_good_ext))
				unset($files[$key]);
			else if ($type_clear === 'directory' AND (!is_dir($path.$file) OR in_array($file, $arr_exclude)))
				unset($files[$key]);
				
		}
		return $files;
	}
	/**
	 * This method get translation for each files of a module,
	 * compare with global $_MODULES array and fill AdminTranslations::modules_translations array
	 * With key as English sentences and values as their iso code translations. 
	 *  
	 * @param array $files
	 * @param string $theme_name
	 * @param string $module_name
	 * @param string|boolean $dir
	 * @param string $iso_code
	 * @return void
	 */
	private function findAndFillTranslations($files, $theme_name, $module_name, $dir = false, $iso_code = '')
	{
		global $_MODULES;
		
		// Default translations and Prestashop overriding themes are distinguish
		$is_default = $theme_name === self::DEFAULT_THEME_NAME ? true : false;
		
		if (!$dir)
			$dir = ($theme_name === self::DEFAULT_THEME_NAME ? _PS_MODULE_DIR_.$module_name.'/' : _PS_ALL_THEMES_DIR_.$theme_name.'/modules/'.$module_name.'/');
		
		// Thank to this var similar keys are not duplicate 
		// in AndminTranslation::modules_translations array
		// see below
		$_tmp = array();
		foreach ($files AS $template_file)
		{
			if ((preg_match('/^(.*).tpl$/', $template_file) OR ($is_default AND preg_match('/^(.*).php$/', $template_file))) AND file_exists($tpl = $dir.$template_file))
			{
				// Get translations key
				$content = file_get_contents($tpl);
				preg_match_all(substr($template_file, -4) == '.tpl' ? self::$tpl_regexp : self::$php_regexp, $content, $matches);
				
				// Write each translation on its module file
				$template_name = substr(basename($template_file), 0, -4);
				
				foreach ($matches[1] AS $key)
				{
					$module_key = ($is_default ? self::DEFAULT_THEME_NAME : '').'<{'.Tools::strtolower($module_name).'}'.($is_default ? 'prestashop' : $theme_name).'>'.Tools::strtolower($template_name).'_'.md5($key);
					
					// to avoid duplicate entry
					if (!in_array($module_key, $_tmp))
					{
						$_tmp[] = $module_key;
						$this->modules_translations[($is_default ? self::DEFAULT_THEME_NAME : $theme_name)][$module_name][$template_name][$key] = key_exists($module_key, $_MODULES) ? html_entity_decode($_MODULES[$module_key], ENT_COMPAT, 'UTF-8') : '';
						$this->total_expression++;
					}
				}
			}
		}
	}

	public function postProcess()
	{
		global $currentIndex;

		if (Tools::isSubmit('submitCopyLang'))
		{
		 	if ($this->tabAccess['add'] === '1')
				$this->submitCopyLang();
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (Tools::isSubmit('submitExport'))
		{
			if ($this->tabAccess['add'] === '1')
				$this->submitExportLang();
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (Tools::isSubmit('submitImport'))
		{
		 	if ($this->tabAccess['add'] === '1')
				$this->submitImportLang();
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (Tools::isSubmit('submitAddLanguage'))
		{
			if ($this->tabAccess['add'] === '1')
				$this->submitAddLang();
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsFront'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
					die(Tools::displayError());
				$this->writeTranslationFile('Front', _PS_THEME_DIR_.'lang/'.Tools::strtolower(Tools::getValue('lang')).'.php');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsPDF'))
		{
		 	if ($this->tabAccess['edit'] === '1')
		 	{
				if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
					die(Tools::displayError());
				$this->writeTranslationFile('PDF', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/pdf.php', 'PDF');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsBack'))
		{
		 	if ($this->tabAccess['edit'] === '1')
		 	{
				if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
					die(Tools::displayError());
				$this->writeTranslationFile('Back', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/admin.php', 'ADM');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsErrors'))
		{
		 	if ($this->tabAccess['edit'] === '1')
		 	{
				if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
					die(Tools::displayError());
				$this->writeTranslationFile('Errors', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/errors.php', false, 'ERRORS');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsFields'))
		{
		 	if ($this->tabAccess['edit'] === '1')
		 	{
				if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
					die(Tools::displayError());
				$this->writeTranslationFile('Fields', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/fields.php', false, 'FIELDS');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');

		}
		elseif (Tools::isSubmit('submitTranslationsMails'))
		{
		 	if ($this->tabAccess['edit'] === '1' && ($id_lang = Language::getIdByIso(Tools::getValue('lang'))) > 0)
		 	{
		 		$content = Tools::getValue('mail');

		 		//core mails
		 		foreach($content['html'] AS $filename => $file_content)
		 		{
					$filename = str_replace('..', '', $filename);
					if (Validate::isCleanHTML($file_content))
					{
						$conn = @fopen(_PS_MAIL_DIR_.Tools::getValue('lang').'/'.$filename, 'w+');
						if ($conn)
						{
							fwrite($conn, $file_content);
							fclose($conn);
						}
					}
					else
						$this->_errors[] = Tools::displayError('HTML mails templates can\'t contain JavaScript code.');
				}
		 		foreach ($content['txt'] AS $filename => $file_content)
		 		{
					$filename = str_replace('..', '', $filename);
					$conn = @fopen(_PS_MAIL_DIR_.Tools::getValue('lang').'/'.$filename, 'w+');
					if ($conn)
					{
						fwrite($conn, $file_content);
						fclose($conn);
					}
				}

				// module mails
				foreach ($content['modules'] AS $module_dir => $versions)
		 		{
		 			if (!file_exists(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang')))
		 			{
		 				mkdir(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang'), 0777);
		 			}
		 			if (isset($versions['html']))
						foreach ($versions['html'] AS $filename => $file_content)
				 		{
							$filename = str_replace('..', '', $filename);
							if (Validate::isCleanHTML($file_content))
							{
								$conn = fopen(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang').'/'.$filename, 'w+');
								if ($conn)
								{
									fwrite($conn, $file_content);
									fclose($conn);
									@chmod(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang').'/'.$filename, 0777);
								}
							}
							else
								$this->_errors[] = Tools::displayError('HTML mails templates can\'t contain JavaScript code.');
						}
					if (isset($versions['txt']))
				 		foreach ($versions['txt'] AS $filename => $file_content)
				 		{
							$filename = str_replace('..', '', $filename);
							$conn = fopen(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang').'/'.$filename, 'w+');
							if ($conn)
							{
								fwrite($conn, $file_content);
								fclose($conn);
								@chmod(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang').'/'.$filename, 0777);
							}
						}
				}

				// themes mail
				foreach ($content['themes'] AS $theme_dir_name => $theme_dir)
				{
				if (isset($theme_dir['html']))
					foreach ($theme_dir['html'] AS $filename => $file_content)
					{
						$filename = str_replace('..', '', $filename);
						if (Validate::isCleanHTML($file_content))
						{
							$conn = @fopen(_PS_ALL_THEMES_DIR_.$theme_dir_name.'/mails/'.Tools::getValue('lang').'/'.$filename, 'w+');
							if ($conn)
							{
								fwrite($conn, $file_content);
								fclose($conn);
							}
						}
						else
							$this->_errors[] = Tools::displayError('HTML mails templates can\'t contain JavaScript code.');
					}
				if (isset($theme_dir['txt']))
					foreach ($content['txt'] AS $filename => $file_content)
					{
						$filename = str_replace('..', '', $filename);
						$conn = @fopen(_PS_MAIL_DIR_.Tools::getValue('lang').'/'.$filename, 'w+');
						if ($conn)
						{
							fwrite($conn, $file_content);
							fclose($conn);
						}
					}
				}

				// themes modules mails
				foreach ($content['themes_module'] AS $theme_dir_name => $theme_dir)
				foreach ($theme_dir AS $theme_module_dir_name => $theme_module_dir)
				{
					foreach ($theme_module_dir['html'] AS $filename => $file_content)
					{
						$filename = str_replace('..', '', $filename);
						if (Validate::isCleanHTML($file_content))
						{
							$conn = @fopen(_PS_ALL_THEMES_DIR_.$theme_dir_name.'/modules/'.$theme_module_dir_name.'/mails/'.Tools::getValue('lang').'/'.$filename, 'w+');
							if ($conn)
							{
								fwrite($conn, $file_content);
								fclose($conn);
							}
						}
						else
							$this->_errors[] = Tools::displayError('HTML mails templates can\'t contain JavaScript code.');
					}
					if (isset($theme_module_dir['txt']))
					foreach($theme_module_dir['txt'] AS $filename => $file_content)
					{
						$filename = str_replace('..', '', $filename);
						$conn = @fopen(_PS_ALL_THEMES_DIR_.$theme_dir_name.'/modules/'.$theme_module_dir_name.'/mails/'.Tools::getValue('lang').'/'.$filename, 'w+');
						if ($conn)
						{
							fwrite($conn, $file_content);
							fclose($conn);
						}
					}
				}

				// subject mail
				$subjecttab = Tools::getValue('subject');
				//Tools::d($subjecttab['mails']);
			if (isset($subjecttab))
			{
				foreach ($subjecttab AS $key => $subjecttype)
				{

					if ($key == 'mails')
					{
						if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
							die(Tools::displayError());
						$this->writeSubjectTranslationFile($subjecttype, _PS_MAIL_DIR_.Tools::strtolower(Tools::getValue('lang')).'/lang.php');
					}
					elseif ($key == 'themes')
					{
						//Tools::d($subjecttype);
						foreach ($subjecttype AS $nametheme => $subtheme)
						{
						if (!Validate::isLanguageIsoCode(Tools::strtolower(Tools::getValue('lang'))))
							die(Tools::displayError());
						$this->writeSubjectTranslationFile($subtheme, _PS_ALL_THEMES_DIR_.$nametheme.'/mails/'.Tools::strtolower(Tools::getValue('lang')).'/lang.php');
						}
					}
				}
				Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
			}
				// end subject mail

				if (count($this->_errors) == 0)
				{
					global $currentIndex;
					$iso_code = array('iso_code' => Language::getIsoById($id_lang), 'id_lang' => $id_lang);
					$iso_code['mails'] = $this->displayFormmails($iso_code['iso_code'], true);
					$sql = '
						UPDATE `ps_translation_info` SET
						`nb_mail_field` = '.((int)$iso_code['mails']['total']).',
						`nb_mail_field_filled` = '.((int)$iso_code['mails']['total']-(int)$iso_code['mails']['empty']).',
						`date_add` = NOW()
						WHERE `id_lang` = '.$iso_code['id_lang']
					;
					$this->submitExportLang($iso_code['iso_code']);
					Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
				}
			}
			else
			{
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
			}
		}
		elseif (Tools::isSubmit('submitTranslationsModules'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$array_lang_src = Language::getLanguages(false);
				foreach ($array_lang_src as $language)
				{
					$this->all_iso_lang[] = $language['iso_code'];
				}
				
				$lang = Tools::strtolower($_POST['lang']);
				if (!Validate::isLanguageIsoCode($lang))
					die(Tools::displayError());
				if (!$modules = scandir(_PS_MODULE_DIR_))
					$this->displayWarning(Tools::displayError('There are no modules in your copy of PrestaShop. Use the Modules tab to activate them or go to our Website to download additional Modules.'));
				else
				{
					$arr_find_and_write = array();
					$arr_files = $this->getAllModuleFiles($modules, _PS_MODULE_DIR_, $lang, true);
					$arr_find_and_write = array_merge($arr_find_and_write, $arr_files);
					
					if(file_exists(_PS_THEME_DIR_.'/modules/'))
					{
						$modules = scandir(_PS_THEME_DIR_.'/modules/');
						$arr_files = $this->getAllModuleFiles($modules, _PS_THEME_DIR_.'modules/', $lang);
						$arr_find_and_write = array_merge($arr_find_and_write, $arr_files);
					}
					
					foreach ($arr_find_and_write as $key=>$value)
						$this->findAndWriteTranslationsIntoFile($value['file_name'], $value['files'], $value['theme'], $value['module'], $value['dir']);
					
					Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}

	public function display()
	{
		global $currentIndex, $cookie;

		$translations = array(
						'front' => $this->l('Front Office translations'),
						'back' => $this->l('Back Office translations'),
						'errors' => $this->l('Error message translations'),
						'fields' => $this->l('Field name translations'),
						'modules' => $this->l('Module translations'),
						'pdf' => $this->l('PDF translations'),
						'mails' => $this->l('Mails translations'),
						);

		if ($type = Tools::getValue('type'))
			$this->{'displayForm'.$type}(Tools::strtolower(Tools::getValue('lang')));
		else
		{
			$languages = Language::getLanguages(false);
			echo '<fieldset><legend><img src="../img/admin/translation.gif" />'.$this->l('Modify translations').'</legend>'.
			$this->l('Here you can modify translations for all text input into PrestaShop.').'<br />'.
			$this->l('First, select a section (such as Back Office or Modules), then click the flag representing the language you want to edit.').'<br /><br />
			<form method="get" action="index.php" id="typeTranslationForm">
				<input type="hidden" name="tab" value="AdminTranslations" />
				<input type="hidden" name="lang" id="translation_lang" value="0" />
				<select name="type" style="float:left; margin-right:10px;">';
			foreach ($translations AS $key => $translation)
				echo '<option value="'.$key.'">'.$translation.'&nbsp;</option>';
			echo '</select>';
			foreach ($languages AS $language)
				echo '<a href="javascript:chooseTypeTranslation(\''.$language['iso_code'].'\')">
						<img src="'._THEME_LANG_DIR_.$language['id_lang'].'.jpg" alt="'.$language['iso_code'].'" title="'.$language['iso_code'].'" />
					</a>';
			echo '<input type="hidden" name="token" value="'.$this->token.'" /></form></fieldset>
			<br /><br /><h2>'.$this->l('Translation exchange').'</h2>';
			echo '<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend onclick="$(\'#submitAddLangContent\').slideDown(\'slow\'); $(\'#submitImportContent\').slideUp(\'slow\');" style="cursor:pointer;">
					<img src="../img/admin/import.gif" />'.$this->l('Add a language').'
				</legend>
				<div id="submitAddLangContent" style="float:left;"><p>'.$this->l('You can add a language directly from prestashop.com here').'</p>
					<div style="font-weight:bold; float:left;">'.$this->l('Language you want to add:').' ';
			// Get all iso code available
			if(@fsockopen('www.prestashop.com', 80))
			{
				$lang_packs = Tools::file_get_contents('http://www.prestashop.com/download/lang_packs/get_each_language_pack.php?version='._PS_VERSION_);
				if ($lang_packs != '' && $lang_packs = json_decode($lang_packs))
				{
					echo 	'<select id="params_import_language" name="params_import_language">';
					foreach($lang_packs AS $lang_pack)
						if (!Language::isInstalled($lang_pack->iso_code))
							echo '<option value="'.$lang_pack->iso_code.'|'.$lang_pack->version.'">'.$lang_pack->name.'</option>';
					echo 	'</select>';
				}
				else
					echo '		<p>'.$this->l('Cannot connect to prestashop.com').'</p>';
			}
			echo		'</div>
					<div style="float:left;">
						<input type="submit" value="'.$this->l('Add the language').'" name="submitAddLanguage" class="button" style="margin:0px 0px 0px 25px;" />
					</div>
				</div>
			</fieldset>
			</form><br />';
			echo '<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
				<fieldset>
					<legend onclick="$(\'#submitImportContent\').slideDown(\'slow\'); $(\'#submitAddLangContent\').slideUp(\'slow\');" style="cursor:pointer;">
						<img src="../img/admin/import.gif" />'.$this->l('Import a language pack manually').'
					</legend>
					<div id="submitImportContent" style="display:none;">
						<p>'.$this->l('Import data from file (language pack).').'<br />'.
						$this->l('If the name format is: isocode.gzip (e.g. fr.gzip) and the language corresponding to this package does not exist, it will automatically be created.').'<br />'.
						$this->l('Be careful, as it will replace all existing data for the destination language!').'<br />'.
						$this->l('Browse your computer for the language file to be imported:').'</p>
						<div style="float:left;">
							<p>
								<div style="width:75px; font-weight:bold; float:left;">'.$this->l('From:').'</div>
								<input type="file" name="file" />
							</p>
						</div>
						<div style="float:left;">
							<input type="submit" value="'.$this->l('Import').'" name="submitImport" class="button" style="margin:10px 0px 0px 25px;" />
						</div>
					</div>
				</fieldset>
			</form>
			<br /><br />
			<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
				<fieldset><legend><img src="../img/admin/export.gif" />'.$this->l('Export a language').'</legend>
					<p>'.$this->l('Export data from one language to a file (language pack).').'<br />'.
					$this->l('Choose the theme from which you want to export translations.').'<br />
					<select name="iso_code" style="margin-top:10px;">';
				foreach ($languages AS $language)
					echo '<option value="'.$language['iso_code'].'">'.$language['name'].'</option>';
				echo '
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="theme" style="margin-top:10px;">';
				$themes = self::getThemesList();
				foreach ($themes AS $theme)
					echo '<option value="'.$theme['name'].'">'.$theme['name'].'</option>';
				echo '
					</select>&nbsp;&nbsp;
					<input type="submit" class="button" name="submitExport" value="'.$this->l('Export').'" />
				</fieldset>
			</form>
			<br /><br />';
			$allLanguages = Language::getLanguages(false);
			echo '
			<form action="'.$currentIndex.'&token='.$this->token.'" method="post">
				<fieldset><legend><img src="../img/admin/copy_files.gif" />'.$this->l('Copy').'</legend>
					<p>'.$this->l('Copies data from one language to another.').'<br />'.
					$this->l('Be careful, as it will replace all existing data for the destination language!').'<br />'.
					$this->l('If necessary').', <b><a href="index.php?tab=AdminLanguages&addlang&token='.Tools::getAdminToken('AdminLanguages'.(int)(Tab::getIdFromClassName('AdminLanguages')).(int)($cookie->id_employee)).'">'.$this->l('first create a new language').'</a></b>.</p>
					<div style="float:left;">
						<p>
							<div style="width:75px; font-weight:bold; float:left;">'.$this->l('From:').'</div>
							<select name="fromLang">';
					foreach	($languages AS $language)
						echo '<option value="'.$language['iso_code'].'">'.$language['name'].'</option>';
					echo '
							</select>
							&nbsp;&nbsp;&nbsp;
							<select name="fromTheme">';
						$themes = self::getThemesList();
						foreach ($themes AS $theme)
							echo '<option value="'.$theme['name'].'">'.$theme['name'].'</option>';
						echo '
							</select> <span style="font-style: bold; color: red;">*</span>
						</p>
						<p>
							<div style="width:75px; font-weight:bold; float:left;">'.$this->l('To:').'</div>
							<select name="toLang">';
					foreach	($allLanguages AS $language)
						echo '<option value="'.$language['iso_code'].'">'.$language['name'].'</option>';
					echo '
							</select>
							&nbsp;&nbsp;&nbsp;
							<select name="toTheme">';
						$themes = self::getThemesList();
						foreach ($themes AS $theme)
							echo '<option value="'.$theme['name'].'">'.$theme['name'].'</option>';
						echo '
							</select>
						</p>
					</div>
					<div style="float:left;">
						<input type="submit" value="'.$this->l('   Copy   ').'" name="submitCopyLang" class="button" style="margin:25px 0px 0px 25px;" />
					</div>
					<p style="clear: left; padding: 16px 0px 0px 0px;"><span style="font-style: bold; color: red;">*</span> '.$this->l('Language files (as indicated at Tools >> Languages >> Edition) must be complete to allow copying of translations').'</p>
				</fieldset>
			</form>';
		}
	}

	public function fileExists($dir, $file, $var)
	{
		${$var} = array();
		if (!file_exists($dir))
			if (!mkdir($dir, 0700))
				die('Please create the directory '.$dir);
		if (!file_exists($dir.'/'.$file))
			if (!file_put_contents($dir.'/'.$file, "<?php\n\nglobal \$".$var.";\n\$".$var." = array();\n\n?>"))
				die('Please create a "'.$file.'" file in '.$dir);
		if (!is_writable($dir.'/'.$file))
			$this->displayWarning(Tools::displayError('This file has to be writable:').' '.$dir.'/'.$file);
		include($dir.'/'.$file);
		return ${$var};
	}

	public function displayToggleButton($closed = false)
	{
		echo '
		<script type="text/javascript">';
		if (Tools::getValue('type') == 'mails')
			echo '$(document).ready(function(){
				openCloseAllDiv(\''.$_GET['type'].'_div\', this.value == openAll); toggleElemValue(this.id, openAll, closeAll);
				});';
		echo '
			var openAll = \''.html_entity_decode($this->l('Expand all fieldsets'), ENT_NOQUOTES, 'UTF-8').'\';
			var closeAll = \''.html_entity_decode($this->l('Close all fieldsets'), ENT_NOQUOTES, 'UTF-8').'\';
		</script>
		<input type="button" class="button" id="buttonall" onclick="openCloseAllDiv(\''.$_GET['type'].'_div\', this.value == openAll); toggleElemValue(this.id, openAll, closeAll);" />
		<script type="text/javascript">toggleElemValue(\'buttonall\', '.($closed ? 'openAll' : 'closeAll').', '.($closed ? 'closeAll' : 'openAll').');</script>';
	}
	
	public function displayAutoTranslate()
	{
		$languageCode = Tools::htmlentitiesUTF8(Language::getLanguageCodeByIso(Tools::getValue('lang')));
		echo '
		<input type="button" class="button" onclick="translateAll();" value="'.$this->l('Translate with Google').'" />
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			var displayOnce = 0;
			google.load("language", "1");
			function translateAll() {
				if (!google.language.isTranslatable("'.$languageCode.'"))
					alert(\'"'.$languageCode.'" : '.addslashes($this->l('this language is not available on Google Translate API')).'\');
				else
				{
					$.each($(\'input[type="text"]\'), function() {
						var tdinput = $(this);
						if (tdinput.attr("value") == "" && tdinput.parent("td").prev().html()) {
							google.language.translate(tdinput.parent("td").prev().html(), "en", "'.$languageCode.'", function(result) {
								if (!result.error)
									tdinput.val(result.translation);
								else if (displayOnce == 0)
								{
									displayOnce = 1;
									alert(result.error.message);
								}
							});
						}
					});
					$.each($("textarea"), function() {
						var tdtextarea = $(this);
						if (tdtextarea.html() == "" && tdtextarea.parent("td").prev().html()) {
							google.language.translate(tdtextarea.parent("td").prev().html(), "en", "'.$languageCode.'", function(result) {
								if (!result.error)
									tdtextarea.html(result.translation);
								else if (displayOnce == 0)
								{
									displayOnce = 1;
									alert(result.error.message);
								}
							});
						}
					});
				}
			}
		</script>';
	}


	public function displayLimitPostWarning($count)
	{
		if (ini_get('suhosin.post.max_vars') AND ini_get('suhosin.post.max_vars') < $count) 
		{ 
			ini_set('suhosin.post.max_vars', $count + 100); 
			if (ini_get('suhosin.post.max_vars') < $count) 
				echo '<div class="warning">'.$this->l('Warning, your hosting provider is using the suhosin patch for PHP, and is limiting the maximum fields which could be posted to') 
				.' <b>'.ini_get('suhosin.post.max_vars').'</b>'.$this->l('. As a result, your translations will be partially saved. Please ask your hosting provider to increase this limit to') 
				.' <u><b>'.((int)$count + 100).' '.$this->l('at least.').'</b></u></div>'; 
		}
	}

	public function displayFormfront($lang)
	{
		global $currentIndex;
		$_LANG = $this->fileExists(_PS_THEME_DIR_.'lang', Tools::strtolower($lang).'.php', '_LANG');

		/* List templates to parse */
		$templates = scandir(_PS_THEME_DIR_);
		$count = 0;
		$files = array();
		foreach ($templates AS $template)
			if (preg_match('/^(.*).tpl$/', $template) AND file_exists($tpl = _PS_THEME_DIR_.$template))
			{
				$template = substr(basename($template), 0, -4);
				$newLang = array();
				$fd = fopen($tpl, 'r');
				$content = fread($fd, filesize($tpl));

				/* Search language tags (eg {l s='to translate'}) */
				$regex = '/\{l s=\''._PS_TRANS_PATTERN_.'\'( js=1)?\}/U';
				preg_match_all($regex, $content, $matches);

				/* Get string translation */
				foreach($matches[1] AS $key)
				{
					$key2 = $template.'_'.md5($key);
					$newLang[$key] = (key_exists($key2, $_LANG)) ? html_entity_decode($_LANG[$key2], ENT_COMPAT, 'UTF-8') : '';
				}
				$files[$template] = $newLang;
				$count += sizeof($newLang);
			}

		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).' - '.$this->l('Front-Office translations').'</h2>
		'.$this->l('Total expressions').' : <b>'.$count.'</b>. '.$this->l('Click the fieldset title to expand or close the fieldset.').'.<br /><br />';
		$this->displayLimitPostWarning($count);
		echo '
		<form method="post" action="'.$currentIndex.'&submitTranslationsFront=1&token='.$this->token.'" class="form">';
		$this->displayToggleButton(sizeof($_LANG) >= $count);
		$this->displayAutoTranslate();
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsFront" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		foreach ($files AS $k => $newLang)
			if (sizeof($newLang))
			{
				$countValues = array_count_values($newLang);
				$empty = isset($countValues['']) ? $countValues[''] : 0;
			 	echo '
				<fieldset><legend style="cursor : pointer" onclick="$(\'#'.$k.'-tpl\').slideToggle();">'.$k.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
					<div name="front_div" id="'.$k.'-tpl" style="display: '.($empty ? 'block' : 'none').';">
						<table cellpadding="2">';
				foreach ($newLang AS $key => $value)
				{
					echo '<tr><td style="width: 40%">'.stripslashes($key).'</td><td>= ';
					if (strlen($key) < TEXTAREA_SIZED)
						echo '<input type="text" style="width: 450px" name="'.$k.'_'.md5($key).'" value="'.stripslashes(preg_replace('/"/', '\&quot;', stripslashes($value))).'" /></td></tr>';
					else
						echo '<textarea rows="'.(int)(strlen($key) / TEXTAREA_SIZED).'" style="width: 450px" name="'.$k.'_'.md5($key).'">'.stripslashes(preg_replace('/"/', '\&quot;', stripslashes($value))).'</textarea></td></tr>';
				}
				echo '
						</table>
					</div>
				</fieldset><br />';
			}
		echo '<br /><input type="submit" name="submitTranslationsFront" value="'.$this->l('Update translations').'" class="button" /></form>';
	}

	public function displayFormback($lang)
	{
		global $currentIndex;
		$_LANGADM = $this->fileExists(_PS_TRANSLATIONS_DIR_.$lang, 'admin.php', '_LANGADM');

		/* List templates to parse */
		$count = 0;
		$tabs = scandir(PS_ADMIN_DIR.'/tabs');
		$tabs[] = '../../classes/AdminTab.php';
		$files = array();
		foreach ($tabs AS $tab)
			if (preg_match('/^(.*)\.php$/', $tab) AND file_exists($tpl = PS_ADMIN_DIR.'/tabs/'.$tab))
			{
				$tab = basename(substr($tab, 0, -4));
				$fd = fopen($tpl, 'r');
				$content = fread($fd, filesize($tpl));
				fclose($fd);
				$regex = '/this->l\(\''._PS_TRANS_PATTERN_.'\'[\)|\,]/U';
				preg_match_all($regex, $content, $matches);
				foreach ($matches[1] AS $key)
					$tabsArray[$tab][$key] = stripslashes(key_exists($tab.md5($key), $_LANGADM) ? html_entity_decode($_LANGADM[$tab.md5($key)], ENT_COMPAT, 'UTF-8') : '');
				$count += isset($tabsArray[$tab]) ? sizeof($tabsArray[$tab]) : 0;
			}
		foreach (array('header.inc', 'footer.inc', 'index', 'login', 'password') AS $tab)
		{
			$tab = PS_ADMIN_DIR.'/'.$tab.'.php';
			$fd = fopen($tab, 'r');
			$content = fread($fd, filesize($tab));
			fclose($fd);
			$regex = '/translate\(\''._PS_TRANS_PATTERN_.'\'\)/U';
			preg_match_all($regex, $content, $matches);
			foreach ($matches[1] AS $key)
				$tabsArray['index'][$key] = stripslashes(key_exists('index'.md5($key), $_LANGADM) ? html_entity_decode($_LANGADM['index'.md5($key)], ENT_COMPAT, 'UTF-8') : '');
			$count += isset($tabsArray['index']) ? sizeof($tabsArray['index']) : 0;
		}

		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).' - '.$this->l('Back-Office translations').'</h2>
		'.$this->l('Expressions to translate').' : <b>'.$count.'</b>. '.$this->l('Click on the titles to open fieldsets').'.<br /><br />';
		$this->displayLimitPostWarning($count);
		echo '
		<form method="post" action="'.$currentIndex.'&submitTranslationsBack=1&token='.$this->token.'" class="form">';
		$this->displayToggleButton();
		$this->displayAutoTranslate();
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsBack" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		foreach ($tabsArray AS $k => $newLang)
			if (sizeof($newLang))
			{
				$countValues = array_count_values($newLang);
				$empty = isset($countValues['']) ? $countValues[''] : 0;
			 	echo '
				<fieldset><legend style="cursor : pointer" onclick="$(\'#'.$k.'-tpl\').slideToggle();">'.$k.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
					<div name="back_div" id="'.$k.'-tpl" style="display: '.($empty ? 'block' : 'none').';">
						<table cellpadding="2">';
				foreach ($newLang AS $key => $value)
				{
					echo '<tr><td style="width: 40%">'.stripslashes($key).'</td><td>= ';
					if (strlen($key) < TEXTAREA_SIZED)
						echo '<input type="text" style="width: 450px" name="'.$k.md5($key).'" value="'.stripslashes(preg_replace('/"/', '\&quot;', $value)).'" /></td></tr>';
					else
						echo '<textarea rows="'.(int)(strlen($key) / TEXTAREA_SIZED).'" style="width: 450px" name="'.$k.md5($key).'">'.stripslashes(preg_replace('/"/', '\&quot;', $value)).'</textarea></td></tr>';
				}
				echo '
						</table>
					</div>
				</fieldset><br />';
			}
		echo '<br /><input type="submit" name="submitTranslationsBack" value="'.$this->l('Update translations').'" class="button" /></form>';
	}

	public function displayFormerrors($lang)
	{
		global $currentIndex;
		$_ERRORS = $this->fileExists(_PS_TRANSLATIONS_DIR_.$lang, 'errors.php', '_ERRORS');

		/* List files to parse */
		$stringToTranslate = array();
		$dirToParse = array(PS_ADMIN_DIR.'/../',
							PS_ADMIN_DIR.'/../classes/',
							PS_ADMIN_DIR.'/../controllers/',
							PS_ADMIN_DIR.'/../override/classes/',
							PS_ADMIN_DIR.'/../override/controllers/',
							PS_ADMIN_DIR.'/',
							PS_ADMIN_DIR.'/tabs/');
		if (!file_exists(_PS_MODULE_DIR_))
				die($this->displayWarning(Tools::displayError('Fatal error: Module directory is not here anymore ').'('._PS_MODULE_DIR_.')'));
			if (!is_writable(_PS_MODULE_DIR_))
				$this->displayWarning(Tools::displayError('The module directory must be writable'));
			if (!$modules = scandir(_PS_MODULE_DIR_))
				$this->displayWarning(Tools::displayError('There are no modules in your copy of PrestaShop. Use the Modules tab to activate them or go to our Website to download additional Modules.'));
			else
			{
				$count = 0;

				foreach ($modules AS $module)
					if (is_dir(_PS_MODULE_DIR_.$module) && $module != '.' && $module != '..' && $module != '.svn' )
						$dirToParse[] = _PS_MODULE_DIR_.$module.'/';
			}
		foreach ($dirToParse AS $dir)
			foreach (scandir($dir) AS $file)
				if (preg_match('/.php$/', $file) AND file_exists($fn = $dir.$file) AND $file != 'index.php')
				{
					if (!filesize($fn))
						continue;
					preg_match_all('/Tools::displayError\(\''._PS_TRANS_PATTERN_.'\'(, (true|false))?\)/U', fread(fopen($fn, 'r'), filesize($fn)), $matches);
					foreach($matches[1] AS $key)
						$stringToTranslate[$key] = (key_exists(md5($key), $_ERRORS)) ? html_entity_decode($_ERRORS[md5($key)], ENT_COMPAT, 'UTF-8') : '';
				}
		$irow = 0;
		echo '<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).' - '.$this->l('Errors translations').'</h2>'
		.$this->l('Errors to translate').' : <b>'.sizeof($stringToTranslate).'</b><br /><br />';
		$this->displayLimitPostWarning(sizeof($stringToTranslate));
		echo '
		<form method="post" action="'.$currentIndex.'&submitTranslationsErrors=1&lang='.$lang.'&token='.$this->token.'" class="form">
		<input type="submit" name="submitTranslationsErrors" value="'.$this->l('Update translations').'" class="button" /><br /><br />
		<table cellpadding="0" cellspacing="0" class="table">';
		ksort($stringToTranslate);
		foreach ($stringToTranslate AS $key => $value)
			echo '<tr '.(empty($value) ? 'style="background-color:#FBB"' : (++$irow % 2 ? 'class="alt_row"' : '')).'><td>'.stripslashes($key).'</td><td style="width: 430px">= <input type="text" name="'.md5($key).'" value="'.preg_replace('/"/', '&quot;', stripslashes($value)).'" style="width: 400px"></td></tr>';
		echo '</table><br /><input type="submit" name="submitTranslationsErrors" value="'.$this->l('Update translations').'" class="button" /></form>';
	}

	public function displayFormfields($lang)
	{
		global $currentIndex;
		$_FIELDS = $this->fileExists(_PS_TRANSLATIONS_DIR_.$lang, 'fields.php', '_FIELDS');

		$classArray = array();
		$count = 0;
		foreach (scandir(_PS_CLASS_DIR_) AS $classFile)
		{
			if (!preg_match('/\.php$/', $classFile) OR $classFile == 'index.php')
				continue;
			include_once(_PS_CLASS_DIR_.$classFile);
			$className = substr($classFile, 0, -4);
			if (!class_exists($className))
				continue;
			if (!is_subclass_of($className, 'ObjectModel'))
				continue;
			$classArray[$className] = call_user_func(array($className, 'getValidationRules'), $className);
			if (isset($classArray[$className]['validate']))
				$count += sizeof($classArray[$className]['validate']);
			if (isset($classArray[$className]['validateLang']))
				$count += sizeof($classArray[$className]['validateLang']);
		}


		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).' - '.$this->l('Fields translations').'</h2>
		'.$this->l('Fields to translate').' : <b>'.$count.'</b>. '.$this->l('Click on the titles to open fieldsets').'.<br /><br />
		<form method="post" action="'.$currentIndex.'&submitTranslationsFields=1&token='.$this->token.'" class="form">';
		$this->displayToggleButton();
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsFields" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		foreach ($classArray AS $className => $rules)
		{
			$translated = 0;
			$toTranslate = 0;
			if (isset($rules['validate']))
				foreach ($rules['validate'] AS $key => $value)
					(array_key_exists($className.'_'.md5($key), $_FIELDS)) ? ++$translated : ++$toTranslate;
			if (isset($rules['validateLang']))
				foreach ($rules['validateLang'] AS $key => $value)
					(array_key_exists($className.'_'.md5($key), $_FIELDS)) ? ++$translated : ++$toTranslate;
			echo '
			<fieldset><legend style="cursor : pointer" onclick="$(\'#'.$className.'-tpl\').slideToggle();">'.$className.' - <font color="blue">'.($toTranslate + $translated).'</font> '.$this->l('fields').' (<font color="red">'.$toTranslate.'</font>)</legend>
			<div name="fields_div" id="'.$className.'-tpl" style="display: '.($toTranslate ? 'block' : 'none').';">
				<table cellpadding="2">';
			if (isset($rules['validate']))
				foreach ($rules['validate'] AS $key => $value)
					echo '<tr><td>'.stripslashes($key).'</td><td style="width: 380px">= <input type="text" name="'.$className.'_'.md5(addslashes($key)).'" value="'.(array_key_exists($className.'_'.md5(addslashes($key)), $_FIELDS) ? html_entity_decode($_FIELDS[$className.'_'.md5(addslashes($key))], ENT_NOQUOTES, 'UTF-8') : '').'" style="width: 350px"></td></tr>';
			if (isset($rules['validateLang']))
				foreach ($rules['validateLang'] AS $key => $value)
					echo '<tr><td>'.stripslashes($key).'</td><td style="width: 380px">= <input type="text" name="'.$className.'_'.md5(addslashes($key)).'" value="'.(array_key_exists($className.'_'.md5(addslashes($key)), $_FIELDS) ? html_entity_decode($_FIELDS[$className.'_'.md5(addslashes($key))], ENT_COMPAT, 'UTF-8') : '').'" style="width: 350px"></td></tr>';
			echo '
				</table>
			</div>
			</fieldset><br />';
		}
		echo '<br /><input type="submit" name="submitTranslationsFields" value="'.$this->l('Update translations').'" class="button" /></form>';
	}

	public function displayFormmails($lang, $noDisplay = false)
	{
		global $cookie, $currentIndex;
		$mailTpls = array();
		$subjectMailContent = array();
		$mailTplsEmpty = array();
		$moduleMailTpls = array();
		$subjectModuleMailContent = array();
		$moduleMailTplsEmpty = array();
		$themeMailTpls = array();
		$subjectThemeMailContent = array();
		$themeMailTplsEmpty = array();
		$themeModuleMailTpls = array();
		$subjectThemeModuleMailContent = array();
		$themeModuleMailTplsEmpty = array();

		$langs = Language::getLanguages();
		$langIds = array();
		foreach ($langs AS &$lang_item)
			$langIds[] = $lang_item['iso_code'];
		foreach (scandir(_PS_MAIL_DIR_) AS $mail_lang_dir)
			if (in_array($mail_lang_dir, $langIds))
				foreach (scandir(_PS_MAIL_DIR_.$mail_lang_dir) AS $tpl_file)
					if (strripos($tpl_file, '.html') > 0 || strripos($tpl_file, '.txt') > 0)
					{
						if (!isset($mailTpls[$tpl_file]))
							$mailTpls[$tpl_file] = array();
						$content = file_get_contents(_PS_MAIL_DIR_.$mail_lang_dir.'/'.$tpl_file);
						if ($lang == $mail_lang_dir)
						{
							if (Tools::strlen($content) === 0)
								$mailTplsEmpty++;
							$mailTpls[$tpl_file][$mail_lang_dir] = $content;
							$subjectMailContent = self::getSubjectMailContent(_PS_MAIL_DIR_.$mail_lang_dir);
						}
					}
		foreach (scandir(_PS_MODULE_DIR_) AS $module_dir)
			if ($module_dir != '.svn' && $module_dir != '.' && $module_dir != '..' && file_exists(_PS_MODULE_DIR_.$module_dir.'/mails'))
				foreach (scandir(_PS_MODULE_DIR_.$module_dir.'/mails') AS $mail_lang_dir)
				{
					if (in_array($mail_lang_dir, $langIds))
					{
						foreach (scandir(_PS_MODULE_DIR_.$module_dir.'/mails/'.$mail_lang_dir) AS $tpl_file)
						{
							if (strripos($tpl_file, '.html') > 0 || strripos($tpl_file, '.txt') > 0)
							{
								if (!isset($moduleMailTpls[$module_dir][$tpl_file]))
									$moduleMailTpls[$module_dir][$tpl_file] = array();
								$content = file_get_contents(_PS_MODULE_DIR_.$module_dir.'/mails/'.$mail_lang_dir.'/'.$tpl_file);
								if ($lang == $mail_lang_dir)
								{
									if (Tools::strlen($content) === 0)
										$moduleMailTplsEmpty++;
									$moduleMailTpls[$module_dir][$tpl_file][$mail_lang_dir] = $content;
									$subjectModuleMailContent = self::getSubjectMailContent(_PS_MAIL_DIR_.$mail_lang_dir);
								}
							}
						}
						if ($mail_lang_dir == $lang AND file_exists(_PS_MODULE_DIR_.$module_dir.'/mails/en'))
						{
							foreach (scandir(_PS_MODULE_DIR_.$module_dir.'/mails/en') AS $tpl_file)
							{
								if (strripos($tpl_file, '.html') > 0 || strripos($tpl_file, '.txt') > 0)
								{
									if (!isset($moduleMailTpls[$module_dir][$tpl_file]))
									{
										$moduleMailTpls[$module_dir][$tpl_file] = array();
									}
									if (!isset($moduleMailTpls[$module_dir][$tpl_file][$mail_lang_dir]))
									{
										$moduleMailTpls[$module_dir][$tpl_file][$mail_lang_dir] = '';
									}
								}
							}
						}
					}
				}
		foreach (scandir(_PS_ALL_THEMES_DIR_) AS $theme_dir)
		{
			if ($theme_dir != '.svn' && $theme_dir != '.' && $theme_dir != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails'))
			{
				if (in_array($mail_lang_dir, $langIds) AND file_exists(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$mail_lang_dir))
					foreach (scandir(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$mail_lang_dir) AS $tpl_file)
						if (strripos($tpl_file, '.html') > 0 || strripos($tpl_file, '.txt') > 0)
						{
							if (!isset($themeMailTpls[$tpl_file]))
								$themeMailTpls[$theme_dir][$tpl_file] = array();
							$content = file_get_contents(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$mail_lang_dir.'/'.$tpl_file);
							if ($lang == $mail_lang_dir)
							{
								if (Tools::strlen($content) === 0)
									$themeMailTplsEmpty++;
								$themeMailTpls[$theme_dir][$tpl_file][$mail_lang_dir] = $content;
								$subjectThemeMailContent[$theme_dir] = self::getSubjectMailContent(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$mail_lang_dir);
							}
						}
			}
			if ($theme_dir != '.svn' && $theme_dir != '.' && $theme_dir != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules'))
			{
				foreach (scandir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules') AS $theme_name_module)
					if ($theme_name_module != '.svn' && $theme_name_module != '.' && $theme_name_module != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$theme_name_module.'/mails'))
						if (in_array($mail_lang_dir, $langIds))
							foreach (scandir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$theme_name_module.'/mails/'.$mail_lang_dir) AS $tpl_file)
								if (strripos($tpl_file, '.html') > 0 || strripos($tpl_file, '.txt') > 0)
								{
									if (!isset($themeModuleMailTpls[$tpl_file]))
										$themeModuleMailTpls[$theme_dir][$theme_name_module][$tpl_file] = array();
									$content = file_get_contents(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$theme_name_module.'/mails/'.$mail_lang_dir.'/'.$tpl_file);
									if ($lang == $mail_lang_dir)
									{
										if (Tools::strlen($content) === 0)
											$themeModuleMailTplsEmpty++;
										$themeModuleMailTpls[$theme_dir][$theme_name_module][$tpl_file][$mail_lang_dir] = $content;
										$subjectThemeModuleMailContent[$theme_dir][$theme_name_module] = self::getSubjectMailContent(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$mail_lang_dir);
									}
								}
			}
		}
		// get mail subjects
		$subjectMail = array();
		$subjectMail = self::getSubjectMail(_PS_ROOT_DIR_, $subjectMail);
		// end get mail subjects

		if ($noDisplay)
		{
			$empty = 0;
			foreach ($mailTpls AS $key => $tpl_file)
			{
				if (Tools::strlen($tpl_file[$lang]) == 0)
					$empty++;
			}

			foreach ($moduleMailTpls AS $key => $tpl_file)
				foreach ($tpl_file AS $key2 => $tpl_file2)
				{
					if (Tools::strlen($tpl_file[$key2][$lang]) == 0)
						$empty++;
				}

			return array('total' => count($mailTpls)+count($moduleMailTpls,COUNT_RECURSIVE), 'empty' => $empty);
		}

		// TinyMCE
		$iso = Language::getIsoById((int)($cookie->id_lang));
		echo '
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript">
				tinyMCE.init({
					mode : "specific_textareas",
					editor_deselector : "noEditor",
					theme : "advanced",
					plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
					// Theme options
					theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
					theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
					theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : false,
					content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
					document_base_url : "'.__PS_BASE_URI__.'",
					width: "600",
					height: "600",
					font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
					// Drop lists for link/image/media/template dialogs
					template_external_list_url : "lists/template_list.js",
					external_link_list_url : "lists/link_list.js",
					external_image_list_url : "lists/image_list.js",
					media_external_list_url : "lists/media_list.js",
					elements : "nourlconvert",
					entity_encoding: "raw",
					convert_urls : false,
					language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
					
				});
				function displayTiny(obj)
				{
					tinyMCE.get(obj.attr(\'name\')).show();
				}
			</script>
		';
		$mylang = new Language(Language::getIdByIso($lang));
			echo '<!--'.$this->l('Language').'-->';
		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).' - '.$this->l('Mails translations').'</h2>'
		.$this->l('Click on the titles to open fieldsets').'.<br /><br />';

		// display form
		echo '
		<form method="post" action="'.$currentIndex.'&token='.$this->token.'" class="form">';
		$this->displayToggleButton();
		echo '<input type="submit" name="submitTranslationsMails" value="'.$this->l('Update translations').'" class="button" /><br/><br/>';

		//count nb core emails
		$nbr = 0;
		foreach ($mailTpls AS $mailTplName => $mailTpl)
			if ((strripos($mailTplName, '.html') AND isset($subjectMail[substr($mailTplName, 0, -5)]))
			OR (strripos($mailTplName, '.txt') AND isset($subjectMail[substr($mailTplName, 0, -4)])))
				$nbr++;

		echo'
		<div class="mails_field" >
			<h3 style="cursor : pointer" onclick="$(\'#core\').slideToggle();">Core e-mails - <font color="blue">'.$nbr.'</font> templates for '.$mylang->name.':</h3>
			<div name="mails_div" id="core">';
		
		// @todo : Need to be factorised
		// core emails
		foreach ($mailTpls AS $mailTplName => $mailTpl)
		{
			if ((strripos($mailTplName, '.html') AND isset($subjectMail[substr($mailTplName, 0, -5)]))
			OR (strripos($mailTplName, '.txt') AND isset($subjectMail[substr($mailTplName, 0, -4)])))
			{
				$subject_mail = isset($subjectMail[substr($mailTplName, 0, -5)]) ? $subjectMail[substr($mailTplName, 0, -5)] : '' ; 
				$subject_mail = ($subject_mail === '' AND isset($subjectMail[substr($mailTplName, 0, -4)])) ? $subjectMail[substr($mailTplName, 0, -4)] : $subject_mail ;
					
				echo '
				<div class="block-mail" >
					<label>'.$mailTplName.'</label>
					<div class="mail-form">';
				if (strripos($mailTplName, '.html'))
				{
					if ($subject_mail !== '')
					{
						echo '
						<div class="label-subject">
							<b>'.$this->l('Subject:').'</b>&nbsp;'.$subject_mail.'<br />
							<input type="text" name="subject[mails]['.$subject_mail.']" value="'.(isset($subjectMailContent[$subject_mail]) ? $subjectMailContent[$subject_mail] : '').'" />
						</div>';
					}
					echo '
					<div>
						<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'mails/'.$lang.'/'.$mailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>
						<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); displayTiny($(this).parent().next()); return false;" class="button">Edit this mail template</a>
					</div>
					<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[html]['.$mailTplName.']">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
				}
				else
				{
					echo '<div><textarea class="rte mailrte noEditor" cols="80" rows="30" name="mail[txt]['.$mailTplName.']" style="width:560px;margin=0;">'.htmlentities(stripslashes(strip_tags($mailTpl[$lang])), ENT_COMPAT, 'UTF-8').'</textarea></div>';
				}
				echo '
					</div><!-- .mail-form -->
				</div><!-- .block-mail -->';
			}
		}
		echo '
			</div><!-- #core -->
			<div class="clear"></div>
		</div>';

		// module mails
		echo '<div id="modules">';
		foreach ($moduleMailTpls AS $key33 => $moduleMailTpls2)
		{
			echo '
			<div class="mails_field" >
				<h3 style="cursor : pointer" onclick="$(\'#'.$key33.'\').slideToggle();">Module "'.$key33.'" - <font color="blue">'.(count($moduleMailTpls2,COUNT_RECURSIVE)/2).'</font> templates for '.$mylang->name.':</h3>
				<div name="mails_div" id="'.$key33.'">';
			foreach ($moduleMailTpls2 AS $mailTplName => $mailTpl)
			{
				if (strripos($mailTplName, '.html') OR strripos($mailTplName, '.txt'))
				{
					$subject_mail = isset($subjectMail[substr($mailTplName, 0, -5)]) ? $subjectMail[substr($mailTplName, 0, -5)] : '' ; 
					$subject_mail = ($subject_mail === '' AND isset($subjectMail[substr($mailTplName, 0, -4)])) ? $subjectMail[substr($mailTplName, 0, -4)] : $subject_mail ;
					
					echo '
					<div class="block-mail">
						<label>'.$mailTplName.'</label>
						<div class="mail-form">';
					if (strripos($mailTplName, '.html'))
					{
						if ($subject_mail !== '')
						{
							echo '
							<div class="label-subject">
								<b>'.$this->l('Subject:').'</b>&nbsp;'.$subject_mail.'<br />
								<input type="text" name="subject[mails]['.$subject_mail.']" value="'.(isset($subjectModuleMailContent[$subject_mail]) ? $subjectModuleMailContent[$subject_mail] : '').'" />
							</div>';
						}
						echo '
						<div>';
						if (file_exists(_PS_MODULE_DIR_.$key33.'/mails/'.$lang.'/'.$mailTplName))
							echo '
							<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'modules/'.$key33.'/mails/'.$lang.'/'.$mailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>';
						else
							echo 'This version is currently not translated. Please click the \'Edit this mail template\' button to create a new template.';
						
						echo '
							<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); displayTiny($(this).parent().next()); return false;" class="button">Edit this mail template</a>
						</div>
						<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[modules]['.$key33.'][html]['.$mailTplName.']">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
					}
					else
						echo '<div><textarea class="rte mailrte noEditor" cols="80" rows="30" name="mail[modules]['.$key33.'][txt]['.$mailTplName.']" style="width:560px;margin=0;">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div>';
					echo '
						</div><!-- .mail-form -->
					</div><!-- .block-mail -->';
				}
			}
			echo '
				</div><!-- #'.$key33.' -->
				<div class="clear"></div>
			</div>';
		}
		echo '</div>';

		// mail theme
		foreach (scandir(_PS_ALL_THEMES_DIR_) AS $theme_dir)
		{
			if ($theme_dir != '.svn' && $theme_dir != '.' && $theme_dir != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir)
				&& isset($themeMailTpls[$theme_dir]))
			{
				// count nb mail in mailtheme
				$nb = 0;
				foreach ($themeMailTpls[$theme_dir] AS $key2 => $tab)
					if ((strripos($key2, '.html') AND isset($subjectMail[substr($key2, 0, -5)]))
					OR (strripos($key2, '.txt') AND isset($subjectMail[substr($key2, 0, -4)])))
						$nb++;

				echo '
				<div class="mails_field" >
					<h3 style="cursor : pointer" onclick="$(\'#'.$theme_dir.'\').slideToggle();">Theme : '.$theme_dir.' - <font color="blue">'.$nb.'</font> templates for '.$mylang->name.' :</h3>
					<div name="mails_div" id="'.$theme_dir.'">';

				// core mail theme
				foreach ($themeMailTpls[$theme_dir] AS $themeMailTplName => $themeMailTpl)
				{
					if (strripos($themeMailTplName, '.html') OR strripos($themeMailTplName, '.txt'))
					{
						$subject_mail = isset($subjectMail[substr($themeMailTplName, 0, -5)]) ? $subjectMail[substr($themeMailTplName, 0, -5)] : '' ; 
						$subject_mail = ($subject_mail === '' AND isset($subjectMail[substr($themeMailTplName, 0, -4)])) ? $subjectMail[substr($themeMailTplName, 0, -4)] : $subject_mail ;
						
						echo '
						<div class="block-mail">
							<label>'.$themeMailTplName.'</label>
							<div class="mail-form">';

						if (strripos($themeMailTplName, '.html'))
						{
							if ($subject_mail !== '')
							{
								echo '
								<div class="label-subject">*
									<b>'.$this->l('Subject').'</b>'.$subject_mail.'<br />
									<input type="text" name="subject[themes]['.$theme_dir.']['.$subject_mail.']" value="'.(isset($subjectThemeMailContent[$theme_dir][$subject_mail]) ? $subjectThemeMailContent[$theme_dir][$subject_mail] : '').'" />
								</div>';
							}
							if (file_exists(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$lang.'/'.$themeMailTplName))
								echo '<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'themes/'.$theme_dir.'/mails/'.$lang.'/'.$themeMailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>';
							else
								echo 'This version is currently not translated. Please click the \'Edit this mail template\' button to create a new template.';

							echo '<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); displayTiny($(this).parent().next()); return false;" class="button">Edit this mail template</a></div>
							<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[themes]['.$theme_dir.'][html]['.$themeMailTplName.']">'.(isset($themeMailTpl[$lang]) ? htmlentities(stripslashes($themeMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
						}
						else
							echo '<div style="clear:both;"><textarea class="rte mailrte noEditor" cols="80" rows="30" name="mail[themes]['.$theme_dir.'][txt]['.$themeMailTplName.']" style="width:560px;margin=0;">'.(isset($themeMailTpl[$lang]) ? htmlentities(stripslashes($themeMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div>';
						echo '
							</div><!-- .mail-form -->
						</div><!-- .block-mail -->';
					}
				}

				// module mail theme
				echo '<span class="style-themeModuleMail" onclick="$(\'#div'.$theme_dir.'\').slideToggle();">Modules - <font color="blue">'.count($themeModuleMailTpls[$theme_dir]).'</font> templates for '.$mylang->name.' :</span>';
				echo '<div style="margin: 0; padding: 1em; border: 1px solid #DFD5C3; background: #FFFFF0;" id="div'.$theme_dir.'">';

				foreach ($themeModuleMailTpls[$theme_dir] AS $themeModuleName => $themeModule)
				{
					echo '<span class="style-themeModuleName" onclick="$(\'#'.$theme_dir.$themeModuleName.'\').slideToggle();">'.$themeModuleName.' - <font color="blue">'.count($themeModule).'</font> templates for '.$mylang->name.' :</span>';
					echo '<div style="margin: 0; padding: 1em; border: 1px solid #DFD5C3; background: #FFFFF0;" id="'.$theme_dir.$themeModuleName.'">';
					foreach ($themeModule AS $themeModuleMailTplName => $themeModuleMailTpl)
					{
						if (strripos($themeModuleMailTplName, '.html') OR strripos($themeModuleMailTplName, '.txt'))
						{
							$subject_mail = isset($subjectMail[substr($themeModuleMailTplName, 0, -5)]) ? $subjectMail[substr($themeModuleMailTplName, 0, -5)] : '' ; 
							$subject_mail = ($subject_mail === '' AND isset($subjectMail[substr($themeModuleMailTplName, 0, -4)])) ? $subjectMail[substr($themeModuleMailTplName, 0, -4)] : $subject_mail ;
							
							echo '
							<div class="block-mail">
								<label>'.$themeModuleMailTplName.'</label>
								<div class="mail-form">';
							if (strripos($themeModuleMailTplName, '.html'))
							{
								if ($subject_mail !== '')
								{
									echo '
									<div class="label-subject">
										<b>'.$this->l('Subject:').'</b>&nbsp;'.$subject_mail.'<br />
										<input type="text" name="subject[themes]['.$theme_dir.']['.$subject_mail.']" value="'.(isset($subjectThemeModuleMailContent[$theme_dir][$themeModuleName][$subject_mail]) ? $subjectThemeModuleMailContent[$theme_dir][$themeModuleName][$subject_mail] : '').'" />
									<div><!-- .label-subject -->';
								}

								if (file_exists(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$themeModuleName.'/mails/'.$lang.'/'.$themeModuleMailTplName))
									echo '<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'themes/'.$theme_dir.'/modules/'.$themeModuleName.'/mails/'.$lang.'/'.$themeModuleMailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>';
								else
									echo 'This version is currently not translated. Please click the \'Edit this mail template\' button to create a new template.';

								echo '<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); displayTiny($(this).parent().next()); return false;" class="button">Edit this mail template</a></div>
								<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[themes_module]['.$theme_dir.']['.$themeModuleName.'][html]['.$themeModuleMailTplName.']">'.(isset($themeModuleMailTpl[$lang]) ? htmlentities(stripslashes($themeModuleMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
							}
							else
								echo '<div><textarea class="rte mailrte noEditor" cols="80" rows="30" name="mail[themes]['.$theme_dir.'][txt]['.$themeModuleMailTplName.']" style="width:560px;margin=0;">'.(isset($themeModuleMailTpl[$lang]) ? htmlentities(stripslashes($themeModuleMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div><br/>';
							echo '
								</div><!-- .mail-form -->
							</div><!-- .block-mail -->';
						}
					}
					echo '</div><!-- #'.$theme_dir.$themeModuleName.' -->';
				}
				echo '
					</div><!-- div'.$theme_dir.' -->
					<div class="clear"></div>
				</div>
				</div>';
			}
		}
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsMails" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		echo '</form>';
	}

	private function getSubjectMail($directory, $subjectMail)
	{
		foreach (scandir($directory) AS $filename)
		{
			if (strripos($filename, '.php') > 0 AND $filename != 'AdminTranslations.php')
			{
				$content = file_get_contents($directory.'/'.$filename);
				$content = str_replace("\n", " ", $content);
				if (preg_match_all('/Mail::Send([^;]*);/si', $content, $tab))
				{
					for ($i = 0 ; isset($tab[1][$i]) ; $i++)
					{
							$tab2 = explode(',', $tab[1][$i]);
							if (is_array($tab2))
							{
								$tab2[1] = trim(str_replace('\'', '', $tab2[1]));
								if (preg_match('/Mail::l\(\'(.*)\'\)/s', $tab2[2], $tab3))
									$tab2[2] = $tab3[1];
								$subjectMail[$tab2[1]] = $tab2[2];
							}
					}
				}
			}
			if ($filename != '.svn' AND $filename != '.' AND $filename != '..' AND is_dir(($directory.'/'.$filename)))
				 $subjectMail = self::getSubjectMail($directory.'/'.$filename, $subjectMail);
		}
		return $subjectMail;
	}

	private function getSubjectMailContent($directory)
	{
		$subjectMailContent =  array();
		if (file_exists($directory.'/lang.php'))
		if (($content = file_get_contents($directory.'/lang.php')))
		{
			$content = str_replace("\n", " ", $content);
			$content = str_replace("\\'", "\'", $content);
			preg_match_all('/\$_LANGMAIL\[\'([^\']*)\'\] = \'([^;]*)\';/', $content, $matches);
			for ($i = 0; isset($matches[1][$i]); $i++)
			{
				if (isset($matches[2][$i]))
					$subjectMailContent[stripslashes($matches[1][$i])] = stripslashes($matches[2][$i]);
			}
		}
		return $subjectMailContent;
	}

	private function writeSubjectTranslationFile($sub, $path, $mark = false, $fullmark = false)
	{
		global $currentIndex;

		if ($fd = fopen($path, 'w'))
		{
			//$tab = ($fullmark ? Tools::strtoupper($fullmark) : 'LANG').($mark ? Tools::strtoupper($mark) : '');
			$tab = 'LANGMAIL';
			fwrite($fd, "<?php\n\nglobal \$_".$tab.";\n\$_".$tab." = array();\n");

			foreach($sub AS $key => $value)
				fwrite($fd, '$_'.$tab.'[\''.pSQL($key, true).'\'] = \''.pSQL($value, true).'\';'."\n");
			fwrite($fd, "\n?>");
			fclose($fd);

		}
		else
			die('Cannot write language file');
	}
	
	/**
	 * This get files to translate in module directory.
	 * Recursive method allow to get each files for a module no matter his depth.
	 * 
	 * @param string $path directory path to scan
	 * @param array $array_files by reference - array which saved files to parse.
	 * @param string $module_name module name
	 * @param string $lang_file full path of translation file
	 * @param boolean $is_default 
	 */
	private function recursiveGetModuleFiles($path, &$array_files, $module_name, $lang_file, $is_default = false)
	{
		$files_module = array();
		$files_module = scandir($path);
		$files_for_module = $this->clearModuleFiles($files_module, 'file');
		if (!empty($files_for_module))
			$array_files[] = array(
				'file_name'		=> $lang_file,
				'dir'			=> $path,
				'files'			=> $files_for_module,
				'module'		=> $module_name,
				'is_default'	=> $is_default,
				'theme'			=> ($is_default ? self::DEFAULT_THEME_NAME : _THEME_NAME_ ),
			);
		$dir_module = $this->clearModuleFiles($files_module, 'directory', $path);
		if(!empty($dir_module))
		{
			foreach ($dir_module AS $folder)
			{
				$this->recursiveGetModuleFiles($path.$folder.'/', $array_files, $module_name, $lang_file, $is_default);
			}
		}
	}
	
	/**
	 * This method get translation in each translations file.
	 * The file depend on $lang param.
	 * 
	 * @param array $modules list of modules
	 * @param string $root_dir path where it get each modules
	 * @param string $lang iso code of choosen language to translate
	 * @param boolean $is_default set it if modules are located in root/prestashop/modules folder
	 * 				  This allow to distinguish overrided prestashop theme and original module 
	 */
	private function getAllModuleFiles(array $modules, $root_dir, $lang, $is_default = false)
	{
		$array_files = array();
		foreach ($modules AS $module)
		{
			if ($module{0} != '.' AND is_dir($root_dir.$module))
			{
				@include_once($root_dir.$module.'/'.$lang.'.php');
				self::getModuleTranslations($is_default);
				$this->recursiveGetModuleFiles($root_dir.$module.'/', $array_files, $module, $root_dir.$module.'/'.$lang.'.php', $is_default);
			}
		}
		return $array_files;
	}
	public function displayFormModules($lang)
	{
		global $currentIndex, $_MODULES;
		
		$array_lang_src = Language::getLanguages(false);
		foreach ($array_lang_src as $language)
		{
			$this->all_iso_lang[] = $language['iso_code'];
		}

		if (!file_exists(_PS_MODULE_DIR_))
			die($this->displayWarning(Tools::displayError('Fatal error: Module directory is not here anymore ').'('._PS_MODULE_DIR_.')'));
		if (!is_writable(_PS_MODULE_DIR_))
			$this->displayWarning(Tools::displayError('The module directory must be writable'));
		if (!$modules = scandir(_PS_MODULE_DIR_))
			$this->displayWarning(Tools::displayError('There are no modules in your copy of PrestaShop. Use the Modules tab to activate them or go to our Website to download additional Modules.'));
		else
		{
			$arr_find_and_fill = array();
			
			$arr_files = $this->getAllModuleFiles($modules, _PS_MODULE_DIR_, $lang, true);
			$arr_find_and_fill = array_merge($arr_find_and_fill, $arr_files);
			
			if(file_exists(_PS_THEME_DIR_.'/modules/'))
			{
				$modules = scandir(_PS_THEME_DIR_.'/modules/');
				$arr_files = $this->getAllModuleFiles($modules, _PS_THEME_DIR_.'modules/', $lang);
				$arr_find_and_fill = array_merge($arr_find_and_fill, $arr_files);
			}
			foreach ($arr_find_and_fill as $value)
				$this->findAndFillTranslations($value['files'], $value['theme'], $value['module'], $value['dir'], $lang);
			
			echo '
			<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).' - '.$this->l('Modules translations').'</h2>
			'.$this->l('Total expressions').' : <b>'.$this->total_expression.'</b>. '.$this->l('Click the fieldset title to expand or close the fieldset.').'.<br /><br />';
			$this->displayLimitPostWarning($this->total_expression);
			echo '
			<form method="post" action="'.$currentIndex.'&submitTranslationsModules=1&token='.$this->token.'" class="form">';
			$this->displayToggleButton();
			$this->displayAutoTranslate();
			echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsModules" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
			echo '<h3 style="padding:0;margin:0;">'.$this->l('Click to access theme translation:').'</h3>';
			echo '<ul style="list-style-type:none;padding:0;margin:0 0 10px 0;">';
			foreach (array_keys($this->modules_translations) as $theme)
			{
				echo '<li><a href="#'.$theme.'" class="link">- '.($theme === 'default' ? $this->l('default') : $theme ).'</a></li>';
			}
			echo '</ul>';
			foreach ($this->modules_translations AS $theme_name => $theme)
			{
				echo '<h2>&gt;'.$this->l('Theme:').' <a name="'.$theme_name.'">'.($theme_name === self::DEFAULT_THEME_NAME ? $this->l('default') : $theme_name ).'</h2>';
				foreach ($theme AS $module_name => $module)
				{
					echo ''.$this->l('Module:').' <a name="'.$module_name.'" style="font-style:italic">'.$module_name.'</a>';
					foreach ($module AS $template_name => $newLang)
						if (sizeof($newLang))
						{
							$countValues = array_count_values($newLang);
							$empty = isset($countValues['']) ? $countValues[''] : 0;
							echo '
							<fieldset style="margin-top:5px"><legend style="cursor : pointer" onclick="$(\'#'.$theme_name.'_'.$module_name.'_'.$template_name.'\').slideToggle();">'.($theme_name === 'default' ? $this->l('default') : $theme_name ).' - '.$template_name.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
								<div name="modules_div" id="'.$theme_name.'_'.$module_name.'_'.$template_name.'" style="display: '.($empty ? 'block' : 'none').';">
									<table cellpadding="2">';
							foreach ($newLang AS $key => $value)
							{
								echo '<tr><td style="width: 40%">'.stripslashes($key).'</td><td>= ';
								if (strlen($key) < TEXTAREA_SIZED)
									echo '<input type="text" style="width: 450px" name="'.md5($module_name.'_'.$theme_name.'_'.$template_name.'_'.md5($key)).'" value="'.stripslashes(preg_replace('/"/', '\&quot;', stripslashes($value))).'" /></td></tr>';
								else
									echo '<textarea rows="'.(int)(strlen($key) / TEXTAREA_SIZED).'" style="width: 450px" name="'.md5($module_name.'_'.$theme_name.'_'.$template_name.'_'.md5($key)).'">'.stripslashes(preg_replace('/"/', '\&quot;', stripslashes($value))).'</textarea></td></tr>';
							}
							echo '
									</table>
								</div>
							</fieldset><br />';
						}
				}
			}
			echo '<br /><input type="submit" name="submitTranslationsModules" value="'.$this->l('Update translations').'" class="button" /></form>';

		}
	}

	public function displayFormPDF()
	{
		global $currentIndex;

		$lang = Tools::strtolower(Tools::getValue('lang'));
		$_LANG = array();
		if (!file_exists(_PS_TRANSLATIONS_DIR_.$lang))
			if (!mkdir(_PS_TRANSLATIONS_DIR_.$lang, 0700))
				die('Please create a "'.$iso.'" directory in '._PS_TRANSLATIONS_DIR_);
		if (!file_exists(_PS_TRANSLATIONS_DIR_.$lang.'/pdf.php'))
			if (!file_put_contents(_PS_TRANSLATIONS_DIR_.$lang.'/pdf.php', "<?php\n\nglobal \$_LANGPDF;\n\$_LANGPDF = array();\n\n?>"))
				die('Please create a "'.Tools::strtolower($lang).'.php" file in '.realpath(PS_ADMIN_DIR.'/'));
		unset($_LANGPDF);
		@include(_PS_TRANSLATIONS_DIR_.$lang.'/pdf.php');
		$files = array();
		$count = 0;
		$tab = 'PDF_invoice';
		$pdf = _PS_CLASS_DIR_.'PDF.php';
		$newLang = array();
		$fd = fopen($pdf, 'r');
		$content = fread($fd, filesize($pdf));
		fclose($fd);
		$regex = '/self::l\(\''._PS_TRANS_PATTERN_.'\'[\)|\,]/U';
		preg_match_all($regex, $content, $matches);
		foreach($matches[1] AS $key)
			$tabsArray[$tab][$key] = stripslashes(key_exists($tab.md5(addslashes($key)), $_LANGPDF) ? html_entity_decode($_LANGPDF[$tab.md5(addslashes($key))], ENT_COMPAT, 'UTF-8') : '');
		$count += isset($tabsArray[$tab]) ? sizeof($tabsArray[$tab]) : 0;
		$closed = sizeof($_LANGPDF) >= $count;

		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>
		'.$this->l('Expressions to translate').' : <b>'.$count.'</b>. '.$this->l('Click on the titles to open fieldsets').'.<br /><br />';
		$this->displayLimitPostWarning($count);
		echo '
		<form method="post" action="'.$currentIndex.'&submitTranslationsPDF=1&token='.$this->token.'" class="form">
				<script type="text/javascript">
					var openAll = \''.html_entity_decode($this->l('Expand all fieldsets'), ENT_NOQUOTES, 'UTF-8').'\';
					var closeAll = \''.html_entity_decode($this->l('Close all fieldsets'), ENT_NOQUOTES, 'UTF-8').'\';
				</script>
				<input type="hidden" name="lang" value="'.$lang.'" />
				<input type="button" class="button" id="buttonall" onclick="openCloseAllDiv(\'pdf_div\', this.value == openAll); toggleElemValue(this.id, openAll, closeAll);" />
				<script type="text/javascript">
					toggleElemValue(\'buttonall\', '.($closed ? 'openAll' : 'closeAll').', '.($closed ? 'closeAll' : 'openAll').');
				</script>';
		echo '<input type="submit" name="submitTranslationsPDF" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		foreach ($tabsArray AS $k => $newLang)
			if (sizeof($newLang))
			{
				$countValues = array_count_values($newLang);
				$empty = isset($countValues['']) ? $countValues[''] : 0;
			 	echo '
				<fieldset style="width: 700px"><legend style="cursor : pointer" onclick="$(\''.$k.'-tpl\').slideToggle();">'.$k.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
					<div name="pdf_div" id="'.$k.'-tpl" style="display: '.($empty ? 'block' : 'none').';">
						<table cellpadding="2">';
				foreach ($newLang AS $key => $value)
				{
					echo '
					<tr>
						<td>'.stripslashes($key).'</td>
						<td style="width: 280px">
							= <input type="text" name="'.$k.md5($key).'" value="'.stripslashes(preg_replace('/"/', '\&quot;', $value)).'" style="width: 250px">
						</td>
					</tr>';
				}
				echo '
						</table>
					</div>
				</fieldset><br />';
			}
		echo '<br /><input type="submit" name="submitTranslationsPDF" value="'.$this->l('Update translations').'" class="button" /></form>';
	}

	/**
	  * Return an array with themes and thumbnails
	  *
	  * @return array
	  */
	static public function getThemesList()
	{
		$dir = opendir(_PS_ALL_THEMES_DIR_);
		while ($folder = readdir($dir))
			if ($folder != '.' AND $folder != '..' AND file_exists(_PS_ALL_THEMES_DIR_.'/'.$folder.'/preview.jpg'))
				$themes[$folder]['name'] = $folder;
		closedir($dir);
		return isset($themes) ? $themes : array();
	}
}

