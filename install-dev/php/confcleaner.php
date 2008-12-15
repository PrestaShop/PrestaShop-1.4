<?php

function configuration_double_cleaner()
{
	$result = Db::getInstance()->ExecuteS('
	SELECT name, MIN(id_configuration) AS minid
	FROM '._DB_PREFIX_.'configuration
	GROUP BY name
	HAVING count(name) > 1');
	foreach ($result as $row)
	{
		DB::getInstance()->Execute('
		DELETE FROM '._DB_PREFIX_.'configuration
		WHERE name = \''.addslashes($row['name']).'\'
		AND id_configuration != '.intval($row['minid']));
	}
	DB::getInstance()->Execute('
	DELETE FROM '._DB_PREFIX_.'configuration_lang
	WHERE id_configuration NOT IN (
		SELECT id_configuration
		FROM '._DB_PREFIX_.'configuration)');
}

?>