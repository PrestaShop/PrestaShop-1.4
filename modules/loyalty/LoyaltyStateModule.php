<?php

class LoyaltyStateModule extends ObjectModel
{
	public $name;
	public $id_order_state;

	protected $fieldsValidate = array('id_order_state' => 'isInt');
	protected $fieldsRequiredLang = array('name');
	protected $fieldsSizeLang = array('name' => 128);
	protected $fieldsValidateLang = array('name' => 'isGenericName');

	protected $table = 'loyalty_state';
	protected $identifier = 'id_loyalty_state';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_order_state'] = intval($this->id_order_state);
		return $fields;
	}

	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}

	static public function getDefaultId() { return 1; }
	static public function getValidationId() { return 2; }
	static public function getCancelId() { return 3; }
	static public function getConvertId() { return 4; }
	static public function getNoneAwardId() { return 5; }

	static public function insertDefaultData()
	{
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		
		$default = new LoyaltyStateModule(LoyaltyStateModule::getDefaultId());
		$default->name[$defaultLanguage] = 'Validation';
		$default->save();

		$validation = new LoyaltyStateModule(LoyaltyStateModule::getValidationId());
		$validation->id_order_state = _PS_OS_DELIVERED_;
		$validation->name[$defaultLanguage] = 'Available';
		$validation->save();

		$cancel = new LoyaltyStateModule(LoyaltyStateModule::getCancelId());
		$cancel->id_order_state = _PS_OS_CANCELED_;
		$cancel->name[$defaultLanguage] = 'Canceled';
		$cancel->save();

		$convert = new LoyaltyStateModule(LoyaltyStateModule::getConvertId());
		$convert->name[$defaultLanguage] = 'Converted';
		$convert->save();

		$noneAward = new LoyaltyStateModule(LoyaltyStateModule::getNoneAwardId());
		$noneAward->name[$defaultLanguage] = 'Unavailable on discounts';
		$noneAward->save();

		return true;
	}

}

?>