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

		$nbProducts = intval(Product::getNewProducts(intval($this->cookie->id_lang), isset($this->p) ? intval($this->p) - 1 : NULL, isset($this->n) ? intval($this->n) : NULL, true));
		
		$this->pagination();

		$this->smarty->assign(array(
			'products' => Product::getNewProducts(intval($this->cookie->id_lang), intval($this->p) - 1, intval($this->n), false, $this->orderBy, $this->orderWay),
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