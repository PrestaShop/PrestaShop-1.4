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
  
class StatsSales extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';
    private $_option = '';
    private $id_country = '';

    function __construct()
    {
        $this->name = 'statssales';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
		parent::__construct();
		
        $this->displayName = $this->l('Sales and orders');
        $this->description = $this->l('Display the sales evolution and orders by statuses');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
		
	public function hookAdminStatsModules($params)
	{
		global $cookie;
		
		$totals = $this->getTotals();
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="float: right; margin-left: 10px;">
				<select name="id_country">
					<option value="0"'.((!Tools::getValue('id_order_state')) ? ' selected="selected"' : '').'>'.$this->l('All').'</option>';
		foreach (Country::getCountries($cookie->id_lang) AS $country)
			$this->_html .= '<option value="'.$country['id_country'].'"'.(($country['id_country'] == Tools::getValue('id_country')) ? ' selected="selected"' : '').'>'.$country['name'].'</option>';
		$this->_html .= '</select>
				<input type="submit" name="submitCountry" value="'.$this->l('Filter').'" class="button" />
			</form>
			<p><center><img src="../img/admin/down.gif" />
				'.$this->l('These graphs represent the evolution of your orders and sales turnover for a given period. It is not an advanced analysis tools, but at least you can overview the rentability of your shop in a flash. You can also keep a watch on the difference with some periods like Christmas. Only valid orders are included in theses two graphs.').'
			</center></p>
			<p>'.$this->l('Total orders placed:').' '.intval($totals['allOrderCount']).'</p>
			<p>'.$this->l('Total orders placed (valid):').' '.intval($totals['orderCount']).'</p>
			<p>'.$this->l('Total products ordered (valid):').' '.intval($totals['products']).'</p>
			<center>'.ModuleGraph::engine(array('type' => 'line', 'option' => '1-'.intval(Tools::getValue('id_country')), 'layers' => 3)).'</center>
			<p>'.$this->l('Sales:').' '.Tools::displayPrice($totals['orderSum'], $currency).'</p>
			<center>'.ModuleGraph::engine(array('type' => 'line', 'option' => '2-'.intval(Tools::getValue('id_country')))).'<br /><br />
			<p class="space"><img src="../img/admin/down.gif" />
				'.$this->l('You can see the order state distribution below.').'
			</p><br />
			'.($totals['orderCount'] ? ModuleGraph::engine(array('type' => 'pie', 'option' => '3-'.intval(Tools::getValue('id_country')))) : $this->l('No order for this period')).'</center>
		</fieldset>
		<br class="clear" />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
			<h2>'.$this->l('Various order status').'</h2>
			<p>
				'.$this->l('In your back-office, you can find many order status : Awaiting cheque payment, Payment accepted, Preparation in progress, Shipping, Delivered, Canceled, Refund, Payment error, Out of stock, and Awaiting bank wire payment.
				These status cannot be removed from the back-office, but you have the possibility to add some more.').'
			</p>
		</fieldset>';
		return $this->_html;
	}

	private function getTotals()
	{
		$result0 = Db::getInstance()->getRow('
		SELECT COUNT(o.`id_order`) as allOrderCount, SUM(o.`total_paid_real`) / c.conversion_rate as orderSum
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.id_currency = c.id_currency
		'.(intval(Tools::getValue('id_country')) ? 'LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address' : '').'
		WHERE o.`invoice_date` BETWEEN '.ModuleGraph::getDateBetween().'
		'.(intval(Tools::getValue('id_country')) ? 'AND a.id_country = '.intval(Tools::getValue('id_country')) : ''));
		$result1 = Db::getInstance()->getRow('
		SELECT COUNT(o.`id_order`) as orderCount, SUM(o.`total_paid_real`) / c.conversion_rate as orderSum
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.id_currency = c.id_currency
		'.(intval(Tools::getValue('id_country')) ? 'LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address' : '').'
		WHERE o.valid = 1
		'.(intval(Tools::getValue('id_country')) ? 'AND a.id_country = '.intval(Tools::getValue('id_country')) : '').'
		AND o.`invoice_date` BETWEEN '.ModuleGraph::getDateBetween());
		$result2 = Db::getInstance()->getRow('
		SELECT SUM(od.product_quantity) as products
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
		'.(intval(Tools::getValue('id_country')) ? 'LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address' : '').'
		WHERE o.valid = 1
		'.(intval(Tools::getValue('id_country')) ? 'AND a.id_country = '.intval(Tools::getValue('id_country')) : '').'
		AND o.`invoice_date` BETWEEN '.ModuleGraph::getDateBetween());
		return array_merge(array_merge($result0, $result1), $result2);
	}
	
	public function setOption($options, $layers = 1)
	{
		list($this->_option, $this->id_country) = explode('-', $options);
		switch ($this->_option)
		{
			case 1:
				$this->_titles['main'][0] = $this->l('Number of orders and products ordered');
				$this->_titles['main'][1] = $this->l('Orders');
				$this->_titles['main'][2] = $this->l('Orders (valid)');
				$this->_titles['main'][3] = $this->l('Products (valid)');
				break;
			case 2:
				$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
				$this->_titles['main'] = $this->l('Sales in').' '.$currency->iso_code;
				break;
			case 3:
				$this->_titles['main'] = $this->l('Percentage of orders by status');
				break;
		}
	}
	
	protected function getData($layers)
	{
		if ($this->_option == 3)
			return $this->getStatesData();
			
		$this->_query0 = '
			SELECT o.`invoice_date`, o.`total_paid_real` / c.conversion_rate AS total_paid_real, SUM(od.product_quantity) as product_quantity
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.id_currency = c.id_currency
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
			'.(intval($this->id_country) ? 'LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address' : '').'
			'.(intval($this->id_country) ? 'WHERE a.id_country = '.intval($this->id_country).' AND ' : 'WHERE ').'
			o.`invoice_date` BETWEEN ';
		$this->_query = '
			SELECT o.`invoice_date`, o.`total_paid_real` / c.conversion_rate AS total_paid_real, SUM(od.product_quantity) as product_quantity
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.id_currency = c.id_currency
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
			'.(intval($this->id_country) ? 'LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address' : '').'
			WHERE o.valid = 1
			'.(intval($this->id_country) ? 'AND a.id_country = '.intval($this->id_country) : '').'
			AND o.`invoice_date` BETWEEN ';
		$this->_query2 = ' GROUP BY o.id_order';
		$this->setDateGraph($layers, true);
	}
	
	protected function setYearValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.$this->getDate().$this->_query2);
		foreach ($result AS $row)
			if ($this->_option == 1)
			{
				$this->_values[1][intval(substr($row['invoice_date'], 5, 2))] += 1;
				$this->_values[2][intval(substr($row['invoice_date'], 5, 2))] += $row['product_quantity'];
			}
			else
				$this->_values[intval(substr($row['invoice_date'], 5, 2))] += $row['total_paid_real'];
		
		if ($this->_option == 1)
		{
			$result = Db::getInstance()->ExecuteS($this->_query0.$this->getDate().$this->_query2);
			foreach ($result AS $row)
				$this->_values[0][intval(substr($row['invoice_date'], 5, 2))] += 1;
		}
	}
	
	protected function setMonthValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.$this->getDate().$this->_query2);
		foreach ($result AS $row)
			if ($this->_option == 1)
			{
				$this->_values[1][intval(substr($row['invoice_date'], 8, 2))] += 1;
				$this->_values[2][intval(substr($row['invoice_date'], 8, 2))] += $row['product_quantity'];
			}
			else
				$this->_values[intval(substr($row['invoice_date'], 8, 2))] += $row['total_paid_real'];
		
		if ($this->_option == 1)
		{
			$result = Db::getInstance()->ExecuteS($this->_query0.$this->getDate().$this->_query2);
			foreach ($result AS $row)
				$this->_values[0][intval(substr($row['invoice_date'], 8, 2))] += 1;
		}
	}

	protected function setDayValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.$this->getDate().$this->_query2);
		foreach ($result AS $row)
			if ($this->_option == 1)
			{
				$this->_values[1][intval(substr($row['invoice_date'], 11, 2))] += 1;
				$this->_values[2][intval(substr($row['invoice_date'], 11, 2))] += $row['product_quantity'];
			}
			else
				$this->_values[intval(substr($row['invoice_date'], 11, 2))] += $row['total_paid_real'];
		
		if ($this->_option == 1)
		{
			$result = Db::getInstance()->ExecuteS($this->_query0.$this->getDate().$this->_query2);
			foreach ($result AS $row)
				$this->_values[0][intval(substr($row['invoice_date'], 11, 2))] += 1;
		}
	}
	
	private function getStatesData()
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT osl.`name`, COUNT(oh.`id_order`) as total
		FROM `'._DB_PREFIX_.'order_state` os
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.intval($this->getLang()).')
		LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON os.`id_order_state` = oh.`id_order_state`
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_order` = oh.`id_order`
		'.(intval($this->id_country) ? 'LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address' : '').'
		WHERE oh.`id_order_history` = (
			SELECT ios.`id_order_history`
			FROM `'._DB_PREFIX_.'order_history` ios
			WHERE ios.`id_order` = oh.`id_order`
			ORDER BY ios.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		)
		'.(intval($this->id_country) ? 'AND a.id_country = '.intval($this->id_country) : '').'
		AND o.`date_add` BETWEEN '.ModuleGraph::getDateBetween().'
		GROUP BY oh.`id_order_state`');
		foreach ($result as $row)
		{
		    $this->_values[] = $row['total'];
		    $this->_legend[] = $row['name'];
		}
	}
}

?>
