<?php

/**
  * Employees tab for admin panel, AdminEmployees.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminEmployees extends AdminTab
{
 	/** @var array profiles list */
	private $profilesArray = array();
 
	public function __construct()
	{
	 	global $cookie;
	 	
	 	$this->table = 'employee';
	 	$this->className = 'Employee';
	 	$this->lang = false;
	 	$this->edit = true; 
	 	$this->delete = true;		
 		$this->_select = 'pl.`name` AS profile';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'profile` p ON a.`id_profile` = p.`id_profile` 
		LEFT JOIN `'._DB_PREFIX_.'profile_lang` pl ON (pl.`id_profile` = p.`id_profile` AND pl.`id_lang` = '.intval($cookie->id_lang).')';
		
		$profiles = Profile::getProfiles(intval($cookie->id_lang));
		if (!$profiles)
			$this->_errors[] = Tools::displayError('No profile');
		else
			foreach ($profiles AS $profile)
				$this->profilesArray[$profile['name']] = $profile['name'];
		
		$this->fieldsDisplay = array(
		'id_employee' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'lastname' => array('title' => $this->l('Last name'), 'width' => 130),
		'firstname' => array('title' => $this->l('First name'), 'width' => 130),
		'email' => array('title' => $this->l('E-mail address'), 'width' => 180), 
		'profile' => array('title' => $this->l('Profile'), 'width' => 90, 'type' => 'select', 'select' => $this->profilesArray, 'filter_key' => 'p!name'),
		'active' => array('title' => $this->l('Can log in'), 'align' => 'center', 'active' => 'status', 'type' => 'bool'));

		$this->optionTitle = $this->l('Employees options');
		$this->_fieldsOptions = array(
			'PS_PASSWD_TIME_BACK' => array('title' => $this->l('Password regenerate:'), 'desc' => $this->l('Security minimum time to wait for regenerate a new password'), 'cast' => 'intval', 'size' => 5, 'type' => 'text', 'suffix' => ' minutes'),
		);

		parent::__construct();
	}
	
	protected function _childValidation() 
	{
		$email = $this->getFieldValue($this->loadObject(true), 'email');
		if (!Validate::isEmail($email))
	 		$this->_errors[] = Tools::displayError('Invalid e-mail');
		else if (Employee::employeeExists($email) AND !Tools::getValue('id_employee'))
			$this->_errors[] = Tools::displayError('an account already exists for this e-mail address:').' '.$email;
	}

	public function displayForm()
	{
		global $currentIndex, $cookie;
		
		$obj = $this->loadObject(true);
		$profiles = Profile::getProfiles(intval($cookie->id_lang));

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/nav-user.gif" />'.$this->l('Employees').'</legend>
				<label>'.$this->l('Last name:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="lastname" value="'.htmlentities($this->getFieldValue($obj, 'lastname'), ENT_COMPAT, 'UTF-8').'" style="text-transform: uppercase;" /> <sup>*</sup>
				</div>
				<label>'.$this->l('First name:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="firstname" value="'.htmlentities($this->getFieldValue($obj, 'firstname'), ENT_COMPAT, 'UTF-8').'" style="text-transform: capitalize;" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Password:').' </label>
				<div class="margin-form">
					<input type="password" size="33" name="passwd" value="" /> <sup>*</sup>
					<p>'.($obj->id ? $this->l('Leave blank if you do not want to change your password') : $this->l('Min. 8 characters; use only letters, numbers or').' -_').'</p>
				</div>
				<label>'.$this->l('E-mail address:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="email" value="'.htmlentities($this->getFieldValue($obj, 'email'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.($this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.(!$this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Allow or disallow this employee to log in to this Back Office').'</p>
				</div>
				<label>'.$this->l('Profile:').' </label>
				<div class="margin-form">
					<select name="id_profile">
						<option value="">---------</option>';
						/* Profile display */
						foreach ($profiles AS $profile)
						 	echo '
						<option value="'.$profile['id_profile'].'"'.($profile['id_profile'] === $this->getFieldValue($obj, 'id_profile') ? ' selected="selected"' : '').'>'.$profile['name'].'</option>';
				echo '</select> <sup>*</sup>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>