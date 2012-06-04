<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_8_3($object)
{
	$object->upgrade_detail['1.8.3'] = array();

	if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'mr_method` ADD `is_deleted` INT NOT NULL'))
	{
		$object->upgrade_detail['1.8.3'][] = $object->l('Can\'t add new field in "method table"');
		return false;
	}
	Configuration::updateValue('MONDIAL_RELAY', $object->version);
	return true;
}