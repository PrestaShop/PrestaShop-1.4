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
	if (empty($filename))
	{
		$newFileName = Tools::getValue('filename');
		if (!empty($newFileName))
			$filename = Tools::getValue('filename');
		else
			$filename = 'file';
	}

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

if (empty($mime_type))
{
	$bName = basename($filename);
	$bName = explode('.', $bName);
	$bName = strtolower($bName[count($bName) - 1]);
	
	$mimeTypes = array(
	'ez' => 'application/andrew-inset',
	'hqx' => 'application/mac-binhex40',
	'cpt' => 'application/mac-compactpro',
	'doc' => 'application/msword',
	'bin' => 'application/octet-stream',
	'dms' => 'application/octet-stream',
	'lha' => 'application/octet-stream',
	'lzh' => 'application/octet-stream',
	'exe' => 'application/octet-stream',
	'class' => 'application/octet-stream',
	'so' => 'application/octet-stream',
	'dll' => 'application/octet-stream',
	'oda' => 'application/oda',
	'pdf' => 'application/pdf',
	'ai' => 'application/postscript',
	'eps' => 'application/postscript',
	'ps' => 'application/postscript',
	'smi' => 'application/smil',
	'smil' => 'application/smil',
	'wbxml' => 'application/vnd.wap.wbxml',
	'wmlc' => 'application/vnd.wap.wmlc',
	'wmlsc' => 'application/vnd.wap.wmlscriptc',
	'bcpio' => 'application/x-bcpio',
	'vcd' => 'application/x-cdlink',
	'pgn' => 'application/x-chess-pgn',
	'cpio' => 'application/x-cpio',
	'csh' => 'application/x-csh',
	'dcr' => 'application/x-director',
	'dir' => 'application/x-director',
	'dxr' => 'application/x-director',
	'dvi' => 'application/x-dvi',
	'spl' => 'application/x-futuresplash',
	'gtar' => 'application/x-gtar',
	'hdf' => 'application/x-hdf',
	'js' => 'application/x-javascript',
	'skp' => 'application/x-koan',
	'skd' => 'application/x-koan',
	'skt' => 'application/x-koan',
	'skm' => 'application/x-koan',
	'latex' => 'application/x-latex',
	'nc' => 'application/x-netcdf',
	'cdf' => 'application/x-netcdf',
	'sh' => 'application/x-sh',
	'shar' => 'application/x-shar',
	'swf' => 'application/x-shockwave-flash',
	'sit' => 'application/x-stuffit',
	'sv4cpio' => 'application/x-sv4cpio',
	'sv4crc' => 'application/x-sv4crc',
	'tar' => 'application/x-tar',
	'tcl' => 'application/x-tcl',
	'tex' => 'application/x-tex',
	'texinfo' => 'application/x-texinfo',
	'texi' => 'application/x-texinfo',
	't' => 'application/x-troff',
	'tr' => 'application/x-troff',
	'roff' => 'application/x-troff',
	'man' => 'application/x-troff-man',
	'me' => 'application/x-troff-me',
	'ms' => 'application/x-troff-ms',
	'ustar' => 'application/x-ustar',
	'src' => 'application/x-wais-source',
	'xhtml' => 'application/xhtml+xml',
	'xht' => 'application/xhtml+xml',
	'zip' => 'application/zip',
	'au' => 'audio/basic',
	'snd' => 'audio/basic',
	'mid' => 'audio/midi',
	'midi' => 'audio/midi',
	'kar' => 'audio/midi',
	'mpga' => 'audio/mpeg',
	'mp2' => 'audio/mpeg',
	'mp3' => 'audio/mpeg',
	'aif' => 'audio/x-aiff',
	'aiff' => 'audio/x-aiff',
	'aifc' => 'audio/x-aiff',
	'm3u' => 'audio/x-mpegurl',
	'ram' => 'audio/x-pn-realaudio',
	'rm' => 'audio/x-pn-realaudio',
	'rpm' => 'audio/x-pn-realaudio-plugin',
	'ra' => 'audio/x-realaudio',
	'wav' => 'audio/x-wav',
	'pdb' => 'chemical/x-pdb',
	'xyz' => 'chemical/x-xyz',
	'bmp' => 'image/bmp',
	'gif' => 'image/gif',
	'ief' => 'image/ief',
	'jpeg' => 'image/jpeg',
	'jpg' => 'image/jpeg',
	'jpe' => 'image/jpeg',
	'png' => 'image/png',
	'tiff' => 'image/tiff',
	'tif' => 'image/tif',
	'djvu' => 'image/vnd.djvu',
	'djv' => 'image/vnd.djvu',
	'wbmp' => 'image/vnd.wap.wbmp',
	'ras' => 'image/x-cmu-raster',
	'pnm' => 'image/x-portable-anymap',
	'pbm' => 'image/x-portable-bitmap',
	'pgm' => 'image/x-portable-graymap',
	'ppm' => 'image/x-portable-pixmap',
	'rgb' => 'image/x-rgb',
	'xbm' => 'image/x-xbitmap',
	'xpm' => 'image/x-xpixmap',
	'xwd' => 'image/x-windowdump',
	'igs' => 'model/iges',
	'iges' => 'model/iges',
	'msh' => 'model/mesh',
	'mesh' => 'model/mesh',
	'silo' => 'model/mesh',
	'wrl' => 'model/vrml',
	'vrml' => 'model/vrml',
	'css' => 'text/css',
	'html' => 'text/html',
	'htm' => 'text/html',
	'asc' => 'text/plain',
	'txt' => 'text/plain',
	'rtx' => 'text/richtext',
	'rtf' => 'text/rtf',
	'sgml' => 'text/sgml',
	'sgm' => 'text/sgml',
	'tsv' => 'text/tab-seperated-values',
	'wml' => 'text/vnd.wap.wml',
	'wmls' => 'text/vnd.wap.wmlscript',
	'etx' => 'text/x-setext',
	'xml' => 'text/xml',
	'xsl' => 'text/xml',
	'mpeg' => 'video/mpeg',
	'mpg' => 'video/mpeg',
	'mpe' => 'video/mpeg',
	'qt' => 'video/quicktime',
	'mov' => 'video/quicktime',
	'mxu' => 'video/vnd.mpegurl',
	'avi' => 'video/x-msvideo',
	'movie' => 'video/x-sgi-movie',
	'ice' => 'x-conference-xcooltalk');
	
	if (isset($mimeTypes[$bName]))
		$mime_type = $mimeTypes[$bName];
	else
		$mime_type = 'application/octet-stream';
}

/* Set headers for download */
header('Content-Transfer-Encoding: binary');
if ($mime_type)
	header('Content-Type: '.$mime_type);
header('Content-Length: '.filesize($file));
header('Content-Disposition: attachment; filename="'.$filename.'"');
readfile($file);

exit;

?>
