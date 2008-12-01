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
  
class StatsOrigin extends ModuleGraph
{
	private $_html;
	
    function __construct()
    {
        $this->name = 'statsorigin';
        $this->tab = 'Stats';
        $this->version = 1.0;
		$this->page = basename(__FILE__, '.php');
        parent::__construct();
		
        $this->displayName = $this->l('Visitors origin');
        $this->description = $this->l('Display the websites from where your visitors come from');
    }

	function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules') AND $this->registerHook('adminOrder'));
	}
	
	private function getOrigins()
	{
		$result = mysql_query('
		SELECT http_referer
		FROM '._DB_PREFIX_.'connections
		WHERE date_add LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'');
		$websites = array('Direct link' => 0);
		while ($row = mysql_fetch_assoc($result))
		{
			if (!isset($row['http_referer']) OR empty($row['http_referer']))
				++$websites['Direct link'];
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
	
	function hookAdminOrder($params)
	{
		$order = new Order(intval($params['id_order']));
		$result = Db::getInstance()->getRow('
		SELECT c.http_referer
		FROM '._DB_PREFIX_.'connections c
		LEFT JOIN '._DB_PREFIX_.'guest g ON c.id_guest = g.id_guest
		WHERE g.id_customer = '.intval($order->id_customer).'
		AND date_add < \''.pSQL($order->date_add).'\'
		ORDER BY date_add DESC');
		if (!isset($result['http_referer']))
			return;
		return '<fieldset style="width: 400px; margin-top: 10px;"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Origin').'</legend>
		'.$this->l('This customer seems to come').' '.(empty($result['http_referer']) ? $this->l('from a direct link') :  $this->l('from').' <a href="'.$result['http_referer'].'">'.preg_replace('/^www./', '', parse_url($result['http_referer'], PHP_URL_HOST)).'</a>').'.</fieldset>';
	}
	
	function hookAdminStatsModules()
	{
		$websites = $this->getOrigins();
		
		$this->_html = '<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> Origin</legend>';
		if (sizeof($websites))
		{
			$this->_html .= '
			<table class="table" border="0" cellspacing="0" cellspacing="0">
				<tr>
					<th style="width:400px;">'.$this->l('Origin').'</th>
					<th style="width:50px; text-align: right">'.$this->l('Total').'</th>
				</tr>';
			foreach ($websites as $website => $total)
				$this->_html .= '<tr><td>'.(!strstr($website, ' ') ? '<a href="http://'.$website.'">' : '').$website.(!strstr($website, ' ') ? '</a>' : '').'</td><td style="text-align: right">'.$total.'</td></tr>';
			$this->_html .= '</table><br /><center>'.ModuleGraph::engine(array('type' => 'pie')).'</center>';
		}
		else
			$this->_html .= '<p><strong>'.$this->l('Direct links only').'</strong></p>';
		$this->_html .= '</fieldset>';
		return $this->_html;
	}
		
	protected function getData()
	{
		$this->_titles['main'] = $this->l('10 first websites');
		$websites = $this->getOrigins();
		$total = 0;
		$total2 = 0;
		$i = 0;
		foreach ($websites as $website => $totalRow)
		{
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
