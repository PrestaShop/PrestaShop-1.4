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
  
class StatsVisits extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';
    private $_option;

    function __construct()
    {
        $this->name = 'statsvisits';
        $this->tab = 'Stats';
        $this->version = 1.0;
		$this->page = basename(__FILE__, '.php');
			
		parent::__construct();
		
        $this->displayName = $this->l('Visits and Visitors');
        $this->description = $this->l('Display statistics about your visits and visitors');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	public function getTotalVisits()
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(c.`id_connections`) AS total
		FROM `'._DB_PREFIX_.'connections` c
		WHERE c.`date_add` LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'');
		return isset($result['total']) ? $result['total'] : 0;
	}
	
	public function getTotalGuests()
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT DISTINCT c.`id_guest`
		FROM `'._DB_PREFIX_.'connections` c
		WHERE c.`date_add` LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'');
		return Db::getInstance()->NumRows();
	}
	
	public function hookAdminStatsModules($params)
	{
		$totalVisits = $this->getTotalVisits();
		$totalGuests = $this->getTotalGuests();
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<p><center>
				<img src="../img/admin/down.gif" />'.$this->l('A visit correspond to the coming of an internet user on your shop. Until the end of his session, only one visit is counted.').'
				'.$this->l('A visitor is an unknown person - who has not registered or logged on - surfing on your shop. A visitor can come and visit your shop many times.').'
			</center></p>
			<div style="margin-top:20px"></div>
			<p>'.$this->l('Total visits:').' '.$totalVisits.'</p>
			<p>'.$this->l('Total visitors:').' '.$totalGuests.'</p>
			'.ModuleGraph::engine(array('layers' => 2, 'type' => 'line', 'option' => 3)).'<br /><br />
			
		</fieldset><br />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
				<h2>'.$this->l('Determine the interest of a visit').'</h2>
				'.$this->l('Visitors\' evolution graph strongly looks like to the visits\' graph, but provides an additional information: <strong>Do your visitors come back?</strong>').'<br />
				<ul>
					<li>'.$this->l('if this is the case, congratulations, your website is well-thought-out and undeniably pleases.').'</li>
					<li>'.$this->l('Otherwise, the conclusion is not so simple. The problem can be esthetic or ergonomic, or else the offer not sufficient. It\'s also possible that these visitors mistakenly came here, without particular interest for your shop; this phenomenon often happens with the search engines.').'</li>
				</ul>
				'.$this->l('This information is mostly qualitative: you have to determin the interest of a disjointed visit.').'<br />
		</fieldset>';
		
		return $this->_html;
	}
	
	public function setOption($option, $layers = 1)
	{
		switch ($option)
		{
			case 3:
				$this->_titles['main'][0] = $this->l('Number of visits and unique visitors');
				$this->_titles['main'][1] = $this->l('Visits');
				$this->_titles['main'][2] = $this->l('Visitors');
				$this->_query[0] = '
					SELECT `date_add`
					FROM `'._DB_PREFIX_.'connections`
					WHERE `date_add` LIKE \'';
				$this->_query2[0] = '\'';
				$this->_query[1] = '
					SELECT `date_add`
					FROM `'._DB_PREFIX_.'connections`
					WHERE `date_add` LIKE \'';
				$this->_query2[1] = '\'
				GROUP BY `id_guest`';
				break;
		}
	}
	
	protected function getData($layers)
	{
		$this->setDateGraph($layers, true);
	}
	
	protected function setYearValues($layers)
	{
		if ($layers == 1)
		{
			$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
			foreach ($result AS $row)
			    $this->_values[intval(substr($row['date_add'], 5, 2)) - 1]++;
		}
		else
		{
			for ($i = 0; $i < $layers; $i++)
			{
				$result = Db::getInstance()->ExecuteS($this->_query[$i].pSQL(ModuleGraph::getDateLike()).$this->_query2[$i]);
				foreach ($result AS $row)
				    $this->_values[$i][intval(substr($row['date_add'], 5, 2)) - 1]++;
			}
		}
	}
	
	protected function setMonthValues($layers)
	{
		if ($layers == 1)
		{
			$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
			foreach ($result AS $row)
				$this->_values[intval(substr($row['date_add'], 8, 2)) - 1]++;
		}
		else
		{
			for ($i = 0; $i < $layers; $i++)
			{
				$result = Db::getInstance()->ExecuteS($this->_query[$i].pSQL(ModuleGraph::getDateLike()).$this->_query2[$i]);
				foreach ($result AS $row)
					$this->_values[$i][intval(substr($row['date_add'], 8, 2)) - 1]++;
			}
		}
	}

	protected function setDayValues($layers)
	{
		if ($layers == 1)
		{
			$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
			foreach ($result AS $row)
			    $this->_values[intval(substr($row['date_add'], 11, 2))]++;
		}
		else
		{
			for ($i = 0; $i < $layers; $i++)
			{
				$result = Db::getInstance()->ExecuteS($this->_query[$i].pSQL(ModuleGraph::getDateLike()).$this->_query2[$i]);
				foreach ($result AS $row)
				    $this->_values[$i][intval(substr($row['date_add'], 11, 2))]++;
			}
		}
	}
}

?>
