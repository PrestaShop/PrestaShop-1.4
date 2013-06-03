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

class MCachedCore extends Cache
{
	protected $_memcacheObj;
	protected $_isConnected = false;
	protected $_prefix;

	protected function __construct()
	{
		parent::__construct();
		$this->connect();
		$this->_prefix = _COOKIE_IV_.'_';
	}
	public function __destruct()
	{
		return $this->close();
	}
	
	public function connect()
	{
		if (class_exists('Memcache') && extension_loaded('memcache'))		
			$this->_memcacheObj = new Memcache();
		else
			return false;
		
		$servers = self::getMemcachedServers();
		if (!$servers)
			return false;
		foreach ($servers as $server)
			$this->_memcacheObj->addServer($server['ip'], $server['port'], $server['weight']);

		$this->_isConnected = true;
	}
	
	private function memcacheGet($key)
	{
		return $this->_memcacheObj->get($key);
	}	

	public function set($key, $value, $expire = 0)
	{
		if (!$this->_isConnected)
			return false;
		$this->_memcacheObj->add($key, $value, 0, $expire);
	}
	
	public function get($query)
	{
		$key = $this->getKey($query);
		return $this->memcacheGet($key);		
	}	
	
	public function setNumRows($query, $value, $expire = 0)
	{
		$key = $this->getKey($query);	
		return $this->set($key.'_nrows', $value, $expire);
	}
	
	public function getNumRows($query)
	{
		$key = $this->getKey($query);
		return $this->memcacheGet($key.'_nrows');		
	}	

	public function setQuery($query, $result)
	{
		if (!$this->_isConnected)
			return false;
		
		if ($this->isBlacklist($query))
			return true;
		
		$key = $this->getKey($query);
		$key = $this->set($key, $result);
	}
	
	public function delete($key, $timeout = 0)
	{
		if (!$this->_isConnected)
			return false;
		if (!empty($key) AND $this->_memcacheObj->delete($key, $timeout))
			return true;
	}

	public function deleteQuery($query)
	{
		if (!$this->_isConnected)
			return false;
		$tables = $this->getTables($query);
		foreach($tables AS $table)
			$this->invalidateNamespace($table);
	}
	
	private function getKey($query)
	{
		$key = '';
		$tables = $this->getTables($query);
		if (is_array($tables))
			foreach($tables AS $table)
				$key .= $this->getTableNamespacePrefix($table);
		else
			$key .= 'nok'.$tables;

		$key .= $query;
		return md5($key);
	}
	
	private function invalidateNamespace($table)
	{
		$key = $this->_prefix.$table;
		if (!$this->_memcacheObj->increment($key))
			$this->_memcacheObj->add($key, time());
	}

	private function getTableNamespacePrefix($table)
	{
		$key = $this->_prefix.$table;
		$namespace = $this->_memcacheObj->get($key);
		if (!$namespace)
		{
			$namespace = time();
			if ($this->_memcacheObj->add($key, $namespace))
				$this->_memcacheObj->get($key); //Lost the race. Need to refetch namespace
		}
		return $namespace;
	}

	private function getTables($query)
	{
		if (preg_match_all('/('._DB_PREFIX_.'[a-z_-]*)`?.*/im', $query, $res))
		{
			return $res[1];
		}
		return false;
	}
	
	public function checkQuery($query)
	{
		if (preg_match('/INSERT|UPDATE|DELETE|DROP|REPLACE/im', $query, $qtype))
			$this->deleteQuery($query);
	}

	protected function close()
	{
		if (!$this->_isConnected)
			return false;
		return $this->_memcacheObj->close();
	}

	public function flush()
	{
		if (!$this->_isConnected)
			return false;
		if ($this->_memcacheObj->flush())
			return true;
		return false;
	}
	
	public static function addServer($ip, $port, $weight)
	{
		return Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'memcached_servers (ip, port, weight) VALUES(\''.pSQL($ip).'\', '.(int)$port.', '.(int)$weight.')', false);
	}

	public static function getMemcachedServers()
	{
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM '._DB_PREFIX_.'memcached_servers', true, false);
	}

	public static function deleteServer($id_server)
	{
		return Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'memcached_servers WHERE id_memcached_server='.(int)$id_server);
	}
}