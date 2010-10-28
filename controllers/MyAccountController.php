<?php

class MyAccountControllerCore extends FrontController
{
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'my-account.css');
	}
	
	public function process()
	{
		parent::process();
		
		$this->smarty->assign(array(
			'voucherAllowed' => intval(Configuration::get('PS_VOUCHERS')),
			'returnAllowed' => intval(Configuration::get('PS_ORDER_RETURN')),
			'HOOK_CUSTOMER_ACCOUNT' => Module::hookExec('customerAccount')
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'my-account.tpl');
	}
}