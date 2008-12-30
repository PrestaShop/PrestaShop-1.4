<?php
	include(dirname(__FILE__).'/../../config/config.inc.php');
	include(dirname(__FILE__).'/../../init.php');
	include(dirname(__FILE__).'/../../modules/mailalerts/mailalerts.php');
	
	if (!$cookie->isLogged())
		die('0');
	
	$id_customer = intval($cookie->id_customer);
	if (!$id_product = intval(Tools::getValue('id_product')))
		die ('0');
	$id_product_attribute = intval(Tools::getValue('id_product_attribute'));

	$mA = new MailAlerts();
	if ($mA->customerHasNotification($id_customer, $id_product, $id_product_attribute))
		die ('1');
	die('0');
?>