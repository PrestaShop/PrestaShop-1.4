<?php

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('Europe/Paris');

define('_PS_MAGIC_QUOTES_GPC_', get_magic_quotes_gpc());
define('_PS_MYSQL_REAL_ESCAPE_STRING_', function_exists('mysql_real_escape_string'));

include(INSTALL_PATH.'/classes/AddConfToFile.php');
include(INSTALL_PATH.'/../classes/Validate.php');
include(INSTALL_PATH.'/../classes/Db.php');
include(INSTALL_PATH.'/../classes/Tools.php');
include_once(INSTALL_PATH.'/../config/settings.inc.php');

function isFormValid()
{
	global $error;
	$validInfos = true;
	foreach ($error as $anError)
		if ($anError != '')
			$validInfos = false;
	return $validInfos;
}

// Check each POST data...

$error = array();
foreach ($_GET AS &$var)
{	
	if (is_string($var))
		$var = html_entity_decode($var, ENT_COMPAT, 'UTF-8');
	elseif (is_array($var))
		foreach ($var AS &$row)
			$row = html_entity_decode($row, ENT_COMPAT, 'UTF-8');
}
	
if(!isset($_GET['infosShop']) OR empty($_GET['infosShop']))
	$error['infosShop'] = '0';
else
	$error['infosShop'] = '';

if(!isset($_GET['infosFirstname']) OR empty($_GET['infosFirstname']))
	$error['infosFirstname'] = '0';
else
	$error['infosFirstname'] = '';
	
if(!isset($_GET['infosName']) OR empty($_GET['infosName']))
	$error['infosName'] = '0';
else
	$error['infosName'] = '';
	
if(isset($_GET['infosEmail']) AND !Validate::isEmail($_GET['infosEmail']))
	$error['infosEmail'] = '3';
else
	$error['infosEmail'] = '';

if (isset($_GET['infosShop']) AND !Validate::isGenericName($_GET['infosShop']))
	$error['validateShop'] = '46';
else
	$error['validateShop'] = '';

if (isset($_GET['infosFirstname']) AND !Validate::isName($_GET['infosFirstname']))
	$error['validateFirstname'] = '47';
else
	$error['validateFirstname'] = '';

if (isset($_GET['infosName']) AND !Validate::isName($_GET['infosName']))
	$error['validateName'] = '48';
else
	$error['validateName'] = '';

if(!isset($_GET['infosEmail']) OR empty($_GET['infosEmail']))
	$error['infosEmail'] = '0';

if (!isset($_GET['infosPassword']) OR empty($_GET['infosPassword']))
	$error['infosPassword'] = '0';
else
	$error['infosPassword'] = '';
	
if (!isset($_GET['infosPasswordRepeat']) OR empty($_GET['infosPasswordRepeat']))
	$error['infosPasswordRepeat'] = '0';
else
	$error['infosPasswordRepeat'] = '';

	
if($error['infosPassword'] == '' AND $_GET['infosPassword'] != $_GET['infosPasswordRepeat'])
	$error['infosPassword'] = '2';
	
if($error['infosPassword'] == '' AND (Tools::strlen($_GET['infosPassword']) < 8 OR !Validate::isPasswdAdmin($_GET['infosPassword'])))
	$error['infosPassword'] = '12';

/////////////////////////////
// IF ALL IS OK DO THE NEXT//
/////////////////////////////

include_once(INSTALL_PATH.'/classes/ToolsInstall.php');
$dbInstance = Db::getInstance();

// set Languages
$error['infosLanguages'] = '';
if(isFormValid())
{
	/*$idDefault = array_search($_GET['infosDL'][0], $_GET['infosWL']) + 1;
	//prepare the requests
	$sqlLanguages = array();
	
	$sqlLanguages[] = "UPDATE `"._DB_PREFIX_."configuration` SET `value` = '".$idDefault."' WHERE `"._DB_PREFIX_."configuration`.`id_configuration` =1";
	$sqlLanguages[] = "TRUNCATE TABLE `"._DB_PREFIX_."lang`";
	
	foreach ($_GET['infosWL'] AS $wl)
		$sqlLanguages[] = "INSERT INTO `"._DB_PREFIX_."lang` (`id_lang` ,`name` ,`active` ,`iso_code`)VALUES (NULL , '".ToolsInstall::getLangString($wl)."', '1', '".pSQL($wl)."')";
	foreach($sqlLanguages AS $query)
		if(!Db::getInstance()->Execute($query))
			$error['infosLanguages'] = '11';
	
	// Flags copy
	if(!$languagesId = Db::getInstance()->ExecuteS('SELECT `id_lang`, `iso_code` FROM `'._DB_PREFIX_.'lang`'))
		$error['infosLanguages'] = '11';
	
	unset($dbInstance);*/
}

// Mail Notification
$error['infosNotification'] = '';
if (isFormValid())
{
	if (isset($_GET['infosNotification']) AND $_GET['infosNotification'] == 'on') {
		include_once(INSTALL_PATH.'/classes/ToolsInstall.php');
		$smtpChecked = (trim($_GET['infosMailMethod']) ==  'smtp');
		$smtpServer = $_GET['smtpSrv'];
		$subject = $_GET['infosShop']." - " . $_GET['mailSubject'];
		$type = 'text/html';
		$to =  $_GET['infosEmail'];
		$from = "no-reply@".htmlspecialchars($_SERVER["HTTP_HOST"], ENT_COMPAT, 'UTF-8');
		$smtpLogin = $_GET['smtpLogin'];
		$smtpPassword = $_GET['smtpPassword'];
		$smtpPort = $_GET['smtpPort'];//'default','secure'
		$smtpEncryption = $_GET['smtpEnc'];//"tls","ssl","off"
		$content = ToolsInstall::getNotificationMail($_GET['infosShop'], INSTALLER__PS_BASE_URI_ABSOLUTE, INSTALLER__PS_BASE_URI_ABSOLUTE."img/logo.jpg", ToolsInstall::strtoupper($_GET['infosFirstname']), $_GET['infosName'], $_GET['infosPassword'], $_GET['infosEmail']);

		$result = @ToolsInstall::sendMail($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort, $smtpEncryption);
	}
}

//Insert configuration parameters into the database
$error['infosInsertSQL'] = '';
if (isFormValid())
{
	$sqlParams = array();	
	$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_SHOP_NAME', '".pSQL($_GET['infosShop'])."', NOW(), NOW())";
	$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_SHOP_EMAIL', '".pSQL($_GET['infosEmail'])."', NOW(), NOW())";
	$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_MAIL_METHOD', '".pSQL($_GET['infosMailMethod'] == "smtp" ? "2": "1")."', NOW(), NOW())";
	$sqlParams[] = 'UPDATE '._DB_PREFIX_.'configuration SET value = (SELECT id_lang FROM '._DB_PREFIX_.'lang WHERE iso_code = \''.pSQL($_GET['isoCode']).'\') WHERE name = \'PS_LANG_DEFAULT\'';
	
	if (isset($_GET['infosMailMethod']) AND $_GET['infosMailMethod'] == "smtp")
	{
		$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_MAIL_SERVER', '".pSQL($_GET['smtpSrv'])."', NOW(), NOW())";
		$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_MAIL_USER', '".pSQL($_GET['smtpLogin'])."', NOW(), NOW())";
		$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_MAIL_PASSWD', '".pSQL($_GET['smtpPassword'])."', NOW(), NOW())";
		$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_MAIL_SMTP_ENCRYPTION', '".pSQL($_GET['smtpEnc'])."', NOW(), NOW())";
		$sqlParams[] = "INSERT INTO "._DB_PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('PS_MAIL_SMTP_PORT', '".pSQL($_GET['smtpPort'])."', NOW(), NOW())";
	}
	$sqlParams[] = 'INSERT INTO '._DB_PREFIX_.'employee (id_employee, lastname, firstname, email, passwd, last_passwd_gen, active, id_profile) VALUES (NULL, \''.pSQL(ToolsInstall::strtoupper($_GET['infosName'])).'\', \''.pSQL(ToolsInstall::ucfirst($_GET['infosFirstname'])).'\', \''.pSQL($_GET['infosEmail']).'\', \''.md5(pSQL(_COOKIE_KEY_.$_GET['infosPassword'])).'\', \''.date('Y-m-d h:i:s', strtotime('-360 minutes')).'\', 1, 1)';
	$sqlParams[] = 'INSERT INTO '._DB_PREFIX_.'contact (id_contact, email) VALUES (NULL, \''.pSQL($_GET['infosEmail']).'\'), (NULL, \''.pSQL($_GET['infosEmail']).'\')';
	
	$dbInstance = Db::getInstance();
	foreach($sqlParams as $query)
		if(!$dbInstance->Execute($query))
			$error['infosInsertSQL'] = '11';
	unset($dbInstance);
}

//////////////////////////
// Building XML Response//
//////////////////////////

echo '<shopConfig>'."\n";
foreach ($error AS $key => $line)
	echo '<field id="'.$key.'" result="'.( $line != "" ? 'fail' : 'ok').'" error="'.$line.'" />'."\n";
echo '</shopConfig>';

?>
