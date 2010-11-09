<?php

include(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('CategoryController');
$controller->run();

?>