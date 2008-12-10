<?php

/**
  * Admin panel initialization file, init.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.0
  *
  */

ob_start();
$timerStart = microtime(true);

$destination = substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])) + 1);
$url_redirect = '?redirect='.(empty($destination) ? 'index.php' : $destination);

/* Getting cookie or logout */
if (!class_exists('Cookie'))
	exit();

$cookie = new Cookie('psAdmin', substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__), -10));
if (isset($_GET['logout'])) {
	$url_redirect = '';
	$cookie->logout();
}

/* logged or not */
if (!$cookie->isLoggedBack())
	Tools::redirectLink('login.php'.$url_redirect);

/* Current tab and current URL */
$tab = Tools::getValue('tab');
$currentIndex = __PS_BASE_URI__.substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__)).($tab ? '?tab='.$tab : '');
if ($back = Tools::getValue('back'))
	$currentIndex .= '&back='.urlencode($back);

/* Include appropriate language file */
Tools::setCookieLanguage();
if (Tools::isSubmit('adminlang'))
{
	Tools::switchLanguage();
	if ($id_lang = Tools::getValue('id_lang'))
		$cookie->id_lang = $id_lang;
}

$iso = strtolower(Language::getIsoById($cookie->id_lang ? $cookie->id_lang : 1));
include(_PS_TRANSLATIONS_DIR_.$iso.'/errors.php');
include(_PS_TRANSLATIONS_DIR_.$iso.'/fields.php');
include(_PS_TRANSLATIONS_DIR_.$iso.'/admin.php');

/* Database connection (singleton) */
Db::getInstance();

?>