<?php

/**
  * Customers tab for admin panel, AdminContacts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminGroups extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'group';
	 	$this->className = 'Group';
		$this->lang = true;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;
		
		$this->_select = 'count(cg.id_customer) as nb';
		$this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer_group cg on (cg.id_group = a.id_group)';
		$this->_group = 'GROUP BY a.id_group';
		$this->_listSkipDelete = array(1);

 		$this->fieldsDisplay = array(
		'id_group' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 80, 'filter_key' => 'b!name'),
		'reduction' => array('title' => $this->l('Reduction'), 'width' => 50, 'align' => 'right'),
		'nb' => array('title' => $this->l('Members'), 'width' => 25, 'align' => 'center'),
		'date_add' => array('title' => $this->l('Creation date'), 'width' => 60, 'type' => 'date'));

		parent::__construct();
	}

	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width3">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/tab-groups.gif" />'.$this->l('Group').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
				$reduction = htmlentities($this->getFieldValue($obj, 'reduction'), ENT_COMPAT, 'UTF-8');
				echo '
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Reduction:').' </label>
				<div class="margin-form">
					<input type="text" size="5" name="reduction" value="'.($reduction ? $reduction : '0').'" /> '.$this->l('%').'
					<p>'.$this->l('Will automatically apply this value as a reduction on ALL shop\'s products for this group\'s members').'</p>
				</div>
				<div class="clear">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	public function viewgroup()
	{
		global $cookie;
		
		$currentIndex = 'index.php?tab=AdminGroups';
		$obj = $this->loadObject(true);
		$group = new Group(intval($obj->id));
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		
		echo '
		<fieldset style="width: 400px">
			<div style="float: right"><a href="'.$currentIndex.'&updategroup&id_group='.$obj->id.'&token='.$this->token.'"><img src="../img/admin/edit.gif" /></a></div>
			<span style="font-weight: bold; font-size: 14px;">'.strval($obj->name[intval($cookie->id_lang)]).'</span>
			<div class="clear">&nbsp;</div>
			'.$this->l('Reduction:').' '.floatval($obj->reduction).$this->l('%').'
		</fieldset>
		<div class="clear">&nbsp;</div>';

		$customers = $obj->getCustomers();
		$this->fieldsDisplay = (array(
			'ID' => array('title' => $this->l('ID')),
			'sex' => array('title' => $this->l('Sex')),
			'name' => array('title' => $this->l('Name')),
			'e-mail' => array('title' => $this->l('e-mail')),
			'birthdate' => array('title' => $this->l('Birth date')),
			'register_date' => array('title' => $this->l('Register date')),
			'orders' => array('title' => $this->l('Orders')),
			'status' => array('title' => $this->l('Status')),
			'actions' => array('title' => $this->l('Actions'))
		));

		if (isset($customers) AND !empty($customers) AND $nbCustomers = sizeof($customers))
		{
			echo '<h2>'.$this->l('Customers member of this group').' ('.$nbCustomers.')</h2>
			<table cellspacing="0" cellpadding="0" class="table widthfull">
				<tr>';
			foreach ($this->fieldsDisplay AS $field)
				echo '<th'.(isset($field['width']) ? 'style="width: '.$field['width'].'"' : '').'>'.$field['title'].'</th>';
			echo '
				</tr>';
			$irow = 0;
			foreach ($customers AS $k => $customer)
			{
				$imgGender = $customer['id_gender'] == 1 ? '<img src="../img/admin/male.gif" alt="'.$this->l('Male').'" />' : ($customer['id_gender'] == 2 ? '<img src="../img/admin/female.gif" alt="'.$this->l('Female').'" />' : '');
				echo '
				<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
					<td>'.$customer['id_customer'].'</td>
					<td class="center">'.$imgGender.'</td>
					<td>'.stripslashes($customer['lastname']).' '.stripslashes($customer['firstname']).'</td>
					<td>'.stripslashes($customer['email']).'<a href="mailto:'.stripslashes($customer['email']).'"> <img src="../img/admin/email_edit.gif" alt="'.$this->l('Write to this customer').'" /></a></td>
					<td>'.Tools::displayDate($customer['birthday'], intval($cookie->id_lang)).'</td>
					<td>'.Tools::displayDate($customer['date_add'], intval($cookie->id_lang)).'</td>
					<td>'.Order::getCustomerNbOrders($customer['id_customer']).'</td>
					<td class="center"><img src="../img/admin/'.($customer['active'] ? 'enabled.gif' : 'forbbiden.gif').'" alt="" /></td>
					<td class="center" width="60px">
						<a href="index.php?tab=AdminCustomers&id_customer='.$customer['id_customer'].'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'">
						<img src="../img/admin/details.gif" alt="'.$this->l('View orders').'" /></a>
						<a href="index.php?tab=AdminCustomers&id_customer='.$customer['id_customer'].'&addcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'">
						<img src="../img/admin/edit.gif" alt="'.$this->l('Modify this customer').'" /></a>
						<a href="index.php?tab=AdminCustomers&id_customer='.$customer['id_customer'].'&deletecustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure?', __CLASS__, true, false).'\');">
						<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this customer').'" /></a>
					</td>
				</tr>';
			}
			echo '</table>';
		}
	}

	public function postProcess()
	{
		global $currentIndex;
		
		$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
		
		if (isset($_GET['delete'.$this->table]))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->id == 1)
						$this->_errors[] = Tools::displayError('You cannot delete default group');
					else
					{
						if ($object->delete())
							Tools::redirectAdmin($currentIndex.'&conf=1&token='.$token);
						$this->_errors[] = Tools::displayError('an error occurred during deletion');
					}
				}
				else
					$this->_errors[] = Tools::displayError('an error occurred while deleting object').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		else
			parent::postProcess();
	}
}
