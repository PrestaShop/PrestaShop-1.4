<?php

/**
  * OrderDetail class, OrderDetail.php
  * Orders detail management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class OrderReturn extends ObjectModel
{
	/** @var integer */
	public		$id;
	
	/** @var integer */
	public 		$id_customer;
	
	/** @var integer */
	public 		$id_order;
	
	/** @var integer */
	public 		$state;
	
	/** @var string message content */
	public		$question;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('order_return');

	protected	$fieldsRequired = array ('id_customer', 'id_order');
	protected	$fieldsValidate = array('id_customer' => 'isUnsignedId', 'id_order' => 'isUnsignedId', 'question' => 'isMessage');

	protected 	$table = 'order_return';
	protected 	$identifier = 'id_order_return';
	
	public function getFields()
	{
		parent::validateFields();

		$fields['id_customer'] = pSQL($this->id_customer);
		$fields['id_order'] = pSQL($this->id_order);
		$fields['state'] = pSQL($this->state);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['question'] = pSQL(nl2br2($this->question), true);
		return $fields;
	}
	
	public function addReturnDetail($orderDetailList, $productQtyList, $customizationIds, $customizationQtyInput)
	{
		/* Classic product return */
		if ($orderDetailList)
			foreach ($orderDetailList AS $key => $orderDetail)
				if ($qty = intval($productQtyList[$key]))
					Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_return_detail', array('id_order_return' => intval($this->id), 'id_order_detail' => intval($orderDetail), 'product_quantity' => $qty, 'id_customization' => 0), 'INSERT');
		/* Customized product return */
		if ($customizationIds)
			foreach ($customizationIds AS $orderDetailId => $customizations)
				foreach ($customizations AS $customizationId)
					if ($quantity = intval($customizationQtyInput[intval($customizationId)]))
						Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_return_detail', array('id_order_return' => intval($this->id), 'id_order_detail' => intval($orderDetailId), 'product_quantity' => $quantity, 'id_customization' => intval($customizationId)), 'INSERT');
	}
	
	public function checkEnoughProduct($orderDetailList, $productQtyList, $customizationIds, $customizationQtyInput)
	{
		$order = new Order(intval($this->id_order));
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError());
		$products = $order->getProducts();
		/* Products already returned */
		$order_return = self::getOrdersReturn($order->id_customer, $order->id, true);
		foreach ($order_return AS $or)
		{
			$order_return_products = self::getOrdersReturnProducts($or['id_order_return'], $order);
			foreach ($order_return_products AS $key => $orp)
				$products[$key]['product_quantity'] -= intval($orp['product_quantity']);
		}
		/* Quantity check */
		if ($orderDetailList)
			foreach ($orderDetailList AS $key => $orderDetail)
				if ($qty = intval($productQtyList[$key]))
					if ($products[$key]['product_quantity'] - $qty < 0)
						return false;
		/* Customization quantity check */
		if ($customizationIds)
		{
			$orderedCustomizations = Customization::getOrderedCustomizations(intval($order->id_cart));
			foreach ($customizationIds AS $productId => $customizations)
				foreach ($customizations AS $customizationId)
				{
					$customizationId = intval($customizationId);
					if (!isset($orderedCustomizations[$customizationId]))
						return false;
					$quantity = (isset($returnedCustomizations[$customizationId]) ? $returnedCustomizations[$customizationId]['product_quantity'] : 0) + (isset($customizationQtyInput[$customizationId]) ? intval($customizationQtyInput[$customizationId]) : 0);
					if (intval($orderedCustomizations[$customizationId]['quantity']) - $quantity < 0)
						return false;
				}
		}
		return true;
	}

	public function countProduct()
	{
		if (!$data = Db::getInstance()->getRow('
		SELECT COUNT(`id_order_return`) AS total
		FROM `'._DB_PREFIX_.'order_return_detail`
		WHERE `id_order_return` = '.intval($this->id)))
			return false;
		return intval($data['total']);
	}
	
	static public function getOrdersReturn($customer_id, $order_id = false, $no_denied = false)
	{
		global $cookie;
		
		$data = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_return`
		WHERE `id_customer` = '.intval($customer_id).
		($order_id ? ' AND `id_order` = '.intval($order_id) : '').
		($no_denied ? ' AND `state` != 4' : '').'
		ORDER BY `date_add` DESC');
		foreach ($data AS $k => $or)
		{
			$state = new OrderReturnState($or['state']);
			$data[$k]['state_name'] = $state->name[$cookie->id_lang];
		}
		return $data;
	}
	
	static public function getOrdersReturnDetail($id_order_return)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_return_detail`
		WHERE `id_order_return` = '.intval($id_order_return));
	}
	
	static public function getOrdersReturnProducts($orderReturnId, $order)
	{
		$productsRet = self::getOrdersReturnDetail($orderReturnId);
		$products = $order->getProducts();
		$tmp = array();
		foreach ($productsRet AS $return_detail)
		{
			$tmp[$return_detail['id_order_detail']]['quantity'] = isset($tmp[$return_detail['id_order_detail']]['quantity']) ? $tmp[$return_detail['id_order_detail']]['quantity'] + intval($return_detail['product_quantity']) : intval($return_detail['product_quantity']);
			$tmp[$return_detail['id_order_detail']]['customizations'] = intval($return_detail['id_customization']);
		}
		$resTab = array();
		foreach ($products AS $key => $product)
			if (isset($tmp[$product['id_order_detail']]))
			{
				$resTab[$key] = $product;
				$resTab[$key]['product_quantity'] = $tmp[$product['id_order_detail']]['quantity'];
				$resTab[$key]['customizations'] = $tmp[$product['id_order_detail']]['customizations'];
			}
		return $resTab;
	}

	static public function getReturnedCustomizedProducts($id_order)
	{
		$returns = Customization::getReturnedCustomizations($id_order);
		$order = new Order(intval($id_order));
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError());
		$products = $order->getProducts();
		foreach ($returns AS &$return)
		{
			$return['product_id'] = intval($products[intval($return['id_order_detail'])]['product_id']);
			$return['product_attribute_id'] = intval($products[intval($return['id_order_detail'])]['product_attribute_id']);
			$return['name'] = $products[intval($return['id_order_detail'])]['product_name'];
			$return['reference'] = $products[intval($return['id_order_detail'])]['product_reference'];
		}
		return $returns;
	}

	static public function deleteOrderReturnDetail($id_order_return, $id_order_detail, $id_customization = 0)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'order_return_detail` WHERE `id_order_detail` = '.intval($id_order_detail).' AND `id_order_return` = '.intval($id_order_return).' AND `id_customization` = '.intval($id_customization));
	}
}

?>