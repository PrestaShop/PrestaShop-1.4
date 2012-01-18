<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(_PS_MODULE_DIR_.'/buyster/classes/BuysterWebService.php');
require_once(_PS_MODULE_DIR_.'/buyster/classes/BuysterOperation.php');
require_once(_PS_MODULE_DIR_.'/buyster/buyster.php');

$action = Tools::safeOutput($_GET['action']);
$reference = Tools::safeOutput($_GET['reference']);
$price = Tools::safeOutput($_GET['price']);
$param = Tools::safeOutput($_GET['param']);
$orderId = Tools::safeOutput($_GET['id_order']);

$buyster = new Buyster();
$order = new Order($orderId);
$webService = new BuysterWebService();

global $currentIndex;

if ($action == "DUPLICATE")
{
	$parametre = 'fromTransactionReference='.$reference.';';
	$result = $webService->operation($action, $param, $price, $parametre);
}
else if ($action == "VALIDATE")
{
	$parametre = 'operationCaptureNewDelay='.$param.';';
	$result = $webService->operation($action, $reference, $price, $parametre);
}
else
{
	$parametre = NULL;
	$result = $webService->operation($action, $reference, $price, $parametre);
}


if ($result->responseCode == "00")
{
	$history = new OrderHistory();
	$history->id_order = (int)$orderId;
	
	if ($action == "DUPLICATE")
	{
		$operation = BuysterOperation::getOperationId($order->id_cart);
		if ($operation == 'paymentValidation')
			$history->changeIdOrderState((int)Configuration::get('BUYSTER_PAYMENT_STATE_VALIDATION'), (int)$orderId);
		else
			$history->changeIdOrderState((int)Configuration::get('BUYSTER_PAYMENT_STATE'), (int)$orderId);
		BuysterOperation::setReferenceReference($param, $reference);
		$reference = $param;
	}
	if ($action == "VALIDATE")
		$history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), (int)$orderId);
	if ($action == "REFUND")
		$history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), (int)$orderId);
	if ($action == "CANCEL")
		$history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), (int)$orderId);
	$history->addWithemail();
}

$return = '';
if ($result->responseCode == "99")
	$return = '<span style="color:red">Probl&egrave;me technique au niveau du serveur Buyster</span><br/>';
if ($result->responseCode == "00")
{
	$return .= '<span style="color:green">L\'&eacute;tat de votre commande a &eacute;t&eacute; modifi&eacute;.</span><br/>';
}
else if ($result->responseCode == "24")
	$return = '<span style="color:red">Op&eacuteration impossible. L\'op&eacuteration que vous souhaitez r&eacute;aliser n\'est pas compatible avec l\'&eacute;tat de la transaction.</span><br/>';
else
	$return .= $result->responseDescription.'<br/>';

echo $return;

/*
global $smarty, $cookie;
$cookie->id_lang = '2';
$smarty->assign('content', $buyster->getContentAdminOrder($orderId));
$smarty->assign('return', $return);
$smarty->display('tpl/adminOrder.tpl');
*/
?>