<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/emailverify.php');

$ev = new EmailVerify();
$ev->activate();

?>
