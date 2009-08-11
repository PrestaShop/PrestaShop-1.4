<?php

/**
  * Link class, Link.php
  * Links management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class Link
{
	/** @var boolean Rewriting activation */
	private $allow;
	private $url;
	
	/**
	  * Constructor (initialization only)
	  */
	function __construct()
	{
		$this->allow = intval(Configuration::get('PS_REWRITING_SETTINGS'));
		$this->url = $_SERVER['SCRIPT_NAME'];
	}
	
	/**
	  * Return the correct link for product/category/supplier/manufacturer
	  *
	  * @param mixed $id_OBJ Can be either the object or the ID only
	  * @param string $alias Friendly URL (only if $id_OBJ is the object)
	  * @return string link
	  */
	public function getProductLink($id_product, $alias = NULL, $category = NULL, $ean13 = NULL)
	{
	 	if (!isset($this->allow)) $this->allow = 0;
		if (is_object($id_product))
			return ($this->allow == 1)?(_PS_BASE_URL_.__PS_BASE_URI__.(($id_product->category != 'home' AND !empty($id_product->category)) ? $id_product->category.'/' : '').intval($id_product->id).'-'.$id_product->link_rewrite.($id_product->ean13 ? '-'.$id_product->ean13 : '').'.html') : 
			(_PS_BASE_URL_.__PS_BASE_URI__.'product.php?id_product='.intval($id_product->id));
		elseif ($alias)
			return ($this->allow == 1)?(_PS_BASE_URL_.__PS_BASE_URI__.(($category AND $category != 'home') ? ($category.'/') : '').intval($id_product).'-'.$alias.($ean13 ? '-'.$ean13 : '').'.html') : 
			(_PS_BASE_URL_.__PS_BASE_URI__.'product.php?id_product='.intval($id_product));
		else
			return _PS_BASE_URL_.__PS_BASE_URI__.'product.php?id_product='.intval($id_product);
	}

	public function getCategoryLink($id_category, $alias = NULL)
	{
		if (is_object($id_category))
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_category->id).'-'.$id_category->link_rewrite) : 
			(_PS_BASE_URL_.__PS_BASE_URI__.'category.php?id_category='.intval($id_category->id));
		if ($alias)
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_category).'-'.$alias) :
			(_PS_BASE_URL_.__PS_BASE_URI__.'category.php?id_category='.intval($id_category));
		return _PS_BASE_URL_.__PS_BASE_URI__.'category.php?id_category='.intval($id_category);
	}

	public function getCMSLink($cms, $alias = null)
	{
		if (is_object($cms))
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.'content/'.intval($cms->id).'-'.$cms->link_rewrite) : 
			(_PS_BASE_URL_.__PS_BASE_URI__.'cms.php?id_cms='.intval($cms->id));
		if ($alias)
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.'content/'.intval($cms).'-'.$alias) :
			(_PS_BASE_URL_.__PS_BASE_URI__.'cms.php?id_cms='.intval($cms));
		return _PS_BASE_URL_.__PS_BASE_URI__.'cms.php?id_cms='.intval($cms);
	}
	
	public function getSupplierLink($id_supplier, $alias = NULL)
	{
		if (is_object($id_supplier))
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_supplier->id).'__'.$id_supplier->link_rewrite) : 
			(_PS_BASE_URL_.__PS_BASE_URI__.'supplier.php?id_supplier='.intval($id_supplier->id));
		if ($alias)
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_supplier).'__'.$alias) :
			(_PS_BASE_URL_.__PS_BASE_URI__.'supplier.php?id_supplier='.intval($id_supplier));
		return _PS_BASE_URL_.__PS_BASE_URI__.'supplier.php?id_supplier='.intval($id_supplier);
	}
	
	public function getManufacturerLink($id_manufacturer, $alias = NULL)
	{
		if (is_object($id_manufacturer))
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_manufacturer->id).'_'.$id_manufacturer->link_rewrite) : 
			(_PS_BASE_URL_.__PS_BASE_URI__.'manufacturer.php?id_manufacturer='.intval($id_manufacturer->id));
		if ($alias)
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_manufacturer).'_'.$alias) :
			(_PS_BASE_URL_.__PS_BASE_URI__.'manufacturer.php?id_manufacturer='.intval($id_manufacturer));
		return _PS_BASE_URL_.__PS_BASE_URI__.'manufacturer.php?id_manufacturer='.intval($id_manufacturer);
	}

	public function getCustomLink($id_custom, $page, $prefix = '~', $alias = NULL)
	{
		if (is_object($id_custom))
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_custom->id).$prefix.$id_custom->link_rewrite) : 
			(_PS_BASE_URL_.__PS_BASE_URI__.$page.'?id_custom='.intval($id_custom->id));
		if ($alias)
			return ($this->allow == 1) ? (_PS_BASE_URL_.__PS_BASE_URI__.intval($id_custom).$prefix.$alias) :
			(_PS_BASE_URL_.__PS_BASE_URI__.$page.'?id_custom='.intval($id_custom));
		return _PS_BASE_URL_.__PS_BASE_URI__.$page.'?id_custom='.intval($id_custom);
	}
	
	public function getImageLink($name, $ids, $type = null)
	{
		return ($this->allow == 1) ? (__PS_BASE_URI__.$ids.($type ? '-'.$type : '').'/'.$name.'.jpg') : (_THEME_PROD_DIR_.$ids.($type ? '-'.$type : '').'.jpg');
	}
	
	public function getCatImageLink($name, $id_category, $type = null)
	{
		return ($this->allow == 1) ? (__PS_BASE_URI__.$id_category.($type ? '-'.$type : '').'/'.$name.'.jpg') : (_THEME_CAT_DIR_.$id_category.($type ? '-'.$type : '').'.jpg');
	}

	
	/**
	  * Create link after language change
	  *
	  * @param integer $id_lang Language ID
	  * @return string link
	  */
	public function getLanguageLink($id_lang)
	{
		if ($this->allow == 1)
			return __PS_BASE_URI__.'lang-'.Language::getIsoById($id_lang).'/'.substr(preg_replace('#/lang-([a-z]{2})/#', '/', $_SERVER['REQUEST_URI']), strlen(__PS_BASE_URI__));
		else
			return $this->getUrlWith('id_lang', intval($id_lang));
	}
	
	public function getLanguageLinkAdmin($id_lang)
	{
		return $this->getUrlWith('id_lang', intval($id_lang));
	}
	
	public function getUrlWith($key, $val)
    {
        $n = 0;
		$url = htmlentities($this->url, ENT_QUOTES, 'UTF-8');
        foreach ($_GET as $k => $value)
			if (!is_array($value) AND $k != $key AND Tools::isSubmit($k))
				$url .= ((!$n++) ? '?' : '&amp;').urlencode($k).($value ? '='.urlencode($value) : '');
        return $url.($n ? '&amp;' : '?').urlencode($key).'='.urlencode($val);
    }
	
	public function goPage($url, $p)
	{
		return $url.($p == 1 ? '' : (!strstr($url, '?') ? '?' : '&amp;').'p='.intval($p));
	}
	
	public function getPaginationLink($type, $id_object, $nb = FALSE, $sort = FALSE, $pagination = FALSE, $array = FALSE)
	{
		if ($type AND $id_object)
			$url = $this->{'get'.$type.'Link'}($id_object, NULL);
		else
			$url = $this->url;
		$vars = (!$array ? '' : array());
		$varsNb = array('n', 'search_query');
		$varsSort = array('orderby', 'orderway');
		$varsPagination = array('p');
		$varsVarious = array('search_query');
		
		foreach ($_GET as $k => $value)
			if ($k != 'id_'.$type)
			{
				$ifNb = (!$nb OR ($nb AND !in_array($k, $varsNb)));
				$ifSort = (!$sort OR ($sort AND !in_array($k, $varsSort)));
				$ifPagination = (!$pagination OR ($pagination AND !in_array($k, $varsPagination)));
				if ($ifNb AND $ifSort AND $ifPagination AND !is_array($value))
					!$array ? ($vars .= ((!$n++ AND ($this->allow == 1 OR $url == $this->url)) ? '?' : '&amp;').urlencode($k).'='.urlencode($value)) : ($vars[urlencode($k)] = urlencode($value));
			}
		if (!$array)
			return $url.$vars;
		$vars['requestUrl'] = $url;
		if ($type AND $id_object)
			$vars['id_'.$type] = (is_object($id_object) ? intval($id_object->id) : intval($id_object));
		return $vars;
	}
	
	public function addSortDetails($url, $orderby, $orderway)
	{
		return $url.(!strstr($url, '?') ? '?' : '&').'orderby='.urlencode($orderby).'&orderway='.urlencode($orderway);
	}
}

?>
