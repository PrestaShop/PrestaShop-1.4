<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/ogone.php');

$ogone = new Ogone();

$id_module = $ogone->id;
$id_order = Tools::getValue('orderID');
$key = Db::getInstance()->getValue('SELECT secure_key FROM '._DB_PREFIX_.'customer WHERE id_customer = '.(int)$cookie->id_lang);

Tools::redirect('order-confirmation.php?id_order='.(int)$id_order.'&id_module='.(int)$id_module.'&key='.$key);
	
?>
