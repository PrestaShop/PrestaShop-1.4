<?php

/**
  * Discount class, Discount.php
  * Discounts management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
class		Discount extends ObjectModel
{
	public		$id;
	
	/** @var integer Customer id only if discount is reserved */
	public		$id_customer;
	
	/** @var integer Discount type ID */
	public		$id_discount_type;
	
	/** @var string Name (the one which must be entered) */
	public 		$name;
	
	/** @var string A short description for the discount */
	public 		$description;
	
	/** @var string Value in percent as well as in euros */
	public 		$value;
	
	/** @var integer Totale quantity available */
	public 		$quantity;
	
	/** @var integer User quantity available */
	public 		$quantity_per_user;
	
	/** @var boolean Indicate if discount is cumulable with others */
	public 		$cumulable;
	
	/** @var integer Indicate if discount is cumulable with already bargained products */
	public 		$cumulable_reduction;
	
	/** @var integer Date from wich discount become active */
	public 		$date_from;
	
	/** @var integer Date from wich discount is no more active */
	public 		$date_to;
	
	/** @var integer Minimum cart total amount required to use the discount */
	public 		$minimal;
	
	/** @var boolean Status */
	public 		$active = true;
	
	protected	$fieldsRequired = array('id_discount_type', 'name', 'value', 'quantity', 'quantity_per_user', 'date_from', 'date_to');
	protected	$fieldsSize = array('name' => '32', 'date_from' => '32', 'date_to' => '32');
	protected	$fieldsValidate = array('id_customer' => 'isUnsignedId', 'id_discount_type' => 'isUnsignedId',
		'name' => 'isDiscountName', 'value' => 'isPrice', 'quantity' => 'isUnsignedInt', 'quantity_per_user' => 'isUnsignedInt',
		'cumulable' => 'isBool', 'cumulable_reduction' => 'isBool', 'date_from' => 'isDate',
		'date_to' => 'isDate', 'minimal' => 'isFloat', 'active' => 'isBool');
	protected	$fieldsRequiredLang = array('description');
	protected	$fieldsSizeLang = array('description' => 128);
	protected	$fieldsValidateLang = array('description' => 'isVoucherDescription');

	protected 	$table = 'discount';
	protected 	$identifier = 'id_discount';
		
	public function getFields()
	{
		parent::validateFields();
		
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_discount_type'] = intval($this->id_discount_type);
		$fields['name'] = pSQL($this->name);
		$fields['value'] = floatval($this->value);
		$fields['quantity'] = intval($this->quantity);
		$fields['quantity_per_user'] = intval($this->quantity_per_user);
		$fields['cumulable'] = intval($this->cumulable);
		$fields['cumulable_reduction'] = intval($this->cumulable_reduction);
		$fields['date_from'] = pSQL($this->date_from);
		$fields['date_to'] = pSQL($this->date_to);
		$fields['minimal'] = floatval($this->minimal);
		$fields['active'] = intval($this->active);
		return $fields;
	}

	public function add($autodate = true, $nullValues = false, $categories = null)
	{
		if (parent::add($autodate, $nullValues))
			$ret = true;

		$this->updateCategories($categories);
		return $ret;
	}
	
	/* Categories initialization is different between add() and update() because the addition will set all categories if none are selected (compatibility with old modules) and update won't update categories if none are selected */
	public function update($autodate = true, $nullValues = false, $categories = false)
	{
		if (parent::update($autodate, $nullValues))
			$ret = true;

		$this->updateCategories($categories);
		return $ret;
	}
	
	public function delete()
	{
		if (!parent::delete())
			return false;
		return (Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart_discount WHERE id_discount = '.intval($this->id)) 
								AND Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'discount_category WHERE id_discount = '.intval($this->id)));
	}
	
	public function getTranslationsFieldsChild()
	{
		if (!parent::validateFieldsLang())
			return false;
		return parent::getTranslationsFields(array('description'));
	}
	
	/**
	  * Return discount types list
	  *
	  * @return array Discount types
	  */
	static public function getDiscountTypes($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM '._DB_PREFIX_.'discount_type dt
		LEFT JOIN `'._DB_PREFIX_.'discount_type_lang` dtl ON (dt.`id_discount_type` = dtl.`id_discount_type` AND dtl.`id_lang` = '.intval($id_lang).')');
	}
    
	/**
	  * Get discount ID from name
	  *
	  * @param string $discountName Discount name
	  * @return integer Discount ID
	  */
	public static function getIdByName($discountName)
	{
	 	if (!Validate::isDiscountName($discountName))
	 		die(Tools::displayError());
	 		
		$result = Db::getInstance()->getRow('
		SELECT `id_discount`
		FROM `'._DB_PREFIX_.'discount`
		WHERE `name` = \''.pSQL($discountName).'\'');
		return isset($result['id_discount']) ? $result['id_discount'] : false;
	}
	
	/**
	  * Return customer discounts
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $id_customer Customer ID
	  * @return array Discounts
	  */
	static public function getCustomerDiscounts($id_lang, $id_customer, $active = false, $includeGenericOnes = true, $stock = false)
    {
		global $cart;
		
    	$res = Db::getInstance()->ExecuteS('
        SELECT d.*, dtl.`name` AS `type`, dl.`description`
		FROM `'._DB_PREFIX_.'discount` d
		LEFT JOIN `'._DB_PREFIX_.'discount_lang` dl ON (d.`id_discount` = dl.`id_discount` AND dl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'discount_type` dt ON dt.`id_discount_type` = d.`id_discount_type`
		LEFT JOIN `'._DB_PREFIX_.'discount_type_lang` dtl ON (dt.`id_discount_type` = dtl.`id_discount_type` AND dtl.`id_lang` = '.intval($id_lang).')
		WHERE (`id_customer` = '.intval($id_customer).($includeGenericOnes ? ' OR `id_customer` = 0' : '').')
		'.($active ? ' AND d.`active` = 1' : '').'
		'.($stock ? ' AND d.`quantity` != 0' : ''));
		
		foreach ($res as &$discount)
			if ($discount['quantity_per_user'])
			{
				$quantity_used = Order::getDiscountsCustomer(intval($id_customer), intval($discount['id_discount']));
				if (isset($cart) AND isset($cart->id))
					$quantity_used += $cart->getDiscountsCustomer(intval($discount['id_discount']));
				$discount['quantity_for_user'] = $discount['quantity_per_user'] - $quantity_used;
			}
			else
				$discount['quantity_for_user'] = 0;
		return $res;
	}
	
	/**
	  * Return discount value
	  *
	  * @param integer $nb_discounts Number of discount currently in cart
	  * @param boolean $order_total_products Total cart products amount
	  * @return mixed Return a float value or '!' if reduction is 'Shipping free'
	  */
	function getValue($nb_discounts = 0, $order_total_products = 0, $shipping_fees = 0, $idCart = false, $useTax = true)
	{
		$totalAmount = 0;

		if (!$this->cumulable AND intval($nb_discounts) > 1)
			return 0;
		if (!$this->active)
			return 0;
		if (!$this->quantity)
			return 0;
		$date_start = strtotime($this->date_from);
		$date_end = strtotime($this->date_to);
		if (time() < $date_start OR time() > $date_end) return 0;

		$cart = new Cart(intval($idCart));
		$products = $cart->getProducts();
		$categories = Discount::getCategories(intval($this->id));
		$in_category = false;

		foreach ($products AS $product)
			if(count($categories))
				if (Product::idIsOnCategoryId($product['id_product'], $categories))
					$totalAmount += $useTax ? $product['total_wt'] : $product['total'];
		
		$totalAmount += floatval($shipping_fees);
		if ($this->minimal > 0 AND $totalAmount < $this->minimal)
			return 0;

		switch ($this->id_discount_type)
		{
			case 1:
				// % on order
				$amount = 0;
				$percentage = $this->value / 100;
				foreach ($products AS $product)
						if (Product::idIsOnCategoryId($product['id_product'], $categories))
							$amount += ($useTax ? $product['total_wt'] : $product['total']) * $percentage;
				return $amount;
			case 2:
				// amount
				foreach ($products AS $product)
						if (Product::idIsOnCategoryId($product['id_product'], $categories))
						{
							$in_category = true;
							break;
						}
				return (($in_category) ? $this->value : 0);
			case 3:
				// Shipping is free
				return '!';
		}
		return 0;
    }

  static public function isParentCategoryProductDiscount($id_category_product, $id_category_discount)
  {
		$category = new Category(intval($id_category_product));
		$parentCategories = $category->getParentsCategories();
		foreach($parentCategories AS $parentCategory)
			if($id_category_discount == $parentCategory['id_category'])
				return true;
		return false;
  }
  
  static public function getCategories($id_discount)
  {
  	return Db::getInstance()->ExecuteS('
		SELECT `id_category`
		FROM `'._DB_PREFIX_.'discount_category`
		WHERE `id_discount` = '.intval($id_discount));
  }
  
	public function updateCategories($categories)
	{
		/* false value will avoid category update and null value will force all category to be selected */
		if ($categories === false)
			return ;
		if ($categories === null)
		{
			// Compatibility for modules which create discount without setting categories (ex. fidelity, sponsorship)
			$result = Db::getInstance()->ExecuteS('SELECT id_category FROM '._DB_PREFIX_.'category');
			$categories = array();
			foreach ($result as $row)
				$categories[] = $row['id_category'];
		}
		elseif (!is_array($categories) OR !sizeof($categories))
			return false;
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount_category` WHERE `id_discount`='.intval($this->id));
		foreach($categories AS $category)
		{
			Db::getInstance()->Execute('
			SELECT `id_discount` 
			FROM `'._DB_PREFIX_.'discount_category`
			WHERE `id_discount`='.intval($this->id).' AND `id_category`='.intval($category));
			if (Db::getInstance()->NumRows() == 0)
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'discount_category` (`id_discount`, `id_category`) VALUES('.intval($this->id).','.intval($category).')');
		}
	}

	static public function discountExists($discountName, $id_discount = 0)
	{
		return Db::getInstance()->getRow('SELECT `id_discount` FROM '._DB_PREFIX_.'discount WHERE `name` LIKE \''.pSQL($discountName).'\' AND `id_discount` != '.intval($id_discount));
	}

	static public function createOrderDiscount($order, $productList, $qtyList, $name, $shipping_cost = false, $id_category = 0, $subcategory = 0)
	{
		$languages = Language::getLanguages($order);
		$products = $order->getProducts(false, $productList, $qtyList);
		$total = $order->getTotalProductsWithTaxes($products);
		if ($shipping_cost)
			$total += $order->total_shipping;

		// create discount
		$voucher = new Discount();
		$voucher->id_discount_type = 2;
		foreach ($languages as $language)
			$voucher->description[$language['id_lang']] = strval($name).intval($order->id);
		$voucher->value = floatval($total);
		$voucher->name = 'V0C'.intval($order->id_customer).'O'.intval($order->id);
		$voucher->id_customer = intval($order->id_customer);
		$voucher->quantity = 1;
		$voucher->quantity_per_user = 1;
		$voucher->cumulable = 1;
		$voucher->cumulable_reduction = 1;
		$voucher->minimal = floatval($voucher->value);
		$voucher->active = 1;
		$now = time();
		$voucher->date_from = date('Y-m-d H:i:s', $now);
		$voucher->date_to = date('Y-m-d H:i:s', $now + (60 * 60 * 24 * 184));
		if (!$voucher->validateFieldsLang(false) OR !$voucher->add())
			return false;
		// set correct name
		$voucher->name = 'V'.intval($voucher->id).'C'.intval($order->id_customer).'O'.$order->id;
		if (!$voucher->update())
			return false;
		
		return $voucher;
	}

	static public function display($discountValue, $discountType, $currency=false)
	{
		if (floatval($discountValue) AND intval($discountType))
		{
			if ($discountType == 1)
				return $discountValue.chr(37); // ASCII #37 --> % (percent)
			elseif ($discountType == 2)
				return Tools::displayPrice($discountValue, $currency);
		}
		return ''; // return a string because it's a display method
	}

}

?>
