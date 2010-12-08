<?php

class ProductControllerCore extends FrontController
{
	public function setMedia()
	{
		parent::setMedia();

		Tools::addCSS(_THEME_CSS_DIR_.'product.css');
		Tools::addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
		Tools::addJS(array(
			_PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js',
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

	public function preProcess()
	{
		if((int)(Configuration::get('PS_REWRITING_SETTINGS')))
		{	
	        $id_product = (int)Tools::getValue('id_product');

			if ($id_product)
			{
				$rewrite_infos = Product::getUrlRewriteInformations($id_product);

				$default_rewrite = array();
				foreach ($rewrite_infos AS $infos)
					$default_rewrite[$infos['id_lang']] = $this->link->getProductLink($id_product, $infos['link_rewrite'], $infos['category_rewrite'], $infos['ean13'], $infos['id_lang']);

				$this->smarty->assign('lang_rewrite_urls', $default_rewrite);
			}
		}
	}

	public function process()
	{
		parent::process();
		global $cart;

		if (!$id_product = (int)(Tools::getValue('id_product')) OR !Validate::isUnsignedId($id_product))
			$this->errors[] = Tools::displayError('product not found');
		else
		{
			$product = new Product($id_product, true, (int)($this->cookie->id_lang));

			if (!Validate::isLoadedObject($product) OR (!$product->active AND (Tools::getValue('adtoken') != Tools::encrypt('PreviewProduct'.$product->id))
																			|| !file_exists(dirname(__FILE__).'/../'.Tools::getValue('ad').'/ajax.php')))
			{
				header('HTTP/1.1 404 page not found');
				$this->errors[] = Tools::displayError('product is no longer available');
			}
			elseif (!$product->checkAccess((int)($this->cookie->id_customer)))
				$this->errors[] = Tools::displayError('you do not have access to this product');
			else
			{
				$this->smarty->assign('virtual', ProductDownload::getIdFromIdProduct((int)($product->id)));

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
				elseif (isset($_GET['deletePicture']) AND !$cart->deletePictureToProduct((int)($product->id), (int)(Tools::getValue('deletePicture'))))
					$this->errors[] = Tools::displayError('An error occurred while deleting the selected picture');

				$files = $this->cookie->getFamily('pictures_'.(int)($product->id));
				$textFields = $this->cookie->getFamily('textFields_'.(int)($product->id));
				foreach ($textFields as $key => $textField)
					$textFields[$key] = str_replace('<br />', "\n", $textField);
				$this->smarty->assign(array(
					'pictures' => $files,
					'textFields' => $textFields));

				$productPriceWithTax = Product::getPriceStatic($id_product, true, NULL, 6);
				if (Product::$_taxCalculationMethod == PS_TAX_INC)
					$productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);

				$productPriceWithoutEcoTax = (float)($productPriceWithTax - $product->ecotax);
				$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

				/* Features / Values */
				$features = $product->getFrontFeatures((int)($this->cookie->id_lang));
				$attachments = $product->getAttachments((int)($this->cookie->id_lang));

				/* Category */
				$category = false;
				if (isset($_SERVER['HTTP_REFERER']) AND preg_match('!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!', $_SERVER['HTTP_REFERER'], $regs) AND !strstr($_SERVER['HTTP_REFERER'], '.html'))
				{
					if (isset($regs[2]) AND is_numeric($regs[2]))
					{
						if (Product::idIsOnCategoryId((int)($product->id), array('0' => array('id_category' => (int)($regs[2])))))
							$category = new Category((int)($regs[2]), (int)($this->cookie->id_lang));
					}
					elseif (isset($regs[5]) AND is_numeric($regs[5]))
					{
						if (Product::idIsOnCategoryId((int)($product->id), array('0' => array('id_category' => (int)($regs[5])))))
							$category = new Category((int)($regs[5]), (int)($this->cookie->id_lang));
					}
				}
				if (!$category)
					$category = new Category($product->id_category_default, (int)($this->cookie->id_lang));

				if (isset($category) AND Validate::isLoadedObject($category))
				{
					$this->smarty->assign(array(
					'category' => $category,
					'subCategories' => $category->getSubCategories((int)($this->cookie->id_lang), true),
					'id_category_current' => (int)($category->id),
					'id_category_parent' => (int)($category->id_parent),
					'return_category_name' => Tools::safeOutput($category->name)));
				}

				$this->smarty->assign(array(
					'return_link' => (isset($category->id) AND $category->id) ? Tools::safeOutput($this->link->getCategoryLink($category)) : 'javascript: history.back();',
					'path' => ((isset($category->id) AND $category->id) ? Tools::getFullPath((int)($category->id), $product->name) : Tools::getFullPath((int)($product->id_category_default), $product->name))
				));

				$lang = Configuration::get('PS_LANG_DEFAULT');
				if (Pack::isPack((int)($product->id), (int)($lang)) AND !Pack::isInStock((int)($product->id), (int)($lang)))
					$product->quantity = 0;

				$group_reduction = (100 - Group::getReduction((int)($this->cookie->id_customer))) / 100;
				$id_customer = (isset($this->cookie->id_customer) AND $this->cookie->id_customer) ? (int)($this->cookie->id_customer) : 0;
				$id_group = $id_customer ? (int)(Customer::getDefaultGroupId($id_customer)) : _PS_DEFAULT_CUSTOMER_GROUP_;
				$id_country = (int)($id_customer ? Customer::getCurrentCountry($id_customer) : Configuration::get('PS_COUNTRY_DEFAULT'));

				// Tax
				$tax_data = Tax::getDataByProductId((int)($product->id));
				$tax = (float)(Tax::getProductTaxRate((int)($product->id), (int)($id_country), (int)($tax_data['id_tax']), (float)($tax_data['rate'])));
				$this->smarty->assign('tax_rate', $tax);
				$ecotaxTaxAmount = $product->ecotax;
				if ($ecotaxTax = new Tax(Configuration::get('PS_ECOTAX_TAX_ID')) AND Product::$_taxCalculationMethod == PS_TAX_INC)
					$ecotaxTaxAmount = Tools::ps_round($ecotaxTaxAmount * (1 + Tax::getApplicableTaxRate((int)$ecotaxTax->id, (float)$ecotaxTax->rate) / 100), 2);
				/* /Quantity discount management */
				$this->smarty->assign(array(
					'quantity_discounts' => $this->formatQuantityDiscounts(SpecificPrice::getQuantityDiscounts((int)($product->id), (int)(Shop::getCurrentShop()), (int)($this->cookie->id_currency), $id_country, $id_group), $product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false), (float)($tax_data['rate'])),
					'product' => $product,
					'ecotax_tax_inc' => $ecotaxTaxAmount,
					'ecotaxTax_rate' => $ecotaxTax ? $ecotaxTax->rate : 0.00,
					'homeSize' => Image::getSize('home'),
					'product_manufacturer' => new Manufacturer((int)($product->id_manufacturer), Configuration::get('PS_LANG_DEFAULT')),
					'token' => Tools::getToken(false),
					'productPriceWithoutEcoTax' => (float)($productPriceWithoutEcoTax),
					'features' => $features,
					'attachments' => $attachments,
					'allow_oosp' => $product->isAvailableWhenOutOfStock((int)($product->out_of_stock)),
					'last_qties' =>  (int)($configs['PS_LAST_QTIES']),
					'group_reduction' => $group_reduction,
					'col_img_dir' => _PS_COL_IMG_DIR_,
					'HOOK_EXTRA_LEFT' => Module::hookExec('extraLeft'),
					'HOOK_EXTRA_RIGHT' => Module::hookExec('extraRight'),
					'HOOK_PRODUCT_OOS' => Hook::productOutOfStock($product),
					'HOOK_PRODUCT_FOOTER' => Hook::productFooter($product, $category),
					'HOOK_PRODUCT_ACTIONS' => Module::hookExec('productActions'),
					'HOOK_PRODUCT_TAB' =>  Module::hookExec('productTab'),
					'HOOK_PRODUCT_TAB_CONTENT' =>  Module::hookExec('productTabContent')));

				$images = $product->getImages((int)($this->cookie->id_lang));
				$productImages = array();
				foreach ($images AS $k => $image)
				{
					if ($image['cover'])
					{
						$this->smarty->assign('mainImage', $images[0]);
						$cover = $image;
						$cover['id_image'] = (int)($product->id).'-'.$cover['id_image'];
						$cover['id_image_only'] = (int)($image['id_image']);
					}
					$productImages[(int)($image['id_image'])] = $image;
				}
				if (!isset($cover))
					$cover = array('id_image' => Language::getIsoById($this->cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');
				$size = Image::getSize('large');
				$this->smarty->assign(array(
					'cover' => $cover,
					'imgWidth' => (int)($size['width']),
					'mediumSize' => Image::getSize('medium'),
					'largeSize' => Image::getSize('large'),
					'accessories' => $product->getAccessories((int)($this->cookie->id_lang))));
				if (sizeof($productImages))
					$this->smarty->assign('images', $productImages);

				/* Attributes / Groups & colors */
				if ($product->quantity > 0 OR Product::isAvailableWhenOutOfStock($product->out_of_stock))
				{
					$colors = array();
					$attributesGroups = $product->getAttributesGroups((int)($this->cookie->id_lang));

					if (Db::getInstance()->numRows())
					{
						$combinationImages = $product->getCombinationImages((int)($this->cookie->id_lang));
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
								$groups[$row['id_attribute_group']]['default'] = (int)($row['id_attribute']);
							if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
								$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
							$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)($row['quantity']);

							$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
							$combinations[$row['id_product_attribute']]['attributes'][] = (int)($row['id_attribute']);
							$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
							$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
							$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
							$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
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
								$attributeList .= '\''.(int)($id_attribute).'\',';
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
					'no_tax' => Tax::excludeTaxeOption() OR !Tax::getProductTaxRate((int)$product->id, (int)$id_country, (int)$product->id_tax, 1),
					'customizationFields' => $product->getCustomizationFields((int)($this->cookie->id_lang))
				));

				// Pack management
				$this->smarty->assign('packItems', Pack::getItemTable($product->id, (int)($this->cookie->id_lang), true));
				$this->smarty->assign('packs', Pack::getPacksTable($product->id, (int)($this->cookie->id_lang), true, 1));
			}
		}

		$this->smarty->assign(array(
			'ENT_NOQUOTES' => ENT_NOQUOTES,
			'outOfStockAllowed' => (int)(Configuration::get('PS_ORDER_OUT_OF_STOCK')),
			'errors' => $this->errors,
			'categories' => Category::getHomeCategories((int)($this->cookie->id_lang)),
			'have_image' => Product::getCover((int)(Tools::getValue('id_product'))),
			'tax_enabled' => Configuration::get('PS_TAX'),
			'display_qties' => (int)(Configuration::get('PS_DISPLAY_QTIES')),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'display_ht' => !Tax::excludeTaxeOption(),
			'ecotax' => (!sizeof($this->errors) AND $product->ecotax > 0 ? Tools::convertPrice((float)($product->ecotax)) : 0),
			'unit_price' => (!sizeof($this->errors) AND $product->unit_price > 0 ? Tools::convertPrice((float)($product->unit_price)) * ((Configuration::get('PS_TAX') AND Product::getTaxCalculationMethod((int)($this->cookie->id_customer)) == 0) ? (((float)($product->tax_rate) / 100) + 1) : 1) * ($group_reduction < 1 ? $group_reduction : 1) : 0)));

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
				$authorizedFileFields[(int)($fieldId['id_customization_field'])] = 'file'.(int)($fieldId['id_customization_field']);
		$indexes = array_flip($authorizedFileFields);
		foreach ($_FILES AS $fieldName => $file)
			if (in_array($fieldName, $authorizedFileFields) AND isset($file['tmp_name']) AND !empty($file['tmp_name']))
			{
				$fileName = md5(uniqid(rand(), true));
				if ($error = checkImage($file, (int)(Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))))
					$this->errors[] = $error;
				if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($file['tmp_name'], $tmpName))
					return false;
				/* Original file */
				elseif (!imageResize($tmpName, _PS_PROD_PIC_DIR_.$fileName))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				/* A smaller one */
				elseif (!imageResize($tmpName, _PS_PROD_PIC_DIR_.$fileName.'_small', (int)(Configuration::get('PS_PRODUCT_PICTURE_WIDTH')), (int)(Configuration::get('PS_PRODUCT_PICTURE_HEIGHT'))))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				elseif (!chmod(_PS_PROD_PIC_DIR_.$fileName, 0777) OR !chmod(_PS_PROD_PIC_DIR_.$fileName.'_small', 0777))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				else
					$cart->addPictureToProduct((int)($product->id), $indexes[$fieldName], $fileName);
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
				$authorizedTextFields[(int)($fieldId['id_customization_field'])] = 'textField'.(int)($fieldId['id_customization_field']);
		$indexes = array_flip($authorizedTextFields);
		foreach ($_POST AS $fieldName => $value)
			if (in_array($fieldName, $authorizedTextFields) AND !empty($value))
			{
				if (!Validate::isMessage($value))
					$this->errors[] = Tools::displayError('Invalid message');
				else
					$cart->addTextFieldToProduct((int)($product->id), $indexes[$fieldName], $value);
			}
			elseif (in_array($fieldName, $authorizedTextFields) AND empty($value))
				$cart->deleteTextFieldFromProduct((int)($product->id), $indexes[$fieldName]);
	}

	public function formTargetFormat()
	{
		$customizationFormTarget = Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
		foreach ($_GET AS $field => $value)
			if (strncmp($field, 'group_', 6) == 0)
				$customizationFormTarget = preg_replace('/&group_([[:digit:]]+)=([[:digit:]]+)/', '', $customizationFormTarget);
		if (isset($_POST['quantityBackup']))
			$this->smarty->assign('quantityBackup', (int)($_POST['quantityBackup']));
		$this->smarty->assign('customizationFormTarget', $customizationFormTarget);
	}

	public function formatQuantityDiscounts($specificPrices, $price, $taxRate)
	{
		foreach ($specificPrices AS $key => &$row)
		{
			$row['quantity'] = &$row['from_quantity'];
			if (!(float)($row['reduction'])) // The price may be directly set
				$row['real_value'] = $price - (Product::$_taxCalculationMethod == PS_TAX_EXC ? $row['price'] : $row['price'] * (1 + $taxRate / 100));
			else
				$row['real_value'] = $row['reduction_type'] == 'amount' ? (Product::$_taxCalculationMethod == PS_TAX_INC ? $row['reduction'] : $row['reduction'] / (1 + $taxRate / 100)) : ($price * $row['reduction']);
			$row['nextQuantity'] = (isset($specificPrices[$key + 1]) ? (int)($specificPrices[$key + 1]['from_quantity']) : -1);
		}
		return $specificPrices;
	}
}



