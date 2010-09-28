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
		return (parent::install() AND $this->registerHook('top') AND $this->registerHook('header'));
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
	
	function hookHeader($params)
	{
		global $css_files;
		
		//$css_files[$this->_path.'blockcurrencies.css'] = 'all';
		$css_files[_THEME_CSS_DIR_.'modules/'.$this->name.'/blockcurrencies.css'] = 'all';
	}
}

?>
