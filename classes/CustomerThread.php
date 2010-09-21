<?php

class CustomerThread extends ObjectModel
{
	public $id;
	public $id_lang;
	public $id_contact;
	public $id_customer;
	public $id_order;
	public $status;
	public $email;
	public $token;
	public $date_add;
	public $date_upd;
	
	protected $table = 'customer_thread';
	protected $identifier = 'id_customer_thread';
	
	protected $fieldsRequired = array('id_lang', 'id_contact', 'token');
	protected $fieldsSize = array('email' => 254);
	protected $fieldsValidate = array('id_lang' => 'isUnsignedId', 'id_contact' => 'isUnsignedId', 'id_customer' => 'isUnsignedId',
										'id_order' => 'isUnsignedId', 'email' => 'isEmail', 'token' => 'isGenericName');

	public	function getFields()
	{
	 	parent::validateFields();
		$fields['id_lang'] = intval($this->id_lang);
		$fields['id_contact'] = intval($this->id_contact);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_order'] = pSQL($this->id_order);
		$fields['status'] = pSQL($this->status);
		$fields['email'] = pSQL($this->email);
		$fields['token'] = pSQL($this->token);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}
	
	public static function getCustomerMessages($id_customer)
	{
		return Db::getInstance()->ExecuteS('
		SELECT * FROM '._DB_PREFIX_.'customer_thread ct
		LEFT JOIN '._DB_PREFIX_.'customer_message cm ON ct.id_customer_thread = cm.id_customer_thread
		WHERE id_customer = '.intval($id_customer));
	}	
}

?>