<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/hipay.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');

$hipay = new HiPay();
$hipay->payment();

?>
