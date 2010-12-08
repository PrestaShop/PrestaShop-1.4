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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
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
	private function getModuleTranslations()
	{
		global $_MODULES, $_MODULE;

		if (!isset($_MODULE) AND !isset($_MODULES))
			$_MODULES = array();
		elseif (isset($_MODULE))
			$_MODULES = (is_array($_MODULES) AND is_array($_MODULE)) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
	}

	private function checkDirAndCreate($dest)
	{
		$bool = true;
		$dir = trim(str_replace(_PS_ROOT_DIR_, '', dirname($dest)), '/');
		$subdir = explode('/', $dir);
		for ($i = 0, $path = ''; $subdir[$i]; $i++)
		{
			$path .= $subdir[$i].'/';
			if (!createDir(_PS_ROOT_DIR_.'/'.$path, 0777))
			{
				$bool &= false;
				$this->_errors[] = $this->l('Cannot create the folder').' "'.$path.'". '.$this->l('Check directory writing permisions.');
				break ;
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
			foreach($_POST as $key => $value)
				if (!empty($value))
					$toInsert[$key] = /*htmlentities(*/$value/*, ENT_COMPAT, 'UTF-8')*/;

			$tab = ($fullmark ? Tools::strtoupper($fullmark) : 'LANG').($mark ? Tools::strtoupper($mark) : '');
			fwrite($fd, "<?php\n\nglobal \$_".$tab.";\n\$_".$tab." = array();\n");
			foreach($toInsert as $key => $value)
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
		foreach ($items as $source => $dest)
		{
			$bool &= $this->checkDirAndCreate($dest);
			$bool &= @copy($source, $dest);
		}
		if ($bool)
			Tools::redirectLink($currentIndex.'&conf=14&token='.$this->token);
		$this->_errors[] = $this->l('a part of the data has been copied but some language files could not be found or copied');
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

	public function submitImportLang()
	{
		global $currentIndex;

		if (!isset($_FILES['file']['tmp_name']) OR !$_FILES['file']['tmp_name'])
			$this->_errors[] = Tools::displayError('no file selected');
		else
		{
			$gz = new Archive_Tar($_FILES['file']['tmp_name'], true);
			if ($gz->extract(_PS_TRANSLATIONS_DIR_.'../', false))
			{
				if (Validate::isLanguageFileName($_FILES['file']['name']))
				{
					$iso_code = str_replace('.gzip', '', $_FILES['file']['name']);
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

		if (Validate::isLangIsoCode(Tools::getValue('iso_import_language')))
		{
			if (@fsockopen('www.prestashop.com', 80))
			{
				if ($content = file_get_contents('http://www.prestashop.com/download/lang_packs/gzip/'.Tools::getValue('iso_import_language').'.gzip'))
				{
					$file = _PS_TRANSLATIONS_DIR_.Tools::getValue('iso_import_language').'.gzip';
					if (file_put_contents($file, $content))
					{
						$gz = new Archive_Tar($file, true);
						if ($gz->extract(_PS_TRANSLATIONS_DIR_.'../', false))
						{
							if (!Language::checkAndAddLanguage(Tools::getValue('iso_import_language')))
								$conf = 20;
							unlink($file);
							Tools::redirectAdmin($currentIndex.'&conf='.(isset($conf) ? $conf : '15').'&token='.$this->token);
						}
						$this->_errors[] = Tools::displayError('archive cannot be extracted');
						unlink($file);
					}
					else
						$this->_errors[] = Tools::displayError('Server don\'t have permissions for writing');
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

	public function findAndWriteTranslationsIntoFile($filename, $files, $themeName, $moduleName, $dir = false)
	{
		static $_cacheFile = array();

		if (!isset($_cacheFile[$filename]))
		{
			$_cacheFile[$filename] = true;
			if (!$fd = fopen($filename, 'w'))
				die ($this->l('Cannot write the theme\'s language file ').'('.$filename.')'.$this->l('. Please check write permissions.'));
			fwrite($fd, "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n");
			fclose($fd);
		}

		$tplRegex = '/\{l s=\''._PS_TRANS_PATTERN_.'\'( mod=\'.+\')?( js=1)?\}/U';
		$phpRegex = '/->l\(\''._PS_TRANS_PATTERN_.'\'(, \'(.+)\')?(, (.+))?\)/U';

		if (!$dir)
			$dir = ($themeName == 'prestashop' ? _PS_MODULE_DIR_.$moduleName.'/' : _PS_ALL_THEMES_DIR_.$themeName.'/modules/'.$moduleName.'/');
		if (!$writeFd = fopen($filename, 'a+'))
			die ($this->l('Cannot write the theme\'s language file ').'('.$filename.')'.$this->l('. Please check write permissions.'));
		else
		{
			$_tmp = array();
			foreach ($files AS $templateFile)
			{
				if ((preg_match('/^(.*).tpl$/', $templateFile) OR ($themeName == 'prestashop' AND preg_match('/^(.*).php$/', $templateFile))) AND file_exists($tpl = $dir.$templateFile))
				{
						/* Get translations key */
						$readFd = fopen($tpl, 'r');
						$content = (filesize($tpl) ? fread($readFd, filesize($tpl)) : '');
						preg_match_all(substr($templateFile, -4) == '.tpl' ? $tplRegex : $phpRegex, $content, $matches);
						fclose($readFd);

						/* Write each translation on its module file */
						$templateName = substr(basename($templateFile), 0, -4);
						foreach ($matches[1] as $key)
						{
							$postKey = md5($moduleName.'_'.$themeName.'_'.$templateName.'_'.md5($key));
							$pattern = '\'<{'.$moduleName.'}'.$themeName.'>'.$templateName.'_'.md5($key).'\'';
							if (array_key_exists($postKey, $_POST) AND !empty($_POST[$postKey]) AND !array_key_exists($pattern, $_tmp))
							{
								$_tmp[$pattern] = true;
								fwrite($writeFd, '$_MODULE['.$pattern.'] = \''.pSQL($_POST[$postKey]).'\';'."\n");
							}
						}
				}
			}
			fclose($writeFd);
		}
	}

	public function findAndFillTranslations($files, &$translationsArray, $themeName, $moduleName, $dir = false)
	{
		global $_MODULES;
		$tplRegex = '/\{l s=\''._PS_TRANS_PATTERN_.'\'( mod=\'.+\')?( js=1)?\}/U';
		$phpRegex = '/->l\(\''._PS_TRANS_PATTERN_.'\'(, \'(.+)\')?(, (.+))?\)/U';

		$count = 0;
		if (!$dir)
			$dir = ($themeName == 'prestashop' ? _PS_MODULE_DIR_.$moduleName.'/' : _PS_ALL_THEMES_DIR_.$themeName.'/modules/'.$moduleName.'/');
		foreach ($files AS $templateFile)
			if ((preg_match('/^(.*).tpl$/', $templateFile) OR ($themeName == 'prestashop' AND preg_match('/^(.*).php$/', $templateFile))) AND file_exists($tpl = $dir.$templateFile))
			{
					/* Get translations key */
					$readFd = fopen($tpl, 'r');
					$content = (filesize($tpl) ? fread($readFd, filesize($tpl)) : '');
					preg_match_all(substr($templateFile, -4) == '.tpl' ? $tplRegex : $phpRegex, $content, $matches);
					fclose($readFd);

					/* Write each translation on its module file */
					$templateName = substr(basename($templateFile), 0, -4);
					foreach ($matches[1] as $key)
					{
						$moduleKey = '<{'.$moduleName.'}'.$themeName.'>'.$templateName.'_'.md5($key);
						$translationsArray[$themeName][$moduleName][$templateName][$key] = key_exists($moduleKey, $_MODULES) ? html_entity_decode($_MODULES[$moduleKey], ENT_COMPAT, 'UTF-8') : '';
					}
					$count += isset($translationsArray[$themeName][$moduleName][$templateName]) ? sizeof($translationsArray[$themeName][$moduleName][$templateName]) : 0;
			}
		return ($count);
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
		 		foreach($content['html'] as $filename => $file_content)
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
		 		foreach($content['txt'] as $filename => $file_content)
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
				foreach($content['modules'] as $module_dir => $versions)
		 		{
		 			if (!file_exists(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang')))
		 			{
		 				mkdir(_PS_MODULE_DIR_.$module_dir.'/mails/'.Tools::getValue('lang'), 0777);
		 			}
		 			if (isset($versions['html']))
						foreach($versions['html'] as $filename => $file_content)
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
				 		foreach($versions['txt'] as $filename => $file_content)
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
				foreach($content['themes'] as $theme_dir_name => $theme_dir)
				{
				if (isset($theme_dir['html']))
					foreach ($theme_dir['html'] as $filename => $file_content)
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
					foreach($content['txt'] as $filename => $file_content)
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
				foreach ($content['themes_module'] as $theme_dir_name => $theme_dir)
				foreach ($theme_dir as $theme_module_dir_name => $theme_module_dir)
				{
					foreach($theme_module_dir['html'] as $filename => $file_content)
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
					foreach($theme_module_dir['txt'] as $filename => $file_content)
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
				foreach ($subjecttab as $key => $subjecttype)
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
						foreach ($subjecttype as $nametheme => $subtheme)
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
				$lang = Tools::strtolower($_POST['lang']);
				if (!Validate::isLanguageIsoCode($lang))
					die(Tools::displayError());
				if (!$modules = scandir(_PS_MODULE_DIR_))
					$this->displayWarning(Tools::displayError('There are no modules in your copy of PrestaShop. Use the Modules tab to activate them or go to our Website to download additional Modules.'));
				else
				{
					foreach ($modules AS $module)
					if ($module{0} != '.' AND is_dir(_PS_MODULE_DIR_.$module))
					{
						$filename = _PS_MODULE_DIR_.$module.'/'.$lang.'.php';
						$content = scandir(_PS_MODULE_DIR_.$module);
						foreach ($content as $cont)
							if ($cont{0} != '.' AND $cont != 'img' AND $cont != 'mails' AND $cont != 'js' AND is_dir(_PS_MODULE_DIR_.$module.'/'.$cont))
								if ($files = @scandir(_PS_MODULE_DIR_.$module.'/'.$cont))
									$this->findAndWriteTranslationsIntoFile($filename, $files, 'prestashop', $module, _PS_MODULE_DIR_.$module.'/'.$cont.'/');
						if ($files = @scandir(_PS_MODULE_DIR_.$module.'/'))
							$this->findAndWriteTranslationsIntoFile($filename, $files, 'prestashop', $module);
					}
				}
				/* Search language tags (eg {l s='to translate'}) */
				if ($themes = scandir(_PS_ALL_THEMES_DIR_))
					foreach ($themes AS $theme)
						if ($theme{0} != '.' AND is_dir(_PS_ALL_THEMES_DIR_.$theme) AND file_exists(_PS_ALL_THEMES_DIR_.$theme.'/modules/'))
						{
							if ($modules = scandir(_PS_ALL_THEMES_DIR_.$theme.'/modules/'))
								foreach ($modules AS $module)
									if ($module{0} != '.' AND is_dir(_PS_ALL_THEMES_DIR_.$theme.'/modules/'.$module) AND $files = scandir(_PS_ALL_THEMES_DIR_.$theme.'/modules/'.$module.'/'))
										$this->findAndWriteTranslationsIntoFile(_PS_ALL_THEMES_DIR_.$theme.'/modules/'.$module.'/'.$lang.'.php', $files, $theme, $module);
						}
				Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
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
			$this->l('Here you can modify translations for every text input on PrestaShop.').'<br />'.
			$this->l('First, select a section (such as Back Office or Modules), then click the flag representing the language you want to edit.').'<br /><br />
			<form method="get" action="index.php" id="typeTranslationForm">
				<input type="hidden" name="tab" value="AdminTranslations" />
				<input type="hidden" name="lang" id="translation_lang" value="0" />
				<select name="type" style="float:left; margin-right:10px;">';
			foreach ($translations as $key => $translation)
				echo '<option value="'.$key.'">'.$translation.'&nbsp;</option>';
			echo '</select>';
			foreach ($languages as $language)
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
					<div style="font-weight:bold; float:left;">'.$this->l('Language you want to add:').'
						<select id="iso_import_language" name="iso_import_language">';
			// Get all iso code available
			$lang_packs = file_get_contents('http://www.prestashop.com/rss/lang_exists.php');
			if ($lang_packs)
			{
				$lang_packs = unserialize($lang_packs);
				foreach($lang_packs as $lang_pack)
					if (!Language::isInstalled($lang_pack['iso_code']))
						echo '<option value="'.$lang_pack['iso_code'].'">'.$lang_pack['name'].'</option>';
			}
			else
				echo '		<option value="0">'.$this->l('Cannot connect to prestashop.com').'</option>';
			echo 		'</select>
					</div>
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
						$this->l('If the name format is: isocode.gzip (eg fr.gzip) and the language corresponding to this package does not exist, it will automatically create.').'<br />'.
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
				foreach ($languages as $language)
					echo '<option value="'.$language['iso_code'].'">'.$language['name'].'</option>';
				echo '
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="theme" style="margin-top:10px;">';
				$themes = self::getThemesList();
				foreach ($themes as $theme)
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
						foreach ($themes as $theme)
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
						foreach ($themes as $theme)
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

	function fileExists($dir, $file, $var)
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

	function displayToggleButton($closed = false)
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
	
	function displayAutoTranslate()
	{
		echo '
		<input type="button" class="button" onclick="translateAll();" value="'.$this->l('Translate with Google').'" />
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("language", "1");
			function translateAll() {
				$.each($(\'input[type="text"]\'), function() {
					var tdinput = $(this);
					if (tdinput.attr("value") == "" && tdinput.parent("td").prev().html()) {
						google.language.translate(tdinput.parent("td").prev().html(), "en", "'.Tools::htmlentitiesUTF8(Tools::getValue('lang')).'", function(result) {
							if (!result.error)
								tdinput.val(result.translation);
						});
					}
				});
				$.each($("textarea"), function() {
					var tdtextarea = $(this);
					if (tdtextarea.html() == "" && tdtextarea.parent("td").prev().html()) {
						google.language.translate(tdtextarea.parent("td").prev().html(), "en", "'.Tools::htmlentitiesUTF8(Tools::getValue('lang')).'", function(result) {
							if (!result.error)
								tdtextarea.html(result.translation);
						});
					}
				});
			}
		</script>';
	}

	function displayFormfront($lang)
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
				foreach($matches[1] as $key)
				{
					$key2 = $template.'_'.md5($key);
					$newLang[$key] = (key_exists($key2, $_LANG)) ? html_entity_decode($_LANG[$key2], ENT_COMPAT, 'UTF-8') : '';
				}
				$files[$template] = $newLang;
				$count += sizeof($newLang);
			}

		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>
		'.$this->l('Total expressions').' : <b>'.$count.'</b>. '.$this->l('Click the fieldset title to expand or close the fieldset.').'.<br /><br />
		<form method="post" action="'.$currentIndex.'&submitTranslationsFront=1&token='.$this->token.'" class="form">';
		$this->displayToggleButton(sizeof($_LANG) >= $count);
		$this->displayAutoTranslate();
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsFront" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		foreach ($files as $k => $newLang)
			if (sizeof($newLang))
			{
				$countValues = array_count_values($newLang);
				$empty = isset($countValues['']) ? $countValues[''] : 0;
			 	echo '
				<fieldset><legend style="cursor : pointer" onclick="openCloseLayer(\''.$k.'\')">'.$k.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
					<div name="front_div" id="'.$k.'" style="display: '.($empty ? 'block' : 'none').';">
						<table cellpadding="2">';
				foreach ($newLang as $key => $value)
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

	function displayFormback($lang)
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
				foreach ($matches[1] as $key)
					$tabsArray[$tab][$key] = stripslashes(key_exists($tab.md5($key), $_LANGADM) ? html_entity_decode($_LANGADM[$tab.md5($key)], ENT_COMPAT, 'UTF-8') : '');
				$count += isset($tabsArray[$tab]) ? sizeof($tabsArray[$tab]) : 0;
			}
		foreach (array('header.inc', 'footer.inc', 'index', 'login', 'password') as $tab)
		{
			$tab = PS_ADMIN_DIR.'/'.$tab.'.php';
			$fd = fopen($tab, 'r');
			$content = fread($fd, filesize($tab));
			fclose($fd);
			$regex = '/translate\(\''._PS_TRANS_PATTERN_.'\'\)/U';
			preg_match_all($regex, $content, $matches);
			foreach ($matches[1] as $key)
				$tabsArray['index'][$key] = stripslashes(key_exists('index'.md5($key), $_LANGADM) ? html_entity_decode($_LANGADM['index'.md5($key)], ENT_COMPAT, 'UTF-8') : '');
			$count += isset($tabsArray['index']) ? sizeof($tabsArray['index']) : 0;
		}

		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>
		'.$this->l('Expressions to translate').' : <b>'.$count.'</b>. '.$this->l('Click on the titles to open fieldsets').'.<br /><br />
		<form method="post" action="'.$currentIndex.'&submitTranslationsBack=1&token='.$this->token.'" class="form">';
		$this->displayToggleButton();
		$this->displayAutoTranslate();
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsBack" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		foreach ($tabsArray as $k => $newLang)
			if (sizeof($newLang))
			{
				$countValues = array_count_values($newLang);
				$empty = isset($countValues['']) ? $countValues[''] : 0;
			 	echo '
				<fieldset><legend style="cursor : pointer" onclick="openCloseLayer(\''.$k.'\')">'.$k.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
					<div name="back_div" id="'.$k.'" style="display: '.($empty ? 'block' : 'none').';">
						<table cellpadding="2">';
				foreach ($newLang as $key => $value)
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

	function displayFormerrors($lang)
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
					foreach($matches[1] as $key)
						$stringToTranslate[$key] = (key_exists(md5($key), $_ERRORS)) ? html_entity_decode($_ERRORS[md5($key)], ENT_COMPAT, 'UTF-8') : '';
				}
		$irow = 0;
		echo '<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>'.$this->l('Errors to translate').' : <b>'.sizeof($stringToTranslate).'</b><br /><br />
		<form method="post" action="'.$currentIndex.'&submitTranslationsErrors=1&lang='.$lang.'&token='.$this->token.'" class="form">
		<input type="submit" name="submitTranslationsErrors" value="'.$this->l('Update translations').'" class="button" /><br /><br />
		<table cellpadding="0" cellspacing="0" class="table">';
		ksort($stringToTranslate);
		foreach ($stringToTranslate as $key => $value)
			echo '<tr '.(empty($value) ? 'style="background-color:#FBB"' : (++$irow % 2 ? 'class="alt_row"' : '')).'><td>'.stripslashes($key).'</td><td style="width: 430px">= <input type="text" name="'.md5($key).'" value="'.preg_replace('/"/', '&quot;', stripslashes($value)).'" style="width: 400px"></td></tr>';
		echo '</table><br /><input type="submit" name="submitTranslationsErrors" value="'.$this->l('Update translations').'" class="button" /></form>';
	}

	function displayFormfields($lang)
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
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>
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
			<fieldset><legend style="cursor : pointer" onclick="openCloseLayer(\''.$className.'\')">'.$className.' - <font color="blue">'.($toTranslate + $translated).'</font> '.$this->l('fields').' (<font color="red">'.$toTranslate.'</font>)</legend>
			<div name="fields_div" id="'.$className.'" style="display: '.($toTranslate ? 'block' : 'none').';">
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

	function displayFormmails($lang, $noDisplay = false)
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
		foreach ($langs as &$lang_item)
			$langIds[] = $lang_item['iso_code'];
		foreach (scandir(_PS_MAIL_DIR_) as $mail_lang_dir)
			if (in_array($mail_lang_dir, $langIds))
				foreach (scandir(_PS_MAIL_DIR_.$mail_lang_dir) as $tpl_file)
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
		foreach (scandir(_PS_MODULE_DIR_) as $module_dir)
			if ($module_dir != '.svn' && $module_dir != '.' && $module_dir != '..' && file_exists(_PS_MODULE_DIR_.$module_dir.'/mails'))
				foreach (scandir(_PS_MODULE_DIR_.$module_dir.'/mails') as $mail_lang_dir)
				{
					if (in_array($mail_lang_dir, $langIds))
					{
						foreach (scandir(_PS_MODULE_DIR_.$module_dir.'/mails/'.$mail_lang_dir) as $tpl_file)
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
						if ($mail_lang_dir == $lang)
						{
							foreach (scandir(_PS_MODULE_DIR_.$module_dir.'/mails/en') as $tpl_file)
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
		foreach (scandir(_PS_ALL_THEMES_DIR_) as $theme_dir)
		{
			if ($theme_dir != '.svn' && $theme_dir != '.' && $theme_dir != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails'))
			{
				if (in_array($mail_lang_dir, $langIds))
					foreach (scandir(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$mail_lang_dir) as $tpl_file)
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
				foreach (scandir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules') as $theme_name_module)
					if ($theme_name_module != '.svn' && $theme_name_module != '.' && $theme_name_module != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$theme_name_module.'/mails'))
						if (in_array($mail_lang_dir, $langIds))
							foreach (scandir(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$theme_name_module.'/mails/'.$mail_lang_dir) as $tpl_file)
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
			foreach ($mailTpls as $key => $tpl_file)
			{
				if (Tools::strlen($tpl_file[$lang]) == 0)
					$empty++;
			}

			foreach ($moduleMailTpls as $key => $tpl_file)
				foreach ($tpl_file AS $key2 => $tpl_file2)
				{
					if (Tools::strlen($tpl_file[$key2][$lang]) == 0)
						$empty++;
				}

			return array('total' => count($mailTpls)+count($moduleMailTpls,COUNT_RECURSIVE), 'empty' => $empty);
		}

		// TinyMCE
		$iso = Language::getIsoById((int)($cookie->id_lang));
		echo ' <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
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
						height: "auto",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
						
					});

		</script>
		';
		$mylang = new Language(Language::getIdByIso($lang));
			echo '<!--'.$this->l('Language').'-->';
		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>'.$this->l('Click on the titles to open fieldsets').'.<br /><br />';

		// display form
		echo '
		<form method="post" action="'.$currentIndex.'&token='.$this->token.'" class="form">';
		$this->displayToggleButton();
		echo '<input type="submit" name="submitTranslationsMails" value="'.$this->l('Update translations').'" class="button" /><br/><br/>';

		//count nb core emails
		$nbr = 0;
		foreach ($mailTpls as $mailTplName => $mailTpl)
			if ((strripos($mailTplName, '.html') AND isset($subjectMail[substr($mailTplName, 0, -5)]))
			OR (strripos($mailTplName, '.txt') AND isset($subjectMail[substr($mailTplName, 0, -4)])))
				$nbr++;

		echo'<fieldset><legend style="cursor : pointer" onclick="openCloseLayer(\'core\')">Core e-mails - <font color="blue">'.$nbr.'</font> templates for '.$mylang->name.':</legend><div name="mails_div" id="core">';

		//core emails
		foreach ($mailTpls as $mailTplName => $mailTpl)
		{
			if ((strripos($mailTplName, '.html') AND isset($subjectMail[substr($mailTplName, 0, -5)]))
			OR (strripos($mailTplName, '.txt') AND isset($subjectMail[substr($mailTplName, 0, -4)])))
			{
				echo '<div style="clear:both;"><br/><label>'.$mailTplName.'</label><br/><br/>';
				echo '<div class="mail-form">';
				if (strripos($mailTplName, '.html'))
				{
					echo '<br/><br/>

					<div class="mail-label">
					<div class="label-subject">'.$this->l('Subject').' :&nbsp</div>
					<table>
						<td style="width:0;white-space: nowrap;"><label>'.$subjectMail[substr($mailTplName, 0, -5)].'&nbsp=&nbsp</label></td>
						<td style="width: 100%;"><input type="text" name="subject[mails]['.$subjectMail[substr($mailTplName, 0, -5)].']" value="'.(isset($subjectMailContent[$subjectMail[substr($mailTplName, 0, -5)]]) ? $subjectMailContent[$subjectMail[substr($mailTplName, 0, -5)]] : '').'" style="width:100%;"/></td>
					</table></div>';

					echo '<br/><br/><div><iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'mails/'.$lang.'/'.$mailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>
					<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); tinyMCEInit($(this).parent().next().show()); return false;" class="button">Edit this mail template</a></div>
					<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[html]['.$mailTplName.']">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
				}
				else
					echo '<br/><div style="clear:both;"><textarea class="rte mailrte" cols="80" rows="30" name="mail[txt]['.$mailTplName.']" style="width:560px;margin=0;">'.htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8').'</textarea></div><br/>';

				echo '</div></div>';
			}
		}
		echo '</div></fieldset>';

		// module mails
		echo '<br/><div id="modules">';
		foreach ($moduleMailTpls as $key33 => $moduleMailTpls2)
		{
			echo '<fieldset><br/>
			<legend style="cursor : pointer" onclick="openCloseLayer(\''.$key33.'\')">Module "'.$key33.'" - <font color="blue">'.(count($moduleMailTpls2,COUNT_RECURSIVE)/2).'</font> templates for '.$mylang->name.':</legend><div name="mails_div" id="'.$key33.'">';
			foreach ($moduleMailTpls2 as $mailTplName => $mailTpl)
			{
				if ((strripos($mailTplName, '.html') AND isset($subjectMail[substr($mailTplName, 0, -5)]))
				OR (strripos($mailTplName, '.txt') AND isset($subjectMail[substr($mailTplName, 0, -4)])))
				{
					echo '<br/><br/><div><label>'.$mailTplName.'</label><br/><div class="mail-form">';
					if (strlen($mailTplName) > 30)
						echo '<br/>';
					if (strripos($mailTplName, '.html'))
					{
						echo '<br/><br/>

						<div class="mail-label" style="margin-bottom:0;">
						<div class="label-subject">'.$this->l('Subject').' :&nbsp</div>
						<table>
							<td style="width:0;white-space: nowrap;"><label>'.$subjectMail[substr($mailTplName, 0, -5)].'&nbsp=&nbsp</label></td>
							<td style="width: 100%;"><input type="text" name="subject[mails]['.$subjectMail[substr($mailTplName, 0, -5)].']" value="'.(isset($subjectModuleMailContent[$subjectMail[substr($mailTplName, 0, -5)]]) ? $subjectModuleMailContent[$subjectMail[substr($mailTplName, 0, -5)]] : '').'" style="width:100%;"/></td>
						</table></div>
						<br/><div>';

						if (file_exists(_PS_MODULE_DIR_.$key33.'/mails/'.$lang.'/'.$mailTplName))
							echo '<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'modules/'.$key33.'/mails/'.$lang.'/'.$mailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>';
						else
							echo 'This version is currently not translated. Please click the \'Edit this mail template\' button to create a new template.';

						echo '<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); tinyMCEInit($(this).parent().next().show()); return false;" class="button">Edit this mail template</a></div>
						<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[modules]['.$key33.'][html]['.$mailTplName.']">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
					}
					else
						echo '<div style="clear:both;"><textarea class="rte mailrte" cols="80" rows="30" name="mail[modules]['.$key33.'][txt]['.$mailTplName.']" style="width:560px;margin=0;">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div><br/>';
					echo '</div></div>';
				}
				else
				{
					echo '<br/><br/><div><label>'.$mailTplName.'</label><br/><div class="mail-form">';
					echo '<div style="clear:both;"><textarea class="rte mailrte" cols="80" rows="30" name="mail[modules]['.$key33.'][txt]['.$mailTplName.']">'.(isset($mailTpl[$lang]) ? htmlentities(stripslashes($mailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div><br/></div></div>';
				}
			}
			echo '</div></fieldset><br />';
		}
		echo '</div><br />';

		// mail theme
		foreach (scandir(_PS_ALL_THEMES_DIR_) as $theme_dir)
		{
			if ($theme_dir != '.svn' && $theme_dir != '.' && $theme_dir != '..' && is_dir(_PS_ALL_THEMES_DIR_.$theme_dir)
				&& isset($themeMailTpls[$theme_dir]))
			{
				// count nb mail in mailtheme
				$nb = 0;
				foreach ($themeMailTpls[$theme_dir] as $key2 => $tab)
					if ((strripos($key2, '.html') AND isset($subjectMail[substr($key2, 0, -5)]))
					OR (strripos($key2, '.txt') AND isset($subjectMail[substr($key2, 0, -4)])))
						$nb++;

				echo '<fieldset><legend style="cursor : pointer" onclick="openCloseLayer(\''.$theme_dir.'\')">Theme : '.$theme_dir.' - <font color="blue">'.$nb.'</font> templates for '.$mylang->name.' :</legend><div name="mails_div" id="'.$theme_dir.'">';

				// core mail theme
				foreach ($themeMailTpls[$theme_dir] as $themeMailTplName => $themeMailTpl)
				{
					if ((strripos($themeMailTplName, '.html') AND isset($subjectMail[substr($themeMailTplName, 0, -5)]))
					OR (strripos($themeMailTplName, '.txt') AND isset($subjectMail[substr($themeMailTplName, 0, -4)])))
					{
						echo '<br/><div style="clear:both;"><label>'.$themeMailTplName.'</label><div class="mail-form">';

						if (strripos($themeMailTplName, '.html'))
						{
							echo '<br/><br/>

							<div class="mail-label">
							<div class="label-subject">'.$this->l('Subject').' :&nbsp</div>
							<table>
							<td style="width:0;white-space: nowrap;"><label>'.$subjectMail[substr($themeMailTplName, 0, -5)].' =&nbsp</label></td>
							<td style="width: 100%;"><input type="text" name="subject[themes]['.$theme_dir.']['.$subjectMail[substr($themeMailTplName, 0, -5)].']" value="'.(isset($subjectThemeMailContent[$theme_dir][$subjectMail[substr($themeMailTplName, 0, -5)]]) ? $subjectThemeMailContent[$theme_dir][$subjectMail[substr($themeMailTplName, 0, -5)]] : '').'" style="width:100%;"/></td>
							</table></div>

							<br /><br /><div>';

							if (file_exists(_PS_ALL_THEMES_DIR_.$theme_dir.'/mails/'.$lang.'/'.$themeMailTplName))
								echo '<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'themes/'.$theme_dir.'/mails/'.$lang.'/'.$themeMailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>';
							else
								echo 'This version is currently not translated. Please click the \'Edit this mail template\' button to create a new template.';

							echo '<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); 	tinyMCEInit($(this).parent().next().show()); return false;" class="button">Edit this mail template</a></div>
							<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[themes]['.$theme_dir.'][html]['.$themeMailTplName.']">'.(isset($themeMailTpl[$lang]) ? htmlentities(stripslashes($themeMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
						}
						else
							echo '<div style="clear:both;"><textarea class="rte mailrte" cols="80" rows="30" name="mail[themes]['.$theme_dir.'][txt]['.$themeMailTplName.']" style="width:560px;margin=0;">'.(isset($themeMailTpl[$lang]) ? htmlentities(stripslashes($themeMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div><br/>';
						echo '</div></div><br/>';
					}
				}

				// module mail theme
				echo '<span class="style-themeModuleMail"
				onclick="openCloseLayer(\'div'.$theme_dir.'\')">Modules - <font color="blue">'.count($themeModuleMailTpls[$theme_dir]).'</font> templates for '.$mylang->name.' :</span>';

				echo '<div style="margin: 0;
				padding: 1em;
				border: 1px solid #DFD5C3;
				background: #FFFFF0;"
				id="div'.$theme_dir.'">
				<br/>';

				foreach ($themeModuleMailTpls[$theme_dir] as $themeModuleName => $themeModule)
				{
					echo '<span class="style-themeModuleName"
					onclick="openCloseLayer(\''.$theme_dir.$themeModuleName.'\')">'.$themeModuleName.' - <font color="blue">'.count($themeModule).'</font> templates for '.$mylang->name.' :</span>';
					echo '<div style="margin: 0;
					padding: 1em;
					border: 1px solid #DFD5C3;
					background: #FFFFF0;"
					id="'.$theme_dir.$themeModuleName.'">';
					foreach ($themeModule as $themeModuleMailTplName => $themeModuleMailTpl)
					{
						if ((strripos($themeModuleMailTplName, '.html') AND isset($subjectMail[substr($themeModuleMailTplName, 0, -5)]))
						OR (strripos($themeModuleMailTplName, '.txt') AND isset($subjectMail[substr($themeModuleMailTplName, 0, -4)])))
						{
							echo '<div style="clear:both;"><label>'.$themeModuleMailTplName.'</label><div class="mail-form" style="margin-top:0;">';
							if (strripos($themeModuleMailTplName, '.html'))
							{
								echo '<br/><br/>
								<div class="mail-label" style="margin-left:-20px;">
								<div class="label-subject" style="margin-left:-30px;">'.$this->l('Subject').' :&nbsp</div>
								<table>
								<td style="width:0;white-space: nowrap;"><label>'.$subjectMail[substr($themeModuleMailTplName, 0, -5)].' =&nbsp</label></td>
								<td style="width: 100%;"><input type="text" name="subject[themes]['.$theme_dir.']['.$subjectMail[substr($themeModuleMailTplName, 0, -5)].']" value="'.(isset($subjectThemeModuleMailContent[$theme_dir][$themeModuleName][$subjectMail[substr($themeModuleMailTplName, 0, -5)]]) ? $subjectThemeModuleMailContent[$theme_dir][$themeModuleName][$subjectMail[substr($themeModuleMailTplName, 0, -5)]] : '').'" style="width:100%;"/></td>
								</table></div>
								<br /><br /><div>';

								if (file_exists(_PS_ALL_THEMES_DIR_.$theme_dir.'/modules/'.$themeModuleName.'/mails/'.$lang.'/'.$themeModuleMailTplName))
									echo '<iframe style="background:white;border:1px solid #DFD5C3;" border="0" src ="'.__PS_BASE_URI__.'themes/'.$theme_dir.'/modules/'.$themeModuleName.'/mails/'.$lang.'/'.$themeModuleMailTplName.'?'.(rand(0,1000000000000)).'" width="565" height="497"></iframe>';
								else
									echo 'This version is currently not translated. Please click the \'Edit this mail template\' button to create a new template.';

								echo '<a style="display:block;margin-top:5px;width:130px;" href="#" onclick="$(this).parent().hide(); tinyMCEInit($(this).parent().next().show()); return false;" class="button">Edit this mail template</a></div>
								<textarea style="display:none;" class="rte mailrte" cols="80" rows="30" name="mail[themes_module]['.$theme_dir.']['.$themeModuleName.'][html]['.$themeModuleMailTplName.']">'.(isset($themeModuleMailTpl[$lang]) ? htmlentities(stripslashes($themeModuleMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea>';
							}
							else
								echo '<div style="clear:both;"><textarea class="rte mailrte" cols="80" rows="30" name="mail[themes]['.$theme_dir.'][txt]['.$themeModuleMailTplName.']" style="width:560px;margin=0;">'.(isset($themeModuleMailTpl[$lang]) ? htmlentities(stripslashes($themeModuleMailTpl[$lang]), ENT_COMPAT, 'UTF-8') : '').'</textarea></div><br/>';
							echo '</div></div><br/>';
						}
					}
					echo '</div>';
				}
				echo '</div></div></fieldset><br/>';
			}
		}
		echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsMails" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
		echo '</form>';
	}

	private function getSubjectMail($directory, $subjectMail)
	{
		foreach (scandir($directory) as $filename)
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

			foreach($sub as $key => $value)
				fwrite($fd, '$_'.$tab.'[\''.pSQL($key, true).'\'] = \''.pSQL($value, true).'\';'."\n");
			fwrite($fd, "\n?>");
			fclose($fd);

		}
		else
			die('Cannot write language file');
	}

	function displayFormModules($lang)
	{
		global $currentIndex, $_MODULES;

		if (!file_exists(_PS_MODULE_DIR_))
			die($this->displayWarning(Tools::displayError('Fatal error: Module directory is not here anymore ').'('._PS_MODULE_DIR_.')'));
		if (!is_writable(_PS_MODULE_DIR_))
			$this->displayWarning(Tools::displayError('The module directory must be writable'));
		if (!$modules = scandir(_PS_MODULE_DIR_))
			$this->displayWarning(Tools::displayError('There are no modules in your copy of PrestaShop. Use the Modules tab to activate them or go to our Website to download additional Modules.'));
		else
		{
			$allfiles = array();
			$count = 0;

			foreach ($modules AS $module)
				if ($module{0} != '.' AND is_dir(_PS_MODULE_DIR_.$module))
				{
					@include_once(_PS_MODULE_DIR_.$module.'/'.$lang.'.php');
					self::getModuleTranslations();

					$content = scandir(_PS_MODULE_DIR_.$module);
					foreach ($content as $cont)
						if ($cont{0} != '.' AND $cont != 'img' AND $cont != 'mails' AND $cont != 'js' AND is_dir(_PS_MODULE_DIR_.$module.'/'.$cont))
							if ($files = @scandir(_PS_MODULE_DIR_.$module.'/'.$cont))
								$count += $this->findAndFillTranslations($files, $allfiles, 'prestashop', $module, 	_PS_MODULE_DIR_.$module.'/'.$cont.'/');

					if ($files = @scandir(_PS_MODULE_DIR_.$module.'/'))
						$count += $this->findAndFillTranslations($files, $allfiles, 'prestashop', $module);
				}

			if ($themes = scandir(_PS_ALL_THEMES_DIR_))
				foreach ($themes AS $theme)
					if ($theme{0} != '.' AND is_dir(_PS_ALL_THEMES_DIR_.$theme) AND file_exists(_PS_ALL_THEMES_DIR_.$theme.'/modules/'))
						{
							$modules = scandir(_PS_ALL_THEMES_DIR_.$theme.'/modules/');
							if ($modules)
								foreach ($modules AS $module)
									if ($module{0} != '.' AND is_dir(_PS_ALL_THEMES_DIR_.$theme.'/modules/'.$module))
									{
										@include_once(_PS_ALL_THEMES_DIR_.$theme.'/modules/'.$module.'/'.$lang.'.php');
										self::getModuleTranslations();

										$files = scandir(_PS_ALL_THEMES_DIR_.$theme.'/modules/'.$module.'/');
										if ($files)
											$count += $this->findAndFillTranslations($files, $allfiles, $theme, $module);
									}
						}

			echo '
			<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>
			'.$this->l('Total expressions').' : <b>'.$count.'</b>. '.$this->l('Click the fieldset title to expand or close the fieldset.').'.<br /><br />
			<form method="post" action="'.$currentIndex.'&submitTranslationsModules=1&token='.$this->token.'" class="form">';
			$this->displayToggleButton();
			$this->displayAutoTranslate();
			echo '<input type="hidden" name="lang" value="'.$lang.'" /><input type="submit" name="submitTranslationsModules" value="'.$this->l('Update translations').'" class="button" /><br /><br />';
			foreach ($allfiles AS $theme_name => $theme)
				foreach ($theme AS $module_name => $module)
					foreach ($module AS $template_name => $newLang)
						if (sizeof($newLang))
						{
							$countValues = array_count_values($newLang);
							$empty = isset($countValues['']) ? $countValues[''] : 0;
							echo '
							<fieldset><legend style="cursor : pointer" onclick="openCloseLayer(\''.$theme_name.'_'.$module_name.'_'.$template_name.'\')">'.$theme_name.' - '.$template_name.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
								<div name="modules_div" id="'.$theme_name.'_'.$module_name.'_'.$template_name.'" style="display: '.($empty ? 'block' : 'none').';">
									<table cellpadding="2">';
							foreach ($newLang as $key => $value)
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
			echo '<br /><input type="submit" name="submitTranslationsModules" value="'.$this->l('Update translations').'" class="button" /></form>';

		}
	}

	function displayFormPDF()
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
		foreach($matches[1] as $key)
			$tabsArray[$tab][$key] = stripslashes(key_exists($tab.md5(addslashes($key)), $_LANGPDF) ? html_entity_decode($_LANGPDF[$tab.md5(addslashes($key))], ENT_COMPAT, 'UTF-8') : '');
		$count += isset($tabsArray[$tab]) ? sizeof($tabsArray[$tab]) : 0;
		$closed = sizeof($_LANGPDF) >= $count;

		echo '
		<h2>'.$this->l('Language').' : '.Tools::strtoupper($lang).'</h2>
		'.$this->l('Expressions to translate').' : <b>'.$count.'</b>. '.$this->l('Click on the titles to open fieldsets').'.<br /><br />
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
		foreach ($tabsArray as $k => $newLang)
			if (sizeof($newLang))
			{
				$countValues = array_count_values($newLang);
				$empty = isset($countValues['']) ? $countValues[''] : 0;
			 	echo '
				<fieldset style="width: 700px"><legend style="cursor : pointer" onclick="openCloseLayer(\''.$k.'\')">'.$k.' - <font color="blue">'.sizeof($newLang).'</font> '.$this->l('expressions').' (<font color="red">'.$empty.'</font>)</legend>
					<div name="pdf_div" id="'.$k.'" style="display: '.($empty ? 'block' : 'none').';">
						<table cellpadding="2">';
				foreach ($newLang as $key => $value)
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

