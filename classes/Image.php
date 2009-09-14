<?php

/**
  * Image class, Image.php
  * Images management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
class		Image extends ObjectModel
{
	public		$id;

	/** @var integer Image ID */
	public $id_image;
	
	/** @var integer Product ID */
	public		$id_product;
	
	/** @var string HTML title and alt attributes */
	public		$legend;
	
	/** @var integer Position used to order images of the same product */
	public		$position;
	
	/** @var boolean Image is cover */
	public		$cover;

	protected $tables = array ('image', 'image_lang');
	
	protected	$fieldsRequired = array('id_product');
	protected 	$fieldsValidate = array('id_product' => 'isUnsignedId', 'position' => 'isUnsignedInt', 'cover' => 'isBool');
	protected 	$fieldsRequiredLang = array('legend');
	protected 	$fieldsSizeLang = array('legend' => 128);
	protected 	$fieldsValidateLang = array('legend' => 'isGenericName');
	
	protected 	$table = 'image';
	protected 	$identifier = 'id_image';	
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_product'] = intval($this->id_product);
		$fields['position'] = intval($this->position);
		$fields['cover'] = intval($this->cover);
		return $fields;
	}
	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('legend'));
	}
	
	public function delete()
	{
		parent::delete();
		$result = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'image`
		WHERE `id_product` = '.intval($this->id_product).'
		ORDER BY `position`');
		$i = 1;
		
		foreach ($result as $row)
		{
			$row['position'] = $i++;
			Db::getInstance()->AutoExecute(_DB_PREFIX_.$this->table, $row, 'UPDATE', '`id_image` = '.intval($row['id_image']), 1);
		}
	}
		
	/**
	  * Return available images for a product
	  *
	  * @param integer $id_lang Language ID
	  * @param integer $id_product Product ID
	  * @return array Images
	  */
	static public function getImages($id_lang, $id_product)
	{
		return Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'image` i
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON i.`id_image` = il.`id_image`
		WHERE i.`id_product` = '.intval($id_product).'
		AND il.`id_lang` = '.intval($id_lang).'
		ORDER BY `position` ASC');
	}
	
	/**
	  * Return Images
	  *
	  * @return array Images
	  */
	static public function getAllImages()
	{
		return Db::getInstance()->ExecuteS('
		SELECT `id_image`, `id_product`
		FROM `'._DB_PREFIX_.'image`
		ORDER BY `id_image` ASC');
	}
	
	/**
	  * Return number of images for a product
	  *
	  * @param integer $id_product Product ID
	  * @return integer number of images
	  */
	static public function getImagesTotal($id_product)
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(`id_image`) AS total
		FROM `'._DB_PREFIX_.'image`
		WHERE `id_product` = '.intval($id_product));
		return $result['total'];
	}
	
	/**
	  * Return highest position of images for a product
	  *
	  * @param integer $id_product Product ID
	  * @return integer highest position of images
	  */
	static public function getHighestPosition($id_product)
	{
		$result = Db::getInstance()->getRow('
		SELECT MAX(`position`) AS max
		FROM `'._DB_PREFIX_.'image`
		WHERE `id_product` = '.intval($id_product));
		return $result['max'];
	}
	
	/**
	  * Delete product cover
	  *
	  * @param integer $id_product Product ID
	  * @return boolean result
	  */
	static public function deleteCover($id_product)
	{
	 	if (!Validate::isUnsignedId($id_product))
	 		die(Tools::displayError());
			
		if (file_exists(_PS_TMP_IMG_DIR_.'product_'.$id_product.'.jpg'))
			unlink(_PS_TMP_IMG_DIR_.'product_'.$id_product.'.jpg');
		return Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'image` 
		SET `cover` = 0 
		WHERE `id_product` = '.intval($id_product));
	}
	
	/**
	  *Get product cover
	  *
	  * @param integer $id_product Product ID
	  * @return boolean result
	  */
	static public function getCover($id_product)
	{
		return Db::getInstance()->getRow('
		SELECT * FROM `'._DB_PREFIX_.'image` 
		WHERE `id_product` = '.intval($id_product).'
		AND `cover`= 1');
	}
	
	/**
	  * Copy images from a product to another
	  *
	  * @param integer $id_product_old Source product ID
	  * @param boolean $id_product_new Destination product ID
	  */
	static public function duplicateProductImages($id_product_old, $id_product_new, $combinationImages)
	{
		$imagesTypes = ImageType::getImagesTypes('products');
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_image`
		FROM `'._DB_PREFIX_.'image`
		WHERE `id_product` = '.intval($id_product_old));
		foreach ($result as $row)
		{
			$image = new Image($row['id_image']);
			$saved_id = $image->id_image;
			unset($image->id);
			unset($image->id_image);
			$image->id_product = intval($id_product_new);
			if ($image->add())
            {
				foreach ($imagesTypes AS $k => $imageType)
					if (file_exists(_PS_PROD_IMG_DIR_.intval($id_product_old).'-'.intval($row['id_image']).'-'.$imageType['name'].'.jpg'))
						copy(_PS_PROD_IMG_DIR_.intval($id_product_old).'-'.intval($row['id_image']).'-'.$imageType['name'].'.jpg', _PS_PROD_IMG_DIR_.
						intval($id_product_new).'-'.intval($image->id).'-'.$imageType['name'].'.jpg');
                if (file_exists(_PS_PROD_IMG_DIR_.intval($id_product_old).'-'.intval($row['id_image']).'.jpg'))
                    copy(_PS_PROD_IMG_DIR_.intval($id_product_old).'-'.intval($row['id_image']).'.jpg',
                            _PS_PROD_IMG_DIR_.intval($id_product_new).'-'.intval($image->id).'.jpg');
				self::replaceAttributeImageAssociationId($combinationImages, intval($saved_id), intval($image->id));
            }
			else
				return false;
		}
		return self::duplicateAttributeImageAssociations($combinationImages);
	}

	static private function replaceAttributeImageAssociationId(&$combinationImages, $saved_id, $id_image)
	{
		if (!isset($combinationImages['new']) OR !is_array($combinationImages['new']))
			return ;
		foreach ($combinationImages['new'] AS $id_product_attribute => $imageIds)
			foreach ($imageIds AS $key => $imageId)
				if (intval($imageId) == intval($saved_id))
					$combinationImages['new'][$id_product_attribute][$key] = intval($id_image);
	}

	/**
	* Duplicate product attribute image associations
	* @param integer $id_product_attribute_old
	* @return boolean
	*/
	static public function duplicateAttributeImageAssociations($combinationImages)
	{
		if (!isset($combinationImages['new']) OR !is_array($combinationImages['new']))
			return true;
		$query = 'INSERT INTO `'._DB_PREFIX_.'product_attribute_image` (`id_product_attribute`, `id_image`) VALUES ';
		foreach ($combinationImages['new'] AS $id_product_attribute => $imageIds)
			foreach ($imageIds AS $imageId)
				$query .= '('.intval($id_product_attribute).', '.intval($imageId).'), ';
		$query = rtrim($query, ', ');
		return DB::getInstance()->Execute($query);
	}

	/**
	  * Reposition image
	  *
	  * @param integer $position Position
	  * @param boolean $direction Direction
	  */
	public function	positionImage($position, $direction)
	{
		$position = intval($position);
		$direction = intval($direction);
		
		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'image`
		SET `position` = `position`'.($direction ? '+1' : '-1').'
		WHERE `id_product` = '.intval($this->id_product).'
		AND `position` = '.($direction ? $position - 1 : $position + 1));
		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'image`
		SET `position` = `position`'.($direction ? '-1' : '+1').'
		WHERE `id_product` = '.intval($this->id_product).'
		AND `id_image` = '.intval($this->id).'');
	}
	
	static public function getSize($type)
	{
	 	return Db::getInstance()->getRow('SELECT `width`, `height` FROM '._DB_PREFIX_.'image_type WHERE `name` = \''.pSQL($type).'\'');
	}
}

?>
