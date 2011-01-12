<?php
/*
* 2007-2010 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
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
		$fields['price'] = (float)($this->price);
		$fields['from_quantity'] = (int)($this->from_quantity);
		$fields['reduction'] = (float)($this->reduction);
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
			SELECT *,
					(IF (`id_shop` = '.(int)($id_shop).', 1, 0) +
					IF (`id_currency` = '.(int)($id_currency).', 1, 0) +
					IF (`id_country` = '.(int)($id_country).', 1, 0) +
					IF (`id_group` = '.(int)($id_group).', 1, 0)) as `score`
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_product` IN(0, '.(int)($id_product).') AND
					`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					`from_quantity` <= '.(int)($quantity).' AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$now.'\' >= `from` AND \''.$now.'\' <= `to`))
			ORDER BY `score` DESC, '.self::getPriorities().', `from_quantity` DESC
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
		$res =  Db::getInstance()->ExecuteS('
			SELECT *,
					(IF (`id_shop` = '.(int)($id_shop).', 1, 0) +
					IF (`id_currency` = '.(int)($id_currency).', 1, 0) +
					IF (`id_country` = '.(int)($id_country).', 1, 0) +
					IF (`id_group` = '.(int)($id_group).', 1, 0)) as `score`
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_product` IN(0, '.(int)($id_product).') AND
					`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$now.'\' >= `from` AND \''.$now.'\' <= `to`))
					ORDER BY `score`  DESC, '.self::getPriorities().', `from_quantity` DESC
		');

		$targeted_prices = array();
		$max_score = NULL;

		foreach($res as $specific_price)
		{
		    if (!isset($max_score))
		        $max_score = $specific_price['score'];
		    else if ($max_score != $specific_price['score'])
		        break;

            if ($specific_price['from_quantity'] > 1)
    		    $targeted_prices[] = $specific_price;
		}

		return $targeted_prices;
	}

	static public function getQuantityDiscount($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity)
	{
		$now = date('Y-m-d H:i:s');
		return Db::getInstance()->getRow('
			SELECT *,
					(IF (`id_shop` = '.(int)($id_shop).', 1, 0) +
					IF (`id_currency` = '.(int)($id_currency).', 1, 0) +
					IF (`id_country` = '.(int)($id_country).', 1, 0) +
					IF (`id_group` = '.(int)($id_group).', 1, 0)) as `score`
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE	`id_product` IN(0, '.(int)($id_product).') AND
					`id_shop` IN(0, '.(int)($id_shop).') AND
					`id_currency` IN(0, '.(int)($id_currency).') AND
					`id_country` IN(0, '.(int)($id_country).') AND
					`id_group` IN(0, '.(int)($id_group).') AND
					`from_quantity` >= '.(int)($quantity).' AND
					(`from` = \'0000-00-00 00:00:00\' OR (\''.$now.'\' >= `from` AND \''.$now.'\' <= `to`))
					ORDER BY `score` DESC, '.self::getPriorities().', `from_quantity` DESC
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

