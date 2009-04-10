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
  
class StatsEquipment extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';

    function __construct()
    {
        $this->name = 'statsequipment';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
		parent::__construct();
		
        $this->displayName = $this->l('Software');
        $this->description = $this->l('Display the software used by your visitors');
	}
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	private function getEquipment()
	{
		$result = mysql_query('
		SELECT DISTINCT g.*
		FROM `'._DB_PREFIX_.'connections` c 
		LEFT JOIN `'._DB_PREFIX_.'guest` g ON g.`id_guest` = c.`id_guest`
		WHERE c.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
		
		$calcArray = array('jsOK' => 0, 'jsKO' => 0, 'javaOK' => 0, 'javaKO' => 0, 'wmpOK' => 0, 'wmpKO' => 0, 'qtOK' => 0, 'qtKO' => 0, 'realOK' => 0, 'realKO' => 0, 'flashOK' => 0, 'flashKO' => 0, 'directorOK' => 0, 'directorKO' => 0);
		while ($row = mysql_fetch_assoc($result))
		{
			if (!$row['javascript'])
			{
				++$calcArray['jsKO'];
				continue;
			}
			++$calcArray['jsOK'];
			($row['windows_media']) ? ++$calcArray['wmpOK'] : ++$calcArray['wmpKO'];
			($row['real_player']) ? ++$calcArray['realOK'] : ++$calcArray['realKO'];
			($row['adobe_flash']) ? ++$calcArray['flashOK'] : ++$calcArray['flashKO'];
			($row['adobe_director']) ? ++$calcArray['directorOK'] : ++$calcArray['directorKO'];
			($row['sun_java']) ? ++$calcArray['javaOK'] : ++$calcArray['javaKO'];
			($row['apple_quicktime']) ? ++$calcArray['qtOK'] : ++$calcArray['qtKO'];
		}
		mysql_free_result($result);
		if (!$calcArray['jsOK'])
			return false;
			
		$equip = array(
			'Windows Media Player' => $calcArray['wmpOK'] / ($calcArray['wmpOK'] + $calcArray['wmpKO']),
			'Real Player' => $calcArray['realOK'] / ($calcArray['realOK'] + $calcArray['realKO']),
			'Apple Quicktime' => $calcArray['qtOK'] / ($calcArray['qtOK'] + $calcArray['qtKO']),
			'Sun Java' => $calcArray['javaOK'] / ($calcArray['javaOK'] + $calcArray['javaKO']),
			'Adobe Flash' => $calcArray['flashOK'] / ($calcArray['flashOK'] + $calcArray['flashKO']),
			'Adobe Shockwave' => $calcArray['directorOK'] / ($calcArray['directorOK'] + $calcArray['directorKO'])
		);
		arsort($equip);
		return $equip;
	}
	
	public function hookAdminStatsModules($params)
	{
		$equipment = $this->getEquipment();
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<center>
				<p><img src="../img/admin/down.gif" />Determine the percentage of web browser used by your customers.</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'wb')).'<br /><br />
				<p><img src="../img/admin/down.gif" />Determine the percentage of operating systems used by your customers.</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'os')).'';
		if ($equipment)
		{
			$this->_html .= '<table class="table space" border="0" cellspacing="0" cellpadding="0">
			<tr><th style="width: 200px">'.$this->l('Plug-ins').'</th><th></th></tr>';
			foreach ($equipment as $name => $value)	
				$this->_html .= '<tr><td>'.$name.'</td><td>'.number_format(100 * $value, 2).'%</td></tr>';
			$this->_html .= '</table>';
		}
		$this->_html .= '
			</center>
		</fieldset><br />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
		<h2>'.$this->l('Ensure that your website is accessible to all').'</h2>
			<p>
				'.$this->l('When managing Websites, it is important to keep track of software used by visitors in order to be sure that the site displays the same way for everyone, and PrestaShop was built in order to be compatible with most recent Web browsers and computer operating systems (OS). However, because you may end up adding advanced features to your Website or even modify the core PrestaShop code, these additions may not be accessible by everyone. That is why it is a good idea to keep tabs on the percentage of users for each type of software before adding or changing something that only a limited number of users will be able to access.').'
			</p><br />
			
		</fieldset>';
		return $this->_html;
	}

	public function setOption($option, $layers = 1)
	{
		switch($option)
		{
			case 'wb':
				$this->_titles['main'] = $this->l('Web browser use');
				$this->_query = '
					SELECT wb.`name`, COUNT(g.`id_web_browser`) AS total
					FROM `'._DB_PREFIX_.'web_browser` wb
					LEFT JOIN `'._DB_PREFIX_.'guest` g ON g.`id_web_browser` = wb.`id_web_browser`
					LEFT JOIN `'._DB_PREFIX_.'connections` c ON g.`id_guest` = c.`id_guest`
					WHERE c.`date_add` BETWEEN ';
				$this->_query2 = ' GROUP BY g.`id_web_browser`';
				break;
			case 'os':
				$this->_titles['main'] = $this->l('Operating systems use');
				$this->_query = '
					SELECT os.`name`, COUNT(g.`id_operating_system`) AS total
					FROM `'._DB_PREFIX_.'operating_system` os
					LEFT JOIN `'._DB_PREFIX_.'guest` g ON g.`id_operating_system` = os.`id_operating_system`
					LEFT JOIN `'._DB_PREFIX_.'connections` c ON g.`id_guest` = c.`id_guest`
					WHERE c.`date_add` BETWEEN ';
				$this->_query2 = ' GROUP BY g.`id_operating_system`';
				break;
		}
	}
	
	protected function getData($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.$this->getDate().$this->_query2);
		$this->_values = array();
		$i = 0;
		foreach ($result as $row)
		{
		    $this->_values[$i] = $row['total'];
		    $this->_legend[$i++] = $row['name'];
		}
	}
}

?>
