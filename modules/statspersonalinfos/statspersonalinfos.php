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
  
class StatsPersonalInfos extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
	private $_option;

    function __construct()
    {
        $this->name = 'statspersonalinfos';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
		parent::__construct();
		
        $this->displayName = $this->l('Registered Customer Info');
        $this->description = $this->l('Display characteristics such as gender and age');
	}
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
		
	public function hookAdminStatsModules($params)
	{
		$this->_html = '<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>';
		if (sizeof(Customer::getCustomers()))
			$this->_html .= '
			<center>
				<p><img src="../img/admin/down.gif" />'.$this->l('Gender distribution allows you to determine the percentage of men and women among your customers.').'</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'gender')).'<br class="clear" /><br />
				<p><img src="../img/admin/down.gif" />'.$this->l('Age ranges allows you to determine in which age range your customers are.').'</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'age')).'<br /><br />
				<p><img src="../img/admin/down.gif" />'.$this->l('Country distribution allows you to determine in which part of the world your customers are.').'</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'country')).'<br /><br />
				<p><img src="../img/admin/down.gif" />'.$this->l('Currency ranges allows you to determine with which currency your customers pay.').'</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'currency')).'<br /><br />
				<p><img src="../img/admin/down.gif" />'.$this->l('Language distribution allows you to determine the general language your customers use on your shop.').'</p>
				'.ModuleGraph::engine(array('type' => 'pie', 'option' => 'language')).'
			</center>';
		else
			$this->_html .= '<p>'.$this->l('No customers registered yet.').'</p>';
		$this->_html .= '
		</fieldset><br />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
			<h2>'.$this->l('Target your audience').'</h2>
			<p>
				'.$this->l('In order for each message to have an impact, you need to know to whom it should be addressed.').'
				'.$this->l('Addressing the right audience is essential for choosing the right tools for winning it over.').'
				'.$this->l('It\'s best to limit action to a group or groups of clients.').'
				'.$this->l('Registered customer information lets you more accurately define the typical customer profile so that you can adapt your specials to various criteria.').'
			</p><br />
			<p>
				'.$this->l('You should use this information for increasing your sales by:').'
				<ul>
					<li class="bullet">'.$this->l('Launching ad campaigns addressed to specific customers who might be interested in a particular offer, at specific dates and times').'</li>
					<li class="bullet">'.$this->l('Contacting a group of clients by e-mail / newsletter.').'</li>
				</ul>
			</p><br />
		</fieldset>';
		return $this->_html;
	}

	public function setOption($option, $layers = 1)
	{
		$this->_option = $option;
	}
	
	protected function getData($layers)
	{
		global $cookie;
		
		switch ($this->_option)
		{
			case 'gender':
				$this->_titles['main'] = $this->l('Gender distribution');
				$result = Db::getInstance()->ExecuteS('
				SELECT c.`id_gender`, COUNT(c.`id_customer`) AS total
				FROM `'._DB_PREFIX_.'customer` c
				GROUP BY c.`id_gender`');
				$gender = array(1 => $this->l('Male'), 2 => $this->l('Female'), 9 => $this->l('Unknown'));
				foreach ($result as $row)
				{
					$this->_values[] = $row['total'];
					$this->_legend[] = $gender[$row['id_gender']];
				}
				break;
			case 'age':
				$this->_titles['main'] = $this->l('Age ranges');
				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) < 18 
				AND c.`birthday` IS NOT NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('0-18 years old');
				}
				
				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) >= 18
				AND (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) < 25
				AND c.`birthday` IS NOT NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('18-24 years old');
				}

 				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) >= 25
				AND (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) < 35
				AND c.`birthday` IS NOT NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('25-34 years old');
				}
				
				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) >= 35
				AND (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) < 50
				AND c.`birthday` IS NOT NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('35-49 years old');
				}
				
				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) >= 50
				AND (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) < 60
				AND c.`birthday` IS NOT NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('50-59 years old');
				}
				
				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE (YEAR(CURDATE()) - YEAR(c.`birthday`)) - (RIGHT(CURDATE(), 5) < RIGHT(c.`birthday`, 5)) >= 60
				AND c.`birthday` IS NOT NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('60 years old and more');
				}
				
				$result = Db::getInstance()->getRow('
				SELECT COUNT(c.`id_customer`) as total
				FROM `'._DB_PREFIX_.'customer` c
				WHERE c.`birthday` IS NULL');
				if (isset($result['total']) AND $result['total'])
				{
					$this->_values[] = $result['total'];
					$this->_legend[] = $this->l('Unknown');
				}
				break;
			case 'country':
				$this->_titles['main'] = $this->l('Country distribution');
				$result = Db::getInstance()->ExecuteS('
				SELECT cl.`name`, COUNT(c.`id_country`) AS total
				FROM `'._DB_PREFIX_.'address` a
				LEFT JOIN `'._DB_PREFIX_.'country` c ON a.`id_country` = c.`id_country`
				LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '.intval($cookie->id_lang).')
				WHERE a.id_customer != 0
				GROUP BY c.`id_country`');
				foreach ($result as $row)
				{
				    $this->_values[] = $row['total'];
				    $this->_legend[] = $row['name'];
				}
				break;
			case 'currency':
				$this->_titles['main'] = $this->l('Currency distribution');
				$result = Db::getInstance()->ExecuteS('
				SELECT c.`name`, COUNT(c.`id_currency`) AS total
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.`id_currency` = c.`id_currency`
				GROUP BY c.`id_currency`');
				foreach ($result as $row)
				{
				    $this->_values[] = $row['total'];
				    $this->_legend[] = $row['name'];
				}
				break;
			case 'language':
				$this->_titles['main'] = $this->l('Language distribution');
				$result = Db::getInstance()->ExecuteS('
				SELECT c.`name`, COUNT(c.`id_lang`) AS total
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'lang` c ON o.`id_lang` = c.`id_lang`
				GROUP BY c.`id_lang`');
				foreach ($result as $row)
				{
				    $this->_values[] = $row['total'];
				    $this->_legend[] = $row['name'];
				}
				break;
		}
	}
}

?>
