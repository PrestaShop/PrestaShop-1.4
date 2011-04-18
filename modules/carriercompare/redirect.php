<?php

$configPath = '../../config/config.inc.php';

if (file_exists($configPath))
{
	include($configPath);
	include(dirname(__FILE__).'/carriercompare.php');

	$controller = new FrontController();
	$controller->init();

	global $cart;

	$carrier = new CarrierCompare();
	$carrier->redirectProcess($cart);
}
?>
