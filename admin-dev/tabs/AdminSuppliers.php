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

class AdminSuppliers extends AdminTab
{
	protected $maxImageSize = 200000;

	public function __construct()
	{
	 	$this->table = 'supplier';
	 	$this->className = 'Supplier';
	 	$this->view = true;
	 	$this->edit = true;
	 	$this->delete = true;
		$this->_select = 'COUNT(p.`id_product`) AS products';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product` p ON (a.`id_supplier` = p.`id_supplier`)';
		$this->_group = 'GROUP BY a.`id_supplier`';
		
 		$this->fieldImageSettings = array('name' => 'logo', 'dir' => 'su');
		
		$this->fieldsDisplay = array(
			'id_supplier' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 120),
			'logo' => array('title' => $this->l('Logo'), 'align' => 'center', 'image' => 'su', 'orderby' => false, 'search' => false),
			'products' => array('title' => $this->l('Number of products'), 'align' => 'right', 'filter_type' => 'int', 'tmpTableFilter' => true)
		);
	
		parent::__construct();
	}
	
	public function viewsupplier()
	{
		global $cookie;
		$supplier = $this->loadObject();		
		echo '<h2>'.$supplier->name.'</h2>';
		
		$products = $supplier->getProductsLite(intval($cookie->id_lang));
		echo '<h3>'.$this->l('Total products:').' '.sizeof($products).'</h3>';
		foreach ($products AS $product)
		{
			$product = new Product($product['id_product'], false, intval($cookie->id_lang));
			echo '<hr />';
			if (!$product->hasAttributes())
			{
				echo '
				<table border="0" cellpadding="0" cellspacing="0" class="table width3">
					<tr>
						<th><a href="index.php?tab=AdminCatalog&id_product='.$product->id.'&addproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" target="_blank">'.$product->name.'</a></th>
						'.(!empty($product->reference) ? '<th width="150">'.$this->l('Ref:').' '.$product->reference.'</th>' : '').'
						'.(!empty($product->ean13) ? '<th width="120">'.$this->l('EAN13:').' '.$product->ean13.'</th>' : '').'
						'.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<th class="right" width="50">'.$this->l('Qty:').' '.$product->quantity.'</th>' : '').'
					</tr>
				</table>';
			}
			else
			{
				echo '
				<h3><a href="index.php?tab=AdminCatalog&id_product='.$product->id.'&addproduct&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" target="_blank">'.$product->name.'</a></h3>
				<table>
					<tr>
						<td colspan="2">
		            		<table border="0" cellpadding="0" cellspacing="0" class="table" style="width: 600px;">
			                	<tr>
				                    <th>'.$this->l('Attribute name').'</th>
				                    <th width="80">'.$this->l('Reference').'</th>
				                    <th width="80">'.$this->l('EAN13').'</th>
				                   '.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<th class="right" width="40">'.$this->l('Quantity').'</th>' : '').'
			                	</tr>';
			     	/* Build attributes combinaisons */
				$combinaisons = $product->getAttributeCombinaisons(intval($cookie->id_lang));
				foreach ($combinaisons AS $k => $combinaison)
				{
					$combArray[$combinaison['id_product_attribute']]['reference'] = $combinaison['reference'];
					$combArray[$combinaison['id_product_attribute']]['ean13'] = $combinaison['ean13'];
					$combArray[$combinaison['id_product_attribute']]['quantity'] = $combinaison['quantity'];
					$combArray[$combinaison['id_product_attribute']]['attributes'][] = array($combinaison['group_name'], $combinaison['attribute_name'], $combinaison['id_attribute']);
				}
				$irow = 0;
				foreach ($combArray AS $id_product_attribute => $product_attribute)
				{
					$list = '';
					foreach ($product_attribute['attributes'] AS $attribute)
						$list .= $attribute[0].' - '.$attribute[1].', ';
					$list = rtrim($list, ', ');
					echo '
					<tr'.($irow++ % 2 ? ' class="alt_row"' : '').' >
						<td>'.stripslashes($list).'</td>
						<td>'.$product_attribute['reference'].'</td>
						'.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<td>'.$product_attribute['ean13'].'</td>' : '').'
						<td class="right">'.$product_attribute['quantity'].'</td>
					</tr>';
				}
				unset($combArray);
				echo '</table>';
				echo '</td></tr></table>';
			}
		}
	}
	
	public function displayForm()
	{
				global $currentIndex;
		
		$supplier = $this->loadObject(true);
		$this->displayImage($supplier->id, _PS_SUPP_IMG_DIR_.$supplier->id.'.jpg', 350);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();

		$langtags = 'description¤smeta_title¤smeta_keywords¤smeta_description';
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width3">
		'.($supplier->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$supplier->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/suppliers.gif" />'.$this->l('Suppliers').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">
					<input type="text" size="40" name="name" value="'.htmlentities(Tools::getValue('name', $supplier->name), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>
				<label>'.$this->l('Description:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($supplier, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both;">'.$this->l('Will appear in supplier list').'</p>
					</div>';							
				$this->displayFlags($languages, $defaultLanguage, $langtags, 'description');
		echo '	</div>
				<label>'.$this->l('Logo:').' </label>
				<div class="margin-form">
					<input type="file" name="logo" />
					<p>'.$this->l('Upload supplier logo from your computer').'</p>
				</div>
				<label>'.$this->l('Meta title:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="smeta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_title_'.$language['id_lang'].'" id="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($supplier, 'meta_title', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $langtags, 'smeta_title');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Meta description:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '<div id="smeta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_description_'.$language['id_lang'].'" id="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($supplier, 'meta_description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>';
		$this->displayFlags($languages, $defaultLanguage, $langtags, 'smeta_description');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Meta keywords:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="smeta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_keywords_'.$language['id_lang'].'" id="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($supplier, 'meta_keywords', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $langtags, 'smeta_keywords');
		echo '		<div class="clear"></div>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
	
	public function postProcess()
	{
		global $currentIndex;
		
		/* Generate image with differents size */
		if (($id_supplier = intval(Tools::getValue('id_supplier'))) AND isset($_FILES) AND count($_FILES) AND file_exists(_PS_SUPP_IMG_DIR_.$id_supplier.'.jpg'))
		{
			$imagesTypes = ImageType::getImagesTypes('suppliers');
			foreach ($imagesTypes AS $k => $imageType)
			{
				$file = _PS_SUPP_IMG_DIR_.$id_supplier.'.jpg';
				imageResize($file, _PS_SUPP_IMG_DIR_.$id_supplier.'-'.stripslashes($imageType['name']).'.jpg', intval($imageType['width']), intval($imageType['height']));
			}
		}
		parent::postProcess();
	}
}

?>