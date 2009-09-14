<?php

/**
  * Catalog tab for admin panel, AdminCatalog.php
  * Tab has been separated in 3 files : this one, AdminCategories.php and AdminProducts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include(PS_ADMIN_DIR.'/tabs/AdminCategories.php');
include(PS_ADMIN_DIR.'/tabs/AdminProducts.php');
include(PS_ADMIN_DIR.'/tabs/AdminAttributeGenerator.php');
include(PS_ADMIN_DIR.'/tabs/AdminImageResize.php');

class AdminCatalog extends AdminTab
{
	/** @var object AdminCategories() instance */
	private $adminCategories;

	/** @var object AdminProducts() instance */
	private $adminProducts;

	/** @var object AttributeGenerator() instance */
	private $attributeGenerator;

	/** @var object AttributeGenerator() instance */
	private $imageResize;

	/** @var object Category() instance for navigation*/
	private static $_category = NULL;

	public function __construct()
	{
		/* Get current category */
		$id_category = abs(intval(Tools::getValue('id_category')));
		if (!$id_category) $id_category = 1;
		self::$_category = new Category($id_category);
		if (!Validate::isLoadedObject(self::$_category))
			die('Category cannot be loaded');

		$this->table = array('category', 'product');
		$this->adminCategories = new AdminCategories();
		$this->adminProducts = new AdminProducts();
		$this->attributeGenerator = new AdminAttributeGenerator();
		$this->imageResize = new AdminImageResize();

		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
	public static function getCurrentCategory()
	{
		return self::$_category;
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->adminCategories->tabAccess = $this->tabAccess;
		$this->adminProducts->tabAccess = $this->tabAccess;
		return $result;
	}

	public function postProcess()
	{
		if (!Tools::getValue('id_product'))
			$this->adminCategories->postProcess();
		elseif (isset($_GET['attributegenerator']))
			$this->attributeGenerator->postProcess();
		elseif (isset($_GET['imageresize']))
			$this->imageResize->postProcess();
		$this->adminProducts->postProcess($this->token);
	}

	public function displayErrors()
	{
		$this->adminProducts->displayErrors();
		$this->adminCategories->displayErrors();
	}

	public function display()
	{
		global $currentIndex;

		if (((Tools::isSubmit('submitAddcategory') OR Tools::isSubmit('submitAddcategoryAndStay')) AND sizeof($this->adminCategories->_errors)) OR isset($_GET['updatecategory']) OR isset($_GET['addcategory']))
		{
			$this->adminCategories->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		elseif (((Tools::isSubmit('submitAddproduct') OR Tools::isSubmit('submitAddproductAndStay')) AND sizeof($this->adminProducts->_errors)) OR Tools::isSubmit('updateproduct') OR Tools::isSubmit('addproduct'))
		{
			$this->adminProducts->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		elseif (isset($_GET['attributegenerator']))
			$this->attributeGenerator->displayForm();
		elseif (isset($_GET['imageresize']))
			$this->imageResize->displayForm();
		elseif (!isset($_GET['editImage']))
		{
			$id_category = intval(Tools::getValue('id_category'));
			if (!$id_category)
				$id_category = 1;
			echo '<div class="cat_bar"><span style="color: #3C8534;">'.$this->l('Current category').' :</span>&nbsp;&nbsp;&nbsp;'.getPath($currentIndex, $id_category).'</div>';
			echo '<h2>'.$this->l('Categories').'</h2>';
			$this->adminCategories->display($this->token);
			echo '<div style="margin:10px">&nbsp;</div>';
			echo '<h2>'.$this->l('Products in this category').'</h2>';
			$this->adminProducts->display($this->token);
		}
	}
	
	public function displayListHeader($token = NULL)
	{
		global $currentIndex;

		$id_category = intval(Tools::getValue('id_category'));
		if ($id_category)
			$currentIndex .= '&id_category='.$id_category.'&token='.$this->token;
	}
}

?>