<?php

require(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('OrderController', false, true);
$controller->run();