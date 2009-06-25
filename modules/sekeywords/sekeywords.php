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
		
		$this->_query = '
		SELECT sek.`keyword`, COUNT(TRIM(sek.`keyword`)) as occurences
		FROM `'._DB_PREFIX_.'sekeyword` sek
		WHERE sek.`date_add` BETWEEN ';
		$this->_query2 = '
		GROUP BY TRIM(sek.`keyword`)
		HAVING occurences > 1
		ORDER BY COUNT(sek.`keyword`) DESC';

        parent::__construct();
		
        $this->displayName = $this->l('Search engine keywords');
        $this->description = $this->l('Display which keywords have led visitors to your website');
    }

	function install()
	{
		if (!parent::install() OR !$this->registerHook('top') OR !$this->registerHook('AdminStatsModules'))
			return false;
		return Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'sekeyword` (
			id_sekeyword INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
			keyword VARCHAR(256) NOT NULL,
			date_add DATETIME NOT NULL,
			PRIMARY KEY(id_sekeyword)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');
	}
	
    function uninstall()
    {
        if (!parent::uninstall())
			return false;
		return (Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'sekeyword`'));
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
		$result = Db::getInstance()->ExecuteS($this->_query.ModuleGraph::getDateBetween().$this->_query2);
		$this->_html = '<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>';
		
		if ($result AND sizeof($result))
		{
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
			$this->_html .= '<center>'.ModuleGraph::engine(array('type' => 'pie')).'</center><br class="clear" />'.$table;
		}
		else
			$this->_html .= '<p><strong>'.$this->l('No keyword searched for more than once found').'</strong></p>';

		$this->_html .= '</fieldset><br class="clear" />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
			<h2>'.$this->l('Identify external search engines keywords').'</h2>
			<p>'.$this->l('There are many ways to find a website, but one of the most common is to find it with a search engine. Identifying the most "visitor-making" keywords entered by your new visitors is really important, it allows you to see which product you have to put in front if you want more visitors and customers.').'</p><br />
			<h3>'.$this->l('How does it work?').'</h2>
			<p>'.$this->l('When a visitors comes to your website, the server knows its previous location. This module parses this URL and finds the keywords in it. Currently, it manages the following search engines:').'<b> Google, AOL, Yandex, Ask, NHL, Yahoo, Baidu, Lycos, Exalead, Live, Voila</b> '.$this->l('and').' <b>Altavista</b>. '.$this->l('Soon it will be possible to add dynamically new search engine and to contribute to this module!').'</p><br />
		</fieldset>';
		return $this->_html;
	}
	
	function getKeywords($url)
	{
		if (!Validate::isAbsoluteUrl($url))
			return false;
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
				if (!isset($kArray[0]) OR empty($kArray[0]))
					preg_match('/[^a-z]'.$varname.'=.+$'.'/', $parsedUrl['query'], $kArray);
				if (!isset($kArray[0]) OR empty($kArray[0]))
					return false;
				$kString = urldecode(str_replace('+', ' ', ltrim(substr(rtrim($kArray[0], '&'), strlen($varname) + 1), '=')));
				return $kString;
			}
		}
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
			$this->_legend[] = $row['keyword'];
			$this->_values[] = $row['occurences'];
			$total2 += $row['occurences'];
		}
		if ($total >= $total2)
		{
			$this->_legend[] = $this->l('Others');
			$this->_values[] = $total - $total2;
		}
	}
}

?>
