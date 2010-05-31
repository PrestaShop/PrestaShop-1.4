<?php

/* Debug only */
ini_set('display_errors', 'on');
define('_PS_DEBUG_SQL_', true);

/* Improve PHP configuration to prevent issues */
@ini_set('default_charset', 'utf-8');

/* Correct Apache charset */
header('Content-Type: text/html; charset=utf-8');

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

/* Include all defines */
include(dirname(__FILE__).'/defines.inc.php');
/* Defines are not in defines.inc.php file for no conflicts in installer */
define('_PS_MAGIC_QUOTES_GPC_',         get_magic_quotes_gpc());
define('_PS_MODULE_DIR_',           _PS_ROOT_DIR_.'/modules/');
define('_PS_MYSQL_REAL_ESCAPE_STRING_', function_exists('mysql_real_escape_string'));

/* aliases */
function p($var) {
	return (Tools::p($var));
}
function d($var) {
	Tools::d($var);
}

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


/*
 * It is not safe to rely on the system's timezone settings, but we can\'t easily determine the user timezone and the use of this function cause trouble for some configurations.
 * This will generate a PHP Strict Standards notice. To fix it up, uncomment the following line.
 */
if (function_exists('date_default_timezone_set'))
{
	$timezone = Tools::getTimezones(Configuration::get('PS_TIMEZONE'));
	date_default_timezone_set($timezone);
}

/* Smarty */
include(dirname(__FILE__).'/smarty.config.inc.php');
