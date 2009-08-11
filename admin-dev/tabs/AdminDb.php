<?php

/**
  * Database tab for admin panel, AdminDb.php
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

class AdminDb extends AdminPreferences
{
	public function __construct()
	{
		$this->className = 'Configuration';
		$this->table = 'configuration';
 	
 		$this->_fieldsDatabase = array(
		'db_server' => array('title' => $this->l('Server:'), 'desc' => $this->l('IP or server name; \'localhost\' will work in most cases'), 'size' => 30, 'type' => 'text', 'required' => true),
		'db_name' => array('title' => $this->l('Database:'), 'desc' => $this->l('Database name (e.g., \'prestashop\')'), 'size' => 30, 'type' => 'text', 'required' => true),
		'db_prefix' => array('title' => $this->l('Prefix:'), 'size' => 30, 'type' => 'text'),
		'db_user' => array('title' => $this->l('User:'), 'size' => 30, 'type' => 'text', 'required' => true),
		'db_passwd' => array('title' => $this->l('Password:'), 'size' => 30, 'type' => 'password', 'desc' => $this->l('Leave blank if no change')));
		parent::__construct();
	}
	
	public function postProcess()
	{
		global $currentIndex;

		if (isset($_POST['submitDatabase'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')	 	
		 	{
				foreach ($this->_fieldsDatabase AS $field => $values)
					if (isset($values['required']) AND $values['required'])
						if (($value = Tools::getValue($field)) == false AND (string)$value != '0')
							$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is required');
	
				if (!sizeof($this->_errors))
				{
					/* Datas are not saved in database but in config/settings.inc.php */
					$settings = array();
				 	foreach ($_POST as $k => $value)
						if ($value)
							$settings['_'.Tools::strtoupper($k).'_'] = $value;
				 	rewriteSettingsFile(NULL, NULL, $settings);
				 	Tools::redirectAdmin($currentIndex.'&conf=6'.'&token='.$this->token);
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}

	public function display()
	{
		echo $this->displayWarning($this->l('Be VERY CAREFUL with these settings, as changes may cause your PrestaShop online store to malfunction. For all issues, check the config/settings.inc.php file.')).'<br />';
		$this->_displayForm('database', $this->_fieldsDatabase, $this->l('Database'), 'width2', 'database_gear');
	}
}

?>