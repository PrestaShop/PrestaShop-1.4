<?php

function set_discount_category()
{
	$discounts = Db::getInstance()->ExecuteS('SELECT `id_discount` FROM `'._DB_PREFIX_.'discount`');
	$categories = Db::getInstance()->ExecuteS('SELECT `id_category` FROM `'._DB_PREFIX_.'category`');
	foreach ($discounts AS $discount)
		foreach ($categories AS $category)
			Db::getInstance()->ExecuteS('INSERT INTO `'._DB_PREFIX_.'discount_category` (`id_discount`,`id_category`) VALUES ('.intval($discount['id_discount']).','.intval($category['id_category']).')');
}

?>