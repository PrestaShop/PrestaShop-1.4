<?php

class CustomerMessage extends ObjectModel
{
	public $id;
	public $id_customer_thread;
	public $id_employee;
	public $message;
	public $file_name;
	public $ip_address;
	public $user_agent;
	public $date_add;
	
	protected $table = 'customer_message';
	protected $identifier = 'id_customer_message';
	
	protected $fieldsRequired = array('message');
	protected $fieldsSize = array('message' => 65000);
	protected $fieldsValidate = array('message' => 'isCleanHtml', 'id_employee' => 'isUnsignedId', 'ip_address' => 'isInt');

	public	function getFields()
	{
	 	parent::validateFields();
		$fields['id_customer_thread'] = intval($this->id_customer_thread);
		$fields['id_employee'] = intval($this->id_employee);
		$fields['message'] = pSQL($this->message);
		$fields['file_name'] = pSQL($this->file_name);
		$fields['ip_address'] = intval($this->ip_address);
		$fields['user_agent'] = pSQL($this->user_agent);
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}
}

?>