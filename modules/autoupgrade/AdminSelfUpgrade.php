<?php
/*
* 2007-2012 PrestaShop
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
*	@author PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2012 PrestaShop SA
*	@version	Release: $Revision$
*	@license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

// _PS_ADMIN_DIR_ is defined in ajax-upgradetab, but may be not defined in direct call
if (!defined('_PS_ADMIN_DIR_') && defined('PS_ADMIN_DIR'))
	define('_PS_ADMIN_DIR_', PS_ADMIN_DIR);

// Note : we cannot use the native AdminTab because 
// we don't know the current PrestaShop version number
require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/AdminSelfTab.php');

//
require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/Upgrader.php');

if (!class_exists('Upgrader',false))
{
	if(file_exists(_PS_ROOT_DIR_.'/override/classes/Upgrader.php'))
		require_once(_PS_ROOT_DIR_.'/override/classes/Upgrader.php');
	else
		eval('class Upgrader extends UpgraderCore{}');
}


require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/Tools14.php');
if(!class_exists('Tools',false))
	eval('class Tools extends Tools14{}');

class AdminSelfUpgrade extends AdminSelfTab
{
	// used for translations
	public static $l_cache;
	// retrocompatibility
	public $noTabLink = array();
	public $id = -1;

	public $svn_link = 'http://svn.prestashop.com/trunk';

	public $ajax = false;
	public $nextResponseType = 'json'; // json, xml
	public $next = 'N/A';

	public $upgrader = null;
	public $standalone = true;

	/**
	 * set to false if the current step is a loop
	 *
	 * @var boolean
	 */
	public $stepDone = true;
	public $status = true;
	public $error ='0';
	public $nextDesc = '.';
	public $nextParams = array();
	public $nextQuickInfo = array();
	public $currentParams = array();
	/**
	 * @var array theses values will be automatically added in "nextParams"
	 * if their properties exists
	 */
	public $ajaxParams = array(
		// autoupgrade options
		'dontBackupImages',
		'install_version',
		'keepDefaultTheme',
		'keepTrad',
		'keepMails',
		'manualMode',
		'deactivateCustomModule',

		'backupName',
		'backupFilesFilename',
		'backupDbFilename',

		'restoreName',
		'restoreFilesFilename',
		'restoreDbFilenames',
	);

	public $autoupgradePath = null;
	/**
	 * autoupgradeDir
	 *
	 * @var string directory relative to admin dir
	 */
	public $autoupgradeDir = 'autoupgrade';
	public $latestRootDir = '';
	public $prodRootDir = '';
	public $adminDir = '';
	public $root_writable = null;
	public $module_version = null;

	public $lastAutoupgradeVersion = '';
	public $svnDir = 'svn';
	public $destDownloadFilename = 'prestashop.zip';

	
	/**
	 * during upgradeFiles process, 
	 * this files contains the list of queries left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toUpgradeQueriesList = 'queriesToUpgrade.list';
	/**
	 * during upgradeFiles process, 
	 * this files contains the list of files left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toUpgradeFileList = 'filesToUpgrade.list';
	/**
	 * during upgradeFiles process, 
	 * this files contains the list of files left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $diffFileList = 'filesDiff.list';
	/**
	 * during backupFiles process,
	 * this files contains the list of files left to save in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toBackupFileList = 'filesToBackup.list';
	/**
	 * during backupDb process,
	 * this files contains the list of tables left to save in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toBackupDbList = 'tablesToBackup.list';
	/**
	 * during restoreDb process,
	 * this file contains a serialized array of queries which left to execute for restoring database
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toRestoreQueryList = 'queryToRestore.list';
	/**
	 * during restoreFiles process, 
	 * this file contains difference between files present in a backupFiles archive
	 * and files currently in directories, in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toRemoveFileList = 'filesToRemove.list';
	/**
	 * during restoreFiles process, 
	 * contains list of files present in backupFiles archive
	 * 
	 * @var string
	 */
	public $fromArchiveFileList = 'filesFromArchive.list';

	/**
	 * mailCustomList contains list of mails files which are customized, 
	 * relative to original files for the current PrestaShop version
	 * 
	 * @var string
	 */
	public $mailCustomList = 'mails-custom.list';

	/**
	 * tradCustomList contains list of mails files which are customized, 
	 * relative to original files for the current PrestaShop version
	 * 
	 * @var string
	 */
	public $tradCustomList = 'translations-custom.list';
	/**
	 * tmp_files contains an array of filename which will be removed 
	 * at the beginning of the upgrade process
	 * 
	 * @var array
	 */
	public $tmp_files = array(
		'toUpgradeFileList', 
		'toUpgradeQueriesList', 
		'diffFileList', 
		'toBackupFileList', 
		'toBackupDbList',
		'toRestoreQueryList', 
		'toRemoveFileList',
		'fromArchiveFileList',
		'tradCustomList',
		'mailCustomList',
	);

	public $install_version; 
	public $dontBackupImages = null;
	public $keepDefaultTheme = null;
	public $keepTrad = null;
	public $keepMails = null;
	public $manualMode = null;
	public $deactivateCustomModule = null;

	public $sampleFileList = array();
	private $backupIgnoreFiles = array();
	private $backupIgnoreAbsoluteFiles = array();
	private $excludeFilesFromUpgrade = array();
	private $excludeAbsoluteFilesFromUpgrade = array();

	private $restoreName = null;
	private $backupName = null;
	private $backupFilesFilename = null;
	private $backupDbFilename = null;
	private $restoreFilesFilename = null;
	private $restoreDbFilenames = array();

	/**
	* int loopBackupFiles : if your server has a low memory size, lower this value
	* @TODO remove the static, add a const, and use it like this : min(AdminUpgrade::DEFAULT_LOOP_ADD_FILE_TO_ZIP,Configuration::get('LOOP_ADD_FILE_TO_ZIP');
	*/
	public static $loopBackupFiles = 500;
	/**
	* int loopBackupDbTime : if your server has a low memory size, lower this value
	* @TODO remove the static, add a const, and use it like this : min(AdminUpgrade::DEFAULT_LOOP_ADD_FILE_TO_ZIP,Configuration::get('LOOP_ADD_FILE_TO_ZIP');
	*/
	public static $loopBackupDbTime = 6;
	/**
   * int loopUpgradeFiles : if your server has a low memory size, lower this value
	 */
	public static $loopUpgradeFiles = 1000;
/**
 * int loopRestoreFiles : if your server has a low memory size, lower this value
 */
	public static $loopRestoreFiles = 500;
/**
 * int loopRestoreQueryTime : if your server has a low memory size, lower this value (in sec)
 */
	public static $loopRestoreQueryTime = 6;
/**
 * int loopRemoveSamples : if your server has a low memory size, lower this value
 */
	public static $loopRemoveSamples = 1000;
/**
 * int loopRemoveUpgraderFiles : remove files to keep only files that where in backup archive
 */
	public static $loopRemoveUpgradedFiles = 1000;

	/* usage :  key = the step you want to ski
  * value = the next step you want instead
 	*	example : public static $skipAction = array('download' => 'unzip');
	*	initial order upgrade: download, unzip, removeSamples, backupFiles, backupDb, upgradeFiles, upgradeDb, upgradeComplete
	* initial order rollback: rollback, restoreFiles, restoreDb, rollbackComplete
	*/
	public static $skipAction = array();

	public $useSvn;
/**
 * if set to true, will use pclZip library
 * even if ZipArchive is available
 */
	public static $force_pclZip = false;

	protected $_includeContainer = false;

	/**
	 * replace tools encrypt
	 * 
	 * @param mixed $string 
	 * @return void
	 */
	public function encrypt($string)
	{
		return md5(_COOKIE_KEY_.$string);
	}

	public function checkToken()
	{
		// simple checkToken in ajax-mode, to be free of Cookie class (and no Tools::encrypt() too )
		if ($this->ajax)
			return ($_COOKIE['autoupgrade'] == $this->encrypt($_COOKIE['id_employee']));
		else
			return parent::checkToken();
	}

	/**
	 * create cookies id_employee, id_tab and autoupgrade (token)
	 */
	public function createCustomToken()
	{
		// ajax-mode for autoupgrade, we can't use the classic authentication
		// so, we'll create a cookie in admin dir, based on cookie key 
		global $cookie;
		$id_employee = $cookie->id_employee;
		$adminDir = trim(str_replace($this->prodRootDir, '', $this->adminDir), '/');
		$cookiePath = __PS_BASE_URI__.$adminDir;
		setcookie('id_employee', $id_employee, time()+7200, $cookiePath);
		setcookie('id_tab', $this->id, time()+7200, $cookiePath);
		setcookie('autoupgrade', $this->encrypt($id_employee), time()+7200, $cookiePath);
		return false;
	}

	public function viewAccess($disable = false)
	{
		if ($this->ajax)
			return true;
		else
		{
			// simple access : we'll allow only admin
			global $cookie;
			if ($cookie->profile == 1)
				return true;
		}
		return false;
	}

	public function __construct()
	{
		// @todo : do this only in ajax mode and if we are allowed to use theses functions
		@set_time_limit(0);
		@ini_set('max_execution_time', '0');
		global $ajax;
		if (!empty($ajax))
			$this->ajax = true;

		$this->init();
		// retrocompatibility when used in module : Tab can't work,
		// but we saved the tab id in a cookie.
		if(class_exists('Tab',false))
			parent::__construct();
		else
		{
			if (isset($_COOKIE['id_tab']))
				$this->id = $_COOKIE['id_tab'];
		}
	}

	protected function l($string, $class = 'AdminTab', $addslashes = FALSE, $htmlentities = TRUE)
	{
		if(version_compare(_PS_VERSION_,'1.4.3.0','<'))
		{
			// need to be called in order to populate $classInModule
			return self::findTranslation('autoupgrade', $string, 'AdminSelfUpgrade');
		}
		else
			return parent::l($string, $class, $addslashes, $htmlentities);
	}
	
	/**
	 * findTranslation (initially in Module class), to make translations works
	 * 
	 * @param string $name module name
	 * @param string $string string to translate
	 * @param string $source current class
	 * @return string translated string
	 */
	public static function findTranslation($name, $string, $source)
	{
		global $_MODULES;
		
		$cache_key = $name . '|' . $string . '|' . $source;
		
		if (!isset(self::$l_cache[$cache_key]))
		{
			if (!is_array($_MODULES))
				return str_replace('"', '&quot;', $string);
			// set array key to lowercase for 1.3 compatibility
			$_MODULES = array_change_key_case($_MODULES);
			$currentKey = '<{'.strtolower($name).'}'.strtolower(_THEME_NAME_).'>'.strtolower($source).'_'.md5($string);
			// note : we should use a variable to define the default theme (instead of "prestashop")
			$defaultKey = '<{'.strtolower($name).'}prestashop>'.strtolower($source).'_'.md5($string);
			
			if (isset($_MODULES[$currentKey]))
				$ret = stripslashes($_MODULES[$currentKey]);
			elseif (isset($_MODULES[strtolower($currentKey)]))
				$ret = stripslashes($_MODULES[strtolower($currentKey)]);
			elseif (isset($_MODULES[$defaultKey]))
				$ret = stripslashes($_MODULES[$defaultKey]);
			elseif (isset($_MODULES[strtolower($defaultKey)]))
				$ret = stripslashes($_MODULES[strtolower($defaultKey)]);
			else
				$ret = stripslashes($string);
			
			self::$l_cache[$cache_key] = str_replace('"', '&quot;', $ret);
		} 
		return self::$l_cache[$cache_key];
	}

	/**
	 * function to set configuration fields display
	 *
	 * @return void
	 */
	private function _setFields()
	{
		$this->_fieldsAutoUpgrade['PS_AUTOUP_DONT_SAVE_IMAGES'] = array(
			'title' => $this->l('Also save images'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc'=>$this->l('You can exclude the image directory from backup if you already saved it by another method (not recommended)'),
		);

		$this->_fieldsAutoUpgrade['PS_AUTOUP_KEEP_DEFAULT_THEME'] = array(
			'title' => $this->l('Keep theme "prestashop"'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc'=>$this->l('If you have customized PrestaShop default theme, you can protect it from upgrade (not recommended)'),
		);

		$this->_fieldsAutoUpgrade['PS_AUTOUP_KEEP_TRAD'] = array(
			'title' => $this->l('Keep translations'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc'=>$this->l('If set to yes, you will keep all your translations'),
		);

		$this->_fieldsAutoUpgrade['PS_AUTOUP_KEEP_MAILS'] = array(
			'title' => $this->l('Keep default mails'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc'=>$this->l('If set to yes, new mailtemplate will be added but old will not be overwritten (not recommended)'),
		);

		$this->_fieldsAutoUpgrade['PS_AUTOUP_CUSTOM_MOD_DESACT'] = array(
			'title' => $this->l('Deactivate custom modules'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc'=>$this->l('If you don\'t deactivate your modules, you can have some compatibility problems and the Modules page might not load correctly.'),
		);
		// allow manual mode only for dev
		if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_)
			$this->_fieldsAutoUpgrade['PS_AUTOUP_MANUAL_MODE'] = array(
				'title' => $this->l('Manual mode'),	'cast' => 'intval',	'validation' => 'isBool',
				'type' => 'bool',	'desc'=>$this->l('Check this if you want to stop after each step'),
			);

		if (defined('_PS_ALLOW_UPGRADE_UNSTABLE_') AND _PS_ALLOW_UPGRADE_UNSTABLE_ AND function_exists('svn_checkout'))
		{
			$this->_fieldsAutoUpgrade['PS_AUTOUP_USE_SVN'] = array(
				'title' => $this->l('Use Subversion'), 'cast' => 'intval', 'validation' => 'isBool',
				'type' => 'bool',	'desc' => $this->l('check this if you want to use unstable svn instead of official release'),
			);
		}
	}

	public function configOk()
	{
		$allowed_array = $this->getCheckCurrentConfig();
		$allowed = array_product($allowed_array);
		return $allowed;
	}

	public function getcheckCurrentConfig()
	{
		static $allowed_array;

		if(empty($allowed_array))
		{
			$allowed_array = array();
			$allowed_array['fopen'] = ConfigurationTest::test_fopen();
			$allowed_array['root_writable'] = $this->getRootWritable();
			$allowed_array['shop_deactivated'] = !Configuration::get('PS_SHOP_ENABLE');
			// xml can enable / disable upgrade
			$allowed_array['autoupgrade_allowed'] = $this->upgrader->autoupgrade;
			$allowed_array['need_upgrade'] = $this->upgrader->need_upgrade;
			$allowed_array['module_version_ok'] = $this->checkAutoupgradeLastVersion();
			// if one option has been defined, all options are.
			$allowed_array['module_configured'] = (Configuration::get('PS_AUTOUP_KEEP_MAILS') !== false);
		}
		return $allowed_array;
	}

	public function getRootWritable()
	{
		// test if prodRootDir is writable recursively
		if (ConfigurationTest::test_dir($this->prodRootDir, true))
			$this->root_writable = true;
		
		return $this->root_writable;
	}

	public function getModuleVersion()
	{
		if (is_null($this->module_version))
		{
			if (file_exists(_PS_ROOT_DIR_.'/modules/autoupgrade/config.xml')
				&& $xml_module_version = simplexml_load_file(_PS_ROOT_DIR_.'/modules/autoupgrade/config.xml')
			)
				$this->module_version = (string)$xml_module_version->version;
			else 
				$this->module_version = false;
		}
		return $this->module_version;
	}

	public function checkAutoupgradeLastVersion()
	{
		if ($this->getModuleVersion())
			$this->lastAutoupgradeVersion = version_compare($this->module_version, $this->upgrader->autoupgrade_last_version, '>=');
		else
			$this->lastAutoupgradeVersion = true;

		return $this->lastAutoupgradeVersion;
	}


	/**
	 * init to build informations we need
	 *
	 * @return void
	 */
	public function init()
	{
		// For later use, let's set up prodRootDir and adminDir
		// This way it will be easier to upgrade a different path if needed
		$this->prodRootDir = _PS_ROOT_DIR_;
		$this->adminDir = _PS_ADMIN_DIR_;

		// from $_POST or $_GET
		$this->action = empty($_REQUEST['action'])?null:$_REQUEST['action'];
		$this->currentParams = empty($_REQUEST['params'])?null:$_REQUEST['params'];
		// test writable recursively
		if(version_compare(_PS_VERSION_,'1.4.6.0','<') || !class_exists('ConfigurationTest', false))
		{
			require_once('ConfigurationTest.php');
			if(!class_exists('ConfigurationTest', false) AND class_exists('ConfigurationTestCore'))
				eval('class ConfigurationTest extends ConfigurationTestCore{}');
		}

		// checkPSVersion only if not ajax
		if (empty($this->action))
		{
			$this->upgrader = new Upgrader();
			$this->upgrader->checkPSVersion();
			$this->install_version = $this->upgrader->version_num;
		}
		// If you have defined this somewhere, you know what you do
		if (defined('_PS_ALLOW_UPGRADE_UNSTABLE_') AND _PS_ALLOW_UPGRADE_UNSTABLE_ AND function_exists('svn_checkout'))
		{
			if(version_compare(_PS_VERSION_,'1.4.5.0','<') OR class_exists('Configuration',false))
				$this->useSvn = Configuration::get('PS_AUTOUP_USE_SVN');
		}
		else
			$this->useSvn = false;


		// If not exists in this sessions, "create"
		// session handling : from current to next params
		if (isset($this->currentParams['removeList']))
			$this->nextParams['removeList'] = $this->currentParams['removeList'];

		if (isset($this->currentParams['filesToUpgrade']))
			$this->nextParams['filesToUpgrade'] = $this->currentParams['filesToUpgrade'];

		// set autoupgradePath, to be used in backupFiles and backupDb config values
		$this->autoupgradePath = $this->adminDir.DIRECTORY_SEPARATOR.$this->autoupgradeDir;

		// directory missing
		// @todo move this in upgrade step
		if (!file_exists($this->autoupgradePath))
			if (!@mkdir($this->autoupgradePath,0777))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$this->autoupgradePath);

		// directory missing
		// @todo move this in upgrade step
		$latest = $this->autoupgradePath.DIRECTORY_SEPARATOR.'latest';
		if (!file_exists($latest))
			if (!@mkdir($latest,0777))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$latest);

		$this->latestRootDir = $latest.DIRECTORY_SEPARATOR.'prestashop';
		// @TODO future option "install in test dir"
		//	$this->testRootDir = $this->autoupgradePath.DIRECTORY_SEPARATOR.'test';

		/* load options from configuration if we're not in ajax mode */
		if (false == $this->ajax)
		{
			$this->dontBackupImages = !Configuration::get('PS_AUTOUP_DONT_SAVE_IMAGES');
			$this->keepDefaultTheme = Configuration::get('PS_AUTOUP_KEEP_DEFAULT_THEME');
			$this->keepTrad = Configuration::get('PS_AUTOUP_KEEP_TRAD');
			$this->keepMails = Configuration::get('PS_AUTOUP_KEEP_MAILS');
			$this->manualMode = Configuration::get('PS_AUTOUP_MANUAL_MODE');
			$this->deactivateCustomModule = Configuration::get('PS_AUTOUP_CUSTOM_MOD_DESACT');

			$rand = dechex ( mt_rand(0, min(0xffffffff, mt_getrandmax() ) ) );
			$date = date('Ymd-His');
			$this->backupName = 'V'._PS_VERSION_.'_'.$date.'-'.$rand;
			$this->backupFilesFilename = 'auto-backupfiles_'.$this->backupName.'.zip';
			$this->backupDbFilename = 'auto-backupdb_XXXXXX_'.$this->backupName.'.sql';
			// removing temporary files

			foreach($this->tmp_files as $tmp_file)
				if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->$tmp_file))
					unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->$tmp_file);
		}
		else
		{
			foreach($this->ajaxParams as $prop)
				if(property_exists($this, $prop))
					$this->{$prop} = isset($this->currentParams[$prop])?$this->currentParams[$prop]:'';
		}
		// We can add any file or directory in the exclude dir : theses files will be not removed or overwritten	
		// @TODO cache should be ignored recursively, but we have to reconstruct it after upgrade
		// - compiled from smarty
		// - .svn
		$this->backupIgnoreAbsoluteFiles[] = "/tools/smarty_v2/compile";
		$this->backupIgnoreAbsoluteFiles[] = "/tools/smarty_v2/cache";
		$this->backupIgnoreAbsoluteFiles[] = "/tools/smarty/compile";
		$this->backupIgnoreAbsoluteFiles[] = "/tools/smarty/cache";
		// do not care about the two autoupgrade dir we use;
		$this->backupIgnoreAbsoluteFiles[] = "/modules/autoupgrade";
		$this->backupIgnoreAbsoluteFiles[] = "/admin/autoupgrade";

		$this->excludeFilesFromUpgrade[] = '.';
		$this->excludeFilesFromUpgrade[] = '..';
		$this->excludeFilesFromUpgrade[] = '.svn';
		// do not copy install, neither settings.inc.php in case it would be present
		$this->excludeAbsoluteFilesFromUpgrade[] = "/install";
		$this->excludeFilesFromUpgrade[] = 'settings.inc.php';
		// this will exclude autoupgrade dir from admin, and autoupgrade from modules
		$this->excludeFilesFromUpgrade[] = 'autoupgrade';
		$this->backupIgnoreFiles[] = '.';
		$this->backupIgnoreFiles[] = '..';
		$this->backupIgnoreFiles[] = '.svn';
		$this->backupIgnoreFiles[] = 'autoupgrade';

		if ($this->dontBackupImages)
			$this->backupIgnoreAbsoluteFiles[] = "/img";


		if ($this->keepDefaultTheme)
			$this->excludeAbsoluteFilesFromUpgrade[] = "/themes/prestashop";

	}

	/**
	 * getFilePath return the path to the zipfile containing prestashop.
	 *
	 * @return void
	 */
	private function getFilePath()
	{
		return $this->autoupgradePath.DIRECTORY_SEPARATOR.$this->destDownloadFilename;
	}

	public function postProcess()
	{
		global $currentIndex;
		$this->_setFields();

		if (Tools::isSubmit('submitAutoUpgradeOptions'))
			return $this->_postConfig($this->_fieldsAutoUpgrade);

		if (Tools::isSubmit('deletebackup'))
		{
			$name = Tools::getValue('name');
			$filelist = scandir($this->autoupgradePath);
			foreach($matches[0] as $filename)
				if (preg_match('#^auto-backup(db|files)_'.preg_quote($name).'\.*$#', $filename, $matches))
					$res &= unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$filename);
			if ($res)
				Tools::redirectAdmin($currentIndex.'&conf=1&token='.Tools::getValue('token'));
			else
				$this->_errors[] = sprintf($this->l('Error when trying to delete backups %s'), $name);
		}
	}

	/**
	 * ends the rollback process
	 * 
	 * @return void
	 */
	public function ajaxProcessRollbackComplete()
	{
		$this->nextDesc = $this->l('Restoration process done. Congratulations ! You can now reactive your shop.');
		$this->next = '';
	}

	/**
	 * ends the upgrade process
	 * 
	 * @return void
	 */
	public function ajaxProcessUpgradeComplete()
	{
		$this->nextDesc = $this->l('Upgrade process done. Congratulations ! You can now reactive your shop.');
		$this->next = '';
	}
	
	public function ajaxProcessCompareReleases()
	{
		$this->upgrader = new Upgrader();
		$this->upgrader->checkPSVersion();

		$diffFileList = $this->upgrader->getDiffFilesList(_PS_VERSION_, $this->upgrader->version_num);
		if (!is_array($diffFileList))
		{
			$this->nextParams['status'] = 'error';
			$this->nextParams['msg'] = '[TECHNICAL ERROR] Unable to generate diff file list';
		}
		else
		{
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->diffFileList, serialize($diffFileList));
			if (count($diffFileList) > 0)
				$this->nextParams['msg'] = sprintf($this->l('%1$s files are diff and will be removed during this upgrade'), count($diffFileList['deleted']));
			else
				$this->nextParams['msg'] = $this->l('No diff files found.');
			$this->nextParams['result'] = $diffFileList;
		}
	}

	public function ajaxProcessCheckFilesVersion()
	{
		$this->upgrader = new Upgrader();

		$changedFileList = $this->upgrader->getChangedFilesList();
		if ($this->upgrader->isAuthenticPrestashopVersion() === true
			&& !is_array($changedFileList) )
		{
			$this->nextParams['status'] = 'error';
			$this->nextParams['msg'] = '[TECHNICAL ERROR] Unable to check files';
			$testOrigCore = false;
		}
		else
		{
			if ($this->upgrader->isAuthenticPrestashopVersion() === true)
			{
				$this->nextParams['status'] = 'ok';
				$testOrigCore = true;
			}
			else
			{
				$testOrigCore = false;
				$this->nextParams['status'] = 'warn';
			}

			if (!isset($changedFileList['core']))
				$changedFileList['core'] = array();

			if (!isset($changedFileList['translation']))
				$changedFileList['translation'] = array();
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->tradCustomList,serialize($changedFileList['translation']));
			
			if (!isset($changedFileList['mail']))
				$changedFileList['mail'] = array();
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->mailCustomList,serialize($changedFileList['mail']));


			if ($changedFileList === false)
			{
				$changedFileList = array();
				$this->nextParams['msg'] = $this->l('Unable to check files');
				$this->nextParams['status'] = 'error';
			}
			else
			{
				$this->nextParams['msg'] = ($testOrigCore?$this->l('Core files are ok'):sprintf($this->l('%1$s core files have been modified (%2$s total)'), count($changedFileList['core']), count(array_merge($changedFileList['core'], $changedFileList['mail'], $changedFileList['translation']))));
			}
			$this->nextParams['result'] = $changedFileList;
		}
	}

	public function ajaxProcessUpgradeNow()
	{
		$this->nextDesc = $this->l('Starting upgrade ...');

		if ($this->useSvn)
		{
			$this->next = 'svnCheckout';
			$this->nextDesc = $this->l('switching to svn checkout (useSvn set to true)');
		}
		else
		{
			$this->next = 'download';
			$this->nextDesc = $this->l('Shop deactivated. Now downloading (this can takes some times )...');
		}
	}

	public function ajaxProcessSvnExport()
	{
		if ($this->useSvn)
		{
			// first of all, delete the content of the latest root dir just in case
			if (is_dir($this->latestRootDir))
			{
				Tools::deleteDirectory($this->latestRootDir, false);
				$this->nextQuickInfo[] = $this->l('latest directory has been emptied');
			}

			if (!file_exists($this->latestRootDir))
				@mkdir($this->latestRootDir);

			if (svn_export($this->autoupgradePath . DIRECTORY_SEPARATOR . $this->svnDir, $this->latestRootDir))
			{

				// export means svn means install-dev and admin-dev.
				// let's rename admin to the correct admin dir
				// and rename install-dev to install
				$adminDir = str_replace($this->prodRootDir, '', $this->adminDir);
				rename($this->latestRootDir.DIRECTORY_SEPARATOR.'install-dev', $this->latestRootDir.DIRECTORY_SEPARATOR.'install');
				rename($this->latestRootDir.DIRECTORY_SEPARATOR.'admin-dev', $this->latestRootDir.DIRECTORY_SEPARATOR.$adminDir);

				// Unsetting to force listing
				unset($this->nextParams['removeList']);
				$this->next = "removeSamples";
				$this->nextDesc = $this->l('Export svn complete. removing sample files...');
				return true;
			}
			else
			{
				$this->next = 'error';
				$this->nextDesc = $this->l('error when svn export ');
			}
		}
	}

	/**
	 * extract last version into admin/autoupgrade/latest directory
	 * 
	 * @return void
	 */
	public function ajaxProcessUnzip(){
		if(version_compare(_PS_VERSION_,'1.4.5.0','<')
			AND !class_exists('Tools',false)
		)
			require_once('Tools.php');

		$filepath = $this->getFilePath();
		$destExtract = $this->autoupgradePath.DIRECTORY_SEPARATOR.'latest';
		if (file_exists($destExtract))
		{
			Tools::deleteDirectory($destExtract, false);
			$this->nextQuickInfo[] = $this->l('latest directory has been emptied');
		}

		if ($this->ZipExtract($filepath, $destExtract))
		{
				$adminDir = str_replace($this->prodRootDir, '', $this->adminDir);
				rename($this->latestRootDir.DIRECTORY_SEPARATOR.'admin', $this->latestRootDir.DIRECTORY_SEPARATOR.$adminDir);
				// Unsetting to force listing
				unset($this->nextParams['removeList']);
				$this->next = "removeSamples";
				$this->nextDesc = $this->l('Extract complete. removing sample files...');
				return true;
		}
		else{
				$this->next = "error";
				$this->nextDesc = sprintf($this->l('unable to extract %1$s into %2$s ...'), $filepath, $destExtract);
				return true;
		}
	}


	/**
	 * _listSampleFiles will make a recursive call to scandir() function
	 * and list all file which match to the $fileext suffixe (this can be an extension or whole filename)
	 *
	 * @TODO maybe $regex instead of $fileext ?
	 * @param string $dir directory to look in
	 * @param string $fileext suffixe filename
	 * @return void
	 */
	private function _listSampleFiles($dir, $fileext = '.jpg'){
		$res = true;
		$dir = rtrim($dir,'/').DIRECTORY_SEPARATOR;
		$toDel = scandir($dir);
		// copied (and kind of) adapted from AdminImages.php
		foreach ($toDel AS $file)
		{
			if ($file[0] != '.')
			{
				if (preg_match('#'.preg_quote($fileext,'#').'$#i',$file))
				{
					$this->sampleFileList[] = $dir.$file;
				}
				else if (is_dir($dir.$file))
				{
					$res &= $this->_listSampleFiles($dir.$file, $fileext);
				}
			}
		}
		return $res;
	}

	public function _listFilesInDir($dir, $way = 'backup')
	{
		$list = array();
		$allFiles = scandir($dir);
		foreach ($allFiles as $file)
		{
			if ($file[0] != '.')
			{
				$fullPath = $dir.DIRECTORY_SEPARATOR.$file;

				if (!$this->_skipFile($file, $fullPath, $way))
				{
					if (is_dir($fullPath))
						$list = array_merge($list, $this->_listFilesInDir($fullPath, $way));
					else
						$list[] = $fullPath;
				}
				// no else needed !
			}
		}
		return $list;
	}


	public function _listFilesToRemove()
	{
		$prev_version = preg_match('#auto-backupfiles_V([0-9.]*)_#', $this->restoreFilesFilename, $matches);
		if ($prev_version)
			$prev_version = $matches[1];
	
		if (!$this->upgrader)
			$this->upgrader = new Upgrader();
		

		$toRemove = $this->upgrader->getDiffFilesList(_PS_VERSION_, $prev_version, false);
		$adminDir = str_replace($this->prodRootDir, '', $this->adminDir);
//		$toRemove = $this->upgrader->getDiffFilesList(_PS_VERSION_, $prev_version, false);
		foreach ($toRemove as $key => $file)
		{
			$filename = substr($file, strrpos($file, '/')+1);
			$toRemove[$key] = preg_replace('#^/admin#', $adminDir, $file);
			if ($this->_skipFile($filename, $file, 'backup'))
				unset($toRemove[$key]);
		}
		return $toRemove;
	}

	/**
	 * list files to upgrade and save them in a serialized array in $this->toUpgradeFileList
	 * 
	 * @param string $dir 
	 * @return number of files found
	 */
	public function _listFilesToUpgrade($dir)
	{
		static $list = array();
		if (!is_dir($dir))
		{
			$this->nextQuickInfo[] = sprintf('[ERROR] %s doesn\'t exists or is not a directory', $dir);
			$this->nextDesc = $this->l('Nothing has been extracted. It seems the unzip step has been skipped.');
			$this->next = 'error';
			return false;
		}

		$allFiles = scandir($dir);
		foreach ($allFiles as $file)
		{
			$fullPath = $dir.DIRECTORY_SEPARATOR.$file;

			if (!$this->_skipFile($file, $fullPath, "upgrade"))
			{
				$list[] = str_replace($this->latestRootDir, '', $fullPath);
				// if is_dir, we will create it :)
				if (is_dir($fullPath))
					if (strpos($dir.DIRECTORY_SEPARATOR.$file, 'install') === false)
						$this->_listFilesToUpgrade($fullPath);
			}
		}
		file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toUpgradeFileList,serialize($list));
		$this->nextParams['filesToUpgrade'] = $this->toUpgradeFileList;
		return sizeof($this->toUpgradeFileList);
	}


	public function ajaxProcessUpgradeFiles()
	{
		$this->nextParams = $this->currentParams;

		if (!isset($this->nextParams['filesToUpgrade']))
		{
			// list saved in $this->toUpgradeFileList
			$total_files_to_upgrade = $this->_listFilesToUpgrade($this->latestRootDir);
			if ($total_files_to_upgrade == 0)
			{
				$this->nextQuickInfo[] = '[ERROR] Unable to find files to upgrade.';
				$this->nextDesc = $this->l('Unable to list files to upgrade');
				$this->next = 'error';
				return false;
			}
			$this->nextQuickInfo[] = sprintf($this->l('%s files will be upgraded.'), $total_files_to_upgrade);

			$this->next = 'upgradeFiles';
			return true;
		}

		// later we could choose between _PS_ROOT_DIR_ or _PS_TEST_DIR_
		$this->destUpgradePath = $this->prodRootDir;

		// upgrade files one by one like for the backup
		// with a 1000 loop because it's funny
		// @TODO :
		// foreach files in latest, copy
		$this->next = 'upgradeFiles';
		$filesToUpgrade = @unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->nextParams['filesToUpgrade']));
		if (!is_array($filesToUpgrade))
		{
			$this->next = 'error';
			$this->nextDesc = $this->l('filesToUpgrade is not an array');
			$this->nextQuickInfo[] = $this->l('filesToUpgrade is not an array');
			return false;
		}

		// @TODO : does not upgrade files in modules, translations if they have not a correct md5 (or crc32, or whatever) from previous version
		for ($i=0;$i < self::$loopUpgradeFiles;$i++)
		{
			if (sizeof($filesToUpgrade) <= 0)
			{
				$this->next = 'upgradeDb';
				unlink($this->nextParams['filesToUpgrade']);
				$this->nextDesc = $this->l('All files upgraded. Now upgrading database');
				$this->nextResponseType = 'json';
				break;
			}

			$file = array_shift($filesToUpgrade);
			if (!$this->upgradeThisFile($file))
			{
				// put the file back to the begin of the list
				$totalFiles = array_unshift($filesToUpgrade, $file);
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('error when trying to upgrade %s'), $file);
				break;
			}
		}
		$this->nextDesc = sprintf($this->l('%1$s files left to upgrade.'), sizeof($filesToUpgrade));
		$this->nextQuickInfo[] = sprintf($this->l('%2$s files left to upgrade.'), $file, sizeof($filesToUpgrade));
		file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->nextParams['filesToUpgrade'],serialize($filesToUpgrade));
		return true;
	}

  private function createCacheFsDirectories($level_depth, $directory = false)
  {
    if (!$directory)
      $directory = _PS_CACHEFS_DIRECTORY_;
    $chars = '0123456789abcdef';
    for ($i = 0; $i < strlen($chars); $i++)
    {   
      $new_dir = $directory.$chars[$i].'/';
      if (mkdir($new_dir))
        if (chmod($new_dir, 0777))
          if ($level_depth - 1 > 0)
            self::createCacheFsDirectories($level_depth - 1, $new_dir);
    }   
  }




	public function ajaxProcessUpgradeDb()
	{
		// @TODO : 1/2/3 have to be done at the beginning !!!!!!!!!!!!!!!!!!!!!!
		$this->nextParams = $this->currentParams;
		if (!$this->doUpgrade())
		{
			$this->next = 'error';
			$this->nextDesc = $this->l('error during upgrade Db. You may need to restore your database');
			return false;
		}
		// @TODO
		// 5) compare activated modules and reactivate them
		return true;
	}

	/**
	 * This function now replaces doUpgrade.php or upgrade.php
	 * 
	 * @return void
	 */
	public function doUpgrade()
	{
		// Initialize
		// setting the memory limit to 128M only if current is lower
		$memory_limit = ini_get('memory_limit');
		if (substr($memory_limit,-1) != 'G'
			AND ((substr($memory_limit,-1) == 'M' AND substr($memory_limit,0,-1) < 128)
			OR is_numeric($memory_limit) AND (intval($memory_limit) < 131072))
		){
			@ini_set('memory_limit','128M');
		}

		/* Redefine REQUEST_URI if empty (on some webservers...) */
		if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '')
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
		if ($tmp = strpos($_SERVER['REQUEST_URI'], '?'))
			$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $tmp);
		$_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);

		define('INSTALL_VERSION', $this->install_version);
		define('INSTALL_PATH', realpath($this->latestRootDir.DIRECTORY_SEPARATOR.'install'));


		define('PS_INSTALLATION_IN_PROGRESS', true);
		define('SETTINGS_FILE', $this->prodRootDir . '/config/settings.inc.php');
		define('DEFINES_FILE', $this->prodRootDir .'/config/defines.inc.php');
		define('INSTALLER__PS_BASE_URI', substr($_SERVER['REQUEST_URI'], 0, -1 * (strlen($_SERVER['REQUEST_URI']) - strrpos($_SERVER['REQUEST_URI'], '/')) - strlen(substr(dirname($_SERVER['REQUEST_URI']), strrpos(dirname($_SERVER['REQUEST_URI']), '/')+1))));
	//	define('INSTALLER__PS_BASE_URI_ABSOLUTE', 'http://'.ToolsInstall::getHttpHost(false, true).INSTALLER__PS_BASE_URI);

		// XML Header
		// header('Content-Type: text/xml');



		$filePrefix = 'PREFIX_';
		$engineType = 'ENGINE_TYPE';

		$mysqlEngine = (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM');

		if (function_exists('date_default_timezone_set'))
			date_default_timezone_set('Europe/Paris');

		// if _PS_ROOT_DIR_ is defined, use it instead of "guessing" the module dir.
		if (defined('_PS_ROOT_DIR_') AND !defined('_PS_MODULE_DIR_'))
			define('_PS_MODULE_DIR_', _PS_ROOT_DIR_.'/modules/');
		else if (!defined('_PS_MODULE_DIR_'))
			define('_PS_MODULE_DIR_', INSTALL_PATH.'/../modules/');

		// @todo upgrade_dir_php should be handled by Upgrader class
		$upgrade_dir_php = 'upgrade/php';
		if (!file_exists(INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php))
		{
			$upgrade_dir_php = 'php';
			if (!file_exists(INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php))
			{
				$this->next = 'error';
				$this->nextDesc = $this->l('php upgrade dir is not found');
				$this->nextQuickInfo[] = 'php upgrade dir is missing';
				return false;
			}
		}
			define('_PS_INSTALLER_PHP_UPGRADE_DIR_',  INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php.DIRECTORY_SEPARATOR);


		//old version detection
		global $oldversion, $logger;
		$oldversion = false;
		if (file_exists(SETTINGS_FILE))
		{
			include_once(SETTINGS_FILE);
			// include_once(DEFINES_FILE);
			$oldversion = _PS_VERSION_;

		}
		else
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('The config/settings.inc.php file was not found.');
			return false;
			die('<action result="fail" error="30" />'."\n");
		}


		if (!defined('__PS_BASE_URI__'))
			define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])).'/../../');


		if (!defined('_THEMES_DIR_'))
			define('_THEMES_DIR_', __PS_BASE_URI__.'themes/');

		$oldversion = _PS_VERSION_;
		$versionCompare =  version_compare(INSTALL_VERSION, $oldversion);

		if ($versionCompare == '-1')
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('This installer is too old');
			return false;
			// die('<action result="fail" error="27" />'."\n");
		}
		elseif ($versionCompare == 0)
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l(sprintf('You already have the %s version.',INSTALL_VERSION));
			return false;
			die('<action result="fail" error="28" />'."\n");
		}
		elseif ($versionCompare === false)
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('There is no older version. Did you delete or rename the config/settings.inc.php file?');
			return false;
			die('<action result="fail" error="29" />'."\n");
		}

		//check DB access
		$this->db();
		error_reporting(E_ALL);
		$resultDB = MySql::tryToConnect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
		if ($resultDB !== 0)
		{
			// $logger->logError('Invalid database configuration.');
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('Invalid database configuration');
			return false;
			die("<action result='fail' error='".$resultDB."'/>\n");
		}

		//custom sql file creation
		$upgradeFiles = array();

		// @todo : upgrade/sql or sql/upgrade should be handled in the Upgrader class
		$upgrade_dir_sql = INSTALL_PATH.'/upgrade/sql';
		// if 1.4;
		if (!file_exists($upgrade_dir_sql))
			$upgrade_dir_sql = INSTALL_PATH.'/sql/upgrade';

		if (!file_exists($upgrade_dir_sql))
		{
			$this->next = 'error';
			$this->nextDesc = $this->l('unable to find upgrade directory in the install path');
			return false;
		}

		if ($handle = opendir($upgrade_dir_sql))
		{
				while (false !== ($file = readdir($handle)))
						if ($file != '.' AND $file != '..')
								$upgradeFiles[] = str_replace(".sql", "", $file);
				closedir($handle);
		}
		if (empty($upgradeFiles))
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = sprintf($this->l('Cannot find the sql upgrade files. Please verify that the %s folder is not empty'), $upgrade_dir_sql);
			// fail 31
			return false;
		}
		natcasesort($upgradeFiles);
		$neededUpgradeFiles = array();

		// fix : complete version number if there is not all 4 numbers
		// for example replace 1.4.3 by 1.4.3.0
		// consequences : file 1.4.3.0.sql will be skipped if oldversion = 1.4.3
		// @since 1.4.4.0
		$arrayVersion = preg_split('#\.#', $oldversion);
		$versionNumbers = sizeof($arrayVersion);

		if ($versionNumbers != 4)
			$arrayVersion = array_pad($arrayVersion, 4, '0');

		$oldversion = implode('.', $arrayVersion);
		// end of fix

		foreach ($upgradeFiles AS $version)
		{

			if (version_compare($version, $oldversion) == 1 AND version_compare(INSTALL_VERSION, $version) != -1)
				$neededUpgradeFiles[] = $version;
		}

		if (empty($neededUpgradeFiles))
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('No upgrade is possible.');
			return false;

			$logger->logError('No upgrade is possible.');
			die('<action result="fail" error="32" />'."\n");
		}



		$sqlContentVersion = array(); 
		if(isset($_GET['customModule']) AND $_GET['customModule'] == 'desactivate')
		{
			require_once(_PS_INSTALLER_PHP_UPGRADE_DIR_.'deactivate_custom_modules.php');
			deactivate_custom_modules();
		}

		foreach($neededUpgradeFiles AS $version)
		{
			$file = $upgrade_dir_sql.DIRECTORY_SEPARATOR.$version.'.sql';
			if (!file_exists($file))
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('Error while loading sql upgrade file "%s.sql".'), $version);
				return false;
				$logger->logError('Error while loading sql upgrade file.');

				die('<action result="fail" error="33" />'."\n");
			}
			if (!$sqlContent = file_get_contents($file)."\n")
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = $this->l(sprintf('Error while loading sql upgrade file %s.', $version));
				return false;
				$logger->logError(sprintf('Error while loading sql upgrade file %s.', $version));
				die('<action result="fail" error="33" />'."\n");
			}
			$sqlContent = str_replace(array($filePrefix, $engineType), array(_DB_PREFIX_, $mysqlEngine), $sqlContent);
			$sqlContent = preg_split("/;\s*[\r\n]+/",$sqlContent);
			$sqlContentVersion[$version] = $sqlContent;

		}


		//sql file execution
		global $requests, $warningExist;
		$requests = '';
		$warningExist = false;

		// Configuration::loadConfiguration();
		$request = '';

		foreach ($sqlContentVersion as $upgrade_file => $sqlContent)
			foreach ($sqlContent as $query)
			{
				$query = trim($query);
				if(!empty($query))
				{
					/* If php code have to be executed */
					if (strpos($query, '/* PHP:') !== false)
					{
						/* Parsing php code */
						$pos = strpos($query, '/* PHP:') + strlen('/* PHP:');
						$phpString = substr($query, $pos, strlen($query) - $pos - strlen(' */;'));
						$php = explode('::', $phpString);
						preg_match('/\((.*)\)/', $phpString, $pattern);
						$paramsString = trim($pattern[0], '()');
						preg_match_all('/([^,]+),? ?/', $paramsString, $parameters);
						if (isset($parameters[1]))
							$parameters = $parameters[1];
						else
							$parameters = array();
						if (is_array($parameters))
							foreach ($parameters AS &$parameter)
								$parameter = str_replace('\'', '', $parameter);
						
						// reset phpRes to a null value
						$phpRes = null;
						/* Call a simple function */
						if (strpos($phpString, '::') === false)
						{
							$func_name = str_replace($pattern[0], '', $php[0]);
							if (!file_exists(_PS_INSTALLER_PHP_UPGRADE_DIR_.strtolower($func_name).'.php'))
							{
								$this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] '.$upgrade_file.' PHP - missing file '.$query.'</div>';
							}
							else
							{
								require_once(_PS_INSTALLER_PHP_UPGRADE_DIR_.strtolower($func_name).'.php');
								$phpRes = call_user_func_array($func_name, $parameters);
							}
						}
						/* Or an object method */
						else
						{
							$func_name = array($php[0], str_replace($pattern[0], '', $php[1]));
							$this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] '.$upgrade_file.' PHP - Object Method called '.$php[0].'::'.str_replace($pattern[0], '', $php[1]).'</div>';
						}

						if (isset($phpRes) && (is_array($phpRes) && !empty($phpRes['error'])) || $phpRes === false )
						{
							$this->next = 'error';
							$this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] PHP '.$upgrade_file
								.' '.(empty($phpRes['error'])?$query:' '.$phpRes['error']).' '.(empty($phpRes['msg'])?'':' - '.$phpRes['msg']).'</div>';
						}
						else
								$this->nextQuickInfo[] = '<div class="upgradeDbOk">[OK] PHP'.$upgrade_file.' '.$query.'</div>';
					}
					elseif(!Db::getInstance()->execute($query))
					{
						$this->next = 'error';
						$this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] SQL '.$upgrade_file.' ' . Db::getInstance()->getNumberError().' in '.$query.': '.Db::getInstance()->getMsgError().'</div>';
						$warningExist = true;
					}
					else
						$this->nextQuickInfo[] = '<div class="upgradeDbOk">[OK] SQL '.$upgrade_file.' '.$query.'</div>';
				}
			}
		if ($this->next == 'error')
		{
			$this->nextDesc = $this->l('An error happen during database upgrade');
			return false;
		}

		$this->nextQuickInfo[] = $this->l('Upgrade Db Ok'); // no error !

		# At this point, database upgrade is over.
		# Now we need to add all previous missing settings items, and reset cache and compile directories
		$this->writeNewSettings();

		// Settings updated, compile and cache directories must be emptied
		// @todo : the list of theses directory should be available elsewhere
		$arrayToClean[] = INSTALL_PATH.'/../tools/smarty/cache/';
		$arrayToClean[] = INSTALL_PATH.'/../tools/smarty/compile/';
		$arrayToClean[] = INSTALL_PATH.'/../tools/smarty_v2/cache/';
		$arrayToClean[] = INSTALL_PATH.'/../tools/smarty_v2/compile/';

		foreach ($arrayToClean as $dir)
			if (!file_exists($dir))
			{
				$this->nextQuickInfo[] = sprintf($this->l('[SKIP] directory "%s" doesn\'t exist and cannot be emptied.'), $dir);
				continue;
			}
			else
				foreach (scandir($dir) as $file)
					if ($file[0] != '.' AND $file != 'index.php' AND $file != '.htaccess')
					{
						unlink($dir.$file);
						$this->nextQuickInfo[] = sprintf($this->l('[cleaning cache] %s removed'), $file);
					}

		// delete cache filesystem if activated

		$depth = (int)Db::getInstance()->getValue('SELECT value 
			FROM '._DB_PREFIX_.'configuration 
			WHERE name = "PS_CACHEFS_DIRECTORY_DEPTH"');
		if($depth)
		{
			Tools::deleteDirectory(_PS_CACHEFS_DIRECTORY_, false);
			CacheFs::createCacheFsDirectories((int)$depth);
		}

		// we do not use class Configuration because it's not loaded;
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'configuration`
			SET value="0" WHERE name = "PS_HIDE_OPTIMIZATION_TIS"');
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'configuration`
			SET value="1" WHERE name = "PS_NEED_REBUILD_INDEX"');
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'configuration`
			SET value="'.INSTALL_VERSION.'" WHERE name = "PS_VERSION_DB"');

		if ($warningExist)
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('Warning detected during upgrade.');
			$this->nextDesc = $this->l('Error while inserting content into the database');
			return false;
		}
		else
		{
			$this->next = 'upgradeComplete';
			$this->nextDesc = $this->l('Upgrade completed');
			return true;
		}
	}

	public function writeNewSettings()
	{
		// note : duplicated line
		$mysqlEngine = (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM');

		$oldLevel = error_reporting(E_ALL);
		//refresh conf file
		require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/AddConfToFile.php');
		$confFile = new AddConfToFile(SETTINGS_FILE, 'w');
		if ($confFile->error)
		{
				$this->next = 'error';
				$this->nextDesc = $this->l('Error when opening settings.inc.php file in write mode');
				$this->nextQuickInfo[] = $confFile->error;
				return false;
		}
		$datas = array(
			array('_DB_SERVER_', _DB_SERVER_),
			array('_DB_TYPE_', _DB_TYPE_),
			array('_DB_NAME_', _DB_NAME_),
			array('_DB_USER_', _DB_USER_),
			array('_DB_PASSWD_', _DB_PASSWD_),
			array('_DB_PREFIX_', _DB_PREFIX_),
			array('_MYSQL_ENGINE_', $mysqlEngine),
			array('_PS_CACHING_SYSTEM_', (defined('_PS_CACHING_SYSTEM_') AND _PS_CACHING_SYSTEM_ != 'CacheMemcache') ? _PS_CACHING_SYSTEM_ : 'CacheMemcache'),
			array('_PS_CACHE_ENABLED_', defined('_PS_CACHE_ENABLED_') ? _PS_CACHE_ENABLED_ : '0'),
			array('_MEDIA_SERVER_1_', defined('_MEDIA_SERVER_1_') ? _MEDIA_SERVER_1_ : ''),
			array('_MEDIA_SERVER_2_', defined('_MEDIA_SERVER_2_') ? _MEDIA_SERVER_2_ : ''),
			array('_MEDIA_SERVER_3_', defined('_MEDIA_SERVER_3_') ? _MEDIA_SERVER_3_ : ''),
			array('_COOKIE_KEY_', _COOKIE_KEY_),
			array('_COOKIE_IV_', _COOKIE_IV_),
			array('_PS_CREATION_DATE_', defined("_PS_CREATION_DATE_") ? _PS_CREATION_DATE_ : date('Y-m-d')),
			array('_PS_VERSION_', INSTALL_VERSION)
		);
		if (defined('_RIJNDAEL_KEY_'))
			$datas[] = array('_RIJNDAEL_KEY_', _RIJNDAEL_KEY_);
		if (defined('_RIJNDAEL_IV_'))
			$datas[] = array('_RIJNDAEL_IV_', _RIJNDAEL_IV_);
		if(!defined('_PS_CACHE_ENABLED_'))
			define('_PS_CACHE_ENABLED_', '0');
		if(!defined('_MYSQL_ENGINE_'))
			define('_MYSQL_ENGINE_', 'MyISAM');

		// if 1.4.7 or above
		if (version_compare(INSTALL_VERSION, '1.5.0.0', '<='))
		{
			$datas[] = array('__PS_BASE_URI__', __PS_BASE_URI__);
			$datas[] = array('_THEME_NAME_', _THEME_NAME_);
		}
		else
			$datas[] = array('_PS_DIRECTORY_', __PS_BASE_URI__);

		foreach ($datas AS $data){
			$confFile->writeInFile($data[0], $data[1]);
		}

		if ($confFile->error != false)
		{
			$this->next = 'error';
			$this->nextDesc = $this->l('Error when generating new settings.inc.php file.');
			$this->nextQuickInfo[] = $confFile->error;
			return false;
		}
		else
			$this->nextQuickInfo[] = $this->l('settings file updated');
		error_reporting($oldLevel);
	}
	/**
	 * upgradeThisFile
	 *
	 * @param mixed $file
	 * @return void
	 */
	public function upgradeThisFile($file)
	{

		if ($this->keepTrad && file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->tradCustomList))
			$translations_custom = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->tradCustomList));
		else
			$translations_custom = array();

		if ($this->keepMails && file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->mailCustomList))
			$mails_custom = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->mailCustomList));
		else
			$mails_custom = array();
		
		$excludeList = array_merge($mails_custom, $translations_custom);


		// relative to keep translations and keep default mails 
		if (in_array(ltrim($file, '/'), $excludeList))
		{
			$this->nextQuickInfo[] = sprintf($this->l('%s is preserved'), $file);
			return true;
		}
		// @TODO : later, we could handle customization with some kind of diff functions
		// for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)
		// $file comes from scandir function, no need to lost time and memory with file_exists()
		if ($this->_skipFile('', $file, 'upgrade'))
		{
			$this->nextQuickInfo[] = sprintf($this->l('%s ignored'), $file);
			return true;
		}
		else
		{
			$orig = $this->latestRootDir.$file;
			$dest = $this->destUpgradePath . $file;

			if (is_dir($orig))
			{
				// if $dest is not a directory (that can happen), just remove that file
				if (!is_dir($dest) AND file_exists($dest))
				{
					unlink($dest);
					$this->nextQuickInfo[] = sprintf('[WARNING] file %1$s has been deleted.', $file);
				}

				if (!file_exists($dest))
				{
					if (@mkdir($dest, 0777))
					{
						$this->nextQuickInfo[] = sprintf($this->l('directory %1$s created.'), $file);
						return true;
					}
					else
					{
						$this->next = 'error';
						$this->nextQuickInfo[] = sprintf($this->l('error when creating directory %s'), $dest);
						$this->nextDesc = sprintf($this->l('error when creating directory %s'), $dest);
						return false;
					}
				}
				else // directory already exists
				{
					$this->nextQuickInfo[] = sprintf($this->l('directory %1$s already exists.'), $file);
					return true;
				}
			}
			else
			{
				if (copy($orig, $dest))
				{
					$this->nextQuickInfo[] = sprintf($this->l('copied %1$s.'), $file);
					return true;
				}
				else
				{
					$this->next = 'error';
					$this->nextQuickInfo[] = sprintf($this->l('error for copying %1$s'), $file);
					$this->nextDesc = sprintf($this->l('error for copying %1$s'), $file);
					return false;
				}
			}
		}
	}

	public function ajaxProcessRollback()
	{
		// 1st, need to analyse what was wrong.
		$this->nextParams = $this->currentParams;
		if (!empty($this->restoreName) && empty($this->restoreFilesFilename) && empty($this->restoreDbFilenames))
		{
			$files = scandir($this->autoupgradePath);
			// find backup filenames, and be sure they exists
			foreach($files as $file)
				if (preg_match('#'.preg_quote('auto-backupfiles_'.$this->restoreName).'#', $file))
				{
					$this->restoreFilesFilename = $file;
					break;
				}
			if (!is_file($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->restoreFilesFilename))
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf('[ERROR] file %s is missing : unable to restore files. Operation aborted.', $this->restoreFilesFilename);
				$this->nextDesc = sprintf($this->l('file %s does not exist. Files Restoration cannot be made.'), $this->restoreFilesFilename);
				return false;
			}
			$files = scandir($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->restoreName);
			foreach($files as $file)
				if (preg_match('#auto-backupdb_[0-9]{6}_'.preg_quote($this->restoreName).'#', $file))
					$this->restoreDbFilenames[] = $file;
			
			// order files is important !
			sort($this->restoreDbFilenames);
			if (count($this->restoreDbFilenames) == 0)
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf('[ERROR] no backup db files found : it would be impossible to restore database. Operation aborted.');
				$this->nextDesc = sprintf($this->l('no backup db files found. Database restoration cannot be made.'), count($this->restoreDbFilenames));
				return false;
			}
			
			$this->next = 'restoreFiles';
			$this->nextDesc = $this->l('Restoring files ...');
			// remove tmp files related to restoreFiles
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList))
				unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList);
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList))
				unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList);
		}
		else
			$this->next = 'noRollbackFound';
	}

	public function ajaxProcessNoRollbackFound()
	{
		$this->nextDesc = $this->l('Nothing to restore');
		$this->next = 'rollbackComplete';	
	}

	/**
	 * ajaxProcessRestoreFiles restore the previously saved files, 
	 * and delete files that weren't archived
	 *
	 * @return boolean true if succeed
	 */
	public function ajaxProcessRestoreFiles()
	{
		// loop
		$this->next = 'restoreFiles';
		if (
			!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList)
			|| !file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList))
		{
			// cleanup current PS tree
			$fromArchive = $this->_listArchivedFiles($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->restoreFilesFilename);
			foreach($fromArchive as $k => $v)
				$fromArchive[$k] = '/'.$v;
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList, serialize($fromArchive));
			// get list of files to remove
			$toRemove = $this->_listFilesToRemove();
			$this->nextQuickInfo[] = sprintf($this->l('%s file(s) will be removed before restoring backup files'), count($toRemove));
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList, serialize($toRemove));

			if ($fromArchive === false || $toRemove === false)
			{
				if (!$fromArchive)
					$this->nextQuickInfo[] = '[ERROR] '.sprintf($this->l('backup file %s does not exists'), $this->fromArchiveFileList);
				if (!$toRemove)
					$this->nextQuickInfo[] = '[ERROR] '.sprintf($this->l('file "%s" does not exists'), $this->toRemoveFileList);
				$this->nextDesc = $this->l('Unable to remove upgraded files.');
				$this->next = 'error';
				return false;
			}
		}

		// first restoreFiles step 
		if (!isset($toRemove))
			$toRemove = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList));

		if (count($toRemove) > 0)
		{
			for($i=0;$i<self::$loopRemoveUpgradedFiles ;$i++)
			{
				if (count($toRemove) <= 0)
				{
					$this->stepDone = true;
					$this->status = 'ok';
					$this->next = 'restoreFiles';
					$this->nextDesc = $this->l('Files from upgrade has been removed.');
					$this->nextQuickInfo[] = $this->l('files from upgrade has been removed.');
					file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList, serialize($toRemove));
					return true;
				}
				else
				{
					$filename = array_shift($toRemove);
					$file = rtrim($this->prodRootDir, DIRECTORY_SEPARATOR).$filename;
					if (file_exists($file))
					{
						if (is_file($file) && @unlink($file))
							$this->nextQuickInfo[] = sprintf('%s removed', $filename);
						else
						{
							if (!file_exists($file))
								$this->nextQuickInfo[] = sprintf('[NOTICE] %s does not exists', $filename);
							elseif (is_dir($file))
							{
								Tools::deleteDirectory($file, true);
								$this->nextQuickInfo[] = sprintf('[NOTICE] %s directory deleted', $filename);
							}
							else
							{
								$this->next = 'error';
								$this->nextDesc = sprintf($this->l('error when removing %1$s'), $filename);
								$this->nextQuickInfo[] = sprintf($this->l('%s not removed'), $filename);
								return false;
							}
						}
					}
				}
			}
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList, serialize($toRemove));
			$this->nextDesc = sprintf($this->l('%s left to remove'), count($toRemove));
			$this->next = 'restoreFiles';
			return true;
		}


		// very second restoreFiles step : extract backup 
		// if (!isset($fromArchive))
		//	$fromArchive = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList));

		$filepath = $this->autoupgradePath.DIRECTORY_SEPARATOR.$this->restoreFilesFilename;
		$destExtract = $this->prodRootDir;
		if ($res = $this->ZipExtract($filepath, $destExtract))
		{
			$this->next = 'restoreDb';
			$this->nextDesc = $this->l('Files restored. Now restoring database ...');
			// get new file list 
			$this->nextQuickInfo[] = $this->l('Files restored.');
			// once it's restored, do not delete the archive file. This has to be done manually
			// and we do not empty the var, to avoid infinite loop.
			return true;
		}
		else
		{
			$this->next = "error";
			$this->nextDesc = sprintf($this->l('unable to extract %1$s into %2$s .'), $filepath, $destExtract);
			return false;
		}
		return true;
	}

	/**
	* try to restore db backup file
	* @return type : hey , what you expect ? well mysql errors array .....
	* @TODO : maybe this could be in the Backup class
	*/
	public function ajaxProcessRestoreDb()
	{
		$this->nextParams['dbStep'] = $this->currentParams['dbStep'];
		$start_time = time();
		$db = $this->db();
		// deal with the next files stored in restoreDbFilenames
		if (is_array($this->restoreDbFilenames) && count($this->restoreDbFilenames) > 0)
		{
			$currentDbFilename = array_shift($this->restoreDbFilenames);
			if (!preg_match('#auto-backupdb_([0-9]{6})_#', $currentDbFilename, $match))
			{
				$this->next = 'error';
				$this->nextDesc = $this->l(sprintf('%s : File format does not match', $currentDbFilename));
				return false;
			}

			$this->nextParams['dbStep'] = $match[1];
			$backupdb_path = $this->autoupgradePath.DIRECTORY_SEPARATOR.$this->restoreName;

			$dot_pos = strrpos($currentDbFilename, '.');
			$fileext = substr($currentDbFilename, $dot_pos+1);
			$requests = array();
			$errors = array();
			$content = '';
			switch ($fileext)
			{
				case 'bz':
				case 'bz2':
					$this->nextQuickInfo[] = 'opening backup db in bz mode';
					if ($fp = bzopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r'))
					{
						while(!feof($fp))
							$content .= bzread($fp, 4096);
					}
					else
						die("error when trying to open in bzmode");
					break;
				case 'gz':
					$this->nextQuickInfo[] = 'opening backup db in gz mode';
					if ($fp = gzopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r'))
					{
						while(!feof($fp))
							$content .= gzread($fp, 4096);
						gzclose($fp);
					}
					break;
				// default means sql ?
				default :
					$this->nextQuickInfo[] = 'opening backup db in txt mode';
					if ($fp = fopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r'))
					{
						while(!feof($fp))
							$content .= fread($fp, 4096);
						fclose($fp);
					}
			}
			$currentDbFilename = '';

			if ($content == '')
			{
				$this->nextQuickInfo[] = $this->l('database backup is empty');
				$this->next = 'rollback';
				return false;
			}

			// preg_match_all is better than preg_split (what is used in do Upgrade.php)
			// This way we avoid extra blank lines
			// option s (PCRE_DOTALL) added
			$listQuery = preg_split('/;[\n\r]+/Usm', $content);
			unset($content);
			// @TODO : drop all old tables (created in upgrade)
			$all_tables = $db->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'%"');
			$ignore_stats_table = array(_DB_PREFIX_.'connections', 
				_DB_PREFIX_.'connections_page', 
				_DB_PREFIX_.'connections_source', 
				_DB_PREFIX_.'guest', 
				_DB_PREFIX_.'statssearch');
			$drops = array();
			foreach ($all_tables as $k => $v)
			{
				$table = array_shift($v);
				if (!in_array($table, $ignore_stats_table))
				{
					$drops['drop table '.$k] = 'DROP TABLE IF EXISTS `'.bqSql($table).'`';
					$drops['drop view '.$k] = 'DROP VIEW IF EXISTS `'.bqSql($table).'`';
				}
			}
			unset($all_tables);
			$listQuery = array_merge($drops, $listQuery);
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList, serialize($listQuery));
		}
		
		// handle current backup file
		if (!isset($listQuery))
			$listQuery = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList));

		$time_elapsed = time() - $start_time;
		if (sizeof($listQuery) > 0)
		{
			do
			{
				if (sizeof($listQuery)<=0)
				{
					unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList);
					$currentDbFilename = '';
					if (count($this->restoreDbFilenames) > 0)
					{
						$this->stepDone = true;
						$this->status = 'ok';
						$this->next = 'restoreDb';
						$this->nextDesc = sprintf($this->l('Database restoration step %s done. %s left) ...'), $this->nextParams['dbStep'], count($this->restoreDbFilenames));
						$this->nextQuickInfo[] = sprintf('Database restoration step %s done. %s left) ...', $this->nextParams['dbStep'], count($this->restoreDbFilenames));
						return true;
					}
					else
					{
						$this->stepDone = true;
						$this->status = 'ok';
						$this->next = 'rollbackComplete';
						$this->nextDesc = $this->l('Database restoration done. now restoring files ...');
						$this->nextQuickInfo[] = $this->l('database backup has been restored. now restoring files ...');
						return true;
					}
				}
				// filesForBackup already contains all the correct files
				$query = array_shift($listQuery);
				if (!empty($query))
				{
					if (!$db->execute($query))
					{
						$listQuery = array_unshift($listQuery, $query);
						$this->nextQuickInfo[] = '[SQL ERROR] '.$query.' - '.$db->getMsgError();
						$this->next = 'error';
						$this->nextDesc = $this->l('error during database restoration');
						return false;
					}
					else
						$this->nextQuickInfo[] = '[OK] '.$query;
				}

				$time_elapsed = time() - $start_time;
			} while($time_elapsed < self::$loopRestoreQueryTime);
			unset($query);
			$queries_left = count($listQuery);

			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList, serialize($listQuery));
			unset($listQuery);
			$this->next = 'restoreDb';
			$this->nextDesc = sprintf($this->l('%s queries left for %s...'), $queries_left, $this->nextParams['dbStep']);
		}

		return true;
	}
	
	protected function db()
	{
		require_once('db/Db.php');
		eval('abstract class Db extends DbCore{}');
		require_once('db/MySQL.php');
		eval('class MySQL extends MySQLCore{}');
		require_once('db/DbMySQLi.php');
		eval('class DbMySQLi extends DbMySQLiCore{}');
		require_once('db/DbPDO.php');
		eval('class DbPDO extends DbPDOCore{}');
		require_once('db/DbQuery.php');
		eval('class DbQuery extends DbQueryCore{}');

		require_once('alias.php');
		return Db::getInstance();
	}

	public function ajaxProcessBackupDb()
	{
		$this->next = 'backupDb';
		$this->nextParams = $this->currentParams;
		$start_time = time();
		$this->db();
	
		$psBackupAll = false;
		$psBackupDropTable = true;
		if (!$psBackupAll)
		{
			$ignore_stats_table = array(_DB_PREFIX_.'connections', 
				_DB_PREFIX_.'connections_page', 
				_DB_PREFIX_.'connections_source', 
				_DB_PREFIX_.'guest', 
				_DB_PREFIX_.'statssearch');
		}
		else
			$ignore_stats_table = array();

		// INIT LOOP
		if (!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList))
		{
			if (!is_dir($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupName))
				mkdir($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupName, 0755);
			$this->nextParams['dbStep'] = 0;
			$tablesToBackup = Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'%"');
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList, serialize($tablesToBackup));
		}

		if (!isset($tablesToBackup))
			$tablesToBackup = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList));
		$found = 0;
		$views = '';
		
		// MAIN BACKUP LOOP //
		$written = 0;
		do
		{
			if (!empty($this->nextParams['backup_table']))
			{
				// only insert (schema already done)
				$table = $this->nextParams['backup_table'];
				$lines = $this->nextParams['backup_lines'];
			}
			else
			{
				if (count($tablesToBackup) == 0)
					break;
				$table = current(array_shift($tablesToBackup));
				$this->nextParams['backup_loop_limit'] = 0;
			}

			if ($written == 0 || $written > 4194304)
			{
				// new file, new step
				$written = 0;
				if (isset($fp))
					fclose($fp);
				$backupfile = $this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupName.DIRECTORY_SEPARATOR.$this->backupDbFilename;
				$backupfile = preg_replace("#_XXXXXX_#", '_'.str_pad($this->nextParams['dbStep'], 6, '0', STR_PAD_LEFT).'_', $backupfile);

				// start init file 
				// Figure out what compression is available and open the file
				if (function_exists('bzopen'))
				{
					$backupfile .= '.bz2';
					$fp = bzopen($backupfile, 'w');
				}
				else if (function_exists('gzopen'))
				{
					$backupfile .= '.gz';
					$fp = @gzopen($backupfile, 'w');
				}
				else
					$fp = @fopen($backupfile, 'w');
	
				if ($fp === false)
				{
					$this->nextQuickInfo[] = sprintf($this->l('Unable to create backup db file %s'), addslashes($backupfile));
					$this->next = 'error';
					$this->nextDesc = $this->l('Error during database backup.');
					return false;
				}
	
				$written += fwrite($fp, '/* Backup ' . $this->nextParams['dbStep'] . ' for ' . Tools::getHttpHost(false, false) . __PS_BASE_URI__ . "\n *  at " . date('r') . "\n */\n");
				$written += fwrite($fp, "\n".'SET NAMES \'utf8\';'."\n\n");
				// end init file 
			}
			

			// Skip tables which do not start with _DB_PREFIX_
			if (strlen($table) < strlen(_DB_PREFIX_) || strncmp($table, _DB_PREFIX_, strlen(_DB_PREFIX_)) != 0)
				continue;
			
			// start schema : drop & create table only
			if (empty($this->currentParams['backup_table']))
			{
				// Export the table schema
				$schema = Db::getInstance()->executeS('SHOW CREATE TABLE `' . $table . '`');

				if (count($schema) != 1 ||
					!((isset($schema[0]['Table']) && isset($schema[0]['Create Table']))
					|| (isset($schema[0]['View']) && isset($schema[0]['Create View']))))
				{
					fclose($fp);
					unlink($backupfile);
					$this->nextQuickInfo[] = sprintf($this->l('An error occurred while backing up. Unable to obtain the schema of %s'), $table);
					$this->next = 'error';
					$this->nextDesc = $this->l('Error during database backup.');
					return false;
				}




				// case view
				if (isset($schema[0]['View']))
				{
					$views .= '/* Scheme for view' . $schema[0]['View'] . " */\n";
					if ($psBackupDropTable)
						$views .= 'DROP TABLE IF EXISTS `'.$schema[0]['View'].'`;'."\n";
					$views .= preg_replace('#DEFINER[^ ]* #', ' ', $schema[0]['Create View']).";\n\n";
					$written += fwrite($fp, "\n".$views);
				}
				// case table
				elseif (isset($schema[0]['Table']))
				{
					// Case common table 
					$written += fwrite($fp, '/* Scheme for table ' . $schema[0]['Table'] . " */\n");
					if ($psBackupDropTable && !in_array($schema[0]['Table'], $ignore_stats_table))
					{
						$written += fwrite($fp, 'DROP TABLE IF EXISTS `'.$schema[0]['Table'].'`;'."\n");
						// CREATE TABLE
						$written += fwrite($fp, $schema[0]['Create Table'] . ";\n\n");
					}
					// schema created, now we need to create the missing vars
					$this->nextParams['backup_table'] = $table;
					$lines = $this->nextParams['backup_lines'] = explode("\n", $schema[0]['Create Table']);
				}
			}
			// end of schema

			// POPULATE TABLE
			if (!in_array($table, $ignore_stats_table))
			{
				do
				{
					$backup_loop_limit = $this->nextParams['backup_loop_limit'];
					$data = Db::getInstance()->executeS('SELECT * FROM `'.$table.'` LIMIT '.(int)$backup_loop_limit.',200', false);
					$this->nextParams['backup_loop_limit'] += 200;
					$sizeof = DB::getInstance()->numRows();
					if ($data && ($sizeof > 0))
					{
						// Export the table data
						$written += fwrite($fp, 'INSERT INTO `'.$table."` VALUES\n");
						$i = 1;
						while ($row = DB::getInstance()->nextRow($data))
						{
							// this starts a row
							$s = '(';
							foreach ($row AS $field => $value)
							{
								$tmp = "'" . Db::getInstance()->escape($value) . "',";
								if ($tmp != "'',")
									$s .= $tmp;
								else
								{
									foreach($lines AS $line)
										if (strpos($line, '`'.$field.'`') !== false)
										{	
											if (preg_match('/(.*NOT NULL.*)/Ui', $line))
												$s .= "'',";
											else
												$s .= 'NULL,';
											break;
										}
								}
							}
							$s = rtrim($s, ',');

							if ($i < $sizeof)
								$s .= "),\n";
							else
								$s .= ");\n";

							$written += fwrite($fp, $s);
							++$i;
						}
						$time_elapsed = time() - $start_time;
					}
					else
						break;
				}
				while(($time_elapsed < self::$loopBackupDbTime) || ($written < 4194304));
			}
			$found++;
			unset($this->nextParams['backup_table']);
			$time_elapsed = time() - $start_time;
			$this->nextQuickInfo[] = sprintf($this->l('%1$s table has been saved.'), $table);
		}
		while(($time_elapsed < self::$loopBackupDbTime) || ($written < 4194304));
		if (isset($fp))
		{
			$this->nextParams['dbStep']++;
			fclose($fp);
			unset($fp);
		}
		file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList, serialize($tablesToBackup));
		if (count($tablesToBackup) > 0){
			$this->nextQuickInfo[] = sprintf($this->l('%1$s tables has been saved.'), $found);
			$this->next = 'backupDb';
			$this->stepDone = false;
			$this->nextDesc = sprintf($this->l('database backup : %s table(s) left ...'), count($tablesToBackup));
			$this->nextQuickInfo[] = sprintf('database backup : %s table(s) left ...', count($tablesToBackup));
			return true;
		}
		if ($found == 0)
		{
			if (isset($backupfile))
				unlink($backupfile);
			$this->nextQuickInfo[] = $this->l('No valid tables were found to backup. Backup cancelled.');
			$this->next = 'error';
			$this->nextDesc = $this->l('Error during database backup.');
			return false;
		}
		else
		{
			$this->nextQuickInfo[] = sprintf($this->l('%1$s tables has been saved.'), $found);
			$this->next = 'upgradeFiles';
			$this->stepDone = true;
			$this->nextDesc = sprintf($this->l('database backup done in %s. Now upgrading files ...'), $this->backupName);
			return true;
		}
		// for backup db, use autoupgrade/backup directory
		// @TODO : autoupgrade must not be static
		// maybe for big tables we should save them in more than one file ?
		// if an error occur, we assume the file is not saved
	}

	public function ajaxProcessBackupFiles()
	{
		$this->nextParams = $this->currentParams;
		$this->stepDone = false;
		if (empty($this->backupFilesFilename))
		{
			$this->next = 'error';
			$this->nextDesc = $this->l('error during backupFiles');
			$this->nextQuickInfo[] = '[ERROR] backupFiles filename has not been set';
			return false;
		}

		if (empty($this->nextParams['filesForBackup']))
		{
			// @todo : only add files and dir listed in "originalPrestashopVersion" list
			$filesToBackup = $this->_listFilesInDir($this->prodRootDir);
			file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupFileList, serialize($filesToBackup));

			$this->nextQuickInfo[] = sprintf($this->l('%s Files to backup.'), sizeof($this->toBackupFileList));
			$this->nextParams['filesForBackup'] = $this->toBackupFileList;

			// delete old backup, create new
			if (!empty($this->backupFilesFilename) && file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupFilesFilename))
				unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupFilesFilename);

			$this->nextQuickInfo[]	= sprintf($this->l('backup files initialized in %s'), $this->backupFilesFilename);
		}
		$filesToBackup = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupFileList));

		/////////////////////
		$this->next = 'backupFiles';
		// @TODO : display percent instead of this
		$this->nextDesc = sprintf($this->l('Backup files in progress. %s files left'), sizeof($filesToBackup));
		if (is_array($filesToBackup))
		{
			// @TODO later
			// 1) calculate crc32 or md5 of next file
			// 2) use the provided xml with crc32 calculated from previous versions ?
			// or simply use the latest dir ?
			//$current = crc32(file_get_contents($file));
			//$file = $this->nextParams['filesForBackup'][0];
			//$latestFile = str_replace(_PS_ROOT_DIR_,$this->latestRootDir,$file);

			//	if (file_exists($latestFile))
			//		$latest = crc32($latestFile);
			//	else
			//		$latest = '';

			if (!self::$force_pclZip && class_exists('ZipArchive', false))
			{
				$zip_archive = true;
				$zip = new ZipArchive();
				$zip->open($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupFilesFilename, ZIPARCHIVE::CREATE);
			}
			else
			{
				$zip_archive = false;
				// pclzip can be already loaded (server configuration)
				if (!class_exists('PclZip',false))
					require_once(dirname(__FILE__).'/pclzip.lib.php');
				$zip = new PclZip($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupFilesFilename);
			}
			if ($zip)
			{
				$this->next = 'backupFiles';
				// @TODO all in one time will be probably too long
				// 1000 ok during test, but 10 by 10 to be sure
				$this->stepDone = false;
				// @TODO min(self::$loopBackupFiles, sizeof())
				for ($i=0;$i<self::$loopBackupFiles;$i++)
				{
					if (sizeof($filesToBackup)<=0)
					{
						$this->stepDone = true;
						$this->status = 'ok';
						$this->next = 'backupDb';
						$this->nextDesc = $this->l('All files saved. Now backup Database');
						$this->nextQuickInfo[] = $this->l('all files have been added to archive.');
						break;
					}
					// filesForBackup already contains all the correct files
					$file = array_shift($filesToBackup);

					$archiveFilename = ltrim(str_replace($this->prodRootDir, '', $file), DIRECTORY_SEPARATOR);
					if ($zip_archive)
					{
						$added_to_zip = $zip->addFile($file, $archiveFilename);
						if ($added_to_zip)
							$this->nextQuickInfo[] = sprintf($this->l('%1$s added to archive. %2$s left.'), $archiveFilename, sizeof($filesToBackup));
						else
						{
							// if an error occur, it's more safe to delete the corrupted backup
							$zip->close();
							if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupFilesFilename))
								unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->backupFilesFilename);
							$this->next = 'error';
							$this->nextDesc = sprintf($this->l('error when trying to add %1$s to archive %2$s.'),$archiveFilename, $backupFilePath);
							break;
						}
					}
					else
					{
						$files_to_add[] = $file;
						$this->nextQuickInfo[] = sprintf($this->l('%1$s added to archive. %2$s left.'), $archiveFilename, sizeof($filesToBackup));
					}
				}

				if ($zip_archive)
					$zip->close();
				else
				{
					$added_to_zip = $zip->add($files_to_add, PCLZIP_OPT_REMOVE_PATH, $this->prodRootDir);
					$zip->privCloseFd();
					if (!$added_to_zip)
					{
						$this->nextQuickInfo[] = '[ERROR] error on backup using pclzip : '.$zip->errorInfo(true);
						$this->next = 'error';
					}
				}

				file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupFileList,serialize($filesToBackup));
				return true;
			}
			else{
				$this->next = 'error';
				$this->nextDesc = $this->l('unable to open archive');
				return false;
			}
		}
		else
		{
			$this->stepDone = true;
			$this->next = 'backupDb';
			$this->nextDesc = 'All files saved. Now backup Database';
			return true;
		}
		// 4) save for display.
	}


	private function _removeOneSample($removeList)
	{
		if (is_array($removeList) AND sizeof($removeList)>0)
		{
			if (file_exists($removeList[0]) AND unlink($removeList[0]))
			{
				$item = str_replace($this->prodRootDir, '', array_shift($removeList));
				$this->next = 'removeSamples';
				$this->nextParams['removeList'] = $removeList;
				$this->nextQuickInfo[] = sprintf($this->l('%1$s removed. %2$s items left'), $item, sizeof($removeList));
			}
			else
			{
				$this->next = 'error';
				$this->nextParams['removeList'] = $removeList;
				$this->nextQuickInfo[] = sprintf($this->l('error when removing %1$s, %2$s items left'), $removeList[0], sizeof($removeList));
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove all sample files.
	 * @TODO : this should be handled with the md5 xml files with a "is_sample" param
	 * 
	 * @return boolean true if succeed
	 */
	public function ajaxProcessRemoveSamples()
	{
		$this->stepDone = false;
		// all images from img dir exept admin ?
		// all images like logo, favicon, ?.
		// all custom image from modules ?
		// all custom image from theme ?
		if (!isset($this->currentParams['removeList']))
		{
			$this->_listSampleFiles($this->autoupgradePath.'/latest/prestashop/img', '.jpg');
			$this->_listSampleFiles($this->autoupgradePath.'/latest/prestashop/img', '.gif');
			$this->_listSampleFiles($this->autoupgradePath.'/latest/prestashop/img', 'favicon.ico');
			$this->_listSampleFiles($this->autoupgradePath.'/latest/prestashop/modules/editorial', 'homepage_logo.jpg');
			// remove all override present in the archive
			$this->_listSampleFiles($this->autoupgradePath.'/latest/prestashop/override', '.php');

			// @TODO handle this bad thing
			$this->nextQuickInfo[] = sprintf($this->l('Starting to remove %1$s sample files'), sizeof($this->sampleFileList));

			$this->nextParams['removeList'] = $this->sampleFileList;
		}


		// @TODO : removing @, adding if file_exists
		$resRemove = true;
		for($i=0;$i<self::$loopRemoveSamples;$i++)
		{
			if (sizeof($this->nextParams['removeList']) <= 0 )
			{
				$this->stepDone = true;
				$this->next = 'backupFiles';
				$this->nextDesc = $this->l('All sample files removed. Now backup files.');
				// break the loop, all sample already removed
				return true;
			}
			$resRemove &= $this->_removeOneSample($this->nextParams['removeList']);
			if (!$resRemove)
				break;
		}

		return $resRemove;
	}

	public function ajaxProcessSvnCheckout()
	{
		$this->nextParams = $this->currentParams;
		if ($this->useSvn){
			$dest = $this->autoupgradePath . DIRECTORY_SEPARATOR . $this->svnDir;

			$svnStatus = svn_status($dest);
			if (is_array($svnStatus))
			{
				if (sizeof($svnStatus) == 0)
				{
					$this->next = 'svnExport';
					$this->nextDesc = sprintf($this->l('working copy already %s up-to-date. now exporting it into latest dir'),$dest);
				}
				else
				{
					// we assume no modification has been done
					// @TODO a svn revert ?
					if ($svnUpdate = svn_update($dest))
					{
						$this->next = 'svnExport';
						$this->nextDesc = sprintf($this->l('SVN Update done for working copy %s . now exporting it into latest...'),$dest);
					}
				}
			}
			else
			{
					// no valid status found
					// @TODO : is 0777 good idea ?
					if (!file_exists($dest))
						if (!@mkdir($dest,0777))
						{
							$this->next = 'error';
							$this->nextDesc = sprintf($this->l('unable to create directory %s'),$dest);
							return false;
						}

					if (svn_checkout($this->svn_link, $dest))
					{
						$this->next = 'svnExport';
						$this->nextDesc = sprintf($this->l('SVN Checkout done from %s . now exporting it into latest...'),$this->svn_link);
						return true;
					}
					else
					{
						$this->next = 'error';
						$this->nextDesc = $this->l('SVN Checkout error...');
					}
				}
		}
		else
		{
			$this->next = 'error';
			$this->nextDesc = $this->l('not allowed to use svn');
		}
	}

	public function ajaxProcessDownload()
	{
		if (@ini_get('allow_url_fopen'))
		{
			if (!is_object($this->upgrader))
				$this->upgrader = new Upgrader();

			$res = $this->upgrader->downloadLast($this->autoupgradePath,$this->destDownloadFilename);
			if ($res){
			 	if (md5_file(realpath($this->autoupgradePath).DIRECTORY_SEPARATOR.$this->destDownloadFilename) == $this->upgrader->md5 )
				{
					$this->next = 'unzip';
					$this->nextDesc = $this->l('Download complete. Now extracting');
				}
				else
				{
					$this->next = 'error';
					$this->nextDesc = $this->l('Download complete but md5sum does not match. Operation aborted.');
				}
			}
			else
			{
				$this->next = 'error';
				$this->nextDesc = $this->l('Error during download');
			}
		}
		else
		{
			// @TODO : ftp mode
			$this->next = 'error';
			$this->nextDesc = sprintf($this->l('you need allow_url_fopen for automatic download. You can also manually upload it in %s'),$this->autoupgradePath.$this->destDownloadFilename);
		}
	}
	
	public function buildAjaxResult()
	{
		$return['error'] = $this->error;
		$return['stepDone'] = $this->stepDone;
		$return['next'] = $this->next;
		$return['status'] = $this->next == 'error' ? 'error' : 'ok';
		$return['nextDesc'] = $this->nextDesc;

		$return['nextParams']['dbStep'] = 0;
		foreach($this->ajaxParams as $v)
			if(property_exists($this,$v))
				$this->nextParams[$v] = $this->$v;
			else
				$this->nextQuickInfo[] = sprintf('[WARNING] property %s is missing', $v);

		$return['nextParams'] = $this->nextParams;
		if (!isset($return['nextParams']['dbStep']))
			$return['nextParams']['dbStep'] = 0;

		$return['nextParams']['typeResult'] = $this->nextResponseType;

		$return['nextQuickInfo'] = $this->nextQuickInfo;
		return Tools14::jsonEncode($return);
	}

	/**
	 * displayConf
	 *
	 * @return void
	 */
	public function displayConf()
	{

		if (version_compare(_PS_VERSION_,'1.4.5.0','<') AND false)
			$this->_errors[] = $this->l('This class depends of several files modified in 1.4.5.0 version and should not be used in an older version');
		parent::displayConf();
	}

	public function ajaxPreProcess()
	{
		/* PrestaShop demo mode */
		if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_)
			return;
		/* PrestaShop demo mode*/
		
		if (!empty($_POST['responseType']) AND $_POST['responseType'] == 'json')
			header('Content-Type: application/json');

		if(!empty($_POST['action']))
		{
			$action = $_POST['action'];
			if (isset(self::$skipAction[$action]))
			{
				$this->next = self::$skipAction[$action];
				$this->nextDesc = sprintf($this->l('action %s skipped'),$action);
				$this->nextQuickInfo[] = sprintf($this->l('action %s skipped'),$action);
				unset($_POST['action']);
			}
			else if (!method_exists(get_class($this), 'ajaxProcess'.$action))
			{
				$this->nextDesc = sprintf($this->l('action "%1$s" not found'), $action);
				$this->next = 'error';
				$this->error = '1';
			}
		}

		if (!method_exists('Tools', 'apacheModExists') || Tools::apacheModExists('evasive'))
			sleep(1);
	}

	private function _getJsErrorMsgs()
	{
		$INSTALL_VERSION = $this->upgrader->version_num;
		$ret = '
var txtError = new Array();
txtError[0] = "'.$this->l('Required field').'";
txtError[1] = "'.$this->l('Too long!').'";
txtError[2] = "'.$this->l('Fields are different!').'";
txtError[3] = "'.$this->l('This email adress is wrong!').'";
txtError[4] = "'.$this->l('Impossible to send the email!').'";
txtError[5] = "'.$this->l('Cannot create settings file, if /config/settings.inc.php exists, please give the public write permissions to this file, else please create a file named settings.inc.php in config directory.').'";
txtError[6] = "'.$this->l('Cannot write settings file, please create a file named settings.inc.php in config directory.').'";
txtError[7] = "'.$this->l('Impossible to upload the file!').'";
txtError[8] = "'.$this->l('Data integrity is not valided. Hack attempt?').'";
txtError[9] = "'.$this->l('Impossible to read the content of a MySQL content file.').'";
txtError[10] = "'.$this->l('Impossible the access the a MySQL content file.').'";
txtError[11] = "'.$this->l('Error while inserting data in the database:').'";
txtError[12] = "'.$this->l('The password is incorrect (alphanumeric string at least 8 characters).').'";
txtError[14] = "'.$this->l('A Prestashop database already exists, please drop it or change the prefix.').'";
txtError[15] = "'.$this->l('This is not a valid file name.').'";
txtError[16] = "'.$this->l('This is not a valid image file.').'";
txtError[17] = "'.$this->l('Error while creating the /config/settings.inc.php file.').'";
txtError[18] = "'.$this->l('Error:').'";
txtError[19] = "'.$this->l('This PrestaShop database already exists. Please revalidate your authentication informations to the database.').'";
txtError[22] = "'.$this->l('An error occurred while resizing the picture.').'";
txtError[23] = "'.$this->l('Database connection is available!').'";
txtError[24] = "'.$this->l('Database Server is available but database is not found').'";
txtError[25] = "'.$this->l('Database Server is not found. Please verify the login, password and server fields.').'";
txtError[26] = "'.$this->l('An error occurred while sending email, please verify your parameters.').'";
txtError[37] = "'.$this->l('Impossible to write the image /img/logo.jpg. If this image already exists, please delete it.').'";
txtError[38] = "'.$this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini').'";
txtError[39] = "'.$this->l('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form').'";
txtError[40] = "'.$this->l('The uploaded file was only partially uploaded').'";
txtError[41] = "'.$this->l('No file was uploaded.').'";
txtError[42] = "'.$this->l('Missing a temporary folder').'";
txtError[43] = "'.$this->l('Failed to write file to disk').'";
txtError[44] = "'.$this->l('File upload stopped by extension').'";
txtError[45] = "'.$this->l('Cannot convert your database\'s data to utf-8.').'";
txtError[46] = "'.$this->l('Invalid shop name').'";
txtError[47] = "'.$this->l('Your firstname contains some invalid characters').'";
txtError[48] = "'.$this->l('Your lastname contains some invalid characters').'";
txtError[49] = "'.$this->l('Your database server does not support the utf-8 charset.').'";
txtError[50] = "'.$this->l('Your MySQL server doesn\'t support this engine, please use another one like MyISAM').'";
txtError[51] = "'.$this->l('The file /img/logo.jpg is not writable, please CHMOD 755 this file or CHMOD 777').'";
txtError[52] = "'.$this->l('Invalid catalog mode').'";
txtError[999] = "'.$this->l('No error code available').'";
//upgrader
txtError[27] = "'.$this->l('This installer is too old.').'";
txtError[28] = "'.sprintf($this->l('You already have the %s version.'),$INSTALL_VERSION).'";
txtError[29] = "'.$this->l('There is no older version. Did you delete or rename the configsettings.inc.php file?').'";
txtError[30] = "'.$this->l('The config/settings.inc.php file was not found. Did you delete or rename this file?').'";
txtError[31] = "'.$this->l('Cannot find the sql upgrade files. Please verify that the /install/sql/upgrade folder is not empty)').'";
txtError[32] = "'.$this->l('No upgrade is possible.').'";
txtError[33] = "'.$this->l('Error while loading sql upgrade file.').'";
txtError[34] = "'.$this->l('Error while inserting content into the database').'";
txtError[35] = "'.$this->l('Unfortunately,').'";
txtError[36] = "'.$this->l('SQL errors have occurred.').'";
txtError[37] = "'.$this->l('The config/defines.inc.php file was not found. Where did you move it?').'";';
		return $ret;
	}

	public function displayAjax(){
		echo $this->buildAjaxResult();
	}

	protected function getBackupFilesAvailable()
	{
		$array = array();

		$files = scandir($this->autoupgradePath);

		foreach($files as $file)
			if ($file[0] != '.')
			{
				if (substr($file, 0, 16) == 'auto-backupfiles')
					$array[] = preg_replace('#^auto-backupfiles_(.*-[0-9a-f]{1,8})\..*$#', '$1', $file);
			}
		
		return $array;
	}

	protected function getBackupDbAvailable()
	{
		$array = array();

		$files = scandir($this->autoupgradePath);

		foreach($files as $file)
			if ($file[0] == 'V' && is_dir($this->autoupgradePath.DIRECTORY_SEPARATOR.$file))
			{
					$array[] = $file;
			}
		return $array;
	}

	protected function _displayRollbackForm()
	{
		$content = '';
		$content .= '<fieldset><legend>'.$this->l('Rollback').'</legend>
		<div id="rollbackForm">';
		$content .= '<p>'
		.$this->l('After upgrading your shop, you can rollback to the previously database and files. Use this function if your theme or an essential module is not working correctly.')
		.'</p><br/>';

		if (empty($this->backupFilesFilename) && empty($this->backupDbFilename))
			$content .= $this->l('No rollback available');
		else if (!empty($this->backupFilesFilename) || !empty($this->backupDbFilename))
		{
			$content .= '<div id="rollbackContainer">
				<a disabled="disabled" class="upgradestep button" href="" id="rollback">'.$this->l('rollback').'</a>
			</div><br/>';
		}
		
		$backup_files_list = $this->getBackupFilesAvailable();
		$backup_db_list = $this->getBackupDbAvailable();
		$backup_available = array_intersect($backup_db_list, $backup_files_list);

		$content .= '<div id="restoreBackupContainer" '.(sizeof($backup_available)==0?'style="display:none"':'').' >'
			.$this->l('backup to use :').'<select name="restoreName">
			<option value="0">'.$this->l('Select').'</option>';
		foreach($backup_available as $file)
			$content .= '<option>'.$file.'</option>';
		$content .=	'</select>';
		$content .'</div>
		<div class="clear">&nbsp</div>';


		$content .= '</div></fieldset>';
		echo $content;
	}

	/** this returns fieldset containing the configuration points you need to use autoupgrade
	 * @return string 
	 */
	private function getCurrentConfiguration()
	{
		$current_config = $this->getcheckCurrentConfig();

		$content = '<fieldset class="width autoupgrade " >';
		$content .= '<legend><a href="#" id="currentConfigurationToggle">'.$this->l('Your current configuration').'</a></legend>';
		$content .= '<div id="currentConfiguration">
		<p>'.$this->l('All the following points must be ok in order to allow the upgrade.').'</p>
		<b>'.$this->l('Root directory').' : </b>'.$this->prodRootDir.'<br/><br/>';
		

		// module version : checkAutoupgradeLastVersion
		if ($current_config['module_version_ok'])
			$srcModuleVersion = '../img/admin/enabled.gif';
		else
			$srcModuleVersion = '../img/admin/disabled.gif';
		$content .= '<b>'.$this->l('Module version').' : </b>'
			.'<img src="'.$srcModuleVersion.'" /> ';
			if($this->lastAutoupgradeVersion)
				$content .= sprintf($this->l('Your version is up-to-date (%s)'), $this->getModuleVersion(), $this->upgrader->autoupgrade_last_version).'<br/><br/>';
			else
			{
				$token_modules = Tools14::getAdminTokenLite('AdminModules');
				$content .= sprintf($this->l('Module version is outdated ( %1$s ). Please install the last version (%2$s)'), $this->getModuleVersion(), $this->upgrader->autoupgrade_last_version);
				$content .= '<br/><br/><a class="button" href="index.php?tab=AdminModules&amp;'.$token_modules.'&amp;url='.$this->upgrader->autoupgrade_module_link.'">'
				.$this->l('Install the latest by clicking "Add from my computer"').'</a><br/><br/>' ;
			}

		// root : getRootWritable()
		if ($current_config['root_writable'])
			$srcRootWritable = '../img/admin/enabled.gif';
		else
			$srcRootWritable = '../img/admin/disabled.gif';
		$content .= '<b>'.$this->l('Root directory status').' : </b>'
			.'<img src="'.$srcRootWritable.'" /> '
			.($current_config['root_writable']?$this->l('fully writable'):$this->l('not writable recursively')).'<br/><br/>';
		

		
		// upgrade available : 
		// - upgrader->autoupgrade
		// - upgrader->need_upgrade
		// - checkAutoupgradeLastVersion()
		$content .= '<b>'.$this->l('Upgrade available').' : </b>';
		if ($current_config['fopen'])
		{
			if ($current_config['need_upgrade'])
			{
				if ($current_config['autoupgrade_allowed'])
					$srcAutoupgrade = '../img/admin/enabled.gif';
				else
					$srcAutoupgrade = '../img/admin/disabled.gif';
	
				$content .= '<img src="'.$srcAutoupgrade.'" /> '
				.($this->upgrader->autoupgrade
					?$this->l('This release allows autoupgrade.')
					:$this->l('This release does not allow autoupgrade')).' <br/><br/>';
			}
			else
			{
				$content .= '<img src="../img/admin/disabled.gif" />'
				.$this->l('You already have the last version.').'<br/><br/>';
			}
		}
		else
			$content .= '<img src="../img/admin/disabled.gif" />'
				.$this->l('please contact your server administrator to enable fopen.').'<br/><br/>';

		// shop enabled
		if ($current_config['shop_deactivated'])
		{
			$srcShopStatus = '../img/admin/enabled.gif';
			$label = $this->l('Yes');
		}
		else
		{
			$srcShopStatus = '../img/admin/disabled.gif';
			$label = $this->l('No');
		}
		if (method_exists('Tools','getAdminTokenLite'))
			$token_preferences = Tools::getAdminTokenLite('AdminPreferences');
		else
			$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');

		$content .= '<b>'.$this->l('Shop deactivated').' : </b>'.'<img src="'.$srcShopStatus.'" /><a href="index.php?tab=AdminPreferences&token='.$token_preferences.'" class="button">'.$label.'</a><br/><br/>';

		// for informaiton, display time limit
		$max_exec_time = ini_get('max_execution_time');
		if ($max_exec_time == 0)
			$srcExecTime = '../img/admin/enabled.gif';
		else
			$srcExecTime = '../img/admin/warning.gif';
		$content .= '<b>'.$this->l('PHP time limit').' : </b>'.'<img src="'.$srcExecTime.'" />'.($max_exec_time == 0?$this->l('disabled'):$max_exec_time.' '.$this->l('seconds')).' <br/><br/>';
		
		// configuration done ?
		if ($current_config['module_configured'])
			$configurationDone = '../img/admin/enabled.gif';
		else
			$configurationDone = '../img/admin/disabled.gif';
		$content .= '<b>'.$this->l('Options chosen').' : </b>'
		.'<img src="'.$configurationDone.'" /> 
		<a class="button" id="scrollToOptions" href="#options">'
		.($current_config['module_configured']
			?$this->l('autoupgrade configuration ok')
			.' - '.$this->l('Modify your options')
			:$this->l('Please configure autoupgrade options')
		).'</a><br/><br/>';
		$content .= '</div></fieldset>';

		return $content;
	}

	public function displayDevTools()
	{
		$content = '';
		$content .= '<br class="clear"/>';
		$content .= '<fieldset class="autoupgradeSteps"><legend>'.$this->l('Step').'</legend>';
		$content .= '<h4>'.$this->l('Upgrade steps').'</h4>';
		$content .= '<div>';
		$content .= '<a href="" id="download" class="button upgradestep" >download</a>';
		$content .= '<a href="" id="unzip" class="button upgradestep" >unzip</a>'; // unzip in autoupgrade/latest
		$content .= '<a href="" id="removeSamples" class="button upgradestep" >removeSamples</a>'; // remove samples (iWheel images)
		$content .= '<a href="" id="backupFiles" class="button upgradestep" >backupFiles</a>'; // backup files
		$content .= '<a href="" id="backupDb" class="button upgradestep" >backupDb</a>';
		$content .= '<a href="" id="upgradeFiles" class="button upgradestep" >upgradeFiles</a>';
		$content .= '<a href="" id="upgradeDb" class="button upgradestep" >upgradeDb</a>';
		$content .= '</div>';

		if (defined('_PS_ALLOW_UPGRADE_UNSTABLE_') AND _PS_ALLOW_UPGRADE_UNSTABLE_ )
		{
			$content .= '<h4>Development tools </h4><div>';
			$content .= '<a href="" name="action" id="svnCheckout"	class="button upgradestep" type="submit" >svnCheckout</a>';
			$content .= '<a href="" name="action" id="svnUpdate"	class="button upgradestep" type="submit" >svnUpdate</a>';
			$content .= '<a href="" name="action" id="svnExport"	class="button upgradestep" type="submit" >svnExport</a>';
			$content .= '<br class="clear"/>';
			$content .= '</div>';
		}
		return $content;
	}

	private function _displayUpgraderForm()
	{
		global $cookie;
		$content = '';
		$pleaseUpdate = $this->upgrader->checkPSVersion();

		$content .= $this->getCurrentConfiguration();
		$content .= '<br/>';

		$content .= '<fieldset class=""><legend>'.$this->l('Update').'</legend>';
		$content .= '<b>'.$this->l('PrestaShop Original version').' : </b>'.'<span id="checkPrestaShopFilesVersion">
		<img id="pleaseWait" src="'.__PS_BASE_URI__.'img/loader.gif"/>
		</span><br/>';
		$content .= '<b>'.$this->l('Version modifications').' : </b>'.'<span id="checkPrestaShopModifiedFiles">
		<img id="pleaseWait" src="'.__PS_BASE_URI__.'img/loader.gif"/>
		</span>';
		$content .= '<script type="text/javascript">
			$("#currentConfigurationToggle").click(function(e){e.preventDefault();$("#currentConfiguration").toggle()});'
			.($this->configOk()?'$("#currentConfiguration").hide();
			$("#currentConfigurationToggle").after("<img src=\"../img/admin/enabled.gif\" />");':'').'</script>';

		// smarty2 uses is a warning only;
		$use_smarty3 = !(Configuration::get('PS_FORCE_SMARTY_2') === '1' || Configuration::get('PS_FORCE_SMARTY_2') === false);
		if ($use_smarty3)
		{
			$srcShopStatus = '../img/admin/enabled.gif';
			$label = $this->l('You use Smarty 3');
		}
		else
		{
			$srcShopStatus = '../img/admin/warning.gif';
			$label = $this->l('Smarty 2 is deprecated in 1.4 and removed maintained in 1.5. You may need to upgrade your current theme or use a new one.');
		}
		// if current version is 1.4, we propose to edit now the configuration
		if (version_compare(_PS_VERSION_, '1.4.0.0', '>='))
		{
			if (method_exists('Tools','getAdminTokenLite'))
				$token_preferences = Tools::getAdminTokenLite('AdminPreferences');
			else
				$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');
			$content .= '<div class="clear">&nbsp;</div><b>'.$this->l('Smarty 3 Usage').' : </b>'.'<img src="'.$srcShopStatus.'" />'.$label;
			if (version_compare(_PS_VERSION_, '1.4.0.0', '<'))
				$content .= '<div class="clear">&nbsp;</div><a href="index.php?tab=AdminPreferences&token='.$token_preferences.'#PS_FORCE_SMARTY_2" class="button">'.$this->l('Edit your Smarty configuration').'</a><br/><br/>';
		}
		$content .= '<div style="clear:left">&nbsp;</div><div style="float:left">
		<h1>'.sprintf($this->l('Your current prestashop version : %s '),_PS_VERSION_).'</h1>';

		// @TODO : this should be checked when init()
		$content .= '<img src="'._PS_ADMIN_IMG_.'information.png" alt="information"/> '
			.$this->l('Latest Prestashop version available is:')
				.' <b>'.$this->upgrader->version_name.'</b> ('. $this->upgrader->version_num.')</p>';

		if ($this->upgrader->need_upgrade)
		{
			if($this->configOk())
			{
				$content .= '<p><a href="" id="upgradeNow" class="button-autoupgrade upgradestep">'.$this->l('Upgrade PrestaShop now !').'</a></p>';
				$content .= '<small>'.sprintf($this->l('PrestaShop will be downloaded from %s'), $this->upgrader->link).'</small><br/>';
				$content .= '<small><a href="'.$this->upgrader->changelog.'">'.$this->l('see CHANGELOG').'</a></small>';
			}
			else
				$content .= '<p>'.$this->displayWarning($this->l('Your current configuration does not allow upgrade.')).'</p>';
		}
		else
			$content .= '<span class="button-autoupgrade upgradestep" >'.$this->l('Your shop is already up to date.').'</span> ';
		$content .= '</div><div class="clear"></div>';
		
		$content .= '<div><br/><br/><small>'.sprintf($this->l('last datetime check : %s '),date('Y-m-d H:i:s',Configuration::get('PS_LAST_VERSION_CHECK'))).'</span> 
		<a class="button" href="index.php?tab=AdminSelfUpgrade&token='.Tools::getAdminToken('AdminSelfUpgrade'.(int)(Tab::getIdFromClassName(get_class($this))).(int)$cookie->id_employee).'&refreshCurrentVersion=1">'.$this->l('Please click to refresh').'</a>
		</small></div>';

		$content .= '<div id="currentlyProcessing" style="display:none;float:right"><h4>Currently processing <img id="pleaseWait" src="'.__PS_BASE_URI__.'img/loader.gif"/></h4>

		<div id="infoStep" class="processing" style=height:50px;width:400px;" >'.$this->l('I\'m analyzing the situation, sir').'</div>';
		$content .= '</div>';

		$content .= '</fieldset>';

			if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_ AND $this->manualMode)
				$content .= $this->displayDevTools();

			$content .='	<div id="quickInfo" class="processing" style="height:100px;">&nbsp;</div>';
			// for upgradeDb
			$content .= '<p id="dbResultCheck"></p>';
			$content .= '<p id="dbCreateResultCheck"></p>';


		$content .= '</fieldset>';
		// information to keep will be in #infoStep
		// temporary infoUpdate will be in #tmpInformation
		$content .= '<script type="text/javascript">';
		// _PS_MODE_DEV_ will be available in js
		if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_)
			$content .= 'var _PS_MODE_DEV_ = true;';

		$content .= $this->_getJsErrorMsgs();

		$content .= '</script>';
		echo $content;
	}

	public function display()
	{
		// We need jquery 1.6 for json 
		// do we ?
		echo '<script type="text/javascript">
		if (jQuery == "undefined")
			jq13 = jQuery.noConflict(true);
			</script>
		<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/autoupgrade/jquery-1.6.2.min.js"></script>';
		$this->createCustomToken();
		/* PrestaShop demo mode */
		if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_)
		{
			echo '<div class="error">'.$this->l('This functionnality has been disabled.').'</div>';
			return;
		}

		if (!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.'ajax-upgradetab.php'))
		{
			echo '<div class="error">'.'<img src="../img/admin/warning.gif" /> [TECHNICAL ERROR] '.$this->l('ajax-upgradetab.php is missing. please reinstall or reset the module').'</div>';
			return false;
		}
		/* PrestaShop demo mode*/

		// in order to not use Tools class
		if(isset($_GET['refreshCurrentVersion']))
		{
			$upgrader = new Upgrader();
			$upgrader->checkPSVersion(true);
			// delete the potential xml files we saved in config/xml (from last release and from current)
			$upgrader->clearXmlMd5File(_PS_VERSION_);
			$upgrader->clearXmlMd5File($upgrader->version_num);
			$this->upgrader = $upgrader;
		}
		echo '<style>
.autoupgradeSteps div {  line-height: 30px; }
.upgradestep { margin-right: 5px;padding-left: 10px; padding-right: 5px;}
#upgradeNow.stepok, .autoupgradeSteps a.stepok { background-image: url("../img/admin/enabled.gif");background-position: left center;background-repeat: no-repeat;padding-left: 15px;}
#upgradeNow {-moz-border-bottom-colors: none;-moz-border-image: none;-moz-border-left-colors: none;-moz-border-right-colors: none;-moz-border-top-colors: none;border-color: #FFF6D3 #DFD5AF #DFD5AF #FFF6D3;border-right: 1px solid #DFD5AF;border-style: solid;border-width: 1px;color: #268CCD;font-size: medium;padding: 5px;}
.button-autoupgrade {-moz-border-bottom-colors: none;-moz-border-image: none;-moz-border-left-colors: none;-moz-border-right-colors: none;-moz-border-top-colors: none;border-color: #FFF6D3 #DFD5AF #DFD5AF #FFF6D3;border-right: 1px solid #DFD5AF;border-style: solid;border-width: 1px;color: #268CCD;font-size: medium;padding: 5px;}
.processing {border:2px outset grey;margin-top:1px;overflow: auto;}
#dbResultCheck{ padding-left:20px;}
#checkPrestaShopFilesVersion, #checkPrestaShopModifiedFiles{margin-bottom:20px;}
#changedList ul{list-style-type:circle}
.changedFileList {margin-left:20px; padding-left:5px;}
.changedNotice li{color:grey;}
.changedImportant li{color:red;font-weight:bold}
.upgradeDbError{background-color:red}
.upgradeDbOk{background-color:green}
.small_label{font-weight:normal;width:300px;float:none;text-align:left;padding:0}
</style>';
		$this->displayWarning($this->l('This function is experimental. It\'s highly recommended to make a backup of your files and database before starting the upgrade process.'));

		global $currentIndex;
		// update['name'] = version name
		// update['num'] = only the version
		// update['link'] = download link
		// @TODO

			if ($this->useSvn)
				echo '<div class="error"><h1>'.$this->l('Unstable upgrade').'</h1>
				<p class="warning">'.$this->l('Your current configuration indicate you want to upgrade your system from the unstable development branch, with no version number. If you upgrade, you will not be able to follow the official release process anymore').'.</p>
				</div>';
			
			$this->_displayUpgraderForm();

			echo '<br/>';
			$this->_displayRollbackForm();

			echo '<br/>';
			$this->_displayForm('autoUpgradeOptions',$this->_fieldsAutoUpgrade,'<a href="" name="options" id="options">'.$this->l('Options').'</a>', '','prefs');
			// @todo manual upload with a form

			echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/autoupgrade/jquery.xml2json.js"></script>';
			echo '<script type="text/javascript">'.$this->_getJsInit().'</script>';

	}

	private function _getJsInit()
	{
		global $currentIndex, $cookie;
		$js = '';

		if (method_exists('Tools','getAdminTokenLite'))
			$token_preferences = Tools::getAdminTokenLite('AdminPreferences');
		else
			$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');

		$js .= '
function ucFirst(str) {
	if (str.length > 0) {
		return str[0].toUpperCase() + str.substring(1);
	}
	else {
		return str;
	}
}

function cleanInfo(){
	$("#infoStep").html("reset<br/>");
}

function updateInfoStep(msg){
	if (msg)
	{
		$("#infoStep").html(msg);
		$("#infoStep").attr({ scrollTop: $("#infoStep").attr("scrollHeight") });
	}
}


function addQuickInfo(arrQuickInfo){
	if (arrQuickInfo)
	{
		$("#quickInfo").show();
		for(i=0;i<arrQuickInfo.length;i++)
			$("#quickInfo").append(arrQuickInfo[i]+"<div class=\"clear\"></div>");
		// Note : jquery 1.6 make uses of prop() instead of attr()
		$("#quickInfo").prop({ scrollTop: $("#quickInfo").prop("scrollHeight") },1);
	}
}';

		if ($this->manualMode)
			$js .= 'var manualMode = true;';
		else
			$js .= 'var manualMode = false;';

		// relative admin dir
		$adminDir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
		$js .= '
var firstTimeParams = '.$this->buildAjaxResult().';
firstTimeParams = firstTimeParams.nextParams;
firstTimeParams.firstTime = "1";

// js initialization : prepare upgrade and rollback buttons
$(document).ready(function(){
	$(".upgradestep").click(function(e)
	{
		e.preventDefault();
		// $.scrollTo("#options")
	});

		// set timeout to 5 minutes (download can be long)
		$.ajaxSetup({timeout:300000});

	// prepare available button here, without params ?
	prepareNextButton("#upgradeNow",firstTimeParams);
	
	/**
	 * reset rollbackParams js array (used to init rollback button)
	 */
	$("select[name=restoreName]").change(function(){
		$(this).next().remove();
		// show delete button if the value is not 0
		if($(this).val() != 0)
		{
			$(this).after("<a class=\"button confirmBeforeDelete\" href=\"index.php?tab=AdminSelfUpgrade&token='
					.Tools::getAdminToken('AdminSelfUpgrade'.(int)(Tab::getIdFromClassName('AdminSelfUpgrade')).(int)$cookie->id_employee)
					.'&amp;deletebackup&amp;name="+$(this).val()+"\">'
					.'<img src=\"../img/admin/disabled.gif\" />'.$this->l('Delete').'</a>");
			$(this).next().click(function(e){
				if (!confirm("'.$this->l('Are you sure you want to delete this backup ?').'"))
					e.preventDefault();
			});
		}

		if ($("select[name=restoreName]").val() != 0)
		{
			$("#rollback").removeAttr("disabled");
			rollbackParams = jQuery.extend(true, {}, firstTimeParams);

			delete rollbackParams.backupName;
			delete rollbackParams.backupFilesFilename;
			delete rollbackParams.backupDbFilename;
			delete rollbackParams.restoreFilesFilename;
			delete rollbackParams.restoreDbFilenames;
			
			// init new name to backup
			rollbackParams.restoreName = $("select[name=restoreName]").val();
			prepareNextButton("#rollback", rollbackParams);
			// Note : theses buttons have been removed.
			// they will be available in a future release (when DEV_MODE and MANUAL_MODE enabled) 
			// prepareNextButton("#restoreDb", rollbackParams);
			// prepareNextButton("#restoreFiles", rollbackParams);
		}
		else
			$("#rollback").attr("disabled", "disabled");
	});
	$("select[name=restoreName]").change();

});


// reuse previousParams, and handle xml returns to calculate next step
// (and the correct next param array)
// a case has to be defined for each requests that returns xml


function afterUpgradeNow(params)
{
	$("#upgradeNow").unbind();
	$("#upgradeNow").replaceWith("<span class=\"button-autoupgrade\">'.$this->l('Upgrading PrestaShop').' ...</span>");
}

function afterUpgradeComplete(params)
{
	$("#pleaseWait").hide();
	$("#dbResultCheck")
		.addClass("ok")
		.removeClass("fail")
		.html("<p>'.$this->l('upgrade complete. Please check your front-office theme is functionnal (try to make an order, check theme)').'</p>")
		.show("slow")
		.append("<a href=\"index.php?tab=AdminPreferences&token='.$token_preferences.'\" class=\"button\">'.$this->l('activate your shop here').'</a>");
	$("#dbCreateResultCheck")
		.hide("slow");
	$("#infoStep").html("<h3>'.$this->l('Upgrade Complete !').'</h3>");
}

function afterRollbackComplete(params)
{
	$("#rollback").attr("disabled", "disabled");
	$($("select[name=restoreName]").children()[0])
		.attr("selected", "selected");
	$(".button-autoupgrade").html("'.$this->l('Restoration complete.').'");
}
function afterRollbackComplete(params)
{
	$("#pleaseWait").hide();
	$("#dbResultCheck")
		.addClass("ok")
		.removeClass("fail")
		.html("<p>'.$this->l('restoration complete.').'</p>")
		.show("slow")
		.append("<a href=\"index.php?tab=AdminPreferences&token='.$token_preferences.'\" class=\"button\">'.$this->l('activate your shop here').'</a>");
	$("#dbCreateResultCheck")
		.hide("slow");
	$("#infoStep").html("<h3>'.$this->l('Restoration Complete.').'</h3>");
}


function afterRestoreDb(params)
{
	// $("#restoreBackupContainer").hide();
}

function afterRestoreFiles(params)
{
	// $("#restoreFilesContainer").hide();
}

function afterBackupFiles(params)
{
	if (params.stepDone)
	{
	}
}

/**
 * afterBackupDb display the button
 *
 */
function afterBackupDb(params)
{
	if (params.stepDone)
	{
		$("#restoreBackupContainer").show();
		$("select[name=restoreName]").children().removeAttr("selected");
		$("select[name=restoreName]")
			.append("<option selected=\"selected\" value=\""+params.backupName+"\">"+params.backupName+"</option>")
	}
}


function call_function(func){
	this[func].apply(this, Array.prototype.slice.call(arguments, 1));
}

function doAjaxRequest(action, nextParams){
	var _PS_MODE_DEV_;
	if (_PS_MODE_DEV_)
		addQuickInfo(["[DEV] ajax request : "+action]);
	$("#pleaseWait").show();
	req = $.ajax({
		type:"POST",
		url : "'. __PS_BASE_URI__.$adminDir.'/autoupgrade/ajax-upgradetab.php'.'",
		async: true,
		data : {
			dir:"'.$adminDir.'",
			ajaxMode : "1",
			token : "'.$this->token.'",
			tab : "AdminSelfUpgrade",
			action : action,
			params : nextParams
		},
		success : function(res,textStatus,jqXHR)
		{
			$("#pleaseWait").hide();

			try{
				res = $.parseJSON(res);
				addQuickInfo(res.nextQuickInfo);
				currentParams = res.nextParams;
				if (res.status == "ok")
				{
					$("#"+action).addClass("done");
					if (res.stepDone)
						$("#"+action).addClass("stepok");
					// if a function "after[action name]" exists, it should be called now.
					// This is used for enabling restore buttons for example
					funcName = "after"+ucFirst(action);
					if (typeof funcName == "string" && eval("typeof " + funcName) == "function") 
						call_function(funcName, currentParams);

					handleSuccess(res);
				}
				else
				{
					// display progression
					$("#"+action).addClass("done");
					$("#"+action).addClass("steperror");
					if (action != "rollback" 
						&& action != "rollbackComplete" 
						&& action != "restoreFiles"
						&& action != "restoreDb"
						&& action != "rollback"
						&& action != "noRollbackFound"
					)
						handleError(res);
					else
						alert("[TECHNICAL ERROR] Error detected during ["+action+"].");
				}
			}
			catch(e){
				res = {status : "error"};
				alert("[TECHNICAL ERROR] Error detected during ["+action+"].");
			}
		},
		error: function(res, textStatus, jqXHR)
		{
			$("#pleaseWait").hide();
			if (textStatus == "timeout" && action == "download")
			{
				updateInfoStep("'.$this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory').'");
			}
			else
				if (textStatus == "timeout")
					updateInfoStep("[Server Error] Timeout:'.$this->l('The request excessed the max_time_limit. Please change your server configuration.').'");
			{
				updateInfoStep("[Server Error] Status message : " + textStatus);
			}
		}
	});
};

/**
 * prepareNextButton make the button button_selector available, and update the nextParams values
 *
 * @param button_selector $button_selector
 * @param nextParams $nextParams
 * @return void
 */
function prepareNextButton(button_selector, nextParams)
{
	$(button_selector).unbind();
	$(button_selector).click(function(e){
		e.preventDefault();
		$("#currentlyProcessing").show();
';
		$js .= '
	action = button_selector.substr(1);
	res = doAjaxRequest(action, nextParams);
	});
}

/**
 * handleSuccess
 * res = {error:, next:, nextDesc:, nextParams:, nextQuickInfo:,status:"ok"}
 * @param res $res
 * @return void
 */
function handleSuccess(res)
{
	updateInfoStep(res.nextDesc);
	if (res.next != "")
	{

		$("#"+res.next).addClass("nextStep");
		if (manualMode)
		{
			prepareNextButton("#"+res.next,res.nextParams);
			alert("manually go to "+res.next+" button ");
		}
		else
		{
			// 1) instead of click(), call a function.
			doAjaxRequest(res.next,res.nextParams);
			// 2) remove all step link (or show them only in dev mode)
			// 3) when steps link displayed, they should change color when passed if they are visible
		}
	}
	else
	{
		// Way To Go, end of upgrade process
		addQuickInfo(["End of process"]);
	}
}

// res = {nextParams, NextDesc}
function handleError(res)
{
	// display error message in the main process thing
	updateInfoStep(res.nextDesc);
	// In case the rollback button has been deactivated, just re-enable it
	$("#rollback").removeAttr("disabled");
	$(".button-autoupgrade").html("'.$this->l('Operation cancelled. Restoration in progress ...').'");
	doAjaxRequest("rollback",res.nextParams);


}
';
// ajax to check md5 files
		$js .= 'function addModifiedFileList(title, fileList, css_class, container)
{
	subList = $("<ul class=\"changedFileList "+css_class+"\"></ul>");

	$(fileList).each(function(k,v){
		$(subList).append("<li>"+v+"</li>");
	});
	$(container).append("<h3><a class=\"toggleSublist\">"+title+"</a> (" + fileList.length + ")</h3>");
	$(container).append(subList);
	$(container).append("<br/>");

}';
	if(!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.'ajax-upgradetab.php'))
		$js .= '$(document).ready(function(){
			$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> [TECHNICAL ERROR] ajax-upgradetab.php '.$this->l('is missing. please reinstall the module').'");
			})';
	else
		$js .= '
			function isJsonString(str) {
				try {
						JSON.parse(str);
				} catch (e) {
						return false;
				}
				return true;
		}
	
$(document).ready(function(){
	$.ajax({
			type:"POST",
			url : "'. __PS_BASE_URI__ . $adminDir.'/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir:"'.$adminDir.'",
				token : "'.$this->token.'",
				tab : "'.get_class($this).'",
				action : "checkFilesVersion",
				ajaxMode : "1",
				params : {}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
				{
					res = {nextParams:{status:"error"}};
				}
					answer = res.nextParams;
					$("#checkPrestaShopFilesVersion").html("<span> "+answer.msg+" </span> ");
					if ((answer.status == "error") || (typeof(answer.result) == "undefined"))
						$("#checkPrestaShopFilesVersion").prepend("<img src=\"../img/admin/warning.gif\" /> ");
					else
					{
						$("#checkPrestaShopFilesVersion").prepend("<img src=\"../img/admin/warning.gif\" /> ");
						$("#checkPrestaShopFilesVersion").append("<a id=\"toggleChangedList\" class=\"button\" href=\"\">'.$this->l('See or hide the list').'</a><br/>");
						$("#checkPrestaShopFilesVersion").append("<div id=\"changedList\" style=\"display:none \"><br/>");
						if(answer.result.core.length)
							addModifiedFileList("'.$this->l('Core file(s)').'", answer.result.core, "changedImportant", "#changedList");
						if(answer.result.mail.length)
							addModifiedFileList("'.$this->l('Mail file(s)').'", answer.result.mail, "changedNotice", "#changedList");
						if(answer.result.translation.length)
							addModifiedFileList("'.$this->l('Translation file(s)').'", answer.result.translation, "changedNotice", "#changedList");

						$("#toggleChangedList").bind("click",function(e){e.preventDefault();$("#changedList").toggle();});
						$(".toggleSublist").live("click",function(e){e.preventDefault();$(this).parent().next().toggle();});
				}
			}
			,
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep("'.$this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory').'");
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> [TECHNICAL ERROR] Unable to check md5 files");
				}
			}
		})
	$.ajax({
			type:"POST",
			url : "'. __PS_BASE_URI__ . $adminDir.'/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir:"'.$adminDir.'",
				token : "'.$this->token.'",
				tab : "'.get_class($this).'",
				action : "compareReleases",
				ajaxMode : "1",
				params : {}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
				{
					res = {nextParams:{status:"error"}};
				}
				answer = res.nextParams;
				$("#checkPrestaShopModifiedFiles").html("<span> "+answer.msg+" </span> ");
				if ((answer.status == "error") || (typeof(answer.result) == "undefined"))
					$("#checkPrestaShopModifiedFiles").prepend("<img src=\"../img/admin/warning.gif\" /> ");
				else
				{
					$("#checkPrestaShopModifiedFiles").prepend("<img src=\"../img/admin/warning.gif\" /> ");
					$("#checkPrestaShopModifiedFiles").append("<a id=\"toggleDiffList\" class=\"button\" href=\"\">'.$this->l('See or hide the list').'</a><br/>");
					$("#checkPrestaShopModifiedFiles").append("<div id=\"diffList\" style=\"display:none \"><br/>");
						if(answer.result.deleted.length)
							addModifiedFileList("'.$this->l('Theses files will be deleted').'", answer.result.deleted, "diffImportant", "#diffList");
						if(answer.result.modified.length)
							addModifiedFileList("'.$this->l('Theses files will be modified').'", answer.result.modified, "diffImportant", "#diffList");

					$("#toggleDiffList").bind("click",function(e){e.preventDefault();$("#diffList").toggle();});
					$(".toggleSublist").die().live("click",function(e){
						e.preventDefault();
						// this=a, parent=h3, next=ul
						$(this).parent().next().toggle();
					});
				}
			},
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep("'.$this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory').'");
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> [TECHNICAL ERROR] Unable to check md5 files");
				}
			}
		})
});';
		return $js;
	}


	/**
	 * @desc extract a zip file to the given directory
	 * @return bool success
	 * we need a copy of it to be able to restore without keeping Tools and Autoload stuff
	 */
	private function ZipExtract($fromFile, $toDir)
	{
		if (!is_file($fromFile))
		{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('%s is not a file'), $fromFile);
				return false;
			}

		if (!file_exists($toDir))
			if (!@mkdir($toDir, 0777))
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('unable to create directory %s'), $toDir);
				return false;
			}

		if (!self::$force_pclZip && class_exists('ZipArchive', false))
		{
			$this->nextQuickInfo[] = $this->l('using class ZipArchive ...');
			$zip = new ZipArchive();
			if (@$zip->open($fromFile) === true)
			{
				if ($resExtract = $zip->extractTo($toDir))
				{
					$this->nextQuickInfo[] = $this->l('backup extracted');
					return true;
				}
				else
				{
					$this->nextQuickInfo[] = sprintf($this->l('zip->extractTo() : unable to use %s as extract destination.'), $toDir);
					return false;
				}
			}
			else
			{
				$this->nextQuickInfo[] = sprintf($this->l('Unable to open zipFile %s'), $fromFile);
				return false;
			}
		}
		else
		{
			// todo : no relative path
			if (!class_exists('PclZip',false))
				require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/pclzip.lib.php');

			$this->nextQuickInfo[] = $this->l('using class pclZip.lib.php');
			$zip = new PclZip($fromFile);
			// replace also modified files
			$extract_result = $zip->extract(PCLZIP_OPT_PATH, $toDir, PCLZIP_OPT_REPLACE_NEWER);
			if (is_array($extract_result))
			{
				foreach ($extract_result as $extractedFile)
				{
					$file = str_replace($this->prodRootDir, '', $extractedFile['filename']);
					if ($extractedFile['status'] != 'ok')
						$this->nextQuickInfo[] = sprintf('[ERROR] %s has not been unzipped', $file);
					else
						$this->nextQuickInfo[] = sprintf('%1$s unzipped into %2$s', 
							$file, str_replace(_PS_ROOT_DIR_, '', $toDir.'/'));
				}
				return true;
			}
			else
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = '[ERROR] error on extract using pclzip : '.$zip->errorInfo(true);
				return false;
			}
		}
	}

	private function _listArchivedFiles($zipfile)
	{
		if (file_exists($zipfile))
		{
			if (!self::$force_pclZip && class_exists('ZipArchive', false))
			{
				$files=array();
				$zip = new ZipArchive();
				$zip->open($zipfile);
				if ($zip){
					for ($i = 0; $i < $zip->numFiles; $i++)
						$files[] = $zip->getNameIndex($i);
					return $files;
				}
				else
				{
					$this->nextQuickInfo[] = '[ERROR] Unable to list archived files';
					return false;
				}
				// @todo : else throw new Exception()
			}
			else
			{
				require_once(dirname(__FILE__).'/pclzip.lib.php');
				if ($zip = new PclZip($zipfile));
					return $zip->listContent();
				// @todo : else throw new Exception()
			}
		}
		return false;
	}

	/**
	 *	bool _skipFile : check whether a file is in backup or restore skip list
	 *
	 * @param type $file : current file or directory name eg:'.svn' , 'settings.inc.php'
	 * @param type $fullpath : current file or directory fullpath eg:'/home/web/www/prestashop/img'
	 * @param type $way : 'backup' , 'upgrade'
	 */
	protected function _skipFile($file, $fullpath, $way='backup')
	{
		$fullpath = str_replace('\\','/', $fullpath); // wamp compliant
		$rootpath = str_replace('\\','/', $this->prodRootDir);
		$adminDir = str_replace($this->prodRootDir, '', $this->adminDir);
		switch ($way)
		{
			case 'backup':
				if (in_array($file, $this->backupIgnoreFiles))
					return true;

				foreach($this->backupIgnoreAbsoluteFiles as $path)
				{
					$path = str_replace('/admin', '/'.$adminDir, $path);
					if ($fullpath == $rootpath.$path)
						return true;
				}
				break;

			case 'upgrade':
				if (in_array($file, $this->excludeFilesFromUpgrade))
					return true;

				foreach ($this->excludeAbsoluteFilesFromUpgrade as $path)
				{
					$path = str_replace('/admin', '/'.$adminDir, $path);
					if (strpos($fullpath, $rootpath.$path) !== false)
						return true;
				}
				break;
			// default : if it's not a backup or an upgrade, do not skip the file
			default:
				return false;
		}
		// by default, don't skip 
		return false;
	}
	public function displayInvalidToken()
	{
		die("wrong token");
	}
}

