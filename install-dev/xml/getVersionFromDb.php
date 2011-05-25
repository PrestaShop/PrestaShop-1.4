<?php
/*
* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(SETTINGS_FILE);
include_once(INSTALL_PATH.'/classes/GetVersionFromDb.php');

include(INSTALL_PATH.'/classes/LanguagesManager.php');
$lm = new LanguageManager(INSTALL_PATH.'/langs/list.xml');
$_LANG = array();
$_LIST_WORDS = array();
function lang($txt) {
	global $_LANG , $_LIST_WORDS;
	return (isset($_LANG[$txt]) ? $_LANG[$txt] : $txt);
}
if ($lm->getIncludeTradFilename())
	include_once($lm->getIncludeTradFilename());

$dbVersion = new GetVersionFromDb();
$versions = $dbVersion->getVersions();

if (!$versions)
{
	die('<action result="ko" lang="' . htmlspecialchars(lang('Warning, the installer was unable to find which is your current PrestaShop version from your database structure analysis. This means some fields or tables are missing, and upgrade is under your own risk.')) . '" />');
}

foreach ($versions as $version)
{
	if (version_compare(_PS_VERSION_, $version) == 0)
	{
		die('<action result="ok" />');
	}
}
die('<action result="ko" lang="' . htmlspecialchars(sprintf(lang('Warning, the installer found in your setting file that your PrestaShop is in version %s, but the database analysis say you seem to be in version %s. Be sure of what you are doing if you start upgrade.<br /><br />If you never tried an update to this new version, or never made modifications in your base, please ignore this warning.'), _PS_VERSION_, '(' . ((count($versions) > 1) ? $versions[count($versions) - 1] . ' - ' . $versions[0] : $versions[0]) . ')')) . '" />');
