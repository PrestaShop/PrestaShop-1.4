<?php

$configPath = '../../../config/config.inc.php';

if (file_exists($configPath))
{
	include($configPath);
	include(dirname(__FILE__).'/../carriercompare.php');

	$controller = new FrontController();
	$carrier = new carrierCompare();

	$controller->init();
	$id_zone = Tools::getValue('id_zone');
	$id_carrier = Tools::getValue('id_carrier');
	$id_state = Tools::getValue('id_state');
	
	if ($id_zone == 'undefined' && $id_state != 'undefined')
		$id_zone = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT `id_zone`
				FROM `'._DB_PREFIX_.'state`
				WHERE id_state = '.$id_state);
	echo $carrier->getCarriersListByIdZone($id_zone, $id_carrier);
}
else
	echo 'Config file can\'t be included';
?>
