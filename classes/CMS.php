<?php

/**
  * CMS class, CMS.php
  * CMS management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class CMS extends ObjectModel
{
	public $meta_title;
	public $meta_description;
	public $meta_keywords;
	public $content;
	public $link_rewrite;

 	protected $fieldsRequiredLang = array('meta_title', 'link_rewrite');
	protected $fieldsSizeLang = array('meta_description' => 255, 'meta_keywords' => 255, 'meta_title' => 128, 'link_rewrite' => 128, 'content' => 65536);
	protected $fieldsValidateLang = array('meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName', 'meta_title' => 'isGenericName', 'link_rewrite' => 'isLinkRewrite', 'content' => 'isCleanHTML');

	protected $table = 'cms';
	protected $identifier = 'id_cms';
	
	public function getFields() { return array('id_cms' => null); }
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();

		$fieldsArray = array('meta_title', 'meta_description', 'meta_keywords', 'link_rewrite');
		$fields = array();
		$languages = Language::getLanguages();
		$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = intval($this->id);
			$fields[$language['id_lang']]['content'] = (isset($this->content[$language['id_lang']])) ? Tools::htmlentitiesDecodeUTF8(pSQL($this->content[$language['id_lang']], true)) : '';
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
	
	public function add($autodate = true, $nullValues = false) { return parent::add($autodate, true); }
	
	public function update($nullValues = false)
	{
	 	$result = 1;
	 	$fields = $this->getTranslationsFieldsChild();
		foreach ($fields as $field)
		{
			foreach ($field as $key => $value)
			 	if (!Validate::isTableOrIdentifier($key))
	 				die(Tools::displayError());
			$mode = Db::getInstance()->getRow('SELECT `id_lang` FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE `'.pSQL($this->identifier).
			'` = '.intval($this->id).' AND `id_lang` = '.intval($field['id_lang']));
			$result *= (!Db::getInstance()->NumRows()) ? Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'INSERT') : 
			Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table.'_lang', $field, 'UPDATE', '`'.
			pSQL($this->identifier).'` = '.intval($this->id).' AND `id_lang` = '.intval($field['id_lang']));
		}
		return $result;
	}
	
	public static function getLinks($id_lang, $selection = null)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT c.id_cms, cl.link_rewrite, cl.meta_title
		FROM '._DB_PREFIX_.'cms c
		LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = '.intval($id_lang).')
		'.(($selection !== null) ? 'WHERE c.id_cms IN ('.implode(',', array_map('intval', $selection)).')' : ''));
		
		$link = new Link();
		$links = array();
		if ($result)
			foreach ($result as $row)
			{
				$row['link'] = $link->getCMSLink($row['id_cms'], $row['link_rewrite']);
				$links[] = $row;
			}
		return $links;
	}
	
	public static function listCms($id_lang = _PS_LANG_DEFAULT_, $id_block = false)
	{
		$result = Db::getInstance()->ExecuteS('
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
		Db::getInstance()->Execute('
		SELECT id_cms FROM '._DB_PREFIX_.'block_cms
		WHERE  id_block = '.intval($id_block).' AND id_cms='.intval($id_cms));
		return (Db::getInstance()->NumRows());
	}
		
	public static function updateCmsToBlock($cms, $id_block)
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'block_cms`
    															WHERE `id_block` ='.intval($id_block));
		foreach ($cms AS $id_cms)
			Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'block_cms (id_block, id_cms) VALUES
																('.intval($id_block).', '.intval($id_cms).')');
			return true;
	}
	
}

?>
