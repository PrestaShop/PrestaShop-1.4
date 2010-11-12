<?php

class MySQL extends MySQLCore
{
	public $count = 0;
	public $queries = array();
	public $queriesTime = array();
	public $tables = array();
	
	private function disableCache($query)
	{
		return preg_replace('/^select /', 'SELECT SQL_NO_CACHE', trim($query));
	}

	public function	getRow($query, $use_cache = 1)
	{
		$this->count++;
		$query2 = preg_replace('/[0-9]+/', 'XX', $query);
		if (!isset($this->queries[$query2]))
			$this->queries[$query2] = 0;
		$this->queries[$query2]++;
		preg_match_all('/(from|join)\s+`?'.preg_replace('/[0-9]+/', 'XX', _DB_PREFIX_).'([a-z0-9_-]+)/ui', $query2, $matches);
		foreach ($matches[2] as $table)
		{
			if (!isset($this->tables[$table]))
				$this->tables[$table] = 0;
			$this->tables[$table]++;
		}
		
		$query = $this->disableCache($query);
		$t0 = microtime(true);
		
		$return = parent::getRow($query, $use_cache);

		if (!isset($this->queriesTime[$query]))
			$this->queriesTime[$query] = microtime(true)-$t0;
			
		return $return;
	}

	public function	getValue($query, $use_cache = 1)
	{
		$this->count++;
		$query2 = preg_replace('/[0-9]+/', 'XX', $query);
		if (!isset($this->queries[$query2]))
			$this->queries[$query2] = 0;
		$this->queries[$query2]++;
		preg_match_all('/(from|join)\s+`?'.preg_replace('/[0-9]+/', 'XX', _DB_PREFIX_).'([a-z0-9_-]+)/ui', $query2, $matches);
		foreach ($matches[2] as $table)
		{
			if (!isset($this->tables[$table]))
				$this->tables[$table] = 0;
			$this->tables[$table]++;
		}
		
		$query = $this->disableCache($query);
		$t0 = microtime(true);
		
		$return = parent::getValue($query, $use_cache);

		if (!isset($this->queriesTime[$query]))
			$this->queriesTime[$query] = microtime(true)-$t0;
			
		return $return;
	}
	
	public function	Execute($query, $use_cache = 1)
	{
		$this->count++;
		$query2 = preg_replace('/[0-9]+/', 'XX', $query);
		if (!isset($this->queries[$query2]))
			$this->queries[$query2] = 0;
		$this->queries[$query2]++;
		preg_match_all('/(from|join)\s+`?'.preg_replace('/[0-9]+/', 'XX', _DB_PREFIX_).'([a-z0-9_-]+)/ui', $query2, $matches);
		foreach ($matches[2] as $table)
		{
			if (!isset($this->tables[$table]))
				$this->tables[$table] = 0;
			$this->tables[$table]++;
		}
		
		$query = $this->disableCache($query);
		$t0 = microtime(true);
		
		$return = parent::Execute($query, $use_cache);

		if (!isset($this->queriesTime[$query]))
			$this->queriesTime[$query] = microtime(true)-$t0;
			
		return $return;
	}
	
	public function	ExecuteS($query, $array = true, $use_cache = 1)
	{
		$this->count++;
		$query2 = preg_replace('/[0-9]+/', 'XX', $query);
		if (!isset($this->queries[$query2]))
			$this->queries[$query2] = 0;
		$this->queries[$query2]++;
		preg_match_all('/(from|join)\s+`?'.preg_replace('/[0-9]+/', 'XX', _DB_PREFIX_).'([a-z0-9_-]+)/ui', $query2, $matches);
		foreach ($matches[2] as $table)
		{
			if (!isset($this->tables[$table]))
				$this->tables[$table] = 0;
			$this->tables[$table]++;
		}
		
		$query = $this->disableCache($query);
		$t0 = microtime(true);
		
		$return = parent::ExecuteS($query, $array, $use_cache);

		if (!isset($this->queriesTime[$query]))
			$this->queriesTime[$query] = microtime(true)-$t0;
			
		return $return;
	}

	protected function q($query, $use_cache = 1)
	{
		$this->count++;
		$query2 = preg_replace('/[0-9]+/', 'XX', $query);
		if (!isset($this->queries[$query2]))
			$this->queries[$query2] = 0;
		$this->queries[$query2]++;
		preg_match_all('/(from|join)\s+`?'.preg_replace('/[0-9]+/', 'XX', _DB_PREFIX_).'([a-z0-9_-]+)/ui', $query2, $matches);
		foreach ($matches[2] as $table)
		{
			if (!isset($this->tables[$table]))
				$this->tables[$table] = 0;
			$this->tables[$table]++;
		}
		
		$query = $this->disableCache($query);
		$t0 = microtime(true);
		
		$return = parent::q($query, $use_cache);

		if (!isset($this->queriesTime[$query]))
			$this->queriesTime[$query] = microtime(true)-$t0;
			
		return $return;
	}
}
