<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

if (!Tools::getValue('width') AND !Tools::getValue('height'))
	require_once(dirname(__FILE__).'/../../header.php');

$xmlFile = _PS_MODULE_DIR_.'referralprogram/referralprogram.xml';

if (file_exists($xmlFile))
{
	if ($xml = @simplexml_load_file($xmlFile))
	{
		$smarty->assign(array(
			'xml' => $xml,
			'paragraph' => 'paragraph_'.$cookie->id_lang
		));
	}
}

echo Module::display(dirname(__FILE__).'/referralprogram', 'referralprogram-rules.tpl'); 

if (!Tools::getValue('width') AND !Tools::getValue('height'))
	require_once(dirname(__FILE__).'/../../footer.php');

?>