<?php
$useSSL = true;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterOperation.php");
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterWebService.php");

if (Tools::getValue('payment'))
	$typePayment = htmlentities(Tools::getValue('payment'));

$operation = new BuysterOperation($cart->id);

if (isset($typePayment) && $typePayment == 'multiple')
	$operation->setOperation('paymentN');
else
	$operation->setOperation(Configuration::get('BUYSTER_PAYMENT_TRANSACTION_TYPE'));

$ref = "BuysterRef".date('Ymdhis').$cart->id; // be carefull the reference must be under this request : [BuysterRef][YYYYMMDDhhmmss][cartId]
$operation->setReference($ref);

//call webservice buyster
$webService = new BuysterWebService();
$url = $webService->getUrl($cart->getOrderTotal(), $_SERVER["REMOTE_ADDR"], $cart->id, $ref, $operation->getOperation(), $cart->id_customer); //amount, address ip, orderid, transactionRef , type , customerId
if ($url['responseCode'] == '00')
	header("location:".$url['redirectionURL']);
else
	echo $url['responseDescription'];
include(dirname(__FILE__).'/../../footer.php');
?>