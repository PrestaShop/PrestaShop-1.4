<?php

function group_reduction_column_fix()
{
	if (!Db::getInstance()->execute('SELECT `group_reduction` FROM `'._DB_PREFIX_.'` LIMIT 1'))
		return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'order_detail` ADD `group_reduction` DECIMAL(10, 2) NOT NULL AFTER `reduction_amount`');
	return true;
}
