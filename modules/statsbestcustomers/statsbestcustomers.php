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
  
class StatsBestCustomers extends ModuleGrid
{
	private $_html = null;
	private $_query =  null;
	private $_columns = null;
	private $_defaultSortColumn = null;
	private $_emptyMessage = null;
	private $_pagingMessage = null;
	
	function __construct()
	{
		$this->name = 'statsbestcustomers';
		$this->tab = 'Stats';
		$this->version = 1.0;
		
		$this->_defaultSortColumn = 'total';
		$this->_emptyMessage = $this->l('Empty recordset returned');
		$this->_pagingMessage = $this->l('Displaying').' {0} - {1} '.$this->l('of').' {2}';
		
		$this->_columns = array(
			array(
				'id' => 'lastname',
				'header' => $this->l('Lastname'),
				'dataIndex' => 'lastname',
				'width' => 50
			),
			array(
				'id' => 'firstname',
				'header' => $this->l('Firstname'),
				'dataIndex' => 'firstname',
				'width' => 50
			),
			array(
				'id' => 'email',
				'header' => $this->l('Email'),
				'dataIndex' => 'email',
				'width' => 120
			),
			array(
				'id' => 'totalVisits',
				'header' => $this->l('Visits'),
				'dataIndex' => 'totalVisits',
				'width' => 80,
				'align' => 'right'),
			array(
				'id' => 'totalPageViewed',
				'header' => $this->l('Page viewed'),
				'dataIndex' => 'totalPageViewed',
				'width' => 80,
				'align' => 'right'),
			array(
				'id' => 'totalMoneySpent',
				'header' => $this->l('Money spent'),
				'dataIndex' => 'totalMoneySpent',
				'width' => 80,
				'align' => 'right')
		);
		
		parent::__construct();
		
		$this->displayName = $this->l('Best customers');
		$this->description = $this->l('A list of the best customers');
	}
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	public function hookAdminStatsModules($params)
	{
		$engineParams = array(
			'id' => 'id_customer',
			'title' => $this->displayName,
			'columns' => $this->_columns,
			'defaultSortColumn' => $this->_defaultSortColumn,
			'emptyMessage' => $this->_emptyMessage,
			'pagingMessage' => $this->_pagingMessage
		);
	
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			'.ModuleGrid::engine($engineParams).'
		</fieldset><br />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
			<h2 >'.$this->l('Develop clients\' loyalty').'</h2>
			<p class="space">
				'.$this->l('Keeping a client is more profitable than capturing a new one. Thus, it is necessary to develop its loyalty, in other words to make him come back in your webshop.').' <br />
				'.$this->l('Word of mouth is also a means to get new satisfied clients; a dissatisfied one won\'t attract new clients.').'<br />
				'.$this->l('In order to achieve this goal you can organize: ').'
				<ul>
					<li>'.$this->l('Punctual operations: commercial rewards (personalized special offers, product or service offered), non commercial rewards (priority handling of an order or a product), pecuniary rewards (bonds, discount coupons, payback...).').'</li>
					<li>'.$this->l('Sustainable operations: loyalty or points cards, which not only justify communication between merchant and client, but also offer advantages to clients (private offers, discounts).').'</li>
				</ul>
				'.$this->l('These operations encourage clients to buy and also to come back in your webshop regularly.').' <br />
			</p><br />
		</fieldset>';
		return $this->_html;
	}
	
	public function getTotalCount()
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(DISTINCT c.`id_customer`) totalCount FROM `'._DB_PREFIX_.'customer` c
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON c.id_customer = o.id_customer
		WHERE o.valid = 1');
		return $result['totalCount'];
	}
	
	public function setOption($option)
	{
	}
	
	public function getData()
	{
		$this->_totalCount = $this->getTotalCount();		
		$this->_query = '
		SELECT	c.`id_customer`, c.`lastname`, c.`firstname`, c.`email`,
			COUNT(DISTINCT co.`id_connections`) AS totalVisits,
			COUNT(cop.`id_page`) AS totalPageViewed, (
				SELECT SUM(IFNULL(o.`total_paid_real`, 0) / cu.conversion_rate)
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'currency` cu ON o.id_currency = cu.id_currency
				WHERE o.id_customer = c.id_customer
				AND o.invoice_date BETWEEN '.$this->getDate().'
				AND o.valid
			) AS totalMoneySpent
		FROM `'._DB_PREFIX_.'customer` c
		LEFT JOIN `'._DB_PREFIX_.'guest` g ON c.`id_customer` = g.`id_customer`
		LEFT JOIN `'._DB_PREFIX_.'connections` co ON g.`id_guest` = co.`id_guest`
		LEFT JOIN `'._DB_PREFIX_.'connections_page` cop ON co.`id_connections` = cop.`id_connections`
		WHERE co.date_add BETWEEN '.$this->getDate().'
		GROUP BY c.`id_customer`, c.`lastname`, c.`firstname`, c.`email`';
		if (Validate::IsName($this->_sort))
		{
			if ($this->_sort == 'total')
				$this->_sort = 'totalMoneySpent';
			$this->_query .= ' ORDER BY `'.$this->_sort.'`';
			if (isset($this->_direction) AND Validate::IsSortDirection($this->_direction))
				$this->_query .= ' '.$this->_direction;
		}
		if (($this->_start === 0 OR Validate::IsUnsignedInt($this->_start)) AND Validate::IsUnsignedInt($this->_limit))
			$this->_query .= ' LIMIT '.$this->_start.', '.($this->_limit);
		$this->_values = Db::getInstance()->ExecuteS($this->_query);
	}
}
