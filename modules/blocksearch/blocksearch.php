<?php

class BlockSearch extends Module
{
	function __construct()
	{
		$this->name = 'blocksearch';
		$this->tab = 'Blocks';
		$this->version = 1.0;

		parent::__construct(); /* The parent construct is required for translations */

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Quick Search block');
		$this->description = $this->l('Adds a block with a quick search field');
	}

	function install()
	{
		if (!parent::install())
			return false;
		//return $this->registerHook('leftColumn');
		//return $this->registerHook('rightColumn');
		return $this->registerHook('top');
	}

	function hookLeftColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookRightColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookTop($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		return $this->display(__FILE__, 'blocksearch-header.tpl');
	}

}
