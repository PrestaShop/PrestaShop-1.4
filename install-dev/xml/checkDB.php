<?php

include(INSTALL_PATH.'/classes/ToolsInstall.php');

$resultDB = ToolsInstall::checkDB($_GET['server'], $_GET['login'], $_GET['password'], $_GET['name']);
die("<action result='".($resultDB === true ? "ok" : "fail")."' error='".($resultDB === true ? "" : $resultDB)."'/>\n");

?>