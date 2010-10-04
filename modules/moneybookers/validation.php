<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/moneybookers.php');

$moneyBookers = new MoneyBookers();

$errors = array();

/* Check for mandatory fields */
$requiredFields = array('status', 'md5sig', 'merchant_id', 'pay_to_email', 'mb_amount', 
'mb_transaction_id', 'currency', 'amount', 'transaction_id', 'pay_from_email', 'mb_currency');

foreach ($requiredFields AS $field)
	if (!isset($_POST[$field]))
		$errors[] = 'Missing field '.$field;

/* Check for MD5 signature */
$md5 = strtoupper(md5($_POST['merchant_id'].$_POST['transaction_id'].strtoupper(md5(Configuration::get('MB_SECRET_WORD'))).$_POST['mb_amount'].$_POST['mb_currency'].$_POST['status']));
if ($md5 != $_POST['md5sig'])
	$errors[] = 'Please double-check your Moneybookers account to make sure you have received the payment (Yours / MB) ['.$md5.'] ['.$_POST['md5sig'].']';

$message = '';
foreach ($_POST AS $key => $value)
	$message .= $key.': '.$value."\n";
if (sizeof($errors))
{
	$message .= sizeof($errors).' error(s):'."\n";
	
	/* Force status to 1 - ERROR ! */
	$_POST['status'] = 1;
}
foreach ($errors AS $error)
	$message .= $error."\n";
$message = nl2br(strip_tags($message));

$id_cart = intval(substr($_POST['transaction_id'], 0, strpos($_POST['transaction_id'], '_')));
$status = intval($_POST['status']);
switch ($status)
{
	/* Bankwire */
	case 0:
		$moneyBookers->validateOrder(intval($id_cart), _PS_OS_BANKWIRE_, floatval($_POST['mb_amount']), $moneyBookers->displayName, $message);
		break;

	/* Payment OK */
	case 2:
		$moneyBookers->validateOrder(intval($id_cart), _PS_OS_PAYMENT_, floatval($_POST['mb_amount']), $moneyBookers->displayName, $message);
		break;

	/* Unknown or error */
	default:
		$moneyBookers->validateOrder(intval($id_cart), _PS_OS_ERROR_, 0, $moneyBookers->displayName, $message);
		break;
}

?>
