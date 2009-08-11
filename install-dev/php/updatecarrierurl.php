<?php

function update_carrier_url()
{
	// Get all carriers
	$sql = '
		SELECT c.`id_carrier`, c.`url`
		FROM `'._DB_PREFIX_.'carrier` c';
	$carriers = Db::getInstance()->ExecuteS($sql);

	// Check each one and erase carrier URL if not correct URL
	foreach ($carriers as $carrier)
		if (!Validate::isAbsoluteUrl($carrier['url']))
			Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'carrier`
				SET `url` = \'\'
				WHERE  `id_carrier`= '.intval($carrier['id_carrier']));
}
