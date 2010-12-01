<?php


/**
  * DejalaCart class, dejalacart.php
  * Manage cart information related to dejala.fr carrier
 **/
if (!defined('_CAN_LOAD_FILES_'))
	exit;
class DejalaCart extends ObjectModel
{
	public 		$id;
	public 		$id_dejala_product;
	public 		$shipping_date;
	public 		$id_delivery;
	// 'TEST' or 'PROD'
	public		$mode;
	
	protected 	$table = 'dejala_cart';
	protected 	$identifier = 'id_cart';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_dejala_product'] = (int)($this->id_dejala_product);
		$fields['shipping_date'] = pSQL($this->shipping_date);
		$fields['id_delivery'] = (int)($this->id_delivery);
		$fields['mode'] = pSQL($this->mode);
		return $fields;
	}
	
}


