<?php

/**
  * Catalog tab for admin panel, AdminCatalog.php
  * Tab has been separated in 3 files : this one, AdminCategories.php and AdminProducts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include(PS_ADMIN_DIR.'/tabs/AdminOrdersStates.php');
include(PS_ADMIN_DIR.'/tabs/AdminReturnStates.php');

class AdminStatuses extends AdminTab
{
	private $adminOrdersStates;
	private $adminReturnStates;

	public function __construct()
	{
		$this->table = array('order_state', 'order_return_state');
		$this->adminOrdersStates = new adminOrdersStates();
		$this->adminReturnStates = new adminReturnStates();

		parent::__construct();
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->adminOrdersStates->tabAccess = $this->tabAccess;
		$this->adminReturnStates->tabAccess = $this->tabAccess;
		return $result;
	}

	public function postProcess()
	{
		$this->adminOrdersStates->token = $this->token;
		$this->adminReturnStates->token = $this->token;

		$this->adminOrdersStates->postProcess($this->token);
		$this->adminReturnStates->postProcess($this->token);
	}

	public function displayErrors()
	{
		$this->adminOrdersStates->displayErrors($this->token);
		$this->adminReturnStates->displayErrors($this->token);
	}

	public function display()
	{
		global $currentIndex;

		if (!Tools::isSubmit('updateorder_return_state') AND !Tools::isSubmit('submitAddorder_return_state'))
		{
			echo '<h2>'.$this->l('Order states').'</h2>';
			$this->adminOrdersStates->display($this->token);
		}
		if (!Tools::isSubmit('updateorder_state') AND !Tools::isSubmit('submitAddupdateorder_state') AND !Tools::isSubmit('addorder_state'))
		{
			if (!Tools::isSubmit('updateorder_return_state') AND !Tools::isSubmit('submitAddorder_return_state'))
				echo '<div style="margin:10px">&nbsp;</div>';
			echo '<h2>'.$this->l('Order return states').'</h2>';
			$this->adminReturnStates->display($this->token);
		}
	}
}

?>