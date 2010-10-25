<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockCart extends Module
{
    private $_html = '';
    private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'blockcart';
		$this->tab = 'front_office_features';
		$this->version = '1.2';

		parent::__construct();

		$this->displayName = $this->l('Cart block');
		$this->description = $this->l('Adds a block containing the customer\'s shopping cart');
	}
	
	public function smartyAssigns(&$smarty, &$params)
	{
		global $errors, $cookie;

		// Set currency
		if (!intval($params['cart']->id_currency))
			$currency = new Currency(intval($params['cookie']->id_currency));
		else
			$currency = new Currency(intval($params['cart']->id_currency));
		if (!Validate::isLoadedObject($currency))
			$currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));

		if ($params['cart']->id_customer)
		{
			$customer = new Customer(intval($params['cart']->id_customer));
			$taxCalculationMethod = Group::getPriceDisplayMethod(intval($customer->id_default_group));
		}
		else
			$taxCalculationMethod = Group::getDefaultPriceDisplayMethod();
		$usetax = $taxCalculationMethod == PS_TAX_EXC ? false : true;

		$products = $params['cart']->getProducts(true);
		$nbTotalProducts = 0;
		foreach ($products AS $product)
			$nbTotalProducts += intval($product['cart_quantity']);

		$wrappingCost = floatval($params['cart']->getOrderTotal($usetax, 6));
		
		$smarty->assign(array(
			'products' => $products,
			'customizedDatas' => Product::getAllCustomizedDatas(intval($params['cart']->id)),
			'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
			'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
			'discounts' => $params['cart']->getDiscounts(false, $usetax),
			'nb_total_products' => intval($nbTotalProducts),
			'shipping_cost' => Tools::displayPrice($params['cart']->getOrderTotal($usetax, 5), $currency),
			'show_wrapping' => $wrappingCost > 0 ? true : false,
			'wrapping_cost' => Tools::displayPrice($wrappingCost, $currency),
			'product_total' => Tools::displayPrice($params['cart']->getOrderTotal($usetax, 4), $currency),
			'total' => Tools::displayPrice($params['cart']->getOrderTotal($usetax), $currency),
			'id_carrier' => intval($params['cart']->id_carrier),
			'order_process' => Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order',
			'ajax_allowed' => intval(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false
		));
		if (sizeof($errors))
			$smarty->assign('errors', $errors);
		if(isset($cookie->ajax_blockcart_display))
			$smarty->assign('colapseExpandStatus', $cookie->ajax_blockcart_display);
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockCart'))
		{
			$ajax = Tools::getValue('ajax');
			if ($ajax != 0 AND $ajax != 1)
				$output .= '<div class="alert error">'.$this->l('Ajax : Invalid choice.').'</div>';
			else
			{
				Configuration::updateValue('PS_BLOCK_CART_AJAX', intval($ajax));
			}
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				
				<label>'.$this->l('Ajax cart').'</label>
				<div class="margin-form">
					<input type="radio" name="ajax" id="ajax_on" value="1" '.(Tools::getValue('ajax', Configuration::get('PS_BLOCK_CART_AJAX')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="ajax_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="ajax" id="ajax_off" value="0" '.(!Tools::getValue('ajax', Configuration::get('PS_BLOCK_CART_AJAX')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="ajax_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Activate AJAX mode for cart (compatible with the default theme)').'</p>
				</div>
				
				<center><input type="submit" name="submitBlockCart" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}

	public function install()
	{
		if
		(
			parent::install() == false
			OR $this->registerHook('rightColumn') == false
			OR $this->registerHook('header') == false
			OR Configuration::updateValue('PS_BLOCK_CART_AJAX', 1) == false
		)
			return false;
		return true;
	}

	public function hookRightColumn($params)
	{
		global $smarty, $page_name;

		$smarty->assign('order_page', $page_name == 'order');
		$this->smartyAssigns($smarty, $params);

		return $this->display(__FILE__, 'blockcart.tpl');
	}

	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	public function hookAjaxCall($params)
	{
		global $smarty;
		$this->smartyAssigns($smarty, $params);
		return $this->display(__FILE__, 'blockcart-json.tpl');
	}
	
	public function hookHeader()
	{
		global $page_name;
		
		if (intval(Configuration::get('PS_BLOCK_CART_AJAX')))
		{
			Tools::addJS(array(_PS_JS_DIR_.'jquery/iutil.prestashop-modifications.js', _PS_JS_DIR_.'jquery/ifxtransfer.js'));
			Tools::addCSS(_THEME_CSS_DIR_.'modules/'.$this->name.'/blockcart.css', 'all');
			if ($page_name != 'order')
				Tools::addJS(__PS_BASE_URI__.'modules/blockcart/ajax-cart.js');
		}
	}
}


?>
