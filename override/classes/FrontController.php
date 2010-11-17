<?php

class FrontController extends FrontControllerCore
{
	private function getTotalColor($n)
	{
		if ($n > 150)
			return 'style="color:red"';
		if ($n > 100)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	private function getTimeColor($n)
	{
		if ($n > 4)
			return 'style="color:red"';
		if ($n > 2)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	private function getQueryColor($n)
	{
		if ($n > 5)
			return 'style="color:red"';
		if ($n > 2)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	private function getTableColor($n)
	{
		if ($n > 30)
			return 'style="color:red"';
		if ($n > 20)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	public function displayFooter()
	{
		parent::displayFooter();
		
		echo '<br /><br />
		<h3><big>'.round(memory_get_usage()/1048576, 1).' Mb</big> of RAM used for this request</h3>
		<br /><br />';
		
		
		echo '<br /><br />
		<h1 '.$this->getTotalColor(Db::getInstance()->count).'>'.Db::getInstance()->count.' queries</h1>
		<br /><br />
		<h3><a href="#stopwatch">Go to Stopwatch</a></h3>
		<h3><a href="#doubles">Go to Doubles</a></h3>
		<h3><a href="#tables">Go to Tables</a></h3>
		<br /><br />
		<h3><a name="stopwatch">Stopwatch (with SQL_NO_CACHE)</a></h3>
		<br /><br />
		<div style="text-align:left">';
		$queries = Db::getInstance()->queriesTime;
		arsort($queries);
		foreach ($queries as $q => $time)
			echo '<hr /><b '.$this->getTimeColor($time * 1000).'>'.round($time * 1000, 3).' ms</b> '.$q;
		echo '</div>
		<br /><br />
		<h3><a name="doubles">Doubles (IDs replaced by "XX")</a></h3>
		<br /><br />
		<div style="text-align:left">';
		$queries = Db::getInstance()->queries;
		arsort($queries);
		foreach ($queries as $q => $nb)
			echo '<hr /><b '.$this->getQueryColor($nb).'>'.$nb.'</b> '.$q;
		echo '</div>
		<br /><br />
		<h3><a name="tables">Tables stress</a></h3>
		<br /><br />
		<div style="text-align:left">';
		$tables = Db::getInstance()->tables;
		arsort($tables);
		foreach ($tables as $table => $nb)
			echo '<hr /><b '.$this->getTableColor($nb).'>'.$nb.'</b> '.$table;
		echo '</div>
		<br /><br />';
	}
}