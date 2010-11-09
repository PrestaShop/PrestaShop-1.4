<?php

require(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('OrderSlipController');
$controller->run();

?>