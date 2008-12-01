<?php

class Paypal extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'paypal';
		$this->tab = 'Payment';
		$this->version = '1.4';
		
		$this->currencies = true;
		$this->currencies_mode = 'radio';

        parent::__construct();

        /* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('PayPal');
        $this->description = $this->l('Accepts payments by PayPal');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	public function getPaypalUrl()
	{
			return Configuration::get('PAYPAL_SANDBOX') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	}

	public function install()
	{
		if (!parent::install() OR !Configuration::updateValue('PAYPAL_BUSINESS', 'paypal@prestashop.com')
			OR !Configuration::updateValue('PAYPAL_SANDBOX', 1) OR !$this->registerHook('payment'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYPAL_BUSINESS') OR !Configuration::deleteByName('PAYPAL_SANDBOX')
			OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>Paypal</h2>';
		if (isset($_POST['submitPaypal']))
		{
			if (empty($_POST['business']))
				$this->_postErrors[] = $this->l('Paypal business e-mail address is required.');
			elseif (!Validate::isEmail($_POST['business']))
				$this->_postErrors[] = $this->l('Paypal business must be an e-mail address.');
			if (!isset($_POST['sandbox']))
				$_POST['sandbox'] = 1;
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('PAYPAL_BUSINESS', $_POST['business']);
				Configuration::updateValue('PAYPAL_SANDBOX', intval($_POST['sandbox']));
				$this->displayConf();
			}
			else
				$this->displayErrors();
		}

		$this->displayPayPal();
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}
	
	
	public function displayPayPal()
	{
		$this->_html .= '
		<img src="../modules/paypal/paypal.gif" style="float:left; margin-right:15px;" />
		<b>'.$this->l('This module allows you to accept payments by PayPal.').'</b><br /><br />
		'.$this->l('If the client chooses this payment mode, your PayPal account will be automatically credited.').'<br />
		'.$this->l('You need to configure your PayPal account first before using this module.').'
		<br /><br /><br />';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('PAYPAL_BUSINESS', 'PAYPAL_SANDBOX'));
		$business = array_key_exists('business', $_POST) ? $_POST['business'] : (array_key_exists('PAYPAL_BUSINESS', $conf) ? $conf['PAYPAL_BUSINESS'] : '');
		$sandbox = array_key_exists('sandbox', $_POST) ? $_POST['sandbox'] : (array_key_exists('PAYPAL_SANDBOX', $conf) ? $conf['PAYPAL_SANDBOX'] : '');

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			<label>'.$this->l('PayPal business e-mail').'</label>
			<div class="margin-form"><input type="text" size="33" name="business" value="'.htmlentities($business, ENT_COMPAT, 'UTF-8').'" /></div>
			<label>'.$this->l('Sandbox mode').'</label>
			<div class="margin-form">
				<input type="radio" name="sandbox" value="1" '.($sandbox ? 'checked="checked"' : '').' /> '.$this->l('Yes').'
				<input type="radio" name="sandbox" value="0" '.(!$sandbox ? 'checked="checked"' : '').' /> '.$this->l('No').'
			</div>
			<br /><center><input type="submit" name="submitPaypal" value="'.$this->l('Update settings').'" class="button" /></center>
		</fieldset>
		</form><br /><br />
		<fieldset class="width3">
			<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
			'.$this->l('In order to use your PayPal payment module, you have to configure your PayPal account (sandbox account as well as live account). Log in to PayPal and follow these instructions.').'<br /><br />
			'.$this->l('In').' <i>'.$this->l('Profile > Selling Preferences > Website Payment Preferences').'</i>, '. $this->l('set:').'<br />
			- <b>'.$this->l('Auto Return').'</b> : '.$this->l('Off').',<br />
			- <b>'.$this->l('Payment Data Transfer').'</b> '.$this->l('to').' <b>Off</b>.<br /><br />
			'.$this->l('In').' <i>'.$this->l('Profile > Selling Preferences > Shipping Calculations:').'</i><br />
			- check <b>'.$this->l('Click here to allow transaction-based shipping values to override the profile shipping settings listed above').'</b><br /><br />
			<b style="color: red;">'.$this->l('All PrestaShop currencies must be also configured</b> inside Profile > Financial Information > Currency balances').'<br />
		</fieldset>';
	}

	public function hookPayment($params)
	{
		global $smarty;

		$address = new Address(intval($params['cart']->id_address_invoice));
		$customer = new Customer(intval($params['cart']->id_customer));
		$business = Configuration::get('PAYPAL_BUSINESS');
		$currency = $this->getCurrency();

		if (!Validate::isEmail($business))
			return $this->l('Paypal error: (invalid or undefined business account email)');

		if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency))
			return $this->l('Paypal error: (invalid address or customer)');
			
		$products = $params['cart']->getProducts();
		
		foreach ($products as $key => $product)
		{
			$products[$key]['name'] = str_replace('"', '\'', $product['name']);
			if (isset($product['attributes']))
				$products[$key]['attributes'] = str_replace('"', '\'', $product['attributes']);
			$products[$key]['name'] = htmlentities(utf8_decode($product['name']));
			$products[$key]['paypalAmount'] = number_format(Tools::convertPrice($product['price_wt'], $currency), 2, '.', '');
		}
		$smarty->assign(array(
			'address' => $address,
			'country' => new Country(intval($address->id_country)),
			'customer' => $customer,
			'business' => $business,
			'currency' => $currency,
			'paypalUrl' => $this->getPaypalUrl(),
			'amount' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 4), $currency), 2, '.', ''),
			'shipping' =>  number_format(Tools::convertPrice(($params['cart']->getOrderShippingCost() + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', ''),
			'discounts' => $params['cart']->getDiscounts(),
			'products' => $products,
			'total' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', ''),
			'id_cart' => intval($params['cart']->id),
			'goBackUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id),
			'returnUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/paypal/validation.php',
			'this_path' => $this->_path
		));

		return $this->display(__FILE__, 'paypal.tpl');
    }
	
	public function getL($key)
	{
		$translations = array(
			'mc_gross' => $this->l('Paypal key \'mc_gross\' not specified, can\'t control amount paid.'),
			'payment_status' => $this->l('Paypal key \'payment_status\' not specified, can\'t control payment validity'),
			'payment' => $this->l('Payment: '),
			'custom' => $this->l('Paypal key \'custom\' not specified, can\'t rely to cart'),
			'txn_id' => $this->l('Paypal key \'txn_id\' not specified, transaction unknown'),
			'mc_currency' => $this->l('Paypal key \'mc_currency\' not specified, currency unknown'),
			'cart' => $this->l('Cart not found'),
			'order' => $this->l('Order has already been placed'),
			'transaction' => $this->l('Paypal Transaction ID: '),
			'verified' => $this->l('The PayPal transaction could not be VERIFIED.'),
			'connect' => $this->l('Problem connecting to the PayPal server.'),
			'nomethod' => $this->l('No communications transport available.'),
			'socketmethod' => $this->l('Verification failure (using fsockopen). Returned: '),
			'curlmethod' => $this->l('Verification failure (using cURL). Returned: '),
			'curlmethodfailed' => $this->l('Connection using cURL failed'),
		);
		return $translations[$key];
	}
}

?>
