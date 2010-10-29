<?php

class NewProductsControllerCore extends FrontController
{
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');
	}
	
	public function process()
	{
		parent::process();
		
		$this->productSort();

		$nbProducts = intval(Product::getNewProducts(intval($this->cookie->id_lang), isset($p) ? intval($p) - 1 : NULL, isset($n) ? intval($n) : NULL, true));
		
		$this->pagination();

		$smarty->assign(array(
			'products' => Product::getNewProducts(intval($this->cookie->id_lang), intval($p) - 1, intval($n), false, $orderBy, $orderWay),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'nbProducts' => intval($nbProducts)
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'new-products.tpl');
	}
}