<?php

/* Convert product prices from the PS < 1.3 wrong rounding system to the new 1.3 one */
function convert_product_price()
{
	$taxes = Tax::getTaxes();
	$taxRates = array();
	foreach ($taxes as $data)
		$taxRates[$data['id_tax']] = floatval($data['rate']) / 100;
	$resource = DB::getInstance()->ExecuteS('SELECT `id_product`, `price`, `id_tax` FROM `'._DB_PREFIX_.'product`', false);
	while ($row = DB::getInstance()->nextRow($resource))
		if ($row['id_tax'])
		{
			$price = $row['price'] * (1 + $taxRates[$row['id_tax']]);
			$decimalPart = $price - (int)$price;
			if ($decimalPart < 0.000001)
			{
				$newPrice = floatval(number_format($price, 6, '.', ''));
				$newPrice = Tools::floorf($newPrice / (1 + $taxRates[$row['id_tax']]), 6);
				DB::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.$newPrice.' WHERE `id_product` = '.intval($row['id_product']));
			}
		}
}
