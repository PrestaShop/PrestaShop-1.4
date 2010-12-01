<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/ogone.php');

ini_set('display_errors', 'on');

$ogone = new Ogone();
$id_module = $ogone->id;
$id_cart = Tools::getValue('orderID');
$key = Db::getInstance()->getValue('SELECT secure_key FROM '._DB_PREFIX_.'customer WHERE id_customer = '.(int)$cookie->id_customer);

$smarty->assign(array('id_module' => $id_module, 'id_cart' => $id_cart, 'key' => $key));
echo $ogone->display(__FILE__, 'waiting.tpl');

include(dirname(__FILE__).'/../../footer.php');


