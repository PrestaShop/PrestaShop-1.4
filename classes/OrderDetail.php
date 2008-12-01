<?php

/**
  * OrderDetail class, OrderDetail.php
  * Orders detail management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.0
  *
  */

class OrderDetail extends ObjectModel
{
	/** @var integer */
	public $id_order_detail;
	
	/** @var integer */
	public $id_order;
	
	/** @var integer */
	public $product_id;
	
	/** @var integer */
	public $product_attribute_id;

	/** @var string */	
	public $product_name;

	/** @var string */	
	public $product_quantity;

	/** @var float */
	public $product_price;
	
	/** @var float */
	public $product_ean13;
	
	/** @var string */
	public $product_reference;
	
	/** @var string */
	public $product_supplier_reference;	
	
	/** @var float */
	public $product_weight;
	
	/** @var string */
	public $tax_name;

	/** @var float */
	public $tax_rate;
	
	/** @var float */
	public $ecotax;

	protected $tables = array ('order_detail');

	protected	$fieldsRequired = array ('id_order', 'product_name', 'product_quantity', 'product_price', 'tax_name', 'tax_rate');

	protected	$fieldsValidate = array ('id_order' => 'isUnsignedId', 'product_name' => 'isGenericName', 'product_quantity' => 'isInt', 'product_price' => 'isPrice',
	'product_reference' => 'isReference', 'product_supplier_reference' => 'isReference', 'product_weight' => 'isFloat', 'tax_name' => 'isGenericName', 'tax_rate' => 'isFloat');
	
	protected 	$table = 'order_detail';
	protected 	$identifier = 'id_order_detail';
	
	public function getFields()
	{
		parent::validateFields();

		$fields['id_order'] = intval($this->id_order);
		$fields['product_id'] = intval($this->product_id);
		$fields['product_attribute_id'] = intval($this->product_attribute_id);
		$fields['product_name'] = pSQL($this->product_name);
		$fields['product_quantity'] = intval($this->product_quantity);
		$fields['product_price'] = floatval($this->product_price);
		$fields['product_ean13'] = pSQL($this->product_ean13);
		$fields['product_reference'] = pSQL($this->product_reference);
		$fields['product_supplier_reference'] = pSQL($this->product_reference);
		$fields['product_weight'] = floatval($this->product_weight);
		$fields['tax_name'] = pSQL($this->tax_name);
		$fields['tax_rate'] = floatval($this->tax_rate);
		
		return $fields;
	}	

	static public function getDownloadFromHash($hash)
	{
		if ($hash == '') return false;
		$sql = 'SELECT *
		  FROM `'._DB_PREFIX_.'order_detail` od
		    LEFT JOIN `'._DB_PREFIX_.'product_download` pd ON (od.`product_id`=pd.`id_product`)
		  WHERE od.`download_hash` = \''.pSQL(strval($hash)).'\'';
		return Db::getInstance()->getRow($sql);
	}

	static public function incrementDownload($id_order_detail, $increment=1)
	{
		$sql = 'UPDATE `'._DB_PREFIX_.'order_detail`
			SET `download_nb` = `download_nb` + '.intval($increment).'
			WHERE `id_order_detail`= '.intval($id_order_detail).'
			LIMIT 1';
		return Db::getInstance()->Execute($sql);
	}

}

?>