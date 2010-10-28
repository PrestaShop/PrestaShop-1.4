<?php

require_once(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('CartController');
$controller->run();