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
  
class StatsSales extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';
    private $_option = '';

    function __construct()
    {
        $this->name = 'statssales';
        $this->tab = 'Stats';
        $this->version = 1.0;
		$this->page = basename(__FILE__, '.php');
		
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
		$totals = $this->getTotals();
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$result = Db::getInstance()->ExecuteS('
		SELECT osl.`name`, COUNT(oh.`id_order`) as total
		FROM `'._DB_PREFIX_.'order_state` os
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = 2)
		LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON os.`id_order_state` = oh.`id_order_state`
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_order` = oh.`id_order`
		WHERE oh.`id_order_history` = (
			SELECT ios.`id_order_history`
			FROM `'._DB_PREFIX_.'order_history` ios
			WHERE ios.`id_order` = oh.`id_order`
			ORDER BY ios.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		)
		AND o.`date_add` LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'
		GROUP BY oh.`id_order_state`');
		$numRows = Db::getInstance()->NumRows();
		
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<p><center><img src="../img/admin/down.gif" />
				'.$this->l('These graphs represent the evolution of your orders and sales turnover for a given period. It is not an advanced analysis tools, but at least you can overview the rentability of your shop in a flash. You can also keep a watch on the difference with some periods like Christmas. Only valid orders are included in theses two graphs.').'
			</center></p>
			<p>'.$this->l('Total orders placed:').' '.intval($totals['orderCount']).'</p>
			<p>'.$this->l('Total products ordered:').' '.intval($totals['products']).'</p>
			<center>'.ModuleGraph::engine(array('type' => 'line', 'option' => 1, 'layers' => 2)).'</center>
			<p>'.$this->l('Sales:').' '.Tools::displayPrice($totals['orderSum'], $currency).'</p>
			<center>'.ModuleGraph::engine(array('type' => 'line', 'option' => 2)).'<br /><br />
			<p class="space"><img src="../img/admin/down.gif" />
				'.$this->l('You can see the order state distribution below.').'
			</p><br />
			'.($numRows ? ModuleGraph::engine(array('type' => 'pie', 'option' => 3)) : $this->l('No order for this period')).'</center>
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
		$result1 = Db::getInstance()->getRow('
		SELECT COUNT(o.`id_order`) as orderCount, SUM(o.`total_paid_real`) as orderSum
		FROM `'._DB_PREFIX_.'orders` o
		WHERE (
			SELECT os.`invoice`
			FROM `'._DB_PREFIX_.'orders` oo
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON oh.`id_order` = oo.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			WHERE oo.`id_order` = o.`id_order`
			ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		) = 1
		AND o.`date_add` LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'');
		$result2 = Db::getInstance()->getRow('
		SELECT SUM(od.product_quantity) as products
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
		WHERE (
			SELECT os.`invoice`
			FROM `'._DB_PREFIX_.'orders` oo
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON oh.`id_order` = oo.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			WHERE oo.`id_order` = o.`id_order`
			ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		) = 1
		AND o.`date_add` LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'');
		return array_merge($result1, $result2);
	}
	
	public function setOption($option, $layers = 1)
	{
		switch ($option)
		{
			case 1:
				$this->_titles['main'][0] = $this->l('Number of orders and products ordered');
				$this->_titles['main'][1] = $this->l('Orders');
				$this->_titles['main'][2] = $this->l('Products');
				$this->_option = 1;
				break;
			case 2:
				$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
				$this->_titles['main'] = $this->l('Sales in').' '.$currency->iso_code;
				$this->_option = 2;
				break;
			case 3:
				$this->_titles['main'] = $this->l('Percentage of orders by status');
				$this->_option = 3;
				break;
		}
	}
	
	protected function getData($layers)
	{
		if ($this->_option == 3)
			return $this->getStatesData();
			
		$this->_query = '
			SELECT o.`date_add`, o.`total_paid_real`, SUM(od.product_quantity) as product_quantity
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
			WHERE (
				SELECT os.`invoice`
				FROM `'._DB_PREFIX_.'orders` oo
				LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON oh.`id_order` = oo.`id_order`
				LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
				WHERE oo.`id_order` = o.`id_order`
				ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
				LIMIT 1
			) = 1
			AND o.`date_add` LIKE \'';
		$this->_query2 = '\'
			GROUP BY o.id_order';
		$this->setDateGraph($layers, true);
	}
	
	protected function setYearValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
		foreach ($result AS $row)
			if ($this->_option == 1)
			{
				$this->_values[0][intval(substr($row['date_add'], 5, 2)) - 1] += 1;
				$this->_values[1][intval(substr($row['date_add'], 5, 2)) - 1] += $row['product_quantity'];
			}
			else
				$this->_values[intval(substr($row['date_add'], 5, 2)) - 1] += $row['total_paid_real'];
	}
	
	protected function setMonthValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
		foreach ($result AS $row)
			if ($this->_option == 1)
			{
				$this->_values[0][intval(substr($row['date_add'], 8, 2)) - 1] += 1;
				$this->_values[1][intval(substr($row['date_add'], 8, 2)) - 1] += $row['product_quantity'];
			}
			else
				$this->_values[intval(substr($row['date_add'], 8, 2)) - 1] += $row['total_paid_real'];
	}

	protected function setDayValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query.pSQL(ModuleGraph::getDateLike()).$this->_query2);
		foreach ($result AS $row)
			if ($this->_option == 1)
			{
				$this->_values[0][intval(substr($row['date_add'], 11, 2))] += 1;
				$this->_values[1][intval(substr($row['date_add'], 11, 2))] += $row['product_quantity'];
			}
			else
				$this->_values[intval(substr($row['date_add'], 11, 2))] += $row['total_paid_real'];
	}
	
	private function getStatesData()
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT osl.`name`, COUNT(oh.`id_order`) as total
		FROM `'._DB_PREFIX_.'order_state` os
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = 2)
		LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON os.`id_order_state` = oh.`id_order_state`
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_order` = oh.`id_order`
		WHERE oh.`id_order_history` = (
			SELECT ios.`id_order_history`
			FROM `'._DB_PREFIX_.'order_history` ios
			WHERE ios.`id_order` = oh.`id_order`
			ORDER BY ios.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1
		)
		AND o.`date_add` LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'
		GROUP BY oh.`id_order_state`');
		foreach ($result as $row)
		{
		    $this->_values[] = $row['total'];
		    $this->_legend[] = $row['name'];
		}
	}
}

?>
