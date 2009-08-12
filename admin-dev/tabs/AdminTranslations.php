<?php

/**
  * Translations tab for admin panel, AdminTranslations.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
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
				Tools::redirectAdmin($currentIndex.'&conf=15&token='.$this->token);
			$this->_errors[] = Tools::displayError('archive cannot be extracted');
		}
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
		elseif (Tools::isSubmit('submitTranslationsFront'))
		{
			if ($this->tabAccess['edit'] === '1')
				$this->writeTranslationFile('Front', _PS_THEME_DIR_.'lang/'.Tools::strtolower(Tools::getValue('lang')).'.php');
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsPDF'))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->writeTranslationFile('PDF', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/pdf.php', 'PDF');
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsBack'))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->writeTranslationFile('Back', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/admin.php', 'ADM');
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsErrors'))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->writeTranslationFile('Errors', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/errors.php', false, 'ERRORS');
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitTranslationsFields'))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->writeTranslationFile('Fields', _PS_TRANSLATIONS_DIR_.Tools::strtolower(Tools::getValue('lang')).'/fields.php', false, 'FIELDS');
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');

		}
		elseif (Tools::isSubmit('submitTranslationsModules'))
		{
		if ($this->tabAccess['edit'] === '1')
			{
				$lang = Tools::strtolower($_POST['lang']);
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
						'errors' => $this->l('Errors messages translations'),
						'fields' => $this->l('Fields name translations'),
						'modules' => $this->l('Modules translations'),
						'pdf' => $this->l('PDF translations'),
						);

		if ($type = Tools::getValue('type'))
			$this->{'displayForm'.$type}(Tools::strtolower(Tools::getValue('lang')));
		else
		{
			$languages = Language::getLanguages();
			echo '<fieldset class="width2"><legend><img src="../img/admin/translation.gif" />'.$this->l('Modify translations').'</legend>'.
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
			<br /><br /><h2>'.$this->l('Translation exchange').'</h2>
			<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
				<fieldset class="width2"><legend><img src="../img/admin/import.gif" />'.$this->l('Import a language pack').'</legend>
					<p>'.$this->l('Import data from file (language pack).').'<br />'.
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
				</fieldset>
			</form>
			<br /><br />
			<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
				<fieldset class="width2"><legend><img src="../img/admin/export.gif" />'.$this->l('Export a language').'</legend>
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
				<fieldset class="width2"><legend><img src="../img/admin/copy_files.gif" />'.$this->l('Copy').'</legend>
					<p>'.$this->l('Copies data from one language to another.').'<br />'.
					$this->l('Be careful, as it will replace all existing data for the destination language!').'<br />'.
					$this->l('If necessary').', <b><a href="index.php?tab=AdminLanguages&addlang&token='.Tools::getAdminToken('AdminLanguages'.intval(Tab::getIdFromClassName('AdminLanguages')).intval($cookie->id_employee)).'">'.$this->l('first create a new language').'</a></b>.</p>
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

	function displayToggleButton()
	{
		echo '
		<script type="text/javascript">
			var openAll = \''.html_entity_decode($this->l('Expand all fieldsets'), ENT_NOQUOTES, 'UTF-8').'\';
			var closeAll = \''.html_entity_decode($this->l('Close all fieldsets'), ENT_NOQUOTES, 'UTF-8').'\';
		</script>
		<input type="button" class="button" id="buttonall" onclick="openCloseAllDiv(\''.$_GET['type'].'_div\', this.value == openAll); toggleElemValue(this.id, openAll, closeAll);" />
		<script type="text/javascript">toggleElemValue(\'buttonall\', openAll, closeAll);</script>';
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
		$this->displayToggleButton();
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
						echo '<textarea rows="'.intval(strlen($key) / TEXTAREA_SIZED).'" style="width: 450px" name="'.$k.'_'.md5($key).'">'.stripslashes(preg_replace('/"/', '\&quot;', stripslashes($value))).'</textarea></td></tr>';
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
		foreach (array('header.inc', 'index', 'login', 'password') as $tab)
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
						echo '<textarea rows="'.intval(strlen($key) / TEXTAREA_SIZED).'" style="width: 450px" name="'.$k.md5($key).'">'.stripslashes(preg_replace('/"/', '\&quot;', $value)).'</textarea></td></tr>';
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
					preg_match_all('/Tools::displayError\(\''._PS_TRANS_PATTERN_.'\'(, true)?\)/U', fread(fopen($fn, 'r'), filesize($fn)), $matches);
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
			<fieldset class="width3"><legend style="cursor : pointer" onclick="openCloseLayer(\''.$className.'\')">'.$className.' - <font color="blue">'.($toTranslate + $translated).'</font> '.$this->l('fields').' (<font color="red">'.$toTranslate.'</font>)</legend>
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
									echo '<textarea rows="'.intval(strlen($key) / TEXTAREA_SIZED).'" style="width: 450px" name="'.md5($module_name.'_'.$theme_name.'_'.$template_name.'_'.md5($key)).'">'.stripslashes(preg_replace('/"/', '\&quot;', stripslashes($value))).'</textarea></td></tr>';
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
					toggleElemValue(\'buttonall\', openAll, closeAll);
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
?>
