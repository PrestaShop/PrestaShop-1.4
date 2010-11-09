<?php

class OrderDetailControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'history.php';
		$this->ssl = true;
		
		parent::__construct();
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	}
	
	public function preProcess()
	{
		parent::preProcess();

		if (Tools::isSubmit('submitMessage'))
		{
			$idOrder = intval(Tools::getValue('id_order'));
			$msgText = htmlentities(Tools::getValue('msgText'), ENT_COMPAT, 'UTF-8');

			if (!$idOrder OR !Validate::isUnsignedId($idOrder))
				$this->errors[] = Tools::displayError('order is no longer valid');
			elseif (empty($msgText))
				$this->errors[] = Tools::displayError('message cannot be blank');
			elseif (!Validate::isMessage($msgText))
				$this->errors[] = Tools::displayError('message is not valid (HTML is not allowed)');
			if(!sizeof($this->errors))
			{
				$order = new Order(intval($idOrder));
				if (Validate::isLoadedObject($order) AND $order->id_customer == $this->cookie->id_customer)
				{
					$message = new Message();
					$message->id_customer = intval($this->cookie->id_customer);
					$message->message = $msgText;
					$message->id_order = intval($idOrder);
					$message->private = false;
					$message->add();
					if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE'))
						$to = strval(Configuration::get('PS_SHOP_EMAIL'));
					else
					{
						$to = new Contact(intval(Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
						$to = strval($to->email);
					}
					$toName = strval(Configuration::get('PS_SHOP_NAME'));
					$customer = new Customer(intval($this->cookie->id_customer));
					if (Validate::isLoadedObject($customer))
						Mail::Send(intval($this->cookie->id_lang), 'order_customer_comment', Mail::l('Message from a customer'),
						array(
						'{lastname}' => $customer->lastname, 
						'{firstname}' => $customer->firstname, 
						'{id_order}' => intval($message->id_order), 
						'{message}' => $message->message),
						$to, $toName, $customer->email, $customer->firstname.' '.$customer->lastname);
					if (Tools::getValue('ajax') != 'true')
						Tools::redirect('order-detail.php?id_order='.intval($idOrder));
				}
				else
				{
					$this->errors[] = Tools::displayError('order not found');
				}
			}
		}

		if (!$id_order = intval(Tools::getValue('id_order')) OR !Validate::isUnsignedId($id_order))
			$this->errors[] = Tools::displayError('order ID is required');
		else
		{
			$order = new Order($id_order);
			if (Validate::isLoadedObject($order) AND $order->id_customer == $this->cookie->id_customer)
			{
				$id_order_state = intval($order->getCurrentState());
				$carrier = new Carrier(intval($order->id_carrier), intval($order->id_lang));
				$addressInvoice = new Address(intval($order->id_address_invoice));
				$addressDelivery = new Address(intval($order->id_address_delivery));
				if ($order->total_discounts > 0)
					$this->smarty->assign('total_old', floatval($order->total_paid - $order->total_discounts));
				$products = $order->getProducts();
				$customizedDatas = Product::getAllCustomizedDatas(intval($order->id_cart));
				Product::addCustomizationPrice($products, $customizedDatas);

				$this->smarty->assign(array(
					'shop_name' => strval(Configuration::get('PS_SHOP_NAME')),
					'order' => $order,
					'return_allowed' => intval($order->isReturnable()),
					'currency' => new Currency($order->id_currency),
					'order_state' => intval($id_order_state),
					'invoiceAllowed' => intval(Configuration::get('PS_INVOICE')),
					'invoice' => (OrderState::invoiceAvailable(intval($id_order_state)) AND $order->invoice_number),
					'order_history' => $order->getHistory(intval($this->cookie->id_lang), false, true),
					'products' => $products,
					'discounts' => $order->getDiscounts(),
					'carrier' => $carrier,
					'address_invoice' => $addressInvoice,
					'invoiceState' => (Validate::isLoadedObject($addressInvoice) AND $addressInvoice->id_state) ? new State(intval($addressInvoice->id_state)) : false,
					'address_delivery' => $addressDelivery,
					'deliveryState' => (Validate::isLoadedObject($addressDelivery) AND $addressDelivery->id_state) ? new State(intval($addressDelivery->id_state)) : false,
					'messages' => Message::getMessagesByOrderId(intval($order->id)),
					'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
					'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
					'use_tax' => Configuration::get('PS_TAX'),
					'HOOK_ORDERDETAILDISPLAYED' => Module::hookExec('orderDetailDisplayed', array('order' => $order)),
					'customizedDatas' => $customizedDatas));
				if ($carrier->url AND $order->shipping_number)
					$this->smarty->assign('followup', str_replace('@', $order->shipping_number, $carrier->url));
				Module::hookExec('OrderDetail', array('carrier' => $carrier, 'order' => $order));
			}
			else
				$this->errors[] = Tools::displayError('cannot find this order');
		}
	}
	
	public function displayHeader()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayHeader();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'order-detail.tpl');
	}
	
	public function displayFooter()
	{
		if (Tools::getValue('ajax') != 'true')
			parent::displayFooter();
	}
}

?>