<?php

/**
  * Admin panel functions, functions.inc.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

require_once(dirname(__FILE__).'/../images.inc.php'); 

function bindDatepicker($id, $time)
{
	if ($time)
	echo '
		var dateObj = new Date();
		var mins = dateObj.getMinutes();
		if (mins < 10) { mins = "0" + mins; }
		var time = " "+dateObj.getHours()+":"+mins+":"+dateObj.getSeconds();';

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
function	rewriteSettingsFile($baseUri = NULL, $theme = NULL, $arrayDB = NULL)
{
 	$defines = array();
	$defines['__PS_BASE_URI__'] = !is_null($baseUri) ? $baseUri : __PS_BASE_URI__;
	$defines['_THEME_NAME_'] = $theme ? $theme : _THEME_NAME_;
	$defines['_DB_NAME_'] = (($arrayDB AND isset($arrayDB['_DB_NAME_'])) ? $arrayDB['_DB_NAME_'] : _DB_NAME_);
	$defines['_DB_SERVER_'] = (($arrayDB AND isset($arrayDB['_DB_SERVER_'])) ? $arrayDB['_DB_SERVER_'] : _DB_SERVER_);
	$defines['_DB_USER_'] = (($arrayDB AND isset($arrayDB['_DB_USER_'])) ? $arrayDB['_DB_USER_'] : _DB_USER_);
	$defines['_DB_PREFIX_'] = (($arrayDB AND isset($arrayDB['_DB_PREFIX_'])) ? $arrayDB['_DB_PREFIX_'] : _DB_PREFIX_);
	$defines['_DB_PASSWD_'] = (($arrayDB AND isset($arrayDB['_DB_PASSWD_'])) ? $arrayDB['_DB_PASSWD_'] : _DB_PASSWD_);
	$defines['_DB_TYPE_'] = (($arrayDB AND isset($arrayDB['_DB_TYPE_'])) ? $arrayDB['_DB_TYPE_'] : _DB_TYPE_);
	$defines['_COOKIE_KEY_'] = addslashes(_COOKIE_KEY_);
	$defines['_COOKIE_IV_'] = addslashes(_COOKIE_IV_);
	$defines['_PS_VERSION_'] = addslashes(_PS_VERSION_);
	$content = "<?php\n\n";
	foreach ($defines as $k => $value)
		$content .= 'define(\''.$k.'\', \''.addslashes($value).'\');'."\n";
	$content .= "\n?>";
	if ($fd = fopen(PS_ADMIN_DIR.'/../config/settings.inc.php', 'w'))
	{
		fwrite($fd, $content);
		fclose($fd);
	}
	else
		Tools::displayError('cannot access settings file');
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

?>