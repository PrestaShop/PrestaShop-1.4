<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

if (!Tools::getValue('width') AND !Tools::getValue('height'))
	require_once(dirname(__FILE__).'/../../header.php');

$iso_code = Tools::strtoupper(Language::getIsoById($cookie->id_lang ? intval($cookie->id_lang) : 1));
$smarty->assign('iso_code', Tools::strtolower($iso_code));

echo Module::display(dirname(__FILE__).'/paypalapi', 'about.tpl'); 

if (!Tools::getValue('width') AND !Tools::getValue('height'))
	require_once(dirname(__FILE__).'/../../footer.php');
