<?php

ini_set('display_errors', 'on');

class Followup extends Module
{
	function __construct()
	{
		$this->name = 'followup';
		$this->tab = 'Tools';
		$this->version = '1.0';

		$this->confKeys = array(
		'PS_FOLLOW_UP_ENABLE_1', 'PS_FOLLOW_UP_ENABLE_2', 'PS_FOLLOW_UP_ENABLE_3', 'PS_FOLLOW_UP_ENABLE_4', 
		'PS_FOLLOW_UP_AMOUNT_1', 'PS_FOLLOW_UP_AMOUNT_2', 'PS_FOLLOW_UP_AMOUNT_3', 'PS_FOLLOW_UP_AMOUNT_4', 
		'PS_FOLLOW_UP_DAYS_1', 'PS_FOLLOW_UP_DAYS_2', 'PS_FOLLOW_UP_DAYS_3', 'PS_FOLLOW_UP_DAYS_4',
		'PS_FOLLOW_UP_THRESHOLD_3',
		'PS_FOLLOW_UP_DAYS_THRESHOLD_4',
		'PS_FOLLOW_UP_CLEAN_DB');

		parent::__construct();

		$this->displayName = $this->l('Customers follow-up');
		$this->description = $this->l('Follow-up your customers with daily customized e-mails');
		$this->confirmUninstall = $this->l('Are you sure you want to delete all settings and your logs?');
	}
	
	public function install()
	{
		$logEmailTable = Db::getInstance()->Execute('
		CREATE TABLE '._DB_PREFIX_.'log_email (
		`id_log_email` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`id_email_type` INT UNSIGNED NOT NULL ,
		`id_discount` INT UNSIGNED NOT NULL ,
		`id_customer` INT UNSIGNED NULL ,
		`id_cart` INT UNSIGNED NULL ,		
		`date_add` DATETIME NOT NULL
		) ENGINE = MYISAM');
		
		foreach ($this->confKeys AS $key)
			Configuration::updateValue($key, 0);
			
		Configuration::updateValue('PS_FOLLOWUP_SECURE_KEY', strtoupper(Tools::passwdGen(16)));
			
		return parent::install();
	}
	
	public function uninstall()
	{
		foreach ($this->confKeys AS $key)
			Configuration::deleteByName($key);
			
		Configuration::deleteByName('PS_FOLLOWUP_SECURE_KEY');
		
		Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'log_email');

		return parent::uninstall();
	}
	
	public function getContent()
	{	
		/* Save settings */
		if (Tools::isSubmit('submitFollowUp'))	
			foreach ($this->confKeys AS $c)
				Configuration::updateValue($c, floatval(Tools::getValue($c)));
		
		/* Init */
		$conf = Configuration::getMultiple($this->confKeys);
		foreach ($this->confKeys AS $k)
			if (!isset($conf[$k]))
				$conf[$k] = '';
		$currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));

		$n1 = $this->cancelledCart(true);
		$n2 = $this->reOrder(true);
		$n3 = $this->bestCustomer(true);
		$n4 = $this->badCustomer(true);
		
		echo '
		<h2>'.$this->l('Customers follow-up').'</h2>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">			
			<fieldset style="width: 400px; float: left;">
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<p>'.$this->l('Four kinds of e-mail alerts in order to stay in touch with your customers!').'<br /><br />
				'.$this->l('Define settings and put this URL in crontab or call it manually daily:').'<br />
				<b>http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/followup/cron.php?secure_key='.Configuration::get('PS_FOLLOWUP_SECURE_KEY').'</b></p>
				<hr size="1" />
				<p><b>1. '.$this->l('Canceled carts').'</b><br /><br />'.$this->l('For each cancelled cart (with no order), generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="PS_FOLLOW_UP_ENABLE_1" value="1" style="vertical-align: middle;" '.($conf['PS_FOLLOW_UP_ENABLE_1'] == 1 ? 'checked="checked"' : '').' /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_AMOUNT_1" value="'.$conf['PS_FOLLOW_UP_AMOUNT_1'].'" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_DAYS_1" value="'.$conf['PS_FOLLOW_UP_DAYS_1'].'" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n1).' '.($n1 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<p><b>2. '.$this->l('Re-order').'</b><br /><br />'.$this->l('For each validated order, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="PS_FOLLOW_UP_ENABLE_2" value="1" style="vertical-align: middle;" '.($conf['PS_FOLLOW_UP_ENABLE_2'] == 1 ? 'checked="checked"' : '').' /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_AMOUNT_2" value="'.$conf['PS_FOLLOW_UP_AMOUNT_2'].'" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_DAYS_2" value="'.$conf['PS_FOLLOW_UP_DAYS_2'].'" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n2).' '.($n2 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<p><b>3. '.$this->l('Best customers').'</b><br /><br />'.$this->l('For each customer raising a threshold, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="PS_FOLLOW_UP_ENABLE_3" value="1" style="vertical-align: middle;" '.($conf['PS_FOLLOW_UP_ENABLE_3'] == 1 ? 'checked="checked"' : '').' /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_AMOUNT_3" value="'.$conf['PS_FOLLOW_UP_AMOUNT_3'].'" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Threshold').'</label>
				<div class="margin-form">'.($currency->format == 1 ? ' '.$currency->sign.' ' : '').'<input type="text" name="PS_FOLLOW_UP_THRESHOLD_3" value="'.$conf['PS_FOLLOW_UP_THRESHOLD_3'].'" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> '.($currency->format == 2 ? ' '.$currency->sign : '').'</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_DAYS_3" value="'.$conf['PS_FOLLOW_UP_DAYS_3'].'" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n3).' '.($n3 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<p><b>4. '.$this->l('Bad customers').'</b><br /><br />'.$this->l('For each customer who has already passed at least one order and with no orders since a given duration, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="PS_FOLLOW_UP_ENABLE_4" value="1" style="vertical-align: middle;" '.($conf['PS_FOLLOW_UP_ENABLE_4'] == 1 ? 'checked="checked"' : '').' /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_AMOUNT_4" value="'.$conf['PS_FOLLOW_UP_AMOUNT_4'].'" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Since x days').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_DAYS_THRESHOLD_4" value="'.$conf['PS_FOLLOW_UP_DAYS_THRESHOLD_4'].'" size="6" /> '.$this->l('day(s)').'</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="PS_FOLLOW_UP_DAYS_4" value="'.$conf['PS_FOLLOW_UP_DAYS_4'].'" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n4).' '.($n4 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<input type="checkbox" style="vertical-align: middle;" name="PS_FOLLOW_UP_CLEAN_DB" value="1" '.($conf['PS_FOLLOW_UP_CLEAN_DB'] == 1 ? 'checked="checked"' : '').' /> '.$this->l('Delete outdated discounts during each launch to clean database').'
				<hr size="1" />
				<center><input type="submit" name="submitFollowUp" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
			
			<style type="text/css">
				table tr th {
					text-align: center;
					font-weight: bold;
				}
				
				table tr td, table tr th {
					padding: 3px;
				}
				
				table tr td {
					text-align: right;
				}
				
				table { width: 460px; border: 1px solid #666; }
			</style>
			<fieldset style="width: 460px; margin-left: 10px; float: left;">
				<legend><img src="'.$this->_path.'logo-2.gif" alt="" title="" />'.$this->l('Statistics').'</legend>
				'.$this->l('Detailed statistics for last 30 days:').'<br /><br />
				<p style="font-size: 10px; font-weight: bold;">
				'.$this->l('S = Number of sent e-mails').'<br />
				'.$this->l('U = Number of discounts used (valid orders only)').'<br />
				'.$this->l('% = Conversion rate').'
				</p><br />
				<table border="1" style="font-size: 11px;">
					<tr>
						<th rowspan="2" style="width: 75px;">'.$this->l('Date').'</th>
						<th colspan="3">'.$this->l('Cancelled carts').'</th>
						<th colspan="3">'.$this->l('Re-order').'</th>
						<th colspan="3">'.$this->l('Best cust.').'</th>
						<th colspan="3">'.$this->l('Bad cust.').'</th>
					</tr>';
					
			$stats = Db::getInstance()->ExecuteS('
			SELECT DATE_FORMAT(l.date_add, \'%Y-%m-%d\') date_stat, l.id_email_type, COUNT(l.id_log_email) nb, 
			(SELECT COUNT(l2.id_discount) 
			FROM '._DB_PREFIX_.'log_email l2
			LEFT JOIN '._DB_PREFIX_.'order_discount od ON (od.id_discount = l2.id_discount)
			LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_order = od.id_order)
			WHERE l2.id_email_type = l.id_email_type AND l2.date_add = l.date_add AND od.id_order IS NOT NULL AND o.valid = 1) nb_used
			FROM '._DB_PREFIX_.'log_email l
			WHERE l.date_add >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
			GROUP BY l.date_add, l.id_email_type');
			
			$statsArray = array();
			foreach ($stats AS $stat)
			{
				$statsArray[$stat['date_stat']][$stat['id_email_type']]['nb'] = intval($stat['nb']);
				$statsArray[$stat['date_stat']][$stat['id_email_type']]['nb_used'] = intval($stat['nb_used']);
			}
			
			echo '
			<tr>
				<td class="center">'.$this->l('S').'</td>
				<td class="center">'.$this->l('U').'</td>
				<td class="center">%</td>
				<td class="center">'.$this->l('S').'</td>
				<td class="center">'.$this->l('U').'</td>
				<td class="center">%</td>
				<td class="center">'.$this->l('S').'</td>
				<td class="center">'.$this->l('U').'</td>
				<td class="center">%</td>
				<td class="center">'.$this->l('S').'</td>
				<td class="center">'.$this->l('U').'</td>
				<td class="center">%</td>
			</tr>';
			
			if (!sizeof($statsArray))
				echo '<tr><td colspan="13" style="font-weight: bold; text-align: center;">'.$this->l('No statistics yet').'</td></tr>';
			
			foreach ($statsArray AS $date_stat => $array)
			{
				$rates = array();
				for ($i = 1; $i != 5; $i++)
					if (isset($statsArray[$date_stat][$i]['nb']) AND isset($statsArray[$date_stat][$i]['nb_used']) AND $statsArray[$date_stat][$i]['nb_used'] > 0)
						$rates[$i] = number_format(($statsArray[$date_stat][$i]['nb'] / $statsArray[$date_stat][$i]['nb_used'])*100, 2, '.', '');
				
				echo '
				<tr>
					<td>'.$date_stat.'</td>';
					
				for ($i = 1; $i != 5; $i++)
				{
					echo '
					<td>'.(isset($statsArray[$date_stat][$i]['nb']) ? intval($statsArray[$date_stat][$i]['nb']) : 0).'</td>
					<td>'.(isset($statsArray[$date_stat][$i]['nb_used']) ? intval($statsArray[$date_stat][$i]['nb_used']) : 0).'</td>
					<td>'.(isset($rates[$i]) ? '<b>'.$rates[$i].'</b>' : '0.00').'</td>';
				}

				echo '
				</tr>';
			}
			
			echo '
				</table>
			</fieldset>
			<div class="clear"></div>
		</form>';
	}
	
	/* Log each sent e-mail */
	private function logEmail($id_email_type, $id_discount, $id_customer = NULL, $id_cart = NULL)
	{
		$values = array('id_email_type' => intval($id_email_type), 'id_discount' => intval($id_discount), 'date_add' => date('Y-m-d H:i:s'));
		if (!empty($id_cart))
			$values['id_cart'] = intval($id_cart);
		if (!empty($id_customer))
			$values['id_customer'] = intval($id_customer);
		Db::getInstance()->autoExecute(_DB_PREFIX_.'log_email', $values, 'INSERT');
	}

	/* Each cart which wasn't transformed into an order */
	private function cancelledCart($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT c.id_cart, c.id_lang, cu.id_customer, cu.firstname, cu.lastname, cu.email
		FROM '._DB_PREFIX_.'cart c
		LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_cart = c.id_cart)
		LEFT JOIN '._DB_PREFIX_.'customer cu ON (cu.id_customer = c.id_customer)
		WHERE DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= c.date_add AND cu.id_customer IS NOT NULL AND o.id_order IS NULL AND c.id_cart NOT IN 
		(SELECT id_cart FROM '._DB_PREFIX_.'log_email WHERE id_email_type = 1)');
		
		if ($count OR !sizeof($emails))
			return sizeof($emails);
		
		$conf = Configuration::getMultiple(array('PS_FOLLOW_UP_AMOUNT_1', 'PS_FOLLOW_UP_DAYS_1'));
		foreach ($emails AS $email)
		{
				$voucher = $this->createDiscount(1, floatval($conf['PS_FOLLOW_UP_AMOUNT_1']), intval($email['id_customer']), strftime('%Y-%m-%d', strtotime('+'.intval($conf['PS_FOLLOW_UP_DAYS_1']).' day')), $this->l('Discount for your cancelled cart'));				
				if ($voucher !== false)
				{
					$templateVars = array('{email}' => $email['email'], '{lastname}' => $email['lastname'], '{firstname}' => $email['firstname'], '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_1'], '{days}' => $conf['PS_FOLLOW_UP_DAYS_1'], '{voucher_num}' => $voucher->name);
					$result = Mail::Send(intval($email['id_lang']), 'followup_1', $this->l('Your cart and your discount'), $templateVars, $email['email'], $email['firstname'].' '.$email['lastname'], NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
					$this->logEmail(1, intval($voucher->id), intval($email['id_customer']), intval($email['id_cart']));
				}
		}
	}
	
	/* For all validated orders, a discount if re-ordering before x days */
	private function reOrder($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT o.id_order, c.id_cart, c.id_lang, cu.id_customer, cu.firstname, cu.lastname, cu.email
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN '._DB_PREFIX_.'customer cu ON (cu.id_customer = o.id_customer)
		LEFT JOIN '._DB_PREFIX_.'cart c ON (c.id_cart = o.id_cart)
		WHERE o.valid = 1 AND c.date_add >= DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND o.id_order NOT IN 
		(SELECT id_order FROM '._DB_PREFIX_.'log_email WHERE id_email_type = 2)');

		if ($count OR !sizeof($emails))
			return sizeof($emails);
			
		$conf = Configuration::getMultiple(array('PS_FOLLOW_UP_AMOUNT_2', 'PS_FOLLOW_UP_DAYS_2'));
		foreach ($emails AS $email)
		{
				$voucher = $this->createDiscount(2, floatval($conf['PS_FOLLOW_UP_AMOUNT_2']), intval($email['id_customer']), strftime('%Y-%m-%d', strtotime('+'.intval($conf['PS_FOLLOW_UP_DAYS_2']).' day')), $this->l('Thanks for your order'));				
				if ($voucher !== false)
				{
					$templateVars = array('{email}' => $email['email'], '{lastname}' => $email['lastname'], '{firstname}' => $email['firstname'], '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_2'], '{days}' => $conf['PS_FOLLOW_UP_DAYS_2'], '{voucher_num}' => $voucher->name);
					$result = Mail::Send(intval($email['id_lang']), 'followup_2', $this->l('Thanks for your order'), $templateVars, $email['email'], $email['firstname'].' '.$email['lastname'], NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
					$this->logEmail(2, intval($voucher->id), intval($email['id_customer']), intval($email['id_cart']));
				}
		}
	}
	
	/* For all customers with more than x euros in 90 days */
	private function bestCustomer($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT SUM(o.total_paid) total, c.id_cart, c.id_lang, cu.id_customer, cu.firstname, cu.lastname, cu.email
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN '._DB_PREFIX_.'customer cu ON (cu.id_customer = o.id_customer)
		LEFT JOIN '._DB_PREFIX_.'cart c ON (c.id_cart = o.id_cart)
		WHERE o.valid = 1 AND DATE_SUB(CURDATE(),INTERVAL 90 DAY) <= o.date_add AND cu.id_customer NOT IN
		(SELECT id_customer FROM '._DB_PREFIX_.'log_email WHERE id_email_type = 3)
		GROUP BY o.id_customer
		HAVING total >= '.floatval(Configuration::get('PS_FOLLOW_UP_THRESHOLD_3')));
		
		if ($count OR !sizeof($emails))
			return sizeof($emails);
			
		$conf = Configuration::getMultiple(array('PS_FOLLOW_UP_AMOUNT_3', 'PS_FOLLOW_UP_DAYS_3'));
		foreach ($emails AS $email)
		{
				$voucher = $this->createDiscount(3, floatval($conf['PS_FOLLOW_UP_AMOUNT_3']), intval($email['id_customer']), strftime('%Y-%m-%d', strtotime('+'.intval($conf['PS_FOLLOW_UP_DAYS_3']).' day')), $this->l('You are one of our best customers'));				
				if ($voucher !== false)
				{
					$templateVars = array('{email}' => $email['email'], '{lastname}' => $email['lastname'], '{firstname}' => $email['firstname'], '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_3'], '{days}' => $conf['PS_FOLLOW_UP_DAYS_3'], '{voucher_num}' => $voucher->name);
					$result = Mail::Send(intval($email['id_lang']), 'followup_3', $this->l('You are one of our best customers'), $templateVars, $email['email'], $email['firstname'].' '.$email['lastname'], NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
					$this->logEmail(3, intval($voucher->id), intval($email['id_customer']), intval($email['id_cart']));
				}
		}
	}
	
	/* For all customers with no orders since more than x days */
	private function badCustomer($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT c.id_lang, c.id_cart, cu.id_customer, cu.firstname, cu.lastname, cu.email, (SELECT COUNT(o.id_order) FROM '._DB_PREFIX_.'orders o WHERE o.id_customer = cu.id_customer) nb_orders
		FROM '._DB_PREFIX_.'customer cu
		LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_customer = cu.id_customer)
		LEFT JOIN '._DB_PREFIX_.'cart c ON (c.id_cart = o.id_cart)
		WHERE cu.id_customer NOT IN
		(SELECT o.id_customer FROM '._DB_PREFIX_.'orders o WHERE DATE_SUB(CURDATE(),INTERVAL '.intval(Configuration::get('PS_FOLLOW_UP_DAYS_THRESHOLD_4')).' DAY) <= o.date_add)
		AND cu.id_customer NOT IN
		(SELECT id_customer FROM '._DB_PREFIX_.'log_email WHERE id_email_type = 4 AND date_add >= DATE_SUB(date_add,INTERVAL '.intval(Configuration::get('PS_FOLLOW_UP_DAYS_THRESHOLD_4')).' DAY))
		GROUP BY cu.id_customer
		HAVING nb_orders >= 1');
	
		if ($count OR !sizeof($emails))
			return sizeof($emails);
			
		$conf = Configuration::getMultiple(array('PS_FOLLOW_UP_AMOUNT_4', 'PS_FOLLOW_UP_DAYS_4'));
		foreach ($emails AS $email)
		{
				$voucher = $this->createDiscount(4, floatval($conf['PS_FOLLOW_UP_AMOUNT_4']), intval($email['id_customer']), strftime('%Y-%m-%d', strtotime('+'.intval($conf['PS_FOLLOW_UP_DAYS_4']).' day')), $this->l('We miss you'));				
				if ($voucher !== false)
				{
					$templateVars = array('{email}' => $email['email'], '{lastname}' => $email['lastname'], '{firstname}' => $email['firstname'], '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_4'], '{days}' => $conf['PS_FOLLOW_UP_DAYS_4'], '{days_threshold}' => intval(Configuration::get('PS_FOLLOW_UP_DAYS_THRESHOLD_4')), '{voucher_num}' => $voucher->name);
					$result = Mail::Send(intval($email['id_lang']), 'followup_4', $this->l('We miss you'), $templateVars, $email['email'], $email['firstname'].' '.$email['lastname'], NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
					$this->logEmail(4, intval($voucher->id), intval($email['id_customer']), intval($email['id_cart']));
				}
		}
	}
	
	private function createDiscount($id_email_type, $amount, $id_customer, $dateValidity, $description)
	{
		$discount = new Discount();
		$discount->id_discount_type = 1;
		$discount->value = floatval($amount);
		$discount->id_customer = intval($id_customer);
		$discount->date_to = $dateValidity;
		$discount->date_from = date('Y-m-d H:i:s');
		$discount->quantity = 1;
		$discount->quantity_per_user = 1;
		$discount->cumulable = 0;
		$discount->cumulable_reduction = 1;
		$discount->minimal = 0;
		$discount->description[1] = $description;
		$discount->description[2] = $description;
		$name = 'FLW-'.intval($id_email_type).'-'.strtoupper(Tools::passwdGen(10));
		$discount->name = $name;
		$discount->active = 1;
		$result = $discount->add();
		
		if (!$result)
			return false;
		return $discount;
	}
	
	public function cronTask()
	{
		$conf = Configuration::getMultiple(array('PS_FOLLOW_UP_ENABLE_1', 'PS_FOLLOW_UP_ENABLE_2', 'PS_FOLLOW_UP_ENABLE_3', 'PS_FOLLOW_UP_ENABLE_4', 'PS_FOLLOW_UP_CLEAN_DB'));

		if ($conf['PS_FOLLOW_UP_ENABLE_1'])
			$this->cancelledCart();
		if ($conf['PS_FOLLOW_UP_ENABLE_2'])
			$this->reOrder();
		if ($conf['PS_FOLLOW_UP_ENABLE_3'])
			$this->bestCustomer();
		if ($conf['PS_FOLLOW_UP_ENABLE_4'])
			$this->badCustomer();
		
		/* Clean-up database by deleting all outdated discounts */
		if ($conf['PS_FOLLOW_UP_CLEAN_DB'] == 1)
		{
			$outdatedDiscounts = Db::getInstance()->ExecuteS('SELECT id_discount FROM '._DB_PREFIX_.'discount WHERE date_to < NOW()');
			foreach ($outdatedDiscounts AS $outdatedDiscount)
			{
				$discount = new Discount(intval($outdatedDiscount['id_discount']));
				if (Validate::isLoadedObject($discount))
					$discount->delete();
			}
		}
	}
}

?>