<?php

/**
  * Feature class, Feature.php
  * Feature management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Feature extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 128);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');
		
	protected 	$table = 'feature';
	protected 	$identifier = 'id_feature';

	public function getFields()
	{
		return array('id_feature' => NULL);
	}
	
	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}
	
	/**
	 * Get a feature data for a given id_feature and id_lang
	 *
	 * @param integer $id_lang Language id
	 * @param integer $id_feature Feature id
	 * @return array Array with feature's data
	 * @static
	 */
	static public function getFeature($id_lang, $id_feature)
	{
		return Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'feature` f
		LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl ON ( f.`id_feature` = fl.`id_feature` AND fl.`id_lang` = '.intval($id_lang).')
		WHERE f.`id_feature` = '.intval($id_feature));
	}
	
	/**
	 * Get all features for a given language
	 *
	 * @param integer $id_lang Language id
	 * @return array Multiple arrays with feature's data
	 * @static
	 */
	static public function getFeatures($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'feature` f
		LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl ON (f.`id_feature` = fl.`id_feature` AND fl.`id_lang` = '.intval($id_lang).')
		ORDER BY fl.`name` ASC');
	}
	
	/**
	 * Delete several objects from database
	 *
	 * @param array $selection Array with items to delete
	 * @return boolean Deletion result
	 */
	public function deleteSelection($selection)
	{
		/* Also delete Attributes */
		foreach ($selection AS $value) {
			$obj = new Feature($value);
			if (!$obj->delete())
				return false;
		}
		return true;
	}

	public function add($autodate = true, $nullValues = false)
	{
		return parent::add($autodate, true);
	}

	public function delete()
	{
	 	/* Also delete related attributes */
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'feature_value_lang` WHERE `id_feature_value` IN (SELECT id_feature_value FROM `'._DB_PREFIX_.'feature_value` WHERE `id_feature` = '.intval($this->id).')');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'feature_value` WHERE `id_feature` = '.intval($this->id));
		return parent::delete();
	}
	
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
	
	/**
	* Count number of features for a given language
	*
	* @param integer $id_lang Language id
	* @return int Number of feature
	* @static
	*/
	static public function nbFeatures($id_lang)
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(ag.`id_feature`) as nb
		FROM `'._DB_PREFIX_.'feature` ag
		LEFT JOIN `'._DB_PREFIX_.'feature_lang` agl ON (ag.`id_feature` = agl.`id_feature` AND `id_lang` = '.intval($id_lang).')
		ORDER BY `name` ASC');
		return ($result['nb']);
	}
	
	/**
	* Create a feature from import
	*
	* @param integer $id_feature Feature id
	* @param integer $id_product Product id	
	* @param array $value Feature Value		
	*/	
	static public function addFeatureImport($name)
	{
		$rq = Db::getInstance()->getRow('SELECT `id_feature` FROM '._DB_PREFIX_.'feature_lang WHERE `name` = \''.pSQL($name).'\' GROUP BY `id_feature`');
		if (!empty($rq))
			return intval($rq['id_feature']);
		// Feature doesn't exist, create it
		$feature = new Feature();
		$languages = Language::getLanguages();
		foreach ($languages as $language)
			$feature->name[$language['id_lang']] = strval($name);
		$feature->add();
		return $feature->id;
	}
}
?>