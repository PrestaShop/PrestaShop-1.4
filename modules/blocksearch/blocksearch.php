<?php

class BlockSearch extends Module
{
	function __construct()
	{
		$this->name = 'blocksearch';
		$this->tab = 'Blocks';
		$this->version = 1.0;

		parent::__construct();
		
		$this->displayName = $this->l('Quick Search block');
		$this->description = $this->l('Adds a block with a quick search field');
	}

	function install()
	{
		if (!parent::install() OR !$this->registerHook('top') OR !$this->registerHook('header'))
			return false;
		return true;
	}

	function hookLeftColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', intval(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookRightColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', intval(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookTop($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', intval(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch-top.tpl');
	}

	function hookHeader($params)
	{
		if (Configuration::get('PS_SEARCH_AJAX'))
			return $this->display(__FILE__, 'header.tpl');
	}
	
	private function _disabledSearchAjax()
	{
		if (!$this->isRegisteredInHook('header'))
			Configuration::updateValue('PS_SEARCH_AJAX', 0);
	}
}
