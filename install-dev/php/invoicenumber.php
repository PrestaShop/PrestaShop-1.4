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
		$order = new Order((int)($row['id_order']));
		$history = $order->getHistory(false);
		foreach ($history as $row2)
		{
			$oS = new OrderState((int)($row2['id_order_state']), Configuration::get('PS_LANG_DEFAULT'));
			if ($oS->invoice)
			{
				Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'orders SET invoice_number = '.(int)($number++).', `invoice_date` = `date_add` WHERE id_order = '.(int)($order->id));
				break ;
			}
		}
	}
	// Add configuration var
	Configuration::updateValue('PS_INVOICE_NUMBER', (int)($number));
}

?>