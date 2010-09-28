<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');
Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');
include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/product-sort.php');

$nbProducts = intval(ProductSale::getNbSales());
include(dirname(__FILE__).'/pagination.php');
	
$smarty->assign(array(
	'products' => ProductSale::getBestSales(intval($cookie->id_lang), intval($p) - 1, intval($n), $orderBy, $orderWay),
	'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
	'nbProducts' => $nbProducts));
	
$smarty->display(_PS_THEME_DIR_.'best-sales.tpl');

include(dirname(__FILE__).'/footer.php');

?>
