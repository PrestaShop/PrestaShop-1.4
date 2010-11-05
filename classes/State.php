<?php

/**
  * State class, State.php
  * States management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class StateCore extends ObjectModel
{
	/** @var integer Country id which state belongs */
	public 		$id_country;

	/** @var integer Zone id which state belongs */
	public 		$id_zone;

	/** @var string 2 letters iso code */
	public 		$iso_code;

	/** @var string Name */
	public 		$name;

	/** @var interger Tax behavior */
	public		$tax_behavior;

	/** @var boolean Status for delivery */
	public		$active = true;

 	protected 	$fieldsRequired = array('id_country', 'id_zone', 'iso_code', 'name', 'tax_behavior');
 	protected 	$fieldsSize = array('iso_code' => 4, 'name' => 32);
 	protected 	$fieldsValidate = array('id_country' => 'isUnsignedId', 'id_zone' => 'isUnsignedId', 'iso_code' => 'isStateIsoCode', 'name' => 'isGenericName', 'tax_behavior' => 'isUnsignedInt', 'active' => 'isBool');

	protected 	$table = 'state';
	protected 	$identifier = 'id_state';
	
	protected	$webserviceParameters = array(
		'objectsNodeName' => 'states',
		'fields' => array(
			'id_zone' => array('sqlId' => 'id_zone', 'xlink_resource'=> 'zones'),
			'id_country' => array('sqlId' => 'id_zone', 'xlink_resource'=> 'countries')
		),
	);

	public function getFields()
	{
		parent::validateFields();
		$fields['id_country'] = intval($this->id_country);
		$fields['id_zone'] = intval($this->id_zone);
		$fields['iso_code'] = pSQL(strtoupper($this->iso_code));
		$fields['name'] = pSQL($this->name);
		$fields['tax_behavior'] = intval($this->tax_behavior);
		$fields['active'] = intval($this->active);
		return $fields;
	}

	public static function getStates($id_lang = false, $active = false)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_state`, `id_country`, `id_zone`, `iso_code`, `name`, `tax_behavior`, `active`
		FROM `'._DB_PREFIX_.'state`
		'.($active ? 'WHERE active = 1' : '').'
		ORDER BY `name` ASC');
	}
	
	/**
	* Get a state name with its ID
	*
	* @param integer $id_state Country ID
	* @return string State name
	*/
	static public function getNameById($id_state)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'state`
		WHERE `id_state` = '.intval($id_state));

        return $result['name'];
    }
    
	/**
	* Get a state id with its name
	*
	* @param string $id_state Country ID
	* @return integer state id
	*/
	static public function getIdByName($state)
    {
	  	$result = Db::getInstance()->getRow('
		SELECT `id_state`
		FROM `'._DB_PREFIX_.'state`
		WHERE `name` LIKE \''.pSQL($state).'\'');
		 	
        return (intval($result['id_state']));
    }

	/**
	* Get a state id with its iso code
	*
	* @param string $iso_code Iso code
	* @return integer state id
	*/
	static public function getIdByIso($iso_code)
    {
	  	return Db::getInstance()->getValue('
			SELECT `id_state`
			FROM `'._DB_PREFIX_.'state`
			WHERE `iso_code` = \''.pSQL($iso_code).'\''
		);
    }
	
	/**
	* Delete a state only if is not in use
	*
	* @return boolean
	*/
	public function delete()
	{
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
	 		die(Tools::displayError());

		if ($this->isUsed()) die(Tools::displayError()); 
		
		/* Database deletion */
		$result = Db::getInstance()->Execute('DELETE FROM `'.pSQL(_DB_PREFIX_.$this->table).'` WHERE `'.pSQL($this->identifier).'` = '.intval($this->id));
		if (!$result)
			return false;

		/* Database deletion for multilingual fields related to the object */
		if (method_exists($this, 'getTranslationsFieldsChild'))
			Db::getInstance()->Execute('DELETE FROM `'.pSQL(_DB_PREFIX_.$this->table).'_lang` WHERE `'.pSQL($this->identifier).'` = '.intval($this->id));
		return $result;
	}	
	
	/**
	 * Check if a state is used
	 *
	 * @return boolean 
	 */
	public function isUsed()
	{
		return ($this->countUsed() > 0);
	}
	
		/**
	 * Returns the number of utilisation of a state
	 *
	 * @return integer count for this state
	 */
	public function countUsed()
	{
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(*) AS nb_used 
		FROM `'._DB_PREFIX_.'address` 
		WHERE `'.pSQL($this->identifier).'` = '.intval($this->id));	
		return $row['nb_used'];
	}
}

?>