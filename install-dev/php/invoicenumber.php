<?php

function invoice_number_set()
{
	Configuration::loadConfiguration();
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
			if ($oS->invoice)
			{
				Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'orders SET invoice_number = '.intval($number++).', `invoice_date` = `date_add` WHERE id_order = '.intval($order->id));
				break ;
			}
		}
	}
	// Add configuration var
	Configuration::updateValue('PS_INVOICE_NUMBER', intval($number));
}

?>