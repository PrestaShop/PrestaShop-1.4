<?php

class TrackingFront extends Module
{
	public function __construct()
	{
		$this->name = 'trackingfront';
		$this->tab = 'Stats';
		$this->version = 1.0;

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Tracking - Front office');
		$this->description = $this->l('Enable your affiliates to access their own statistics.');
	}
	
	public function postProcess()
	{
		global $cookie, $smarty;

		if (Tools::isSubmit('ajaxProductFilter'))
		{
			$result = Db::getInstance()->getRow('
			SELECT `id_referrer`
			FROM `'._DB_PREFIX_.'referrer`
			WHERE `id_referrer` = '.intval(Tools::getValue('id_referrer')).' AND `passwd` = \''.pSQL(Tools::getValue('token')).'\'');
			if (isset($result['id_referrer']) ? $result['id_referrer'] : false)
				Referrer::getAjaxProduct(intval(Tools::getValue('id_referrer')), intval(Tools::getValue('id_product')));
		}
		elseif (Tools::isSubmit('logout_tracking'))
		{
			unset($cookie->tracking_id);
			unset($cookie->tracking_passwd);
			Tools::redirect('modules/trackingfront/stats.php');
		}
		elseif (Tools::isSubmit('submitLoginTracking'))
		{
			$errors = array();
			$login = trim(Tools::getValue('login'));
			$passwd = trim(Tools::getValue('passwd'));
			if (empty($login))
				$errors[] = $this->l('login is required');
			elseif (!Validate::isGenericName($login))
				$errors[] = $this->l('invalid login');
			elseif (empty($passwd))
				$errors[] = $this->l('password is required');
			elseif (!Validate::isPasswd($passwd))
				$errors[] = $this->l('invalid password');
			else
			{
				$passwd = Tools::encrypt($passwd);
				$result = Db::getInstance()->getRow('
				SELECT `id_referrer`
				FROM `'._DB_PREFIX_.'referrer`
				WHERE `name` = \''.pSQL($login).'\' AND `passwd` = \''.pSQL($passwd).'\'');
				if (!isset($result['id_referrer']) OR !($tracking_id = intval($result['id_referrer'])))
					$errors[] = $this->l('authentication failed');
				else
				{
					$cookie->tracking_id = $tracking_id;
					$cookie->tracking_passwd = $passwd;
					Tools::redirect('modules/trackingfront/stats.php');
				}
			}
			$smarty->assign('errors', $errors);
		}
		elseif (Tools::isSubmit('submitTrackingRange'))
		{
			if ($day = Tools::getValue('dateInputDay') AND Validate::isUnsignedInt($day))
				$cookie->stats_day = $day;
			else
				unset($cookie->stats_day);
			if ($month = Tools::getValue('dateInputMonth') AND Validate::isUnsignedInt($month))
				$cookie->stats_month = $month + 1;
			else
				unset($cookie->stats_month);
			if ($year = Tools::getValue('dateInputYear') AND Validate::isUnsignedInt($year))
				$cookie->stats_year = $year;
			else
				unset($cookie->stats_year);
			if ($granularity = Tools::getValue('dateInputGranularity'))
				$cookie->stats_granularity = $granularity;
			else
				unset($cookie->stats_granularity);
		}
	}
	
	public function isLogged()
	{
		global $cookie;
		if (!$cookie->tracking_id OR !$cookie->tracking_passwd)
			return false;
		$result = Db::getInstance()->getRow('
		SELECT `id_referrer`
		FROM `'._DB_PREFIX_.'referrer`
		WHERE `id_referrer` = '.intval($cookie->tracking_id).' AND `passwd` = \''.pSQL($cookie->tracking_passwd).'\'');
		return isset($result['id_referrer']) ? $result['id_referrer'] : false;
	}
		
	public function displayLogin()
	{
		return $this->display(__FILE__, 'login.tpl');
	}
	
	public function displayAccount()
	{
		global $smarty, $cookie;
		$smarty->assign('stats_year', isset($cookie->stats_year) ? $cookie->stats_year : date('Y'));
		$smarty->assign('stats_month', isset($cookie->stats_month) ? $cookie->stats_month - 1 : date('m') - 1);
		$smarty->assign('stats_day', isset($cookie->stats_day) ? $cookie->stats_day : date('d'));
		$smarty->assign('stats_granularity', isset($cookie->stats_granularity) ? $cookie->stats_granularity : 'd');
		
		Referrer::refreshCache(array(array('id_referrer' => intval($cookie->tracking_id))));
		$referrer = new Referrer(intval($cookie->tracking_id));
		$smarty->assign('referrer', $referrer);
		$displayTab = array(
			'uniqs' => $this->l('Unique visitors'),
			'visitors' => $this->l('Visitors'),
			'visits' => $this->l('Visits'),
			'pages' => $this->l('Pages viewed'),
			'registrations' => $this->l('Registrations'),
			'orders' => $this->l('Orders'),
			'base_fee' => $this->l('Base fee'),
			'percent_fee' => $this->l('Percent fee'),
			'reg_rate' => $this->l('Registration rate'),
			'sales' => $this->l('Sales'),
			'order_rate' => $this->l('Order rate'));
		$smarty->assign('displayTab', $displayTab);
		
		$products = Product::getSimpleProducts(intval($cookie->id_lang));
		$productsArray = array();
		foreach ($products as $product)
			$productsArray[] = $product['id_product'];
		
		$echo = '
		<script type="text/javascript">
			function updateValues()
			{
				$.getJSON("stats.php",{ajaxProductFilter:1,id_referrer:'.$referrer->id.',token:"'.$cookie->tracking_passwd.'",id_product:0},
					function(j) {';
		foreach ($displayTab as $key => $value)
			$echo .= '$("#'.$key.'").html(j[0].'.$key.');';
		$echo .= '		}
				)
			}		

			var productIds = new Array(\''.implode('\',\'', $productsArray).'\');
			var referrerStatus = new Array();
			
			function newProductLine(id_referrer, result, color)
			{
				return \'\'+
				\'<tr id="trprid_\'+id_referrer+\'_\'+result.id_product+\'" style="background-color: rgb(\'+color+\', \'+color+\', \'+color+\');">\'+
				\'	<td align="center">\'+result.id_product+\'</td>\'+
				\'	<td>\'+result.product_name+\'</td>\'+
				\'	<td align="center">\'+result.uniqs+\'</td>\'+
				\'	<td align="center">\'+result.visits+\'</td>\'+
				\'	<td align="center">\'+result.pages+\'</td>\'+
				\'	<td align="center">\'+result.registrations+\'</td>\'+
				\'	<td align="center">\'+result.orders+\'</td>\'+
				\'	<td align="right">\'+result.sales+\'</td>\'+
				\'	<td align="center">\'+result.reg_rate+\'</td>\'+
				\'	<td align="center">\'+result.order_rate+\'</td>\'+
				\'	<td align="center">\'+result.base_fee+\'</td>\'+
				\'	<td align="center">\'+result.percent_fee+\'</td>\'+
				\'</tr>\';
			}
			
			function showProductLines()
			{
				var irow = 0;
				for (var i = 0; i < productIds.length; ++i)
					$.getJSON("stats.php",{ajaxProductFilter:1,token:"'.$cookie->tracking_passwd.'",id_referrer:'.$referrer->id.',id_product:productIds[i]},
						function(result) {
							var newLine = newProductLine('.$referrer->id.', result[0], (irow++%2 ? 204 : 238));
							$(newLine).hide().insertBefore($(\'#trid_dummy\')).fadeIn();
						}
					);
			}
		</script>';
		
		$echo2 = '
		<script type="text/javascript">
			updateValues();
			showProductLines();
		</script>';
		
		return $echo.$this->display(__FILE__, 'account.tpl').$echo2;
	}	
}

?>