<?php

class Memcached extends Cache {
	
	private $_memcacheObj;
	private $_isConnected = false;

	protected function __construct()
	{
		parent::__construct();
		return $this->connect();
	}

	public function connect()
	{
		$this->_memcacheObj = new Memcache();
		$servers = self::getMemcachedServers();
		if (!$servers)
			return false;
		foreach ($servers	AS $server)
			$this->_memcacheObj->addServer($server['ip'], $server['port'], $server['weight']);

		$this->_isConnected = true;
		return $this->_setKeys();
	}

	public function set($key, $value, $expire = 0)
	{
		if (!$this->_isConnected)
			return false;
		if ($this->_memcacheObj->set($key, $value, 0, $expire))
		{
			$this->_keysCached[$key] = true;
			return $key;
		}
	}

	public function get($key)
	{
		if (!isset($this->_keysCached[$key]))
			return false;
		return $this->_memcacheObj->get($key);
	}
	
	private function _setKeys()
	{
		if (!$this->_isConnected)
			return false;
		$this->_keysCached =	$this->_memcacheObj->get('keysCached');
		$this->_tablesCached = $this->_memcacheObj->get('tablesCached');
		
		return true;
	}
	
	public function setQuery($query, $result)
	{
		if (!$this->_isConnected)
			return false;
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
		if (!$this->_isConnected)
			return false;
		if ($this->_memcacheObj->delete($key, $timeout))
			unset($this->_keysCached[$key]);
	}

	public function deleteQuery($query)
	{
		if (!$this->_isConnected)
			return false;
		if(preg_match_all('/('._DB_PREFIX_.'[a-z_-]*)`?'."\s".'/Ui', $query, $res))
			foreach ($res[1] AS $table)
				if (isset($this->_tablesCached[$table]))
				{
					foreach ($this->_tablesCached[$table] AS $memcachedKey => $foo)
						$this->delete($memcachedKey);
					unset($this->_tablesCached[$table]);
				}
	}

	protected function close()
	{
		if (!$this->_isConnected)
			return false;
		return $this->_memcacheObj->close();
	}

	public function flush()
	{
		if(!$this->_isConnected)
			return false;
		if ($this->_memcacheObj->flush())
			return $this->_setKeys();
		return false;
	}

	public function __destruct()
	{
		parent::__destruct();
		if (!$this->_isConnected)
			return false;
		$this->_memcacheObj->set('keysCached', $this->_keysCached, 0, 0);
		$this->_memcacheObj->set('tablesCached', $this->_tablesCached, 0, 0);
		$this->close();
	}

	public static function addServer($ip, $port, $weight)
	{
		return Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'memcached_servers (id_memcached_server, ip, port, weight)
																							VALUES(\'\', \''.pSQL($ip).'\', '.(int)$port.', '.(int)$weight.')', false);
	}

	public static function getMemcachedServers()
	{
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT *
																					FROM '._DB_PREFIX_.'memcached_servers', true, false);
	}

	public static function deleteServer($id_server)
	{
		return Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'memcached_servers WHERE id_memcached_server='.(int)$id_server);
	}
}
