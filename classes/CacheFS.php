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

class CacheFSCore extends Cache {

	protected $_depth;
	protected $_prefix;

	protected function __construct()
	{
		parent::__construct();
		$this->_init();
		$this->_prefix = _COOKIE_IV_.'_';
	}

	protected function _init()
	{
		$this->_depth = Configuration::get('PS_CACHEFS_DIRECTORY_DEPTH');
	}

	public function set($key, $value, $expire = 0)
	{
		if ($this->_add($this->_getPath($key), $value, $expire = 0))
			return $key;
		return false;
	}

	protected function _add($path, $value, $expire = 0)
	{
		return (!file_exists($path) && (bool)@file_put_contents($path, serialize($value)));
	}

	public function get($query)
	{
		$key = $this->getKey($query);
		return $this->_get($this->_getPath($key));
	}

	protected function _get($path)
	{
		if (file_exists($path))
		{
			$file = file_get_contents($path);
			if ($file !== false)
				return unserialize($file);
		}
		return false;
	}

	protected function _getPath($key)
	{
		$path = _PS_CACHEFS_DIRECTORY_;
		for ($i = 0; $i < $this->_depth; $i++)
			$path .= $key[$i].DIRECTORY_SEPARATOR;
		if (!file_exists($path))
			$path = _PS_CACHEFS_DIRECTORY_.DIRECTORY_SEPARATOR;
		return $path.$key;
	}

	public function setNumRows($query, $value, $expire = 0)
	{
		$key = $this->getKey($query);
		$return = $this->set($key.'_nrows', $value, $expire);
		return $return;
	}

	public function getNumRows($query)
	{
		$key = $this->getKey($query);
		return $this->get($key.'_nrows');
	}

	public function setQuery($query, $result)
	{
		if ($this->isBlacklist($query))
			return true;
		$key = $this->getKey($query);
		$key = $this->set($key, $result);
		return $key;
	}

	public function delete($key, $timeout = 0)
	{
		$path = $this->_getPath($key);
		if (!file_exists($path))
			return true;
		if (!unlink($path))
			return false;
		return true;
	}

	public function deleteQuery($query)
	{
		$tables = $this->getTables($query);
		if (is_array($tables))
			foreach($tables AS $table)
			{
				$this->invalidateNamespace($table);
				$this->delete(md5($this->_prefix.$table));
			}
	}

	protected function _increment($key)
	{
		$count = $this->_get($this->_getPath($key));
		if ($count !== false)
			return (int)((int)$count + 1);
		return false;
	}

	protected function invalidateNamespace($table)
	{
		$key = md5($this->_prefix.$table);
		if (!$this->_increment($key))
			$this->_add($this->_getPath($key), time());
	}

	protected function getTableNamespacePrefix($table)
	{
		$key = md5($this->_prefix.$table);
		$path = $this->_getPath($key);
		$namespace = $this->_get($path);
		if (!$namespace)
		{
			$namespace = time();
			if ($this->_add($path, $namespace))
				$this->_get($path); //Lost the race. Need to refetch namespace
		}
		return $namespace;
	}

	public function flush()
	{
	}

	public static function deleteCacheDirectory()
	{
		Tools::deleteDirectory(_PS_CACHEFS_DIRECTORY_, false);
	}

	public static function createCacheDirectories($level_depth, $directory = false)
	{
		if (!$directory)
			$directory = _PS_CACHEFS_DIRECTORY_;
		$chars = '0123456789abcdef';
		for ($i = 0; $i < strlen($chars); $i++)
		{
			$new_dir = $directory.$chars[$i].DIRECTORY_SEPARATOR;
			if (mkdir($new_dir))
				if (@chmod($new_dir, 0775))
					if ($level_depth - 1 > 0)
						self::createCacheDirectories($level_depth - 1, $new_dir);
		}
	}
}