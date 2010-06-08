<?php

class BlockSpecials extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'blockspecials';
        $this->tab = 'Blocks';
        $this->version = 0.8;

        parent::__construct();

        $this->displayName = $this->l('Specials block');
        $this->description = $this->l('Adds a block with current product Specials');
    }

    function install()
    {
        return (parent::install() AND $this->registerHook('rightColumn'));
    }

    function hookRightColumn($params)
    {
		global $smarty;

		if ($special = Product::getRandomSpecial(intval($params['cookie']->id_lang)))
			$smarty->assign(array(
			'special' => $special,
			'priceWithoutReduction_tax_excl' => Tools::ps_round($special['price_without_reduction'] / (1 + $special['rate'] / 100), 2),
			'oldPrice' => $special['price'] + $special['reduction'],
			'mediumSize' => Image::getSize('medium')));
		return $this->display(__FILE__, 'blockspecials.tpl');
	}
	
	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
}

?>
