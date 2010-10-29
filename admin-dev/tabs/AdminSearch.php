<?php

/**
  * Search tab for admin panel, AdminSearch.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

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
		
		$this->_list['products'] = Product::searchByName(intval($cookie->id_lang), $query);
		if (!empty($this->_list['products']))
			for ($i = 0; $i < count($this->_list['products']); $i++)
				$this->_list['products'][$i]['nameh'] = str_ireplace($query, '<span class="highlight">'.$query.'</span>', $this->_list['products'][$i]['name']);

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
		
		$query = trim(Tools::getValue('bo_query'));
		$searchType = (int)Tools::getValue('bo_search_type');
		
		/* Handle empty search field */
		if (empty($query))
			$this->_errors[] = Tools::displayError('please fill in search form first');
		else
		{
			echo '<h2>'.$this->l('Search results').'</h2>';
			
			/* Product research */
			if (!$searchType OR $searchType == 1)
			{
				$this->fieldsDisplay['catalog'] = (array(
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

				/* Handle product ID */
				if ($searchType == 1 AND intval($query) AND Validate::isUnsignedInt(intval($query)))
					if ($product = new Product(intval($query)) AND Validate::isLoadedObject($product))
						Tools::redirectAdmin('index.php?tab=AdminCatalog&id_product='.intval($product->id).'&addproduct'.'&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)));

				/* Normal catalog search */
				$this->searchCatalog($query);
			}

			/* Customer */
			if (!$searchType OR $searchType == 2)
			{
				$this->fieldsDisplay['customers'] = (array(
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
				if ($searchType AND intval($query) AND Validate::isUnsignedInt(intval($query)))
					if ($customer = new Customer(intval($query)) AND Validate::isLoadedObject($customer))
						Tools::redirectAdmin('index.php?tab=AdminCustomers&id_customer='.intval($customer->id).'&viewcustomer'.'&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)));

				/* Normal customer search */
				$this->searchCustomer($query);
			}

			/* Order */
			if ($searchType == 3)
			{
				if (intval($query) AND Validate::isUnsignedInt(intval($query)) AND $order = new Order(intval($query)) AND Validate::isLoadedObject($order))
					Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.intval($order->id).'&vieworder'.'&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee)));
				$this->_errors[] = Tools::displayError('no order found with this ID:').' '.Tools::htmlentitiesUTF8($query);
			}
			
			/* Invoices */
			if ($searchType == 4)
			{
				if (intval($query) AND Validate::isUnsignedInt(intval($query)) AND $invoice = Order::getInvoice(intval($query)))
					Tools::redirectAdmin('pdf.php?id_order='.intval($invoice['id_order']).'&pdf');
				$this->_errors[] = Tools::displayError('no invoice found with this ID:').' '.Tools::htmlentitiesUTF8($query);
			}

			/* Cart */
			if ($searchType == 5)
			{
				if (intval($query) AND Validate::isUnsignedInt(intval($query)) AND $cart = new Cart(intval($query)) AND Validate::isLoadedObject($cart))
					Tools::redirectAdmin('index.php?tab=AdminCarts&id_cart='.intval($cart->id).'&viewcart'.'&token='.Tools::getAdminToken('AdminCarts'.intval(Tab::getIdFromClassName('AdminCarts')).intval($cookie->id_employee)));
				$this->_errors[] = Tools::displayError('no cart found with this ID:').' '.Tools::htmlentitiesUTF8($query);
			}
		}
	}

	public function display()
	{
		global $cookie;
		$currentIndex = 'index.php';
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$query = trim(Tools::getValue('bo_query'));
		$nbCategories = $nbProducts = $nbCustomers = 0;
		
		/* Display categories if any has been matching */
		if (isset($this->_list['categories']) AND $nbCategories = sizeof($this->_list['categories']))
		{
			echo '<h3>'.$nbCategories.' '.($nbCategories > 1 ? $this->l('categories found with') : $this->l('category found with')).' <b>"'.Tools::htmlentitiesUTF8($query).'"</b></h3>';
			echo '<table cellspacing="0" cellpadding="0" class="table">';
			$irow = 0;
			foreach ($this->_list['categories'] AS $k => $category)
				echo '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'"><td>'.rtrim(getPath($currentIndex.'?tab=AdminCatalog', $category['id_category'], '', $query), ' >').'</td></tr>';
			echo '</table>
			<div class="clear">&nbsp;</div>';
		}

		/* Display products if any has been matching */
		if (isset($this->_list['products']) AND !empty($this->_list['products']) AND $nbProducts = sizeof($this->_list['products']))
		{
			echo '<h3>'.$nbProducts.' '.($nbProducts > 1 ? $this->l('products found with') : $this->l('product found with')).' <b>"'.Tools::htmlentitiesUTF8($query).'"</b></h3>
			<table class="table" cellpadding="0" cellspacing="0">
				<tr>';
			foreach ($this->fieldsDisplay['catalog'] AS $field)
				echo '<th'.(isset($field['width']) ? 'style="width: '.$field['width'].'"' : '').'>'.$field['title'].'</th>';
			echo '</tr>';
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
						<a href="'.$currentIndex.'?tab=AdminCatalog&id_product='.$product['id_product'].'&deleteproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'"
							onclick="return confirm(\''.$this->l('Do you want to delete this product?', __CLASS__, true, false).' ('.addslashes($product['name']).')\');">
						<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this product').'" /></a>
					</td>
				</tr>';
			}
			echo '</table>
			<div class="clear">&nbsp;</div>';
		}

		/* Display customers if any has been matching */
		if (isset($this->_list['customers']) AND !empty($this->_list['customers']) AND $nbCustomers = sizeof($this->_list['customers']))
		{
			echo '<h3>'.$nbCustomers.' '.($nbCustomers > 1 ? $this->l('customers') : $this->l('customer')).' '.$this->l('found with').' <b>"'.Tools::htmlentitiesUTF8($query).'"</b></h3>
			<table cellspacing="0" cellpadding="0" class="table widthfull">
				<tr>';
			foreach ($this->fieldsDisplay['customers'] AS $field)
				echo '<th'.(isset($field['width']) ? 'style="width: '.$field['width'].'"' : '').'>'.$field['title'].'</th>';
			echo '</tr>';
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
			echo '</table>
			<div class="clear">&nbsp;</div>';
		}
			
		/* Display error if nothing has been matching */
		if (!$nbCategories AND !$nbProducts AND !$nbCustomers)
			echo '<h3>'.$this->l('Nothing found for').' "'.Tools::htmlentitiesUTF8($query).'"</h3>';
	}
}

?>
