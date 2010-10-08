<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class LoyaltyModule extends ObjectModel
{
	public $id_loyalty_state;
	public $id_customer;
	public $id_order;
	public $id_discount;
	public $points;
	public $date_add;
	public $date_upd;

	protected $fieldsRequired = array('id_customer', 'points');
	protected $fieldsSize = array(
		'id_loyalty_state' => 2,
		'id_customer' => 8,
		'id_order' => 8,
		'id_discount' => 8,
		'points' => 16
	);
	protected $fieldsValidate = array(
		'id_loyalty_state' => 'isInt',
		'id_customer' => 'isInt',
		'id_discount' => 'isInt',
		'id_order' => 'isInt',
		'points' => 'isInt'
	);

	protected $table = 'loyalty';
	protected $identifier = 'id_loyalty';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_loyalty_state'] = intval($this->id_loyalty_state);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_order'] = intval($this->id_order);
		$fields['id_discount'] = intval($this->id_discount);
		$fields['points'] = intval($this->points);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}

	public function save($nullValues = false, $autodate = true)
	{
		parent::save($nullValues, $autodate);
		$this->historize();
	}

	static public function getByOrderId($id_order)
	{
		if (!Validate::isUnsignedId($id_order))
			return false;

		$result = Db::getInstance()->getRow('
		SELECT f.id_loyalty
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_order = '.intval($id_order));

		return isset($result['id_loyalty']) ? $result['id_loyalty'] : false;
	}

	static public function getOrderNbPoints($order)
	{
		if (!Validate::isLoadedObject($order))
			return false;
		return self::getCartNbPoints(new Cart($order->id_cart));
	}

	static public function getCartNbPoints($cart, $newProduct = NULL)
	{
		$total = 0;
		if (Validate::isLoadedObject($cart))
		{
			$cartProducts = $cart->getProducts();
			
			if (isset($newProduct) AND !empty($newProduct))
			{
				$cartProductsNew['reduction_from'] = $newProduct->reduction_from;
				$cartProductsNew['reduction_to'] = $newProduct->reduction_to;
				$cartProductsNew['reduction_price'] = $newProduct->reduction_price;
				$cartProductsNew['reduction_percent'] = $newProduct->reduction_percent;
				$cartProductsNew['on_sale'] = $newProduct->on_sale;
				$cartProductsNew['price_wt'] = number_format($newProduct->getPrice(true, intval($newProduct->getIdProductAttributeMostExpsensive())), 2, '.', '');
				$cartProductsNew['cart_quantity'] = 1;
				$cartProducts[] = $cartProductsNew;
			}
			foreach ($cartProducts AS $product)
			{
				if (!intval(Configuration::get('PS_LOYALTY_NONE_AWARD')) AND ($product['reduction_from'] == $product['reduction_to'] OR 
				date('Y-m-d H:i:s') <= $product['reduction_to'] AND date('Y-m-d H:i:s') >= $product['reduction_from'] AND ($product['reduction_price'] > 0 OR $product['reduction_percent'] > 0 OR $product['on_sale'])))
					continue;
				
				$total += $product['price_wt'] * intval($product['cart_quantity']);
			}
			foreach ($cart->getDiscounts(false) AS $discount)
				$total -= $discount['value_real'];
			$total = self::getNbPointsByPrice($total);
		}

		return $total;
	}

	static public function getVoucherValue($nbPoints)
	{
		global $cookie;
		
		return floatval(floatval($nbPoints) * floatval(Tools::convertPrice(Configuration::get('PS_LOYALTY_POINT_VALUE'), new Currency(intval($cookie->id_currency)))));
	}

	static public function getNbPointsByPrice($price)
	{
		global $cookie;

		$points = 0;

		if (Configuration::get('PS_CURRENCY_DEFAULT') != $cookie->id_currency)
		{
			$currency = new Currency(intval($cookie->id_currency));
			if ($currency->conversion_rate)
				$price = $price / $currency->conversion_rate;
		}

		/* Prevent division by zero */
		if ($pointRate = floatval(Configuration::get('PS_LOYALTY_POINT_RATE')))
			$points = floor($price / $pointRate);
		return $points;
	}

	static public function getPointsByCustomer($id_customer)
	{
		$return = Db::getInstance()->getRow('
		SELECT SUM(f.points) points
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.intval($id_customer).'
		AND f.id_loyalty_state IN ('.intval(LoyaltyStateModule::getValidationId()).', '.intval(LoyaltyStateModule::getNoneAwardId()).')');
		
		return intval($return['points']);
	}

	static public function getAllByIdCustomer($id_customer, $id_lang, $onlyValidate=false)
	{
		$query = '
		SELECT f.id_order AS id, f.date_add AS date, (o.total_paid - o.total_shipping) AS total_without_shipping, f.points AS points, f.id_loyalty AS id_loyalty, f.id_loyalty_state AS id_loyalty_state, fsl.name AS state
		FROM `'._DB_PREFIX_.'loyalty` f
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (f.id_order = o.id_order)
		LEFT JOIN `'._DB_PREFIX_.'loyalty_state_lang` fsl ON (f.id_loyalty_state = fsl.id_loyalty_state AND fsl.id_lang = '.intval($id_lang).')
		WHERE f.id_customer = '.intval($id_customer);
		if ($onlyValidate === true)
			$query.= ' AND f.id_loyalty_state = '.intval(LoyaltyStateModule::getValidationId());
		$query .= ' GROUP BY f.id_loyalty';

		return Db::getInstance()->ExecuteS($query);
	}

	static public function getDiscountByIdCustomer($id_customer, $last=false)
	{
		$query = '
		SELECT f.id_discount AS id_discount, f.date_upd AS date_add
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.intval($id_customer).' AND id_discount > 0';
		if ($last === true)
			$query.= ' ORDER BY f.id_loyalty DESC LIMIT 0,1';
		$query.= ' GROUP BY f.id_discount';

		return Db::getInstance()->ExecuteS($query);
	}

	static public function registerDiscount($discount)
	{
		if (!Validate::isLoadedObject($discount))
			die (Tools::displayError('Incorrect object Discount.'));
		$items = self::getAllByIdCustomer($discount->id_customer, NULL, true);
		foreach ($items AS $item)
		{
			$f = new LoyaltyModule($item['id_loyalty']);
			$f->id_discount = $discount->id;
			$f->id_loyalty_state = LoyaltyStateModule::getConvertId();
			$f->save();
		}
	}

	static public function getOrdersByIdDiscount($id_discount)
	{
		$query = '
		SELECT f.id_order AS id_order, f.points AS points, f.date_upd AS date
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_discount = '.intval($id_discount).' AND f.id_loyalty_state = '.intval(LoyaltyStateModule::getConvertId());

		$items = Db::getInstance()->ExecuteS($query);
		if (!empty($items) AND is_array($items))
		{
			foreach ($items AS $key => $item)
			{
				$order = new Order($item['id_order']);
				$items[$key]['id_currency'] = $order->id_currency;
				$items[$key]['id_lang'] = $order->id_lang;
				$items[$key]['total_paid'] = $order->total_paid;
				$items[$key]['total_shipping'] = $order->total_shipping;
			}
			return $items;
		}

		return false;
	}

	/* Register all transaction in a specific history table */
	private function historize()
	{
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'loyalty_history` (`id_loyalty`, `id_loyalty_state`, `points`, `date_add`)
		VALUES ('.intval($this->id).', '.intval($this->id_loyalty_state).', '.intval($this->points).', NOW())');
	}

}

?>
