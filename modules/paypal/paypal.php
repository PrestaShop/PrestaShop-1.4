<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;
	
define('WPS', 1);
define('HSS', 2);
define('ECS', 4);
define('TRACKING_CODE', 'FR_PRESTASHOP_H3S');
define('_PAYPAL_LOGO_XML_', 'logos.xml');
define('_PAYPAL_MODULE_DIRNAME_', 'paypal');
define('_PAYPAL_TRANSLATIONS_XML_', 'translations.xml');

include(_PS_MODULE_DIR_.'/paypal/paypal_abstract.php');
include(_PS_MODULE_DIR_.'/paypal/paypal_logos.php');
include(_PS_MODULE_DIR_.'/paypal/paypal_'.(_PS_VERSION_ < '1.5' ? '1.4' : '1.5').'.php');
