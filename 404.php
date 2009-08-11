<?php

if (in_array(substr($_SERVER['REQUEST_URI'], -3), array('png', 'jpg', 'gif')))
{
	include(dirname(__FILE__).'/config/settings.inc.php');
	header('Location: '.__PS_BASE_URI__.'img/404.gif');
	exit;
}

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

$smarty->display(_PS_THEME_DIR_.'404.tpl');

include(dirname(__FILE__).'/footer.php');

?>