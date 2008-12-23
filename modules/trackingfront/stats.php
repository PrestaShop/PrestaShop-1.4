<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/trackingfront.php');

$tf = new TrackingFront();
$tf->postProcess();
echo $tf->isLogged() ? $tf->displayAccount() : $tf->displayLogin();

?>