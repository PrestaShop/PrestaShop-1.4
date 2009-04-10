<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
class StatsCatalog extends Module
{
	private $_join = '';
	private $_where = '';

    function __construct()
    {
        $this->name = 'statscatalog';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
        parent::__construct();
		
        $this->displayName = $this->l('Catalog statistics');
        $this->description = $this->l('General statistics about your catalog');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	public function getQuery1()
	{
		return DB::getInstance()->getRow('
		SELECT COUNT(DISTINCT p.`id_product`) AS total, SUM(p.`price`) / COUNT(`price`) AS average_price, COUNT(DISTINCT i.`id_image`) AS images
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'image` i ON i.`id_product` = p.`id_product`
		'.$this->_join.'
		WHERE p.`active` = 1
		'.$this->_where);
	}
		
	public function getTotalPageViewed()
	{
		$result = Db::getInstance()->getRow('
		SELECT SUM(pv.`counter`) AS viewed
		FROM `'._DB_PREFIX_.'product` p 
		LEFT JOIN `'._DB_PREFIX_.'page` pa ON p.`id_product` = pa.`id_object`
		LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON (pt.`id_page_type` = pa.`id_page_type` AND pt.`name` = \'product.php\')
		LEFT JOIN `'._DB_PREFIX_.'page_viewed` pv ON pv.`id_page` = pa.`id_page`
		'.$this->_join.'
		WHERE p.`active` = 1 '.$this->_where);
		return isset($result['viewed']) ? $result['viewed'] : 0;
	}
	
	public function getTotalProductViewed()
	{
		return Db::getInstance()->getValue('
		SELECT COUNT(DISTINCT pa.`id_object`)
		FROM `'._DB_PREFIX_.'page_viewed` pv
		LEFT JOIN `'._DB_PREFIX_.'page` pa ON pv.`id_page` = pa.`id_page`
		LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = pa.`id_page_type`
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = pa.`id_object`
		'.$this->_join.'
		WHERE pt.`name` = \'product.php\'
		AND p.`active` = 1
		'.$this->_where);
	}
	
	public function getTotalBought()
	{
		$result = Db::getInstance()->getRow('
		SELECT SUM(od.`product_quantity`) AS bought
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON o.`id_order` = od.`id_order`
		'.$this->_join.'
		WHERE o.valid = 1
		'.$this->_where);
		return isset($result['bought']) ? $result['bought'] : 0;
	}
	
	public function getProductsNB($id_lang)
	{
		$precalc = Db::getInstance()->ExecuteS('
		SELECT p.`id_product`
		FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON o.`id_order` = od.`id_order`
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = od.`product_id`
		'.$this->_join.'
		WHERE o.valid = 1
		'.$this->_where.'
		AND p.`active` = 1
		GROUP BY p.`id_product`');
			
		$precalc2 = array();
		foreach ($precalc as $array)
			$precalc2[] = intval($array['id_product']);
		
		$result = Db::getInstance()->ExecuteS('
		SELECT p.id_product, pl.name, pl.link_rewrite
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.id_lang = '.intval($id_lang).')
		'.$this->_join.'
		WHERE p.`active` = 1
		'.(sizeof($precalc2) ? 'AND p.`id_product` NOT IN ('.implode(',', $precalc2).')' : '').'
		'.$this->_where);
		return array('total' => Db::getInstance()->NumRows(), 'result' => $result);
	}
	
	public function hookAdminStatsModules($params)
	{
		global $cookie;
		$categories = Category::getCategories(intval($cookie->id_lang), true, false);
		$productToken = Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee));
		$currency = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$link = new Link();
		$irow = 0;
		
		if ($id_category = intval(Tools::getValue('id_category')))
		{
			$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON cp.`id_product` = p.`id_product`';
			$this->_where = 'AND cp.`id_category` = '.$id_category;
		}
		
		$result1 = $this->getQuery1(true);
		$total = $result1['total'];
		$averagePrice = $result1['average_price'];
		$totalPictures = $result1['images'];
		$averagePictures = $total ? $totalPictures / $total : 0;
		
		$neverBought = $this->getProductsNB(intval($cookie->id_lang));
		$totalNB = $neverBought['total'];
		$productsNB = $neverBought['result'];
		
		$totalBought = $this->getTotalBought();
		$averagePurchase = $total ? ($totalBought / $total) : 0;
		
		$totalPageViewed = $this->getTotalPageViewed();
		$averageViewed = $total ? ($totalPageViewed / $total) : 0;		
		$conversion = number_format(floatval($totalPageViewed ? ($totalBought / $totalPageViewed) : 0), 2, '.', '');
		if ($conversionReverse = number_format(floatval($totalBought ? ($totalPageViewed / $totalBought) : 0), 2, '.', ''))
			$conversion .= ' (1 '.$this->l('purchase').' / '.$conversionReverse.' '.$this->l('visits').')';

		$totalNV = $total - $this->getTotalProductViewed();
		
		$html = '
		<script type="text/javascript" language="javascript">openCloseLayer(\'calendar\');</script>
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<label>
				'.$this->l('Choose a category').'
			</label>
			<div class="margin-form">
				<form action="" method="post" id="categoriesForm">
					<select name="id_category" onchange="$(\'#categoriesForm\').submit();">
						<option value="0">'.$this->l('All').'</option>';
		foreach ($categories as $category)
			$html .= '<option value="'.$category['id_category'].'"'.($id_category == $category['id_category'] ? ' selected="selected"' : '').'>'.$category['name'].'</option>';
		$html .= '
					</select>
				</form>
			</div>
			<div class="clear space"></div>
			<table>
				'.$this->returnLine($this->l('Products available:'), intval($total)).'
				'.$this->returnLine($this->l('Average price (base price):'), Tools::displayPrice($averagePrice, $currency)).'
				'.$this->returnLine($this->l('Product pages viewed:'), intval($totalPageViewed)).'
				'.$this->returnLine($this->l('Products bought:'), intval($totalBought)).'
				'.$this->returnLine($this->l('Average number of page visits:'), number_format(floatval($averageViewed), 2, '.', '')).'
				'.$this->returnLine($this->l('Average number of purchases:'), number_format(floatval($averagePurchase), 2, '.', '')).'
				'.$this->returnLine($this->l('Images available:'), intval($totalPictures)).'
				'.$this->returnLine($this->l('Average number of images:'), number_format(floatval($averagePictures), 2, '.', '')).'
				'.$this->returnLine($this->l('Products never viewed:'), intval($totalNV).' / '.intval($total)).'
				'.$this->returnLine('<a style="cursor : pointer" onclick="openCloseLayer(\'pnb\')">'.$this->l('Products never bought:').'</a>', intval($totalNB).' / '.intval($total)).'
				'.$this->returnLine($this->l('Conversion rate*:'), $conversion).'
			</table>
			<div style="margin-top: 20px;">
				<span style="color:red;font-weight:bold">*</span> 
				'.$this->l('Average conversion rate for the product page. It is possible to purchase a product without viewing the product page, so this rate can be greater than 1.').'
			</div>
		</fieldset>';
		
		if (sizeof($productsNB) AND sizeof($productsNB) < 50)
		{
			$html .= '
			<fieldset class="width3 space"><legend><img src="../modules/'.$this->name.'/basket_delete.png" /> '.$this->l('Products never bought').'</legend>
				<table cellpadding="0" cellspacing="0" class="table">
					<tr><th>'.$this->l('ID').'</th><th>'.$this->l('Name').'</th><th>'.$this->l('Edit / View').'</th></tr>';
			foreach ($productsNB as $product)
				$html .= '
					<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>
						<td>'.$product['id_product'].'</td>
						<td style="width: 400px;">'.$product['name'].'</td>
						<td style="text-align: right">
							<a href="index.php?tab=AdminCatalog&id_product='.$product['id_product'].'&addproduct&token='.$productToken.'" target="_blank"><img src="../modules/'.$this->name.'/page_edit.png" /></a>
							<a href="'.$link->getProductLink($product['id_product'], $product['link_rewrite']).'" target="_blank"><img src="../modules/'.$this->name.'/application_home.png" /></a>
						</td>
					</tr>';
			$html .= '
				</table>
			</fieldset>';
		}
		return $html;
	}
	
	private function returnLine($label, $data)
	{
		return '<tr><td>'.$label.'</td><td style="color:green;font-weight:bold;padding-left:20px;">'.$data.'</td></tr>';
	}
}

?>
