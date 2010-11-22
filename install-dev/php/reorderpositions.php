<?php

function reorderpositions()
{
	//clean products position
	$cat = Category::getCategories(1, false, false);
	
	foreach($cat as $i => $categ)
	{
		Product::cleanPositions(intval($categ['id_category']));
	}
	
	//clean Category position and delete old position system
	Language::loadLanguages();
	$language = Language::getLanguages();
	$cat_parent = Db::getInstance()->ExecuteS('SELECT DISTINCT c.id_parent FROM `'._DB_PREFIX_.'category` c WHERE id_category != 1');
	foreach($cat_parent as $parent)
	{
		$result = Db::getInstance()->ExecuteS('
							SELECT DISTINCT c.*, cl.*
							FROM `'._DB_PREFIX_.'category` c 
							LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.intval(Configuration::get('PS_LANG_DEFAULT')).')
							WHERE c.id_parent = '.intval($parent['id_parent']).'
							ORDER BY name ASC');
		foreach($result as $i => $categ)
		{
		$sizeof = sizeof($result);
			for ($i = 0; $i < $sizeof; ++$i)
			{
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'category`
				SET `position` = '.intval($i).'
				WHERE `id_parent` = '.intval($categ['id_parent']).'
				AND `id_category` = '.intval($result[$i]['id_category']));
			}
		
			foreach($language as $lang)
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'category` c 
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`)  
				SET `name` = \''.preg_replace('/^[0-9]+\./', '',$categ['name']).'\' 
				WHERE c.id_category = '.intval($categ['id_category']).' AND id_lang = \''.intval($lang['id_lang']).'\'');
		}
	}
	
	//clean CMS position
	$cms_cat = CMSCategory::getCategories(1, false, false);
	
	foreach($cms_cat as $i => $categ)
	{
		CMS::cleanPositions(intval($categ['id_cms_category']));
	}

}

?>