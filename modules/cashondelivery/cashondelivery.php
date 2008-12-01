<?php

class CashOnDelivery extends PaymentModule
{	
	function __construct()
	{
		$this->name = 'cashondelivery';
		$this->tab = 'Payment';
		$this->version = 0.1;
		
		$this->currencies = false;

		parent::__construct();

		/* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Cash on delivery (COD)');
		$this->description = $this->l('Accept cash on delivery payments');
	}

	function install()
	{
        parent::install();
		$this->registerHook('payment');
		$this->registerHook('paymentReturn');
	}

	function hookPayment($params)
	{
		global $smarty;
		
		foreach ($params['cart']->getProducts() AS $product)
			if (Validate::isUnsignedInt(ProductDownload::getIdFromIdProduct(intval($product['id_product']))))
				return false;
		
		$smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
            ));
		return $this->display(__FILE__, 'payment.tpl');
	}
	
	function hookPaymentReturn($params)
	{
		return $this->display(__FILE__, 'confirmation.tpl');
	}
}

?>