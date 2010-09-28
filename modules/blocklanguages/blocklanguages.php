<?php

class BlockLanguages extends Module
{
	function __construct()
	{
		$this->name = 'blocklanguages';
		$this->tab = 'Blocks';
		$this->version = 0.1;

		parent::__construct();

		$this->displayName = $this->l('Language block');
		$this->description = $this->l('Adds a block for selecting a language');
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
		$languages = Language::getLanguages();
		if (!sizeof($languages))
			return '';
		$smarty->assign('languages', $languages);
		return $this->display(__FILE__, 'blocklanguages.tpl');
	}
	
	function hookHeader($params)
	{
		global $css_files;
		
		//$css_files[$this->_path.'blocklanguages.css'] = 'all';
		$css_files[_THEME_CSS_DIR_.'modules/'.$this->name.'/blocklanguages.css'] = 'all';
	}
}

?>
