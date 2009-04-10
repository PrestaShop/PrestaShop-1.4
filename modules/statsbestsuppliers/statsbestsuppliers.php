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
  
class StatsBestSuppliers extends ModuleGrid
{
	private $_html = null;
	private $_query =  null;
	private $_columns = null;
	private $_defaultSortColumn = null;
	private $_emptyMessage = null;
	private $_pagingMessage = null;
	
	function __construct()
	{
		$this->name = 'statsbestsuppliers';
		$this->tab = 'Stats';
		$this->version = 1.0;
		
		$this->_defaultSortColumn = 'sales';
		$this->_emptyMessage = $this->l('Empty recordset returned');
		$this->_pagingMessage = $this->l('Displaying').' {0} - {1} '.$this->l('of').' {2}';
		
		$this->_columns = array(
			array(
				'id' => 'name',
				'header' => $this->l('Name'),
				'dataIndex' => 'name',
				'align' => 'left',
				'width' => 200
			),
			array(
				'id' => 'quantity',
				'header' => $this->l('Quantity sold'),
				'dataIndex' => 'quantity',
				'width' => 60,
				'align' => 'right'
			),
			array(
				'id' => 'sales',
				'header' => $this->l('Total paid'),
				'dataIndex' => 'sales',
				'width' => 60,
				'align' => 'right'
			)
		);
		
		parent::__construct();
		
		$this->displayName = $this->l('Best suppliers');
		$this->description = $this->l('A list of the best suppliers');
	}
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	public function hookAdminStatsModules($params)
	{
		$engineParams = array(
			'id' => 'id_category',
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
	
	public function getTotalCount()
	{
		return Db::getInstance()->getValue('
		SELECT COUNT(DISTINCT(s.id_supplier)
		FROM '._DB_PREFIX_.'order_detail od
		LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product = od.product_id
		LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
		LEFT JOIN '._DB_PREFIX_.'supplier s ON s.id_supplier = p.id_supplier
		WHERE o.invoice_date BETWEEN '.$this->getDate().' AND o.valid = 1
		AND s.id_supplier IS NOT NULL');
	}
	
	public function getData()
	{	
		$this->_totalCount = $this->getTotalCount();

		$this->_query = '
		SELECT s.name, SUM(od.product_quantity) as quantity, ROUND(SUM(od.product_quantity * od.product_price) / c.conversion_rate, 2) as sales
		FROM '._DB_PREFIX_.'order_detail od
		LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product = od.product_id
		LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
		LEFT JOIN '._DB_PREFIX_.'currency c ON c.id_currency = o.id_currency
		LEFT JOIN '._DB_PREFIX_.'supplier s ON s.id_supplier = p.id_supplier
		WHERE o.invoice_date BETWEEN '.$this->getDate().' AND o.valid = 1
		AND s.id_supplier IS NOT NULL
		GROUP BY p.id_supplier';
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
