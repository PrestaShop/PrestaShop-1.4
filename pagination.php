<?php

$nArray = intval(Configuration::get('PS_PRODUCTS_PER_PAGE')) != 10 ? array(intval(Configuration::get('PS_PRODUCTS_PER_PAGE')), 10, 20, 50) : array(10, 20, 50);
asort($nArray);
$n = abs(intval(Tools::getValue('n', ((isset($cookie->nb_item_per_page) AND $cookie->nb_item_per_page >= 10) ? $cookie->nb_item_per_page : intval(Configuration::get('PS_PRODUCTS_PER_PAGE'))))));
$p = abs(intval(Tools::getValue('p', 1)));
$range = 2; /* how many pages around page selected */

if ($p < 0)
	$p = 0;

if (isset($cookie->nb_item_per_page) AND $n != $cookie->nb_item_per_page AND in_array($n, $nArray))
	$cookie->nb_item_per_page = $n;
	
if ($p > ($nbProducts / $n))
	$p = ceil($nbProducts / $n);
$pages_nb = ceil($nbProducts / intval($n));

$start = intval($p - $range);
if ($start < 1)
	$start = 1;
$stop = intval($p + $range);
if ($stop > $pages_nb)
	$stop = intval($pages_nb);
$smarty->assign('nb_products', $nbProducts);
$pagination_infos = array('pages_nb' => intval($pages_nb), 'p' => intval($p), 'n' => intval($n), 'nArray' => $nArray, 'range' => intval($range), 'start' => intval($start),	'stop' => intval($stop));
$smarty->assign($pagination_infos);

?>
