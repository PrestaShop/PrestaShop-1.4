<?php

class MailAlerts extends Module
{
 	private $_html = '';
    private $_postErrors = array();

 	private $_merchant_mails;
 	private $_merchant_order;
 	private $_merchant_oos;
	private $_customer_qty;

 	const __MA_MAIL_DELIMITOR__ = ',';

	public function __construct()
	{
		$this->name = 'mailalerts';
        $this->tab = 'Tools';
		$this->version = '2.2';

		$this->_refreshProperties();

	 	parent::__construct();

	 	$this->displayName = $this->l('Mail alerts');
		$this->description = $this->l('Sends e-mails notifications to customers and merchants');
		$this->confirmUninstall = $this->l('Are you sure you want to delete all customers notifications ?');
	}

   	public function install()
   	{
		if (!parent::install() OR
	   	 	!$this->registerHook('newOrder') OR
	   	 	!$this->registerHook('updateQuantity') OR
			!$this->registerHook('productOutOfStock') OR
			!$this->registerHook('customerAccount') OR
			!$this->registerHook('updateProduct') OR
			!$this->registerHook('updateProductAttribute')
			)
			return false;

		Configuration::updateValue('MA_MERCHANT_ORDER', 1);
		Configuration::updateValue('MA_MERCHANT_OOS', 1);
		Configuration::updateValue('MA_CUSTOMER_QTY', 1);
		Configuration::updateValue('MA_MERCHANT_MAILS', Configuration::get('PS_SHOP_EMAIL'));
		
		$query = '
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mailalert_customer_oos` (
			`id_customer` int(10) unsigned NOT NULL,
			`customer_email` varchar(128) NOT NULL,
			`id_product` int(10) unsigned NOT NULL,
			`id_product_attribute` int(10) unsigned NOT NULL,
			PRIMARY KEY  (`id_customer`,`customer_email`,`id_product`,`id_product_attribute`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
	 	if (!Db::getInstance()->Execute($query))
	 		return false;
		
		/* This hook is optional */
		$this->registerHook('myAccountBlock');
		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('MA_MERCHANT_ORDER');
		Configuration::deleteByName('MA_MERCHANT_OOS');
		Configuration::deleteByName('MA_CUSTOMER_QTY');
		Configuration::deleteByName('MA_MERCHANT_MAILS');
	 	if (!Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'mailalert_customer_oos'))
	 		return false;
		parent::uninstall();
	}
	
	private function _refreshProperties()
	{
		$this->_merchant_mails =  Configuration::get('MA_MERCHANT_MAILS');
		$this->_merchant_order = intval(Configuration::get('MA_MERCHANT_ORDER'));
		$this->_merchant_oos = intval(Configuration::get('MA_MERCHANT_OOS'));
		$this->_customer_qty = intval(Configuration::get('MA_CUSTOMER_QTY'));
	}

    public function hookNewOrder($params)
    {
		if (!$this->_merchant_order OR empty($this->_merchant_mails))
			return;

		// Getting differents vars
		$id_lang = intval(Configuration::get('PS_LANG_DEFAULT'));
     	$currency = $params['currency'];
		$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_MAIL_METHOD', 'PS_MAIL_SERVER', 'PS_MAIL_USER', 'PS_MAIL_PASSWD', 'PS_SHOP_NAME'));
		$order = $params['order'];
		$customer = $params['customer'];
		$delivery = new Address(intval($order->id_address_delivery));
		$invoice = new Address(intval($order->id_address_invoice));
		$order_date_text = Tools::displayDate($order->date_add, intval($id_lang));
		$carrier = new Carrier(intval($order->id_carrier));
		$message = $order->getFirstMessage();
		if (!$message OR empty($message))
			$message = $this->l('No message');

		$itemsTable = '';
		foreach ($params['cart']->getProducts() AS $key => $product)
		{
			$unit_price = Product::getPriceStatic($product['id_product'], true, $product['id_product_attribute']);
			$price = Product::getPriceStatic($product['id_product'], true, $product['id_product_attribute'], 6, NULL, false, true, $product['quantity']);
			$itemsTable .=
				'<tr style="background-color:'.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
					<td style="padding:0.6em 0.4em;">'.$product['reference'].'</td>
					<td style="padding:0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '').'</strong></td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($unit_price, $currency, false, false).'</td>
					<td style="padding:0.6em 0.4em; text-align:center;">'.intval($product['quantity']).'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice(($price * $product['quantity']), $currency, false, false).'</td>
				</tr>';
		}
		foreach ($params['cart']->getDiscounts() AS $discount)
		{
			$itemsTable .=
			'<tr style="background-color:#EBECEE;">
					<td colspan="4" style="padding:0.6em 0.4em; text-align:right;">'.$this->l('Voucher code:').' '.$discount['name'].'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">-'.Tools::displayPrice($discount['value_real'], $currency, false, false).'</td>
			</tr>';
		}

		if ($delivery->id_state)
			$delivery_state = new State(intval($delivery->id_state));
		if ($invoice->id_state)
			$invoice_state = new State(intval($invoice->id_state));

		// Filling-in vars for email
		$template = 'new_order';
		$subject = $this->l('New order');
		$templateVars = array(
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
			'{order_name}' => sprintf("%06d", $order->id),
			'{shop_name}' => Configuration::get('PS_SHOP_NAME'),
			'{date}' => $order_date_text,
			'{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
			'{payment}' => $order->payment,
			'{items}' => $itemsTable,
			'{total_paid}' => Tools::displayPrice($order->total_paid, $currency),
			'{total_products}' => Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency),
			'{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency),
			'{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency),
			'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency),
			'{currency}' => $currency->sign,
			'{message}' => $message
		);
		$iso = Language::getIsoById(intval($id_lang));
		if (file_exists(dirname(__FILE__).'/mails/'.$iso.'/'.$template.'.txt') AND file_exists(dirname(__FILE__).'/mails/'.$iso.'/'.$template.'.html'))
			Mail::Send($id_lang, $template, $subject, $templateVars, explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails), NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/mails/');
	}
	
	public function hookProductOutOfStock($params)
    {
		global $smarty, $cookie;
		
		if (!$this->_customer_qty)
			return ;

		$id_product = intval($params['product']->id);
		$id_product_attribute = 0;

		if (!$cookie->isLogged())
			$smarty->assign('email', 1);
		else
		{
			$id_customer = intval($params['cookie']->id_customer);
			if ($this->customerHasNotification($id_customer, $id_product, $id_product_attribute))
				return ;
		}

		$smarty->assign(array(
			'id_product' => $id_product,
			'id_product_attribute' => $id_product_attribute));

		return $this->display(__FILE__, 'product.tpl');
	}
	
	public function customerHasNotification($id_customer, $id_product, $id_product_attribute)
	{
		$query = 'SELECT * FROM `'._DB_PREFIX_.'mailalert_customer_oos` 
		WHERE `id_customer` = '.intval($id_customer).' AND `id_product` = '.intval($id_product).' AND `id_product_attribute` = '.intval($id_product_attribute).';';
		$result = Db::getInstance()->ExecuteS($query);
		return sizeof($result);
	}
	
	public function hookUpdateQuantity($params)
	{
		$qty = intval($params['product']['quantity_attribute'] ? $params['product']['quantity_attribute'] : $params['product']['stock_quantity']) - intval($params['product']['quantity']);
		if ($qty <= intval(Configuration::get('PS_LAST_QTIES')) AND !(!$this->_merchant_oos OR empty($this->_merchant_mails)))
		{
			$templateVars = array('{qty}' => $qty,
			'{last_qty}' => intval(Configuration::get('PS_LAST_QTIES')),
			'{product}' => strval($params['product']['name']));
			Mail::Send(intval(Configuration::get('PS_LANG_DEFAULT')), 'productoutofstock', $this->l('Product out of stock'), $templateVars, explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails), NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
		}
	}
	
	public function hookUpdateProduct($params)
	{
		if (!$this->_customer_qty)
			return ;
		$qty = intval($params['product']->quantity);
		if ($qty > intval(Configuration::get('PS_LAST_QTIES')))
			$this->sendCustomerAlert(intval($params['product']->id), 0);
	}
	
	public function hookUpdateProductAttribute($params)
	{
		if (!$this->_customer_qty)
			return ;
		$result = Db::getInstance()->GetRow('
		SELECT `id_product`, `quantity` FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product_attribute` = '.intval($params['id_product_attribute']));
		$qty = intval($result['quantity']);
		if ($qty > intval(Configuration::get('PS_LAST_QTIES')))
			$this->sendCustomerAlert(intval($result['id_product']), intval($params['id_product_attribute']));
	}
	
	public function sendCustomerAlert($id_product, $id_product_attribute)
	{
		$customers = Db::getInstance()->ExecuteS('
		SELECT id_customer, customer_email
		FROM `'._DB_PREFIX_.'mailalert_customer_oos`
		WHERE `id_product` = '.intval($id_product).
		($id_product_attribute ? ' AND `id_product_attribute` = '.intval($id_product_attribute) : ''));
		
		$product =  new Product(intval($id_product));
		$templateVars = array(
			'{product}' => strval($product->name[intval(Configuration::get('PS_LANG_DEFAULT'))])
		);
		foreach ($customers as $cust)
		{
			if ($cust['id_customer'])
			{
				$customer = new Customer(intval($cust['id_customer']));
				$customer_email = $customer->email;
				$customer_id = $customer->id;
			}
			else
			{
				$customer_email = $cust['customer_email'];
				$customer_id = 0;
			}
			Mail::Send(intval(Configuration::get('PS_LANG_DEFAULT')), 'customer_qty', $this->l('Product available'), $templateVars, strval($customer_email), NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
			self::deleteAlert(intval($customer_id), strval($customer_email), intval($id_product), intval($id_product_attribute));
		}
	}
	
	public function hookCustomerAccount($params)
	{
		global $smarty;
		return $this->display(__FILE__, 'my-account.tpl');
	}
	
	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		$this->_postProcess();
		$this->_displayForm();
		return $this->_html;
	}

    private function _displayForm()
    {
		$tab = Tools::getValue('tab');
		$currentIndex = __PS_BASE_URI__.substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__)).($tab ? '?tab='.$tab : '');
		$token = Tools::getValue('token');
		
        $this->_html .= '
		<form action="'.$currentIndex.'&token='.$token.'&configure=mailalerts" method="post">
			<fieldset class="width3"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Customer notification').'</legend>
				<label>'.$this->l('Product availability:').' </label>
				<div class="margin-form">
					<input type="checkbox" value="1" id="mA_customer_qty" name="mA_customer_qty" '.(Tools::getValue('mA_customer_qty', $this->_customer_qty) == 1 ? 'checked' : '').'>
					&nbsp;<label for="mA_customer_qty" class="t">'.$this->l('Gives the customer the possibility to receive a notification for an available product if this one is out of stock ').'</label>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitMACustomer" class="button" />
				</div>
			</fieldset>
		</form>
		<br />
		<form action="'.$currentIndex.'&token='.$token.'&configure=mailalerts" method="post">
			<fieldset class="width3"><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Merchant notification').'</legend>
				<label>'.$this->l('New order:').' </label>
				<div class="margin-form">
					<input type="checkbox" value="1" id="mA_merchand_order" name="mA_merchand_order" '.(Tools::getValue('mA_merchand_order', $this->_merchant_order) == 1 ? 'checked' : '').'>
					&nbsp;<label for="mA_merchand_order" class="t">'.$this->l('Receive a notification if a new order is made').'</label>
				</div>
				<label>'.$this->l('Out of stock:').' </label>
				<div class="margin-form">
					<input type="checkbox" value="1" id="mA_merchand_oos" name="mA_merchand_oos" '.(Tools::getValue('mA_merchand_oos', $this->_merchant_oos) == 1 ? 'checked' : '').'>
					&nbsp;<label for="mA_merchand_oos" class="t">'.$this->l('Receive a notification if a product is out of stock').'</label>
				</div>
				<label>'.$this->l('Send to this emails:').' </label>
				<div class="margin-form">
					<div style="float:left; margin-right:10px;">
						<textarea name="mA_merchant_mails" rows="10" cols="30">'.Tools::getValue('mA_merchant_mails', str_replace(self::__MA_MAIL_DELIMITOR__, "\n", $this->_merchant_mails)).'</textarea>
					</div>
					<div style="float:left;">
						'.$this->l('One email address per line').'<br />
						'.$this->l('e.g.,').' bob@example.com
					</div>
				</div>
				<div style="clear:both;">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitMAMerchant" class="button" />
				</div>
			</fieldset>
		</form>';
	}
	
	private function _postProcess()
	{
		if (Tools::isSubmit('submitMACustomer'))
		{
			if (!Configuration::updateValue('MA_CUSTOMER_QTY', intval(Tools::getValue('mA_customer_qty'))))
				$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		elseif (Tools::isSubmit('submitMAMerchant'))
		{
			$emails = strval(Tools::getValue('mA_merchant_mails'));
			if (!$emails OR empty($emails))
				$this->_html .= '<div class="alert error">'.$this->l('Please type one (or more) email address').'</div>';
			else
			{
				$emails = explode("\n", $emails);
				foreach ($emails AS $k => $email)
				{
					$email = trim($email);
					if (!empty($email) AND !Validate::isEmail($email))
						return ($this->_html .= '<div class="alert error">'.$this->l('Invalid e-mail:').' '.$email.'</div>');
					if (!empty($email) AND sizeof($email))
						$emails[$k] = $email;
					else
						unset($emails[$k]);
				}
				$emails = implode(self::__MA_MAIL_DELIMITOR__, $emails);
				if (!Configuration::updateValue('MA_MERCHANT_MAILS', strval($emails)))
					$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
				elseif (!Configuration::updateValue('MA_MERCHANT_ORDER', intval(Tools::getValue('mA_merchand_order'))))
					$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
				elseif (!Configuration::updateValue('MA_MERCHANT_OOS', intval(Tools::getValue('mA_merchand_oos'))))
					$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
				else
					$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}
		$this->_refreshProperties();
	}
	
	static public function getProductsAlerts($id_customer, $id_lang)
	{
		if (
			!Validate::isUnsignedId($id_customer) OR
			!Validate::isUnsignedId($id_lang))
			die (Tools::displayError());
		$products = Db::getInstance()->ExecuteS('
		SELECT ma.`id_product`, p.`quantity` AS product_quantity, pl.`name`, ma.`id_product_attribute`
		FROM `'._DB_PREFIX_.'mailalert_customer_oos` ma
		JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = ma.`id_product`
		JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.`id_product` = ma.`id_product`
		WHERE ma.`id_customer` = '.intval($id_customer).'
		AND pl.`id_lang` = '.intval($id_lang));
		if (empty($products) === true OR !sizeof($products))
			return array();
		for ($i = 0; $i < sizeof($products); ++$i)
		{
			$obj = new Product(intval($products[$i]['id_product']), false, intval($id_lang));
			if (!Validate::isLoadedObject($obj))
				continue;
			
			if (isset($products[$i]['id_product_attribute']) AND
				Validate::isUnsignedInt($products[$i]['id_product_attribute']))
			{
				$result = Db::getInstance()->ExecuteS('
				SELECT al.`name` AS attribute_name
				  FROM `'._DB_PREFIX_.'product_attribute_combination` pac
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.intval($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.intval($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
				WHERE pac.`id_product_attribute` = '.intval($products[$i]['id_product_attribute']));
				$products[$i]['attributes_small'] = '';
				if ($result)
					foreach ($result AS $k => $row)
						$products[$i]['attributes_small'] .= $row['attribute_name'].', ';
				$products[$i]['attributes_small'] = rtrim($products[$i]['attributes_small'], ', ');
				
				// cover
				$attrgrps = $obj->getAttributesGroups(intval($id_lang));
				foreach ($attrgrps AS $attrgrp)
					if ($attrgrp['id_product_attribute'] == intval($products[$i]['id_product_attribute']) AND $attrgrp['id_image'])
					{
						$products[$i]['cover'] = $obj->id.'-'.$attrgrp['id_image'];
						break;
					}
			}
			if (!isset($products[$i]['cover']) OR !$products[$i]['cover'])
			{
				$images = $obj->getImages(intval($id_lang));
				foreach ($images AS $k => $image)
					if ($image['cover'])
					{
						$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
						break;
					}
			}
			if (!isset($products[$i]['cover']))
				$products[$i]['cover'] = Language::getIsoById($id_lang).'-default';
			$products[$i]['link'] = $obj->getLink();
		}
		return ($products);
	}
	
	static public function deleteAlert($id_customer, $customer_email, $id_product, $id_product_attribute)
	{
		$query = 'DELETE FROM `'._DB_PREFIX_.'mailalert_customer_oos` 
		WHERE `id_customer` = '.intval($id_customer).'
		AND `customer_email` = '.strval($customer_email).'
		AND `id_product` = '.intval($id_product).'
		AND `id_product_attribute` = '.intval($id_product_attribute).';';
		return Db::getInstance()->Execute($query);
	}
}

?>