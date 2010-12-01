#!/usr/bin/php
<?php
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/fianetfraud.php');

FianetFraud::checkWaitingOrders();

