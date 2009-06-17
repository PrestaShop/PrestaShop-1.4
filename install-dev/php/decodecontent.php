<?php

function decode_content()
{
	// CMS_LANG
	$sql = 'SELECT `id_cms`, `content` FROM `'._DB_PREFIX_.'cms_lang`';
	$result = Db::getInstance()->ExecuteS($sql);
	foreach ($result as $cms)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_lang`
									SET `content` = \''.pSql(Tools::htmlentitiesDecodeUTF8($cms['content']), true).'\'
									WHERE  `id_cms`= '.intval($cms['id_cms']));

	// MANUFACTURER_LANG
	$sql = 'SELECT `id_manufacturer`, `description`, `short_description` FROM `'._DB_PREFIX_.'manufacturer_lang`';
	$result = Db::getInstance()->ExecuteS($sql);
	foreach ($result as $manu)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'manufacturer_lang`
									SET `description` = \''.pSql(Tools::htmlentitiesDecodeUTF8($manu['description']), true).'\', 
										`short_description` = \''.pSql(Tools::htmlentitiesDecodeUTF8($manu['short_description']), true).'\'
									WHERE  `id_manufacturer`= '.intval($manu['id_manufacturer']));

	// PRODUCT_LANG
	$sql = 'SELECT `id_product`, `description`, `description_short` FROM `'._DB_PREFIX_.'product_lang`';
	$result = Db::getInstance()->ExecuteS($sql);
	foreach ($result as $manu)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_lang`
									SET `description` = \''.pSql(Tools::htmlentitiesDecodeUTF8($manu['description']), true).'\', 
										`description_short` = \''.pSql(Tools::htmlentitiesDecodeUTF8($manu['description_short']), true).'\'
									WHERE  `id_product`= '.intval($manu['id_product']));
}

?>