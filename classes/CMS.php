<?php

/**
  * CMS class, CMS.php
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class		CMS extends ObjectModel
{
	
	/** @var string Name */
	public $meta_title;
	public $meta_description;
	public $meta_keywords;
	public $content;
	public $link_rewrite;
	public $id_cms_category;
	public $position;

 	protected $fieldsRequiredLang = array('meta_title', 'link_rewrite');
	protected $fieldsSizeLang = array('meta_description' => 255, 'meta_keywords' => 255, 'meta_title' => 128, 'link_rewrite' => 128, 'content' => 3999999999999);
	protected $fieldsValidateLang = array('meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName', 'meta_title' => 'isGenericName', 'link_rewrite' => 'isLinkRewrite', 'content' => 'isString');

	protected $table = 'cms';
	protected $identifier = 'id_cms';
	
	public function getFields() 
	{ 
		parent::validateFields();
		$fields['id_cms'] = intval($this->id);
		$fields['id_cms_category'] = intval($this->id_cms_category);
		$fields['position'] = intval($this->position);
		return $fields;	 
	}
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();

		$fieldsArray = array('meta_title', 'meta_description', 'meta_keywords', 'link_rewrite');
		$fields = array();
		$languages = Language::getLanguages(false);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = intval($language['id_lang']);
			$fields[$language['id_lang']][$this->identifier] = intval($this->id);
			$fields[$language['id_lang']]['content'] = (isset($this->content[$language['id_lang']])) ? pSQL($this->content[$language['id_lang']], true) : '';
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
				else
					$fields[$language['id_lang']][$field] = '';
			}
		}
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{ 
		$this->position = CMS::getLastPosition(intval(Tools::getValue('id_cms_category')));
		return parent::add($autodate, true); 
	}
	
	public function update()
	{
		if (parent::update())
			return $this->cleanPositions($this->id_cms_category);
		return false;
	}
	
	public function delete()
	{
	 	if (parent::delete())
			return $this->cleanPositions($this->id_cms_category);
		return false;
	}

	public static function getLinks($id_lang, $selection = NULL)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.id_cms, cl.link_rewrite, cl.meta_title
		FROM '._DB_PREFIX_.'cms c
		LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = '.intval($id_lang).')
		'.(($selection !== NULL) ? 'WHERE c.id_cms IN ('.implode(',', array_map('intval', $selection)).')' : ''));
		$link = new Link();
		$links = array();
		if ($result)
			foreach ($result as $row)
			{
				$row['link'] = $link->getCMSLink(intval($row['id_cms']), $row['link_rewrite']);
				$links[] = $row;
			}
		return $links;
	}
	
	public static function listCms($id_lang = NULL, $id_block = false)
	{
		if (empty($id_lang))
			$id_lang = intval(Configuration::get('PS_LANG_DEFAULT'));

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.id_cms, l.meta_title
		FROM  '._DB_PREFIX_.'cms c
		JOIN '._DB_PREFIX_.'cms_lang l ON (c.id_cms = l.id_cms)
		'.(($id_block) ? 'JOIN '._DB_PREFIX_.'block_cms b ON (c.id_cms = b.id_cms)' : '').'
		WHERE l.id_lang = '.intval($id_lang).(($id_block) ? ' AND b.id_block = '.intval($id_block) : '')  
		);
		return $result;
	}
	
	public static function isInBlock($id_cms, $id_block)
	{
		Db::getInstance()->getRow('
		SELECT id_cms FROM '._DB_PREFIX_.'block_cms
		WHERE id_block = '.intval($id_block).' AND id_cms = '.intval($id_cms));
		
		return (Db::getInstance()->NumRows());
	}
		
	public static function updateCmsToBlock($cms, $id_block)
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'block_cms` WHERE `id_block` = '.intval($id_block));

		$list = '';
		foreach ($cms AS $id_cms)
			$list .= '('.intval($id_block).', '.intval($id_cms).'),';
		$list = rtrim($list, ',');
		
		if (!empty($list))
			Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'block_cms (id_block, id_cms) VALUES '.pSQL($list));
			
		return true;
	}
	
	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->ExecuteS('
			SELECT cp.`id_cms`, cp.`position`, cp.`id_cms_category` 
			FROM `'._DB_PREFIX_.'cms` cp
			WHERE cp.`id_cms_category` = '.intval(Tools::getValue('id_cms_category', 1)).' 
			ORDER BY cp.`position` ASC'
		))
			return false;
		
		foreach ($res AS $cms)
			if (intval($cms['id_cms']) == intval($this->id))
				$movedCms = $cms;
		
		if (!isset($movedCms) || !isset($position))
			return false;
		
		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position` 
			'.($way 
				? '> '.intval($movedCms['position']).' AND `position` <= '.intval($position)
				: '< '.intval($movedCms['position']).' AND `position` >= '.intval($position)).'
			AND `id_cms_category`='.intval($movedCms['id_cms_category']))
		AND Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms`
			SET `position` = '.intval($position).'
			WHERE `id_cms` = '.intval($movedCms['id_cms']).'
			AND `id_cms_category`='.intval($movedCms['id_cms_category'])));
	}
	
	static public function cleanPositions($id_category)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_cms`
		FROM `'._DB_PREFIX_.'cms`
		WHERE `id_cms_category` = '.intval($id_category).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i){
				$sql = '
				UPDATE `'._DB_PREFIX_.'cms`
				SET `position` = '.intval($i).'
				WHERE `id_cms_category` = '.intval($id_category).'
				AND `id_cms` = '.intval($result[$i]['id_cms']);
				Db::getInstance()->Execute($sql);
			}
		return true;
	}
	
	static public function getLastPosition($id_category)
	{
		return (Db::getInstance()->getValue('SELECT MAX(position)+1 FROM `'._DB_PREFIX_.'cms` WHERE `id_cms_category` = '.intval($id_category)));
	}
	
	static public function getCMSPages($id_lang = NULL, $id_cms_category = NULL)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'cms` c
		JOIN `'._DB_PREFIX_.'cms_lang` l ON (c.id_cms = l.id_cms)'.
		(isset($id_cms_category) ? 'WHERE `id_cms_category` = '.intval($id_cms_category) : '').'
		AND l.id_lang = '.intval($id_lang).'
		ORDER BY `position`');
	}

}

?>
