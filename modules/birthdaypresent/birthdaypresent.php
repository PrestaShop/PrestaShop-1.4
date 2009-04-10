<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
class BirthdayPresent extends Module
{
    private $_html = '';

    function __construct()
    {
        $this->name = 'birthdaypresent';
        $this->tab = 'Tools';
        $this->version = 1.0;
		
		parent::__construct();
		
        $this->displayName = $this->l('Birthday Present');
        $this->description = $this->l('Offer your clients birthday presents automatically');
	}
		
	public function getContent()
	{
		global $cookie, $currentIndex;
		
		if (Tools::isSubmit('submitBirthday'))
		{
			Configuration::updateValue('BIRTHDAY_ACTIVE', intval(Tools::getValue('bp_active')));
			Configuration::updateValue('BIRTHDAY_DISCOUNT_TYPE', intval(Tools::getValue('id_discount_type')));
			Configuration::updateValue('BIRTHDAY_DISCOUNT_VALUE', floatval(Tools::getValue('discount_value')));
			Configuration::updateValue('BIRTHDAY_MINIMAL_ORDER', floatval(Tools::getValue('minimal_order')));
			Tools::redirectAdmin($currentIndex.'&configure=birthdaypresent&token='.Tools::getValue('token').'&conf=4');
		}
		
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<p>'.$this->l('Create a voucher for your clients celebrating their birthday').'</p>
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<label>'.$this->l('Active').'</label>
				<div class="margin-form">
					<img src="../img/admin/enabled.gif" /> <input type="radio" name="bp_active" value="1"'.(Configuration::get('BIRTHDAY_ACTIVE') ? ' checked="checked"' : '').' />
					<img src="../img/admin/disabled.gif" /> <input type="radio" name="bp_active" value="0"'.(!Configuration::get('BIRTHDAY_ACTIVE') ? ' checked="checked"' : '').' />
					<p style="clear: both;">'.$this->l('Additionnaly, you have to set a CRON rule which calls the file').' '.dirname(__FILE__).'/cron.php '.$this->l('everyday').'</p>
				</div>
				<label>'.$this->l('Type').'</label>
				<div class="margin-form">
					<select name="id_discount_type">';
		$discountTypes = Discount::getDiscountTypes(intval($cookie->id_lang));
		foreach ($discountTypes AS $discountType)
			$this->_html .= '<option value="'.intval($discountType['id_discount_type']).'"'.((Configuration::get('BIRTHDAY_DISCOUNT_TYPE') == $discountType['id_discount_type']) ? ' selected="selected"' : '').'>'.$discountType['name'].'</option>';
		$this->_html .= '
					</select>
				</div>
				<label>'.$this->l('Value').'</label>
				<div class="margin-form">
					<input type="text" size="15" name="discount_value" value="'.Configuration::get('BIRTHDAY_DISCOUNT_VALUE').'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\'); " />
					<p style="clear: both;">'.$this->l('Either the monetary amount or the %, depending on Type selected above').'</p>
				</div>
				<label>'.$this->l('Minimal order').'</label>
				<div class="margin-form">
					<input type="text" size="15" name="minimal_order" value="'.Configuration::get('BIRTHDAY_MINIMAL_ORDER').'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\'); " />
					<p style="clear: both;">'.$this->l('The minimal order amount needed to use the voucher').'</p>
				</div>
				<div class="clear center">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitBirthday" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</form>
		</fieldset><br />
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/comment.gif" /> '.$this->l('Guide').'</legend>
			<h2>'.$this->l('Develop clients\' loyalty').'</h2>
			<p>'.$this->l('Offering a present to a client is a means of securing its loyalty.').'</p>
			<h3>'.$this->l('What should you do?').'</h3>
			<p>
				'.$this->l('Keeping a client is more profitable than capturing a new one. Thus, it is necessary to develop its loyalty, in other words to make him come back in your webshop.').' <br />
				'.$this->l('Word of mouth is also a means to get new satisfied clients; a dissatisfied one won\'t attract new clients.').'<br />
				'.$this->l('In order to achieve this goal you can organize: ').'
				<ul>
					<li>'.$this->l('Punctual operations: commercial rewards (personalized special offers, product or service offered), non commercial rewards (priority handling of an order or a product), pecuniary rewards (bonds, discount coupons, payback...).').'</li>
					<li>'.$this->l('Sustainable operations: loyalty or points cards, which not only justify communication between merchant and client, but also offer advantages to clients (private offers, discounts).').'</li>
				</ul>
				'.$this->l('These operations encourage clients to buy and also to come back in your webshop regularly.').' <br />
			</p>
		</fieldset>';
		return $this->_html;
	}
	
	public function createTodaysVouchers()
	{
		$users = Db::getInstance()->ExecuteS('
		SELECT DISTINCT c.id_customer, firstname, lastname, email
		FROM '._DB_PREFIX_.'customer c
		LEFT JOIN '._DB_PREFIX_.'orders o ON c.id_customer = o.id_customer
		WHERE o.valid = 1
		AND c.birthday LIKE \'%'.date('-m-d').'\'');

		foreach ($users as $user)
		{
			$voucher = new Discount();
			$voucher->id_customer = $user['id_customer'];
			$voucher->id_discount_type = Configuration::get('BIRTHDAY_DISCOUNT_TYPE');
			$voucher->name = 'birthday';
			$voucher->description[Configuration::get('PS_LANG_DEFAULT')] = $this->l('Your birthday present !');
			$voucher->value = Configuration::get('BIRTHDAY_DISCOUNT_VALUE');
			$voucher->quantity = 1;
			$voucher->quantity_per_user = 1;
			$voucher->cumulable = 1;
			$voucher->cumulable_reduction = 1;
			$voucher->date_from = date('Y-m-d');
			$voucher->date_to = (date('Y') + 1).date('-m-d');
			$voucher->minimal = Configuration::get('BIRTHDAY_MINIMAL_ORDER');
			$voucher->active = true;
			if ($voucher->add())
				Mail::Send(intval(Configuration::get('PS_LANG_DEFAULT')), 'birthday', $this->l('Happy birthday!'), array('{firstname}' => $user['firstname'], '{lastname}' => $user['lastname']), $user['email'], NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
			else
				echo Db::getInstance()->getMsgError();
		}
	}
}

?>