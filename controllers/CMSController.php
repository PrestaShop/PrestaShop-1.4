<?php

class CmsControllerCore extends FrontController
{
	public $assignCase;
	public $cms;
	public $cms_category;
	
	public function preProcess()
	{
		parent::preProcess();
        
		if (($id_cms = (int)(Tools::getValue('id_cms'))) 
		    AND $this->cms = new CMS((int)($id_cms), (int)($this->cookie->id_lang)) 
		    AND Validate::isLoadedObject($this->cms) AND
			    ($this->cms->active OR (Tools::getValue('adtoken') == Tools::encrypt('PreviewCMS'.$this->cms->id) 
			    AND file_exists(dirname(__FILE__).'/../'.Tools::getValue('ad').'/ajax.php'))))
			$this->assignCase = 1;
		elseif (($id_cms_category = (int)(Tools::getValue('id_cms_category'))) AND $this->cms_category = new CMSCategory((int)(Tools::getValue('id_cms_category')), (int)($this->cookie->id_lang)) AND Validate::isLoadedObject($this->cms_category))
			$this->assignCase = 2;
		else
			Tools::redirect('404.php');
		
	    $rewrite_infos = (isset($id_cms) AND !isset($id_cms_category)) ? CMS::getUrlRewriteInformations($id_cms) : CMSCategory::getUrlRewriteInformations($id_cms_category);
		$default_rewrite = array();
		foreach ($rewrite_infos AS $infos)
		{
		    $arr_link = (isset($id_cms) AND !isset($id_cms_category)) ?
		        $this->link->getCMSLink($id_cms, $infos['link_rewrite'], $this->ssl, $infos['id_lang']) :
		        $this->link->getCMSCategoryLink($id_cms_category, $infos['link_rewrite'], $infos['id_lang']);
			$default_rewrite[$infos['id_lang']] = $arr_link;
		}
		
		$this->smarty->assign('lang_rewrite_urls', $default_rewrite);
	}
	
	public function setMedia()
	{
		parent::setMedia();
		
		if ($this->assignCase == 1)
		{
			Tools::AddJS(_THEME_JS_DIR_.'cms.js');
			Tools::AddCSS(_THEME_CSS_DIR_.'cms.css');
		}
	}
	
	public function process()
	{
		parent::process();
		if ($this->assignCase == 1)
		{
			$this->smarty->assign(array(
				'cms' => $this->cms,
				'content_only' => (int)(Tools::getValue('content_only')),
				'path' => ((isset($this->cms->id_cms_category) AND $this->cms->id_cms_category) ? Tools::getFullPath((int)($this->cms->id_cms_category), $this->cms->meta_title, 'CMS') : Tools::getFullPath(1, $this->cms->meta_title, 'CMS'))
			));
		}
		elseif ($this->assignCase == 2)
		{
			$this->smarty->assign(array(
				'category' => $this->cms_category,
				'sub_category' => $this->cms_category->getSubCategories((int)($this->cookie->id_lang)),
				'cms_pages' => CMS::getCMSPages((int)($this->cookie->id_lang), (int)($this->cms_category->id) ),
				'path' => Tools::getPath((int)($this->cms_category->id), $this->cms_category->name, false, 'CMS'),
			));
		}
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'cms.tpl');
	}
}

?>