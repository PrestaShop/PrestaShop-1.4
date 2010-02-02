<?php

class BlockPaymentLogo extends Module
{
	function __construct()
	{
		$this->name = 'blockpaymentlogo';
		$this->tab = 'Blocks';
		$this->version = 0.1;

		parent::__construct();
		
		$this->displayName = $this->l('Block payment logo');
		$this->description = $this->l('Adds a block to display all payment logo');
	}

	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('leftColumn'))
			return false;
		return true;
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookLeftColumn($params)
	{
		global $smarty;
		$smarty->assign('securepayment', $this->l('secure-payment'));
		return $this->display(__FILE__, 'blockpaymentlogo.tpl');
	}
	
	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

	function hookFooter($params)
	{
		return $this->hookLeftColumn($params);
	}

}

?>