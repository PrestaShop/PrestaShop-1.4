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
  
class StatsSearch extends ModuleGraph
{
    private $_html = '';
	private $_query = '';
	private $_query2 = '';

    function __construct()
    {
        $this->name = 'statssearch';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
		$this->_query = '
		SELECT ss.`keyword`, COUNT(TRIM(ss.`keyword`)) as occurences
		FROM `'._DB_PREFIX_.'statssearch` ss
		WHERE LEFT(ss.`date_add`, 10) BETWEEN ';
		$this->_query2 = '
		GROUP BY TRIM(ss.`keyword`)
		HAVING occurences > 1
		ORDER BY COUNT(ss.`keyword`) DESC';

        parent::__construct();
		
        $this->displayName = $this->l('Customer search');
        $this->description = $this->l('Display which keywords have been searched by your visitors');
    }

	function install()
	{
		if (!parent::install() OR !$this->registerHook('top') OR !$this->registerHook('AdminStatsModules'))
			return false;
		return Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'statssearch` (
			id_statssearch INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			keyword VARCHAR(256) NOT NULL,
			date_add DATETIME NOT NULL,
			PRIMARY KEY(id_statssearch)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');
	}
	
    function uninstall()
    {
        if (!parent::uninstall())
			return false;
		return (Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'statssearch`'));
    }
	
	function hookTop($params)
	{
		if ($query = trim(Tools::getValue('search_query')) AND Validate::isValidSearch($query))
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'statssearch` (`keyword`,`date_add`)
			VALUES (\''.pSQL($query).'\', NOW())');
	}
	
	function hookAdminStatsModules()
	{
		$result = Db::getInstance()->ExecuteS($this->_query.ModuleGraph::getDateBetween().$this->_query2);
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>';
		$table = '<div style="overflow-y: scroll; height: 600px;">
		<table class="table" border="0" cellspacing="0" cellspacing="0">
		<thead>
			<tr><th style="width:400px;">'.$this->l('Keywords').'</th>
			<th style="width:50px; text-align: right">'.$this->l('Occurences').'</th></tr>
		</thead><tbody>';

		foreach ($result as $index => $row)
		{
			$keyword =& $row['keyword'];
			$occurences =& $row['occurences'];
			$table .= '<tr><td>'.$keyword.'</td><td style="text-align: right">'.$occurences.'</td></tr>';
		}
		$table .= '</tbody></table></div>';
		
		if (sizeof($result))
			$this->_html .= '<center>'.ModuleGraph::engine(array('type' => 'pie')).'</center><br class="clear" />'.$table;
		else
			$this->_html .= '<p><strong>'.$this->l('No keyword searched more than once found.').'</strong></p>';
		$this->_html .= '</fieldset>';
		return $this->_html;
	}
	
	protected function getData($layers)
	{
		$this->_titles['main'] = $this->l('10 first keywords');
		$totalResult = Db::getInstance()->ExecuteS($this->_query.$this->getDate().$this->_query2);
		$total = 0;
		$total2 = 0;
		foreach ($totalResult as $totalRow)
			$total += $totalRow['occurences'];
		$result = Db::getInstance()->ExecuteS($this->_query.$this->getDate().$this->_query2.' LIMIT 9');
		foreach ($result as $row)
		{
			if (!$row['occurences'])
				continue;
			$this->_legend[] = $row['keyword'];
			$this->_values[] = $row['occurences'];
			$total2 += $row['occurences'];
		}
		if ($total > $total2)
		{
			$this->_legend[] = $this->l('Others');
			$this->_values[] = $total - $total2;
		}
	}
}

?>
