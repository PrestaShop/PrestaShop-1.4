<?php

/**
  * Country class, Country.php
  * Countries management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Country extends ObjectModel
{
	public 		$id;

	/** @var integer Zone id which country belongs */
	public 		$id_zone;

	/** @var string 2 letters iso code */
	public 		$iso_code;

	/** @var string Name */
	public 		$name;

	/** @var boolean Contain states */
	public		$contains_states;

	/** @var boolean Status for delivery */
	public		$active = true;

	private static $_idZones = array();

	protected 	$tables = array ('country', 'country_lang');

 	protected 	$fieldsRequired = array('id_zone', 'iso_code', 'contains_states');
 	protected 	$fieldsSize = array('iso_code' => 3);
 	protected 	$fieldsValidate = array('id_zone' => 'isUnsignedId', 'iso_code' => 'isLanguageIsoCode', 'active' => 'isBool', 'contains_states' => 'isBool');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 64);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'country';
	protected 	$identifier = 'id_country';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_zone'] = intval($this->id_zone);
		$fields['iso_code'] = pSQL(strtoupper($this->iso_code));
		$fields['active'] = intval($this->active);
		$fields['contains_states'] = intval($this->contains_states);
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
		return parent::getTranslationsFields(array('name'));
	}

	/**
	  * Return available countries
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active coutries
	  * @return array Countries and corresponding zones
	  */
	static public function getCountries($id_lang, $active = false, $containStates = NULL)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$states = Db::getInstance()->ExecuteS('
		SELECT s.*
		FROM `'._DB_PREFIX_.'state` s
		');

		$result = Db::getInstance()->ExecuteS('
		SELECT cl.*,c.*, cl.`name` AS country, z.`name` AS zone
		FROM `'._DB_PREFIX_.'country` c
		LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'zone` z ON z.`id_zone` = c.`id_zone`
		WHERE 1
		'.($active ? 'AND c.active = 1' : '').'
		'.(!is_null($containStates) ? 'AND c.`contains_states` = '.intval($containStates) : '').'
		ORDER BY cl.name ASC');
		$countries = array();
		foreach ($result AS &$country)
			$countries[$country['id_country']] = $country;
		foreach ($states AS &$state)
			if (isset($countries[$state['id_country']])) /* Does not keep the state if its country has been disabled and not selected */
				$countries[$state['id_country']]['states'][] = $state;
		return $countries;
	}

	/**
	  * Get a country ID with its iso code
	  *
	  * @param string $iso_code Country iso code
	  * @return integer Country ID
	  */
	static public function getByIso($iso_code)
    {
     	if (!Validate::isLanguageIsoCode($iso_code))
     		die(Tools::displayError());

	    $result = Db::getInstance()->getRow('
		SELECT `id_country`
		FROM `'._DB_PREFIX_.'country`
		WHERE `iso_code` = \''.pSQL(strtoupper($iso_code)).'\'');

		return $result['id_country'];
    }
	
	static public function getIdZone($id_country)
    {		
     	if (!Validate::isUnsignedId($id_country))
     		die(Tools::displayError());
			
		if (isset(self::$_idZones[$id_country]))
			return self::$_idZones[$id_country];

	    $result = Db::getInstance()->getRow('
		SELECT `id_zone`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.intval($id_country));

		self::$_idZones[$id_country] = $result['id_zone'];
		return $result['id_zone'];
    }

	/**
	* Get a country name with its ID
	*
	* @param integer $id_lang Language ID
	* @param integer $id_country Country ID
	* @return string Country name
	*/
	static public function getNameById($id_lang, $id_country)
    {
	    $result = Db::getInstance()->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'country_lang`
		WHERE `id_lang` = '.intval($id_lang).'
		AND `id_country` = '.intval($id_country));

        return $result['name'];
    }
    
	/**
	* Get a country id with its name
	*
	* @param integer $id_lang Language ID
	* @param string $country Country Name 
	* @return intval Country id
	*/
	static public function getIdByName($id_lang = NULL, $country)
    {
	    $sql = '
		SELECT `id_country`
		FROM `'._DB_PREFIX_.'country_lang`
		WHERE `name` LIKE \''.pSQL($country).'\'';
		if ($id_lang)
			$sql .= ' AND `id_lang` = '.intval($id_lang);
		 	
		$result = Db::getInstance()->getRow($sql);
        return (intval($result['id_country']));
    }    
}

?>
