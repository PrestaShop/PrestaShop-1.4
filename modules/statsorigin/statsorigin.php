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
  
class StatsOrigin extends ModuleGraph
{
	private $_html;
	
    function __construct()
    {
        $this->name = 'statsorigin';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
        parent::__construct();
		
        $this->displayName = $this->l('Visitors origin');
        $this->description = $this->l('Display the websites from where your visitors come from');
    }

	function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}

	private function getOrigins($dateBetween)
	{
		$directLink = $this->l('Direct link');
		$result = mysql_query('
		SELECT c.http_referer
		FROM '._DB_PREFIX_.'connections c
		WHERE c.date_add BETWEEN '.$dateBetween);
		$websites = array($directLink => 0);
		while ($row = mysql_fetch_assoc($result))
		{
			if (!isset($row['http_referer']) OR empty($row['http_referer']))
				++$websites[$directLink];
			else
			{
				$website = preg_replace('/^www./', '', parse_url($row['http_referer'], PHP_URL_HOST));
				if (!isset($websites[$website]))
					$websites[$website] = 1;
				else
					++$websites[$website];
			}
		}
		mysql_free_result($result);
		arsort($websites);
		return $websites;
	}

	function hookAdminStatsModules()
	{
		$websites = $this->getOrigins(ModuleGraph::getDateBetween());
		
		$this->_html = '<fieldset class="width3 center"><legend><img src="../modules/'.$this->name.'/logo.gif" /> Origin</legend>';
		if (sizeof($websites))
		{
			$this->_html .= '
			<p><img src="../img/admin/down.gif" />'. $this->l('Here is the percentage of the 10 most popular referrer websites by which visitors went through to get on your shop.').'</p>
			'.ModuleGraph::engine(array('type' => 'pie')).'<br /><br />
			<div style="overflow-y: scroll; height: 600px;">
			<table class="table" border="0" cellspacing="0" cellspacing="0">
				<tr>
					<th style="width:400px;">'.$this->l('Origin').'</th>
					<th style="width:50px; text-align: right">'.$this->l('Total').'</th>
				</tr>';
			foreach ($websites as $website => $total)
				$this->_html .= '<tr><td>'.(!strstr($website, ' ') ? '<a href="http://'.$website.'">' : '').$website.(!strstr($website, ' ') ? '</a>' : '').'</td><td style="text-align: right">'.$total.'</td></tr>';
			$this->_html .= '</table></div>';
		}
		else
			$this->_html .= '<p><strong>'.$this->l('Direct links only').'</strong></p>';
		$this->_html .= '</fieldset><br />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
		<h2>'.$this->l('What is a referrer website?').'</h2>
			<p>
				'.$this->l('When visiting a webpage, the referrer is the URL of the previous webpage from which a link was followed.').'<br />
				'.$this->l('A referrer enables you to know which keywords are entered by visitors in search engines when they try to get on your shop; and also to optimize your web promotion.').'<br /><br />
				'. $this->l('A referrer can be:').'
				<ul>
					<li class="bullet">'. $this->l('Someone who put a link on his website towards your shop').'</li>
					<li class="bullet">'. $this->l('A partner with whom you made a link exchange in order to bring in sales or attract new customers').'</li>
				</ul>
			</p>
		</fieldset>';
		return $this->_html;
	}
		
	protected function getData($layers)
	{
		$this->_titles['main'] = $this->l('10 first websites');
		$websites = $this->getOrigins($this->getDate());
		$total = 0;
		$total2 = 0;
		$i = 0;
		foreach ($websites as $website => $totalRow)
		{
			if (!$totalRow)
				continue;
			$total += $totalRow;
			if ($i++ < 9)
			{
				$this->_legend[] = $website;
				$this->_values[] = $totalRow;
				$total2 += $totalRow;
			}
		}
		if ($total != $total2)
		{
			$this->_legend[] = $this->l('Others');
			$this->_values[] = $total - $total2;
		}
	}
}

?>
