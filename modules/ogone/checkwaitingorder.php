<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
die(
	Db::getInstance()->getValue('SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart = '.(int)Tools::getValue('id_cart').' AND secure_key = "'.pSQL(Tools::getValue('key')).'"')
	? 'ok'
	: 'ko'
);

?>
