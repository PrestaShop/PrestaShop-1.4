<?php

/* SSL Management */
$useSSL = true;

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once(dirname(__FILE__).'/LoyaltyModule.php');
include_once(dirname(__FILE__).'/LoyaltyStateModule.php');

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=modules/loyalty/loyalty-program.php');

Tools::addCSS(_PS_CSS_DIR_.'jquery.cluetip.css', 'all');
Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.dimensions.js',_PS_JS_DIR_.'jquery/jquery.cluetip.js'));

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
	$voucher->id_currency = intval($cookie->id_currency);
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
	$categories = Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY');
	if ($categories != '' AND $categories != 0)
		$categories = explode(',', Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY'));
	else
		die(Tools::displayError());
	$languages = Language::getLanguages(true);
	$default_text = Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', intval(Configuration::get('PS_LANG_DEFAULT')));
	foreach ($languages as $language)
	{
		$text = Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', intval($language['id_lang']));
		$voucher->description[intval($language['id_lang'])] = $text ? strval($text) : strval($default_text);
	}
	if (is_array($categories) AND sizeof($categories))
		$voucher->add(true, false, $categories);
	else
		$voucher->add();
	/* register order(s) which contribute to create this voucher */
	LoyaltyModule::registerDiscount($voucher);

	Tools::redirect('modules/loyalty/loyalty-program.php');
}

include(dirname(__FILE__).'/../../header.php');

$orders = LoyaltyModule::getAllByIdCustomer(intval($cookie->id_customer), intval($cookie->id_lang));
$displayorders = LoyaltyModule::getAllByIdCustomer(intval($cookie->id_customer), intval($cookie->id_lang), false, true, (intval(Tools::getValue('n')) > 0 ? intval(Tools::getValue('n')) : 10), (intval(Tools::getValue('p')) > 0 ? intval(Tools::getValue('p')) : 1));
$smarty->assign(array(
	'orders' => $orders,
	'displayorders' => $displayorders,
	'pagination_link' => __PS_BASE_URI__.'modules/loyalty/loyalty-program.php',
	'totalPoints' => $customerPoints,
	'voucher' => LoyaltyModule::getVoucherValue($customerPoints, intval($cookie->id_currency)),
	'validation_id' => LoyaltyStateModule::getValidationId(),
	'transformation_allowed' => $customerPoints > 0,
	'page' => (intval(Tools::getValue('p')) > 0 ? intval(Tools::getValue('p')) : 1),
	'nbpagination' => (intval(Tools::getValue('n') > 0) ? intval(Tools::getValue('n')) : 10),
	'nArray' => array(10, 20, 50),
	'max_page' => floor(sizeof($orders) / (intval(Tools::getValue('n') > 0) ? intval(Tools::getValue('n')) : 10))
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

$allCategories = Category::getSimpleCategories(intval($cookie->id_lang));
$voucherCategories = Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY');
if ($voucherCategories != '' AND $voucherCategories != 0)
	$voucherCategories = explode(',', Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY'));
else
	die(Tools::displayError());

if (sizeof($voucherCategories) == sizeof($allCategories))
	$categoriesNames = null;
else
{
	$categoriesNames = '';
	foreach ($voucherCategories as $voucherCategory)
		foreach ($allCategories as $allCategory)
			if ($voucherCategory['id_category'] == $allCategory['id_category'])
			{
				$categoriesNames .= $allCategory['name'].', ';
				break;
			}
	$categoriesNames = rtrim($categoriesNames, ', ');
	$categoriesNames .= '.';
}
$smarty->assign(array(
	'nbDiscounts' => $nbDiscounts,
	'discounts' => $discounts,
	'categories' => $categoriesNames
));

echo Module::display(dirname(__FILE__).'/loyalty.php', 'loyalty.tpl');

include(dirname(__FILE__).'/../../footer.php');

?>
