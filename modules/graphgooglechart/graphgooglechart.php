<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  */
  
class GraphGoogleChart extends ModuleGraphEngine
{
	private $_width;
	private $_height;
	private $_values;
	private $_legend;
	private $_titles;
	
    function __construct($type = null)
    {
		if ($type != null)
		{
			parent::__construct($type);
		}
		else
		{
	        $this->name = 'graphgooglechart';
	        $this->tab = 'Stats Engines';
	        $this->version = 1.0;
			$this->page = basename(__FILE__, '.php');

	        Module::__construct();
			
	        $this->displayName = $this->l('Google Chart');
	        $this->description = $this->l('The Google Chart API lets you dynamically generate charts.');
		}
    }

	function install()
	{
		return (parent::install() AND $this->registerHook('GraphEngine'));
	}
    
	public static function hookGraphEngine($params, $drawer)
	{
		return '<img src="'.$drawer.'&width='.$params['width'].'&height='.$params['height'].'" />';
	}
	
	
	public function createValues($values)
	{		
		$this->_values = $values;
	}
	
	public function setSize($width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}
	
	public function setLegend($legend)
	{
		$this->_legend = $legend;
	}

	public function setTitles($titles)
	{
		$this->_titles = $titles;
	}
	
	private function getChbh($sizeof_values)
	{
		$chbh = 12;

		if ($sizeof_values < 25)
			$chbh += 4;
		if ($sizeof_values < 20)
			$chbh += 4;
		if ($sizeof_values < 15)
			$chbh += 8;
		if ($sizeof_values < 10)
			$chbh += 14;
		return ($chbh);
	}

	private function drawColumn($max_y)
	{
		$sizeof_values = sizeof($this->_values);
		$url = 'bvs&chxt=x,y&chxr=1,0,'.$max_y.'&chbh='.$this->getChbh($sizeof_values).'&chg=0,12.5&chxl=0:|';
		for ($i = 0; $i < $sizeof_values; $i++)
			$this->_values[$i] = ($this->_values[$i] * 100) / $max_y;
		return ($url);
	}
	
	private function drawLine($max_y)
	{
		return ('lc&chxt=x,y&chbh='.$this->getChbh(sizeof($this->_values)).'&chg=0,12.5&chxl=0:|');
	}

	private function drawPie()
	{
		return ('p3&chl=');
	}

	public function draw()
	{
		$url = 'http://chart.apis.google.com/chart?cht=';
		$legend = '';
		$values = '';
		$scale = '';

		switch ($this->_type)
		{
			case 'pie':
				$url .= $this->drawPie();
				break;
			case 'line':
				$url .= $this->drawLine($this->getYMax($this->_values));
			case 'column':
			default:
				$url .= $this->drawColumn($this->getYMax($this->_values));
				break;
		}

		foreach ($this->_legend as $label)
			$legend .= $label.'|';
		$url .= htmlentities(urlencode(html_entity_decode(rtrim($legend, '|'))));

		foreach ($this->_values as $label)
			$values .= ($label ? $label : '0').',';
		$url .= '&chd=t:'.urlencode(rtrim($values, ','));

		$url .= '&chs='.intval($this->_width).'x'.intval($this->_height);
		$url .= (isset($this->_titles['main'])) ? '&chtt='.urlencode($this->_titles['main']) : '';
		readfile($url);
	}
	
	private function getYMax($values)
	{
		$max = 0;
		foreach ($values as $k => $val)
			if ($val > $max)
				$max = $val;
		return ($max < 4) ? 4 : (round($max, 0));
	}
}

?>
