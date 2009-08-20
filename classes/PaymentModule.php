<?php

/**
  * Payment modules class, PaymentModule.php
  * Payment modules management and abstraction
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(dirname(__FILE__).'/../config/config.inc.php');

abstract class PaymentModule extends Module
{
	/** @var integer Current order's id */
	public	$currentOrder;
	public	$currencies = true;
	public	$currencies_mode = 'checkbox';
	
	public function install()
	{
		if (!parent::install())
			return false;
		
		// Insert currencies availability
		if ($this->currencies_mode == 'checkbox')
		{
			if (!Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'module_currency` (id_module, id_currency)
			SELECT '.intval($this->id).', id_currency FROM `'._DB_PREFIX_.'currency` WHERE deleted = 0'))
				return false;
		}
		elseif ($this->currencies_mode == 'radio')
		{
			if (!Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'module_currency` (id_module, id_currency)
			VALUES ('.intval($this->id).', -2)'))
				return false;
		}
		else
			Tools::displayError('No currency mode for payment module');

		// Insert countries availability
		$return = Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'module_country` (id_module, id_country)
		SELECT '.intval($this->id).', id_country FROM `'._DB_PREFIX_.'country` WHERE active = 1');
		// Insert group availability
		$return &= Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'module_group` (id_module, id_group)
		SELECT '.intval($this->id).', id_group FROM `'._DB_PREFIX_.'group`');
		
		return $return;
	}
	
	public function uninstall()
	{
		if (!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE id_module = '.intval($this->id))
			OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_currency` WHERE id_module = '.intval($this->id))
			OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_group` WHERE id_module = '.intval($this->id)))
			return false;
		return parent::uninstall();
	}

	/**
	* Validate an order in database
	* Function called from a payment module
	*
	* @param integer $id_cart Value
	* @param integer $id_order_state Value
	* @param float $amountPaid Amount really paid by customer (in the default currency)
	* @param string $paymentMethod Payment method (eg. 'Credit cart')
	* @param string $message Message to attach to order
	*/

	function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false)
	{
		$cart = new Cart(intval($id_cart));

		// Does order already exists ?
		if (Validate::isLoadedObject($cart) AND $cart->OrderExists() === 0)
		{
			// Copying data from cart
			$order = new Order();
			$order->id_carrier = intval($cart->id_carrier);
			$order->id_customer = intval($cart->id_customer);
			$order->id_address_invoice = intval($cart->id_address_invoice);
			$order->id_address_delivery = intval($cart->id_address_delivery);
			$vat_address = new Address(intval($order->id_address_delivery));
			$id_zone = Address::getZoneById(intval($vat_address->id));
			$order->id_currency = ($currency_special ? intval($currency_special) : intval($cart->id_currency));
			$order->id_lang = intval($cart->id_lang);
			$order->id_cart = intval($cart->id);
			$customer = new Customer(intval($order->id_customer));
			$order->secure_key = pSQL($customer->secure_key);
			$order->payment = Tools::substr($paymentMethod, 0, 32);
			if (isset($this->name))
				$order->module = $this->name;
			$order->recyclable = $cart->recyclable;
			$order->gift = intval($cart->gift);
			$order->gift_message = $cart->gift_message;
			$currency = new Currency($order->id_currency);
			$amountPaid = !$dont_touch_amount ? floatval(Tools::convertPrice(floatval(number_format($amountPaid, 2, '.', '')), $currency)) : $amountPaid;
			$order->total_paid_real = $amountPaid;
			$order->total_products = floatval(Tools::convertPrice(floatval(number_format($cart->getOrderTotal(false, 1), 2, '.', '')), $currency));
			$order->total_discounts = floatval(Tools::convertPrice(floatval(number_format(abs($cart->getOrderTotal(true, 2)), 2, '.', '')), $currency));
			$order->total_shipping = floatval(Tools::convertPrice(floatval(number_format($cart->getOrderShippingCost(), 2, '.', '')), $currency));
			$order->total_wrapping = floatval(Tools::convertPrice(floatval(number_format(abs($cart->getOrderTotal(true, 6)), 2, '.', '')), $currency));
			$order->total_paid = floatval(Tools::convertPrice(floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', '')), $currency));
			// Amount paid by customer is not the right one -> Status = payment error
			if ($order->total_paid != $order->total_paid_real)
				$id_order_state = _PS_OS_ERROR_;

			// Creating order
			if ($cart->OrderExists() === 0)
				$result = $order->add();
			else 
				die(Tools::displayError('An order has already been placed using this cart'));

			// Next !
			if ($result AND isset($order->id))
			{
				// Optional message to attach to this order 
				if (isset($message) AND !empty($message))
				{
					$msg = new Message();
					$message = strip_tags($message, '<br>');
					if (!Validate::isCleanHtml($message))
						$message = $this->l('Payment message is not valid, please check your module!');
					$msg->message = $message;
					$msg->id_order = intval($order->id);
					$msg->private = 1;
					$msg->add();
				}

				// Insert products from cart into order_detail table
				$products = $cart->getProducts();
				$productsList = '';
				$db = Db::getInstance();
				$query = 'INSERT INTO `'._DB_PREFIX_.'order_detail`
					(`id_order`, `product_id`, `product_attribute_id`, `product_name`, `product_quantity`, `product_quantity_in_stock`, `product_price`, `product_quantity_discount`, `product_ean13`, `product_reference`, `product_supplier_reference`, `product_weight`, `tax_name`, `tax_rate`, `ecotax`, `download_deadline`, `download_hash`)
				VALUES ';

				$customizedDatas = Product::getAllCustomizedDatas(intval($order->id_cart));
				Product::addCustomizationPrice($products, $customizedDatas);
				foreach ($products AS $key => $product)
				{
					$productQuantity = intval(Product::getQuantity(intval($product['id_product']), ($product['id_product_attribute'] ? intval($product['id_product_attribute']) : NULL)));
					$quantityInStock = ($productQuantity - intval($product['quantity']) < 0) ? $productQuantity : intval($product['quantity']);
					if ($id_order_state != _PS_OS_CANCELED_ AND $id_order_state != _PS_OS_ERROR_)
					{
						if ($id_order_state != _PS_OS_OUTOFSTOCK_ AND (($updateResult = Product::updateQuantity($product)) === false OR $updateResult === -1))
							{
								$id_order_state = _PS_OS_OUTOFSTOCK_;
								$history = new OrderHistory();
								$history->id_order = intval($order->id);
								$history->changeIdOrderState(_PS_OS_OUTOFSTOCK_, intval($order->id));
								$history->addWithemail();
							}
						Hook::updateQuantity($product, $order);
					}
					$price = Tools::convertPrice(Product::getPriceStatic(intval($product['id_product']), false, ($product['id_product_attribute'] ? intval($product['id_product_attribute']) : NULL), 6, NULL, false, true, $product['quantity']), $currency);
					$price_wt = Tools::convertPrice(Product::getPriceStatic(intval($product['id_product']), true, ($product['id_product_attribute'] ? intval($product['id_product_attribute']) : NULL), 6, NULL, false, true, $product['quantity']), $currency);

					// Add some informations for virtual products
					$deadline = '0000-00-00 00:00:00';
					$download_hash = NULL;
					$productDownload = new ProductDownload();
					if ($id_product_download = $productDownload->getIdFromIdProduct(intval($product['id_product'])))
					{
						$productDownload = new ProductDownload(intval($id_product_download));
						$deadline = $productDownload->getDeadLine();
						$download_hash = $productDownload->getHash();
					}

					// Exclude VAT
					if (Tax::excludeTaxeOption())
					{
						$product['tax'] = 0;
						$product['rate'] = 0;
					}
					else
						$tax = Tax::getApplicableTax(intval($product['id_tax']), floatval($product['rate']));

					// Quantity discount
					$reduc = 0.0;
					if ($product['quantity'] > 1 AND ($qtyD = QuantityDiscount::getDiscountFromQuantity($product['id_product'], $product['quantity'])))
						$reduc = QuantityDiscount::getValue($price_wt, $qtyD->id_discount_type, $qtyD->value);

					// Query
					$query .= '('.intval($order->id).',
						'.intval($product['id_product']).',
						'.(isset($product['id_product_attribute']) ? intval($product['id_product_attribute']) : 'NULL').',
						\''.pSQL($product['name'].((isset($product['attributes']) AND $product['attributes'] != NULL) ? ' - '.$product['attributes'] : '')).'\',
						'.intval($product['quantity']).',
						'.$quantityInStock.',
						'.floatval($price).',
						'.floatval($reduc).',
						'.(empty($product['ean13']) ? 'NULL' : '\''.pSQL($product['ean13']).'\'').',
						'.(empty($product['reference']) ? 'NULL' : '\''.pSQL($product['reference']).'\'').',
						'.(empty($product['supplier_reference']) ? 'NULL' : '\''.pSQL($product['supplier_reference']).'\'').',
						'.floatval(array_key_exists('id_product_attribute', $product) ? $product['weight_attribute'] : $product['weight']).',
						\''.(!$tax ? '' : pSQL($product['tax'])).'\',
						'.floatval($tax).',
						'.floatval($product['ecotax']).',
						\''.pSQL($deadline).'\',
						\''.pSQL($download_hash).'\'),';

					$priceWithTax = number_format($price * (($tax + 100) / 100), 2, '.', '');
					$customizationQuantity = 0;
					if (isset($customizedDatas[$product['id_product']][$product['id_product_attribute']]))
					{
						$customizationQuantity = intval($product['customizationQuantityTotal']);
						$productsList .=
						'<tr style="background-color:'.($key%2 ? '#DDE2E6' : '#EBECEE').';">
							<td style="padding:0.6em 0.4em;">'.$product['reference'].'</td>
							<td style="padding:0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '').' - '.$this->l('Customized').'</strong></td>
							<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($price * ($tax + 100) / 100, $currency, false, false).'</td>
							<td style="padding:0.6em 0.4em; text-align:center;">'.$customizationQuantity.'</td>
							<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($customizationQuantity * $priceWithTax, $currency, false, false).'</td>
						</tr>';
					}

					if (!$customizationQuantity OR intval($product['quantity']) > $customizationQuantity)
						$productsList .=
						'<tr style="background-color:'.($key%2 ? '#DDE2E6' : '#EBECEE').';">
							<td style="padding:0.6em 0.4em;">'.$product['reference'].'</td>
							<td style="padding:0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '').'</strong></td>
							<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($price * ($tax + 100) / 100, $currency, false, false).'</td>
							<td style="padding:0.6em 0.4em; text-align:center;">'.(intval($product['quantity']) - $customizationQuantity).'</td>
							<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice((intval($product['quantity']) - $customizationQuantity) * $priceWithTax, $currency, false, false).'</td>
						</tr>';
				} // end foreach ($products)
				$query = rtrim($query, ',');
				$result = $db->Execute($query);

				// Insert discounts from cart into order_discount table
				$discounts = $cart->getDiscounts();
				$discountsList = '';
				foreach ($discounts AS $discount)
				{
					$objDiscount = new Discount(intval($discount['id_discount']));
					$value = $objDiscount->getValue(sizeof($discounts), $cart->getOrderTotal(true, 1), $order->total_shipping, $cart->id);
					$order->addDiscount($objDiscount->id, $objDiscount->name, $value);
					if ($id_order_state != _PS_OS_ERROR_ AND $id_order_state != _PS_OS_CANCELED_)
						$objDiscount->quantity = $objDiscount->quantity - 1;
					$objDiscount->update();

					$discountsList .=
					'<tr style="background-color:#EBECEE;">
							<td colspan="4" style="padding:0.6em 0.4em; text-align:right;">'.$this->l('Voucher code:').' '.$objDiscount->name.'</td>
							<td style="padding:0.6em 0.4em; text-align:right;">-'.Tools::displayPrice($value, $currency, false, false).'</td>
					</tr>';
				}

				// Specify order id for message
				$oldMessage = Message::getMessageByCartId(intval($cart->id));
				if ($oldMessage)
				{
					$message = new Message(intval($oldMessage['id_message']));
					$message->id_order = intval($order->id);
					$message->update();
				}

				// Hook new order
				$orderStatus = new OrderState(intval($id_order_state));
				if (Validate::isLoadedObject($orderStatus))
				{
					Hook::newOrder($cart, $order, $customer, $currency, $orderStatus);
					foreach ($cart->getProducts() as $product)
						if ($orderStatus->logable)
							ProductSale::addProductSale($product['id_product'], $product['quantity']);
				}

				// Set order state in order history ONLY if the "out of stock" status has not been yet reached
				// If it has, a status changing has already been applied at that time
				if ($id_order_state != _PS_OS_OUTOFSTOCK_)
				{
					$new_history = new OrderHistory();
					$new_history->id_order = intval($order->id);
					$new_history->changeIdOrderState(intval($id_order_state), intval($order->id));
					$new_history->addWithemail(true, $extraVars);
				}

				// Send an e-mail to customer
				if ($id_order_state != _PS_OS_ERROR_ AND $id_order_state != _PS_OS_CANCELED_ AND $customer->id)
				{
					$invoice = new Address(intval($order->id_address_invoice));
					$delivery = new Address(intval($order->id_address_delivery));
					$carrier = new Carrier(intval($order->id_carrier));
					$delivery_state = $delivery->id_state ? new State(intval($delivery->id_state)) : false;
					$invoice_state = $invoice->id_state ? new State(intval($invoice->id_state)) : false;

					$data = array(
					
						'{firstname}' => $customer->firstname,
						'{lastname}' => $customer->lastname,
						'{email}' => $customer->email,
						'{delivery_company}' => $delivery->company,
						'{delivery_firstname}' => $delivery->firstname,
						'{delivery_lastname}' => $delivery->lastname,
						'{delivery_address1}' => $delivery->address1,
						'{delivery_address2}' => $delivery->address2,
						'{delivery_city}' => $delivery->city,
						'{delivery_postal_code}' => $delivery->postcode,
						'{delivery_country}' => $delivery->country,
						'{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
						'{delivery_phone}' => $delivery->phone,
						'{delivery_other}' => $delivery->other,
						'{invoice_company}' => $invoice->company,
						'{invoice_firstname}' => $invoice->firstname,
						'{invoice_lastname}' => $invoice->lastname,
						'{invoice_address2}' => $invoice->address2,
						'{invoice_address1}' => $invoice->address1,
						'{invoice_city}' => $invoice->city,
						'{invoice_postal_code}' => $invoice->postcode,
						'{invoice_country}' => $invoice->country,
						'{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
						'{invoice_phone}' => $invoice->phone,
						'{invoice_other}' => $invoice->other,
						'{order_name}' => sprintf("#%06d", intval($order->id)),
						'{date}' => Tools::displayDate(date('Y-m-d H:i:s'), intval($order->id_lang), 1),
						'{carrier}' => (strval($carrier->name) != '0' ? $carrier->name : Configuration::get('PS_SHOP_NAME')),
						'{payment}' => $order->payment,
						'{products}' => $productsList,
						'{discounts}' => $discountsList,
						'{total_paid}' => Tools::displayPrice($order->total_paid, $currency, false, false),
						'{total_products}' => Tools::displayPrice($order->total_paid - $order->total_shipping - $order->total_wrapping+ $order->total_discounts, $currency, false, false),
						'{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency, false, false),
						'{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency, false, false),
						'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency, false, false)
					);
					
					if (is_array($extraVars))
						$data = array_merge($data, $extraVars);

					// Join PDF invoice
					if (intval(Configuration::get('PS_INVOICE')) AND Validate::isLoadedObject($orderStatus) AND $orderStatus->invoice AND $order->invoice_number)
					{
						$fileAttachment['content'] = PDF::invoice($order, 'S');
						$fileAttachment['name'] = Configuration::get('PS_INVOICE_PREFIX', intval($order->id_lang)).sprintf('%06d', $order->invoice_number).'.pdf';
						$fileAttachment['mime'] = 'application/pdf';
					}
					else
						$fileAttachment = NULL;

					if ($orderStatus->send_email AND Validate::isEmail($customer->email))
						Mail::Send(intval($order->id_lang), 'order_conf', 'Order confirmation', $data, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, $fileAttachment);
					$this->currentOrder = intval($order->id);
					return true;
				}
				return true;
			}
			else
				die(Tools::displayError('Order creation failed'));
		}
		else
			die(Tools::displayError('An order has already been placed using this cart'));
	}
	
	public function getCurrency()
	{
		global $cookie;
		
		if (!$this->currencies)
			return false;
		if ($this->currencies_mode == 'checkbox')
		{
			$currencies = Currency::getPaymentCurrencies($this->id);
			return $currencies;
		}
		elseif ($this->currencies_mode == 'radio')
		{
			$currencies = Currency::getPaymentCurrenciesSpecial($this->id);
			$currency = $currencies['id_currency'];
			if ($currency == -1)
				$id_currency = intval($cookie->id_currency);
			elseif ($currency == -2)
				$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
			else
				$id_currency = $currency;
		}
		if (!isset($id_currency) OR empty($id_currency))
			return false;
		return (new Currency($id_currency));
	}
}

?>
