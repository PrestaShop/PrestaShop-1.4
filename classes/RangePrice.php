<?php

/**
  * Price ranges class, RangePrice.php
  * Price ranges management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class RangePrice extends ObjectModel
{
	public		$id_carrier;
	public 		$delimiter1;
	public 		$delimiter2;
	
 	protected 	$fieldsRequired = array('id_carrier', 'delimiter1', 'delimiter2');
 	protected 	$fieldsValidate = array('id_carrier' => 'isInt', 'delimiter1' => 'isFloat', 'delimiter2' => 'isFloat');

	protected 	$table = 'range_price';
	protected 	$identifier = 'id_range_price';
		
	public function getFields()
	{
		parent::validateFields();
		$fields['id_carrier'] = intval($this->id_carrier);
		$fields['delimiter1'] = floatval($this->delimiter1);
		$fields['delimiter2'] = floatval($this->delimiter2);
		return $fields;
	}
	
	/**
	* Get all available price ranges
	*
	* @return array Ranges
	*/
	public static function getRanges($id_carrier)
	{
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'range_price` WHERE `id_carrier` = '.intval($id_carrier).' ORDER BY `delimiter1` ASC';
		return Db::getInstance()->ExecuteS($sql);
	}
}

?>