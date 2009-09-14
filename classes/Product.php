<?php

/**
  * Products class, Product.php
  * Products management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

define('_CUSTOMIZE_FILE_', 0);
define('_CUSTOMIZE_TEXTFIELD_', 1);

class		Product extends ObjectModel
{
	/** @var integer Tax id */
	public		$id_tax;

	/** @var string Tax name */
	public		$tax_name;

	/** @var string Tax rate */
	public		$tax_rate;

	/** @var integer Manufacturer id */
	public		$id_manufacturer;

	/** @var integer Supplier id */
	public		$id_supplier;

	/** @var integer default Category id */
	public 		$id_category_default;

	/** @var integer default Attribute id if color picker is enabled */
	public 		$id_color_default;

	/** @var string Manufacturer name */
	public		$manufacturer_name;

	/** @var string Supplier name */
	public		$supplier_name;

	/** @var string Name */
	public 		$name;

	/** @var string Long description */
	public 		$description;

	/** @var string Short description */
	public 		$description_short;

	/** @var integer Quantity available */
	public 		$quantity = 0;

	/** @var string available_now */
	public 		$available_now;
	
	/** @var string available_later */
	public 		$available_later;

	/** @var float Price in euros */
	public 		$price = 0;

	/** @var float Wholesale Price in euros */
	public 		$wholesale_price = 0;

	/** @var float Reduction price in euros */
	public 		$reduction_price = 0;

	/** @var float Reduction percentile */
	public 		$reduction_percent = 0;

	/** @var string Reduction beginning */
	public		$reduction_from = '1942-01-01';

	/** @var string Reduction end */
	public		$reduction_to = '1942-01-01';

	/** @var boolean on_sale */
	public 		$on_sale = false;

	/** @var float Ecotax */
	public		$ecotax = 0;

	/** @var string Reference */
	public 		$reference;
	
	/** @var string Supplier Reference */
	public 		$supplier_reference;	
	
	/** @var string Location */
	public 		$location;

	/** @var string Weight in default weight unit */
	public 		$weight = 0;

	/** @var string Ean-13 barcode */
	public 		$ean13;

	/** @var string Friendly URL */
	public 		$link_rewrite;

	/** @var string Meta tag description */
	public 		$meta_description;

	/** @var string Meta tag keywords */
	public 		$meta_keywords;

	/** @var string Meta tag title */
	public 		$meta_title;

	/** @var integer Out of stock behavior */
	public		$out_of_stock = 2;

	/** @var boolean Product statuts */
	public		$quantity_discount = 0;

	/** @var boolean Product customization */
	public		$customizable;
	
	/** @var boolean Product is new */
	public		$new = NULL;

	/** @var integer Number of uploadable files (concerning customizable products) */
	public		$uploadable_files;

	/** @var interger Number of text fields */
	public		$text_fields;

	/** @var boolean Product statuts */
	public		$active = 1;
	
	public		$indexed = 0;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	/*** @var array Tags */
	public		$tags;
	
	private static $_prices = array();

	private static $_incat = array();
	
	/** @var array tables */
	protected $tables = array ('product', 'product_lang');

	protected $fieldsRequired = array('id_tax', 'quantity', 'price');
	protected $fieldsSize = array('reference' => 32, 'supplier_reference' => 32, 'location' => 64, 'ean13' => 13);
	protected $fieldsValidate = array(
		'id_tax' => 'isUnsignedId',
		'id_manufacturer' => 'isUnsignedId',
		'id_supplier' => 'isUnsignedId',
		'id_category_default' => 'isUnsignedId',
		'id_color_default' => 'isUnsignedInt', /* unsigned integer because its value could be 0 if the feature is disabled */
		'quantity' => 'isUnsignedInt',
		'price' => 'isPrice',
		'wholesale_price' => 'isPrice',
		'reduction_price' => 'isPrice',
		'reduction_percent' => 'isFloat',
		'reduction_from' => 'isDate',
		'reduction_to' => 'isDate',
		'on_sale' => 'isBool',
		'ecotax' => 'isPrice',
		'reference' => 'isReference',
    	'supplier_reference' => 'isReference',
		'location' => 'isReference',
		'weight' => 'isFloat',
		'out_of_stock' => 'isUnsignedInt',
		'quantity_discount' => 'isBool',
		'customizable' => 'isUnsignedInt',
		'uploadable_files' => 'isUnsignedInt',
		'text_fields' => 'isUnsignedInt',
		'active' => 'isBool',
		'ean13' => 'isEan13'
	);
	protected $fieldsRequiredLang = array('link_rewrite', 'name');
	/* Description short is limited to 400 chars, but without html, so it can't be generic */
	protected $fieldsSizeLang = array('meta_description' => 255, 'meta_keywords' => 255,
		'meta_title' => 128, 'link_rewrite' => 128, 'name' => 128, 'available_now' => 255, 'available_later' => 255);
	protected $fieldsValidateLang = array(
		'meta_description' => 'isGenericName', 'meta_keywords' => 'isGenericName',
		'meta_title' => 'isGenericName', 'link_rewrite' => 'isLinkRewrite', 'name' => 'isCatalogName',
		'description' => 'isCleanHtml', 'description_short' => 'isCleanHtml', 'available_now' => 'isGenericName', 'available_later' => 'IsGenericName');

	protected 	$table = 'product';
	protected 	$identifier = 'id_product';

	public	function __construct($id_product = NULL, $full = false, $id_lang = NULL)
	{
		parent::__construct($id_product, $id_lang);
		if ($full AND $this->id)
		{
			$this->manufacturer_name = Manufacturer::getNameById(intval($this->id_manufacturer));
			$this->supplier_name = Supplier::getNameById(intval($this->id_supplier));
			$tax = new Tax(intval($this->id_tax), intval($id_lang));
			$this->tax_name = $tax->name;
			$this->tax_rate = floatval($tax->rate);
			$this->new = $this->isNew();
		}
		$this->category = Category::getLinkRewrite(intval($this->id_category_default), intval($id_lang));
		$this->tags = Tag::getProductTags($this->id);
	}

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_product'] = intval($this->id);
		$fields['id_tax'] = intval($this->id_tax);
		$fields['id_manufacturer'] = intval($this->id_manufacturer);
		$fields['id_supplier'] = intval($this->id_supplier);
		$fields['id_category_default'] = intval($this->id_category_default);
		$fields['id_color_default'] = intval($this->id_color_default);
		$fields['quantity'] = intval($this->quantity);
		$fields['price'] = floatval($this->price);
		$fields['wholesale_price'] = floatval($this->wholesale_price);
		$fields['reduction_price'] = floatval($this->reduction_price);
		$fields['reduction_percent'] = floatval($this->reduction_percent);
		$fields['reduction_from'] = pSQL($this->reduction_from);
		$fields['reduction_to'] = pSQL($this->reduction_to);
		$fields['on_sale'] = intval($this->on_sale);
		$fields['ecotax'] = floatval($this->ecotax);
		$fields['reference'] = pSQL($this->reference);
		$fields['supplier_reference'] = pSQL($this->supplier_reference);
		$fields['location'] = pSQL($this->location);
		$fields['weight'] = floatval($this->weight);
		$fields['out_of_stock'] = pSQL($this->out_of_stock);
		$fields['quantity_discount'] = intval($this->quantity_discount);
		$fields['customizable'] = intval($this->customizable);
		$fields['uploadable_files'] = intval($this->uploadable_files);
		$fields['text_fields'] = intval($this->text_fields);
		$fields['active'] = intval($this->active);
		$fields['indexed'] = 0; // Reset indexation every times
		$fields['ean13'] = pSQL($this->ean13);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);

		return $fields;
	}

	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		self::validateFieldsLang();

		$fieldsArray = array('meta_description', 'meta_keywords', 'meta_title', 'link_rewrite', 'name', 'available_now', 'available_later');
		$fields = array();
		$languages = Language::getLanguages();
		$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = intval($this->id);
			$fields[$language['id_lang']]['description'] = (isset($this->description[$language['id_lang']])) ? Tools::htmlentitiesDecodeUTF8(pSQL($this->description[$language['id_lang']], true)) : '';
			$fields[$language['id_lang']]['description_short'] = (isset($this->description_short[$language['id_lang']])) ? Tools::htmlentitiesDecodeUTF8(pSQL($this->description_short[$language['id_lang']], true)) : '';
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());

				/* Check fields validity */
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
				else
					$fields[$language['id_lang']][$field] = '';
			}
		}
		return $fields;
	}

	/**
	 * Move a product inside its category
	 * @param boolean $way Up (1)  or Down (0)
	 * * @param intger $position* 
	 * return boolean Update result
	 */
	public function updatePosition($way, $position = NULL)
	{
		if (!$res = Db::getInstance()->ExecuteS('
		SELECT cp.`id_product`, cp.`position`, cp.`id_category` 
		FROM `'._DB_PREFIX_.'category_product` cp
		WHERE cp.`id_category` = '.intval(Tools::getValue('id_category')).' 
		ORDER BY cp.`position` '.(intval($way) ? 'ASC' : 'DESC')))
			return false;
		foreach ($res AS $key => $values)
			if (intval($values[$this->identifier]) == intval($this->id))
			{
				$k = $key ;
				break ;
			}
		if (!isset($k) OR !isset($res[$k]) OR !isset($res[$k + 1]))
			return false;
		$from = $res[$k];
		$to = $res[$k + 1];

		if (isset($position))
			$to['position'] = intval($position);
					
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'category_product`
		SET `position`= position '.($way ? '-1' : '+1').'
		WHERE position between '.min(array($from['position'], $to['position'])) .'
		AND '.max(array($from['position'], $to['position'])).'
		AND `id_category`='.intval($from['id_category']))
		AND
		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'category_product`
		SET `position`='.intval($to['position']).'
		WHERE `'.pSQL($this->identifier).'` = '.intval($from[$this->identifier]).'
		AND `id_category`='.intval($to['id_category'])));
	}
	
	/*
	 * Reorder product position
	 *
	 * @param boolean $id_hook Hook ID
	 */
	static public function cleanPositions($id_category)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_product`
		FROM `'._DB_PREFIX_.'category_product`
		WHERE `id_category` = '.intval($id_category).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i)
			Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'category_product`
			SET `position` = '.($i).'
			WHERE `id_category` = '.intval($id_category).'
			AND `id_product` = '.intval($result[$i]['id_product']));
		return true;
	}

	/**
	* Get the default attribute for a product
	*
	* @return array Attributes list
	*/
	static public function getDefaultAttribute($id_product, $minimumQuantity = 0)
	{

		$sql = 'SELECT `id_product_attribute`
		FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `default_on` = 1 '.(intval($minimumQuantity) > 0 ? 'AND `quantity` >= '.intval($minimumQuantity).' ' : '').'AND `id_product` = '.intval($id_product);
		$result = Db::getInstance()->getRow($sql);
		if (!$result)
			$result = Db::getInstance()->getRow('
			SELECT `id_product_attribute`
			FROM `'._DB_PREFIX_.'product_attribute`
			WHERE '.(intval($minimumQuantity) > 0 ? '`quantity` >= '.intval($minimumQuantity).' AND ' : '').'`id_product` = '.intval($id_product));
		if (!$result)
			$result = Db::getInstance()->getRow('
			SELECT `id_product_attribute`
			FROM `'._DB_PREFIX_.'product_attribute`
			WHERE `id_product` = '.intval($id_product));			
		return $result['id_product_attribute'];
	}

	public function validateFieldsLang($die = true, $errorReturn = false)
	{
		foreach ($this->description_short as $k => $value)
			if (Tools::strlen(strip_tags($value)) > 400)
			{
				if ($die) die (Tools::displayError().' ('.get_class($this).'->description: length > 400 for language '.$k.')');
				return $errorReturn ? get_class($this).'->'.Tools::displayError('description: length > 400 for language').' '.$k : false;
			}
		return parent::validateFieldsLang($die, $errorReturn);
	}

	public function delete()
	{
		Hook::deleteProduct($this);
		if (!parent::delete() OR
			!$this->deleteCategories() OR
			!$this->deleteImages() OR
			!$this->deleteProductAttributes() OR
			!$this->deleteProductFeatures() OR
			!$this->deleteTags() OR
			!$this->deleteCartProducts() OR
        	!$this->deleteAttributesImpacts())
		return false;
		if ($id = ProductDownload::getIdFromIdProduct($this->id))
			if ($productDownload = new ProductDownload($id) AND !$productDownload->delete(true))
				return false;
		return true;
	}

	public function deleteSelection($products)
	{
		$return = true;
		foreach ($products AS $id_product)
		{
			$product = new Product(intval($id_product));
			$return &= $product->delete();
		}
		return $return;
	}


	public static function getByReference($reference)
	{
		if (!Validate::isReference($reference))
			die(Tools::displayError());

		$result = Db::getInstance()->getRow('
		SELECT `id_product`
		FROM `'._DB_PREFIX_.'product` p
		WHERE p.`reference` = \''.pSQL($reference).'\'');
		if (!isset($result['id_product']))
			return false;

		return new self($result['id_product']);
	}

	/**
	* Update categories to index product into
	*
	* @param string $productCategories Categories list to index product into
	* @return array Update/insertion result
	*/
	public function updateCategories($categories, $keepingCurrentPos = false)
	{
		$positions = array();
		$result = Db::getInstance()->ExecuteS('SELECT IFNULL(MAX(`position`), 0) + 1 AS max, `id_category`
				FROM `'._DB_PREFIX_.'category_product`
				WHERE `id_category` IN('.implode(',', array_map('intval', $categories)).')
				GROUP BY `id_category`
			');
		if (!is_array($result))
			return (false);
		foreach ($result AS $position)
			$positions[$position['id_category']] = $position;
		/* Product Update, so saving current positions */
		if ($keepingCurrentPos)
		{
			if (!is_array($oldPositions = Db::getInstance()->ExecuteS('SELECT `id_category`, `id_product`, `position` AS max FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.intval($this->id))))
				return false;
			foreach ($oldPositions AS $position)
				$positions[$position['id_category']] = $position;
		}
		$this->deleteCategories();
        $productCats = array();
		foreach ($categories AS &$categorie)
			$categorie = intval($categorie);
		foreach ($categories AS $k => $productCategory)
			$productCats[] = '('.$productCategory.','.$this->id.','.(isset($positions[$productCategory]) ? $positions[$productCategory]['max'] : 0).')';

		$result = Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'category_product` (`id_category`, `id_product`, `position`)
		VALUES '.implode(',', $productCats));

		return ($result);
	}

	/**
	* Delete categories where product is indexed
	*
	* @return array Deletion result
	*/
	public function deleteCategories()
	{
		$result = Db::getInstance()->Executes('SELECT `id_category` FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.intval($this->id));
		$return = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.intval($this->id));
		foreach($result AS $row)
			$this->cleanPositions(intval($row['id_category']));
		return $return;
	}

	/**
	* Delete products tags entries
	*
	* @return array Deletion result
	*/
	public function deleteTags()
	{
		return (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_tag` WHERE `id_product` = '.intval($this->id))
		AND Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tag` WHERE `id_tag` NOT IN (SELECT `id_tag` FROM `'._DB_PREFIX_.'product_tag`)'));
	}

	/**
	* Delete product from cart
	*
	* @return array Deletion result
	*/
	public function deleteCartProducts()
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_product` = '.intval($this->id));
	}

	/**
	* Delete product images from database
	*
	* @return array Deletion result
	*/
	public function deleteImages()
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_image`
		FROM `'._DB_PREFIX_.'image`
		WHERE `id_product` = '.intval($this->id));
		foreach($result as $row)
			if (!deleteImage(intval($this->id), $row['id_image']) OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'image_lang` WHERE `id_image` = '.intval($row['id_image'])))
				return false;
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'image` WHERE `id_product` = '.intval($this->id));
	}

	static public function getProductAttributePrice($id_product_attribute)
	{
		$rq = Db::getInstance()->getRow('
		SELECT `price`
		FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product_attribute` = '.intval($id_product_attribute));
		return $rq['price'];
	}

	/**
	* Get all available products
	*
	* @param integer $id_lang Language id
	* @param integer $start Start number
	* @param integer $limit Number of products to return
	* @param string $orderBy Field for ordering
	* @param string $orderWay Way for ordering (ASC or DESC)
	* @return array Products details
	*/
	static public function getProducts($id_lang, $start, $limit, $orderBy, $orderWay, $id_category = false, $only_active = false)
	{
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die (Tools::displayError());
		if ($orderBy == 'id_product' OR	$orderBy == 'price' OR	$orderBy == 'date_add')
			$orderByPrefix = 'p';
		elseif ($orderBy == 'name')
			$orderByPrefix = 'pl';
		elseif ($orderBy == 'position')
			$orderByPrefix = 'c';

		$rq = Db::getInstance()->ExecuteS('
		SELECT p.*, pl.* , t.`rate` AS tax_rate, m.`name` AS manufacturer_name, s.`name` AS supplier_name
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
		LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = p.`id_supplier`)'.
		($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)' : '').'
		WHERE pl.`id_lang` = '.intval($id_lang).
		($id_category ? ' AND c.`id_category` = '.intval($id_category) : '').
		($only_active ? ' AND p.`active` = 1' : '').'
		ORDER BY '.(isset($orderByPrefix) ? pSQL($orderByPrefix).'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).
		($limit > 0 ? ' LIMIT '.intval($start).','.intval($limit) : '')
		);
		if($orderBy == 'price')
			Tools::orderbyPrice($rq,$orderWay);

		return ($rq);
	}

	static public function getSimpleProducts($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT p.`id_product`, pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
		WHERE pl.`id_lang` = '.intval($id_lang).'
		ORDER BY pl.`name`');
	}

	/**
	  * Return the products in the same category than the default category of the instancied product
	  *
	  * @param integer $id_lang Language ID
	  * @return array Products
	  */
	public function getDefaultCategoryProducts($idLang = NULL, $limit = NULL)
	{
		//get idLang
		$idLang = is_null($idLang) ? _USER_ID_LANG_ : intval($idLang);

		$result = Db::getInstance()->ExecuteS('
		SELECT p.`id_product`, pl.`description_short`, pl.`link_rewrite`, pl.`name`, i.`id_image`
		FROM `'._DB_PREFIX_.'category_product` cp
		LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.id_product = cp.id_product)
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product`)
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)
		WHERE cp.id_category = ' . intval($this->id_category_default) . '
		AND id_lang = ' . intval($idLang) . '
		AND p.`active` = 1
		AND i.`cover` = 1
		'. (is_null($limit) ? '' : ' LIMIT 0 , ' . intval($limit)));
		return $result;
	}

	public function isNew()
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT id_product FROM `'._DB_PREFIX_.'product` p
			WHERE 1
			AND id_product = '.intval($this->id).'
			AND DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0
		');
		return sizeof($result) > 0;
	}


	public function productAttributeExists($attributesList, $currentProductAttribute = false)
	{
		$result = Db::getInstance()->ExecuteS('SELECT pac.`id_attribute`, pac.`id_product_attribute`
		FROM `'._DB_PREFIX_.'product_attribute` pa
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
		WHERE pa.`id_product` = '.intval($this->id));
		/* If something's wrong */
		if (!$result OR empty($result))
			return false;
		/* Product attributes simulation */
		$productAttributes = array();
		foreach ($result AS $productAttribute)
			$productAttributes[$productAttribute['id_product_attribute']][] = $productAttribute['id_attribute'];
		/* Checking product's attribute existence */
		foreach ($productAttributes AS $key => $productAttribute)
			if (sizeof($productAttribute) == sizeof($attributesList))
			{
				$diff = false;
				for ($i = 0; $diff == false AND isset($productAttribute[$i]); $i++)
					if (!in_array($productAttribute[$i], $attributesList) OR $key == $currentProductAttribute)
						$diff = true;
				if (!$diff)
					return true;
			}
		return false;
	}

	/**
	* Add a product attribute
	*
	* @param float $price Additional price
	* @param float $weight Additional weight
	* @param float $ecotax Additional ecotax
	* @param integer $quantity Quantity available
	* @param integer $id_images Image ids
	* @param string $reference Reference
	* @param string $supplier_reference Supplier Reference
	* @param string $location Location
	* @param string $ean13 Ean-13 barcode
	* @param boolean $default Is default attribute for product
	* @return mixed $id_product_attribute or false
	*/
	public function addProductAttribute($price, $weight, $ecotax, $quantity, $id_images, $reference, $supplier_reference, $ean13, $default, $location = NULL)
	{
		$price = str_replace(',', '.', $price);
		$weight = str_replace(',', '.', $weight);
		Db::getInstance()->AutoExecute(_DB_PREFIX_.'product_attribute',
		array('id_product' => intval($this->id), 'price' => floatval($price), 'ecotax' => floatval($ecotax), 'quantity' => intval($quantity),
		'weight' => ($weight ? floatval($weight) : 0), 'reference' => pSQL($reference), 'supplier_reference' => pSQL($supplier_reference), 
		'location' => pSQL($location), 'ean13' => pSQL($ean13), 'default_on' => intval($default)),
		'INSERT');
		if (!$id_product_attribute = Db::getInstance()->Insert_ID())
			return false;
		if (empty($id_images))
			return intval($id_product_attribute);
		$query = 'INSERT INTO `'._DB_PREFIX_.'product_attribute_image` (`id_product_attribute`, `id_image`) VALUES ';
		foreach ($id_images AS $id_image)
			$query .= '('.intval($id_product_attribute).', '.intval($id_image).'), ';
		$query = trim($query, ', ');
		if (!Db::getInstance()->Execute($query))
			return false;
		return intval($id_product_attribute);
	}

	public function addCombinationEntity($wholesale_price, $price, $weight, $ecotax, $quantity, $id_images, $reference, $supplier_reference, $ean13, $default, $location = NULL)
	{
		if (!$id_product_attribute = $this->addProductAttribute($price, $weight, $ecotax, $quantity, $id_images, $reference, $supplier_reference, $ean13, $default, $location = NULL) OR !Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_attribute` SET `wholesale_price` = '.floatval($wholesale_price).' WHERE `id_product_attribute` = '.intval($id_product_attribute)))
			return false;
		return intval($id_product_attribute);
	}

	public function addProductAttributeMultiple($attributes, $setDefault = true)
	{
		$values = array();
		$keys = array();
		$fields = array();
		$default_value = 1;
		foreach ($attributes AS &$attribute)
			foreach ($attribute AS $key => $value)
				if ($value != "")
					$fields[$key] = $key;

		foreach ($attributes AS &$attribute)
		{
			$k = array();
			$v = array();
			foreach ($attribute AS $key => $value)
			{
				if (in_array($key, $fields))
				{
					$k[] = '`'.$key.'`';
					$v[] = '\''.$value.'\'';
				}
			}
			if ($setDefault)
			{
				$k[] = '`default_on`';
				$v[] = '\''.$default_value.'\'';
				$default_value = 0;
			}
			$values[] = '(' . implode(', ', $v).')';
			$keys[] = implode(', ', $k);
		}
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'product_attribute` ('. $keys[0].') VALUES '.implode(', ', $values));

		return (array_map(create_function('$elem', 'return $elem[\'id_product_attribute\'];'),
			Db::getInstance()->ExecuteS('
			SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product_attribute` >= '.intval(Db::getInstance()->Insert_ID())
			)));
	}

	/**
	* Del all default attributes for product
	*/
	public function deleteDefaultAttributes()
	{
		return Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'product_attribute`
		SET `default_on` = 0
		WHERE `id_product` = '.intval($this->id));
	}

	/**
	* Update a product attribute
	*
	* @param integer $id_product_attribute Product attribute id
	* @param float $price Additional price
	* @param float $weight Additional weight
	* @param float $ecotax Additional ecotax
	* @param integer $quantity Quantity available
	* @param integer $id_image Image id
	* @param string $reference Reference
	* @param string $ean13 Ean-13 barcode
	* @return array Update result
	*/
	public function updateProductAttribute($id_product_attribute, $wholesale_price, $price, $weight, $ecotax, $quantity, $id_images, $reference, $supplier_reference, $ean13, $default, $location = NULL)
	{
		Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_attribute_combination`
		WHERE `id_product_attribute` = '.intval($id_product_attribute));

		$price = str_replace(',', '.', $price);
		$weight = str_replace(',', '.', $weight);
		$data = array(
		'wholesale_price' => floatval($wholesale_price),
		'price' => floatval($price),
		'ecotax' => floatval($ecotax),
		'quantity' => intval($quantity),
		'weight' => ($weight ? floatval($weight) : 0),
		'reference' => pSQL($reference), 
		'supplier_reference' => pSQL($supplier_reference),
		'location' => pSQL($location),
		'ean13' => pSQL($ean13),
		'default_on' => intval($default));
		if (!Db::getInstance()->AutoExecute(_DB_PREFIX_.'product_attribute', $data, 'UPDATE', '`id_product_attribute` = '.intval($id_product_attribute)) OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_attribute_image` WHERE `id_product_attribute` = '.intval($id_product_attribute)))
			return false;
		Hook::updateProductAttribute($id_product_attribute);
		if (empty($id_images))
			return true;
		$query = 'INSERT INTO `'._DB_PREFIX_.'product_attribute_image` (`id_product_attribute`, `id_image`) VALUES ';
		foreach ($id_images AS $id_image)
			$query .= '('.intval($id_product_attribute).', '.intval($id_image).'), ';
		$query = trim($query, ', ');
		return Db::getInstance()->Execute($query);
	}

	/**
	* Delete product attributes
	*
	* @return array Deletion result
	*/
	public function deleteProductAttributes()
	{
		$result = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_attribute_combination`
		WHERE `id_product_attribute` IN (SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product` = '.intval($this->id).')');

		$result2 = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product` = '.intval($this->id));

		return ($result & $result2);
	}

	/**
	* Delete product attributes impacts
	*
	* @return Deletion result
	*/
    public function deleteAttributesImpacts()
    {
        return Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'attribute_impact`
		WHERE `id_product` = '.intval($this->id));
    }

	/**
	* Delete product features
	*
	* @return array Deletion result
	*/
	public function deleteProductFeatures()
	{
		return $this->deleteFeatures();
	}

	/**
	* Add a product attributes combinaison
	*
	* @param integer $id_product_attribute Product attribute id
	* @param array $attributes Attributes to forge combinaison
	* @return array Insertion result
	*/
	public function addAttributeCombinaison($id_product_attribute, $attributes)
	{
		if (!is_array($attributes))
			die(Tools::displayError());
		if (!sizeof($attributes))
			return false;
		$attributesList = '';
		foreach($attributes AS $id_attribute)
			$attributesList .= '('.intval($id_product_attribute).','.intval($id_attribute).'),';
		$attributesList = rtrim($attributesList, ',');

		if (!Validate::isValuesList($attributesList))
			die(Tools::displayError());

		$result = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'product_attribute_combination` (`id_product_attribute`, `id_attribute`) VALUES '.$attributesList);
		return $result;
	}

	public function addAttributeCombinationMultiple($id_attributes, $combinations)
	{
		$attributesList = '';
		foreach ($id_attributes AS $nb => $id_product_attribute)
			if (isset($combinations[$nb]))
				foreach ($combinations[$nb] AS $id_attribute)
					$attributesList .= '('.intval($id_product_attribute).','.intval($id_attribute).'),';
		$attributesList = rtrim($attributesList, ',');
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'product_attribute_combination` (`id_product_attribute`, `id_attribute`) VALUES '.$attributesList);
	}

	/**
	* Delete a product attributes combinaison
	*
	* @param integer $id_product_attribute Product attribute id
	* @return array Deletion result
	*/
	public function deleteAttributeCombinaison($id_product_attribute)
	{
		if (!$id_product_attribute OR !is_numeric($id_product_attribute))
			return false;

		$result = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product_attribute` = '.intval($id_product_attribute).'
		AND `id_product` = '.intval($this->id));
		$result2 = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_attribute_combination`
		WHERE `id_product_attribute` = '.intval($id_product_attribute));
		return ($result & $result2);
	}

	/**
	* Delete features
	*
	*/
	public function deleteFeatures()
	{
		// List products features
		$result1 = Db::getInstance()->ExecuteS('
		SELECT p.*, f.*
		FROM `'._DB_PREFIX_.'feature_product` as p
		LEFT JOIN `'._DB_PREFIX_.'feature_value` as f ON (f.`id_feature_value` = p.`id_feature_value`)
		WHERE `id_product` = '.intval($this->id));
		foreach ($result1 as $tab)
			// Delete product custom features
			if ($tab['custom']) {
				$result2 = Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'feature_value`
				WHERE `id_feature_value` = '.intval($tab['id_feature_value']));
				$result3 = Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'feature_value_lang`
				WHERE `id_feature_value` = '.intval($tab['id_feature_value']));
			}
		// Delete product features
		$result4 = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'feature_product`
		WHERE `id_product` = '.intval($this->id));
		return ($result4);
	}

	/**
	* Get all available product attributes combinaisons
	*
	* @param integer $id_lang Language id
	* @return array Product attributes combinaisons
	*/
	public function getAttributeCombinaisons($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT pa.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name, a.`id_attribute`
		FROM `'._DB_PREFIX_.'product_attribute` pa
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.intval($id_lang).')
		WHERE pa.`id_product` = '.intval($this->id).'
		ORDER BY pa.`id_product_attribute`');
	}

	public function getCombinationImages($id_lang)
	{
		if (!$productAttributes = Db::getInstance()->ExecuteS('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product` = '.intval($this->id)))
			return false;
		$ids = array();
		foreach ($productAttributes AS $productAttribute)
			$ids[] = intval($productAttribute['id_product_attribute']);
		if (!$result = Db::getInstance()->ExecuteS('
			SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
			FROM `'._DB_PREFIX_.'product_attribute_image` pai
			LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (il.`id_image` = pai.`id_image`)
			WHERE pai.`id_product_attribute` IN ('.implode(', ', $ids).') AND il.`id_lang` = '.intval($id_lang)))
			return false;
		$images = array();
		foreach ($result AS $row)
			$images[$row['id_product_attribute']][] = $row;
		return $images;
	}

	/**
	* Check if product has attributes combinaisons
	*
	* @return integer Attributes combinaisons number
	*/
	public function hasAttributes()
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(`id_product_attribute`) AS nb
		FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product` = '.intval($this->id));
		return $result['nb'];
	}

	/**
	* Get new products
	*
	* @param integer $id_lang Language id
	* @param integer $pageNumber Start from (optional)
	* @param integer $nbProducts Number of products to return (optional)
	* @return array New products
	*/
	static public function getNewProducts($id_lang, $pageNumber = 0, $nbProducts = 10, $count = false, $orderBy = NULL, $orderWay = NULL)
	{
		global $link, $cookie;

		if ($pageNumber < 0) $pageNumber = 0;
		if ($nbProducts < 1) $nbProducts = 10;
		if (empty($orderBy) || $orderBy == 'position') $orderBy = 'date_add';
		if (empty($orderWay)) $orderWay = 'DESC';
		if ($orderBy == 'id_product' OR $orderBy == 'price' OR $orderBy == 'date_add')
			$orderByPrefix = 'p';
		elseif ($orderBy == 'name')
            $orderByPrefix = 'pl';
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die(Tools::displayError());

		if ($count)
		{
			$result = Db::getInstance()->getRow('
			SELECT COUNT(`id_product`) AS nb
			FROM `'._DB_PREFIX_.'product` p
			WHERE `active` = 1
			AND DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0
			');
			return intval($result['nb']);
		}

		$result = Db::getInstance()->ExecuteS('
			SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`,
				i.`id_image`, il.`legend`, t.`rate`, m.`name` AS manufacturer_name
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
			LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
			WHERE p.`active` = 1
			AND DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0
			AND p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
				WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
			)
			ORDER BY '.(isset($orderByPrefix) ? pSQL($orderByPrefix).'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
			LIMIT '.intval($pageNumber * $nbProducts).', '.intval($nbProducts));
		if ($orderBy == 'price')
			Tools::orderbyPrice($result, $orderWay);
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}

	/**
	* Get a random special
	*
	* @param integer $id_lang Language id
	* @return array Special
	*/
	static public function getRandomSpecial($id_lang, $beginning = false, $ending = false)
	{
		global	$link, $cookie;

		$currentDate = date('Y-m-d');
		$row = Db::getInstance()->getRow('
		SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`,
			i.`id_image`, il.`legend`, t.`rate`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
		WHERE (`reduction_price` > 0 OR `reduction_percent` > 0)
		'.((!$beginning AND !$ending) ?
			'AND (`reduction_from` = `reduction_to` OR (`reduction_from` <= \''.pSQL($currentDate).'\' AND `reduction_to` >= \''.pSQL($currentDate).'\'))'
		:
			($beginning ? 'AND `reduction_from` <= \''.pSQL($beginning).'\'' : '').($ending ? 'AND `reduction_to` >= \''.pSQL($ending).'\'' : '')).'
		AND p.`active` = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
		)
		ORDER BY RAND()');

		if ($row)
			return Product::getProductProperties($id_lang, $row);

		return $row;
	}

	/**
	* Get prices drop
	*
	* @param integer $id_lang Language id
	* @param integer $pageNumber Start from (optional)
	* @param integer $nbProducts Number of products to return (optional)
	* @param boolean $count Only in order to get total number (optional)
	* @return array Prices drop
	*/
	static public function getPricesDrop($id_lang, $pageNumber = 0, $nbProducts = 10, $count = false, $orderBy = NULL, $orderWay = NULL, $beginning = false, $ending = false)
	{
		global $link, $cookie;
		if (!Validate::isBool($count))
			die(Tools::displayError());

		if ($pageNumber < 0) $pageNumber = 0;
		if ($nbProducts < 1) $nbProducts = 10;
		if (empty($orderBy) || $orderBy == 'position') $orderBy = 'myprice';
		if (empty($orderWay)) $orderWay = 'DESC';
		if ($orderBy == 'id_product' OR $orderBy == 'price' OR $orderBy == 'date_add')
			$orderByPrefix = 'p';
		elseif ($orderBy == 'name')
            $orderByPrefix = 'pl';
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die (Tools::displayError());

		if ($count)
		{
			$sql = '
			SELECT COUNT(DISTINCT p.`id_product`) AS nb
			FROM `'._DB_PREFIX_.'product` p
			WHERE p.`active` = 1
			AND p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
				WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
			)';
			$result = Db::getInstance()->getRow($sql);
			return intval($result['nb']);
		}
		$currentDate = date('Y-m-d');
		$sql = '
		SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`, i.`id_image`, il.`legend`, t.`rate`, (p.`reduction_price` + (p.`reduction_percent` * p.`price`)) AS myprice, m.`name` AS manufacturer_name
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
		WHERE (`reduction_price` > 0 OR `reduction_percent` > 0)
		'.((!$beginning AND !$ending) ?
			'AND (`reduction_from` = `reduction_to` OR (`reduction_from` <= \''.pSQL($currentDate).'\' AND `reduction_to` >= \''.pSQL($currentDate).'\'))'
		:
			($beginning ? 'AND `reduction_from` <= \''.pSQL($beginning).'\'' : '').($ending ? 'AND `reduction_to` >= \''.pSQL($ending).'\'' : '')).'
		AND p.`active` = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
		)
		ORDER BY '.(isset($orderByPrefix) ? pSQL($orderByPrefix).'.' : '').'`'.pSQL($orderBy).'`'.' '.pSQL($orderWay).'
		LIMIT '.intval($pageNumber * $nbProducts).', '.intval($nbProducts);
		$result = Db::getInstance()->ExecuteS($sql);
		if($orderBy == 'price')
		{
			Tools::orderbyPrice($result,$orderWay);
		}
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}

	/**
	* Get categories where product is indexed
	*
	* @param integer $id_product Product id
	* @return array Categories where product is indexed
	*/
	static public function getIndexedCategories($id_product)
	{
		return Db::getInstance()->ExecuteS('
		SELECT `id_category`
		FROM `'._DB_PREFIX_.'category_product`
		WHERE `id_product` = '.intval($id_product));
	}

	/**
	* Get product images and legends
	*
	* @param integer $id_lang Language id for multilingual legends
	* @return array Product images and legends
	*/
	public function	getImages($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT i.`cover`, i.`id_image`, il.`legend`
		FROM `'._DB_PREFIX_.'image` i
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		WHERE i.`id_product` = '.intval($this->id).'
		ORDER BY `position`');
	}

	/**
	* Get product cover image
	*
	* @return array Product cover image
	*/
	public static function getCover($id_product)
	{
		return Db::getInstance()->getRow('
		SELECT `id_image`
		FROM `'._DB_PREFIX_.'image`
		WHERE `id_product` = '.intval($id_product).'
		AND `cover` = 1');
	}

	/**
	* Get reduction value for a given product
	*
	* @param array $result SQL result with reduction informations
	* @param boolean $wt With taxes or not (optional)
	* @return float Reduction value in euros
	*/
	public static function getReductionValue($reduction_price, $reduction_percent, $date_from, $date_to, $product_price, $usetax, $taxrate)
	{
		// Avoid an error with 1970-01-01
		if (!Validate::isDate($date_from) OR !Validate::isDate($date_to))
			return 0;
		$currentDate = date('Y-m-d');
		if ($date_from != $date_to AND ($currentDate > $date_to OR $currentDate < $date_from))
			return 0;

		// reduction values
		if (!$usetax)
			$reduction_price /= (1 + ($taxrate / 100));

		// make the reduction
		if ($reduction_price AND $reduction_price > 0)
		{
			if ($reduction_price >= $product_price)
				$ret = $product_price;
			else
				$ret = $reduction_price;
		}
		elseif ($reduction_percent AND $reduction_percent > 0)
		{
			if ($reduction_percent >= 100)
				$ret = $product_price;
			else
				$ret = $product_price * $reduction_percent / 100;
		}
		return isset($ret) ? $ret : 0;
	}

	/**
	* Get product price
	*
	* @param integer $id_product Product id
	* @param boolean $tax With taxes or not (optional)
	* @param integer $id_product_attribute Product attribute id (optional)
	* @param integer $decimals Number of decimals (optional)
	* @param integer $divisor Util when paying many time without fees (optional)
	* @return float Product price
	*/
	public static function getPriceStatic($id_product, $usetax = true, $id_product_attribute = NULL, $decimals = 6, $divisor = NULL, $only_reduc = false, $usereduc = true, $quantity = 1, $forceAssociatedTax = false)
	{
		global $cookie;

		// Get id_customer if exists
		$id_customer = ((isset($cookie) AND get_class($cookie) == 'Cookie' AND isset($cookie->id_customer) AND $cookie->id_customer)
			? intval($cookie->id_customer) : null);

		if (!Validate::isBool($usetax) OR !Validate::isUnsignedId($id_product))
			die(Tools::displayError());

		// Caching system
		$cacheId = $id_product.'-'.($usetax?'1':'0').'-'.$id_product_attribute.'-'.$decimals.'-'.$divisor.'-'.($only_reduc?'1':'0').'-'.($usereduc?'1':'0').'-'.$quantity;
		if (isset(self::$_prices[$cacheId]))
			return self::$_prices[$cacheId];

		// Getting price
		$result = Db::getInstance()->getRow('
		SELECT p.`price`, p.`reduction_price`, p.`reduction_percent`, p.`reduction_from`, p.`reduction_to`, p.`id_tax`, t.`rate`, 
		'.($id_product_attribute ? 'pa.`price`' : 'IFNULL((SELECT pa.price FROM `'._DB_PREFIX_.'product_attribute` pa WHERE id_product = '.intval($id_product).' AND default_on = 1), 0)').' AS attribute_price
		FROM `'._DB_PREFIX_.'product` p
		'.($id_product_attribute ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON pa.`id_product_attribute` = '.intval($id_product_attribute) : '').'
		LEFT JOIN `'._DB_PREFIX_.'tax` AS t ON t.`id_tax` = p.`id_tax`
		WHERE p.`id_product` = '.intval($id_product));
		$price = $result['price'];

		// Exclude tax
		$tax = floatval(Tax::getApplicableTax(intval($result['id_tax']), floatval($result['rate'])));
		if ($forceAssociatedTax)
			$tax = floatval($result['rate']);
		if (Tax::excludeTaxeOption() OR !$tax)
			$usetax = false;
		if ($usetax)
			$price *= (1 + ($tax / 100));

		// Attribute price
		$attribute_price = $usetax ? $result['attribute_price'] : ($result['attribute_price'] / (1 + (($tax ? $tax : $result['rate']) / 100)));
		if (isset($result['attribute_price']))
			$price += $attribute_price;
		$reduc = self::getReductionValue($result['reduction_price'], $result['reduction_percent'], $result['reduction_from'], $result['reduction_to'],
				$price, $usetax, floatval($result['rate']));

		// Only reduction
		if ($only_reduc)
			return $reduc;
		
		// Reduction
		if ($usereduc)
			$price -= $reduc;

		// Quantity discount
		if ($quantity > 1 AND ($qtyD = QuantityDiscount::getDiscountFromQuantity($id_product, $quantity)))
			$price -= QuantityDiscount::getValue($price, $qtyD->id_discount_type, $qtyD->value);

		// Group reduction
		if ($id_customer)
			$price *= ((100 - Group::getReduction($id_customer))/100);

		self::$_prices[$cacheId] = ($divisor AND $divisor != 'NULL') ? number_format($price/$divisor, $decimals, '.', '') : number_format($price, $decimals, '.', '');
		return self::$_prices[$cacheId];
	}

	/**
	* Get product price
	* Same as static function getPriceStatic, no need to specify product id
	*
	* @param boolean $tax With taxes or not (optional)
	* @param integer $id_product_attribute Product attribute id (optional)
	* @param integer $decimals Number of decimals (optional)
	* @param integer $divisor Util when paying many time without frais (optional)
	* @return float Product price in euros
	*/
	public function getPrice($tax = true, $id_product_attribute = NULL, $decimals = 6, $divisor = NULL, $only_reduc = false, $usereduc = true, $quantity = 1)
	{
			return self::getPriceStatic(intval($this->id), $tax, $id_product_attribute, $decimals, $divisor, $only_reduc, $usereduc, $quantity);
	}

	public function getPriceWithoutReduct($notax = false)
	{
		$res = Db::getInstance()->getRow('
			SELECT p.`price`, t.`rate`, t.`id_tax`
			FROM `'._DB_PREFIX_.$this->table.'` p
			LEFT JOIN `'._DB_PREFIX_.'tax`t ON (p.`id_tax` = t.`id_tax`)
			WHERE p.`id_product` = '.intval($this->id));
		if (!$res)
			return false;
		$tax = floatval(Tax::getApplicableTax(intval($res['id_tax']), floatval($res['rate'])));
		if (!Tax::excludeTaxeOption() || $notax)
			return ($res['price'] * (1 + $tax / 100));
		return ($res['price']);
	}

	/**
	* Get product price for display
	* Also display currency sign and reduction
	*
	* @param array $params Product price, reduction...
	* @param object $smarty Smarty object
	* @return string Product price fully formated in customer currency
	*/
	static function productPrice($params, &$smarty)
	{
		$ret = '';
		if (isset($params['p']['reduction']) AND $params['p']['reduction'])
			$ret .= '<span class="discounted">'.Tools::displayPrice($params['p']['price_without_reduction'], $smarty->ps_currency).'</span><br />';
		$ret .= Tools::displayPrice($params['p']['price'], $smarty->ps_currency);
		return $ret;
	}

	static function productPriceWithoutDisplay($params, &$smarty)
	{
		return Tools::convertPrice($params['p'], $params['c']);
	}

	/**
	* Display price with right format and currency
	*
	* @param array $params Params
	* @object $smarty Smarty object
	* @return string Price with right format and currency
	*/
	static function convertPrice($params, &$smarty)
	{
		return Tools::displayPrice($params['price'], $smarty->ps_currency);
	}

	static function convertPriceWithCurrency($params, &$smarty)
	{
		if (!isset($params['convert']))
			$params['convert'] = true;
		return Tools::displayPrice($params['price'], $params['currency'], false, $params['convert']);
	}

	static function displayWtPrice($params, &$smarty)
	{
		return Tools::displayPrice($params['p'], $smarty->ps_currency);
	}

	static function displayWtPriceWithCurrency($params, &$smarty)
	{
		return Tools::displayPrice($params['price'], $params['currency'], false, $params['convert']);
	}

	/**
	* Get available product quantities
	*
	* @param integer $id_product Product id
	* @param integer $id_product_attribute Product attribute id (optional)
	* @return integer Available quantities
	*/
	public static function getQuantity($id_product, $id_product_attribute = NULL)
	{
		$result = Db::getInstance()->GetRow('
		SELECT IF(COUNT(id_product_attribute), SUM(pa.`quantity`), p.`quantity`) as total
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` AS pa ON pa.`id_product` = p.`id_product`
		WHERE p.`id_product` = '.intval($id_product).'
		'.(isset($id_product_attribute) ? 'AND `id_product_attribute` = '.intval($id_product_attribute) : '').'
		GROUP BY p.`id_product`');
		return $result['total'];
	}

	/**
	* Update available product quantities
	*
	* @param array $product Array with ordered product (quantity, id_product_attribute if applicable)
	* @return mixed Query result
	*/
	public static function updateQuantity($product)
	{
		if (!is_array($product))
			die (Tools::displayError());

		$result = Db::getInstance()->getRow('
		SELECT `quantity`
		FROM `'._DB_PREFIX_.($product['id_product_attribute'] ? 'product_attribute' : 'product').'`
		WHERE `id_product` = '.intval($product['id_product']).($product['id_product_attribute'] ?
		' AND `id_product_attribute` = '.intval($product['id_product_attribute']) : ''));

		if (!Configuration::get('PS_STOCK_MANAGEMENT'))
			return true;
		if (self::isAvailableWhenOutOfStock($product['out_of_stock']) AND intval($result['quantity']) == 0)
			return -1;

		if ($result['quantity'] < $product['quantity'])
		{
			Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.($product['id_product_attribute'] ? 'product_attribute' : 'product').'`
			SET `quantity` = 0
			WHERE `id_product` = '.intval($product['id_product']).($product['id_product_attribute'] ?
			' AND `id_product_attribute` = '.intval($product['id_product_attribute']) : ''));
			return false;
		}

		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'product'.($product['id_product_attribute'] ? '_attribute' : '').'`
		SET `quantity` = `quantity`-'.intval($product['quantity']).'
		WHERE `id_product` = '.intval($product['id_product']).
		($product['id_product_attribute'] ? ' AND `id_product_attribute` = '.intval($product['id_product_attribute']) : ''));
		return true;
	}

	public static function reinjectQuantities(&$orderDetail, $quantity)
	{
		if (!Validate::isLoadedObject($orderDetail))
			die(Tools::displayError());
		$sql = '
		UPDATE `'._DB_PREFIX_.'product'.($orderDetail->product_attribute_id ? '_attribute' : '').'`
		SET `quantity` = `quantity`+'.intval($quantity).'
		WHERE `id_product` = '.intval($orderDetail->product_id).
		($orderDetail->product_attribute_id ? ' AND `id_product_attribute` = '.intval($orderDetail->product_attribute_id) : '');
		if (!Db::getInstance()->Execute($sql) OR !Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'order_detail`
			SET `product_quantity_reinjected` = `product_quantity_reinjected` + '.intval($quantity).'
			WHERE `id_order_detail` = '.intval($orderDetail->id)))
			return false;
		$orderDetail->product_quantity_reinjected += intval($quantity);
		return true;
	}

	public static function isAvailableWhenOutOfStock($oos)
	{
		return (intval($oos) == 2 ? intval(Configuration::get('PS_ORDER_OUT_OF_STOCK')) : intval($oos));
	}

	/**
	* Check product availability
	*
	* @param integer $qty Quantity desired
	* @return boolean True if product is available with this quantity
	*/
	public function checkQty($qty)
	{
		if ($this->isAvailableWhenOutOfStock($this->out_of_stock))
			return true;

		$result = Db::getInstance()->getRow('
		SELECT `quantity`
		FROM `'._DB_PREFIX_.'product`
		WHERE `id_product` = '.intval($this->id));

		return ($result AND $qty <= $result['quantity']);
	}

	/**
	* Check if there is not a default attribute and create it not
	*/
	public function checkDefaultAttributes()
	{
		$row = Db::getInstance()->getRow('
		SELECT id_product
		FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `default_on` = 1 AND `id_product` = '.intval($this->id));
		if ($row)
			return true;

		$mini = Db::getInstance()->getRow('
		SELECT MIN(pa.id_product_attribute) as `id_attr`
		FROM `'._DB_PREFIX_.'product_attribute` pa
		WHERE `id_product` = '.intval($this->id));
		if (!$mini)
			return false;

		if (!Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'product_attribute`
			SET `default_on` = 1
			WHERE `id_product_attribute` = '.intval($mini['id_attr'])))
			return false;
		return true;
	}

	/**
	* Get all available attribute groups
	*
	* @param integer $id_lang Language id
	* @return array Attribute groups
	*/
	public function getAttributesGroups($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT ag.`id_attribute_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name, a.`id_attribute`, al.`name` AS attribute_name,
		a.`color` AS attribute_color, pa.`id_product_attribute`, pa.`quantity`, pa.`price`, pa.`ecotax`, pa.`weight`, pa.`default_on`, pa.`reference`
		FROM `'._DB_PREFIX_.'product_attribute` pa
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
		WHERE pa.`id_product` = '.intval($this->id).'
		AND al.`id_lang` = '.intval($id_lang).'
		AND agl.`id_lang` = '.intval($id_lang).'
		ORDER BY pa.`id_product_attribute`');
	}

	/**
	* Delete product accessories
	*
	* @return mixed Deletion result
	*/
	public function deleteAccessories()
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'accessory` WHERE `id_product_1` = '.intval($this->id));
	}

	/**
	* Get product accessories (only names)
	*
	* @param integer $id_lang Language id
	* @param integer $id_product Product id
	* @return array Product accessories
	*/
	public static function getAccessoriesLight($id_lang, $id_product)
	{
		return Db::getInstance()->ExecuteS('
		SELECT p.`id_product`, pl.`name`
		FROM `'._DB_PREFIX_.'accessory`
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product`= `id_product_2`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		WHERE `id_product_1` = '.intval($id_product));
	}

	/**
	* Get product accessories
	*
	* @param integer $id_lang Language id
	* @return array Product accessories
	*/
	public function getAccessories($id_lang, $active = true)
	{
		global	$link, $cookie;

		$result = Db::getInstance()->ExecuteS('
		SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`,
		i.`id_image`, il.`legend`, t.`rate`, m.`name` as manufacturer_name, cl.`name` AS category_default, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new
		FROM `'._DB_PREFIX_.'accessory`
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = `id_product_2`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.intval($id_lang).')		
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (p.`id_manufacturer`= m.`id_manufacturer`)
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
		WHERE `id_product_1` = '.intval($this->id).'
		'.($active ? 'AND p.`active` = 1' : ''));

		if (!$result)
			return false;

		return $this->getProductsProperties($id_lang, $result);
	}

	public static function getAccessoryById($accessoryId)
	{
		return Db::getInstance()->getRow('SELECT `id_product`, `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product` = '.intval($accessoryId));
	}

	/**
	* Link accessories with product
	*
	* @param array $accessories_id Accessories ids
	*/
	public function changeAccessories($accessories_id)
	{
		foreach ($accessories_id as $id_product_2)
			Db::getInstance()->AutoExecute(_DB_PREFIX_.'accessory', array('id_product_1' => intval($this->id), 'id_product_2' => intval($id_product_2)), 'INSERT');
	}

	/**
	* Add new feature to product
	*/
	public function addFeaturesCustomToDB($id_value, $lang, $cust)
	{
		$row = array('id_feature_value' => intval($id_value), 'id_lang' => intval($lang), 'value' => pSQL($cust));
		$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'feature_value_lang', $row, 'INSERT');
	}

	public function addFeaturesToDB($id_feature, $id_value, $cust = 0)
	{
		if ($cust)
		{
			$row = array('id_feature' => intval($id_feature), 'custom' => 1);
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'feature_value', $row, 'INSERT');
			$id_value = Db::getInstance()->Insert_ID();
		}
		$row = array('id_feature' => intval($id_feature), 'id_product' => intval($this->id), 'id_feature_value' => intval($id_value));
		$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'feature_product', $row, 'INSERT');
		if ($id_value)
			return ($id_value);
	}
	
	static public function addFeatureProductImport($id_product, $id_feature, $id_feature_value)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'feature_product` (`id_feature`, `id_product`, `id_feature_value`)
			VALUES ('.intval($id_feature).', '.intval($id_product).', '.intval($id_feature_value).')
			ON DUPLICATE KEY UPDATE `id_feature_value` = '.intval($id_feature_value)
		);
	}

	/**
	* Select all features for the object
	*
	* @return array Array with feature product's data
	*/
	public function getFeatures()
	{
		return self::getFeaturesStatic(intval($this->id));
	}

	static public function getFeaturesStatic($id_product)
	{
		return Db::getInstance()->ExecuteS('
		SELECT id_feature, id_product, id_feature_value
		FROM `'._DB_PREFIX_.'feature_product`
		WHERE `id_product` = '.intval($id_product));
	}

	/**
	* Admin panel product search
	*
	* @param integer $id_lang Language id
	* @param string $query Search query
	* @return array Matching products
	*/
	static public function searchByName($id_lang, $query)
	{
		if (!Validate::isCatalogName($query))
			die(Tools::displayError());

		$result = Db::getInstance()->ExecuteS('
		SELECT p.`id_product`, pl.`name`, pl.`link_rewrite`, p.`weight`, p.`active`, p.`ecotax`, i.`id_image`, p.`reference`,
		il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name
		FROM `'._DB_PREFIX_.'category_product` cp
		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`) AND i.`cover` = 1
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		WHERE pl.`name` LIKE \'%'.pSQL($query).'%\' OR p.`reference` LIKE \'%'.pSQL($query).'%\' OR p.`supplier_reference` LIKE \'%'.pSQL($query).'%\'
		GROUP BY `id_product`
		ORDER BY pl.`name` ASC');

		if (!$result)
			return false;

		$resultsArray = array();
		foreach ($result AS $k => $row)
		{
			$row['price'] = Product::getPriceStatic($row['id_product'], true, NULL, 2);
			$row['quantity'] = Product::getQuantity($row['id_product']);
			$resultsArray[] = $row;
		}
		return $resultsArray;
	}

	/**
	* Duplicate attributes when duplicating a product
	*
	* @param integer $id_product_old Old product id
	* @param integer $id_product_new New product id
	*/
	static public function duplicateAttributes($id_product_old, $id_product_new)
	{
		$return = true;
		$combinationImages = array();

		$result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'product_attribute`
		WHERE `id_product` = '.intval($id_product_old));
		foreach ($result as $row)
		{
			$id_product_attribute_old = intval($row['id_product_attribute']);
			$result2 = Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'product_attribute_combination`
			WHERE `id_product_attribute` = '.$id_product_attribute_old);

			$row['id_product'] = $id_product_new;
			unset($row['id_product_attribute']);
			$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'product_attribute', $row, 'INSERT');

			$id_product_attribute_new = intval(Db::getInstance()->Insert_ID());
			if ($resultImages = self::_getAttributeImageAssociations($id_product_attribute_old))
			{
				$combinationImages['old'][$id_product_attribute_old] = $resultImages;
				$combinationImages['new'][$id_product_attribute_new] = $resultImages;
			}
			foreach ($result2 AS $row2)
			{
				$row2['id_product_attribute'] = $id_product_attribute_new;
				$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'product_attribute_combination', $row2, 'INSERT');
			}
		}
		return !$return ? false : $combinationImages;
	}

	/**
	* Get product attribute image associations
	* @param integer $id_product_attribute
	* @return boolean
	*/
	static public function _getAttributeImageAssociations($id_product_attribute)
	{
		$combinationImages = array();
		$data = Db::getInstance()->ExecuteS('
			SELECT `id_image`
			FROM `'._DB_PREFIX_.'product_attribute_image`
			WHERE `id_product_attribute` = '.intval($id_product_attribute));
		foreach ($data AS $row)
			$combinationImages[] = intval($row['id_image']);
		return $combinationImages;
	}

	static public function duplicateAccessories($id_product_old, $id_product_new)
	{
		$return = true;

		$result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'accessory`
		WHERE `id_product_1` = '.intval($id_product_old));
		foreach ($result as $row)
		{
			$data = array(
				'id_product_1' => intval($id_product_new),
				'id_product_2' => intval($row['id_product_2']));
			$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'accessory', $data, 'INSERT');
		}
		return $return;
	}

	static public function duplicateTags($id_product_old, $id_product_new)
	{
		$resource = Db::getInstance()->Execute('SELECT `id_tag` FROM `'._DB_PREFIX_.'product_tag` WHERE `id_product` = '.intval($id_product_old));
		if (!Db::getInstance()->NumRows())
			return true;
		$query = 'INSERT INTO `'._DB_PREFIX_.'product_tag` (`id_product`, `id_tag`) VALUES';
		while ($row = Db::getInstance()->nextRow($resource))
			$query .= ' ('.intval($id_product_new).', '.intval($row['id_tag']).'),';
		$query = rtrim($query, ',');
		return Db::getInstance()->Execute($query);
	}

	static public function duplicateQuantityDiscount($id_product_old, $id_product_new)
	{
		$return = true;

		$result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'discount_quantity`
		WHERE `id_product` = '.intval($id_product_old));
		foreach ($result as $row)
		{
			$row['id_product'] = intval($id_product_new);
			unset($row['id_discount_quantity']);
			$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'discount_quantity', $row, 'INSERT');
		}
		return $return;
	}

	/**
	* Duplicate features when duplicating a product
	*
	* @param integer $id_product_old Old product id
	* @param integer $id_product_old New product id
	*/
	static public function duplicateFeatures($id_product_old, $id_product_new)
	{
		$return = true;

		$result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'feature_product`
		WHERE `id_product` = '.intval($id_product_old));
		foreach ($result as $row)
		{
			$result2 = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'feature_value`
			WHERE `id_feature_value` = '.intval($row['id_feature_value']));
			// Custom feature value, need to duplicate it
			if ($result2['custom'])
			{
				$old_id_feature_value = $result2['id_feature_value'];
				unset($result2['id_feature_value']);
				$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'feature_value', $result2, 'INSERT');
				$max_fv = Db::getInstance()->getRow('
					SELECT MAX(`id_feature_value`) AS nb
					FROM `'._DB_PREFIX_.'feature_value`');
				$new_id_feature_value = $max_fv['nb'];
				$languages = Language::getLanguages();
				foreach ($languages as $language)
				{
					$result3 = Db::getInstance()->getRow('
					SELECT *
					FROM `'._DB_PREFIX_.'feature_value_lang`
					WHERE `id_feature_value` = '.intval($old_id_feature_value).'
					AND `id_lang` = '.intval($language['id_lang']));
					$result3['id_feature_value'] = $new_id_feature_value;
					$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'feature_value_lang', $result3, 'INSERT');
				}
				$row['id_feature_value'] = $new_id_feature_value;
			}
			$row['id_product'] = $id_product_new;
			$return &= Db::getInstance()->AutoExecute(_DB_PREFIX_.'feature_product', $row, 'INSERT');
		}
		return $return;
	}

	static private function _getCustomizationFieldsNLabels($productId)
	{
		$customizations = array();
		if (($customizations['fields'] = Db::getInstance()->ExecuteS('
			SELECT `id_customization_field`, `type`, `required`
			FROM `'._DB_PREFIX_.'customization_field`
			WHERE `id_product` = '.intval($productId))) === false)
			return false;
		if (empty($customizations['fields']))
			return array();
		$customizationFieldIds = array();
		foreach ($customizations['fields'] AS $customizationField)
			$customizationFieldIds[] = intval($customizationField['id_customization_field']);
		if (($customizationLabels = Db::getInstance()->ExecuteS('
			SELECT `id_customization_field`, `id_lang`, `name`
			FROM `'._DB_PREFIX_.'customization_field_lang`
			WHERE `id_customization_field` IN ('.implode(', ', $customizationFieldIds).')')) === false)
			return false;
		foreach ($customizationLabels AS $customizationLabel)
			$customizations['labels'][$customizationLabel['id_customization_field']][] = $customizationLabel;
		return $customizations;
	}

	static public function duplicateCustomizationFields($oldProductId, $productId)
	{
		if (($customizations = self::_getCustomizationFieldsNLabels($oldProductId)) === false)
			return false;
		if (empty($customizations))
			return true;
		foreach ($customizations['fields'] AS $customizationField)
		{
			/* The new datas concern the new product */
			$customizationField['id_product'] = intval($productId);
			$oldCustomizationFieldId = intval($customizationField['id_customization_field']);
			unset($customizationField['id_customization_field']);
			if (!Db::getInstance()->AutoExecute(_DB_PREFIX_.'customization_field', $customizationField, 'INSERT'))
				return false;
			if (!$customizationFieldId = Db::getInstance()->Insert_ID())
				return false;
			if (isset($customizations['labels']))
			{
				$query = 'INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `name`) VALUES ';
				foreach ($customizations['labels'][$oldCustomizationFieldId] AS $customizationLabel)
					$query .= '('.intval($customizationFieldId).', '.$customizationLabel['id_lang'].', \''.$customizationLabel['name'].'\'), ';
				$query = rtrim($query, ', ');			
				if (!Db::getInstance()->Execute($query))
					return false;
			}
		}
		return true;
	}

	/**
	* Get the link of the product page of this product
	*/
	public function getLink()
	{
		global $link;
		return $link->getProductLink($this);
	}

	public function getTags($id_lang)
	{
		if (!($this->tags AND key_exists($id_lang, $this->tags)))
			return '';
		$result = '';
		foreach ($this->tags[$id_lang] AS $tagName)
			$result .= $tagName.', ';
		return rtrim($result, ', ');
	}

	static public function defineProductImage($row)
	{
		global $cookie;
		if (!$row['id_image'])
		{
			$row['id_image'] = Language::getIsoById($cookie->id_lang).'-default';
			$row['legend'] = 'no picture';
		}
		else
			$row['id_image'] = $row['id_product'].'-'.$row['id_image'];
		return $row['id_image'];
	}

	private static $producPropertiesCache = array();
	
	static public function getProductProperties($id_lang, $row)
	{
		if (!$row['id_product'])
			return false;
		
		$row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
		if ((!isset($row['id_product_attribute']) OR !$row['id_product_attribute']) AND $ipa_default = Product::getDefaultAttribute($row['id_product'], !$row['allow_oosp']))
			$row['id_product_attribute'] = $ipa_default;
		if (!isset($row['id_product_attribute']))
			$row['id_product_attribute'] = 0;
		
		// Tax
		$usetax = true;
		$tax = floatval(Tax::getApplicableTax(intval($row['id_tax']), floatval($row['rate'])));
		if (Tax::excludeTaxeOption() OR !$tax)
			$usetax = false;
		
		$cacheKey = $row['id_product'].'-'.$row['id_product_attribute'].'-'.$id_lang.'-'.intval($usetax);
		if (array_key_exists($cacheKey, self::$producPropertiesCache))
			return self::$producPropertiesCache[$cacheKey];

		// Datas
		$link = new Link();
		$row['category'] = Category::getLinkRewrite($row['id_category_default'], intval($id_lang));
		$row['link'] = $link->getProductLink($row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);
		$row['attribute_price'] = isset($row['id_product_attribute']) AND $row['id_product_attribute'] ? floatval(Product::getProductAttributePrice($row['id_product_attribute'])) : 0;
		$row['price_tax_exc'] = Product::getPriceStatic($row['id_product'], false, ((isset($row['id_product_attribute']) AND !empty($row['id_product_attribute'])) ? intval($row['id_product_attribute']) : NULL), 2);
		$row['price'] = Product::getPriceStatic($row['id_product'], true, ((isset($row['id_product_attribute']) AND !empty($row['id_product_attribute'])) ? intval($row['id_product_attribute']) : NULL), 2);
		$row['reduction'] = self::getReductionValue($row['reduction_price'], $row['reduction_percent'], $row['reduction_from'], $row['reduction_to'], $row['price'], $usetax, floatval($row['rate']));
		$row['price_without_reduction'] = Product::getPriceStatic($row['id_product'], true, ((isset($row['id_product_attribute']) AND !empty($row['id_product_attribute'])) ? intval($row['id_product_attribute']) : NULL), 2, NULL, false, false);
		$row['quantity'] = Product::getQuantity($row['id_product']);
		$row['id_image'] = Product::defineProductImage($row);
		$row['features'] = Product::getFrontFeaturesStatic(intval($id_lang), $row['id_product']);
		$row['attachments'] = Product::getAttachmentsStatic(intval($id_lang), $row['id_product']);
		$row['pack'] = Pack::isPack($row['id_product']);
		$row['packItems'] = $row['pack'] ? Pack::getItemTable($row['id_product'], $id_lang) : array();
		$row['nopackprice'] = $row['pack'] ? Pack::noPackPrice($row['id_product']) : 0;
		
		self::$producPropertiesCache[$cacheKey] = $row;
		return self::$producPropertiesCache[$cacheKey];
	}

	static public function getProductsProperties($id_lang, $query_result)
	{
		$resultsArray = array();
		foreach ($query_result AS $row)
			if ($row2 = Product::getProductProperties($id_lang, $row))
				$resultsArray[] = $row2;
		return $resultsArray;
	}

	/*
	* Select all features for a given language
	*
	* @param $id_lang Language id
	* @return array Array with feature's data
	*/
	static public function getFrontFeaturesStatic($id_lang, $id_product)
	{
		return Db::getInstance()->ExecuteS('
		SELECT name, value, pf.id_feature
		FROM '._DB_PREFIX_.'feature_product pf
		LEFT JOIN '._DB_PREFIX_.'feature_lang fl ON (fl.id_feature = pf.id_feature AND fl.id_lang = '.intval($id_lang).')
		LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = pf.id_feature_value AND fvl.id_lang = '.intval($id_lang).')
		WHERE pf.id_product = '.intval($id_product));
	}

	public function getFrontFeatures($id_lang)
	{
		return self::getFrontFeaturesStatic($id_lang, $this->id);
	}
	
	static public function getAttachmentsStatic($id_lang, $id_product)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM '._DB_PREFIX_.'product_attachment pa
		LEFT JOIN '._DB_PREFIX_.'attachment a ON a.id_attachment = pa.id_attachment
		LEFT JOIN '._DB_PREFIX_.'attachment_lang al ON (a.id_attachment = al.id_attachment AND al.id_lang = '.intval($id_lang).')
		WHERE pa.id_product = '.intval($id_product));
	}

	public function getAttachments($id_lang)
	{
		return self::getAttachmentsStatic($id_lang, $this->id);
	}

	/*
	** Customization management
	*/

	static public function getAllCustomizedDatas($id_cart)
	{
		if (!$result = Db::getInstance()->ExecuteS('
			SELECT cd.`id_customization`, c.`id_product`, c.`id_product_attribute`, cd.`type`, cd.`index`, cd.`value`
			FROM `'._DB_PREFIX_.'customized_data` cd
			NATURAL JOIN `'._DB_PREFIX_.'customization` c
			WHERE c.`id_cart` = '.intval($id_cart).'
			ORDER BY `id_product`, `id_product_attribute`, `type`, `index`'))
			return false;
		$customizedDatas = array();
		foreach ($result AS $row)
			$customizedDatas[intval($row['id_product'])][intval($row['id_product_attribute'])][intval($row['id_customization'])]['datas'][intval($row['type'])][] = $row;
		if (!$result = Db::getInstance()->ExecuteS('SELECT `id_product`, `id_product_attribute`, `id_customization`, `quantity`, `quantity_refunded`, `quantity_returned` FROM `'._DB_PREFIX_.'customization` WHERE `id_cart` = '.intval($id_cart)))
			return false;

		foreach ($result AS $row)
		{
			$customizedDatas[intval($row['id_product'])][intval($row['id_product_attribute'])][intval($row['id_customization'])]['quantity'] = intval($row['quantity']);
			$customizedDatas[intval($row['id_product'])][intval($row['id_product_attribute'])][intval($row['id_customization'])]['quantity_refunded'] = intval($row['quantity_refunded']);
			$customizedDatas[intval($row['id_product'])][intval($row['id_product_attribute'])][intval($row['id_customization'])]['quantity_returned'] = intval($row['quantity_returned']);
		}
		return $customizedDatas;
	}

	public function deleteCustomizedDatas($id_customization)
	{
		if (($result = Db::getInstance()->ExecuteS('SELECT `value` FROM `'._DB_PREFIX_.'customized_data` WHERE `id_customization` = '.intval($id_customization).' AND `type` = '._CUSTOMIZE_FILE_)) === false)
			return false;
		foreach ($result AS $row)
			if (!@unlink(_PS_PROD_PIC_DIR_.$row['value']) OR !@unlink(_PS_PROD_PIC_DIR_.$row['value'].'_small'))
				return false;
		return (Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization` WHERE `id_customization` = '.intval($id_customization)) AND Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customized_data` WHERE `id_customization` = '.intval($id_customization)));
	}

	static public function addCustomizationPrice(&$products, &$customizedDatas)
	{
		foreach ($products AS &$productUpdate)
		{
			$customizationQuantity = 0;
			$customizationQuantityRefunded = 0;
			$customizationQuantityReturned = 0;
			/* Compatibility */
			$productId = intval(isset($productUpdate['id_product']) ? $productUpdate['id_product'] : $productUpdate['product_id']);
			$productAttributeId = intval(isset($productUpdate['id_product_attribute']) ? $productUpdate['id_product_attribute'] : $productUpdate['product_attribute_id']);
			$productQuantity = intval(isset($productUpdate['quantity']) ? $productUpdate['quantity'] : $productUpdate['product_quantity']);
			$price = isset($productUpdate['price']) ? $productUpdate['price'] : $productUpdate['product_price'];
			$priceWt = $price * (1 + ((isset($productUpdate['tax_rate']) ? $productUpdate['tax_rate'] : $productUpdate['rate'])* 0.01));
			if (isset($customizedDatas[$productId][$productAttributeId]))
				foreach ($customizedDatas[$productId][$productAttributeId] AS $customization)
				{
					$customizationQuantity += intval($customization['quantity']);
					$customizationQuantityRefunded += intval($customization['quantity_refunded']);
					$customizationQuantityReturned += intval($customization['quantity_returned']);
				}
			$productUpdate['customizationQuantityTotal'] = $customizationQuantity;
			$productUpdate['customizationQuantityRefunded'] = $customizationQuantityRefunded;
			$productUpdate['customizationQuantityReturned'] = $customizationQuantityReturned;
			if ($customizationQuantity)
			{
				$productUpdate['total_wt'] = $priceWt * ($productQuantity - $customizationQuantity);
				$productUpdate['total_customization_wt'] = $priceWt * $customizationQuantity;
				$productUpdate['total'] = $price * ($productQuantity - $customizationQuantity);
				$productUpdate['total_customization'] = $price * $customizationQuantity;
			}
		}
	}

	/*
	** Customization fields' label management
	*/

	private function _checkLabelField($field, $value)
	{
		if (!Validate::isLabel($value))
			return false;
		$tmp = explode('_', $field);
		if (count($tmp) < 4)
			return false;
		return $tmp;
	}

	private function _deleteOldLabels()
	{
		$max = array(_CUSTOMIZE_FILE_ => intval(Tools::getValue('uploadable_files')), _CUSTOMIZE_TEXTFIELD_ => intval(Tools::getValue('text_fields')));
		/* Get customization field ids */
		if (($result = Db::getInstance()->ExecuteS('SELECT `id_customization_field`, `type` FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.intval($this->id).' ORDER BY `id_customization_field`')) === false)
			return false;
		if (empty($result))
			return true;
		$customizationFields = array(_CUSTOMIZE_FILE_ => array(), _CUSTOMIZE_TEXTFIELD_ => array());
		foreach ($result AS $row)
			$customizationFields[intval($row['type'])][] = intval($row['id_customization_field']);
		$extraFile = count($customizationFields[_CUSTOMIZE_FILE_]) - $max[_CUSTOMIZE_FILE_];
		$extraText = count($customizationFields[_CUSTOMIZE_TEXTFIELD_]) - $max[_CUSTOMIZE_TEXTFIELD_];

		/* If too much inside the database, deletion */
		if ($extraFile > 0 AND count($customizationFields[_CUSTOMIZE_FILE_]) - $extraFile >= 0 AND
		(!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.intval($this->id).' AND `type` = '._CUSTOMIZE_FILE_.' AND `id_customization_field` >= '.intval($customizationFields[_CUSTOMIZE_FILE_][count($customizationFields[_CUSTOMIZE_FILE_]) - $extraFile]))
		OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization_field_lang` WHERE `id_customization_field` NOT IN (SELECT `id_customization_field` FROM `'._DB_PREFIX_.'customization_field`)')))
			return false;

		if ($extraText > 0 AND count($customizationFields[_CUSTOMIZE_TEXTFIELD_]) - $extraText >= 0 AND
		(!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.intval($this->id).' AND `type` = '._CUSTOMIZE_TEXTFIELD_.' AND `id_customization_field` >= '.intval($customizationFields[_CUSTOMIZE_TEXTFIELD_][count($customizationFields[_CUSTOMIZE_TEXTFIELD_]) - $extraText]))
		OR !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization_field_lang` WHERE `id_customization_field` NOT IN (SELECT `id_customization_field` FROM `'._DB_PREFIX_.'customization_field`)')))
			return false;
		return true;
	}

	private function _createLabel(&$languages, $type)
	{
		/* Label insertion */
		if (!Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`) VALUES ('.intval($this->id).', '.intval($type).', 0)') OR !$id_customization_field = intval(Db::getInstance()->Insert_ID()))
			return false;

		/* Multilingual label name creation */
		$values = '';
		foreach ($languages AS $language)
			$values .= '('.$id_customization_field.', '.intval($language['id_lang']).', \'\'), ';
		$values = rtrim($values, ', ');
		if (!Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `name`) VALUES '.$values))
			return false;
		return true;
	}

	public function createLabels($uploadableFiles, $textFields)
	{
		$languages = Language::getLanguages();
		if (intval($uploadableFiles) > 0)
			for ($i = 0; $i < intval($uploadableFiles); $i++)
				if (!$this->_createLabel($languages, _CUSTOMIZE_FILE_))
					return false;
		if (intval($textFields) > 0)
			for ($i = 0; $i < intval($textFields); $i++)
				if (!$this->_createLabel($languages, _CUSTOMIZE_TEXTFIELD_))
					return false;
		return true;
	}

	public function updateLabels()
	{
		$hasRequiredFields = 0;
		foreach ($_POST AS $field => $value)
			/* Label update */
			if (strncmp($field, 'label_', 6) == 0)
			{
				if (!$tmp = $this->_checkLabelField($field, $value))
					return false;
				/* Multilingual label name update */
				if (!Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'customization_field_lang`
					(`id_customization_field`, `id_lang`, `name`) VALUES ('.intval($tmp[2]).', '.intval($tmp[3]).', \''.pSQL($value).'\')
					ON DUPLICATE KEY UPDATE `name` = \''.pSQL($value).'\''))
					return false;
				$isRequired = isset($_POST['require_'.intval($tmp[1]).'_'.intval($tmp[2])]) ? 1 : 0;
				$hasRequiredFields |= $isRequired;
				/* Require option update */
				if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customization_field` SET `required` = '.intval($isRequired).' WHERE `id_customization_field` = '.intval($tmp[2])))
					return false;
			}
		if ($hasRequiredFields AND !Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `customizable` = 2 WHERE `id_product` = '.intval($this->id)))
			return false;
		if (!$this->_deleteOldLabels())
			return false;
		return true;
	}

	public function getCustomizationFields($id_lang = false)
	{
		if (!$result = Db::getInstance()->ExecuteS('
			SELECT cf.`id_customization_field`, cf.`type`, cf.`required`, cfl.`name`, cfl.`id_lang`
			FROM `'._DB_PREFIX_.'customization_field` cf
			NATURAL JOIN `'._DB_PREFIX_.'customization_field_lang` cfl
			WHERE cf.`id_product` = '.intval($this->id).($id_lang ? ' AND cfl.`id_lang` = '.intval($id_lang) : '').'
			ORDER BY cf.`id_customization_field`'))
			return false;
		if ($id_lang)
			return $result;
		$customizationFields = array();
		foreach ($result AS $row)
			$customizationFields[intval($row['type'])][intval($row['id_customization_field'])][intval($row['id_lang'])] = $row;
		return $customizationFields;
	}

	public function getCustomizationFieldIds()
	{
		return Db::getInstance()->ExecuteS('SELECT `id_customization_field`, `type` FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.intval($this->id));
	}

	public function getRequiredCustomizableFields()
	{
		return Db::getInstance()->ExecuteS('SELECT `id_customization_field`, `type` FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.intval($this->id).' AND `required` = 1');
	}
	
	public function hasAllRequiredCustomizableFields()
	{
		global $cookie;

		$fields = array_merge($cookie->getFamily('pictures_'.intval($this->id)), $cookie->getFamily('textFields_'.intval($this->id)));
		if (($requiredFields = $this->getRequiredCustomizableFields()) === false)
			return false;
		$prefix = array(_CUSTOMIZE_FILE_ => 'pictures_'.intval($this->id).'_', _CUSTOMIZE_TEXTFIELD_ => 'textFields_'.intval($this->id).'_');
		foreach ($requiredFields AS $field)
			if (!isset($fields[$prefix[$field['type']].$field['id_customization_field']]) OR empty($fields[$prefix[$field['type']].$field['id_customization_field']]))
				return false;
		return true;
	}

	/**
	* Specify if a product is already in database
	*
	* @param $id_product Product id
	* @return boolean
	*/	
	public static function existsInDatabase($id_product)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_product`
		FROM '._DB_PREFIX_.'product p
		WHERE p.`id_product` = '.intval($id_product));
		
		return isset($row['id_product']);
	}
	
	public static function idIsOnCategoryId($id_product, $categories)
	{
		$sql = 'SELECT id_product FROM `'._DB_PREFIX_.'category_product` WHERE `id_product`='.intval($id_product).' AND `id_category` IN(';
		foreach ($categories AS $category)
			$sql .= intval($category['id_category']).',';
		$sql = rtrim($sql, ',').')';

		if (isset(self::$_incat[md5($sql)]))
			return self::$_incat[md5($sql)];

		if (!Db::getInstance()->Execute($sql))
			return false;
		self::$_incat[md5($sql)] =  (Db::getInstance()->NumRows() > 0 ? true : false);
		return self::$_incat[md5($sql)];
	}
	
	public function getNoPackPrice()
	{
		return Pack::noPackPrice($this->id);
	}

	public function checkAccess($id_customer)
	{
		if (!$id_customer)
		{
			$result = Db::getInstance()->getRow('
			SELECT ctg.`id_group`
			FROM `'._DB_PREFIX_.'category_product` cp
			INNER JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category`)
			WHERE cp.`id_product` = '.intval($this->id).' AND ctg.`id_group` = 1');
		} else {
			$result = Db::getInstance()->getRow('
			SELECT cg.`id_group`
			FROM `'._DB_PREFIX_.'category_product` cp
			INNER JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category`)
			INNER JOIN `'._DB_PREFIX_.'customer_group` cg ON (cg.`id_group` = ctg.`id_group`)
			WHERE cp.`id_product` = '.intval($this->id).' AND cg.`id_customer` = '.intval($id_customer));
		}
		if ($result AND isset($result['id_group']) AND $result['id_group'])
			return true;
		return false;
	}
}

?>
