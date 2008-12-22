<?php
	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');

	if (!$cookie->isLogged())
		die('0');

	$id_customer = intval($cookie->id_customer);
	if (!$id_product = intval(Tools::getValue('id_product'))) die ('0');
	$id_product_attribute = intval(Tools::getValue('id_product_attribute'));

	$query = '
		INSERT INTO `'._DB_PREFIX_.'mailalert_customer_oos` (`id_customer` , `id_product` , `id_product_attribute`)
		VALUES (\''.$id_customer.'\', \''.$id_product.'\', \''.$id_product_attribute.'\');
	';

	if (Db::getInstance()->Execute($query))
		die ('1');
	die ('0');
?>