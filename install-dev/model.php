<?php

/* Redefine REQUEST_URI if empty (on some webservers...) */
if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '')
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
if ($tmp = strpos($_SERVER['REQUEST_URI'], '?'))
	$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $tmp);

define('INSTALL_VERSION', '1.1.0.5'); 
define('INSTALL_PATH', dirname(__FILE__));
define('SETTINGS_FILE', INSTALL_PATH.'/../config/settings.inc.php');
define('INSTALLER__PS_BASE_URI', substr($_SERVER['REQUEST_URI'], 0, -1 * (strlen($_SERVER['REQUEST_URI']) - strrpos($_SERVER['REQUEST_URI'], '/')) - strlen(substr(dirname($_SERVER['REQUEST_URI']), strrpos(dirname($_SERVER['REQUEST_URI']), '/')+1))));
define('INSTALLER__PS_BASE_URI_ABSOLUTE', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').INSTALLER__PS_BASE_URI);

// XML Header
header('Content-Type: text/xml');

// Switching method
if(isset($_GET['method']))
{
	switch ($_GET['method'])
	{
		
		case 'checkConfig' :
			include_once('xml/checkConfig.php');
		break;
	
		case 'checkDB' :
			include_once('xml/checkDB.php');
		break;
		
		case 'createDB' :
			include_once('xml/createDB.php');
		break;
	
		case 'checkMail' :
			include_once('xml/checkMail.php');
		break;
	
		case 'checkShopInfos' :
			include_once('xml/checkShopInfos.php');
		break;
		
		case 'doUpgrade' :
			include_once('xml/doUpgrade.php');
		break;
	}
}
?>
