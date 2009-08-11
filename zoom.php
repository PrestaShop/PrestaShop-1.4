<?php

include(dirname(__FILE__).'/config/config.inc.php');

if (isset($_GET['id_image']) AND is_numeric($_GET['id_image']))
{
	$cookie = new Cookie('ps');
	Tools::setCookieLanguage();

	$id_image = intval($_GET['id_image']);
	$image = new Image(intval($id_image), intval($cookie->id_lang));
	$product = new Product(intval($image->id_product), false, intval($cookie->id_lang));
	if (Validate::isLoadedObject($image) AND Validate::isLoadedObject($product))
	{
		if (file_exists(_PS_PROD_IMG_DIR_.intval($image->id_product).'-'.intval($id_image).'.jpg'))
		{
		 	$smarty->assign(array(
			'css_dir' => _THEME_CSS_DIR_,
			'img_dir' => _THEME_IMG_DIR_,
			'product_name' => Tools::safeOutput($product->name),
			'image_size' => getimagesize(_PS_PROD_IMG_DIR_.intval($image->id_product).'-'.intval($id_image).'.jpg'),
			'image' => _THEME_PROD_DIR_.intval($image->id_product).'-'.intval($id_image).'.jpg',
			'legend' => Tools::safeOutput($image->legend)));
		}
		else
			$smarty->assign('error', 'this image cannot be found');
	}
	else
		$smarty->assign('error', 'this image cannot be found');
}
else
	$smarty->assign('error', 'missing parameter');

$smarty->display(_PS_THEME_DIR_.'zoom.tpl');

?>