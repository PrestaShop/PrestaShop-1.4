<?php

/**
  * Admin panel functions, functions.inc.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

require_once(dirname(__FILE__).'/../images.inc.php'); 

function bindDatepicker($id, $time)
{
	if ($time)
	echo '
		var dateObj = new Date();
		var hours = dateObj.getHours();
		var mins = dateObj.getMinutes();
		var secs = dateObj.getSeconds();
		if (hours < 10) { hours = "0" + hours; }
		if (mins < 10) { mins = "0" + mins; }
		if (secs < 10) { secs = "0" + secs; }
		var time = " "+hours+":"+mins+":"+secs;';

	echo '
	$(function() {
		$("#'.$id.'").datepicker({
			prevText:"",
			nextText:"",
			dateFormat:"yy-mm-dd"'.($time ? '+time' : '').'});
	});';
}

// id can be a identifier or an array of identifiers
function includeDatepicker($id, $time = false)
{
	global $cookie;
	echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/datepicker/jquery-ui-personalized-1.6rc4.packed.js"></script>';
	$iso = Db::getInstance()->getValue('SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE `id_lang` = '.intval($cookie->id_lang));
	if ($iso != 'en')
		echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/datepicker/ui/i18n/ui.datepicker-'.$iso.'.js"></script>';
	echo '<script type="text/javascript">';
		if (is_array($id))
			foreach ($id as $id2)
				bindDatepicker($id2, $time);
		else
			bindDatepicker($id, $time);
	echo '</script>';
}

/**
  * Generate a new settings file, only transmitted parameters are updated
  *
  * @param string $baseUri Base URI
  * @param string $theme Theme name (eg. default)
  * @param array $arrayDB Parameters in order to connect to database
  */
function	rewriteSettingsFile($baseUrls = NULL, $theme = NULL, $arrayDB = NULL)
{
 	$defines = array();
	$defines['__PS_BASE_URI__'] = ($baseUrls AND $baseUrls['__PS_BASE_URI__']) ? $baseUrls['__PS_BASE_URI__'] : __PS_BASE_URI__;
	$defines['_THEMES_DIR_'] = ($baseUrls AND $baseUrls['_THEMES_DIR_']) ? $baseUrls['_THEMES_DIR_'] : _THEMES_DIR_;
	$defines['_PS_IMG_'] = ($baseUrls AND $baseUrls['_PS_IMG_']) ? $baseUrls['_PS_IMG_'] : _PS_IMG_;
	$defines['_PS_JS_DIR_'] = ($baseUrls AND $baseUrls['_PS_JS_DIR_']) ? $baseUrls['_PS_JS_DIR_'] : _PS_JS_DIR_;
	$defines['_PS_CSS_DIR_'] = ($baseUrls AND $baseUrls['_PS_CSS_DIR_']) ? $baseUrls['_PS_CSS_DIR_'] : _PS_CSS_DIR_;	
	$defines['_THEME_NAME_'] = $theme ? $theme : _THEME_NAME_;
	$defines['_DB_NAME_'] = (($arrayDB AND isset($arrayDB['_DB_NAME_'])) ? $arrayDB['_DB_NAME_'] : _DB_NAME_);
	$defines['_DB_SERVER_'] = (($arrayDB AND isset($arrayDB['_DB_SERVER_'])) ? $arrayDB['_DB_SERVER_'] : _DB_SERVER_);
	$defines['_DB_USER_'] = (($arrayDB AND isset($arrayDB['_DB_USER_'])) ? $arrayDB['_DB_USER_'] : _DB_USER_);
	$defines['_DB_PREFIX_'] = (($arrayDB AND isset($arrayDB['_DB_PREFIX_'])) ? $arrayDB['_DB_PREFIX_'] : _DB_PREFIX_);
	$defines['_DB_PASSWD_'] = (($arrayDB AND isset($arrayDB['_DB_PASSWD_'])) ? $arrayDB['_DB_PASSWD_'] : _DB_PASSWD_);
	$defines['_DB_TYPE_'] = (($arrayDB AND isset($arrayDB['_DB_TYPE_'])) ? $arrayDB['_DB_TYPE_'] : _DB_TYPE_);
	$defines['_COOKIE_KEY_'] = addslashes(_COOKIE_KEY_);
	$defines['_COOKIE_IV_'] = addslashes(_COOKIE_IV_);
	if (defined('_RIJNDAEL_KEY_'))
		$defines['_RIJNDAEL_KEY_'] = addslashes(_RIJNDAEL_KEY_);
	if (defined('_RIJNDAEL_IV_'))
		$defines['_RIJNDAEL_IV_'] = addslashes(_RIJNDAEL_IV_);
	$defines['_PS_VERSION_'] = addslashes(_PS_VERSION_);
	$content = "<?php\n\n";
	foreach ($defines as $k => $value)
		$content .= 'define(\''.$k.'\', \''.addslashes($value).'\');'."\n";
	$content .= "\n?>";
	if ($fd = @fopen(PS_ADMIN_DIR.'/../config/settings.inc.php', 'w'))
	{
		fwrite($fd, $content);
		fclose($fd);
		return true;
	}
	return false;
}

/**
  * Display SQL date in friendly format
  *
  * @param string $sqlDate Date in SQL format (YYYY-MM-DD HH:mm:ss)
  * @param boolean $withTime Display both date and time
  * @todo Several formats (french : DD-MM-YYYY)
  */
function	displayDate($sqlDate, $withTime = false)
{
	return strftime('%Y-%m-%d'.($withTime ? ' %H:%M:%S' : ''), strtotime($sqlDate));
}

/**
  * Return path to a product category
  *
  * @param string $urlBase Start URL
  * @param integer $id_category Start category
  * @param string $path Current path
  * @param string $highlight String to highlight (in XHTML/CSS)
  */
function	getPath($urlBase, $id_category, $path = '', $highlight = '')
{
	global $cookie;
	
	$category = new Category($id_category, intval($cookie->id_lang));
	if (!$category->id)
		return $path;
	$name = ($highlight != NULL) ? str_ireplace($highlight, '<span class="highlight">'.$highlight.'</span>', 
	Category::hideCategoryPosition($category->name)) : Category::hideCategoryPosition($category->name);
	$edit = '<a href="'.$urlBase.'&id_category='.$category->id.'&addcategory&token=' . Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'"><img src="../img/admin/edit.gif" alt="Modify" /></a> ';
	if ($category->id == 1)
		$edit = '<a href="'.$urlBase.'&id_category='.$category->id.'&viewcategory&token=' . Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'"><img src="../img/admin/home.gif" alt="Home" /></a> ';
	$path = $edit.'<a href="'.$urlBase.'&id_category='.$category->id.'&viewcategory&token=' . Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">'.$name.'</a> > '.$path;
	if ($category->id == 1)
		return substr($path, 0, strlen($path) - 3);
	return getPath($urlBase, $category->id_parent, $path);
}

function	getDirContent($path)
{
	$content = array();
	if (is_dir($path))
	{
		$d = dir($path);
		while (false !== ($entry = $d->read()))
			if ($entry{0} != '.')
				$content[] = $entry;
		$d->close();
	}
	return $content;
}

function createDir($path, $rights)
{
	if (file_exists($path))
		return true;
	return @mkdir($path, $rights);
}

function checkPSVersion()
{
	libxml_set_streams_context(stream_context_create(array('http' => array('timeout' => 3))));
	if ($feed = @simplexml_load_file('http://www.prestashop.com/xml/version.xml') AND _PS_VERSION_ < $feed->version->num)
		return array('name' => $feed->version->name, 'link' => $feed->download->link);
	return false;
}

function translate($string)
{
	global $_LANGADM;
	if (!is_array($_LANGADM))
		return str_replace('"', '&quot;', $string);
	$key = md5(str_replace('\'', '\\\'', $string));
	$str = (key_exists('index'.$key, $_LANGADM)) ? $_LANGADM['index'.$key] : ((key_exists('index'.$key, $_LANGADM)) ? $_LANGADM['index'.$key] : $string);
	return str_replace('"', '&quot;', stripslashes($str));
}

function recursiveTab($id_tab)
{
	global $cookie, $tabs;
	
	$adminTab = Tab::getTab(intval($cookie->id_lang), $id_tab);
	$tabs[]= $adminTab;
	if ($adminTab['id_parent'] > 0)
		recursiveTab($adminTab['id_parent']);
}

function checkingTab($tab)
{
	global $adminObj, $cookie;

	$tab = trim($tab);
	if (!Validate::isTabName($tab))
		return false;
	if ($module = Db::getInstance()->getValue('SELECT module FROM '._DB_PREFIX_.'tab WHERE class_name = \''.pSQL($tab).'\'') AND file_exists(_PS_MODULE_DIR_.'/'.$module.'/'.$tab.'.php'))
		include_once(_PS_MODULE_DIR_.'/'.$module.'/'.$tab.'.php');
	elseif (file_exists(PS_ADMIN_DIR.'/tabs/'.$tab.'.php'))
		include_once(PS_ADMIN_DIR.'/tabs/'.$tab.'.php');
	$id_tab = Tab::getIdFromClassName($tab);
	if (!class_exists($tab, false) OR !$id_tab)
	{
		echo Tools::displayError('Tab does not exist');
		return false;
	}
	$adminObj = new $tab;
	if (!$adminObj->viewAccess() AND ($adminObj->table != 'employee' OR $cookie->id_employee != Tools::getValue('id_employee') OR !Tools::isSubmit('updateemployee')))
	{
		$adminObj->_errors = array(Tools::displayError('access denied'));
		echo $adminObj->displayErrors();
		return false;
	}
	return ($id_tab);
}

function checkTabRights($id_tab)
{
	global $cookie;
	static $tabAccesses = NULL;
	
	if ($tabAccesses === NULL)
		$tabAccesses =  Profile::getProfileAccesses($cookie->profile);

	return ($tabAccesses[intval($id_tab)]['view'] === '1');
}

?>
