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
  
abstract class ModuleGraph extends Module
{
	/** @var integer array graph data */
	protected	$_values = array();
	
	/** @var string array graph legends (X axis) */
	protected	$_legend = array();
	
	/**@var string graph titles */
	protected	$_titles = array('main' => NULL, 'x' => NULL, 'y' => NULL);
		
	/** @var ModuleGraphEngine graph engine */
	protected $_render;
		
	abstract protected function getData();
	
	protected function setDateGraph($legend = false)
	{
		global $cookie;

		if (isset($cookie->stats_granularity) AND $cookie->stats_granularity == 'd')
		{
			if ($legend)
				for ($i = 0; $i < 24; $i++)
				{
					$this->_values[$i] = 0;
					$this->_legend[$i] = (strlen($i) == 1) ? ('0'.$i) : $i;
				}
			if (is_callable(array($this, 'setDayValues')))
				$this->setDayValues();
		}
		elseif (isset($cookie->stats_granularity) AND $cookie->stats_granularity == 'm')
		{
			$max = date('t', mktime(0, 0, 0, $cookie->stats_month, 1, $cookie->stats_year)); 
			if ($legend)
				for ($i = 0; $i < $max; $i++)
				{
					$this->_values[$i] = 0;
					$this->_legend[$i] = ($i != 0 && ($i + 1) % 5) ? '' : $i + 1;
				}
			if (is_callable(array($this, 'setMonthValues')))
				$this->setMonthValues();
		}
		else
		{
			if ($legend)
			{
				$this->_values = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
				$this->_legend = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
			}
			if (is_callable(array($this, 'setYearValues')))
				$this->setYearValues();
		}
	}
	
	public function create($render, $type, $width, $height)
	{
		require_once(dirname(__FILE__).'/../modules/'.$render.'/'.$render.'.php');
		$this->_render = new $render($type);
		
		$this->getData();
		$this->_render->createValues($this->_values);
		$this->_render->setSize($width, $height);
		$this->_render->setLegend($this->_legend);
		$this->_render->setTitles($this->_titles);
	}
	
	public function draw()
	{
		$this->_render->draw();
	}
		
	public static function engine($params)
	{		
		if (!($render = Configuration::get('PS_STATS_RENDER')))
			return Tools::displayError('No graph engine selected');
		if (!file_exists(dirname(__FILE__).'/../modules/'.$render.'/'.$render.'.php'))
			return Tools::displayError('Graph engine selected unavailable');

		if (!isset($params['type']))
			$params['type'] = 'column';
		if (!isset($params['width']))
			$params['width'] = 550;
		if (!isset($params['height']))
			$params['height'] = 270;
		
		$drawer = 'drawer.php?render='.$render.'&module='.Tools::getValue('module').'&type='.$params['type'];
		if (isset($params['option']))
			$drawer .= '&option='.$params['option'];
			
		require_once(dirname(__FILE__).'/../modules/'.$render.'/'.$render.'.php');
		return call_user_func(array($render, 'hookGraphEngine'), $params, $drawer);
	}
	
	public static function getDateLike()
	{
		global $cookie;
		if (!isset($cookie->stats_year))
			$cookie->stats_year = date('Y');
		if (!isset($cookie->stats_granularity))
			$cookie->stats_granularity = 'y';
		
		$dateLike = '';
		if ($year = intval($cookie->stats_year))
			$dateLike .= $year.'-';
		if ($cookie->stats_granularity != 'y' AND isset($cookie->stats_month) AND $month = intval($cookie->stats_month))
			$dateLike .= (strlen($month) == 1 ? '0' : '').$month.'-';
		if ($cookie->stats_granularity == 'd' AND isset($cookie->stats_day) AND $day = intval($cookie->stats_day))
			$dateLike .= (strlen($day) == 1 ? '0' : '').$day.' ';
		$dateLike .= '%';
		return $dateLike;
	}
}

?>
