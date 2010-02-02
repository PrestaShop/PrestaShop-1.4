<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/reverso.php');

if (!class_exists('Reverso'))
	Tools::d(Tools::displayError('Error in loading Reverso'));

$reverso = new Reverso();
if (!Validate::isLoadedObject($reverso))
	Tools::d(Tools::displayError('Error in loading Reverso'));

$reverso_result = $reverso->callReverso(pSQL(Tools::getValue('phone')));

echo $reverso_result;
exit;
?>
