<?php

require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');

$a = new Attachment(intval(Tools::getValue('id_attachment')), intval($cookie->id_lang));

header('Content-Transfer-Encoding: binary');
header('Content-Type: '.$a->mime);
header('Content-Length: '.filesize(_PS_DOWNLOAD_DIR_.$a->file));
header('Content-Disposition: attachment; filename="'.$a->name.'"');
readfile(_PS_DOWNLOAD_DIR_.$a->file);
exit;

?>