<?php

require_once(dirname(__FILE__).'/../config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

if (isset($_GET['img']) AND Validate::isMd5($_GET['img']) AND isset($_GET['name']) AND Validate::isGenericName($_GET['name']) AND file_exists(_PS_PROD_PIC_DIR_.$_GET['img']))
{
	header('Content-type: image/jpeg');
	header('Content-Disposition: attachment; filename="'.$_GET['name'].'.jpg"');
	echo file_get_contents(_PS_PROD_PIC_DIR_.$_GET['img']);
}
?>