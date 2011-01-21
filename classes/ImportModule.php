<?php

/**
  * ImportModule class, ImportModule.php
  * Import module management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

abstract class ImportModuleCore extends Module
{
	protected $_link = NULL;
	
	public $server;
	
	public $user;
	
	public $passwd;
	
	public $database;
	
	/** @var string Prefix database */
	public $prefix;
	
	public function __construct()
	{
		parent::__construct ();
	}	
	
	public function __destruct()
	{
		@mysql_close($this->_link);
	}
	
	private function initDatabaseConnection()
	{
		if ($this->_link != NULL)
			return $this->_link;
		if ($this->_link = mysql_connect($this->server, $this->user, $this->passwd, true))
		{
			if(!mysql_select_db($this->database, $this->_link))
				die(Tools::displayError('The database selection cannot be made.'));
		}
		else
			die(Tools::displayError('Link to database cannot be established.'));
		return $this->_link;
	}
	
	public function executeS($query)
	{
		$this->initDatabaseConnection();
		$result = mysql_query($query, $this->_link);
		$resultArray = array();
		if ($result !== true)
			while ($row = mysql_fetch_assoc($result))
				$resultArray[] = $row;
		return $resultArray;
	}
	
	public static function getImportModulesOnDisk ()
	{
		$modules = Module::getModulesOnDisk();
		foreach ($modules as $key => $module)
			if(get_parent_class($module) != 'ImportModule')
				unset($modules[$key]);
		return $modules;
	}
	
	abstract public function getDefaultIdLang();

}

?>
