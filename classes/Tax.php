<?php

/**
  * Taxes class, Tax.php
  * Taxes management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class TaxCore extends ObjectModel
{
 	/** @var string Name */
	public 		$name;

	/** @var float Rate (%) */
	public 		$rate;
	
	/** @var bool active state */
	public 		$active;

 	protected 	$fieldsRequired = array('rate');
 	protected 	$fieldsValidate = array('rate' => 'isFloat');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'tax';
	protected 	$identifier = 'id_tax';
	
	protected static $_product_country_tax = array();

	public		$noZeroObject = 'getTaxes';

	public function getFields()
	{
		parent::validateFields();
		$fields['rate'] = floatval($this->rate);
		$fields['active'] = (int)($this->active);
		return $fields;
	}
	
	/** @var array Tax zones cache */
	private static $_TAX_ZONES;

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

	public function delete()
	{
		/* Clean associations */
		if (!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.(int)$this->id))
			return false;
		self::cleanAssociatedCountries((int)$this->id);
		return parent::delete();
	}

	/**
	 * @deprecated use Tax::zoneHasTax() instead
	 */
	public static function checkTaxZone($id_tax, $id_zone)
	{
		Tools::displayAsDeprecated();
		return isset(self::$_TAX_ZONES[intval($id_zone)][intval($id_tax)]);
	}

	public function getStates()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `id_state`, `id_tax` FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.(int)($this->id));
	}

	public function getState($id_state)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `id_state` FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.(int)($this->id).' AND `id_state` = '.(int)($id_state));
	}

	public function addState($id_state)
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_state` (`id_tax`, `id_state`) VALUES ('.(int)($this->id).', '.(int)($id_state).')');
	}

	public function deleteState($id_state)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.(int)($this->id).' AND `id_state` = '.(int)($id_state));
	}

	/**
	 * Get all zones
	 *
	 * @return array Zones
	 */
	public function getZones()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.(int)($this->id));
	}

	/**
	 * Get a specific zones
	 *
	 * @return array Zone
	 */
	public function getZone($id_zone)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.(int)($this->id).'
			AND `id_zone` = '.(int)($id_zone));
	}

	/**
	 * Add zone
	 */
	public function addZone($id_zone)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'tax_zone` (`id_tax` , `id_zone`)
			VALUES ('.(int)($this->id).', '.(int)($id_zone).')');
	}

	/**
	 * Delete zone
	 */
	public function deleteZone($id_zone)
	{
		return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.(int)($this->id).'
			AND `id_zone` = '.(int)($id_zone).' LIMIT 1');
	}

	/**
	* Get all available taxes
	*
	* @return array Taxes
	*/
	static public function getTaxes($id_lang = false, $active = 1)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT t.id_tax, t.rate'.((int)($id_lang) ? ', tl.name, tl.id_lang ' : '').'
		FROM `'._DB_PREFIX_.'tax` t
		'.((int)($id_lang) ? 'LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($id_lang).')'
		.($active == 1 ? 'WHERE t.`active` = 1' : '').'
		ORDER BY `name` ASC' : ''));
	}

	static public function excludeTaxeOption()
	{
		return !Configuration::get('PS_TAX');
	}

	static public function zoneHasTax($id_tax, $id_zone)
	{
		return isset(self::$_TAX_ZONES[intval($id_zone)][intval($id_tax)]);
	}

	static public function getRateByState($id_state, $active = 1)
	{
		$tax = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT ts.`id_tax`, t.`rate`
			FROM `'._DB_PREFIX_.'tax_state` ts
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = ts.`id_tax`)
			WHERE `id_state` = '.(int)($id_state).
			($active == 1 ? ' AND t.`active` = 1' : ''));
		return $tax ? floatval($tax['rate']) : false;
	}

	/**
	 *  Return the applicable tax rate depending of the country and state
	 * @deprecated use getApplicableTaxRate
	 * @param integer $id_tax
	 * @param float $productTax
	 * @param integer $id_address
	 * 
	 * @return float taxe_rate
	 */
	static public function getApplicableTax($id_tax, $productTax, $id_address = NULL)
	{
		Tools::displayAsDeprecated();
		return Tax::getApplicableTaxRate($id_tax, $productTax, $id_address);
	}
	
	/**
	 *  Return the applicable tax rate depending of the country and state
	 *
	 * @param integer $id_tax
	 * @param float $productTax
	 * @param integer $id_address
	 *
	 * @return float taxe_rate
	 */
	public static function getApplicableTaxRate($id_tax, $productTax, $id_address = NULL)
	{
		global $cart, $cookie, $defaultCountry;

		if (!is_object($cart))
			die(Tools::displayError());
			
		if (!$id_address)
			$id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
			
		/* If customer has an address (implies that he is registered and logged) */
		if ($id_address AND $address_infos = Address::getCountryAndState($id_address))
		{
			if (!empty($address_infos['vat_number']) AND $address_infos['id_country'] != Configuration::get('VATNUMBER_COUNTRY') AND Configuration::get('VATNUMBER_MANAGEMENT'))
				return 0;
				
			/* If customer's invoice address is inside a state */
			if ($address_infos['id_state'])
			{
				$id_zone_country = Country::getIdZone(intval($address_infos['id_country']));				
				$state = new State(intval($address_infos['id_state']));				
				if (!Validate::isLoadedObject($state))
					die(Tools::displayError());
					
				/* Return tax value depending to the tax behavior */
				$tax_behavior = (int)($state->tax_behavior);
				if ($tax_behavior == PS_PRODUCT_TAX)
					return $productTax * Tax::zoneHasTax((int)($id_tax), (int)($id_zone_country));
				if ($tax_behavior == PS_STATE_TAX)
					return Tax::getRateByState((int)($address_infos['id_state']));
				if ($tax_behavior == PS_BOTH_TAX)
					return ($productTax * Tax::zoneHasTax((int)($id_tax), (int)($id_zone_country))) + Tax::getRateByState((int)($address_infos['id_state']));
				/* Unknown behavior */
				die(Tools::displayError('Unknown tax behavior!'));
			}
			
			/* Else getting country zone tax */
			if (!$id_zone = Address::getZoneById($id_address))
				die(Tools::displayError());
			
			return $productTax * Tax::zoneHasTax((int)($id_tax), (int)($id_zone));
		}

		/* Default tax application */
		if (!Validate::isLoadedObject($defaultCountry))
			die(Tools::displayError());
		return $productTax * Tax::zoneHasTax((int)($id_tax), (int)($defaultCountry->id_zone));
	}

	/**
	  * Load all tax/zones relations in memory for caching
	  */
	static public function loadTaxZones($active = 1)
	{
		self::$_TAX_ZONES = array();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT tz.`id_tax`, tz.`id_zone` FROM `'._DB_PREFIX_.'tax_zone` tz 
		JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tz.`id_tax`)'.
		($active == 1 ? ' WHERE t.`active` = 1' : ''));
		if ($result === false)
			die(Tools::displayError('Invalid loadTaxZones() SQL query!'));
		foreach ($result AS $row)
			self::$_TAX_ZONES[(int)($row['id_zone'])][(int)($row['id_tax'])] = true;
	}
	
	static public function getTaxIdByRate($rate, $active = 1)
	{
		$tax = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `id_tax`
			FROM `'._DB_PREFIX_.'tax`
			WHERE `rate` = '.floatval($rate).
			($active == 1 ? ' AND `active` = 1' : ''));
		return $tax ? (int)($tax['id_tax']) : false;
	}

	static public function getDataByProductId($id_product)
	{
		return Db::getInstance()->getRow('
		SELECT p.`id_tax`, t.`rate`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'tax` AS t ON t.`id_tax` = p.`id_tax`
		WHERE p.`id_product` = '.(int)($id_product));
	}
	
	/**
	 * Return the product country tax
	 *
	 * @param integer $id_product
	 * @param integer $id_country
	 * @return Tax
	 */		
	public static function getProductTaxRate($id_product, $id_country, $id_tax, $product_tax, $id_address = NULL)
	{		
		$rate = Tax::getProductCountryTaxRate((int)($id_product), intval($id_country));

		if (!$rate)
			$rate = Tax::getApplicableTaxRate((int)($id_tax), floatval($product_tax), (int)($id_address));
	
		return $rate;
	}

	/**
	 * Return the product country tax rate
	 *
	 * @param integer $id_product
	 * @param integer $id_country
	 * @return float
	 */		
	public static function getProductCountryTaxRate($id_product, $id_country)
	{
		$tax = Tax::getProductCountryTax($id_product, $id_country);		
		return $tax ? $tax->rate : 0;
	}
	
	/**
	 * Return the product country tax
	 *
	 * @param integer $id_product
	 * @param integer $id_country
	 * @return Tax
	 */		
	public static function getProductCountryTax($id_product, $id_country)
	{		
	
		if (!isset(self::$_product_country_tax[$id_product.'-'.$id_country]))
		{		
			$id_tax = Db::getInstance()->getValue('
				SELECT `id_tax`
				FROM `'._DB_PREFIX_.'product_country_tax`
				WHERE `id_product` = '.(int)$id_product.'
				AND `id_country` = '.(int)$id_country.'
				ORDER BY `id_country` DESC, `id_product` DESC'
			);		
		

			self::$_product_country_tax[$id_product.'-'.$id_country] = (!empty($id_tax) ? new Tax((int)$id_tax) : false);
		}

		return self::$_product_country_tax[$id_product.'-'.$id_country];
	}		
	
	
	public static function getProductCountryTaxes($id_product, $id_lang)
	{
		return 	Db::getInstance()->ExecuteS('
		SELECT c.`id_country`, c.`name` AS country, t.`rate`
		FROM `'._DB_PREFIX_.'product_country_tax` pct
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = pct.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'country_lang` c ON (c.`id_country` = pct.`id_country`)
		WHERE pct.`id_product` = '.intval($id_product).'
		AND c.`id_lang` = '.(int)($id_lang)		
		);
	}
	
	
	public static function deleteProductCountryTax($id_product, $id_country)
	{
		return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'product_country_tax`
			WHERE `id_product` = '.(int)$id_product.'
			AND `id_country` = '.(int)$id_country
		);	
	}
	
	/**
	 * Create a product tax
	 *
	 * @param integer $id_product
	 * @param integer $id_country
	 * @param integer $id_tax 
	 * @return boolean
	 */
	public static function setProductCountryTax($id_product, $id_country, $id_tax)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'product_country_tax`(`id_product`, `id_country`, `id_tax`)
			VALUES ('.(int)($id_product).','.(int)($id_country).','.(int)($id_tax).')
		');
	}
	
	static public function cleanAssociatedCountries($id_tax)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'country_tax` WHERE `id_tax` = '.(int)$id_tax);
	}
}
