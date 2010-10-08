<?php

include_once(dirname(__FILE__).'/../config/config.inc.php');

/* Getting cookie or logout */
require_once(dirname(__FILE__).'/init.php');

$smtpChecked = (trim($_GET['mailMethod']) ==  'smtp');
$smtpServer = $_GET['smtpSrv'];
$content = $_GET['testMsg'];
$subject = $_GET['testSubject'];
$type = 'text/html';
$to =  $_GET['testEmail'];
$from = Configuration::get('PS_SHOP_EMAIL');
$smtpLogin = $_GET['smtpLogin'];
$smtpPassword = $_GET['smtpPassword'];
$smtpPort = $_GET['smtpPort'];
$smtpEncryption = $_GET['smtpEnc'];

$result = Mail::sendMailTest(Tools::htmlentitiesUTF8($smtpChecked), Tools::htmlentitiesUTF8($smtpServer), Tools::htmlentitiesUTF8($content), Tools::htmlentitiesUTF8($subject), Tools::htmlentitiesUTF8($type), Tools::htmlentitiesUTF8($to), Tools::htmlentitiesUTF8($from), Tools::htmlentitiesUTF8($smtpLogin), Tools::htmlentitiesUTF8($smtpPassword), Tools::htmlentitiesUTF8($smtpPort), Tools::htmlentitiesUTF8($smtpEncryption));
die($result ? 'ok' : 'fail');

?>