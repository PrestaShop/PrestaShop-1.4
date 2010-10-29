<?php

class AddressesControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'addresses.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
	}
	
	public function process()
	{
		parent::process();
		
		$customer = new Customer(intval($this->cookie->id_customer));
		if (!Validate::isLoadedObject($customer))
			die(Tools::displayError('customer not found'));
		$this->smarty->assign('addresses', $customer->getAddresses(intval($this->cookie->id_lang)));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'addresses.tpl');
	}
}