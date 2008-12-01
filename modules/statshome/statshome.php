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
  
class StatsHome extends Module
{
    private $_html = '';

    function __construct()
    {
        $this->name = 'statshome';
        $this->tab = 'Stats';
        $this->version = 1.0;
		$this->page = basename(__FILE__, '.php');
		
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
		if (Tools::isSubmit('submitStats'))
		{
			Configuration::updateValue('STATSHOME_YEAR_FROM', ($year = intval(Tools::getValue('statsYearFrom')) AND $year > 1970 AND $year < 2070) ? $year : date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_FROM', ($month = intval(Tools::getValue('statsMonthFrom')) AND $month > 0 AND $month <= 12) ? $month : 0);
			Configuration::updateValue('STATSHOME_DAY_FROM', ($month AND $day = intval(Tools::getValue('statsDayFrom')) AND $day > 0 AND $day < 31) ? $day : 0);
			Configuration::updateValue('STATSHOME_YEAR_TO', ($year = intval(Tools::getValue('statsYearTo')) AND $year > 1970 AND $year < 2070) ? $year : date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_TO', ($month = intval(Tools::getValue('statsMonthTo')) AND $month > 0 AND $month <= 12) ? $month : 0);
			Configuration::updateValue('STATSHOME_DAY_TO', ($month AND $day = intval(Tools::getValue('statsDayTo')) AND $day > 0 AND $day < 31) ? $day : 0);
		}
		if (Tools::isSubmit('submitStatsToday'))
		{
			Configuration::updateValue('STATSHOME_YEAR_FROM', date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_FROM', date('m'));
			Configuration::updateValue('STATSHOME_DAY_FROM', date('d'));
			Configuration::updateValue('STATSHOME_YEAR_TO', date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_TO', date('m'));
			Configuration::updateValue('STATSHOME_DAY_TO', date('d'));
		}
		if (Tools::isSubmit('submitStatsMonth'))
		{
			Configuration::updateValue('STATSHOME_YEAR_FROM', date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_FROM', date('m'));
			Configuration::updateValue('STATSHOME_DAY_FROM', 0);
			Configuration::updateValue('STATSHOME_YEAR_TO', date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_TO', date('m'));
			Configuration::updateValue('STATSHOME_DAY_TO', 0);
		}
		if (Tools::isSubmit('submitStatsYear'))
		{
			Configuration::updateValue('STATSHOME_YEAR_FROM', date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_FROM', 0);
			Configuration::updateValue('STATSHOME_DAY_FROM', 0);
			Configuration::updateValue('STATSHOME_YEAR_TO', date('Y'));
			Configuration::updateValue('STATSHOME_MONTH_TO', 0);
			Configuration::updateValue('STATSHOME_DAY_TO', 0);
		}
	}
	
	public function hookBackOfficeHome($params)
	{
		global $cookie;
		
		$this->_postProcess();
		$currency = Currency::getCurrency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
		$results = $this->getResults();
	
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
			<div style="float:left;text-align:right;width:240px">
			<form action="index.php" method="post">
				<p>
					<input type="submit" name="submitStatsToday" class="button" value="'.$this->l('Today').'">
					<input type="submit" name="submitStatsMonth" class="button" value="'.$this->l('Month').'">
					<input type="submit" name="submitStatsYear" class="button" value="'.$this->l('Year').'">
				</p>
				<p>
					'.$this->l('From year').' <input type="text" name="statsYearFrom" style="width: 40px" value="'.(Configuration::get('STATSHOME_YEAR_FROM') ? Configuration::get('STATSHOME_YEAR_FROM') : date('Y')).'">
					'.$this->l('to').' <input type="text" name="statsYearTo" style="width: 40px" value="'.(Configuration::get('STATSHOME_YEAR_TO') ? Configuration::get('STATSHOME_YEAR_TO') : date('Y')).'">
				</p>
				<p>
					'.$this->l('From month').' <input type="text" name="statsMonthFrom" style="width: 40px" value="'.(Configuration::get('STATSHOME_MONTH_FROM')).'">
					'.$this->l('to').' <input type="text" name="statsMonthTo" style="width: 40px" value="'.(Configuration::get('STATSHOME_MONTH_TO')).'">
				</p>
				<p>
					'.$this->l('From day').' <input type="text" name="statsDayFrom" style="width: 40px" value="'.(Configuration::get('STATSHOME_DAY_FROM')).'">
					'.$this->l('to').' <input type="text" name="statsDayTo" style="width: 40px" value="'.(Configuration::get('STATSHOME_DAY_TO')).'">
				</p>
				<p>
					<input type="submit" name="submitStats" class="button" value="'.$this->l('OK').'">
				</p>
				<div class="space"></div>
				<p style=" font-weight: bold">'.$this->l('Visitors online now:').' '.intval($this->getVisitorsNow()).'</p>
			</form>
			</div>
		</fieldset>
		<div class="clear"></div>';
		return $this->_html;
	}
	
	private function getVisitorsNow()
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT cp.`id_connections`
		FROM `'._DB_PREFIX_.'connections_page` cp
		WHERE cp.`time_end` IS NULL
		AND TIME_TO_SEC(TIMEDIFF(NOW(), cp.`time_start`)) < 900
		GROUP BY cp.`id_connections`');
		return Db::getInstance()->NumRows();
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
		SELECT SUM(o.`total_paid_real`) as total_sales, COUNT(o.`total_paid_real`) as total_orders
		FROM `'._DB_PREFIX_.'orders` o
		WHERE (
			SELECT IF(os.`id_order_state` = 8, 0, 1)
			FROM `'._DB_PREFIX_.'orders` oo
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON oh.`id_order` = oo.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			WHERE oo.`id_order` = o.`id_order`
			ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		) = 1
		AND o.`date_add` >= \''.pSQL($yearFrom).'-'.pSQL($monthFrom).'-'.pSQL($dayFrom).' 00:00:00\'
		AND o.`date_add` <= \''.pSQL($yearTo).'-'.pSQL($monthTo).'-'.pSQL($dayTo).' 23:59:59\'');
		$result2 = Db::getInstance()->getRow('
		SELECT COUNT(`id_customer`) AS total_registrations
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`date_add` >= \''.pSQL($yearFrom).'-'.pSQL($monthFrom).'-'.pSQL($dayFrom).' 00:00:00\'
		AND c.`date_add` <= \''.pSQL($yearTo).'-'.pSQL($monthTo).'-'.pSQL($dayTo).' 23:59:59\'');
		$result3 = Db::getInstance()->getRow('
		SELECT SUM(pv.`counter`) AS total_viewed
		FROM `'._DB_PREFIX_.'page_viewed` pv
		LEFT JOIN `'._DB_PREFIX_.'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
		LEFT JOIN `'._DB_PREFIX_.'page` p ON pv.`id_page` = p.`id_page`
		LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
		WHERE pt.`name` = \'product.php\'
		AND dr.`time_start` >= \''.pSQL($yearFrom).'-'.pSQL($monthFrom).'-'.pSQL($dayFrom).' 00:00:00\'
		AND dr.`time_end` <= \''.pSQL($yearTo).'-'.pSQL($monthTo).'-'.pSQL($dayTo).' 23:59:59\'');	
		return array_merge($result, array_merge($result2, $result3));
	}
}

?>
