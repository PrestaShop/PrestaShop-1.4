<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

//will be initialized bellow...
if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
	$rewrited_url = null;

/* CSS ans JS files calls */
$css_files = array(__PS_BASE_URI__.'css/jquery.cluetip.css' => 'all', _THEME_CSS_DIR_.'scenes.css' => 'all');

include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/product-sort.php');

$errors = array();
if (!isset($_GET['id_category']) OR !Validate::isUnsignedId($_GET['id_category']))
	$errors[] = Tools::displayError('category ID is missing');
else
{
	$category = new Category(intval(Tools::getValue('id_category')), intval($cookie->id_lang));
	if (!Validate::isLoadedObject($category))
		$errors[] = Tools::displayError('category does not exist');
	elseif (!$category->checkAccess(intval($cookie->id_customer)))
		$errors[] = Tools::displayError('you do not have access to this category');
	else
	{
		/* rewrited url set */
		$rewrited_url = $link->getCategoryLink($category->id, $category->link_rewrite);
		
		/* Scenes  (could be externalised to another controler if you need them */
		$smarty->assign('scenes', Scene::getScenes(intval($category->id), intval($cookie->id_lang), true, false));

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
			$smarty->assign('thumbSceneImageType', isset($thumbSceneImageType) ? $thumbSceneImageType : NULL);
			$smarty->assign('largeSceneImageType', isset($largeSceneImageType) ? $largeSceneImageType : NULL);
		}
		
		$category->name = Category::hideCategoryPosition($category->name);
		$category->description = nl2br2($category->description);
		$subCategories = $category->getSubCategories(intval($cookie->id_lang));
		$smarty->assign('category', $category);
		if (Db::getInstance()->numRows())
			$smarty->assign('subcategories', $subCategories);
		if ($category->id != 1)
		{
			$nbProducts = $category->getProducts(NULL, NULL, NULL, $orderBy, $orderWay, true);
			include(dirname(__FILE__).'/pagination.php');
			$smarty->assign('nb_products', $nbProducts);
			$cat_products = $category->getProducts(intval($cookie->id_lang), intval($p), intval($n), $orderBy, $orderWay);
		}
		$smarty->assign(array(
			'products' => (isset($cat_products) AND $cat_products) ? $cat_products : NULL,
			'id_category' => intval($category->id),
			'id_category_parent' => intval($category->id_parent),
			'return_category_name' => Tools::safeOutput(Category::hideCategoryPosition($category->name)),
			'path' => Tools::getPath(intval($category->id), $category->name)
		));
	}
}

$smarty->assign(array(
	'allow_oosp' => intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')),
	'suppliers' => Supplier::getSuppliers(),
	'errors' => $errors));

if (isset($subCategories))
	$smarty->assign(array(
		'subcategories_nb_total' => sizeof($subCategories),
		'subcategories_nb_half' => ceil(sizeof($subCategories) / 2)));

$smarty->display(_PS_THEME_DIR_.'category.tpl');

include(dirname(__FILE__).'/footer.php');

?>
