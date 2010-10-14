<?php

class AdminStockMvt extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'stock_mvt';
	 	$this->className = 'StockMvt';
	 	$this->edit = false;
		$this->delete = false;
		$this->view = true;
				
		$this->fieldsDisplay = array(
		'id_stock_mvt' => array('title' => $this->l('ID'), 'width' => 40),
		'product_name' => array('title' => $this->l('Product Name'), 'width' => 250, 'havingFilter' => true),
		'quantity' => array('title' => $this->l('Quantity'), 'width' => 40),
		'reason' => array('title' => $this->l('Reason'), 'width' => 250),
		'id_order' => array('title' => $this->l('ID Order'), 'width' => 40),
		'employee' => array('title' => $this->l('Employee'), 'width' => 100, 'havingFilter' => true),
		);
		
		global $cookie;
		
		$this->_select = 'CONCAT(pl.name, \' \', GROUP_CONCAT(IFNULL(al.name, \'\'), \'\')) product_name, CONCAT(e.lastname, \' \', e.firstname) employee, mrl.name reason';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (a.id_product = pl.id_product AND pl.id_lang = '.(int)$cookie->id_lang.')
							LEFT JOIN `'._DB_PREFIX_.'stock_mvt_reason_lang` mrl ON (a.id_stock_mvt_reason = mrl.id_stock_mvt_reason AND mrl.id_lang = '.(int)$cookie->id_lang.')
							LEFT JOIN `'._DB_PREFIX_.'employee` e ON (e.id_employee = a.id_employee)
							LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)
							LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.id_attribute = pac.id_attribute AND al.id_lang = '.(int)$cookie->id_lang.')';
		$this->_group = 'GROUP BY a.id_stock_mvt';
		parent::__construct();
	}

	public function postProcess()
	{
		global $cookie;
		if (Tools::isSubmit('rebuildStock'))
			StockMvt::addMissingMvt((int)$cookie->id_employee, false);
		return parent::postProcess();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		parent::displayForm();

		$obj = $this->loadObject(true);
		$dl = 'name';
		echo '<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/search.gif" />'.$this->l('Stock Movement').'</legend>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="40" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $dl, 'name');
		echo '</div><div class="clear space">&nbsp;</div>';
		echo 	'<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
			</fieldset>
		</form>';
	}
	
	public function viewstock_mvt()
	{
		global $cookie;
		
		$stockMvt = new StockMvt((int)Tools::getValue('id_stock_mvt'));
		$product = new Product((int)$stockMvt->id_product, true,  (int)$cookie->id_lang);
		$movements = $product->getStockMvts((int)$cookie->id_lang);

			echo '<h2>'.$this->l('Stock Movements for').' '.$product->name.'</h2>
			<table cellspacing="0" cellpadding="0" class="table widthfull">
				<tr><th>ID</th><th>Product Name</th><th>Quantity</th><th>Reason</th><th>Employee</th><th>Order</th><th>Date</th>
				</tr>';
			$irow = 0;
			foreach ($movements AS $k => $mvt)
			{
				echo '
				<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
					<td>'.$mvt['id_stock_mvt'].'</td>
					<td>'.$mvt['product_name'].'</td>
					<td>'.$mvt['quantity'].'</td>
					<td>'.$mvt['reason'].'</td>
					<td>'.$mvt['employee'].'</td>
					<td>#'.$mvt['id_order'].'</td>
					<td>'.Tools::displayDate($mvt['date_add'], intval($cookie->id_lang)).'</td>
				</tr>';
			}
			echo '</table>';
	}
	
	public function display()
	{
		global $currentIndex, $cookie;
		
		$old_post = false;
		
		if (!isset($_GET['addstock_mvt_reason']) AND (Tools::isSubmit('submitAddstock_mvt_reason') OR !Tools::getValue('id_stock_mvt_reason')))
		{
			$old_post = $_POST;
			parent::display();
			if (!isset($_GET['view'.$this->table]))
				echo '<fieldset>
						<form method="post" action="'.$currentIndex.'&token='.$this->token.'&rebuildMvt=1">
						<label for="stock_rebuild">'.$this->l('Calculate the movement of inventory missing').'</label>
						<input class="button" type="submit" name="rebuildStock" value="'.$this->l('Submit').'" />
						</form>
				</fieldset>';
		}
		if (isset($_GET['view'.$this->table]))
			return;
		if ($old_post)
			$_POST = $old_post;

	 	$this->table = 'stock_mvt_reason';
	 	$this->className = 'StockMvtReason';
	 	$this->identifier = 'id_stock_mvt_reason';
	 	$this->edit = true;
		$this->delete = true;
		$this->lang = true;
		$this->add = true;
		$this->view = false;
		$this->_listSkipDelete = array(1,2);
		

		$this->_defaultOrderBy = $this->identifier;
		$this->fieldsDisplay = array('id_stock_mvt_reason' => array('title' => $this->l('ID'), 'width' => 40),
												'name' => array('title' => $this->l('Name'), 'width' => 500));
		
		$reasons = StockMvtReason::getStockMvtReasons((int)$cookie->id_lang);
		$this->_fieldsOptions = array('PS_STOCK_MVT_REASON_DEFAULT' => array('title' => $this->l('Default Stock Movement reason:'), 
												'cast' => 'intval', 
												'type' => 'select', 
												'list' => $reasons, 
												'identifier' => 'id_stock_mvt_reason'));
		
		unset($this->_select, $this->_join, $this->_group, $this->_filterHaving, $this->_filter);
		$this->postProcess();
		return parent::display();
	}
}

?>
