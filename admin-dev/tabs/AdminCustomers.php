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

class AdminCustomers extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'customer';
	 	$this->className = 'Customer';
	 	$this->lang = false;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;
		$this->deleted = true;

		$this->_select = '(YEAR(NOW()) - YEAR(birthday)) as age, (
			SELECT c.date_add FROM '._DB_PREFIX_.'guest g
			LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest
			WHERE g.id_customer = a.id_customer
			ORDER BY c.date_add DESC
			LIMIT 1
		) as connect';
		$genders = array(1 => $this->l('M'), 2 => $this->l('F'), 9 => $this->l('?'));
 		$this->fieldsDisplay = array(
		'id_customer' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'id_gender' => array('title' => $this->l('Gender'), 'width' => 25, 'align' => 'center', 'icon' => array(1 => 'male.gif', 2 => 'female.gif', 'default' => 'unknown.gif'), 'orderby' => false, 'type' => 'select', 'select' => $genders, 'filter_key' => 'a!id_gender'),
		'lastname' => array('title' => $this->l('Last Name'), 'width' => 80),
		'firstname' => array('title' => $this->l('First name'), 'width' => 60),
		'email' => array('title' => $this->l('E-mail address'), 'width' => 120, 'maxlength' => 19),
		'age' => array('title' => $this->l('Age'), 'width' => 30, 'search' => false),
		'active' => array('title' => $this->l('Enabled'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false),
		'newsletter' => array('title' => $this->l('News.'), 'width' => 25, 'align' => 'center', 'type' => 'bool', 'icon' => array(0 => 'disabled.gif', 1 => 'enabled.gif'), 'orderby' => false),
		'optin' => array('title' => $this->l('Opt.'), 'width' => 25, 'align' => 'center', 'type' => 'bool', 'icon' => array(0 => 'disabled.gif', 1 => 'enabled.gif'), 'orderby' => false),
		'date_add' => array('title' => $this->l('Registration'), 'width' => 60, 'type' => 'date'),
		'connect' => array('title' => $this->l('Connection'), 'width' => 60, 'type' => 'datetime', 'search' => false));

		$this->optionTitle = $this->l('Customers options');
		$this->_fieldsOptions = array(
			'PS_PASSWD_TIME_FRONT' => array('title' => $this->l('Password regenerate:'), 'desc' => $this->l('Security minimum time to wait for regenerate a new password'), 'cast' => 'intval', 'size' => 5, 'type' => 'text', 'suffix' => ' minutes'),
		);

		parent::__construct();
	}
	
	public function postProcess()
	{
		global $currentIndex;
		
		if (Tools::getValue('submitAdd'.$this->table))
		{
		 	/* Checking fields validity */
			$this->validateRules();
			if (!sizeof($this->_errors))
			{
				$id = intval(Tools::getValue('id_'.$this->table));
				if (isset($id) AND !empty($id))
				{
					if ($this->tabAccess['edit'] !== '1')
						$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
					else
					{
						$object = new $this->className($id);
						if (Validate::isLoadedObject($object))
						{
							$customer_email = strval(Tools::getValue('email'));
							
							// check if e-mail already used
							if ($customer_email != $object->email)
							{
								$customer = new Customer();
								$customer->getByEmail($customer_email);
								if ($customer->id)
									$this->_errors[] = Tools::displayError('an account already exists for this e-mail address:').' '.$customer_email;
							}
							
							// Updating customer's group
							if (!sizeof($this->_errors))
							{
								$groupList = Tools::getValue('groupBox');
								$object->cleanGroups();
								if (is_array($groupList) AND sizeof($groupList) > 0)
									$object->addGroups($groupList);
							}
						}
						else
							$this->_errors[] = Tools::displayError('an error occurred while loading object').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					}
				}
			}
		}
		return parent::postProcess();
	}

	public function viewcustomer()
	{
		global $currentIndex, $cookie;

		$irow = 0;
		$configurations = Configuration::getMultiple(array('PS_LANG_DEFAULT', 'PS_CURRENCY_DEFAULT'));
		$defaultLanguage = intval($configurations['PS_LANG_DEFAULT']);
		$defaultCurrency = intval($configurations['PS_CURRENCY_DEFAULT']);
		$customer = $this->loadObject();
		$customerStats = $customer->getStats();
		$addresses = $customer->getAddresses($defaultLanguage);
		$products = $customer->getBoughtProducts();
		$discounts = Discount::getCustomerDiscounts($defaultLanguage, $customer->id, false, false);
		$orders = Order::getCustomerOrders($customer->id);
		$carts = Cart::getCustomerCarts($customer->id);
		$groups = $customer->getGroups();
		$referrers = Referrer::getReferrers($customer->id);

		echo '
		<div style="float: left">
		<fieldset style="width: 400px"><div style="float: right"><a href="'.$currentIndex.'&addcustomer&id_customer='.$customer->id.'&token='.$this->token.'"><img src="../img/admin/edit.gif" /></a></div>
			<span style="font-weight: bold; font-size: 14px;">'.$customer->firstname.' '.$customer->lastname.'</span>
			<img src="../img/admin/'.($customer->id_gender == 2 ? 'female' : ($customer->id_gender == 1 ? 'male' : 'unknown')).'.gif" style="margin-bottom: 5px" /><br />
			<a href="mailto:'.$customer->email.'" style="text-decoration: underline; color: blue">'.$customer->email.'</a><br /><br />
			'.$this->l('ID:').' '.sprintf('%06d', $customer->id).'<br />
			'.$this->l('Registration date:').' '.Tools::displayDate($customer->date_add, intval($cookie->id_lang), true).'<br />
			'.$this->l('Last visit:').' '.($customerStats['last_visit'] ? Tools::displayDate($customerStats['last_visit'], intval($cookie->id_lang), true) : $this->l('never')).'
		</fieldset>
		</div>
		<div style="float: left; margin-left: 50px">
		<fieldset style="width: 300px"><div style="float: right"><a href="'.$currentIndex.'&addcustomer&id_customer='.$customer->id.'&token='.$this->token.'"><img src="../img/admin/edit.gif" /></a></div>
			'.$this->l('Newsletter:').' '.($customer->newsletter ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').'<br />
			'.$this->l('Opt-in:').' '.($customer->optin ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').'<br />
			'.$this->l('Age:').' '.$customerStats['age'].' '.((!empty($customer->birthday['age'])) ? '('.Tools::displayDate($customer->birthday, intval($cookie->id_lang)).')' : $this->l('unknown')).'<br /><br />
			'.$this->l('Last update:').' '.Tools::displayDate($customer->date_upd, intval($cookie->id_lang), true).'<br />
			'.$this->l('Status:').' '.($customer->active ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').'
		</fieldset>
		</div>
		<div class="clear">&nbsp;</div>';

		// display hook specified to this page : AdminCustomers
		if (($hook = Module::hookExec('adminCustomers', array('id_customer' => $customer->id))) !== false)
			echo '<div>'.$hook.'</div>';

		echo '<h2>'.$this->l('Groups').' ('.sizeof($groups).')</h2>';
		if ($groups AND sizeof($groups))
		{
			echo '
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th class="center">'.$this->l('ID').'</th>
					<th class="center">'.$this->l('Name').'</th>
					<th class="center">'.$this->l('Actions').'</th>
				</tr>';
			$tokenGroups = Tools::getAdminToken('AdminGroups'.intval(Tab::getIdFromClassName('AdminGroups')).intval($cookie->id_employee));
			foreach ($groups AS $group)
			{
				$objGroup = new Group($group);
				echo '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').' style="cursor: pointer" onclick="document.location = \'?tab=AdminGroups&id_group='.$objGroup->id.'&viewgroup&token='.$tokenGroups.'\'">
					<td class="center">'.$objGroup->id.'</td>
					<td>'.$objGroup->name[$defaultLanguage].'</td>
					<td align="center"><a href="?tab=AdminGroups&id_group='.$objGroup->id.'&viewgroup&token='.$tokenGroups.'"><img src="../img/admin/details.gif" /></a></td>
				</tr>';
			}
			echo '
			</table>';
		}
		echo '<div class="clear">&nbsp;</div>';
		echo '<h2>'.$this->l('Orders').' ('.sizeof($orders).')</h2>';
		if ($orders AND sizeof($orders))
		{
			echo '
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th class="center">'.$this->l('ID').'</th>
					<th class="center">'.$this->l('Date').'</th>
					<th class="center">'.$this->l('Quantity').'</th>
					<th class="center">'.$this->l('Total').'</th>
					<th class="center">'.$this->l('Payment').'</th>
					<th class="center">'.$this->l('State').'</th>
					<th class="center">'.$this->l('Actions').'</th>
				</tr>';
			$tokenOrders = Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee));
			foreach ($orders AS $order)
				echo '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').' style="cursor: pointer" onclick="document.location = \'?tab=AdminOrders&id_order='.$order['id_order'].'&vieworder&token='.$tokenOrders.'\'">
					<td class="center">'.sprintf('%06d', $order['id_order']).'</td>
					<td>'.Tools::displayDate($order['date_add'], intval($cookie->id_lang), true).'</td>
					<td align="right">'.$order['nb_products'].'</td>
					<td align="right">'.Tools::displayPrice($order['total_paid'], new Currency(intval($order['id_currency']))).'</td>
					<td>'.$order['payment'].'</td>
					<td>'.$order['order_state'].'</td>
					<td align="center"><a href="?tab=AdminOrders&id_order='.$order['id_order'].'&vieworder&token='.$tokenOrders.'"><img src="../img/admin/details.gif" /></a></td>
				</tr>';
			echo '
			</table>';
		}
		else
			echo $customer->firstname.' '.$customer->lastname.' '.$this->l('has placed no orders yet');
		if ($products AND sizeof($products))
		{
			echo '<div class="clear">&nbsp;</div>
			<h2>'.$this->l('Products').' ('.sizeof($products).')</h2>
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th class="center">'.$this->l('Date').'</th>
					<th class="center">'.$this->l('Name').'</th>
					<th class="center">'.$this->l('Quantity').'</th>
					<th class="center">'.$this->l('Actions').'</th>
				</tr>';
			$tokenOrders = Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee));
			foreach ($products AS $product)
				echo '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').' style="cursor: pointer" onclick="document.location = \'?tab=AdminOrders&id_order='.$product['id_order'].'&vieworder&token='.$tokenOrders.'\'">
					<td>'.Tools::displayDate($product['date_add'], intval($cookie->id_lang), true).'</td>
					<td>'.$product['product_name'].'</td>
					<td align="right">'.$product['product_quantity'].'</td>
					<td align="center"><a href="?tab=AdminOrders&id_order='.$product['id_order'].'&vieworder&token='.$tokenOrders.'"><img src="../img/admin/details.gif" /></a></td>
				</tr>';
			echo '
			</table>';
		}
		echo '<div class="clear">&nbsp;</div>
		<h2>'.$this->l('Addresses').' ('.sizeof($addresses).')</h2>';
		if (sizeof($addresses))
		{
			echo '
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th>'.$this->l('Company').'</th>
					<th>'.$this->l('Name').'</th>
					<th>'.$this->l('Address').'</th>
					<th>'.$this->l('Country').'</th>
					<th>'.$this->l('Phone number(s)').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>';
			$tokenAddresses = Tools::getAdminToken('AdminAddresses'.intval(Tab::getIdFromClassName('AdminAddresses')).intval($cookie->id_employee));
			foreach ($addresses AS $address)
				echo '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').'>
					<td>'.($address['company'] ? $address['company'] : '--').'</td>
					<td>'.$address['firstname'].' '.$address['lastname'].'</td>
					<td>'.$address['address1'].($address['address2'] ? ' '.$address['address2'] : '').' '.$address['postcode'].' '.$address['city'].'</td>
					<td>'.$address['country'].'</td>
					<td>'.($address['phone'] ? ($address['phone'].($address['phone_mobile'] ? '<br />'.$address['phone_mobile'] : '')) : ($address['phone_mobile'] ? '<br />'.$address['phone_mobile'] : '--')).'</td>
					<td align="center">
						<a href="?tab=AdminAddresses&id_address='.$address['id_address'].'&addaddress&token='.$tokenAddresses.'"><img src="../img/admin/edit.gif" /></a>
						<a href="?tab=AdminAddresses&id_address='.$address['id_address'].'&deleteaddress&token='.$tokenAddresses.'"><img src="../img/admin/delete.gif" /></a>
					</td>
				</tr>';
			echo '
			</table>';
		}
		else
			echo $customer->firstname.' '.$customer->lastname.' '.$this->l('has registered no addresses yet').'.';
		echo '<div class="clear">&nbsp;</div>
		<h2>'.$this->l('Discounts').' ('.sizeof($discounts).')</h2>';
		if (sizeof($discounts))
		{
			echo '
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th>'.$this->l('ID').'</th>
					<th>'.$this->l('Code').'</th>
					<th>'.$this->l('Type').'</th>
					<th>'.$this->l('Value').'</th>
					<th>'.$this->l('Qty available').'</th>
					<th>'.$this->l('Status').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>';
			$tokenDiscounts = Tools::getAdminToken('AdminDiscounts'.intval(Tab::getIdFromClassName('AdminDiscounts')).intval($cookie->id_employee));
			foreach ($discounts AS $discount)
			{
				echo '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').'>
					<td align="center">'.$discount['id_discount'].'</td>
					<td>'.$discount['name'].'</td>
					<td>'.$discount['type'].'</td>
					<td align="right">'.$discount['value'].'</td>
					<td align="center">'.$discount['quantity_for_user'].'</td>
					<td align="center"><img src="../img/admin/'.($discount['active'] ? 'enabled.gif' : 'disabled.gif').'" alt="'.$this->l('Status').'" title="'.$this->l('Status').'" /></td>
					<td align="center">
						<a href="?tab=AdminDiscounts&id_discount='.$discount['id_discount'].'&adddiscount&token='.$tokenDiscounts.'"><img src="../img/admin/edit.gif" /></a>
						<a href="?tab=AdminDiscounts&id_discount='.$discount['id_discount'].'&deletediscount&token='.$tokenDiscounts.'"><img src="../img/admin/delete.gif" /></a>
					</td>
				</tr>';
			}
			echo '
			</table>';

		}
		else
			echo $customer->firstname.' '.$customer->lastname.' '.$this->l('has no discount vouchers').'.';
		echo '<div class="clear">&nbsp;</div>';
		
		echo '<h2>'.$this->l('Carts').' ('.sizeof($carts).')</h2>';
		if ($carts AND sizeof($carts))
		{
			echo '
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th class="center">'.$this->l('ID').'</th>
					<th class="center">'.$this->l('Date').'</th>
					<th class="center">'.$this->l('Total').'</th>
					<th class="center">'.$this->l('Carrier').'</th>
					<th class="center">'.$this->l('Actions').'</th>
				</tr>';
			$tokenCarts = Tools::getAdminToken('AdminCarts'.intval(Tab::getIdFromClassName('AdminCarts')).intval($cookie->id_employee));
			foreach ($carts AS $cart)
			{
				$cartI = new Cart(intval($cart['id_cart']));
				$summary = $cartI->getSummaryDetails();
				$currency = new Currency(intval($cart['id_currency']));
				$carrier = new Carrier(intval($cart['id_carrier']));
				echo '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').' style="cursor: pointer" onclick="document.location = \'?tab=AdminCarts&id_cart='.$cart['id_cart'].'&viewcart&token='.$tokenCarts.'\'">
					<td class="center">'.sprintf('%06d', $cart['id_cart']).'</td>
					<td>'.Tools::displayDate($cart['date_add'], intval($cookie->id_lang), true).'</td>
					<td align="right">'.Tools::displayPrice($summary['total_price'], $currency).'</td>
					<td>'.$carrier->name.'</td>
					<td align="center"><a href="?tab=AdminCarts&id_cart='.$cart['id_cart'].'&viewcart&token='.$tokenCarts.'"><img src="../img/admin/details.gif" /></a></td>
				</tr>';
			}
			echo '
			</table>';
		}
		else
			echo $this->l('No cart available').'.';
		echo '<div class="clear">&nbsp;</div>';

		/* Last connections */
        $connections = $customer->getLastConnections();
        if (sizeof($connections))    
        {
            echo '<h2>'.$this->l('Last connections').'</h2>
            <table cellspacing="0" cellpadding="0" class="table">
                <tr>
                    <th style="width: 200px">'.$this->l('Date').'</th>
                    <th style="width: 100px">'.$this->l('Pages viewed').'</th>
                    <th style="width: 100px">'.$this->l('Total time').'</th>
                    <th style="width: 100px">'.$this->l('Origin').'</th>
                    <th style="width: 100px">'.$this->l('IP Address').'</th>
                </tr>';
            foreach ($connections as $connection)
                echo '<tr>
                        <td>'.Tools::displayDate($connection['date_add'], intval($cookie->id_lang), true).'</td>
                        <td>'.intval($connection['pages']).'</td>
                        <td>'.$connection['time'].'</td>
                        <td>'.($connection['http_referer'] ? preg_replace('/^www./', '', parse_url($connection['http_referer'], PHP_URL_HOST)) : $this->l('Direct link')).'</td>
                        <td>'.$connection['ipaddress'].'</td>
                    </tr>';
            echo '</table><div class="clear">&nbsp;</div>';
        }
        if (sizeof($referrers))    
        {
            echo '<h2>'.$this->l('Referrers').'</h2>
            <table cellspacing="0" cellpadding="0" class="table">
                <tr>
                    <th style="width: 200px">'.$this->l('Date').'</th>
                    <th style="width: 200px">'.$this->l('Name').'</th>
                </tr>';
            foreach ($referrers as $referrer)
                echo '<tr>
                        <td>'.Tools::displayDate($referrer['date_add'], intval($cookie->id_lang), true).'</td>
                        <td>'.$referrer['name'].'</td>
                    </tr>';
            echo '</table><div class="clear">&nbsp;</div>';
        }
        echo '<a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to customer list').'</a><br />';
    }

	public function displayForm()
	{
		global $currentIndex;
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$birthday = explode('-', $this->getFieldValue($obj, 'birthday'));
		$customer_groups = $obj->getGroups();
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width3">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/tab-customers.gif" />'.$this->l('Customer').'</legend>
				<label>'.$this->l('Gender:').' </label>
				<div class="margin-form">
					<input type="radio" size="33" name="id_gender" id="gender_1" value="1" '.($this->getFieldValue($obj, 'id_gender') == 1 ? 'checked="checked" ' : '').'/>
					<label class="t" for="gender_1"> '.$this->l('Male').'</label>
					<input type="radio" size="33" name="id_gender" id="gender_2" value="2" '.($this->getFieldValue($obj, 'id_gender') == 2 ? 'checked="checked" ' : '').'/>
					<label class="t" for="gender_2"> '.$this->l('Female').'</label>
					<input type="radio" size="33" name="id_gender" id="gender_3" value="9" '.(($this->getFieldValue($obj, 'id_gender') == 9 OR !$this->getFieldValue($obj, 'id_gender')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="gender_3"> '.$this->l('Unknown').'</label>
				</div>
				<label>'.$this->l('Last name:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="lastname" value="'.htmlentities($this->getFieldValue($obj, 'lastname'), ENT_COMPAT, 'UTF-8').'" style="text-transform: uppercase;" /> <sup>*</sup>
					<span class="hint" name="help_box">'.$this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
				</div>
				<label>'.$this->l('First name:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="firstname" value="'.htmlentities($this->getFieldValue($obj, 'firstname'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
				</div>
				<label>'.$this->l('Password:').' </label>
				<div class="margin-form">
					<input type="password" size="33" name="passwd" value="" /> '.(!$obj->id ? '<sup>*</sup>' : '').'
					<p>'.($obj->id ? $this->l('Leave blank if no change') : $this->l('5 characters min., only letters, numbers, or').' -_').'</p>
				</div>
				<label>'.$this->l('E-mail address:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="email" value="'.htmlentities($this->getFieldValue($obj, 'email'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Birthday:').' </label>';
				$sl_year = ($this->getFieldValue($obj, 'birthday')) ? $birthday[0] : 0;
				$years = Tools::dateYears();
				$sl_month = ($this->getFieldValue($obj, 'birthday')) ? $birthday[1] : 0;
				$months = Tools::dateMonths();
				$sl_day = ($this->getFieldValue($obj, 'birthday')) ? $birthday[2] : 0;
				$days = Tools::dateDays();
				$tab_months = array(
					$this->l('January'),
					$this->l('February'),
					$this->l('March'),
					$this->l('April'),
					$this->l('May'),
					$this->l('June'),
					$this->l('July'),
					$this->l('August'),
					$this->l('September'),
					$this->l('October'),
					$this->l('November'),
					$this->l('December'));
				echo '
					<div class="margin-form">
					<select name="days">
						<option value="">-</option>';
						foreach ($days as $v)
							echo '<option value="'.$v.'" '.($sl_day == $v ? 'selected="selected"' : '').'>'.$v.'</option>';
					echo '
					</select>
					<select name="months">
						<option value="">-</option>';
						foreach ($months as $k => $v)
							echo '<option value="'.$k.'" '.($sl_month == $k ? 'selected="selected"' : '').'>'.$this->l($v).'</option>';
					echo '</select>
					<select name="years">
						<option value="">-</option>';
						foreach ($years as $v)
							echo '<option value="'.$v.'" '.($sl_year == $v ? 'selected="selected"' : '').'>'.$v.'</option>';
					echo '</select>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.($this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.(!$this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Allow or disallow this customer to log in').'</p>
				</div>
				<label>'.$this->l('Newsletter:').' </label>
				<div class="margin-form">
					<input type="radio" name="newsletter" id="newsletter_on" value="1" '.($this->getFieldValue($obj, 'newsletter') ? 'checked="checked" ' : '').'/>
					<label class="t" for="newsletter_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="newsletter" id="newsletter_off" value="0" '.(!$this->getFieldValue($obj, 'newsletter') ? 'checked="checked" ' : '').'/>
					<label class="t" for="newsletter_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Customer will receive your newsletter via e-mail').'</p>
				</div>
				<label>'.$this->l('Opt-in:').' </label>
				<div class="margin-form">
					<input type="radio" name="optin" id="optin_on" value="1" '.($this->getFieldValue($obj, 'optin') ? 'checked="checked" ' : '').'/>
					<label class="t" for="optin_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="optin" id="optin_off" value="0" '.(!$this->getFieldValue($obj, 'optin') ? 'checked="checked" ' : '').'/>
					<label class="t" for="optin_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Customer will receive your ads via e-mail').'</p>
				</div>
				<label>'.$this->l('Groups:').' </label>
				<div class="margin-form">';
					$groups = Group::getGroups($defaultLanguage, true);
					if (sizeof($groups))
					{
						echo '
					<table cellspacing="0" cellpadding="0" class="table" style="width: 29.5em;">
						<tr>
							<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'groupBox[]\', this.checked)" /></th>
							<th>'.$this->l('ID').'</th>
							<th>'.$this->l('Group name').'</th>
						</tr>';
						$irow = 0;
						foreach ($groups as $group)
						{
							echo '
							<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
								<td>'.($group['id_group'] != 1 ? '<input type="checkbox" name="groupBox[]" class="groupBox" id="groupBox_'.$group['id_group'].'" value="'.$group['id_group'].'" '.(in_array($group['id_group'], $customer_groups) ? 'checked="checked" ' : '').'/>' : '').'</td>
								<td>'.$group['id_group'].'</td>
								<td><label for="groupBox_'.$group['id_group'].'" class="t">'.$group['name'].'</label></td>
							</tr>';
						}
						echo '
					</table>
					<p style="padding:0px; margin:10px 0px 10px 0px;">'.$this->l('Mark all checkbox(es) of groups to which the customer is to be member').'<sup> *</sup></p>
					';
					} else
						echo '<p>'.$this->l('No group created').'</p>';
				echo '
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL)
	{
		global $cookie;
		return parent::getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
	}
	
	public function beforeDelete($object)
	{
		return $object->isUsed();
	}
}

?>
