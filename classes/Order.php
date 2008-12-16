<?php

/**
  * Orders class, Order.php
  * Orders management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

class		Order extends ObjectModel
{
	/** @var integer Delivery address id */
	public 		$id_address_delivery;

	/** @var integer Invoice address id */
	public 		$id_address_invoice;

	/** @var integer Cart id */
	public 		$id_cart;

	/** @var integer Currency id */
	public 		$id_currency;

	/** @var integer Language id */
	public 		$id_lang;

	/** @var integer Customer id */
	public 		$id_customer;

	/** @var integer Carrier id */
	public 		$id_carrier;

	/** @var string Secure key */
	public		$secure_key;

	/** @var string Payment method id */
	public 		$payment;

	/** @var string Payment module */
	public 		$module;

	/** @var boolean Customer is ok for a recyclable package */
	public		$recyclable = 1;

	/** @var boolean True if the customer wants a gift wrapping */
	public		$gift = 0;

	/** @var string Gift message if specified */
	public 		$gift_message;

	/** @var string Shipping number */
	public 		$shipping_number;

	/** @var float Discounts total */
	public 		$total_discounts;

	/** @var float Total to pay */
	public 		$total_paid;

	/** @var float Total really paid */
	public 		$total_paid_real;

	/** @var float Products total */
	public 		$total_products;

	/** @var float Shipping total */
	public 		$total_shipping;
	
	/** @var float Wrapping total */
	public 		$total_wrapping;

	/** @var integer Invoice number */
	public 		$invoice_number;
	
	/** @var integer Delivery number */
	public 		$delivery_number;
	
	/** @var string Invoice creation date */
	public 		$invoice_date;
	
	/** @var string Delivery creation date */
	public 		$delivery_date;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('orders');

	protected	$fieldsRequired = array('id_address_delivery', 'id_address_invoice', 'id_cart', 'id_currency', 'id_lang', 'id_customer', 'id_carrier', 'payment', 'total_paid', 'total_paid_real', 'total_products');
	protected	$fieldsSize = array('payment' => 32);
	protected	$fieldsValidate = array(
		'id_address_delivery' => 'isUnsignedId',
		'id_address_invoice' => 'isUnsignedId',
		'id_cart' => 'isUnsignedId',
		'id_currency' => 'isUnsignedId',
		'id_lang' => 'isUnsignedId',
		'id_customer' => 'isUnsignedId',
		'id_carrier' => 'isUnsignedId',
		'secure_key' => 'isMd5',
		'payment' => 'isGenericName',
		'recyclable' => 'isBool',
		'gift' => 'isBool',
		'gift_message' => 'isMessage',
		'total_discounts' => 'isPrice',
		'total_paid' => 'isPrice',
		'total_paid_real' => 'isPrice',
		'total_products' => 'isPrice',
		'total_shipping' => 'isPrice',
		'total_wrapping' => 'isPrice',
		'shipping_number' => 'isUrl'
	);

	/* MySQL does not allow 'order' for a table name */
	protected 	$table = 'orders';
	protected 	$identifier = 'id_order';

	public function getFields()
	{
		parent::validateFields();

		$fields['id_address_delivery'] = intval($this->id_address_delivery);
		$fields['id_address_invoice'] = intval($this->id_address_invoice);
		$fields['id_cart'] = intval($this->id_cart);
		$fields['id_currency'] = intval($this->id_currency);
		$fields['id_lang'] = intval($this->id_lang);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_carrier'] = intval($this->id_carrier);
		$fields['secure_key'] = pSQL($this->secure_key);
		$fields['payment'] = pSQL($this->payment);
		$fields['module'] = pSQL($this->module);
		$fields['recyclable'] = intval($this->recyclable);
		$fields['gift'] = intval($this->gift);
		$fields['gift_message'] = pSQL($this->gift_message);
		$fields['shipping_number'] = pSQL($this->shipping_number);
		$fields['total_discounts'] = floatval($this->total_discounts);
		$fields['total_paid'] = floatval($this->total_paid);
		$fields['total_paid_real'] = floatval($this->total_paid_real);
		$fields['total_products'] = floatval($this->total_products);
		$fields['total_shipping'] = floatval($this->total_shipping);
		$fields['total_wrapping'] = floatval($this->total_wrapping);
		$fields['invoice_number'] = intval($this->invoice_number);
		$fields['delivery_number'] = intval($this->delivery_number);
		$fields['invoice_date'] = pSQL($this->invoice_date);
		$fields['delivery_date'] = pSQL($this->delivery_date);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);

		return $fields;
	}
	
	public function deleteProduct($order, $orderDetail, $quantity)
	{
		if (!$currentStatus = intval($this->getCurrentState()))
			return false;
		
		if ($this->hasBeenDelivered())
		{
			$orderDetail->product_quantity_return += $quantity;
			return $orderDetail->update();
		}
		elseif ($this->hasBeenPaid())
		{
			$orderDetail->product_quantity_cancelled += $quantity;
			return $orderDetail->update();
		}
		else
		{
			// Update order
			$productPrice = ($orderDetail->product_price * (1 + ($orderDetail->tax_rate * 0.01))) * $quantity;
			$order->total_paid -= $productPrice;
			$order->total_paid_real -= $productPrice;
			$order->total_products -= $productPrice;

			// Update order detail
			$orderDetail->product_quantity -= $quantity;
			
			if (!$orderDetail->product_quantity)
				return $orderDetail->delete();
			return $orderDetail->update() AND $order->update();
		}
	}

	/**
	 * Get order history
	 *
	 * @param integer $id_lang Language id
	 *
	 * @return array History entries ordered by date DESC
	 */
	public function getHistory($id_lang, $id_order_state = false)
	{
		$id_lang = $id_lang ? intval($id_lang) : 'o.`id_lang`';
		$query = '
			SELECT oh.*, e.`firstname` AS employee_firstname, e.`lastname` AS employee_lastname, osl.`name` AS ostate_name
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON o.`id_order` = oh.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'employee` e ON e.`id_employee` = oh.`id_employee`
			WHERE oh.id_order = '.intval($this->id);
		if (intval($id_order_state))
			$query.= ' AND oh.`id_order_state` = '.intval($id_order_state);
		$query.= ' ORDER BY oh.date_add DESC, oh.id_order_history DESC';
		return Db::getInstance()->ExecuteS($query);
	}

	public function getProductsDetail()
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_detail` od
		WHERE od.`id_order` = '.intval($this->id));
	}
	
	public function getLastMessage()
	{
		$sql = 'SELECT `message` FROM `'._DB_PREFIX_.'message` WHERE `id_order` = '.$this->id.' ORDER BY `id_message` desc';
		$result = Db::getInstance()->getRow($sql);
		return $result['message'];
	}

	/**
	 * Get order products
	 *
	 * @return array Products with price, quantity (with taxe and without)
	 */
	public function getProducts($products = false, $selectedProducts = false, $selectedQty = false)
	{
		if (!$products)
			$products = $this->getProductsDetail();
		$resultArray = array();
		foreach ($products AS $k => $row)
		{
			// Change qty if selected
			if ($selectedQty)
			{
				$row['product_quantity'] = 0;
				foreach ($selectedProducts AS $key => $id_product)
					if ($row['id_order_detail'] == $id_product)
						$row['product_quantity'] = intval($selectedQty[$key]);
				if (!$row['product_quantity'])
					continue ;
			}

			$row['product_price_wt'] = number_format($row['product_price'] * (1 + ($row['tax_rate'] * 0.01)), 2, '.', '');
			$row['total_wt'] = $row['product_quantity'] * $row['product_price_wt'];
			$row['total_price'] = number_format($row['total_wt'] / (1 + ($row['tax_rate'] * 0.01)), 2, '.', '');
			$row['total_wt'] = number_format($row['total_wt'], 2, '.', '');

			/* Add information for virtual product */
			if ($row['download_hash'] AND !empty($row['download_hash']))
				$row['filename'] = ProductDownload::getFilenameFromIdProduct($row['product_id']);

			/* Stock product */
			$resultArray[intval($row['id_order_detail'])] = $row;
		}
		return $resultArray;
	}

	/**
	 * Count virtual products in order
	 *
	 * @return int number of virtual products
	 */
	public function getVirtualProducts()
	{
		$sql = '
			SELECT `product_id`, `download_hash`, `download_deadline`
			FROM `'._DB_PREFIX_.'order_detail` od
			WHERE od.`id_order` = '.intval($this->id).'
				AND `download_hash` <> \'\'';
		return Db::getInstance()->ExecuteS($sql);
	}

	/**
	* Check if order contains (only) virtual products
	* @return boolean true if is a virtual order or false
	*
	*/
	public function isVirtual($strict = true)
	{
		$products = $this->getProducts();
		if (count($products) < 1)
			return false;
		$virtual = 1;
		foreach ($products AS $product)
		{
			$isVirtualProduct = Validate::isUnsignedInt(ProductDownload::getIdFromIdProduct(intval($product['product_id'])));
			if ($strict === false AND $isVirtualProduct)
				return true;
			$virtual &= ($isVirtualProduct ? true : false);
		}
		return((bool) $virtual);
	}


	/**
	 * Get order discounts
	 *
	 * @return array Discounts with price and quantity
	 */
	public function getDiscounts()
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_discount`
		WHERE `id_order` = '.intval($this->id));
	}


	static public function getDiscountsCustomer($id_customer, $id_discount)
	{
		$result = Db::getInstance()->ExecuteS('
				SELECT od.id_discount FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN '._DB_PREFIX_.'order_discount od ON (od.id_order = o.id_order)
		WHERE o.id_customer = '.intval($id_customer).'
		AND od.id_discount = '.intval($id_discount));

		return Db::getInstance()->NumRows();
	}

	/**
	 * Get current order state (eg. Awaiting payment, Delivered...)
	 *
	 * @return array Order state details
	 */
	public function getCurrentState()
	{
		$orderHistory = OrderHistory::getLastOrderState($this->id);
		if (!isset($orderHistory) OR !$orderHistory)
			return false;
		return $orderHistory->id;
	}

	/**
	 * Get current order state name (eg. Awaiting payment, Delivered...)
	 *
	 * @return array Order state details
	 */
	public function getCurrentStateFull($id_lang)
	{
		return Db::getInstance()->getRow('
		SELECT oh.`id_order_state`, osl.`name`, os.`logable`
		FROM `'._DB_PREFIX_.'order_history` oh
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = oh.`id_order_state`)
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
		WHERE osl.`id_lang` = '.intval($id_lang).' AND oh.`id_order` = '.intval($this->id).'
		ORDER BY `date_add` DESC, `id_order_history` DESC');
	}


	public function isLogable()
	{
		$result = Db::getInstance()->getRow('
		SELECT oh.`id_order_state`, os.`logable`
		FROM `'._DB_PREFIX_.'order_history` oh
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
		WHERE oh.`id_order` = '.intval($this->id).'
		ORDER BY oh.`date_add` DESC');

		return $result ? intval($result['logable']) : false;
	}
	
	public function hasBeenDelivered()
	{
		return sizeof($this->getHistory(Configuration::get('PS_DEFAULT_LANG'), _PS_OS_DELIVERED_));
	}
	
	public function hasBeenPaid()
	{
		return sizeof($this->getHistory(Configuration::get('PS_DEFAULT_LANG'), _PS_OS_PAYMENT_));
	}
	
	/**
	 * Get customer orders
	 *
	 * @param integer $id_customer Customer id
	 * @return array Customer orders
	 */
	static public function getCustomerOrders($id_customer)
    {
    	$res = Db::getInstance()->ExecuteS('
        SELECT o.*, (
				SELECT SUM(od.`product_quantity`)
				FROM `'._DB_PREFIX_.'order_detail` od
				WHERE od.`id_order` = o.`id_order`)
				AS nb_products
        FROM `'._DB_PREFIX_.'orders` o
        LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order`)
        WHERE o.`id_customer` = '.intval($id_customer).'
        GROUP BY o.`id_order`
        ORDER BY o.`date_add` DESC');
		if (!$res)
			return false;

		foreach ($res AS $key => $val)
		{
			$res2 = Db::getInstance()->ExecuteS('
				SELECT os.`id_order_state`, osl.`name` AS order_state, os.`invoice`
				FROM `'._DB_PREFIX_.'order_history` oh
				LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
				LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.intval($val['id_lang']).')
				WHERE oh.`id_order_history` = (
						SELECT MAX(`id_order_history`)
						FROM `'._DB_PREFIX_.'order_history` moh
						WHERE moh.`id_order` = '.intval($val['id_order']).'
						GROUP BY moh.`id_order`)
				ORDER BY oh.`date_add` DESC
			');
			if ($res2)
				$res[$key] = array_merge($res[$key], $res2[0]);
		}
		return $res;
    }

	static public function getOrdersIdByDate($date_from, $date_to, $id_customer = NULL, $type = NULL)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_order`
		FROM `'._DB_PREFIX_.'orders`
		WHERE DATE_ADD(date_add, INTERVAL -1 DAY) <= \''.$date_to.'\' AND date_add >= \''.$date_from.'\''
		.($type ? ' AND '.strval($type).'_number != 0' : '')
		.($id_customer ? ' AND id_customer = '.intval($id_customer) : ''));

		$orders = array();
		foreach ($result AS $order)
			$orders[] = intval($order['id_order']);
		return $orders;
	}

    /**
     * Get product total with taxes
     *
     * @return Product total with taxes
     */
    public function getTotalProductsWithTaxes($products = false)
	{
		if (!$products)
			$products = $this->getProductsDetail();

        $total = 0;
		foreach ($products AS $k => $row)
		{
			$qty = intval($row['product_quantity']);
			$total += round(floatval($row['product_price']) * (floatval($row['tax_rate']) * 0.01 + 1), 2) * $qty;
		}
		return $total;
	}

	/**
	 * Get customer orders number
	 *
	 * @param integer $id_customer Customer id
	 * @return array Customer orders number
	 */
	static public function getCustomerNbOrders($id_customer)
    {
    	$result = Db::getInstance()->getRow('
        SELECT COUNT(`id_order`) AS nb
        FROM `'._DB_PREFIX_.'orders`
        WHERE `id_customer` = '.intval($id_customer));

		return isset($result['nb']) ? $result['nb'] : 0;
    }

	/**
	 * Get an order by its cart id
	 *
	 * @param integer $id_cart Cart id
	 * @return array Order details
	 */
	static public function getOrderByCartId($id_cart)
    {
    	$result = Db::getInstance()->getRow('
        SELECT `id_order`
        FROM `'._DB_PREFIX_.'orders`
        WHERE `id_cart` = '.intval($id_cart));

		return isset($result['id_order']) ? $result['id_order'] : false;
    }

    /**
	 * Add a discount to order
	 *
	 * @param integer $id_discount Discount id
	 * @param string $name Discount name
	 * @param float $value Discount value
	 * @return boolean Query sucess or not
	 */
	public function	addDiscount($id_discount, $name, $value)
	{
		return Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_discount', array('id_order' => intval($this->id), 'id_discount' => intval($id_discount), 'name' => pSQL($name), 'value' => floatval($value)), 'INSERT');
	}

	/**
	 * Get orders number last week
	 *
	 * @return integer Orders number last week
	 */
	public static function getWeeklyOrders()
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(`id_order`) as nb
		FROM `'._DB_PREFIX_.'orders`
		WHERE YEARWEEK(`date_add`) = YEARWEEK(NOW())');

		return isset($result['nb']) ? $result['nb'] : 0;
	}

	/**
	 * Get sales amount last month
	 *
	 * @return float Sales amount last month
	 */
	public static function getMonthlySales()
	{
		$result = Db::getInstance()->getRow('
		SELECT SUM(`total_paid`) as nb
		FROM `'._DB_PREFIX_.'orders`
		WHERE MONTH(`date_add`) = MONTH(NOW())
		AND YEAR(`date_add`) = YEAR(NOW())');

		return isset($result['nb']) ? $result['nb'] : 0;
	}

	public function getNumberOfDays()
	{
		$nbReturnDays = intval(Configuration::get('PS_ORDER_RETURN_NB_DAYS'));
		if (!$nbReturnDays)
			return true;
		$result = Db::getInstance()->getRow('
		SELECT TO_DAYS(NOW()) - TO_DAYS(`date_add`)  AS days FROM `'._DB_PREFIX_.'orders`
		WHERE `id_order` = '.$this->id);
		if ($result['days'] <= $nbReturnDays)
			return true;
		return false;
	}


	public function isReturnable()
	{
		return (intval(Configuration::get('PS_ORDER_RETURN')) == 1 AND intval($this->getCurrentState()) == _PS_OS_DELIVERED_ AND $this->getNumberOfDays());
	}

	public function setInvoice()
	{
		// Set invoice number
		$this->invoice_number = intval(Configuration::get('PS_INVOICE_NUMBER'));
		Configuration::updateValue('PS_INVOICE_NUMBER', $this->invoice_number + 1);
		if (!intval($this->invoice_number))
			die(Tools::displayError('Invalid invoice number'));
		
		// Set invoice date
		$this->invoice_date = date('Y-m-d H:i:s');
		
		// Update object
		$this->update();
	}
	
	public function setDelivery()
	{
		// Set delivery number
		$this->delivery_number = intval(Configuration::get('PS_DELIVERY_NUMBER'));
		Configuration::updateValue('PS_DELIVERY_NUMBER', $this->delivery_number + 1);
		if (!intval($this->delivery_number))
			die(Tools::displayError('Invalid delivery number'));
		
		// Set delivery date
		$this->delivery_date = date('Y-m-d H:i:s');
		
		// Update object
		$this->update();
	}
	
	static public function  printPDFIcons($id_order)
	{
		$order = new Order($id_order);
		$orderState = OrderHistory::getLastOrderState($id_order);
		if (!Validate::isLoadedObject($orderState) OR !Validate::isLoadedObject($order))
			die(Tools::displayError('Invalid objects!'));
		echo '<span style="width:20px; margin-right:5px;">';
		if ($orderState->invoice OR $order->invoice_number)
			echo '<a href="pdf.php?id_order='.intval($order->id).'&pdf"><img src="../img/admin/tab-invoice.gif" alt="invoice" /></a>';
		else
			echo '&nbsp;';
		echo '</span>';
		echo '<span style="width:20px;">';
		if ($orderState->delivery OR $order->delivery_number)
			echo '<a href="pdf.php?id_delivery='.intval($order->delivery_number).'"><img src="../img/admin/delivery.gif" alt="delivery" /></a>';
		else
			echo '&nbsp;';
		echo '</span>';
	}
	
	static public function getByDelivery($id_delivery)
	{
	    $res = Db::getInstance()->getRow('
        SELECT id_order
        FROM `'._DB_PREFIX_.'orders`
        WHERE `delivery_number` = '.intval($id_delivery));
		return new Order(intval($res['id_order']));
	}
	
	public function getTotalWeight()
	{
		$result = Db::getInstance()->getRow('
		SELECT SUM(product_weight*product_quantity) as weight
		FROM '._DB_PREFIX_.'order_detail
		WHERE id_order = '.intval($this->id));
		return $result['weight'];
	}
}

?>
