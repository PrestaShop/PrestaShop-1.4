<?php

require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');

$a = new Attachment(intval(Tools::getValue('id_attachment')), intval($cookie->id_lang));
$file = _PS_DOWNLOAD_DIR_.$a->file;

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
header('Content-Disposition: attachment; filename="'.$a->name.'"');
readfile($file);

exit;

?>