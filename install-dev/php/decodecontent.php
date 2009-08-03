<?php

function decode_content()
{
	// CMS_LANG
	$sql = 'SELECT `id_cms`, `content`, `id_lang` FROM `'._DB_PREFIX_.'cms_lang`';
	$result = Db::getInstance()->ExecuteS($sql);
	foreach ($result as $cms)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_lang`
									SET `content` = \''.pSql(Tools::htmlentitiesDecodeUTF8($cms['content']), true).'\'
									WHERE  `id_cms`= '.intval($cms['id_cms']).' AND `id_lang` = '.intval($cms['id_lang']));

	// MANUFACTURER_LANG
	$sql = 'SELECT `id_manufacturer`, `description`, `short_description`, `id_lang` FROM `'._DB_PREFIX_.'manufacturer_lang`';
	$result = Db::getInstance()->ExecuteS($sql);
	foreach ($result as $manu)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'manufacturer_lang`
									SET `description` = \''.pSql(Tools::htmlentitiesDecodeUTF8($manu['description']), true).'\', 
										`short_description` = \''.pSql(Tools::htmlentitiesDecodeUTF8($manu['short_description']), true).'\'
									WHERE `id_manufacturer`= '.intval($manu['id_manufacturer']).' AND `id_lang` = '.intval($manu['id_lang']));

	// PRODUCT_LANG
	$sql = 'SELECT `id_product`, `description`, `description_short`, `id_lang` FROM `'._DB_PREFIX_.'product_lang`';
	$result = Db::getInstance()->ExecuteS($sql);
	foreach ($result as $prod)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_lang`
									SET `description` = \''.pSql(Tools::htmlentitiesDecodeUTF8($prod['description']), true).'\', 
										`description_short` = \''.pSql(Tools::htmlentitiesDecodeUTF8($prod['description_short']), true).'\'
									WHERE `id_product`= '.intval($prod['id_product']).' AND `id_lang` = '.intval($prod['id_lang']));
}

?>