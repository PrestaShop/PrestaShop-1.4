<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(_PS_MODULE_DIR_.'/buyster/classes/BuysterWebService.php');

$ref = htmlentities($_GET['ref']);

$webService = new BuysterWebService();
$result = $webService->operation("DIAGNOSTIC", $ref);
global $smarty;

$smarty->assign('diagnostic', $result);
$smarty->display('tpl/diagnostic.tpl' );
