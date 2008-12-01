<?php

class GCheckout extends PaymentModule
{
    function __construct()
    {
        $this->name = 'gcheckout';
        $this->tab = 'Payment';
        $this->version = 1.0;
		
		$this->currencies = true;
		$this->currencies_mode = 'radio';

        parent::__construct();

		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Google Checkout');
        $this->description = $this->l('Google Checkout API implementation');
		
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency set for this module');
    }

    function install()
    {		
        if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn') OR !Configuration::updateValue('GCHECKOUT_MERCHANT_ID', '822305931131113') OR !Configuration::updateValue('GCHECKOUT_MERCHANT_KEY', '2Lv_osMomVIocnLK0aif3A') OR !Configuration::updateValue('GCHECKOUT_LOGS', '1') OR !Configuration::updateValue('GCHECKOUT_MODE', 'real'))
			return false;
		return true;
    }

    function uninstall()
    {
        return (
			parent::uninstall() AND
			Configuration::deleteByName('GCHECKOUT_MERCHANT_ID') AND
			Configuration::deleteByName('GCHECKOUT_MERCHANT_KEY') AND
			Configuration::deleteByName('GCHECKOUT_MODE') AND
			Configuration::deleteByName('GCHECKOUT_LOGS'));
    }
	
	function getContent()
	{
		global $currentIndex, $cookie;
		
		if (Tools::isSubmit('submitGoogleCheckout'))
		{
			$errors = array();
			if (($merchant_id = Tools::getValue('gcheckout_merchant_id')) AND preg_match('/[0-9]{15}/', $merchant_id))
				Configuration::updateValue('GCHECKOUT_MERCHANT_ID', $merchant_id);
			else
				$errors[] = '<div class="warning warn"><h3>'.$this->l('Merchant ID seems to be wrong').'</h3></div>';
			if (($merchant_key = Tools::getValue('gcheckout_merchant_key')) AND preg_match('/[a-zA-Z0-9_-]{22}/', $merchant_key))
				Configuration::updateValue('GCHECKOUT_MERCHANT_KEY', $merchant_key);
			else
				$errors[] = '<div class="warning warn"><h3>'.$this->l('Merchant key seems to be wrong').'</h3></div>';
			if ($mode = (Tools::getValue('gcheckout_mode') == 'real' ? 'real' : 'sandbox'))
				Configuration::updateValue('GCHECKOUT_MODE', $mode);
			if (Tools::getValue('gcheckout_logs'))
				Configuration::updateValue('GCHECKOUT_LOGS', 1);
			else
				Configuration::updateValue('GCHECKOUT_LOGS', 0);
			if (!sizeof($errors))
				Tools::redirectAdmin($currentIndex.'&configure=gcheckout&token='.Tools::getValue('token').'&conf=4');
			foreach ($errors as $error)
				echo $error;
		}
		
		$html = '<h2>'.$this->displayName.'</h2>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<legend><img src="'.__PS_BASE_URI__.'modules/gcheckout/logo.gif" />'.$this->l('Settings').'</legend>
				<p>'.$this->l('First use the sandbox to test out the module then you can use the real mode if everything\'s fine. Don\'t forget to change your merchant key and id according to the mode!').'</p>
				<label>
					'.$this->l('Mode').'
				</label>
				<div class="margin-form">
					<select name="gcheckout_mode">
						<option value="real"'.(Configuration::get('GCHECKOUT_MODE') == 'real' ? ' selected="selected"' : '').'>'.$this->l('Real').'&nbsp;&nbsp;</option>
						<option value="sandbox"'.(Configuration::get('GCHECKOUT_MODE') == 'sandbox' ? ' selected="selected"' : '').'>'.$this->l('Sandbox').'&nbsp;&nbsp;</option>
					</select>
				</div>
				<p>'.$this->l('You can find these keys in your Google Checkout account > Settings > Integration. Sandbox and real mode both have these keys.').'</p>
				<label>
					'.$this->l('Merchant ID').'
				</label>
				<div class="margin-form">
					<input type="text" name="gcheckout_merchant_id" value="'.Tools::getValue('gcheckout_merchant_id', Configuration::get('GCHECKOUT_MERCHANT_ID')).'" />
				</div>
				<label>
					'.$this->l('Merchant Key').'
				</label>
				<div class="margin-form">
					<input type="text" name="gcheckout_merchant_key" value="'.Tools::getValue('gcheckout_merchant_key', Configuration::get('GCHECKOUT_MERCHANT_KEY')).'" />
				</div>
				<p>'.$this->l('You can log the server-to-server communication. The log files are').' '.__PS_BASE_URI__.'modules/gcheckout/googleerror.log '.$this->l('and').' '.__PS_BASE_URI__.'modules/gcheckout/googlemessage.log. '.$this->l('If you do activate it, be sure to protect them by putting a .htaccess file in the same directory. If you forget to do so, they will be readable by everyone').'</p>
				<label>
					'.$this->l('Logs').'
				</label>
				<div class="margin-form" style="margin-top:5px">
					<input type="checkbox" name="gcheckout_logs"'.(Tools::getValue('gcheckout_logs', Configuration::get('GCHECKOUT_LOGS')) ? ' checked="checked"' : '').' />
				</div>
				<div class="clear center"><input type="submit" name="submitGoogleCheckout" class="button" value="'.$this->l('   Save   ').'" /></div>
			</fieldset>
		</form>
		<br /><br />
		<fieldset>
			<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
			<p>- '.$this->l('In order to use your Google Checkout module, you have to configure your Google Checkout account (sandbox account as well as live account). Log in to Google Checkout then go to Settings > Integration. The API callback URL is:').'<br />
				<b>http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/gcheckout/validation.php</b>
			</p>
			<p>- '.$this->l('The callback method must be set to').' <b>XML</b>.</p>
			<p>- '.$this->l('The orders must be placed with the same currency as your seller account. Carts in other currencies will be converted if the customer choose to pay with this module.').'<p>
		</fieldset>';
		
		return $html;
	}

	function hookPayment($params)
	{
		global $smarty;
		
		require_once('library/googlecart.php');
		require_once('library/googleitem.php');
		require_once('library/googleshipping.php');
		
		$currency = $this->getCurrency();
		$googleCart = new GoogleCart(Configuration::get('GCHECKOUT_MERCHANT_ID'), Configuration::get('GCHECKOUT_MERCHANT_KEY'), Configuration::get('GCHECKOUT_MODE'), $currency->iso_code);
		foreach ($params['cart']->getProducts() as $product)
			$googleCart->AddItem(new GoogleItem(utf8_decode($product['name']), utf8_decode($product['description_short']), intval($product['quantity']), number_format(Tools::convertPrice($product['price_wt'], $currency), 2, '.', '')));
		foreach ($params['cart']->getDiscounts() as $voucher)
			$googleCart->AddItem(new GoogleItem(utf8_decode($voucher['name']), utf8_decode($voucher['description']), 1, '-'.number_format(Tools::convertPrice($voucher['value_real'], $currency), 2, '.', '')));
		$googleCart->AddShipping(new GooglePickUp($this->l('Shipping costs'), number_format(Tools::convertPrice($params['cart']->getOrderShippingCost($params['cart']->id_carrier), $currency), 2, '.', '')));
		
		$googleCart->SetEditCartUrl('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order.php');      
		$googleCart->SetContinueShoppingUrl('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order-confirmation.php');  
		$googleCart->SetRequestBuyerPhone(false);

		$googleCart->SetMerchantPrivateData($params['cart']->id);

		$smarty->assign('CheckoutButtonCode', $googleCart->CheckoutButtonCode($this->l('Pay with GoogleCheckout'), 'LARGE'));
		$smarty->assign('ModulePath', $this->_path);

		return $this->display(__FILE__, 'payment.tpl');
	}
	
    function hookPaymentReturn($params)
    {
		return $this->display(__FILE__, 'payment_return.tpl');
    }
}

?>
