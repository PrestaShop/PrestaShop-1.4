<?php

/**
  * Customization class, Customization.php
  * Customization management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class Customization
{

	static public function getReturnedCustomizations($id_order)
	{
		if (($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT ore.`id_order_return`, ord.`id_order_detail`, ord.`id_customization`, ord.`product_quantity`
			FROM `'._DB_PREFIX_.'order_return` ore
			INNER JOIN `'._DB_PREFIX_.'order_return_detail` ord ON (ord.`id_order_return` = ore.`id_order_return`)
			WHERE ore.`id_order` = '.intval($id_order).' AND ord.`id_customization` != 0')) === false)
			return false;
		$customizations = array();
		foreach ($result AS $row)
			$customizations[intval($row['id_customization'])] = $row;
		return $customizations;
	}

	static public function getOrderedCustomizations($id_cart)
	{
		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `id_customization`, `quantity` FROM `'._DB_PREFIX_.'customization` WHERE `id_cart` = '.intval($id_cart)))
			return false;
		$customizations = array();
		foreach ($result AS $row)
			$customizations[intval($row['id_customization'])] = $row;
		return $customizations;
	}

	static public function countCustomizationQuantityByProduct($customizations)
	{
		$total = array();
		foreach ($customizations AS $customization)
			$total[intval($customization['id_order_detail'])] = !isset($total[intval($customization['id_order_detail'])]) ? intval($customization['quantity']) : $total[intval($customization['id_order_detail'])] + intval($customization['quantity']);
		return $total;
	}

	static public function getLabel($id_customization, $id_lang)
	{
		if (!$id_customization || !$id_lang)
			return false;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `name` 
		FROM `'._DB_PREFIX_.'customization_field_lang` 
		WHERE `id_customization_field` = '.intval($id_customization).' 
		AND `id_lang` = '.intval($id_lang)
		);

		return $result['name'];
	}
	
	public static function retrieveQuantitiesFromIds(array $ids_customizations)
	{
		$quantities = array();
	
		$in_values  = '';
		foreach($ids_customizations as $key => $id_customization)
		{
			if ($key > 0) $in_values += ',';
			$in_values += intval($id_customization);
		}
		
		if (!empty($in_values))
		{
			$results =  Db::getInstance()->ExecuteS(
							'SELECT `id_customization`, `id_product`, `quantity`, `quantity_refunded`, `quantity_returned`
							 FROM `'._DB_PREFIX_.'customization`
							 WHERE `id_customization` IN ('.$in_values.')');
							 
			foreach($results as $row)
			{
				$quantities[$row['id_customization']] = $row;
			}
		}
		
		return $quantities;
	}
	
	public static function countQuantityByCart($id_cart)
	{
		$quantity = array();
		
		$results =  Db::getInstance()->executeS('
					SELECT `id_product`, SUM(`quantity`) AS quantity 
					FROM `'._DB_PREFIX_.'customization` 
					WHERE `id_cart` = '.intval($id_cart).'
					GROUP BY `id_cart`, `id_product`'
					);
					
		foreach($results as $row)
		{
			$quantity[$row['id_product']] = $row['quantity'];
		}
		
		return $quantity;
	}

}

?>
