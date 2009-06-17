<?php

class BlockCart extends Module
{
    private $_html = '';
    private $_postErrors = array();

	function __construct()
	{
		$this->name = 'blockcart';
		$this->tab = 'Blocks';
		$this->version = '1.2';

		parent::__construct();

		$this->displayName = $this->l('Cart block');
		$this->description = $this->l('Adds a block containing the customer\'s shopping cart');
	}
	
	function smartyAssigns(&$smarty, &$params)
	{
		global $errors;

		// Set currency
		if (!intval($params['cart']->id_currency))
			$currency = new Currency(intval($params['cookie']->id_currency));
		else
			$currency = new Currency(intval($params['cart']->id_currency));
		if (!Validate::isLoadedObject($currency))
			$currency = new Currency(intval(Configuration::get('PS_DEFAULT_CURRENCY')));

		$products = $params['cart']->getProducts(true);
		foreach ($products as $k => $product)
			$products[$k]['real_price'] = Product::getPriceStatic($product['id_product'], intval(Configuration::get('PS_PRICE_DISPLAY')) == 1 ? false : true, $product['id_product_attribute'], 6, NULL, false, true, $product['cart_quantity']) * $product['cart_quantity'];

		$smarty->assign(array(
			'products'=> $products,
			'customizedDatas' => Product::getAllCustomizedDatas(intval($params['cart']->id)),
			'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
			'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
			'discounts' => $params['cart']->getDiscounts(false, true),
			'nb_total_products' =>$params['cart']->nbProducts(),
			'shipping_cost' => Tools::displayPrice($params['cart']->getOrderTotal(intval(Configuration::get('PS_PRICE_DISPLAY')) == 1 ? false : true, 5), $currency),
			'show_wrapping' => floatval($params['cart']->getOrderTotal(true, 6)) > 0 ? true : false,
			'wrapping_cost' => Tools::displayPrice($params['cart']->getOrderTotal(intval(Configuration::get('PS_PRICE_DISPLAY')) == 1 ? false : true, 6), $currency),
			'product_total' => Tools::displayPrice($params['cart']->getOrderTotal(intval(Configuration::get('PS_PRICE_DISPLAY')) == 1 ? false : true, 4), $currency),
			'total' => Tools::displayPrice($params['cart']->getOrderTotal(intval(Configuration::get('PS_PRICE_DISPLAY')) == 1 ? false : true), $currency),
			'id_carrier' => $params['cart']->id_carrier,
			'ajax_allowed' => intval(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false
		));
		if (sizeof($errors))
			$smarty->assign('errors', $errors);
		if(isset($params['cookie']->ajax_blockcart_display))
			$smarty->assign('colapseExpandStatus', $params['cookie']->ajax_blockcart_display);
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

	function install()
	{
			if
			(
				parent::install() == false
				OR $this->registerHook('rightColumn') == false
				OR Configuration::updateValue('PS_BLOCK_CART_AJAX', 1) == false
			)
			return false;
		return true;
	}

	function hookRightColumn($params)
	{
		global $smarty;
		$this->smartyAssigns($smarty, $params);
		return $this->display(__FILE__, 'blockcart.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	function hookAjaxCall($params)
	{
		global $smarty;
		$this->smartyAssigns($smarty, $params);
		return $this->display(__FILE__, 'blockcart-json.tpl');
	}
}


?>
