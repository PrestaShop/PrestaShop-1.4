<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (($id_cms = intval(Tools::getValue('id_cms'))) AND $cms = new CMS(intval($id_cms), intval($cookie->id_lang)) AND Validate::isLoadedObject($cms))
{
	include(dirname(__FILE__).'/header.php');
	$smarty->assign(array(
		'cms' => $cms,
		'content_only' => intval(Tools::getValue('content_only'))
	));
	$smarty->display(_PS_THEME_DIR_.'cms.tpl');
	include(dirname(__FILE__).'/footer.php');
}
else
	Tools::redirect('404.php');

?>
