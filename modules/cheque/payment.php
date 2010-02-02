<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cheque.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
	
$cheque = new Cheque();
echo $cheque->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>
