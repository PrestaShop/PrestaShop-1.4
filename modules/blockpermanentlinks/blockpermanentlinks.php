<?php

class BlockPermanentLinks extends Module
{
	function __construct()
	{
		$this->name = 'blockpermanentlinks';
		$this->tab = 'Blocks';
		$this->version = 0.1;

		parent::__construct();
		
		$this->displayName = $this->l('Permanent links block');
		$this->description = $this->l('Adds a block that displays permanent links such as sitemap, contact, etc.');
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
		return $this->display(__FILE__, 'blockpermanentlinks-header.tpl');
	}

	/**
	* Returns module content for left column
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookLeftColumn($params)
	{
		return $this->display(__FILE__, 'blockpermanentlinks.tpl');
	}

	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
}

?>
