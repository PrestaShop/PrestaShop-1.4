<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  */
  
if (!isset($_POST['token']) OR !isset($_POST['type']))
	die;

include(dirname(__FILE__).'/config/config.inc.php');

if (Configuration::get('PS_CIPHER_ALGORITHM'))
	$cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
else
	$cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
$token = $cipherTool->decrypt($_POST['token']);

if ($_POST['type'] == 'navinfo')
{
	if (!Validate::isUnsignedId((int)($token)))
		exit;
	$guest = new Guest($token);
	$guest->javascript = true;
	$guest->screen_resolution_x = (int)($_POST['screen_resolution_x']);
	$guest->screen_resolution_y = (int)($_POST['screen_resolution_y']);
	$guest->screen_color = (int)($_POST['screen_color']);
	$guest->sun_java = (int)($_POST['sun_java']);
	$guest->adobe_flash = (int)($_POST['adobe_flash']);
	$guest->adobe_director = (int)($_POST['adobe_director']);
	$guest->apple_quicktime = (int)($_POST['apple_quicktime']);
	$guest->real_player = (int)($_POST['real_player']);
	$guest->windows_media = (int)($_POST['windows_media']);
	$guest->update();
}
elseif ($_POST['type'] == 'pagetime')
{
	if (!Validate::isInt($_POST['time']) OR $_POST['time'] <= 0)
		exit;
	$tokenArray = explode('|', $token);
	Connection::setPageTime($tokenArray[0], $tokenArray[1], substr($tokenArray[2], 0, 19), (int)($_POST['time']));
}

