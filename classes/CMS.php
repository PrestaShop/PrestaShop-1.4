<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CMSCore extends ObjectModel
{
	public $meta_title;
	public $meta_description;
	public $meta_keywords;
	public $content;
	public $link_rewrite;
	public $id_cms_category;
	public $position;
	public $active;

 	protected $fieldsValidate = array('id_cms_category' => 'isUnsignedInt');
	protected $fieldsRequiredLang = array('meta_title', 'link_rewrite');
	protected $fieldsSizeLang = array('meta_description' => 255, 'meta_keywords' => 255, 'meta_title' => 128, 'link_rewrite' => 128, 'content' => 3999999999999);
	protected $fieldsValidateLang = array('meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName', 'meta_title' => 'isGenericName', 'link_rewrite' => 'isLinkRewrite', 'content' => 'isString');

	protected $table = 'cms';
	protected $identifier = 'id_cms';
	
	protected	$webserviceParameters = array(
		'objectNodeName' => 'content',
		'objectsNodeName' => 'content_management_system',
	);

	public function getFields() 
	{ 
		parent::validateFields();
		$fields['id_cms'] = (int)$this->id;
		$fields['id_cms_category'] = (int)$this->id_cms_category;
		$fields['position'] = (int)$this->position;
		$fields['active'] = (int)$this->active;
		return $fields;	 
	}
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();

		$fieldsArray = array('meta_title', 'meta_description', 'meta_keywords', 'link_rewrite');
		$fields = array();
		$languages = Language::getLanguages(false);
		$defaultLanguage = (int)(_PS_LANG_DEFAULT_);
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = (int)($language['id_lang']);
			$fields[$language['id_lang']][$this->identifier] = (int)($this->id);
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
		$this->position = CMS::getLastPosition((int)$this->id_cms_category);
		return parent::add($autodate, true); 
	}
	
	public function update($nullValues = false)
	{
		if (parent::update($nullValues))
			return $this->cleanPositions($this->id_cms_category);
		return false;
	}
	
	public function delete()
	{
	 	if (parent::delete())
			return $this->cleanPositions($this->id_cms_category);
		return false;
	}

	public static function getLinks($id_lang, $selection = null, $active = true, $ssl = false)
	{
		$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.id_cms, cl.link_rewrite, cl.meta_title
		FROM '._DB_PREFIX_.'cms c
		LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = '.(int)$id_lang.')
		WHERE 1
		'.(!empty($selection) ? ' AND c.id_cms IN ('.implode(',', array_map('intval', $selection)).')' : '').
		($active ? ' AND c.`active` = 1 ' : '').
		'ORDER BY c.`position`');

		if ($results)
		{
			$link = new Link();
			foreach ($results as &$row)
				$row['link'] = $link->getCMSLink((int)$row['id_cms'], $row['link_rewrite'], (bool)$ssl, (int)$id_lang);
		}

		return $results;
	}
	
	public static function listCms($id_lang = null, $id_block = false, $active = true)
	{
		if (empty($id_lang))
			$id_lang = (int)_PS_LANG_DEFAULT_;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.id_cms, l.meta_title
		FROM  '._DB_PREFIX_.'cms c
		JOIN '._DB_PREFIX_.'cms_lang l ON (c.id_cms = l.id_cms)
		'.($id_block ? 'JOIN '._DB_PREFIX_.'block_cms b ON (c.id_cms = b.id_cms)' : '').'
		WHERE l.id_lang = '.(int)$id_lang.($id_block ? ' AND b.id_block = '.(int)$id_block : '').($active ? ' AND c.`active` = 1 ' : '').'
		ORDER BY c.`position`');
	}
	
	/**
	 * @deprecated
	 */
	public static function isInBlock($id_cms, $id_block)
	{
		Tools::displayAsDeprecated();
		Db::getInstance()->getRow('
		SELECT id_cms FROM '._DB_PREFIX_.'block_cms
		WHERE id_block = '.(int)$id_block.' AND id_cms = '.(int)$id_cms);
		
		return (Db::getInstance()->NumRows());
	}
	
	/**
	 * @deprecated
	 */
	public static function updateCmsToBlock($cms, $id_block)
	{
		Tools::displayAsDeprecated();
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'block_cms` WHERE `id_block` = '.(int)$id_block);

		$list = '';
		foreach ($cms as $id_cms)
			$list .= '('.(int)($id_block).', '.(int)($id_cms).'),';
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
		WHERE cp.`id_cms_category` = '.(int)$this->id_cms_category.' 
		ORDER BY cp.`position` ASC'))
			return false;
		
		foreach ($res as $cms)
			if ((int)$cms['id_cms'] == (int)$this->id)
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
				? '> '.(int)$movedCms['position'].' AND `position` <= '.(int)$position
				: '< '.(int)$movedCms['position'].' AND `position` >= '.(int)$position).'
			AND `id_cms_category`='.(int)$movedCms['id_cms_category'])
		&& Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms`
			SET `position` = '.(int)$position.'
			WHERE `id_cms` = '.(int)$movedCms['id_cms'].'
			AND `id_cms_category`='.(int)$movedCms['id_cms_category']));
	}
	
	public static function cleanPositions($id_category)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_cms`
		FROM `'._DB_PREFIX_.'cms`
		WHERE `id_cms_category` = '.(int)$id_category.'
		ORDER BY `position`');

		$sizeof = count($result);
		for ($i = 0; $i < $sizeof; ++$i)
			Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms`
			SET `position` = '.(int)$i.'
			WHERE `id_cms_category` = '.(int)$id_category.' AND `id_cms` = '.(int)$result[$i]['id_cms'].'
			LIMIT 1');

		return true;
	}
	
	public static function getLastPosition($id_category)
	{
		return (Db::getInstance()->getValue('SELECT MAX(position)+1 FROM `'._DB_PREFIX_.'cms` WHERE `id_cms_category` = '.(int)($id_category)));
	}
	
	public static function getCMSPages($id_lang = null, $id_cms_category = null, $active = true)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'cms` c
		JOIN `'._DB_PREFIX_.'cms_lang` l ON (c.id_cms = l.id_cms)'.
		(isset($id_cms_category) ? 'WHERE `id_cms_category` = '.(int)($id_cms_category) : '').
		($active ? ' AND c.`active` = 1 ' : '').' AND l.id_lang = '.(int)($id_lang).'
		ORDER BY `position`');
	}

    public static function getUrlRewriteInformations($id_cms)
	{
		return Db::getInstance()->ExecuteS('
		SELECT l.`id_lang`, c.`link_rewrite`
		FROM `'._DB_PREFIX_.'cms_lang` c
		LEFT JOIN  `'._DB_PREFIX_.'lang` l ON (c.`id_lang` = l.`id_lang`)
		WHERE c.`id_cms` = '.(int)$id_cms.'	AND l.`active` = 1');
	}
}