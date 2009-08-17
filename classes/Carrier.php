<?php

/**
  * Carriers class, Carrier.php
  * Carriers management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Carrier extends ObjectModel
{
	/** @var int Tax id (none = 0) */
	public		$id_tax;

 	/** @var string Name */
	public 		$name;

 	/** @var string URL with a '@' for */
	public 		$url;

	/** @var string Delay needed to deliver customer */
	public 		$delay;

	/** @var boolean Carrier statuts */
	public 		$active = true;

	/** @var boolean True if carrier has been deleted (staying in database as deleted) */
	public 		$deleted = 0;

	/** @var boolean Active or not the shipping handling */
	public		$shipping_handling = true;
	
	/** @var int Behavior taken for unknown range */
	public		$range_behavior;
	
	/** @var boolean Carrier module */
	public		$is_module;

 	protected 	$fieldsRequired = array('name', 'active');
 	protected 	$fieldsSize = array('name' => 64);
 	protected 	$fieldsValidate = array('id_tax' => 'isInt', 'name' => 'isCarrierName', 'active' => 'isBool', 'url' => 'isAbsoluteUrl', 'shipping_handling' => 'isBool', 'range_behavior' => 'isBool');
 	protected 	$fieldsRequiredLang = array('delay');
 	protected 	$fieldsSizeLang = array('delay' => 128);
 	protected 	$fieldsValidateLang = array('delay' => 'isGenericName');

	protected 	$table = 'carrier';
	protected 	$identifier = 'id_carrier';
	
	private static $priceByWeight = array();
	private static $priceByPrice = array();

	public function getFields()
	{
		parent::validateFields();
		$fields['id_tax'] = intval($this->id_tax);
		$fields['name'] = pSQL($this->name);
		$fields['url'] = pSQL($this->url);
		$fields['active'] = intval($this->active);
		$fields['deleted'] = intval($this->deleted);
		$fields['shipping_handling'] = intval($this->shipping_handling);
		$fields['range_behavior'] = intval($this->range_behavior);
		$fields['is_module'] = intval($this->is_module);
		return $fields;
	}

	public function __construct($id = NULL, $id_lang = NULL)
	{
		parent::__construct($id, $id_lang);
		if ($this->name == '0')
			$this->name = Configuration::get('PS_SHOP_NAME');
	}

	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('delay'));
	}

	public function add($autodate = true, $nullValues = false)
	{
		if (!parent::add($autodate, $nullValues) OR !Validate::isLoadedObject($this))
			return false;
		if (!$result = Db::getInstance()->ExecuteS('SELECT `id_carrier` FROM `'._DB_PREFIX_.$this->table.'` WHERE `deleted` = 0'))
			return false;
		if (!$numRows = Db::getInstance()->NumRows())
			return false;
		if (intval($numRows) == 1)
			Configuration::updateValue('PS_CARRIER_DEFAULT', intval($this->id));
		return true;
	}

	/**
	* Change carrier id in delivery prices when updating a carrier
	*
	* @param integer $id_old Old id carrier
	*/
	public function setConfiguration($id_old)
	{
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'delivery` SET `id_carrier` = '.intval($this->id).' WHERE `id_carrier` = '.intval($id_old));
	}

	/**
	 * Get delivery prices for a given order
	 *
	 * @param floatval $totalWeight Order total weight
	 * @param integer $id_zone Zone id (for customer delivery address)
	 * @return float Delivery price
	 */
	public function getDeliveryPriceByWeight($totalWeight, $id_zone)
	{
		if (isset(self::$priceByWeight[$this->id]))
			return self::$priceByWeight[$this->id];
		$result = Db::getInstance()->getRow('
		SELECT d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		LEFT JOIN `'._DB_PREFIX_.'range_weight` w ON (d.`id_range_weight` = w.`id_range_weight`)
		WHERE d.`id_zone` = '.intval($id_zone).'
		AND '.floatval($totalWeight).' <= w.`delimiter2`
		AND d.`id_carrier` = '.intval($this->id).'
		ORDER BY w.`delimiter1` ASC');
		if (!isset($result['price']))
			return $this->getMaxDeliveryPriceByWeight($id_zone);
		return $result['price'];
	}
	
	static public function checkDeliveryPriceByWeight($id_carrier, $totalWeight, $id_zone)
	{
		$result = Db::getInstance()->getRow('
		SELECT d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		LEFT JOIN `'._DB_PREFIX_.'range_weight` w ON d.`id_range_weight` = w.`id_range_weight`
		WHERE d.`id_zone` = '.intval($id_zone).'
		AND '.floatval($totalWeight).' <= w.`delimiter2`
		AND d.`id_carrier` = '.intval($id_carrier).'
		ORDER BY w.`delimiter1` ASC');
		if (!isset($result['price']))
			return false;
		return true;
	}
	
	public function getMaxDeliveryPriceByWeight($id_zone)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		INNER JOIN `'._DB_PREFIX_.'range_weight` w ON d.`id_range_weight` = w.`id_range_weight`
		WHERE d.`id_zone` = '.intval($id_zone).'
		AND d.`id_carrier` = '.intval($this->id).'
		ORDER BY w.`delimiter2` DESC LIMIT 1');
		if (!isset($result[0]['price']))
			return false;
		return $result[0]['price'];
	}

	/**
	 * Get delivery prices for a given order
	 *
	 * @param floatval $orderTotal Order total to pay
	 * @param integer $id_zone Zone id (for customer delivery address)
	 * @return float Delivery price
	 */
	public function getDeliveryPriceByPrice($orderTotal, $id_zone)
	{
		
		if (isset(self::$priceByPrice[$this->id]))
			return self::$priceByPrice[$this->id];
		$result = Db::getInstance()->getRow('
		SELECT d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		LEFT JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
		WHERE d.`id_zone` = '.intval($id_zone).'
		AND '.floatval($orderTotal).' <= r.`delimiter2`
		AND d.`id_carrier` = '.intval($this->id).'
		ORDER BY r.`delimiter1` ASC');
		if (!isset($result['price']))
			return $this->getMaxDeliveryPriceByPrice($id_zone);
		return $result['price'];
	}
	
	static public function checkDeliveryPriceByPrice($id_carrier, $orderTotal, $id_zone)
	{
		$result = Db::getInstance()->getRow('
		SELECT d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		LEFT JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
		WHERE d.`id_zone` = '.intval($id_zone).'
		AND '.floatval($orderTotal).' <= r.`delimiter2`
		AND d.`id_carrier` = '.intval($id_carrier).'
		ORDER BY r.`delimiter1` ASC');
		if (!isset($result['price']))
			return false;
		return true;
	}
	
	public function getMaxDeliveryPriceByPrice($id_zone)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		INNER JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
		WHERE d.`id_zone` = '.intval($id_zone).'
		AND d.`id_carrier` = '.intval($this->id).'
		ORDER BY r.`delimiter2` DESC LIMIT 1');
		if (!isset($result[0]['price']))
			return false;
		return $result[0]['price'];
	}

	/**
	 * Get delivery prices for a given shipping method (price/weight)
	 *
	 * @param string $rangeTable Table name (price or weight)
	 * @return array Delivery prices
	 */
	public static function getDeliveryPriceByRanges($rangeTable)
	{
		$rangeTable = pSQL($rangeTable);
		return Db::getInstance()->ExecuteS('
		SELECT d.`id_'.$rangeTable.'`, d.`id_carrier`, d.`id_zone`, d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		LEFT JOIN `'._DB_PREFIX_.$rangeTable.'` r ON r.`id_'.$rangeTable.'` = d.`id_'.$rangeTable.'`
		WHERE (d.`id_'.$rangeTable.'` IS NOT NULL AND d.`id_'.$rangeTable.'` != 0)
		ORDER BY r.`delimiter1` ASC');
	}

	/**
	 * Get all carriers in a given language
	 *
	 * @param integer $id_lang Language id
	 * @param boolean $active Returns only active carriers when true
	 * @return array Carriers
	 */
	public static function getCarriers($id_lang, $active = false, $delete = false, $id_zone = false)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());
	 	
		$sql = '
			SELECT c.*, cl.delay
			FROM `'._DB_PREFIX_.'carrier` c
			LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.intval($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz  ON (cz.`id_carrier` = c.`id_carrier`)'.
			($id_zone ? 'LEFT JOIN `'._DB_PREFIX_.'zone` z  ON (z.`id_zone` = '.intval($id_zone).')' : '').'
			WHERE c.`deleted` '.($delete ? '= 1' : ' = 0').
			($active ? ' AND c.`active` = 1' : '').
			($id_zone ? ' AND cz.`id_zone` = '.intval($id_zone).'
			AND z.`active` = 1' : '').'
			AND c.`is_module` = 0
			GROUP BY c.`id_carrier`';
		$carriers = Db::getInstance()->ExecuteS($sql);
		
		if (is_array($carriers) AND count($carriers))
		{
			foreach ($carriers as $key => $carrier)
				if ($carrier['name'] == '0')
					$carriers[$key]['name'] = Configuration::get('PS_SHOP_NAME');
		}
		else
			$carriers = array();

		return $carriers;
	}

	public static function checkCarrierZone($id_carrier, $id_zone)
	{
		return Db::getInstance()->ExecuteS('
			SELECT c.`id_carrier`
			FROM `'._DB_PREFIX_.'carrier` c
			LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz  ON (cz.`id_carrier` = c.`id_carrier`)
			LEFT JOIN `'._DB_PREFIX_.'zone` z  ON (z.`id_zone` = '.$id_zone.')
			WHERE c.`id_carrier` = '.$id_carrier.'
			AND c.`deleted` = 0
			AND c.`active` = 1
			AND cz.`id_zone` = '.$id_zone.'
			AND z.`active` = 1'
		);
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
			FROM `'._DB_PREFIX_.'carrier_zone`
			WHERE `id_carrier` = '. intval($this->id));
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
			FROM `'._DB_PREFIX_.'carrier_zone`
			WHERE `id_carrier` = '.intval($this->id).'
			AND `id_zone` = '.intval($id_zone));
	}

	/**
	 * Add zone
	 */
	public function addZone($id_zone)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'carrier_zone` (`id_carrier` , `id_zone`)
			VALUES ('.intval($this->id).', '.intval($id_zone).')');
	}

	/**
	 * Delete zone
	 */
	public function deleteZone($id_zone)
	{
		return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'carrier_zone`
			WHERE `id_carrier` = '.intval($this->id).'
			AND `id_zone` = '.intval($id_zone).' LIMIT 1');
	}

	/**
	 * Clean delivery prices (weight/price)
	 *
	 * @param string $rangeTable Table name to clean (weight or price according to shipping method)
	 * @return boolean Deletion result
	 */
	public function deleteDeliveryPrice($rangeTable)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` = '.intval($this->id).' AND (`id_'.$rangeTable.'` IS NOT NULL OR `id_'.$rangeTable.'` = 0)');
	}

	/**
	 * Add new delivery prices
	 *
	 * @param string $priceList Prices list separated by commas
	 * @return boolean Insertion result
	 */
	public function addDeliveryPrice($priceList)
	{
	 	if (!Validate::isValuesList($priceList))
	 		die(Tools::displayError());
		return Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'delivery` (`id_range_price`, `id_range_weight`, `id_carrier`, `id_zone`, `price`)
		VALUES '.$priceList);
	}

	/**
	 * Copy old carrier informations when update carrier
	 *
	 * @param integer $oldId Old id carrier (copy from that id)
	 */
	public function copyCarrierData($oldId)
	{
		if (!Validate::isUnsignedId($oldId))
			die(Tools::displayError());

		$oldLogo = _PS_SHIP_IMG_DIR_.'/'.intval($oldId).'.jpg';
		if (file_exists($oldLogo))
			copy($oldLogo, _PS_SHIP_IMG_DIR_.'/'.intval($this->id).'.jpg');

		// Copy existing ranges price
		$res = Db::getInstance()->ExecuteS('
		SELECT * FROM `'._DB_PREFIX_.'range_price`
		WHERE id_carrier = '.intval($oldId));
		foreach ($res as $val)
		{
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'range_price` (`id_carrier`, `delimiter1`, `delimiter2`)
			VALUES ('.$this->id.','.$val['delimiter1'].','.$val['delimiter2'].')');
			$maxRangePrice = Db::getInstance()->Insert_ID();
			$res2 = Db::getInstance()->ExecuteS('
			SELECT * FROM `'._DB_PREFIX_.'delivery`
			WHERE id_carrier = '.intval($oldId).'
			AND id_range_price = '.intval($val['id_range_price']));
			foreach ($res2 as $val2)
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'delivery` (`id_carrier`,`id_range_price`,`id_range_weight`,`id_zone`, `price`)
				VALUES ('.$this->id.','.intval($maxRangePrice).',NULL,'.$val2['id_zone'].','.$val2['price'].')');
		}
		
		// Copy existing ranges weight
		$res = Db::getInstance()->ExecuteS('
		SELECT * FROM `'._DB_PREFIX_.'range_weight`
		WHERE id_carrier = '.intval($oldId));
		foreach ($res as $val)
		{
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'range_weight` (`id_carrier`, `delimiter1`, `delimiter2`)
			VALUES ('.$this->id.','.$val['delimiter1'].','.$val['delimiter2'].')');
			$maxRangeWeight = Db::getInstance()->Insert_ID();
			$res2 = Db::getInstance()->ExecuteS('
			SELECT * FROM `'._DB_PREFIX_.'delivery`
			WHERE id_carrier = '.intval($oldId).'
			AND id_range_weight = '.$val['id_range_weight']);
			foreach ($res2 as $val2)
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'delivery` (`id_carrier`,`id_range_price`,`id_range_weight`,`id_zone`, `price`)
				VALUES ('.$this->id.',NULL,'.intval($maxRangeWeight).','.$val2['id_zone'].','.$val2['price'].')');
		}

		// Copy existing zones
		$res = Db::getInstance()->ExecuteS('
		SELECT * FROM `'._DB_PREFIX_.'carrier_zone`
		WHERE id_carrier = '.intval($oldId));
		foreach ($res as $val)
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'carrier_zone` (`id_carrier`, `id_zone`)
			VALUES ('.$this->id.','.$val['id_zone'].')');

		//Copy default carrier
		if (intval(Configuration::get('PS_CARRIER_DEFAULT')) == $oldId)
			Configuration::updateValue('PS_CARRIER_DEFAULT', intval($this->id));
	}

	/**
	 * Check if carrier is used (at least one order placed)
	 *
	 * @return integer Order count for this carrier
	 */
	public function isUsed()
	{
		$row = Db::getInstance()->getRow('
		SELECT COUNT(`id_carrier`) AS total
		FROM `'._DB_PREFIX_.'orders`
		WHERE `id_carrier` = '.intval($this->id));

		return intval($row['total']);
	}


	/**
	* Get the price without taxes defined in carrier
	**/
	public function getPriceWithoutTaxes($productPrice)
	{
		$tax = new Tax($this->id_tax);
		return round($productPrice - ($productPrice * $tax->rate / 100), 2);
	}
}

?>
