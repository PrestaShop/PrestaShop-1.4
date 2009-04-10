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
	
	public static function getKeywords($url)
	{
		$parsedUrl = @parse_url($url);
		if (!isset($parsedUrl['host']) OR !isset($parsedUrl['query']))
			return false;
		$result = Db::getInstance()->ExecuteS('SELECT `server`, `getvar` FROM `'._DB_PREFIX_.'search_engine`');
		foreach ($result as $index => $row)
		{
			$host =& $row['server'];
			$varname =& $row['getvar'];
			if (strstr($parsedUrl['host'], $host))
			{
				$kArray = array();
				preg_match('/[^a-z]'.$varname.'=.+\&'.'/U', $parsedUrl['query'], $kArray);
				if (empty($kArray[0]))
					preg_match('/[^a-z]'.$varname.'=.+$'.'/', $parsedUrl['query'], $kArray);
				if (empty($kArray[0]))
					return false;
				$kString = urldecode(str_replace('+', ' ', ltrim(substr(rtrim($kArray[0], '&'), strlen($varname) + 1), '=')));
				return $kString;
			}
		}
	}
}

?>
