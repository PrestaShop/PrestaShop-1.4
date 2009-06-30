<?php

class productsCategory extends Module
{
 	function __construct()
 	{
 	 	$this->name = 'productscategory';
 	 	$this->version = '1.2.1';
 	 	$this->tab = 'Products';
		
		parent::__construct();
		
		$this->displayName = $this->l('Products Category');
		$this->description = $this->l('Display products of the same category on the product page');
 	}

	function install()
	{
	 	if (!parent::install())
	 		return false;
	 	return $this->registerHook('productfooter');
	}
	
	private function getCurrentProduct($products, $id_current)
	{
		if ($products)
			foreach ($products as $key => $product)
				if ($product['id_product'] == $id_current)
					return $key;
		return false;
	}
	
	public function hookProductFooter($params)
	{
		global $smarty, $cookie;
		
		$idProduct = intval(Tools::getValue('id_product'));
		$product = new Product(intval($idProduct));
		
		$category = new Category(1);
		if (isset($params['category']->id_category))
			$category = $params['category'];
		if ($category->id_category == 1 AND isset($product->id_category_default) AND $product->id_category_default > 1)
			$category = New Category(intval($product->id_category_default));
		if (!Validate::isLoadedObject($category))
			Tools::displayError('Bad category!');
		if (intval($category->id_category) === 1)
			return;
		
		// Get infos
		$sizeOfCategoryProducts = $category->getProducts(intval($cookie->id_lang), 1, 30, NULL, NULL, true);
		$categoryProducts = $category->getProducts(intval($cookie->id_lang), 1, $sizeOfCategoryProducts);
		
		//remove current product from the list
		$current_product_key = null;
		foreach ($categoryProducts as $key => $categoryProduct)
		{
			if ($categoryProduct['id_product'] == $idProduct)
			{
				$current_product_key = $key;
				break;
			}
		}
		if (isset($categoryProducts[$current_product_key]))
			unset($categoryProducts[$current_product_key]);
		
		// Get positions
		$middlePosition = round($sizeOfCategoryProducts / 2, 0);
		$productPosition = $this->getCurrentProduct($categoryProducts, $idProduct);
		
		// Flip middle product with current product
		if ($productPosition)
		{
			$tmp = $categoryProducts[$middlePosition-1];
			$categoryProducts[$middlePosition-1] = $categoryProducts[$productPosition];
			$categoryProducts[$productPosition] = $tmp;
		}
		
		// If products tab higher than 30, slice it
		if ($sizeOfCategoryProducts > 30)
		{
			$categoryProducts = array_slice($categoryProducts, $middlePosition - 15, 30, true);
			$middlePosition = 15;
		}
		
		// Display tpl
		$smarty->assign('categoryProducts', $categoryProducts);
		$smarty->assign('middlePosition', $middlePosition);
		return $this->display(__FILE__, 'productscategory.tpl');
	}
}
?>
