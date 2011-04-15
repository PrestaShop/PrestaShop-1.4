<?php

$configPath = '../../../config/config.inc.php';

if (file_exists($configPath))
{
	include($configPath);
	include(dirname(__FILE__).'/../carriercompare.php');

	$controller = new FrontController();
	$carrier = new carrierCompare();

	$controller->init();
	echo $carrier->getStatesByIdCountry(Tools::getValue('id_country'), Tools::getValue('id_state'));
}
else
	echo 'Config file can\'t be included';
?>
