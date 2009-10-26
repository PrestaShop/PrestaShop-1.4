<?php

/**
  * Search tab for admin panel, AdminSearch.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminSearch extends AdminTab
{
	/**
	* Search a specific string in the products and categories
	*
	* @params string $query String to find in the catalog
	*/
	public function searchCatalog($query)
	{
		global $cookie;

		$products = false;
		if (Validate::isCatalogName($query))
		{
			$this->_list['products'] = Product::searchByName(intval($cookie->id_lang), $query);
			if (!empty($this->_list['products']))
				for ($i = 0; $i < count($this->_list['products']); $i++)
					$this->_list['products'][$i]['nameh'] = str_ireplace($query, '<span class="highlight">'.$query.'</span>', $this->_list['products'][$i]['name']);
		}
		if (Validate::isCatalogName($query))
			$this->_list['categories'] = Category::searchByName(intval($cookie->id_lang), $query);
	}

	/**
	* Search a specific name in the customers
	*
	* @params string $query String to find in the catalog
	*/
	public function	searchCustomer($query)
	{
		$this->_list['customers'] = Customer::searchByName($query);
	}

	function postProcess()
	{
		global $cookie;
		/* Handle empty search field */
		if (!isset($_POST['bo_query']) OR empty($_POST['bo_query']) OR !isset($_POST['bo_search_type']))
		{
			echo '<h2>'.$this->l('Search results').'</h2>';
			$this->_errors[] = Tools::displayError('please fill in search form first');
		}
		else
		{
			/* Product research */
			if (intval($_POST['bo_search_type']) == 1)
			{
				$this->fieldsDisplay = (array(
					'ID' => array('title' => $this->l('ID')),
					'manufacturer' => array('title' => $this->l('Manufacturer')),
					'reference' => array('title' => $this->l('Reference')),
					'name' => array('title' => $this->l('Name')),
					'price' => array('title' => $this->l('Price')),
					'tax' => array('title' => $this->l('Tax')),
					'stock' => array('title' => $this->l('Stock')),
					'weight' => array('title' => $this->l('Weight')),
					'status' => array('title' => $this->l('Status')),
					'action' => array('title' => $this->l('Actions'))
				));
				$this->searchCatalog(trim(strval($_POST['bo_query'])));
			}

			/* Customer */
			elseif (intval($_POST['bo_search_type']) == 2)
			{
				$this->fieldsDisplay = (array(
					'ID' => array('title' => $this->l('ID')),
					'sex' => array('title' => $this->l('Sex')),
					'name' => array('title' => $this->l('Name')),
					'e-mail' => array('title' => $this->l('e-mail')),
					'birthdate' => array('title' => $this->l('Birth date')),
					'register_date' => array('title' => $this->l('Register date')),
					'orders' => array('title' => $this->l('Orders')),
					'status' => array('title' => $this->l('Status')),
					'actions' => array('title' => $this->l('Actions'))
				));
				/* Handle customer ID */
				if (intval($_POST['bo_query']) AND Validate::isUnsignedInt(intval($_POST['bo_query'])))
				{
					$customer = new Customer(intval($_POST['bo_query']));
					if ($customer->id)
						Tools::redirectAdmin('index.php?tab=AdminCustomers&id_customer='.intval($_POST['bo_query']).'&viewcustomer'.'&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)));
					else
						$this->_errors[] = Tools::displayError('customer #').intval($_POST['bo_query']).' '.Tools::displayError('not found');
				}
				/* Search customers by name */
				else
					self::searchCustomer($_POST['bo_query']);
			}

			/* Order */
			elseif (intval($_POST['bo_search_type']) == 3)
			{
				if (intval($_POST['bo_query']) AND Validate::isUnsignedInt(intval($_POST['bo_query'])))
				{
					$order = new Order(intval($_POST['bo_query']));
					if ($order->id)
						Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.intval($_POST['bo_query']).'&vieworder'.'&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee)));
					else
						$this->_errors[] = Tools::displayError('order #').intval($_POST['bo_query']).' '.Tools::displayError('not found');
				}
				else
					$this->_errors[] = Tools::displayError('please type an order ID');
			}

			/* Cart */
			elseif (intval($_POST['bo_search_type']) == 5)
			{
				if (intval($_POST['bo_query']) AND Validate::isUnsignedInt(intval($_POST['bo_query'])))
				{
					if ($cart = new Cart(intval($_POST['bo_query'])) AND $cart->id)
					{
						Tools::redirectAdmin('index.php?tab=AdminCarts&id_cart='.intval($cart->id).'&viewcart'.'&token='.Tools::getAdminToken('AdminCarts'.intval(Tab::getIdFromClassName('AdminCarts')).intval($cookie->id_employee)));
					}
					else
						$this->_errors[] = Tools::displayError('cart #').intval($_POST['bo_query']).' '.Tools::displayError('not found');
				}
				else
					$this->_errors[] = Tools::displayError('please type a cart ID');
			}
			
			/* Invoices */
			elseif (intval($_POST['bo_search_type']) == 4)
			{
				if (intval($_POST['bo_query']) AND Validate::isUnsignedInt(intval($_POST['bo_query'])))
				{
					if ($invoice = Order::getInvoice(intval($_POST['bo_query'])))
					{
						Tools::redirectAdmin('pdf.php?id_order='.intval($invoice['id_order']).'&pdf');
					}
					else
						$this->_errors[] = Tools::displayError('invoice #').intval($_POST['bo_query']).' '.Tools::displayError('not found');
				}
				else
					$this->_errors[] = Tools::displayError('please type an invoice ID');
			}
			else
				Tools::displayError('please fill in search form first.');
		}
	}

	public function display()
	{
		global $cookie;
		$currentIndex = 'index.php';
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		$query = isset($_POST['bo_query']) ? trim(strval($_POST['bo_query'])) : '';
		/* Display categories if any has been matching */
		if (isset($this->_list['categories']) AND $nbCategories = sizeof($this->_list['categories']))
		{
			echo '<h3>'.$nbCategories.' '.($nbCategories > 1 ? $this->l('categories found with') : $this->l('category found with')).' <b>"'.Tools::safeOutput($query).'"</b></h3>';
			echo '
			<table cellspacing="0" cellpadding="0" class="table">';
			$irow = 0;
			foreach ($this->_list['categories'] AS $k => $category)
				echo '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'"><td>'.rtrim(getPath($currentIndex.'?tab=AdminCatalog', $category['id_category'], '', $query), ' >').'</td></tr>';
			echo '</table><br /><br />';
		}
		else
			$nbCategories = 0;

		/* Display products if any has been matching */
		if (isset($this->_list['products']) AND !empty($this->_list['products']) AND $nbProducts = sizeof($this->_list['products']))
		{
			echo '<h3>'.$nbProducts.' '.($nbProducts > 1 ? $this->l('products found with') : $this->l('product found with')).' <b>"'.Tools::safeOutput($query).'"</b></h3>
			<table class="table" cellpadding="0" cellspacing="0">
				<tr>';
			foreach ($this->fieldsDisplay AS $field)
				echo '<th'.(isset($field['width']) ? 'style="width: '.$field['width'].'"' : '').'>'.$field['title'].'</th>';
			echo '
				</tr>';
			foreach ($this->_list['products'] AS $k => $product)
			{
				echo '
				<tr>
					<td>'.$product['id_product'].'</td>
					<td align="center">'.($product['manufacturer_name'] != NULL ? stripslashes($product['manufacturer_name']) : '--').'</td>
					<td>'.$product['reference'].'</td>
					<td><a href="'.$currentIndex.'?tab=AdminCatalog&id_product='.$product['id_product'].'&addproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">'.stripslashes($product['nameh']).'</a></td>
					<td>'.Tools::displayPrice($product['price'], $currency).'</td>
					<td>'.stripslashes($product['tax_name']).'</td>
					<td align="center">'.$product['quantity'].'</td>
					<td align="center">'.$product['weight'].' '.Configuration::get('PS_WEIGHT_UNIT').'</td>
					<td align="center"><a href="'.$currentIndex.'?tab=AdminCatalog&id_product='.$product['id_product'].'&status&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">
					<img src="../img/admin/'.($product['active'] ? 'enabled.gif' : 'forbbiden.gif').'" alt="" /></a></td>
					<td>
						<a href="'.$currentIndex.'?tab=AdminCatalog&id_product='.$product['id_product'].'&addproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">
						<img src="../img/admin/edit.gif" alt="'.$this->l('Modify this product').'" /></a>&nbsp;
						<a href="'.$currentIndex.'?tab=AdminCatalog&id_product='.$product['id_product'].'&deleteproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Do you want to delete', __CLASS__, true, false).' '.addslashes($product['name']).$this->l('?', __CLASS__, true, false).'\');">
						<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this product').'" /></a>
					</td>
				</tr>';
			}
			echo '</table>';
		}
		else
			$nbProducts = 0;

		/* Display customers if any has been matching */
		if (isset($this->_list['customers']) AND !empty($this->_list['customers']) AND $nbCustomers = sizeof($this->_list['customers']))
		{
			echo '<h3>'.$nbCustomers.' '.($nbCustomers > 1 ? $this->l('customers') : $this->l('customer')).' '.$this->l('found with').' <b>"'.Tools::safeOutput($query).'"</b></h3>
			<table cellspacing="0" cellpadding="0" class="table widthfull">
				<tr>';
			foreach ($this->fieldsDisplay AS $field)
				echo '<th'.(isset($field['width']) ? 'style="width: '.$field['width'].'"' : '').'>'.$field['title'].'</th>';
			echo '
				</tr>';
			$irow = 0;
			foreach ($this->_list['customers'] AS $k => $customer)
			{
				$imgGender = $customer['id_gender'] == 1 ? '<img src="../img/admin/male.gif" alt="'.$this->l('Male').'" />' : ($customer['id_gender'] == 2 ? '<img src="../img/admin/female.gif" alt="'.$this->l('Female').'" />' : '');
				echo '
				<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
					<td>'.$customer['id_customer'].'</td>
					<td class="center">'.$imgGender.'</td>
					<td>'.stripslashes($customer['lastname']).' '.stripslashes($customer['firstname']).'</td>
					<td>'.stripslashes($customer['email']).'<a href="mailto:'.stripslashes($customer['email']).'"> <img src="../img/admin/email_edit.gif" alt="'.$this->l('Write to this customer').'" /></a></td>
					<td>'.Tools::displayDate($customer['birthday'], intval($cookie->id_lang)).'</td>
					<td>'.Tools::displayDate($customer['date_add'], intval($cookie->id_lang)).'</td>
					<td>'.Order::getCustomerNbOrders($customer['id_customer']).'</td>
					<td class="center"><img src="../img/admin/'.($customer['active'] ? 'enabled.gif' : 'forbbiden.gif').'" alt="" /></td>
					<td class="center" width="60px">
						<a href="'.$currentIndex.'?tab=AdminCustomers&id_customer='.$customer['id_customer'].'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'">
						<img src="../img/admin/details.gif" alt="'.$this->l('View orders').'" /></a>
						<a href="'.$currentIndex.'?tab=AdminCustomers&id_customer='.$customer['id_customer'].'&addcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'">
						<img src="../img/admin/edit.gif" alt="'.$this->l('Modify this customer').'" /></a>
						<a href="'.$currentIndex.'?tab=AdminCustomers&id_customer='.$customer['id_customer'].'&deletecustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure?', __CLASS__, true, false).'\');">
						<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this customer').'" /></a>
					</td>
				</tr>';
			}
			echo '</table>';
		}
		else
			$nbCustomers = 0;

		if (isset($this->_list['cart']))
		{
			$cart = $this->_list['cart'];
			$products = $cart->getProducts();
			$discounts = $cart->getDiscounts();
			$total_discounts = $cart->getOrderTotal(false, 2);
			$total_shipping = $cart->getOrderShippingCost($cart->id_carrier);
			$total_wrapping = $cart->getOrderTotal(true, 6);
			$total_products = $cart->getOrderTotal(true, 1);
			$total_price = $cart->getOrderTotal();

			echo '<h2>'.$this->l('Cart found:').' (#'.sprintf('%08d', $cart->id).')</h2>
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th width="75" align="center">'.$this->l('Reference').'</th>
					<th>Product</th>
					<th width="55" align="center">'.$this->l('Quantity').'</th>
					<th width="88" align="right">'.$this->l('Unit price').'</th>
					<th width="80" align="right">'.$this->l('Total price').'</th>
				</tr>';
			if ($products)
				foreach ($products as $product)
					echo '
					<tr>
						<td>'.$product['reference'].'</td>
						<td>'.$product['name'].'</a></td>
						<td align="right">'.$product['quantity'].'</td>
						<td align="right">'.Tools::displayPrice($product['price'], $currency).'</td>
						<td align="right">'.Tools::displayPrice($product['total_wt'], $currency).'</td>
					</tr>';
			if ($discounts)
				foreach ($discounts as $discount)
					echo '
					<tr>
						<td>'.$discount['name'].'</td>
						<td>'.$discount['description'].'</td>
						<td align="right">1</td>
						<td align="right">-'.Tools::displayPrice($discount['value'], $currency).'</td>
						<td align="right">-'.Tools::displayPrice($discount['value'], $currency).'</td>
					</tr>';
			echo '
				<tr style="text-align: right; font-weight: bold;">
					<td colspan="4">'.$this->l('Products:').' </td>
					<td>'.Tools::displayPrice($total_products, $currency).'</td>
				</tr>
				<tr style="text-align: right; font-weight: bold;">
					<td colspan="4">'.$this->l('Vouchers').' </td>
					<td>'.Tools::displayPrice($total_discounts, $currency).'</td>
				</tr>
				<tr style="text-align: right; font-weight: bold;">
					<td colspan="4">'.$this->l('Gift-wrapping:').' </td>
					<td>'.Tools::displayPrice($total_wrapping, $currency).'</td>
				</tr>				
				<tr style="text-align: right; font-weight: bold;">
					<td colspan="4">'.$this->l('Shipping:').' </td>
					<td>'.Tools::displayPrice($total_shipping, $currency).'</td>
				</tr>
				<tr style="text-align: right; font-weight: bold;">
					<td colspan="4">'.$this->l('Total:').' </td>
					<td>'.Tools::displayPrice($total_price, $currency).'</td>
				</tr>
			</table>';
		}
			
		/* Display error if nothing has been matching */
		if (!$nbCategories AND !$nbProducts AND !$nbCustomers AND !isset($this->_list['cart']))
			echo '<h3>'.$this->l('Nothing found').'.</h3>';
	}
}

?>
