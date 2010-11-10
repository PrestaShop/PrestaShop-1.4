<?php

/**
  * Database tab for admin panel, AdminDb.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class AdminAppearance extends AdminThemes // extends AdminThemes only for retro-compatibility
{
	public function display()
	{
		Tools::redirectAdmin('index.php?tab=AdminThemes&token='.Tools::getAdminTokenLite('AdminThemes'));
	}
}

?>
