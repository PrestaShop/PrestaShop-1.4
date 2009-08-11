<?php

/**
  * Suppliers tab for admin panel, AdminSuppliers.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminTracking extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'none';
	 	$this->className = 'none';

		parent::__construct();
	}
	
	public function display()
	{
		global $currentIndex;
		
		echo '<h2 class="space">'.$this->l('Catalog tracking').'</h2>';
		$this->getObjects('categories_empty');
		$this->displayCategories();
		$this->getObjects('products_disabled');
		$this->displayProducts();
		$this->getObjects('products_nostock');
		$this->displayProducts();
	}
	
	public function getObjects($type)
	{
		switch ($type)
		{
			case 'categories_empty':
				$sql = '
					SELECT id_category
					FROM `'._DB_PREFIX_.'category`
					WHERE id_category NOT IN (
					  SELECT DISTINCT(id_category)
					  FROM `'._DB_PREFIX_.'category_product`
					)
				';
				$this->_list['message'] = $this->l('List of empty categories:');
				break ;
			case 'products_disabled':
				$sql = '
					SELECT *
					FROM `'._DB_PREFIX_.'product`
					WHERE active = 0
				';
				$this->_list['message'] = $this->l('List of disabled products:');
				break ;
			case 'products_nostock':
				$sql = '
					SELECT DISTINCT(id_product)
					FROM `'._DB_PREFIX_.'product`
					WHERE id_product IN (
					  SELECT id_product
					  FROM `'._DB_PREFIX_.'product`
					  WHERE id_product NOT IN (
						SELECT DISTINCT(id_product)
						FROM `'._DB_PREFIX_.'product_attribute`
					  )
					  AND quantity <= 0
					) OR id_product IN (
					  SELECT pa.id_product
					  FROM `'._DB_PREFIX_.'product_attribute` pa
					  WHERE pa.quantity <= 0
					)
				';
				$this->_list['message'] = $this->l('List of out of stock products:');
				break ;
		}
		$this->_list['obj'] = Db::getInstance()->ExecuteS($sql);
	}
	
	public function displayCategories()
	{
		global $currentIndex;
		
		if (isset($this->_list['obj']))
		{
			$nbCategories = sizeof($this->_list['obj']);
			echo '<h3>'.$this->_list['message'].' '.$nbCategories.' '.$this->l('found').'</h3>';
			if (!$nbCategories)
				return ;
			echo '
			<table cellspacing="0" cellpadding="0" class="table">';
			$irow = 0;
			foreach ($this->_list['obj'] AS $k => $category)
				echo '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'"><td>'.rtrim(getPath('index.php?tab=AdminCatalog', $category['id_category']), ' >').'</td></tr>';
			echo '</table><br /><br />';
		}
	}
	
	public function displayProducts()
	{
		global $currentIndex, $cookie;

		if (isset($this->_list['obj']))
		{
			$nbProducts = sizeof($this->_list['obj']);
			echo '<h3>'.$this->_list['message'].' '.$nbProducts.' '.$this->l('found').'</h3>';
			if (!$nbProducts)
				return ;
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
			$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
			echo '
			<table class="table" cellpadding="0" cellspacing="0">
				<tr>';
			foreach ($this->fieldsDisplay AS $field)
				echo '<th'.(isset($field['width']) ? 'style="width: '.$field['width'].'"' : '').'>'.$field['title'].'</th>';
			echo '
				</tr>';
			foreach ($this->_list['obj'] AS $k => $prod)
			{
				$product = new Product(intval($prod['id_product']));
				$product->name = $product->name[intval($cookie->id_lang)];
				$tax = new Tax(intval($product->id_tax));
				echo '
				<tr>
					<td>'.$product->id.'</td>
					<td align="center">'.($product->manufacturer_name != NULL ? stripslashes($product->manufacturer_name) : '--').'</td>
					<td>'.$product->reference.'</td>
					<td><a href="index.php?tab=AdminCatalog&id_product='.$product->id.'&addproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">'.stripslashes($product->name).'</a></td>
					<td>'.Tools::displayPrice($product->getPrice(), $currency).'</td>
					<td>'.stripslashes($tax->name[intval($cookie->id_lang)]).'</td>
					<td align="center">'.$product->quantity.'</td>
					<td align="center">'.$product->weight.' '.Configuration::get('PS_WEIGHT_UNIT').'</td>
					<td align="center"><a href="index.php?tab=AdminCatalog&id_product='.$product->id.'&status&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'"><img src="../img/admin/'.($product->active ? 'enabled.gif' : 'disabled.gif').'" alt="" /></a></td>
					<td>
						<a href="index.php?tab=AdminCatalog&id_product='.$product->id.'&addproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">
						<img src="../img/admin/edit.gif" alt="'.$this->l('Modify this product').'" /></a>&nbsp;
						<a href="index.php?tab=AdminCatalog&id_product='.$product->id.'&deleteproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" onclick="return confirm(\''.addslashes($this->l('Do you want to delete').' '.$product->name).' ?\');">
						<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this product').'" /></a>
					</td>
				</tr>';
			}
			echo '</table><br /><br />';
		}
	}
}

?>