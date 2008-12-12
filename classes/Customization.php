<?php

/**
  * Customization class, Customization.php
  * Customization management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.0
  *
  */

class Customization
{

	static public function getReturnedCustomizations($id_order)
	{
		if (!$result = Db::getInstance()->ExecuteS('
			SELECT od.`id_order_detail`, ocr.`customization_id`, ocr.`quantity`
			FROM `'._DB_PREFIX_.'order_detail` od
			INNER JOIN `'._DB_PREFIX_.'order_customization_return` ocr ON (ocr.`id_order_detail` = od.`id_order_detail`)
			WHERE od.`id_order` = '.intval($id_order)))
			return false;
		$customizations = array();
		foreach ($result AS $row)
			$customizations[intval($row['customization_id'])] = $row;
		return $customizations;
	}

	static public function getOrderedCustomizations($id_cart)
	{
		if (!$result = Db::getInstance()->ExecuteS('SELECT `id_customization`, `quantity` FROM `'._DB_PREFIX_.'customization` WHERE `id_cart` = '.intval($id_cart)))
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

}

?>