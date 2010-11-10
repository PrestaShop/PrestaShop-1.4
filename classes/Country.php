<?php

/**
  * Country class, Country.php
  * Countries management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class CountryCore extends ObjectModel
{
	public 		$id;

	/** @var integer Zone id which country belongs */
	public 		$id_zone;

	/** @var string 2 letters iso code */
	public 		$iso_code;

	/** @var integer international call prefix */
	public 		$call_prefix;

	/** @var string Name */
	public 		$name;

	/** @var boolean Contain states */
	public		$contains_states;

	/** @var boolean Need identification number dni/nif/nie */
	public		$need_identification_number;
	
	/** @var boolean Need Zip Code */
	public		$need_zip_code;
	
	/** @var string Zip Code Format */
	public		$zip_code_format;
	
	/** @var boolean Status for delivery */
	public		$active = true;

	private static $_idZones = array();

	protected 	$tables = array ('country', 'country_lang');

 	protected 	$fieldsRequired = array('id_zone', 'iso_code', 'contains_states', 'need_identification_number');
 	protected 	$fieldsSize = array('iso_code' => 3);
 	protected 	$fieldsValidate = array('id_zone' => 'isUnsignedId', 'call_prefix' => 'isInt', 'iso_code' => 'isLanguageIsoCode', 'active' => 'isBool', 'contains_states' => 'isBool', 'need_identification_number' => 'isBool', 'need_zip_code' => 'isBool', 'zip_code_format' => 'isZipCodeFormat');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 64);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected	$webserviceParameters = array(
		'objectsNodeName' => 'countries',
		'fields' => array(
			'id_zone' => array('sqlId' => 'id_zone', 'xlink_resource'=> 'zones'),
		),
		'linked_tables' => array(//TODO this should be native...
			'i18n' => array(
				'table' => 'country_lang',
				'fields' => array(
					'id_lang' => array('sqlId' => 'id_lang', 'xlink_resource'=> 'languages'),
					'name' => array('sqlId' => 'name'),
				),
			),
		),
	);

	protected 	$table = 'country';
	protected 	$identifier = 'id_country';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_zone'] = intval($this->id_zone);
		$fields['iso_code'] = pSQL(strtoupper($this->iso_code));
		$fields['call_prefix'] = intval($this->call_prefix);
		$fields['active'] = intval($this->active);
		$fields['contains_states'] = intval($this->contains_states);
		$fields['need_identification_number'] = intval($this->need_identification_number);
		$fields['need_zip_code'] = intval($this->need_zip_code);
		$fields['zip_code_format'] = $this->zip_code_format;
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

		$states = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT s.*
		FROM `'._DB_PREFIX_.'state` s
		ORDER BY s.`name` ASC');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
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
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
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

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
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
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'country_lang`
		WHERE `id_lang` = '.intval($id_lang).'
		AND `id_country` = '.intval($id_country));

		return $result['name'];
	}
    
	/**
	* Get a country iso with its ID
	*
	* @param integer $id_country Country ID
	* @return string Country iso
	*/
	static public function getIsoById($id_country)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `iso_code`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.intval($id_country));

		return $result['iso_code'];
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
		 	
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

		return (intval($result['id_country']));
	}
    
	static public function getNeedIdentifcationNumber($id_country)
	{
		if (!intval($id_country))
			return false;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `need_identification_number` 
		FROM `'._DB_PREFIX_.'country` 
		WHERE `id_country` = '.intval($id_country));
	}

	static public function getNeedZipCode($id_country)
	{
		if (!intval($id_country))
			return false;
	
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `need_zip_code` 
		FROM `'._DB_PREFIX_.'country` 
		WHERE `id_country` = '.intval($id_country));
	}

	static public function getZipCodeFormat($id_country)
	{
		if (!intval($id_country))
			return false;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `zip_code_format` 
		FROM `'._DB_PREFIX_.'country` 
		WHERE `id_country` = '.intval($id_country));
	}
	
	public static function displayCallPrefix($prefix)
	{
		return (intval($prefix) ? '+'.$prefix : '-');
	}
}

?>