<?php

/**
  * Addresses class, Address.php
  * Addresses management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Address extends ObjectModel
{
	/** @var integer Customer id which address belongs */
	public		$id_customer = NULL;

	/** @var integer Manufacturer id which address belongs */
	public		$id_manufacturer = NULL;

	/** @var integer Supplier id which address belongs */
	public		$id_supplier = NULL;

	/** @var integer Country id */
	public		$id_country;

	/** @var integer Country id */
	public		$id_state;

	/** @var string Country name */
	public		$country;

	/** @var string Alias (eg. Home, Work...) */
	public		$alias;

	/** @var string Company (optional) */
	public 		$company;

	/** @var string Lastname */
	public 		$lastname;

	/** @var string Firstname */
	public 		$firstname;

	/** @var string Address first line */
	public 		$address1;

	/** @var string Address second line (optional) */
	public 		$address2;

	/** @var string Postal code */
	public 		$postcode;

	/** @var string City */
	public 		$city;

	/** @var string Any other useful information */
	public 		$other;

	/** @var string Phone number */
	public 		$phone;

	/** @var string Mobile phone number */
	public 		$phone_mobile;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	/** @var boolean True if address has been deleted (staying in database as deleted) */
	public 		$deleted = 0;
	
	private static $_idZones = array();
	private static $_idCountries = array();

	protected	$fieldsRequired = array('id_country', 'alias', 'lastname', 'firstname', 'address1', 'postcode', 'city');
	protected	$fieldsSize = array('alias' => 32, 'company' => 32, 'lastname' => 32, 'firstname' => 32,
									'address1' => 128, 'address2' => 128, 'postcode' => 12, 'city' => 64,
									'other' => 300, 'phone' => 16, 'phone_mobile' => 16);
	protected	$fieldsValidate = array('id_customer' => 'isNullOrUnsignedId', 'id_manufacturer' => 'isNullOrUnsignedId',
										'id_supplier' => 'isNullOrUnsignedId', 'id_country' => 'isUnsignedId', 'id_state' => 'isNullOrUnsignedId',
										'alias' => 'isGenericName', 'company' => 'isGenericName', 'lastname' => 'isName',
										'firstname' => 'isName', 'address1' => 'isAddress', 'address2' => 'isAddress',
										'postcode' => 'isPostCode', 'city' => 'isCityName', 'other' => 'isMessage',
										'phone' => 'isPhoneNumber', 'phone_mobile' => 'isPhoneNumber', 'deleted' => 'isBool');

	protected 	$table = 'address';
	protected 	$identifier = 'id_address';
	protected	$_includeVars = array('addressType' => 'table');
	protected	$_includeContainer = false;

	/**
	 * Build an address
	 *
	 * @param integer $id_address Existing address id in order to load object (optional)
	 */
	public	function __construct($id_address = NULL, $id_lang = NULL)
	{
		parent::__construct($id_address);

		/* Get and cache address country name */
		if ($this->id)
		{
			$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'country_lang`
												WHERE `id_country` = '.intval($this->id_country).'
												AND `id_lang` = '.($id_lang ? intval($id_lang) : Configuration::get('PS_LANG_DEFAULT')));
			$this->country = $result['name'];
		}
	}

	public function delete()
	{
		if (!$this->isUsed())
			return parent::delete();
		else
		{
			$class =  get_class($this);
			$obj = new $class($this->id);
			$obj->deleted = true;
			return $obj->update();
		}
	}

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_address'] = intval($this->id);
		$fields['id_customer'] = is_null($this->id_customer) ? 0 : intval($this->id_customer);
		$fields['id_manufacturer'] = is_null($this->id_manufacturer) ? 0 : intval($this->id_manufacturer);
		$fields['id_supplier'] = is_null($this->id_supplier) ? 0 : intval($this->id_supplier);
		$fields['id_country'] = intval($this->id_country);
		$fields['id_state'] = intval($this->id_state);
		$fields['alias'] = pSQL($this->alias);
		$fields['company'] = pSQL($this->company);
		$fields['lastname'] = pSQL(Tools::strtoupper($this->lastname));
		$fields['firstname'] = pSQL($this->firstname);
		$fields['address1'] = pSQL($this->address1);
		$fields['address2'] = pSQL($this->address2);
		$fields['postcode'] = pSQL($this->postcode);
		$fields['city'] = pSQL($this->city);
		$fields['other'] = pSQL($this->other);
		$fields['phone'] = pSQL($this->phone);
		$fields['phone_mobile'] = pSQL($this->phone_mobile);
		$fields['deleted'] = intval($this->deleted);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}

	/**
	 * Get zone id for a given address
	 *
	 * @param integer $id_address Address id for which we want to get zone id
	 * @return integer Zone id
	 */
	public static function getZoneById($id_address)
	{
		if (isset(self::$_idZones[$id_address]))
			return self::$_idZones[$id_address];
	
		$result = Db::getInstance()->getRow('
		SELECT s.`id_zone` AS id_zone_state, c.`id_zone`
		FROM `'._DB_PREFIX_.'address` a
		LEFT JOIN `'._DB_PREFIX_.'country` c ON c.`id_country` = a.`id_country`
		LEFT JOIN `'._DB_PREFIX_.'state` s ON s.`id_state` = a.`id_state`
		WHERE a.`id_address` = '.intval($id_address));

		self::$_idZones[$id_address] = (intval($result['id_zone_state']) ? $result['id_zone_state'] : $result['id_zone']);
		return self::$_idZones[$id_address];
	}

	/**
	 * Check if country is active for a given address
	 *
	 * @param integer $id_address Address id for which we want to get country status
	 * @return integer Country status
	 */
	public static function isCountryActiveById($id_address)
	{
		if (!$result = Db::getInstance()->getRow('
		SELECT c.`active`
		FROM `'._DB_PREFIX_.'address` a
		LEFT JOIN `'._DB_PREFIX_.'country` c ON c.`id_country` = a.`id_country`
		WHERE a.`id_address` = '.intval($id_address)))
			return false;
		return ($result['active']);
	}

	/**
	 * Check if address is used (at least one order placed)
	 *
	 * @return integer Order count for this address
	 */
	public function isUsed()
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(`id_order`) AS used
		FROM `'._DB_PREFIX_.'orders`
		WHERE `id_address_delivery` = '.intval($this->id).'
		OR `id_address_invoice` = '.intval($this->id));

		return isset($result['used']) ? $result['used'] : false;
	}

	static public function getManufacturerIdByAddress($id_address)
	{
		$result = Db::getInstance()->getRow('
			SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'address`
			WHERE `id_address` = '.intval($id_address));
		return isset($result['id_manufacturer']) ? $result['id_manufacturer'] : false;
	}
	
	static public function getCountryAndState($id_address)
	{
		if (isset(self::$_idCountries[$id_address]))
			return self::$_idCountries[$id_address];
		$result = Db::getInstance()->getRow('
		SELECT `id_country`, `id_state` FROM `'._DB_PREFIX_.'address`
		WHERE `id_address` = '.intval($id_address));
		self::$_idCountries[$id_address] = $result;
		return $result;
	}
	
	/**
	* Specify if an address is already in base
	*
	* @param $id_address Address id
	* @return boolean
	*/	
	static public function addressExists($id_address)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_address`
		FROM '._DB_PREFIX_.'address a
		WHERE a.`id_address` = '.intval($id_address));
		
		return isset($row['id_address']);
	}

	static public function getFirstCustomerAddressId($id_customer, $active = true)
	{
		return Db::getInstance()->getValue('
			SELECT `id_address`
			FROM `'._DB_PREFIX_.'address`
			WHERE `id_customer` = '.intval($id_customer).' AND `deleted` = 0'.($active ? ' AND `active` = 1' : '')
		);
	}
}

?>