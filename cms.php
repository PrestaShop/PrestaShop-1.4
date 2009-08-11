<?php

include(dirname(__FILE__).'/config/config.inc.php');

//will be initialized bellow...
if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
	$rewrited_url = null;

include(dirname(__FILE__).'/init.php');

if (($id_cms = intval(Tools::getValue('id_cms'))) AND $cms = new CMS(intval($id_cms), intval($cookie->id_lang)) AND Validate::isLoadedObject($cms))
{
	/* rewrited url set */
	$rewrited_url = $link->getCmsLink($cms, $cms->link_rewrite);
	
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
