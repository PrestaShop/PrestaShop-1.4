<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=history.php');

/* Js files call */
$js_files = array(
	__PS_BASE_URI__.'js/jquery/jquery.scrollto.js',
	_THEME_JS_DIR_.'history.js');

$smarty->assign('ordersSlip', OrderSlip::getOrdersSlip(intval($cookie->id_customer)));

include(dirname(__FILE__).'/header.php');
$smarty->display(_PS_THEME_DIR_.'order-slip.tpl');
include(dirname(__FILE__).'/footer.php');

?>