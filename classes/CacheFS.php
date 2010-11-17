<?php

class CacheFSCore extends Cache {
	
	private $_depth;
	
	protected function __construct()
	{
		parent::__construct();
		return $this->_init();
	}
	
	private function _init()
	{
		$this->_depth = Configuration::get('PS_CACHEFS_DIRECTORY_DEPTH');	
		return $this->_setKeys();
	}

	public function set($key, $value, $expire = 0)
	{
		$path = _PS_CACHEFS_DIRECTORY_;
		for ($i = 0; $i < $this->_depth; $i++)
			$path.=$key[$i].'/';
		if(file_put_contents($path.$key, serialize($value)))
		{
			$this->_keysCached[$key] = true;
			return $key;
		}
		return false;
	}

	public function get($key)
	{
		if (!isset($this->_keysCached[$key]))
			return false;
		
		$path = _PS_CACHEFS_DIRECTORY_;
		for ($i = 0; $i < $this->_depth; $i++)
			$path.=$key[$i].'/';
		
		$file = file_get_contents($path.$key);
		return unserialize($file);
	}

	private function _setKeys()
	{
		if (file_exists(_PS_CACHEFS_DIRECTORY_.'keysCached'))
		{
			$file = file_get_contents(_PS_CACHEFS_DIRECTORY_.'keysCached');
			$this->_keysCached =	unserialize($file);
		}
		if (file_exists(_PS_CACHEFS_DIRECTORY_.'tablesCached'))
		{
			$file = file_get_contents(_PS_CACHEFS_DIRECTORY_.'tablesCached');
			$this->_tablesCached = unserialize($file);
		}
		return true;
	}

	public function setQuery($query, $result)
	{
		if (isset($this->_keysCached[md5($query)]))
			return true;
		$key = $this->set(md5($query), $result);
		if(preg_match_all('/('._DB_PREFIX_.'[a-z_-]*)`?'."\s".'/Ui', $query, $res))
			foreach($res[1] AS $table)
				if(!isset($this->_tablesCached[$table][$key]))
					$this->_tablesCached[$table][$key] = true;
	}

	public function delete($key, $timeout = 0)
	{
		$path = _PS_CACHEFS_DIRECTORY_;
		for ($i = 0; $i < $this->_depth; $i++)
			$path.=$key[$i].'/';
		if (!unlink($path.$key))
			return false;
		unset($this->_keysCached[$key]);
		return true;
	}

	public function deleteQuery($query)
	{

		if(preg_match_all('/('._DB_PREFIX_.'[a-z_-]*)`?'."\s".'/Ui', $query, $res))
			foreach ($res[1] AS $table)
				if (isset($this->_tablesCached[$table]))
				{
					foreach ($this->_tablesCached[$table] AS $fsKey => $foo)
						$this->delete($fsKey);
					unset($this->_tablesCached[$table]);
				}
	}

	public function flush()
	{
	}

	public function __destruct()
	{
		parent::__destruct();
		file_put_contents(_PS_CACHEFS_DIRECTORY_.'keysCached', serialize($this->_keysCached));
		file_put_contents(_PS_CACHEFS_DIRECTORY_.'tablesCached', serialize($this->_tablesCached));
	}

	public static function deleteCacheDirectory()
	{
		Tools::deleteDirectory(_PS_CACHEFS_DIRECTORY_, false);
	}

	public static function createCacheDirectories($level_depth, $directory = false)
	{
		if (!$directory)
			$directory = _PS_CACHEFS_DIRECTORY_;
		$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
		for ($i = 0; $i < strlen($chars); $i++)
		{
			$new_dir = $directory.$chars[$i].'/';
			if (mkdir($new_dir))
				if (chmod($new_dir, 0777))
					if ($level_depth - 1 > 0)
						self::createCacheDirectories($level_depth - 1, $new_dir);
		}
	}
}
