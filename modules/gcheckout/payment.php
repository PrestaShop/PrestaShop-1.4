<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/gcheckout.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
	
$gcheckout = new GCheckout();
echo $gcheckout->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>
