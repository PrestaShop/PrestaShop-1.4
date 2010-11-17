<?php

class StockMvtCore extends ObjectModel
{
	public		$id;

	public		$id_product;
	public		$id_product_attribute = NULL;
	public 		$id_order = NULL;
	public 		$id_employee = NULL;
	public 		$quantity;
	public 		$id_stock_mvt_reason;
	
	public		$date_add;
	public		$date_upd;
	
	protected	$table = 'stock_mvt';
	protected 	$identifier = 'id_stock_mvt';
	

 	protected 	$fieldsRequired = array('id_product', 'id_stock_mvt_reason', 'quantity');
 	protected 	$fieldsValidate = array('id_product' => 'isUnsignedId', 'id_product_attribute' => 'isUnsignedId','id_order' => 'isUnsignedId','id_employee' => 'isUnsignedId',
 													'quantity' => 'isInt', 'id_stock_mvt_reason' => 'isUnsignedId');
	
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_product'] = intval($this->id_product);
		$fields['id_product_attribute'] = intval($this->id_product_attribute);
		$fields['id_order'] = intval($this->id_order);
		$fields['id_employee'] = intval($this->id_employee);
		$fields['id_stock_mvt_reason'] = intval($this->id_stock_mvt_reason);
		$fields['quantity'] = intval($this->quantity);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false, $update_quantity = true)
	{
		if (!parent::add($autodate, $nullValues))
			return false;
		if (!$update_quantity)
			return true;

		if ($this->id_product_attribute)
		{
			$product = new Product(intval($this->id_product), false, Configuration::get('PS_LANG_DEFAULT'));
			return (Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product_attribute SET quantity=quantity+'.(int)$this->quantity.'
															WHERE id_product='.(int)$product->id.' AND id_product_attribute='.(int)$this->id_product_attribute) AND $product->updateQuantityProductWithAttributeQuantity());
		}
		else
			return Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'product SET quantity=quantity+'.(int)$this->quantity.' WHERE id_product='.(int)$this->id_product);
	}
	
	public static function addMissingMvt($id_employee)
	{
		$products_without_attributes = Db::getInstance()->ExecuteS('SELECT p.id_product, pa.id_product_attribute, (p.quantity - SUM(IFNULL(sm.quantity, 0))) quantity
																						FROM '._DB_PREFIX_.'product p
																						LEFT JOIN '._DB_PREFIX_.'stock_mvt sm ON (sm.id_product = p.id_product)
																						LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product)
																						WHERE pa.id_product_attribute IS NULL
																						GROUP BY p.id_product');
		
		$products_with_attributes = Db::getInstance()->ExecuteS('SELECT p.id_product, pa.id_product_attribute, SUM(pa.quantity) - SUM(IFNULL(sm.quantity, 0)) quantity
																						FROM '._DB_PREFIX_.'product p
																						LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product)
																						LEFT JOIN '._DB_PREFIX_.'stock_mvt sm ON (sm.id_product = pa.id_product AND sm.id_product_attribute = pa.id_product_attribute)
																						WHERE pa.id_product_attribute IS NOT NULL
																						GROUP BY pa.id_product_attribute');

		

		$products = array_merge($products_without_attributes, $products_with_attributes);
		if ($products)
		{
			foreach ($products AS $product)
			{
				if (!$product['quantity'])
					continue;
				$mvt = new self();
				foreach ($product AS $k => $row)
					$mvt->{$k} = $row;
				$mvt->id_employee = (int)$id_employee;
				$mvt->id_stock_mvt_reason = _STOCK_MOVEMENT_MISSING_REASON_;
				$mvt->add(true, false, false);
			}
		}
	}	
}
