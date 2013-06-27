<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!isset($_POST['token']) OR !isset($_POST['type']))
	die;

include(dirname(__FILE__).'/config/config.inc.php');

if ($_POST['type'] == 'navinfo')
{
	if (sha1($_POST['id_guest']._COOKIE_KEY_) != $_POST['token'])
		die;

	$guest = new Guest((int)substr($_POST['id_guest'],0,10));
	$guest->javascript = true;
	$guest->screen_resolution_x = (int)substr($_POST['screen_resolution_x'],0,5);
	$guest->screen_resolution_y = (int)substr($_POST['screen_resolution_y'],0,5);
	$guest->screen_color = (int)substr($_POST['screen_color'],0,3);
	$guest->sun_java = (int)substr($_POST['sun_java'],0,1);
	$guest->adobe_flash = (int)substr($_POST['adobe_flash'],0,1);
	$guest->adobe_director = (int)substr($_POST['adobe_director'],0,1);
	$guest->apple_quicktime = (int)substr($_POST['apple_quicktime'],0,1);
	$guest->real_player = (int)substr($_POST['real_player'],0,1);
	$guest->windows_media = (int)substr($_POST['windows_media'],0,1);
	$guest->update();
}
elseif ($_POST['type'] == 'pagetime')
{
	if (sha1($_POST['id_connections'].$_POST['id_page'].$_POST['time_start']._COOKIE_KEY_) != $_POST['token'])
		die;
	if (!Validate::isInt($_POST['time']) OR $_POST['time'] <= 0)
		die;
	Connection::setPageTime((int)$_POST['id_connections'], (int)$_POST['id_page'], substr($_POST['time_start'], 0, 19), intval($_POST['time']));
}