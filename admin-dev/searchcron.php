<?php

include(dirname(__FILE__).'/../config/config.inc.php');

ini_set('max_execution_time', 600);
Search::indexation(Tools::getValue('full'));
Tools::redirectAdmin($_SERVER['HTTP_REFERER'].'&conf=4');

?>