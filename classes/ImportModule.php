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
	protected $db = NULL;
	
	/** @var string Prefix database */
	public $prefix;
	
	public function __construct()
	{
		parent::__construct ();
	}	
	
	public function initDatabaseConnection($server, $user, $passwd, $database)
	{
		$this->db = new MySQL($server, $user, $passwd, $database);
		if (!$this->db)
		{
			Tools::displayError();
			return false;
		}
		return true;
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
