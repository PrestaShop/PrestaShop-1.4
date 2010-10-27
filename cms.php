<?php

include(dirname(__FILE__).'/config/config.inc.php');

//will be initialized bellow...
if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
	$rewrited_url = null;

include(dirname(__FILE__).'/init.php');

if (($id_cms = intval(Tools::getValue('id_cms'))) AND $cms = new CMS(intval($id_cms), intval($cookie->id_lang)) AND Validate::isLoadedObject($cms) AND
	($cms->active OR (Tools::getValue('adtoken') == Tools::encrypt('PreviewCMS'.$cms->id) AND file_exists(dirname(__FILE__).'/'.Tools::getValue('ad').'/ajax.php'))))
{
	/* rewrited url set */
	$rewrited_url = $link->getCmsLink($cms, $cms->link_rewrite);
	Tools::AddJS(_THEME_JS_DIR_.'cms.js');
	Tools::AddCSS(_THEME_CSS_DIR_.'cms.css');
	
	include(dirname(__FILE__).'/header.php');
	$smarty->assign(array(
		'cms' => $cms,
		'content_only' => intval(Tools::getValue('content_only'))
	));
	$smarty->display(_PS_THEME_DIR_.'cms.tpl');
	include(dirname(__FILE__).'/footer.php');
}
elseif (($id_cms_category = intval(Tools::getValue('id_cms_category'))) AND $cms_category = new CMSCategory(intval(Tools::getValue('id_cms_category')), intval($cookie->id_lang)) AND Validate::isLoadedObject($cms_category))
{
	$rewrited_url = $link->getCmsLink($cms_category, $cms_category->link_rewrite);
		
	include(dirname(__FILE__).'/header.php');
	$smarty->assign(array(
		'category' => $cms_category,
		'sub_category' => $cms_category->getSubCategories(intval($cookie->id_lang)),
		'cms_pages' => CMS::getCMSPages(intval($cookie->id_lang))
	));
	$smarty->display(_PS_THEME_DIR_.'cms.tpl');
	include(dirname(__FILE__).'/footer.php');

}
else
	Tools::redirect('404.php');

?>
