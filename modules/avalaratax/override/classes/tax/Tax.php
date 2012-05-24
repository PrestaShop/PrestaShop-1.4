<?php

class Tax extends TaxCore
{

	/**
	 * Return the product tax
	 *
	 * @param integer $id_product
	 * @param integer $id_address
	 * @return Tax
	 */
	public static function getProductTaxRate($id_product, $id_address = NULL, $getCarrierRate = false)
	{
		// Check if the module is active (Check the DB directly just in case the module was deleted from the site)
		$moduleActive = Db::getInstance()->getValue('SELECT `active` 
													FROM '._DB_PREFIX_.'module 
													WHERE `name` = \'avalaratax\'');
		if (!$moduleActive)
			return parent::getProductTaxRate($id_product, $id_address, $getCarrierRate);
		
		global $cart;
		
		// Check cache first
		if ($id_address)
			$region = Db::getInstance()->getValue('SELECT s.`iso_code`
											FROM '._DB_PREFIX_.'address a
											LEFT JOIN '._DB_PREFIX_.'state s ON (s.`id_state` = a.`id_state`)
											WHERE a.`id_address` = '.(int)$id_address);
		if (empty($region))
			$region = Configuration::get('AVALARATAX_STATE');
		
		$result = Db::getInstance()->ExecuteS('SELECT ac.`tax_rate`, ac.`update_date` 
											FROM '._DB_PREFIX_.'avalara_'.($getCarrierRate ? 'carrier' : 'product').'_cache ac
											WHERE ac.`id_'.($getCarrierRate ? 'carrier' : 'product').'` = '.(int)$id_product.'
											AND ac.`region` = \''.pSQL($region).'\'');
		
		if (count($result) && (float)$result[0]['tax_rate'] > 0)
		{
			$result = $result[0];
			
			// IMPORTANT : Do not check if the cached tax is expired so the system can return the last fetched tax
			// Compare date/time
			// date_default_timezone_set(@date_default_timezone_get());
			// $date1 = new DateTime($result['update_date']);
			// $date2 = new DateTime(date('Y-m-d H:i:s'));
			// $dateTimeComparison = $date1->diff($date2);
			// if ($dateTimeComparison->y == 0 
			// && $dateTimeComparison->m == 0 
			// && $dateTimeComparison->d == 0 
			// && (int)$dateTimeComparison->h == 0 
			// && (int)$dateTimeComparison->i < (int)Configuration::get('AVALARA_CACHE_MAX_LIMIT') 
			// && (float)$result['tax_rate'])
				return $result['tax_rate'];
		}
		return;
		
		if (!class_exists('AvalaraTax'))
		{
			if (version_compare(_PS_VERSION_, '1.5', '<')) // The regular override for 1.4
				include(dirname(__FILE__).'/../../modules/avalaratax/avalaratax.php');
			else
				include(dirname(__FILE__).'/../../../modules/avalaratax/avalaratax.php');
		}
		
		$avalaraModule = new AvalaraTax();
		if ($avalaraModule->active && $getCarrierRate)
		{
			ini_set('max_execution_time', 0);
			$avalaraProducts[] = array('id_product' => 0, 'quantity' => 0, 'tax_code' => '', 'name' => '', 'description_short' => '', 'total' => 0);
			$getTaxResult = $avalaraModule->getTax($avalaraProducts, array('type' => 'SalesOrder', 'DocCode' => 1, 'cart' => $cart));
			return $total_tax = isset($getTaxResult) && isset($getTaxResult['TotalTax']) ? (float)$getTaxResult['TotalTax'] : 0.0;
		}
		
		// If we got to this point asking for the carrier rate means that the Avalara Module is not active, 
		// then use PrestaShop's default Tax System
		if ($getCarrierRate) 
			return (float)parent::getCarrierTaxRate($id_product, $id_address);
		
		return (float)parent::getProductTaxRate($id_product, $id_address);
	}

	public static function getCarrierTaxRate($id_carrier, $id_address = NULL)
	{
		return (float)self::getProductTaxRate($id_carrier, $id_address, true);
	}
}

