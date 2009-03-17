<?php
  
class Referrer extends ObjectModel
{
	public $name;
	public $passwd;
	
	public $http_referer_regexp;
	public $http_referer_like;
	public $request_uri_regexp;
	public $request_uri_like;
	public $http_referer_regexp_not;
	public $http_referer_like_not;
	public $request_uri_regexp_not;
	public $request_uri_like_not;
	
	public $base_fee;
	public $percent_fee;
	public $click_fee;
	
	public $cache_visitors;
	public $cache_visits;
	public $cache_pages;
	public $cache_registrations;
	public $cache_orders;
	public $cache_sales;
	public $cache_reg_rate;
	public $cache_order_rate;
	
	public $date_add;
		
	protected	$fieldsRequired = array('name');	
	protected	$fieldsSize = array('name' => 64, 'http_referer_regexp' => 64, 'request_uri_regexp' => 64, 'http_referer_like' => 64, 'request_uri_like' => 64, 'passwd' => 32);	
	protected	$fieldsValidate = array(
		'name' => 'isGenericName', 'passwd' => 'isPasswd',
		'http_referer_regexp' => 'isCleanHtml',	'request_uri_regexp' => 'isGenericName', 'http_referer_like' => 'isCleanHtml',	'request_uri_like' => 'isGenericName',
		'http_referer_regexp_not' => 'isCleanHtml',	'request_uri_regexp_not' => 'isGenericName', 'http_referer_like_not' => 'isCleanHtml',	'request_uri_like_not' => 'isGenericName',
		'base_fee' => 'isFloat', 'percent_fee' => 'isFloat', 'click_fee' => 'isFloat',
		'cache_visitors' => 'isUnsignedInt', 'cache_visits' => 'isUnsignedInt', 'cache_pages' => 'isUnsignedInt', 'cache_registrations' => 'isUnsignedInt',
		'cache_orders' => 'isUnsignedInt', 'cache_sales' => 'isOptFloat', 'cache_reg_rate' => 'isOptFloat', 'cache_order_rate' => 'isOptFloat');

	protected 	$table = 'referrer';
	protected 	$identifier = 'id_referrer';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['name'] = pSQL($this->name);
		$fields['passwd'] = pSQL($this->passwd);
		$fields['http_referer_regexp'] = pSQL($this->http_referer_regexp, true);
		$fields['request_uri_regexp'] = pSQL($this->request_uri_regexp, true);
		$fields['http_referer_like'] = pSQL($this->http_referer_like, true);
		$fields['request_uri_like'] = pSQL($this->request_uri_like, true);
		$fields['http_referer_regexp_not'] = pSQL($this->http_referer_regexp_not, true);
		$fields['request_uri_regexp_not'] = pSQL($this->request_uri_regexp_not, true);
		$fields['http_referer_like_not'] = pSQL($this->http_referer_like_not, true);
		$fields['request_uri_like_not'] = pSQL($this->request_uri_like_not, true);
		$fields['base_fee'] = number_format($this->base_fee, 2, '.', '');
		$fields['percent_fee'] = number_format($this->percent_fee, 2, '.', '');
		$fields['click_fee'] = number_format($this->percent_fee, 2, '.', '');
		$fields['cache_visitors'] = intval($this->cache_visitors);
		$fields['cache_visits'] = intval($this->cache_visits);
		$fields['cache_pages'] = intval($this->cache_pages);
		$fields['cache_registrations'] = intval($this->cache_registrations);
		$fields['cache_orders'] = intval($this->cache_orders);
		$fields['cache_sales'] = number_format($this->cache_sales, 2, '.', '');
		$fields['cache_reg_rate'] = $this->cache_reg_rate > 100 ? 100 : number_format(floatval($this->cache_reg_rate) * 100, 2, '.', '');
		$fields['cache_order_rate'] = $this->cache_order_rate > 100 ? 100 : number_format(floatval($this->cache_order_rate) * 100, 2, '.', '');
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		if (!parent::add($autodate, $nullValues))
			return false;
		return $this->refreshCache(array(array('id_referrer' => $this->id)));
	}
	
	private function getRegexp()
	{
		$regexp = '';
		if (!empty($this->http_referer_like))
			$regexp .= ' AND cs.http_referer LIKE \''.pSQL($this->http_referer_like).'\' ';
		if (!empty($this->request_uri_like))
			$regexp .= ' AND cs.request_uri LIKE \''.pSQL($this->request_uri_like).'\' ';
		if (!empty($this->http_referer_like_not))
			$regexp .= ' AND cs.http_referer NOT LIKE \''.pSQL($this->http_referer_like_not).'\' ';
		if (!empty($this->request_uri_like_not))
			$regexp .= ' AND cs.request_uri NOT LIKE \''.pSQL($this->request_uri_like_not).'\' ';
			
		if (!empty($this->request_uri_regexp))
			$regexp .= ' AND cs.request_uri REGEXP \''.pSQL($this->request_uri_regexp).'\' ';
		if (!empty($this->http_referer_regexp))
			$regexp .= ' AND cs.http_referer REGEXP \''.pSQL($this->http_referer_regexp).'\' ';
		if (!empty($this->request_uri_regexp_not))
			$regexp .= ' AND cs.request_uri NOT REGEXP \''.pSQL($this->request_uri_regexp_not).'\' ';
		if (!empty($this->http_referer_regexp_not))
			$regexp .= ' AND cs.http_referer NOT REGEXP \''.pSQL($this->http_referer_regexp_not).'\' ';
		return $regexp;
	}
	
	public static function getReferrers($id_customer)
	{
		return Db::getInstance()->ExecuteS('
		SELECT DISTINCT c.date_add, r.name
		FROM '._DB_PREFIX_.'guest g
		LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest
		LEFT JOIN '._DB_PREFIX_.'connections_source cs ON c.id_connections = cs.id_connections
		LEFT JOIN '._DB_PREFIX_.'referrer r ON (
			(r.http_referer_like IS NULL OR r.http_referer_like = \'\' OR cs.http_referer LIKE r.http_referer_like)
			AND (r.request_uri_like IS NULL OR r.request_uri_like = \'\' OR cs.request_uri LIKE r.request_uri_like)
			AND (r.http_referer_like_not IS NULL OR r.http_referer_like_not = \'\' OR cs.http_referer NOT LIKE r.http_referer_like_not)
			AND (r.request_uri_like_not IS NULL OR r.request_uri_like_not = \'\' OR cs.request_uri NOT LIKE r.request_uri_like_not)
			AND (r.http_referer_regexp IS NULL OR r.http_referer_regexp = \'\' OR cs.http_referer REGEXP r.http_referer_regexp)
			AND (r.request_uri_regexp IS NULL OR r.request_uri_regexp = \'\' OR cs.request_uri REGEXP r.request_uri_regexp)
			AND (r.http_referer_regexp_not IS NULL OR r.http_referer_regexp_not = \'\' OR cs.http_referer NOT REGEXP r.http_referer_regexp_not)
			AND (r.request_uri_regexp_not IS NULL OR r.request_uri_regexp_not = \'\' OR cs.request_uri NOT REGEXP r.request_uri_regexp_not)
		)
		WHERE g.id_customer = '.intval($id_customer).'
		AND r.name IS NOT NULL');
	}
	
	public function getStatsVisits($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (Validate::isUnsignedId($id_product) AND $id_product)
		{
			$join = 'LEFT JOIN `'._DB_PREFIX_.'page` p ON cp.`id_page` = p.`id_page`
					 LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`';
			$where = 'AND pt.`name` = \'product.php\'
					  AND p.`id_object` = '.intval($id_product);
		}

		return Db::getInstance()->getRow('
		SELECT 	COUNT(DISTINCT cs.id_connections_source) AS visits,
				COUNT(DISTINCT cs.id_connections) as visitors,
				COUNT(DISTINCT c.id_guest) as uniqs,
				COUNT(DISTINCT cp.time_start) as pages
		FROM '._DB_PREFIX_.'connections_source cs
		LEFT JOIN '._DB_PREFIX_.'connections c ON cs.id_connections = c.id_connections
		LEFT JOIN '._DB_PREFIX_.'connections_page cp ON cp.id_connections = c.id_connections
		'.$join.'
		WHERE cs.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
		'.$this->getRegexp().'
		'.$where);
	}
	
	public function getRegistrations($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (Validate::isUnsignedId($id_product))
		{
			$join = 'LEFT JOIN '._DB_PREFIX_.'connections_page cp ON cp.id_connections = c.id_connections
					 LEFT JOIN `'._DB_PREFIX_.'page` p ON cp.`id_page` = p.`id_page`
					 LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`';
			$where = 'AND pt.`name` = \'product.php\'
					  AND p.`id_object` = '.intval($id_product);
		}
		
		$result = Db::getInstance()->getRow('
		SELECT COUNT(DISTINCT cu.id_customer) AS registrations
		FROM '._DB_PREFIX_.'connections_source cs
		LEFT JOIN '._DB_PREFIX_.'connections c ON cs.id_connections = c.id_connections
		LEFT JOIN '._DB_PREFIX_.'guest g ON g.id_guest = c.id_guest
		LEFT JOIN '._DB_PREFIX_.'customer cu ON cu.id_customer = g.id_customer
		'.$join.'
		WHERE cu.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
		'.$this->getRegexp().'
		'.$where);
		return $result['registrations'];
	}
	
	public function getStatsSales($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (Validate::isUnsignedId($id_product))
		{
			$join =	'LEFT JOIN '._DB_PREFIX_.'order_detail od ON oo.id_order = od.id_order';
			$where = 'AND od.product_id = '.intval($id_product);
		}
		
		return Db::getInstance()->getRow('
		SELECT 	COUNT(o.id_order) AS orders,
				SUM(o.total_paid_real) / c.conversion_rate AS sales
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN `'._DB_PREFIX_.'currency` c ON o.id_currency = c.id_currency
		WHERE o.id_order IN (
			SELECT DISTINCT oo.id_order
			FROM '._DB_PREFIX_.'connections_source cs
			LEFT JOIN '._DB_PREFIX_.'connections c ON cs.id_connections = c.id_connections
			LEFT JOIN '._DB_PREFIX_.'guest g ON g.id_guest = c.id_guest
			LEFT JOIN '._DB_PREFIX_.'orders oo ON oo.id_customer = g.id_customer
			'.$join.'
			WHERE oo.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
			'.$this->getRegexp().'
			'.$where.'
		)
		AND o.valid = 1');
	}
	
	public function getStatsRegRate($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (Validate::isUnsignedId($id_product))
		{
			$join =	'LEFT JOIN '._DB_PREFIX_.'order_detail od ON oo.id_order = od.id_order';
			$where = 'AND od.product_id = '.intval($id_product);
		}
		
		return Db::getInstance()->getRow('
		SELECT 	COUNT(DISTINCT cu.id_customer) as registrations
		FROM '._DB_PREFIX_.'customer cu
		WHERE cu.id_customer IN (
			SELECT g.id_customer
			FROM '._DB_PREFIX_.'guest g
			LEFT JOIN '._DB_PREFIX_.'connections c ON g.id_guest = c.id_guest
			LEFT JOIN '._DB_PREFIX_.'connections_source cs ON cs.id_connections = c.id_connections
			'.$join.'
			WHERE cs.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
			'.$this->getRegexp().'
			'.$where.'
		)
		AND cu.date_add BETWEEN '.ModuleGraph::getDateBetween($employee));
	}
	
	public function getStatsOrderRate($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (Validate::isUnsignedId($id_product))
		{
			$join =	'LEFT JOIN '._DB_PREFIX_.'order_detail od ON oo.id_order = od.id_order';
			$where = 'AND od.product_id = '.intval($id_product);
		}
		
		return Db::getInstance()->getRow('
		SELECT 	COUNT(DISTINCT o.id_customer) as visitors,
				COUNT(o.id_customer) as uniqs
		FROM '._DB_PREFIX_.'orders o
		WHERE o.id_customer IN (
			SELECT oo.id_customer
			FROM '._DB_PREFIX_.'orders oo
			LEFT JOIN '._DB_PREFIX_.'guest g ON oo.id_customer = g.id_customer
			LEFT JOIN '._DB_PREFIX_.'connections c ON g.id_guest = c.id_guest
			LEFT JOIN '._DB_PREFIX_.'connections_source cs ON cs.id_connections = c.id_connections
			'.$join.'
			WHERE cs.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
			'.$this->getRegexp().'
			'.$where.'
		)
		AND o.valid = 1');
	}
	
	public static function refreshCache($referrers = null, $employee = null)
	{
		if (!$referrers OR !is_array($referrers))
			$referrers = Db::getInstance()->ExecuteS('SELECT id_referrer FROM '._DB_PREFIX_.'referrer');
		foreach ($referrers as $row)
		{
			$referrer = new Referrer(intval($row['id_referrer']));
			$statsVisits = $referrer->getStatsVisits(null, $employee);
			$referrer->cache_visitors = $statsVisits['uniqs'];
			$referrer->cache_visits = $statsVisits['visits'];
			$referrer->cache_pages = $statsVisits['pages'];
			$registrations = $referrer->getRegistrations(null, $employee);
			$referrer->cache_registrations = $registrations;
			$statsSales = $referrer->getStatsSales(null, $employee);
			$referrer->cache_orders = intval($statsSales['orders']);
			$referrer->cache_sales = number_format($statsSales['sales'], 2, '.', '');
			$statsTransfo = $referrer->getStatsRegRate(null, $employee);
			$referrer->cache_reg_rate = $statsVisits['uniqs'] ? $statsTransfo['registrations'] / $statsVisits['uniqs'] : 0;
			$statsTransfo2 = $referrer->getStatsOrderRate(null, $employee);
			$referrer->cache_order_rate = $statsVisits['uniqs'] ? $statsTransfo2['uniqs'] / $statsVisits['uniqs'] : 0;
			if (!$referrer->update())
				Tools::dieObject(mysql_error());
			Configuration::updateValue('PS_REFERRERS_CACHE_LIKE', ModuleGraph::getDateBetween($employee));
			Configuration::updateValue('PS_REFERRERS_CACHE_DATE', date('Y-m-d h:i:s'));
		}
		return true;
	}
	
	public static function getAjaxProduct($id_referrer, $id_product, $employee = null)
	{
		$product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'));
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$referrer = new Referrer($id_referrer);
		$statsVisits = $referrer->getStatsVisits($id_product, $employee);
		$registrations = $referrer->getRegistrations($id_product, $employee);
		$statsSales = $referrer->getStatsSales($id_product, $employee);
		$statsTransfo = $referrer->getStatsRegRate($id_product, $employee);
		$statsTransfo2 = $referrer->getStatsOrderRate($id_product, $employee);

		// If it's not a product alone and it has no visits nor orders
		if (!$id_product AND !$statsVisits['visits'] AND !$statsSales['orders'])
			exit;
		
		$jsonArray = array();
		$jsonArray[] = 'id_product:\''.intval($product->id).'\'';
		$jsonArray[] = 'product_name:\''.addslashes($product->name).'\'';
		$jsonArray[] = 'uniqs:\''.intval($statsVisits['uniqs']).'\'';
		$jsonArray[] = 'visitors:\''.intval($statsVisits['visitors']).'\'';
		$jsonArray[] = 'visits:\''.intval($statsVisits['visits']).'\'';
		$jsonArray[] = 'pages:\''.intval($statsVisits['pages']).'\'';
		$jsonArray[] = 'registrations:\''.intval($registrations).'\'';
		$jsonArray[] = 'orders:\''.intval($statsSales['orders']).'\'';
		$jsonArray[] = 'sales:\''.Tools::displayPrice($statsSales['sales'], $currency).'\'';
		$jsonArray[] = 'reg_rate:\''.number_format(intval($statsVisits['uniqs']) ? intval($statsTransfo['registrations']) / intval($statsVisits['uniqs']) : 0, 4, '.', '').'\'';
		$jsonArray[] = 'order_rate:\''.number_format(intval($statsVisits['uniqs']) ? intval($statsTransfo2['uniqs']) / intval($statsVisits['uniqs']) : 0, 4, '.', '').'\'';
		$jsonArray[] = 'click_fee:\''.Tools::displayPrice(intval($statsVisits['visits']) * $referrer->click_fee, $currency).'\'';
		$jsonArray[] = 'base_fee:\''.Tools::displayPrice($statsSales['orders'] * $referrer->base_fee, $currency).'\'';
		$jsonArray[] = 'percent_fee:\''.Tools::displayPrice($statsSales['sales'] * $referrer->percent_fee / 100, $currency).'\'';
		die ('[{'.implode(',', $jsonArray).'}]');
	}
}

?>
