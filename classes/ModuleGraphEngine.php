<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
abstract class ModuleGraphEngine extends Module
{
	protected	$_type;

	public function __construct($type)
	{
		$this->_type = $type;
	}
	
	public function install()
	{
		if (!parent::install())
			return false;
		return Configuration::updateValue('PS_STATS_RENDER', $this->name);
	}

	public static function getGraphEngines()
	{
		$result = Db::getInstance()->ExecuteS('
    	SELECT m.`name`
    	FROM `'._DB_PREFIX_.'module` m
    	LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
    	LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
    	WHERE h.`name` = \'GraphEngine\'');
		
		$arrayEngines = array();
		foreach ($result AS $module)
    	{
			$instance = Module::getInstanceByName($module['name']);
			if (!$instance)
				continue;
			$arrayEngines[$module['name']] = array($instance->displayName, $instance->description);
		}
		
		return $arrayEngines;
	}
	
	abstract public function createValues($values);
	abstract public function setSize($width, $height);
	abstract public function setLegend($legend);
	abstract public function setTitles($titles);
	abstract public function draw();
}

?>
