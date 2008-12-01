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

include_once(dirname(__FILE__).'/AdminStatsTab.php');

class AdminStats extends AdminStatsTab
{
	private static $validOrder;
	
	private static function recordQuery($dateLike, $format, $order)
	{
		return Db::getInstance()->getRow('
		SELECT records.`date`, SUM(records.`ht`) as totalht, SUM(records.`ttc`) as totalttc
		FROM (
			SELECT date_format(o.`date_add`, \''.$format.'\') as date, o.`total_paid` as ttc, o.`total_products` as ht
			FROM `'._DB_PREFIX_.'orders` o
			'.self::$validOrder.'
			AND o.`date_add` LIKE \''.pSQL($dateLike).'\') records
		GROUP BY records.date
		ORDER BY SUM(records.ht) '.$order);
	}
	
	public static function getRecords($dateLike)
	{
		$xtrems = array();
		$xtrems['bestmonth'] = self::recordQuery($dateLike, '%Y-%m', 'DESC');
		$xtrems['worstmonth'] = self::recordQuery($dateLike, '%Y-%m', 'ASC');
		$xtrems['bestday'] = self::recordQuery($dateLike, '%Y-%m-%d', 'DESC');
		$xtrems['worstday'] = self::recordQuery($dateLike, '%Y-%m-%d', 'ASC');
		if ($xtrems['bestmonth'])
			return $xtrems;
	}
	
	public static function getSales($dateLike)
	{	
		$result = Db::getInstance()->getRow('
		SELECT COUNT(DISTINCT o.`id_order`) as orders, SUM(o.`total_paid`) as ttc, SUM(o.`total_products`) as ht
		FROM `'._DB_PREFIX_.'orders` o
		'.self::$validOrder.'
		AND o.`date_add` LIKE \''.pSQL($dateLike).'\'');
		
		$products = Db::getInstance()->getRow('
		SELECT COUNT(od.`id_order_detail`) as products
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON o.`id_order` = od.`id_order`
		'.self::$validOrder.'
		AND o.`date_add` LIKE \''.pSQL($dateLike).'\'');
		
		$xtrems = Db::getInstance()->getRow('
		SELECT MAX(`total_products`) as maxht, MIN(`total_products`) as minht, MAX(`total_paid`) as maxttc, MIN(`total_paid`) as minttc
		FROM (
			SELECT o.`total_paid`, o.`total_products`
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON o.`id_order` = od.`id_order`
			'.self::$validOrder.'
			AND o.`date_add` LIKE \''.pSQL($dateLike).'\') records');
		return array_merge($result, array_merge($xtrems, $products));
	}
	
	public static function getCarts($dateLike)
	{
		$xtrems = Db::getInstance()->getRow('
		SELECT AVG(cartsum) as avg, MAX(cartsum) as max, MIN(cartsum) as min
		FROM (
			SELECT SUM(p.`price`) as cartsum
			FROM `'._DB_PREFIX_.'cart` c
			LEFT JOIN `'._DB_PREFIX_.'cart_product` cp ON c.`id_cart` = cp.`id_cart`
			LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
			WHERE c.`date_upd` LIKE \''.pSQL($dateLike).'\'
			GROUP BY c.`id_cart`) carts');
		return array('average' => $xtrems['avg'], 'highest' => $xtrems['max'], 'lowest' => $xtrems['min']);
	}
	
	public function display()
	{
		global $cookie;
	
		self::$validOrder = '
		WHERE (
			SELECT os.`invoice`
			FROM `'._DB_PREFIX_.'orders` oo
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON oh.`id_order` = oo.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			WHERE oo.`id_order` = o.`id_order`
			ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		) = 1';
	
		$currency = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$language = Language::getLanguage(intval($cookie->id_lang));
		$iso = $language['iso_code'];
		$dateLike = ModuleGraph::getDateLike();
		$sales = self::getSales($dateLike);
		$carts = self::getCarts($dateLike);
		$records = self::getRecords($dateLike);
	
		echo '
		<div style="float: left">';
		$this->displayCalendar();
		$this->displayMenu(false);
		$this->displayEngines();
		$this->displaySearch();
		echo '
		</div>
		<div style="float: left; margin-left: 40px;">
			<fieldset class="width3"><legend><img src="../img/admin/tab-stats.gif" /> '.$this->l('Help').'</legend>
				<p>
					'.$this->l('Use the calendar on the left to select the time period.').'<br />
					'.$this->l('First, click on a unit of time (D = one day, M = one month, Y = one year), then choose the date.').'<br />
				</p>
				<p>
					'.$this->l('All available statistic modules are displayed in the Navigation list beneath the calendar.').'
				</p>
				<p>
					'.$this->l('In the Settings sub-tab, you can also customize the Stats tab to fit your needs and resources, change the graph rendering engine, and adjust the database settings.').'
				</p>
			</fieldset>
			<fieldset class="space"><legend><img src="../img/admin/___info-ca.gif" style="vertical-align: middle" /> '.$this->l('Sales').'</legend>
				<table>
					<tr><td style="font-weight: bold">'.$this->l('Total placed orders').'</td><td style="padding-left: 20px">'.$sales['orders'].'</td></tr>
					<tr><td style="font-weight: bold">'.$this->l('Total products sold').'</td><td style="padding-left: 20px">'.$sales['products'].'</td></tr>
				</table>
				<table cellspacing="0" cellpadding="0" class="table space">
					<tr>
						<th style="width: 150px"></th>
						<th style="width: 120px; text-align: center; font-weight: bold">'.$this->l('with tax').'</th>
						<th style="width: 180px; text-align: center; font-weight: bold">'.$this->l('only products not incl. tax').'</th>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Sales turnover').'</th>
						<td align="right">'.Tools::displayPrice($sales['ttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($sales['ht'], $currency).'</td>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Largest order').'</th>
						<td align="right">'.Tools::displayPrice($sales['maxttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($sales['maxht'], $currency).'</td>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Smallest order').'</th>
						<td align="right">'.Tools::displayPrice($sales['minttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($sales['minht'], $currency).'</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="space"><legend><img src="../img/admin/products.gif" style="vertical-align: middle" /> '.$this->l('Carts (pre-tax prices)').'</legend>
				<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th style="width: 150px"></th>
						<th style="width: 180px; text-align: center; font-weight: bold">'.$this->l('only products not incl. tax').'</th>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Average cart').'</th>
						<td align="right">'.Tools::displayPrice($carts['average'], $currency).'</td>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Largest cart').'</th>
						<td align="right">'.Tools::displayPrice($carts['highest'], $currency).'</td>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Smallest cart').'</th>
						<td align="right">'.Tools::displayPrice($carts['lowest'], $currency).'</td>
					</tr>
				</table>
			</fieldset>';
			
		if (strtolower($cookie->stats_granularity) != 'd' AND $records AND is_array($records))
		{
			echo '
			<fieldset class="space"><legend><img src="../img/admin/medal.png" style="vertical-align: middle" />'.$this->l('Records').'</legend>
				<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th style="width: 150px"></th>
						<th style="width: 100px; text-align: center; font-weight: bold">'.$this->l('date').'</th>
						<th style="width: 120px; text-align: center; font-weight: bold">'.$this->l('with tax').'</th>
						<th style="width: 180px; text-align: center; font-weight: bold">'.$this->l('only products not incl. tax').'</th>
					</tr>';
			if (strtolower($cookie->stats_granularity) == 'y')
				echo '
					<tr>
						<th style="font-weight: bold">'.$this->l('Best month').'</th>
						<td align="right">'.$this->displayDate($records['bestmonth']['date'], $iso).'</td>
						<td align="right">'.Tools::displayPrice($records['bestmonth']['totalttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($records['bestmonth']['totalht'], $currency).'</td>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Worst month').'</th>
						<td align="right">'.$this->displayDate($records['worstmonth']['date'], $iso).'</td>
						<td align="right">'.Tools::displayPrice($records['worstmonth']['totalttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($records['worstmonth']['totalht'], $currency).'</td>
					</tr>';
			echo '
					<tr>
						<th style="font-weight: bold">'.$this->l('Best day').'</th>
						<td align="right">'.$this->displayDate($records['bestday']['date'], $iso).'</td>
						<td align="right">'.Tools::displayPrice($records['bestday']['totalttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($records['bestday']['totalht'], $currency).'</td>
					</tr>
					<tr>
						<th style="font-weight: bold">'.$this->l('Worst day').'</th>
						<td align="right">'.$this->displayDate($records['worstday']['date'], $iso).'</td>
						<td align="right">'.Tools::displayPrice($records['worstday']['totalttc'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($records['worstday']['totalht'], $currency).'</td>
					</tr>
				</table>
			</fieldset>';
		}
		
		echo '
		</div>
		<div class="clear"></div>';
	}
	
	static public function displayDate($date, $iso)
	{
	 	$tmpTab = explode('-', $date);
	 	if (strtolower($iso == 'fr'))
	 		return (isset($tmpTab[2]) ? ($tmpTab[2].'-') : '').$tmpTab[1].'-'.$tmpTab[0];
	 	else
	 		return $tmpTab[0].'-'.$tmpTab[1].(isset($tmpTab[2]) ? ('-'.$tmpTab[2]) : '');
	}
}

?>
