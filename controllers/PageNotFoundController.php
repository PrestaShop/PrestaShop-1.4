<?php

class PageNotFoundControllerCore extends FrontController
{
	public function displayContent()
	{
		$this->smarty->display(_PS_THEME_DIR_.'404.tpl');
	}
}

?>