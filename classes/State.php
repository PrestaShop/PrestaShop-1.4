<?php

/**
  * State class, State.php
  * States management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		State extends ObjectModel
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

	public static function getStates($id_lang, $active = false)
	{
		return Db::getInstance()->ExecuteS('
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
	    $result = Db::getInstance()->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'state`
		WHERE `id_state` = '.intval($id_state).'');

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
}

?>