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

$items = Db::getInstance()->ExecuteS('
SELECT p.`id_product`, `reference`, pl.name
FROM `'._DB_PREFIX_.'product` p
LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product)
WHERE (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\') AND pl.id_lang = '.intval($cookie->id_lang).
(!empty($excludeIds) ? ' AND p.id_product NOT IN ('.$excludeIds.') ' : ''));

if ($items)
	foreach ($items AS $item)
		echo $item['name'].(!empty($item['reference']) ? ' ('.$item['reference'].')' : '').'|'.intval($item['id_product'])."\n";
