<?php

include_once(dirname(__FILE__).'/AdminThemes.php');

class AdminAppearance extends AdminThemes // extends AdminThemes only for retro-compatibility
{
	public function display()
	{
		Tools::redirectAdmin('index.php?tab=AdminThemes&token='.Tools::getAdminTokenLite('AdminThemes'));
	}
}

?>
