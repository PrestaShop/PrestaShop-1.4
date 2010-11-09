<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockStore extends Module
{
    function __construct()
    {
        $this->name = 'blockstore';
        $this->tab = 'front_office_features';
        $this->version = 1.0;

        parent::__construct();

		$this->displayName = $this->l('Stores block');
        $this->description = $this->l('Displays a block with a link to the store locator');
    }

    function install()
    {
        return (parent::install() AND $this->registerHook('rightColumn') AND $this->registerHook('header'));
    }
   
    function hookLeftColumn($params)
    {
		return $this->hookRightColumn($params);
	}
	
	function hookRightColumn($params)
	{
		return $this->display(__FILE__, 'blockstore.tpl');
	}
	
	function hookHeader($params)
	{
		Tools::addCSS(_THEME_CSS_DIR_.'modules/'.$this->name.'/blockstore.css', 'all');
	}
}

?>