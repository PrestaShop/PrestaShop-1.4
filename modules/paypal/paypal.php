<?php

class Paypal extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'paypal';
		$this->tab = 'Payment';
		$this->version = '1.6';
		
		$this->currencies = true;
		$this->currencies_mode = 'radio';

        parent::__construct();

		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('PayPal');
        $this->description = $this->l('Accepts payments by PayPal');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
		if (Configuration::get('PAYPAL_BUSINESS') == 'paypal@prestashop.com')
			$this->warning = $this->l('You are currently using the default PayPal email address, you need to use your own email address');
		if ($_SERVER['SERVER_NAME'] == 'localhost')
			$this->warning = $this->l('Your are in localhost, PayPal we can\'t validate order');
	}

	public function getPaypalUrl()
	{
			return Configuration::get('PAYPAL_SANDBOX') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	}

	public function install()
	{
		if (!parent::install()
			OR !Configuration::updateValue('PAYPAL_BUSINESS', 'paypal@prestashop.com')
			OR !Configuration::updateValue('PAYPAL_SANDBOX', 1)
			OR !$this->registerHook('payment')
			OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYPAL_BUSINESS')
			OR !Configuration::deleteByName('PAYPAL_SANDBOX')
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
				Configuration::updateValue('PAYPAL_BUSINESS', strval($_POST['business']));
				Configuration::updateValue('PAYPAL_SANDBOX', intval($_POST['sandbox']));
				Configuration::updateValue('PAYPAL_HEADER', strval($_POST['header']));
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
		<div style="float: right; width: 440px; height: 150px; border: dashed 1px #666; padding: 8px; margin-left: 12px;">
			<h2>'.$this->l('Opening your PayPal account').'</h2>
			<div style="clear: both;"></div>
			<p>'.$this->l('By opening your PayPal account by clicking on the following image you are helping us significantly to improve the PrestaShop software:').'</p>
			<p style="text-align: center;"><a href="https://www.paypal.com/fr/mrb/pal=TWJHHUL9AEP9C"><img src="../modules/paypal/prestashop_paypal.png" alt="PrestaShop & PayPal" style="margin-top: 12px;" /></a></p>
			<div style="clear: right;"></div>
		</div>
		<img src="../modules/paypal/paypal.gif" style="float:left; margin-right:15px;" />
		<b>'.$this->l('This module allows you to accept payments by PayPal.').'</b><br /><br />
		'.$this->l('If the client chooses this payment mode, your PayPal account will be automatically credited.').'<br />
		'.$this->l('You need to configure your PayPal account first before using this module.').'
		<div style="clear:both;">&nbsp;</div>';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('PAYPAL_BUSINESS', 'PAYPAL_SANDBOX', 'PAYPAL_HEADER'));
		$business = array_key_exists('business', $_POST) ? $_POST['business'] : (array_key_exists('PAYPAL_BUSINESS', $conf) ? $conf['PAYPAL_BUSINESS'] : '');
		$sandbox = array_key_exists('sandbox', $_POST) ? $_POST['sandbox'] : (array_key_exists('PAYPAL_SANDBOX', $conf) ? $conf['PAYPAL_SANDBOX'] : '');
		$header = array_key_exists('header', $_POST) ? $_POST['header'] : (array_key_exists('PAYPAL_HEADER', $conf) ? $conf['PAYPAL_HEADER'] : '');

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="clear: both;">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			<label>'.$this->l('PayPal business e-mail').'</label>
			<div class="margin-form"><input type="text" size="33" name="business" value="'.htmlentities($business, ENT_COMPAT, 'UTF-8').'" /></div>
			<label>'.$this->l('Sandbox mode (Test)').'</label>
			<div class="margin-form">
				<input type="radio" name="sandbox" value="1" '.($sandbox ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('Yes').'</label>
				<input type="radio" name="sandbox" value="0" '.(!$sandbox ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('No').'</label>
			</div>
			<label>'.$this->l('Banner image URL').'</label>
			<div class="margin-form"><input type="text" size="82" name="header" value="'.htmlentities($header, ENT_COMPAT, 'UTF-8').'" />
			<p class="hint clear" style="display: block; width: 501px;">'.$this->l('The image should be host on a securised server in order to avoid security warnings. Size should be limited at 750x90px.').'</p></div><br /><br /><br />
			<br /><center><input type="submit" name="submitPaypal" value="'.$this->l('Update settings').'" class="button" /></center>
		</fieldset>
		</form><br /><br />
		<fieldset class="width3">
			<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
			'.$this->l('In order to use your PayPal payment module, you have to configure your PayPal account (sandbox account as well as live account). Log in to PayPal and follow these instructions.').'<br /><br />
			'.$this->l('In').' <i>'.$this->l('My account > Profile > Website Payment Preferences').'</i>, '. $this->l('set:').'<br />
			- <b>'.$this->l('Auto Return').'</b> : '.$this->l('Off').',<br />
			- <b>'.$this->l('Payment Data Transfer').'</b> '.$this->l('to').' <b>Off</b>.<br /><br />
			'.$this->l('In').' <i>'.$this->l('My account > Profile > Shipping Calculations').'</i><br />
			- check <b>'.$this->l('Click here to allow transaction-based shipping values to override the profile shipping settings listed above').'</b><br /><br />
			<b style="color: red;">'.$this->l('All PrestaShop currencies must be also configured</b> inside Profile > Financial Information > Currency balances').'</b><br />
		</fieldset>';
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;

		return $this->display(__FILE__, 'paypal.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;

		return $this->display(__FILE__, 'confirmation.tpl');
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

	function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false)
	{
		if (!$this->active)
			return ;

		parent::validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars);
	}
}
