<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

function displayError($msg)
{
	$translations = array(
		'Invalid key.' => Tools::displayError('Invalid key.'),
		'This product doesn\'t exists in our store.' => Tools::displayError('This product doesn\'t exists in our store.'),
		'This product has been deleted.' => Tools::displayError('This product has been deleted.'),
		'This file no more exists.'	=> Tools::displayError('This file no more exists.'),
		'The product deadline is in the past.' => Tools::displayError('The product deadline is in the past.'),
		'Dear customer, you exceed the expiration date.' => Tools::displayError('Dear customer, you exceed the expiration date.'),
		'You reach the maximum number of allowed downloads.' => Tools::displayError('You reach the maximum number of allowed downloads.'));
?>
<script type="text/javascript">
<!--
alert("<?php echo html_entity_decode($translations[$msg], ENT_QUOTES, 'utf-8'); ?>");
window.location.href = '<?php echo __PS_BASE_URI__ ?>';
-->
</script>
<?php
	exit();
}

$cookie = new Cookie('psAdmin');
if ($cookie->isLoggedBack() AND Tools::getValue('file'))
{
	/* Admin can directly access to file */
	$filename = Tools::getValue('file');
	if (!Validate::isSha1($filename))
		die(Tools::displayError());
	$file = _PS_DOWNLOAD_DIR_.strval(preg_replace('/\.{2,}/', '.',$filename));
	$filename = ProductDownload::getFilenameFromFilename(Tools::getValue('file'));
	if (!file_exists($file))
		Tools::redirect('index.php');
}
else
{
	if (!($key = Tools::getValue('key')))
		displayError('Invalid key.');

	$cookie = new Cookie('ps');
	Tools::setCookieLanguage();
	if (!$cookie->isLogged())
		Tools::redirect('authentication.php?back=get-file.php&key='.$key);

	/* Key format: <sha1-filename>-<hashOrder> */
	$tmp = explode('-', $key);
	if (sizeof($tmp) != 2)
		displayError('Invalid key.');

	$filename = $tmp[0];
	$hash = $tmp[1];

	if (!($info = OrderDetail::getDownloadFromHash($hash)))
		displayError('This product doesn\'t exists in our store.');

	/* Product no more present in catalog */
	if (!isset($info['id_product_download']) OR empty($info['id_product_download']))
		displayError('This product has been deleted.');

	if (!file_exists(_PS_DOWNLOAD_DIR_.$filename))
		displayError('This file no more exists.');

	$now = time();

	$product_deadline = strtotime($info['download_deadline']);
	if ($now > $product_deadline AND $info['download_deadline'] != '0000-00-00 00:00:00')
		displayError('The product deadline is in the past.');

	$customer_deadline = strtotime($info['date_expiration']);
	if ($now > $customer_deadline AND $info['date_expiration'] != '0000-00-00 00:00:00')
		displayError('Dear customer, you exceed the expiration date.');

	if ($info['download_nb'] >= $info['nb_downloadable'] AND $info['nb_downloadable'])
		displayError('You reach the maximum number of allowed downloads.');

	/* Access is authorized -> increment download value for the customer */
	OrderDetail::incrementDownload($info['id_order_detail']);

	$file = _PS_DOWNLOAD_DIR_.$info['physically_filename'];
	$filename = $info['display_filename'];
}

/* Detect mime content type */
$mime_type = false;
if (function_exists('finfo_open'))
{
	$finfo = @finfo_open(FILEINFO_MIME);
	$mime_type = @finfo_file($finfo, $file);
	@finfo_close($finfo);
}
elseif (function_exists('mime_content_type'))
	$mime_type = @mime_content_type($file);
elseif (function_exists('exec'))
	$mime_type = trim(@exec('file -bi '.escapeshellarg($file)));

/* Set headers for download */
header('Content-Transfer-Encoding: binary');
if ($mime_type)
	header('Content-Type: '.$mime_type);
header('Content-Length: '.filesize($file));
header('Content-Disposition: attachment; filename="'.$filename.'"');
readfile($file);

exit;

?>
