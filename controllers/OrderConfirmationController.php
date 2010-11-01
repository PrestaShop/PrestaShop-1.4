<?php

class OrderConfirmationControllerCore extends FrontController
{
	public $id_cart;
	public $id_module;
	public $id_order;
	public $secure_key;
	
	public function preProcess()
	{
		parent::preProcess();
		
		$this->id_cart = intval(Tools::getValue('id_cart', 0));
		$this->id_module = intval(Tools::getValue('id_module', 0));
		$this->id_order = Order::getOrderByCartId(intval($id_cart));
		$this->secure_key = Tools::getValue('key', false);
		if (!$this->id_order OR !$this->id_module OR !$this->secure_key OR empty($this->secure_key))
			Tools::redirect('history.php'.(Tools::isSubmit('slowvalidation') ? '?slowvalidation' : ''));

		$order = new Order(intval($this->id_order));
		if (!Validate::isLoadedObject($order) OR $order->id_customer != $this->cookie->id_customer OR $this->secure_key != $order->secure_key)
			Tools::redirect('history.php');
		$module = Module::getInstanceById(intval($this->id_module));
		if ($order->payment != $module->displayName)
			Tools::redirect('history.php');
	}
	
	public function process()
	{
		parent::process();
		$this->smarty->assign(array(
			'HOOK_ORDER_CONFIRMATION' => Hook::orderConfirmation(intval($this->id_order)),
			'HOOK_PAYMENT_RETURN' => Hook::paymentReturn(intval($this->id_order), intval($this->id_module))
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'order-confirmation.tpl');
	}
}