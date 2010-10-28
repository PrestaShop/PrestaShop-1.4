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
		$this->smarty->assign('categoriesTree', $categTree);
		$this->smarty->assign('voucherAllowed', intval(Configuration::get('PS_VOUCHERS')));

		if (Module::isInstalled('blockcms'))
		{
			$this->smarty->assign('blockCMSInstalled', true);
			$cms_module = Module::getInstanceByName('blockcms');
			$this->smarty->assign('blocks', $cms_module->getAllBlocksCMS());
			$this->smarty->assign('pages', $cms_module->getAllCMStitles());
		}
		else
		{
			$this->smarty->assign('blockcms', false);
			$cms = CMS::listCms(intval($this->cookie->id_lang));
			$id_cms = array();
			foreach($cms AS $row)
				$id_cms[] = intval($row['id_cms']);
			$this->smarty->assign('cmslinks', CMS::getLinks(intval($this->cookie->id_lang), $id_cms ? $id_cms : NULL));	
		}
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'sitemap.tpl');
	}
}