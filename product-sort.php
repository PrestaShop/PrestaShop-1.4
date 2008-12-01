<?php

$stock_management = intval(Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false; // no display quantity order if stock management disabled
$orderByValues = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity');
$orderWayValues = array(0 => 'ASC', 1 => 'DESC');
$orderBy = Tools::strtolower(Tools::getValue('orderby', $orderByValues[intval(Configuration::get('PS_PRODUCTS_ORDER_BY'))]));
$orderWay = Tools::strtoupper(Tools::getValue('orderway', $orderWayValues[intval(Configuration::get('PS_PRODUCTS_ORDER_WAY'))]));
if (!in_array($orderBy, $orderByValues))
	$orderBy = $orderByValues[0];
if (!in_array($orderWay, $orderWayValues))
	$orderWay = $orderWayValues[0];

$smarty->assign(array(
	'orderby' => $orderBy,
	'orderway' => $orderWay,
	'stock_management' => $stock_management
));

?>
