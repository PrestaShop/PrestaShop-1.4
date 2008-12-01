<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/product-sort.php');


/* Classic search */
if ($query = trim(Tools::getValue('search_query')))
{
	if (!Validate::isValidSearch($query))
		$smarty->assign('errors', array(Tools::displayError('invalid search')));
	else
	{
		$search = new Search();
		$nbProducts = intval($search->find(intval($cookie->id_lang), $query, true));
		include(dirname(__FILE__).'/pagination.php');
		$smarty->assign(array(
			'products' => $search->find(intval($cookie->id_lang), $query, false, $p, $n, $orderBy, $orderWay),
			'nbProducts' => $nbProducts,
			'query' => $query));
	}
}

/* Tags */
elseif ($tag = Tools::getValue('tag'))
{
	$search = new Search();
	$nbProducts = intval($search->tag(intval($cookie->id_lang), $tag, true));
	include(dirname(__FILE__).'/pagination.php');
	$smarty->assign(array(
		'tag' => $tag,
		'products' => $search->tag(intval($cookie->id_lang), $tag, false, $p, $n),
		'nbProducts' => $nbProducts));
}

/* Reference */
elseif ($ref = Tools::getValue('ref'))
{
	$search = new Search();
	$nbProducts = intval($search->ref(intval($cookie->id_lang), $ref, true));
	include(dirname(__FILE__).'/pagination.php');
	$smarty->assign(array(
		'ref' => $ref,
		'query' => $ref,
		'products' => $search->ref(intval($cookie->id_lang), $ref, false, $p, $n),
		'nbProducts' => $nbProducts));
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