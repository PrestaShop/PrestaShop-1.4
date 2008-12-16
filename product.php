<?php
include(dirname(__FILE__).'/config/config.inc.php');

function pictureUpload(Product $product, Cart $cart)
{
	global $errors;

	if (!$fieldIds = $product->getCustomizationFieldIds())
		return false;
	$authorizedFileFields = array();
	foreach ($fieldIds AS $fieldId)
		if ($fieldId['type'] == _CUSTOMIZE_FILE_)
			$authorizedFileFields[intval($fieldId['id_customization_field'])] = 'file'.intval($fieldId['id_customization_field']);
	$indexes = array_flip($authorizedFileFields); 
	foreach ($_FILES AS $fieldName => $file)
		if (in_array($fieldName, $authorizedFileFields) AND isset($file['tmp_name']) AND !empty($file['tmp_name']))
		{
			$fileName = md5(uniqid(rand(), true));
			if ($error = checkImage($file, intval(Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))))
				$errors[] = $error;
			/* Original file */
			elseif (!imageResize($file, _PS_PROD_PIC_DIR_.$fileName))
				$errors[] = Tools::displayError('An error occurred during the image upload.');
			/* A smaller one */
			elseif (!imageResize($file, _PS_PROD_PIC_DIR_.$fileName.'_small', intval(Configuration::get('PS_PRODUCT_PICTURE_WIDTH')), intval(Configuration::get('PS_PRODUCT_PICTURE_HEIGHT'))))
				$errors[] = Tools::displayError('An error occurred during the image upload.');
			elseif (!chmod(_PS_PROD_PIC_DIR_.$fileName, 0777) OR !chmod(_PS_PROD_PIC_DIR_.$fileName.'_small', 0777))
				$errors[] = Tools::displayError('An error occurred during the image upload.');
			else
				$cart->addPictureToProduct(intval($product->id), $indexes[$fieldName], $fileName);
		}
	return true;
}

function textRecord(Product $product, Cart $cart)
{
	global $errors;

	if (!$fieldIds = $product->getCustomizationFieldIds())
		return false;
	$authorizedTextFields = array();
	foreach ($fieldIds AS $fieldId)
		if ($fieldId['type'] == _CUSTOMIZE_TEXTFIELD_)
			$authorizedTextFields[intval($fieldId['id_customization_field'])] = 'textField'.intval($fieldId['id_customization_field']);
	$indexes = array_flip($authorizedTextFields);
	foreach ($_POST AS $fieldName => $value)
		if (in_array($fieldName, $authorizedTextFields) AND !empty($value))
		{
			if (!Validate::isMessage($value))
				$errors[] = Tools::displayError('Invalid message');
			else
				$cart->addTextFieldToProduct(intval($product->id), $indexes[$fieldName], htmlentities($value, ENT_COMPAT, 'UTF-8'));
		}
		elseif (in_array($fieldName, $authorizedTextFields) AND empty($value))
			$cart->deleteTextFieldFromProduct(intval($product->id), $indexes[$fieldName]);
}

function formTargetFormat()
{
	global $smarty;
	$customizationFormTarget = Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
	foreach ($_GET AS $field => $value)
		if (strncmp($field, 'group_', 6) == 0)
			$customizationFormTarget = preg_replace('/&group_([[:digit:]]+)=([[:digit:]]+)/', '', $customizationFormTarget);
	if (isset($_POST['quantityBackup']))
		$smarty->assign('quantityBackup', intval($_POST['quantityBackup']));
	$smarty->assign('customizationFormTarget', $customizationFormTarget);
}

/* CSS ans JS files calls */
$css_files = array(__PS_BASE_URI__.'css/thickbox.css' => 'all');
$js_files = array(
	__PS_BASE_URI__.'js/jquery/thickbox-modified.js',
	__PS_BASE_URI__.'js/jquery/jquery.idTabs.modified.js',
	__PS_BASE_URI__.'js/jquery/jquery.scrollto.js',
	__PS_BASE_URI__.'js/jquery/jquery.serialScroll.js',
	_THEME_JS_DIR_.'tools.js',
	_THEME_JS_DIR_.'product.js');

global $errors;
$errors = array();
include_once(dirname(__FILE__).'/header.php');

if (!isset($_GET['id_product']) OR !Validate::isUnsignedId($_GET['id_product']))
	$errors[] = Tools::displayError('product not found');
else
{
	$cookie = new Cookie('ps');
	Tools::setCookieLanguage();
	$product = new Product(intval($_GET['id_product']), true, intval($cookie->id_lang));
	if (!Validate::isLoadedObject($product) OR !$product->active)
		$errors[] = Tools::displayError('product is no longer available');
	else
	{
		$smarty->assign('virtual', ProductDownload::getIdFromIdProduct(intval($product->id)));
	
		/* Product pictures management */
		require_once('images.inc.php');
		$smarty->assign('customizationFormTarget', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));
		if (Tools::isSubmit('submitCustomizedDatas'))
		{
			pictureUpload($product, $cart);
			textRecord($product, $cart);
			formTargetFormat();
		}
		elseif (isset($_GET['deletePicture']) AND !$cart->deletePictureToProduct(intval($product->id), intval(Tools::getValue('deletePicture'))))
			$errors[] = Tools::displayError('An error occured while deleting the selected picture');

		$files = $cookie->getFamily('pictures_'.intval($product->id));
		$textFields = $cookie->getFamily('textFields_'.intval($product->id));
		$smarty->assign(array(
			'pictures' => $files,
			'textFields' => $textFields));

		$productPriceWithTax = floatval($product->getPrice(true, NULL, 2));
		$productPriceWithoutEcoTax = floatval($productPriceWithTax - $product->ecotax);
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

		/* Features / Values */
		$features = $product->getFrontFeatures(intval($cookie->id_lang));
		
		/* Category */
		if (isset($_SERVER['HTTP_REFERER']) AND ereg('^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$', $_SERVER['HTTP_REFERER'], $regs) AND !strstr($_SERVER['HTTP_REFERER'], '.html'))
		{
			if (isset($regs[2]) AND is_numeric($regs[2]))
				$category = new Category(intval($regs[2]), intval($cookie->id_lang));
			elseif (isset($regs[5]) AND is_numeric($regs[5]))
				$category = new Category(intval($regs[5]), intval($cookie->id_lang));
		}
		else
		{
			$category = new Category($product->id_category_default, intval($cookie->id_lang));
		}
		if (isset($category) AND Validate::isLoadedObject($category))
		{
			$smarty->assign(array(
			'category' => $category,
			'subCategories' => $category->getSubCategories(intval($cookie->id_lang), true),
			'id_category_current' => intval($category->id),
			'id_category_parent' => intval($category->id_parent),
			'return_category_name' => Tools::safeOutput(Category::hideCategoryPosition($category->name))));
		}
		$smarty->assign(array(
			'return_link' => (isset($category->id) AND $category->id) ? Tools::safeOutput($link->getCategoryLink($category)) : 'javascript: history.back();',
			'path' => (isset($category->id) AND $category->id) ? Tools::getPath(intval($category->id), $product->name) : Tools::getPath(intval($product->id_category_default), $product->name)
		));

		/* /Quantity discount management */
		$smarty->assign(array(
			'quantity_discounts' => QuantityDiscount::getQuantityDiscounts(intval($product->id), $product->getPriceWithoutReduct()),
			'product' => $product,
			'product_manufacturer' => new Manufacturer(intval($product->id_manufacturer)),
			'token' => Tools::getToken(false),
			'productPriceWithoutEcoTax' => floatval($productPriceWithoutEcoTax),
			'features' => $features,
			'allow_oosp' => $product->isAvailableWhenOutOfStock(intval($product->out_of_stock)),
			'last_qties' =>  intval($configs['PS_LAST_QTIES']),
			'col_img_dir' => _PS_COL_IMG_DIR_,
			'HOOK_EXTRA_LEFT' => Module::hookExec('extraLeft'),
			'HOOK_EXTRA_RIGHT' => Module::hookExec('extraRight'),
			'HOOK_PRODUCT_OOS' => Hook::productOutOfStock($product),
			'HOOK_PRODUCT_FOOTER' => Hook::productFooter($product, $category),
			'HOOK_PRODUCT_ACTIONS' => Module::hookExec('productActions'),
			'HOOK_PRODUCT_TAB' =>  Module::hookExec('productTab'),
			'HOOK_PRODUCT_TAB_CONTENT' =>  Module::hookExec('productTabContent')));
		
		$images = $product->getImages(intval($cookie->id_lang));
		$productImages = array();
		foreach ($images AS $k => $image)
		{
			if ($image['cover'])
			{
				$smarty->assign('mainImage', $images[0]);
				$cover = $image;
				$cover['id_image'] = intval($product->id).'-'.$cover['id_image'];
				$cover['id_image_only'] = intval($image['id_image']);
			}
			$productImages[intval($image['id_image'])] = $image;
		}
		if (!isset($cover))
			$cover = array('id_image' => Language::getIsoById($cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');
		$size = Image::getSize('large');

		$smarty->assign(array(
			'cover' => $cover,
			'imgWidth' => intval($size['width']),
			'accessories' => $product->getAccessories(intval($cookie->id_lang))));
		if (sizeof($productImages))
			$smarty->assign('images', $productImages);

		/* Attributes / Groups & colors */
		$colors = array();
		$attributesGroups = $product->getAttributesGroups(intval($cookie->id_lang));
		
		if (Db::getInstance()->numRows())
		{
			foreach ($attributesGroups AS $k => $row)
			{
				/* Color management */
				if (isset($row['attribute_color']) AND $row['attribute_color'] AND $row['id_attribute_group'] == $product->id_color_default)
				{
					$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
					$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
				}

				$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
				$groups[$row['id_attribute_group']]['name'] = $row['public_group_name'];
				if ($row['default_on'])
					$groups[$row['id_attribute_group']]['default'] = intval($row['id_attribute']);
				if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
				$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += intval($row['quantity']);

				$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
				$combinations[$row['id_product_attribute']]['attributes'][] = intval($row['id_attribute']);
				$combinations[$row['id_product_attribute']]['price'] = floatval($row['price']);
				$combinations[$row['id_product_attribute']]['ecotax'] = floatval($row['ecotax']);
				$combinations[$row['id_product_attribute']]['weight'] = floatval($row['weight']);
				$combinations[$row['id_product_attribute']]['quantity'] = intval($row['quantity']);
				$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
				$combinations[$row['id_product_attribute']]['id_image'] = (($row['id_image'] != NULL) ? intval($row['id_image']) : -1);
			}
			//wash attributes list (if some attributes are unavailables and if allowed to wash it)
			if (Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
				foreach ($groups AS &$group)
					foreach ($group['attributes_quantity'] AS $key => &$quantity)
						if (!$quantity)
							unset($group['attributes'][$key]);
			
			foreach ($combinations AS $id_product_attribute => $comb)
			{
				$attributeList = '';
				foreach ($comb['attributes'] AS $id_attribute)
					$attributeList .= '\''.intval($id_attribute).'\',';
				$attributeList = rtrim($attributeList, ',');
				$combinations[$id_product_attribute]['list'] = $attributeList;
			}

			$smarty->assign(array(
				'groups' => $groups,
				'combinaisons' => $combinations, /* Kept for compatibility purpose only */
				'combinations' => $combinations,
				'colors' => (sizeof($colors) AND $product->id_color_default) ? $colors : false));
		}
		$smarty->assign(array(
			'no_tax' => Tax::excludeTaxeOption() OR !Tax::getApplicableTax(intval($product->id_tax), 1),
			'customizationFields' => $product->getCustomizationFields(intval($cookie->id_lang))
		));
	}
}
$smarty->assign(array(
	'ENT_NOQUOTES' => ENT_NOQUOTES,
	'outOfStockAllowed' => intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')),
	'displayPreTax' => intval(Configuration::get('PS_DISPLAY_WITHOUT_TAX')),
	'errors' => $errors,
	'categories' => Category::getHomeCategories(intval($cookie->id_lang)),
	'have_image' => Product::getCover(intval(Tools::getValue('id_product'))),
	'display_qties' => intval(Configuration::get('PS_DISPLAY_QTIES')),
	'display_ht' => !Tax::excludeTaxeOption()));

if (file_exists(_PS_THEME_DIR_.'thickbox.tpl'))
	$smarty->display(_PS_THEME_DIR_.'thickbox.tpl');

$smarty->assign(array('currencySign' => $currency->sign, 'currencyRate' => $currency->conversion_rate, 'currencyFormat' => $currency->format));
$smarty->display(_PS_THEME_DIR_.'product.tpl');

include(dirname(__FILE__).'/footer.php');

?>