<?php

/**
  * Alias class, Alias.php
  * Alias management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class Alias extends ObjectModel
{
	public $alias;
	public $search;
	public $active = true;
		
 	protected 	$fieldsRequired = array('alias', 'search');
 	protected 	$fieldsSize = array('alias' => 255, 'search' => 255);
 	protected 	$fieldsValidate = array('search' => 'isValidSearch', 'alias' => 'isValidSearch', 'active' => 'isBool');

	protected 	$table = 'alias';
	protected 	$identifier = 'id_alias';

	function __construct($id = NULL, $alias = NULL, $search = NULL, $id_lang = NULL)
	{
		if ($id)
			parent::__construct($id);
		elseif ($alias AND Validate::isValidSearch($alias))
		{
			$row = Db::getInstance()->getRow('
			SELECT a.id_alias, a.search, a.alias
			FROM `'._DB_PREFIX_.'alias` a
			WHERE `alias` LIKE \''.pSQL($alias).'\' AND `active` = 1');

			if ($row)
			{
			 	$this->id = intval($row['id_alias']);
			 	$this->search = $search ? trim($search) : $row['search'];
				$this->alias = $row['alias'];
			}
			else
			{
				$this->alias = trim($alias);
				$this->search = trim($search);
			}
		}
	}

	static public function deleteAliases($search)
	{
		return Db::getInstance()->Execute('
			DELETE
			FROM `'._DB_PREFIX_.'alias`
			WHERE `search` LIKE \''.pSQL($search).'\'');
	}
	
	public function getAliases()
	{
		$aliases = Db::getInstance()->ExecuteS('
			SELECT a.alias
			FROM `'._DB_PREFIX_.'alias` a
			WHERE `search` = \''.pSQL($this->search).'\'');
		$aliases = array_map('implode', $aliases);
		return implode(', ', $aliases);
	}
	
	public function getFields()
	{
		parent::validateFields();
		
		$fields['alias'] = pSQL($this->alias);
		$fields['search'] = pSQL($this->search);
		$fields['active'] = intval($this->active);
		return $fields;
	}
}

?>