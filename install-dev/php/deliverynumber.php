<?php

global $_CONF;
Configuration::loadConfiguration();

function delivery_number_set()
{
	$number = 1;

	// Update each order with a number
	$result = Db::getInstance()->ExecuteS('
	SELECT id_order
	FROM '._DB_PREFIX_.'orders
	ORDER BY id_order');
	foreach ($result as $row)
	{
		$order = new Order(intval($row['id_order']));
		$history = $order->getHistory(false);
		foreach ($history as $row2)
		{
			$oS = new OrderState(intval($row2['id_order_state']));
			if ($oS->delivery)
			{
				Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'orders SET delivery_number = '.intval($number++).', `delivery_date` = `date_add` WHERE id_order = '.intval($order->id));
				break ;
			}
		}
	}
	// Add configuration var
	Configuration::updateValue('PS_DELIVERY_NUMBER', intval($number));
}

?>