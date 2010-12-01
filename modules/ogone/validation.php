<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/ogone.php');

$ogone = new Ogone();

/* First we need to check var presence */
$neededVars = array('orderID', 'amount', 'currency', 'PM', 'ACCEPTANCE', 'STATUS', 'CARDNO', 'PAYID', 'NCERROR', 'BRAND', 'SHASIGN');
$params = '<br /><br />'.$ogone->l('Received parameters:').'<br /><br />';

foreach ($neededVars AS $k)
	if (!isset($_GET[$k]))
		die($ogone->l('Missing parameter:').' '.$k);
	else
		$params .= $k.' : '.$_GET[$k].'<br />';

/* Then, load the customer cart and perform some checks */
$cart = new Cart((int)($_GET['orderID']));
if (Validate::isLoadedObject($cart))
{
	/* Fist, check for a valid SHA-1 signature */
	$ogoneParams = array();
	foreach ($_GET as $key => $value)
	if (strtoupper($key) != 'SHASIGN' AND $value != '')
		$ogoneParams[strtoupper($key)] = $value;

	ksort($ogoneParams);
	$shasign = '';
	foreach ($ogoneParams as $key => $value)
		$shasign .= strtoupper($key).'='.$value.Configuration::get('OGONE_SHA_OUT');
	$sha1 = strtoupper(sha1($shasign));	

	if ($sha1 == $_GET['SHASIGN'])
	{
		switch ($_GET['STATUS'])
		{
			case 1:
				/* Real error or payment canceled */
				$ogone->validate(_PS_OS_ERROR_, 0, $_GET['NCERROR'].$params);
				break;
			case 2:
				/* Real error - authorization refused */
				$ogone->validate(_PS_OS_ERROR_, 0, $ogone->l('Error (auth. refused)').'<br />'.$_GET['NCERROR'].$params);
				break;
			case 5:
			case 9:
				/* Payment OK */
				$ogone->validate(_PS_OS_PAYMENT_, floatval($_GET['amount']), $ogone->l('Payment authorized / OK').$params, true);
				break;
			case 6:
			case 7:
			case 8:
				// Payment canceled later
				if ($id_order = (int)(Order::getOrderByCartId((int)($_GET['orderID']))))
				{
					// Update the amount really paid
					$order = new Order($id_order);
					$order->total_paid_real = 0;
					$order->update();
					
					// Send a new message and change the state
					$history = new OrderHistory();
					$history->id_order = $id_order;
					$history->changeIdOrderState(_PS_OS_ERROR_, $id_order);
					$history->addWithemail(true, array());
				}
				break;
			default:
				$ogone->validate(_PS_OS_ERROR_, floatval($_GET['amount']), $ogone->l('Unknown status:').' '.$_GET['STATUS'].$params);
		}
		exit;
	}
	else
	{
		$message = $ogone->l('Invalid SHA-1 signature').'<br />'.$ogone->l('SHA-1 given:').' '.$_GET['SHASIGN'].'<br />'.$ogone->l('SHA-1 calculated:').' '.$sha1.'<br />'.$ogone->l('Plain key:').' '.$shasign;
		$ogone->validate(_PS_OS_ERROR_, 0, $message.'<br />'.$params);
	}
}
	
