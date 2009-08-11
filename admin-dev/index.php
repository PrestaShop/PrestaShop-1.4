<?php

/**
  * Homepage and main page for admin panel, index.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

//ob_start();

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');
include(PS_ADMIN_DIR.'/functions.php');
include(PS_ADMIN_DIR.'/toolbar.php');
include(PS_ADMIN_DIR.'/header.inc.php');

if ($tab)
{
	if ($id_tab = checkingTab($tab))
	{
		$tabs = array();
		recursiveTab($id_tab);
		$tabs = array_reverse($tabs);
		echo '<div class="path_bar"><img src="../img/admin/prefs.gif" style="margin-right:10px" /><a href="?token='.Tools::getAdminToken($tab.intval(Tab::getIdFromClassName($tab)).intval($cookie->id_employee)).'">'.translate('Back Office').'</a>';
		foreach ($tabs AS $key => $item)
			echo ' >> <img src="../img/t/'.$item['class_name'].'.gif" style="margin-right:5px" />'.((sizeof($tabs) - 1 > $key) ? '<a href="?tab='.$item['class_name'].'&token='.Tools::getAdminToken($item['class_name'].intval($item['id_tab']).intval($cookie->id_employee)).'">' : '').$item['name'].((sizeof($tabs) - 1 > $key) ? '</a>' : '');
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
	echo '<div id="adminHeader">
	<img src="../img/logo.jpg" alt="Logo" title="Logo" /><br /><br />
	<h2>'.translate('Welcome to your Back Office').'</h2>
	'.translate('Click the tabs to navigate.').'
	<br /><br /><br />';
	
	if (@ini_get('allow_url_fopen') AND $update = checkPSVersion())
		echo '<div class="warning warn" style="margin-bottom:30px;"><h3>'.translate('New PrestaShop version avalaible').' : <a style="text-decoration: underline;" href="'.$update['link'].'">'.translate('Download').'&nbsp;'.$update['name'].'</a> !</h3></div>';
    elseif (!@ini_get('allow_url_fopen'))
    {
		echo '<p>'.translate('Update notification unavailable').'</p>';
		echo '<p>&nbsp;</p>';
		echo '<p>'.translate('To receive PrestaShop update warnings, you need to activate the <b>allow_url_fopen</b> command in your <b>php.ini</b> config file.').' [<a href="'.translate('http://www.php.net/manual/en/ref.filesystem.php').'">'.translate('more infos').'</a>]</p>';
		echo '<p>'.translate('If you don\'t know how to do that, please contact your host administrator !').'</p><br>';
	}
  echo '</div>';

	echo Module::hookExec('backOfficeHome');

	/* News from PrestaShop website */
	echo '<div id="adminNews">
	<h2>'.translate('PrestaShop live feed').'</h2>';
	$isoDefault = Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT')));
	$isoUser = Language::getIsoById(intval($cookie->id_lang));
	echo'<iframe frameborder="no" style="margin: 0px; padding: 0px; width: 780px; height: 380px;" src="http://www.prestashop.com/rss/news.php?v='._PS_VERSION_.'&lang='.$isoUser.'"></iframe></div>';
}

include(PS_ADMIN_DIR.'/footer.inc.php');

?>