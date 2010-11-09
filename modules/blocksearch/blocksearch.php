<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockSearch extends Module
{
	function __construct()
	{
		$this->name = 'blocksearch';
		$this->tab = 'search_filter';
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
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', intval(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookRightColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', intval(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookTop($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', intval(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch-top.tpl');
	}

	function hookHeader($params)
	{
		global $smarty;
		$instantSearch = intval(Configuration::get('PS_INSTANT_SEARCH'));
		$smarty->assign('instantsearch', $instantSearch);
		if (Configuration::get('PS_SEARCH_AJAX'))
		{
			Tools::addCSS(_PS_CSS_DIR_.'jquery.autocomplete.css');
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.autocomplete.js');
		}
		Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');
		Tools::addCSS(($this->_path).'blocksearch.css', 'all');
	}
	
	private function _disabledSearchAjax()
	{
		if (!$this->isRegisteredInHook('header'))
			Configuration::updateValue('PS_SEARCH_AJAX', 0);
	}
}
