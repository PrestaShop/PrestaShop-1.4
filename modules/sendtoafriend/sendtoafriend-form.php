<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/sendtoafriend.php');

$sendtoafriend = new sendToAFriend();
echo $sendtoafriend->displayFrontForm();

include(dirname(__FILE__).'/../../footer.php');

?>