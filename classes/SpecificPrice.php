<?php

/**
  * Specific prices class, SpecificPrice.php
  * Specific prices management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class SpecificPriceCore extends ObjectModel
{
	public	$id_product;
	public	$id_shop;
	public	$id_currency;
	public	$id_country;
	public	$id_group;
	public	$priority;
	public	$price;
	public	$from_quantity;
	public	$reduction;
	public	$reduction_type;
	public	$from;
	public	$to;

 	protected 	$fieldsRequired = array('id_product', 'id_shop', 'id_currency', 'id_country', 'id_group', 'price', 'from_quantity', 'reduction', 'reduction_type', 'from', 'to');
 	protected 	$fieldsValidate = array('id_product' => 'isUnsignedId', 'id_shop' => 'isUnsignedId', 'id_country' => 'isUnsignedId', 'id_group' => 'isUnsignedId', 'priority' => 'isUnsignedInt', 'price' => 'isPrice', 'from_quantity' => 'isUnsignedInt', 'reduction' => 'isPrice', 'reduction_type' => 'isReductionType', 'from' => 'isDateFormat', 'to' => 'isDateFormat');

	protected 	$table = 'specific_price';
	protected 	$identifier = 'id_specific_price';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_product'] = (int)($this->id_product);
		$fields['id_shop'] = (int)($this->id_shop);
		$fields['id_currency'] = (int)($this->id_currency);
		$fields['id_country'] = (int)($this->id_country);
		$fields['id_group'] = (int)($this->id_group);
		$fields['priority'] = (int)($this->priority);
		$fields['price'] = floatval($this->price);
		$fields['from_quantity'] = (int)($this->from_quantity);
		$fields['reduction'] = floatval($this->reduction);
		$fields['reduction_type'] = pSQL($this->reduction_type);
		$fields['from'] = pSQL($this->from);
		$fields['to'] = pSQL($this->to);
		return $fields;
	}

	public function add($autodate = true, $nullValues = false)
	{
		$maxPriority = (int)(DB::getInstance()->getValue('SELECT MAX(`priority`) FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` = '.(int)($this->id_product)));
		$this->priority = $maxPriority == 0 ? 0 : $maxPriority + 1;
		return parent::add($autodate, $nullValues);
	}

	static public function getByProductId($id_product)
	{
		return Db::getInstance()->ExecuteS('
			SELECT * FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` = '.(int)($id_product).' ORDER BY `priority`
		');
	}

	static public function getIdsByProductId($id_product)
	{
		return Db::getInstance()->ExecuteS('
			SELECT `id_specific_price` FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` = '.(int)($id_product).'
		');
	}

	static public function getSpecificPrice($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity)
	{
		$now = date('Y-m-d H:i:s');
		return Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_product` IN(0, '.(int)($id_product).') AND
					`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					`from_quantity` <= '.(int)($quantity).' AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$now.'\' >= `from` AND \''.$now.'\' <= `to`))
			ORDER BY `priority`, '.self::getPriorities().', `from_quantity` DESC
		');
	}

	static public function setPriorities($priorities)
	{
		$value = '';
		foreach ($priorities as $priority)
			$value .= pSQL($priority).';';
		return Configuration::updateValue('PS_SPECIFIC_PRICE_PRIORITIES', rtrim($value, ';')) AND DB::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'specific_price` SET `priority` = 0');
	}

	static public function getPriorities()
	{
		$data = Configuration::get('PS_SPECIFIC_PRICE_PRIORITIES');
		return '`'.str_replace(';', '`, `', $data).'`';
	}

	static public function setSpecificPriorities($id_product, $priorities)
	{
		$fields = '';
		foreach ($priorities as $priority)
			$fields .= '`'.pSQL($priority).'` DESC, ';
		$result = DB::getInstance()->ExecuteS('SELECT `id_specific_price` FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` = '.(int)($id_product).' ORDER BY '.rtrim($fields, ', DESC'));
		$position = 0;
		foreach ($result AS $row)
			if (!DB::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'specific_price` SET `priority` = '.++$position.' WHERE `id_specific_price` = '.(int)($row['id_specific_price'])))
				return false;
		return true;
	}

	static public function getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group)
	{
		$now = date('Y-m-d H:i:s');
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_product` IN(0, '.(int)($id_product).') AND
					`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					`from_quantity` > 1 AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$now.'\' >= `from` AND \''.$now.'\' <= `to`))
					ORDER BY `priority`, '.self::getPriorities().', `from_quantity` DESC
		');
	}

	static public function getQuantityDiscount($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity)
	{
		$now = date('Y-m-d H:i:s');
		return Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_product` IN(0, '.(int)($id_product).') AND
					`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					`from_quantity` >= '.(int)($quantity).' AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$now.'\' >= `from` AND \''.$now.'\' <= `to`))
					ORDER BY `priority`, '.self::getPriorities().', `from_quantity` DESC
		');
	}

	static public function getProductIdByDate($id_shop, $id_currency, $id_country, $id_group, $beginning, $ending)
	{
		$resource = Db::getInstance()->ExecuteS('
			SELECT `id_product`
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					`from_quantity` = 1 AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$beginning.'\' >= `from` AND \''.$ending.'\' <= `to`)) AND
					`reduction` > 0
		', false);
		$ids_product = array();
		while ($row = DB::getInstance()->nextRow($resource))
			$ids_product[] = (int)($row['id_product']);
		return $ids_product;
	}

	static public function deleteByProductId($id_product)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` = '.(int)($id_product));
	}

	public function duplicate($id_product = false)
	{
		if ($id_product)
			$this->id_product = (int)($id_product);
		return $this->add();
	}
}

?>
