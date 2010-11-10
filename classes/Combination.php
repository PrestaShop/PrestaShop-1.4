<?php

/**
  * Combination class, Combination.php
  * Product combination management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class CombinationCore extends ObjectModel
{
	public $id_product;

	public $reference;

	public $supplier_reference;

	public $location;

	public $ean13;
	
	public $upc;

	public $wholesale_price;

	public $price;

	public $ecotax;

	public $quantity;

	public $weight;

	public $default_on;

	protected	$fieldsRequired = array(
		'id_product',
	);
	protected	$fieldsSize = array(
		'reference' => 32,
		'supplier_reference' => 32,
		'location' => 64,
		'ean13' => 13,
		'upc' => 12,
		'wholesale_price' => 27,
		'price' => 20,
		'ecotax' => 20,
		'quantity' => 10
	);
	protected	$fieldsValidate = array(
		'id_product' => 'isUnsignedId',
		'location' => 'isGenericName',
		'ean13' => 'isEan13',
		'upc' => 'isUpc',
		'wholesale_price' => 'isPrice',
		'price' => 'isPrice',
		'ecotax' => 'isPrice',
		'quantity' => 'isUnsignedInt',
		'weight' => 'isFloat',
		'default_on' => 'isBool',
	);

	protected $table = 'product_attribute';
	protected $identifier = 'id_product_attribute';
	
	protected	$webserviceParameters = array(
		'objectsNodeName' => 'combinations',
		'objectNodeName' => 'combination',
		'fields' => array(
			'id_product' => array('sqlId' => 'id_product', 'required' => true, 'xlink_resource'=> 'products'),
		),
		'associations' => array(
			'product_option_values' => array('resource' => 'product_option_value'),
		),
	);

	public function getFields()
	{
		parent::validateFields();
		$fields['id_product'] = intval($this->id_product);
		$fields['reference'] = pSQL($this->reference);
		$fields['supplier_reference'] = pSQL($this->supplier_reference);
		$fields['location'] = pSQL($this->location);
		$fields['ean13'] = pSQL($this->ean13);
		$fields['upc'] = pSQL($this->upc);
		$fields['wholesale_price'] = pSQL($this->wholesale_price);
		$fields['price'] = pSQL($this->price);
		$fields['ecotax'] = pSQL($this->ecotax);
		$fields['quantity'] = intval($this->quantity);
		$fields['weight'] = pSQL($this->weight);
		$fields['default_on'] = intval($this->default_on);
		return $fields;
	}
	
	public function delete()
	{
		if (!parent::delete() OR $this->deleteAssociations() === false)
			return false;
		return true;
	}
	
	public function deleteAssociations()
	{
		if (
			Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'product_attribute_combination`
				WHERE `id_product_attribute` = '.intval($this->id)) === false
			||
			Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'product_attribute_image`
				WHERE `id_product_attribute` = '.intval($this->id)) === false
			)
			return false;
		return true;
	}
	
	public function setWsProductOptionValues($values)
	{
		$ids = array();
		foreach ($values as $value)
			$ids[] = $value['id'];
		if ($this->deleteAssociations())
		{
			if ($ids)
			{
				$sqlValues = '';
				$ids = array_map('intval', $ids);
				foreach ($ids as $position => $id)
					$sqlValues[] = '('.(int)$id.', '.(int)$this->id.', '.(int)$position.')';
				$result = Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'category_product` (`id_category`, `id_product`, `position`)
					VALUES '.implode(',', $sqlValues)
				);
				return $result;
			}
		}
		return false;
	}
	
	public function getWsProductOptionValues()
	{
		$result = Db::getInstance()->executeS('SELECT id_attribute AS id from `'._DB_PREFIX_.'product_attribute_combination` WHERE id_product_attribute = '.(int)$this->id);
		return $result;
	}

}

?>
