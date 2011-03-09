<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminHome extends AdminTab
{
	public function postProcess()
	{
	}
	
	public function display()
	{
		global $cookie;
	$tab = get_class();
	$protocol = (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';
	$isoDefault = Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT')));
	$isoUser = Language::getIsoById(intval($cookie->id_lang));
	$currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
	echo '<div>
	<h1>'.$this->l('Dashboard').'</h1>
	<hr style="background-color: #812143;color: #812143;" />
	<br />';
	if (@ini_get('allow_url_fopen') AND $update = checkPSVersion())
		echo '<div class="warning warn" style="margin-bottom:30px;"><h3>'.$this->l('New PrestaShop version available').' : <a style="text-decoration: underline;" href="'.$update['link'].'">'.$this->l('Download').'&nbsp;'.$update['name'].'</a> !</h3></div>';
    elseif (!@ini_get('allow_url_fopen'))
    {
		echo '<p>'.$this->l('Update notification unavailable').'</p>';
		echo '<p>&nbsp;</p>';
		echo '<p>'.$this->l('To receive PrestaShop update warnings, you need to activate the <b>allow_url_fopen</b> command in your <b>php.ini</b> config file.').' [<a href="http://www.php.net/manual/'.$isoUser.'/ref.filesystem.php">'.$this->l('more info').'</a>]</p>';
		echo '<p>'.$this->l('If you don\'t know how to do that, please contact your host administrator !').'</p><br>';
	}
  echo '</div>';

  	if (!isset($cookie->show_screencast))
  		$cookie->show_screencast = true;
  	if ($cookie->show_screencast)
		echo'
		<div id="adminpresentation">
			<iframe src="http://screencasts.prestashop.com/screencast.php?iso_lang='.Tools::strtolower($isoUser).'" style="border:none;width:100%;height:420px;" scrolling="no"></iframe>
			<div id="footer_iframe_home">
				<!--<a href="#">'.$this->l('View more video tutorials').'</a>-->
				<input type="checkbox" id="screencast_dont_show_again"><label for="screencast_dont_show_again">'.$this->l('don\'t show again').'</label>
			</div>
		</div>
		<script type="text/javascript">
		$(document).ready(function() {
			$(\'#screencast_dont_show_again\').click(function() {
				if ($(this).is(\':checked\'))
				{
					$.ajax({
						type: \'POST\',
						async: true,
						url: \'ajax.php?toggleScreencast\',
						success: function(data) {
							$(\'#adminpresentation\').slideUp(\'slow\');
						}
					});
				}
			});
		});
		</script>
		<div class="clear"></div><br />';

	if (Tools::isSubmit('hideOptimizationTips'))
		Configuration::updateValue('PS_HIDE_OPTIMIZATION_TIPS', 1);
	elseif (!Configuration::get('PS_HIDE_OPTIMIZATION_TIPS'))
		displayOptimizationTips();

	echo '
	<div id="column_left">
		<ul class="F_list clearfix">
			<li id="first_block">
				<h4><a href="index.php?tab=AdminCatalog&addcategory&token='.Tools::getAdminTokenLite('AdminCatalog').'">'.$this->l('New category').'</a></h4>
				<p>'.$this->l('Create a new category and organize your products.').'</p>
			</li>
			<li id="second_block">
				<h4><a href="index.php?tab=AdminCatalog&id_category=1&addproduct&token='.Tools::getAdminTokenLite('AdminCatalog').'">'.$this->l('New product').'</a></h4>
				<p>'.$this->l('Fill up your catalog with new articles and attributes.').'</p>
			</li>
			<li id="third_block">
				<h4><a href="index.php?tab=AdminStats&token='.Tools::getAdminTokenLite('AdminStats').'">'.$this->l('Statistics').'</a></h4>
				<p>'.$this->l('Manage your activity with a thorough analysis of your e-shop.').'</p>
			</li>
			<li id="fourth_block">
				<h4><a href="index.php?tab=AdminEmployees&addemployee&token='.Tools::getAdminTokenLite('AdminEmployees').'">'.$this->l('New employee').'</a></h4>
				<p>'.$this->l('Add a new employee account and discharge a part of your duties of shop owner.').'</p>
			</li>
		</ul>
		';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT SUM(o.`total_paid_real` / o.conversion_rate) as total_sales, COUNT(*) as total_orders
		FROM `'._DB_PREFIX_.'orders` o
		WHERE o.valid = 1
		AND o.`invoice_date` BETWEEN \''.date('Y-m').'-01 00:00:00\' AND \''.date('Y-m').'-31 23:59:59\' ');
		$result2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(`id_customer`) AS total_registrations
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`date_add` BETWEEN \''.date('Y-m').'-01 00:00:00\' AND \''.date('Y-m').'-31 23:59:59\'');
		$result3 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT SUM(pv.`counter`) AS total_viewed
		FROM `'._DB_PREFIX_.'page_viewed` pv
		LEFT JOIN `'._DB_PREFIX_.'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
		LEFT JOIN `'._DB_PREFIX_.'page` p ON pv.`id_page` = p.`id_page`
		LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
		WHERE pt.`name` = \'product.php\'
		AND dr.`time_start` BETWEEN \''.date('Y-m').'-01 00:00:00\' AND \''.date('Y-m').'-31 23:59:59\'
		AND dr.`time_end` BETWEEN \''.date('Y-m').'-01 00:00:00\' AND \''.date('Y-m').'-31 23:59:59\'');
		$results = array_merge($result, array_merge($result2, $result3));
		echo '
		<div class="table_info">
			<h5><a href="index.php?tab=AdminStats&token='.Tools::getAdminTokenLite('AdminStats').'">'.$this->l('View more').'</a> '.$this->l('Month Statistics').' </h5>
			<table class="table_info_details">
				<tr class="tr_odd">
					<td class="td_align_left">
					'.$this->l('Sales').'
					</td>
					<td>
						'.Tools::displayPrice($results['total_sales'], $currency).'
					</td>
				</tr>
				<tr>
					<td class="td_align_left">
						'.$this->l('Total registrations').'
					</td>
					<td>
						'.(int)($results['total_registrations']).'
					</td>
				</tr>
				<tr class="tr_odd">
					<td class="td_align_left">
						'.$this->l('Total orders').'
					</td>
					<td>
						'.(int)($results['total_orders']).'
					</td>
				</tr>
				<tr>
					<td class="td_align_left">
						'.$this->l('Product pages viewed').'
					</td>
					<td>
						'.(int)($results['total_viewed']).'
					</td>
				</tr>
			</table>
		</div>
		';
		$all = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'customer_thread');
		$unread = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'customer_thread` WHERE `status` = "open"');
		$pending = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'customer_thread` WHERE `status` LIKE "%pending%"');
		$close = $all - ($unread + $pending);
		echo '
		<div class="table_info" id="table_info_last">
			<h5><a href="index.php?tab=AdminCustomerThreads&token='.Tools::getAdminTokenLite('AdminCustomerThreads').'">'.$this->l('View more').'</a> '.$this->l('Customers service').'</h5>
			<table class="table_info_details">
				<tr class="tr_odd">
					<td class="td_align_left">
					'.$this->l('Thread unread').'
					</td>
					<td>
						'.$unread.'
					</td>
				</tr>
				<tr>
					<td class="td_align_left">
						'.$this->l('Thread pending').'
					</td>
					<td>
						'.$pending.'
					</td>
				</tr>
				<tr class="tr_odd">
					<td class="td_align_left">
						'.$this->l('Thread closed').'
					</td>
					<td>
						'.$close.'
					</td>
				</tr>
				<tr>
					<td class="td_align_left">
						'.$this->l('Total thread').'
					</td>
					<td>
						'.$all.'
					</td>
				</tr>
			</table>
		</div>

		<div id="table_info_large">
			<h5><a href="index.php?tab=AdminStats&token='.Tools::getAdminTokenLite('AdminStats').'">'.$this->l('View more').'</a> <strong>'.$this->l('Statistics').'</strong> / '.$this->l('Sales of the week').'</h5>
			<div id="stat_google">';

	define('PS_BASE_URI', __PS_BASE_URI__);
	$chart = new Chart();
	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT total_paid / conversion_rate as total_converted, invoice_date
		FROM '._DB_PREFIX_.'orders o
		WHERE valid = 1
		AND invoice_date BETWEEN \''.date('Y-m-d', strtotime('-7 DAYS', time())).' 00:00:00\' AND \''.date('Y-m-d H:i:s').'\'');
	foreach ($result as $row)
		$chart->getCurve(1)->setPoint(strtotime($row['invoice_date']), $row['total_converted']);
	$chart->setSize(580, 170);
	$chart->setTimeMode(strtotime('-7 DAYS', time()), time(), 'd');
	$chart->getCurve(1)->setLabel($this->l('Sales +Tx').' ('.strtoupper($currency->iso_code).')');
	$chart->display();
	echo '	</div>
		</div>
		<table cellpadding="0" cellspacing="0" id="table_customer">
			<thead>
				<tr>
					<th class="order_id"><span class="first">'.$this->l('ID').'</span></th>
					<th class="order_customer"><span>'.$this->l('Customer Name').'</span></th>
					<th class="order_status"><span>'.$this->l('Status').'</span></th>
					<th class="order_total"><span>'.$this->l('Total').'</span></th>
					<th class="order_action"><span class="last">'.$this->l('Action').'</span></th>
				<tr>
			</thead>
			<tbody>';

	$orders = Order::getOrdersWithInformations(10);
	$i = 0;
	foreach ($orders AS $order)
	{
		$currency = Currency::getCurrency((int)$order['id_currency']);
		echo '
				<tr'.($i % 2 ? ' id="order_line1"' : '').'>
					<td class="order_td_first order_id">'.(int)$order['id_order'].'</td>
					<td class="order_customer">'.Tools::htmlentitiesUTF8($order['firstname']).' '.Tools::htmlentitiesUTF8($order['lastname']).'</td>
					<td class="order_status">'.Tools::htmlentitiesUTF8($order['state_name']).'</td>
					<td class="order_total">'.Tools::displayPrice((float)$order['total_paid'], $currency).'</td>
					<td class="order_action">
						<a href="index.php?tab=AdminOrders&id_order='.(int)$order['id_order'].'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders').'" title="'.$this->l('Details').'"><img src="../img/admin/details.gif" alt="'.$this->l('See').'" /></a>
					</td>
				</tr>
			';
		$i++;
	}

	echo '
			</tbody>
		</table>

	</div>';

	echo '
	<div id="column_right">';

	$context = stream_context_create(array('http' => array('method'=>"GET", 'timeout' => 5)));
	$content = @file_get_contents('https://www.prestashop.com/partner/preactivation/preactivation-block.php?version=1.0&email='.urlencode(Configuration::get('PS_SHOP_EMAIL')).'&security='.md5(Configuration::get('PS_SHOP_EMAIL')._COOKIE_IV_), false, $context);
	$content = explode('|', $content);
	if ($content[0] == 'OK')
	{
		echo $content[2];
		$content[1] = explode('#%#', $content[1]);
		foreach ($content[1] as $partnerPopUp)
			if ($partnerPopUp)
			{
				$partnerPopUp = explode('%%', $partnerPopUp);
				if (!Configuration::get('PS_PREACTIVATION_'.strtoupper($partnerPopUp[0])))
				{
					echo $partnerPopUp[1];
					Configuration::updateValue('PS_PREACTIVATION_'.strtoupper($partnerPopUp[0]), 'TRUE');
				}
			}
	}


	echo '
		<div id="table_info_link">
			<h5>'.$this->l('PrestaShop Link').'</h5>
			<ul id="prestashop_link">
				<li>
					<p>'.$this->l('Discover the latest official guide :').'</p>
					<a href="http://www.prestashop.com/download/Userguide_'.(in_array($isoUser, array('en', 'es', 'fr')) ? $isoUser : 'en').'.pdf" target="_blank">'.$this->l('User Guide PrestaShop 1.3').'</a>
					<a href="http://www.prestashop.com/download/Techguide_'.(in_array($isoUser, array('fr', 'es')) ? $isoUser : 'fr').'.pdf" target="_blank">'.$this->l('Technical Documentation').'</a>
				</li>
				<li>
					<p>'.$this->l('Use the PrestaShop forum & discover a great community').'</p>
					<a href="http://www.prestashop.com/forums/" target="_blank">'.$this->l('Go to forums.prestashop.com').'</a>
				</li>
				<li>
					<p>'.$this->l('Enhance your Shop with new templates & modules').'</p>
					<a href="http://addons.prestashop.com" target="_blank">'.$this->l('Go to addons.prestashop.com').'</a>
				</li>
			</ul>
		</div>';
		if (@fsockopen('www.prestashop.com', 80, $errno, $errst, 3))
			echo '<iframe frameborder="no" style="margin: 0px; padding: 0px; width: 315px; height: 450px;" src="'.$protocol.'://www.prestashop.com/rss/news2.php?v='._PS_VERSION_.'&lang='.$isoUser.'"></iframe>';
	echo '</div>
	<div class="clear"></div>';

	echo Module::hookExec('backOfficeHome');
	}
}


