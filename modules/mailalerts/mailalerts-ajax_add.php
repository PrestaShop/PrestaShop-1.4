<?php
	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');

	if (!$id_product = intval(Tools::getValue('id_product')))
		die ('0');
	$id_product_attribute = intval(Tools::getValue('id_product_attribute'));

	if (!$cookie->isLogged())
	{
		$id_customer = 0;
		$customer_email = strval(Tools::getValue('customer_email'));
		
		// Check if already in dbb
		$query = '
			SELECT * FROM `'._DB_PREFIX_.'mailalert_customer_oos` 
			WHERE `id_customer` = '.$id_customer.'
			AND `customer_email` = \''.$customer_email.'\'
			AND `id_product` = '.$id_product.'
			AND `id_product_attribute` = '.$id_product_attribute;
		if (Db::getInstance()->ExecuteS($query))
			die('1');
	}
	else
	{
		$id_customer = intval($cookie->id_customer);
		$customer_email = 0;
	}

	$query = '
		INSERT INTO `'._DB_PREFIX_.'mailalert_customer_oos` (`id_customer`, `customer_email`, `id_product` , `id_product_attribute`)
		VALUES (\''.$id_customer.'\', \''.$customer_email.'\', \''.$id_product.'\', \''.$id_product_attribute.'\');
	';

	if (Db::getInstance()->Execute($query))
		die ('1');
	die ('0');
?>