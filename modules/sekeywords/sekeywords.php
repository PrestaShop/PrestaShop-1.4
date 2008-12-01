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
  
class SEKeywords extends ModuleGraph
{
    private $_html = '';
	private $_query = '';
	private $_query2 = '';

    function __construct()
    {
        $this->name = 'sekeywords';
        $this->tab = 'Stats';
        $this->version = 1.0;
		$this->page = basename(__FILE__, '.php');
		$this->_query = '
		SELECT sek.`keyword`, COUNT(TRIM(sek.`keyword`)) as occurences
		FROM `'._DB_PREFIX_.'sekeyword` sek
		WHERE sek.`date_add` LIKE \'';
		$this->_query2 = '\'
		GROUP BY TRIM(sek.`keyword`)
		ORDER BY COUNT(sek.`keyword`) DESC';

        parent::__construct();
		
        $this->displayName = $this->l('Search engine keywords');
        $this->description = $this->l('Display which keywords have led visitors to your website');
    }

	function install()
	{
		if (!parent::install() OR !$this->registerHook('top') OR !$this->registerHook('AdminStatsModules'))
			return false;
		Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'sekeyword` (
			id_sekeyword INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			keyword VARCHAR(256) NOT NULL,
			date_add DATETIME NOT NULL,
			PRIMARY KEY(id_sekeyword)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');
		Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'search_engine` (
			id_search_engine INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			server VARCHAR(64) NOT NULL,
			getvar VARCHAR(16) NOT NULL,
			PRIMARY KEY(id_search_engine)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');
		return Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'search_engine` (`server`,`getvar`)
		VALUES  (\'google\',\'q\'),
				(\'search.aol\',\'query\'),
				(\'yandex.ru\',\'text\'),
				(\'ask.com\',\'q\'),
				(\'nhl.com\',\'q\'),
				(\'search.yahoo\',\'p\'),
				(\'baidu.com\',\'wd\'),
				(\'search.lycos\',\'query\'),
				(\'exalead\',\'q\'),
				(\'search.live.com\',\'q\'),
				(\'search.ke.voila\',\'rdata\'),
				(\'altavista\',\'q\')');
	}
	
    function uninstall()
    {
        if (!parent::uninstall())
			return false;
		return (Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'sekeyword`')
				AND Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'search_engine`'));
    }
	
	function hookTop($params)
	{
		if (!isset($_SERVER['HTTP_REFERER']) OR strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']))
			return;
		if (!Validate::isAbsoluteUrl($_SERVER['HTTP_REFERER']))
			return;
		if ($keywords = $this->getKeywords($_SERVER['HTTP_REFERER']))
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'sekeyword` (`keyword`,`date_add`)
			VALUES (\''.pSQL(trim($keywords)).'\',\''.pSQL(date('Y-m-d H:i:s')).'\')');
	}
	
	function hookAdminStatsModules()
	{
		$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<h2>'.$this->l('What is this?').'</h2>
			<p>'.$this->l('There are many ways to find a website, but one of the most common is to find it with a search engine. Identifying the most "visitor-making" keywords entered by your new visitors is really important, it allows you to see which product you have to put in front if you want more visitors and customers.').'</p><br />
			<h2>'.$this->l('How do you do that?').'</h2>
			<p>'.$this->l('When a visitors come to your website, the server know it\'s previous location. This module parse this URL and find the keywords in it. Currently, it manages the following search engines: Google, AOL, Yandex, Ask, NHL, Yahoo, Baidu, Lycos, Exalead, Live, Voila and Altavista. Soon it will be possible to add dynamically new search engine and to contribute to this module!').'</p><br />';
		$table = '<table class="table" border="0" cellspacing="0" cellspacing="0">
		<thead>
			<tr><th style="width:400px;">'.$this->l('Keywords').'</th>
			<th style="width:50px; text-align: right">'.$this->l('Occurences').'</th></tr>
		</thead>';

		$i = 0;
		foreach ($result as $index => $row)
		{
			$keyword =& $row['keyword'];
			$occurences =& $row['occurences'];
			if ($i === 0)
				$table .= '<tbody>';
			$table .= '<tr><td>'.$keyword.'</td><td style="text-align: right">'.$occurences.'</td></tr>';
			$i++;
		}
		
		if ($i > 0)
		{
			$table .= '</tbody></table>';
			$this->_html .= $table;
			$this->_html .= '<br /><center>'.ModuleGraph::engine(array('type' => 'pie')).'</center>';
		}
		else
			$this->_html .= '<p><strong>'.$this->l('No keyword found').'</strong></p>';

		$this->_html .= '</fieldset>';
		return $this->_html;
	}
	
	function getKeywords($url)
	{
		$parsedUrl = parse_url($url);
		$result = Db::getInstance()->ExecuteS('SELECT `server`, `getvar` FROM `'._DB_PREFIX_.'search_engine`');
		foreach ($result as $index => $row)
		{
			$host =& $row['server'];
			$varname =& $row['getvar'];
			if (strstr($parsedUrl['host'], $host))
			{
				$kArray = array();
				preg_match('/[^a-z]'.$varname.'=.+\&'.'/U', $parsedUrl['query'], $kArray);
				if (empty($kArray[0]))
					preg_match('/[^a-z]'.$varname.'=.+$'.'/', $parsedUrl['query'], $kArray);
				$kString = urldecode(str_replace('+', ' ', ltrim(substr(rtrim($kArray[0], '&'), strlen($varname) + 1), '=')));
				return $kString;
			}
		}
	}
	
	protected function getData()
	{
		$this->_titles['main'] = $this->l('10 first keywords');
		$totalResult = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
		$total = 0;
		$total2 = 0;
		foreach ($totalResult as $totalRow)
			$total += $totalRow['occurences'];
		$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2.' LIMIT 9');
		foreach ($result as $row)
		{
			$this->_legend[] = $row['keyword'];
			$this->_values[] = $row['occurences'];
			$total2 += $row['occurences'];
		}
		if ($total != $total2)
		{
			$this->_legend[] = $this->l('Others');
			$this->_values[] = $total2 - $total;
		}
	}
}

?>
