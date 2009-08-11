<?php

/**
  * FeatureValue class, FeatureValue.php
  * FeatureValue management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		FeatureValue extends ObjectModel
{
	/** @var integer Group id which attribute belongs */
	public		$id_feature;
	
	/** @var string Name */
	public 		$value;
	
 	protected 	$fieldsRequired = array('id_feature');
	protected 	$fieldsValidate = array('id_feature' => 'isUnsignedId');
 	protected 	$fieldsRequiredLang = array('value');
 	protected 	$fieldsSizeLang = array('value' => 255);
 	protected 	$fieldsValidateLang = array('value' => 'isGenericName');
		
	protected 	$table = 'feature_value';
	protected 	$identifier = 'id_feature_value';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_feature'] = intval($this->id_feature);
		return $fields;
	}
	
	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('value'));
	}
	
	/**
	 * Get all values for a given feature
	 *
	 * @param boolean $id_feature Feature id
	 * @return array Array with feature's values
	 * @static
	 */
	static public function getFeatureValues($id_feature)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'feature_value`
		WHERE `id_feature` = '.intval($id_feature));
	}
	
	/**
	 * Get all values for a given feature and language
	 *
	 * @param integer $id_lang Language id
	 * @param boolean $id_feature Feature id
	 * @return array Array with feature's values
	 * @static
	 */
	static public function getFeatureValuesWithLang($id_lang, $id_feature)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'feature_value` v
		LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` vl ON (v.`id_feature_value` = vl.`id_feature_value` AND vl.`id_lang` = '.intval($id_lang).')
		WHERE v.`id_feature` = '.intval($id_feature).' AND (v.`custom` IS NULL OR v.`custom` = 0)
		ORDER BY vl.`value` ASC');
	}

	/**
	 * Get all language for a given value
	 *
	 * @param boolean $id_feature_value Feature value id
	 * @return array Array with value's languages
	 * @static
	 */
	static public function getFeatureValueLang($id_feature_value)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'feature_value_lang`
		WHERE `id_feature_value` = '.intval($id_feature_value).'
		ORDER BY `id_lang`');
	}
	
	/**
	 * Select the good lang in tab
	 *
	 * @param array $lang Array with all language
	 * @param integer $id_lang Language id
	 * @return string String value name selected
	 * @static
	 */
	static public function selectLang($lang, $id_lang)
	{
		foreach ($lang as $tab)
			if ($tab['id_lang'] == $id_lang)
				return $tab['value'];
	}
	
	static public function addFeatureValueImport($id_feature, $name)
	{
		$rq = Db::getInstance()->ExecuteS('
			SELECT fv.`id_feature_value`
			FROM '._DB_PREFIX_.'feature_value fv
			LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.`id_feature_value` = fv.`id_feature_value`)
			WHERE `value` = \''.pSQL($name).'\'
			AND fv.`id_feature` = '.intval($id_feature).'
			GROUP BY fv.`id_feature_value` LIMIT 1');
		if (!isset($rq[0]['id_feature_value']) OR !$id_feature_value = intval($rq[0]['id_feature_value']))
		{
			// Feature doesn't exist, create it
			$featureValue = new FeatureValue();
			$languages = Language::getLanguages();
			foreach ($languages as $language)
				$featureValue->value[$language['id_lang']] = strval($name);
			$featureValue->id_feature = $id_feature;
			$featureValue->custom = 1;
			$featureValue->add();
			return $featureValue->id;
		}
		return $id_feature_value;
	}
}

?>