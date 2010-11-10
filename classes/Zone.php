<?php

/**
  * Zones class, Zone.php
  * Zones management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class ZoneCore extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
	/** @var boolean Zone status */
	public 		$active = true;
	public 		$eu_zone = false; /* Obsolete; to remove */
	
 	protected 	$fieldsRequired = array('name');
 	protected 	$fieldsSize = array('name' => 64);
 	protected 	$fieldsValidate = array('name' => 'isGenericName', 'active' => 'isBool');
		
	protected 	$table = 'zone';
	protected 	$identifier = 'id_zone';
	
	protected	$webserviceParameters = array(
		'objectsNodeName' => 'zones',
	);

	public function getFields()
	{
		parent::validateFields();
		
		$fields['name'] = pSQL($this->name);
		$fields['active'] = intval($this->active);
		
		return $fields;
	}
	
	/**
	* Get all available geographical zones
	*
	* @return array Zones
	*/
	static public function getZones($active = false)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'zone`
		'.($active ? 'WHERE active = 1' : '').'
		ORDER BY `name` ASC');
	}

	/**
	* Get a zone ID from its default language name
	*
	* @return integer id_zone
	*/
	static public function getIdByName($name)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `id_zone`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `name` = \''.pSQL($name).'\''
		);
	}
}

?>