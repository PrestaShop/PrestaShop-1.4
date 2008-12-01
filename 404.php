<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

$smarty->display(_PS_THEME_DIR_.'404.tpl');

include(dirname(__FILE__).'/footer.php');

?>