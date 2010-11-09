<?php

class CmsControllerCore extends FrontController
{
	public $assignCase;
	public $cms;
	
	public function preProcess()
	{
		parent::preProcess();

		if (($id_cms = intval(Tools::getValue('id_cms'))) AND $this->cms = new CMS(intval($id_cms), intval($this->cookie->id_lang)) AND Validate::isLoadedObject($this->cms) AND
			($this->cms->active OR (Tools::getValue('adtoken') == Tools::encrypt('PreviewCMS'.$this->cms->id) AND file_exists(dirname(__FILE__).'/../'.Tools::getValue('ad').'/ajax.php'))))
			$this->assignCase = 1;
		elseif (($id_cms_category = intval(Tools::getValue('id_cms_category'))) AND $cms_category = new CMSCategory(intval(Tools::getValue('id_cms_category')), intval($this->cookie->id_lang)) AND Validate::isLoadedObject($cms_category))
			$this->assignCase = 2;
		else
			Tools::redirect('404.php');
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
				'content_only' => intval(Tools::getValue('content_only'))
			));
		}
		elseif ($this->assignCase == 1)
		{
			$this->smarty->assign(array(
				'category' => $cms_category,
				'sub_category' => $cms_category->getSubCategories(intval($this->cookie->id_lang)),
				'cms_pages' => CMS::getCMSPages(intval($this->cookie->id_lang))
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