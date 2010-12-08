<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

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
		$countries = Country::getCountries((int)($cookie->id_lang));
		foreach ($countries AS $country)
			$this->countriesArray[$country['id_country']] = $country['name'];
		$this->fieldsDisplayAddresses = array(
		'id_address' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'm!manufacturer_name' => array('title' => $this->l('Manufacturer'), 'width' => 100),
		'firstname' => array('title' => $this->l('First name'), 'width' => 80),
		'lastname' => array('title' => $this->l('Last name'), 'width' => 100, 'filter_key' => 'a!name'),
		'postcode' => array('title' => $this->l('Postcode/ Zip Code'), 'align' => 'right', 'width' => 50),
		'city' => array('title' => $this->l('City'), 'width' => 150),
		'country' => array('title' => $this->l('Country'), 'width' => 100, 'type' => 'select', 'select' => $this->countriesArray, 'filter_key' => 'cl!id_country'));
		$this->_includeTabTitle = array($this->l('Manufacturers addresses'));
		$this->_joinAddresses = 'LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON 
		(cl.`id_country` = a.`id_country` AND cl.`id_lang` = '.(int)($cookie->id_lang).') ';
	 	$this->_joinAddresses .= 'LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (a.`id_manufacturer` = m.`id_manufacturer`)';
		$this->_selectAddresses = 'cl.`name` as country, m.`name` AS manufacturer_name';
		$this->_includeTab = array('Addresses' => array('addressType' => 'manufacturer', 'fieldsDisplay' => $this->fieldsDisplayAddresses, '_join' => $this->_joinAddresses, '_select' => $this->_selectAddresses));
		$this->view = true;
		$this->_select = 'COUNT(`id_product`) AS `products`, (SELECT COUNT(ad.`id_manufacturer`) as `addresses` FROM `'._DB_PREFIX_.'address` ad WHERE ad.`id_manufacturer` = a.`id_manufacturer` AND ad.`deleted` = 0 GROUP BY ad.`id_manufacturer`) as `addresses`';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product` p ON (a.`id_manufacturer` = p.`id_manufacturer`)';
		$this->_joinCount = false;
		$this->_group = 'GROUP BY a.`id_manufacturer`';

		$this->fieldImageSettings = array('name' => 'logo', 'dir' => 'm');

		$this->fieldsDisplay = array(
			'id_manufacturer' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 200),
			'logo' => array('title' => $this->l('Logo'), 'align' => 'center', 'image' => 'm', 'orderby' => false, 'search' => false),
			'addresses' => array('title' => $this->l('Addresses'), 'align' => 'right', 'tmpTableFilter' => true, 'width' => 20),
			'products' => array('title' => $this->l('Products'), 'align' => 'right', 'tmpTableFilter' => true, 'width' => 20),
			'active' => array('title' => $this->l('Enabled'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
		);

		$countries = Country::getCountries((int)($cookie->id_lang));
		foreach ($countries AS $country)
			$this->countriesArray[$country['id_country']] = $country['name'];

		parent::__construct();
	}

	public function afterImageUpload()
	{
		/* Generate image with differents size */
		if (($id_manufacturer = (int)(Tools::getValue('id_manufacturer'))) AND isset($_FILES) AND count($_FILES) AND file_exists(_PS_MANU_IMG_DIR_.$id_manufacturer.'.jpg'))
		{
			$imagesTypes = ImageType::getImagesTypes('manufacturers');
			foreach ($imagesTypes AS $k => $imageType)
				imageResize(_PS_MANU_IMG_DIR_.$id_manufacturer.'.jpg', _PS_MANU_IMG_DIR_.$id_manufacturer.'-'.stripslashes($imageType['name']).'.jpg', (int)($imageType['width']), (int)($imageType['height']));
		}
	}

	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		parent::displayForm();
		
		$manufacturer = $this->loadObject(true);
		$langtags = 'cdesc2¤cdesc¤mmeta_title¤mmeta_keywords¤mmeta_description';

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($manufacturer->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$manufacturer->id.'" />' : '').'
			<fieldset style="width: 850px;">
				<legend><img src="../img/admin/manufacturers.gif" />'.$this->l('Manufacturers').'</legend>
				<label>'.$this->l('Name').'</label>
				<div class="margin-form">
					<input type="text" size="40" name="name" value="'.htmlentities(Tools::getValue('name', $manufacturer->name), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>';

		echo '<br class="clear" /><label>'.$this->l('Short description').'</label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '
							<div id="cdesc2_'.$language['id_lang'].'" style="float: left;'.($language['id_lang'] != $this->_defaultFormLanguage ? 'display:none;' : '').'">
								<textarea class="rte" cols="48" rows="5" id="short_description_'.$language['id_lang'].'" name="short_description_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($manufacturer, 'short_description', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
							</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $langtags, 'cdesc2');
		echo '</div>';
				
		echo '<br class="clear" /><br /><br /><label>'.$this->l('Description').'</label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '
							<div id="cdesc_'.$language['id_lang'].'" style="float: left;'.($language['id_lang'] != $this->_defaultFormLanguage ? 'display:none;' : '').'">
								<textarea class="rte" cols="48" rows="10" id="description_'.$language['id_lang'].'" name="description_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($manufacturer, 'description', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
							</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $langtags, 'cdesc');
		echo '</div>';
		
		// TinyMCE
		global $cookie;
		$iso = Language::getIsoById((int)($cookie->id_lang));
		echo '
					<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						// Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
						content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
						document_base_url : "'.__PS_BASE_URI__.'",
						width: "600",
						height: "auto",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert,ajaxfilemanager",
						file_browser_callback : "ajaxfilemanager",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
						
					});
					function ajaxfilemanager(field_name, url, type, win) {
						var ajaxfilemanagerurl = "'.dirname($_SERVER["PHP_SELF"]).'/ajaxfilemanager/ajaxfilemanager.php";
						switch (type) {
							case "image":
								break;
							case "media":
								break;
							case "flash": 
								break;
							case "file":
								break;
							default:
								return false;
					}
		            tinyMCE.activeEditor.windowManager.open({
		                url: "'.dirname($_SERVER["PHP_SELF"]).'/ajaxfilemanager/ajaxfilemanager.php",
		                width: 782,
		                height: 440,
		                inline : "yes",
		                close_previous : "no"
		            },{
		                window : win,
		                input : field_name
		            });
		}
	</script>';
		echo '<br style="clear:both;" /><br/><br/><label>'.$this->l('Logo').'</label>
				<div class="margin-form">';
					$this->displayImage($manufacturer->id, _PS_MANU_IMG_DIR_.$manufacturer->id.'.jpg', 350);
		echo '	<br /><input type="file" name="logo" />
					<p>'.$this->l('Upload manufacturer logo from your computer').'</p>
				</div>
				<label>'.$this->l('Meta title').'</label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '
					<div id="mmeta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_title_'.$language['id_lang'].'" id="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($manufacturer, 'meta_title', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $langtags, 'mmeta_title');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Meta description').'</label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '<div id="mmeta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_description_'.$language['id_lang'].'" id="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($manufacturer, 'meta_description', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $langtags, 'mmeta_description');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Meta keywords').'</label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '
					<div id="mmeta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_keywords_'.$language['id_lang'].'" id="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($manufacturer, 'meta_keywords', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $langtags, 'mmeta_keywords');
		echo '		<div class="clear"></div>
				</div>
				<label>'.$this->l('Enable:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.($this->getFieldValue($manufacturer, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.(!$this->getFieldValue($manufacturer, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
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

		$products = $manufacturer->getProductsLite((int)($cookie->id_lang));
		$addresses = $manufacturer->getAddresses((int)($cookie->id_lang));
		
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
			$product = new Product($product['id_product'], false, (int)($cookie->id_lang));
			echo '<hr />';
			if (!$product->hasAttributes())
			{
				echo '
				<div style="float:right;">
					<a href="?tab=AdminCatalog&id_product='.$product->id.'&updateproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)).'" class="button">'.$this->l('Edit').'</a>
					<a href="?tab=AdminCatalog&id_product='.$product->id.'&deleteproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)).'" class="button" onclick="return confirm(\''.$this->l('Delete item #', __CLASS__, TRUE).$product->id.' ?\');">'.$this->l('Delete').'</a>
				</div>
				<table border="0" cellpadding="0" cellspacing="0" class="table width3">
					<tr>
						<th>'.$product->name.'</th>
						'.(!empty($product->reference) ? '<th width="150">'.$this->l('Ref:').' '.$product->reference.'</th>' : '').'
						'.(!empty($product->ean13) ? '<th width="120">'.$this->l('EAN13:').' '.$product->ean13.'</th>' : '').'
						'.(!empty($product->upc) ? '<th width="120">'.$this->l('UPC:').' '.$product->upc.'</th>' : '').'
						'.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<th class="right" width="50">'.$this->l('Qty:').' '.$product->quantity.'</th>' : '').'
					</tr>
				</table>';
			}
			else
			{
				echo '
				<div style="float:right;">
					<a href="?tab=AdminCatalog&id_product='.$product->id.'&updateproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)).'" class="button">'.$this->l('Edit').'</a>
					<a href="?tab=AdminCatalog&id_product='.$product->id.'&deleteproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)).'" class="button" onclick="return confirm(\''.$this->l('Delete item #', __CLASS__, TRUE).$product->id.' ?\');">'.$this->l('Delete').'</a>
				</div>
				<h3><a href="?tab=AdminCatalog&id_product='.$product->id.'&updateproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)).'">'.$product->name.'</a></h3>
				<table border="0" cellpadding="0" cellspacing="0" class="table" style="width: 600px;">
					<tr>
	                    <th>'.$this->l('Attribute name').'</th>
	                    <th width="80">'.$this->l('Reference').'</th>
	                    <th width="80">'.$this->l('EAN13').'</th>
						<th width="80">'.$this->l('UPC').'</th>
	                   '.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<th class="right" width="40">'.$this->l('Quantity').'</th>' : '').'
                	</tr>';
			     	/* Build attributes combinaisons */
				$combinaisons = $product->getAttributeCombinaisons((int)($cookie->id_lang));
				foreach ($combinaisons AS $k => $combinaison)
				{
					$combArray[$combinaison['id_product_attribute']]['reference'] = $combinaison['reference'];
					$combArray[$combinaison['id_product_attribute']]['ean13'] = $combinaison['ean13'];
					$combArray[$combinaison['id_product_attribute']]['upc'] = $combinaison['upc'];
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
						'.(Configuration::get('PS_STOCK_MANAGEMENT') ? '<td>'.$product_attribute['ean13'].'</td><td>'.$product_attribute['upc'].'</td>' : '').'
						<td class="right">'.$product_attribute['quantity'].'</td>
					</tr>';
				}
				unset($combArray);
				echo '</table>';
			}
		}
	}
}
