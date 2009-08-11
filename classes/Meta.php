<?php

/**
  * Meta class, Meta.php
  * Meta management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Meta extends ObjectModel
{
	/** @var string Name */
	public 		$page;
	
	public 		$title;
	public 		$description;
	public 		$keywords;
	
 	protected 	$fieldsRequired = array('page');
 	protected 	$fieldsSize = array('page' => 64);
 	protected 	$fieldsValidate = array('page' => 'isFileName');
	
	protected	$fieldsRequiredLang = array();
	protected	$fieldsSizeLang = array('title' => 255, 'description' => 255, 'keywords' => 255);
	protected	$fieldsValidateLang = array('title' => 'isGenericName', 'description' => 'isGenericName', 'keywords' => 'isGenericName');
	
	protected 	$table = 'meta';
	protected 	$identifier = 'id_meta';
		
	public function getFields()
	{
		parent::validateFields();
		return array('page' => pSQL($this->page));
	}
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('title', 'description', 'keywords'));
	}
	
	static public function getPages($excludeFilled = false, $addPage = false)
	{
		$selectedPages = array();
		if (!$files = scandir(_PS_ROOT_DIR_))
			die(Tools::displayError('Cannot scan base URI'));
		
		// Exclude pages forbidden
		$exludePages = array(
		'cart', 'order', 'my-account', 'history', 'addresses', 'address', 'identity', 'discount', 'authentication', 'search',
		'get-file', 'order-slip', 'order-detail', 'order-follow', 'order-return', 'order-confirmation', 'pagination', 'pdf-invoice',
		'pdf-order-return', 'pdf-order-slip', 'product-sort', 'statistics', 'zoom', 'images.inc', 'header', 'footer', 'init',
		'category', 'product', 'cms');
		foreach ($files as $file)
			if (preg_match('/^[a-z0-9_.-]*\.php$/i', $file) AND !in_array(str_replace('.php', '', $file), $exludePages))
				$selectedPages[] = str_replace('.php', '', $file);
		// Exclude page already filled
		if ($excludeFilled)
		{
			$metas = self::getMetas();
			foreach ($metas as $k => $meta)
				if (in_array($meta['page'], $selectedPages))
					unset($selectedPages[array_search($meta['page'], $selectedPages)]);
		}
		// Add selected page
		if ($addPage)
		{
			$selectedPages[] = $addPage;
			sort($selectedPages);
		}
		return $selectedPages;
	}
	
	static public function getMetas()
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM '._DB_PREFIX_.'meta
		ORDER BY page ASC');
	}
	
	static public function getMetaByPage($page, $id_lang)
	{
		return Db::getInstance()->getRow('
		SELECT *
		FROM '._DB_PREFIX_.'meta m
		LEFT JOIN '._DB_PREFIX_.'meta_lang ml on (m.id_meta = ml.id_meta)
		WHERE m.page = \''.pSQL($page).'\' AND ml.id_lang = '.intval($id_lang));
	}
}
?>
