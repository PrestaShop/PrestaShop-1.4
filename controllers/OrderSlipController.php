<?php

class OrderSlipControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'order-slip.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.scrollto.js',_THEME_JS_DIR_.'history.js'));
	}
	
	public function process()
	{
		parent::process();
		$this->smarty->assign('ordersSlip', OrderSlip::getOrdersSlip(intval($this->cookie->id_customer)));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'order-slip.tpl');
	}
}