<?php
/*
* 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

define('_PS_ADMIN_DIR_', getcwd());
define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);

include(PS_ADMIN_DIR.'/../config/config.inc.php');
include(PS_ADMIN_DIR.'/functions.php');
include(PS_ADMIN_DIR.'/header.inc.php');

if ($tab)
{
	if ($id_tab = checkingTab($tab))
	{
		$tabs = array();
		recursiveTab($id_tab);
		$tabs = array_reverse($tabs);
		echo '<div class="path_bar"><a href="?token='.Tools::getAdminToken($tab.(int)(Tab::getIdFromClassName($tab)).(int)($cookie->id_employee)).'">'.translate('Back Office').'</a>';
		foreach ($tabs AS $key => $item)
			echo ' <img src="../img/admin/separator_breadcrum.png" style="margin-right:5px" /><img src="'.((trim($item['module']) != '') ? _MODULE_DIR_.$item['module'].'/'.$item['class_name'].'.gif' : '../img/t/'.$item['class_name'].'.gif').'" style="margin-right:5px" /><a href="?tab='.$item['class_name'].'&token='.Tools::getAdminToken($item['class_name'].(int)($item['id_tab']).(int)($cookie->id_employee)).'" '.((sizeof($tabs) - 1 == $key) ? 'class="blue" style="font-color:blue;"' : '').'>'.$item['name'].'</a>';
		echo '</div>';

		if (Validate::isLoadedObject($adminObj))
			if (!$adminObj->checkToken())
				return;

		/* Filter memorization */
		if (isset($_POST) AND !empty($_POST) AND isset($adminObj->table))
			foreach ($_POST AS $key => $value)
				if (is_array($adminObj->table))
				{
					foreach ($adminObj->table AS $table)
						if (strncmp($key, $table.'Filter_', 7) === 0 OR strncmp($key, 'submitFilter', 12) === 0)
							$cookie->$key = !is_array($value) ? $value : serialize($value);
				}
				elseif (strncmp($key, $adminObj->table.'Filter_', 7) === 0 OR strncmp($key, 'submitFilter', 12) === 0)
					$cookie->$key = !is_array($value) ? $value : serialize($value);

		if (isset($_GET) AND !empty($_GET) AND isset($adminObj->table))
			foreach ($_GET AS $key => $value)
				if (is_array($adminObj->table))
				{
					foreach ($adminObj->table AS $table)
						if (strncmp($key, $table.'OrderBy', 7) === 0 OR strncmp($key, $table.'Orderway', 8) === 0)
							$cookie->$key = $value;
				}
				elseif (strncmp($key, $adminObj->table.'OrderBy', 7) === 0 OR strncmp($key, $adminObj->table.'Orderway', 12) === 0)
					$cookie->$key = $value;

		$adminObj->displayConf();
		$adminObj->postProcess();
		$adminObj->displayErrors();
		$adminObj->display();
	}
}
else /* Else display homepage */
{
	$isoDefault = Language::getIsoById((int)(Configuration::get('PS_LANG_DEFAULT')));
	$isoUser = Language::getIsoById((int)($cookie->id_lang));
	echo '<div id="adminHeader">
	<img src="../img/logo.jpg" alt="Logo" title="Logo" /><br /><br />
	<h2>'.translate('Welcome to your Back Office').'</h2>
	'.translate('Click the tabs to navigate.').'
	<br /><br /><br />';
	if (@ini_get('allow_url_fopen') AND $update = checkPSVersion())
		echo '<div class="warning warn" style="margin-bottom:30px;"><h3>'.translate('New PrestaShop version available').' : <a style="text-decoration: underline;" href="'.$update['link'].'">'.translate('Download').'&nbsp;'.$update['name'].'</a> !</h3></div>';
    elseif (!@ini_get('allow_url_fopen'))
    {
		echo '<p>'.translate('Update notification unavailable').'</p>';
		echo '<p>&nbsp;</p>';
		echo '<p>'.translate('To receive PrestaShop update warnings, you need to activate the <b>allow_url_fopen</b> command in your <b>php.ini</b> config file.').' [<a href="http://www.php.net/manual/'.$isoUser.'/ref.filesystem.php">'.translate('more info').'</a>]</p>';
		echo '<p>'.translate('If you don\'t know how to do that, please contact your host administrator !').'</p><br>';
	}
  echo '</div>';

	echo Module::hookExec('backOfficeHome');

	/* News from PrestaShop website */
	echo '<div id="adminNews">
	<h2>'.translate('PrestaShop live feed').'</h2>';
	$protocol = (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';
	echo'<iframe frameborder="no" style="margin: 0px; padding: 0px; width: 780px; height: 380px;" src="'.$protocol.'://www.prestashop.com/rss/news.php?v='._PS_VERSION_.'&lang='.$isoUser.'&activity='.(int)Configuration::get('PS_SHOP_ACTIVITY').'"></iframe></div>';
}

include(PS_ADMIN_DIR.'/footer.inc.php');


