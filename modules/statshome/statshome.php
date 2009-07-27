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
  
class StatsHome extends Module
{
    private $_html = '';
	private $_adminPath;

    function __construct()
    {
        $this->name = 'statshome';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
		
		$ru = dirname($_SERVER['REQUEST_URI'].'a');
		$this->_adminPath = substr($ru, strrpos($ru, '/'));
		
		parent::__construct();
		
        $this->displayName = $this->l('Condensed stats for the Back Office homepage');
        $this->description = $this->l('Display a small block of statistics on the Back Office homepage');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('backOfficeHome'));
	}
	
	private function _postProcess()
	{
		include_once(dirname(__FILE__).'/../..'.$this->_adminPath.'/tabs/AdminStats.php');
		$calendarTab = new AdminStats();
		$calendarTab->postProcess();
	}
	
	public function hookBackOfficeHome($params)
	{
		global $cookie;
		
		$this->_postProcess();
		$currency = Currency::getCurrency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
		$results = $this->getResults();

		$employee = new Employee(intval($cookie->id_employee));
		$id_tab_stats = Tab::getIdFromClassName('AdminStats');
		$access = Profile::getProfileAccess($employee->id_profile, $id_tab_stats);
		if (!$access['view'])
			return '';
		$this->_html = '
		<fieldset style="width:520px;">
			<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Statistics').'</legend>
			<div style="float:left;width:240px;text-align:center">
				<div style="float:left;width:120px;text-align:center">
					<center><p style="font-weight:bold;height:80px;width:100px;text-align:center;background-image:url(\''.__PS_BASE_URI__.'modules/statshome/square1.gif\')">
						<br /><br />'.Tools::displayPrice($results['total_sales'], $currency).'
					</p></center>
					<p>'.$this->l('of sales').'</p>
					<center><p style="font-weight:bold;height:80px;width:100px;text-align:center;background-image:url(\''.__PS_BASE_URI__.'modules/statshome/square3.gif\')">
						<br /><br />'.intval($results['total_registrations']).'
					</p></center>
					<p>'.(($results['total_registrations'] != 1) ? $this->l('registrations') : $this->l('registration')).'</p>
				</div>
				<div style="float:left;width:120px;text-align:center">
					<center><p style="font-weight:bold;height:80px;width:100px;text-align:center;background-image:url(\''.__PS_BASE_URI__.'modules/statshome/square2.gif\')">
						<br /><br />'.intval($results['total_orders']).'
					</p></center>
					<p>'.(($results['total_orders'] != 1) ? $this->l('orders placed') : $this->l('order placed')).'</p>
					<center><p style="font-weight:bold;height:80px;width:100px;text-align:center;background-image:url(\''.__PS_BASE_URI__.'modules/statshome/square4.gif\')">
						<br /><br />'.intval($results['total_viewed']).'
					</p></center>
					<p>'.(($results['total_viewed'] != 1) ? $this->l('product pages viewed') : $this->l('product page viewed')).'</p>
				</div>
			</div>
			<div style="float:right;text-align:right;width:240px">';
		include_once(dirname(__FILE__).'/../..'.$this->_adminPath.'/tabs/AdminStats.php');
		$this->_html .= AdminStatsTab::displayCalendarStatic(array('Calendar' => $this->l('Calendar'), 'Today' => $this->l('Today'), 'Month' => $this->l('Month'), 'Year' => $this->l('Year')));
		$this->_html .= '<div class="space"></div>
				<p style=" font-weight: bold ">'.$this->l('Visitors online now:').' '.intval($this->getVisitorsNow()).'</p>
			</div>
		</fieldset>
		<div class="clear space"><br /><br /></div>';
		return $this->_html;
	}
	
	private function getVisitorsNow()
	{
		return Db::getInstance()->getValue('
		SELECT COUNT(DISTINCT cp.`id_connections`)
		FROM `'._DB_PREFIX_.'connections_page` cp
		WHERE TIME_TO_SEC(TIMEDIFF(NOW(), cp.`time_start`)) < 900');
	}
	
	private function getResults()
	{
		$yearFrom = intval(Configuration::get('STATSHOME_YEAR_FROM'));
		$monthFrom = intval(Configuration::get('STATSHOME_MONTH_FROM'));
		$dayFrom = intval(Configuration::get('STATSHOME_DAY_FROM'));
		$yearTo = intval(Configuration::get('STATSHOME_YEAR_TO'));
		$monthTo = intval(Configuration::get('STATSHOME_MONTH_TO'));
		$dayTo = intval(Configuration::get('STATSHOME_DAY_TO'));
		if (!$yearFrom)
			Configuration::updateValue('STATSHOME_YEAR_FROM', $yearFrom = date('Y'));
		if (!$yearFrom)
			Configuration::updateValue('STATSHOME_YEAR_TO', $yearTo = date('Y'));
		$monthFrom = $monthFrom ? ((strlen($monthFrom) == 1 ? '0' : '').$monthFrom) : '01';
		$dayFrom = $dayFrom ? ((strlen($dayFrom) == 1 ? '0' : '').$dayFrom) : '01';
		$monthTo = $monthTo ? ((strlen($monthTo) == 1 ? '0' : '').$monthTo) : '12';
		$dayTo = $dayTo ? ((strlen($dayTo) == 1 ? '0' : '').$dayTo) : '31';

		$result = Db::getInstance()->getRow('
		SELECT SUM(o.`total_paid_real` / c.conversion_rate) as total_sales, COUNT(*) as total_orders
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.id_currency = c.id_currency
		WHERE o.valid = 1
		AND o.`invoice_date` BETWEEN '.ModuleGraph::getDateBetween());
		$result2 = Db::getInstance()->getRow('
		SELECT COUNT(`id_customer`) AS total_registrations
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`date_add` BETWEEN '.ModuleGraph::getDateBetween());
		$result3 = Db::getInstance()->getRow('
		SELECT SUM(pv.`counter`) AS total_viewed
		FROM `'._DB_PREFIX_.'page_viewed` pv
		LEFT JOIN `'._DB_PREFIX_.'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
		LEFT JOIN `'._DB_PREFIX_.'page` p ON pv.`id_page` = p.`id_page`
		LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
		WHERE pt.`name` = \'product.php\'
		AND dr.`time_start` BETWEEN '.ModuleGraph::getDateBetween().'
		AND dr.`time_end` BETWEEN '.ModuleGraph::getDateBetween());	
		return array_merge($result, array_merge($result2, $result3));
	}
}

?>
