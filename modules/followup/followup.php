<?php

class Followup extends Module
{
	function __construct()
	{
		$this->name = 'followup';
		$this->tab = 'Tools';
		$this->version = '1.0';

		parent::__construct();

		$this->displayName = $this->l('Customers follow-up');
		$this->description = $this->l('Follow-up your customers with daily customized e-mails');
	}
	
	public function getContent()
	{
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		$n1 = $this->emptyCart(true);
		$n2 = $this->reOrder(true);
		$n3 = $this->bestCustomer(true);
		$n4 = $this->badCustomer(true);
		
		echo '
		<h2>'.$this->l('Customers follow-up').'</h2>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">			
			<fieldset class="width2">
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<p>'.$this->l('Four kinds of e-mail alerts in order to stay in touch with your customers!').'<br /><br />
				'.$this->l('Define settings and put this URL in crontab or call it manually daily:').'<br />
				<b>http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/followup/cron.php</b></p>
				<hr size="1" />
				<p><b>1. '.$this->l('Canceled carts').'</b><br /><br />'.$this->l('For each cart with no order during last 24h, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="enable_1" value="1" style="vertical-align: middle;" /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n1).' '.($n1 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<p><b>2. '.$this->l('Re-order').'</b><br /><br />'.$this->l('For each validated order, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="enable_2" value="1" style="vertical-align: middle;" /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n2).' '.($n2 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<p><b>3. '.$this->l('Best customers').'</b><br /><br />'.$this->l('For each customer raising a threshold, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="enable_3" value="1" style="vertical-align: middle;" /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Threshold').'</label>
				<div class="margin-form">'.($currency->format == 1 ? ' '.$currency->sign.' ' : '').'<input type="text" name="amount_1" value="" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> '.($currency->format == 2 ? ' '.$currency->sign : '').'</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n3).' '.($n3 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<p><b>4. '.$this->l('Bad customers').'</b><br /><br />'.$this->l('For each customer with no orders since a given duration, generate a discount and send it to the customer').'</p>
				<label>'.$this->l('Enable').'</label>
				<div class="margin-form" style="padding-top: 5px;"><input type="checkbox" name="enable_4" value="1" style="vertical-align: middle;" /></div>
				<label>'.$this->l('Discount amount').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> %</div>
				<label>'.$this->l('Threshold').'</label>
				<div class="margin-form">'.($currency->format == 1 ? ' '.$currency->sign.' ' : '').'<input type="text" name="amount_1" value="" size="6" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> '.($currency->format == 2 ? ' '.$currency->sign : '').'</div>
				<label>'.$this->l('Discount validity').'</label>
				<div class="margin-form"><input type="text" name="amount_1" value="" size="6" /> '.$this->l('day(s)').'</div>
				<p>'.$this->l('Next process will send:').' <b>'.intval($n4).' '.($n4 > 1 ? $this->l('emails') : $this->l('email')).'</b></p>
				<hr size="1" />
				<input type="checkbox" style="vertical-align: middle;" name="" /> '.$this->l('Delete outdated discounts during each launch to clean database').'
				<hr size="1" />
				<center><input type="submit" name="submitFollowUp" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}
	
	/* Log each sent e-mail */
	private function logEmail($id_email_type, $id_customer = NULL, $id_cart = NULL)
	{
		$values = array('id_email_type' => intval($id_email_type), 'date_add' => date('Y-m-d H:i:s'));
		if (!empty($id_cart))
			$values['id_cart'] = intval($id_cart);
		if (!empty($id_customer))
			$values['id_customer'] = intval($id_customer);
		Db::getInstance()->autoExecute('log_email', $values, 'INSERT');
	}

	/* Each cart which wasn't transformed into an order */
	private function emptyCart($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT c.id_cart, cu.firstname, cu.lastname, cu.email
		FROM '._DB_PREFIX_.'cart c
		LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_cart = c.id_cart)
		LEFT JOIN '._DB_PREFIX_.'customer cu ON (cu.id_customer = c.id_customer)
		WHERE DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= c.date_add AND o.id_order IS NULL AND c.id_cart NOT IN 
		(SELECT id_cart FROM '._DB_PREFIX_.'log_email WHERE id_email_type = 1 AND DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= date_add)');
		
		if ($count)
			return sizeof($emails);
		
		// 5% de reduc valable combien de temps ?
	}
	
	/* For all validated orders, a discount if re-ordering before 15 days */
	private function reOrder($count = false)
	{
		$emails = array();

		if ($count)
			return sizeof($emails);
	}
	
	/* For all customers with more than 500 euros in 90 days */
	private function bestCustomer($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT SUM(o.total_paid) total
		FROM '._DB_PREFIX_.'orders o
		WHERE DATE_SUB(CURDATE(),INTERVAL 90 DAY) <= o.date_add
		HAVING total >= 500.00');
		
		if ($count)
			return sizeof($emails);
	}
	
	/* For all customers with no orders since more than 90 days */
	private function badCustomer($count = false)
	{
		$emails = Db::getInstance()->ExecuteS('
		SELECT cu.firstname, cu.lastname, cu.email
		FROM '._DB_PREFIX_.'customer
		WHERE cu.id_customer NOT IN
		(SELECT o.id_customer FROM '._DB_PREFIX_.'orders o WHERE DATE_SUB(CURDATE(),INTERVAL 90 DAY) <= o.date_add)');
	
		if ($count)
			return sizeof($emails);
	}
	
	public function cronTask()
	{
		$this->emptyCart();
		$this->reOrder();
		$this->bestCustomer();
		$this->badCustomer();
	}
}

?>