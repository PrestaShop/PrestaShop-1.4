<?php

if (!isset($smarty))
	exit;

/* Theme is missing or maintenance */
if (!is_dir(dirname(__FILE__).'/themes/'._THEME_NAME_))
	die(Tools::displayError('Current theme unavailable. Please check your theme directory name and permissions.'));
elseif (basename($_SERVER['PHP_SELF']) != 'disabled.php' AND !intval(Configuration::get('PS_SHOP_ENABLE')))
	$maintenance = true;

ob_start();
global $cart, $cookie, $_CONF, $link;

/* get page name to display it in body id */
$pathinfo = pathinfo(__FILE__);
$page_name = basename($_SERVER['PHP_SELF'], '.'.$pathinfo['extension']);
$page_name = (ereg('^[0-9]', $page_name)) ? 'page_'.$page_name : $page_name;

$cookie = new Cookie('ps');
Tools::setCookieLanguage();
Tools::switchLanguage();
/* attribute id_lang is often needed, so we create a constant for performance reasons */
define('_USER_ID_LANG_', intval($cookie->id_lang));

if (isset($_GET['logout']) OR ($cookie->logged AND Customer::isBanned(intval($cookie->id_customer))))
{
	$cookie->logout();
	Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
}
elseif (isset($_GET['mylogout']))
{
 	$cookie->mylogout();
	Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
}

$iso = strtolower(Language::getIsoById($cookie->id_lang ? intval($cookie->id_lang) : 1));
@include(_PS_TRANSLATIONS_DIR_.$iso.'/fields.php');
@include(_PS_TRANSLATIONS_DIR_.$iso.'/errors.php');
$_MODULES = array();

$currency = Tools::setCurrency();

if (is_numeric($cookie->id_cart))
{
	$cart = new Cart(intval($cookie->id_cart));
	$cart->id_lang = intval($cookie->id_lang);
	if ($cart->OrderExists())
		unset($cookie->id_cart, $cart);
	else
	{	
		if ($cookie->id_customer)
    		$cart->id_customer = intval($cookie->id_customer);
    	$cart->id_currency = intval($cookie->id_currency);
    	$cart->update();
    }
}

if (!isset($cart) OR !$cart->id)
{
	$cart = new Cart();
	$cart->id_lang = intval($cookie->id_lang);
    $cart->id_currency = intval($cookie->id_currency);
    if ($cookie->id_customer)
    	$cart->id_customer = intval($cookie->id_customer);
}
if (!$cart->nbProducts())
	$cart->id_carrier = NULL;

$ps_language = new Language(intval($cookie->id_lang));
setlocale(LC_TIME, strtolower($ps_language->iso_code).'_'.strtoupper($ps_language->iso_code).'@euro', 
strtolower($ps_language->iso_code).'_'.strtoupper($ps_language->iso_code), strtolower($ps_language->iso_code));

if (is_object($currency))
	$smarty->ps_currency = $currency;
if (is_object($ps_language))
	$smarty->ps_language = $ps_language;

$smarty->register_function('dateFormat', array('Tools', 'dateFormat'));
$smarty->register_function('productPrice', array('Product', 'productPrice'));
$smarty->register_function('convertPrice', array('Product', 'convertPrice'));
$smarty->register_function('convertPriceWithoutDisplay', array('Product', 'productPriceWithoutDisplay'));
$smarty->register_function('convertPriceWithCurrency', array('Product', 'convertPriceWithCurrency'));
$smarty->register_function('displayWtPrice', array('Product', 'displayWtPrice'));
$smarty->register_function('displayWtPriceWithCurrency', array('Product', 'displayWtPriceWithCurrency'));
$smarty->register_function('displayPrice', array('Tools', 'displayPriceSmarty'));

$smarty->assign(Tools::getMetaTags(intval($cookie->id_lang)));
$smarty->assign('request_uri', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

/* Breadcrumb */
$navigationPipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
$smarty->assign('navigationPipe', $navigationPipe);

$protocol = (isset($useSSL) AND $useSSL AND Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';

$smarty->assign(array(
	'base_dir' => __PS_BASE_URI__,
	'base_dir_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
	/* If the current page need SSL encryption and the shop allow it, then active it */
	'protocol' => $protocol,
	'img_ps_dir' => _PS_IMG_,
	'img_cat_dir' => _THEME_CAT_DIR_,
	'img_lang_dir' => _THEME_LANG_DIR_,
	'img_prod_dir' => _THEME_PROD_DIR_,
	'img_manu_dir' => _THEME_MANU_DIR_,
	'img_sup_dir' => _THEME_SUP_DIR_,
	'img_ship_dir' => _THEME_SHIP_DIR_,
	'img_col_dir' => _THEME_COL_DIR_,
	'img_dir' => _THEME_IMG_DIR_,
	'css_dir' => _THEME_CSS_DIR_,
	'js_dir' => _THEME_JS_DIR_,
	'tpl_dir' => _PS_THEME_DIR_,
	'modules_dir' => _MODULE_DIR_,
	'mail_dir' => _MAIL_DIR_,
	'pic_dir' => _THEME_PROD_PIC_DIR_,
	'lang_iso' => $ps_language->iso_code,
	'come_from' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').htmlentities($_SERVER['REQUEST_URI']),
	'shop_name' => Configuration::get('PS_SHOP_NAME'),
	'cart_qties' => intval($cart->nbProducts()),
	'cart' => $cart,
	'currencies' => Currency::getCurrencies(),
	'id_currency_cookie' => intval($currency->id),
	'currency' => $currency,
	'languages' => Language::getLanguages(),
	'logged' => $cookie->isLogged(),
	'page_name' => $page_name,
	'customerName' => ($cookie->logged ? $cookie->customer_firstname.' '.$cookie->customer_lastname : false)));
?>