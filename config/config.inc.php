<?php

/* Debug only */
@ini_set('display_errors', 'on');
define('_PS_DEBUG_SQL_', true);
$start_time = microtime(true);

/* Compatibility warning */
define('_PS_DISPLAY_COMPATIBILITY_WARNING_', true);

/* SSL configuration */
define('_PS_SSL_PORT_', 443);

/* Improve PHP configuration to prevent issues */
@ini_set('upload_max_filesize', '100M');
@ini_set('default_charset', 'utf-8');

/* Correct Apache charset */
header('Content-Type: text/html; charset=utf-8');

/* Autoload */
require(dirname(__FILE__).'/autoload.php');

/* No settings file? goto installer...*/
if (!file_exists(dirname(__FILE__).'/settings.inc.php'))
{
	$dir = ((is_dir($_SERVER['REQUEST_URI']) OR substr($_SERVER['REQUEST_URI'], -1) == '/') ? $_SERVER['REQUEST_URI'] : dirname($_SERVER['REQUEST_URI']).'/');
	if(!file_exists(dirname(__FILE__).'/../install'))
		die('Error: \'install\' directory is missing');
	Tools::redirect('install', $dir);
}
require_once(dirname(__FILE__).'/settings.inc.php');

/* Redefine REQUEST_URI if empty (on some webservers...) */
if (!isset($_SERVER['REQUEST_URI']) OR empty($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	if (isset($_SERVER['QUERY_STRING']) AND !empty($_SERVER['QUERY_STRING']))
		$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
}

/* Include all defines */
require_once(dirname(__FILE__).'/defines.inc.php');
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

/* Load all configuration keys */
Configuration::loadConfiguration();

/* Load all language definitions */
Language::loadLanguages();

/* Load all zone/tax relations */
Tax::loadTaxZones();

/* Loading default country */
global $defaultCountry;
$defaultCountry = new Country(intval(Configuration::get('PS_COUNTRY_DEFAULT')), Configuration::get('PS_LANG_DEFAULT'));

/* It is not safe to rely on the system's timezone settings, and this would generate a PHP Strict Standards notice. */
if (function_exists('date_default_timezone_set'))
	date_default_timezone_set(Configuration::get('PS_TIMEZONE'));

/* Smarty */
require_once(dirname(__FILE__).'/smarty.config.inc.php');
