<?php

//delete settings file if it exist
if(file_exists(SETTINGS_FILE))
{
	if (!unlink(SETTINGS_FILE))
	{
	die('<action result="fail" error="17" />'."\n");
	}
}

include(INSTALL_PATH.'/classes/AddConfToFile.php');
include(INSTALL_PATH.'/../classes/Validate.php');
include(INSTALL_PATH.'/../classes/Db.php');
include(INSTALL_PATH.'/../classes/Tools.php');

//check db access
include(INSTALL_PATH.'/classes/ToolsInstall.php');
$resultDB = ToolsInstall::checkDB($_GET['server'], $_GET['login'], $_GET['password'], $_GET['name']);
if ($resultDB !== true){
	die("<action result='fail' error='".$resultDB."'/>\n");
}


// Check POST data...
$data_check = array(
	!isset($_GET['mode']) OR ( $_GET['mode'] != "full" AND $_GET['mode'] != "lite"),
	!isset($_GET['tablePrefix']) OR !Validate::isMailName($_GET['tablePrefix']) OR !preg_match('/^[a-z0-9_]*$/i', $_GET['tablePrefix'])
);
foreach ($data_check AS $data)
	if ($data)
		die('<action result="fail" error="8"/>'."\n");

// Writing data in settings file
$oldLevel = error_reporting(E_ALL);
$datas = array(
	array('_DB_SERVER_', $_GET['server']),
	array('_DB_TYPE_', $_GET['type']),
	array('_DB_NAME_', $_GET['name']),
	array('_DB_USER_', $_GET['login']),
	array('_DB_PASSWD_', $_GET['password']),
	array('_DB_PREFIX_', $_GET['tablePrefix']),
	array('__PS_BASE_URI__', str_replace(' ', '%20', INSTALLER__PS_BASE_URI)),
	array('_THEME_NAME_', 'prestashop'),
	array('_COOKIE_KEY_', Tools::passwdGen(56)),
	array('_COOKIE_IV_', Tools::passwdGen(8)),
	array('_PS_CREATION_DATE_', date('Y-m-d')),
	array('_PS_VERSION_', INSTALL_VERSION)
);
error_reporting($oldLevel);
$confFile = new AddConfToFile(SETTINGS_FILE, 'w');
if ($confFile->error)
	die('<action result="fail" error="'.$confFile->error.'" />'."\n");
	
foreach ($datas AS $data){
	$confFile->writeInFile($data[0], $data[1]);
}
$confFile->writeEndTagPhp();

if ($confFile->error != false)
	die('<action result="fail" error="'.$confFile->error.'" />'."\n");

//load new settings
include(INSTALL_PATH.'/../config/settings.inc.php');

//-----------
//import SQL data
//-----------
switch (_DB_TYPE_) {
	case "MySQL" :
		
		$filePrefix = "PREFIX_";
		
		//send the SQL structure file requests
		$structureFile = dirname(__FILE__)."/../sql/db.sql";
		if(!file_exists($structureFile))
			die('<action result="fail" error="10" />'."\n");
		$db_structure_settings ="";
		if ( !$db_structure_settings .= file_get_contents($structureFile) )
			die('<action result="fail" error="9" />'."\n");
		$db_structure_settings = str_replace($filePrefix, $_GET['tablePrefix'], $db_structure_settings);		
		$db_structure_settings = preg_split("/;\s*[\r\n]+/",$db_structure_settings);
		foreach($db_structure_settings as $query){
			$query = trim($query);
			if(!empty($query)){
				if(!Db::getInstance()->Execute($query)){
					if(Db::getInstance()->getNumberError() == 1050){
						die('<action result="fail" error="14" />'."\n");
					} else {
						die(
							'<action
							result="fail"
							error="11"
							sqlMsgError="'.addslashes(htmlentities(Db::getInstance()->getMsgError())).'"
							sqlNumberError="'.htmlentities(Db::getInstance()->getNumberError()).'"
							sqlQuery="'.addslashes(htmlentities($query)).'"
							/>'
						);
					}
				}
			}
		}
		
		//send the SQL data file requests
		
		$db_data_settings = "";
		
		$liteFile = dirname(__FILE__)."/../sql/db_settings_lite.sql";
		if(!file_exists($liteFile))
			die('<action result="fail" error="10" />'."\n");
		if ( !$db_data_settings .= file_get_contents( $liteFile ) )
			die('<action result="fail" error="9" />'."\n");
		
		if($_GET['mode'] == "full"){
			$fullFile = dirname(__FILE__)."/../sql/db_settings_extends.sql";
			if(!file_exists($fullFile))
				die('<action result="fail" error="10" />'."\n");
			if ( !$db_data_settings .= file_get_contents( $fullFile ) )
				die('<action result="fail" error="9" />'."\n");
		}
		
		$db_data_settings = str_replace($filePrefix, $_GET['tablePrefix'], $db_data_settings);		
		$db_data_settings = preg_split("/;\s*[\r\n]+/",$db_data_settings);
		/* UTF-8 support */
		array_unshift($db_data_settings, 'SET NAMES \'utf8\';');
		foreach($db_data_settings as $query){
			$query = trim($query);
			if(!empty($query)){
				if(!Db::getInstance()->Execute($query)){
					if(Db::getInstance()->getNumberError() == 1050){
						die('<action result="fail" error="14" />'."\n");
					} else {
						die(
							'<action
							result="fail"
							error="11"
							sqlMsgError="'.addslashes(htmlentities(Db::getInstance()->getMsgError())).'"
							sqlNumberError="'.htmlentities(Db::getInstance()->getNumberError()).'"
							sqlQuery="'.addslashes(htmlentities($query)).'"
							/>'
						);
					}
				}
			}
		}
	break;
}
die('<action result="ok" error="" />'."\n");
?>
