<?php

class PricesDropControllerCore extends FrontController
{
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');
	}
	
	public function process()
	{
		parent::process();
		
		include(dirname(__FILE__).'/../product-sort.php');
		$nbProducts = Product::getPricesDrop(intval($this->cookie->id_lang), NULL, NULL, true);
		include(dirname(__FILE__).'/../pagination.php');
		
		global $n, $p, $orderBy, $orderWay;
		$this->smarty->assign(array(
			'products' => Product::getPricesDrop(intval($this->cookie->id_lang), intval($p) - 1, intval($n), false, $orderBy, $orderWay),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'nbProducts' => $nbProducts));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'prices-drop.tpl');
	}
}