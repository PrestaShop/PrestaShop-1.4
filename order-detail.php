<?php

require(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('OrderDetailController', true, true);
$controller->run();