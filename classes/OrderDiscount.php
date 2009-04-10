<?php
/**
  * OrderDiscount class, OrderDiscount.php
  * OrdersDiscount management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class OrderDiscount extends ObjectModel
{
	/** @var integer */
	public $id_order_discount;
	
	/** @var integer */
	public $id_order;

	/** @var string */	
	public $name;

	/** @var integer */	
	public $value;

	protected $tables = array ('order_discount');

	protected	$fieldsRequired = array ('id_order', 'name', 'value');	
	protected	$fieldsValidate = array ('id_order' => 'isUnsignedId', 'name' => 'isGenericName', 'value' => 'isInt');

	/* MySQL does not allow 'order detail' for a table name */
	protected 	$table = 'order_discount';
	protected 	$identifier = 'id_order_discount';
	
	public function getFields()
	{
		parent::validateFields();

		$fields['id_order'] = intval($this->id_order);
		$fields['name'] = pSQL($this->name);
		$fields['value'] = intval($this->value);
		
		return $fields;
	}	
}

?>