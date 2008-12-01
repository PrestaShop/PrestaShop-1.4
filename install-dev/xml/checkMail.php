<?php

include(INSTALL_PATH.'/classes/ToolsInstall.php');

$smtpChecked = (trim($_GET['mailMethod']) ==  'smtp');
$smtpServer = $_GET['smtpSrv'];
$content = $_GET['testMsg'];
$subject = $_GET['testSubject'];
$type = 'text/html';
$to =  $_GET['testEmail'];
$from = 'no-reply@'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8');
$smtpLogin = $_GET['smtpLogin'];
$smtpPassword = $_GET['smtpPassword'];
$smtpPort = $_GET['smtpPort'];
$smtpEncryption = $_GET['smtpEnc'];

$result = ToolsInstall::sendMail($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort, $smtpEncryption);
die($result ? '<action result="ok"/>' : '<action result="fail"/>');

?>