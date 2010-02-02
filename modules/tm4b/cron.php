<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/tm4b.php');
include(dirname(__FILE__).'/classes/Tm4bSms.php');

if (!Configuration::get('TM4B_DAILY_REPORT_ACTIVE'))
	die ('Daily report not active');
if (Configuration::get('TM4B_LAST_REPORT') == date('Y-m-d'))
	die ('Report already sent');
Configuration::updateValue('TM4B_LAST_REPORT', date('Y-m-d'));
$module = new Tm4b();
$sms = new Tm4bSms(Configuration::get('TM4B_USER'), Configuration::get('TM4B_PASSWORD'), Configuration::get('TM4B_ROUTE'));
$sms->msg = $module->getStatsBody();
$numbers = explode(',', Configuration::get('TM4B_NEW_ORDER_NUMBERS'));
foreach ($numbers as $number)
	if ($number != '')
		$sms->addRecipient($number);
$sms->Send(Configuration::get('TM4B_SIM'));
die ('OK');

?>