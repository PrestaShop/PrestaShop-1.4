<?php

class MailAlerts extends Module
{
 	private $_html = '';
    private $_postErrors = array();
    private $_postSucess;
 	private $_data;

 	private $_mails;
 	private $_alertNewOrder;
 	private $_alertUpdateQuantity;
	private $_files = array();

 	const __MA_MAIL_DELIMITOR__ = ',';


	public function __construct()
	{
		$this->name = 'mailalerts';
        $this->tab = 'Tools';
		$this->version = 1.1;

		$this->_mails =  Configuration::get('MA_MAILS');
		$this->_alertNewOrder = intval(Configuration::get('MA_NEW_ORDER'));
		$this->_alertUpdateQuantity = intval(Configuration::get('MA_UPDATE_QUANTITY'));

	 	parent::__construct();

	 	/* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
	 	$this->displayName = $this->l('Mail alerts');
		$this->description = $this->l('Sends e-mails to the merchant for new orders and stock notifications');
		$this->confirmUninstall = $this->l('Are you sure you want to delete all templates mails ?');
	}

   	public function install()
   	{
   	 	parent::install();
   	 	$this->registerHook('NewOrder');
   	 	$this->registerHook('UpdateQuantity');

		Configuration::updateValue('MA_NEW_ORDER', 1);
		Configuration::updateValue('MA_UPDATE_QUANTITY', 1);
		Configuration::updateValue('MA_MAILS', Configuration::get('PS_SHOP_EMAIL'));
	}

	public function uninstall()
	{
		Configuration::deleteByName('MA_MAILS');
		Configuration::deleteByName('MA_NEW_ORDER');
		Configuration::deleteByName('MA_UPDATE_QUANTITY');
		parent::uninstall();
	}

    public function hookNewOrder($params)
    {
		if (!$this->_alertNewOrder OR empty($this->_mails))
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
		$message = $order->getLastMessage();
		if (!$message OR empty($message))
			$message = $this->l('No message');

		$itemsTable = '';
		foreach ($params['cart']->getProducts() AS $key => $product)
		{
			$reduc = 0.0;
			$price = Tools::convertPrice(Product::getPriceStatic(intval($product['id_product']), false, ($product['id_product_attribute'] ? intval($product['id_product_attribute']) : NULL), 4), $currency);
			$price_wt = Tools::convertPrice(Product::getPriceStatic(intval($product['id_product']), true, ($product['id_product_attribute'] ? intval($product['id_product_attribute']) : NULL), 4), $currency);
			if (Tax::excludeTaxeOption())
			{
				$product['tax'] = 0;
				$product['rate'] = 0;
			}
			else
				$tax = Tax::getApplicableTax(intval($product['id_tax']), floatval($product['rate']));
			if ($product['quantity'] > 1 AND ($qtyD = QuantityDiscount::getDiscountFromQuantity($product['id_product'], $product['quantity'])))
			{
				$reduc = QuantityDiscount::getValue($price_wt, $qtyD->id_discount_type, $qtyD->value);
				$price -= $reduc / (1 + floatval($tax) / 100);
			}
			$itemsTable .=
				'<tr style="background-color:'.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
					<td style="padding:0.6em 0.4em;">'.$product['reference'].'</td>
					<td style="padding:0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '').'</strong></td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($price * ($tax + 100) / 100, $currency, false, false).'</td>
					<td style="padding:0.6em 0.4em; text-align:center;">'.intval($product['quantity']).'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice(intval($product['quantity']) * ($price * ($tax + 100) / 100), $currency, false, false).'</td>
				</tr>';
		}
		foreach ($params['cart']->getDiscounts() AS $discount)
		{
			$itemsTable .=
			'<tr style="background-color:#EBECEE;">
					<td colspan="4" style="padding:0.6em 0.4em; text-align:right;">'.$this->l('Voucher code:').' '.$objDiscount->name.'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">-'.Tools::displayPrice($value, $currency, false, false).'</td>
			</tr>';
		}

		// Filling-in vars for mail
		$template = 'new_order';
		$subject = $this->l('New order');
		$templateVars = array(
			'{firstname}' => $customer->firstname,
			'{lastname}' => $customer->lastname,
			'{email}' => $customer->email,
			'{delivery_firstname}' => $delivery->firstname,
			'{delivery_lastname}' => $delivery->lastname,
			'{delivery_address1}' => $delivery->address1,
			'{delivery_address2}' => $delivery->address2,
			'{delivery_city}' => $delivery->city,
			'{delivery_postal_code}' => $delivery->postcode,
			'{delivery_country}' => $delivery->country,
			'{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
			'{delivery_phone}' => $delivery->phone,
			'{invoice_firstname}' => $invoice->firstname,
			'{invoice_lastname}' => $invoice->lastname,
			'{invoice_address2}' => $invoice->address2,
			'{invoice_address1}' => $invoice->address1,
			'{invoice_city}' => $invoice->city,
			'{invoice_postal_code}' => $invoice->postcode,
			'{invoice_country}' => $invoice->country,
			'{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
			'{invoice_phone}' => $invoice->phone,
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
		Mail::Send($id_lang, $template, $subject, $templateVars, split(',', $this->_mails), NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/mails/');
	}

	public function hookUpdateQuantity($params)
	{
		$qty = intval($params['product']['quantity_attribute'] ? $params['product']['quantity_attribute'] : $params['product']['stock_quantity']) - intval($params['product']['quantity']);
		if ($qty <= intval(Configuration::get('PS_LAST_QTIES')) AND !(!$this->_alertUpdateQuantity OR empty($this->_mails)))
		{
			$templateVars = array('{qty}' => $qty,
			'{last_qty}' => intval(Configuration::get('PS_LAST_QTIES')),
			'{product}' => strval($params['product']['name']));
			Mail::Send(intval(Configuration::get('PS_LANG_DEFAULT')), 'productoutofstock', $this->l('Product out of stock'), $templateVars, split(',', $this->_mails), NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
		}
	}

	public function getContent()
	{
	  	$this->_html = '<h2>'.$this->displayName.'</h2><br/>';

        if (!empty($_POST))
        {
		    $this->_postValidation();
	        if (!sizeof($this->_postErrors))
	           $this->_postProcess();
	        else
	            foreach ($this->_postErrors AS $err)
	                $this->_html .= '<div class="errmsg">'.$err.'</div>';
        }
       	$this->_displayForm();
        return $this->_html;
	}

    private function _displayForm()
    {
        if (!isset($_POST['btnSubmit']))
        {
            if ($this->_mails)
            {
             	$_POST['mails'] = str_replace(self::__MA_MAIL_DELIMITOR__, "\n", $this->_mails);
            	$_POST['alert_new_order'] = $this->_alertNewOrder;
            	$_POST['alert_update_quantity'] = $this->_alertUpdateQuantity;
			}
        }

        $button_txt = $this->l('Update settings');
        $this->_html .=
		'<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->l('Settings').'</legend>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<table id="form" cellspacing="5">
				<tr><td colspan="2"><h4><u>'.$this->l('Receiving alerts').'</u></h4></td></tr>
				 <tr>
					<td>'.$this->l('New order alert?').'</td>
					<td><input type="checkbox" value="1" name="alert_new_order" '.( (isset($_POST['alert_new_order']) AND $_POST['alert_new_order'] == '1') ? 'checked' : '').'></td>

				</tr>
				<tr>
					<td>'.$this->l('Inventory alert?').'</td>
					<td><input type="checkbox" value="1" name="alert_update_quantity" '.( (isset($_POST['alert_update_quantity']) AND $_POST['alert_update_quantity'] == '1') ? 'checked' : '').'></td>
				</tr>
				<tr><td colspan="2"><h4><u>'.$this->l('E-mail addresses').'</u></h4></td></tr>
				<tr><td style="vertical-align: top;">
				<span>'.$this->l('One e-mail per line').'<br />'.$this->l('E.g.: bob@example.com').'</span></a></td>
				<td>
					<textarea name="mails" rows="10" cols="30">'.(isset($_POST['mails']) ? $_POST['mails'] : '').'</textarea>
				</td></tr>

                <tr>
					<td colspan="2" align="center"><input name="btnSubmit" class="button" value="'.$button_txt.'" type="submit" /></td>
				</tr>
            </table>
        </form></fieldset>';
    }

	private function _postProcess()
	{
		$this->_alertNewOrder = isset($_POST['alert_new_order']) ? 1 : 0;
		$this->_alertUpdateQuantity = isset($_POST['alert_update_quantity']) ? 1 : 0;

		Configuration::updateValue('MA_NEW_ORDER', isset($_POST['alert_new_order']) ? 1 : 0);
		Configuration::updateValue('MA_UPDATE_QUANTITY', isset($_POST['alert_update_quantity']) ? 1 : 0);

        $mails = explode("\n", $_POST['mails']);
        $this->_mails = '';
		foreach ($mails as $mail)
			$this->_mails .= trim($mail).self::__MA_MAIL_DELIMITOR__;
		$this->_mails = trim($this->_mails, ',');
		Configuration::updateValue('MA_MAILS', $this->_mails);
        $this->_html .= '<div class="conf">'.$this->l('Settings updated').'</div>';
	}

	private function _postValidation()
	{
		if (!isset($_POST['mails']) OR empty($_POST['mails']))
			$this->_postErrors[] = $this->l('No e-mail addresses specified');
		else
		{
			$mails = explode("\n", $_POST['mails']);
			foreach ($mails as $mail)
			{
				$mail = trim($mail);
          		if (!empty($mail) AND !Validate::isEmail($mail))
					$this->_postErrors[]  = $this->l('Invalid e-mail: ').$mail.'.';
			}
		}
	}
}

?>
