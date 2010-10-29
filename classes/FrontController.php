<?php

class FrontControllerCore
{
	public $errors = array();
	public $smarty;
	public $cookie;
	public $link;
	public $cart;
	
	public $orderBy;
	public $orderWay;
	public $p;
	public $n;
	
	public function __construct($auth = false, $ssl = false)
	{
		global $smarty, $cookie, $link, $cart, $useSSL;

		$useSSL = $ssl;
		require_once(dirname(__FILE__).'/../init.php');
		
		$this->smarty = &$smarty;
		$this->cookie = &$cookie;
		$this->link = &$link;
		$this->cart = &$cart;

		if ($auth AND !$this->cookie->isLogged())
			Tools::redirect('authentication.php');
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
			'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
			'content_only' => intval(Tools::getValue('content_only'))
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
			'content_only' => intval(Tools::getValue('content_only'))));
		$this->smarty->display(_PS_THEME_DIR_.'footer.tpl');
	}
	
	public function productSort()
	{
		$stock_management = intval(Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false; // no display quantity order if stock management disabled
		$orderByValues = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity');
		$orderWayValues = array(0 => 'ASC', 1 => 'DESC');
		$this->orderBy = Tools::strtolower(Tools::getValue('orderby', $orderByValues[intval(Configuration::get('PS_PRODUCTS_ORDER_BY'))]));
		$this->orderWay = Tools::strtoupper(Tools::getValue('orderway', $orderWayValues[intval(Configuration::get('PS_PRODUCTS_ORDER_WAY'))]));
		if (!in_array($this->orderBy, $orderByValues))
			$this->orderBy = $orderByValues[0];
		if (!in_array($this->orderWay, $orderWayValues))
			$this->orderWay = $orderWayValues[0];

		$this->smarty->assign(array(
			'orderby' => $this->orderBy,
			'orderway' => $this->orderWay,
			'orderwayposition' => $orderWayValues[intval(Configuration::get('PS_PRODUCTS_ORDER_WAY'))],
			'stock_management' => intval($stock_management)));
	}
	
	public function pagination()
	{
		global $nbProducts;
		$nArray = intval(Configuration::get('PS_PRODUCTS_PER_PAGE')) != 10 ? array(intval(Configuration::get('PS_PRODUCTS_PER_PAGE')), 10, 20, 50) : array(10, 20, 50);
		asort($nArray);
		$this->n = abs(intval(Tools::getValue('n', ((isset($this->cookie->nb_item_per_page) AND $this->cookie->nb_item_per_page >= 10) ? $this->cookie->nb_item_per_page : intval(Configuration::get('PS_PRODUCTS_PER_PAGE'))))));
		$this->p = abs(intval(Tools::getValue('p', 1)));
		$range = 2; /* how many pages around page selected */

		if ($this->p < 0)
			$this->p = 0;

		if (isset($this->cookie->nb_item_per_page) AND $this->n != $this->cookie->nb_item_per_page AND in_array($this->n, $nArray))
			$this->cookie->nb_item_per_page = $this->n;
			
		if ($this->p > ($nbProducts / $this->n))
			$this->p = ceil($nbProducts / $this->n);
		$pages_nb = ceil($nbProducts / intval($this->n));

		$start = intval($this->p - $range);
		if ($start < 1)
			$start = 1;
		$stop = intval($this->p + $range);
		if ($stop > $pages_nb)
			$stop = intval($pages_nb);
		$this->smarty->assign('nb_products', $nbProducts);
		$pagination_infos = array(
					'pages_nb' => intval($pages_nb),
					'p' => intval($this->p),
					'n' => intval($this->n),
					'nArray' => $nArray,
					'range' => intval($range),
					'start' => intval($start),
					'stop' => intval($stop));
		$this->smarty->assign($pagination_infos);
	}
}

?>