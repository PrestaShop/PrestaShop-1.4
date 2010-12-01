<?php

class OrderReturnControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'order-follow.php';
		$this->ssl = true;
		
		parent::__construct();
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	}

	public function preProcess()
	{
		parent::preProcess();
		
		if (!isset($_GET['id_order_return']) OR !Validate::isUnsignedId($_GET['id_order_return']))
			$this->errors[] = Tools::displayError('order ID is required');
		else
		{
			$orderRet = new OrderReturn((int)($_GET['id_order_return']));
			if (Validate::isLoadedObject($orderRet) AND $orderRet->id_customer == $this->cookie->id_customer)
			{
				$order = new Order((int)($orderRet->id_order));
				if (Validate::isLoadedObject($order))
				{
					$state = new OrderReturnState((int)($orderRet->state));
					$this->smarty->assign(array(
						'orderRet' => $orderRet,
						'order' => $order,
						'state_name' => $state->name[(int)($this->cookie->id_lang)],
						'return_allowed' => false,
						'products' => OrderReturn::getOrdersReturnProducts((int)($orderRet->id), $order),
						'returnedCustomizations' => OrderReturn::getReturnedCustomizedProducts((int)($orderRet->id_order)),
						'customizedDatas' => Product::getAllCustomizedDatas((int)($order->id_cart))
					));
				}
				else
					$this->errors[] = Tools::displayError('cannot find this order return');
			}
			else
				$this->errors[] = Tools::displayError('cannot find this order return');
		}

		$this->smarty->assign(array(
			'errors' => $this->errors,
			'nbdaysreturn' => (int)(Configuration::get('PS_ORDER_RETURN_NB_DAYS'))
		));
	}
	
	public function displayHeader()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayHeader();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'order-return.tpl');
	}
	
	public function displayFooter()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayFooter();
	}
}

