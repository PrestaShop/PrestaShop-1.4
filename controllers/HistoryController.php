<?php

class HistoryControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'history.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'history.css');
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.scrollto.js', _THEME_JS_DIR_.'history.js'));
	}
	
	public function process()
	{
		parent::process();
		
		if ($orders = Order::getCustomerOrders((int)($this->cookie->id_customer)))
			foreach ($orders AS &$order)
			{
				$myOrder = new Order((int)($order['id_order']));
				if (Validate::isLoadedObject($myOrder))
					$order['virtual'] = $myOrder->isVirtual(false);
			}
		$this->smarty->assign(array(
			'orders' => $orders,
			'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
			'slowValidation' => Tools::isSubmit('slowvalidation')
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'history.tpl');
	}
}

?>