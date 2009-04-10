<?php

/**
  * Localization tab for admin panel, AdminLocalization.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/tabs/AdminPreferences.php');

class AdminLocalization extends AdminPreferences
{
	public function __construct()
	{
		global $cookie;

		$lang = strtoupper(Language::getIsoById($cookie->id_lang));
		$this->className = 'Configuration';
		$this->table = 'configuration';

		$this->_fieldsLocalization = array(
			'PS_WEIGHT_UNIT' => array('title' => $this->l('Weight unit:'), 'desc' => $this->l('The weight unit of your shop'), 'validation' => 'isWeightUnit', 'required' => true, 'type' => 'text'));

		parent::__construct();
	}

	public function postProcess()
	{
		if (isset($_POST['submitLocalization'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsLocalization);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}	

	public function display()
	{
		$this->_displayForm('localization', $this->_fieldsLocalization, $this->l('Localization'), 'width2', 'localization');
	}
}

?>