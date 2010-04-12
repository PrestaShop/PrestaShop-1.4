<?php

include(dirname(__FILE__).'/../config/config.inc.php');

if (substr(_COOKIE_KEY_, 34, 8) != Tools::getValue('token'))
	die;

ini_set('max_execution_time', 7200);
Search::indexation(Tools::getValue('full'));
Tools::redirectAdmin($_SERVER['HTTP_REFERER'].'&conf=4');

?>