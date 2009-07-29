<?php

/* Improve PHP configuration to prevent issues */
@ini_set('display_errors', 'on');
@ini_set('upload_max_filesize', '100M');
@ini_set('default_charset', 'utf-8');

/* Correct Apache charset */
header('Content-Type: text/html; charset=utf-8');

/*
 * It is not safe to rely on the system's timezone settings, but we can\'t easily determine the user timezone and the use of this function cause trouble for some configurations.
 * This will generate a PHP Strict Standards notice. To fix it up, uncomment the following line.
 */

/* Autoload */
function __autoload($className)
{
	if (!class_exists($className, false))
		require_once(dirname(__FILE__).'/../classes/'.$className.'.php');
}

/* No settings file? goto installer...*/
if (!file_exists(dirname(__FILE__).'/settings.inc.php'))
{
	$dir = ((is_dir($_SERVER['REQUEST_URI']) OR substr($_SERVER['REQUEST_URI'], -1) == '/') ? $_SERVER['REQUEST_URI'] : dirname($_SERVER['REQUEST_URI']).'/');
	if(!file_exists(dirname(__FILE__).'/../install'))
		die('Error: \'install\' directory is missing');
	Tools::redirect('install', $dir);
}
include(dirname(__FILE__).'/settings.inc.php');

/* Redefine REQUEST_URI if empty (on some webservers...) */
if (!isset($_SERVER['REQUEST_URI']) OR empty($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	if (isset($_SERVER['QUERY_STRING']) AND !empty($_SERVER['QUERY_STRING']))
		$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
}

$currentDir = dirname(__FILE__);

/* Base and themes */
define('_THEMES_DIR_',     __PS_BASE_URI__.'themes/');
define('_THEME_IMG_DIR_',  _THEMES_DIR_._THEME_NAME_.'/img/');
define('_THEME_CSS_DIR_',  _THEMES_DIR_._THEME_NAME_.'/css/');
define('_THEME_JS_DIR_',   _THEMES_DIR_._THEME_NAME_.'/js/');
define('_THEME_CAT_DIR_',  __PS_BASE_URI__.'img/c/');
define('_THEME_PROD_DIR_', __PS_BASE_URI__.'img/p/');
define('_THEME_PROD_PIC_DIR_', __PS_BASE_URI__.'upload/');
define('_THEME_MANU_DIR_', __PS_BASE_URI__.'img/m/');
define('_THEME_SCENE_DIR_', __PS_BASE_URI__.'img/scenes/');
define('_THEME_SCENE_THUMB_DIR_', __PS_BASE_URI__.'img/scenes/thumbs');
define('_THEME_SUP_DIR_',  __PS_BASE_URI__.'img/su/');
define('_THEME_SHIP_DIR_', __PS_BASE_URI__.'img/s/');
define('_THEME_LANG_DIR_', __PS_BASE_URI__.'img/l/');
define('_THEME_COL_DIR_',  __PS_BASE_URI__.'img/co/');
define('_SUPP_DIR_',       __PS_BASE_URI__.'img/su/');
define('_THEME_DIR_',      _THEMES_DIR_._THEME_NAME_.'/');
define('_MAIL_DIR_',        __PS_BASE_URI__.'mails/');
define('_MODULE_DIR_',        __PS_BASE_URI__.'modules/');
define('_PS_IMG_',         __PS_BASE_URI__.'img/');
define('_PS_ADMIN_IMG_',   _PS_IMG_.'admin/');

/* Directories */
define('_PS_ROOT_DIR_',             realpath($currentDir.'/..'));
define('_PS_CLASS_DIR_',            _PS_ROOT_DIR_.'/classes/');
define('_PS_TRANSLATIONS_DIR_',     _PS_ROOT_DIR_.'/translations/');
define('_PS_DOWNLOAD_DIR_',         _PS_ROOT_DIR_.'/download/');
define('_PS_MAIL_DIR_',             _PS_ROOT_DIR_.'/mails/');
define('_PS_MODULE_DIR_',           _PS_ROOT_DIR_.'/modules/');
define('_PS_ALL_THEMES_DIR_',       _PS_ROOT_DIR_.'/themes/');
define('_PS_THEME_DIR_',            _PS_ROOT_DIR_.'/themes/'._THEME_NAME_.'/');
define('_PS_IMG_DIR_',              _PS_ROOT_DIR_.'/img/');
define('_PS_CAT_IMG_DIR_',          _PS_IMG_DIR_.'c/');
define('_PS_PROD_IMG_DIR_',         _PS_IMG_DIR_.'p/');
define('_PS_SCENE_IMG_DIR_',        _PS_IMG_DIR_.'scenes/');
define('_PS_SCENE_THUMB_IMG_DIR_',  _PS_IMG_DIR_.'scenes/thumbs/');
define('_PS_MANU_IMG_DIR_',         _PS_IMG_DIR_.'m/');
define('_PS_SHIP_IMG_DIR_',         _PS_IMG_DIR_.'s/');
define('_PS_SUPP_IMG_DIR_',         _PS_IMG_DIR_.'su/');
define('_PS_COL_IMG_DIR_',			_PS_IMG_DIR_.'co/');
define('_PS_TMP_IMG_DIR_',          _PS_IMG_DIR_.'tmp/');
define('_PS_PROD_PIC_DIR_',			_PS_ROOT_DIR_.'/upload/');
define('_PS_TOOL_DIR_',             _PS_ROOT_DIR_.'/tools/');
define('_PS_SMARTY_DIR_',           _PS_TOOL_DIR_.'smarty/');
define('_PS_STEST_DIR_',            _PS_TOOL_DIR_.'simpletest/');
define('_PS_SWIFT_DIR_',            _PS_TOOL_DIR_.'swift/');
define('_PS_FPDF_PATH_',            _PS_TOOL_DIR_.'fpdf/');
define('_PS_PEAR_XML_PARSER_PATH_', _PS_TOOL_DIR_.'pear_xml_parser/');
define('_PS_CSS_DIR_',              __PS_BASE_URI__.'css/');
define('_PS_JS_DIR_',               __PS_BASE_URI__.'js/');

/* settings php */
define('_PS_MAGIC_QUOTES_GPC_',         get_magic_quotes_gpc());
define('_PS_MYSQL_REAL_ESCAPE_STRING_', function_exists('mysql_real_escape_string'));
define('_PS_TRANS_PATTERN_',            '(.*[^\\\\])');
define('_PS_MIN_TIME_GENERATE_PASSWD_', '360');

/* aliases */
function p($var) {
	Tools::p($var);
}
function d($var) {
	Tools::d($var);
}

/* Order states */
define('_PS_OS_CHEQUE_',      1);
define('_PS_OS_PAYMENT_',     2);
define('_PS_OS_PREPARATION_', 3);
define('_PS_OS_SHIPPING_',    4);
define('_PS_OS_DELIVERED_',   5);
define('_PS_OS_CANCELED_',    6);
define('_PS_OS_REFUND_',      7);
define('_PS_OS_ERROR_',       8);
define('_PS_OS_OUTOFSTOCK_',  9);
define('_PS_OS_BANKWIRE_',    10);
define('_PS_OS_PAYPAL_',      11);

/* Tax behavior */
define('PS_PRODUCT_TAX', 0);
define('PS_STATE_TAX', 1);
define('PS_BOTH_TAX', 2);

define('_PS_PRICE_DISPLAY_PRECISION_', 2);


global $_MODULES;
$_MODULES = array();

/* Globals */
global $defaultCountry;

/* Load all configuration keys */
Configuration::loadConfiguration();

/* Load all language definitions */
Language::loadLanguages();

/* Load all zone/tax relations */
Tax::loadTaxZones();

/* Loading default country */
$defaultCountry = new Country(intval(Configuration::get('PS_COUNTRY_DEFAULT')));

/* Define default timezone */
$timezone = Tools::getTimezones(Configuration::get('PS_TIMEZONE'));

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set($timezone);

/* Smarty */
include(dirname(__FILE__).'/smarty.config.inc.php');
