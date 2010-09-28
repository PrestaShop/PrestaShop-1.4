<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');
Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');

include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/product-sort.php');

$nbProducts = Product::getPricesDrop(intval($cookie->id_lang), NULL, NULL, true);
include(dirname(__FILE__).'/pagination.php');

$smarty->assign(array(
	'products' => Product::getPricesDrop(intval($cookie->id_lang), intval($p) - 1, intval($n), false, $orderBy, $orderWay),
	'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
	'nbProducts' => $nbProducts));

$smarty->display(_PS_THEME_DIR_.'prices-drop.tpl');

include(dirname(__FILE__).'/footer.php');

?>