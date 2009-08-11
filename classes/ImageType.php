<?php

/**
	* ImageType class, ImageType.php
	* Image types management
	* @category classes
	*
	* @author PrestaShop <support@prestashop.com>
	* @copyright PrestaShop
	* @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
	* @version 1.2
	*
	*/

class		ImageType extends ObjectModel
{
	public		$id;

	/** @var string Name */
	public		$name;

	/** @var integer Width */
	public		$width;

	/** @var integer Height */
	public 		$height;

	/** @var boolean Apply to products */
	public		$products;

	/** @var integer Apply to categories */
	public 		$categories;

	/** @var integer Apply to manufacturers */
	public 		$manufacturers;

	/** @var integer Apply to suppliers */
	public 		$suppliers;
	
	/** @var integer Apply to scenes */
	public 		$scenes;

	protected $fieldsRequired = array('name', 'width', 'height');
	protected $fieldsValidate = array(
		'name' => 'isImageTypeName',
		'width' => 'isImageSize',
		'height' => 'isImageSize',
		'categories' => 'isBool',
		'products' => 'isBool',
		'manufacturers' => 'isBool',
		'suppliers' => 'isBool',
		'scenes' => 'isBool'
	);
	protected $fieldsSize = array('name' => 16);

	protected $table = 'image_type';
	protected $identifier = 'id_image_type';

	public function getFields()
	{
		parent::validateFields();
		$fields['name'] = pSQL($this->name);
		$fields['width'] = intval($this->width);
		$fields['height'] = intval($this->height);
		$fields['products'] = intval($this->products);
		$fields['categories'] = intval($this->categories);
		$fields['manufacturers'] = intval($this->manufacturers);
		$fields['suppliers'] = intval($this->suppliers);
		$fields['scenes'] = intval($this->scenes);
		return $fields;
	}

	/**
		* Return Image types
		*
		* @return array Image types
		*/
	static public function getImagesTypes($type = NULL)
	{
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'image_type`
			WHERE 1
			'.(($type == 'products') ? ' AND products = 1 ' : '').'
			'.(($type == 'categories') ? ' AND categories = 1 ' : '').'
			'.(($type == 'manufacturers') ? ' AND manufacturers = 1 ' : '').'
			'.(($type == 'suppliers') ? ' AND suppliers = 1 ' : '').'
			'.(($type == 'scenes') ? ' AND scenes = 1 ' : '').'
			ORDER BY `name` ASC'
		);
	}
	
	/**
		* Check if type already is already registered in database
		*
		* @param string $typeName Name
		* @return integer Number of results found
		*/
	static public function typeAlreadyExists($typeName)
	{
		if (!Validate::isImageTypeName($typeName))
			die(Tools::displayError());
		$result = Db::getInstance()->ExecuteS('
			SELECT `id_image_type`
			FROM `'._DB_PREFIX_.'image_type`
			WHERE `name` = \''.pSQL($typeName).'\''
		);
		return Db::getInstance()->NumRows();
	}

	static public function getByNameNType($name, $type)
	{
		if (!in_array($type, array('products', 'categories', 'manufacturers', 'suppliers', 'scenes')))
			die(Tools::displayError());
		return Db::getInstance()->getRow('SELECT `id_image_type`, `name`, `width`, `height`, `products`, `categories`, `manufacturers`, `suppliers`, `scenes` FROM `'._DB_PREFIX_.'image_type` WHERE `name` = \''.pSQL($name).'\' AND `'.pSQL($type).'` = 1');
	}

}
