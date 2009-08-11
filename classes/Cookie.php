<?php

/**
  * Cookie class, Cookie.php
  * Cookies management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class	Cookie
{
	/** @var array Contain cookie content in a key => value format */
	private $_content;

	/** @var array Crypted cookie name for setcookie() */
	private $_name;

	/** @var array expiration date for setcookie() */
	private $_expire;

	/** @var array Website domain for setcookie() */
	private $_domain;

	/** @var array Path for setcookie() */
	private $_path;

	/** @var array Blowfish instance */
	private $_bf;

	/** @var array 56 chars Blowfish initialization key */
	private $_key;

	/** @var array 8 chars Blowfish initilization vector */
	private $_iv;

	/**
	  * Get data if the cookie exists and else initialize an new one
	  *
	  * @param $name Cookie name before encrypting
	  * @param $path
	  */
	function __construct($name, $path = '', $expire = NULL)
	{
		$this->_content = array();
		$this->_expire = isset($expire) ? intval($expire) : (time() + 1728000);
		$this->_name = md5($name._COOKIE_KEY_);
		$this->_path = trim(__PS_BASE_URI__.$path, '/\\').'/';
		if ($this->_path{0} != '/') $this->_path = '/'.$this->_path;
		$this->_path = rawurlencode($this->_path);
		$this->_path = str_replace('%2F', '/', $this->_path);
		$this->_path = str_replace('%7E', '~', $this->_path);
		$this->_key = _COOKIE_KEY_;
		$this->_iv = _COOKIE_IV_;
		$this->_domain = $this->getDomain();
		$this->_bf = new Blowfish($this->_key, $this->_iv);
		$this->update();
	}

	private function getDomain()
	{
		$r = '!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';
	    preg_match ($r, $_SERVER['HTTP_HOST'], $out);
		if (preg_match('/^(((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]|[1-9]).)'. 
         '{1}((25[0-5]|2[0-4][0-9]|[1]{1}[0-9]{2}|[1-9]{1}[0-9]|[0-9]).)'. 
         '{2}((25[0-5]|2[0-4][0-9]|[1]{1}[0-9]{2}|[1-9]{1}[0-9]|[0-9]){1}))$/', $out[4]))
			return false;
		if (!strstr($_SERVER['HTTP_HOST'], '.'))
			return false;
		$domain = $out[4];
		$subDomains = SubDomain::getSubDomains();
		if ($subDomains === false)
			die(Tools::displayError('Bad SubDomain SQL query!'));
		foreach ($subDomains AS $subDomain)
		{
			$subDomainLength = strlen($subDomain) + 1;
			if (strncmp($subDomain.'.', $domain, $subDomainLength) == 0)
				$domain = substr($domain, $subDomainLength);
		}
		return $domain;
	}

	/**
	  * Set expiration date
	  *
	  * @param integer $expire Expiration time from now
	  */
	function setExpire($expire)
	{
		$this->_expire = intval($expire);
	}

	/**
	  * Magic method wich return cookie data from _content array
	  *
	  * @param $key key wanted
	  * @return string value corresponding to the key
	  */
	function __get($key)
	{
		return isset($this->_content[$key]) ? $this->_content[$key] : false;
	}

	/**
	  * Magic method wich check if key exists in the cookie
	  *
	  * @param $key key wanted
	  * @return boolean key existence
	  */
	function __isset($key)
	{
		return isset($this->_content[$key]);
	}

	/**
	  * Magic method wich add data into _content array
	  *
	  * @param $key key desired
	  * @param $value value corresponding to the key
	  */
	function __set($key, $value)
	{
		if (is_array($value))
			die(Tools::displayError());
		if (preg_match('/¤|\|/', $key) OR preg_match('/¤|\|/', $value))
			throw new Exception('Forbidden chars in cookie');
		$this->_content[$key] = $value;
		$this->write();
	}

	/**
	  * Magic method wich delete data into _content array
	  *
	  * @param $key key wanted
	  */
	function __unset($key)
	{
		unset($this->_content[$key]);
		$this->write();
	}

	/**
	  * Check customer informations saved into cookie and return customer validity
	  *
	  * @return boolean customer validity
	  */
	function isLogged()
	{
		/* Customer is valid only if it can be load and if cookie password is the same as database one */
	 	if ($this->logged == 1 AND $this->id_customer AND Validate::isUnsignedId($this->id_customer) AND Customer::checkPassword(intval($this->id_customer), $this->passwd))
        	return true;
        return false;
	}

	/**
	  * Check employee informations saved into cookie and return employee validity
	  *
	  * @return boolean employee validity
	  */
	function isLoggedBack()
	{
		/* Employee is valid only if it can be load and if cookie password is the same as database one */
	 	if ($this->id_employee AND Validate::isUnsignedId($this->id_employee) AND Employee::checkPassword(intval($this->id_employee), $this->passwd))
			return true;
		return false;
	}

	/**
	  * Delete cookie
	  */
	function logout()
	{
		$this->_content = array();
		$this->_setcookie();
		unset($_COOKIE[$this->_name]);
	}

	/**
	  * Soft logout, delete everything links to the customer
	  * but leave there affiliate's informations
	  */
	function mylogout()
	{
		unset($this->_content['id_customer']);
		unset($this->_content['id_guest']);
		unset($this->_content['id_connections']);
		unset($this->_content['customer_lastname']);
		unset($this->_content['customer_firstname']);
		unset($this->_content['passwd']);
		unset($this->_content['logged']);
		unset($this->_content['email']);
		unset($this->_content['id_cart']);
		unset($this->_content['id_address_invoice']);
		unset($this->_content['id_address_delivery']);
		$this->write();
	}
	
	function makeNewLog()
	{
		unset($this->_content['id_customer']);
		unset($this->_content['id_guest']);
		Guest::setNewGuest($this);
	}

	/**
	  * Get cookie content
	  */
	function update($nullValues = false)
	{
		if (isset($_COOKIE[$this->_name]))
		{
			/* Decrypt cookie content */
			$content = $this->_bf->decrypt($_COOKIE[$this->_name]);

			/* Get cookie checksum */
			$checksum = crc32($this->_iv.substr($content, 0, strrpos($content, '¤') + 2));

			/* Unserialize cookie content */
			$tmpTab = explode('¤', $content);

			foreach ($tmpTab AS $keyAndValue)
			{
				$tmpTab2 = explode('|', $keyAndValue);
				if (sizeof($tmpTab2) == 2)
					 $this->_content[$tmpTab2[0]] = $tmpTab2[1];
			 }

			/* Blowfish fix */
			if (isset($this->_content['checksum']))
				$this->_content['checksum'] = intval($this->_content['checksum']);

			/* Check if cookie has not been modified */
			if (!isset($this->_content['checksum']) OR $this->_content['checksum'] != $checksum)
				$this->logout();
		}
		else
			$this->_content['date_add'] = date('Y-m-d H:i:s');
	}

	/**
	  * Setcookie according to php version
	  */
	private function _setcookie($cookie = NULL)
	{
		if ($cookie)
		{
			$content = $this->_bf->encrypt($cookie);
			$time = $this->_expire;
		}
		else
		{
			$content = 0;
			$time = time() - 1;
		}

		if (version_compare(substr(phpversion(), 0, 3), '5.2.0') == -1)
			return setcookie($this->_name, $content, $time, $this->_path, $this->_domain, 0);
		else
			return setcookie($this->_name, $content, $time, $this->_path, $this->_domain, 0, true);
	}

	/**
	  * Save cookie with setcookie()
	  */
	function write()
	{
		$cookie = '';

		/* Serialize cookie content */
		if (isset($this->_content['checksum'])) unset($this->_content['checksum']);
		foreach ($this->_content AS $key => $value)
			$cookie .= $key.'|'.$value.'¤';

		/* Add checksum to cookie */
		$cookie .= 'checksum|'.crc32($this->_iv.$cookie);

		/* Cookies are encrypted for evident security reasons */
		return $this->_setcookie($cookie);
	}

	/**
	 * Get a family of variables (e.g. "filter_")
	 */
	public function getFamily($origin)
	{
		$result = array();
		if (count($this->_content) == 0)
			return $result;
		foreach ($this->_content AS $key => $value)
			if (strncmp($key, $origin, strlen($origin)) == 0)
				$result[$key] = $value;
		return $result;
	}

	/**
	 *
	 */
	public function unsetFamily($origin)
	{
		$family = $this->getFamily($origin);
		foreach ($family AS $member => $value)
			unset($this->$member);
	}

}

?>