<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/birthdaypresent.php');

if (!Configuration::get('BIRTHDAY_ACTIVE'))
	die ('Birthday present not active');
if (Configuration::get('BIRTHDAY_DATE_UPD') == date('Y-m-d'))
	die ('Birthday already wished');
Configuration::updateValue('BIRTHDAY_DATE_UPD', date('Y-m-d'));
$module = new BirthdayPresent();
$module->createTodaysVouchers();
die ('OK');

?>