<?php

class OrderFollowControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'order-follow.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function preProcess()
	{
		parent::preProcess();
		
		if (Tools::isSubmit('submitReturnMerchandise'))
		{
			if (!$id_order = intval(Tools::getValue('id_order')))
				Tools::redirect('history.php');
			if (!$order_qte_input = Tools::getValue('order_qte_input'))
				Tools::redirect('order-follow.php?errorDetail1');
			if ($customizationIds = Tools::getValue('customization_ids') AND !$customizationQtyInput = Tools::getValue('customization_qty_input'))
				Tools::redirect('order-follow.php?errorDetail1');
			if (!$ids_order_detail = Tools::getValue('ids_order_detail') AND !$customizationIds)
				Tools::redirect('order-follow.php?errorDetail2');

			$order = new Order(intval($id_order));
			if (!$order->isReturnable()) Tools::redirect('order-follow.php?errorNotReturnable');
			if ($order->id_customer != $this->cookie->id_customer)
				die(Tools::displayError());
			$orderReturn = new OrderReturn();
			$orderReturn->id_customer = intval($this->cookie->id_customer);
			$orderReturn->id_order = $id_order;
			$orderReturn->question = strval(Tools::getValue('returnText'));
			if (empty($orderReturn->question))
				Tools::redirect('order-follow.php?errorMsg');
			if (!$orderReturn->checkEnoughProduct($ids_order_detail, $order_qte_input, $customizationIds, $customizationQtyInput))
				Tools::redirect('order-follow.php?errorQuantity');

			$orderReturn->state = 1;
			$orderReturn->add();
			$orderReturn->addReturnDetail($ids_order_detail, $order_qte_input, $customizationIds, $customizationQtyInput);
			Module::hookExec('orderReturn', array('orderReturn' => $orderReturn));
			Tools::redirect('order-follow.php');
		}

		$ordersReturn = OrderReturn::getOrdersReturn(intval($this->cookie->id_customer));
		if (Tools::isSubmit('errorQuantity'))
			$this->smarty->assign('errorQuantity', true);
		elseif (Tools::isSubmit('errorMsg'))
			$this->smarty->assign('errorMsg', true);
		elseif (Tools::isSubmit('errorDetail1'))
			$this->smarty->assign('errorDetail1', true);
		elseif (Tools::isSubmit('errorDetail2'))
			$this->smarty->assign('errorDetail2', true);
		elseif (Tools::isSubmit('errorNotReturnable'))
			$this->smarty->assign('errorNotReturnable',true);

		$this->smarty->assign('ordersReturn', $ordersReturn);
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.scrollto.js', _THEME_JS_DIR_.'history.js'));
	}

	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'order-follow.tpl');
	}
}

?>