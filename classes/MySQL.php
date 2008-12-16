<?php

/**
  * MySQL class, MySQL.php
  * MySQLs management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

class MySQL extends Db
{
	public function	connect()
	{
		if ($this->_link = @mysql_connect($this->_server, $this->_user, $this->_password))
		{
			if(!mysql_select_db($this->_database, $this->_link))
				die(Tools::displayError('The database selection cannot be made.'));
		}
		else
			die(Tools::displayError('Link to database cannot be established.'));
		/* UTF-8 support */
		if (!mysql_query('SET NAMES \'utf8\'', $this->_link))
			die(Tools::displayError('PrestaShop Fatal error: no utf-8 support. Please check your server configuration.'));
		/* Disable some MySQL limitations */
		mysql_query('SET GLOBAL SQL_MODE=\'\'', $this->_link);
		return $this->_link;
	}
	
	public function	disconnect()
	{
		if ($this->_link)
			mysql_close($this->_link);
		$this->_link = false;
	}
	
	public function	getRow($query)
	{
		if (parent::blacklist($query))
			return false;
		$this->_result = false;
		if ($this->_link)
			if ($this->_result = mysql_query($query.' LIMIT 1', $this->_link))
				return mysql_fetch_assoc($this->_result);
		return false;
	}
	
	public function	Execute($query)
	{
		if (parent::blacklist($query))
			return false;
		$this->_result = false;
		if ($this->_link)
		{
			$this->_result = mysql_query($query, $this->_link);
			return $this->_result;
		}				
		return false;
	}
	
	public function	ExecuteS($query)
	{
		if (parent::blacklist($query))
			return false;
		$this->_result = false;
		if ($this->_link)
			if ($this->_result = mysql_query($query, $this->_link))
			{
				$resultArray = array();
				while ($row = mysql_fetch_assoc($this->_result))
					$resultArray[] = $row;
				return $resultArray;
			}
		return false;
	}
	
	public function	delete($table, $where = false, $limit = false)
	{
		$this->_result = false;
		if ($this->_link)
			return mysql_query('DELETE FROM `'.pSQL($table).'`'.($where ? ' WHERE '.$where : '').($limit ? ' LIMIT '.intval($limit) : ''), $this->_link);
		return false;
	}
	
	public function	NumRows()
	{
		if ($this->_link AND $this->_result)
			return mysql_num_rows($this->_result);
	}
	
	public function	Insert_ID()
	{
		if ($this->_link)
			return mysql_insert_id($this->_link);
		return false;
	}
	
	public function	Affected_Rows()
	{
		if ($this->_link)
			return mysql_affected_rows($this->_link);
		return false;
	}

	protected function q($query)
	{
		if (parent::blacklist($query))
			return false;
		$this->_result = false;
		if ($this->_link)
			return mysql_query($query, $this->_link);
		return false;
	}
	
	/**
	 * Returns the text of the error message from previous MySQL operation
	 *
	 * @acces public
	 * @return string error
	 */
	public function getMsgError()
	{
		return mysql_error();
	}
	
	public function getNumberError()
	{
		return mysql_errno();
	}
	
	static public function tryToConnect($server, $user, $pwd, $db)
	{
		if (!$link = @mysql_connect($server, $user, $pwd))
			return 1;
		if (!@mysql_select_db($db, $link))
			return 2;
		return 0;
	}
}

?>
