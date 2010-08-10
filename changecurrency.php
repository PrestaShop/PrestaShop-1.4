<?php
include ('config/config.inc.php');
include ('init.php');

$new_currency = intval(Tools::getValue('id'));
if ($new_currency AND Currency::getCurrency($new_currency))
{
	$cookie->id_currency = $new_currency;
	die(1);
}
else
	die(0);
?>
