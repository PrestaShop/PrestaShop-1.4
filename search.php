<?php

include(dirname(__FILE__).'/config/config.inc.php');

if (Tools::getValue('ajaxSearch') AND $query = urldecode(Tools::getValue('q')) AND !is_array($query))
{
	include(dirname(__FILE__).'/init.php');
	$link = new Link();
	$searchResults = Search::find(intval(Tools::getValue('id_lang')), $query, 1, 10, 'position', 'desc', true);
	foreach ($searchResults AS &$product)
		$product['product_link'] = $link->getProductLink($product['id_product'], $product['prewrite'], $product['crewrite']);
	die(json_encode($searchResults));
}

include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/product-sort.php');

if ($query = Tools::getValue('search_query', Tools::getValue('ref')) AND !is_array($query))
{
	$n = abs(intval(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))));
	$p = abs(intval(Tools::getValue('p', 1)));
	$search = Search::find(intval($cookie->id_lang), $query, $p, $n, $orderBy, $orderWay);
	$nbProducts = $search['total'];
	include(dirname(__FILE__).'/pagination.php');
	$smarty->assign(array('products' => $search['result'], 'nbProducts' => $search['total'], 'search_query' => $query, 'homeSize' => Image::getSize('home')));
}
elseif ($tag = Tools::getValue('tag') AND !is_array($tag))
{
	$nbProducts = intval(Search::searchTag(intval($cookie->id_lang), $tag, true));
	include(dirname(__FILE__).'/pagination.php');
	$smarty->assign(array('search_tag' => $tag, 'products' => Search::searchTag(intval($cookie->id_lang), $tag, false, $p, $n, $orderBy, $orderWay), 'nbProducts' => $nbProducts, 'homeSize' => Image::getSize('home')));
}
else
{
	$smarty->assign(array(
	'products' => array(),
	'pages_nb' => 1,
	'nbProducts' => 0));
}

$smarty->display(_PS_THEME_DIR_.'search.tpl');

include(dirname(__FILE__).'/footer.php');

?>
