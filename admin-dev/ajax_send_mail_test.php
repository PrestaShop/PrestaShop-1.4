<?php

include_once(dirname(__FILE__).'/../config/config.inc.php');

$smtpChecked = (trim($_GET['mailMethod']) ==  'smtp');
$smtpServer = $_GET['smtpSrv'];
$content = $_GET['testMsg'];
$subject = $_GET['testSubject'];
$type = 'text/html';
$to =  $_GET['testEmail'];
$from = 'no-reply@'.Tools::getHttpHost(false, true).'.com';
$smtpLogin = $_GET['smtpLogin'];
$smtpPassword = $_GET['smtpPassword'];
$smtpPort = $_GET['smtpPort'];
$smtpEncryption = $_GET['smtpEnc'];

$result = Mail::sendMailTest($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort, $smtpEncryption);
die($result ? 'ok' : 'fail');

?>