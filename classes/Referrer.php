<?php

/**
  * Referrer class, Referrer.php
  * Referrer management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

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
		'http_referer_regexp' => 'isCleanHtml',	'request_uri_regexp' => 'isCleanHtml', 'http_referer_like' => 'isCleanHtml',	'request_uri_like' => 'isCleanHtml',
		'http_referer_regexp_not' => 'isCleanHtml',	'request_uri_regexp_not' => 'isCleanHtml', 'http_referer_like_not' => 'isCleanHtml',	'request_uri_like_not' => 'isCleanHtml',
		'base_fee' => 'isFloat', 'percent_fee' => 'isFloat', 'click_fee' => 'isFloat',
		'cache_visitors' => 'isUnsignedInt', 'cache_visits' => 'isUnsignedInt', 'cache_pages' => 'isUnsignedInt', 'cache_registrations' => 'isUnsignedInt',
		'cache_orders' => 'isUnsignedInt', 'cache_sales' => 'isOptFloat', 'cache_reg_rate' => 'isOptFloat', 'cache_order_rate' => 'isOptFloat');

	protected 	$table = 'referrer';
	protected 	$identifier = 'id_referrer';
	
	private static $_join = '(r.http_referer_like IS NULL OR r.http_referer_like = \'\' OR cs.http_referer LIKE r.http_referer_like)
			AND (r.request_uri_like IS NULL OR r.request_uri_like = \'\' OR cs.request_uri LIKE r.request_uri_like)
			AND (r.http_referer_like_not IS NULL OR r.http_referer_like_not = \'\' OR cs.http_referer NOT LIKE r.http_referer_like_not)
			AND (r.request_uri_like_not IS NULL OR r.request_uri_like_not = \'\' OR cs.request_uri NOT LIKE r.request_uri_like_not)
			AND (r.http_referer_regexp IS NULL OR r.http_referer_regexp = \'\' OR cs.http_referer REGEXP r.http_referer_regexp)
			AND (r.request_uri_regexp IS NULL OR r.request_uri_regexp = \'\' OR cs.request_uri REGEXP r.request_uri_regexp)
			AND (r.http_referer_regexp_not IS NULL OR r.http_referer_regexp_not = \'\' OR cs.http_referer NOT REGEXP r.http_referer_regexp_not)
			AND (r.request_uri_regexp_not IS NULL OR r.request_uri_regexp_not = \'\' OR cs.request_uri NOT REGEXP r.request_uri_regexp_not)';
	
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
		$fields['click_fee'] = number_format($this->click_fee, 2, '.', '');
		$fields['cache_visitors'] = intval($this->cache_visitors);
		$fields['cache_visits'] = intval($this->cache_visits);
		$fields['cache_pages'] = intval($this->cache_pages);
		$fields['cache_registrations'] = intval($this->cache_registrations);
		$fields['cache_orders'] = intval($this->cache_orders);
		$fields['cache_sales'] = number_format($this->cache_sales, 2, '.', '');
		$fields['cache_reg_rate'] = $this->cache_reg_rate > 1 ? 1 : number_format(floatval($this->cache_reg_rate), 4, '.', '');
		$fields['cache_order_rate'] = $this->cache_order_rate > 1 ? 1 : number_format(floatval($this->cache_order_rate), 4, '.', '');
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		if (!($result = parent::add($autodate, $nullValues)))
			return false;
		$this->refreshCache(array(array('id_referrer' => $this->id)));
		$this->refreshIndex(array(array('id_referrer' => $this->id)));
		return $result;
	}
	
	public static function cacheNewSource($id_connections_source)
	{
		Db::getInstance()->Execute('
		INSERT INTO '._DB_PREFIX_.'referrer_cache (id_referrer, id_connections_source) (
			SELECT id_referrer, id_connections_source
			FROM '._DB_PREFIX_.'referrer r
			LEFT JOIN '._DB_PREFIX_.'connections_source cs ON ('.self::$_join.')
			WHERE id_connections_source = '.intval($id_connections_source).'
		)');
	}
	
	public static function getReferrers($id_customer)
	{
		return Db::getInstance()->ExecuteS('
		SELECT DISTINCT c.date_add, r.name
		FROM '._DB_PREFIX_.'guest g
		LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest
		LEFT JOIN '._DB_PREFIX_.'connections_source cs ON c.id_connections = cs.id_connections
		LEFT JOIN '._DB_PREFIX_.'referrer r ON ('.self::$_join.')
		WHERE g.id_customer = '.intval($id_customer).'
		AND r.name IS NOT NULL');
	}
	
	public function getStatsVisits($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (intval($id_product))
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
		FROM '._DB_PREFIX_.'referrer_cache rc
		LEFT JOIN '._DB_PREFIX_.'connections_source cs ON rc.id_connections_source = cs.id_connections_source
		LEFT JOIN '._DB_PREFIX_.'connections c ON cs.id_connections = c.id_connections
		LEFT JOIN '._DB_PREFIX_.'connections_page cp ON cp.id_connections = c.id_connections
		'.$join.'
		WHERE cs.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
		AND rc.id_referrer = '.intval($this->id).'
		'.$where);
	}
	
	public function getRegistrations($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (intval($id_product))
		{
			$join = 'LEFT JOIN '._DB_PREFIX_.'connections_page cp ON cp.id_connections = c.id_connections
					 LEFT JOIN `'._DB_PREFIX_.'page` p ON cp.`id_page` = p.`id_page`
					 LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`';
			$where = 'AND pt.`name` = \'product.php\'
					  AND p.`id_object` = '.intval($id_product);
		}
		
		$result = Db::getInstance()->getRow('
		SELECT COUNT(DISTINCT cu.id_customer) AS registrations
		FROM '._DB_PREFIX_.'referrer_cache rc
		LEFT JOIN '._DB_PREFIX_.'connections_source cs ON rc.id_connections_source = cs.id_connections_source
		LEFT JOIN '._DB_PREFIX_.'connections c ON cs.id_connections = c.id_connections
		LEFT JOIN '._DB_PREFIX_.'guest g ON g.id_guest = c.id_guest
		LEFT JOIN '._DB_PREFIX_.'customer cu ON cu.id_customer = g.id_customer
		'.$join.'
		WHERE cu.date_add BETWEEN '.ModuleGraph::getDateBetween($employee).'
		AND cu.date_add > cs.date_add
		AND rc.id_referrer = '.intval($this->id).'
		'.$where);
		return $result['registrations'];
	}
	
	public function getStatsSales($id_product = null, $employee = null)
	{
		list($join, $where) = array('','');
		if (intval($id_product))
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
			FROM '._DB_PREFIX_.'referrer_cache rc
			LEFT JOIN '._DB_PREFIX_.'connections_source cs ON rc.id_connections_source = cs.id_connections_source
			LEFT JOIN '._DB_PREFIX_.'connections c ON cs.id_connections = c.id_connections
			LEFT JOIN '._DB_PREFIX_.'guest g ON g.id_guest = c.id_guest
			LEFT JOIN '._DB_PREFIX_.'orders oo ON oo.id_customer = g.id_customer
			'.$join.'
			WHERE oo.invoice_date BETWEEN '.ModuleGraph::getDateBetween($employee).'
			AND oo.date_add > cs.date_add
			AND rc.id_referrer = '.intval($this->id).'
			AND oo.valid = 1
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
			$referrer->cache_registrations = intval($registrations);
			$statsSales = $referrer->getStatsSales(null, $employee);
			$referrer->cache_orders = intval($statsSales['orders']);
			$referrer->cache_sales = number_format($statsSales['sales'], 2, '.', '');
			$referrer->cache_reg_rate = $statsVisits['uniqs'] ? intval($registrations) / $statsVisits['uniqs'] : 0;
			$referrer->cache_order_rate = $statsVisits['uniqs'] ? intval($statsSales['orders']) / $statsVisits['uniqs'] : 0;
			if (!$referrer->update())
				Tools::dieObject(mysql_error());
			Configuration::updateValue('PS_REFERRERS_CACHE_LIKE', ModuleGraph::getDateBetween($employee));
			Configuration::updateValue('PS_REFERRERS_CACHE_DATE', date('Y-m-d H:i:s'));
		}
		return true;
	}
	
	public static function refreshIndex($referrers = null)
	{
		if (!$referrers OR !is_array($referrers))
		{
			Db::getInstance()->Execute('TRUNCATE '._DB_PREFIX_.'referrer_cache');
			Db::getInstance()->Execute('
			INSERT INTO '._DB_PREFIX_.'referrer_cache (id_referrer, id_connections_source) (
				SELECT id_referrer, id_connections_source
				FROM '._DB_PREFIX_.'referrer r
				LEFT JOIN '._DB_PREFIX_.'connections_source cs ON ('.self::$_join.')
			)');
		}
		else
			foreach ($referrers as $row)
			{
				Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'referrer_cache WHERE id_referrer = '.intval($row['id_referrer']));
				Db::getInstance()->Execute('
				INSERT INTO '._DB_PREFIX_.'referrer_cache (id_referrer, id_connections_source) (
					SELECT id_referrer, id_connections_source
					FROM '._DB_PREFIX_.'referrer r
					LEFT JOIN '._DB_PREFIX_.'connections_source cs ON ('.self::$_join.')
					WHERE id_referrer = '.intval($row['id_referrer']).'
				)');
			}
	}
	
	public static function getAjaxProduct($id_referrer, $id_product, $employee = null)
	{
		$product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'));
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$referrer = new Referrer($id_referrer);
		$statsVisits = $referrer->getStatsVisits($id_product, $employee);
		$registrations = $referrer->getRegistrations($id_product, $employee);
		$statsSales = $referrer->getStatsSales($id_product, $employee);

		// If it's a product and it has no visits nor orders
		if (intval($id_product) AND !$statsVisits['visits'] AND !$statsSales['orders'])
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
		$jsonArray[] = 'cart:\''.Tools::displayPrice((intval($statsSales['orders']) ? $statsSales['sales'] / intval($statsSales['orders']) : 0), $currency).'\'';
		$jsonArray[] = 'reg_rate:\''.number_format(intval($statsVisits['uniqs']) ? intval($registrations) / intval($statsVisits['uniqs']) : 0, 4, '.', '').'\'';
		$jsonArray[] = 'order_rate:\''.number_format(intval($statsVisits['uniqs']) ? intval($statsSales['orders']) / intval($statsVisits['uniqs']) : 0, 4, '.', '').'\'';
		$jsonArray[] = 'click_fee:\''.Tools::displayPrice(intval($statsVisits['visits']) * $referrer->click_fee, $currency).'\'';
		$jsonArray[] = 'base_fee:\''.Tools::displayPrice($statsSales['orders'] * $referrer->base_fee, $currency).'\'';
		$jsonArray[] = 'percent_fee:\''.Tools::displayPrice($statsSales['sales'] * $referrer->percent_fee / 100, $currency).'\'';
		die ('[{'.implode(',', $jsonArray).'}]');
	}
}

?>
