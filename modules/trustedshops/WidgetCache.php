<?php

class WidgetCache
{
	private $_fileName;
	private $__ts_id;
	
	public function __construct($_fileName, $_ts_id)
	{
		$this->_fileName = $_fileName;
		$this->_ts_id = $_ts_id;
	}

	public function isFresh($timeout = 10800)
	{
		if (file_exists($this->_fileName))
			return ((mktime() - filemtime($this->_fileName)) < $timeout);
		else
			return false;
	}
	
	public function refresh()
	{
		return file_put_contents($this->_fileName, file_get_contents('https://www.trustedshops.com/bewertung/widget/widgets/'.$this->_ts_id.'.gif'));
	}
}

?>