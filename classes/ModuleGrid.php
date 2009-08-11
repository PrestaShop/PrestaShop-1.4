<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
abstract class ModuleGrid extends Module
{
	protected $_employee;
	
	/** @var string array graph data */
	protected $_values = array();
	
	/** @var integer total number of values **/
	protected $_totalCount = 0;
	
	/**@var string graph titles */
	protected $_title;
	
	/**@var integer start */
	protected $_start;
	
	/**@var integer limit */
	protected $_limit;
	
	/**@var string column name on which to sort */
	protected $_sort = null;
	
	/**@var string sort direction DESC/ASC */
	protected $_direction = null;

	/** @var ModuleGridEngine grid engine */
	protected $_render;
	
	public function setEmployee($id_employee)
	{
		$this->_employee = new Employee(intval($id_employee));
	}
	public function setLang($id_lang)
	{
		$this->_id_lang = $id_lang;
	}
	
	public function create($render, $type, $width, $height, $start, $limit, $sort, $dir)
	{
		require_once(dirname(__FILE__).'/../modules/'.$render.'/'.$render.'.php');
		$this->_render = new $render($type);
		
		$this->_start = $start;
		$this->_limit = $limit;
		$this->_sort = $sort;
		$this->_direction = $dir;
		
		$this->getData();

		$this->_render->setTitle($this->_title);
		$this->_render->setSize($width, $height);
		$this->_render->setValues($this->_values);
		$this->_render->setTotalCount($this->_totalCount);
	}
	
	public function render()
	{
		$this->_render->render();
	}
	
	public static function engine($params)
	{
		if (!($render = Configuration::get('PS_STATS_GRID_RENDER')))
			return Tools::displayError('No grid engine selected');
		if (!file_exists(dirname(__FILE__).'/../modules/'.$render.'/'.$render.'.php'))
			return Tools::displayError('Grid engine selected unavailable');
			
		$grider = 'grider.php?render='.$render.'&module='.Tools::getValue('module');
		
		global $cookie;
		$grider .= '&id_employee='.intval($cookie->id_employee);
		$grider .= '&id_lang='.intval($cookie->id_lang);
		
		if (!isset($params['width']) OR !Validate::IsUnsignedInt($params['width']))
			$params['width'] = 600;
		if (!isset($params['height']) OR !Validate::IsUnsignedInt($params['height']))
			$params['height'] = 920;
		if (!isset($params['start']) OR !Validate::IsUnsignedInt($params['start']))
			$params['start'] = 0;
		if (!isset($params['limit']) OR !Validate::IsUnsignedInt($params['height']))
			$params['limit'] = 40;

		$grider .= '&width='.$params['width'];
		$grider .= '&height='.$params['height'];
		if (isset($params['start']) AND Validate::IsUnsignedInt($params['start']))
			$grider .= '&start='.$params['start'];
		if (isset($params['limit']) AND Validate::IsUnsignedInt($params['limit']))
			$grider .= '&limit='.$params['limit'];
		if (isset($params['type']) AND Validate::IsName($params['type']))
			$grider .= '&type='.$params['type'];
		if (isset($params['option']) AND Validate::IsGenericName($params['option']))
			$grider .= '&option='.$params['option'];
		if (isset($params['sort']) AND Validate::IsName($params['sort']))
			$grider .= '&sort='.$params['sort'];
		if (isset($params['dir']) AND Validate::IsSortDirection($params['dir']))
			$grider .= '&dir='.$params['dir'];
			
		require_once(dirname(__FILE__).'/../modules/'.$render.'/'.$render.'.php');
		return call_user_func(array($render, 'hookGridEngine'), $params, $grider);
	}
	
	abstract protected function getData();
	
	public function getDate()
	{
		return ModuleGraph::getDateBetween($this->_employee);
	}
	public function getLang()
	{
		return $this->_id_lang;
	}
}
?>
