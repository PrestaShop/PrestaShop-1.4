<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

include(dirname(__FILE__).'/product-sort.php');

$nbProducts = intval(Product::getNewProducts(intval($cookie->id_lang), isset($p) ? intval($p) - 1 : NULL, isset($n) ? intval($n) : NULL, true));
include(dirname(__FILE__).'/pagination.php');

$smarty->assign(array(
	'products' => Product::getNewProducts(intval($cookie->id_lang), intval($p) - 1, intval($n), false, $orderBy, $orderWay),
	'nbProducts' => intval($nbProducts)));

$smarty->display(_PS_THEME_DIR_.'new-products.tpl');

include(dirname(__FILE__).'/footer.php');

?>
