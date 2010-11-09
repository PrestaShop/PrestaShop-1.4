<?php

/**
  * Stores class, Store.php
  * Stores management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class StoreCore extends ObjectModel
{
	/** @var integer Country id */
	public		$id_country;

	/** @var integer State id */
	public		$id_state;
	
	/** @var string Store name */
	public 		$name;
	
	/** @var string Address first line */
	public 		$address1;

	/** @var string Address second line (optional) */
	public 		$address2;

	/** @var string Postal code */
	public 		$postcode;

	/** @var string City */
	public 		$city;
	
	/** @var float Latitude */
	public 		$latitude;
	
	/** @var float Longitude */
	public 		$longitude;
	
	/** @var string Store hours (PHP serialized) */
	public 		$hours;
	
	/** @var string Phone number */
	public 		$phone;
	
	/** @var string Fax number */
	public 		$fax;
	
	/** @var string Note */
	public		$note;
	
	/** @var string e-mail */
	public 		$email;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;
	
	/** @var boolean Store status */
	public 		$active = true;
	
 	protected 	$fieldsRequired = array('id_country', 'name', 'address1', 'postcode', 'city', 'active');
 	protected 	$fieldsSize = array('name' => 128, 'address1' => 128, 'address2' => 128, 'postcode' => 12, 'city' => 64, 'latitude' => 10, 'longitude' => 10, 'hours' => 254, 'phone' => 16, 'fax' => 16, 'email' => 128, 'note' => 65000);
 	protected 	$fieldsValidate = array('id_country' => 'isUnsignedId', 'id_state' => 'isNullOrUnsignedId', 'name' => 'isGenericName', 'address1' => 'isAddress', 'address2' => 'isAddress',
	'postcode' => 'isPostCode', 'city' => 'isCityName', 'latitude' => 'isCoordinate', 'longitude' => 'isCoordinate', 'hours' => 'isSerializedArray', 'phone' => 'isPhoneNumber', 'fax' => 'isPhoneNumber',
	'note' => 'isCleanHtml', 'email' => 'isEmail', 'active' => 'isBool');

	protected 	$table = 'store';
	protected 	$identifier = 'id_store';
	
	protected	$webserviceParameters = array(
		'objectsNodeName' => 'stores',
		'fields' => array(
			'id_country' => array('sqlId' => 'id_country', 'xlink_resource'=> 'countries'),
			'id_state' => array('sqlId' => 'id_state', 'xlink_resource'=> 'states'),
		),
	);

	public function getFields()
	{
		parent::validateFields();
		
		$fields['id_country'] = intval($this->id_country);
		$fields['id_state'] = intval($this->id_state);
		$fields['name'] = pSQL($this->name);
		$fields['address1'] = pSQL($this->address1);
		$fields['address2'] = pSQL($this->address2);
		$fields['postcode'] = pSQL($this->postcode);
		$fields['city'] = pSQL($this->city);
		$fields['latitude'] = floatval($this->latitude);
		$fields['longitude'] = floatval($this->longitude);
		$fields['hours'] = pSQL($this->hours);
		$fields['phone'] = pSQL($this->phone);
		$fields['fax'] = pSQL($this->fax);
		$fields['note'] = pSQL($this->note);
		$fields['email'] = pSQL($this->email);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['active'] = intval($this->active);
		
		return $fields;
	}
}

?>