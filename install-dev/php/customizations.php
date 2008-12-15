<?php

define('_CONTAINS_REQUIRED_FIELD_', 2);

function add_required_customization_field_flag()
{
	if (($result = Db::getInstance()->ExecuteS('SELECT `id_product` FROM `'._DB_PREFIX_.'customization_field` WHERE `required` = 1')) === false)
		return false;
	if (Db::getInstance()->numRows())
	{
		$productIds = array();
		foreach ($result AS $row)
			$productIds[] = intval($row['id_product']);
		if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `customizable` = '._CONTAINS_REQUIRED_FIELD_.' WHERE `id_product` IN ('.implode(', ', $productIds).')'))
			return false;
	}
	return true;
}

?>