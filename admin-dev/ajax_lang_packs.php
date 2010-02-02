<?php

define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');
/* Getting cookie or logout */
require_once(dirname(__FILE__).'/init.php');
	
if(!isset($_GET['iso']) OR empty($_GET['iso']))
	die('fail:0');

if(@fsockopen('www.prestashop.com', 80))
{
	// Get all iso code available
	$lang_packs = file_get_contents('http://www.prestashop.com/rss/lang_exists.php');

	if ($lang_packs)
	{
		$lang_packs = unserialize($lang_packs);
		foreach($lang_packs as $lang_pack)
		{
			if($lang_pack['iso_code'] == $_GET['iso'])
				die('ok');
		}
		die('fail:1');
	}
	else
		die('fail:2');
}
die('offline');
?>