<?php

/**
  * Customers tab for admin panel, AdminContacts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminCarts extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'cart';
	 	$this->className = 'Cart';
		$this->lang = false;
	 	$this->edit = false;
	 	$this->view = true;
	 	$this->delete = false;


		$this->_select = 'CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`, a.id_cart as total, ca.name as carrier';
		$this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer c on (c.id_customer = a.id_customer)
		LEFT JOIN '._DB_PREFIX_.'currency cu on (cu.id_currency = a.id_currency)
		LEFT JOIN '._DB_PREFIX_.'carrier ca on (ca.id_carrier = a.id_carrier)
		';
		
 		$this->fieldsDisplay = array(
		'id_cart' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'customer' => array('title' => $this->l('Customer'), 'width' => 80, 'filter_key' => 'c!lastname'),
		'total' => array('title' => $this->l('Total'), 'callback' => 'getTotalCart', 'orderby' => false, 'search' => false, 'width' => 50, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'currency' => true),
		'carrier' => array('title' => $this->l('Carrier'), 'width' => 25, 'align' => 'center', 'callback' => 'replaceZeroByShopName'),
		'date_add' => array('title' => $this->l('Date'), 'width' => 90, 'align' => 'right', 'type' => 'datetime', 'filter_key' => 'a!date_add'));
		parent::__construct();
	}

	public function viewDetails()
	{
		global $currentIndex, $cookie;

		$cart = $this->loadObject();
		$customer = new Customer($cart->id_customer);
		$customerStats = $customer->getStats();
		$products = $cart->getProducts();
		$customizedDatas = Product::getAllCustomizedDatas(intval($cart->id));
		Product::addCustomizationPrice($products, $customizedDatas);
		$summary = $cart->getSummaryDetails();
		$discounts = $cart->getDiscounts();

		$currency = new Currency($cart->id_currency);
		$currentLanguage = new Language(intval($cookie->id_lang));

		// display cart header
		echo '<h2>'.(($customer->id) ? $customer->firstname.' '.$customer->lastname : $this->l('Guest')).' - '.$this->l('Cart #').sprintf('%06d', $cart->id).' '.$this->l('from').' '.$cart->date_upd.'</h2>';

		/* Display customer information */
		echo '
		<br />
		<div style="float: left;">
		<fieldset style="width: 400px">
			<legend><img src="../img/admin/tab-customers.gif" /> '.$this->l('Customer information').'</legend>
			<span style="font-weight: bold; font-size: 14px;">';
			if ($customer->id)
				echo '
			<a href="?tab=AdminCustomers&id_customer='.$customer->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'"> '.$customer->firstname.' '.$customer->lastname.'</a></span> ('.$this->l('#').$customer->id.')<br />
			(<a href="mailto:'.$customer->email.'">'.$customer->email.'</a>)<br /><br />
			'.$this->l('Account registered:').' '.Tools::displayDate($customer->date_add, intval($cookie->id_lang), true).'<br />
			'.$this->l('Valid orders placed:').' <b>'.$customerStats['nb_orders'].'</b><br />
			'.$this->l('Total paid since registration:').' <b>'.Tools::displayPrice($customerStats['total_orders'], $currency, false, false).'</b><br />';
			else
				echo $this->l('Guest not registered').'</span>';
		echo '</fieldset>';
		echo '
		</div>
		<div style="float: left; margin-left: 40px">';
		
		/* Display order information */
		$id_order = intval(Order::getOrderByCartId($cart->id));
		$order = new Order($id_order);
		echo '
		<fieldset style="width: 400px">
			<legend><img src="../img/admin/cart.gif" /> '.$this->l('Order information').'</legend>
			<span style="font-weight: bold; font-size: 14px;">';
			if ($order->id)
				echo '
			<a href="?tab=AdminOrders&id_order='.intval($order->id).'&vieworder&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee)).'"> '.$this->l('Order #').sprintf('%06d', $order->id).'</a></span>
			<br /><br />
			'.$this->l('Made on:').' '.$order->date_add.'<br /><br /><br /><br />';
			else
				echo $this->l('No order created from this cart').'</span>';
		echo '</fieldset>';
		echo '
		</div>';

		// List of products
		echo '
		<br style="clear:both;" />
			<fieldset style="margin-top:25px; width: 715px; ">
				<legend><img src="../img/admin/cart.gif" alt="'.$this->l('Products').'" />'.$this->l('Cart summary').'</legend>
				<div style="float:left;">
					<table style="width: 700px;" cellspacing="0" cellpadding="0" class="table" id="orderProducts">
						<tr>
							<th align="center" style="width: 60px">&nbsp;</th>
							<th>'.$this->l('Product').'</th>
							<th style="width: 80px; text-align: center">'.$this->l('UP').'</th>
							<th style="width: 20px; text-align: center">'.$this->l('Qty').'</th>
							<th style="width: 30px; text-align: center">'.$this->l('Stock').'</th>
							<th style="width: 90px; text-align: right; font-weight:bold;">'.$this->l('Total').'</th>
						</tr>';
						$tokenCatalog = Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee));
						foreach ($products as $k => $product)
						{
							$image = array();
							if (isset($product['id_product_attribute']) AND intval($product['id_product_attribute']))
								$image = Db::getInstance()->getRow('
								SELECT id_image
								FROM '._DB_PREFIX_.'product_attribute_image
								WHERE id_product_attribute = '.intval($product['id_product_attribute']));
						 	if (!isset($image['id_image']))
								$image = Db::getInstance()->getRow('
								SELECT id_image
								FROM '._DB_PREFIX_.'image
								WHERE id_product = '.intval($product['id_product']).' AND cover = 1');
						 	$stock = Db::getInstance()->getRow('
							SELECT '.($product['id_product_attribute'] ? 'pa' : 'p').'.quantity
							FROM '._DB_PREFIX_.'product p
							'.($product['id_product_attribute'] ? 'LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON p.id_product = pa.id_product' : '').'
							WHERE p.id_product = '.intval($product['id_product']).'
							'.($product['id_product_attribute'] ? 'AND pa.id_product_attribute = '.intval($product['id_product_attribute']) : ''));
							/* Customization display */
							$this->displayCustomizedDatas($customizedDatas, $product, $currency, $image, $tokenCatalog, $stock);
							if ($product['quantity'] > $product['customizationQuantityTotal'])
								echo '
								<tr>
									<td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.intval($product['id_product']).'-'.intval($image['id_image']).'.jpg',
									'product_mini_'.intval($product['id_product']).(isset($product['id_product_attribute']) ? '_'.intval($product['id_product_attribute']) : '').'.jpg', 45, 'jpg') : '--').'</td>
									<td><a href="index.php?tab=AdminCatalog&id_product='.$product['id_product'].'&updateproduct&token='.$tokenCatalog.'">
										<span class="productName">'.$product['name'].'</span><br />
										'.($product['reference'] ? $this->l('Ref:').' '.$product['reference'] : '')
										.(($product['reference'] AND $product['supplier_reference']) ? ' / '.$product['supplier_reference'] : '')
										.'</a></td>
									<td align="center">'.Tools::displayPrice($product['price_wt'], $currency, false, false).'</td>
									<td align="center" class="productQuantity">'.(intval($product['quantity']) - $product['customizationQuantityTotal']).'</td>
									<td align="center" class="productQuantity">'.intval($stock['quantity']).'</td>
									<td align="right">'.Tools::displayPrice($product['total_wt'], $currency, false, false).'</td>
								</tr>';
							if (isset($image['id_image']))
							{
								$target = '../img/tmp/product_mini_'.intval($product['id_product']).(isset($product['id_product_attribute']) ? '_'.intval($product['id_product_attribute']) : '').'.jpg';
								if (file_exists($target))
									$products[$k]['image_size'] = getimagesize($target);
							}
						}
					echo '
					<tr class="cart_total_product">
				<td colspan="5">'.$this->l('Total products:').'</td>
				<td class="price bold right">'.Tools::displayPrice($summary['total_products_wt'], $currency, false).'</td>
			</tr>';
			if ($summary['total_discounts'] != 0)
			echo '
			<tr class="cart_total_voucher">
				<td colspan="5">'.$this->l('Total vouchers:').'</td>
				<td class="price-discount bold right">'.Tools::displayPrice($summary['total_discounts'], $currency, false).'</td>
			</tr>';
			if ($summary['total_wrapping'] > 0)
			 echo '
			 <tr class="cart_total_voucher">
				<td colspan="5">'.$this->l('Total gift-wrapping:').'</td>
				<td class="price-discount bold right">'.Tools::displayPrice($summary['total_wrapping'], $currency, false).'</td>
			</tr>';
			if ($cart->getOrderTotal(true, 5) > 0)
			echo '
			<tr class="cart_total_delivery">
				<td colspan="5">'.$this->l('Total shipping:').'</td>
				<td class="price bold right">'.Tools::displayPrice($cart->getOrderTotal(true, 5), $currency, false).'</td>
			</tr>';
			echo '
			<tr class="cart_total_price">
				<td colspan="5" class="bold">'.$this->l('Total:').'</td>
				<td class="price bold right">'.Tools::displayPrice($summary['total_price'], $currency, false).'</td>
			</tr>
			</table>';

			if (sizeof($discounts))
			{
				echo '
			<table cellspacing="0" cellpadding="0" class="table" style="width:280px; margin:15px 0px 0px 420px;">
				<tr>
					<th><img src="../img/admin/coupon.gif" alt="'.$this->l('Discounts').'" />'.$this->l('Discount name').'</th>
					<th align="center" style="width: 100px">'.$this->l('Value').'</th>
				</tr>';
				
				foreach ($discounts as $discount)
					echo '
				<tr>
					<td><a href="?tab=AdminDiscounts&id_discount='.$discount['id_discount'].'&updatediscount&token='.Tools::getAdminToken('AdminDiscounts'.intval(Tab::getIdFromClassName('AdminDiscounts')).intval($cookie->id_employee)).'">'.$discount['name'].'</a></td>
					<td align="center">- '.Tools::displayPrice($discount['value_real'], $currency, false).'</td>
				</tr>';
				echo '
			</table>';
			}
				echo '
				</div>';

				// Cancel product
				echo '
			</fieldset>
		<div class="clear" style="height:20px;">&nbsp;</div>';
	}
	
	private function displayCustomizedDatas(&$customizedDatas, &$product, &$currency, &$image, $tokenCatalog, &$stock)
	{
		$order = $this->loadObject();

		if (is_array($customizedDatas) AND isset($customizedDatas[intval($product['product_id'])][intval($product['product_attribute_id'])]))
		{
			echo '
			<tr>
				<td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.intval($product['product_id']).'-'.intval($image['id_image']).'.jpg',
				'product_mini_'.intval($product['product_id']).(isset($product['product_attribute_id']) ? '_'.intval($product['product_attribute_id']) : '').'.jpg', 45, 'jpg') : '--').'</td>
				<td><a href="index.php?tab=AdminCatalog&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'">
					<span class="productName">'.$product['product_name'].'</span><br />
					'.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'] : '')
					.(($product['product_reference'] AND $product['product_supplier_reference']) ? ' / '.$product['product_supplier_reference'] : '')
					.'</a></td>
				<td align="center">'.Tools::displayPrice($product['product_price_wt'], $currency, false, false).'</td>
				<td align="center" class="productQuantity">'.$product['customizationQuantityTotal'].'</td>
				<td align="center" class="productQuantity">'.intval($stock['quantity']).'</td>
				<td align="center">'.Tools::displayPrice($product['total_customization_wt'], $currency, false, false).'</td>
			</tr>';
			foreach ($customizedDatas[intval($product['product_id'])][intval($product['product_attribute_id'])] AS $customization)
			{
				echo '
				<tr>
					<td colspan="2">';
				foreach ($customization['datas'] AS $type => $datas)
					if ($type == _CUSTOMIZE_FILE_)
					{
						$i = 0;
						echo '<ul style="margin: 4px 0px 4px 0px; padding: 0px; list-style-type: none;">';
						foreach ($datas AS $data)
							echo '<li style="display: inline; margin: 2px;">
									<a href="displayImage.php?img='.$data['value'].'&name='.intval($order->id).'-file'.++$i.'" target="_blank"><img src="'._THEME_PROD_PIC_DIR_.$data['value'].'_small" alt="" /></a>
								</li>';
						echo '</ul>';
					}
					elseif ($type == _CUSTOMIZE_TEXTFIELD_)
					{
						$i = 0;
						echo '<ul style="margin: 0px 0px 4px 0px; padding: 0px 0px 0px 6px; list-style-type: none;">';
						foreach ($datas AS $data)
							echo '<li>'.$this->l('Text #').++$i.$this->l(':').' '.$data['value'].'</li>';
						echo '</ul>';
					}
				echo '</td>
					<td align="center"></td>
					<td align="center" class="productQuantity">'.$customization['quantity'].'</td>
					<td align="center" class="productQuantity"></td>
					<td align="center">'.Tools::displayPrice(floatval($product['product_price']) * floatval($customization['quantity']), $currency, false, false).'</td>
				</tr>';
			}
		}
	}

	public function display()
	{
		global $cookie;

		if (isset($_GET['view'.$this->table]))
			$this->viewDetails();
		else
		{
			$this->getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
			$this->displayList();
		}
	}
}

?>