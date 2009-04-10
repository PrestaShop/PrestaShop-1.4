<?php

/**
  * SubDomain class, SubDomain.php
  * Sub domain management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

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