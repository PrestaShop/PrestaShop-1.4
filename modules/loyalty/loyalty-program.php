<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once(dirname(__FILE__).'/LoyaltyModule.php');
include_once(dirname(__FILE__).'/LoyaltyStateModule.php');

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=modules/loyalty/loyalty-program.php');

// CSS ans JS file calls
$css_files = array(
	_PS_CSS_DIR_.'jquery.cluetip.css' => 'all'
);
$js_files = array(
	_PS_JS_DIR_.'jquery/jquery.dimensions.js',
	_PS_JS_DIR_.'jquery/jquery.cluetip.js'
);

$customerPoints = intval(LoyaltyModule::getPointsByCustomer(intval($cookie->id_customer)));

/* transform point into voucher if needed */
if (Tools::getValue('transform-points') == 'true' AND $customerPoints > 0)
{
	/* generate a voucher code */
	$voucherCode = null;
	do $voucherCode = 'FID'.rand(1000, 100000);
	while (Discount::discountExists($voucherCode));

	/* voucher creation and add to customer */
	$voucher = new Discount();	
	$voucher->name = $voucherCode;
	$voucher->id_discount_type = 2; // Discount on order (amount)
	$voucher->id_customer = intval($cookie->id_customer);
	$voucher->value = LoyaltyModule::getVoucherValue($customerPoints);
	$voucher->quantity = 1;
	$voucher->quantity_per_user = 1;
	$voucher->cumulable = 1;
	$voucher->cumulable_reduction = 1;
	$dateFrom = time();
	if (Configuration::get('PS_ORDER_RETURN'))
		$dateFrom = $dateFrom + (60 * 60 * 24 * intval(Configuration::get('PS_ORDER_RETURN_NB_DAYS')));
	$voucher->date_from = date('Y-m-d H:i:s', $dateFrom);
	$voucher->date_to = date('Y-m-d H:i:s', $dateFrom + 31536000); // + 1 year
	$voucher->minimal = 0;
	$voucher->active = 1;
	$languages = Language::getLanguages(true);
	$default_text = Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', intval(Configuration::get('PS_LANG_DEFAULT')));
	foreach ($languages as $language)
	{
		$text = Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', intval($language['id_lang']));
		$voucher->description[intval($language['id_lang'])] = $text ? strval($text) : strval($default_text);
	}
	$voucher->save();

	/* register order(s) which contribute to create this voucher */
	LoyaltyModule::registerDiscount($voucher);

	Tools::redirect('modules/loyalty/loyalty-program.php');
}

include(dirname(__FILE__).'/../../header.php');

$orders = LoyaltyModule::getAllByIdCustomer(intval($cookie->id_customer), intval($cookie->id_lang));
$smarty->assign(array(
	'orders' => $orders,
	'totalPoints' => $customerPoints,
	'voucher' => LoyaltyModule::getVoucherValue($customerPoints),
	'validation_id' => LoyaltyStateModule::getValidationId(),
	'transformation_allowed' => $customerPoints > 0
));

/* Discounts */
$nbDiscounts = 0;
$discounts = array();
if ($ids_discount = LoyaltyModule::getDiscountByIdCustomer(intval($cookie->id_customer)))
{
	$nbDiscounts = count($ids_discount);
	foreach ($ids_discount as $key => $discount)
	{
		$discounts[$key] = new Discount($discount['id_discount'], intval($cookie->id_lang));
		$discounts[$key]->date_add = $discount['date_add'];
		$discounts[$key]->orders = LoyaltyModule::getOrdersByIdDiscount($discount['id_discount']);
	}
}
$smarty->assign(array(
	'nbDiscounts' => $nbDiscounts,
	'discounts' => $discounts
));

echo Module::display(dirname(__FILE__).'/loyalty.php', 'loyalty.tpl');

include(dirname(__FILE__).'/../../footer.php');

?>