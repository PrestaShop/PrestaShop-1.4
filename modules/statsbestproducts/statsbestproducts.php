<?php

/**
  * Statistics
  * @category stats
  *
  * @author John Thiriet / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
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
		$this->page = basename(__FILE__, '.php');
		
		$this->_defaultSortColumn = 'totalPriceSold';
		$this->_emptyMessage = $this->l('Empty recordset returned');
		$this->_pagingMessage = $this->l('Displaying').' {0} - {1} '.$this->l('of').' {2}';
		
		$this->_columns = array(
			array(
				'id' => 'name',
				'header' => $this->l('Name'),
				'dataIndex' => 'name',
				'align' => 'left',
				'width' => 300
			),
			array(
				'id' => 'totalQuantitySold',
				'header' => $this->l('Total Quantity Sold'),
				'dataIndex' => 'totalQuantitySold',
				'width' => 30,
				'align' => 'right'
			),
			array(
				'id' => 'totalPriceSold',
				'header' => $this->l('Total Price Sold'),
				'dataIndex' => 'totalPriceSold',
				'width' => 30,
				'align' => 'right'
			),
			array(
				'id' => 'totalPageViewed',
				'header' => $this->l('Total Viewed'),
				'dataIndex' => 'totalPageViewed',
				'width' => 30,
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
	
	public function getTotalCount()
	{
		$result = Db::getInstance()->GetRow('SELECT COUNT(p.`id_product`) totalCount FROM `'._DB_PREFIX_.'product` p');
		return $result['totalCount'];
	}
	
	public function setOption($option)
	{
	}
	
	public function getData()
	{
		global $cookie;
		$id_lang = (isset($cookie->id_lang) ? intval($cookie->id_lang) : Configuration::get('PS_LANG_DEFAULT'));
	
		$this->_totalCount = $this->getTotalCount();

$this->_query = 'SELECT
pr.`id_product`,
pl.`name`,
IFNULL(t.`totalQuantitySold`, 0) AS totalQuantitySold,
IFNULL(t.`totalPriceSold`, 0) AS totalPriceSold,
(
	SELECT IFNULL(SUM(pv.`counter`), 0)
	FROM `'._DB_PREFIX_.'page` p
	LEFT JOIN `'._DB_PREFIX_.'page_viewed` pv ON p.`id_page` = pv.`id_page`
	LEFT JOIN `'._DB_PREFIX_.'product` pr2 ON CAST(p.`id_object` AS UNSIGNED INTEGER) = pr2.`id_product`
	WHERE p.`id_page_type` = 1 AND pr.`id_product` = pr2.`id_product`		
) AS totalPageViewed
FROM `'._DB_PREFIX_.'product` pr
LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON pr.`id_product` = pl.`id_product`
LEFT OUTER JOIN
(
	SELECT
	pr.`id_product`,
	IFNULL(SUM(cp.`product_quantity`), 0) AS totalQuantitySold,
	IFNULL(SUM(pr.`price` * cp.`product_quantity`), 0) AS totalPriceSold
	FROM `'._DB_PREFIX_.'product` pr
	LEFT OUTER JOIN `'._DB_PREFIX_.'order_detail` cp ON pr.`id_product` = cp.`product_id`
	WHERE
	(
		SELECT
		os.`invoice`
		FROM `'._DB_PREFIX_.'order_history` oh
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
		WHERE cp.`id_order` = oh.`id_order`
		ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
		LIMIT 1
	) = 1
	GROUP BY pr.`id_product`
) t
ON t.`id_product` = pr.`id_product`
WHERE
pl.`id_lang` = '.$id_lang;

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