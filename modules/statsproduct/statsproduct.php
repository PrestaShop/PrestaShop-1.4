<?php

class StatsProduct extends ModuleGraph
{
    private $_html = '';
	private $_query = '';
	private $_option = 0;
	private $_id_product = 0;

    function __construct()
    {
        $this->name = 'statsproduct';
        $this->tab = 'Stats';
        $this->version = 1.0;

        parent::__construct();
		
        $this->displayName = $this->l('Product details');
        $this->description = $this->l('Get detailed statistics for each product');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
	
	public function getTotalBought($id_product)
	{
		$dateBetween = ModuleGraph::getDateBetween();
		$result = Db::getInstance()->getRow('
		SELECT SUM(od.`product_quantity`) AS total
		FROM `'._DB_PREFIX_.'order_detail` od
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_order` = od.`id_order`
		WHERE od.`product_id` = '.intval($id_product).'
		AND o.valid = 1
		AND LEFT(o.`date_add`, 10) BETWEEN '.$dateBetween.'');
		return isset($result['total']) ? $result['total'] : 0;
	}
	
	public function getTotalViewed($id_product)
	{
		$dateBetween = ModuleGraph::getDateBetween();
		$result = Db::getInstance()->getRow('
		SELECT SUM(pv.`counter`) AS total
		FROM `'._DB_PREFIX_.'page_viewed` pv
		LEFT JOIN `'._DB_PREFIX_.'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
		LEFT JOIN `'._DB_PREFIX_.'page` p ON pv.`id_page` = p.`id_page`
		LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
		WHERE pt.`name` = \'product.php\'
		AND p.`id_object` = '.intval($id_product).'
		AND LEFT(dr.`time_start`, 10) BETWEEN '.$dateBetween.'
		AND LEFT(dr.`time_end`, 10) BETWEEN '.$dateBetween.'');
		return isset($result['total']) ? $result['total'] : 0;
	}
	
	private function getProducts()
	{
		global $cookie;
		return Db::getInstance()->ExecuteS('
		SELECT p.`id_product`, pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON p.`id_product` = pl.`id_product`
		'.(Tools::getValue('id_category') ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`' : '').'
		WHERE pl.`id_lang` = '.intval($cookie->id_lang).'
		'.(Tools::getValue('id_category') ? 'AND cp.id_category = '.intval(Tools::getValue('id_category')) : '').'
		ORDER BY pl.`name`');
	}
	
	public function hookAdminStatsModules($params)
	{
		global $cookie, $currentIndex;
		$id_category = intval(Tools::getValue('id_category'));
		
		$this->_html = '<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>';
		if ($id_product = intval(Tools::getValue('id_product')))
		{
			$product = new Product($id_product, false, intval($cookie->id_lang));
			$totalBought = $this->getTotalBought($product->id);
			$totalViewed = $this->getTotalViewed($product->id);
			$this->_html .= '<h3>'.$product->name.' - '.$this->l('Details').'</h3>
			<p>'.$this->l('Conversion rate:').' '.number_format($totalViewed ? $totalBought / $totalViewed : 0, 2).'</p>
			<p>'.$this->l('Total bought:').' '.$totalBought.'</p>
			<center>'.ModuleGraph::engine(array('type' => 'line', 'option' => '1-'.$id_product)).'</center>
			<p>'.$this->l('Total viewed:').' '.$totalViewed.'</p>
			<center>'.ModuleGraph::engine(array('type' => 'line', 'option' => '2-'.$id_product)).'</center>';
			if ($product->hasAttributes() AND $totalBought)
				$this->_html .= '<h3 class="space">'.$this->l('Attribute sales distribution').'</h3><center>'.ModuleGraph::engine(array('type' => 'pie', 'option' => '3-'.$id_product)).'</center>';
		}
		else
		{
			$categories = Category::getCategories(intval($cookie->id_lang), true, false);
			$this->_html .= '
			<label>'.$this->l('Choose a category').'</label>
			<div class="margin-form">
				<form action="" method="post" id="categoriesForm">
					<select name="id_category" onchange="$(\'#categoriesForm\').submit();">
						<option value="0">'.$this->l('All').'</option>';
			foreach ($categories as $category)
				$this->_html .= '<option value="'.$category['id_category'].'"'.($id_category == $category['id_category'] ? ' selected="selected"' : '').'>'.$category['name'].'</option>';
			$this->_html .= '
					</select>
				</form>
			</div>
			<div class="clear space"></div>
			'.$this->l('Click on a product to access its statistics.').'
			<div class="clear space"></div>
			<h2>'.$this->l('Products available').'</h2>
			<ul>';
			foreach ($this->getProducts() AS $product)
				$this->_html .= '<li><a href="'.$currentIndex.'&token='.Tools::getValue('token').'&module='.$this->name.'&id_product='.$product['id_product'].'">'.$product['name'].'</a></li>';
			$this->_html .= '</ul>';
		}
		
		$this->_html .= '</fieldset><br />
		<fieldset class="width3"><legend><img src="../img/admin/comment.gif" /> '.$this->l('Guide').'</legend>
		<h2>'.$this->l('Number of purchases compared to number of viewings').'</h2>
			<p>
				'.$this->l('After choosing a category and selecting a product available in this category, some graphs appear. Then, you can analyze them.').'
				<ul>
					<li class="bullet">'.$this->l('If you notice that a product is successful, very purchased, but also little viewed: you should put it more prominently on your webshop front-office.').'</li>
					<li class="bullet">'.$this->l('On the other hand, if a product knows a lot of viewings but is not really purchased: we advise you to check or modify this product\'s information, description and photography again.').'
					</li>
				</ul>
			</p>
		</fieldset>';
		return $this->_html;
	}
	
	public function setOption($option, $layers = 1)
	{
		list($this->_option, $this->_id_product) = explode('-', $option);
		$dateBetween = $this->getDate();
		switch ($this->_option)
		{
			case 1:
				$this->_query = '
					SELECT o.`date_add`, SUM(od.`product_quantity`) AS total
					FROM `'._DB_PREFIX_.'order_detail` od
					LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_order` = od.`id_order`
					WHERE od.`product_id` = '.intval($this->_id_product).'
					AND o.valid = 1
					AND LEFT(o.`date_add`, 10) BETWEEN '.$dateBetween.'
					GROUP BY o.`date_add`';
				$this->_titles['main'] = $this->l('Number of purchases');
				break;
			case 2:
				$this->_query = '
					SELECT dr.`time_start` AS date_add, SUM(pv.`counter`) AS total
					FROM `'._DB_PREFIX_.'page_viewed` pv
					LEFT JOIN `'._DB_PREFIX_.'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
					LEFT JOIN `'._DB_PREFIX_.'page` p ON pv.`id_page` = p.`id_page`
					LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
					WHERE pt.`name` = \'product.php\'
					AND p.`id_object` = '.intval($this->_id_product).'
					AND LEFT(dr.`time_start`, 10) BETWEEN '.$dateBetween.'
					AND LEFT(dr.`time_end`, 10) BETWEEN '.$dateBetween.'
					GROUP BY dr.`time_start`';
				$this->_titles['main'] = $this->l('Number of visits');
				break;
			case 3:
				$this->_query = '
					SELECT product_attribute_id, SUM(od.`product_quantity`) AS total
					FROM `'._DB_PREFIX_.'orders` o
					LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON o.`id_order` = od.`id_order`
					WHERE od.`product_id` = '.intval($this->_id_product).'
					AND o.valid = 1
					AND LEFT(o.`date_add`, 10) BETWEEN '.$dateBetween.'
					GROUP BY od.`product_attribute_id`';
				$this->_titles['main'] = $this->l('Attributes');
				break;
		}
	}
	
	protected function getData($layers)
	{
		if ($this->_option != 3)
			$this->setDateGraph($layers, true);
		else
		{
			$product = new Product($this->_id_product, false, intval($this->getLang()));
			
			$combArray = array();
			$assocNames = array();
			$combinaisons = $product->getAttributeCombinaisons(intval($this->getLang()));
			foreach ($combinaisons AS $k => $combinaison)
				$combArray[$combinaison['id_product_attribute']][] = array('group' => $combinaison['group_name'], 'attr' => $combinaison['attribute_name']);
			foreach ($combArray AS $id_product_attribute => $product_attribute)
			{
				$list = '';
				foreach ($product_attribute AS $attribute)
					$list .= trim($attribute['group']).' - '.trim($attribute['attr']).', ';
				$list = rtrim($list, ', ');
				$assocNames[$id_product_attribute] = $list;
			}
		
			$result = Db::getInstance()->ExecuteS($this->_query);
			foreach ($result as $row)
			{
			    $this->_values[] = $row['total'];
			    $this->_legend[] = $assocNames[$row['product_attribute_id']];
			}
		}
	}
	
	protected function setYearValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query);
		foreach ($result AS $row)
		    $this->_values[intval(substr($row['date_add'], 5, 2))] += $row['total'];
	}
	
	protected function setMonthValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query);
		foreach ($result AS $row)
		    $this->_values[intval(substr($row['date_add'], 8, 2))] += $row['total'];
	}

	protected function setDayValues($layers)
	{
		$result = Db::getInstance()->ExecuteS($this->_query);
		foreach ($result AS $row)
		    $this->_values[intval(substr($row['date_add'], 11, 2))] += $row['total'];
	}
}


?>
