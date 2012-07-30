<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(_PS_MODULE_DIR_.'prestassurance/classes/psaRequest.php');
include_once(_PS_MODULE_DIR_.'prestassurance/classes/psaTools.php');
include_once(_PS_MODULE_DIR_.'prestassurance/classes/psaStats.php');


class prestassurance extends ModuleGraph
{
	private $_updatePsaQty = false;
	private $_html;
	private $_postErrors;
	private $_psaUrl = 'https://assurance.prestashop.com';
	private $_categories = array();
	private $_psa_cart_products = array();
	private $_cacheCheckAdd = array();
	public $categoriesMatch = array();

	const PSA_TAX_RATE = 9;

	public function __construct()
	{
		global $cookie , $psa_cookie;
		$psa_cookie = new Cookie('psa_cookie');
		if (!isset($psa_cookie->limited_country) || !strlen($psa_cookie->limited_country))
			$psa_cookie->limited_country = true;

		$this->name = 'prestassurance';
		$this->tab = 'front_office_features';
		$this->version = '1.3';
		$this->author = 'PrestaShop';
		$this->need_instance = 1;
		
		if (!Configuration::get('PSA_ORDER_STATUS'))
			$this->warning = $this->l('Vous devez configurer l\'option \'statut de commande\' pour que le module fonctionne');
		
		parent::__construct ();

		$this->displayName = $this->l('PrestaShop Assurance');
		$this->description = $this->l('The cart insurance, a service opportunity and additional benefit');

	}

	public function uninstall()
	{
		psaTools::deleteHiddenCategoryAndProduct();
		return parent::uninstall();
	}

	public function install()
	{
		if (parent::install())
		{
			psaTools::createHiddenCategoryAndProduct();
			if (!Configuration::updateValue('PSA_ID_MERCHANT', 0)
				or !Configuration::updateValue('PSA_KEY', 0)
				or !Configuration::updateValue('PSA_CGV_UPDATED', 0)
				or !Configuration::updateValue('PSA_ADDED_PRICE', '#ffffff')
				or !Configuration::updateValue('PSA_ADDED_BG', '#c2c2c2')
				or !Configuration::updateValue('PSA_ADDED_TXT', '#636363')
				or !Configuration::updateValue('PSA_NOT_ADDED_PRICE', '#ffffff')
				or !Configuration::updateValue('PSA_NOT_ADDED_BG', '#636363')
				or !Configuration::updateValue('PSA_NOT_ADDED_TXT', '#c2c2c2')
				or !Configuration::updateValue('PSA_PROPOSE_DISCONECT', 1)
				or !Configuration::updateValue('PSA_MINIMUM_THRESHOLD', 0)
				or !$this->createTable()
				or !$this->registerHook('header')
				or !$this->registerHook('top')
				or !$this->registerHook('cart')
				or !$this->registerHook('newOrder')
				or !$this->registerHook('adminOrder')
				or !$this->registerHook('backOfficeHeader')
				or !$this->registerHook('authentication')
				or !$this->registerHook('updateOrderStatus')
				or !$this->registerHook('customerAccount')
				/* or !$this->registerHook('AdminStatsModules') */
				or !$this->registerHook('myAccountBlock'))
				return false;
		}
		else
			return false;
		return true;
	}

	private function createTable()
	{
		// Checking compatibility with older PrestaShop and fixing it
		if (!defined('_MYSQL_ENGINE_'))
			define('_MYSQL_ENGINE_', 'MyISAM');

		$return = true;
		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_category_match` (
				`id_category` int(11) NOT NULL,
				`id_psa_category` int(11) NOT NULL,
				`minimum_price` decimal(20,2) NOT NULL,
				`maximum_price` decimal(20,2) NOT NULL,
				`minimum_product_price` decimal(20,2) NOT NULL,
				`maximum_product_price` decimal(20,2) NOT NULL,
				`impact_type` enum(\'percentage\', \'fixed_price\') NOT NULL default \'fixed_price\',
				`impact_value` decimal(20,2) NULL,
				`selling_price` decimal(20,2) NULL,
				`benefit` decimal(20,2) NULL,
			UNIQUE KEY `id_category` (`id_category`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_category_attribute` (
				`id_category` int(11) NOT NULL,
				`id_attribute` int(11) NOT NULL,
				`id_product_attribute` int(11) NOT NULL,
			UNIQUE KEY `id_category` (`id_category`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_cart` (
				`id_cart` int(11) NOT NULL,
				`id_product` int(11) NOT NULL,
				`id_product_attribute` int(11) NOT NULL,
				`id_psa_product_attribute` int(11) NOT NULL,
				`qty` int(11) NOT NULL,
				`deleted` tinyint(1),
				`order_valid` tinyint(4) NOT NULL default 0,
				UNIQUE KEY `id_psa_cart` (`id_cart`, `id_product`, `id_product_attribute`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_insurance_detail` (
				`id_order` int(11) NOT NULL,
				`order_valid` int(1) NOT NULL default 0,
				`order_valid_date` date NOT NULL,
				`id_agreement` int(11) NOT NULL,
				`has_errors` tinyint(1),
				`total_inssurance` decimal(20,2) NULL,
				`total_benefit` decimal(20,2) NULL,
				`date_add` datetime,
				UNIQUE KEY `id_psa_cart` (`id_order`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_insurance_detail_message` (
				`id_order` int(11) NOT NULL,
				`id_product` int(11) NOT NULL,
				`message` text ,
				`date_add` datetime
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_disaster` (
				`id_disaster`int(11) NOT NULL auto_increment ,
				`id_order` int(11) NOT NULL ,
				`id_psa_disaster` int(11) NOT NULL ,
				`id_product` int(11) NOT NULL ,
				`status` VARCHAR(64) NOT NULL ,
				`reason` VARCHAR(64) NOT NULL,
				`date_add` DATE,
				PRIMARY KEY  (`id_disaster`)
			) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		$return &= Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psa_disaster_comments` (
				`id_disaster` int(11) NOT NULL,
				`way` int(11) NOT NULL,
				`comment` TEXT,
				`date_add` DATE
			) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;');

		return $return;
	}

	public function lang($str)
	{
		return $str;
	}

	public function hookHeader($params)
	{
		if (!psaTools::checkEnvironement() or !psaTools:: checkLimitedCountry())
		{
			$this->cleanAllInsurance();
			return;
		}
		$html = '<script type="text/javascript"> var id_psa_product = '.(int)Configuration::get('PSA_ID_PRODUCT').' ; </script>
				<script type="text/javascript"> var order_page = false; </script>';
		if (preg_match('#order.php|order-opc.php#', $_SERVER['PHP_SELF']))
			$html .= '<script type="text/javascript"> order_page = true; </script>';

		if (preg_match('#history.php#', $_SERVER['PHP_SELF']) and Configuration::get('PS_ORDER_RETURN'))
		{
			$id_customer = (int)$params['cookie']->id_customer;
			$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
			$products = Db::getInstance()->ExecuteS('
												SELECT * FROM `'._DB_PREFIX_.'order_detail`
												WHERE `id_order` IN (SELECT id_order FROM `'._DB_PREFIX_.'orders` WHERE `id_customer` = '.(int)$id_customer.')');
			$html .= '<script type="text/javascript">
					$(document).ready(function(){';
			foreach($products as $product)
			{
				if ($product['product_id'] == $psa_product_id)
					$html .= '$(\'input[name="ids_order_detail['.(int)$psa_product_id.']"]\').remove();';
			}
			$html .= '});</script>';
		}

		Tools::addJs(_MODULE_DIR_.$this->name.'/js/psa_fo.js');
		Tools::addJs(_MODULE_DIR_.$this->name.'/js/disaster.js');
		Tools::addCSS(_MODULE_DIR_.$this->name.'/css/psa_fo.css');

		if (!Configuration::get('PSA_ENVIRONMENT') and psaTools::checkEnvironement())
		{
			$html .='
				<script type="text/javascript">
				//<![CDATA[
				$(document).ready(function()
				{
					$(\'body\').append().prepend(\'<div id="warn_mode" class="center" style="width:100%;padding:10px;border:solid 1px #D3C200;background-color:#FFFAC6">'.addslashes($this->l('Module : PrestaShop Assurance is in Pre-Production mode. All souscription will not impacte your account')).'</div>\');
				});
				//]]>
				</script>
			';
		}

		return $html;
	}

	public function hookbackOfficeHeader($params)
	{
		if (Tools::getValue('tab') == 'AdminCatalog' and Tools::getValue('id_product') == (int)Configuration::get('PSA_ID_PRODUCT'))
			Tools::redirectAdmin('?tab=AdminCatalog&token='.Tools::getAdminTokenLite('AdminCatalog'));
		$html = '';
		if (Tools::getValue('tab') == 'AdminAttributesGroups')
			$html .= '<script type="text/javascript">$(document).ready(function(){ $(\'.table td:contains(PrestaShop Assurance[Do not delete])\').each( function() { $(this).parent().remove(); });});</script>;';

		if (Tools::getValue('tab') == 'AdminModules' and Tools::getValue('configure') == $this->name)
			$html .= '
			<script type="text/javascript" src="../modules/'.$this->name.'/easyui/jquery.easyui.min.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/js/treegrid.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/js/jquery.bubblepopup.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/colorpicker/js/colorpicker.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
			
			<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/css/jquery.bubblepopup.css" />
			<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/fancybox/jquery.fancybox-1.3.4.css" />
			<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/easyui//themes/default/easyui.css" />
			<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/easyui//themes/icon.css" />
			<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/css/style.css" />
			<link rel="stylesheet" href="../modules/'.$this->name.'/colorpicker/css/colorpicker.css" type="text/css" />
			<script type="text/javascript" src="../modules/'.$this->name.'/js/psa_bo.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/js/jquery-typewatch.pack.js"></script>
			<script type="text/javascript" src="'._THEME_DIR_.'/js/tools.js"></script>
			
			<script type="text/javascript">
				var themePath = "../modules/'.$this->name.'/img/jquerybubblepopup-theme";
				var urlAjaxCategory = "../modules/'.$this->name.'/ajax.php";
			</script>
			<style>#content {padding: 0.7em;}</style>
			';
		if (Tools::getValue('tab') == 'AdminOrders')
			$html .= '<script>
						var ajax_url = \'../modules/'.$this->name.'/\';
						var token = \''.sha1(_COOKIE_KEY_.'prestassurance').'\';
					</script>
					<script type="text/javascript" src="../modules/'.$this->name.'/js/reSubmitInssurance.js"></script>';
		return $html;
	}

	public function hookbackOfficeFooter($params)
	{
		if (Tools::getValue('tab') == 'AdminOrders' and $id_order = Tools::getValue('id_order') and Configuration::get('PS_ORDER_RETURN'))
		{
			$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
			$products = Db::getInstance()->ExecuteS('
												SELECT * FROM `'._DB_PREFIX_.'order_detail`
												WHERE `id_order` = '.(int)$id_order);
			$html = '<script type="text/javascript">
					$(document).ready(function(){';
			foreach($products as $product)
			{
				if ($product['product_id'] == $psa_product_id)
					$html .= '$(\'input[name="id_order_detail['.(int)$psa_product_id.']"]\').parent().parent(\'tr\').children(\'.cancelCheck, .cancelQuantity\').html(\'\');';
			}
			$html .= '});</script>';
		}
		return $html;
	}

	public function hookadminOrder($params)
	{
		$result = Db::getInstance()->ExecuteS('SELECT *
				FROM '._DB_PREFIX_.'psa_insurance_detail
				WHERE id_order = '.(int)$params['id_order']);

		$messages = Db::getInstance()->ExecuteS('SELECT *
				FROM '._DB_PREFIX_.'psa_insurance_detail_message
				WHERE id_order = '.(int)$params['id_order'].'
				ORDER BY date_add DESC');

		if (!sizeof($result))
			return;
		$html =
			'<div id="reSubmitSouscriptionContent">
			<br class="clear">
				<fieldset style="'.($result[0]['has_errors'] ? 'background-color:#FAE2E3' : 'background-color:#DFFAD3').';width:400px">
				<legend><img src="'._PS_ADMIN_IMG_.'information.png" alt="" title="" />'.$this->displayName.'</legend>';

		if ($result[0]['has_errors'])
		{
			$html .= '
					<script type="text/javascript" src="../modules/'.$this->name.'/js/reSubmitInssurance.js"></script>
					<div class="center" style="padding:10px">
						<a href="#" class="button" onclick="reSubmitSouscription(\''.(int)$params['id_order'].'\');return false;"  style="padding:10px">
							'.$this->l('re-submit Souscription').'
						</a>
					</div>';
		}
		else
		{
			$html .= '
					<span><b>'.$this->l('Total Insurance').' : '.Tools::displayPrice($result[0]['total_inssurance']).'</b></span>
					<p style="color:green;"><b>'.$this->l('Your benefit').' : '.Tools::displayPrice($result[0]['total_benefit']).'</b>
					</p>';
		}
		$html .= '<p>'.$this->l('Souscription details').' :</p>
						<ul style="max-height:100px;overflow:auto">';
		foreach($messages as $message)
			$html .= '<li style="margin-bottom:10px"><b>'.$message['date_add'].'</b> - '.$message['message'].'</li>';
		$html .= '</ul></fieldset></div>';

		return $html;
	}

	public function hookcart($params)
	{
		global $cookie;	
		$cartObj = $params['cart'];
		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
	
		if ($this->_updatePsaQty)
			return;
		
		$cartObj = $params['cart'];
		if (!Validate::isLoadedObject($cartObj))
			return;

		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
		$preselected = (int)Configuration::get('PSA_PRESELECTED');

		$qtyByCategory = array();
		$cartProducts = $this->_formatPsCartProducts($cartObj->getProducts());

		$products = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.(int)$cookie->id_cart);
		//check if there is some product in cart directly in db
		if (!sizeof($products))
		{
			$this->_cleanPsaCartProducts($cartObj->id);
			return;
		}
		else
		{
			$onlyPsaProduct = true;
			foreach($products as $product)
				if ($product['id_product'] != $psa_product_id)
					$onlyPsaProduct &= false;

			if ($onlyPsaProduct)
			{
				$this->_cleanPsCartProducts($cartObj->id, true);
				return;
			}
		}

		$qtyByCategory = $this->_getQtyByCategory($cartProducts);
		$result = '';
		if (sizeof($qtyByCategory))
			$result = Db::getInstance()->ExecuteS('
												SELECT * FROM `'._DB_PREFIX_.'psa_category_attribute`
												WHERE `id_category` IN ('.implode(', ', array_keys($qtyByCategory)).')');
		
		if (!is_array($result))
			return;
		
		//format id attributes tab
		$ids_psa_attributes = array();
		foreach($result as $key => $val)
			if (!isset($ids_psa_attributes[$val['id_category']]))
				$ids_psa_attributes[$val['id_category']] = $val['id_product_attribute'];

		if (!sizeof($ids_psa_attributes))
			return;
		//get psa cart
		$this->_getPsaCartProducts($cartObj->id);
				
		$to_remove = array_diff_assoc($this->_psa_cart_products, $cartProducts);
		foreach($to_remove as $key => $val)
		{
			unset($this->_psa_cart_products[$key]);
			Db::getInstance()->Execute('
										DELETE FROM `'._DB_PREFIX_.'psa_cart`
										WHERE `id_cart` = '.(int)$val['id_cart'].' 
										AND `id_product` = '.(int)$val['id_product'].' 
										AND `id_product_attribute` = '.(int)$val['id_product_attribute']);
		}
		
		foreach($cartProducts as $identifier => $product)
		{
			if (!(int)$product['id_category_default'])
				continue;
			$id_category_default = $product['id_category_default'];
			$this->getCategoriesMatch(array($id_category_default));

			//check if matching has been set
			if (array_key_exists($id_category_default, $this->categoriesMatch))
			{
				$minimum_product_price = $this->categoriesMatch[$id_category_default]['minimum_product_price'];
				$maximum_product_price = $this->categoriesMatch[$id_category_default]['maximum_product_price'];
	
				//check if the product price is between min and max price of insurance
				if ($product['price_wt'] < $minimum_product_price or $product['price_wt'] > $maximum_product_price)
					continue;
				if (!psaTools::checkMinimumThreshold($product['price_wt'], $this->categoriesMatch[$id_category_default]['selling_price'] , Configuration::get('PSA_MINIMUM_THRESHOLD')))
					continue;
			}
			else
				continue;

			$id_psa_attribute = (int)$ids_psa_attributes[$product['id_category_default']];
			
			//check if the default category has been configured
			if (!array_key_exists((int)$product['id_category_default'], $ids_psa_attributes))
				continue;
			
			//check if product is in psa_cart products and add if not
			if (!isset($this->_psa_cart_products[$identifier]))
			{
				$tmp['id_cart'] = (int)$cartObj->id;
				$tmp['id_product'] = (int)$product['id_product'];
				$tmp['id_product_attribute'] = (int)$product['id_product_attribute'];
				$tmp['id_psa_product_attribute'] = (int)$id_psa_attribute;
				$tmp['qty'] = (int)$product['qty'];
				if ($preselected)
					$tmp['deleted'] = 0;
				else
					$tmp['deleted'] = 1;
				
				$this->_psa_cart_products[$identifier] = $tmp;
			}
			else
				$this->_psa_cart_products[$identifier]['qty'] = $product['qty'];
		}
		//update psa cart
		$this->_updatePsaCartProducts($cartProducts, $cartObj);
		$this->_updatePsCartProducts($ids_psa_attributes, $cartObj);

		$this->_reorderProductInCart($cartProducts, $ids_psa_attributes, $cartObj->id);
	}

	public function hooktop($params)
	{
		global $cookie, $smarty, $psa_cookie;
		
		$cartObj = $params['cart'];
		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');		
		if (!psaTools::checkEnvironement())
			return;
		
		if (!psaTools::checkLimitedCountry())
		{
			$this->_getPsaCartProducts((int)$cartObj->id);
			if (!$psa_cookie->limited_country)
				return;
			else if (count($cartObj->getProducts()))
			{
				$this->cleanAllInsurance();
				$psa_cookie->limited_country = 0;
				return $this->display(__FILE__, '/tpl/alert_limited_country.tpl');
			}
		}
		else
		{
			if (isset($cartObj->id))
				$cartObj->update();
			$psa_cookie->limited_country = 1;
		}
		
		$this->_getPsaCartProducts((int)$cartObj->id);
		
		if (array_key_exists('submitReorder', $_GET))
			$cartObj->update();

		$product = new Product((int)$psa_product_id);
		$combinaisons = $product->getAttributeCombinaisons($params['cookie']->id_lang);
		$combinaisonsTmp = array();
		foreach($combinaisons as $comb)
			$combinaisonsTmp[$comb['id_product_attribute']] = $comb;

		$combinaisons = $combinaisonsTmp;

		$cartQty = 0;//count number of product in cart without psa products
		$cartProducts = $this->_formatPsCartProducts($cartObj->getProducts());
		foreach($cartProducts as $identifier => $product)
			if ($product['id_product'] != $psa_product_id)
				$cartQty += $product['qty'];
		
		$to_remove = array_diff_assoc($this->_psa_cart_products, $cartProducts);

		foreach($to_remove as $key => $val)
			unset($this->_psa_cart_products[$key]);

		$psa_products = array();

		foreach($this->_psa_cart_products as $product)
		{
			if ($combinaisons[$product['id_psa_product_attribute']]['price'] == 0)//check if price has been set
				continue;
			
			$tmp['id_psa_product'] = $psa_product_id.'_'.$product['id_psa_product_attribute'];
			$tmp['id_product'] = $product['id_product'];
			$tmp['id_product_attribute'] = $product['id_product_attribute'];
			$tmp['name'] = $combinaisons[$product['id_psa_product_attribute']]['attribute_name'];
			$tmp['price'] = Tools::displayPrice($combinaisons[$product['id_psa_product_attribute']]['price'] * $product['qty']);
			$tmp['unit_price'] = Tools::displayPrice($combinaisons[$product['id_psa_product_attribute']]['price']);
			$tmp['deleted'] = $product['deleted'];
			$tmp['qty'] = $product['qty'];
			$tmp['token'] = sha1(_COOKIE_KEY_.$cartObj->id.$product['id_product'].$product['id_product_attribute']);
			$psa_products[] = $tmp;
		}
		return $this->getPsaCartTpl($psa_products, $cartObj, $cartQty);
	}
	
	public function hookauthentication($params)
	{
		if (!psaTools::checkLimitedCountry())
			$this->cleanAllInsurance();
	}
	
	private function cleanAllInsurance()
	{
		global $cookie;
		$cartObj = new Cart($cookie->id_cart);
		$this->_cleanPsaCartProducts((int)$cartObj->id);
		$this->_cleanPsCartProducts((int)$cartObj->id, true);
	}

	public function displayDisasterForm()
	{
		global $smarty, $cookie;

		$inssurance_orders = $this->getCustomerOrderValidWithInsurance((int)$cookie->id_customer);
		if (!$inssurance_orders || !is_array($inssurance_orders))
			$orders = array();
		elseif(sizeof($inssurance_orders))
			foreach($inssurance_orders as $order)
				$orders[] = array(
					'id_order' => (int)$order['id_order'],
					'date' => $order['order_date_add']
				);
			$smarty->assign(
				array(
					'orders' => $orders,
					'id_psa_product' => (int)Configuration::get('PSA_ID_PRODUCT'),
					'token_psa' => sha1(_COOKIE_KEY_.'prestassurance_fo'.$cookie->id_customer)
				)
			);
		return $this->display(__FILE__, 'tpl/disaster_form.tpl');
	}

	public function getOrderDisasterDetails($id_order)
	{
		global $cookie;
		$insurance = Db::getInstance()->ExecuteS('
			SELECT *  FROM `'._DB_PREFIX_.'psa_insurance_detail` pid
			WHERE pid.`id_order` = '.(int)$id_order);

		if (!is_array($insurance) || !$insurance)
			return array('hasError' => true, 'errors' => array($this->l('no detail for this order')));

		$insurance = $insurance[0];
		$details = array();
		if (!$this->timeIsUpToDeclare($insurance['order_valid_date']))
		{
			$details['id_order'] = (int)$insurance['id_order'];
			$tmp_order = new Order($details['id_order']);
			$details['product'] = $tmp_order->getProducts();
			$products_psa_cart_ids_tmp = Db::getInstance()->ExecuteS('SELECT id_product FROM `'._DB_PREFIX_.'psa_cart` WHERE id_cart = '.(int)$tmp_order->id_cart.' AND deleted !=1');
			
			foreach($products_psa_cart_ids_tmp as $id)
				$products_psa_cart_ids[] = $id['id_product'];
			
			foreach($details['product'] as $key => $product)
				if (!in_array($product['product_id'], $products_psa_cart_ids))
					unset($details['product'][$key]);
			
			$details['disaster'] = Db::getInstance()->ExecuteS('SELECT *
						FROM `'._DB_PREFIX_.'psa_disaster` pdi
						LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pdi.`id_product` = pl.`id_product`)
						WHERE pl.`id_lang` = '.(int)$cookie->id_lang.' AND pdi.`id_order` = '.(int)$insurance['id_order'].'
						ORDER BY pdi.`date_add` ');
			if(is_array($details['disaster']) || $details['disaster'])
			{
				foreach($details['disaster'] as $key => $val)
					$details['disaster'][$key]['comments'] = Db::getInstance()->ExecuteS('
							SELECT * FROM `'._DB_PREFIX_.'psa_disaster_comments` pdc
							WHERE pdc.`id_disaster` = '.(int)$val['id_disaster'].'
							ORDER BY pdc.`date_add` ');
			}
		}
		return array('hasError' => false, 'details' => $details);
	}

	public function getCustomerOrderValidWithInsurance($id_customer)
	{
		return  Db::getInstance()->ExecuteS('
			SELECT pid.*, o.*, o.`date_add` as order_date_add FROM `'._DB_PREFIX_.'psa_insurance_detail` pid
			LEFT JOIN `'._DB_PREFIX_.'orders` o ON (pid.`id_order` = o.`id_order`)
			WHERE o.`id_customer` = '.(int)$id_customer.' AND pid.`order_valid` = 1');
	}

	public function hookCustomerAccount($params)
	{
		return $this->hookmyAccountBlock($params);
	}

	public function hookmyAccountBlock($params)
	{
		global $cookie;
		if (!psaTools::checkEnvironement() or !psaTools:: checkLimitedCountry())
		{
			$this->cleanAllInsurance();
			return;
		}

		return $this->display(__FILE__, '/tpl/my_account.tpl');
	}

	public function hookUpdateOrderStatus($params)
	{
		global $cookie;

		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
		$psa_order_status = (int)Configuration::get('PSA_ORDER_STATUS');

		if (!$psa_order_status) //order status not configured
			return;

		$id_order = $params['id_order'];
		//check if there is insurance in this order
		if (!sizeof(Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$params['id_order'].' AND `product_id` = '.(int)$psa_product_id)))
			return;
		//check if the new order stat match with the configuration
		if ($params['newOrderStatus']->id != $psa_order_status)
			return;

		//check if insurance has already send to the platforme.
		if (sizeof(Db::getInstance()->executeS('SELECT `id_order` FROM `'._DB_PREFIX_.'psa_insurance_detail` WHERE `id_order` = '.(int)$id_order)))
			return;

		$id_cart = Db::getInstance()->getValue('SELECT `id_cart` FROM `'._DB_PREFIX_.'orders` WHERE `id_order` = '.(int)$params['id_order']);

		$cartObj = new Cart((int)$id_cart);
		$orderObj = new Order((int)$id_order);

		if (!Validate::isLoadedObject($cartObj))
			return;

		$this->_getPsaCartProducts((int)$cartObj->id);

		$combinaisons = $this->_getPsaCombinaisons($cookie->id_lang);

		$data = $this->_makeSubsriptionDatas($orderObj, $cartObj, $combinaisons);

		$response = $this->_sendSubsriptionDatas($data);

		$this->_saveSubscriptionDetails($response, $orderObj);
		
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'psa_insurance_detail` SET `order_valid` = 1 , `order_valid_date` = \''.$orderObj->date_add.'\' WHERE `id_order` = '.(int)$orderObj->id);
	}

	public function getPsaCartTpl($psa_products, $cartObj, $cartQty)
	{
		global $smarty, $cookie, $link;
		$link_conditions = '';
		if (Configuration::get('PSA_CMS_PAGE') != 0)
		{
			$cms = new CMS((int)(Configuration::get('PSA_CMS_PAGE')), (int)$cookie->id_lang);
			$link_conditions = $link->getCMSLink($cms, $cms->link_rewrite, true);
			if (!strpos($link_conditions, '?'))
				$link_conditions .= '?content_only=1';
			else
				$link_conditions .= '&content_only=1';
		}

		$nbr_insurance = 0;

		foreach($psa_products as $product)
			if (!$product['deleted'])
				$nbr_insurance +=1;
				
		if ($this->_cartHasInssurance() || !psaTools::openPopIn())
			$psa_customer_alert = false;
		else
			$psa_customer_alert = true;

		$cms = '';
		if (Configuration::get('PSA_CMS_PAGE_ALERT') != 0 and !$nbr_insurance)
			$cms = new CMS((int)(Configuration::get('PSA_CMS_PAGE_ALERT')), (int)$cookie->id_lang);

		$smarty->assign(array(
				'psa_products' => $psa_products,
				'add_to_cart_url' => __PS_BASE_URI__.'modules/'.$this->name.'/psaCart.php',
				'psa_added_price' => Configuration::get('PSA_ADDED_PRICE'),
				'psa_not_added_price' => Configuration::get('PSA_NOT_ADDED_PRICE'),
				'psa_added_bg' => Configuration::get('PSA_ADDED_BG'),
				'psa_added_txt' => Configuration::get('PSA_ADDED_TXT'),
				'psa_not_added_bg' => Configuration::get('PSA_NOT_ADDED_BG'),
				'psa_not_added_txt' => Configuration::get('PSA_NOT_ADDED_TXT'),
				'id_product_psa' => Configuration::get('PSA_ID_PRODUCT'),
				'link_conditions' => $link_conditions,
				'psa_customer_alert' => $psa_customer_alert,
				'psa_token' => sha1(_COOKIE_KEY_.'prestassurance_fo'.(int)$cookie->id_customer),
				'alert_psa_cms' => $cms,
				'id_cart' => $cartObj->id));
		$output = $this->display(__FILE__, '/tpl/psa_cart_summary.tpl').$this->display(__FILE__, '/tpl/psa_block_cart.tpl').
			'<script type="text/javascript"> var cartQty = '.(int)$cartQty.'; var ajaxCartActive = '.(int)Configuration::get('PS_BLOCK_CART_AJAX').'; </script>';
		return $output;
	}

	public function hooknewOrder($params)
	{
		$cartObj = $params['cart'];

		$this->_getPsaCartProducts((int)$cartObj->id);

		if ($this->_cartHasInssurance())
		{
			if (!psaTools::checkEnvironement() or !psaTools:: checkLimitedCountry())
			{
				$this->cleanAllInsurance();
				return;
			}

			$orderObj = $params['order'];

			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'psa_cart` SET `order_valid` = 1 WHERE `id_cart` = '.(int)$cartObj->id);
			
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'psa_insurance_detail` SET `order_valid` = 1 , `order_valid_date` = \''.$orderObj->date_add.'\' WHERE `id_order` = '.(int)$orderObj->id);
			
			$combinaisons = $this->_getPsaCombinaisons($params['cookie']->id_lang);

			$this->_alterOrderDetails($orderObj, $cartObj, $combinaisons);

			$this->_cleanValidateCartForReOrder($cartObj); //delete all psa_product in cart for later reOrder

			//re-inject quantity to psa product
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'product_attribute` SET `quantity` = 100 WHERE `id_product` = '.(int)Configuration::get('PSA_ID_PRODUCT'));
		}
	}

	public function getContent()
	{
		if (!function_exists('curl_init'))
		{
			return '<div class="warn">'.
				$this->l('You must activate').' <a style="color:blue;text-decoration:underline" target="_blank" href="http://php.net/manual/ref.curl.php">curl</a> '.
				$this->l('to use this module.').'<br />'.
				$this->l('Contact your system administrator').$this->helpPsa('curl').'</div>';
		}
		
		/*
if (psaTools::checkLocalUse())
		{
			return '<div class="warn">'.
				$this->l('Your PrestaShop appears to be installed locally').'<br/>'.
				$this->l('To use this module you have to install the module remotely on your web server').$this->helpPsa('local_use').'</div>';
		}
*/
		

		$currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
		$this->_html .=
			$this->_displayAcceptCgv().'
			<div id="psa_content">
			<h2>' . $this->l('PrestaShop Assurance').'</h2>
			<script>
				var token = \''.sha1(_COOKIE_KEY_.'prestassurance').'\';
				var ajax_url = \'../modules/'.$this->name.'/\';
				var img_url = \'../modules/'.$this->name.'/\';
				var cgv_updated = \''.Configuration::get('PSA_CGV_UPDATED').'\';
				var currencyFormat = '.$currency->format.';
				var currencySign = \''.$currency->sign.'\';
				var priceDisplayPrecision = 2;
			</script>';

		if (Tools::isSubmit('submitPreselected') || Tools::isSubmit('submitMinimum') || Tools::isSubmit('reactive'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="warning">'.$err.'</div>';
		}
		return $this->_displayContent().'</div>';
	}

	private function _displayContent()
	{
		if (!Configuration::get('PSA_ID_MERCHANT') or !Configuration::get('PSA_KEY'))
			return $this->_displaySignInForm();
		else
			return $this->_displayConfiguration();
	}

	private function _postValidation()
	{
		//$this->_postErrors;
	}

	private function _postProcess()
	{		
		if (Tools::isSubmit('reactive'))
		{
			if ($this->reactive(Tools::getValue('merchant_id'), Tools::getValue('email')))
				$this->_html .= '<div class="conf"><img src="'._PS_ADMIN_IMG_.'ok2.png">'.$this->l('Le module est actif').'</div>';
			else
				$this->_html .= '<div class="error"><img src="'._PS_ADMIN_IMG_.'error2.png">'.$this->l('Erreur lors de l\'activation du module. Merci de contacter notre service support').'</div>';
		}
		
		if (Tools::isSubmit('submitMinimum'))
		{
			if (Configuration::updateValue('PSA_MINIMUM_THRESHOLD', (float)str_replace(',', '.', Tools::getValue('PSA_MINIMUM_THRESHOLD'))))
				$this->_html .= '<div class="conf"><img src="'._PS_ADMIN_IMG_.'ok2.png">'.$this->l('Configuration saved successfully').'</div>';
			else
				$this->_html .= '<div class="error"><img src="'._PS_ADMIN_IMG_.'error2.png">'.$this->l('Configuration save failed').'</div>';
		}

		if (Tools::isSubmit('submitPreselected'))
		{
			if (Configuration::updateValue('PSA_PRESELECTED', Tools::getValue('PSA_PRESELECTED'))
				and Configuration::updateValue('PSA_CMS_PAGE', Tools::getValue('PSA_CMS_PAGE'))
				and Configuration::updateValue('PSA_ORDER_STATUS', Tools::getValue('PSA_ORDER_STATUS'))
				and Configuration::updateValue('PSA_CMS_PAGE_ALERT', Tools::getValue('PSA_CMS_PAGE_ALERT'))
				and Configuration::updateValue('PSA_PROPOSE_DISCONECT', Tools::getValue('PSA_PROPOSE_DISCONECT'))
				and Configuration::updateValue('PSA_ENVIRONMENT', Tools::getValue('PSA_ENVIRONMENT'))
				and Configuration::updateValue('PSA_IP_ADDRESS', Tools::getValue('PSA_IP_ADDRESS'))
				and Configuration::updateValue('PSA_ADDED_PRICE', Tools::getValue('PSA_ADDED_PRICE'))
				and Configuration::updateValue('PSA_ADDED_BG', Tools::getValue('PSA_ADDED_BG'))
				and Configuration::updateValue('PSA_ADDED_TXT', Tools::getValue('PSA_ADDED_TXT'))
				and Configuration::updateValue('PSA_NOT_ADDED_PRICE', Tools::getValue('PSA_NOT_ADDED_PRICE'))
				and Configuration::updateValue('PSA_NOT_ADDED_BG', Tools::getValue('PSA_NOT_ADDED_BG'))
				and Configuration::updateValue('PSA_NOT_ADDED_TXT', Tools::getValue('PSA_NOT_ADDED_TXT')))
				$this->_html .= '<div class="conf"><img src="'._PS_ADMIN_IMG_.'ok2.png">'.$this->l('Configuration saved successfully').'</div>';
			else
				$this->_html .= '<div class="error"><img src="'._PS_ADMIN_IMG_.'error2.png">'.$this->l('Configuration save failed').'</div>';
		}
	}

	private function _displayAcceptCgv()
	{
		return '
		<div id="psa_cgv" class="center" style="display:none;padding:20px;background-color:#DDE9F7;border:solid 1px #50B0EC">
			<p>'.$this->l('CGV has change please accept them before continue').' <a href="#cgv_psa" onclick="$(\'.cgv_psa\').slideDown();" style="text-decoration:underline" onclick="" >'.$this->l('Read').'</a></p>
			<div style="display:none" class="cgv_psa" ></div>
			<br/><a class="button" style="padding:10px" href="#" onclick="acceptCGV();">'.$this->l('Accept').'</a>
		</div>';
	}

	private function _displayMarketIntro()
	{
		return  '
		<p><b style="color:green">'.$this->l('En partenariat avec FMA Risk et DEVK (AMG), 4ème assureur allemand avec 4 millions de clients, PrestaShop propose un service d\'assurance gratuit pour l\'e-marchand.').'</b></p>
		<p>'.$this->l('Proposez à vos clients d\'assurer les produits qu\'ils achètent sur votre boutique en ligne, pendant 60 jours après leur achat et pour une valeur maximum de 1.500 euros TTC par article assuré.').'</p>
		<ul>
			<li><b>Assurance Casse :</b> remboursement du produit en cas de dommages accidentels.</li>
			<li><b>Assurance Vol :</b> remplacement à neuf du bien acheté en cas de vol.</li>
			<li><b>Assurance Livraison :</b> rn cas d\'absence de livraison, le bien acheté est remboursé.</li>
			<li><b>Assurance continuité de service :</b> remboursement des abonnements internet en cas de chômage ou de maladie.</li>
			<li style="list-style: none;">
				<a style="text-decoration:underline" target="blank" href="'.$this->_psaUrl.'/pdf/notice-information-assurance.pdf">'.$this->l('Cliquez ici pour voir la notice d’information').'</a>
			</li>
			<li style="list-style: none;">
				<a style="text-decoration:underline" class="fancyBox" href="#excluded_product_list">'.$this->l('Cliquez ici pour voir la liste des exclusions de garantie de l\'assurance panier').'</a>
			</li>
		</ul>
		
		<p>'.$this->l('Les 3 avantages clés de PrestaShop Assurance :').'</p>
		<ul>
			<li><b>Augmentez le taux de transformation </b>de votre boutique en rassurant vos clients.</li>
			<li><b>Générez des revenus supplémentaires :</b> vous percevez 25% du prix de vente HT sur chaque assurance vendue.</li>
			<li><b>Gagnez en temps et en efficacité </b>sur la gestion de votre SAV.</li>
		</ul>
		<p>'.$this->l('Deux étapes suffisent :').'</p>
		<ol>
			<li>'.$this->l('Inscrivez-vous au service auprès de PrestaShop.').' <b style="color:green">Voir ci-contre</b><br>
			<a style="text-decoration:underline" href="https://assurance.prestashop.com/pdf/cgu-prestashop-assuance.pdf">Cliquez ici pour voir les CGU du service PrestaShop Assurance</a></li>
			<li>'.$this->l('Configurez du montant de la cotisation d\'assurance par catégorie et sous-catégorie de produits dans votre catalogue. (cette étape sera accessible dès la validation de votre inscription)').'</li>
		</ol>
			<p><img src="../modules/'.$this->name.'/img/help.png"> '
				.$this->l('Vous retrouverez cette petite icône tout au long de la configuration du module. N\'hésitez pas à passer la souris dessus pour obtenir de l\'aide.').'</p>
		';
	}

	private function _displaySignInForm()
	{
		global $cookie;
		$culture = new Language((int)$cookie->id_lang);

		$this->_html .= '
			<script>
				var fill_all_input = \''.$this->l('Veuillez renseigner tous les champs pour importer votre configuration').'\';
				var no_config_found = \''.$this->l('No configuration found with those information').'\';
			</script>
			<fieldset style="float:right;width:320px">
				<legend><img src="'._PS_ADMIN_IMG_.'employee.gif" alt="" title="" />'.$this->l('S\'inscrire').'</legend>
				<div class="hint clear" style="display:block;background:#DDE9F7">
					<p>'.$this->l('Before begin the process, make sure you have the following information :').'</p>
					<ul style="margin-left:-25px">
						<li>'.$this->l('Votre Numéro de Siret').'</li>
						<li>'.$this->l('Your RIB').$this->helpPsa('rib').'</li>
					</ul>
				</div>
				<div style="display:none">
					<div id="excluded_product_list">'.$this->displayExcludedProductList().'</div>
				</div>
				<div class="clear"><br/></div>
				<form action="'.$this->_psaUrl.'/'.$culture->iso_code.'/signin/step/1" method="post">
				<p style="text-align:center"><b>'.$this->l('Cliquez ici pour créer un compte sur le site de PrestaShop Assurance').' : </b></p>
				<div class="clear"><br/></div>
				<div class="center">
					'.$this->_displayHiddenSignInInput().'
					<button type="submit" name="submitSignInPSA" id="inscription">'.$this->l('Sign In').'</button>
				</div>
				</form>
				<div style="margin-top:20px">
					<a href="#" onclick="$(\'#reactive\').fadeToggle();">Cliquez <span style="text-decoration:underline">ici</span> si vous avez déjà un compte</a>
					<div id="reactive" style="display:none;padding-top:20px">
						<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
							<label style="width:130px">Email d\'inscription :</label>
							<div class="margin-form" style="padding-left:150px">
								<input type="text" name="email">'.$this->helpPsa('email_subscribe').'
							</div>
							<label style="width:130px">Id marchand :</label>
							<div class="margin-form" style="padding-left:150px">
								<input type="text" name="merchant_id">'.$this->helpPsa('merchant_id').'
							</div>
							<div class="margin-form" style="padding-left:150px">
								<input type="submit" class="button" value="Activer le module" />
							</div>
							<input type="hidden" name="reactive" value="reactive"/>
						</form>
					</div>
				</div>
			</fieldset>
			
			<fieldset style="width:550px">
				'.$this->_displayMarketIntro().'
			</fieldset>
			
			<div class="clear"><br/></div>
			<fieldset style="float:right;width:400px;display:none">
				<legend><img src="'._PS_ADMIN_IMG_.'employee.gif" alt="" title="" />'.$this->l('Importer une configuration existante').'</legend>
				<div class="hint clear" style="display:block">
					<p>'.$this->l('Votre configuration sera automatiquement importée dans votre module.').'</p>
					<p>'.$this->l('Once the import is complete, you will access to your module configuration.').'</p>
				</div>
				<div class="clear"><br></div>
				<div class="error" id="error_return" style="display:none"></div>
				<label style="width:110px">'.$this->l('Votre ID unique').' : </label>
				<div class="margin-form">
					<input id="unique_id" type="text">
					<p>'.$this->l('Indiquez le numéro d\'identification unique qui vous a été fourni lors de l\'export de votre configuration').'</p>
				</div>
				<label style="width:110px">'.$this->l('Your email').' : </label>
				<div class="margin-form">
					<input id="email" type="text">
					<p>'.$this->l('Indiquez l\'e-mail que vous avez utilisé lorsque vous vous êtes abonné à PrestaShop Assurance').'</p>
				</div>
				<div class="margin-form">
					<button class="button" onclick="importConfig();" style="height:50px;width:80px;"/>'.$this->l('Import').'</button>
					<div id="ajax-loader-export" style="display:none">
						<img src="../modules/'.$this->name.'/img/ajax-loader.gif">
					</div>
				</div>
			</fieldset>';


		return $this->_html;
	}

	private function _displayHiddenSignInInput()
	{
		global $cookie;
		$employee = new Employee($cookie->id_employee);
		$inputName = array(
			'PS_SHOP_ACTIVITY' => 'shop_activity', 'PS_SHOP_EMAIL' => 'email', 'PS_SHOP_NAME' => 'shop_name', 'PS_SHOP_ADDR1' => 'address1', 'PS_SHOP_ADDR2' => 'address2',
			'PS_SHOP_CODE' => 'postcode', 'PS_SHOP_CITY' => 'city', 'PS_SHOP_COUNTRY' => 'country');

		$config = Configuration::getMultiple(array('PS_SHOP_ACTIVITY', 'PS_SHOP_EMAIL', 'PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_ADDR2', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY_ID'));
		if (isset($config['PS_SHOP_COUNTRY_ID']) and $config['PS_SHOP_COUNTRY_ID'] != 0)
		{
			$config['PS_SHOP_COUNTRY'] = Country::getIsoById((int)$config['PS_SHOP_COUNTRY_ID']);
			unset($config['PS_SHOP_COUNTRY_ID']);
		}
		$config['firstname'] = $employee->firstname;
		$config['lastname'] = $employee->lastname;
		$config['shop_url'] = Tools::getShopDomain(true);
		$config['signin_url_return'] = $this->getSignInUrlReturn();
		$out = '';
		foreach($config as $key => $val)
			$out .= '<input type="hidden" name="'.(isset($inputName[$key]) ? $inputName[$key] : $key).'" value="'.Tools::htmlentitiesUTF8($val).'" />';
		return $out;
	}

	private function _displayConfiguration()
	{
		$this->_html .= '
			<script type="text/javascript">
				var pos_select = '.(($tab = (int)Tools::getValue('tabs')) ? $tab : '0').';
				var addr = \''.Tools::getRemoteAddr().'\';
				var tab_1 = \''.$this->l('Parameters').'\';
				var tab_2 = \''.$this->l('Categories').'\';
				var tab_3 = \''.$this->l('Aide / Notice / CGU').'\';
			</script>
			
			<div id="tab-pane-1" class="easyui-tabs" style="width:940px"></div>
			<div style="display:none">
				<div id="tab_1">'.$this->_displayParametersTab().'</div>
				<div id="tab_2">'.$this->_displayCategoriesTab().'</div>
				<div id="tab_3">'.$this->_displayHelpTab().'</div>
			</div>
			<div class="clear"></div>';

		return $this->_html;
	}

	private function _displayParametersTab()
	{
		global $cookie;
		$this->getCategoriesMatch(1);
		$ps_order_states = OrderState::getOrderStates($cookie->id_lang);
		$cms_pages = CMS::getCMSPages((int)$cookie->id_lang);
		$html = '
			<fieldset style="width: 903px;">
				<legend><img src="'._PS_ADMIN_IMG_.'cog.gif" alt="" title="" />'.$this->l('Configuration').'</legend>
					<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'&tabs=0" method="post">
						<label>'.$this->l('Activation').' : </label>
						<div class="margin-form">
							<label class="t" for="PSA_ENVIRONMENT_ON">
								<img src="../modules/'.$this->name.'/img/pre_prod.png" alt="Pre-prod" title="Pre-prod"></label>
							<input type="radio" name="PSA_ENVIRONMENT" id="PSA_ENVIRONMENT_PRE_PROD" value="0" '.(!Configuration::get('PSA_ENVIRONMENT') ? 'checked="checked"' : '').'>
							<label class="t" for="PSA_ENVIRONMENT_PRE_PROD">'.$this->l('Tester le module').'</label>
							<label class="t" for="PSA_ENVIRONMENT_OFF">
								<img src="../modules/'.$this->name.'/img/prod.png" alt="Prod" title="Prod" style="margin-left: 10px;"></label>
							<input type="radio" name="PSA_ENVIRONMENT" id="PSA_ENVIRONMENT_PROD" value="1" '.(Configuration::get('PSA_ENVIRONMENT') ? 'checked="checked"' : '').'>
							<label class="t" for="PSA_ENVIRONMENT_PROD">'.$this->l('Activer le module').'</label>'.$this->helpPsa('environnement').'
							<p style="clear:both">'.$this->l('￼Indiquez si l’assurance est visible uniquement pour vos tests ou par tous les internautes').'.</p>
						</div>
						<div id="preprod_ip">
							<label>'.$this->l('IP address of pre-prod').' : </label>
							<div class="margin-form">
								<input type="text" size="50" name="PSA_IP_ADDRESS" value="'.Tools::getValue('PSA_IP_ADDRESS', Configuration::get('PSA_IP_ADDRESS')).'">
								<a href="#" class="button" onclick="addRemoteAddr(); return false;">'.$this->l('Click here to add your ip').'</a>
								<p style="clear:both">'.
				$this->l('Set address of Pre-prod. All other addresses will not show the module in pre-prod mode. Use a comma to separate them (e.g., 42.24.4.2,127.0.0.1,99.98.97.96)').'.</p>
							</div>
						</div>
						<label>'.$this->l('Ajout automatique').' : </label>
						<div class="margin-form">
							<label class="t" for="PSA_PRESELECTED_ON">
								<img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="Oui" title="Oui"></label>
							<input type="radio" name="PSA_PRESELECTED" id="PSA_PRESELECTED_ON" value="1" '.(Configuration::get('PSA_PRESELECTED') ? 'checked="checked"' : '').'>
							<label class="t" for="PSA_PRESELECTED_ON">'.$this->l('Yes').'</label>
							<label class="t" for="PSA_PRESELECTED_OFF">
								<img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="Non" title="Non" style="margin-left: 10px;"></label>
							<input type="radio" name="PSA_PRESELECTED" id="PSA_PRESELECTED_OFF" value="0" '.(!Configuration::get('PSA_PRESELECTED') ? 'checked="checked"' : '').'>
							<label class="t" for="PSA_PRESELECTED_OFF">'.$this->l('No').'</label>
							<p style="clear:both">'.$this->l('En sélectionnant cette option, l\'assurance sera ajoutée par défaut au panier de vos clients').'.</p>
						</div>
						<label>'.$this->l('Proposer l\'assurance même aux clients non connectés').' : </label>
						<div class="margin-form">
							<label class="t" for="PSA_PROPOSE_DISCONECT_ON">
								<img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="Oui" title="Oui"></label>
							<input type="radio" name="PSA_PROPOSE_DISCONECT" id="PSA_PROPOSE_DISCONECT_ON" value="1" '.(Configuration::get('PSA_PROPOSE_DISCONECT') ? 'checked="checked"' : '').'>
							<label class="t" for="PSA_PROPOSE_DISCONECT_ON">'.$this->l('Yes').'</label>
							<label class="t" for="PSA_PROPOSE_DISCONECT_OFF">
								<img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="Non" title="Non" style="margin-left: 10px;"></label>
							<input type="radio" name="PSA_PROPOSE_DISCONECT" id="PSA_PROPOSE_DISCONECT_OFF" value="0" '.(!Configuration::get('PSA_PROPOSE_DISCONECT') ? 'checked="checked"' : '').'>
							<label class="t" for="PSA_PROPOSE_DISCONECT_OFF">'.$this->l('No').'</label>
							<p style="clear:both">'.$this->l('Choose if the insurance will be propose when your customer is not connected').'.</p>
						</div>
						<label>'.$this->l('Order Status').' : </label>
						<div class="margin-form">
							<select name="PSA_ORDER_STATUS">
								<option value="0">'.$this->l('Select an order status').'</option>';
		foreach($ps_order_states as $status)
			$html .= '<option '.(Tools::getValue('PSA_ORDER_STATUS', Configuration::get('PSA_ORDER_STATUS')) == (int)$status['id_order_state'] ? 'selected="selected"' : '').' value="'.(int)$status['id_order_state'].'">
								'.$status['name'].'
								</option>';
		$html .= '
							</select>'.$this->helpPsa('order_status').'
						</div>
						<label>'.$this->l('"Read More" link').' : </label>
						<div class="margin-form">
							<select name="PSA_CMS_PAGE">
								<option value="0">'.$this->l('Select a cms page').'</option>';
		foreach($cms_pages as $page)
			$html .= '<option '.(Tools::getValue('PSA_CMS_PAGE', Configuration::get('PSA_CMS_PAGE')) == (int)$page['id_cms'] ? 'selected="selected"' : '').' value="'.(int)$page['id_cms'].'">
								'.$page['meta_title'].'
								</option>';
		$html .= '
							</select>'.$this->helpPsa('read_more_link').'
						</div>
						<label>'.$this->l('Customer alert').' : </label>
						<div class="margin-form">
							<select name="PSA_CMS_PAGE_ALERT">
								<option value="0">'.$this->l('Select a cms page').'</option>';
		foreach($cms_pages as $page)
			$html .= '<option '.(Tools::getValue('PSA_CMS_PAGE_ALERT', Configuration::get('PSA_CMS_PAGE_ALERT')) == (int)$page['id_cms'] ? 'selected="selected"' : '').' value="'.(int)$page['id_cms'].'">
								'.$page['meta_title'].'
								</option>';
		$html .= '
							</select>
							<p style="clear:both">'.$this->l('This cms page\'s will be displayed if the customer did not add any insurance in his cart').'.</p>
						</div>
						<h2>'.$this->l('Personnalisation de l’apparence graphique de l\'assurance au niveau du panier').' :</h2>
						<label>'.$this->l('Inssurance selected').' :</label>
						<div class="margin-form">
							<table>
								<tr>
									<td>'.$this->l('Background color').' :</td>
									<td>
										<div class="color-picker">
						        			<div style="background-color: #'.Tools::getValue('PSA_ADDED_BG', Configuration::get('PSA_ADDED_BG')).'"></div>
						   				</div>
						   				<input value="'.Tools::getValue('PSA_ADDED_BG', Configuration::get('PSA_ADDED_BG')).'" id="PSA_ADDED_BG" name="PSA_ADDED_BG" type="hidden" data-hex="true" />
						   			 </td>
									<td>'.$this->l('Text color').' :</td>
									<td>
										<div class="color-picker">
						        			<div style="background-color: #'.Tools::getValue('PSA_ADDED_TXT', Configuration::get('PSA_ADDED_TXT')).'"></div>
						   				</div>
						   				<input value="'.Tools::getValue('PSA_ADDED_TXT', Configuration::get('PSA_ADDED_TXT')).'" id="PSA_ADDED_TXT" name="PSA_ADDED_TXT" type="hidden" data-hex="true" />
									</td>
									<td>'.$this->l('Price color').' :</td>
									<td>
										<div class="color-picker">
						        			<div style="background-color: #'.Tools::getValue('PSA_ADDED_TXT', Configuration::get('PSA_ADDED_TXT')).'"></div>
						   				</div>
						   				<input value="'.Tools::getValue('PSA_ADDED_PRICE', Configuration::get('PSA_ADDED_PRICE')).'" id="PSA_ADDED_PRICE" name="PSA_ADDED_PRICE" type="hidden" data-hex="true" />
									</td>
								</tr>
							</table>
					    	<p>'.$this->l('You can choose the background color and the text color of the product line "Insurance" when it\'s added').'.</p>
					    </div>
					    <label>'.$this->l('Inssurance not selected').' :</label>
						<div class="margin-form">
							<table>
								<tr>
									<td>'.$this->l('Background color').' :</td>
									<td>
										<div class="color-picker">
						        			<div style="background-color: #'.Tools::getValue('PSA_NOT_ADDED_BG', Configuration::get('PSA_NOT_ADDED_BG')).'"></div>
						   				</div>
						   				<input value="'.Tools::getValue('PSA_NOT_ADDED_BG', Configuration::get('PSA_NOT_ADDED_BG')).'" id="PSA_NOT_ADDED_BG" name="PSA_NOT_ADDED_BG" type="hidden" data-hex="true" />
						   			 </td>
									<td>'.$this->l('Text color').' :</td>
									<td>
										<div class="color-picker">
						        			<div style="background-color: #'.Tools::getValue('PSA_NOT_ADDED_TXT', Configuration::get('PSA_NOT_ADDED_TXT')).'"></div>
						   				</div>
						   				<input value="'.Tools::getValue('PSA_NOT_ADDED_TXT', Configuration::get('PSA_NOT_ADDED_TXT')).'" id="PSA_NOT_ADDED_TXT" name="PSA_NOT_ADDED_TXT" type="hidden" data-hex="true" />
									</td>
									<td>'.$this->l('Price color').' :</td>
									<td>
										<div class="color-picker">
						        			<div style="background-color: #'.Tools::getValue('PSA_NOT_ADDED_PRICE', Configuration::get('PSA_NOT_ADDED_PRICE')).'"></div>
						   				</div>
						   				<input value="'.Tools::getValue('PSA_NOT_ADDED_PRICE', Configuration::get('PSA_NOT_ADDED_PRICE')).'" id="PSA_NOT_ADDED_PRICE" name="PSA_NOT_ADDED_PRICE" type="hidden" data-hex="true" />
									</td>
								</tr>
							</table>
					    	<p>'.$this->l('You can choose the background color and the text color of the product line "Insurance" when it\'s not added').'.</p>
					    </div>
						<div class="center">
							<input type="submit" name="submitPreselected" value="'.$this->l('Save').'" class="button" />
						</div>
					</form>
			</fieldset>
			<div class="clear"></div>';
		return $html;
	}

	private function _displayCategoriesTab()
	{
		return '
			<fieldset>
				<legend><img src="'._PS_ADMIN_IMG_.'tab-categories.gif" alt="" title="" />'.$this->l('Seuil minimum').'</legend>
				<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
					<div class="hint clear" style="display:block">
						<p>'.$this->l('Vous pouvez décider de ne proposer l\'assurance sur un article que si le montant de la cotisation d\'assurance ne représente pas plus d\'une certaine proportion du prix de vente TTC de l\'article concerné.').'</p>
						<p><b>'.$this->l('Exemple :').'</b>
						'.$this->l('dans une catégorie, le montant de la cotisation est fixée à 2€ TTC. On trouve dans cette catégorie des articles dont le prix va de 5€ à 500€. En tant que marchand, je ne souhaite pas proposer l\'assurance lorsque le montant de celle-ci représente plus de 10% du prix de vente TTC de l\'article. Dans notre exemple, tous les articles à moins de 20€ TTC (2€ / 10%) ne pourront donc pas être assurés.').'
						</p>
					</div>
					<div class="clear"><br/></div>
					<label>'.$this->l('Seuil minimum').' : </label>
					<div class="margin-form">
						<input type="text" name="PSA_MINIMUM_THRESHOLD" value="'.Tools::getValue('PSA_MINIMUM_THRESHOLD', Configuration::get('PSA_MINIMUM_THRESHOLD')).'"> %
						<p>'.$this->l('Saisir 0 pour désactiver cette fonctionnalité').'</p>
					</div>
					<div class="center">
						<input type="hidden" name="tabs" value="1" />
						<input type="submit" name="submitMinimum" value="'.$this->l('Save').'" class="button" />
					</div>
				</form>
			</fieldset>
			<div class="clear"><br/></div>
			<fieldset>
				<legend><img src="'._PS_ADMIN_IMG_.'tab-categories.gif" alt="" title="" />'.$this->l('Montant de la cotisation d\'assurance par catégorie de produits vendus').'</legend>
					<div class="hint clear" style="display:block">
					<p>'.$this->l('Vous devez désormais définir le montant de la cotisation d’assurance affectée à chaque catégorie et sous-catégories de produits de votre catalogue. Le minimum est fixé à 1,19 euros TTC par article assuré. Vous avez néanmoins la possibilité d’augmenter ce montant dans la limite de 2% du prix de vente TTC et jusqu’à 30 euros TTC par article assuré.').'</p>
					<br>
					<p>'.$this->l('La configuration se fait en 2 étapes :').'</p>
					<ul>
						<li>'.$this->l('Faîtes tout d\'abord correspondre vos catégories et sous-catégories de produits avec la nomenclature utilisée par l\'assureur').'.</li>
						<li>'.$this->l('Affecter ensuite le montant de la cotisation que vous souhaitez mettre en place pour chaque catégorie et sous-catégorie de votre catalogue (en respectant le plancher et le plafond indiqués ci-dessus)').'.</li>
					</ul>
					</div>
					<hr/>
					<table id="treegrid" style="background-color:#FFF"></table>
					
					
					<div style="display:none">
						<div id="win_set_category_match" title="'.$this->l('Choisir une catégorie d\'assurance').'"></div>
						<div id="win_set_price" title="'.$this->l('Choisir le montant de la cotisation d’assurance').'"></div> 
					</div>
					
			</fieldset>';
	}

	private function _displayHelpTab()
	{
		return '
			<fieldset>
				<legend><img src="'._PS_ADMIN_IMG_.'information.png" alt="" title="" />'.$this->l('Aide').'</legend>
				<label style="padding-top:0px">'.$this->l('Lien vers l\'aide').' :</label>
				<div class="margin-form">
					<a style="text-decoration:underline;font-size:12px" href="http://assurance.prestashop.com/fr/documentation">'.$this->l('Documentation').'</a>
				</div>
			</fieldset>
			<div class="clear"><br/></div>
			<fieldset>
				<legend><img src="'._PS_ADMIN_IMG_.'information.png" alt="" title="" />'.$this->l('Notice d\'information').'</legend>
				<label style="padding-top:0px">'.$this->l('Lien vers la notice d\'information').' :</label>
				<div class="margin-form">
					<a style="text-decoration:underline;font-size:12px" href="'.$this->_psaUrl.'/pdf/notice-information-assurance.pdf">'.$this->l('Notice d\'information').'</a>'.$this->helpPsa('notice_info').'
				</div>
			</fieldset>
			<div class="clear"><br/></div>
			<fieldset>
				<legend><img src="'._PS_ADMIN_IMG_.'information.png" alt="" title="" />'.$this->l('CGU').'</legend>
				<label style="padding-top:0px">'.$this->l('Lien vers les CGU de PrestaShop Assurance').' :</label>
				<div class="margin-form">
					<a style="text-decoration:underline;font-size:12px" href="https://assurance.prestashop.com/pdf/cgu-prestashop-assuance.pdf">'.$this->l('CGU').'</a>
				</div>
			</fieldset>';
	}

	public function _displayExportConfigurationTab()
	{
		return '<div class="export_config_psa" style="padding-top:20px">
					<label>'.$this->l('Export Configuration').' :</label>
					<div class="margin-form">
						<button onclick="exportConfig();" class="button" style="height:40px;padding:0 20px 0 20px">'.$this->l('Export').'</button>
						<p>'.$this->l('Click here to export your module config').'</p>
						<div id="ajax-loader-export" style="display:none"><img src="../modules/'.$this->name.'/img/ajax-loader.gif"></div>
					</div>
					<div class="error" id="error_return" style="display:none"></div>
					<div class="conf clear" id="conf_return" style="display:none">
						<p>'.$this->l('Your unique identifier is : ').'"<span id="unique_id"></span>". '.$this->l('Keep it safe').'</p>
					</div>
					<div class="hint clear" style="display:block">
						<p>'.$this->l('Votre configuration sera automatiquement transmise à la plateforme PrestaShop.').'</p>
						<p>'.$this->l('Une fois que l\'exportation terminée, un identifiant unique vous sera transmis. Il vous permettra de ré-importer votre configuration à l\'avenir.').'</p>
					</div>
				</div>';
	}
	
	public function displayPriceWidget($category_id)
	{
		$id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
		if (isset($this->categoriesMatch[(int)$category_id]))
		{
			$conf = $this->categoriesMatch[(int)$category_id];
			if (!$conf['impact_value'] || $conf['impact_value'] <= 0)
				return '<p>---</p>';
			else
			{
				$conf['selling_price'] = $this->calcFinalPrice($conf['minimum_price'], $conf['impact_value'], $conf['maximum_price']);
				return  Tools::displayPrice($conf['selling_price'], (int)$id_currency);
			}
		}
		else
			return '<p>---</p>';
	}
	
	public function displayMenuAction($category_id)
	{
		$cayetory_match = $set_children_price = false;
		if (isset($this->categoriesMatch[(int)$category_id]))
		{
			$cayetory_match = true;
			$conf = $this->categoriesMatch[(int)$category_id];
			if ($conf['impact_value'] > 0)
				$set_children_price = true;
		}	
		
		return '<a href="javascript:void(0)" class="menu_action" id="action_'.(int)$category_id.'">'.$this->l('Action').'</a>
				<div id="menu_action_'.(int)$category_id.'" style="width:270px;">  
				    <div iconCls="icon-edit-cat" onclick="openWindowCategoryMatch('.(int)$category_id.')">Choisir la catégorie d\'assurance</div>
				    <div iconCls="icon-sub" '.(!$cayetory_match ? 'style="color:gray" onclick="return false;"' : 'onclick="applyMatchingChildren('.(int)$category_id.')"').'>Appliquer cette catégorie sur les enfants</div> 
				    <div iconCls="icon-delete" '.(!$cayetory_match ? 'style="color:gray" onclick="return false;"' : 'onclick="deleteMatching('.(int)$category_id.')"').'>Supprimer la catégorie d\'assurance</div>
				    <div class="menu-sep"></div> 
				    <div iconCls="icon-edit-price" '.(!$cayetory_match ? 'style="color:gray" onclick="return false;"' : 'onclick="openWindowPrice('.(int)$category_id.')"').' >Choisir le prix de vente</div>
				    <div iconCls="icon-sub" '.(!$set_children_price ? 'style="color:gray" onclick="return false;"' : 'onclick="applyPriceChildren('.(int)$category_id.')"').'>Appliquer ce prix sur les enfants</div>  
				</div>';
	}
	
	public function displayPsaCategoryWidget($category_id)
	{
		if (isset($this->categoriesMatch[(int)$category_id]))
			return $this->categoriesMatch[(int)$category_id]['name'];
		else
			return '<p>---</p>';
	}

	public function displayPriceForm($id_category, $conf)
	{
		$id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
		$currency = new Currency((int)$id_currency);

		return '<div id="fma_impact" style="margin:10px">
					<label style="width:120px">'.$this->l('Minimum price').' : </label>
					<div class="margin-form" style="padding-left: 130px;">
						<span id="minimum_price_'.(int)$id_category.'">'.Tools::displayPrice((float)$conf['minimum_price'], (int)$id_currency).'</span>
						<input type="hidden" name="minimum_price_'.(int)$id_category.'" value="'.(float)$conf['minimum_price'].'">
						<span style="font-weight: bold;"> - '.$this->l('tax excl').'</span>
					</div>
					<label style="width:120px">'.$this->l('Maximum price').' : </label>
					<div class="margin-form" style="padding-left: 130px;">
						<span id="maximum_price_'.(int)$id_category.'">'.Tools::displayPrice((float)$conf['maximum_price'], (int)$id_currency).'</span>
						<input type="hidden" name="maximum_price_'.(int)$id_category.'" value="'.(float)$conf['maximum_price'].'">
						<span style="font-weight: bold;"> - '.$this->l('tax excl').'</span>
					</div>
					<label style="width:120px">'.$this->l('Tax rate').' : </label>
					<div class="margin-form" style="padding-left: 130px;">
						<span>'.self::PSA_TAX_RATE.' %</span>
					</div>
					<hr/>
					<label style="width:120px;display:none">'.$this->l('Impact type').' :</label>
					<div class="margin-form" style="padding-left: 130px;display:none">
						<select class="impact_type" id="impact_type_'.(int)$id_category.'">
							<option value="fixed_price" '.($conf['impact_type'] == 'fixed_price' ? 'selected="selected"' : '').'>'.$this->l('Fixed Price').'</option>
							<option value="percentage" '.($conf['impact_type'] == 'percentage' ? 'selected="selected"' : '').'>'.$this->l('Percentage').'</option>
						</select>
					</div>
					<img style="display:none;margin-bottom:10px" id="ajax-loader" src="../img/loadingAnimation.gif">
					<label style="width:120px">'.$this->l('Selling price').' :</label>
					<div class="margin-form" style="padding-left: 130px;">
						<input id="impact_value_'.(int)$id_category.'" type="text" size="6" value="'.$conf['impact_value'].'"> '.$currency->sign.'
						<span style="font-weight: bold;"> - '.$this->l('tax excl').'</span>
					</div>
					<label style="width:120px"></label>
					<div class="margin-form" style="padding-left: 130px;font-size:1em">
						<input type="hidden" name="selling_price_'.(int)$id_category.'" size="6" value="'.$conf['selling_price'].'">
						<span class="selling_price_'.(int)$id_category.'">'.$conf['selling_price'].' '.$currency->sign.'</span>
						<span style="font-weight: bold;"> - '.$this->l('tax incl').'</span>
					</div>
					<hr/>
					<label style="width:120px">'.$this->l('Votre commission').' :</label>
					<div class="margin-form" style="padding-left: 130px;">
						<span class="impact_fixed_input benefit_'.(int)$id_category.'" style="'.($conf['impact_type'] != 'fixed_price' ? 'display:none' : '').'">
							'.Tools::displayPrice((float)$conf['benefit'], (int)$id_currency).'</span>
						<span class="impact_percentage_input" style="'.($conf['impact_type'] != 'percentage' ? 'display:none' : '').'">'.$this->l('Depends on the price of the product').'</span>
						<span style="font-weight: bold;"> - '.$this->l('tax excl').'</span>
						<input type="hidden" name="benefit_'.(int)$id_category.'" value="'.(float)$conf['benefit'].'">
					</div>
					<div class="clear"><br/></div>
					<div class="center">
						<button class="my_button" id="save_impact" style="padding:10px;">
							<img style="margin-right:5px" src="../modules/'.$this->name.'/img/save.png" />
							'.$this->l('Sauvegarder').'
						</button>
					</div>
				</div>
				<div class="hint clear" style="display:block;width:300px;margin:10px 0 0 20px">
					<p>'.$this->l('Votre commission s\'élève à 25% du montant HT de la cotisation d’assurance.').'</p>
					<p>'.$this->l('Les taxes d\'assurance réglementaires sont fixées à 9%, elles sont entièrement reversées à l\'assureur et ne sont donc pas récupérables').'</p>
				</div>';
	}

	public function getFmaSubCategory($id_category)
	{
		global $cookie;
		$culture = new Language((int)$cookie->id_lang);
		$currency = new Currency((int)$cookie->id_currency);
		$request = new psaRequest($culture->iso_code.'/category/getSubCategory/'.$currency->iso_code.'/'.(int)$id_category.'.json', 'GET');
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		return $request->getResponseBody();
	}
	
	public function getHomeCategories()
	{
		global $cookie;
		$home_category = Category::getHomeCategories((int)($cookie->id_lang));
		
		$tmp = array();
		foreach($home_category as $cat)
			$tmp[] = $cat['id_category'];

		$this->getCategoriesMatch($tmp);
		
		foreach($home_category as $key => $val)
		{
			 $has_children = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT COUNT(*)
				FROM `'._DB_PREFIX_.'category` c
				WHERE c.`id_parent` = '.(int)$val['id_category']);
			$home_category[$key]['category'] = $val['id_category'];
			$home_category[$key]['psa_cat'] = $this->displayPsaCategoryWidget((int)$val['id_category']);
			$home_category[$key]['price'] = $this->displayPriceWidget((int)$val['id_category']);
			$home_category[$key]['action'] = $this->displayMenuAction((int)$val['id_category']);		
			if ($has_children)
				$home_category[$key]['state'] = 'closed';
		}
		$return_cat = array(
			array(
				'id' => 0,
				'iconCls' => 'icon-home',
				'category' => 0,
				'name' => '',
				'children' => $home_category));
		return $return_cat;
	}
	
	public function getChildrenCategories($id_category_parent)
	{
		global $cookie;
		
		$children_categories = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.`id_category`, cl.`name`, IF((
			SELECT COUNT(*)
			FROM `'._DB_PREFIX_.'category` c2
			WHERE c2.`id_parent` = c.`id_category`
		) > 0, 1, 0) AS has_children
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
		WHERE `id_lang` = '.(int)$cookie->id_lang.'
		AND c.`id_parent` = '.(int)$id_category_parent.'
		ORDER BY `position` ASC');
		
		$tmp = array();
		foreach($children_categories as $cat)
			$tmp[] = $cat['id_category'];

		$this->getCategoriesMatch($tmp);
		
		foreach($children_categories as $key => $cat)
		{
			$children_categories[$key]['category'] = $cat['id_category'];
			$children_categories[$key]['psa_cat'] = $this->displayPsaCategoryWidget((int)$cat['id_category']);
			$children_categories[$key]['price'] = $this->displayPriceWidget((int)$cat['id_category']);
			$children_categories[$key]['action'] = $this->displayMenuAction((int)$cat['id_category']);
			if ($cat['has_children'])
				$children_categories[$key]['state'] = 'closed';
		}
		
		return $children_categories;
	}

	public function getRootCategories()
	{
		global $cookie;
		$culture = new Language((int)$cookie->id_lang);
		$currency = new Currency((int)$cookie->id_currency);
		$insurer_id = Configuration::get('PSA_INSURER_ID');
		$request = new psaRequest($culture->iso_code.'/category/getRootCategories/'.$currency->iso_code.'/'.(int)$insurer_id.'.json');
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		return $request->getResponseBody();
	}

	public function getCategoriesMatch($category_ids)
	{
		if (!is_array($category_ids))
			$category_ids = array($category_ids);
		$results = Db::getInstance()->ExecuteS('
											SELECT c.*, a.`name` FROM `'._DB_PREFIX_.'psa_category_match` c
											LEFT JOIN `'._DB_PREFIX_.'psa_category_attribute` ca ON (ca.`id_category` = c.`id_category`)
											LEFT JOIN `'._DB_PREFIX_.'attribute_lang` a ON (a.`id_attribute` = ca.`id_attribute`)
											WHERE c.`id_category` IN ('.implode(',', $category_ids) .')');
		if (is_array($results))
		{
			foreach($results as &$cat)
			{
				if (is_null($cat['selling_price']))
				{
					//if selling price is null calc default price and TODO Create an attribut.
					if (!Configuration::get('PSA_DEFAULT_IMPACT_VALUE'))
						$cat['selling_price'] = $cat['minimum_price'];
					else
						$cat['selling_price'] = $this->calcFinalPrice($cat['minimum_price'], Configuration::get('PSA_DEFAULT_IMPACT_VALUE'), $cat['maximum_price']);

					$cat['benefit'] = $this->calcBenefit($cat['selling_price']);
				}
				$this->categoriesMatch[(int)$cat['id_category']] = $cat;
			}
		}
	}
	
	public function getProductPriceAverage($id_category)
	{
		return Db::getInstance()->getValue('
			SELECT AVG(`price`) FROM  `'._DB_PREFIX_.'ps_product` 
			WHERE  `id_category_default` = '.(int)$d_category);
	}

	private function _formatPsCartProducts($cartProducts, $psa_product = false)
	{
		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
		$return = array();
		foreach($cartProducts as $product)
		{
			//if id_product == psa_id_product => skip it
			if ($product['id_product'] == $psa_product_id and !$psa_product)
				continue;
			$tmp['id_product'] = $product['id_product'];
			$tmp['id_product_attribute'] = $product['id_product_attribute'];
			$tmp['id_category_default'] = $product['id_category_default'];
			$tmp['qty'] = $product['cart_quantity'];
			$tmp['price_wt'] = $product['price_wt'];
			$tmp['price'] = $product['price'];
			$return[$product['id_product'].'_'.$product['id_product_attribute']] = $tmp;
		}
		return $return;
	}

	private function _getQtyByCategory(&$cartProducts)
	{
		$qtyByCategory = array();
		foreach($cartProducts as $product)
		{
			if (!isset($qtyByCategory[$product['id_category_default']]))
				$qtyByCategory[$product['id_category_default']] = $product['qty'];
			else
				$qtyByCategory[$product['id_category_default']] += $product['qty'];
		}
		return $qtyByCategory;
	}

	private function _getPsaCartProducts($id_cart)
	{
		$results = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'psa_cart` WHERE `id_cart` = '.(int)$id_cart);
		$this->_psa_cart_products = array();
		foreach($results as $product)
			$this->_psa_cart_products[$product['id_product'].'_'.(int)$product['id_product_attribute']] = $product;
	}

	private function _updatePsaCartProducts($cartProducts, $cartObj)
	{
		global $cookie;

		$currency = new Currency((int)$cookie->id_currency);
		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
		$return = true;
		
		foreach($this->_psa_cart_products as $key => $product)
		{
			if (array_key_exists($product['id_product'].'_'.$product['id_product_attribute'], $cartProducts))
			{
				$id_category_default = $cartProducts[$product['id_product'].'_'.$product['id_product_attribute']]['id_category_default'];
				$product_price = $cartProducts[$product['id_product'].'_'.$product['id_product_attribute']]['price_wt'];

				$this->getCategoriesMatch(array($id_category_default));
				$price = $this->categoriesMatch[$id_category_default]['selling_price'];
				$minimum_product_price = $this->categoriesMatch[$id_category_default]['minimum_product_price'];
				$maximum_product_price = $this->categoriesMatch[$id_category_default]['maximum_product_price'];
				$id_psa_category = $this->categoriesMatch[$id_category_default]['id_psa_category'];
				
				$insert = true;

				if (!isset($cartProducts[$product['id_product'].'_'.$product['id_product_attribute']]))
					$insert &= false;

				//check if the product price is between min and max price of insurance
				if ($price < $minimum_product_price or $price > $maximum_product_price)
					$insert &= false;
				
				if (Configuration::get('PSA_MINIMUM_THRESHOLD'))
				{
					if (!psaTools::checkMinimumThreshold($product_price, $this->categoriesMatch[$id_category_default]['selling_price'] , Configuration::get('PSA_MINIMUM_THRESHOLD')))
						$insert &= false;
				
				}

				if (!$this->_checkAdd($id_psa_category, $price, $currency->iso_code))
					$insert &= false;
				
				if ($insert)
				{
					$return &= Db::getInstance()->Execute('
												INSERT INTO `'._DB_PREFIX_.'psa_cart` (`id_cart`, `id_product`, `id_product_attribute`, `id_psa_product_attribute`, `qty`, `deleted`)
												VALUES ('.(int)$product['id_cart'].', '.(int)$product['id_product'].', '.(int)$product['id_product_attribute'].', '.(int)$product['id_psa_product_attribute'].',
												'.(int)$product['qty'].', '.(int)$product['deleted'].')
												ON DUPLICATE KEY UPDATE `qty` = '.(int)$product['qty'].' , `deleted` ='.(int)$product['deleted']);
				}
				else
				{
					unset($this->_psa_cart_products[$key]);
					$return &= Db::getInstance()->Execute('
													DELETE FROM `'._DB_PREFIX_.'psa_cart`
													WHERE `id_cart` = '.(int)$product['id_cart'].' 
													AND `id_product` = '.(int)$product['id_product'].' 
													AND `id_product_attribute` = '.(int)$product['id_product_attribute']);
				}
			}
		}
		return $return;
	}

	private function _updatePsCartProducts($ids_psa_attributes, $cartObj)
	{
		global $cookie, $cart;
		$currency = new Currency((int)$cookie->id_currency);
		
		if (!sizeof($this->_psa_cart_products))
		{
			$this->_cleanPsCartProducts((int)$cookie->id_cart, true);
			return;
		}
		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
		$cartProducts = $this->_formatPsCartProducts($cartObj->getProducts(), true);
		$qtyByCategory = array();
		
		foreach($cartProducts as $product)
		{
			if ($product['id_product'] == $psa_product_id || !array_key_exists((int)$product['id_product'].'_'.(int)$product['id_product_attribute'], $this->_psa_cart_products))
				continue;

			if ($this->_psa_cart_products[(int)$product['id_product'].'_'.(int)$product['id_product_attribute']]['deleted'])
				continue;
			
			if (!isset($qtyByCategory[$product['id_category_default']]))
				$qtyByCategory[$product['id_category_default']] = $product['qty'];
			else
				$qtyByCategory[$product['id_category_default']] += $product['qty'];
		}
		$qtyByProductAttribute = array();
		foreach($qtyByCategory as $key => $val)
			$qtyByProductAttribute[$ids_psa_attributes[$key]] = $val;

		foreach($qtyByProductAttribute as $id_psa_attribute => $qty)
		{
			$id_category_default = Db::getInstance()->getValue('
												SELECT id_category FROM `'._DB_PREFIX_.'psa_category_attribute`
												WHERE `id_product_attribute` = '.(int)$id_psa_attribute);
			if (!$id_category_default) //check if matching has been set between shop category and Inssurance category
				continue;
			$this->getCategoriesMatch(array($id_category_default));
			$price = $this->categoriesMatch[$id_category_default]['selling_price'];
			$id_psa_category = $this->categoriesMatch[$id_category_default]['id_psa_category'];

			if (array_key_exists($psa_product_id.'_'.$id_psa_attribute, $cartProducts))
			{
				//check if the product price is between min and max price of insurance
				if ($cartProducts[$psa_product_id.'_'.$id_psa_attribute]['price'] < $this->categoriesMatch[$id_category_default]['minimum_product_price'] 
				or $cartProducts[$psa_product_id.'_'.$id_psa_attribute]['price'] > $this->categoriesMatch[$id_category_default]['maximum_product_price'])
					continue;
				
				$product = $cartProducts[$psa_product_id.'_'.$id_psa_attribute];
				if ($product['qty'] != $qty)
				{
					if ($product['qty'] < $qty)
					{
						if ($this->_checkAdd($id_psa_category, $price, $currency->iso_code))
						{
							$qtyToAdd = $qty - $product['qty'];
							$this->_updatePsaQty = true;
							$cartObj->updateQty((int)$qtyToAdd, (int)$psa_product_id, (int)$id_psa_attribute, false, 'up');
						}
					}
					else if ($product['qty'] > $qty)
					{
						$qtyToDelete = $product['qty'] - $qty;
						$this->_updatePsaQty = true;
						$cartObj->updateQty((int)$qtyToDelete, (int)$psa_product_id, (int)$id_psa_attribute, false, 'down');
					}
				}
			}
			elseif ($this->_checkAdd($id_psa_category, $price, $currency->iso_code))
			{
				$this->_updatePsaQty = true;
				$cartObj->updateQty((int)$qty, (int)$psa_product_id, (int)$id_psa_attribute, false, 'up');
			}
		}
		foreach($cartProducts as $product)
		{
			if ($product['id_product'] != $psa_product_id)
				continue;
			if (!isset($qtyByProductAttribute[$product['id_product_attribute']]))
			{
				$this->_updatePsaQty = true;
				$cartObj->updateQty((int)$product['qty'], (int)$psa_product_id, (int)$product['id_product_attribute'], false, 'down');
			}
		}
	}

	private function _checkAdd($id_psa_category, $price, $currency)
	{
		if (isset($_cacheCheckAdd[$id_psa_category.'_'.$price.'_'.$currency]))
			return $this->_cacheCheckAdd[$id_psa_category.'_'.$price.'_'.$currency];
		$request = new psaRequest('check_add/'.$id_psa_category, 'POST', array('currency' => $currency, 'selling_price' => $price, 'id' => $id_psa_category));
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		$response = (array)Tools::jsonDecode($request->getResponseBody());
		if (!sizeof($response))
			return false;
		$this->_cacheCheckAdd[$id_psa_category.'_'.$price.'_'.$currency] = $response['return'];
		return $response['return'];
	}

	private function _cleanPsaCartProducts($id_cart)
	{
		$this->_psa_cart_products = array();
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'psa_cart` WHERE `id_cart` = '.(int)$id_cart.' AND `order_valid` != 1 ');
	}

	private function _cleanPsCartProducts($id_cart, $onlyPsaProducts = false)
	{
		$this->_psa_cart_products = array();
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.(int)$id_cart.($onlyPsaProducts ? ' AND `id_product` = '.(int)Configuration::get('PSA_ID_PRODUCT') : ''));
	}

	private function _reorderProductInCart($cartProducts, $ids_psa_attributes, $id_cart)
	{
		$i = 0;
		$psa_product_id = (int)Configuration::get('PSA_ID_PRODUCT');
		$category_ids = array();
		foreach($cartProducts as $product)
			$category_ids[$product['id_category_default']][] = array(
				'id_product' => $product['id_product'],
				'id_product_attribute' => $product['id_product_attribute'],
				'id_category_default' => $product['id_category_default']
			);
		foreach($category_ids as $cat)
		{
			foreach($cat as $product)
			{
				Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.'cart_product` SET `date_add`= ADDTIME( NOW(), \'00:00:'.(int)$i.'\')
											WHERE `id_cart` = '.(int)$id_cart.' AND `id_product` = '.(int)$product['id_product'].'
											AND `id_product_attribute` = '.(int)$product['id_product_attribute']
				);
				$i++;
			}
			if (array_key_exists((int)$product['id_category_default'], $ids_psa_attributes))
				Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.'cart_product` SET `date_add`= ADDTIME( NOW(), \'00:00:'.(int)$i.'\')
											WHERE `id_cart` = '.(int)$id_cart.' AND `id_product` = '.(int)$psa_product_id.'
											AND `id_product_attribute` = '.(int)$ids_psa_attributes[(int)$product['id_category_default']]
			);
			$i++;
		}
	}

	public function saveSignInInfos($id_merchant, $key)
	{
		if (Configuration::updateValue('PSA_ID_MERCHANT', $id_merchant) and Configuration::updateValue('PSA_KEY',$key ))
			return true;
		else
			return false;
	}

	public function saveMatchCategory($ps_cat, $psa_cat, $minimum_price, $name, $maximum_price, $minimum_product_price, $maximum_product_price)
	{
		$this->deleteMatching($ps_cat);
		$this->createAttributAndCombinaison($ps_cat, $name);

		$return = Db::getInstance()->Execute('
									INSERT INTO `'._DB_PREFIX_.'psa_category_match` (`id_category`, `id_psa_category`, `minimum_price`, `maximum_price`, `minimum_product_price`, `maximum_product_price`)
									VALUES ('.(int)$ps_cat.', '.(int)$psa_cat.', '.(float)$minimum_price.',  '.(float)$maximum_price.', '.(float)$minimum_product_price.',  '.(float)$maximum_product_price.')
									ON DUPLICATE KEY UPDATE `id_psa_category` = '.(int)$psa_cat.'').',
									`minimum_price` = '.(float)$minimum_price.',
									`maximum_price`= '.(float)$maximum_price.',
									`minimum_product_price`= '.(float)$minimum_product_price.',
									`maximum_product_price`= '.(float)$maximum_product_price;
		if ($return)
			return array(
				'hasError' => false,
				'html' => $this->renderTreeRow($ps_cat)
				);
		else
			return array('hasError' => true);
	}

	public function deleteMatching($id_category)
	{
		$return = true;
		$return &= $this->cleanAttributAndCombinaison($id_category);
		$return &= Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'psa_category_attribute` WHERE `id_category` = '.(int)$id_category);
		$return &= Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'psa_category_match` WHERE `id_category` = '.(int)$id_category);
				
		if ($return)
			return array(
				'hasError' => false,
				'html' => $this->renderTreeRow($id_category)
				);
		else
			return array('hasError' => true);
	}
	
	public function renderTreeRow($ps_cat)
	{
		$this->getCategoriesMatch($ps_cat);
			
		$html = array(
			'psa_cat' => $this->displayPsaCategoryWidget((int)$ps_cat),
			'price' => $this->displayPriceWidget((int)$ps_cat),
			'action' => $this->displayMenuAction((int)$ps_cat)
			);
		return $html;
	}

	public function createAttributAndCombinaison($id_category, $name)
	{
		$return = true;
		$languages = Language::getLanguages();

		//delete old attribut and combinaison befor create a new one
		$this->cleanAttributAndCombinaison($id_category);

		$comb = new Combination();
		$comb->id_product = (int)Configuration::get('PSA_ID_PRODUCT');
		$comb->quantity = 100;
		$return &= $comb->add();

		$attr = new Attribute();
		$attr->id_attribute_group = (int)Configuration::get('PSA_ATTR_GROUP_ID');
		foreach ($languages as $language)
			$attr->name[$language['id_lang']] = $name;
		$return &= $attr->add();

		$return &= Db::getInstance()->Execute('
									INSERT INTO `'._DB_PREFIX_.'product_attribute_combination` (`id_attribute`, `id_product_attribute`)
									VALUES ('.(int)$attr->id.', '.(int)$comb->id.')');

		//Save matching category and attribute
		$return &= Db::getInstance()->Execute('
									INSERT INTO `'._DB_PREFIX_.'psa_category_attribute` (`id_category`, `id_attribute`, `id_product_attribute`)
									VALUES ('.(int)$id_category.', '.(int)$attr->id.', '.(int)$comb->id.')
									ON DUPLICATE KEY UPDATE `id_attribute` = '.(int)$attr->id.', `id_product_attribute` = '.(int)$comb->id);
		return $return;
	}

	private function cleanAttributAndCombinaison($id_category)
	{
		$return = true;
		$id_attribute = Db::getInstance()->getValue('SELECT `id_attribute` FROM `'._DB_PREFIX_.'psa_category_attribute` WHERE `id_category` = '.(int)$id_category);
		$id_attr_comb = Db::getInstance()->getValue('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `id_attribute` = '.(int)$id_attribute);
		$return &= Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product_attribute` = '.(int)$id_attr_comb);
		$return &= Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `id_attribute` = '.(int)$id_attribute);
		$return &= Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'attribute` WHERE `id_attribute` = '.(int)$id_attribute);
		$return &= Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'attribute_lang` WHERE `id_attribute` = '.(int)$id_attribute);
		return $return;
	}

	public function saveImpact($ps_cat, $impact_type, $impact_value, $selling_price, $benefit)
	{
		$return = true;
		$return &= Db::getInstance()->Execute('
									UPDATE `'._DB_PREFIX_.'psa_category_match`
									SET `impact_type`= \''.pSQL($impact_type).'\',
										`impact_value`= '.(float)$impact_value.',
										`selling_price`= '.(float)$selling_price.',
										`benefit`= '.(float)$benefit.'
									WHERE `id_category` = '.(int)$ps_cat);

		$id_product_attribute = Db::getInstance()->getValue('
									SELECT pac.`id_product_attribute`
									FROM `'._DB_PREFIX_.'product_attribute_combination` pac
									LEFT JOIN `'._DB_PREFIX_.'psa_category_attribute` ca ON (ca.`id_attribute` = pac.`id_attribute`)
									WHERE ca.`id_category` = '.(int)$ps_cat);

		//update combinaison price
		$return &= Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.'product_attribute`
											SET `price` = '.(float)Tools::ps_round($selling_price, 2).'
											WHERE `id_product_attribute` = '.(int)$id_product_attribute);
		if ($return)
			return array(
				'hasError' => false,
				'html' => $this->renderTreeRow($ps_cat)
				);
		else
			return array('hasError' => true);
	}

	public function calcFinalPrice($minimum_price, $impact_value, $maximum_price)
	{
		$return = array();
		$tax = self::PSA_TAX_RATE;

		$selling_price = Tools::ps_round($impact_value * (($tax/100) +1), 2);
		//check if final price isn't lower than minimum
		if ($selling_price < ($minimum_price * ($tax/100) + $minimum_price))
			$selling_price = ($minimum_price * ($tax/100) + $minimum_price);

		if ($selling_price > $maximum_price)
			$selling_price = ($maximum_price * ($tax/100) + $maximum_price);

		return $selling_price;
	}

	public function calcBenefit($selling_price)
	{
		$tax = self::PSA_TAX_RATE;
		return Tools::ps_round($selling_price, 2) / ($tax/100 + 1) / 4;
	}

	private function _cartHasInssurance()
	{
		$has_inssurance = true;
		if (!sizeof($this->_psa_cart_products))
			$has_inssurance &= false;

		foreach ($this->_psa_cart_products as $psa_product)
			if (!$psa_product['deleted'])
				return true;
			else
				$has_inssurance &= false;

			return $has_inssurance;
	}

	private function _getPsaCombinaisons($id_lang)
	{
		$product = new Product((int)Configuration::get('PSA_ID_PRODUCT'));
		$combinaisons = $product->getAttributeCombinaisons($id_lang);
		$combinaisonsTmp = array();
		foreach($combinaisons as $comb)
			$combinaisonsTmp[$comb['id_product_attribute']] = $comb;
		$combinaisons = $combinaisonsTmp;
		return $combinaisons;
	}

	private function _makeSubsriptionDatas($orderObj, $cartObj, $combinaisons)
	{
		$id_customer = $orderObj->id_customer;
		$id_address_delivery = $orderObj->id_address_delivery;
		$id_address_invoice = $orderObj->id_address_invoice;
		$id_cart = $cartObj->id;
		$id_psa_product = Configuration::get('PSA_ID_PRODUCT');

		$data['id_marchand'] = Configuration::get('PSA_ID_MERCHANT');
		$customerObj = new Customer((int)$id_customer);
		$deliveryObj = new Address((int)$id_address_delivery);
		$invoiceObj = new Address((int)$id_address_invoice);
		$customer = array(
			'lastname' => $customerObj->lastname,
			'firstname' => $customerObj->firstname,
			'email' => $customerObj->email,
			'addresses' => array(
				'delivery' => array('address1' => $deliveryObj->address1,
					'address2' => $deliveryObj->address2,
					'postcode' => $deliveryObj->postcode,
					'city' => $deliveryObj->city,
					'country' => $deliveryObj->country
				),
				'invoice'=> array('address1' => $invoiceObj->address1,
					'address2' => $invoiceObj->address2,
					'postcode' => $invoiceObj->postcode,
					'city' => $invoiceObj->city,
					'country' => $deliveryObj->country
				)
			));
		$data['subscriber'] = $customer;
		$products = array();
		$psCartProducts = $orderObj->getProducts();

		foreach($psCartProducts as &$product)
			if ($product['product_id'] != $id_psa_product)
			{
				$product['id_category_default'] = Db::getInstance()->getValue('SELECT id_category_default FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)$product['product_id']);
				$productsCategories[] = $product['id_category_default'];
			}
		$result = Db::getInstance()->ExecuteS('
											SELECT * FROM `'._DB_PREFIX_.'psa_category_attribute`
											WHERE `id_category` IN ('.implode(', ', $productsCategories).')');
		$this->getCategoriesMatch($productsCategories);
		$ids_psa_categories = array();
		foreach($this->categoriesMatch as $cat)
			$ids_psa_categories[$cat['id_category']] = $cat['id_psa_category'];
		//format id attributes tab
		$ids_psa_attributes = array();
		foreach($result as $key => $val)
			if (!isset($ids_psa_attributes[$val['id_category']]))
				$ids_psa_attributes[$val['id_category']] = $val['id_product_attribute'];

			$total_inssurance = 0;

		foreach($psCartProducts as $psPproduct)
		{
			if ($psPproduct['product_id'] == $id_psa_product || !array_key_exists($psPproduct['product_id'].'_'.$psPproduct['product_attribute_id'], $this->_psa_cart_products))
				continue;
						
			if ($this->_psa_cart_products[$psPproduct['product_id'].'_'.$psPproduct['product_attribute_id']]['deleted'])
				continue;
			$id_psa_product_attribute = $this->_psa_cart_products[$psPproduct['product_id'].'_'.$psPproduct['product_attribute_id']]['id_psa_product_attribute'];
			$price_inssurance = $combinaisons[$id_psa_product_attribute]['price'];

			$tmp = array(
				'id_ps_product' => $psPproduct['product_id'],
				'id_psa_category' => $ids_psa_categories[$psPproduct['id_category_default']],
				'product_reference' => (string)$psPproduct['product_reference'],
				'product_name' => $psPproduct['product_name'],
				'tax_rate' => $psPproduct['tax_rate'],
				'price_tax_excluded' => $psPproduct['product_price_wt'],
				'price_inssurance' => $price_inssurance
			);

			for($i=0;$i<$psPproduct['product_quantity'];$i++)
			{
				$products[] = $tmp;
				$total_inssurance += $price_inssurance;
			}
		}

		$data['products'] = $products;
		$currency = new Currency((int)$orderObj->id_currency);
		$order = array(
			'id_order' => $orderObj->id,
			'purchase_date' => $orderObj->date_add,
			'total_paid' => $orderObj->total_paid,
			'total_inssurance' => $total_inssurance,
			'currency' => $currency->iso_code
		);

		$data['order'] = $order;

		return $data;
	}

	private function _sendSubsriptionDatas($data)
	{
		$request = new psaRequest('subscription', 'POST', $data);
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		$response = (array)Tools::jsonDecode($request->getResponseBody());
		$response['id_order'] = (int)$data['order']['id_order'];
		$response['total_inssurance'] = $data['order']['total_inssurance'];
		$response['total_benefit'] = $this->calcBenefit($data['order']['total_inssurance']);
		return $response;
	}

	private function _saveSubscriptionDetails($response, $orderObj)
	{
		$result = Db::getInstance()->ExecuteS('SELECT *
				FROM '._DB_PREFIX_.'psa_insurance_detail
				WHERE id_order = '.(int)$response['id_order']);

		foreach($response['message'] as $message)
		{
			$message = array('message' => $message, 'id_order' => (int)$response['id_order'], 'date_add' => date("Y-m-d h:m:s"));
			Db::getInstance()->autoExecute(_DB_PREFIX_.'psa_insurance_detail_message', $message, 'INSERT');
		}
		unset($response['message']);

		if (sizeof($result))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'psa_insurance_detail', $response, 'UPDATE', 'id_order='.(int)$response['id_order']);
		else
		{
			$result['date_add'] = $orderObj->date_add;
			Db::getInstance()->autoExecute(_DB_PREFIX_.'psa_insurance_detail', $response, 'INSERT');
		}
	}

	private function _cleanValidateCartForReOrder($cartObj)
	{
		$id_psa_product = (int)Configuration::get('PSA_ID_PRODUCT');
		//delete all psa_product in cart for later reOrder
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.(int)$cartObj->id.' AND `id_product` = '.(int)$id_psa_product);
	}


	private function _alterOrderDetails($orderObj, $cartObj, $combinaisons)
	{
		$id_psa_product = (int)Configuration::get('PSA_ID_PRODUCT');

		$products = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$orderObj->id.' AND `product_id` != '.(int)$id_psa_product);
		//delete all product in order detail
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$orderObj->id);
		//get all psa product not deleted
		$this->_getPsaCartProducts((int)$cartObj->id);
		//add psa products and ps products in order detail
		foreach($products as $product)
		{
			unset($product['id_order_detail']);
			$product['product_name'] = pSQL($product['product_name']);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'order_detail', $product, 'INSERT');
			if (isset($this->_psa_cart_products[$product['product_id'].'_'.$product['product_attribute_id']]))
			{
				$psa_product = $this->_psa_cart_products[$product['product_id'].'_'.$product['product_attribute_id']];
				if (!$psa_product['deleted'])
				{
					Db::getInstance()->execute(
						'INSERT INTO `'._DB_PREFIX_.'order_detail` (`id_order`, `product_id`, `product_attribute_id`, `product_name`, `product_quantity`, `product_price`)
					VALUES ('.(int)$orderObj->id.', '.(int)$id_psa_product.', '.(int)$psa_product['id_psa_product_attribute'].',
					\''.pSQL($this->l('Inssurance').' - '.$combinaisons[$psa_product['id_psa_product_attribute']]['attribute_name']).'\',
					'.$psa_product['qty'].','.(float)$combinaisons[$psa_product['id_psa_product_attribute']]['price'].')');
				}
			}
		}
	}

	public function reSubmitSouscription($id_order, $id_cart)
	{
		global $cookie;
		$order = new Order((int)$id_order);
		$cart = new Cart((int)$id_cart);
		$params = array('order' => $order, 'cart' => $cart, 'cart' => $cookie);
		$this->hooknewOrder($params);
	}

	public function updateLimitedInsurerCountry($insurer_id, $limited_country)
	{
		Configuration::updateValue('PSA_INSURER_ID', (int)$insurer_id);
		Configuration::updateValue('PSA_LIMITED_COUNTRY', $limited_country);
	}

	public function saveDisaster($id_order, $id_product, $reason, $comment, $phone)
	{
		$orderObj = new Order((int)$id_order);
		$carrier = new Carrier((int)($orderObj->id_carrier));
		
		$id_agreement = Db::getInstance()->getValue('SELECT `id_agreement` 
			FROM `'._DB_PREFIX_.'psa_insurance_detail` 
			WHERE `id_order` ='.(int)$id_order);
		
		$data = array(
			'id_agreement' => $id_agreement,
			'id_product' => $id_product,
			'reason' => $reason,
			'comment' => $comment,
			'phone' => $phone,
			'carrier_name' => $carrier->name,
			'followup_link' => str_replace('@', $orderObj->shipping_number, $carrier->url)
		);

		$request = new psaRequest('disaster/add', 'POST', $data);
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		$response = (array)Tools::jsonDecode($request->getResponseBody());
		if (!$response['hasErrors'])
			if (Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'psa_disaster` (`id_order`, `id_psa_disaster`, `id_product`, `status`, `reason`, `date_add`)
											VALUES ('.(int)$id_order.', '.(int)$response['id_psa_disaster'].', '.(int)$id_product.', \'wait\', \''. pSQL($reason).'\', NOW())'))
				return $this->addDisasterComment((int)Db::getInstance()->Insert_ID(), 1, $comment);
			else
				return false;
	}

	public function changeDisasterStatus($id_disaster, $new_status)
	{		
		return Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'psa_disaster SET `status` = \''.pSQL($new_status).'\' WHERE `id_disaster` = '.(int)$id_disaster);
	}

	public function addDisasterComment($id_disaster, $way = 1, $comment)
	{
		if ($comment != '')
			return Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'psa_disaster_comments (`id_disaster`, `way`, `comment`, `date_add`)
													VALUES ('.(int)$id_disaster.', '.(int)$way.', \''.pSQL($comment).'\', NOW())');
		else
			return true;
	}

	public function sendDisasterComment($id_disaster, $way = 1, $comment, $id_psa_disaster)
	{
		$data = array(
			'comment' => $comment,
			'id_psa_disaster' => $id_psa_disaster
		);

		$request = new psaRequest('disaster/add/comment', 'POST', $data);
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		$response = (array)Tools::jsonDecode($request->getResponseBody());
		if (!$response['hasErrors'])
			return $this->addDisasterComment((int)$id_disaster, (int)$way, $comment);
		else
			return true;
	}

	public function getSignInUrlReturn()
	{
		return 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8')._MODULE_DIR_.$this->name.'/ajax.php?token='.sha1(_COOKIE_KEY_.'prestassurance');
	}

	public function exportConfig()
	{
		$tables = array(
			'psa_category_match',
			'psa_category_attribute'
		);

		$confs = array(
			'PSA_ID_MERCHANT',
			'PSA_KEY',
			'PSA_CGV_UPDATED',
			'PSA_ADDED_PRICE',
			'PSA_ADDED_BG',
			'PSA_ADDED_TXT',
			'PSA_NOT_ADDED_PRICE',
			'PSA_NOT_ADDED_BG',
			'PSA_NOT_ADDED_TXT',
			'PSA_PROPOSE_DISCONECT'
		);

		$datas = array();

		foreach($tables as $table)
		{
			$secure_table = (function_exists('bqSQL') ? bqSQL($table) : pSQL(($table)));
			$datas['tables'][$table] = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.$secure_table);
		}

		foreach($confs as $conf)
			$datas['conf'][$conf] = Configuration::get($conf);

		//export psa product attrubits and combinations
		$result = Db::getInstance()->executeS('
			SELECT * FROM '._DB_PREFIX_.'attribute
			WHERE `id_attribute_group` = '.(int)Configuration::get('PSA_ATTR_GROUP_ID')
		);
		$attributes = array();
		$combination = array();
		foreach($result as $attribute)
		{
			$tmp_attr = Db::getInstance()->executeS('
				SELECT * FROM '._DB_PREFIX_.'attribute_lang
				WHERE `id_attribute` = '.(int)$attribute['id_attribute']
			);

			$tmp_comb = Db::getInstance()->executeS('
				SELECT * FROM `'._DB_PREFIX_.'product_attribute` pa
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac
				ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
				WHERE `id_attribute` = '.(int)$attribute['id_attribute']
			);
			$tmp_comb = $tmp_comb[0];

			$attributes[(int)$attribute['id_attribute']] = $tmp_attr;
			$combination[(int)$attribute['id_attribute']] = $tmp_comb;
		}
		$datas['attributes'] = $attributes;
		$datas['combination'] = $combination;

		return $datas;
	}

	public function importConfig($config)
	{
		$config = unserialize($config);

		$return = true;
		$languages = Language::getLanguages(true);
		$id_attr_group = (int)Configuration::get('PSA_ATTR_GROUP_ID');


		foreach($config['attributes'] as $key => $attribut)
		{
			foreach($attribut as $val)
			{
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute` (`id_attribute_group`, `color`) VALUES ('.(int)$id_attr_group.', 0)');
				$new_id_attribut = Db::getInstance()->Insert_ID();
				foreach($languages as $lang)
					Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute_lang` (`id_attribute` ,`id_lang` ,`name`) VALUES ('.(int)$new_id_attribut.', '.(int)$lang.', \''.pSQL($val['name']).'\')');

				//modify id_attribute in other table
				foreach($config['tables']['psa_category_attribute'] as &$category_match)
					if ($category_match['id_attribute'] == $val['id_attribute'])
						$category_match['id_attribute'] = $new_id_attribut;

					foreach($config['combination'] as &$comb)
						if ($comb['id_attribute'] == $val['id_attribute'])
							$comb['id_attribute'] = $new_id_attribut;
			}
		}

		$config['product_attribute_combination'] = array();
		foreach($config['combination'] as $key => $val)
		{
			$tmp_id_attribute = $val['id_attribute'];
			unset($val['id_attribute'], $val['id_product_attribute']);

			$val['id_product'] = (int)Configuration::get('PSA_ID_PRODUCT');

			Db::getInstance()->autoExecute(_DB_PREFIX_.'product_attribute', $val, 'INSERT');

			$new_id_product_attribute = Db::getInstance()->Insert_ID();
			$config['product_attribute_combination'][$tmp_id_attribute] = $new_id_product_attribute;
		}

		foreach($config['product_attribute_combination'] as $id_attribute => $id_product_attribute)
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'product_attribute_combination` (`id_attribute`, `id_product_attribute`) VALUES ('.(int)$id_attribute.', '.(int)$id_product_attribute.')');

		foreach($config['tables'] as $table_name => $table)
		{
			$secure_table_name = (function_exists('bqSQL') ? bqSQL($table_name) : pSQL(($table_name)));
			$return =& Db::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.$secure_table_name);
			foreach($table as $values)
			{
				$secure_table = (function_exists('bqSQL') ? bqSQL($table_name) : pSQL(($table_name)));
				$return =& Db::getInstance()->autoExecute(_DB_PREFIX_.$secure_table, $values, 'INSERT');
			}
		}

		foreach($config['conf'] as $key => $val)
			$return =& Configuration::updateValue($key, $val);


		return $return;
	}

	public function timeIsUpToDeclare($order_date)
	{
		$s = strtotime(date('Y-m-d h:m:s')) - strtotime($order_date);
		$d = intval($s/86400)+1;
		if ($d > Configuration::get('PSA_INSSURANCE_NBR_DAY'))
			return false;
		else
			return true;
	}
	
	/************* STATS **************/
	
	
	public function hookAdminStatsModules($params)
	{
		return '';
		$nbr_total_orders = psaStats::getTotalOrder();
		$nbr_orders_insurance = psaStats::getTotalOrderWithInsurance();
		$html = '';
		$html = '
		<fieldset style="width:650px">
			<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Orders with insurance').'</legend>
			<div style="margin-top:20px"></div>
			<p>'.$this->l('Total orders').' : '.$nbr_total_orders.'</p>
			<p>'.$this->l('Total orders with insurance').' : '.$nbr_orders_insurance.'</p>
			<p>'.$this->l('% orders with insurance').' : '.round(($nbr_orders_insurance * 100)/$nbr_total_orders, 2).' %</p>
			'.($nbr_total_orders ? ModuleGraph::engine(array('layers' => 2, 'type' => 'line', 'option' => 1, 'width' => 650)) : '').'
		</fieldset><br>';
		
		$total_disaster = psaStats::getTotalDisaster();
		$html .= '
		<fieldset style="width:650px">
			<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Disaster').'</legend>
			<div style="margin-top:20px"></div>
			<p>'.$this->l('Total disaster').' : '.$total_disaster.'</p>
			'.($total_disaster ? ModuleGraph::engine(array('layers' => 1, 'type' => 'line', 'option' => 2, 'width' => 650)) : '').'
		</fieldset><br>';

		$html .= '
		<fieldset style="width:650px">
			<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('TOP 10 product with insurance').'</legend>
			<div style="margin-top:20px"></div>
			'.($nbr_orders_insurance ? ModuleGraph::engine(array('layers' => 1, 'type' => 'pie', 'option' => 3, 'width' => 650)) : 'Aucune commande').'
		</fieldset><br>';
		
		$html .= '
		<fieldset style="width:650px">
			<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('TOP 10 category with insurance').'</legend>
			<div style="margin-top:20px"></div>
			'.($nbr_orders_insurance ? ModuleGraph::engine(array('layers' => 1, 'type' => 'pie', 'option' => 4, 'width' => 650)) : 'Aucune commande').'
		</fieldset>';

		return $html;
	}
	
	public function setOption($options, $layers = 1)
	{
		global $cookie;
		$this->_option = $options;
		switch ($this->_option)
		{
			case 1:
				$this->_titles['main'][0] = $this->l('Orders with insurance');
				$this->_titles['main'][1] = $this->l('Orders');
				$this->_titles['main'][2] = $this->l('Orders with insurance');
				$this->_query[0] = '
					SELECT date_add, COUNT(`date_add`) as total
					FROM `'._DB_PREFIX_.'orders`
					WHERE `date_add` BETWEEN ';
				$this->_query[1] = '
					SELECT date_add, COUNT(DISTINCT(`date_add`)) as total 
					FROM `ps_orders` o
					LEFT JOIN `ps_order_detail` op on (o.id_order = op.id_order)
					WHERE op.`product_id` = '.(int)Configuration::get('PSA_ID_PRODUCT').'
					AND `date_add` BETWEEN ';
				break;
			case 2:
				$this->_titles['main'] = $this->l('Number of disaster');
				$this->_query[0] = '
					SELECT date_add, COUNT(`date_add`) as total
					FROM `'._DB_PREFIX_.'psa_disaster`
					WHERE `date_add` BETWEEN ';
				break;
			case 3:
				$this->_titles['main'] = $this->l('TOP 10 product with insurance');
				$this->_query[0] = '
					SELECT pl.name, COUNT(pca.`id_product`) as total FROM `'._DB_PREFIX_.'orders` o
					LEFT JOIN `'._DB_PREFIX_.'psa_cart` pca ON (pca.`id_cart` = o.`id_cart`) 
					LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = pca.`id_product`)
					WHERE o.`date_add` BETWEEN ';
					
				$this->_query2[0] = '
					AND pl.`id_lang` = '.(int)$cookie->id_lang.'
					GROUP BY pca.`id_product`
					ORDER BY total DESC
					LIMIT 10';
				break;
			case 4:
				$this->_titles['main'] = $this->l('TOP 10 product category with insurance');
				$this->_query[0] = '
					SELECT cl.name, COUNT(pca.`id_product`) as total FROM `'._DB_PREFIX_.'orders` o
					LEFT JOIN `'._DB_PREFIX_.'psa_cart` pca ON (pca.`id_cart` = o.`id_cart`) 
					LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = pca.`id_product`)
					LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = p.`id_category_default`)
					WHERE o.`date_add` BETWEEN ';
					
				$this->_query2[0] = '
					AND cl.`id_lang` = '.(int)$cookie->id_lang.'
					GROUP BY cl.`id_category`
					ORDER BY total DESC
					LIMIT 10';
				break;
		}
	}
		
	protected function getData($layers)
	{
		$this->setDateGraph($layers, true);
	}
	
	protected function setAllTimeValues($layers)
	{
		if ($this->_option == 3 || $this->_option == 4)
			$this->_values = $this->_legend = array();
		
		for ($i = 0; $i < $layers; $i++)
		{
			if ($this->_option == 3 || $this->_option == 4)
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().$this->_query2[$i]);
			else
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 4)');
			
			foreach ($result AS $row)
				if ($this->_option == 2)
					$this->_values[(int)(substr($row['date_add'], 0, 4))] = (int)$row['total'];
				elseif ($this->_option == 3 || $this->_option == 4)
				{
					$this->_values[] = $row['total'];
					$this->_legend[] = substr($row['name'], 0, 25).'...';
				}
				else
					$this->_values[$i][(int)(substr($row['date_add'], 0, 4))] = (int)$row['total'];
			
		}
	}
	
	protected function setYearValues($layers)
	{
		if ($this->_option == 3 || $this->_option == 4)
			$this->_values = $this->_legend = array();
		
		for ($i = 0; $i < $layers; $i++)
		{
			if ($this->_option == 3 || $this->_option == 4)
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().$this->_query2[$i]);
			else
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 7)');
				
			foreach ($result AS $row)
				if ($this->_option == 2)
					$this->_values[(int)(substr($row['date_add'], 5, 2))] = (int)$row['total'];
				elseif ($this->_option == 3 || $this->_option == 4)
				{
					$this->_values[] = $row['total'];
					$this->_legend[] = substr($row['name'], 0, 25).'...';
				}
				else
					$this->_values[$i][(int)(substr($row['date_add'], 5, 2))] = (int)$row['total'];
		}
	}
	
	protected function setMonthValues($layers)
	{
		if ($this->_option == 3 || $this->_option == 4)
			$this->_values = $this->_legend = array();
		
		
		for ($i = 0; $i < $layers; $i++)
		{
			if ($this->_option == 3 || $this->_option == 4)
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().$this->_query2[$i]);
			else
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 10)');
			
			foreach ($result AS $row)
				if ($this->_option == 2)
					$this->_values[(int)(substr($row['date_add'], 8, 2))] = (int)$row['total'];
				elseif ($this->_option == 3 || $this->_option == 4)
				{
					$this->_values[] = $row['total'];
					$this->_legend[] = substr($row['name'], 0, 25).'...';
				}
				else
					$this->_values[$i][(int)(substr($row['date_add'], 8, 2))] = (int)$row['total'];
		}
	}

	protected function setDayValues($layers)
	{
		if ($this->_option == 3 || $this->_option == 4)
			$this->_values = $this->_legend = array();
		
		for ($i = 0; $i < $layers; $i++)
		{
			if ($this->_option == 3)
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().$this->_query2[$i]);
			else
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($this->_query[$i].$this->getDate().' GROUP BY LEFT(date_add, 13)');
			
			foreach ($result AS $row)
				if ($this->_option == 2)
					$this->_values[(int)(substr($row['date_add'], 0, 4))] = (int)$row['total'];
				elseif ($this->_option == 3 || $this->_option == 4)
				{
					$this->_values[] = $row['total'];
					$this->_legend[] = substr($row['name'], 0, 25).'...';
				}
				else
					$this->_values[$i][(int)(substr($row['date_add'], 0, 4))] = (int)$row['total'];
		}
	}
	
	/************* END STATS **************/
	
	public function helpPsa($key)
	{
		$help = array(
			'email_subscribe' => $this->l('Saisir le mail que vous avez utilisé lors de votre inscription'),
			'merchant_id' => $this->l('L\'id marchant vous à été envoyé par mail lors de votre inscription'),
			'rib' => $this->l('Vous encaissez directement le montant TTC des cotisations auprès de vos clients. Vous devez nous fournir vos coordonnées bancaires car PrestaShop prélève ensuite chaque semaine sur votre compte le montant des taxes et des commissions. Vous conservez ainsi 25% du montant HT des cotisations encaissées.'),
			'deposit' => $this->l('La caution constitue un fond de roulement payable en une fois et qui sert à prémunir PrestaShop contre tout problème à l’occasion du prélèvement hebdomadaire des taxes et des commissions sur votre compte. Ce dépôt est restituable, déduction faite des impayés éventuels, au moment de la résiliation du service PrestaShop Assurance.'),
			'environnement' => $this->l('Mode Tester : ').'<br/>'.
								$this->l('L\'assurance ne sera pas visible par vos clients. Tous vos tests de souscriptions d\'assurance ne seront pas comptabilisées.').'<br/><br/>'.
								$this->l('Mode Activer : ').'<br/>'.
								$this->l('L\'assurance est visible de tout le monde. Avant d\'activer ce mode, assurez-vous que le module soit complètement configuré.'),
			'order_status' => $this->l('Les garanties de PrestaShop Assurance ne sont enclenchées qu\'à partir du moment où le client a payé sa commande et que la marchandise est en cours d\'acheminement.
Vous devez donc indiquer à ce niveau l\'étape du processus de vente, telle que vous l\'avez définie, qui correspond au stade « en cours de livraison ».'),
			'notice_info' => $this->l('La notice d\'information doit apparaitre sur votre site au niveau des conditions générales'),
			'read_more_link' => $this->l('￼Il s’agit de la page qui s\'affiche (pop-in) lorsque le client clique sur le lien « plus d’info » qui se situe à côté de chaque ligne « assurance » dans le panier.
Vous pouvez créer cette page CMS à vocation marketing en allant dans le menu « Outils » puis en choisissant « CMS ».'),
			'curl' => 'Curl is a tool to transfer data from or to a server. If you don\'t know how does it work please contact your system administrator',
			'local_use' => 'Afin de pouvoir fonctionner normalement le module PrestaShop Assurance doit obligatoirement être installé sur un serveur web et non en local.');
		
		if (array_key_exists($key, $help))
			return '<span class="help_psa"><img alt="'.$help[$key].'" src="../modules/'.$this->name.'/img/help.png"></span>';
		else
			return '<span class="help_psa"><img alt="'.$this->l('No help').'" src="../modules/'.$this->name.'/img/help.png"></span>';
	}
	
	public function displayExcludedProductList()
	{
		return '<fieldset>
				<h3 style="text-align: left;">Exclusions de garantie de la garantie Achat :</h3>
 				<ul style="text-align: left;">
					<li>les véhicules terrestres, maritimes, fluviaux ou aériens à moteur ainsi que leurs accessoires intérieurs ou extérieurs.</li>
					<li>les oeuvres d\'art, les bijoux.</li>
					<li>les animaux vivants.</li>
					<li>les dommages esthétiques, les bris de verre, la panne.</li>
					<li>les frais de douane.</li>
					<li>l’absence de livraison résultant d’une faillite ou de la liquidation judiciaire d’un partenaire de PRESTASHOP.</li>
					<li>les retards de livraison.</li>
					<li>l\'absence de livraison résultant d’un dysfonctionnement du transporteur du bien acheté.</li>
					<li>l\'usure normale, le vice propre ou la disparition inexpliquée du bien.</li>
					<li>le vol par effraction d\'une habitation lorsque les moyens de protection n\'ont pas été actionnés.</li>
					<li>le vol commis dans un véhicule automobile.</li>
					<li>les dommages immatériels, directs ou indirects.</li>
				</ul>
				<h3 style="text-align: left;">Exclusions de garantie Continuité de service :</h3>
				<ul style="text-align: left;">
					<li>les conséquences d\'une situation, maladie, accident dont l\'assuré aurait eu connaissance antérieurement à la date de souscription</li>
					<li>les conséquences d\'une maladie, accident générant un arrêt de travail inférieur à 15 jours</li>
					<li>les conséquences de la mutilation volontaire, du suicide ou de la tentative de suicide ainsi que de toute lésion causée ou provoquée intentionnellement par l\'assuré</li>
					<li>les conséquences d\'un accident lors de la participation à des sports aériens ou motorisés</li>
					<li>l\'accident lors de la pratique d’un sport à titre professionnel, la participation à des rixes ou bagarres sauf cas de légitime défense</li>
					<li>la mutation, la démission et le licenciement autre qu\'économique</li>
					<li>les conséquences des affections de type purement psychiatrique ou de dépressions nerveuses</li>
					<li>l\'indisponibilité ou l’interruption du service internet</li>
					<li>les frais de mise en service</li>
				</ul>
				<h3 style="text-align: left;">Exclusions communes à l’ensemble des garanties :</h3>
				<ul style="text-align: left;text-align:justify">
					<li>les espèces, les devises, les chèques de voyage, les titres de transport et de tout titre négociable</li>
					<li>les dommages esthétiques, le bris des verres, les pannes, les conséquences indirectes et les pertes d’exploitation</li>
					<li>les défectuosités ou les déceptions dans le rendement ou les performances du bien, les achats contraires à l’ordre public français</li>
					<li><p style="width: 700px;text-align:justify">le sinistre dont l’origine provient (i) d\'une usure normale, d\'un vice propre ou d’une disparition inexpliquée du bien du non-respect des conditions d\'utilisation du bien préconisées par son fabricant ou son distributeur, (ii) d\'une faute intentionnelle ou dolosive de la part de l’assuré ou de la part d’un de ses proches (conjoint, concubin, ascendant ou descendant), (iii) d\'une guerre civile ou étrangère, (iv) d\'un embargo, d\'une confiscation, d\'une capture ou d\'une destruction par ordre d\'un gouvernement ou d\'une autorité publique, (v) d\'une désintégration du noyau atomique ou d\'un rayonnement ionisant, (v) de la perte ou de la disparition inexpliquée du bien, (vi) du vol du bien déposé dans un véhicule, (vii) du vol sans effraction ou sans agression.</p>
					</li>
				</ul>
					<p style="text-align:center"><button onclick="$.fancybox.close();return false;" class="button" style="padding:10px;font-size:1.3em">'.$this->l('Fermer').'</button></p>
				</fieldset>';
	}
	
	public function getStep3Details($step_2)
	{
		$step3_detail = array(
			'product_purchased_broken' => array('1_1' => $this->l('à la livraison'), '1_2' => $this->l('accidentelle')),
			'product_purchased_stolen' => array('2_1' => $this->l('au domicile'), '2_2' => $this->l('avec agression'), '2_3' => $this->l('dans un véhicule')),
			'product_purchased_not_delivered' => array('3_1' => $this->l('retard de livraison'), '3_2' => $this->l('absence de livraison')),
			'breakdown' => false,
			'internal_damage' => false,
			'manufacturing_default' => false,
			'internet' => array('7_1' => $this->l('Décés'), '7_2' => $this->l('Maladie ou accident -  cas standard'), '7_3' => $this->l('Maladie ou accident -  Etudiants en CDI ou CDD'))
			);
		
		if (array_key_exists($step_2, $step3_detail))
			return $step3_detail[$step_2];
		else
			return false;
	}
	
	public function getDocumentsForDisaster($disaster_key)
	{
		$return_doc = array();
		$document_list = array(
			1 => $this->l('Justificatif de réception du colis'),
			2 => $this->l('Numéro de suivi du colis'),
			3 => $this->l('Mandat client pour recours contre le transporteur'), 
			4 => $this->l('Facture d\'achat'), 
			5 => $this->l('Dépôt de plainte'), 
			6 => $this->l('Certificat médical ou témoignage écrit'),
			7 => $this->l('Devis / facture de réparation / attestation de bien irréparable'),
			8 => $this->l('Photographie du bien'),
			9 => $this->l('Photocopie du contrat d\'abonnement Internet'),
			10 => $this->l('Photocopie du dernier relevé de compte sur lequel apparait le dernier prélèvement Internet'),
			11 => $this->l('Certificat de décès original et d\'hérédité Coordonnées du notaire'),
			12 => $this->l('Certificat médical descriptif accompagné de l\'ensemble des arrêts de travail'),
			13 => $this->l('Nom et adresse du médecin traitant'),
			14 => $this->l('Photocopies des bulletins de salaires'),
			15 => $this->l('Déclarations de revenus de l\'année précédente et avis d\'imposition'),
			16 => $this->l('Copie du contrat de travail de CDD ou CDI'),
			17 => $this->l('Attestation de l\'établissement justifiant l\'absence (son motif et sa durée)'),
			);
		
		$disaster_documents = array(
			'1_1' => array(1, 3, 4), '1_2' => array(4, 7, 8), '2_1' => array(),
			'2_2' => array(4, 5, 6), '2_3' => array(), '3_1' => array(),
			'3_2' => array(2, 3, 4), '4_1' => array(), '5_1' => array(),
			'6_1' => array(), '7_1' => array(4, 9, 10, 11), '7_2' => array(4, 9, 10, 12, 13, 14, 15),
			'7_3' => array(4, 9, 10, 12, 13, 14, 16, 17),
			);
		
		if (array_key_exists($disaster_key, $disaster_documents))
		{
			$document_ids = $disaster_documents[$disaster_key];
			foreach($document_ids as $id)
				$return_doc[] = $document_list[$id];
		
			if (count($return_doc))
				return $return_doc;
			else
				return false;
		}
		else
			return false;		
	}
	
	public function reactive($id_marchant, $email)
	{
		$data = array(
			'id_marchant' => $id_marchant,
			'email' => $email,
			'signin_url_return' => $this->getSignInUrlReturn()
		);
		$request = new psaRequest('module/reactive', 'POST', $data);
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		$response = (array)Tools::jsonDecode($request->getResponseBody());
		if (isset($response['hasErrors']) &&  !$response['hasErrors'])
		{
			Configuration::updateValue('PSA_ID_MERCHANT', $response['merchant_id']);
			Configuration::updateValue('PSA_KEY', $response['secure_key']);
			$this->updateLimitedInsurerCountry($response['insurer_id'], $response['insurer_limited_country']);
			return true;
		}
		else
			return false;
	}
	
}