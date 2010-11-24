<?php

/**
  * Catalog tab for admin panel, AdminCMS.php
  * Tab has been separated in 3 files : this one, adminCMSCategories.php and adminCMS.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include(PS_ADMIN_DIR.'/tabs/AdminCMSCategories.php');
include(PS_ADMIN_DIR.'/tabs/AdminCMS.php');

class AdminCMSContent extends AdminTab
{
	/** @var object adminCMSCategories() instance */
	private $adminCMSCategories;

	/** @var object adminCMS() instance */
	private $adminCMS;

	/** @var object Category() instance for navigation*/
	private static $_category = NULL;

	public function __construct()
	{
		/* Get current category */
		$id_cms_category = (int)(Tools::getValue('id_cms_category', Tools::getValue('id_cms_category_parent', 1)));
		self::$_category = new CMSCategory($id_cms_category);
		if (!Validate::isLoadedObject(self::$_category))
			die('Category cannot be loaded');

		$this->table = array('cms_category', 'cms');
		$this->adminCMSCategories = new adminCMSCategories();
		$this->adminCMS = new adminCMS();

		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
	public static function getCurrentCMSCategory()
	{
		return self::$_category;
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->adminCMSCategories->tabAccess = $this->tabAccess;
		$this->adminCMS->tabAccess = $this->tabAccess;
		return $result;
	}

	public function postProcess()
	{
		if(Tools::isSubmit('submitDelcms_category') OR Tools::isSubmit('submitAddcms_categoryAndBackToParent') OR Tools::isSubmit('submitAddcms_category') OR isset($_GET['deletecms_category']))
			$this->adminCMSCategories->postProcess();
		if (Tools::isSubmit('submitDelcms') OR Tools::isSubmit('fakeSubmitAddcmsAndPreview') OR Tools::isSubmit('submitAddcms') OR isset($_GET['deletecms']))
			$this->adminCMS->postProcess();
	}

	public function displayErrors()
	{
		parent::displayErrors();
		$this->adminCMS->displayErrors();
		$this->adminCMSCategories->displayErrors();
	}

	public function display()
	{
		global $currentIndex;

		if (((Tools::isSubmit('submitAddcms_category') OR Tools::isSubmit('submitAddcms_categoryAndStay')) AND sizeof($this->adminCMSCategories->_errors)) OR isset($_GET['updatecms_category']) OR isset($_GET['addcms_category']))
		{
			$this->adminCMSCategories->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
			
		}
		elseif (((Tools::isSubmit('submitAddcms') OR Tools::isSubmit('submitAddcmsAndStay')) AND sizeof($this->adminCMS->_errors)) OR isset($_GET['updatecms']) OR isset($_GET['addcms']))
		{
			$this->adminCMS->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		
		}
		else
		{
		$id_cms_category = (int)(Tools::getValue('id_cms_category'));
		if (!$id_cms_category)
			$id_cms_category = 1;
		$cms_tabs = array('cms_category', 'cms');
		// Cleaning links
		$catBarIndex = $currentIndex;
		foreach ($cms_tabs AS $tab)
			if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway')) 
				$catBarIndex = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', $currentIndex);
		echo '<div class="cat_bar"><span style="color: #3C8534;">'.$this->l('Current category').' :</span>&nbsp;&nbsp;&nbsp;'.getPath($catBarIndex, $id_cms_category,'','','cms').'</div>';
		echo '<h2>'.$this->l('Categories').'</h2>';
		$this->adminCMSCategories->display($this->token);
		echo '<div style="margin:10px">&nbsp;</div>';
		echo '<h2>'.$this->l('Pages in this category').'</h2>';
		$this->adminCMS->display($this->token);
		}
		
	}
}

?>