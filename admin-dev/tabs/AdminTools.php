<?php

/**
  * Tools tab for admin panel, AdminTools.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminTools extends AdminTab
{
	public function postProcess()
	{
	}
	
	public function display()
	{
		echo '<fieldset><legend><img src="../img/admin/tab-tools.gif" />'.$this->l('Shop Tools').'</legend>';
		echo '<p>'.$this->l('Several tools are available to manage your shop.').'</p>';
		echo '<br />';
		echo '<p>'.$this->l('Please choose a tool by selecting a Tools sub-tab above.').'</p>';
		echo '</fieldset>';
	}
}

?>