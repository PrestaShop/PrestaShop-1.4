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
  
class GraphArtichow extends ModuleGraphEngine
{
	private	$_graph;
	private	$_plot = NULL;
	private	$_error = 0;

    function __construct($type = null)
    {
		if ($type != null)
		{
			require_once(dirname(__FILE__).'/artichow/Graph.class.php');
			$this->_graph = new Graph();
			$this->_graph->setAntiAliasing(true);
			parent::__construct($type);
		}
		else
		{
	        $this->name = 'graphartichow';
	        $this->tab = 'Stats Engines';
	        $this->version = 1.0;
			$this->page = basename(__FILE__, '.php');

	        Module::__construct();
			
	        $this->displayName = $this->l('Artichow');
	        $this->description = $this->l('Artichow is a library which enable the display of simple picture-based graphs using PHP and GD.');
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
		
	private function drawLine($values)
	{
		require_once(dirname(__FILE__).'/artichow/LinePlot.class.php');
		
		$this->_plot = new LinePlot(array_values($values));
		$this->_plot->setFillGradient(new LinearGradient(new Color(255, 246, 211, 20), new Color(240, 225, 160, 50), 90));
		$this->_plot->setColor(new Color(80, 50, 0, 0));
	}

	private function drawColumn($values)
	{
		require_once(dirname(__FILE__).'/artichow/BarPlot.class.php');

		$this->_plot = new BarPlot(array_values($values));
		$this->_plot->label->set(array_values($values));
		$this->_plot->label->setColor(new Color(110, 110, 110));
		$this->_plot->label->move(0, -7);
		$this->_plot->label->setAlign(Positionable::CENTER, NULL);
		$this->_plot->setBarGradient(new LinearGradient(new Color(254, 255, 216), new Color(233, 231, 218), 90));
		$this->_plot->barBorder->setColor(new Color(115, 115, 150));
		$this->_plot->barShadow->setSize(1);
		$this->_plot->barShadow->setColor(new Color(215, 215, 215));
		$this->_plot->barShadow->setPosition(Shadow::RIGHT_TOP);
		$this->_plot->barShadow->smooth(TRUE);
	}
	
	private function setErrorImage()
	{
		require_once(dirname(__FILE__).'/artichow/BarPlot.class.php');

		$this->_plot = new BarPlot(array(0));
		$this->_type = 'column';
		$this->_plot->setSize(0, 0);
		$this->_plot->setCenter(-20, -20);
	}

	private function drawPie($values)
	{
		require_once(dirname(__FILE__).'/artichow/Pie.class.php');
		$exploder = array();
		$counter = sizeof($values);
		
		$this->_plot = new Pie(array_values($values), Pie::COLORED);
		$this->_plot->set3D(10);
		$this->_plot->setLabelPrecision(2);
		$this->_plot->setLabelPosition(2);
		$this->_plot->legend->shadow->setSize(1);
		$this->_plot->legend->setModel(Legend::MODEL_BOTTOM);
		$this->_plot->legend->setPosition(NULL, 0.87);
		$this->_plot->setSize(0.90, 0.90);
		for ($i = 0; $i < $counter; $i++)
			$exploder[$i] = 8;
		$this->_plot->explode($exploder);
	}

	private function drawLineColumn($y_max)
	{
		$this->_plot->setYMax($y_max);
		$this->_plot->setSize(1, 1);
		$this->_plot->setCenter(0.508, 0.53);
		$this->_plot->setPadding(28, 10, NULL, 40);
		$this->_plot->grid->setType(Line::DASHED);
	}

	public function createValues($values)
	{
		if (sizeof($values) == 0)
		{
			$this->setErrorImage();
			return;
		}
		switch ($this->_type)
		{
			case 'pie':
				$this->drawPie($values);
				break;
			case 'line':
				$this->drawLine($values);
				$this->drawLineColumn($this->getYMax($values));
				break;
			case 'column':
			default:
				$this->drawColumn($values);
				$this->drawLineColumn($this->getYMax($values));
				break;
		}
	}
	
	public function setSize($width, $height)
	{
		if (Validate::isUnsignedInt($width) AND Validate::isUnsignedInt($height))
			$this->_graph->setSize($width, $height);
	}
	
	public function setLegend($legend)
	{
		if ($this->_plot == NULL)
			return;
		foreach ($legend as $k => $val)
			$legend[$k] = html_entity_decode(htmlentities(html_entity_decode($val), ENT_NOQUOTES, 'utf-8'));
		switch ($this->_type)
		{
			case 'pie':
				$this->_plot->setLegend($legend);
				break;
			case 'column':
			default:
				if (isset($this->_plot->xAxis, $this->_plot->yAxis, $this->_plot->grid))
				{
					$this->_plot->xAxis->setLabelText($legend);
					$this->_plot->xAxis->label->setFont(new Tuffy(7));
					$this->_plot->yAxis->setLabelPrecision(0);
					$this->_plot->grid->setType(Line::DASHED);
				}
				break;
		}
	}

	public function setTitles($titles)
	{
		if ($this->_plot != NULL)
		{
			if (isset($titles['main']))
			{
				$this->_graph->title->set($titles['main']);
				$this->_graph->title->setFont(new Tuffy(10));
				$this->_graph->title->setAlign(Positionable::CENTER, Positionable::MIDDLE);
			}
			if (isset($titles['x']))
			{
				$this->_plot->xAxis->title->set($titles['x']);
				$this->_plot->xAxis->title->setFont(new Tuffy(10));
				$this->_plot->xAxis->setTitleAlignment(Label::RIGHT);
			}
			if (isset($titles['y']))
			{
				$this->_plot->yAxis->title->set($titles['y']);
				$this->_plot->yAxis->title->setFont(new Tuffy(10));
				$this->_plot->yAxis->setTitleAlignment(Label::TOP);
			}
		}
	}
	
	public function draw()
	{
		if ($this->_plot != NULL)
			$this->_graph->add($this->_plot);
		$this->_graph->draw();
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
