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
		SELECT ss.`keywords`, COUNT(TRIM(ss.`keywords`)) as occurences, MAX(results) as total
		FROM `'._DB_PREFIX_.'statssearch` ss
		WHERE ss.`date_add` BETWEEN ';
		$this->_query2 = '
		GROUP BY ss.`keywords`
		HAVING occurences > 1
		ORDER BY occurences DESC';

        parent::__construct();
		
        $this->displayName = $this->l('Shop search');
        $this->description = $this->l('Display which keywords have been searched by your visitors');
    }

	function install()
	{
		if (!parent::install() OR !$this->registerHook('search') OR !$this->registerHook('AdminStatsModules'))
			return false;
		return Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'statssearch` (
			id_statssearch INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			keywords VARCHAR(255) NOT NULL,
			results INT(6) NOT NULL DEFAULT 0,
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
	
	function hookSearch($params)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'statssearch` (`keywords`,`results`,`date_add`) VALUES (\''.pSQL($params['expr']).'\', '.intval($params['total']).', NOW())');
	}
	
	function hookAdminStatsModules()
	{
		$result = Db::getInstance()->ExecuteS($this->_query.ModuleGraph::getDateBetween().$this->_query2);
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>';
		$table = '<div style="overflow-y: scroll; height: 600px;">
		<table class="table" border="0" cellspacing="0" cellspacing="0">
		<thead>
			<tr>
				<th style="width:400px;">'.$this->l('keywords').'</th>
				<th style="width:50px; text-align: right">'.$this->l('Occurences').'</th>
				<th style="width:50px; text-align: right">'.$this->l('Results').'</th>
			</tr>
		</thead><tbody>';

		foreach ($result as $row)
			$table .= '<tr>
				<td>'.$row['keywords'].'</td>
				<td style="text-align: right">'.$row['occurences'].'</td>
				<td style="text-align: right">'.$row['total'].'</td>
			</tr>';
		$table .= '</tbody></table></div>';
		
		if (sizeof($result))
			$this->_html .= '<center>'.ModuleGraph::engine(array('type' => 'pie')).'</center><br class="clear" />'.$table;
		else
			$this->_html .= '<p><strong>'.$this->l('No keywords searched more than once found.').'</strong></p>';
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
			$this->_legend[] = $row['keywords'];
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
