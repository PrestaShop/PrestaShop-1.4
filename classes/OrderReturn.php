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
	
	public function addReturnDetail($orderDetailList, $productQtyList)
	{
		foreach ($orderDetailList as $key => $orderDetail)
			if ($qty = intval($productQtyList[$key]))
				Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_return_detail', array('id_order_return' => intval($this->id), 'id_order_detail' => intval($orderDetail), 'product_quantity' => $qty), 'INSERT');
	}
	
	public function checkEnoughProduct($orderDetailList, $productQtyList)
	{
		$order = new Order(intval($this->id_order));
		$products = $order->getProducts();
		$order_return = self::getOrdersReturn($order->id_customer, $order->id, true);
		foreach ($order_return as $or)
		{
			$order_return_products = self::getOrdersReturnProducts($or['id_order_return'], $order);
			foreach ($order_return_products as $key => $orp)
				$products[$key]['product_quantity'] -= $orp['product_quantity'];
		}
		foreach ($orderDetailList as $key => $orderDetail)
			if ($qty = intval($productQtyList[$key]))
				if ($products[$key]['product_quantity'] - $qty < 0)
					return false;
		return true;
	}
	
	public function countProduct()
	{
		$data = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_return_detail`
		WHERE `id_order_return` = '.intval($this->id));
		return $data;
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
		foreach ($data as $k => $or)
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
		foreach ($productsRet as $return_detail)
			$tmp[$return_detail['id_order_detail']] = $return_detail['product_quantity'];
		$resTab = array();
		foreach ($products as $key => $product)
			if (isset($tmp[$product['id_order_detail']]))
			{
				$resTab[$key] = $product;
				$resTab[$key]['product_quantity'] = $tmp[$product['id_order_detail']];;
			}
		return $resTab;
	}
}

?>