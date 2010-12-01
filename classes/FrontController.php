<?php

class FrontControllerCore
{
	public $errors = array();
	public $smarty;
	public $cookie;
	public $link;
	public $cart;
	public $iso;
	
	public $orderBy;
	public $orderWay;
	public $p;
	public $n;
	
	public $auth = false;
	public $authRedirection = false;
	public $ssl = false;
	
	public static $initialized = false;
	
	public function __construct()
	{
		global $smarty, $cookie, $link, $cart, $useSSL, $iso;

		$useSSL = $this->ssl;
		$this->init();
		
		$this->smarty = &$smarty;
		$this->cookie = &$cookie;
		$this->link = &$link;
		$this->cart = &$cart;
		$this->iso = &$iso;

		if ($this->auth AND !$this->cookie->isLogged())
			Tools::redirect('authentication.php'.($this->authRedirection ? '?back='.$this->authRedirection : ''));
	}
	
	public function run()
	{
		$this->preProcess();
		$this->setMedia();
		$this->displayHeader();
		$this->process();
		$this->displayContent();
		$this->displayFooter();
	}
	
	public function init()
	{
		if (self::$initialized)
			return;
		self::$initialized = true;
			
		global $_CONF, $cookie, $smarty, $cart, $iso, $defaultCountry, $page_name;
		if (!isset($smarty))
			exit;

		// Init Cookie
		$cookie = new Cookie('ps');

		/* Theme is missing or maintenance */
		if (!is_dir(dirname(__FILE__).'/../themes/'._THEME_NAME_))
			die(Tools::displayError('Current theme unavailable. Please check your theme directory name and permissions.'));
		elseif (basename($_SERVER['PHP_SELF']) != 'disabled.php' AND !(int)(Configuration::get('PS_SHOP_ENABLE')))
			$maintenance = true;
		elseif (intval(Configuration::get('PS_GEOLOCALIZATION_ENABLED')) AND $_SERVER['SERVER_NAME'] != 'localhost' AND $_SERVER['SERVER_NAME'] != '127.0.0.1')
		{
			/* Check if Maxmind Database exists */
			if (file_exists(_PS_GEOIP_DIR_.'GeoLiteCity.dat'))
			{
				if (!isset($cookie->iso_code_country) OR (isset($cookie->iso_code_country) AND !in_array(strtoupper($cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES')))))
				{
					include_once(_PS_GEOIP_DIR_.'geoipcity.inc');
					include_once(_PS_GEOIP_DIR_.'geoipregionvars.php');
					
					$gi = geoip_open(realpath(_PS_GEOIP_DIR_.'GeoLiteCity.dat'), GEOIP_STANDARD);
					$record = geoip_record_by_addr($gi, Tools::getRemoteAddr());
					
					if (!in_array(strtoupper($record->country_code), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))))
					{
						if (Configuration::get('PS_GEOLOCALIZATION_BEHAVIOR') == _PS_GEOLOCALIZATION_NO_CATALOG_)
							$restricted_country = true;
						elseif (Configuration::get('PS_GEOLOCALIZATION_BEHAVIOR') == _PS_GEOLOCALIZATION_NO_ORDER_)
							$smarty->assign(array(
								'restricted_country_mode' => true,
								'geolocalization_country' => $record->country_name
							));
					}
					else
					{
						$cookie->iso_code_country = strtoupper($record->country_code);
						$hasBeenSet = true;
					}
				}
				
				if (intval($id_country = Country::getByIso(strtoupper($cookie->iso_code_country))))
				{
					/* Update defaultCountry */
					$defaultCountry = new Country($id_country);
					if (isset($hasBeenSet) AND $hasBeenSet)
						$cookie->id_currency = intval(Currency::getCurrencyInstance($defaultCountry->id_currency ? intval($defaultCountry->id_currency) : Configuration::get('PS_CURRENCY_DEFAULT'))->id);
				}
			}
			/* If not exists we disabled the geolocalization feature */
			else
				Configuration::updateValue('PS_GEOLOCALIZATION_ENABLED', 0);
		}

		ob_start();

		/* get page name to display it in body id */
		$pathinfo = pathinfo(__FILE__);
		$page_name = basename($_SERVER['PHP_SELF'], '.'.$pathinfo['extension']);
		$page_name = (preg_match('/^[0-9]/', $page_name)) ? 'page_'.$page_name : $page_name;

		// Init Cookie
		$cookie = new Cookie('ps');
		
		// Init rewrited links
		global $link;
		$link = new Link();
		$smarty->assign('link', $link);

		// Switch language if needed and init cookie language
		if ($iso = Tools::getValue('isolang') AND Validate::isLanguageIsoCode($iso) AND ($id_lang = (int)(Language::getIdByIso($iso))))
			$_GET['id_lang'] = $id_lang;

		Tools::switchLanguage();
		Tools::setCookieLanguage();

		/* attribute id_lang is often needed, so we create a constant for performance reasons */
		define('_USER_ID_LANG_', (int)($cookie->id_lang));

		if (isset($_GET['logout']) OR ($cookie->logged AND Customer::isBanned((int)($cookie->id_customer))))
		{
			$cookie->logout();
			Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
		}
		elseif (isset($_GET['mylogout']))
		{
			$cookie->mylogout();
			Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
		}

		$iso = strtolower(Language::getIsoById($cookie->id_lang ? (int)($cookie->id_lang) : 1));
		@include(_PS_TRANSLATIONS_DIR_.$iso.'/fields.php');
		@include(_PS_TRANSLATIONS_DIR_.$iso.'/errors.php');
		$_MODULES = array();

		global $currency;
		$currency = Tools::setCurrency();

		if ((int)($cookie->id_cart))
		{
			$cart = new Cart((int)($cookie->id_cart));
			if ($cart->OrderExists())
				unset($cookie->id_cart, $cart);
			/* Delete product of cart, if user can't make an order form his country */
			elseif (intval(Configuration::get('PS_GEOLOCALIZATION_ENABLED')) AND !in_array(strtoupper($cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))) AND $cart->nbProducts())
				unset($cookie->id_cart, $cart);
			elseif ($cookie->id_customer != $cart->id_customer OR $cookie->id_lang != $cart->id_lang OR $cookie->id_currency != $cart->id_currency)
			{
				if ($cookie->id_customer)
					$cart->id_customer = (int)($cookie->id_customer);
				$cart->id_lang = (int)($cookie->id_lang);
				$cart->id_currency = (int)($cookie->id_currency);
				$cart->update();
			}
		}

		if (!isset($cart) OR !$cart->id)
		{
			$cart = new Cart();
			$cart->id_lang = (int)($cookie->id_lang);
			$cart->id_currency = (int)($cookie->id_currency);
			$cart->id_guest = (int)($cookie->id_guest);
			if ($cookie->id_customer)
			{
				$cart->id_customer = (int)($cookie->id_customer);
				$cart->id_address_delivery = (int)(Address::getFirstCustomerAddressId($cart->id_customer));
				$cart->id_address_invoice = $cart->id_address_delivery;
			}
			else
			{
				$cart->id_address_delivery = 0;
				$cart->id_address_invoice = 0;
			}
		}
		if (!$cart->nbProducts())
			$cart->id_carrier = NULL;

		$locale = strtolower(Configuration::get('PS_LOCALE_LANGUAGE')).'_'.strtoupper(Configuration::get('PS_LOCALE_COUNTRY').'.UTF-8');
		setlocale(LC_COLLATE, $locale);
		setlocale(LC_CTYPE, $locale);
		setlocale(LC_TIME, $locale);
		setlocale(LC_NUMERIC, 'en_US.UTF-8');

		if (is_object($currency))
			$smarty->ps_currency = $currency;
		$ps_language = new Language((int)($cookie->id_lang));
		if (is_object($ps_language))
			$smarty->ps_language = $ps_language;

		/* Use Smarty 3 API calls */
		if (!_PS_FORCE_SMARTY_2_) /* PHP version > 5.1.2 */
		{
			$smarty->registerPlugin('function', 'dateFormat', array('Tools', 'dateFormat'));
			$smarty->registerPlugin('function', 'productPrice', array('Product', 'productPrice'));
			$smarty->registerPlugin('function', 'convertPrice', array('Product', 'convertPrice'));
			$smarty->registerPlugin('function', 'convertPriceWithoutDisplay', array('Product', 'productPriceWithoutDisplay'));
			$smarty->registerPlugin('function', 'convertPriceWithCurrency', array('Product', 'convertPriceWithCurrency'));
			$smarty->registerPlugin('function', 'displayWtPrice', array('Product', 'displayWtPrice'));
			$smarty->registerPlugin('function', 'displayWtPriceWithCurrency', array('Product', 'displayWtPriceWithCurrency'));
			$smarty->registerPlugin('function', 'displayPrice', array('Tools', 'displayPriceSmarty'));
		}
		/* or keep a backward compatibility if PHP version < 5.1.2 */
		else
		{
			$smarty->register_function('dateFormat', array('Tools', 'dateFormat'));
			$smarty->register_function('productPrice', array('Product', 'productPrice'));
			$smarty->register_function('convertPrice', array('Product', 'convertPrice'));
			$smarty->register_function('convertPriceWithoutDisplay', array('Product', 'productPriceWithoutDisplay'));
			$smarty->register_function('convertPriceWithCurrency', array('Product', 'convertPriceWithCurrency'));
			$smarty->register_function('displayWtPrice', array('Product', 'displayWtPrice'));
			$smarty->register_function('displayWtPriceWithCurrency', array('Product', 'displayWtPriceWithCurrency'));
			$smarty->register_function('displayPrice', array('Tools', 'displayPriceSmarty'));
		}

		$smarty->assign(Tools::getMetaTags($cookie->id_lang));
		$smarty->assign('request_uri', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

		/* Breadcrumb */
		$navigationPipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
		$smarty->assign('navigationPipe', $navigationPipe);

		/* Server Params */
		$server_host_ssl = Tools::getHttpHost(false, true);
		$server_host     = str_replace(':'._PS_SSL_PORT_, '',$server_host_ssl);
		
		global $protocol, $protocol_ssl, $protocol_link, $protocol_content;
		$protocol = 'http://';
		$protocol_ssl = 'https://';
		$protocol_link = (Configuration::get('PS_SSL_ENABLED') OR (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on')) ? $protocol_ssl : $protocol;
		$protocol_content = ((isset($useSSL) AND $useSSL AND Configuration::get('PS_SSL_ENABLED')) OR (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on')) ? $protocol_ssl : $protocol;
		define('_PS_BASE_URL_', $protocol.$server_host);

		Product::initPricesComputation();

		$priceDisplay = Product::getTaxCalculationMethod();

		if (!Configuration::get('PS_THEME_V11'))
		{
			define('_PS_BASE_URL_SSL_', $protocol_ssl.$server_host);
			$smarty->assign(array(
				'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
				'base_dir_ssl' => $protocol_link.$server_host_ssl.__PS_BASE_URI__,
				'content_dir' => $protocol_content.$server_host.__PS_BASE_URI__,
				'tpl_dir' => _PS_THEME_DIR_,
				'modules_dir' => _MODULE_DIR_,
				'mail_dir' => _MAIL_DIR_,
				'lang_iso' => $ps_language->iso_code,
				'come_from' => Tools::getHttpHost(true, true).htmlentities($_SERVER['REQUEST_URI']),
				'shop_name' => Configuration::get('PS_SHOP_NAME'),
				'cart_qties' => (int)($cart->nbProducts()),
				'cart' => $cart,
				'currencies' => Currency::getCurrencies(),
				'id_currency_cookie' => (int)($currency->id),
				'currency' => $currency,
				'cookie' => $cookie,
				'languages' => Language::getLanguages(),
				'logged' => $cookie->isLogged(),
				'page_name' => $page_name,
				'customerName' => ($cookie->logged ? $cookie->customer_firstname.' '.$cookie->customer_lastname : false),
				'priceDisplay' => $priceDisplay,
				'roundMode' => (int)(Configuration::get('PS_PRICE_ROUND_MODE')),
				'use_taxes' => (int)(Configuration::get('PS_TAX')),
				'vat_management' => (int)(Configuration::get('VATNUMBER_MANAGEMENT'))));
			$assignArray = array(
				'img_ps_dir' => _PS_IMG_,
				'img_cat_dir' => _THEME_CAT_DIR_,
				'img_lang_dir' => _THEME_LANG_DIR_,
				'img_prod_dir' => _THEME_PROD_DIR_,
				'img_manu_dir' => _THEME_MANU_DIR_,
				'img_sup_dir' => _THEME_SUP_DIR_,
				'img_ship_dir' => _THEME_SHIP_DIR_,
				'img_store_dir' => _THEME_STORE_DIR_,
				'img_col_dir' => _THEME_COL_DIR_,
				'img_dir' => _THEME_IMG_DIR_,
				'css_dir' => _THEME_CSS_DIR_,
				'js_dir' => _THEME_JS_DIR_,
				'pic_dir' => _THEME_PROD_PIC_DIR_
			);// TODO for better performances (cache usage), remove these assign and use a smarty function to get the right media server in relation to the full ressource name

			foreach ($assignArray as $assignKey => $assignValue)
				if (substr($assignValue, 0, 1) == '/' OR $protocol_content == 'https://')
					$smarty->assign($assignKey, $protocol_content.Tools::getMediaServer($assignValue).$assignValue);
				else
					$smarty->assign($assignKey, $assignValue);
		}
		else
		{
			$protocol = ((isset($useSSL) AND $useSSL AND Configuration::get('PS_SSL_ENABLED')) OR (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on')) ? 'https://' : 'http://';
			$smarty->assign(array(
				'base_dir' => __PS_BASE_URI__,
				'base_dir_ssl' =>  $protocol_link.$server_host_ssl.__PS_BASE_URI__,
				'content_dir' => __PS_BASE_URI__,
				/* If the current page need SSL encryption and the shop allow it, then active it */
				'protocol' => $protocol,
				'img_ps_dir' => _PS_IMG_,
				'img_cat_dir' => _THEME_CAT_DIR_,
				'img_lang_dir' => _THEME_LANG_DIR_,
				'img_prod_dir' => _THEME_PROD_DIR_,
				'img_manu_dir' => _THEME_MANU_DIR_,
				'img_sup_dir' => _THEME_SUP_DIR_,
				'img_ship_dir' => _THEME_SHIP_DIR_,
				'img_store_dir' => _THEME_STORE_DIR_,
				'img_col_dir' => _THEME_COL_DIR_,
				'img_dir' => _THEME_IMG_DIR_,
				'css_dir' => _THEME_CSS_DIR_,
				'js_dir' => _THEME_JS_DIR_,
				'tpl_dir' => _PS_THEME_DIR_,
				'modules_dir' => _MODULE_DIR_,
				'mail_dir' => _MAIL_DIR_,
				'pic_dir' => _THEME_PROD_PIC_DIR_,
				'lang_iso' => $ps_language->iso_code,
				'come_from' => Tools::getHttpHost(true, true).htmlentities($_SERVER['REQUEST_URI']),
				'shop_name' => Configuration::get('PS_SHOP_NAME'),
				'cart_qties' => (int)($cart->nbProducts()),
				'cart' => $cart,
				'currencies' => Currency::getCurrencies(),
				'id_currency_cookie' => (int)($currency->id),
				'currency' => $currency,
				'cookie' => $cookie,
				'languages' => Language::getLanguages(),
				'logged' => $cookie->isLogged(),
				'priceDisplay' => $priceDisplay,
				'page_name' => $page_name,
				'customerName' => ($cookie->logged ? $cookie->customer_firstname.' '.$cookie->customer_lastname : false),
				'roundMode' => (int)(Configuration::get('PS_PRICE_ROUND_MODE')),
				'use_taxes' => (int)(Configuration::get('PS_TAX')),
				'vat_management' => (int)(Configuration::get('VATNUMBER_MANAGEMENT'))));
		}

		/* Display a maintenance page if shop is closed */
		if (isset($maintenance) AND (!in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')))))
		{
			header('HTTP/1.1 503 temporarily overloaded');
			$smarty->display(_PS_THEME_DIR_.'maintenance.tpl');
			exit;
		}
		elseif (isset($restricted_country) AND $restricted_country)
		{
			header('HTTP/1.1 503 temporarily overloaded');
			$smarty->display(_PS_THEME_DIR_.'restricted-country.tpl');
			exit;
		}

		global $css_files, $js_files, $iso;
		$css_files = array();
		$js_files = array();
		
		Tools::addCSS(_THEME_CSS_DIR_.'global.css', 'all');
		Tools::addJS(array(_PS_JS_DIR_.'tools.js', _PS_JS_DIR_.'jquery/jquery-1.4.4.min.js', _PS_JS_DIR_.'jquery/jquery.easing.1.3.js'));
		
		// Load each links once, for better performances...
		$link->preloadPageLinks();
	}

	public function preProcess()
	{
	}
	
	public function setMedia()
	{

	}
	
	public function process()
	{
	}
	
	public function displayContent()
	{
		Tools::safePostVars();
		$this->smarty->assign('errors', $this->errors);
	}
	
	public function displayHeader()
	{
		global $css_files;
		global $js_files;
		
		// P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
		header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

		/* Hooks are volontary out the initialize array (need those variables already assigned) */
		$this->smarty->assign(array(
			'HOOK_HEADER' => Module::hookExec('header'),
			'HOOK_LEFT_COLUMN' => Module::hookExec('leftColumn'),
			'HOOK_TOP' => Module::hookExec('top'),
			'static_token' => Tools::getToken(false),
			'token' => Tools::getToken(),
			'logo_image_width' => Configuration::get('SHOP_LOGO_WIDTH'),
			'logo_image_height' => Configuration::get('SHOP_LOGO_HEIGHT'),
			'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
			'content_only' => (int)(Tools::getValue('content_only'))
		));

		if (is_writable(_PS_THEME_DIR_.'cache'))
		{
			// CSS compressor management
			if (Configuration::get('PS_CSS_THEME_CACHE'))
				Tools::cccCss();

			//JS compressor management
			if (Configuration::get('PS_JS_THEME_CACHE'))
				Tools::cccJs();
		}

		$this->smarty->assign('css_files', $css_files);
		$this->smarty->assign('js_files', $js_files);
		$this->smarty->display(_PS_THEME_DIR_.'header.tpl');
	}
	
	public function displayFooter()
	{
		$this->smarty->assign(array(
			'HOOK_RIGHT_COLUMN' => Module::hookExec('rightColumn'),
			'HOOK_FOOTER' => Module::hookExec('footer'),
			'content_only' => (int)(Tools::getValue('content_only'))));
		$this->smarty->display(_PS_THEME_DIR_.'footer.tpl');
	}
	
	public function productSort()
	{
		$stock_management = (int)(Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false; // no display quantity order if stock management disabled
		$orderByValues = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity');
		$orderWayValues = array(0 => 'asc', 1 => 'desc');
		$this->orderBy = Tools::strtolower(Tools::getValue('orderby', $orderByValues[(int)(Configuration::get('PS_PRODUCTS_ORDER_BY'))]));
		$this->orderWay = Tools::strtolower(Tools::getValue('orderway', $orderWayValues[(int)(Configuration::get('PS_PRODUCTS_ORDER_WAY'))]));
		if (!in_array($this->orderBy, $orderByValues))
			$this->orderBy = $orderByValues[0];
		if (!in_array($this->orderWay, $orderWayValues))
			$this->orderWay = $orderWayValues[0];

		$this->smarty->assign(array(
			'orderby' => $this->orderBy,
			'orderway' => $this->orderWay,
			'orderwayposition' => $orderWayValues[(int)(Configuration::get('PS_PRODUCTS_ORDER_WAY'))],
			'stock_management' => (int)($stock_management)));
	}
	
	public function pagination($nbProducts = 10)
	{
		$nArray = (int)(Configuration::get('PS_PRODUCTS_PER_PAGE')) != 10 ? array((int)(Configuration::get('PS_PRODUCTS_PER_PAGE')), 10, 20, 50) : array(10, 20, 50);
		asort($nArray);
		$this->n = abs((int)(Tools::getValue('n', ((isset($this->cookie->nb_item_per_page) AND $this->cookie->nb_item_per_page >= 10) ? $this->cookie->nb_item_per_page : (int)(Configuration::get('PS_PRODUCTS_PER_PAGE'))))));
		$this->p = abs((int)(Tools::getValue('p', 1)));
		$range = 2; /* how many pages around page selected */

		if ($this->p < 0)
			$this->p = 0;

		if (isset($this->cookie->nb_item_per_page) AND $this->n != $this->cookie->nb_item_per_page AND in_array($this->n, $nArray))
			$this->cookie->nb_item_per_page = $this->n;
			
		if ($this->p > ($nbProducts / $this->n))
			$this->p = ceil($nbProducts / $this->n);
		$pages_nb = ceil($nbProducts / (int)($this->n));

		$start = (int)($this->p - $range);
		if ($start < 1)
			$start = 1;
		$stop = (int)($this->p + $range);
		if ($stop > $pages_nb)
			$stop = (int)($pages_nb);
		$this->smarty->assign('nb_products', $nbProducts);
		$pagination_infos = array(
					'pages_nb' => (int)($pages_nb),
					'p' => (int)($this->p),
					'n' => (int)($this->n),
					'nArray' => $nArray,
					'range' => (int)($range),
					'start' => (int)($start),
					'stop' => (int)($stop));
		$this->smarty->assign($pagination_infos);
	}
}


