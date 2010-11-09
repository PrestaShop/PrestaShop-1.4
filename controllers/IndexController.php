<?php

class IndexControllerCore extends FrontController
{
	public function process()
	{
		parent::process();
		$this->smarty->assign('HOOK_HOME', Module::hookExec('home'));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'index.tpl');
	}
}

?>