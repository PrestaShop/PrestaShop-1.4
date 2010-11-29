<?php

class SitemapControllerCore extends FrontController
{
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'sitemap.css');
		Tools::addJS(_THEME_JS_DIR_.'tools/treeManagement.js');
	}
	
	public function process()
	{
		parent::process();
		
		$depth = 0;
		$categTree = Category::getRootCategory()->recurseLiteCategTree($depth);
		$cms = CMSCategory::getRecurseCategory(_USER_ID_LANG_, 1, 1, 1);
		$this->smarty->assign('categoriesTree', $categTree);
		$this->smarty->assign('categoriescmsTree', $cms);
		$this->smarty->assign('voucherAllowed', (int)(Configuration::get('PS_VOUCHERS')));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'sitemap.tpl');
	}
}

?>