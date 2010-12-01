<?php

/**
  * Statistics
  * @category stats
  *
  * @author PrestaShop
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  */

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class StatsNewsletter extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';
    private $_option = '';

    function __construct()
    {
        $this->name = 'statsnewsletter';
        $this->tab = 'analytics_stats';
        $this->version = 1.0;
		
		parent::__construct();
		
        $this->displayName = $this->l('Newsletter');
        $this->description = $this->l('Display the newsletter registrations');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
		
	public function hookAdminStatsModules($params)
	{
		if(Module::isInstalled('blocknewsletter'))
		{
			$totals = $this->getTotals();
			
			$this->_html = '
			<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
				<p>'.$this->l('Registrations from customers:').' '.(int)($totals['customers']).'</p>
				<p>'.$this->l('Registrations from visitors:').' '.(int)($totals['visitors']).'</p>
				<p>'.$this->l('Both:').' '.(int)($totals['both']).'</p>
				<center>'.ModuleGraph::engine(array('type' => 'line', 'layers' => 3)).'</center>
			</fieldset>';
		}
		else
			$this->_html = '<p>'.$this->l('Module Newsletter Block must be installed').'</p>';
		
		return $this->_html;
	}

	private function getTotals()
	{
		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(*) as customers
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`newsletter_date_add` BETWEEN '.ModuleGraph::getDateBetween());
		$result2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(*) as visitors
		FROM '._DB_PREFIX_.'newsletter n
		WHERE n.`newsletter_date_add` BETWEEN '.ModuleGraph::getDateBetween());
		return array('customers' => $result1['customers'], 'visitors' => $result2['visitors'], 'both' => $result1['customers'] + $result2['visitors']);
	}
		
	protected function getData($layers)
	{
		$this->_titles['main'][0] = $this->l('Newsletter statistics');
		$this->_titles['main'][1] = $this->l('Customers');
		$this->_titles['main'][2] = $this->l('Visitors');
		$this->_titles['main'][3] = $this->l('Both');
			
		$this->_query = '
		SELECT c.newsletter_date_add
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`newsletter_date_add` BETWEEN ';
		$this->_query2 = '
		SELECT n.newsletter_date_add
		FROM '._DB_PREFIX_.'newsletter n
		WHERE n.`newsletter_date_add` BETWEEN ';
		$this->setDateGraph($layers, true);
	}
	
	protected function setYearValues($layers)
	{
		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query.$this->getDate());
		$result2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query2.$this->getDate());
		foreach ($result1 AS $row)
			$this->_values[0][(int)(substr($row['newsletter_date_add'], 5, 2))] += 1;
		if ($result2)
			foreach ($result2 AS $row)
				$this->_values[1][(int)(substr($row['newsletter_date_add'], 5, 2))] += 1;
		foreach ($this->_values[2] as $key => $zerofill)
			$this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
	}
	
	protected function setMonthValues($layers)
	{
		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query.$this->getDate());
		$result2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query2.$this->getDate());
		foreach ($result1 AS $row)
			$this->_values[0][(int)(substr($row['newsletter_date_add'], 8, 2))] += 1;
		if ($result2)
			foreach ($result2 AS $row)
				$this->_values[1][(int)(substr($row['newsletter_date_add'], 8, 2))] += 1;
		foreach ($this->_values[2] as $key => $zerofill)
			$this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
	}

	protected function setDayValues($layers)
	{
		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query.$this->getDate());
		$result2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query2.$this->getDate());
		foreach ($result1 AS $row)
			$this->_values[0][(int)(substr($row['newsletter_date_add'], 11, 2))] += 1;
		if ($result2)
			foreach ($result2 AS $row)
				$this->_values[1][(int)(substr($row['newsletter_date_add'], 11, 2))] += 1;
		foreach ($this->_values[2] as $key => $zerofill)
			$this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
	}
}


