<?php

define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');
/* Getting cookie or logout */
require_once(dirname(__FILE__).'/init.php');

$query = Tools::getValue('q', false);
if (!$query OR $query == '' OR strlen($query) < 1)
	die();

if ($excludeIds = Tools::getValue('excludeIds', false))
	$excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));

$items = Db::s('
	SELECT p.`id_product`, `reference`, pl.name
	FROM `'._DB_PREFIX_.'product` p
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product)
	WHERE 1
	AND (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')
	AND pl.id_lang = '.intval($cookie->id_lang)
	.($excludeIds !== false ? ' AND p.id_product NOT IN ('.$excludeIds.') ' : '')
);
if ($items)
	foreach ($items as $item)
		echo $item['name'].(!empty($item['reference']) ? '('.$item['reference'].')' : '').'|'.$item['id_product']."\n";
