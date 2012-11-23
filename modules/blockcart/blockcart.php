<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class BlockCart extends Module
{
	public function __construct()
	{
		$this->name = 'blockcart';
		$this->tab = 'front_office_features';
		$this->version = '1.3';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Cart block');
		$this->description = $this->l('Adds a block containing the customer\'s shopping cart.');
	}

	public function smartyAssigns(&$smarty, &$params)
	{
		global $errors, $cookie;

		// Set currency
		if (!(int)($params['cart']->id_currency))
			$currency = new Currency((int)$params['cookie']->id_currency);
		else
			$currency = new Currency((int)$params['cart']->id_currency);
		if (!Validate::isLoadedObject($currency))
			$currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));

		if ($params['cart']->id_customer)
		{
			$customer = new Customer((int)$params['cart']->id_customer);
			$taxCalculationMethod = Group::getPriceDisplayMethod((int)$customer->id_default_group);
		}
		else
			$taxCalculationMethod = Group::getDefaultPriceDisplayMethod();

		$useTax = $taxCalculationMethod != PS_TAX_EXC;

		$products = $params['cart']->getProducts(true);
		$nbTotalProducts = 0;
		foreach ($products AS $product)
			$nbTotalProducts += (int)$product['cart_quantity'];

		$wrappingCost = (float)($params['cart']->getOrderTotal($useTax, Cart::ONLY_WRAPPING));

		if (Configuration::get('PS_TAX_DISPLAY') == 1 && ($useTax || Configuration::get('PS_TAX_DISPLAY_ALL')))
		{
			$totalToPay = $params['cart']->getOrderTotal(true);
			$totalToPayWithoutTaxes = $params['cart']->getOrderTotal(false);
			$smarty->assign('tax_cost', Tools::displayPrice($totalToPay - $totalToPayWithoutTaxes, $currency));
		}
		else
			$totalToPay = $params['cart']->getOrderTotal($useTax);

		$smarty->assign(array(
			'products' => $products,
			'customizedDatas' => Product::getAllCustomizedDatas((int)($params['cart']->id)),
			'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
			'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
			'discounts' => $params['cart']->getDiscounts(false, Tools::isSubmit('id_product')),
			'nb_total_products' => (int)($nbTotalProducts),
			'shipping_cost' => Tools::displayPrice($params['cart']->getOrderTotal($useTax, Cart::ONLY_SHIPPING), $currency),
			'show_wrapping' => $wrappingCost > 0 ? true : false,
			'show_tax' => (int)((int)Configuration::get('PS_TAX_DISPLAY') && (int)Configuration::get('PS_TAX')),
			'wrapping_cost' => Tools::displayPrice($wrappingCost, $currency),
			'product_total' => Tools::displayPrice($params['cart']->getOrderTotal($useTax, Cart::BOTH_WITHOUT_SHIPPING), $currency),
			'total' => Tools::displayPrice($totalToPay, $currency),
			'id_carrier' => (int)($params['cart']->id_carrier),
			'order_process' => Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order',
			'ajax_allowed' => (int)(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false
		));
		if (sizeof($errors))
			$smarty->assign('errors', $errors);
		if (isset($cookie->ajax_blockcart_display))
			$smarty->assign('colapseExpandStatus', $cookie->ajax_blockcart_display);
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockCart'))
		{
			if (Tools::getValue('ps_display_tax') == 2)
			{
				Configuration::updateValue('PS_TAX_DISPLAY', 1);
				Configuration::updateValue('PS_TAX_DISPLAY_ALL', 1);
			}
			elseif (Tools::getValue('ps_display_tax') == 1)
			{
				Configuration::updateValue('PS_TAX_DISPLAY', 1);
				Configuration::updateValue('PS_TAX_DISPLAY_ALL', 0);
			}
			else
			{
				Configuration::updateValue('PS_TAX_DISPLAY', 0);
				Configuration::updateValue('PS_TAX_DISPLAY_ALL', 0);
			}

			Configuration::updateValue('PS_BLOCK_CART_AJAX', (int)Tools::getValue('ajax'));

			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="width3">
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>

				<label>'.$this->l('Enable Ajax cart').'</label>
				<div class="margin-form">
					<input type="radio" name="ajax" id="ajax_on" value="1" '.(Configuration::get('PS_BLOCK_CART_AJAX') ? 'checked="checked" ' : '').'/>
					<label class="t" for="ajax_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'" />'.$this->l('Yes').'&nbsp;</label>
					<input type="radio" name="ajax" id="ajax_off" value="0" '.(!Configuration::get('PS_BLOCK_CART_AJAX') ? 'checked="checked" ' : '').'/>
					<label class="t" for="ajax_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" />'.$this->l('No').'</label>
					<p class="clear">'.$this->l('Enable AJAX mode for cart (Your theme has to be compliant)').'</p>
				</div>
				<label>'.$this->l('Display taxes').'</label>
				<div class="margin-form">
					<select name="ps_display_tax" style="width: 380px;">
						<option value="2"'.((Configuration::get('PS_TAX_DISPLAY') && Configuration::get('PS_TAX_DISPLAY_ALL')) ? ' selected="selected"' : '').'>'.$this->l('Yes, for all customers').'</option>
						<option value="1"'.((Configuration::get('PS_TAX_DISPLAY') && !Configuration::get('PS_TAX_DISPLAY_ALL')) ? ' selected="selected"' : '').'>'.$this->l('Yes, but only if the customer\'s group has taxes enabled').'</option>
						<option value="0"'.((!Configuration::get('PS_TAX_DISPLAY') && !Configuration::get('PS_TAX_DISPLAY_ALL')) ? ' selected="selected"' : '').'>'.$this->l('No').'</option>
					</select>
				</div>
				<br />
				<center><input type="submit" name="submitBlockCart" value="'.$this->l('   Save   ').'" class="button" /></center>
			</fieldset>
		</form>
		<br />';
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
		if (Configuration::get('PS_CATALOG_MODE'))
			return;

		global $smarty;
		$smarty->assign('order_page', strpos($_SERVER['PHP_SELF'], 'order') !== false);
		$this->smartyAssigns($smarty, $params);
		return $this->display(__FILE__, 'blockcart.tpl');
	}

	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	public function hookAjaxCall($params)
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;

		global $smarty;
		$this->smartyAssigns($smarty, $params);
		$res = $this->display(__FILE__, 'blockcart-json.tpl');
		return $res;
	}

	public function hookHeader()
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;

		Tools::addCSS(($this->_path).'blockcart.css', 'all');
		if ((int)(Configuration::get('PS_BLOCK_CART_AJAX')))
			Tools::addJS(($this->_path).'ajax-cart.js');
	}
}

