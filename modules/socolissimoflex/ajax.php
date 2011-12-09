<?php

$configPath = '../../config/config.inc.php';
$initPath = '../../init.php';
if (file_exists($configPath) && file_exists($initPath))
{
	include($configPath);
	include($initPath);
	include('socolissimoflex.php');
	$SocolissimoFlex = new SocolissimoFlex();
	echo $SocolissimoFlex->hookExtraCarrierAjax(array());
}

