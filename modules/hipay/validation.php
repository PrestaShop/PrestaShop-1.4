<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/hipay.php');
$hipay = new HiPay();
$hipay->validation();

?>
