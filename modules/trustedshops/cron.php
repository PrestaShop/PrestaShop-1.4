<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__) .'/trustedshops.php');

if (isset($_GET['secure_key']) AND Configuration::get('PS_TS_SECURE_KEY') == $_GET['secure_key'])
		RatingAlert::executeCronTask();

?>