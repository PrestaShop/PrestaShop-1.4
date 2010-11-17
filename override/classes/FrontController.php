<?php

class FrontController extends FrontControllerCore
{
	public $memory = array();
	
	private function displayMemoryColor($n)
	{
		$n /= 1048576;
		if ($n > 3)
			return '<span style="color:red">'.round($n, 2).' Mb</span>';
		if ($n > 1)
			return '<span style="color:orange">'.round($n, 2).' Mb</span>';
		return '<span style="color:green">'.round($n, 2).' Mb</span>';
	}
	
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
	
	public function __construct()
	{
		$this->_memory[-2] = memory_get_usage();
		parent::__construct();
		$this->_memory[-1] = memory_get_usage();
	}
	
	public function run()
	{
		$this->_memory[0] = memory_get_usage();
		$this->preProcess();
		$this->_memory[1] = memory_get_usage();
		$this->setMedia();
		$this->_memory[2] = memory_get_usage();
		$this->displayHeader();
		$this->_memory[3] = memory_get_usage();
		$this->process();
		$this->_memory[4] = memory_get_usage();
		$this->displayContent();
		$this->_memory[5] = memory_get_usage();
		$this->displayFooter();
	}
	
	
	public function displayFooter()
	{
		parent::displayFooter();
		
		$this->_memory[6] = memory_get_usage();
		
		echo '<br /><br />
		<h3>
			<big>'.$this->displayMemoryColor($this->_memory[6]).'</big> of RAM used for this page<br />
			Config: '.$this->displayMemoryColor($this->_memory[-1]).' Mb<br />
			Constructor: '.$this->displayMemoryColor(($this->_memory[-1] - $this->_memory[-2])).' Mb<br />
			preProcess: '.$this->displayMemoryColor(($this->_memory[1] - $this->_memory[0])).' Mb<br />
			setMedia: '.$this->displayMemoryColor(($this->_memory[2] - $this->_memory[1])).' Mb<br />
			displayHeader: '.$this->displayMemoryColor(($this->_memory[3] - $this->_memory[2])).' Mb<br />
			process: '.$this->displayMemoryColor(($this->_memory[4] - $this->_memory[3])).' Mb<br />
			displayContent: '.$this->displayMemoryColor(($this->_memory[5] - $this->_memory[4])).' Mb<br />
			displayFooter: '.$this->displayMemoryColor(($this->_memory[6] - $this->_memory[5])).' Mb
		</h3>
		<br /><br />';
		
		$countByTypes = '';
		foreach (Db::getInstance()->countTypes as $type => $count)
			if ($count)
				$countByTypes .= $count.' x '.$type.' | ';
		$countByTypes = rtrim($countByTypes, ' |');
			
		echo '
		<h1 '.$this->getTotalColor(Db::getInstance()->count).'>'.Db::getInstance()->count.' queries<br /><span style="font-size:0.6em">('.$countByTypes.')</span></h1>
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