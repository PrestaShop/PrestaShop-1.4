<?php

class StockMvtReasonCore extends ObjectModel
{
	public		$id;
	public		$name;
	
	public		$date_add;
	public		$date_upd;
	
	protected	$table = 'stock_mvt_reason';
	protected 	$identifier = 'id_stock_mvt_reason';
	

 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 255);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');
	
	
	public function getFields()
	{
		parent::validateFields();
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}
	
	static public function getStockMvtReasons($id_lang)
	{
		return Db::getInstance()->ExecuteS('SELECT smrl.name, smr.id_stock_mvt_reason
														FROM '._DB_PREFIX_.'stock_mvt_reason smr
														LEFT JOIN '._DB_PREFIX_.'stock_mvt_reason_lang smrl ON (smr.id_stock_mvt_reason = smrl.id_stock_mvt_reason AND smrl.id_lang='.(int)$id_lang.')');
	}
}
