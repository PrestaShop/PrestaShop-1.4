<?php

class DiscountControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'discount.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function process()
	{
		parent::process();
		
		$discounts = Discount::getCustomerDiscounts((int)($this->cookie->id_lang), (int)($this->cookie->id_customer), true, false);
		$nbDiscounts = 0;
		foreach ($discounts AS $discount)
			if ($discount['quantity_for_user'])
				$nbDiscounts++;

		$this->smarty->assign(array('nbDiscounts' => (int)($nbDiscounts), 'discount' => $discounts));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'discount.tpl');
	}
}

