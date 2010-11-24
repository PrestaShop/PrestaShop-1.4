<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$currency = new Currency((int)(Tools::getValue('id_currency')));
if (Validate::isLoadedObject($currency) AND !$currency->deleted)
{
	$cookie->id_currency = (int)($currency->id);
	die('1');
}
else
	die('0');

?>