<?php

class BlockCurrencies extends Module
{
	function __construct()
	{
		$this->name = 'blockcurrencies';
		$this->tab = 'Blocks';
		$this->version = 0.1;

		parent::__construct();
		
		$this->displayName = $this->l('Currency block');
		$this->description = $this->l('Adds a block for selecting a currency');
	}

	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('top'))
			return false;
		return true;
	}

	/**
	* Returns module content for header
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookTop($params)
	{
		global $smarty;
		$currencies = Currency::getCurrencies();
		if (!sizeof($currencies))
			return '';
		$smarty->assign('currencies', $currencies);
		return $this->display(__FILE__, 'blockcurrencies.tpl');
	}

}

?>
