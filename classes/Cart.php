<?php

/**
  * Cart class, Cart.php
  * Carts management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Cart extends ObjectModel
{
	public		$id;

	/** @var integer Customer delivery address ID */
	public 		$id_address_delivery;

	/** @var integer Customer invoicing address ID */
	public 		$id_address_invoice;

	/** @var integer Customer currency ID */
	public 		$id_currency;

	/** @var integer Customer ID */
	public 		$id_customer;

	/** @var integer Guest ID */
	public 		$id_guest;

	/** @var integer Language ID */
	public 		$id_lang;

	/** @var integer Carrier ID */
	public 		$id_carrier;

	/** @var boolean True if the customer wants a recycled package */
	public		$recyclable = 1;

	/** @var boolean True if the customer wants a gift wrapping */
	public		$gift = 0;

	/** @var string Gift message if specified */
	public 		$gift_message;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	private static $_nbProducts = -1;
	
	protected	$fieldsRequired = array('id_currency', 'id_lang');
	protected	$fieldsValidate = array('id_address_delivery' => 'isUnsignedId', 'id_address_invoice' => 'isUnsignedId',
		'id_currency' => 'isUnsignedId', 'id_customer' => 'isUnsignedId', 'id_guest' => 'isUnsignedId', 'id_lang' => 'isUnsignedId',
		'id_carrier' => 'isUnsignedId', 'recyclable' => 'isBool', 'gift' => 'isBool', 'gift_message' => 'isMessage');

	private		$_nb_products = NULL;
	private		$_products = NULL;
	private		$_totalWeight = NULL;
	private	static $_discounts = NULL;
	private	static $_discountsLite = NULL;
	private	static $_carriers = NULL;
	private	static $_taxes = NULL;
	protected 	$table = 'cart';
	protected 	$identifier = 'id_cart';

	public function getFields()
	{
		parent::validateFields();

		$fields['id_address_delivery'] = intval($this->id_address_delivery);
		$fields['id_address_invoice'] = intval($this->id_address_invoice);
		$fields['id_currency'] = intval($this->id_currency);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_guest'] = intval($this->id_guest);
		$fields['id_lang'] = intval($this->id_lang);
		$fields['id_carrier'] = intval($this->id_carrier);
		$fields['recyclable'] = intval($this->recyclable);
		$fields['gift'] = intval($this->gift);
		$fields['gift_message'] = pSQL($this->gift_message);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);

		return $fields;
	}

	public function add($autodate = true, $nullValues = false)
	{
		$return = parent::add($autodate);
		Module::hookExec('cart');
		return $return;
	}

	public function update($nullValues = false)
	{
		self::$_nbProducts = 0;
		$return = parent::update();
		Module::hookExec('cart');
		return $return;
	}

	public function delete()
	{
		if (!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_discount` WHERE `id_cart` = '.intval($this->id)) OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.intval($this->id)))
			return false;
		return parent::delete();
	}

	/**
	 * Return cart discounts
	 *
	 * @result array Discounts
	 */
	public function getDiscounts($lite = false, $refresh = false)
	{
		if (!$this->id)
			return array();
		if (!$lite AND !$refresh AND isset(self::$_discounts[$this->id]))
			return self::$_discounts[$this->id];
		if ($lite AND isset(self::$_discountsLite[$this->id]))
			return self::$_discountsLite[$this->id];

		$result = Db::getInstance()->ExecuteS('
		SELECT d.*, `id_cart`
		FROM `'._DB_PREFIX_.'cart_discount` c
		LEFT JOIN `'._DB_PREFIX_.'discount` d ON c.`id_discount` = d.`id_discount`
		WHERE `id_cart` = '.intval($this->id));

		$products = $this->getProducts();
		foreach ($result AS $k=>$discount)
		{
			$categories = Discount::getCategories($discount['id_discount']);
			$in_category = false;
			foreach ($products AS $product)
					if (Product::idIsOnCategoryId(intval($product['id_product']), $categories))
					{
						$in_category = true;
						break;
					}
			if (!$in_category)
				unset($result[$k]);
		}

		if ($lite)
		{
			self::$_discountsLite[$this->id] = $result;
			return $result;
		}

		$total_products_wt = $this->getOrderTotal(true, 1);
		$total_products = $this->getOrderTotal(false, 1);
		$shipping_wt = $this->getOrderShippingCost();
		$shipping = $this->getOrderShippingCost(NULL, false);
		self::$_discounts[$this->id] = array();
		foreach ($result as $row)
		{
			$discount = new Discount($row['id_discount'], intval($this->id_lang));
			$row['description'] = $discount->description ? $discount->description : $discount->name;
			$row['value_real'] = $discount->getValue(sizeof($result), $total_products_wt, $shipping_wt, $this->id);
			$row['value_tax_exc'] = $discount->getValue(sizeof($result), $total_products, $shipping, $this->id, false);
			self::$_discounts[$this->id][] = $row;
		}

		return isset(self::$_discounts[$this->id]) ? self::$_discounts[$this->id] : NULL;
	}

	public function getDiscountsCustomer($id_discount)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_discount`
		FROM `'._DB_PREFIX_.'cart_discount`
		WHERE `id_discount` = '.intval($id_discount).' AND `id_cart` = '.intval($this->id));

		return Db::getInstance()->NumRows();
	}
	
	public function getLastProduct()
	{
		$sql = '
			SELECT `id_product`, `id_product_attribute`
			FROM `'._DB_PREFIX_.'cart_product`
			WHERE `id_cart` = '.intval($this->id).'
			ORDER BY `date_add` DESC';
		$result = Db::getInstance()->GetRow($sql);
		if ($result AND isset($result['id_product']) AND $result['id_product'])
			return $result;
		return false;
	}

	/**
	 * Return cart products
	 *
	 * @result array Products
	 */
	public function getProducts($refresh = false, $id_product = false)
	{
		if (!$this->id)
			return array();
		if ($this->_products AND !$refresh)
			return $this->_products;
		$sql = '
		SELECT cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, pl.`name`,
		pl.`description_short`, pl.`available_now`, pl.`available_later`, p.`id_product`, p.`id_category_default`, p.`id_supplier`, p.`id_manufacturer`, p.`id_tax`, p.`on_sale`, p.`ecotax`,
		p.`quantity`, p.`price`, p.`reduction_price`, p.`reduction_percent`, p.`reduction_from`, p.`reduction_to`, p.`weight`, p.`out_of_stock`, p.`active`, p.`date_add`, p.`date_upd`,
		t.`id_tax`, tl.`name` AS tax, t.`rate`, pa.`price` AS price_attribute, pa.`quantity` AS quantity_attribute, 
        pa.`ecotax` AS ecotax_attr, i.`id_image`, il.`legend`, pl.`link_rewrite`, cl.`link_rewrite` AS category, CONCAT(cp.`id_product`, cp.`id_product_attribute`) AS unique_id,
        IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference, 
        IF (IFNULL(pa.`supplier_reference`, \'\') = \'\', p.`supplier_reference`, pa.`supplier_reference`) AS supplier_reference, 
        IF (IFNULL(pa.`weight`, 0) = \'\', p.`weight`, pa.`weight`) AS weight_attribute,
        IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13
		FROM `'._DB_PREFIX_.'cart_product` cp
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($this->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product_attribute` = cp.`id_product_attribute`)
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($this->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_image` pai ON (pai.`id_product_attribute` = cp.`id_product_attribute`)
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = cp.`id_product` AND (IF(pai.`id_image`, pai.`id_image` = i.`id_image`, i.`cover` = 1)))
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($this->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.intval($this->id_lang).')
		WHERE `id_cart` = '.intval($this->id).'
		'.($id_product ? ' AND cp.`id_product` = '.intval($id_product) : '').'
		AND p.`id_product` IS NOT NULL
		GROUP BY unique_id
		ORDER BY cp.date_add ASC';
		$result = Db::getInstance()->ExecuteS($sql);

		/* Modify SQL results */
		$products = array();

		if (empty($result))
			return array();
		foreach ($result AS $k => $row)
		{
			if (isset($row['ecotax_attr']) AND $row['ecotax_attr'] > 0)
				$row['ecotax'] = floatval($row['ecotax_attr']);
			$row['stock_quantity'] = intval($row['quantity']);
			$row['weight'] = $row['weight_attribute'];
			$row['quantity'] = intval($row['cart_quantity']);
			$row['price'] = Product::getPriceStatic(intval($row['id_product']), false, isset($row['id_product_attribute']) ? intval($row['id_product_attribute']) : NULL, 6, NULL, false, true, intval($row['quantity']));
			$row['price_wt'] = Product::getPriceStatic(intval($row['id_product']), true, isset($row['id_product_attribute']) ? intval($row['id_product_attribute']) : NULL, 6, NULL, false, true, intval($row['quantity']));
			$row['total'] = $row['price'] * intval($row['quantity']);
			$row['total_wt'] = $row['price_wt'] * intval($row['quantity']);
			$row['id_image'] = Product::defineProductImage($row);
			$row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
			$row['features'] = Product::getFeaturesStatic(intval($row['id_product']));

			/* Add attributes to the SQL result if needed */
			if (isset($row['id_product_attribute']) AND intval($row['id_product_attribute']))
			{
				$result2 = Db::getInstance()->ExecuteS('
				SELECT agl.`public_name` AS public_group_name, al.`name` AS attribute_name
				FROM `'._DB_PREFIX_.'product_attribute_combination` pac
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.intval($this->id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.intval($this->id_lang).')
				WHERE pac.`id_product_attribute` = '.intval($row['id_product_attribute']));

				$attributesList = '';
				$attributesListSmall = '';
				if ($result2)
					foreach ($result2 AS $k2 => $row2)
					{
						$attributesList .= $row2['public_group_name'].' : '.$row2['attribute_name'].', ';
						$attributesListSmall .= $row2['attribute_name'].', ';
					}
				$attributesList = rtrim($attributesList, ', ');
				$attributesListSmall = rtrim($attributesListSmall, ', ');
				$row['attributes'] = $attributesList;
				$row['attributes_small'] = $attributesListSmall;
				$row['stock_quantity'] = $row['quantity_attribute'];
			}
			$products[] = $row;
		}
		$this->_products = $products;
		return $this->_products;
	}

	/**
	 * Return cart products quantity
	 *
	 * @result integer Products quantity
	 */
	public	function nbProducts()
	{
		if (!$this->id)
			return 0;
		if (!$this->_nb_products)
		{
			$row = Db::getInstance()->getRow('SELECT SUM(`quantity`) AS nb FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.intval($this->id));
			$this->_nb_products = intval($row['nb']);
		}
		return $this->_nb_products;
	}

	public static function getNbProducts($id)
	{
		if (self::$_nbProducts > 0)
			return self::$_nbProducts;
		$row = Db::getInstance()->getRow('SELECT SUM(`quantity`) AS nb FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.intval($id));
		self::$_nbProducts = intval($row['nb']);
		return intval($row['nb']);
	}

	/**
	 * Add a discount to the cart (NO controls except doubles)
	 *
	 * @param integer $id_discount The discount to add to the cart
	 * @result boolean Update result
	 */
	public	function addDiscount($id_discount)
	{
		return Db::getInstance()->AutoExecute(_DB_PREFIX_.'cart_discount', array('id_discount' => intval($id_discount), 'id_cart' => intval($this->id)), 'INSERT');
	}

	public function containsProduct($id_product, $id_product_attribute, $id_customization)
	{
		return Db::getInstance()->getRow('
			SELECT cp.`quantity`
			FROM `'._DB_PREFIX_.'cart_product` cp
			'.($id_customization ? 'LEFT JOIN `'._DB_PREFIX_.'customization` c ON (c.`id_product` = cp.`id_product` AND c.`id_product_attribute` = cp.`id_product_attribute`)' : '').'
			WHERE cp.`id_product` = '.intval($id_product).' AND cp.`id_product_attribute` = '.intval($id_product_attribute).' AND cp.`id_cart` = '.intval($this->id).
			($id_customization ? ' AND c.`id_customization` = '.intval($id_customization) : ''));
	}

	/**
	 * Update product quantity
	 *
	 * @param integer $quantity Quantity to add (or substract)
	 * @param integer $id_product Product ID
	 * @param integer $id_product_attribute Attribute ID if needed
	 * @param string $operator Indicate if quantity must be increased or decreased
	 */
	public	function updateQty($quantity, $id_product, $id_product_attribute = NULL, $id_customization = false, $operator = 'up')
	{
		self::$_nbProducts = 0;
		if (intval($quantity) <= 0)
			return $this->deleteProduct(intval($id_product), intval($id_product_attribute), intval($id_customization));
		else
		{
			/* Check if the product is already in the cart */
			$result = $this->containsProduct($id_product, $id_product_attribute, $id_customization);

			/* Update quantity if product already exist */
			if (Db::getInstance()->NumRows())
			{
				if ($operator == 'up')
				{
					$result2 = Db::getInstance()->getRow('
						SELECT '.($id_product_attribute ? 'pa' : 'p').'.`quantity`, p.`out_of_stock`
						FROM `'._DB_PREFIX_.'product` p
						'.($id_product_attribute ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON p.`id_product` = pa.`id_product`' : '').'
						WHERE p.`id_product` = '.intval($id_product).
						($id_product_attribute != NULL ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : ''));
					$productQty = intval($result2['quantity']);
					$newQty = $result['quantity'] + intval($quantity);
					$qty = '`quantity` + '.intval($quantity);
					if ((intval($result2['out_of_stock']) == 0 OR ((intval($result2['out_of_stock']) == 2 AND !Configuration::get('PS_ORDER_OUT_OF_STOCK')))) AND $newQty > $productQty)
						return false;
				}
				elseif ($operator == 'down')
				{
					$qty = '`quantity` - '.intval($quantity);
					$newQty = $result['quantity'] - intval($quantity);
				}
				else
					return false;

				/* Delete product from cart */
				if ($newQty <= 0)
					return $this->deleteProduct(intval($id_product), intval($id_product_attribute), intval($id_customization));
				else
					Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'cart_product`
					SET `quantity` = '.$qty.'
					WHERE `id_product` = '.intval($id_product).
					($id_product_attribute != NULL ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : '').'
					AND `id_cart` = '.intval($this->id));
			}

			/* Add produt to the cart */
			else
			{
				$result2 = Db::getInstance()->getRow('
					SELECT '.($id_product_attribute ? 'pa' : 'p').'.`quantity`, p.`out_of_stock`
					FROM `'._DB_PREFIX_.'product` p
					'.($id_product_attribute ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON p.`id_product` = pa.`id_product`' : '').'
					WHERE p.`id_product` = '.intval($id_product).
					($id_product_attribute != NULL ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : ''));
				$productQty = intval($result2['quantity']);
				if (intval($quantity) > $productQty AND (intval($result2['out_of_stock']) == 0 OR (intval($result2['out_of_stock']) == 2 AND !Configuration::get('PS_ORDER_OUT_OF_STOCK'))))
					return false;
				if (!Db::getInstance()->AutoExecute(_DB_PREFIX_.'cart_product', array('id_product' => intval($id_product),
				'id_product_attribute' => intval($id_product_attribute), 'id_cart' => intval($this->id),
				'quantity' => intval($quantity), 'date_add' => pSql(date('Y-m-d H:i:s'))), 'INSERT'))
					return false;
			}
		}
		return $this->_updateCustomizationQuantity(intval($quantity), intval($id_customization), intval($id_product), intval($id_product_attribute), $operator);
	}

	/*
	** Customization management
	*/
	private function _updateCustomizationQuantity($quantity, $id_customization, $id_product, $id_product_attribute, $operator = 'up')
	{
		global $cookie;

		/* Getting datas */
		$files = $cookie->getFamily('pictures_'.intval($id_product).'_');
		$textFields = $cookie->getFamily('textFields_'.intval($id_product).'_');
		/* Customization addition */
		if (count($files) > 0 OR count($textFields) > 0)
			return $this->_addCustomization(intval($id_product), intval($id_product_attribute), $files, $textFields, intval($quantity));
		/* Deletion */
		if (intval($id_customization) AND intval($quantity) < 1)
			return $this->_deleteCustomization(intval($id_customization), intval($id_product), intval($id_product_attribute));
		/* Quantity update */
		if (($result = Db::getInstance()->getRow('SELECT `quantity` FROM `'._DB_PREFIX_.'customization` WHERE `id_customization` = '.intval($id_customization))) === false)
			return true;
		if (Db::getInstance()->NumRows())
		{
			if ($operator == 'down' AND intval($result['quantity']) - intval($quantity) < 1)
				return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization` WHERE `id_customization` = '.intval($id_customization));
			return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity` = `quantity` '.($operator == 'up' ? '+ ' : '- ').intval($quantity).' WHERE `id_customization` = '.intval($id_customization));
		}
		return true;
	}

	public function _addCustomization($id_product, $id_product_attribute, $files, $textFields, $quantity)
	{
		if (!is_array($files) OR !is_array($textFields))
			die(Tools::displayError());
		/* Copying them inside the db */
		if (!Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'customization` (`id_cart`, `id_product`, `id_product_attribute`, `quantity`) VALUES ('.intval($this->id).', '.intval($id_product).', '.intval($id_product_attribute).', '.intval($quantity).')'))
			return false;
		if (!$id_customization = Db::getInstance()->Insert_ID())
			return false;
		$query = 'INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`) VALUES ';
		if (count($files))
			foreach ($files AS $key => $filename)
			{
				$tmp = explode('_', $key);
				$query .= '('.intval($id_customization).', '._CUSTOMIZE_FILE_.', '.$tmp[2].', \''.$filename.'\'), ';
			}
		if (count($textFields))
			foreach ($textFields AS $key => $textFieldValue)
			{
				$tmp = explode('_', $key);
				$query .= '('.intval($id_customization).', '._CUSTOMIZE_TEXTFIELD_.', '.$tmp[2].', \''.$textFieldValue.'\'), ';
			}
		$query = rtrim($query, ', ');
		if (!$result = Db::getInstance()->Execute($query))
			return false;
		/* Deleting customized informations from the cart (we just copied them inside the db) */
		return Cart::deleteCustomizationInformations(intval($id_product));
	}

	/**
	 * Check if order has already been placed
	 *
	 * @return boolean result
	 */
	public function OrderExists()
	{
		$result = Db::getInstance()->ExecuteS('SELECT `id_cart` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = '.intval($this->id));
		return Db::getInstance()->NumRows();
	}

	/*
	** Deletion
	*/

	/**
	 * Delete a discount from the cart
	 *
	 * @param integer $id_discount Discount ID
	 * @return boolean result
	 */
	public	function deleteDiscount($id_discount)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_discount` WHERE `id_discount` = '.intval($id_discount).' AND `id_cart` = '.intval($this->id).' LIMIT 1');
	}

	/**
	 * Delete a product from the cart
	 *
	 * @param integer $id_product Product ID
	 * @param integer $id_product_attribute Attribute ID if needed
	 * @param integer $id_customization Customization id
	 * @return boolean result
	 */
	public	function deleteProduct($id_product, $id_product_attribute = NULL, $id_customization = NULL)
	{
		self::$_nbProducts = 0;
		if (intval($id_customization))
			return $this->_deleteCustomization(intval($id_customization), intval($id_product), intval($id_product_attribute)) AND $this->deleteProduct(intval($id_product), $id_product_attribute, NULL);

		/* Get customization quantity */
		if (($result = Db::getInstance()->getRow('SELECT SUM(`quantity`) AS \'quantity\' FROM `'._DB_PREFIX_.'customization` WHERE `id_cart` = '.intval($this->id).' AND `id_product` = '.intval($id_product).' AND `id_product_attribute` = '.intval($id_product_attribute))) === false)
			return false;

		/* If the product still possesses customization it does not have to be deleted */
		if (Db::getInstance()->NumRows() AND intval($result['quantity']))
			return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cart_product` SET `quantity` = '.intval($result['quantity']).' WHERE `id_cart` = '.intval($this->id).' AND `id_product` = '.intval($id_product).($id_product_attribute != NULL ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : ''));

		/* Product deletion */
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_product` = '.intval($id_product).($id_product_attribute != NULL ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : '').' AND `id_cart` = '.intval($this->id));
	}

	/**
	 * Delete a customization from the cart
	 *
	 * @param integer $id_customization
	 * @return boolean result
	 */
	private	function _deleteCustomization($id_customization, $id_product, $id_product_attribute)
	{
		if (!$result = Db::getInstance()->getRow('SELECT `quantity` FROM `'._DB_PREFIX_.'customization` WHERE `id_customization` = '.intval($id_customization)) OR 
			!Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cart_product`
				SET `quantity` = `quantity` - '.intval($result['quantity']).'
				WHERE `id_cart` = '.intval($this->id).' AND `id_product` = '.intval($id_product).(intval($id_product_attribute) ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : '')))
			return false;
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization` WHERE `id_customization` = '.intval($id_customization));
	}

	/**
	* This function returns the total cart amount
	*
	* type = 1 : only products
	* type = 2 : only discounts
	* type = 3 : both
	* type = 4 : both but without shipping
	* type = 5 : only shipping
	* type = 6 : only wrapping
	* type = 7 : only products without shipping
	*
	* @param boolean $withTaxes With or without taxes
	* @param integer $type Total type
	* @return float Order total
	*/
	static public function getTotalCart($id_cart)
	{
		$cart = new Cart(intval($id_cart));
		if (!Validate::isLoadedObject($cart))
			die(Tools::displayError());
		return Tools::displayPrice($cart->getOrderTotal(), new Currency(intval($cart->id_currency)), false, false);
	}
	
	function getOrderTotal($withTaxes = true, $type = 3)
	{
		if (!$this->id)
			return 0;
		$type = intval($type);
		if (!in_array($type, array(1, 2, 3, 4, 5, 6, 7)))
			die(Tools::displayError());

		// no shipping cost if is a cart with only virtuals products
		$virtual = $this->isVirtualCart();
		if ($virtual AND $type == 5)
			return 0;
		if ($virtual AND $type == 3)
			$type = 4;
		$shipping_fees = ($type != 4 AND $type != 7) ? $this->getOrderShippingCost(NULL, intval($withTaxes)) : 0;
		if ($type == 7)
			$type = 1;
			
		$products = $this->getProducts();
		$order_total = 0;
		foreach ($products AS $product)
		{
			$price = floatval(Product::getPriceStatic(intval($product['id_product']), $withTaxes, intval($product['id_product_attribute']), 6, NULL, false, true, $product['quantity']));
			$total_price = $price * intval($product['quantity']);
			$order_total += $total_price;
		}
		$order_total_products = $order_total;
		if ($type == 2) $order_total = 0;

		// Wrapping Fees
		$wrapping_fees = 0;
		if ($this->gift)
		{
			$wrapping_fees = floatval(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
			if (!$withTaxes)
			{
				$wrapping_fees_tax = new Tax(intval(Configuration::get('PS_GIFT_WRAPPING_TAX')));
				$wrapping_fees /= 1 + ((floatval($wrapping_fees_tax->rate) / 100));
			}
		}

		if ($type != 1)
		{
			$discounts = array();
			/* Firstly get all discounts, looking for a free shipping one (in order to substract shipping fees to the total amount) */
			if ($discountIds = $this->getDiscounts(true))
			{
				foreach ($discountIds AS $id_discount)
				{
					$discount = new Discount(intval($id_discount['id_discount']));
					if (Validate::isLoadedObject($discount))
					{
						$discounts[] = $discount;
						if ($discount->id_discount_type == 3)
							foreach($products AS $product)
							{
								$categories = Discount::getCategories($discount->id);
								if(count($categories))
										if (Product::idIsOnCategoryId($product['id_product'], $categories))
										{
											if($type == 2)
												$order_total -= $shipping_fees;
											$shipping_fees = 0;
											break;
										}
							}
					}
				}
				/* Secondly applying all vouchers to the correct amount */
				foreach ($discounts AS $discount)
					if ($discount->id_discount_type != 3)
						$order_total -= floatval($discount->getValue(sizeof($discounts), $order_total_products, $shipping_fees, $this->id, intval($withTaxes)));
			}
		}
		if ($type == 5) return $shipping_fees;
		if ($type == 6) return $wrapping_fees;
		if ($type == 3) $order_total += $shipping_fees + $wrapping_fees;
		if ($order_total < 0 AND $type != 2) return 0;
		return floatval($order_total);
	}

	/**
	* Return shipping total
	*
	* @param integer $id_carrier Carrier ID (default : current carrier)
	* @return float Shipping total
	*/
    function getOrderShippingCost($id_carrier = NULL, $useTax = true)
    {
		global $defaultCountry;

		if ($this->isVirtualCart())
			return 0;
		
		// Checking discounts in cart
		$products = $this->getProducts();
		$discounts = $this->getDiscounts(true);
		if ($discounts)
			foreach ($discounts AS $id_discount)
			{
				$discount = new Discount(intval($id_discount['id_discount']));
				if (!Validate::isLoadedObject($discount))
					die(Tools::displayError());
				if ($discount->id_discount_type == 3)
				{
					$total_cart = 0;
					$categories = Discount::getCategories($discount->id);
					foreach($products AS $product)
					{
						if(count($categories))
							if (Product::idIsOnCategoryId($product['id_product'], $categories))
									$total_cart += $product['total_wt'];
					}
					if ($total_cart >= $discount->minimal)
						return 0;
				}
			}

		// Order total without fees
		$orderTotal = $this->getOrderTotal(true, 7);
		
		// Start with shipping cost at 0
        $shipping_cost = 0;
		
		// If no product added, return 0
		if ($orderTotal <= 0 AND !intval(self::getNbProducts($this->id)))
			return $shipping_cost;

		// If no carrier, select default one
		if (!$id_carrier)
            $id_carrier = $this->id_carrier;
        if (empty($id_carrier))
            $id_carrier = Configuration::get('PS_CARRIER_DEFAULT');
		if (!isset(self::$_carriers[$id_carrier]))
			self::$_carriers[$id_carrier] = new Carrier(intval($id_carrier));
		$carrier = self::$_carriers[$id_carrier];
		if (!Validate::isLoadedObject($carrier))
			die(Tools::displayError('Hack attempt: "no default carrier"'));
        if (!$carrier->active)
			return $shipping_cost;
		// Get id zone
        if (isset($this->id_address_delivery) AND $this->id_address_delivery)
			$id_zone = Address::getZoneById(intval($this->id_address_delivery));
		else
			$id_zone = intval($defaultCountry->id_zone);
		
		// Select carrier tax
		if ($useTax AND $carrier->id_tax)
		{
			if (!isset(self::$_taxes[$carrier->id_tax]))
				self::$_taxes[$carrier->id_tax] = new Tax(intval($carrier->id_tax));
			$tax = self::$_taxes[$carrier->id_tax];
			if (Validate::isLoadedObject($tax) AND Tax::zoneHasTax(intval($tax->id), intval($id_zone)) AND !Tax::excludeTaxeOption())
				$carrierTax = $tax->rate;
		}
		$configuration = Configuration::getMultiple(array('PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT'));
		// Free fees
		if (isset($configuration['PS_SHIPPING_FREE_PRICE']) AND $orderTotal >= floatval($configuration['PS_SHIPPING_FREE_PRICE']) AND floatval($configuration['PS_SHIPPING_FREE_PRICE']) > 0)
			return $shipping_cost;
		if (isset($configuration['PS_SHIPPING_FREE_WEIGHT']) AND $this->getTotalWeight() >= floatval($configuration['PS_SHIPPING_FREE_WEIGHT']) AND floatval($configuration['PS_SHIPPING_FREE_WEIGHT']) > 0)
			return $shipping_cost;

		// Get shipping cost using correct method
		if ($carrier->range_behavior)
		{
			// Get id zone
	        if (isset($this->id_address_delivery) AND $this->id_address_delivery)
				$id_zone = Address::getZoneById(intval($this->id_address_delivery));
			else
				$id_zone = intval($defaultCountry->id_zone);
			if ((Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByWeight($carrier->id, $this->getTotalWeight(), $id_zone)))
				OR (!Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByPrice($carrier->id, $this->getOrderTotal(true, 4), $id_zone))))
				$shipping_cost += 0;
			else {
		        if (intval($configuration['PS_SHIPPING_METHOD']))
		            $shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
		        else
		            $shipping_cost += $carrier->getDeliveryPriceByPrice($orderTotal, $id_zone);
			}
		}
		else
		{
			if (intval($configuration['PS_SHIPPING_METHOD']))
				$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
			else
				$shipping_cost += $carrier->getDeliveryPriceByPrice($orderTotal, $id_zone);
		}
		
		// Apply tax
		if (isset($carrierTax))
			 $shipping_cost *= 1 + ($carrierTax / 100);

		// Adding handling charges
		if (isset($configuration['PS_SHIPPING_HANDLING']) AND $carrier->shipping_handling)
            $shipping_cost += floatval($configuration['PS_SHIPPING_HANDLING']);
		return floatval($shipping_cost);
    }

	/**
	* Return cart weight
	*
	* @return float Cart weight
	*/
	public function getTotalWeight()
	{
		if (!$this->id)
			return 0;
			
		if ($this->_totalWeight)
			return $this->_totalWeight;

		$result = Db::getInstance()->getRow('
		SELECT SUM((p.`weight` + pa.`weight`) * cp.`quantity`) as nb
		FROM `'._DB_PREFIX_.'cart_product` cp
		LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON cp.`id_product_attribute` = pa.`id_product_attribute`
		WHERE (cp.`id_product_attribute` IS NOT NULL AND cp.`id_product_attribute` != 0)
		AND cp.`id_cart` = '.intval($this->id));
		$result2 = Db::getInstance()->getRow('
		SELECT SUM(p.`weight` * cp.`quantity`) as nb
		FROM `'._DB_PREFIX_.'cart_product` cp
		LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
		WHERE (cp.`id_product_attribute` IS NULL OR cp.`id_product_attribute` = 0)
		AND cp.`id_cart` = '.intval($this->id));

		$this->_totalWeight = round(floatval($result['nb']) + floatval($result2['nb']), 3);
		return $this->_totalWeight;
	}

	/**
	* Check discount validity
	*
	* @return mixed Return a string if an error occurred and false otherwise
	*/
	function checkDiscountValidity($discountObj, $discounts, $order_total, $products, $checkCartDiscount = false)
	{
		global $cookie;
		
		if (!$order_total)
			 return Tools::displayError('cannot add voucher if order is free');
		if (!$discountObj->active)
			return Tools::displayError('this voucher has already been used or is disabled');
		if (!$discountObj->quantity)
			return Tools::displayError('this voucher has expired (usage limit attained)');
		if ($checkCartDiscount
			AND (
				$this->getDiscountsCustomer($discountObj->id) >= $discountObj->quantity_per_user
				OR (Order::getDiscountsCustomer(intval($cookie->id_customer), $discountObj->id) + $this->getDiscountsCustomer($discountObj->id) >= $discountObj->quantity_per_user) >= $discountObj->quantity_per_user
				)
			)
			return Tools::displayError('you cannot use this voucher anymore (usage limit attained)');
		if (strtotime($discountObj->date_from) > time())
			return Tools::displayError('this voucher is not yet valid');
		if (strtotime($discountObj->date_to) < time())
			return Tools::displayError('this voucher has expired');
		if (sizeof($discounts) >= 1 AND $checkCartDiscount)
		{
			if (!$discountObj->cumulable)
				return Tools::displayError('this voucher isn\'t cumulative with other current discounts');
			foreach ($discounts as $discount)
				if (!$discount['cumulable'])
					return Tools::displayError('previous voucher added isn\'t cumulative with other discounts');
		}
		if (is_array($discounts) AND in_array($discountObj->id, $discounts))
			return Tools::displayError('this voucher is already in your cart');
		if ($discountObj->id_customer AND $this->id_customer != $discountObj->id_customer)
		{
			if (!$cookie->isLogged())
				return Tools::displayError('you cannot use this voucher').' - '.Tools::displayError('try to log in if you own it');
			return Tools::displayError('you cannot use this voucher');
		}
		$currentDate = date('Y-m-d');
		if (!$discountObj->cumulable_reduction)
		{
			foreach ($products as $product)
				if ((intval($product['reduction_price']) OR intval($product['reduction_percent'])) AND ($product['reduction_from'] == $product['reduction_to'] OR ($currentDate >= $product['reduction_from'] AND $currentDate <= $product['reduction_to']))
					OR $product['on_sale'])
					return Tools::displayError('this voucher isn\'t cumulative on products with reduction or marked as on sale');
		}
		$products  = $this->getProducts();
		$total_cart = 0;
		$categories = Discount::getCategories($discountObj->id);
		foreach($products AS $product)
		{
			if(count($categories))
				if (Product::idIsOnCategoryId($product['id_product'], $categories))
						$total_cart += $product['total_wt'];
		}
		if ($total_cart < $discountObj->minimal)
			return Tools::displayError('the total amount of your order isn\'t high enough or this voucher cannot be used with those products');
		return false;
	}

	public function hasProductInCategory($discountObj)
	{
		$products = $this->getProducts();
		$categories = Discount::getCategories($discountObj->id);
		foreach ($products AS $product)
		{
				if (Product::idIsOnCategoryId($product['id_product'], $categories))
					return true;
		}
		return false;
	}

	/**
	* Return useful informations for cart
	*
	* @return array Cart details
	*/
	function getSummaryDetails()
	{
		global $cookie;
		return array(
			'delivery' => new Address(intval($this->id_address_delivery)),
			'invoice' => new Address(intval($this->id_address_invoice)),
			'carrier' => new Carrier(intval($this->id_carrier), $cookie->id_lang),
			'products' => $this->getProducts(false),
			'discounts' => $this->getDiscounts(),
			'total_discounts' => number_format($this->getOrderTotal(true, 2), 2, '.', ''),
			'total_discounts_tax_exc' => number_format($this->getOrderTotal(false, 2), 2, '.', ''),
			'total_wrapping' => number_format($this->getOrderTotal(true, 6), 2, '.', ''),
			'total_wrapping_tax_exc' => number_format($this->getOrderTotal(false, 6), 2, '.', ''),
			'total_shipping' => number_format($this->getOrderShippingCost(), 2, '.', ''),
			'total_shipping_tax_exc' => number_format($this->getOrderShippingCost(NULL, false), 2, '.', ''),
			'total_products_wt' => number_format($this->getOrderTotal(true, 1), 2, '.', ''),
			'total_products' => number_format($this->getOrderTotal(false, 1), 2, '.', ''),
			'total_price' => number_format($this->getOrderTotal(), 2, '.', ''),
			'total_tax' => number_format($this->getOrderTotal() - $this->getOrderTotal(false), 2, '.', ''),
			'total_price_without_tax' => number_format($this->getOrderTotal(false), 2, '.', ''));
	}

	/**
	* Return carts thats have not been converted in orders
	*
	* @param string $dateFrom Select only cart updated after this date
	* @param string $dateTo Select only cart updated before this date
	* @return array Carts
	*/
	static function getNonOrderedCarts($dateFrom, $dateTo)
	{
		if (!Validate::isDate($dateFrom) OR !Validate::isDate($dateTo))
			die (Tools::displayError());

		return Db::getInstance()->ExecuteS('
		SELECT cart.`id_cart`, cart.`date_upd`, c.`id_customer` AS id_customer, c.`lastname` AS customer_lastname, c.`firstname` AS customer_firstname,
		SUM(cp.`quantity`) AS nb_products,
		COUNT(cd.`id_cart`) AS nb_discounts
		FROM `'._DB_PREFIX_.'cart` cart
		LEFT JOIN `'._DB_PREFIX_.'cart_product` cp ON cart.`id_cart` = cp.`id_cart`
		LEFT JOIN `'._DB_PREFIX_.'cart_discount` cd ON cart.`id_cart` = cd.`id_cart`
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON cart.`id_customer` = c.`id_customer`
		WHERE cart.`id_cart` NOT IN (SELECT `id_cart` FROM `'._DB_PREFIX_.'orders`)
		AND TO_DAYS(cart.`date_upd`) >= TO_DAYS(\''.pSQL(strftime('%Y-%m-%d %H:%M:%S', strtotime($dateFrom))).'\')
		AND TO_DAYS(cart.`date_upd`) <= TO_DAYS(\''.pSQL(strftime('%Y-%m-%d %H:%M:%S', strtotime($dateTo))).'\')
		GROUP BY cart.`id_cart`, cp.`id_cart`, cd.`id_cart`
		ORDER BY cart.`date_upd` DESC');
	}

	public function checkQuantities()
	{
		if (Configuration::get('PS_STOCK_MANAGEMENT'))
			foreach ($this->getProducts() AS $product)
			    if (!$product['active'] OR (!$product['allow_oosp'] AND $product['stock_quantity'] < $product['quantity']))
			    	return false;
		return true;
	}

	static public function lastNoneOrderedCart($id_customer)
	{
	 	if (!$result = Db::getInstance()->getRow('
		 	SELECT c.`id_cart`
			FROM '._DB_PREFIX_.'cart c
			LEFT JOIN '._DB_PREFIX_.'orders o ON (c.`id_cart` = o.`id_cart`)
			WHERE c.`id_customer` = '.intval($id_customer).' AND o.`id_cart` IS NULL
			ORDER BY c.`date_upd` DESC'))
	 		return false;
	 	return $result['id_cart'];
	}

	/**
	* Check if cart contains only virtual products
	* @return boolean true if is a virtual cart or false
	*
	*/
	public function isVirtualCart()
	{
		if (!intval(self::getNbProducts($this->id)))
			return false;
		$allVirtual = true;
		foreach ($this->getProducts() AS $product)
			$allVirtual &= (ProductDownload::getIdFromIdProduct(intval($product['id_product'])) ? true : false);
		return $allVirtual;
	}

	static public function getCartByOrderId($id_order)
	{
		$result = Db::getInstance()->getRow('SELECT `id_cart` FROM '._DB_PREFIX_.'orders WHERE `id_order` = '.intval($id_order));
		if (!$result OR empty($result) OR !key_exists('id_cart', $result))
			return false;
		return new Cart(intval($result['id_cart']));
	}

	/*
	* Add customer's pictures
	*
	* @return bool Always true
	*/
	public function addPictureToProduct($id_product, $index, $identifier)
	{
		global $cookie;

		$varName = 'pictures_'.intval($id_product).'_'.intval($index);
		if ($cookie->$varName)
		{
			unlink(_PS_PROD_PIC_DIR_.$cookie->$varName);
			unlink(_PS_PROD_PIC_DIR_.$cookie->$varName.'_small');
		}
		$cookie->$varName = $identifier;
		return true;
	}

	/*
	* Add customer's text
	*
	* @return bool Always true
	*/
	public function addTextFieldToProduct($id_product, $index, $textValue)
	{
		global $cookie;

		$cookie->{'textFields_'.intval($id_product).'_'.intval($index)} = pSQL($textValue);
		return true;
	}

	/*
	* Delete customer's text
	*
	* @return bool Always true
	*/
	public function deleteTextFieldFromProduct($id_product, $index)
	{
		global $cookie;

		unset($cookie->{'textFields_'.intval($id_product).'_'.intval($index)});
		return true;
	}

	/*
	* Remove a customer's picture
	*
	* @return bool
	*/
	public function deletePictureToProduct($id_product, $index)
	{
		global $cookie;

		$varName = 'pictures_'.intval($id_product).'_'.intval($index);
		if ($picture = $cookie->$varName)
		{
			if (!@unlink(_PS_PROD_PIC_DIR_.$picture) OR !@unlink(_PS_PROD_PIC_DIR_.$picture.'_small'))
				return false;
			unset($cookie->$varName);
			return true;
		}
		return false;
	}

	static public function deleteCustomizationInformations($id_product)
	{
		global $cookie;

		$cookie->unsetFamily('pictures_'.intval($id_product).'_');
		$cookie->unsetFamily('textFields_'.intval($id_product).'_');
		return true;
	}
	
	static public function getCustomerCarts($id_customer)
    {
	 	$result = Db::getInstance()->ExecuteS('
		 	SELECT *
			FROM '._DB_PREFIX_.'cart c
			WHERE c.`id_customer` = '.intval($id_customer).'
			ORDER BY c.`date_add` DESC');
	 	return $result;
    }

	static public function replaceZeroByShopName($echo, $tr)
	{
		return ($echo == '0' ? Configuration::get('PS_SHOP_NAME') : $echo);
	}

  /* DEPRECATED */
	public function getCustomeremail()
	{
		$customer = new Customer(intval($this->id_customer));
		return $customer->email;
	}
}
