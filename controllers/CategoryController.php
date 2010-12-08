<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class CategoryControllerCore extends FrontController
{
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(array(
			_PS_CSS_DIR_.'jquery.cluetip.css' => 'all',
			_THEME_CSS_DIR_.'scenes.css' => 'all',
			_THEME_CSS_DIR_.'category.css' => 'all',
			_THEME_CSS_DIR_.'product_list.css' => 'all'
		));
		Tools::addJS(_THEME_JS_DIR_.'products-comparison.js');
	}
	
	public function displayHeader()
	{
		parent::displayHeader();
		$this->productSort();
	}
    public function preProcess()
    {
        parent::preProcess();
        if((int)(Configuration::get('PS_REWRITING_SETTINGS')))
		{
            $id_category = isset($_GET['id_category']) ? $_GET['id_category'] : false;
    
    		if ($id_category)
    		{
    			$rewrite_infos = Category::getUrlRewriteInformations($id_category);
    
    			$default_rewrite = array();
    			foreach ($rewrite_infos AS $infos)
    				$default_rewrite[$infos['id_lang']] = $this->link->getCategoryLink($id_category, $infos['link_rewrite'], $infos['id_lang']);
    
    			$this->smarty->assign('lang_rewrite_urls', $default_rewrite);
    		}
		}
    }
	public function process()
	{
		parent::process();
		if (!isset($_GET['id_category']) OR !Validate::isUnsignedId($_GET['id_category']))
			$this->errors[] = Tools::displayError('category ID is missing');
		else
		{
			$category = new Category((int)(Tools::getValue('id_category')), (int)($this->cookie->id_lang));
			if (!Validate::isLoadedObject($category))
				$this->errors[] = Tools::displayError('category does not exist');
			elseif (!$category->checkAccess((int)($this->cookie->id_customer)))
				$this->errors[] = Tools::displayError('you do not have access to this category');
			elseif (!$category->active)
				$smarty->assign('category', $category);
			else
			{
				$rewrited_url = $this->link->getCategoryLink($category->id, $category->link_rewrite);
				
				/* Scenes  (could be externalised to another controler if you need them */
				$this->smarty->assign('scenes', Scene::getScenes((int)($category->id), (int)($this->cookie->id_lang), true, false));

				/* Scenes images formats */
				if ($sceneImageTypes = ImageType::getImagesTypes('scenes'))
				{
					foreach ($sceneImageTypes AS $sceneImageType)
					{
						if ($sceneImageType['name'] == 'thumb_scene')
							$thumbSceneImageType = $sceneImageType;
						elseif ($sceneImageType['name'] == 'large_scene')
							$largeSceneImageType = $sceneImageType;
					}
					$this->smarty->assign('thumbSceneImageType', isset($thumbSceneImageType) ? $thumbSceneImageType : NULL);
					$this->smarty->assign('largeSceneImageType', isset($largeSceneImageType) ? $largeSceneImageType : NULL);
				}
				
				$category->description = nl2br2($category->description);
				$subCategories = $category->getSubCategories((int)($this->cookie->id_lang));
				$this->smarty->assign('category', $category);
				if (Db::getInstance()->numRows())
				{
					$this->smarty->assign('subcategories', $subCategories);
					$this->smarty->assign(array(
						'subcategories_nb_total' => sizeof($subCategories),
						'subcategories_nb_half' => ceil(sizeof($subCategories) / 2)
					));
				}
				if ($category->id != 1)
				{
					$nbProducts = $category->getProducts(NULL, NULL, NULL, $this->orderBy, $this->orderWay, true);
					$this->pagination($nbProducts);
					$this->smarty->assign('nb_products', $nbProducts);
					$cat_products = $category->getProducts((int)($this->cookie->id_lang), (int)($this->p), (int)($this->n), $this->orderBy, $this->orderWay);
				}
				$this->smarty->assign(array(
					'products' => (isset($cat_products) AND $cat_products) ? $cat_products : NULL,
					'id_category' => (int)($category->id),
					'id_category_parent' => (int)($category->id_parent),
					'return_category_name' => Tools::safeOutput($category->name),
					'path' => Tools::getPath((int)($category->id), $category->name),
					'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
					'homeSize' => Image::getSize('home')
				));
			}
		}

		$this->smarty->assign(array(
			'allow_oosp' => (int)(Configuration::get('PS_ORDER_OUT_OF_STOCK')),
			'comparator_max_item' => (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM')),
			'suppliers' => Supplier::getSuppliers()
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'category.tpl');
	}
}

