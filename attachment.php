<?php

require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');

$a = new Attachment((int)(Tools::getValue('id_attachment')), (int)($cookie->id_lang));

header('Content-Transfer-Encoding: binary');
header('Content-Type: '.$a->mime);
header('Content-Length: '.filesize(_PS_DOWNLOAD_DIR_.$a->file));
header('Content-Disposition: attachment; filename="'.utf8_decode($a->file_name).'"');
readfile(_PS_DOWNLOAD_DIR_.$a->file);
exit;

?>