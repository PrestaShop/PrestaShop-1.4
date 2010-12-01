<?php

class BestSalesControllerCore extends FrontController
{
	public function preProcess()
	{
		$this->productSort();
		$nbProducts = (int)(ProductSale::getNbSales());
		$this->pagination($nbProducts);
		
		global $orderBy, $orderWay, $p, $n;
		$this->smarty->assign(array(
			'products' => ProductSale::getBestSales((int)($this->cookie->id_lang), (int)($p) - 1, (int)($n), $orderBy, $orderWay),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'nbProducts' => $nbProducts,
			'homeSize' => Image::getSize('home')
		));
	}

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'best-sales.tpl');
	}
}

