<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

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
	$ch = curl_init('https://'.$paypalServer.'/cgi-bin/webscr');
    
	// If the above fails, then try the url with a trailing slash (fixes problems on some servers)
 	if (!$ch)
		$ch = curl_init('https://'.$paypalServer.'/cgi-bin/webscr/');
	
	if (!$ch)
	{
		$errors .= $paypal->l('Problem connecting to the PayPal server.');
		$errors .= ' ';
		$errors .= $paypal->l('Connection using cURL failed');
	}
	else
	{
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		if (strtoupper($result) != 'VERIFIED')
			$errors .= $paypal->l('Verification failure (using cURL). Returned: ').$result.' cURL error:'.curl_error($ch);
		curl_close($ch);
	}
}
elseif (($fp = @fsockopen('ssl://'.$paypalServer, 443, $errno, $errstr, 30)) || ($fp = @fsockopen($paypalServer, 80, $errno, $errstr, 30)))
{
	// fsockopen ready

	fputs($fp, 'POST /cgi-bin/webscr HTTP/1.0'."\r\n".'Host: '.$paypalServer."\r\n".
          'Content-Type: application/x-www-form-urlencoded'."\r\n".'Content-Length: '.Tools::strlen($params)."\r\n".
          'Connection: close'."\r\n\r\n".$params);

 	$read = '';
 	while (!feof($fp))
	{
		$reading = trim(fgets($fp, 1024));
		$read .= $reading;
		if (strtoupper($reading) == 'VERIFIED' OR strtoupper($reading) == 'INVALID')
		{
		 	$result = $reading;
			break;
		}
 	}
	if (strtoupper($result) != 'VERIFIED')
		$errors .= $paypal->l('Verification failure (using fsockopen). Returned: ').$result;
	fclose($fp);
}
else
{
	$errors = $paypal->l('Problem connecting to the PayPal server.');
	$errors .= $paypal->l('No communications transport available.');
}

$cart_secure = (isset($_POST['custom']) ? explode('_', $_POST['custom']) : array());
// If there isn't any cart ID, set it to "0"
if (!isset($cart_secure[0]))
	$cart_secure[0] = 0;
// If there isn't any secure key, set it to anything short of "false"
if (!isset($cart_secure[1]))
	$cart_secure[1] = '42';

// Printing errors...
if (strtoupper($result) == 'VERIFIED')
{
	if (!isset($_POST['mc_gross']))
	{
		$errors .= $paypal->l('PayPal key \'mc_gross\' not specified, cannot control amount paid.').'<br />';
		$_POST['mc_gross'] = 0;
	}
	if (!isset($_POST['payment_status']))
	{
		$errors .= $paypal->l('PayPal key \'payment_status\' not specified, cannot control payment validity').'<br />';
		$_POST['payment_status'] = 'ko';
	}
	elseif (strtoupper($_POST['payment_status']) != 'COMPLETED')
		$errors .= $paypal->l('Payment: ').$_POST['payment_status'].'<br />';
	if (!isset($_POST['custom']))
		$errors .= $paypal->l('PayPal key \'custom\' not specified, cannot relay to cart').'<br />';
	if (!isset($_POST['txn_id']))
	{
		$errors .= $paypal->l('PayPal key \'txn_id\' not specified, transaction unknown').'<br />';
		$_POST['txn_id'] = 0;
	}
	if (!isset($_POST['mc_currency']))
		$errors .= $paypal->l('PayPal key \'mc_currency\' not specified, currency unknown').'<br />';
	if (empty($errors))
	{
		$cart = new Cart((int)$cart_secure[0]);
		if (!$cart->id)
			$errors = $paypal->l('Cart not found').'<br />';
		elseif (Order::getOrderByCartId((int)($cart_secure[0])))
			$errors = $paypal->l('Order has already been placed').'<br />';
		else
			$paypal->validateOrder((int)$cart_secure[0], Configuration::get('PS_OS_PAYMENT'), (float)($_POST['mc_gross']),
				$paypal->displayName, $paypal->l('Paypal Transaction ID: ').$_POST['txn_id'], array('transaction_id' => $_POST['txn_id'],
				'payment_status' => $_POST['payment_status']), null, false, $cart_secure[1]);
	}
}
else
	$errors .= $paypal->l('The PayPal transaction could not be VERIFIED.');

// Set transaction details if pcc is defiend in PaymentModule class
if (isset($paypal->pcc))
	$paypal->pcc->transaction_id = (isset($_POST['txn_id']) ? $_POST['txn_id'] : '');

if (!empty($errors) AND isset($_POST['custom']))
{
	if (strtoupper($_POST['payment_status']) == 'PENDING')
		$paypal->validateOrder((int)$cart_secure[0], Configuration::get('PS_OS_PAYPAL'), (float)$_POST['mc_gross'], 
			$paypal->displayName, $paypal->l('Paypal Transaction ID: ').$_POST['txn_id'].'<br />'.$errors, 
			array('transaction_id' => $_POST['txn_id'], 'payment_status' => $_POST['payment_status']), NULL, false, $cart_secure[1]);
	else
		$paypal->validateOrder((int)$cart_secure[0], Configuration::get('PS_OS_ERROR'), 0, $paypal->displayName,
			$errors.'<br />', array(), NULL, false, $cart_secure[1]);

}
