<?php

function set_payment_module()
{
	// Get all modules then select only payment ones
	$modules = Module::getModulesInstalled();
	foreach ($modules AS $module)
	{
		$file = _PS_MODULE_DIR_.$module['name'].'/'.$module['name'].'.php';
		$fd = fopen($file, 'r');
		$content = fread($fd, filesize($file));
		if (preg_match_all('/extends PaymentModule/U', $content, $matches))
		{
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'module_country` (id_module, id_country)
			SELECT '.intval($module['id_module']).', id_country FROM `'._DB_PREFIX_.'country` WHERE active = 1');
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'module_currency` (id_module, id_currency)
			SELECT '.intval($module['id_module']).', id_currency FROM `'._DB_PREFIX_.'currency` WHERE deleted = 0');
		}
		fclose($fd);
	}
}

?>