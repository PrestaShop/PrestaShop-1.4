<?php

/**
  * Referrer tab for admin panel, AdminReferrers.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

if (!defined('PS_ADMIN_DIR')) define('PS_ADMIN_DIR', getcwd().'/..');
include_once(PS_ADMIN_DIR.'/../config/config.inc.php');
include_once(PS_ADMIN_DIR.'/init.php');

if (Tools::getValue('token') == Tools::getAdminToken('AdminReferrers'.intval(Tab::getIdFromClassName('AdminReferrers')).intval(Tools::getValue('id_employee'))))
{
	if (Tools::isSubmit('ajaxProductFilter'))
		Referrer::getAjaxProduct(intval(Tools::getValue('id_referrer')), intval(Tools::getValue('id_product')), new Employee(intval(Tools::getValue('id_employee'))));
	else if (Tools::isSubmit('ajaxFillProducts'))
	{
		$jsonArray = array();
		$result = Db::getInstance()->ExecuteS('
		SELECT p.id_product, pl.name
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '.intval(Tools::getValue('id_lang')).')
		'.(Tools::getValue('filter') != 'undefined' ? 'WHERE name LIKE "%'.pSQL(Tools::getValue('filter')).'%"' : ''));
		foreach ($result as $row)
			$jsonArray[] = '{id_product:'.intval($row['id_product']).',name:\''.addslashes($row['name']).'\'}';
		die ('['.implode(',', $jsonArray).']');
	}
}

include_once(dirname(__FILE__).'/AdminStats.php');

class AdminReferrers extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'referrer';
	 	$this->className = 'Referrer';
	 	$this->view = true;
	 	$this->edit = true;
		$this->delete = true;

		$this->_select = 'IF(cache_orders > 0, ROUND(cache_sales/cache_orders, 2), 0) as cart, (cache_visits*click_fee) as fee0, (cache_orders*base_fee) as fee1, (cache_sales*percent_fee/100) as fee2';
		$this->fieldsDisplay = array(
			'id_referrer' => array('title' => $this->l('ID'), 'width' => 25, 'align' => 'center'),
			'name' => array('title' => $this->l('Name'), 'width' => 80),
			'cache_visitors' => array('title' => $this->l('Visitors'), 'width' => 30, 'align' => 'center'),
			'cache_visits' => array('title' => $this->l('Visits'), 'width' => 30, 'align' => 'center'),
			'cache_pages' => array('title' => $this->l('Pages'), 'width' => 30, 'align' => 'center'),
			'cache_registrations' => array('title' => $this->l('Reg.'), 'width' => 30, 'align' => 'center'),
			'cache_orders' => array('title' => $this->l('Ord.'), 'width' => 30, 'align' => 'center'),
			'cache_sales' => array('title' => $this->l('Sales'), 'width' => 80, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'price' => true),
			'cart' => array('title' => $this->l('Avg. cart'), 'width' => 50, 'align' => 'right', 'price' => true),
			'cache_reg_rate' => array('title' => $this->l('Reg. rate'), 'width' => 30, 'align' => 'center'),
			'cache_order_rate' => array('title' => $this->l('Order rate'), 'width' => 30, 'align' => 'center'),
			'fee0' => array('title' => $this->l('Click'), 'width' => 30, 'align' => 'right', 'price' => true),
			'fee1' => array('title' => $this->l('Base'), 'width' => 30, 'align' => 'right', 'price' => true),
			'fee2' => array('title' => $this->l('Percent'), 'width' => 30, 'align' => 'right', 'price' => true));
			
		parent::__construct();
	}

	private function enableCalendar()
	{
		return (!Tools::isSubmit('add'.$this->table) AND !Tools::isSubmit('submitAdd'.$this->table) AND !Tools::isSubmit('update'.$this->table));
	}
	
	public function displayJavascript()
	{
		global $cookie, $currentIndex;
		
		$products = Product::getSimpleProducts(intval($cookie->id_lang));
		$productsArray = array();
		foreach ($products as $product)
			$productsArray[] = $product['id_product'];
			
		return '
			<script type="text/javascript">
				var productIds = new Array(\''.implode('\',\'', $productsArray).'\');
				var referrerStatus = new Array();
				
				function newProductLine(id_referrer, result)
				{
					return \'\'+
					\'<tr id="trprid_\'+id_referrer+\'_\'+result.id_product+\'" style="background-color: rgb(255, 255, 187);">\'+
					\'	<td align="center">--</td>\'+
					\'	<td align="center">\'+result.id_product+\'</td>\'+
					\'	<td>\'+result.product_name+\'</td>\'+
					\'	<td align="center">\'+result.uniqs+\'</td>\'+
					\'	<td align="center">\'+result.visits+\'</td>\'+
					\'	<td align="center">\'+result.pages+\'</td>\'+
					\'	<td align="center">\'+result.registrations+\'</td>\'+
					\'	<td align="center">\'+result.orders+\'</td>\'+
					\'	<td align="right">\'+result.sales+\'</td>\'+
					\'	<td align="right">\'+result.cart+\'</td>\'+
					\'	<td align="center">\'+result.reg_rate+\'</td>\'+
					\'	<td align="center">\'+result.order_rate+\'</td>\'+
					\'	<td align="center">\'+result.click_fee+\'</td>\'+
					\'	<td align="center">\'+result.base_fee+\'</td>\'+
					\'	<td align="center">\'+result.percent_fee+\'</td>\'+
					\'	<td align="center">--</td>\'+
					\'</tr>\';
				}
				
				function showProductLines(id_referrer)
				{
					if (!referrerStatus[id_referrer])
					{
						referrerStatus[id_referrer] = true;
						for (var i = 0; i < productIds.length; ++i)
							$.getJSON("'.dirname($currentIndex).'/tabs/AdminReferrers.php",{ajaxProductFilter:1,id_employee:'.intval($cookie->id_employee).',token:"'.Tools::getValue('token').'",id_referrer:id_referrer,id_product:productIds[i]},
								function(result) {
									var newLine = newProductLine(id_referrer, result[0]);
									$(newLine).hide().insertAfter(getE(\'trid_\'+id_referrer)).fadeIn();
								}
							);
					}
					else
					{
						referrerStatus[id_referrer] = false;
						for (var i = 0; i < productIds.length; ++i)
							$("#trprid_"+id_referrer+"_"+productIds[i]).fadeOut("fast",	function(){$("#trprid_"+i).remove();});
					}
				}
			</script>';
	}
	
	public function display()
	{
		global $currentIndex;
		
		if (!Tools::isSubmit('viewreferrer'))
			echo $this->displayJavascript();
		
		if ($this->enableCalendar())
		{
			echo '
			<div style="float: left; margin-right: 20px;">
				'.AdminStatsTab::displayCalendarStatic(array('Calendar' => $this->l('Calendar'), 'Today' => $this->l('Today'), 'Month' => $this->l('Month'), 'Year' => $this->l('Year'))).'
			</div>';
			if (!Tools::isSubmit('viewreferrer'))
				echo '
				<div style="float: left; margin-right: 20px;">
					<fieldset class="width3"><legend><img src="../img/admin/tab-preferences.gif" /> '.$this->l('Settings').'</legend>
						<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post">
							<label>'.$this->l('Save direct traffic').'</label>
							<div class="float" style="margin-left: 200px;">
								<label class="t" for="tracking_dt_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'" /></label>
								<input type="radio" name="tracking_dt" id="tracking_dt_on" value="1" '.(intval(Tools::getValue('tracking_dt', Configuration::get('TRACKING_DIRECT_TRAFFIC'))) ? 'checked="checked"' : '').' />
								<label class="t" for="tracking_dt_on"> '.$this->l('Yes').'</label>
								<label class="t" for="tracking_dt_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" style="margin-left: 10px;" /></label>
								<input type="radio" name="tracking_dt" id="tracking_dt_off" value="0" '.(!intval(Tools::getValue('tracking_dt', Configuration::get('TRACKING_DIRECT_TRAFFIC'))) ? 'checked="checked"' : '').'/>
								<label class="t" for="tracking_dt_off"> '.$this->l('No').'</label>
							</div>
							<br class="clear" />
							<p>'.$this->l('Direct traffic can be quite consuming, you should consider to enable it only if you have a strong database server and the need for it.').'</p>
							<input type="submit" class="button" value="'.$this->l('   Save   ').'" name="submitSettings" />
						</form>
						<hr />
						<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post">
						<p class="bold">'.$this->l('Indexation').'</p>
						<p>'.$this->l('There is a huge quantity of data, so each connection corresponding to a referrer is indexed. You can refresh this index by clicking on the button below. Be aware that it may take a long time and it is only needed if you modified or added a referrer and if you want your changes to be retroactive.').'</p>
						<input type="submit" class="button" value="'.$this->l('Refresh index').'" name="submitRefreshIndex" />
						</form>
						<hr />
						<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post">
						<p class="bold">'.$this->l('Cache').'</p>
						<p>'.$this->l('For you to sort and filter your data, it is cached. You can refresh the cache by clicking on the button below.').'</p>
						<input type="submit" class="button" value="'.$this->l('Refresh cache').'" name="submitRefreshCache" />
						</form>
					</fieldset>
				</div>';
		}
		echo '<div class="clear space">&nbsp;</div>';
		parent::display();
		echo '<div class="clear space">&nbsp;</div>';
	}
	
	public function postProcess()
	{
		global $currentIndex;
		
		if ($this->enableCalendar())
		{
			$calendarTab = new AdminStats();
			$calendarTab->postProcess();
		}

		if (Tools::isSubmit('submitSettings'))
			if ($this->tabAccess['edit'] === '1')
				if (Configuration::updateValue('TRACKING_DIRECT_TRAFFIC', intval(Tools::getValue('tracking_dt'))))
					Tools::redirectAdmin($currentIndex.'&conf=4&token='.Tools::getValue('token'));

		if (ModuleGraph::getDateBetween() != Configuration::get('PS_REFERRERS_CACHE_LIKE') OR Tools::isSubmit('submitRefreshCache'))
			Referrer::refreshCache();
		if (Tools::isSubmit('submitRefreshIndex'))
			Referrer::refreshIndex();
		
		return parent::postProcess();
	}
	
	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);
		foreach (array('http_referer_like', 'http_referer_regexp', 'request_uri_like', 'request_uri_regexp') as $field)
			$obj->{$field} = str_replace('\\', '\\\\', $obj->{$field});
		$uri = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width4"><legend><img src="../img/admin/affiliation.png" /> '.$this->l('Affiliate').'</legend>
				<label>'.$this->l('Name').'</label>
				<div class="margin-form">
					<input type="text" size="20" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Password').'</label>
				<div class="margin-form">
					<input type="password" name="passwd" value="" />
					<p>'.$this->l('Leave blank if no change').'</p>
				</div>
				<p>
					'.$this->l('Affiliates can access to their own data with these name and password.').'<br />
					'.$this->l('Front access:').' <a href="'.$uri.'modules/trackingfront/stats.php" style="font-style: italic;">'.$uri.'modules/trackingfront/stats.php</a>
				</p>
			</fieldset>
			<br class="clear" />
			<fieldset class="width4"><legend><img src="../img/admin/money.png" /> '.$this->l('Commission plan').'</legend>
				<label>'.$this->l('Click fee').'</label>
				<div class="margin-form">
					<input type="text" size="8" name="click_fee" value="'.number_format($this->getFieldValue($obj, 'click_fee'), 2).'" />
					<p>'.$this->l('Fee given for each visit.').'</p>
				</div>
				<label>'.$this->l('Base fee').'</label>
				<div class="margin-form">
					<input type="text" size="8" name="base_fee" value="'.number_format($this->getFieldValue($obj, 'base_fee'), 2).'" />
					<p>'.$this->l('Fee given for each order placed.').'</p>
				</div>
				<label>'.$this->l('Percent fee').'</label>
				<div class="margin-form">
					<input type="text" size="8" name="percent_fee" value="'.number_format($this->getFieldValue($obj, 'percent_fee'), 2).'" />
					<p>'.$this->l('Percent of the sales.').'</p>
				</div>
			</fieldset>
			<br class="clear" />
			<fieldset class="width4"><legend onclick="openCloseLayer(\'tracking_help\')" style="cursor: pointer;"><img src="../img/admin/help.png" /> '.$this->l('Help').'</legend>
			<div id="tracking_help" style="display: none;">
				<p>'.$this->l('Definitions:').'</p>
				<ul style="list-style: disc; margin-left: 20px;">
					<li>
						'.$this->l('The field `http_referer` is the website from which your customers come.').'<br />
						'.$this->l('For example, visitors coming from Google will have a `http_referer` like this one: "http://www.google.com/search?q=prestashop".').'<br />
						'.$this->l('If the visitor come directly (by typing the URL of your shop or by using its bookmarks for example), `http_referer` will be empty.').'<br />
						'.$this->l('So if you want all the visitors coming from google, you can type "%google%" in this field, or "%google.fr%" if you want the visitors coming from Google France only.').'<br />
					</li>
					<br />
					<li>
						'.$this->l('The field `request_uri` is the URL by which the customer come to your website.').'<br />
						'.$this->l('For example, if the visitor access to a product page, this URL will be').' "'.$uri.'music-ipods/1-ipod-nano.html".<br />
						'.$this->l('This is interesting because you can add some tags or token in the links pointing to your website. For exemple, you can post a link').' "'.$uri.'index.php?prestashop" '.$this->l('in the forum and get statistics by entering "%prestashop" in the field `request_uri`. You will get all the visitors coming from the forum.').'
						'.$this->l('This method is more reliable than the `http_referer` one, but there is a danger: if a search engine read a page with your link, then it will be displayed in its results and you will have not only the forum visitors, but also the ones from the search engine.').'
					</li>
					<br />
					<li>
						'.$this->l('The fields `include` indicate what has to be included in the URL.').'
					</li>
					<br />
					<li>
						'.$this->l('The fields `exclude` indicate what has to be excluded from the URL.').'
					</li>
					<br />
					<li>
						'.$this->l('When using the simple mode, you can use some generic characters which can replace any characters:').'
						<ul>
							<li>'.$this->l('"_" will replace one character. If you want to use the real "_", you should type "\\\\_".').'</li>
							<li>'.$this->l('"%" will replace any number of characters. If you want to use the real "%", you should type "\\\\%".').'</li>
						</ul>
					</li>
					<br />
					<li>
						'.$this->l('The simple mode uses the MySQL "LIKE", but for a higher potency you can use MySQL regular expressions.').'
						<a href="http://dev.mysql.com/doc/refman/5.0/en/regexp.html" target="_blank" style="font-style: italic;">'.$this->l('Take a look to the documentation for more details...').'</a>
					</li>
				</ul>
			</div>
			</fieldset>
			<br class="clear" />
			<fieldset class="width4"><legend><img src="../img/admin/affiliation.png" /> '.$this->l('Technical information - Simple mode').'</legend>
				<a style="cursor: pointer; font-style: italic;" onclick="openCloseLayer(\'tracking_help\');"><img src="../img/admin/help.png" /> '.$this->l('Get help!').'</a><br />
				<br class="clear" />
				<h3>'.$this->l('HTTP referrer').'</h3>
				<label>'.$this->l('Include').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="http_referer_like">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'http_referer_like'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<label>'.$this->l('Exclude').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="http_referer_like_not">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'http_referer_like_not'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<h3>'.$this->l('Request Uri').'</h3>
				<label>'.$this->l('Include').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="request_uri_like">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'request_uri_like'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<label>'.$this->l('Exclude').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="request_uri_like_not">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'request_uri_like_not'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<br class="clear" />
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<br class="clear" />
				'.$this->l('If you know how to use MySQL regular expressions, you can use the').' <a style="cursor: pointer; font-weight: bold;" onclick="openCloseLayer(\'tracking_expert\');">'.$this->l('expert mode').'.</a>
			</fieldset>
			<br class="clear" />
			<fieldset class="width4"><legend onclick="openCloseLayer(\'tracking_expert\')" style="cursor: pointer;"><img src="../img/admin/affiliation.png" /> '.$this->l('Technical information - Expert mode').'</legend>
			<div id="tracking_expert" style="display: none;">
				<h3>'.$this->l('HTTP referrer').'</h3>
				<label>'.$this->l('Include').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="http_referer_regexp">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'http_referer_regexp'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<label>'.$this->l('Exclude').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="http_referer_regexp_not">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'http_referer_regexp_not'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<h3>'.$this->l('Request Uri').'</h3>
				<label>'.$this->l('Include').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="request_uri_regexp">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'request_uri_regexp'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<label>'.$this->l('Exclude').'</label>
				<div class="margin-form">
					<textarea cols="40" rows="1" name="request_uri_regexp_not">'.str_replace('\\', '\\\\', htmlentities($this->getFieldValue($obj, 'request_uri_regexp_not'), ENT_COMPAT, 'UTF-8')).'</textarea>
				</div>
				<br class="clear" />
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
			</div>
			</fieldset>
		</form>';
	}
	
	public function viewreferrer()
	{
		global $cookie, $currentIndex;
		$referrer = new Referrer(intval(Tools::getValue('id_referrer')));

		$displayTab = array(
			'uniqs' => $this->l('Unique visitors'),
			'visitors' => $this->l('Visitors'),
			'visits' => $this->l('Visits'),
			'pages' => $this->l('Pages viewed'),
			'registrations' => $this->l('Registrations'),
			'orders' => $this->l('Orders'),
			'sales' => $this->l('Sales'),
			'reg_rate' => $this->l('Registration rate'),
			'order_rate' => $this->l('Order rate'),
			'click_fee' => $this->l('Click fee'),
			'base_fee' => $this->l('Base fee'),
			'percent_fee' => $this->l('Percent fee'));
		echo '
		<script type="text/javascript">
			function updateConversionRate(id_product)
			{
				$.getJSON("'.dirname($currentIndex).'/tabs/AdminReferrers.php",{ajaxProductFilter:1,id_employee:'.intval($cookie->id_employee).',token:"'.Tools::getValue('token').'",id_referrer:'.$referrer->id.',id_product:id_product},
					function(j) {';
		foreach ($displayTab as $key => $value)
			echo '$("#'.$key.'").html(j[0].'.$key.');';
		echo '		}
				)
			}
			
			function fillProducts(filter)
			{
				var form = document.layers ? document.forms.product : document.product;
				var filter = form.filterProduct.value;
				$.getJSON("'.dirname($currentIndex).'/tabs/AdminReferrers.php",
					{ajaxFillProducts:1,id_employee:'.intval($cookie->id_employee).',token:"'.Tools::getValue('token').'",id_lang:'.intval($cookie->id_lang).',filter:filter},
					function(j) {
						
						form.selectProduct.length = j.length + 1;
						for (var i = 0; i < j.length; i++)
						{
							form.selectProduct.options[i+1].value = j[i].id_product;
							form.selectProduct.options[i+1].text = j[i].name;
						}
					}
				);
			}
		</script>
		<fieldset class="width3" style="float: left"><legend><img src="../img/admin/tab-stats.gif" /> Statistics</legend>
			<h2>'.$referrer->name.'</h2>
			<table>';
		foreach ($displayTab as $data => $label)
			echo '<tr><td>'.$label.'</td><td style="color:green;font-weight:bold;padding-left:20px;" id="'.$data.'"></td></tr>';
		echo '</table>
		<br class="clear" />
		<form id="product" name="product">
			'.$this->l('Filter by product:').'
			<select id="selectProduct" name="selectProduct" style="width: 200px;" onfocus="fillProducts();" onchange="updateConversionRate(this.value);">
				<option value="0" selected="selected">-- '.$this->l('All').' --</option>
			</select> <input type="text" size="25" id="filterProduct" name="filterProduct" onkeyup="fillProducts();" class="space" />
		</form>
		</fieldset>
		<script type="text/javascript">
			updateConversionRate(0);
		</script>';
	}
	
	public function displayListContent($token = NULL)
	{
		global $currentIndex;

		$irow = 0;
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		if ($this->_list)
			foreach ($this->_list AS $tr)
			{
				$id = $tr[$this->identifier];
				echo '<tr id="trid_'.$id.'"'.($irow++ % 2 ? ' class="alt_row"' : '').'>
				<td class="center"><input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" /></td>';
				foreach ($this->fieldsDisplay AS $key => $params)
				{
					echo '<td onclick="showProductLines('.$id.');" class="pointer '.(isset($params['align']) ? $params['align'] : '').'">'.(isset($params['prefix']) ? $params['prefix'] : '');
					if (isset($tr[$key]) AND isset($params['price']))
						echo Tools::displayPrice($tr[$key], $currency);
					elseif (isset($tr[$key]))
						echo $tr[$key];
					else
						echo '--';
					echo (isset($params['suffix']) ? $params['suffix'] : '').'</td>';
				}
				echo '
				<td class="center" style="width: 60px">
					<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&view'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'">
					<img src="../img/admin/details.gif" border="0" alt="'.$this->l('View').'" title="'.$this->l('View').'" /></a>
					<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&update'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'">
					<img src="../img/admin/edit.gif" border="0" alt="'.$this->l('Edit').'" title="'.$this->l('Edit').'" /></a>
					<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'" onclick="return confirm(\''.addslashes($this->l('Delete item ?')).'\');">
					<img src="../img/admin/delete.gif" border="0" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a>
				</tr>';
			}
	}
}

?>
