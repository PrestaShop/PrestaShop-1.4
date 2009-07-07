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
        parent::install();
        $this->registerHook('rightColumn');
    }

    function hookRightColumn($params)
    {
		global $smarty;

		if ($special = Product::getRandomSpecial(intval($params['cookie']->id_lang)))
			$smarty->assign(array(
			'special' => $special,
			'oldPrice' => number_format($special['price'] + $special['reduction'], 2, '.', ''),
			'mediumSize' => Image::getSize('medium')));
		return $this->display(__FILE__, 'blockspecials.tpl');
	}
	
	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
}

?>
