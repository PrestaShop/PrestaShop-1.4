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
  
class GraphVisifire extends ModuleGraphEngine
{
	private	$_xml;
	private	$_values = NULL;
	private	$_legend = NULL;
	
    function __construct($type = null)
    {
		if ($type != null)
		{
			$this->_xml = '<vc:Chart xmlns:vc="clr-namespace:Visifire.Charts;assembly=Visifire.Charts" BorderThickness="0" AnimationEnabled="True" AnimationType="Type5"';
			if ($type == 'pie' || $type == 'line')
				$this->_xml .= ' Theme="Theme1" View3D="True"';
			else
				$this->_xml .= ' Theme="Theme2" ColorSet="Visifire2" UniqueColors="True"';
			$this->_xml .= '>';
			parent::__construct($type);
		}
		else
		{
	        $this->name = 'graphvisifire';
	        $this->tab = 'Stats Engines';
	        $this->version = 1.0;
			$this->page = basename(__FILE__, '.php');

	        Module::__construct();
			
	        $this->displayName = $this->l('Visifire');
	        $this->description = $this->l('Visifire is a set of open source data visualization components - powered by Microsoft Silverlight 2 beta 2.');
		}
    }

	function install()
	{
		return (parent::install() AND $this->registerHook('GraphEngine'));
	}
    
	public static function hookGraphEngine($params, $drawer)
	{
		static $divid = 1;
		return '<script type="text/javascript" src="../modules/graphvisifire/visifire/Visifire.js"></script>
		<div id="VisifireChart'.$divid.'">
			<script language="javascript" type="text/javascript">
				var vChart = new Visifire("../modules/graphvisifire/visifire/Visifire.xap", '.$params['width'].', '.$params['height'].');
				vChart.setLogLevel(0);
				vChart.setDataUri(\''.$drawer.'\');
				vChart.render("VisifireChart'.$divid++.'");
			</script>
		</div>';
	}
			
	public function createValues($values)
	{
		$this->_values = $values;
	}
	
	public function setSize($width, $height)
	{
		// Unavailable
	}

	public function setLegend($legend)
	{
		$this->_legend = $legend;
	}

	public function setTitles($titles)
	{
		if (isset($titles['main']))
			$this->_xml .= '<vc:Title Text="'.$titles['main'].'"/>';
		if (isset($titles['x']))
			$this->_xml .= '<vc:AxisX Title="'.$titles['x'].'" />';
		if (isset($titles['y']))
			$this->_xml .= '<vc:AxisY Title="'.$titles['y'].'" />';
	}

	public function draw()
	{
		if ($this->_values != NULL && $this->_legend != NULL)
		{
			$size = sizeof($this->_values);
			if ($size == sizeof($this->_legend))
			{
				$this->_xml .= '<vc:DataSeries RenderAs="'.$this->_type.'">';
				for ($i = 0; $i < $size; $i++)
				{
					$this->_xml .= '<vc:DataPoint ';
					if (!empty($this->_legend[$i]))
						$this->_xml .= 'AxisLabel="'.str_replace('<', '&lt;', str_replace('>', '&gt;', str_replace('&', '&amp;', str_replace('&quot', "'", $this->_legend[$i])))).'" ';
					$this->_xml .= 'YValue="'.$this->_values[$i].'"';
					if ($this->_type == 'pie')
						$this->_xml .= ' ExplodeOffset="0.2"';
					$this->_xml .= '/>';
				}
				$this->_xml .= '</vc:DataSeries>';
			}
		}
		$this->_xml .= '</vc:Chart>';
		echo $this->_xml;
	}
}

?>
