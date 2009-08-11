<?php

/**
  * Taxes class, Tax.php
  * Taxes management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class Tax extends ObjectModel
{
 	/** @var string Name */
	public 		$name;

	/** @var float Rate (%) */
	public 		$rate;

 	protected 	$fieldsRequired = array('rate');
 	protected 	$fieldsValidate = array('rate' => 'isFloat');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'tax';
	protected 	$identifier = 'id_tax';

	public		$noZeroObject = 'getTaxes';

	public function getFields()
	{
		parent::validateFields();
		$fields['rate'] = floatval($this->rate);
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

	public static function checkTaxZone($id_tax, $id_zone)
	{
		return isset(self::$_TAX_ZONES[intval($id_zone)][intval($id_tax)]);
	}

	public function getStates()
	{
		return Db::getInstance()->ExecuteS('SELECT `id_state`, `id_tax` FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id));
	}

	public function getState($id_state)
	{
		return Db::getInstance()->getRow('SELECT `id_state` FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id).' AND `id_state` = '.intval($id_state));
	}

	public function addState($id_state)
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_state` (`id_tax`, `id_state`) VALUES ('.intval($this->id).', '.intval($id_state).')');
	}

	public function deleteState($id_state)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id).' AND `id_state` = '.intval($id_state));
	}

	/**
	 * Get all zones
	 *
	 * @return array Zones
	 */
	public function getZones()
	{
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.intval($this->id));
	}

	/**
	 * Get a specific zones
	 *
	 * @return array Zone
	 */
	public function getZone($id_zone)
	{
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.intval($this->id).'
			AND `id_zone` = '.intval($id_zone));
	}

	/**
	 * Add zone
	 */
	public function addZone($id_zone)
	{
		return Db::getInstance()->ExecuteS('
			INSERT INTO `'._DB_PREFIX_.'tax_zone` (`id_tax` , `id_zone`)
			VALUES ('.intval($this->id).', '.intval($id_zone).')');
	}

	/**
	 * Delete zone
	 */
	public function deleteZone($id_zone)
	{
		return Db::getInstance()->ExecuteS('
			DELETE FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.intval($this->id).'
			AND `id_zone` = '.intval($id_zone).' LIMIT 1');
	}

	/**
	* Get all available taxes
	*
	* @return array Taxes
	*/
	static public function getTaxes($id_lang = false)
	{
		return Db::getInstance()->ExecuteS('
		SELECT t.id_tax, t.rate'.(intval($id_lang) ? ', tl.name, tl.id_lang ' : '').'
		FROM `'._DB_PREFIX_.'tax` t
		'.(intval($id_lang) ? 'LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')
		ORDER BY `name` ASC' : ''));
	}

	static public function excludeTaxeOption()
	{
		return !Configuration::get('PS_TAX');
	}

	static public function zoneHasTax($id_tax, $id_zone)
	{
		return Tax::checkTaxZone(intval($id_tax), intval($id_zone));
	}

	static public function getRateByState($id_state)
	{
		$tax = Db::getInstance()->getRow('
			SELECT ts.`id_tax`, t.`rate`
			FROM `'._DB_PREFIX_.'tax_state` ts
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = ts.`id_tax`)
			WHERE `id_state` = '.intval($id_state));
		return $tax ? floatval($tax['rate']) : false;
	}

	static public function getApplicableTax($id_tax, $productTax)
	{
		global $cart, $cookie, $defaultCountry;

		$id_address_invoice = intval((Validate::isLoadedObject($cart) AND $cart->id_address_invoice) ? $cart->id_address_invoice : (isset($cookie->id_address_invoice) ? $cookie->id_address_invoice : 0));
		/* If customer has an address (implies that he is registered and logged) */
		if ($id_address_invoice AND $address_ids = Address::getCountryAndState($id_address_invoice))
		{
			$id_zone_country = Country::getIdZone(intval($address_ids['id_country']));
			/* If customer's invoice address is inside a state */
			if ($address_ids['id_state'])
			{
				$state = new State(intval($address_ids['id_state']));
				
				if (!Validate::isLoadedObject($state))
					die(Tools::displayError());
				/* Return tax value depending to the tax behavior */
				$tax_behavior = intval($state->tax_behavior);
				if ($tax_behavior == PS_PRODUCT_TAX)
					return $productTax * Tax::zoneHasTax(intval($id_tax), intval($id_zone_country));
				if ($tax_behavior == PS_STATE_TAX)
					return Tax::getRateByState(intval($address_ids['id_state']));
				if ($tax_behavior == PS_BOTH_TAX)
					return ($productTax * Tax::zoneHasTax(intval($id_tax), intval($id_zone_country))) + Tax::getRateByState(intval($address_ids['id_state']));
				/* Unknown behavior */
				die(Tools::displayError('Unknown tax behavior!'));
			}
			/* Else getting country zone tax */
			if (!$id_zone = Address::getZoneById($id_address_invoice))
				die(Tools::displayError());
			return $productTax * Tax::zoneHasTax(intval($id_tax), intval($id_zone));
		}
		/* Default tax application */
		if (!Validate::isLoadedObject($defaultCountry))
			die(Tools::displayError());
		return $productTax * Tax::zoneHasTax(intval($id_tax), intval($defaultCountry->id_zone));
	}
	
	/**
	  * Load all tax/zones relations in memory for caching
	  */
	static public function loadTaxZones()
	{
		self::$_TAX_ZONES = array();
		$result = Db::getInstance()->ExecuteS('SELECT `id_tax`, `id_zone` FROM `'._DB_PREFIX_.'tax_zone`');
		if ($result === false)
			die(Tools::displayError('Invalid loadTaxZones() SQL query!'));
		foreach ($result AS $row)
			self::$_TAX_ZONES[intval($row['id_zone'])][intval($row['id_tax'])] = true;
	}
	
	static public function getTaxIdByRate($rate)
	{
		$tax = Db::getInstance()->getRow('
			SELECT `id_tax`
			FROM `'._DB_PREFIX_.'tax`
			WHERE `rate` LIKE '.floatval($rate));
		return $tax ? intval($tax['id_tax']) : false;
	}
}
