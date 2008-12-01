<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  */
  
class SearchEngine extends ObjectModel
{	
	public $server;
	public $getvar;

	protected	$fieldsRequired = array ('server', 'getvar');	
	protected	$fieldsValidate = array ('server' => 'isUrl', 'getvar' => 'isModuleName');

	protected 	$table = 'search_engine';
	protected 	$identifier = 'id_search_engine';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['server'] = pSQL($this->server);
		$fields['getvar'] = pSQL($this->getvar);
		return $fields;
	}	
}

?>
