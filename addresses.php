<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=addresses.php');

$customer = new Customer(intval($cookie->id_customer));
if (!Validate::isLoadedObject($customer))
	die(Tools::displayError('customer not found'));

include(dirname(__FILE__).'/header.php');
$smarty->assign('addresses', $customer->getAddresses(intval($cookie->id_lang)));
$smarty->display(_PS_THEME_DIR_.'addresses.tpl');
include(dirname(__FILE__).'/footer.php');

?>
