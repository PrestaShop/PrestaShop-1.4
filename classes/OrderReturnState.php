<?php

/**
  * Order states class, OrderState.php
  * Order states management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		OrderReturnState extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
	/** @var string Display state in the specified color */
	public		$color;
	
	
 	protected 	$fieldsValidate = array('color' => 'isColor');
	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 64);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');
	
	protected 	$table = 'order_return_state';
	protected 	$identifier = 'id_order_return_state';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['color'] = pSQL($this->color);
		return $fields;
	}
	
	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}
	
	/**
	* Get all available order states
	*
	* @param integer $id_lang Language id for state name
	* @return array Order states
	*/
	static public function getOrderReturnStates($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_return_state` ors
		LEFT JOIN `'._DB_PREFIX_.'order_return_state_lang` orsl ON (ors.`id_order_return_state` = orsl.`id_order_return_state` AND orsl.`id_lang` = '.intval($id_lang).')
		ORDER BY ors.`id_order_return_state` ASC');
	}
	
	
}

?>