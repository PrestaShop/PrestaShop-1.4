<?php

/**
  * Employee class, Employee.php
  * Employees management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Employee extends ObjectModel
{
	public 		$id;
	
	/** @var string Determine employee profile */
	public 		$id_profile;
	
	/** @var string Lastname */
	public 		$lastname;
	
	/** @var string Firstname */
	public 		$firstname;
	
	/** @var string e-mail */
	public 		$email;
	
	/** @var string Password */
	public 		$passwd;
	
	/** @var datetime Password */
	public 		$last_passwd_gen;
	
	public $stats_date_from;
	public $stats_date_to;
	
	/** @var boolean Status */
	public 		$active = 1;
	
	
 	protected 	$fieldsRequired = array('lastname', 'firstname', 'email', 'passwd', 'id_profile');
 	protected 	$fieldsSize = array('lastname' => 32, 'firstname' => 32, 'email' => 128, 'passwd' => 32);
 	protected 	$fieldsValidate = array('lastname' => 'isName', 'firstname' => 'isName', 'email' => 'isEmail', 
		'passwd' => 'isPasswdAdmin', 'active' => 'isBool', 'id_profile' => 'isInt');
	
	protected 	$table = 'employee';
	protected 	$identifier = 'id_employee';

	public	function getFields()
	{
	 	parent::validateFields();
		
		$fields['id_profile'] = intval($this->id_profile);
		$fields['lastname'] = pSQL(Tools::strtoupper($this->lastname));
		$fields['firstname'] = pSQL(Tools::ucfirst($this->firstname));
		$fields['email'] = pSQL($this->email);
		$fields['passwd'] = pSQL($this->passwd);
		$fields['last_passwd_gen'] = pSQL($this->last_passwd_gen);
		$fields['stats_date_from'] = pSQL($this->stats_date_from);
		$fields['stats_date_to'] = pSQL($this->stats_date_to);
		$fields['active'] = intval($this->active);
		
		return $fields;
	}
	
	/**
	 * Return all employee id and email
	 *
	 * @return array Employees
	 */
	static public function getEmployees()
	{
		return (Db::getInstance()->ExecuteS('
		SELECT `id_employee`, CONCAT(`firstname`, \' \', `lastname`) AS "name"
		  FROM `'._DB_PREFIX_.'employee`
		WHERE `active` = 1
		ORDER BY `email`'));
	}
	
	public function add($autodate = true, $nullValues = true)
	{
		$this->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-'.Configuration::get('PS_PASSWD_TIME_BACK').'minutes'));
	 	return parent::add($autodate, $nullValues);
	}
		
	/**
	  * Return employee instance from its e-mail (optionnaly check password)
	  * 
	  * @param string $email e-mail
	  * @param string $passwd Password is also checked if specified
	  * @return Employee instance
	  */
	public function getByemail($email, $passwd = NULL)
	{
	 	if (!Validate::isEmail($email) OR ($passwd != NULL AND !Validate::isPasswd($passwd)))
	 		die(Tools::displayError());

		$result = Db::getInstance()->getRow('
		SELECT * 
		FROM `'._DB_PREFIX_.'employee`
		WHERE `active` = 1
		AND `email` = \''.pSQL($email).'\'
		'.($passwd ? 'AND `passwd` = \''.Tools::encrypt($passwd).'\'' : ''));
		if (!$result)
			return false;
		$this->id = $result['id_employee'];
		$this->id_profile = $result['id_profile'];
		foreach ($result AS $key => $value)
			if (key_exists($key, $this))
				$this->{$key} = $value;
		return $this;
	}
	
	static public function employeeExists($email)
	{
	 	if (!Validate::isEmail($email))
	 		die (Tools::displayError());
	 	
		$result = Db::getInstance()->getRow('
		SELECT `id_employee`
		FROM `'._DB_PREFIX_.'employee`
		WHERE `email` = \''.pSQL($email).'\'');
		return isset($result['id_employee']);
	}

	/**
	  * Check if employee password is the right one
	  * 
	  * @param string $passwd Password
	  * @return boolean result
	  */
	static public function checkPassword($id_employee, $passwd)
	{
	 	if (!Validate::isUnsignedId($id_employee) OR !Validate::isPasswd($passwd, 8))
	 		die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT `id_employee`
		FROM `'._DB_PREFIX_.'employee`
		WHERE `id_employee` = '.intval($id_employee).' AND `passwd` = \''.pSQL($passwd).'\'');

		return isset($result['id_employee']) ? $result['id_employee'] : false;
	}
}

?>
