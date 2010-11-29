<?php

/**
  * Module class, Module.php
  * Modules management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

abstract class ModuleCore
{
	/** @var integer Module ID */
	public $id = NULL;

	/** @var float Version */
	public $version;

	/** @var string Unique name */
	public $name;

	/** @var string Human name */
	public $displayName;

	/** @var string A little description of the module */
	public $description;

	/** @var string Admin tab correponding to the module */
	public $tab = NULL;

	/** @var boolean Status */
	public $active = false;

	/** @var array current language translations */
	private $_lang = array();

	/** @var string Module web path (eg. '/shop/modules/modulename/')  */
	protected $_path = NULL;

	/** @var string Fill it if the module is installed but not yet set up */
	public $warning;

	/** @var string Message display before uninstall a module */
	public $beforeUninstall = NULL;

	public $_errors = false;

	protected $table = 'module';

	protected $identifier = 'id_module';

	static public $_db;

	/**
	 * Constructor
	 *
	 * @param string $name Module unique name
	 */
	private static $modulesCache;
	private static $_hookModulesCache;
	public function __construct($name = NULL)
	{
		global $cookie;

		if ($this->name == NULL)
			$this->name = $this->id;
		if ($this->name != NULL)
		{
			if (self::$modulesCache == NULL AND !is_array(self::$modulesCache))
			{
				self::$modulesCache = array();
				$result = Db::getInstance()->ExecuteS('SELECT * FROM `'.pSQL(_DB_PREFIX_.$this->table).'`');
				foreach ($result as $row)
					self::$modulesCache[$row['name']] = $row;
			}
			if (!isset(self::$modulesCache[$this->name]))
				return false;
			$this->active = true;
			$this->id = self::$modulesCache[$this->name]['id_module'];
			foreach (self::$modulesCache[$this->name] AS $key => $value)
				if (key_exists($key, $this))
					$this->{$key} = $value;
			$this->_path = __PS_BASE_URI__.'modules/'.$this->name.'/';
		}
	}

	/**
	 * Insert module into datable
	 */
	public function install()
	{
		if (!Validate::isModuleName($this->name))
			die(Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT `id_module`
		FROM `'._DB_PREFIX_.'module`
		WHERE `name` = \''.pSQL($this->name).'\'');
		if ($result)
			return false;
		$result = Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table, array('name' => $this->name, 'active' => 1), 'INSERT');
		if (!$result)
			return false;
		$this->id = Db::getInstance()->Insert_ID();
		return true;
	}

	/**
	 * Delete module from datable
	 *
	 * @return boolean result
	 */
	public function uninstall()
	{
		if (!Validate::isUnsignedId($this->id))
			return false;
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_hook`
		FROM `'._DB_PREFIX_.'hook_module` hm
		WHERE `id_module` = '.(int)($this->id));
		foreach	($result AS $row)
		{
			Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'hook_module`
			WHERE `id_module` = '.(int)($this->id).'
			AND `id_hook` = '.(int)($row['id_hook']));
			$this->cleanPositions($row['id_hook']);
		}
		return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'module`
			WHERE `id_module` = '.(int)($this->id));
	}

	/**
	 * Connect module to a hook
	 *
	 * @param string $hook_name Hook name
	 * @return boolean result
	 */
	public function registerHook($hook_name)
	{
		if (!Validate::isHookName($hook_name))
			die(Tools::displayError());
		if (!isset($this->id) OR !is_numeric($this->id))
			return false;

		// Check if already register
		$result = Db::getInstance()->getRow('
		SELECT hm.`id_module` FROM `'._DB_PREFIX_.'hook_module` hm, `'._DB_PREFIX_.'hook` h
		WHERE hm.`id_module` = '.(int)($this->id).'
		AND h.`name` = \''.pSQL($hook_name).'\'
		AND h.`id_hook` = hm.`id_hook`');
		if ($result)
			return true;

		// Get hook id
		$result = Db::getInstance()->getRow('
		SELECT `id_hook`
		FROM `'._DB_PREFIX_.'hook`
		WHERE `name` = \''.pSQL($hook_name).'\'');
		if (!isset($result['id_hook']))
			return false;

		// Get module position in hook
		$result2 = Db::getInstance()->getRow('
		SELECT MAX(`position`) AS position
		FROM `'._DB_PREFIX_.'hook_module`
		WHERE `id_hook` = '.(int)($result['id_hook']));
		if (!$result2)
			return false;

		// Register module in hook
		$return = Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'hook_module` (`id_module`, `id_hook`, `position`)
		VALUES ('.(int)($this->id).', '.(int)($result['id_hook']).', '.(int)($result2['position'] + 1).')');

		$this->cleanPositions((int)($result['id_hook']));

		return $return;
	}

	/**
	  * Display flags in forms for translations
	  *
	  * @param array $languages All languages available
	  * @param integer $defaultLanguage Default language id
	  * @param string $ids Multilingual div ids in form
	  * @param string $id Current div id]
	  * #param boolean $return define the return way : false for a display, true for a return
	  */
	public function displayFlags($languages, $defaultLanguage, $ids, $id, $return = false)
	{
		if (sizeof($languages) == 1)
			return false;
		$defaultIso = Language::getIsoById($defaultLanguage);
		$output = '
		<div class="displayed_flag">
			<img src="../img/l/'.$defaultLanguage.'.jpg" class="pointer" id="language_current_'.$id.'" onclick="toggleLanguageFlags(this);" alt="" />
		</div>
		<div id="languages_'.$id.'" class="language_flags">
			'.$this->l('Choose language:').'<br /><br />';
		foreach ($languages as $language)
			$output .= '<img src="../img/l/'.(int)($language['id_lang']).'.jpg" class="pointer" alt="'.$language['name'].'" title="'.$language['name'].'" onclick="changeLanguage(\''.$id.'\', \''.$ids.'\', '.$language['id_lang'].', \''.$language['iso_code'].'\');" /> ';
		$output .= '</div>';

		if ($return)
			return $output;
		echo $output;
	}

	/**
	  * Unregister module from hook
	  *
	  * @param int $id_hook Hook id
	  * @return boolean result
	  */
	public function unregisterHook($hook_id)
	{
		return Db::getInstance()->Execute('
		DELETE
		FROM `'._DB_PREFIX_.'hook_module`
		WHERE `id_module` = '.(int)($this->id).'
		AND `id_hook` = '.(int)($hook_id));
	}

	/**
	  * Unregister exceptions linked to module
	  *
	  * @param int $id_hook Hook id
	  * @return boolean result
	  */
	public function unregisterExceptions($hook_id)
	{
		return Db::getInstance()->Execute('
		DELETE
		FROM `'._DB_PREFIX_.'hook_module_exceptions`
		WHERE `id_module` = '.(int)($this->id).'
		AND `id_hook` = '.(int)($hook_id));
	}

	/**
	  * Add exceptions for module->Hook
	  *
	  * @param int $id_hook Hook id
	  * @param array $excepts List of file name
	  * @return boolean result
	  */
	public function registerExceptions($id_hook, $excepts)
	{
		foreach ($excepts AS $except)
		{
			if (!empty($except))
			{
				$result = Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'hook_module_exceptions` (`id_module`, `id_hook`, `file_name`)
				VALUES ('.(int)($this->id).', '.(int)($id_hook).', \''.pSQL(strval($except)).'\')');
				if (!$result)
					return false;
			}
		}
		return true;
	}
	
	public function editExceptions($id_hook, $excepts)
	{
		// Cleaning...
		Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'hook_module_exceptions` 
				WHERE `id_module` = '.(int)($this->id).' AND `id_hook` ='.(int)($id_hook));
		return $this->registerExceptions($id_hook, $excepts);
	}

	/**
	  * Return an instance of the specified module
	  *
	  * @param string $moduleName Module name
	  * @return Module instance
	  */
	static public function getInstanceByName($moduleName)
	{
		if (!Tools::file_exists_cache(_PS_MODULE_DIR_.$moduleName.'/'.$moduleName.'.php'))
			return false;
		include_once(_PS_MODULE_DIR_.$moduleName.'/'.$moduleName.'.php');
		if (!class_exists($moduleName, false))
			return false;
		return (new $moduleName);
	}

	/**
	  * Return an instance of the specified module
	  *
	  * @param integer $id_module Module ID
	  * @return Module instance
	  */
	static public function getInstanceById($id_module)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'module`
		WHERE `id_module` = '.(int)($id_module));
		return ($result ? Module::getInstanceByName($result['name']) : false);
	}

	/**
	  * Return available modules
	  *
	  * @return array Modules
	  */
	public static function getModulesOnDisk()
	{
		$moduleList = array();
		$errors = array();
		$modules_dir = self::getModulesDirOnDisk();
		foreach ($modules_dir AS $module)
		{
			$file = trim(file_get_contents(_PS_MODULE_DIR_.'/'.$module.'/'.$module.'.php'));
			if (substr($file, 0, 5) == '<?php')
				$file = substr($file, 5);
			if (substr($file, -2) == '?>')
				$file = substr($file, 0, -2);
			if (class_exists($module, false) OR eval($file) !== false)
				$moduleList[] = new $module;
			else
				$errors[] = $module;
		}
		
		if (sizeof($errors))
		{
			echo '<div class="alert error"><h3>'.Tools::displayError('Parse error(s) in module(s)').'</h3><ol>';
			foreach ($errors AS $error)
				echo '<li>'.$error.'</li>';
			echo '</ol></div>';
		}
		return $moduleList;
	}

	public static function getModulesDirOnDisk()
	{
		$moduleList = array();
		$modules = scandir(_PS_MODULE_DIR_);
		foreach ($modules AS $name)
		{
			if (Tools::file_exists_cache($moduleFile = _PS_MODULE_DIR_.'/'.$name.'/'.$name.'.php'))
			{
				if (!Validate::isModuleName($name))
					die(Tools::displayError().' (Module '.$name.')');
				$moduleList[] = $name;
			}
		}
		return $moduleList;
	}

	/**
		* Return installed modules
		*
		* @param int $position Take only positionnables modules
		* @return array Modules
		*/
	public static function getModulesInstalled($position = 0)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'module` m
		'.($position ? '
		LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON m.`id_module` = hm.`id_module`
		LEFT JOIN `'._DB_PREFIX_.'hook` k ON hm.`id_hook` = k.`id_hook`
		WHERE k.`position` = 1' : ''));
	}

	/*
	 * Execute modules for specified hook
	 *
	 * @param string $hook_name Hook Name
	 * @param array $hookArgs Parameters for the functions
	 * @return string modules output
	 */
	public static function hookExec($hook_name, $hookArgs = array(), $id_module = NULL)
	{
		if ((!empty($id_module) AND !Validate::isUnsignedId($id_module)) OR !Validate::isHookName($hook_name))
			die(Tools::displayError());

		global $cart, $cookie;

		if (!isset($hookArgs['cookie']) OR !$hookArgs['cookie'])
			$hookArgs['cookie'] = $cookie;
		if (!isset($hookArgs['cart']) OR !$hookArgs['cart'])
			$hookArgs['cart'] = $cart;
		$hook_name = strtolower($hook_name);

		if (!isset(self::$_hookModulesCache))
		{
			$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
			$result = $db->ExecuteS('
			SELECT h.`name` as hook, m.`id_module`, h.`id_hook`, m.`name` as module
			FROM `'._DB_PREFIX_.'module` m
			LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
			LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
			AND m.`active` = 1
			ORDER BY hm.`position`', false);
			self::$_hookModulesCache = array();
			while ($row = $db->nextRow())
			{
				$row['hook'] = strtolower($row['hook']);
				if (!isset(self::$_hookModulesCache[$row['hook']]))
					self::$_hookModulesCache[$row['hook']] = array();
				self::$_hookModulesCache[$row['hook']][] = array('id_hook' => $row['id_hook'], 'module' => $row['module'], 'id_module' => $row['id_module']);
			}
		}

		if (!isset(self::$_hookModulesCache[$hook_name]))
			return;
		$altern = 0;
		$output = '';
		foreach (self::$_hookModulesCache[$hook_name] AS $array)
		{
			if ($id_module AND $id_module != $array['id_module'])
				continue;
			if (!($moduleInstance = Module::getInstanceByName($array['module'])))
				continue;
			
			$exceptions = $moduleInstance->getExceptions((int)($array['id_hook']), $array['id_module']);
			$phpSelf = basename($_SERVER['PHP_SELF']);
			foreach ($exceptions as $exception)
				if ($phpSelf == $exception['file_name'])
					continue 2;
			if (is_callable(array($moduleInstance, 'hook'.$hook_name)))
			 {
				$hookArgs['altern'] = ++$altern;
				$output .= call_user_func(array($moduleInstance, 'hook'.$hook_name), $hookArgs);
			}
		}
		return $output;
	}
	
	public static function hookExecPayment()
	{
		global $cart, $cookie;
		$hookArgs = array('cookie' => $cookie, 'cart' => $cart);
		$id_customer = (int)($cookie->id_customer);
		$billing = new Address((int)($cart->id_address_invoice));
		$output = '';

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT DISTINCT h.`id_hook`, m.`name`, hm.`position`
		FROM `'._DB_PREFIX_.'module_country` mc
		LEFT JOIN `'._DB_PREFIX_.'module` m ON m.`id_module` = mc.`id_module`
		INNER JOIN `'._DB_PREFIX_.'module_group` mg ON (m.`id_module` = mg.`id_module`)
		INNER JOIN `'._DB_PREFIX_.'customer_group` cg on (cg.`id_group` = mg.`id_group` AND cg.`id_customer` = '.(int)($id_customer).')
		LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
		LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
		WHERE h.`name` = \'payment\'
		AND mc.id_country = '.(int)($billing->id_country).'
		AND m.`active` = 1
		ORDER BY hm.`position`, m.`name` DESC');
		if ($result)
			foreach ($result AS $k => $module)
				if (($moduleInstance = Module::getInstanceByName($module['name'])) AND is_callable(array($moduleInstance, 'hookpayment')))
					if (!$moduleInstance->currencies OR ($moduleInstance->currencies AND sizeof(Currency::checkPaymentCurrencies($moduleInstance->id))))
						$output .= call_user_func(array($moduleInstance, 'hookpayment'), $hookArgs);
		return $output;
	}

	/*
	 * Get translation for a given module text
	 *
	 * @param string $string String to translate
	 * @return string Translation
	 */
	public function l($string, $specific = false)
	{
		global $_MODULES, $_MODULE, $cookie;
		
		$id_lang = (!isset($cookie) OR !is_object($cookie)) ? (int)(Configuration::get('PS_LANG_DEFAULT')) : (int)($cookie->id_lang);
		$file = _PS_MODULE_DIR_.$this->name.'/'.Language::getIsoById($id_lang).'.php';
		if (Tools::file_exists_cache($file) AND include_once($file))
			$_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;

		if (!is_array($_MODULES))
			return (str_replace('"', '&quot;', $string));

		$source = Tools::strtolower($specific ? $specific : get_class($this));
		$string2 = str_replace('\'', '\\\'', $string);
		$currentKey = '<{'.$this->name.'}'._THEME_NAME_.'>'.$source.'_'.md5($string2);
		$defaultKey = '<{'.$this->name.'}prestashop>'.$source.'_'.md5($string2);

		if (key_exists($currentKey, $_MODULES))
			$ret = stripslashes($_MODULES[$currentKey]);
		elseif (key_exists($defaultKey, $_MODULES))
			$ret = stripslashes($_MODULES[$defaultKey]);
		else
			$ret = $string;
		return str_replace('"', '&quot;', $ret);
	}

	/*
	 * Reposition module
	 *
	 * @param boolean $id_hook Hook ID
	 * @param boolean $way Up (1) or Down (0)
	 * @param intger $position
	 */
	public function updatePosition($id_hook, $way, $position = NULL)
	{
		if (!$res = Db::getInstance()->ExecuteS('
		SELECT hm.`id_module`, hm.`position`, hm.`id_hook` 
		FROM `'._DB_PREFIX_.'hook_module` hm 
		WHERE hm.`id_hook` = '.(int)($id_hook).' 
		ORDER BY hm.`position` '.((int)($way) ? 'ASC' : 'DESC')))
			return false;
		foreach ($res AS $key => $values)
			if ((int)($values[$this->identifier]) == (int)($this->id))
			{
				$k = $key ;
				break ;
			}
		if (!isset($k) OR !isset($res[$k]) OR !isset($res[$k + 1]))
			return false;
		$from = $res[$k];
		$to = $res[$k + 1];

		if (isset($position) and !empty($position))
			$to['position'] = (int)($position);
		
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'hook_module`
		SET `position`= position '.($way ? '-1' : '+1').'
		WHERE position between '.(int)(min(array($from['position'], $to['position']))) .' AND '.(int)(max(array($from['position'], $to['position']))).'
		AND `id_hook`='.(int)($from['id_hook']))
		AND
		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'hook_module`
		SET `position`='.(int)($to['position']).'
		WHERE `'.pSQL($this->identifier).'` = '.(int)($from[$this->identifier]).' AND `id_hook`='.(int)($to['id_hook']))
		);
	}

	/*
	 * Reorder modules position
	 *
	 * @param boolean $id_hook Hook ID
	 */
	public function cleanPositions($id_hook)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_module`
		FROM `'._DB_PREFIX_.'hook_module`
		WHERE `id_hook` = '.(int)($id_hook).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i)
			Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'hook_module`
			SET `position` = '.(int)($i + 1).'
			WHERE `id_hook` = '.(int)($id_hook).'
			AND `id_module` = '.(int)($result[$i]['id_module']));
		return true;
	}

	/*
	 * Return module position for a given hook
	 *
	 * @param boolean $id_hook Hook ID
	 * @return integer position
	 */
	public function getPosition($id_hook)
	{
		$result = Db::getInstance()->getRow('
			SELECT `position`
			FROM `'._DB_PREFIX_.'hook_module`
			WHERE `id_hook` = '.(int)($id_hook).'
			AND `id_module` = '.(int)($this->id));
		return $result['position'];
	}

	public function displayError($error)
	{
	 	$output = '
		<div class="module_error alert error">
			<img src="'._PS_IMG_.'admin/warning.gif" alt="" title="" /> '.$error.'
		</div>';
		$this->error = true;
		return $output;
	}

	public function displayConfirmation($string)
	{
	 	$output = '
		<div class="module_confirmation conf confirm">
			<img src="'._PS_IMG_.'admin/ok.gif" alt="" title="" /> '.$string.'
		</div>';
		return $output;
	}

	/*
	 * Return exceptions for module in hook
	 *
	 * @param int $id_hook Hook ID
	 * @return array Exceptions
	 */
	private static $exceptionsCache = NULL;
	public function getExceptions($id_hook)
	{
		if (self::$exceptionsCache == NULL AND !is_array(self::$exceptionsCache))
		{
			self::$exceptionsCache = array();
			$result = Db::getInstance()->ExecuteS('
			SELECT CONCAT(id_hook, \'-\', id_module) as `key`, `file_name` as value
			FROM `'._DB_PREFIX_.'hook_module_exceptions`');
			foreach ($result as $row)
			{
				if (!array_key_exists($row['key'], self::$exceptionsCache))
					self::$exceptionsCache[$row['key']] = array();
				self::$exceptionsCache[$row['key']][] = array('file_name' => $row['value']);
			}
		}
		return (array_key_exists((int)($id_hook).'-'.(int)($this->id), self::$exceptionsCache) ? self::$exceptionsCache[(int)($id_hook).'-'.(int)($this->id)] : array());
	}

	public static function isInstalled($moduleName)
	{
		Db::getInstance()->Execute('SELECT `id_module` FROM `'._DB_PREFIX_.'module` WHERE `name` = \''.pSQL($moduleName).'\'');
		return (bool)Db::getInstance()->NumRows();
	}
	
	public function isRegisteredInHook($hook)
	{
		if (!$this->id)
			return false;
		
		return Db::getInstance()->getValue('
		SELECT COUNT(*) 
		FROM `'._DB_PREFIX_.'hook_module` hm
		LEFT JOIN `'._DB_PREFIX_.'hook` h ON (h.`id_hook` = hm.`id_hook`) 
		WHERE h.`name` = \''.pSQL($hook).'\'
		AND hm.`id_module` = '.(int)($this->id)
		);
	}

	/*
	** Template management (display, overload, cache)
	*/
	protected static function _isTemplateOverloadedStatic($moduleName, $template)
	{
		if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$moduleName.'/'.$template))
			return true;
		elseif (Tools::file_exists_cache(_PS_MODULE_DIR_.$moduleName.'/'.$template))
			return false;
		return NULL;
	}

	protected function _isTemplateOverloaded($template)
	{
		return self::_isTemplateOverloadedStatic($this->name, $template);
	}

	public static function display($file, $template, $cacheId = NULL, $compileId = NULL)
	{
		global $smarty;

		if (_PS_FORCE_SMARTY_2_) /* Keep a backward compatibility for Smarty v2 */
		{
			$previousTemplate = $smarty->currentTemplate;
			$smarty->currentTemplate = substr(basename($template), 0, -4);
		}
		$smarty->assign('module_dir', __PS_BASE_URI__.'modules/'.basename($file, '.php').'/');
		if (($overloaded = self::_isTemplateOverloadedStatic(basename($file, '.php'), $template)) === NULL)
			$result = Tools::displayError('No template found');
		else
		{
			$smarty->assign('module_template_dir', ($overloaded ? _THEME_DIR_ : __PS_BASE_URI__).'modules/'.basename($file, '.php').'/');
			$result = $smarty->fetch(($overloaded ? _PS_THEME_DIR_.'modules/'.basename($file, '.php') : _PS_MODULE_DIR_.basename($file, '.php')).'/'.$template, $cacheId, $compileId);
		}
		if (_PS_FORCE_SMARTY_2_) /* Keep a backward compatibility for Smarty v2 */
			$smarty->currentTemplate = $previousTemplate;
		return $result;
	}

	protected function _getApplicableTemplateDir($template)
	{
		return $this->_isTemplateOverloaded($template) ? _PS_THEME_DIR_ : _PS_MODULE_DIR_.$this->name.'/';
	}

	public function isCached($template, $cacheId = NULL, $compileId = NULL)
	{
		global $smarty;

		/* Use Smarty 3 API calls */
		if (!_PS_FORCE_SMARTY_2_) /* PHP version > 5.1.2 */
			return $smarty->isCached($this->_getApplicableTemplateDir($template).$template, $cacheId, $compileId);
		/* or keep a backward compatibility if PHP version < 5.1.2 */
		else
			return $smarty->is_cached($this->_getApplicableTemplateDir($template).$template, $cacheId, $compileId);
	}

	protected function _clearCache($template, $cacheId = NULL, $compileId = NULL)
	{
		global $smarty;

		/* Use Smarty 3 API calls */
		if (!_PS_FORCE_SMARTY_2_) /* PHP version > 5.1.2 */
			return $smarty->clear_cache($template ? $this->_getApplicableTemplateDir($template).$template : NULL, $cacheId, $compileId);
		/* or keep a backward compatibility if PHP version < 5.1.2 */
		else
			return $smarty->clearCache($template ? $this->_getApplicableTemplateDir($template).$template : NULL, $cacheId, $compileId);
	}
}
