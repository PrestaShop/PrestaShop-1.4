<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

$smarty->assign('HOOK_HOME', Module::hookExec('home'));
$smarty->display(_PS_THEME_DIR_.'index.tpl');

include(dirname(__FILE__).'/footer.php');

?>