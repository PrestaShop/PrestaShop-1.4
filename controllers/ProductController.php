<?php

class ProductControllerCore extends FrontController
{	
	public function setMedia()
	{
		parent::setMedia();
		
		Tools::addCSS(_THEME_CSS_DIR_.'product.css');
		Tools::addCSS(_PS_CSS_DIR_.'thickbox.css', 'screen');
		Tools::addJS(array(
			_PS_JS_DIR_.'jquery/thickbox-modified.js',
			_PS_JS_DIR_.'jquery/jquery.idTabs.modified.js',
			_PS_JS_DIR_.'jquery/jquery.scrollto.js',
			_PS_JS_DIR_.'jquery/jquery.serialScroll.js',
			_THEME_JS_DIR_.'tools.js',
			_THEME_JS_DIR_.'product.js'
		));

		$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);
		if ($jqZoomEnabled)
		{
			Tools::addCSS(_PS_CSS_DIR_.'jqzoom.css', 'screen');
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.jqzoom.js');
		}
	}
	
	public function process()
	{
		parent::process();
		
		if (!$id_product = intval(Tools::getValue('id_product')) OR !Validate::isUnsignedId($id_product))
			$this->errors[] = Tools::displayError('product not found');
		else
		{
			$product = new Product($id_product, true, intval($this->cookie->id_lang));

			if (!Validate::isLoadedObject($product) OR (!$product->active AND (Tools::getValue('adtoken') != Tools::encrypt('PreviewProduct'.$product->id))
																			|| !file_exists(dirname(__FILE__).'/'.Tools::getValue('ad').'/ajax.php')))
			{
				header('HTTP/1.1 404 page not found');
				$this->errors[] = Tools::displayError('product is no longer available');
			}
			elseif (!$product->checkAccess(intval($this->cookie->id_customer)))
				$this->errors[] = Tools::displayError('you do not have access to this product');
			else
			{
				$this->smarty->assign('virtual', ProductDownload::getIdFromIdProduct(intval($product->id)));

				if (!$product->active)
					$this->smarty->assign('adminActionDisplay', true);

				/* rewrited url set */
				$rewrited_url = $this->link->getProductLink($product->id, $product->link_rewrite);

				/* Product pictures management */
				require_once('images.inc.php');
				$this->smarty->assign('customizationFormTarget', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));
				if (Tools::isSubmit('submitCustomizedDatas'))
				{
					$this->pictureUpload($product, $cart);
					$this->textRecord($product, $cart);
					$this->formTargetFormat();
				}
				elseif (isset($_GET['deletePicture']) AND !$cart->deletePictureToProduct(intval($product->id), intval(Tools::getValue('deletePicture'))))
					$this->errors[] = Tools::displayError('An error occured while deleting the selected picture');

				$files = $this->cookie->getFamily('pictures_'.intval($product->id));
				$textFields = $this->cookie->getFamily('textFields_'.intval($product->id));
				foreach ($textFields as $key => $textField)
					$textFields[$key] = str_replace('<br />', "\n", $textField);
				$this->smarty->assign(array(
					'pictures' => $files,
					'textFields' => $textFields));

				$productPriceWithTax = Product::getPriceStatic($id_product, true, NULL, 6);
				if (Product::$_taxCalculationMethod == PS_TAX_INC)
					$productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);

				$productPriceWithoutEcoTax = floatval($productPriceWithTax - $product->ecotax);
				$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

				/* Features / Values */
				$features = $product->getFrontFeatures(intval($this->cookie->id_lang));
				$attachments = $product->getAttachments(intval($this->cookie->id_lang));

				/* Category */
				$category = false;
				if (isset($_SERVER['HTTP_REFERER']) AND preg_match('!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!', $_SERVER['HTTP_REFERER'], $regs) AND !strstr($_SERVER['HTTP_REFERER'], '.html'))
				{
					if (isset($regs[2]) AND is_numeric($regs[2]))
					{
						if (Product::idIsOnCategoryId(intval($product->id), array('0' => array('id_category' => intval($regs[2])))))
							$category = new Category(intval($regs[2]), intval($this->cookie->id_lang));
					}
					elseif (isset($regs[5]) AND is_numeric($regs[5]))
					{
						if (Product::idIsOnCategoryId(intval($product->id), array('0' => array('id_category' => intval($regs[5])))))
							$category = new Category(intval($regs[5]), intval($this->cookie->id_lang));
					}
				}
				if (!$category)
					$category = new Category($product->id_category_default, intval($this->cookie->id_lang));

				if (isset($category) AND Validate::isLoadedObject($category))
				{
					$this->smarty->assign(array(
					'category' => $category,
					'subCategories' => $category->getSubCategories(intval($this->cookie->id_lang), true),
					'id_category_current' => intval($category->id),
					'id_category_parent' => intval($category->id_parent),
					'return_category_name' => Tools::safeOutput(Category::hideCategoryPosition($category->name))));
				}

				$this->smarty->assign(array(
					'return_link' => (isset($category->id) AND $category->id) ? Tools::safeOutput($this->link->getCategoryLink($category)) : 'javascript: history.back();',
					'path' => ((isset($category->id) AND $category->id) ? Tools::getFullPath(intval($category->id), $product->name) : Tools::getFullPath(intval($product->id_default_category), $product->name))
				));
				
				$lang = Configuration::get('PS_LANG_DEFAULT');
				if (Pack::isPack(intval($product->id), intval($lang)) AND !Pack::isInStock(intval($product->id), intval($lang)))
					$product->quantity = 0;

				$group_reduction = (100 - Group::getReduction(intval($this->cookie->id_customer))) / 100;
				$id_customer = (isset($this->cookie->id_customer) AND $this->cookie->id_customer) ? intval($this->cookie->id_customer) : 0;
				$id_group = $id_customer ? intval(Customer::getDefaultGroupId($id_customer)) : _PS_DEFAULT_CUSTOMER_GROUP_;
				$id_country = intval($id_customer ? Customer::getCurrentCountry($id_customer) : Configuration::get('PS_COUNTRY_DEFAULT'));

				// Tax
				$tax_data = Tax::getDataByProductId(intval($product->id));
				$tax = floatval(Tax::getApplicableTax(intval($tax_data['id_tax']), floatval($tax_data['rate'])));
				/* /Quantity discount management */
				$this->smarty->assign(array(
					'quantity_discounts' => $this->formatQuantityDiscounts(SpecificPrice::getQuantityDiscounts(intval($product->id), intval(Shop::getCurrentShop()), intval($this->cookie->id_currency), $id_country, $id_group), $product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, NULL), floatval($tax_data['rate'])),
					'product' => $product,
					'homeSize' => Image::getSize('home'),
					'product_manufacturer' => new Manufacturer(intval($product->id_manufacturer)),
					'token' => Tools::getToken(false),
					'productPriceWithoutEcoTax' => floatval($productPriceWithoutEcoTax),
					'features' => $features,
					'attachments' => $attachments,
					'allow_oosp' => $product->isAvailableWhenOutOfStock(intval($product->out_of_stock)),
					'last_qties' =>  intval($configs['PS_LAST_QTIES']),
					'group_reduction' => $group_reduction,
					'col_img_dir' => _PS_COL_IMG_DIR_,
					'HOOK_EXTRA_LEFT' => Module::hookExec('extraLeft'),
					'HOOK_EXTRA_RIGHT' => Module::hookExec('extraRight'),
					'HOOK_PRODUCT_OOS' => Hook::productOutOfStock($product),
					'HOOK_PRODUCT_FOOTER' => Hook::productFooter($product, $category),
					'HOOK_PRODUCT_ACTIONS' => Module::hookExec('productActions'),
					'HOOK_PRODUCT_TAB' =>  Module::hookExec('productTab'),
					'HOOK_PRODUCT_TAB_CONTENT' =>  Module::hookExec('productTabContent')));

				$images = $product->getImages(intval($this->cookie->id_lang));
				$productImages = array();
				foreach ($images AS $k => $image)
				{
					if ($image['cover'])
					{
						$this->smarty->assign('mainImage', $images[0]);
						$cover = $image;
						$cover['id_image'] = intval($product->id).'-'.$cover['id_image'];
						$cover['id_image_only'] = intval($image['id_image']);
					}
					$productImages[intval($image['id_image'])] = $image;
				}
				if (!isset($cover))
					$cover = array('id_image' => Language::getIsoById($this->cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');
				$size = Image::getSize('large');
				$this->smarty->assign(array(
					'cover' => $cover,
					'imgWidth' => intval($size['width']),
					'mediumSize' => Image::getSize('medium'),
					'largeSize' => Image::getSize('large'),
					'accessories' => $product->getAccessories(intval($this->cookie->id_lang))));
				if (sizeof($productImages))
					$this->smarty->assign('images', $productImages);

				/* Attributes / Groups & colors */
				if ($product->quantity > 0 OR Product::isAvailableWhenOutOfStock($product->out_of_stock))
				{	
					$colors = array();
					$attributesGroups = $product->getAttributesGroups(intval($this->cookie->id_lang));

					if (Db::getInstance()->numRows())
					{
						$combinationImages = $product->getCombinationImages(intval($this->cookie->id_lang));
						foreach ($attributesGroups AS $k => $row)
						{
							/* Color management */
							if (((isset($row['attribute_color']) AND $row['attribute_color']) OR (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) AND $row['id_attribute_group'] == $product->id_color_default)
							{
								$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
								$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
							}

							$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
							$groups[$row['id_attribute_group']]['name'] = $row['public_group_name'];
							$groups[$row['id_attribute_group']]['is_color_group'] = $row['is_color_group'];
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
							$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
							$combinations[$row['id_product_attribute']]['id_image'] = isset($combinationImages[$row['id_product_attribute']][0]['id_image']) ? $combinationImages[$row['id_product_attribute']][0]['id_image'] : -1;
						}
						//wash attributes list (if some attributes are unavailables and if allowed to wash it)
						if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
							foreach ($groups AS &$group)
								foreach ($group['attributes_quantity'] AS $key => &$quantity)
									if (!$quantity)
										unset($group['attributes'][$key]);
						foreach($groups AS &$group)
							natcasesort($group['attributes']);
						foreach ($combinations AS $id_product_attribute => $comb)
						{
							$attributeList = '';
							foreach ($comb['attributes'] AS $id_attribute)
								$attributeList .= '\''.intval($id_attribute).'\',';
							$attributeList = rtrim($attributeList, ',');
							$combinations[$id_product_attribute]['list'] = $attributeList;
						}
						$this->smarty->assign(array(
							'groups' => $groups,
							'combinaisons' => $combinations, /* Kept for compatibility purpose only */
							'combinations' => $combinations,
							'colors' => (sizeof($colors) AND $product->id_color_default) ? $colors : false,
							'combinationImages' => $combinationImages));
					}
				}
				
				$this->smarty->assign(array(
					'no_tax' => Tax::excludeTaxeOption() OR !Tax::getApplicableTax(intval($product->id_tax), 1),
					'customizationFields' => $product->getCustomizationFields(intval($this->cookie->id_lang))
				));

				// Pack management
				$this->smarty->assign('packItems', Pack::getItemTable($product->id, intval($this->cookie->id_lang), true));
				$this->smarty->assign('packs', Pack::getPacksTable($product->id, intval($this->cookie->id_lang), true, 1));
			}
		}

		$this->smarty->assign(array(
			'ENT_NOQUOTES' => ENT_NOQUOTES,
			'outOfStockAllowed' => intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')),
			'errors' => $this->errors,
			'categories' => Category::getHomeCategories(intval($this->cookie->id_lang)),
			'have_image' => Product::getCover(intval(Tools::getValue('id_product'))),
			'tax_enabled' => Configuration::get('PS_TAX'),
			'display_qties' => intval(Configuration::get('PS_DISPLAY_QTIES')),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'display_ht' => !Tax::excludeTaxeOption(),
			'ecotax' => ($product->ecotax > 0 ? Tools::convertPrice(floatval($product->ecotax)) : 0),
			'unit_price' => ($product->unit_price > 0 ? Tools::convertPrice(floatval($product->unit_price)) * ((Configuration::get('PS_TAX') AND Product::getTaxCalculationMethod(intval($this->cookie->id_customer)) == 0) ? ((floatval($product->tax_rate) / 100) + 1) : 1) * ($group_reduction < 1 ? $group_reduction : 1) * ((100 - $product->reduction_percent) / 100) : 0)));

		if (file_exists(_PS_THEME_DIR_.'thickbox.tpl'))
			$this->smarty->display(_PS_THEME_DIR_.'thickbox.tpl');
			
		global $currency;
		$this->smarty->assign(array(
			'currencySign' => $currency->sign,
			'currencyRate' => $currency->conversion_rate,
			'currencyFormat' => $currency->format,
			'currencyBlank' => $currency->blank,
			'jqZoomEnabled' => Configuration::get('PS_DISPLAY_JQZOOM')
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'product.tpl');
	}
	
	public function pictureUpload(Product $product, Cart $cart)
	{
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
					$this->errors[] = $error;
				if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($file['tmp_name'], $tmpName))
					return false;
				/* Original file */
				elseif (!imageResize($tmpName, _PS_PROD_PIC_DIR_.$fileName))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				/* A smaller one */
				elseif (!imageResize($tmpName, _PS_PROD_PIC_DIR_.$fileName.'_small', intval(Configuration::get('PS_PRODUCT_PICTURE_WIDTH')), intval(Configuration::get('PS_PRODUCT_PICTURE_HEIGHT'))))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				elseif (!chmod(_PS_PROD_PIC_DIR_.$fileName, 0777) OR !chmod(_PS_PROD_PIC_DIR_.$fileName.'_small', 0777))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				else
					$cart->addPictureToProduct(intval($product->id), $indexes[$fieldName], $fileName);
				unlink($tmpName);
			}
		return true;
	}

	public function textRecord(Product $product, Cart $cart)
	{
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
					$this->errors[] = Tools::displayError('Invalid message');
				else
					$cart->addTextFieldToProduct(intval($product->id), $indexes[$fieldName], $value);
			}
			elseif (in_array($fieldName, $authorizedTextFields) AND empty($value))
				$cart->deleteTextFieldFromProduct(intval($product->id), $indexes[$fieldName]);
	}

	public function formTargetFormat()
	{
		$customizationFormTarget = Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
		foreach ($_GET AS $field => $value)
			if (strncmp($field, 'group_', 6) == 0)
				$customizationFormTarget = preg_replace('/&group_([[:digit:]]+)=([[:digit:]]+)/', '', $customizationFormTarget);
		if (isset($_POST['quantityBackup']))
			$this->smarty->assign('quantityBackup', intval($_POST['quantityBackup']));
		$this->smarty->assign('customizationFormTarget', $customizationFormTarget);
	}

	public function formatQuantityDiscounts($specificPrices, $price, $taxRate)
	{
		foreach ($specificPrices AS $key => &$row)
		{
			$row['quantity'] = &$row['from_quantity'];
			if (!floatval($row['reduction'])) // The price may be directly set
				$row['real_value'] = $price - (Product::$_taxCalculationMethod == PS_TAX_EXC ? $row['price'] : $row['price'] * (1 + $taxRate / 100));
			else
				$row['real_value'] = $row['reduction_type'] == 'amount' ? $row['reduction'] : ($price * $row['reduction']);
			$row['nextQuantity'] = (isset($specificPrices[$key + 1]) ? intval($specificPrices[$key + 1]['quantity']) : -1);
		}
		return $specificPrices;
	}
}