<?php

$nArray = intval(Configuration::get('PS_PRODUCTS_PER_PAGE')) != 10 ? array(intval(Configuration::get('PS_PRODUCTS_PER_PAGE')), 10, 20, 50) : array(10, 20, 50);
$n = abs(intval(Tools::getValue('n', intval(Configuration::get('PS_PRODUCTS_PER_PAGE')))));
$p = abs(intval(Tools::getValue('p', 1)));
$range = 2; /* how many pages around page selected */

if (!$n)
	$n = $nArray[0];
if ($p < 0)
	$p = 0;

if ($p > ($nbProducts / $n))
	$p = ceil($nbProducts / $n);
$pages_nb = ceil($nbProducts / intval($n));

$start = intval($p - $range);
if ($start < 1)
	$start = 1;
$stop = intval($p + $range);
if ($stop > $pages_nb)
	$stop = intval($pages_nb);

$pagination_infos = array('pages_nb' => intval($pages_nb), 'p' => intval($p), 'n' => intval($n), 'nArray' => $nArray, 'range' => intval($range), 'start' => intval($start),	'stop' => intval($stop));
$smarty->assign($pagination_infos);

?>
