<?php

/**
  * Products tab for admin panel, AdminProducts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/tabs/AdminProfiles.php');

class AdminProducts extends AdminTab
{
	protected $maxImageSize = 2000000;
	protected $maxFileSize  = 10000000;

	private $_category;

	public function __construct()
	{
		global $currentIndex;

		$this->table = 'product';
		$this->className = 'Product';
		$this->lang = true;
		$this->edit = true;
	 	$this->delete = true;
		$this->view = false;
		$this->duplicate = true;
		$this->imageType = 'jpg';

		$this->fieldsDisplay = array(
			'id_product' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 20),
			'image' => array('title' => $this->l('Photo'), 'align' => 'center', 'image' => 'p', 'width' => 45, 'orderby' => false, 'filter' => false, 'search' => false),
			'name' => array('title' => $this->l('Name'), 'width' => 277, 'filter_key' => 'b!name'),
			'reference' => array('title' => $this->l('Reference'), 'align' => 'center', 'width' => 20), 
			'price' => array('title' => $this->l('Base price'), 'width' => 70, 'price' => true, 'align' => 'right', 'filter_key' => 'a!price'),
			'price_final' => array('title' => $this->l('Final price'), 'width' => 70, 'price' => true, 'align' => 'right'),
			'quantity' => array('title' => $this->l('Quantity'), 'width' => 30, 'align' => 'right', 'filter_key' => 'a!quantity', 'type' => 'decimal'),
			'position' => array('title' => $this->l('Position'), 'width' => 40, 'align' => 'center', 'position' => 'position'),
			'active' => array('title' => $this->l('Displayed'), 'active' => 'status', 'align' => 'center', 'type' => 'bool', 'orderby' => false));

		/* Join categories table */
		$this->_category = AdminCatalog::getCurrentCategory();
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = a.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = a.`id_product`)';
		$this->_filter = 'AND cp.`id_category` = '.intval($this->_category->id);
		$this->_select = 'cp.`position`, i.`id_image`';

		parent::__construct();
	}

	protected function copyFromPost(&$object, $table)
	{
		parent::copyFromPost($object, $table);

		if (get_class($object) != 'Product')
			return;
			
		/* Additional fields */
		$languages = Language::getLanguages();
		foreach ($languages as $language)
			if (isset($_POST['meta_keywords_'.$language['id_lang']]))
				$_POST['meta_keywords_'.$language['id_lang']] = preg_replace('/ *,? +,*/', ',', strtolower($_POST['meta_keywords_'.$language['id_lang']]));
		$_POST['weight'] = empty($_POST['weight']) ? '0' : str_replace(',', '.', $_POST['weight']);
		if ($_POST['reduction_price'] != NULL) $object->reduction_price = str_replace(',', '.', $_POST['reduction_price']);
		if ($_POST['reduction_percent'] != NULL) $object->reduction_percent = str_replace(',', '.', $_POST['reduction_percent']);
		if ($_POST['ecotax'] != NULL) $object->ecotax = str_replace(',', '.', $_POST['ecotax']);
		$object->active = (!isset($_POST['active']) OR $_POST['active']) ? true : false;
		$object->on_sale = (!isset($_POST['on_sale']) ? false : true);
	}

	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL)
	{
		$orderByPriceFinal = (empty($orderBy) ? Tools::getValue($this->table.'Orderby', 'id_'.$this->table) : $orderBy);
		$orderWayPriceFinal = (empty($orderWay) ? Tools::getValue($this->table.'Orderway', 'ASC') : $orderWay);
		if ($orderByPriceFinal == 'price_final')
		{
			$orderBy = 'id_'.$this->table;
			$orderWay = 'ASC';
		}
		parent::getList($id_lang, $orderBy, $orderWay, $start, $limit);

		/* update product quantity with attributes ...*/
		if ($this->_list)
		{
			$nb = count ($this->_list);
			for ($i = 0; $i < $nb; $i++)
				Attribute::updateQtyProduct($this->_list[$i]);
			/* update product final price */
			for ($i = 0; $i < $nb; $i++)
				$this->_list[$i]['price_tmp'] = Product::getPriceStatic($this->_list[$i]['id_product'], $usetax = true, $id_product_attribute = NULL, $decimals = 6, $divisor = NULL, $only_reduc = false, $usereduc = true, $quantity = 1, $forceAssociatedTax = true);
		}
		
		if ($orderByPriceFinal == 'price_final')
		{
			if(strtolower($orderWayPriceFinal) == 'desc')
				uasort($this->_list, 'cmpPriceDesc');
			else
				uasort($this->_list, 'cmpPriceAsc');
		}
		for ($i = 0; $this->_list AND $i < $nb; $i++)
		{
			$this->_list[$i]['price_final'] = $this->_list[$i]['price_tmp'];
			unset($this->_list[$i]['price_tmp']);
		}
	}

	public function deleteVirtualProduct()
	{
		if (!($id_product_download = ProductDownload::getIdFromIdProduct(Tools::getValue('id_product'))))
			return false;
		$productDownload = new ProductDownload(intval($id_product_download));
		return $productDownload->deleteFile();
	}

	public function postProcess($token = NULL)
	{
		global $currentIndex;
		/* Add a new product */
		if (Tools::isSubmit('submitAddproduct') OR Tools::isSubmit('submitAddproductAndStay'))
		{
			if ($this->tabAccess['add'] === '1')
				$this->submitAddproduct($token);
			elseif (Tools::getValue('id_product') AND $this->tabAccess['edit'] === '1')
				$this->submitAddproduct($token);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}

		/* Delete a product in the download folder */
		if (Tools::getValue('deleteVirtualProduct'))
		{
			if ($this->tabAccess['delete'] === '1')
				$this->deleteVirtualProduct();
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete anything here.');
		}
		
		/* Update attachments */
		elseif (Tools::isSubmit('submitAttachments'))
		{
			if ($this->tabAccess['edit'] === '1')
				if ($id = intval(Tools::getValue($this->identifier)))
					if (Attachment::attachToProduct($id, $_POST['attachments']))
						Tools::redirectAdmin($currentIndex.'&id_product='.$id.'&conf=4&add'.$this->table.'&tabs=6&token='.($token ? $token : $this->token));
		}

		/* Product duplication */
		elseif (isset($_GET['duplicate'.$this->table]))
		{
			if ($this->tabAccess['add'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					$id_product_old = $product->id;
					unset($product->id);
					unset($product->id_product);
					$product->indexed = 0;

					if ($product->add()
					AND Category::duplicateProductCategories($id_product_old, $product->id)
					AND ($combinationImages = Product::duplicateAttributes($id_product_old, $product->id)) !== false
					AND Product::duplicateAccessories($id_product_old, $product->id)
					AND Product::duplicateFeatures($id_product_old, $product->id)
					AND Product::duplicateQuantityDiscount($id_product_old, $product->id)
					AND Pack::duplicate($id_product_old, $product->id)
					AND Product::duplicateCustomizationFields($id_product_old, $product->id)
					AND Product::duplicateTags($id_product_old, $product->id))
					{
						if (!Tools::getValue('noimage') AND !Image::duplicateProductImages($id_product_old, $product->id, $combinationImages))
							$this->_errors[] = Tools::displayError('an error occurred while copying images');
						else
						{
							Hook::addProduct($product);
							Search::indexation(false);
							Tools::redirectAdmin($currentIndex.'&id_category='.intval(Tools::getValue('id_category')).'&conf=19&token='.($token ? $token : $this->token));
						}
					}
					else
						$this->_errors[] = Tools::displayError('an error occurred while creating object');
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}

		/* Product images management */
		elseif (($id_image = intval(Tools::getValue('id_image'))) AND Validate::isUnsignedId($id_image) AND Validate::isLoadedObject($image = new Image($id_image)))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				/* Delete product image */
				if (isset($_GET['deleteImage']))
				{
					$image->delete();
					deleteImage($image->id_product, $image->id);
					if (!Image::getCover($image->id_product))
					{
						$first_img = Db::getInstance()->getRow('
						SELECT `id_image` FROM `'._DB_PREFIX_.'image`
						WHERE `id_product` = '.intval($image->id_product));
						Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'image`
						SET `cover` = 1
						WHERE `id_image` = '.intval($first_img['id_image']));
					}
					@unlink(dirname(__FILE__).'/../../img/tmp/product_'.$image->id_product.'.jpg');
					@unlink(dirname(__FILE__).'/../../img/tmp/product_mini_'.$image->id_product.'.jpg'); 
					Tools::redirectAdmin($currentIndex.'&id_product='.$image->id_product.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=1'.'&token='.($token ? $token : $this->token));
				}

				/* Update product image/legend */
				elseif (isset($_GET['editImage']))
				{
					if ($image->cover)
						$_POST['cover'] = 1;
					$languages = Language::getLanguages();
					foreach ($languages as $language)
						if (isset($image->legend[$language['id_lang']]))
							$_POST['legend_'.$language['id_lang']] = $image->legend[$language['id_lang']];
					$_POST['id_image'] = $image->id;
					$this->displayForm($token ? $token : $this->token);
				}

				/* Choose product cover image */
				elseif (isset($_GET['coverImage']))
				{
					Image::deleteCover($image->id_product);
					$image->cover = 1;
					if (!$image->update())
						$this->_errors[] = Tools::displayError('Impossible to change the product cover');
					else
					{
						$productId = intval(Tools::getValue('id_product'));
						@unlink(dirname(__FILE__).'/../../img/tmp/product_'.$productId.'.jpg');
						@unlink(dirname(__FILE__).'/../../img/tmp/product_mini_'.$productId.'.jpg');
						Tools::redirectAdmin($currentIndex.'&id_product='.$image->id_product.'&id_category='.intval(Tools::getValue('id_category')).'&addproduct&tabs=1'.'&token='.($token ? $token : $this->token));
					}
				}

				/* Choose product image position */
				elseif (isset($_GET['imgPosition']) AND isset($_GET['imgDirection']))
				{
					$image->positionImage(intval(Tools::getValue('imgPosition')), intval(Tools::getValue('imgDirection')));
					Tools::redirectAdmin($currentIndex.'&id_product='.$image->id_product.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=1&token='.($token ? $token : $this->token));
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		/* Product attributes management */
		elseif (Tools::isSubmit('submitProductAttribute'))
		{
			if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
			{
				if (!isset($_POST['attribute_quantity']) OR $_POST['attribute_quantity'] == NULL)
					$this->_errors[] = Tools::displayError('attribute quantity is required');
				if (!isset($_POST['attribute_price']) OR $_POST['attribute_price'] == NULL)
					$this->_errors[] = Tools::displayError('attribute price is required');
				if (!isset($_POST['attribute_combinaison_list']) OR !sizeof($_POST['attribute_combinaison_list']))
					$this->_errors[] = Tools::displayError('you must add at least one attribute');

				if (!sizeof($this->_errors))
				{
					if (!isset($_POST['attribute_wholesale_price'])) $_POST['attribute_wholesale_price'] = 0;
					if (!isset($_POST['attribute_price_impact'])) $_POST['attribute_price_impact'] = 0;
					if (!isset($_POST['attribute_weight_impact'])) $_POST['attribute_weight_impact'] = 0;
					if (!isset($_POST['attribute_ecotax'])) $_POST['attribute_ecotax'] = 0;
					if (Tools::getValue('attribute_default'))
						$product->deleteDefaultAttributes();
					// Change existing one
					if ($id_product_attribute = intval(Tools::getValue('id_product_attribute')))
					{
						if ($this->tabAccess['edit'] === '1')
						{
							if ($product->productAttributeExists($_POST['attribute_combinaison_list'], $id_product_attribute))
								$this->_errors[] = Tools::displayError('This attribute already exists.');
							else
								$product->updateProductAttribute($id_product_attribute,
								Tools::getValue('attribute_wholesale_price'),
								Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact'),
								Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact'),
								Tools::getValue('attribute_ecotax'),
								Tools::getValue('attribute_quantity'),
								Tools::getValue('id_image_attr'),
								Tools::getValue('attribute_reference'),
								Tools::getValue('attribute_supplier_reference'),
								Tools::getValue('attribute_ean13'),
								Tools::getValue('attribute_default'),
								Tools::getValue('attribute_location'));
						}
						else
							$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
					}
					// Add new
					else
					{
						if ($this->tabAccess['add'] === '1')
						{
							if ($product->productAttributeExists($_POST['attribute_combinaison_list']))
								$this->_errors[] = Tools::displayError('This combination already exists.');
							else
								$id_product_attribute = $product->addCombinationEntity(Tools::getValue('attribute_wholesale_price'), Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact'),
                                Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact'), Tools::getValue('attribute_ecotax'), 
                                Tools::getValue('attribute_quantity'),	Tools::getValue('id_image_attr'), Tools::getValue('attribute_reference'), 
                                Tools::getValue('attribute_supplier_reference'), Tools::getValue('attribute_ean13'), Tools::getValue('attribute_default'), Tools::getValue('attribute_location'));
						}
						else
							$this->_errors[] = Tools::displayError('You do not have permission to').'<hr>'.Tools::displayError('edit something here.');
					}
					if (!sizeof($this->_errors))
					{
						$product->addAttributeCombinaison($id_product_attribute, Tools::getValue('attribute_combinaison_list'));
						$product->checkDefaultAttributes();
					}
					if (!sizeof($this->_errors))
						Tools::redirectAdmin($currentIndex.'&id_product='.$product->id.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=2&token='.($token ? $token : $this->token));
				}
			}
		}
		elseif (isset($_GET['deleteProductAttribute']))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (($id_product = intval(Tools::getValue('id_product'))) AND Validate::isUnsignedId($id_product) AND Validate::isLoadedObject($product = new Product($id_product)))
				{
					$product->deleteAttributeCombinaison(intval(Tools::getValue('id_product_attribute')));
					$product->checkDefaultAttributes();
					Tools::redirectAdmin($currentIndex.'&add'.$this->table.'&id_category='.intval(Tools::getValue('id_category')).'&tabs=2&id_product='.$product->id.'&token='.($token ? $token : $this->token));
				}
				else
					$this->_errors[] = Tools::displayError('impossible to delete attribute');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}

		/* Product features management */
		elseif (Tools::isSubmit('submitProductFeature'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					// delete all objects
					$product->deleteFeatures();

					// add new objects
					$languages = Language::getLanguages();
					foreach ($_POST AS $key => $val)
					{
						if (preg_match("/^feature_([0-9]+)_value/i", $key, $match))
						{
							if ($val)
								$product->addFeaturesToDB($match[1], $val);
							else {
								if ($default_value = $this->checkFeatures($languages, $match[1]))
								{
									$id_value = $product->addFeaturesToDB($match[1], 0, 1, $language['id_lang']);
									foreach ($languages AS $language)
									{
										if ($cust = Tools::getValue('custom_'.$match[1].'_'.$language['id_lang']))
											$product->addFeaturesCustomToDB($id_value, $language['id_lang'], $cust);
										else
											$product->addFeaturesCustomToDB($id_value, $language['id_lang'], $default_value);
									}
								}
							}
						}
					}
					if (!sizeof($this->_errors))
						Tools::redirectAdmin($currentIndex.'&id_product='.$product->id.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=3&token='.($token ? $token : $this->token));
				}
				else
					$this->_errors[] = Tools::displayError('product must be created before adding features');
			}
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		/* Product quantity discount management */
		elseif (Tools::isSubmit('submitQuantityDiscount'))
		{
			$_POST['tabs'] = 5;
			if ($this->tabAccess['add'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					if (!($id_discount_type = intval(Tools::getValue('id_discount_type'))))
						$this->_errors[] = Tools::displayError('discount type not selected');
					else if (!($quantity_discount = intval(Tools::getValue('quantity_discount'))))
						$this->_errors[] = Tools::displayError('quantity is required');
					else if (!($value_discount = floatval(Tools::getValue('value_discount'))))
						$this->_errors[] = Tools::displayError('value is required');
					else
					{
						$qD = new QuantityDiscount();
						$qD->id_product = $product->id;
						$qD->id_discount_type = $id_discount_type;
						$qD->quantity = $quantity_discount;
						$qD->value = $value_discount;
						if ($qD->add() AND !sizeof($this->_errors) AND $qD->validateFields())
							Tools::redirectAdmin($currentIndex.'&id_product='.$product->id.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=5&conf=3&token='.($token ? $token : $this->token));
						$this->_errors[] = Tools::displayError('an error occurred while creating object');
					}
				}
				else
					$this->_errors[] = Tools::displayError('product must be created before adding quantity discounts');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (isset($_GET['deleteQuantityDiscount']))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					if (Validate::isLoadedObject($qD = new QuantityDiscount(intval(Tools::getValue('id_quantity_discount')))))
					{
						$qD->delete();
						if (!sizeof($this->_errors))
							Tools::redirectAdmin($currentIndex.'&id_product='.$product->id.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=5&conf=1&token='.($token ? $token : $this->token));
					}
					else
						$this->_errors[] = Tools::displayError('not a valid quantity discount');
				} else
					$this->_errors[] = Tools::displayError('product must be created before delete quantity discounts');
				$qD = new QuantityDiscount();
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}

		/* Customization management */
		elseif (Tools::isSubmit('submitCustomizationConfiguration'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					if (!$product->createLabels(intval($_POST['uploadable_files']) - intval($product->uploadable_files), intval($_POST['text_fields']) - intval($product->text_fields)))
						$this->_errors[] = Tools::displayError('an error occured while creating customization fields');
					if (!sizeof($this->_errors) AND !$product->updateLabels())
						$this->_errors[] = Tools::displayError('an error occured while updating customization');
					$product->uploadable_files = intval($_POST['uploadable_files']);
					$product->text_fields = intval($_POST['text_fields']);
					$product->customizable = (intval($_POST['uploadable_files']) > 0 OR intval($_POST['text_fields']) > 0) ? 1 : 0;
					if (!sizeof($this->_errors) AND !$product->update())
						$this->_errors[] = Tools::displayError('an error occured while updating customization configuration');
					if (!sizeof($this->_errors))
						Tools::redirectAdmin($currentIndex.'&id_product='.$product->id.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=4&token='.($token ? $token : $this->token));
				}
				else
					$this->_errors[] = Tools::displayError('product must be created before adding customization possibilities');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (Tools::isSubmit('submitProductCustomization'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					foreach ($_POST AS $field => $value)
						if (strncmp($field, 'label_', 6) == 0 AND !Validate::isLabel($value))
							$this->_errors[] = Tools::displayError('label fields are invalid');
					if (!sizeof($this->_errors) AND !$product->updateLabels())
						$this->_errors[] = Tools::displayError('an error occured while updating customization');
					if (!sizeof($this->_errors))
						Tools::redirectAdmin($currentIndex.'&id_product='.$product->id.'&id_category='.intval(Tools::getValue('id_category')).'&add'.$this->table.'&tabs=4&token='.($token ? $token : $this->token));
				}
				else
					$this->_errors[] = Tools::displayError('product must be created before adding customization possibilities');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		// Delete object
		elseif (isset($_GET['delete'.$this->table]))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Validate::isLoadedObject($product = new Product(intval(Tools::getValue('id_product')))))
				{
					if (!$this->deleteImage($product->id))
						$this->_errors[] = Tools::displayError('an error occurred during product image deletion');
					if ($product->delete())
						Tools::redirectAdmin($currentIndex.'&id_category='.intval(Tools::getValue('id_category')).'&conf=1&token='.($token ? $token : $this->token));
					$this->_errors[] = Tools::displayError('an error occurred during product deletion');
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		else
			parent::postProcess(true);
	}

	// Checking customs feature
	private function checkFeatures($languages, $feature_id)
	{
		$rules = call_user_func(array('FeatureValue', 'getValidationRules'), 'FeatureValue');
		$feature = Feature::getFeature(Configuration::get('PS_LANG_DEFAULT'), $feature_id);
		$val = 0;
		foreach ($languages AS $language)
			if ($val = Tools::getValue('custom_'.$feature_id.'_'.$language['id_lang']))
			{
				$currentLanguage = new Language($language['id_lang']);
				if (Tools::strlen($val) > $rules['sizeLang']['value'])
					$this->_errors[] = Tools::displayError('name for feature').' <b>'.$feature['name'].'</b> '.Tools::displayError('is too long in').' '.$currentLanguage->name;
				elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $val))
					$this->_errors[] = Tools::displayError('valid name required for feature').' <b>'.$feature['name'].'</b> '.Tools::displayError('in').' '.$currentLanguage->name;
				if (sizeof($this->_errors))
					return (0);
				// Getting default language
				if ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'))
					return ($val);
			}
		return (0);
	}


	/**
	 * Add or update a product image
	 *
	 * @param object $product Product object to add image
	 */
	public function addProductImage($product, $method = 'auto')
	{
		/* Updating an existing product image */
		if ($id_image = (intval(Tools::getValue('id_image'))))
		{
			$image = new Image($id_image);
			if (!Validate::isLoadedObject($image))
				$this->_errors[] = Tools::displayError('an error occurred while loading object image');
			else
			{
				if (($cover = Tools::getValue('cover')) == 1)
					Image::deleteCover($product->id);
				$image->cover = $cover;
				$this->validateRules('Image');
				$this->copyFromPost($image, 'image');
				if (sizeof($this->_errors) OR !$image->update())
					$this->_errors[] = Tools::displayError('an error occurred while updating image');
				elseif (isset($_FILES['image_product']['tmp_name']) AND $_FILES['image_product']['tmp_name'] != NULL)
					$this->copyImage($product->id, $image->id, $method);
			}
		}

		/* Adding a new product image */
		elseif (isset($_FILES['image_product']['tmp_name']) AND $_FILES['image_product']['tmp_name'] != NULL)
		{
			if (!Validate::isLoadedObject($product))
				$this->_errors[] = Tools::displayError('cannot add image because product add failed');
			else
			{
				$image = new Image();
				$image->id_product = intval($product->id);
				$_POST['id_product'] = $image->id_product;
				$image->position = Image::getHighestPosition($product->id) + 1;
				if (($cover = Tools::getValue('cover')) == 1)
					Image::deleteCover($product->id);
				$image->cover = !$cover ? !sizeof($product->getImages(Configuration::get('PS_LANG_DEFAULT'))) : true;
				$this->validateRules('Image', 'image');
				$this->copyFromPost($image, 'image');
				if (!sizeof($this->_errors))
				{
					if (!$image->add())
						$this->_errors[] = Tools::displayError('error while creating additional image');
					else
						$this->copyImage($product->id, $image->id, $method);
				}
			}
			$id_image = $image->id;

		}
		if (isset($image) AND Validate::isLoadedObject($image) AND !file_exists(_PS_IMG_DIR_.'p/'.$image->id_product.'-'.$image->id.'.jpg'))
			$image->delete();
		if (sizeof($this->_errors))
			return false;
		@unlink(dirname(__FILE__).'/../../img/tmp/product_'.$product->id.'.jpg');
		@unlink(dirname(__FILE__).'/../../img/tmp/product_mini_'.$product->id.'.jpg');
		return ((isset($id_image) AND is_int($id_image) AND $id_image) ? $id_image : true);
	}

	/**
	 * Copy a product image
	 *
	 * @param integer $id_product Product Id for product image filename
	 * @param integer $id_image Image Id for product image filename
	 */
	public function copyImage($id_product, $id_image, $method = 'auto')
	{
		if (!isset($_FILES['image_product']['tmp_name']) OR !file_exists($_FILES['image_product']['tmp_name']))
			return false;
		if ($error = checkImage($_FILES['image_product'], $this->maxImageSize))
			$this->_errors[] = $error;
		else
		{		
			if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['image_product']['tmp_name'], $tmpName))
				$this->_errors[] = Tools::displayError('An error occured during the image upload');
			elseif (!imageResize($tmpName, _PS_IMG_DIR_.'p/'.$id_product.'-'.$id_image.'.jpg'))
				$this->_errors[] = Tools::displayError('an error occurred while copying image');
			elseif($method == 'auto')
			{
				$imagesTypes = ImageType::getImagesTypes('products');
				foreach ($imagesTypes AS $k => $imageType)
					if (!imageResize($tmpName, _PS_IMG_DIR_.'p/'.$id_product.'-'.$id_image.'-'.stripslashes($imageType['name']).'.jpg', $imageType['width'], $imageType['height']))
						$this->_errors[] = Tools::displayError('an error occurred while copying image').' '.stripslashes($imageType['name']);
			}
			@unlink($tmpName);
			Module::hookExec('watermark', array('id_image' => $id_image, 'id_product' => $id_product));
		}
	}

	/**
	 * Add or update a product
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function submitAddproduct($token = NULL)
	{
		global $currentIndex;

		$className = 'Product';
		$rules = call_user_func(array($this->className, 'getValidationRules'), $this->className);
		$defaultLanguage = new Language(intval(Configuration::get('PS_LANG_DEFAULT')));
		$languages = Language::getLanguages();

		/* Check required fields */
		foreach ($rules['required'] AS $field)
			if (($value = Tools::getValue($field)) == false AND $value != '0')
			{
				if (Tools::getValue('id_'.$this->table) AND $field == 'passwd')
					continue;
				$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b> '.$this->l('is required');
			}

		/* Check multilingual required fields */
		foreach ($rules['requiredLang'] AS $fieldLang)
			if (!Tools::getValue($fieldLang.'_'.$defaultLanguage->id))
				$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).'</b> '.$this->l('is required at least in').' '.$defaultLanguage->name;

		/* Check fields sizes */
		foreach ($rules['size'] AS $field => $maxLength)
			if ($value = Tools::getValue($field) AND Tools::strlen($value) > $maxLength)
				$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b> '.$this->l('is too long').' ('.$maxLength.' '.$this->l('chars max').')';

		if (isset($_POST['description_short']))
		{
			$saveShort = $_POST['description_short'];
			$_POST['description_short'] = strip_tags($_POST['description_short']);
		}

		/* Check description short size without html */
		foreach ($languages AS $language)
			if ($value = Tools::getValue('description_short_'.$language['id_lang']))
				if (Tools::strlen(strip_tags($value)) > 400)
					$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), 'description_short').' ('.$language['name'].')</b> '.$this->l('is too long').' : 400 '.$this->l('chars max').' ('.$this->l('count now').' '.Tools::strlen(strip_tags($value)).')';
		/* Check multilingual fields sizes */
		foreach ($rules['sizeLang'] AS $fieldLang => $maxLength)
			foreach ($languages AS $language)
				if ($value = Tools::getValue($fieldLang.'_'.$language['id_lang']) AND Tools::strlen($value) > $maxLength)
					$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).' ('.$language['name'].')</b> '.$this->l('is too long').' ('.$maxLength.' '.$this->l('chars max').')';
		if (isset($_POST['description_short']))
			$_POST['description_short'] = $saveShort;

		/* Check fields validity */
		foreach ($rules['validate'] AS $field => $function)
			if ($value = Tools::getValue($field))
				if (!Validate::$function($value))
					$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b> '.$this->l('is invalid');

		/* Check multilingual fields validity */
		foreach ($rules['validateLang'] AS $fieldLang => $function)
			foreach ($languages AS $language)
				if ($value = Tools::getValue($fieldLang.'_'.$language['id_lang']))
					if (!Validate::$function($value))
						$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).' ('.$language['name'].')</b> '.$this->l('is invalid');
		$productCats = '';
		if (!isset($_POST['categoryBox']) OR !sizeof($_POST['categoryBox']))
			$this->_errors[] = $this->l('product must be in at least one Category');

		foreach ($languages AS $language)
			if ($value = Tools::getValue('tags_'.$language['id_lang']))
				if (!Validate::isTagsList($value))
					$this->_errors[] = $this->l('Tags list').' ('.$language['name'].') '.$this->l('is invalid');
		if (!sizeof($this->_errors))
		{
			$id = intval(Tools::getValue('id_'.$this->table));
			$tagError = true;

			/* Update an existing product */
			if (isset($id) AND !empty($id))
			{
				$object = new $this->className($id);
				if (Validate::isLoadedObject($object))
				{
					$this->copyFromPost($object, $this->table);
					if ($object->update())
					{
						$this->updateAccessories($object);
						$this->updateDownloadProduct($object);
						if (!$this->updatePackItems($object))
							$this->_errors[] = Tools::displayError('an error occurred while adding products to the pack');
						elseif (!$object->updateCategories($_POST['categoryBox'], true))
							$this->_errors[] = Tools::displayError('an error occurred while linking object').' <b>'.$this->table.'</b> '.Tools::displayError('to categories');
						elseif (!$this->updateTags($languages, $object))
							$this->_errors[] = Tools::displayError('an error occurred while adding tags');
						elseif ($id_image = $this->addProductImage($object, Tools::getValue('resizer')))
						{
							Hook::updateProduct($object);
							Search::indexation(false);
							if (Tools::getValue('resizer') == 'man' && isset($id_image) AND is_int($id_image) AND $id_image)
								Tools::redirectAdmin($currentIndex.'&id_product='.$object->id.'&id_category='.intval(Tools::getValue('id_category')).'&edit='.strval(Tools::getValue('productCreated')).'&id_image='.$id_image.'&imageresize&toconf=4&submitAddAndStay='.((Tools::isSubmit('submitAdd'.$this->table.'AndStay') OR Tools::getValue('productCreated') == 'on') ? 'on' : 'off').'&token='.(($token ? $token : $this->token)));
							// Save and stay on same form
							if (Tools::isSubmit('submitAdd'.$this->table.'AndStay') OR ($id_image AND $id_image !== true))
								Tools::redirectAdmin($currentIndex.'&id_product='.$object->id.'&id_category='.intval(Tools::getValue('id_category')).'&addproduct&conf=4&tabs='.intval(Tools::getValue('tabs')).'&token='.($token ? $token : $this->token));
							// Default behavior (save and back)
							Tools::redirectAdmin($currentIndex.'&id_category='.intval(Tools::getValue('id_category')).'&conf=4&token='.($token ? $token : $this->token));
						}
					}
					else
						$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
				}
				else
					$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b> ('.Tools::displayError('cannot load object').')';
			}

			/* Add a new product */
			else
			{
				$object = new $this->className();
				$this->copyFromPost($object, $this->table);
				if ($object->add())
				{
					$this->updateAccessories($object);
					if (!$this->updatePackItems($object))
						$this->_errors[] = Tools::displayError('an error occurred while adding products to the pack');
					$this->updateDownloadProduct($object);
					if (!sizeof($this->_errors))
					{
						if (!$object->updateCategories($_POST['categoryBox']))
							$this->_errors[] = Tools::displayError('an error occurred while linking object').' <b>'.$this->table.'</b> '.Tools::displayError('to categories');
						else if (!$this->updateTags($languages, $object))
							$this->_errors[] = Tools::displayError('an error occurred while adding tags');
						elseif ($id_image = $this->addProductImage($object))
						{
							Hook::addProduct($object);
							Search::indexation(false);
							if (Tools::getValue('resizer') == 'man' && isset($id_image) AND is_int($id_image) AND $id_image)
								Tools::redirectAdmin($currentIndex.'&id_product='.$object->id.'&id_category='.intval(Tools::getValue('id_category')).'&id_image='.$id_image.'&imageresize&toconf=3&submitAddAndStay='.(Tools::isSubmit('submitAdd'.$this->table.'AndStay') ? 'on' : 'off').'&token='.(($token ? $token : $this->token)));
							// Save and stay on same form
							if (Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
								Tools::redirectAdmin($currentIndex.'&id_product='.$object->id.'&id_category='.intval(Tools::getValue('id_category')).'&addproduct&conf=3&tabs='.intval(Tools::getValue('tabs')).'&token='.($token ? $token : $this->token));
							// Default behavior (save and back)
							Tools::redirectAdmin($currentIndex.'&id_category='.intval(Tools::getValue('id_category')).'&conf=3&token='.($token ? $token : $this->token));
						}
					}
					else
						$object->delete();
				}
				else
					$this->_errors[] = Tools::displayError('an error occurred while creating object').' <b>'.$this->table.'</b>';
			}
		}

	}

	/**
	 * Update product download
	 *
	 * @param object $product Product
	 */
	public function updateDownloadProduct($product)
	{
		/* add or update a virtual product */
		if (Tools::getValue('is_virtual_good') == 'true')
		{
			if (!Tools::getValue('virtual_product_name'))
			{
				$this->_errors[] = $this->l('the field').' <b>'.$this->l('display filename').'</b> '.$this->l('is required');
				return false;
			}
			if (!Tools::getValue('virtual_product_nb_days'))
			{
				$this->_errors[] = $this->l('the field').' <b>'.$this->l('number of days').'</b> '.$this->l('is required');
				return false;
			}
			if (Tools::getValue('virtual_product_expiration_date') AND !Validate::isDate(Tools::getValue('virtual_product_expiration_date')))
			{
				$this->_errors[] = $this->l('the field').' <b>'.$this->l('expiration date').'</b> '.$this->l('is not valid');
				return false;
			}
			// The oos behavior MUST be "Deny orders" for virtual products
			if (Tools::getValue('out_of_stock') != 0)
			{
				$this->_errors[] = $this->l('The "when out of stock" behavior selection must be "deny order" for virtual products');
				return false;
			}

			$download = new ProductDownload(Tools::getValue('virtual_product_id'));
			$download->id_product          = $product->id;
			$download->display_filename    = Tools::getValue('virtual_product_name');
			$download->physically_filename = Tools::getValue('virtual_product_filename') ? Tools::getValue('virtual_product_filename') : $download->getNewFilename();
			$download->date_deposit        = date('Y-m-d H:i:s');
			$download->date_expiration     = Tools::getValue('virtual_product_expiration_date') ? Tools::getValue('virtual_product_expiration_date').' 23:59:59' : '';
			$download->nb_days_accessible  = Tools::getValue('virtual_product_nb_days');
			$download->nb_downloadable     = Tools::getValue('virtual_product_nb_downloable');
			$download->active              = 1;
			if ($download->save())
				return true;
		}
		else
		{
			/* unactive download product if checkbox not checked */
			if ($id_product_download = ProductDownload::getIdFromIdProduct($product->id))
			{
				$productDownload = new ProductDownload($id_product_download);
				$productDownload->date_expiration = date('Y-m-d H:i:s', time()-1);
				$productDownload->active = 0;
				return $productDownload->save();
			}
		}
		return false;
	}

	/**
	 * Update product accessories
	 *
	 * @param object $product Product
	 */
	public function updateAccessories($product)
	{
		$product->deleteAccessories();
		if ($accessories = Tools::getValue('inputAccessories'))
		{
			$accessories_id = array_unique(explode('-', $accessories));
			if (sizeof($accessories_id))
			{
				array_pop($accessories_id);
				$product->changeAccessories($accessories_id);
			}
		}
	}

	/**
	 * Update product tags
	 *
	 * @param array Languages
	 * @param object $product Product
	 * @return boolean Update result
	 */
	public function updateTags($languages, $product)
	{
		$tagError = true;
		/* Reset all tags for THIS product */
		if (!Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_tag`
		WHERE `id_product` = '.intval($product->id)))
			return false;
		/* Assign tags to this product */
		foreach ($languages AS $language)
			if ($value = Tools::getValue('tags_'.$language['id_lang']))
				$tagError &= Tag::addTags($language['id_lang'], intval($product->id), $value);
		return $tagError;
	}

	public function display($token = NULL)
	{
		global $currentIndex, $cookie;

		$this->getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'position' : NULL, !Tools::getValue($this->table.'Orderway') ? 'ASC' : NULL);
		$id_category = intval(Tools::getValue('id_category'));
		if (!$id_category)
			$id_category = 1;
		echo '<h3>'.(!$this->_listTotal ? ($this->l('No products found')) : ($this->_listTotal.' '.($this->_listTotal > 1 ? $this->l('products') : $this->l('product')))).' '.
		$this->l('in category').' "'.stripslashes(Category::hideCategoryPosition($this->_category->getName())).'"</h3>';
		echo '<a href="'.$currentIndex.'&id_category='.$id_category.'&add'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new product').'</a>
		<div style="margin:10px;">';
		$this->displayList($token);
		echo '</div>';
	}

	public function displayList($token = NULL)
	{
		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader($token);
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.sizeof($this->fieldsDisplay).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent($token);

		/* Close list table and submit button */
		$this->displayListFooter($token);
	}

	/**
	 * Build a categories tree
	 *
	 * @param array $indexedCategories Array with categories where product is indexed (in order to check checkbox)
	 * @param array $categories Categories to list
	 * @param array $current Current category
	 * @param integer $id_category Current category id
	 */
	function recurseCategoryForInclude($indexedCategories, $categories, $current, $id_category = 1, $id_category_default = NULL)
	{
		global $done;
		static $irow;
		$id_obj = intval(Tools::getValue($this->identifier));

		if (!isset($done[$current['infos']['id_parent']]))
			$done[$current['infos']['id_parent']] = 0;
		$done[$current['infos']['id_parent']] += 1;

		$todo = sizeof($categories[$current['infos']['id_parent']]);
		$doneC = $done[$current['infos']['id_parent']];

		$level = $current['infos']['level_depth'] + 1;
		$img = $level == 1 ? 'lv1.gif' : 'lv'.$level.'_'.($todo == $doneC ? 'f' : 'b').'.gif';

		echo '
		<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
			<td>
				<input type="checkbox" name="categoryBox[]" class="categoryBox'.($id_category_default != NULL ? ' id_category_default' : '').'" id="categoryBox_'.$id_category.'" value="'.$id_category.'"'.((in_array($id_category, $indexedCategories) OR (intval(Tools::getValue('id_category')) == $id_category AND !intval($id_obj))) ? ' checked="checked"' : '').' />
			</td>
			<td>
				'.$id_category.'
			</td>
			<td>
				<img src="../img/admin/'.$img.'" alt="" /> &nbsp;<label for="categoryBox_'.$id_category.'" class="t">'.stripslashes(Category::hideCategoryPosition($current['infos']['name'])).'</label>
			</td>
		</tr>';

		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] AS $key => $row)
				if ($key != 'infos')
					$this->recurseCategoryForInclude($indexedCategories, $categories, $categories[$id_category][$key], $key);
	}
	
	public function displayErrors()
	{
		if ($this->includeSubTab('displayErrors'))
			;
		elseif ($nbErrors = sizeof($this->_errors))
		{
			echo '<div class="alert error"><h3>'.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol style="margin-left: 30px;">';
			foreach ($this->_errors AS $error)
				echo '<li>'.$error.'</li>';
			echo '
			</ol></div>';
		}
	}

	function displayForm($token = NULL)
	{
		global $currentIndex, $link, $cookie;
		
		if ($id_category_back = intval(Tools::getValue('id_category')))
			$currentIndex .= '&id_category='.$id_category_back;

		$obj = $this->loadObject(true);
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();

		if ($obj->id)
			$currentIndex .= '&id_product='.$obj->id;

		echo '
		<h3>'.$this->l('Current product:').' <span id="current_product" style="font-weight: normal;">'.$this->l('no name').'</span></h3>
		<script type="text/javascript">
			var pos_select = '.(($tab = Tools::getValue('tabs')) ? $tab : '0').';
			id_language = Number('.$defaultLanguage.');
			'.$this->initCombinationImagesJS().'
		</script>
		<script src="../js/tabpane.js" type="text/javascript"></script>
		<link type="text/css" rel="stylesheet" href="../css/tabpane.css" />
		<form action="'.$currentIndex.'&token='.($token != NULL ? $token : $this->token).'" method="post" enctype="multipart/form-data" name="product" id="product">
			<input type="hidden" name="tabs" id="tabs" value="0" />
			<input type="hidden" name="id_category" value="'.(($id_category = Tools::getValue('id_category')) ? intval($id_category) : '0').'">
			<div class="tab-pane" id="tabPane1">';
				/* Tabs */
		$this->displayFormInformations($obj, $currency, $languages, $defaultLanguage);
		$this->displayFormImages($obj, $languages, $defaultLanguage, $token);
		if ($obj->id)
			echo '
			<div class="tab-page" id="step3"><h4 class="tab">3. '.$this->l('Combinations').'</h4></div>
			<div class="tab-page" id="step4"><h4 class="tab">4. '.$this->l('Features').'</h4></div>
			<div class="tab-page" id="step5"><h4 class="tab">5. '.$this->l('Customization').'</h4></div>
			<div class="tab-page" id="step6""><h4 class="tab">6. '.$this->l('Discounts').'</h4></div>
			<div class="tab-page" id="step7""><h4 class="tab">7. '.$this->l('Attachments').'</h4></div>';
		echo '	<script type="text/javascript">
					var toload = new Array();
					toload[3] = true;
					toload[4] = true;
					toload[5] = true;
					toload[6] = true;
					toload[7] = true;
					function loadTab(id) {';
		if ($obj->id)
			echo ' 		if (toload[id]) {
							toload[id] = false;
							$.post("'.dirname($currentIndex).'/ajax.php",{ajaxProductTab:id,id_product:'.$obj->id.',token:\''.Tools::getValue('token').'\',id_category:'.intval(Tools::getValue('id_category')).'},
								function(rep) {
									getE("step" + id).innerHTML = rep;
									if (id == 3) populate_attrs();
									if (id == 7)
									{
										$(\'#addAttachment\').click(function() {
											return !$(\'#selectAttachment1 option:selected\').remove().appendTo(\'#selectAttachment2\');  
										});  
										$(\'#removeAttachment\').click(function() {  
											return !$(\'#selectAttachment2 option:selected\').remove().appendTo(\'#selectAttachment1\');  
										});  
										$(\'#product\').submit(function() {  
											$(\'#selectAttachment1 option\').each(function(i) {  
												$(this).attr("selected", "selected");  
											});  
										});
									}
								}
							)
						}';
		echo '		}
				</script>
			</div>
			<div class="clear"></div>
			<input type="hidden" name="id_product_attribute" id="id_product_attribute" value="0" />
		</form>';
		if (Tools::getValue('id_category') > 1)
			echo '<br /><br /><a href="'.$currentIndex.($token ? '&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)) : '').'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to the category').'</a><br />';
	}

	function displayFormQuantityDiscount($obj, $languages, $defaultLanguage)
	{
		global $cookie, $currentIndex;

		if ($obj->id)
		{
			$quantityDiscounts = QuantityDiscount::getQuantityDiscounts($obj->id, false);
			$defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
			echo '
			<table cellpadding="5">
				<tr>
					<td colspan="2"><b>'.$this->l('Add quantity discount to this product').'</b></td>
				</tr>
			</table>
			<hr style="width:730px;"><br />
			<table cellpadding="5" style="width:100%">
				<tr>
					<td style="width:150" valign="top">'.$this->l('Product quantity:').'</td>
					<td>
						<input type="text" name="quantity_discount" size="10" />
						<p style="padding:0px; margin:0px 0px 10px 0px;">'.$this->l('Minimum product quantity for discount').'</p>
					</td>
				</tr>
				<tr>
					<td style="width:150" valign="top">'.$this->l('Discount value:').'</td>
					<td>
						<input type="text" name="value_discount" size="10" />
						<p style="padding:0px; margin:0px 0px 10px 0px;">'.$this->l('The discount value (% or amount)').'</p>
					</td>
				</tr>
				<tr>
					<td style="width:150" valign="top">'.$this->l('Discount type:').'</td>
					<td>
						<select name="id_discount_type">
							<option value="1">'.$this->l('By %').'</option>
							<option value="2">'.$this->l('By amount').'</option>
						</select>
						<p style="padding:0px; margin:0px 0px 10px 0px;">'.$this->l('Will be applied on final product price').'</p>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center;">
						<input type="submit" name="submitQuantityDiscount" id="submitQuantityDiscount" value="'.$this->l('Add quantity discount').'" class="button" onclick="this.form.action += \'&addproduct&tabs=5\';" />
					</td>
				</tr>
				<tr><td colspan="2"><hr style="width:730px;"></td></tr>
				<tr>
					<td colspan="2"  style="text-align:center;">
						<table border="0" cellpadding="0" cellspacing="0" class="table" style="width:270px; margin:auto;">
							<tr>
								<th style="width:20px">'.$this->l('ID').'</td>
								<th style="width:100px">'.$this->l('# products').'</td>
								<th style="width:100px">'.$this->l('Discount').'</td>
								<th style="width:50px">'.$this->l('Action').'</td>
							</tr>';
			// Listing
			$irow = 0;
			if (is_array($quantityDiscounts) AND sizeof($quantityDiscounts))
			{
				foreach ($quantityDiscounts as $qD)
					echo '
							<tr '.($irow++ % 2 ? ' class="alt_row"' : '').'>
								<td style="width:25px" style="text-align:center;">'.$qD['id_discount_quantity'].'</td>
								<td style="width:100px">&nbsp;'.$qD['quantity'].'</td>
								<td style="width:100px">'.($qD['id_discount_type'] == 1 ? $qD['value'].'%' : Tools::displayPrice($qD['value'], $defaultCurrency)).'</td>
								<td style="width:50px" style="text-align:center;">
									<a href="index.php?tab=AdminCatalog&id_category='.Tools::getValue('id_category').'&id_product='.Tools::getValue('id_product').'&token='.Tools::getValue('token').'&deleteQuantityDiscount&id_quantity_discount='.$qD['id_discount_quantity'].'&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure?', __CLASS__, true, false).'\');">
										<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this discount').'" />
									</a>
								</td>
							</tr>';
			}
			else
				echo '
							<tr><td colspan="4" style="text-align:center;">'.$this->l('No quantity discount defined').'</td></tr>';
			echo '
						</table>
					</td>
				</tr>
			</table>';
		}
		else
			echo '<b>'.$this->l('You must save this product before adding quantity discounts').'.</b>';
	}

	private function _getCustomizationFieldIds($labels, $alreadyGenerated, $obj)
	{
		$customizableFieldIds = array();
		if (isset($labels[_CUSTOMIZE_FILE_]))
			foreach ($labels[_CUSTOMIZE_FILE_] AS $id_customization_field => $label)
				$customizableFieldIds[] = 'label_'._CUSTOMIZE_FILE_.'_'.intval($id_customization_field);
		if (isset($labels[_CUSTOMIZE_TEXTFIELD_]))
			foreach ($labels[_CUSTOMIZE_TEXTFIELD_] AS $id_customization_field => $label)
				$customizableFieldIds[] = 'label_'._CUSTOMIZE_TEXTFIELD_.'_'.intval($id_customization_field);
		$j = 0;
		for ($i = $alreadyGenerated[_CUSTOMIZE_FILE_]; $i < intval($this->getFieldValue($obj, 'uploadable_files')); $i++)
			$customizableFieldIds[] = 'newLabel_'._CUSTOMIZE_FILE_.'_'.$j++;
		$j = 0;
		for ($i = $alreadyGenerated[_CUSTOMIZE_TEXTFIELD_]; $i < intval($this->getFieldValue($obj, 'text_fields')); $i++)
			$customizableFieldIds[] = 'newLabel_'._CUSTOMIZE_TEXTFIELD_.'_'.$j++;
		return implode('¤', $customizableFieldIds);
	}

	private function _displayLabelField(&$label, $languages, $defaultLanguage, $type, $fieldIds, $id_customization_field)
	{
		$fieldsName = 'label_'.$type.'_'.intval($id_customization_field);
		$fieldsContainerName = 'labelContainer_'.$type.'_'.intval($id_customization_field);
		echo '<div id="'.$fieldsContainerName.'">';
		foreach ($languages as $language)
		{
			$fieldName = 'label_'.$type.'_'.intval($id_customization_field).'_'.intval($language['id_lang']);
			echo '<div id="'.$fieldName.'" style="display: '.(intval($language['id_lang']) == intval($defaultLanguage) ? 'block' : 'none').'; clear: left; float: left; padding-bottom: 4px;">
					<div style="width:40px; float:left; text-align:right;">#'.intval($id_customization_field).'</div>
					&nbsp;&nbsp;<input type="text" name="'.$fieldName.'" value="'.htmlentities($label[intval($language['id_lang'])]['name'], ENT_COMPAT, 'UTF-8').'" />
				</div>';
		}
		echo '</div>';
		$this->displayFlags($languages, $defaultLanguage, $fieldIds, $fieldsName);
		echo '<div style="float: left; margin-left: 16px;"><input type="checkbox" name="require_'.$type.'_'.intval($id_customization_field).'" value="1" '.($label[intval($language['id_lang'])]['required'] ? 'checked="checked"' : '').'/> '.$this->l('required').'</div>';
	}

	private function _displayLabelFields(&$obj, &$labels, $languages, $defaultLanguage, $type)
	{
		$type = intval($type);
		$labelGenerated = array(_CUSTOMIZE_FILE_ => (isset($labels[_CUSTOMIZE_FILE_]) ? count($labels[_CUSTOMIZE_FILE_]) : 0), _CUSTOMIZE_TEXTFIELD_ => (isset($labels[_CUSTOMIZE_TEXTFIELD_]) ? count($labels[_CUSTOMIZE_TEXTFIELD_]) : 0));

		$fieldIds = $this->_getCustomizationFieldIds($labels, $labelGenerated, $obj);
		if (isset($labels[$type]))
			foreach ($labels[$type] AS $id_customization_field => $label)
				$this->_displayLabelField($label, $languages, $defaultLanguage, $type, $fieldIds, intval($id_customization_field));
	}

	function displayFormCustomization($obj, $languages, $defaultLanguage)
	{
		$labels = $obj->getCustomizationFields();
		$defaultIso = Language::getIsoById($defaultLanguage);
		
		$hasFileLabels = intval($this->getFieldValue($obj, 'uploadable_files'));
		$hasTextLabels = intval($this->getFieldValue($obj, 'text_fields'));

		echo '
			<table cellpadding="5">
				<tr>
					<td colspan="2"><b>'.$this->l('Add or modify customizable properties').'</b></td>
				</tr>
			</table>
			<hr style="width:730px;"><br />
			<table cellpadding="5" style="width:100%">
				<tr>
					<td style="width:150px" valign="top">'.$this->l('File fields:').'</td>
					<td style="padding-bottom:5px;">
						<input type="text" name="uploadable_files" id="uploadable_files" size="4" value="'.(intval($this->getFieldValue($obj, 'uploadable_files')) ? intval($this->getFieldValue($obj, 'uploadable_files')) : '0').'" />
						<p>'.$this->l('Number of upload file fields displayed').'</p>
					</td>
				</tr>
				<tr>
					<td style="width:150px" valign="top">'.$this->l('Text fields:').'</td>
					<td style="padding-bottom:5px;">
						<input type="text" name="text_fields" id="text_fields" size="4" value="'.(intval($this->getFieldValue($obj, 'text_fields')) ? intval($this->getFieldValue($obj, 'text_fields')) : '0').'" />
						<p>'.$this->l('Number of text fields displayed').'</p>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center;">
						<input type="submit" name="submitCustomizationConfiguration" value="'.$this->l('Update settings').'" class="button" onclick="this.form.action += \'&addproduct&tabs=4\';" />
					</td>
				</tr>';
				
				if ($hasFileLabels)
				{
					echo '
				<tr><td colspan="2"><hr style="width:730px;"></td></tr>
				<tr>
					<td style="width:150px" valign="top">'.$this->l('Files fields:').'</td>
					<td style="padding-bottom:5px;">';
					$this->_displayLabelFields($obj, $labels, $languages, $defaultLanguage, _CUSTOMIZE_FILE_);
					echo '
					</td>
				</tr>';
				}
				
				if ($hasTextLabels)
				{
					echo '
				<tr><td colspan="2"><hr style="width:730px;"></td></tr>
				<tr>
					<td style="width:150px" valign="top">'.$this->l('Text fields:').'</td>
					<td style="padding-bottom:5px;">';
					$this->_displayLabelFields($obj, $labels, $languages, $defaultLanguage, _CUSTOMIZE_TEXTFIELD_);
					echo '
					</td>
				</tr>';
				}
				
				echo '
				<tr>
					<td colspan="2" style="text-align:center;">';
				
				if ($hasFileLabels OR $hasTextLabels)
					echo '<input type="submit" name="submitProductCustomization" id="submitProductCustomization" value="'.$this->l('Save labels').'" class="button" onclick="this.form.action += \'&addproduct&tabs=4\';" />';
				echo '
					</td>
				</tr>
			</table>';
	}
	
	function displayFormAttachments($obj, $languages, $defaultLanguage)
	{
		global $currentIndex, $cookie;
		$obj = $this->loadObject(true);
		$languages = Language::getLanguages();
		$attach1 = Attachment::getAttachments($cookie->id_lang, $obj->id, true);
		$attach2 = Attachment::getAttachments($cookie->id_lang, $obj->id, false);
		
		echo '
		<a href="index.php?tab=AdminAttachments&addattachment&token='.Tools::getAdminToken('AdminAttachments'.intval(Tab::getIdFromClassName('AdminAttachments')).intval($cookie->id_employee)).'">
			<img src="../img/admin/add.gif" alt="new" title="'.$this->l('Upload new attachment').'" />&nbsp;'.$this->l('Upload new attachment').'
		</a>
		<div class="clear">&nbsp;</div>
		<table><tr>
			<td>
				<select multiple id="selectAttachment1" name="attachments[]" style="width:300px;height:160px;">';
		foreach ($attach1 as $attach)
			echo '	<option value="'.$attach['id_attachment'].'">'.$attach['name'].'</option>';
		echo '	</select><br /><br />
				<a href="#" id="addAttachment" style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
					'.$this->l('Remove').' &gt;&gt;
				</a>
			</td>
			<td style="padding-left:20px;">
				<select multiple id="selectAttachment2" style="width:300px;height:160px;">';
		foreach ($attach2 as $attach)
			echo '	<option value="'.$attach['id_attachment'].'">'.$attach['name'].'</option>';
		echo '	</select><br /><br />
				<a href="#" id="removeAttachment" style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
					&lt;&lt; '.$this->l('Add').'
				</a>
			</div>
			</td>
		</tr></table>
		<div class="clear">&nbsp;</div>
		<input type="submit" name="submitAttachments" id="submitAttachments" value="'.$this->l('Update attachments').'" class="button" />';
	}

	function displayFormInformations($obj, $currency, $languages, $defaultLanguage)
	{
		global $currentIndex, $cookie;
		$iso = Language::getIsoById(intval($cookie->id_lang));

		$divLangName = 'cname¤cdesc¤cdesc_short¤clink_rewrite¤cmeta_description¤cmeta_title¤cmeta_keywords¤ctags¤cavailable_now¤cavailable_later';
		$qty_state = 'readonly';
		$qty = Attribute::getAttributeQty($this->getFieldValue($obj, 'id_product'));
		if ($qty === false) {
			if (Validate::isLoadedObject($obj))
				$qty = $this->getFieldValue($obj, 'quantity');
			else
				$qty = 1;
			$qty_state = '';
		}
		$cover = Product::getCover($obj->id);
		$link = new Link();

		//includeDatepicker(array('reduction_from', 'reduction_to'));
		echo '
		<div class="tab-page" id="step1">
			<h4 class="tab">1. '.$this->l('Info.').'</h4>
			<b>'.$this->l('Product global informations').'</b>&nbsp;-&nbsp;';
			if (isset($obj->id))
			{
				echo '
			<a href="'.($link->getProductLink($this->getFieldValue($obj, 'id'), $this->getFieldValue($obj, 'link_rewrite', $defaultLanguage), Category::getLinkRewrite($this->getFieldValue($obj, 'id_category_default'), intval($cookie->id_lang)))).'"><img src="../img/admin/details.gif" alt="'.$this->l('View product in shop').'" title="'.$this->l('View product in shop').'" /> '.$this->l('View product in shop').'</a>';
				if (file_exists(_PS_MODULE_DIR_.'statsproduct/statsproduct.php'))
					echo '&nbsp;-&nbsp;
					<a href="index.php?tab=AdminStatsModules&module=statsproduct&id_product='.$obj->id.'&token='.Tools::getAdminToken('AdminStatsModules'.intval(Tab::getIdFromClassName('AdminStatsModules')).intval($cookie->id_employee)).'"><img src="../modules/statsproduct/logo.gif" alt="'.$this->l('View product sales').'" title="'.$this->l('View product sales').'" /> '.$this->l('View product sales').'</a>';
			}
			echo '	
			<hr class="clear"/>
			<br />
				<table cellpadding="5" style="width:100%">
					<tr>
						<td class="col-left">'.$this->l('Name:').'</td>
						<td style="padding-bottom:5px;">';
		foreach ($languages as $language)
			echo '			<div id="cname_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
								<input size="55" type="text" id="name_'.$language['id_lang'].'" name="name_'.$language['id_lang'].'"
								value="'.stripslashes(htmlspecialchars($this->getFieldValue($obj, 'name', $language['id_lang']))).'"'.((!$obj->id) ? ' onkeyup="copy2friendlyURL();"' : '').' onchange="updateCurrentText();" /><sup> *</sup>
								<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cname');
		echo '<script type="text/javascript">updateCurrentText();</script>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top">'.$this->l('Status:').'</td>
						<td style="padding-bottom:5px;">
							<input style="float:left;" type="radio" name="active" id="active_on" value="1" '.($this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
							<label for="active_on" class="t"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="float:left; padding:0px 5px 0px 5px;" />'.$this->l('Enabled').'</label>
							<br style="clear:both;" />
							<input style="float:left;" type="radio" name="active" id="active_off" value="0" '.(!$this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
							<label for="active_off" class="t"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="float:left; padding:0px 5px 0px 5px" />'.$this->l('Disabled').'</label>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Manufacturer:').'</td>
						<td style="padding-bottom:5px;">
							<select name="id_manufacturer" id="id_manufacturer">
								<option value="0">-- '.$this->l('Choose (optional)').' --</option>';
		if ($id_manufacturer = $this->getFieldValue($obj, 'id_manufacturer'))
			echo '				<option value="'.$id_manufacturer.'" selected="selected">'.Manufacturer::getNameById($id_manufacturer).'</option>
								<option disabled="disabled">----------</option>';
		echo '
							</select>&nbsp;&nbsp;&nbsp;<a href="?tab=AdminManufacturers&addmanufacturer&token='.Tools::getAdminToken('AdminManufacturers'.intval(Tab::getIdFromClassName('AdminManufacturers')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure you want to delete entered product information?', __CLASS__, true, false).'\');"><img src="../img/admin/add.gif" alt="'.$this->l('Create').'" title="'.$this->l('Create').'" /> <b>'.$this->l('Create').'</b></a>
							<script type="text/javascript">
								var ajaxManufacturersClicked = false;
								$("select#id_manufacturer").focus(
									function() {
										if (ajaxManufacturersClicked == true) return; else ajaxManufacturersClicked = true;
										$.getJSON("'.dirname($currentIndex).'/ajax.php",{ajaxProductManufacturers:1},
											function(j) {
												var options = \'\';
												for (var i = 0; i < getE("id_manufacturer").options.length; i++)
												{
													if (getE("id_manufacturer").options[i].innerHTML == \'----------\')
														options += \'<option disabled="disabled">----------</option>\';
													else
														options += \'<option value="\' +  getE("id_manufacturer").options[i].value + \'">\' + getE("id_manufacturer").options[i].innerHTML + \'</option>\';
												}
												for (var i = 0; i < j.length; i++)
													options += \'<option value="\' + j[i].optionValue + \'">\' + j[i].optionDisplay + \'</option>\';
												$("select#id_manufacturer").html(options);
											}
										)
									}
								);
							</script>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Supplier:').'</td>
						<td style="padding-bottom:5px;">
							<select name="id_supplier" id="id_supplier">
								<option value="0">-- '.$this->l('Choose (optional)').' --</option>';
		if ($id_supplier = $this->getFieldValue($obj, 'id_supplier'))
			echo '				<option value="'.$id_supplier.'" selected="selected">'.Supplier::getNameById($id_supplier).'</option>
								<option disabled="disabled">----------</option>';
		echo '
							</select>&nbsp;&nbsp;&nbsp;<a href="?tab=AdminSuppliers&addsupplier&token='.Tools::getAdminToken('AdminSuppliers'.intval(Tab::getIdFromClassName('AdminSuppliers')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure you want to delete entered product information?', __CLASS__, true, false).'\');"><img src="../img/admin/add.gif" alt="'.$this->l('Create').'" title="'.$this->l('Create').'" /> <b>'.$this->l('Create').'</b></a>
							<script type="text/javascript">
								var ajaxSuppliersClicked = false;
								$("select#id_supplier").focus(
									function() {
										if (ajaxSuppliersClicked == true) return; else ajaxSuppliersClicked = true;
										$.getJSON("'.dirname($currentIndex).'/ajax.php",{ajaxProductSuppliers:1},
											function(j) {
												var options = \'\';
												for (var i = 0; i < getE("id_supplier").options.length; i++)
												{
													if (getE("id_supplier").options[i].innerHTML == \'----------\')
														options += \'<option disabled="disabled">----------</option>\';
													else
														options += \'<option value="\' +  getE("id_supplier").options[i].value + \'">\' + getE("id_supplier").options[i].innerHTML + \'</option>\';
												}
												for (var i = 0; i < j.length; i++)
													options += \'<option value="\' + j[i].optionValue + \'">\' + j[i].optionDisplay + \'</option>\';
												$("select#id_supplier").html(options);
											}
										)
									}
								);
							</script>
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Reference:').'</td>
						<td style="padding-bottom:5px;">
							<input size="55" type="text" name="reference" value="'.htmlentities($this->getFieldValue($obj, 'reference'), ENT_COMPAT, 'UTF-8').'" style="width: 130px; margin-right: 44px;" />
							'.$this->l('EAN13:').'<input size="55" maxlength="13" type="text" name="ean13" value="'.$this->getFieldValue($obj, 'ean13').'" style="width: 110px; margin-left: 10px;" />
							<span class="hint" name="help_box">'.$this->l('Special characters allowed:').' .-_#\<span class="hint-pointer">&nbsp;</span></span>
						</td>
					</tr>
                	<tr>
						<td class="col-left">'.$this->l('Supplier Reference:').'</td>
						<td style="padding-bottom:5px;">
							<input size="55" type="text" name="supplier_reference" value="'.htmlentities($this->getFieldValue($obj, 'supplier_reference'), ENT_COMPAT, 'UTF-8').'" style="width: 130px; margin-right: 44px;" />
							'.$this->l('Location:').'<input size="55" type="text" name="location" value="'.$this->getFieldValue($obj, 'location').'" style="width: 101px; margin-left: 10px;" />
							<span class="hint" name="help_box">'.$this->l('Special characters allowed:').' .-_#\<span class="hint-pointer">&nbsp;</span></span>
						</td>
					</tr>					
					<tr>
						<td class="col-left">'.$this->l('Weight:').'</td>
						<td style="padding-bottom:5px;">
							<input size="6" maxlength="6" name="weight" type="text" value="'.htmlentities($this->getFieldValue($obj, 'weight'), ENT_COMPAT, 'UTF-8').'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> '.Configuration::get('PS_WEIGHT_UNIT').'
						</td>
					</tr>
					<tr><td colspan="2"><hr style="width:730px;"></td></tr>';
					$this->displayPack($obj);
		echo '		<tr><td colspan="2"><hr style="width:730px;"></td></tr>';

/*
 * Form for add a virtual product like software, mp3, etc...
 */
	$productDownload = new ProductDownload();
	if ($id_product_download = $productDownload->getIdFromIdProduct($this->getFieldValue($obj, 'id')))
		$productDownload = new ProductDownload($id_product_download);

?>
    <script type="text/javascript">
    // <![CDATA[
    	ThickboxI18nImage = '<?php echo $this->l('Image') ?>';
    	ThickboxI18nOf = '<?php echo $this->l('of') ?>';;
    	ThickboxI18nClose = '<?php echo $this->l('Close') ?>';
    	ThickboxI18nOrEscKey = '<?php echo $this->l('(or "Esc")') ?>';
    	ThickboxI18nNext = '<?php echo $this->l('Next >') ?>';
    	ThickboxI18nPrev = '<?php echo $this->l('< Previous') ?>';
    	tb_pathToImage = '../img/loadingAnimation.gif';
    //]]>
    </script>
	<script type="text/javascript" src="<?php echo _PS_JS_DIR_ ?>jquery/thickbox-modified.js"></script>
	<script type="text/javascript" src="<?php echo _PS_JS_DIR_ ?>jquery/ajaxfileupload.js"></script>
	<script type="text/javascript" src="<?php echo _PS_JS_DIR_ ?>date.js"></script>
	<style type="text/css">
		<!--
		@import url(<?php echo _PS_CSS_DIR_?>thickbox.css);
		-->
	</style>
	<script type="text/javascript">
	<!--	
	function toggleVirtualProduct(elt)
	{
		if (elt.checked)
		{
			$('#virtual_good').show('slow');
			getE('out_of_stock_1').checked = 'checked';
			getE('out_of_stock_2').readOnly = true;
			getE('out_of_stock_3').readOnly = true;
			getE('label_out_of_stock_2').setAttribute('for', '');
			getE('label_out_of_stock_3').setAttribute('for', '');
		}
		else
		{
			$('#virtual_good').hide('slow');
			getE('out_of_stock_2').readOnly = false;
			getE('out_of_stock_3').readOnly = false;
			getE('label_out_of_stock_2').setAttribute('for', 'out_of_stock_2');
			getE('label_out_of_stock_3').setAttribute('for', 'out_of_stock_3');
		}
	}
	function uploadFile()
	{
		$.ajaxFileUpload (
			{
				url:'./uploadProductFile.php',
				secureuri:false,
				fileElementId:'virtual_product_file',
				dataType: 'xml',

				success: function (data, status)
				{
					data = data.getElementsByTagName('return')[0];
					var result = data.getAttribute("result");
					var msg = data.getAttribute("msg");
					var fileName = data.getAttribute("filename");

					if(result == "error")
					{
						$("#upload-confirmation").html('<p>error: ' + msg + '</p>');
					}
					else
					{
						$('#virtual_product_file').remove();
						$('#virtual_product_file_label').hide();
						$('#virtual_product_name').attr('value', fileName);
						$('#upload-confirmation').html(
							'<a class="link" href="get-file-admin.php?file=' + msg + '"><?php echo $this->l('The file') ?>&nbsp;"' + fileName + '"&nbsp;<?php echo $this->l('has successfully been uploaded') ?></a>' +
							'<input type="hidden" id="virtual_product_filename" name="virtual_product_filename" value="' + msg + '" />');
					}
				}
			}
		);
	}

	-->
	</script>
	<?php
		echo '
		<script type="text/javascript">
			var newLabel = \''.$this->l('New label').'\';
			var choose_language = \''.$this->l('Choose language:').'\';
			var required = \''.$this->l('required').'\';
			var customizationUploadableFileNumber = '.intval($this->getFieldValue($obj, 'uploadable_files')).';
			var customizationTextFieldNumber = '.intval($this->getFieldValue($obj, 'text_fields')).';
			var uploadableFileLabel = 0;
			var textFieldLabel = 0;
			var defaultLanguage = '.intval($defaultLanguage).';
			var languages = new Array();';
		$i = 0;
		foreach ($languages AS $language)
			echo 'languages['.$i++.'] = new Array('.intval($language['id_lang']).', \''.$language['iso_code'].'\', \''.htmlentities($language['name'], ENT_COMPAT, 'UTF-8').'\');'."\n";
		echo '
		</script>';
	?>
	<tr>
		<td colspan="2">
			<input type="checkbox" id="is_virtual_good" name="is_virtual_good" value="true" onchange="toggleVirtualProduct(this)" onclick="toggleVirtualProduct(this);" <?php if(($productDownload->id OR Tools::getValue('is_virtual_good')=='true') AND $productDownload->active) echo 'checked="checked"' ?> />
			<label for="is_virtual_good" class="t bold"><?php echo $this->l('Is this a downloadable product?') ?></label>
			<div id="virtual_good" <?php if(!$productDownload->id OR !$productDownload->active) echo 'style="display:none;"' ?> >
	<?php if(!ProductDownload::checkWritableDir()): ?>
		<p class="alert">
			<?php echo $this->l('Your download repository is not writable.'); ?><br/>
			<?php echo realpath(_PS_DOWNLOAD_DIR_); ?>
		</p>
	<?php else: ?>
			<?php if($productDownload->id) echo '<input type="hidden" id="virtual_product_id" name="virtual_product_id" value="'.$productDownload->id.'" />' ?>
				<p class="block">
	<?php if(!$productDownload->checkFile()): ?>
		<?php if($productDownload->id): ?>
					<p class="alert">
						<?php echo $this->l('This product is missing') ?>:<br/>
						<?php echo realpath(_PS_DOWNLOAD_DIR_) .'/'. $productDownload->physically_filename ?>
					</p>
		<?php endif; ?>
					<p><?php echo $this->l('Your server\'s maximum upload file size is') . ':&nbsp;' . ini_get('upload_max_filesize') ?></p>
					<?php if (!strval(Tools::getValue('virtual_product_filename'))): ?>
					<label id="virtual_product_file_label" for="virtual_product_file" class="t"><?php echo $this->l('Upload a file') ?></label>
					<input type="file" id="virtual_product_file" name="virtual_product_file" value="" class="" onchange="uploadFile()" maxlength="<?php echo $this->maxFileSize ?>" />
					<?php endif; ?>
					<div id="upload-confirmation">
					<?php if ($up_filename = strval(Tools::getValue('virtual_product_filename'))): ?>
						<input type="hidden" id="virtual_product_filename" name="virtual_product_filename" value="<?php echo $up_filename ?>" />
					<?php endif; ?>
					</div>
	<?php else: ?>
					<input type="hidden" id="virtual_product_filename" name="virtual_product_filename" value="<?php echo $productDownload->physically_filename ?>" />
					<?php echo $this->l('This is the link').':&nbsp;'.$productDownload->getHtmlLink(false, true) ?>
					<a href="confirm.php?height=200&amp;width=300&amp;modal=true&amp;referer=<?php echo rawurlencode($_SERVER['REQUEST_URI'].'&deleteVirtualProduct=true') ?>" class="thickbox red" title="<?php echo $this->l('Delete this file') ?>"><?php echo $this->l('Delete this file') ?></a>
	<?php endif; // check if file exists ?>
				</p>
				<p class="block">
					<label for="virtual_product_name" class="t"><?php echo $this->l('Filename') ?></label>
					<input type="text" id="virtual_product_name" name="virtual_product_name" class="" value="<?php echo $productDownload->id > 0 ? $productDownload->display_filename : htmlentities(Tools::getValue('virtual_product_name'), ENT_COMPAT, 'UTF-8') ?>" />
					<span class="hint" name="help_box" style="display:none;"><?php echo $this->l('The complete filename with its extension (e.g., Our best song.mp3)') ?></span>
				</p>
				<p class="block">
					<label for="virtual_product_nb_downloable" class="t"><?php echo $this->l('Number of downloads') ?></label>
					<input type="text" id="virtual_product_nb_downloable" name="virtual_product_nb_downloable" value="<?php echo $productDownload->id > 0 ? $productDownload->nb_downloadable : htmlentities(Tools::getValue('virtual_product_nb_downloable'), ENT_COMPAT, 'UTF-8') ?>" class="" size="6" />
					<span class="hint" name="help_box" style="display:none"><?php echo $this->l('Number of authorized downloads per customer') ?></span>
				</p>
				<p class="block">
					<label for="virtual_product_expiration_date" class="t"><?php echo $this->l('Expiration date') ?></label>
					<input type="text" id="virtual_product_expiration_date" name="virtual_product_expiration_date" value="<?php echo ($productDownload->id > 0) ? ((!empty($productDownload->date_expiration) AND $productDownload->date_expiration != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($productDownload->date_expiration))
: '' ) : htmlentities(Tools::getValue('virtual_product_expiration_date'), ENT_COMPAT, 'UTF-8') ?>" size="11" maxlength="10" autocomplete="off" /> <?php echo $this->l('Format: YYYY-MM-DD'); ?>
					<span class="hint" name="help_box" style="display:none"><?php echo $this->l('No expiration date if you leave this blank'); ?></span>
				</p>
				<p class="block">
					<label for="virtual_product_nb_days" class="t"><?php echo $this->l('Number of days') ?></label>
					<input type="text" id="virtual_product_nb_days" name="virtual_product_nb_days" value="<?php echo $productDownload->id > 0 ? $productDownload->nb_days_accessible : htmlentities(Tools::getValue('virtual_product_nb_days'), ENT_COMPAT, 'UTF-8') ?>" class="" size="4" /><sup> *</sup>
					<span class="hint" name="help_box" style="display:none"><?php echo $this->l('How many days this file can be accessed by customers') ?></span>
				</p>
	<?php endif; // check if download directory is writable ?>
			</div>
		</td>
	</tr>
	<tr><td colspan="2" style="padding-bottom:5px;"><hr style="width:730px;"></td></tr>
	<script type="text/javascript">
		if ($('#is_virtual_good').attr('checked'))
			$('#virtual_good').show('slow');
	</script>

<?php
					echo '
					<tr>
						<td class="col-left">'.$this->l('Pre-tax wholesale price:').'</td>
						<td style="padding-bottom:5px;">
							'.($currency->format == 1 ? $currency->sign.' ' : '').'<input size="11" maxlength="14" name="wholesale_price" type="text" value="'.htmlentities($this->getFieldValue($obj, 'wholesale_price'), ENT_COMPAT, 'UTF-8').'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" />'.($currency->format == 2 ? ' '.$currency->sign : '').'
							<span style="margin-left:10px">'.$this->l('The wholesale price at which you bought this product').'</span>
						</td>
					</tr>';
					echo '
					<tr>
						<td class="col-left">'.$this->l('Pre-tax retail price:').'</td>
						<td style="padding-bottom:5px;">
							'.($currency->format == 1 ? $currency->sign.' ' : '').'<input size="11" maxlength="14" id="priceTE" name="price" type="text" value="'.$this->getFieldValue($obj, 'price').'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\'); calcPriceTI();" />'.($currency->format == 2 ? ' '.$currency->sign : '').'<sup> *</sup>
							<span style="margin-left:2px">'.$this->l('The pre-tax retail price to sell this product').'</span>
						</td>
					</tr>';
					$taxes = Tax::getTaxes(intval($cookie->id_lang));
					echo '<script type="text/javascript">';
					echo 'noTax = '.(Tax::excludeTaxeOption() ? 'true' : 'false'), ";\n";
					echo 'taxesArray = new Array ();'."\n";
					echo 'taxesArray[0] = 0', ";\n";
					foreach ($taxes AS $k => $tax)
						echo 'taxesArray['.$tax['id_tax'].']='.$tax['rate']."\n";
					echo '
					</script>';
					echo '
					<tr>
						<td class="col-left">'.$this->l('Tax:').'</td>
						<td style="padding-bottom:5px;">
							<select onChange="javascript:calcPriceTI();" name="id_tax" id="id_tax" '.(Tax::excludeTaxeOption() ? 'disabled="disabled"' : '' ).'>
								<option value="0"'.(($this->getFieldValue($obj, 'id_tax') == 0) ? ' selected="selected"' : '').'>'.$this->l('No tax').'</option>';
							foreach ($taxes AS $k => $tax)
								echo '
								<option value="'.$tax['id_tax'].'"'.(($this->getFieldValue($obj, 'id_tax') == $tax['id_tax']) ? ' selected="selected"' : '').'>'.stripslashes($tax['name']).' ('.$tax['rate'].'%)</option>';
							echo '
							</select>';
							if (Tax::excludeTaxeOption())
							{
								echo '<span style="margin-left:10px; color:red;">'.$this->l('Taxes are currently disabled').'</span> (<b><a href="index.php?tab=AdminTaxes&token='.Tools::getAdminToken('AdminTaxes'.intval(Tab::getIdFromClassName('AdminTaxes')).intval($cookie->id_employee)).'">'.$this->l('Tax options').'</a></b>)';
								echo '<input type="hidden" value="'.intval($this->getFieldValue($obj, 'id_tax')).'" name="id_tax" />';
							}
				echo '</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Retail price with tax:').'</td>
						<td style="padding-bottom:5px;">
							'.($currency->format == 1 ? ' '.$currency->sign : '').' <input size="11" maxlength="14" id="priceTI" type="text" value="" onKeyUp="noComma(\'priceTI\'); calcPriceTE();" />'.($currency->format == 2 ? ' '.$currency->sign : '').'
							<span style="margin-left:10px">
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Eco-tax:').'</td>
						<td style="padding-bottom:5px;">
							'.($currency->format == 1 ? $currency->sign.' ' : '').'<input size="11" maxlength="14" id="ecotax" name="ecotax" type="text" value="'.$this->getFieldValue($obj, 'ecotax').'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\'); if (parseInt(this.value) > getE(\'priceTE\').value) this.value = getE(\'priceTE\').value; if (isNaN(this.value)) this.value = 0;" />'.($currency->format == 2 ? ' '.$currency->sign : '').'
							<span style="margin-left:10px">('.$this->l('already included in price').')</span>
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Reduction amount:').'</td>
						<td style="padding-bottom:5px;">
							'.($currency->format == 1 ? ' '.$currency->sign.' ' : '').'<input size="11" maxlength="14" type="text" name="reduction_price" id="reduction_price" value="'.$this->getFieldValue($obj, 'reduction_price').'" onkeyup="javascript:this.value = this.value.replace(/,/g, \'.\'); var key = window.event ? window.event.keyCode : event.which; if (key != 9) reductionPrice();" /> '.($currency->format == 2 ? ' '.$currency->sign : '').'
							<span style="padding-right: 15px; padding-left: 15px; font-weight: bold">'.$this->l('OR').'</span>
							<input size="10" maxlength="14" type="text" name="reduction_percent" id="reduction_percent" value="'.$this->getFieldValue($obj, 'reduction_percent').'" onkeyup="javascript:this.value = this.value.replace(/,/g, \'.\'); var key = window.event ? window.event.keyCode : event.which; if (key != 9) reductionPercent();" /> %
						</td>
					</tr>
					<tr>
						<td class="col-left">&nbsp;</td>
						<td>'.$this->l('available from').' <input type="text" id="reduction_from" name="reduction_from" value="'.(($from = $this->getFieldValue($obj, 'reduction_from') AND $from != '0000-00-00' AND $from != '1942-01-01') ? $from : date('Y-m-d')).'" />
							'.$this->l('to').' <input type="text" id="reduction_to" name="reduction_to" value="'.(($to = $this->getFieldValue($obj, 'reduction_to') AND $to != '0000-00-00' AND $to != '1942-01-01') ? $to : date('Y-m-d')).'" />
							<p>'.$this->l('Leave same dates for undefined duration').'</p>
						</td>
					</tr>
					<tr>
						<td class="col-left">&nbsp;</td>
						<td style="padding-bottom:5px;">
							<input type="checkbox" name="on_sale" id="on_sale" style="padding-top: 5px;" '.($this->getFieldValue($obj, 'on_sale') ? 'checked="checked"' : '').'value="1" />&nbsp;<label for="on_sale" class="t">'.$this->l('Display "on sale" icon on product page and text on product listing').'</label>
						</td>
					</tr>
					<tr>
						<td class="col-left"><b>'.$this->l('Final retail price:').'</b></td>
						<td style="padding-bottom:5px;">
							'.($currency->format == 1 ? $currency->sign.' ' : '').'<span id="finalPrice" style="font-weight: bold;"></span>'.($currency->format == 2 ? ' '.$currency->sign : '').'
						</td>
					</tr>
					<tr><td colspan="2" style="padding-bottom:5px;"><hr style="width:730px;"></td></tr>
					<tr>
						<td class="col-left">'.$this->l('Quantity:').'</td>
						<td style="padding-bottom:5px;"><input size="3" maxlength="6" '.$qty_state.' name="quantity" type="text" value="'.$qty.'" '.
						((isset($_POST['attQty']) AND $_POST['attQty']) ? 'onclick="alert(\''.$this->l('Quantity is already defined by Attributes').'.<br />'.$this->l('Delete attributes first').'.\');" readonly="readonly" ' : '').'/><sup> *</sup>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Displayed text when in-stock:').'</td>
						<td style="padding-bottom:5px;">';
		foreach ($languages as $language)
			echo '
							<div id="cavailable_now_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
								<input size="30" type="text" id="available_now_'.$language['id_lang'].'" name="available_now_'.$language['id_lang'].'"
								value="'.stripslashes(htmlentities($this->getFieldValue($obj, 'available_now', $language['id_lang']), ENT_COMPAT, 'UTF-8')).'" />
								<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cavailable_now');
		echo '			</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Displayed text when allowed to be back-ordered:').'</td>
						<td style="padding-bottom:5px;">';
		foreach ($languages as $language)
			echo '
							<div id="cavailable_later_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
								<input size="30" type="text" id="available_later_'.$language['id_lang'].'" name="available_later_'.$language['id_lang'].'"
								value="'.stripslashes(htmlentities($this->getFieldValue($obj, 'available_later', $language['id_lang']), ENT_COMPAT, 'UTF-8')).'" />
								<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cavailable_later');
		echo '			</td>
					</tr>

					<script type="text/javascript" src="../js/price.js"></script>
					<script type="text/javascript">
						calcPriceTI();
					</script>

					<tr>
						<td class="col-left">'.$this->l('When out of stock:').'</td>
						<td style="padding-bottom:5px;">
							<input type="radio" name="out_of_stock" id="out_of_stock_1" value="0" '.(intval($this->getFieldValue($obj, 'out_of_stock')) == 0 ? 'checked="checked"' : '').'/> <label for="out_of_stock_1" class="t" id="label_out_of_stock_1">'.$this->l('Deny orders').'</label>
							<br /><input type="radio" name="out_of_stock" id="out_of_stock_2" value="1" '.($this->getFieldValue($obj, 'out_of_stock') == 1 ? 'checked="checked"' : '').'/> <label for="out_of_stock_2" class="t" id="label_out_of_stock_2">'.$this->l('Allow orders').'</label>
							<br /><input type="radio" name="out_of_stock" id="out_of_stock_3" value="2" '.($this->getFieldValue($obj, 'out_of_stock') == 2 ? 'checked="checked"' : '').'/> <label for="out_of_stock_3" class="t" id="label_out_of_stock_3">'.$this->l('Default:').' <i>'.$this->l((intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')) ? 'Allow orders' : 'Deny orders')).'</i> ('.$this->l('as set in').' <a href="index.php?tab=AdminPPreferences&token='.Tools::getAdminToken('AdminPPreferences'.intval(Tab::getIdFromClassName('AdminPPreferences')).intval($cookie->id_employee)).'"  onclick="return confirm(\''.$this->l('Are you sure you want to delete entered product information?', __CLASS__, true, false).'\');">'.$this->l('Preferences').'</a>)</label>
						</td>
					</tr>

					<tr><td colspan="2" style="padding-bottom:5px;"><hr style="width:730px;"></td></tr>
					<tr>
						<td class="col-left"><label for="id_category_default" class="t">'.$this->l('Default category:').'</label></td>
						<td>
							<select id="id_category_default" name="id_category_default" onchange="checkDefaultCategory(this.value);">';
		$categories = Category::getCategories(intval($cookie->id_lang), false);
		Category::recurseCategory($categories, $categories[0][1], 1, ($this->getFieldValue($obj, 'id_category_default') ? $this->getFieldValue($obj, 'id_category_default') : Tools::getValue('id_category', 1)));
		echo '
							</select>
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Catalog:').'</td>
						<td>
							<div style="overflow: auto; min-height: 300px; padding-top: 0.6em;" id="categoryList">
							<table cellspacing="0" cellpadding="0" class="table">
									<tr>
										<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categoryBox[]\', this.checked)" /></th>
										<th>'.$this->l('ID').'</th>
										<th style="width: 400px">'.$this->l('Name').'</th>
									</tr>';
		$done = array();
		$index = array();
		$indexedCategories =  isset($_POST['categoryBox']) ? $_POST['categoryBox'] : ($obj->id ? Product::getIndexedCategories($obj->id) : array());
		foreach ($indexedCategories AS $k => $row)
			$index[] = $row['id_category'];
		$this->recurseCategoryForInclude($index, $categories, $categories[0][1], 1, $obj->id_category_default);
		echo '
							</table>
							<p style="padding:0px; margin:0px 0px 10px 0px;">'.$this->l('Mark all checkbox(es) of categories in which product is to appear').'<sup> *</sup></p>
							</div>
						</td>
					</tr>
					<tr><td colspan="2" style="padding-bottom:5px;"><hr style="width:730px;"></td></tr>
					<tr><td colspan="2">
						<span onclick="javascript:openCloseLayer(\'seo\');" style="cursor: pointer"><img src="../img/admin/arrow.gif" alt="'.$this->l('SEO').'" title="'.$this->l('SEO').'" style="float:left; margin-right:5px;"/>'.$this->l('Click here to improve product\'s rank in search engines (SEO)').'</span><br />
						<div id="seo" style="display: none; padding-top: 15px;">
							<table>
								<tr>
									<td class="col-left">'.$this->l('Meta title:').'</td>
									<td>';
		foreach ($languages as $language)
			echo '
										<div id="cmeta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
											<input size="55" type="text" id="meta_title_'.$language['id_lang'].'" name="meta_title_'.$language['id_lang'].'"
											value="'.htmlentities($this->getFieldValue($obj, 'meta_title', $language['id_lang']), ENT_COMPAT, 'UTF-8').'" />
											<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
										</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cmeta_title');
		echo '
										<p style="clear: both">'.$this->l('Product page title; leave blank to use product name').'</p>
									</td>
								</tr>
								<tr>
									<td class="col-left">'.$this->l('Meta description:').'</td>
									<td>';
		foreach ($languages as $language)
			echo '
										<div id="cmeta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
											<input size="55" type="text" id="meta_description_'.$language['id_lang'].'" name="meta_description_'.$language['id_lang'].'"
											value="'.htmlentities($this->getFieldValue($obj, 'meta_description', $language['id_lang']), ENT_COMPAT, 'UTF-8').'" />
											<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
										</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cmeta_description');
		echo '
										<p style="clear: both">'.$this->l('A single sentence for HTML header').'</p>
									</td>
								</tr>
								<tr>
									<td class="col-left">'.$this->l('Meta keywords:').'</td>
									<td>';
		foreach ($languages as $language)
			echo '
										<div id="cmeta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
											<input size="55" type="text" id="meta_keywords_'.$language['id_lang'].'" name="meta_keywords_'.$language['id_lang'].'"
											value="'.htmlentities($this->getFieldValue($obj, 'meta_keywords', $language['id_lang']), ENT_COMPAT, 'UTF-8').'" />
											<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
										</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cmeta_keywords');
		echo '
										<p style="clear: both">'.$this->l('Keywords for HTML header, separated by a comma').'</p>
									</td>
								</tr>
								<tr>
									<td class="col-left">'.$this->l('Friendly URL:').'</td>
									<td>';
		foreach ($languages as $language)
			echo '
										<div id="clink_rewrite_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
											<input size="55" type="text" id="link_rewrite_'.$language['id_lang'].'" name="link_rewrite_'.$language['id_lang'].'"
											value="'.htmlentities($this->getFieldValue($obj, 'link_rewrite', $language['id_lang']), ENT_COMPAT, 'UTF-8').'" onkeyup="this.value = str2url(this.value); updateFriendlyURL();" /><sup> *</sup>
											<span class="hint" name="help_box">'.$this->l('Only letters and the "less" character are allowed').'<span class="hint-pointer">&nbsp;</span></span>
										</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'clink_rewrite');
		global $cookie;
		$iso = Language::getIsoById(intval($cookie->id_lang));
		echo '
										<p style="clear: both; width: 360px; word-wrap: break-word; overflow: auto;">'.$this->l('Product link will look like this:').' '.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].'/id_product-<span id="friendly-url"></span>.html</p>
									</td>
								</tr>
								<script type="text/javascript">updateFriendlyURL();</script>';
		echo '</td></tr></table>
						</div>
					</td></tr>
					<tr><td colspan="2" style="padding-bottom:5px;"><hr style="width:730px;"></td></tr>
					<tr>
						<td class="col-left">'.$this->l('Short description:').'<br /><br /><i>('.$this->l('appears in search results').')</i></td>
						<td style="padding-bottom:5px;">';
		foreach ($languages as $language)
			echo '
							<div id="cdesc_short_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
								<textarea class="rte" cols="100" rows="10" id="description_short_'.$language['id_lang'].'" name="description_short_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($obj, 'description_short', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cdesc_short');
		echo '
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Description:').'</td>
						<td style="padding-bottom:5px;">';
		foreach ($languages as $language)
			echo '
							<div id="cdesc_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
								<textarea class="rte" cols="100" rows="20" id="description_'.$language['id_lang'].'" name="description_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($obj, 'description', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
							</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'cdesc');
		echo '
						</td>
					</tr>';
				echo '<tr><td class="col-left">'.$this->l('Tags:').'</td><td style="padding-bottom:5px;">';
				foreach ($languages as $language)
				{
					echo '<div id="ctags_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
							<input size="55" type="text" id="tags_'.$language['id_lang'].'" name="tags_'.$language['id_lang'].'"
							value="'.htmlentities(Tools::getValue('tags_'.$language['id_lang'], $obj->getTags($language['id_lang'], true)), ENT_COMPAT, 'UTF-8').'" />
							<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' !<>;?=+#"&deg;{}_$%<span class="hint-pointer">&nbsp;</span></span>
						  </div>';
				}
				$this->displayFlags($languages, $defaultLanguage, $divLangName, 'ctags');
				echo '<p style="clear: both">'.$this->l('Tags separated by commas (e.g., dvd, dvd player, hifi)').'</p>';
				echo '</td>
					</tr>';
				$accessories = Product::getAccessoriesLight(intval($cookie->id_lang), $obj->id);
				if ($postAccessories = Tools::getValue('inputAccessories'))
				{
					$postAccessoriesTab = explode('-', Tools::getValue('inputAccessories'));
					foreach ($postAccessoriesTab AS $accessoryId)
						if (!$this->haveThisAccessory($accessoryId, $accessories) AND $accessory = Product::getAccessoryById($accessoryId))
							$accessories[] = $accessory;
				}
					echo '
					<tr>
						<td class="col-left">'.$this->l('Accessories:').'<br /><br /><i>'.$this->l('(Do not forget to Save the product afterward)').'</i></td>
						<td style="padding-bottom:5px;">
							<div id="divAccessories">';
					foreach ($accessories as $accessory)
						echo $accessory['name'].'<span onclick="delAccessory('.$accessory['id_product'].');" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span><br />';
					echo '</div>
							<input type="hidden" name="inputAccessories" id="inputAccessories" value="';
					foreach ($accessories as $accessory)
						echo $accessory['id_product'].'-';
					echo '" />
							<input type="hidden" name="nameAccessories" id="nameAccessories" value="';
					foreach ($accessories as $accessory)
						echo $accessory['name'].'¤';

					echo '" />
							<script type="text/javascript">
								var formProduct;
								var accessories = new Array();
								
								function fillAccessories()
								{
									$.getJSON("'.dirname($currentIndex).'/ajax.php",{ajaxProductAccessories:1,id_lang:'.intval($cookie->id_lang).',id_product:'.($obj->id ? intval($obj->id) : 0).'},
										function(j)
										{
											for (var i = 0; i < j.length; i++)
												accessories[i] = new Array(j[i].value, j[i].text);
												
											formProduct = document.layers ? document.forms.product : document.product;
											formProduct.selectAccessories.length = accessories.length + 1;
											for (i = 0, j = 1; i < accessories.length; i++)
											{
												if (formProduct.filter.value)
													if (accessories[i][1].toLowerCase().indexOf(formProduct.filter.value.toLowerCase()) == -1)
														continue;
												formProduct.selectAccessories.options[j].value = accessories[i][0];
												formProduct.selectAccessories.options[j].text = accessories[i][1];
												j++;
											}
											if (j == 1)
											{
												formProduct.selectAccessories.length = 2;
												formProduct.selectAccessories.options[1].value = -1;
												formProduct.selectAccessories.options[1].text = \''.$this->l('No match found').'\';
												formProduct.selectAccessories.options.selectedIndex = 1;
											}
											else
											{
												formProduct.selectAccessories.length = j;
												formProduct.selectAccessories.options.selectedIndex = (formProduct.filter.value == \'\' ? 0 : 1);
											}
										}
									);
								}
							</script>
							<select id="selectAccessories" name="selectAccessories" style="width: 380px;">
								<option value="0" selected="selected">-- '.$this->l('Choose').' --</option>
							</select>
							<script type="text/javascript">
								fillAccessories();
							</script>
							<span onclick="addAccessory();" style="cursor: pointer;"><img src="../img/admin/add.gif" alt="'.$this->l('Add an accessory').'" title="'.$this->l('Add an accessory').'" /></span>
							<br />'.$this->l('Filter:').' <input type="text" size="25" name="filter" onkeyup="fillAccessories();" class="space" />
						</td>
					</tr>
					<tr><td colspan="2" style="padding-bottom:10px;"><hr style="width:730px;"></td></tr>
					<tr>
						<td colspan="2" style="text-align:center;">
							<input type="submit" value="'.$this->l('Save').'" name="submitAdd'.$this->table.'" class="button" />
							&nbsp;<input type="submit" value="'.$this->l('Save and stay').'" name="submitAdd'.$this->table.'AndStay" class="button" /></td>
					</tr>
				</table>
			</div>

			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
			<script type="text/javascript">
			function tinyMCEInit(element)
			{
				$().ready(function() {
					$(element).tinymce({
						// Location of TinyMCE script
						script_url : \''.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js\',
						// General options
						theme : "advanced",
						plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						// Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						width : "100",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : true,
						content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
					});
				});
			}
			tinyMCEInit(\'textarea.rte\');
			toggleVirtualProduct(getE(\'is_virtual_good\'));
			</script>
		';
	}

	function displayFormImages($obj, $languages, $defaultLanguage, $token = NULL)
	{
		global $cookie, $currentIndex, $attributeJs, $images;

		echo '
		<div class="tab-page" id="step2">
				<h4 class="tab">2. '.$this->l('Images').'</h4>
				<table cellpadding="5">
				<tr>
					<td><b>'.$this->l('Add a new image to this product').'</b></td>
				</tr>
				</table>
				<hr style="width:730px;"><br />
				<table cellpadding="5" style="width:100%">
					<tr>
						<td class="col-left">'.$this->l('File:').'</td>
						<td style="padding-bottom:5px;">
							<input type="file" id="image_product" name="image_product" />
							<p>'.$this->l('Format:').' JPG, GIF, PNG<br />'.$this->l('Filesize:').' '.($this->maxImageSize / 1000).''.$this->l('Kb max.').'</p>
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Caption:').'</td>
						<td style="padding-bottom:5px;">';
						foreach ($languages as $language)
						{
							if (!Tools::getValue('legend_'.$language['id_lang']))
								$legend = $this->getFieldValue($obj, 'name', $language['id_lang']);
							else
								$legend = Tools::getValue('legend_'.$language['id_lang']);
							echo '
								<div id="clegend_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float:left; width:370px;">
									<input size="55" type="text" id="legend_'.$language['id_lang'].'" name="legend_'.$language['id_lang'].'" value="'.stripslashes(htmlentities($legend, ENT_COMPAT, 'UTF-8')).'" maxlength="128" />
									<sup>*</sup>
									<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<br />'.$this->l('Forbidden characters will be automatically erased.').'<span class="hint-pointer">&nbsp;</span></span>
									<p style="clear: both">'.$this->l('Short description of the image').'</p>
								</div>';
						}
						$this->displayFlags($languages, $defaultLanguage, 'clegend', 'clegend');
						echo '
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Cover:').'</td>
						<td style="padding-bottom:5px;">
							<input type="checkbox" size="40" name="cover" id="cover_on" class="checkbox"'.((isset($_POST['cover']) AND intval($_POST['cover'])) ? ' checked="checked"' : '').' value="1" /><label class="t" for="cover_on"> '.$this->l('Use as product cover?').'</label>
							<p>'.$this->l('If you want to select this image as a product cover').'</p>
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$this->l('Thumbnails resize method:').'</td>
						<td style="padding-bottom:5px;">
							<select name="resizer">
								<option value="auto"'.(Tools::getValue('resizer', 'auto') == 'auto' ? ' selected="selected"' : '').'>'.$this->l('Automatic').'</option>
								<option value="man"'.(Tools::getValue('resizer', 'auto') == 'man' ? ' selected="selected"' : '').'>'.$this->l('Manual').'</option>
							</select>
							<p>'.$this->l('Method you want to use to generate resized thumbnails').'</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:center;">';
					$images = Image::getImages(intval($cookie->id_lang), $obj->id);
					$imagesTotal = Image::getImagesTotal($obj->id);
					
							if (isset($obj->id) AND sizeof($images))
							{
								echo '<input type="submit" value="'.$this->l('   Save image   ').'" name="submitAdd'.$this->table.'" class="button" />';
								echo '<input type="hidden" value="on" name="productCreated" /><br /><br />';
							}
							echo (Tools::getValue('id_image') ? '<input type="hidden" name="id_image" value="'.intval(Tools::getValue('id_image')).'" />' : '').'
						</td>
					</tr>
					<tr><td colspan="2" style="padding-bottom:10px;"><hr style="width:730px;"></td></tr>';
					if (!sizeof($images) OR !isset($obj->id))
						echo '<tr>
						<td colspan="2" style="text-align:center;">
							<input type="hidden" value="off" name="productCreated" />
							'.(Tools::isSubmit('id_category') ? '<input type="submit" value="'.$this->l('Save').'" name="submitAdd'.$this->table.'" class="button" />' : '').'
							&nbsp;<input type="submit" value="'.$this->l('Save and stay').'" name="submitAdd'.$this->table.'AndStay" class="button" /></td>
					</tr>';
					else
					{
					echo '<tr>
						<td colspan="2">
							<table cellspacing="0" cellpadding="0" class="table">
							<tr>
								<th style="width: 100px;">'.$this->l('Image').'</th>
								<th>&nbsp;</th>
								<th>'.$this->l('Position').'</th>
								<th>'.$this->l('Cover').'</th>
								<th>'.$this->l('Action').'</th>
							</tr>';

			foreach ($images AS $k => $image)
			{
				echo '
				<tr>
					<td style="padding: 4px;"><a href="../img/p/'.$obj->id.'-'.$image['id_image'].'.jpg" target="_blank">
					<img src="../img/p/'.$obj->id.'-'.$image['id_image'].'-small.jpg"
					alt="'.htmlentities(stripslashes($image['legend']), ENT_COMPAT, 'UTF-8').'" title="'.htmlentities(stripslashes($image['legend']), ENT_COMPAT, 'UTF-8').'" /></a></td>
					<td class="center">'.intval($image['position']).'</td>
					<td class="center">';

				if ($image['position'] == 1)
				{
					echo '[ <img src="../img/admin/up_d.gif" alt="" border="0"> ]';
					if ($image['position'] == $imagesTotal)
						echo '[ <img src="../img/admin/down_d.gif" alt="" border="0"> ]';
					else
						echo '[ <a href="'.$currentIndex.'&id_image='.$image['id_image'].'&imgPosition='.$image['position'].'&imgDirection=0&token='.($token ? $token : $this->token).'"><img src="../img/admin/down.gif" alt="" border="0"></a> ]';
				}
				elseif ($image['position'] == $imagesTotal)
					echo '
						[ <a href="'.$currentIndex.'&id_image='.$image['id_image'].'&imgPosition='.$image['position'].'&imgDirection=1&token='.($token ? $token : $this->token).'"><img src="../img/admin/up.gif" alt="" border="0"></a> ]
						[ <img src="../img/admin/down_d.gif" alt="" border="0"> ]';
				else
					echo '
						[ <a href="'.$currentIndex.'&id_image='.$image['id_image'].'&imgPosition='.$image['position'].'&imgDirection=1&token='.($token ? $token : $this->token).'"><img src="../img/admin/up.gif" alt="" border="0"></a> ]
						[ <a href="'.$currentIndex.'&id_image='.$image['id_image'].'&imgPosition='.$image['position'].'&imgDirection=0&token='.($token ? $token : $this->token).'"><img src="../img/admin/down.gif" alt="" border="0"></a> ]';
				echo '
					</td>
					<td class="center"><a href="'.$currentIndex.'&id_image='.$image['id_image'].'&coverImage&token='.($token ? $token : $this->token).'"><img src="../img/admin/'.($image['cover'] ? 'enabled.gif' : 'forbbiden.gif').'" alt="" /></a></td>
					<td class="center">
						<a href="'.$currentIndex.'&id_image='.$image['id_image'].'&editImage&tabs=1&token='.($token ? $token : $this->token).'"><img src="../img/admin/edit.gif" alt="'.$this->l('Modify this image').'" title="'.$this->l('Modify this image').'" /></a>
						<a href="'.$currentIndex.'&id_image='.$image['id_image'].'&deleteImage&tabs=1&token='.($token ? $token : $this->token).'" onclick="return confirm(\''.$this->l('Are you sure?', __CLASS__, true, false).'\');"><img src="../img/admin/delete.gif" alt="'.$this->l('Delete this image').'" title="'.$this->l('Delete this image').'" /></a>
					</td>
				</tr>';
			}
			}
			echo '
							</table>
						</td>
					</tr>
				</table>
			</div>';
			echo '
			<script type="text/javascript" src="../js/attributesBack.js"></script>
			<script type="text/javascript">
				var attrs = new Array();
				var modifyattributegroup = \''.addslashes(html_entity_decode($this->l('Modify this attribute combination'), ENT_COMPAT, 'UTF-8')).'\';
				attrs[0] = new Array(0, \'---\');';

			$attributes = Attribute::getAttributes(intval($cookie->id_lang), true);
			$attributeJs = array();

			foreach ($attributes AS $k => $attribute)
				$attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];

			foreach ($attributeJs AS $idgrp => $group)
			{
				echo '
				attrs['.$idgrp.'] = new Array(0, \'---\' ';
				foreach ($group AS $idattr => $attrname)
					echo ', '.$idattr.', \''.addslashes(($attrname)).'\'';
				echo ');';
			}
			echo '
			</script>';
	}

	public function initCombinationImagesJS()
	{
		global $cookie;

		$content = 'var combination_images = new Array();';
		if (!$allCombinationImages = $this->loadObject(true)->getCombinationImages(intval($cookie->id_lang)))
			return $content;
		foreach ($allCombinationImages AS $id_product_attribute => $combinationImages)
		{
			$i = 0;
			$content .= 'combination_images['.intval($id_product_attribute).'] = new Array();';
			foreach ($combinationImages AS $combinationImage)
				$content .= 'combination_images['.intval($id_product_attribute).']['.$i++.'] = '.intval($combinationImage['id_image']).';';
		}
		return $content;
	}

	function displayFormAttributes($obj, $languages, $defaultLanguage)
	{
		global $currentIndex, $cookie;
		
		$attributeJs = array();
		$attributes = Attribute::getAttributes(intval($cookie->id_lang), true);
		foreach ($attributes AS $k => $attribute)
			$attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$attributes_groups = AttributeGroup::getAttributesGroups(intval($cookie->id_lang));

		$images = Image::getImages(intval($cookie->id_lang), $obj->id);
		if ($obj->id)
			{
				echo '
			<table cellpadding="5">
				<tr>
					<td colspan="2"><b>'.$this->l('Add or modify combinations for this product').'</b> - 
					&nbsp;<a href="index.php?tab=AdminCatalog&id_product='.$obj->id.'&id_category='.intval(Tools::getValue('id_category')).'&attributegenerator&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure you want to delete entered product information?', __CLASS__, true, false).'\');"><img src="../img/admin/appearance.gif" alt="combinations_generator" class="middle" title="'.$this->l('Product combinations generator').'" />&nbsp;'.$this->l('Product combinations generator').'</a>
					</td>
				</tr>
			</table>
			<hr style="width:730px;"><br />
			<table cellpadding="5" style="width:100%">
			<tr>
			  <td style="width:150px" valign="top">'.$this->l('Group:').'</td>
			  <td style="padding-bottom:5px;"><select name="attribute_group" id="attribute_group" style="width: 200px;" onchange="populate_attrs();">';
				if (isset($attributes_groups))
					foreach ($attributes_groups AS $k => $attribute_group)
						if (isset($attributeJs[$attribute_group['id_attribute_group']]))
							echo '
							<option value="'.$attribute_group['id_attribute_group'].'">
							'.htmlentities(stripslashes($attribute_group['name']), ENT_COMPAT, 'UTF-8').'&nbsp;&nbsp;</option>';
				echo '
				</select></td>
		  </tr>
		  <tr>
			  <td style="width:150px" valign="top">'.$this->l('Attribute:').'</td>
			  <td style="padding-bottom:5px;"><select name="attribute" id="attribute" style="width: 200px;">
			  <option value="0">---</option>
			  </select>
			  <script type="text/javascript" language="javascript">populate_attrs();</script>
			  </td>
		  </tr>
		  <tr>
			  <td style="width:150px" valign="top">
			  <input style="width: 140px; margin-bottom: 10px;" type="button" value="'.$this->l('Add').'" class="button" onclick="add_attr();"/><br />
			  <input style="width: 140px;" type="button" value="'.$this->l('Delete').'" class="button" onclick="del_attr()"/></td>
			  <td align="left">
				  <select id="product_att_list" name="attribute_combinaison_list[]" multiple="multiple" size="4" style="width: 320px;"></select>
				</td>
		  </tr>
		  <tr><td colspan="2"><hr style="width:730px;"></td></tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Reference:').'</td>
			  <td style="padding-bottom:5px;">
				<input size="55" type="text" id="attribute_reference" name="attribute_reference" value="" style="width: 130px; margin-right: 44px;" />
				'.$this->l('EAN13:').'<input size="55" maxlength="13" type="text" id="attribute_ean13" name="attribute_ean13" value="" style="width: 110px; margin-left: 10px;" />
				<span class="hint" name="help_box">'.$this->l('Special characters allowed:').' .-_#<span class="hint-pointer">&nbsp;</span></span>
			  </td>
		  </tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Supplier Reference:').'</td>
			  <td style="padding-bottom:5px;">
				<input size="55" type="text" id="attribute_supplier_reference" name="attribute_supplier_reference" value="" style="width: 130px; margin-right: 44px;" />
				'.$this->l('Location:').'<input size="55" type="text" id="attribute_location" name="attribute_location" value="" style="width: 101px; margin-left: 10px;" />
				<span class="hint" name="help_box">'.$this->l('Special characters allowed:').' .-_#<span class="hint-pointer">&nbsp;</span></span>
			  </td>
		  </tr>
		  <tr><td colspan="2"><hr style="width:730px;"></td></tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Wholesale price:').'</td>
			  <td style="padding-bottom:5px;">'.($currency->format == 1 ? $currency->sign.' ' : '').'<input type="text" size="6"  name="attribute_wholesale_price" id="attribute_wholesale_price" value="0.00" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" />'.($currency->format == 2 ? ' '.$currency->sign : '').' ('.$this->l('overrides Wholesale price on Information tab').')</td>
		  </tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Impact on price:').'</td>
			  <td colspan="2" style="padding-bottom:5px;">
				<select name="attribute_price_impact" id="attribute_price_impact" style="width: 140px;" onchange="check_impact();">
				  <option value="0">'.$this->l('None').'</option>
				  <option value="1">'.$this->l('Increase').'</option>
				  <option value="-1">'.$this->l('Reduction').'</option>
				</select> <sup>*</sup>
				<span id="span_impact">&nbsp;&nbsp;'.$this->l('of').'&nbsp;&nbsp;'.($currency->format == 1 ? $currency->sign.' ' : '').'
					<input type="text" size="6" name="attribute_price" id="attribute_price" value="0.00" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');"/>'.($currency->format == 2 ? ' '.$currency->sign : '').'
				</span>
			</td>
		  </tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Impact on weight:').'</td>
			  <td colspan="2" style="padding-bottom:5px;"><select name="attribute_weight_impact" id="attribute_weight_impact" style="width: 140px;" onchange="check_weight_impact();">
			  <option value="0">'.$this->l('None').'</option>
			  <option value="1">'.$this->l('Increase').'</option>
			  <option value="-1">'.$this->l('Reduction').'</option>
			  </select>
			  <span id="span_weight_impact">&nbsp;&nbsp;'.$this->l('of').'&nbsp;&nbsp;
				<input type="text" size="6" name="attribute_weight" id="attribute_weight" value="0.00" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" /> '.Configuration::get('PS_WEIGHT_UNIT').'</span></td>
		  </tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Eco-tax:').'</td>
			  <td style="padding-bottom:5px;">'.($currency->format == 1 ? $currency->sign.' ' : '').'<input type="text" size="3" name="attribute_ecotax" id="attribute_ecotax" value="0.00" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\');" />'.($currency->format == 2 ? ' '.$currency->sign : '').' ('.$this->l('overrides Eco-tax on Information tab').')</td>
		  </tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Quantity:').'</td>
			  <td style="padding-bottom:5px;"><input type="text" size="3" name="attribute_quantity" id="attribute_quantity" value="1" /> ('.$this->l('overrides Quantity on Information tab').')</td>
		  </tr>
			<tr>
				<td colspan="2"><sup>*</sup> '.$this->l('included tax').'</td>
			</tr>
		  <tr><td colspan="2"><hr style="width:730px;"></td></tr>
		  <tr>
			  <td style="width:150px">'.$this->l('Image:').'</td>
			  <td style="padding-bottom:5px;">
				<ul id="id_image_attr">';
			$i = 0;
			$imageType = ImageType::getByNameNType('small', 'products');
			$imageWidth = (isset($imageType['width']) ? intval($imageType['width']) : 64) + 25;
			foreach ($images AS $image)
			{
				echo '<li style="float: left; width: '.$imageWidth.'px;"><input type="checkbox" name="id_image_attr[]" value="'.intval($image['id_image']).'" id="id_image_attr_'.intval($image['id_image']).'" />
				<label for="id_image_attr_'.intval($image['id_image']).'" style="float: none;"><img src="../img/p/'.$obj->id.'-'.$image['id_image'].'-small.jpg" alt="'.htmlentities(stripslashes($image['legend']), ENT_COMPAT, 'UTF-8').'" title="'.htmlentities(stripslashes($image['legend']), ENT_COMPAT, 'UTF-8').'" /></label></li>';
				++$i;
			}
			echo '</ul>
				<img id="pic" alt="" title="" style="display: none; width: 100px; height: 100px; float: left; border: 1px dashed #BBB; margin-left: 20px;" />
			  </td>
		  </tr>
			<tr>
			  <td style="width:150px">'.$this->l('Default:').'<br /><br /></td>
			  <td style="padding-bottom:5px;">
				<input type="checkbox" name="attribute_default" id="attribute_default" value="1" />&nbsp;'.$this->l('Make the default combination for this product').'<br /><br />
			  </td>
		  </tr>
		  <tr>
			  <td style="width:150px">&nbsp;</td>
			  <td style="padding-bottom:5px;">
				<span style="float: left;"><input type="submit" name="submitProductAttribute" id="submitProductAttribute" value="'.$this->l('Add this combination').'" class="button" onclick="attr_selectall();" /> </span>
				<span id="ResetSpan" style="float: left; margin-left: 8px; display: none;">
				  <input type="reset" name="ResetBtn" id="ResetBtn" onclick="if (!confirm(\''.$this->l('Are you sure you want to cancel?', __CLASS__, true, false).'\')) return;
				  init_elems(); getE(\'submitProductAttribute\').value = \''.$this->l('Add this attributes group', __CLASS__, true).'\';
				  getE(\'id_product_attribute\').value = -1; openCloseLayer(\'ResetSpan\');" class="button" value="'.$this->l('Cancel modification').'" /></span><span style="clear: both;"></span>
			  </td>
		  </tr>
		  <tr><td colspan="2"><hr style="width:730px;"></td></tr>
		  <tr>
			  <td colspan="2">
					<br />
					<table border="0" cellpadding="0" cellspacing="0" class="table" style="width: 600px;">
						<tr>
							<th>'.$this->l('Attributes').'</th>
							<th>'.$this->l('Price').'</th>
							<th>'.$this->l('Weight').'</th>
							<th>'.$this->l('Reference').'</th>
							<th>'.$this->l('EAN13').'</th>
							<th class="center">'.$this->l('Quantity').'</th>
							<th class="center">'.$this->l('Actions').'</th>
						</tr>';
			if ($obj->id)
			{
				/* Build attributes combinaisons */
				$combinaisons = $obj->getAttributeCombinaisons(intval($cookie->id_lang));
				$groups = array();
				if (is_array($combinaisons))
				{
					$combinationImages = $obj->getCombinationImages(intval($cookie->id_lang));
					foreach ($combinaisons AS $k => $combinaison)
					{
						$combArray[$combinaison['id_product_attribute']]['wholesale_price'] = $combinaison['wholesale_price'];
						$combArray[$combinaison['id_product_attribute']]['price'] = $combinaison['price'];
						$combArray[$combinaison['id_product_attribute']]['weight'] = $combinaison['weight'];
						$combArray[$combinaison['id_product_attribute']]['reference'] = $combinaison['reference'];
                        $combArray[$combinaison['id_product_attribute']]['supplier_reference'] = $combinaison['supplier_reference'];
                        $combArray[$combinaison['id_product_attribute']]['ean13'] = $combinaison['ean13'];
						$combArray[$combinaison['id_product_attribute']]['location'] = $combinaison['location'];
						$combArray[$combinaison['id_product_attribute']]['quantity'] = $combinaison['quantity'];
						$combArray[$combinaison['id_product_attribute']]['id_image'] = isset($combinationImages[$combinaison['id_product_attribute']][0]['id_image']) ? $combinationImages[$combinaison['id_product_attribute']][0]['id_image'] : 0;
						$combArray[$combinaison['id_product_attribute']]['default_on'] = $combinaison['default_on'];
						$combArray[$combinaison['id_product_attribute']]['ecotax'] = $combinaison['ecotax'];
						$combArray[$combinaison['id_product_attribute']]['attributes'][] = array($combinaison['group_name'], $combinaison['attribute_name'], $combinaison['id_attribute']);
						if ($combinaison['is_color_group'])
							$groups[$combinaison['id_attribute_group']] = $combinaison['group_name'];
					}
				}
				$irow = 0;
				if (isset($combArray))
					foreach ($combArray AS $id_product_attribute => $product_attribute)
					{
						$list = '';
						$jsList = '';
						foreach ($product_attribute['attributes'] AS $attribute)
						{
							$list .= addslashes(htmlspecialchars($attribute[0])).' - '.addslashes(htmlspecialchars($attribute[1])).', ';
							$jsList .= '\''.addslashes(htmlspecialchars($attribute[0])).' : '.addslashes(htmlspecialchars($attribute[1])).'\', \''.$attribute[2].'\', ';
						}
						$list = rtrim($list, ', ');
						$jsList = rtrim($jsList, ', ');
						$attrImage = $product_attribute['id_image'] ? new Image($product_attribute['id_image']) : false;
						echo '
						<tr'.($irow++ % 2 ? ' class="alt_row"' : '').($product_attribute['default_on'] ? ' style="background-color:#D1EAEF"' : '').'>
							<td>'.stripslashes($list).'</td>
							<td class="right">'.($currency->format == 1 ? $currency->sign.' ' : '').$product_attribute['price'].($currency->format == 2 ? ' '.$currency->sign : '').'</td>
							<td class="right">'.$product_attribute['weight'].Configuration::get('PS_WEIGHT_UNIT').'</td>
							<td class="right">'.$product_attribute['reference'].'</td>
							<td class="right">'.$product_attribute['ean13'].'</td>
							<td class="center">'.$product_attribute['quantity'].'</td>
							<td class="center">
							<a style="cursor: pointer;">
							<img src="../img/admin/edit.gif" alt="'.$this->l('Modify this combination').'"
							onclick="javascript:fillCombinaison(\''.$product_attribute['wholesale_price'].'\', \''.$product_attribute['price'].'\', \''.$product_attribute['weight'].'\', \''.$product_attribute['reference'].'\', \''.$product_attribute['supplier_reference'].'\', \''.$product_attribute['ean13'].'\',
							\''.$product_attribute['quantity'].'\', \''.($attrImage ? $attrImage->id : 0).'\', Array('.$jsList.'), \''.$id_product_attribute.'\', \''.$product_attribute['default_on'].'\', \''.$product_attribute['ecotax'].'\', \''.$product_attribute['location'].'\');" /></a>&nbsp;
							<a href="'.$currentIndex.'&deleteProductAttribute&id_product_attribute='.$id_product_attribute.'&id_product='.$obj->id.'&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure?', __CLASS__, true, false).'\');">
							<img src="../img/admin/delete.gif" alt="'.$this->l('Delete this combination').'" /></a></td>
						</tr>';
					}
						else
				echo '<tr><td colspan="7" align="center"><i>'.$this->l('No combination yet').'.</i></td></tr>';
			}
			echo '
						</table>
						<br />'.$this->l('The row in blue is the default combination.').'
						<br />
						'.$this->l('A default combination must be designated for each product.').'
						</td>
						</tr>
					</table>
					<script type="text/javascript">
						var impact = getE(\'attribute_price_impact\');
						var impact2 = getE(\'attribute_weight_impact\');

						var s_attr_group = document.getElementById(\'span_new_group\');
						var s_attr_name = document.getElementById(\'span_new_attr\');
						var s_impact = document.getElementById(\'span_impact\');
						var s_impact2 = document.getElementById(\'span_weight_impact\');

						init_elems();
					</script>
					<hr style="width:730px;">
					<table cellpadding="5">
						<tr>
							<td class="col-left"><b>'.$this->l('Color picker:').'</b></td>
							<td style="padding-bottom:5px;">
								<select name="id_color_default">
								<option value="0">'.$this->l('Do not display').'</option>';
								foreach ($attributes_groups AS $k => $attribute_group)
									if (isset($groups[$attribute_group['id_attribute_group']]))
										echo '<option value="'.intval($attribute_group['id_attribute_group']).'"
												'.(intval($attribute_group['id_attribute_group']) == intval($obj->id_color_default) ? 'selected="selected"' : '').'>'
												.htmlentities(stripslashes($attribute_group['name']), ENT_COMPAT, 'UTF-8').
											'</option>';
								echo '
								</select>
								&nbsp;&nbsp;<input type="submit" value="'.$this->l('OK').'" name="submitAdd'.$this->table.'" class="button" />
								&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?tab=AdminAttributesGroups&token='.Tools::getAdminToken('AdminAttributesGroups'.intval(Tab::getIdFromClassName('AdminAttributesGroups')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure you want to delete entered product information?', __CLASS__, true, false).'\');"><img src="../img/admin/asterisk.gif" alt="" /> '.$this->l('Color attribute management').'</a>
								<p >'.$this->l('Active the color choice by selecting a color attribute group.').'</p>
							</td>
						</tr>
					</table>';
				}
				else
					echo '<b>'.$this->l('You must save this product before adding combinations').'.</b>';
	}

	function displayFormFeatures($obj, $languages, $defaultLanguage)
	{
		global $cookie, $currentIndex;

		if ($obj->id)
		{
			$feature = Feature::getFeatures(intval($cookie->id_lang));
			$ctab = '';
			foreach ($feature AS $tab)
				$ctab .= 'ccustom_'.$tab['id_feature'].'¤';
			$ctab = rtrim($ctab, '¤');

			echo '
			<table cellpadding="5">
				<tr>
					<td colspan="2"><b>'.$this->l('Assign features to this product').'</b></td>
				</tr>
			</table>
			<hr style="width:730px;"><br />';
			// Header
			$nb_feature = Feature::nbFeatures(intval($cookie->id_lang));
			echo '
			<table border="0" cellpadding="0" cellspacing="0" class="table" style="width:600px;">
				<tr>
					<th>'.$this->l('Features').'</td>
					<th style="width:220px">'.$this->l('Value').'</td>
					<th style="width:170px">'.$this->l('Customized').'</td>
				</tr>';
			if (!$nb_feature)
					echo '<tr><td colspan="3" style="text-align:center;">'.$this->l('No features defined').'</td></tr>';
			echo '</table>';
			// Listing

			if ($nb_feature)
			{
				echo '
				<table cellpadding="5" style="width:600px; margin-top:10px">';
				foreach ($feature AS $tab_features) {
					$current_item = false;
					$custom = false;
					$product_features = $obj->getFeatures();
					foreach ($product_features as $tab_products)
						if ($tab_products['id_feature'] == $tab_features['id_feature'])
							$current_item = $tab_products['id_feature_value'];
					echo '
					<tr>
						<td>'.$tab_features['name'].'</td>
						<td style="width:220px">
							<select name="feature_'.$tab_features['id_feature'].'_value">
							<option value="0">---&nbsp;</option>';
					$feature_values = FeatureValue::getFeatureValues($tab_features['id_feature']);
					foreach ($feature_values AS $tab_values) {
						if (!$tab_values['custom']) {
							$value = FeatureValue::selectLang(FeatureValue::getFeatureValueLang($tab_values['id_feature_value']), intval($cookie->id_lang));
							echo '<option value="'.$tab_values['id_feature_value'].'"'.(($current_item == $tab_values['id_feature_value']) ? ' selected="selected"' : '').'>'.substr($value, 0, 40).(Tools::strlen($value) > 40 ? '...' : '').'&nbsp;</option>';
						} else
							$custom = true;
					}
						echo '
							</select>
						</td>
						<td style="width:170px">';
							$tab_customs = array();
							if ($custom)
								$tab_customs = FeatureValue::getFeatureValueLang($current_item);
							foreach ($languages as $language) {
								$custom_lang = FeatureValue::selectLang($tab_customs, $language['id_lang']);
								echo '
								<div id="ccustom_'.$tab_features['id_feature'].'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
									<input type="text" name="custom_'.$tab_features['id_feature'].'_'.$language['id_lang'].'" size="20" value="'.htmlentities(Tools::getValue('custom_'.$tab_features['id_feature'].'_'.$language['id_lang'], $custom_lang), ENT_COMPAT, 'UTF-8').'" />
								</div>';
							}
							$this->displayFlags($languages, $defaultLanguage, $ctab, 'ccustom_'.$tab_features['id_feature']);
							echo '
						</td>
					</tr>';
				}
				echo '
				<tr>
					<td>&nbsp;</td>
					<td style="height:50px; " valign="bottom"><input type="submit" name="submitProductFeature" id="submitProductFeature" value="'.$this->l('Update features').'" class="button" /></td>
				</tr>';
			}
			echo '</table>
			<hr style="width:730px;">
			<div style="text-align:center;">
				<a href="index.php?tab=AdminFeatures&addfeature&token='.Tools::getAdminToken('AdminFeatures'.intval(Tab::getIdFromClassName('AdminFeatures')).intval($cookie->id_employee)).'" onclick="return confirm(\''.$this->l('Are you sure you want to delete entered product information?', __CLASS__, true, false).'\');"><img src="../img/admin/add.gif" alt="new_features" title="'.$this->l('Create new features').'" />&nbsp;'.$this->l('Create new features').'</a>
			</div>';
		}
		else
			echo '<b>'.$this->l('You must save this product before adding features').'.</b>';
	}

	public function haveThisAccessory($accessoryId, $accessories)
	{
		foreach ($accessories AS $accessory)
			if (intval($accessory['id_product']) == intval($accessoryId))
				return true;
		return false;
	}

	private function displayPack(Product $obj)
	{
		global $currentIndex, $cookie;
		
		$boolPack = (($obj->id AND Pack::isPack($obj->id)) OR Tools::getValue('ppack')) ? true : false;
		$packItems = $boolPack ? Pack::getItems($obj->id, $cookie->id_lang) : array();

		echo '
		<tr>
			<td>
				<input type="checkbox" name="ppack" id="ppack" value="1"'.($boolPack ? ' checked="checked"' : '').' onchange="openCloseLayer(\'ppackdiv\');" />
				<label class="t" for="ppack">'.$this->l('Pack').'</label>
			</td>
			<td>
				<div id="ppackdiv" '.($boolPack ? '' : ' style="display: none;"').'>
					<div id="divPackItems">';
		foreach ($packItems as $packItem)
			echo $packItem->pack_quantity.' x '.$packItem->name.'<span onclick="delPackItem('.$packItem->id.');" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span><br />';
		echo '		</div>
					<input type="hidden" name="inputPackItems" id="inputPackItems" value="';
					if (Tools::getValue('inputPackItems'))
						echo Tools::getValue('inputPackItems');
					else
						foreach ($packItems as $packItem)
							echo $packItem->pack_quantity.'x'.$packItem->id.'-';
					echo '" />
					<input type="hidden" name="namePackItems" id="namePackItems" value="';
					if (Tools::getValue('namePackItems'))
						echo Tools::getValue('namePackItems');
					else
					foreach ($packItems as $packItem)
						echo $packItem->pack_quantity.'x '.$packItem->name.'¤';
					echo '" />
					<script type="text/javascript">
						var formProduct;
						var packItems = new Array();
						'.$this->fillPackItems($obj).'
						'.$this->addPackItem().'
						'.$this->delPackItem().'
						delPackItem(0);
					</script>
					<select id="selectPackItems" name="selectPackItems" style="width: 380px;" onfocus="fillPackItems();">
						<option value="0" selected="selected">-- '.$this->l('Choose').' --</option>
					</select>
					<input type="text" name="quantityPackItems" id="quantityPackItems" value="1" size="1" />
					<span onclick="addPackItem();" style="cursor: pointer;"><img src="../img/admin/add.gif" alt="'.$this->l('Add an item to the pack').'" title="'.$this->l('Add an item to the pack').'" /></span>
					<br />'.$this->l('Filter:').' <input type="text" size="25" name="filterPack" onkeyup="fillPackItems();" class="space" />
				</td>
			</div>
		</tr>';
	}
	
	private function fillPackItems($obj)
	{
		global $currentIndex, $cookie;
		return '
		function fillPackItems()
		{
			$.getJSON("'.dirname($currentIndex).'/ajax.php",{ajaxProductPackItems:1,id_lang:'.intval($cookie->id_lang).',id_product:'.($obj->id ? intval($obj->id) : 0).'},
				function(result) {
					for (var i = 0; i < result.length; i++)
						packItems[i] = new Array(result[i].value, result[i].text);
						
					formProduct = document.layers ? document.forms.product : document.product;
					formProduct.selectPackItems.length = packItems.length + 1;
					for (i = 0, j = 1; i < packItems.length; i++)
					{
						if (formProduct.filterPack.value)
							if (packItems[i][1].toLowerCase().indexOf(formProduct.filterPack.value.toLowerCase()) == -1)
								continue;
						formProduct.selectPackItems.options[j].value = packItems[i][0];
						formProduct.selectPackItems.options[j].text = packItems[i][1];
						j++;
					}
					if (j == 1)
					{
						formProduct.selectPackItems.length = 2;
						formProduct.selectPackItems.options[1].value = -1;
						formProduct.selectPackItems.options[1].text = \''.$this->l('No match found').'\';
						formProduct.selectPackItems.options.selectedIndex = 1;
					}
					else
					{
						formProduct.selectPackItems.length = j;
						formProduct.selectPackItems.options.selectedIndex = (formProduct.filterPack.value == \'\' ? 0 : 1);
					}
				}
			);
		}';
	}
	
	private function packItemJsInit()
	{
		return '
			var reg = new RegExp(\'-\', \'g\');
			var regx = new RegExp(\'x\', \'g\');
			
			var div = getE(\'divPackItems\');
			var input = getE(\'inputPackItems\');
			var name = getE(\'namePackItems\');
			var select = getE(\'selectPackItems\');
			var select_quantity = getE(\'quantityPackItems\');';
	}
	
	private function addPackItem()
	{
		return '
		function addPackItem()
		{
			'.$this->packItemJsInit().'

			if (select.value == \'0\')
				return;
			var cut = select.value.split(reg);
				
			var inputCut = input.value.split(reg);
			for (var i = 0; i < inputCut.length; ++i)
				if (inputCut[i])
				{
					var inputQty = inputCut[i].split(regx);
					if (inputQty[1] == cut[0])
						return false;
				}
			

			for (i = 0; i < select.length; ++i)
				if (select.options[i].selected == true)
					select.options[i] = null;
			select.selectedIndex = 0;

			var nameStr = \'\';
			for (i = 1; i < cut.length; ++i)
				nameStr += select_quantity.value + \' x \' + cut[i];
			input.value += select_quantity.value + \'x\' + cut[0] + \'-\';
			name.value += nameStr + \'¤\';
			div.innerHTML += nameStr + \' <span onclick="delPackItem(\' + cut[0] + \');" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span><br />\';
		}';
	}
	
	private function delPackItem()
	{
		return '
		function delPackItem(id)
		{
			'.$this->packItemJsInit().'
			
			var inputCut = input.value.split(reg);
			var nameCut = name.value.split(new RegExp(\'¤\', \'g\'));

			input.value = \'\';
			name.value = \'\';
			div.innerHTML = \'\';

			for (var i = 0; i < inputCut.length; ++i)
				if (inputCut[i])
				{
					var inputQty = inputCut[i].split(regx);
					if (inputQty[1] != id)
					{
						input.value += inputCut[i] + \'-\';
						name.value += nameCut[i] + \'¤\';
						div.innerHTML += nameCut[i] + \' <span onclick="delPackItem(\' + inputQty[1] + \');" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span><br />\';
					}
				}
		}';
	}
	
	public function updatePackItems($product)
	{
		Pack::deleteItems($product->id);
		if (Tools::getValue('ppack') AND $items = Tools::getValue('inputPackItems') AND sizeof($ids = array_unique(explode('-', $items))))
			if (!Pack::addItems($product->id, $ids))
				return false;
		return true;
	}
}



?>
