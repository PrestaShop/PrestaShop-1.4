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
		return Db::getInstance()->getValue('
		SELECT COUNT(c.`id_connections`)
		FROM `'._DB_PREFIX_.'connections` c
		WHERE c.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
	}
	
	public function getTotalGuests()
	{
		return Db::getInstance()->getValue('
		SELECT COUNT(DISTINCT c.`id_guest`)
		FROM `'._DB_PREFIX_.'connections` c
		WHERE c.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
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
			'.($totalVisits ? ModuleGraph::engine(array('layers' => 2, 'type' => 'line', 'option' => 3)).'<br /><br />' : '').'
		</fieldset>
		<br class="clear" />
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
					SELECT date_add, COUNT(`date_add`) as total
					FROM `'._DB_PREFIX_.'connections`
					WHERE `date_add` BETWEEN ';
				$this->_query[1] = '
					SELECT date_add, COUNT(DISTINCT `id_guest`) as total
					FROM `'._DB_PREFIX_.'connections`
					WHERE `date_add` BETWEEN ';
				break;
		}
	}
	
	protected function getData($layers)
	{
		$this->setDateGraph($layers, true);
	}
	
	protected function setYearValues($layers)
	{
		for ($i = 0; $i < $layers; $i++)
		{
			$result = Db::getInstance()->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 7)');
			foreach ($result AS $row)
				$this->_values[$i][intval(substr($row['date_add'], 5, 2))] = intval($row['total']);
		}
	}
	
	protected function setMonthValues($layers)
	{
		for ($i = 0; $i < $layers; $i++)
		{
			$result = Db::getInstance()->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 10)');
			foreach ($result AS $row)
				$this->_values[$i][intval(substr($row['date_add'], 8, 2))] = intval($row['total']);
		}
	}

	protected function setDayValues($layers)
	{
		for ($i = 0; $i < $layers; $i++)
		{
			$result = Db::getInstance()->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 13)');
			foreach ($result AS $row)
				$this->_values[$i][intval(substr($row['date_add'], 11, 2))] = intval($row['total']);
		}
	}
}

?>
