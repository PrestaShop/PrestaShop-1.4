<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/paypal.php');

$errors = '';
$result = false;
$paypal = new Paypal();

// Fill params
$params = 'cmd=_notify-validate';
foreach ($_POST AS $key => $value)
	$params .= '&'.$key.'='.urlencode(stripslashes($value));

// PayPal Server
$paypalServer = 'www.'.(Configuration::get('PAYPAL_SANDBOX') ? 'sandbox.' : '').'paypal.com';

// Getting PayPal data...
if (function_exists('curl_exec'))
{
	// curl ready
	$ch = curl_init('https://' . $paypalServer . '/cgi-bin/webscr');
    
	// If the above fails, then try the url with a trailing slash (fixes problems on some servers)
 	if (!$ch)
		$ch = curl_init('https://' . $paypalServer . '/cgi-bin/webscr/');
	
	if (!$ch)
		$errors .= $paypal->getL('connect').' '.$paypal->getL('curlmethodfailed');
	else
	{
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		if ($result != 'VERIFIED')
			$errors .= $paypal->getL('curlmethod').$result.' cURL error:'.curl_error($ch);
		curl_close($ch);
	}
}
elseif (($fp = @fsockopen('ssl://' . $paypalServer, 443, $errno, $errstr, 30)) || ($fp = @fsockopen($paypalServer, 80, $errno, $errstr, 30)))
{
	// fsockopen ready
	$header = 'POST /cgi-bin/webscr HTTP/1.0'."\r\n" .
          'Host: '.$paypalServer."\r\n".
          'Content-Type: application/x-www-form-urlencoded'."\r\n".
          'Content-Length: '.Tools::strlen($params)."\r\n".
          'Connection: close'."\r\n\r\n";
	fputs($fp, $header.$params);
 	
 	$read = '';
 	while (!feof($fp))
	{
		$reading = trim(fgets($fp, 1024));
		$read .= $reading;
		if (($reading == 'VERIFIED') || ($reading == 'INVALID'))
		{
		 	$result = $reading;
			break;
		}
 	}
	if ($result != 'VERIFIED')
		$errors .= $paypal->getL('socketmethod').$result;
	fclose($fp);
}
else
	$errors = $paypal->getL('connect').$paypal->getL('nomethod');

// Printing errors...
if ($result == 'VERIFIED') {
	if (!isset($_POST['mc_gross']))
		$errors .= $paypal->getL('mc_gross').'<br />';
	if (!isset($_POST['payment_status']))
		$errors .= $paypal->getL('payment_status').'<br />';
	elseif ($_POST['payment_status'] != 'Completed')
		$errors .= $paypal->getL('payment').$_POST['payment_status'].'<br />';
	if (!isset($_POST['custom']))
		$errors .= $paypal->getL('custom').'<br />';
	if (!isset($_POST['txn_id']))
		$errors .= $paypal->getL('txn_id').'<br />';
	if (!isset($_POST['mc_currency']))
		$errors .= $paypal->getL('mc_currency').'<br />';
	if (empty($errors))
	{
		$cart = new Cart(intval($_POST['custom']));
		if (!$cart->id)
			$errors = $paypal->getL('cart').'<br />';
		elseif (Order::getOrderByCartId(intval($_POST['custom'])))
			$errors = $paypal->getL('order').'<br />';
		else
			$paypal->validateOrder($_POST['custom'], _PS_OS_PAYMENT_, floatval($_POST['mc_gross']), $paypal->displayName, $paypal->getL('transaction').$_POST['txn_id']);
	}
} else {
	$errors .= $paypal->getL('verified');
}

if (!empty($errors) AND isset($_POST['custom']))
	$paypal->validateOrder(intval($_POST['custom']), _PS_OS_ERROR_, 0, $paypal->displayName, $errors.'<br />');

?>