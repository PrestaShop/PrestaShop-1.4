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
		$nbProducts = (int)(Product::getNewProducts((int)($this->cookie->id_lang), isset($this->p) ? (int)($this->p) - 1 : NULL, isset($this->n) ? (int)($this->n) : NULL, true));
		$this->pagination($nbProducts);
		
		$this->smarty->assign(array(
			'products' => Product::getNewProducts((int)($this->cookie->id_lang), (int)($this->p) - 1, (int)($this->n), false, $this->orderBy, $this->orderWay),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'nbProducts' => (int)($nbProducts),
			'homeSize' => Image::getSize('home')	
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'new-products.tpl');
	}
}

