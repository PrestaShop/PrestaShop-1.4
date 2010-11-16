<?php

/**
  * OrderDetail class, OrderDetail.php
  * Orders detail management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class OrderSlipCore extends ObjectModel
{
	/** @var integer */
	public		$id;
	
	/** @var integer */
	public 		$id_customer;
	
	/** @var integer */
	public 		$id_order;
	
	/** @var float */
	public		$conversion_rate;
	
	/** @var integer */
	public		$shipping_cost;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('order_slip');

	protected	$fieldsRequired = array ('id_customer', 'id_order', 'conversion_rate');
	protected	$fieldsValidate = array('id_customer' => 'isUnsignedId', 'id_order' => 'isUnsignedId', 'conversion_rate' => 'isFloat');

	protected 	$table = 'order_slip';
	protected 	$identifier = 'id_order_slip';
	
	public function getFields()
	{
		parent::validateFields();

		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_order'] = intval($this->id_order);
		$fields['conversion_rate'] = floatval($this->conversion_rate);
		$fields['shipping_cost'] = intval($this->shipping_cost);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}
	
	public function addSlipDetail($orderDetailList, $productQtyList)
	{
		foreach ($orderDetailList as $key => $orderDetail)
			if ($qty = intval($productQtyList[$key]))
				Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_slip_detail', array('id_order_slip' => intval($this->id), 'id_order_detail' => intval($orderDetail), 'product_quantity' => $qty), 'INSERT');
	}
	
	static public function getOrdersSlip($customer_id, $order_id = false)
	{
		global $cookie;
		
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_slip`
		WHERE `id_customer` = '.intval($customer_id).
		($order_id ? ' AND `id_order` = '.intval($order_id) : '').'
		ORDER BY `date_add` DESC');
	}
	
	static public function getOrdersSlipDetail($id_order_slip = true, $id_order_detail = false)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
		($id_order_detail ? 'SELECT SUM(`product_quantity`) AS `total`' : 'SELECT *').
		'FROM `'._DB_PREFIX_.'order_slip_detail`'
		.($id_order_slip ? ' WHERE `id_order_slip` = '.intval($id_order_slip) : '')
		.($id_order_detail ? ' WHERE `id_order_detail` = '.intval($id_order_detail) : ''));
	}
	
	static public function getOrdersSlipProducts($orderSlipId, $order)
	{
		$productsRet = self::getOrdersSlipDetail($orderSlipId);
		$products = $order->getProductsDetail();
		$tmp = array();
		foreach ($productsRet as $slip_detail)
			$tmp[$slip_detail['id_order_detail']] = $slip_detail['product_quantity'];
		$resTab = array();
		foreach ($products as $key => $product)
			if (isset($tmp[$product['id_order_detail']]))
			{
				$resTab[$key] = $product;
				$resTab[$key]['product_quantity'] = $tmp[$product['id_order_detail']];
			}
		return $order->getProducts($resTab);
	}
	
	static public function createOrderSlip($order, $productList, $qtyList, $shipping_cost = false)
	{
		$currency = new Currency($order->id_currency);
		$orderSlip =  new OrderSlip();
		$orderSlip->id_customer = intval($order->id_customer);
		$orderSlip->id_order = intval($order->id);
		$orderSlip->shipping_cost = intval($shipping_cost);
		$orderSlip->conversion_rate = $currency->conversion_rate;
		if (!$orderSlip->add())
			return false;

		$orderSlip->addSlipDetail($productList, $qtyList);
		return true;
	}
}

?>