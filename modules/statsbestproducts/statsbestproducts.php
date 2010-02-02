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
  
class StatsBestProducts extends ModuleGrid
{
	private $_html = null;
	private $_query =  null;
	private $_columns = null;
	private $_defaultSortColumn = null;
	private $_emptyMessage = null;
	private $_pagingMessage = null;
	
	function __construct()
	{
		$this->name = 'statsbestproducts';
		$this->tab = 'Stats';
		$this->version = 1.0;
		
		$this->_defaultSortColumn = 'totalPriceSold';
		$this->_emptyMessage = $this->l('Empty recordset returned');
		$this->_pagingMessage = $this->l('Displaying').' {0} - {1} '.$this->l('of').' {2}';
		
		$this->_columns = array(
			array(
				'id' => 'reference',
				'header' => $this->l('Ref.'),
				'dataIndex' => 'reference',
				'align' => 'left',
				'width' => 50
			),
			array(
				'id' => 'name',
				'header' => $this->l('Name'),
				'dataIndex' => 'name',
				'align' => 'left',
				'width' => 100
			),
			array(
				'id' => 'totalQuantitySold',
				'header' => $this->l('Qty sold'),
				'dataIndex' => 'totalQuantitySold',
				'width' => 50,
				'align' => 'right'
			),
			array(
				'id' => 'avgPriceSold',
				'header' => $this->l('Price sold'),
				'dataIndex' => 'avgPriceSold',
				'width' => 50,
				'align' => 'right'
			),
			array(
				'id' => 'totalPriceSold',
				'header' => $this->l('Sales'),
				'dataIndex' => 'totalPriceSold',
				'width' => 50,
				'align' => 'right'
			),
			array(
				'id' => 'averageQuantitySold',
				'header' => $this->l('Qty sold / day'),
				'dataIndex' => 'averageQuantitySold',
				'width' => 60,
				'align' => 'right'
			),
			array(
				'id' => 'totalPageViewed',
				'header' => $this->l('Page viewed'),
				'dataIndex' => 'totalPageViewed',
				'width' => 60,
				'align' => 'right'
			),
			array(
				'id' => 'quantity',
				'header' => $this->l('Stock'),
				'dataIndex' => 'quantity',
				'width' => 50,
				'align' => 'right'
			)
		);
		
		parent::__construct();
		
		$this->displayName = $this->l('Best products');
		$this->description = $this->l('A list of the best products');
	}
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	public function hookAdminStatsModules($params)
	{
		$engineParams = array(
			'id' => 'id_product',
			'title' => $this->displayName,
			'columns' => $this->_columns,
			'defaultSortColumn' => $this->_defaultSortColumn,
			'emptyMessage' => $this->_emptyMessage,
			'pagingMessage' => $this->_pagingMessage
		);
	
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			'.ModuleGrid::engine($engineParams).'
		</fieldset>';
		return $this->_html;
	}
	
	public function getTotalCount($dateBetween)
	{
		$result = Db::getInstance()->GetRow('
		SELECT COUNT(DISTINCT p.`id_product`) totalCount
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN '._DB_PREFIX_.'order_detail od ON od.product_id = p.id_product
		LEFT JOIN '._DB_PREFIX_.'orders o ON od.id_order = o.id_order
		WHERE p.active = 1 AND o.valid = 1
		AND o.invoice_date BETWEEN '.$dateBetween);
		return $result['totalCount'];
	}
		
	public function getData()
	{
		$dateBetween = $this->getDate();
		$arrayDateBetween = explode(' AND ', $dateBetween);
		$this->_totalCount = $this->getTotalCount($dateBetween);

		$this->_query = '
		SELECT p.reference, p.id_product, pl.name, ROUND(AVG(od.product_price / c.conversion_rate), 2) as avgPriceSold, 
			(p.quantity + IFNULL((SELECT SUM(pa.quantity) FROM '._DB_PREFIX_.'product_attribute pa WHERE pa.id_product = p.id_product GROUP BY pa.id_product), 0)) as quantity,
			IFNULL(SUM(od.product_quantity), 0) AS totalQuantitySold,
			ROUND(IFNULL(IFNULL(SUM(od.product_quantity), 0) / (1 + LEAST(TO_DAYS('.$arrayDateBetween[1].'), TO_DAYS(NOW())) - GREATEST(TO_DAYS('.$arrayDateBetween[0].'), TO_DAYS(p.date_add))), 0), 2) as averageQuantitySold,
			ROUND(IFNULL(SUM((od.product_price * od.product_quantity) / c.conversion_rate), 0), 2) AS totalPriceSold,
			(
				SELECT IFNULL(SUM(pv.counter), 0)
				FROM '._DB_PREFIX_.'page pa
				LEFT JOIN '._DB_PREFIX_.'page_viewed pv ON pa.id_page = pv.id_page
				LEFT JOIN '._DB_PREFIX_.'date_range dr ON pv.id_date_range = dr.id_date_range
				WHERE pa.id_object = p.id_product AND pa.id_page_type = 1
				AND dr.time_start BETWEEN '.$dateBetween.'
				AND dr.time_end BETWEEN '.$dateBetween.'
			) AS totalPageViewed
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '.intval($this->getLang()).')
		LEFT JOIN '._DB_PREFIX_.'order_detail od ON od.product_id = p.id_product
		LEFT JOIN '._DB_PREFIX_.'orders o ON od.id_order = o.id_order
		LEFT JOIN '._DB_PREFIX_.'currency c ON o.id_currency = c.id_currency
		WHERE p.active = 1 AND o.valid = 1
		AND o.invoice_date BETWEEN '.$dateBetween.'
		GROUP BY od.product_id';

		if (Validate::IsName($this->_sort))
		{
			$this->_query .= ' ORDER BY `'.$this->_sort.'`';
			if (isset($this->_direction) AND Validate::IsSortDirection($this->_direction))
				$this->_query .= ' '.$this->_direction;
		}
		if (($this->_start === 0 OR Validate::IsUnsignedInt($this->_start)) AND Validate::IsUnsignedInt($this->_limit))
			$this->_query .= ' LIMIT '.$this->_start.', '.($this->_limit);
		$this->_values = Db::getInstance()->ExecuteS($this->_query);
	}
}