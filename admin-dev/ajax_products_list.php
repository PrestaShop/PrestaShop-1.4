<?php

include_once(dirname(__FILE__).'/../config/config.inc.php');

/* Getting cookie or logout */
if (!class_exists('Cookie'))
	exit();


$cookie = new Cookie('psAdmin', substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__), -10));
if (!$cookie->isLoggedBack())
	die;

$query = Tools::getValue('q', false);

if (!$query OR $query == '' OR strlen($query) < 1)
	die();

$items = Db::s('
	SELECT p.`id_product`, `reference`, pl.name
	FROM `'._DB_PREFIX_.'product` p
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product)
	WHERE 1
	AND (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')
	AND pl.id_lang = '.intval($cookie->id_lang).'
');
if ($items)
	foreach ($items as $item)
		echo $item['name'].(!empty($item['reference']) ? '('.$item['reference'].')' : '').'|'.$item['id_product']."\n";
