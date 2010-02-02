<?php

class BlockBestSellers extends Module
{
	private $_html = '';
	private $_postErrors = array();

	function __construct()
    {
        $this->name = 'blockbestsellers';
        $this->tab = 'Blocks';
        $this->version = '1.1';

        parent::__construct();

        $this->displayName = $this->l('Top seller block');
        $this->description = $this->l('Add a block displaying the shop\'s top sellers');
    }

    public function install()
    {
        if (!parent::install() OR
			!$this->registerHook('rightColumn') OR
			!$this->registerHook('updateOrderStatus') OR
			!ProductSale::fillProductSales())
				return false;
			return true;
    }


    function hookRightColumn($params)
    {
		global $smarty;
		$currency = new Currency(intval($params['cookie']->id_currency));
		$bestsellers = ProductSale::getBestSalesLight(intval($params['cookie']->id_lang), 0, 5);

		$best_sellers = array();
		foreach ($bestsellers AS $bestseller)
		{
			$bestseller['price'] = Tools::displayPrice(Product::getPriceStatic(intval($bestseller['id_product'])), $currency);
			$best_sellers[] = $bestseller;
		}
		$smarty->assign(array(
			'best_sellers' => $best_sellers,
			'mediumSize' => Image::getSize('medium')));
		return $this->display(__FILE__, 'blockbestsellers.tpl');
	}
	
	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
}

?>
