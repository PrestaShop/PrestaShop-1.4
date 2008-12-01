<?php

class SubDomain extends ObjectModel
{
	public $name;

	protected $fieldsRequired = array('name');
	protected $fieldsSize = array('name' => 16);
	protected $fieldsValidate = array('name' => 'isSubDomainName');

	protected $table = 'subdomain';
	protected $identifier = 'id_subdomain';

	public function getFields()
	{
		parent::validateFields();
		$fields['name'] = pSQL($this->name);
		return $fields;
	}

	static public function getSubDomains()
	{
		if (!$result = Db::getInstance()->ExecuteS('SELECT `name` FROM `'._DB_PREFIX_.'subdomain`'))
			return false;
		$subDomains = array();
		foreach ($result AS $row)
			$subDomains[] = $row['name'];
		return $subDomains;
	}
}

?>