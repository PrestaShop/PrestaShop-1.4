<?php

class ReferralProgramModule extends ObjectModel
{
	public $id_sponsor;
	public $email;
	public $lastname;
	public $firstname;
	public $id_customer;
	public $id_discount;
	public $id_discount_sponsor;
	public $date_add;
	public $date_upd;

	protected $fieldsRequired = array('id_sponsor', 'email', 'lastname', 'firstname');
	protected $fieldsSize = array(
		'id_sponsor' => 8,
		'email' => 255,
		'lastname' => 128,
		'firstname' => 128,
		'id_customer' => 8,
		'id_discount' => 8,
		'id_discount_sponsor' => 8
	);
	protected $fieldsValidate = array(
		'id_sponsor' => 'isUnsignedId',
		'email' => 'isEmail',
		'lastname' => 'isName',
		'firstname' => 'isName',
		'id_customer' => 'isUnsignedId',
		'id_discount' => 'isUnsignedId',
		'id_discount_sponsor' => 'isUnsignedId'
	);

	protected $table = 'referralprogram';
	protected $identifier = 'id_referralprogram';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_sponsor']            = intval($this->id_sponsor);
		$fields['email']                 = pSQL($this->email);
		$fields['lastname']              = pSQL($this->lastname);
		$fields['firstname']             = pSQL($this->firstname);
		$fields['id_customer']           = intval($this->id_customer);
		$fields['id_discount']           = intval($this->id_discount);
		$fields['id_discount_sponsor']   = intval($this->id_discount_sponsor);
		$fields['date_add']              = pSQL($this->date_add);
		$fields['date_upd']              = pSQL($this->date_upd);
		return $fields;
	}

	public function save($nullValues=true, $autodate=true)
	{
		return parent::save($nullValues, $autodate);
	}

	static public function getDiscountPrefix()
	{
		return 'SP';
	}

	public function registerDiscountForSponsor()
	{
		if (intval($this->id_discount_sponsor) > 0)
			return false;
		return $this->registerDiscount($this->id_sponsor, 'sponsor');
	}

	public function registerDiscountForSponsored()
	{
		if (!intval($this->id_customer) OR intval($this->id_discount) > 0)
			return false;
		return $this->registerDiscount($this->id_customer, 'sponsored');
	}

	public function registerDiscount($id_customer, $register=false)
	{
		$configurations = Configuration::getMultiple(array(
			'REFERRAL_DISCOUNT_TYPE',
			'REFERRAL_DISCOUNT_VALUE'
		));
		$discount = new Discount();
		$discount->id_discount_type = intval($configurations['REFERRAL_DISCOUNT_TYPE']);
		$discount->value = floatval($configurations['REFERRAL_DISCOUNT_VALUE']);
		$discount->quantity = 1;
		$discount->quantity_per_user = 1;
		$discount->date_from = date('Y-m-d H:i:s', time());
		$discount->date_to = date('Y-m-d H:i:s', time() + 31536000); // + 1 year
		$discount->name = $this->getDiscountPrefix().Tools::passwdGen(6);
		$discount->description = Configuration::getInt('REFERRAL_DISCOUNT_DESCRIPTION');
		$discount->id_customer = intval($id_customer);
		if ($discount->add())
		{
			if ($register!=false)
			{
				if ($register=='sponsor')
					$this->id_discount_sponsor = $discount->id;
				elseif ($register=='sponsored')
					$this->id_discount = $discount->id;
				return $this->save();
			}
			return true;
		}
		return false;
	}

	/**
	  * Return sponsored friends
	  *
	  * @return array Sponsor
	  */
	static public function getSponsorFriend($id_customer, $restriction = false)
	{
		if (!intval($id_customer))
			return array();
		$query = '
			SELECT s.*
			FROM `'._DB_PREFIX_.'referralprogram` s
			WHERE s.`id_sponsor` = '.intval($id_customer);
		if ($restriction)
		{
			if ($restriction == 'pending')
				$query.= ' AND s.`id_customer` = 0';
			elseif ($restriction == 'subscribed')
				$query.= ' AND s.`id_customer` != 0';
		}
		return Db::getInstance()->ExecuteS($query);
	}

	/**
	  * Return if a customer is sponsorised
	  *
	  * @return boolean
	  */
	static public function isSponsorised($id_customer, $getId=false)
	{
		$query = '
			SELECT s.`id_referralprogram`
			FROM `'._DB_PREFIX_.'referralprogram` s
			WHERE s.`id_customer` = '.intval($id_customer);
		$result = Db::getInstance()->getRow($query);
		if (isset($result['id_referralprogram']) AND $getId===true)
			return intval($result['id_referralprogram']);
		return isset($result['id_referralprogram']);
	}

	/**
	  * Return if an email is already register
	  *
	  * @return boolean OR int idReferralProgram
	  */
	static public function isEmailExists($email, $getId = false, $checkCustomer = true)
	{
		if (!Validate::isEmail($email))
			die (Tools::displayError('Email invalid.'));
		if ($checkCustomer === true AND Customer::customerExists($email))
			return false;
		$result = Db::getInstance()->getRow('
			SELECT s.`id_referralprogram`
			FROM `'._DB_PREFIX_.'referralprogram` s
			WHERE s.`email` = \''.pSQL($email).'\'');
		if ($getId)
			return intval($result['id_referralprogram']);
		return isset($result['id_referralprogram']);
	}

}

?>