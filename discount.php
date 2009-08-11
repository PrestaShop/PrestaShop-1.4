<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=discount.php');

$discounts = Discount::getCustomerDiscounts(intval($cookie->id_lang), intval($cookie->id_customer), true, false);
$nbDiscounts = 0;
foreach ($discounts AS $discount)
	if ($discount['quantity_for_user'])
		$nbDiscounts++;

$smarty->assign(array('nbDiscounts' => intval($nbDiscounts), 'discount' => $discounts));

include(dirname(__FILE__).'/header.php');
$smarty->display(_PS_THEME_DIR_.'discount.tpl');
include(dirname(__FILE__).'/footer.php');

?>