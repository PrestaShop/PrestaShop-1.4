<?php

/**
  * Manufacturers tab for admin panel, AdminManufacturers.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(realpath(PS_ADMIN_DIR.'/../').'/classes/AdminTab.php');

class AdminManufacturers extends AdminTab
{
	protected $maxImageSize = 200000;

	/** @var array countries list */
	private $countriesArray = array();

	public function __construct()
	{
		global $cookie;

		$this->table = 'manufacturer';
		$this->className = 'Manufacturer';
		$this->lang = false;
		$this->edit = true;
	 	$this->delete = true;
		
		// Sub tab addresses
		$countries = Country::getCountries(intval($cookie->id_lang));
		foreach ($countries AS $country)
			$this->countriesArray[$country['id_country']] = $country['name'];
		$this->fieldsDisplayAddresses = array(
		'id_address' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'm!manufacturer_name' => array('title' => $this->l('Manufacturer'), 'width' => 100),
		'firstname' => array('title' => $this->l('First name'), 'width' => 80),
		'lastname' => array('title' => $this->l('Last name'), 'width' => 100, 'filter_key' => 'a!name'),
		'postcode' => array('title' => $this->l('Post/Zip code'), 'align' => 'right', 'width' => 50),
		'city' => array('title' => $this->l('City'), 'width' => 150),
		'country' => array('title' => $this->l('Country'), 'width' => 100, 'type' => 'select', 'select' => $this->countriesArray, 'filter_key' => 'cl!id_country'));
		$this->_includeTabTitle = array($this->l('Manufacturers addresses'));
		$this->_joinAddresses = 'LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON 
		(cl.`id_country` = a.`id_country` AND cl.`id_lang` = '.intval($cookie->id_lang).') ';
	 	$this->_joinAddresses .= 'LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (a.`id_manufacturer` = m.`id_manufacturer`)';
		$this->_selectAddresses = 'cl.`name` as country, m.`name` AS manufacturer_name';
		$this->_includeTab = array('Addresses' => array('addressType' => 'manufacturer', 'fieldsDisplay' => $this->fieldsDisplayAddresses, '_join' => $this->_joinAddresses, '_select' => $this->_selectAddresses));
		
		$this->view = true;
		$this->_select = 'COUNT(`id_product`) AS `products`, (SELECT COUNT(ad.`id_manufacturer`) as `addresses` FROM `'._DB_PREFIX_.'address` ad WHERE ad.`id_manufacturer` = a.`id_manufacturer` GROUP BY ad.`id_manufacturer`) as `addresses`';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product` p ON (a.`id_manufacturer` = p.`id_manufacturer`)';
		$this->_joinCount = false;
		$this->_group = 'GROUP BY a.`id_manufacturer`';

		$this->fieldImageSettings = array('name' => 'logo', 'dir' => 'm');

		$this->fieldsDisplay = array(
			'id_manufacturer' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 200),
			'logo' => array('title' => $this->l('Logo'), 'align' => 'center', 'image' => 'm', 'orderby' => false, 'search' => false),
			'addresses' => array('title' => $this->l('Addresses'), 'align' => 'right', 'tmpTableFilter' => true, 'width' => 20),
			'products' => array('title' => $this->l('Products'), 'align' => 'right', 'tmpTableFilter' => true, 'width' => 20)
		);

		$countries = Country::getCountries(intval($cookie->id_lang));
		foreach ($countries AS $country)
			$this->countriesArray[$country['id_country']] = $country['name'];

		parent::__construct();
	}

	public function afterImageUpload()
	{
		/* Generate image with differents size */
		if (($id_manufacturer = intval(Tools::getValue('id_manufacturer'))) AND isset($_FILES) AND count($_FILES) AND file_exists(_PS_MANU_IMG_DIR_.$id_manufacturer.'.jpg'))
		{
			$imagesTypes = ImageType::getImagesTypes('manufacturers');
			foreach ($imagesTypes AS $k => $imageType)
				imageResize(_PS_MANU_IMG_DIR_.$id_manufacturer.'.jpg', _PS_MANU_IMG_DIR_.$id_manufacturer.'-'.stripslashes($imageType['name']).'.jpg', intval($imageType['width']), intval($imageType['height']));
		}
	}

	public function displayForm()
	{
		global $currentIndex, $cookie;
		$divLangName = 'cdesc¤cdesc2';
		$manufacturer = $this->loadObject(true);
		$this->displayImage($manufacturer->id, _PS_MANU_IMG_DIR_.$manufacturer->id.'.jpg', 350);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$langtags = 'mmeta_title¤mmeta_keywords¤mmeta_description';

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width3">
		'.($manufacturer->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$manufacturer->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/manufacturers.gif" />'.$this->l('Manufacturers').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">
					<input type="text" size="40" name="name" value="'.htmlentities(Tools::getValue('name', $manufacturer->name), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>';

		echo '<br class="clear" /><br /><br /><label>'.$this->l('Short description:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
							<div id="cdesc2_'.$language['id_lang'].'" style="float: left;">
								<textarea cols="38" rows="5" id="short_description_'.$language['id_lang'].'" name="short_description_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($manufacturer, 'short_description', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cdesc2');
		echo '</div>';
				
		echo '<br class="clear" /><br /><br /><label>'.$this->l('Description:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
							<div id="cdesc_'.$language['id_lang'].'" style="float: left;">
								<textarea cols="38" rows="10" id="description_'.$language['id_lang'].'" name="description_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($manufacturer, 'description', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cdesc');
		echo '</div>';

			echo '	
			<script type="text/javascript" src="../js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript">
				tinyMCE.init({
					language : "';
		$iso = Language::getIsoById(intval($cookie->id_lang));
		echo ((!file_exists(PS_ADMIN_DIR.'/../js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js')) ? 'en' : $iso).'",
					mode : "textareas",
					theme : "advanced",
					theme_advanced_buttons1 : "bold, italic, underline, fontselect, fontsizeselect",
					theme_advanced_buttons2 : "forecolor, backcolor, separator, justifyleft, justifycenter, justifyright, justifyfull, separator, bullist, numlist, separator, undo, redo",
					theme_advanced_buttons3 : "preview, code, tablecontrols, pastetext, pasteword, selectall, link, unlink, advhr",
					paste_create_paragraphs : false,
					paste_create_linebreaks : false,
					paste_use_dialog : true,
					paste_auto_cleanup_on_paste : true,
					paste_convert_middot_lists : false,
					paste_unindented_list_class : "unindentedList",
					paste_convert_headers_to_strong : true,
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					plugins : "advhr, advlink, cleanup, paste, preview, table",
					cleanup : true,
					entities : "",
					entity_encoding : "named",
					extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],object[classid|codebase|width|height],param[name|value],embed[src|quality|width|height|type|pluginspage|bgcolor|allowFullScreen],style[type|id]"
				});';
		foreach ($languages as $language)
			if ($language['id_lang'] != $defaultLanguage)
				echo '
						getE(\'cdesc_'.$language['id_lang'].'\').style.display = \'none\';
						getE(\'cdesc_'.$language['id_lang'].'\').rows = 38;
						getE(\'cdesc_'.$language['id_lang'].'\').cols = 8;
						getE(\'cdesc2_'.$language['id_lang'].'\').style.display = \'none\';
						getE(\'cdesc2_'.$language['id_lang'].'\').rows = 5;
						getE(\'cdesc2_'.$language['id_lang'].'\').cols = 8;';
		echo '</script>';		
		echo '<br style="clear:both;" /><br/><br/><label>'.$this->l('Logo:').' </label>
				<div class="margin-form">
					<input type="file" name="logo" />
					<p>'.$this->l('Upload manufacturer logo from your computer').'</p>
				</div>
				<label>'.$this->l('Meta title:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="mmeta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_title_'.$language['id_lang'].'" id="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($manufacturer, 'meta_title', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $langtags, 'mmeta_title');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Meta description:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '<div id="mmeta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_description_'.$language['id_lang'].'" id="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($manufacturer, 'meta_description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>';
		$this->displayFlags($languages, $defaultLanguage, $langtags, 'mmeta_description');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Meta keywords:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="mmeta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_keywords_'.$language['id_lang'].'" id="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($manufacturer, 'meta_keywords', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $langtags, 'mmeta_keywords');
		echo '		<div class="clear"></div>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	public function viewmanufacturer()
	{
		global $cookie;
		$manufacturer = $this->loadObject();
		echo '<h2>'.$manufacturer->name.'</h2>';

		$products = $manufacturer->getProductsLite(intval($cookie->id_lang));
		$addresses = $manufacturer->getAddresses(intval($cookie->id_lang));
		
		echo '<h3>'.$this->l('Total addresses:').' '.sizeof($addresses).'</h3>';
		echo '<hr />';
		foreach ($addresses AS $addresse)
			echo '
				<h3></h3>
				<table border="0" cellpadding="0" cellspacing="0" class="table" style="width: 600px;">
					<tr>
						<th><b>'.$addresse['firstname'].' '.$addresse['lastname'].'</b></th>
					</tr>
					<tr>
						<td>
							<div style="padding:5px; float:left; width:350px;">
								'.$addresse['address1'].'<br />
								'.($addresse['address2'] ? $addresse['address2'].'<br />' : '').'
								'.$addresse['postcode'].' '.$addresse['city'].'<br />
								'.($addresse['state'] ? $addresse['state'].'<br />' : '').'
								<b>'.$addresse['country'].'</b><br />
								</div>
							<div style="padding:5px; float:left;">
								'.($addresse['phone'] ? $addresse['phone'].'<br />' : '').'
								'.($addresse['phone_mobile'] ? $addresse['phone_mobile'].'<br />' : '').'
							</div>
							'.($addresse['other'] ? '<div style="padding:5px; clear:both;"><br /><i>'.$addresse['other'].'</i></div>' : '').'
						</td>
					</tr>
				</table>';
		if (!sizeof($addresses))
			echo 'No address for this manufacturer.';
		echo '<br /><br />';
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
						<th>'.$product->name.'</th>
						'.(!empty($product->reference) ? '<th width="150">'.$this->l('Ref:').' '.$product->reference.'</th>' : '').'
						'.(!empty($product->ean13) ? '<th width="120">'.$this->l('EAN13:').' '.$product->ean13.'</th>' : '').'
						'.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<th class="right" width="50">'.$this->l('Qty:').' '.$product->quantity.'</th>' : '').'
					</tr>
				</table>';
			}
			else
			{
				echo '
				<h3>'.$product->name.'</h3>
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
			}
		}
	}
}
