<?php

require(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('SitemapController');
$controller->run();