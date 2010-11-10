<?php

abstract class CacheCore {
	
	private static $_instance;
	protected $_keysCached;
	protected $_tablesCached = array();
	
	public static function getInstance()
	{	
		if(!isset(self::$_instance))
		{
			$caching_system =  _PS_CACHING_SYSTEM_;
			self::$_instance = new $caching_system();
		}
		return self::$_instance;
	}
	
	protected function __construct()
	{
	}
	
	protected function __destruct()
	{
	}

	abstract public function get($key);
	abstract public function delete($key, $timeout = 0);
	abstract public function set($key, $value, $expire = 0);
	abstract public function flush();
	abstract public function setQuery($query, $result);
	abstract public function deleteQuery($query);
	
}
