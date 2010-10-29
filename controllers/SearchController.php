<?php

class SearchControllerCore extends FrontController
{
	public $instantSearch;
	public $ajaxSearch;
	
	public function __construct()
	{
		parent::__construct();
		$this->instantSearch = Tools::getValue('instantSearch');
		$this->ajaxSearch = Tools::getValue('ajaxSearch');
	}
	
	public function preProcess()
	{
		parent::preProcess();
		
		$query = urldecode(Tools::getValue('q'));
		if ($this->ajaxSearch)
		{
			$this->link = new Link();
			$searchResults = Search::find(intval(Tools::getValue('id_lang')), $query, 1, 10, 'position', 'desc', true);
			foreach ($searchResults AS &$product)
				$product['product_link'] = $this->link->getProductLink($product['id_product'], $product['prewrite'], $product['crewrite']);
			die(json_encode($searchResults));
		}
		
		if ($this->instantSearch && !is_array($query))
		{
			include(dirname(__FILE__).'/../product-sort.php');
			global $orderBy, $orderWay;
			$n = abs(intval(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))));
			$p = abs(intval(Tools::getValue('p', 1)));
			$search = Search::find(intval($this->cookie->id_lang), $query, $p, $n, $orderBy, $orderWay);
			$nbProducts = $search['total'];
			include(dirname(__FILE__).'/../pagination.php');
			$this->smarty->assign(array(
			'products' => $search['result'],
			'nbProducts' => $search['total'],
			'search_query' => $query,
			'instantSearch' => $this->instantSearch,
			'homeSize' => Image::getSize('home')));
		}
		elseif ($query = Tools::getValue('search_query', Tools::getValue('ref')) AND !is_array($query))
		{
			include(dirname(__FILE__).'/../product-sort.php');
			global $orderBy, $orderWay;
			$n = abs(intval(Tools::getValue('n', Configuration::get('PS_PRODUCTS_PER_PAGE'))));
			$p = abs(intval(Tools::getValue('p', 1)));
			$search = Search::find(intval($this->cookie->id_lang), $query, $p, $n, $orderBy, $orderWay);
			$nbProducts = $search['total'];
			include(dirname(__FILE__).'/../pagination.php');
			$this->smarty->assign(array(
			'products' => $search['result'],
			'nbProducts' => $search['total'],
			'search_query' => $query,
			'homeSize' => Image::getSize('home')));
		}
		elseif ($tag = Tools::getValue('tag') AND !is_array($tag))
		{
			$nbProducts = intval(Search::searchTag(intval($this->cookie->id_lang), $tag, true));
			include(dirname(__FILE__).'/../pagination.php');
			global $p, $n, $orderBy, $orderWay;
			$this->smarty->assign(array(
			'search_tag' => $tag,
			'products' => Search::searchTag(intval($this->cookie->id_lang), $tag, false, $p, $n, $orderBy, $orderWay),
			'nbProducts' => $nbProducts,
			'homeSize' => Image::getSize('home')));
		}
		else
		{
			$this->smarty->assign(array(
			'products' => array(),
			'pages_nb' => 1,
			'nbProducts' => 0));
		}
		$this->smarty->assign('add_prod_display', Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'));
	}
	
	public function displayHeader()
	{
		if (!$this->instantSearch AND !$this->ajaxSearch)
			parent::displayHeader();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'search.tpl');
	}
	
	public function displayFooter()
	{
		if (!$this->instantSearch AND !$this->ajaxSearch)
			parent::displayFooter();
	}
}