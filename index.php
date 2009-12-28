<?php

include(dirname(__FILE__).'/config/config.inc.php');

if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
	$rewrited_url = __PS_BASE_URI__;

include(dirname(__FILE__).'/header.php');

$smarty->assign('HOOK_HOME', Module::hookExec('home'));
$smarty->display(_PS_THEME_DIR_.'index.tpl');

include(dirname(__FILE__).'/footer.php');

?>
