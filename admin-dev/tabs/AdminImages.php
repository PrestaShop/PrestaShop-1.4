<?php

/**
  * Image settings tab for admin panel, AdminImages.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(realpath(PS_ADMIN_DIR.'/../').'/classes/AdminTab.php');

class AdminImages extends AdminTab
{
	public function __construct()
	{
		$this->table = 'image_type';
		$this->className = 'ImageType';
		$this->lang = false;
		$this->edit = true;
	 	$this->delete = true;

		$this->fieldsDisplay = array(
			'id_image_type' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 140, 'size' => 16),
			'width' => array('title' => $this->l('Width'), 'align' => 'right', 'suffix' => ' px', 'width' => 50, 'size' => 5),
			'height' => array('title' => $this->l('Height'), 'align' => 'right', 'suffix' => ' px', 'width' => 50, 'size' => 5)
		);

		parent::__construct();
	}

	public function displayList()
	{
		parent::displayList();
		$this->displayRegenerate();
	}

	public function postProcess()
	{
		global $currentIndex;
		if (Tools::getValue('submitRegenerate'.$this->table))
		{
		 	if ($this->tabAccess['edit'] === '1')
		 	{
				if ($this->_regenerateThumbnails())
					Tools::redirectAdmin($currentIndex.'&conf=9'.'&token='.$this->token);
				$this->_errors[] = Tools::displayError('An error occured while thumbnails\' regeneration.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		else
			parent::postProcess();
	}

	protected function _childValidation()
	{
		if (!Tools::getValue('id_image_type') AND Validate::isImageTypeName($typeName = Tools::getValue('name')) AND ImageType::typeAlreadyExists($typeName))
			$this->_errors[] = Tools::displayError('this name already exists');
	}

	public function displayForm()
	{
		global $currentIndex;
		$obj = $this->loadObject(true);

		echo $obj->id ? $this->displayWarning($this->l('After modification, do not forget to regenerate thumbnails')) : '';

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/picture.gif" />'.$this->l('Images').'</legend><br />
				<label>'.$this->l('Type name:').' </label>
				<div class="margin-form">
					<input type="text" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Letters only (e.g., small, medium, large, extra-large)').'</p>
				</div>
				<label>'.$this->l('Width:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="5" name="width" value="'.intval($this->getFieldValue($obj, 'width')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Maximum image width in pixels').'</p>
				</div>
				<label>'.$this->l('Height:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="5" name="height" value="'.intval($this->getFieldValue($obj, 'height')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Maximum image height in pixels').'</p>
				</div>
				<label>'.$this->l('Products:').' </label>
				<div class="margin-form">
					<input type="radio" name="products" id="products_on" value="1" '.($this->getFieldValue($obj, 'products') ? 'checked="checked" ' : '').'/>
					<label class="t" for="products_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="products" id="products_off" value="0" '.(!$this->getFieldValue($obj, 'products') ? 'checked="checked" ' : '').'/>
					<label class="t" for="products_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('This type will be applied to product images').'</p>
				</div>
				<label>'.$this->l('Categories:').' </label>
				<div class="margin-form">
					<input type="radio" name="categories" id="categories_on" value="1" '.($this->getFieldValue($obj, 'categories') ? 'checked="checked" ' : '').'/>
					<label class="t" for="categories_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="categories" id="categories_off" value="0" '.(!$this->getFieldValue($obj, 'categories') ? 'checked="checked" ' : '').'/>
					<label class="t" for="categories_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('This type will be applied to category images').'</p>
				</div>
				<label>'.$this->l('Manufacturers:').' </label>
				<div class="margin-form">
					<input type="radio" name="manufacturers" id="manufacturers_on" value="1" '.($this->getFieldValue($obj, 'manufacturers') ? 'checked="checked" ' : '').'/>
					<label class="t" for="manufacturers_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="manufacturers" id="manufacturers_off" value="0" '.(!$this->getFieldValue($obj, 'manufacturers') ? 'checked="checked" ' : '').'/>
					<label class="t" for="manufacturers_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('This type will be applied to manufacturer images').'</p>
				</div>
				<label>'.$this->l('Suppliers:').' </label>
				<div class="margin-form">
					<input type="radio" name="suppliers" id="suppliers_on" value="1" '.($this->getFieldValue($obj, 'suppliers') ? 'checked="checked" ' : '').'/>
					<label class="t" for="suppliers_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="suppliers" id="suppliers_off" value="0" '.(!$this->getFieldValue($obj, 'suppliers') ? 'checked="checked" ' : '').'/>
					<label class="t" for="suppliers_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('This type will be applied to suppliers images').'</p>
				</div>
				<label>'.$this->l('Scenes:').' </label>
				<div class="margin-form">
					<input type="radio" name="scenes" id="scenes_on" value="1" '.($this->getFieldValue($obj, 'scenes') ? 'checked="checked" ' : '').'/>
					<label class="t" for="scenes_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="scenes" id="scenes_off" value="0" '.(!$this->getFieldValue($obj, 'scenes') ? 'checked="checked" ' : '').'/>
					<label class="t" for="scenes_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('This type will be applied to scenes images').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	/**
	  * Display form for thumbnails regeneration
	  *
	  * @global string $currentIndex Current URL in order to keep current Tab
	  */
	public function displayRegenerate()
	{
	 	global $currentIndex;

		echo '
		<h2 class="space">'.$this->l('Regenerate thumbnails').'</h2>
		'.$this->l('Regenerates thumbnails for all existing product images').'.<br /><br />';
		$this->displayWarning($this->l('Please be patient, as this can take several minutes').'<br />'.$this->l('Be careful! Manually generated thumbnails will be erased by automatically generated thumbnails.'));
		echo '
		<form action="'.$currentIndex.'&token='.$this->token.'" method="post">
			<input type="Submit" name="submitRegenerate'.$this->table.'" value="'.$this->l('Regenerate thumbnails').'" class="button space" onclick="return confirm(\''.$this->l('Are you sure?', __CLASS__, true, false).'\');" />
		</form>';
	}

	/**
	  * Delete resized image then regenerate new one with updated settings
	  */
	private function _regenerateThumbnails()
	{
		@ini_set('max_execution_time', 3600);
		
		$productsTypes = ImageType::getImagesTypes('products');
		$categoriesTypes = ImageType::getImagesTypes('categories');
		$languages = Language::getLanguages();
		$productsImages = Image::getAllImages();

		/* Delete categories images */
		$toDel = scandir(_PS_CAT_IMG_DIR_);
		foreach ($toDel AS $d)
			if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
				unlink(_PS_CAT_IMG_DIR_.$d);

		/* Regenerate categories images */
		$errors = false;
		$categoriesImages = scandir(_PS_CAT_IMG_DIR_);
		foreach ($categoriesImages as $image)
			if (preg_match('/^[0-9]*\.jpg$/', $image))
				foreach ($categoriesTypes AS $k => $imageType)
					if (!imageResize(_PS_CAT_IMG_DIR_.$image, _PS_CAT_IMG_DIR_.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg', intval($imageType['width']), intval($imageType['height'])))
						$errors = true;
		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write category image. Please check the folder\'s writing permissions.');

		/* Regenerate No-picture images */
		$errors = false;
		foreach ($categoriesTypes AS $k => $imageType)
			foreach ($languages AS $language)
			{
				$file = _PS_CAT_IMG_DIR_.$language['iso_code'].'.jpg';
				if (!file_exists($file))
					$file = _PS_PROD_IMG_DIR_.Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT'))).'.jpg';
				if (!imageResize($file, _PS_CAT_IMG_DIR_.$language['iso_code'].'-default-'.stripslashes($imageType['name']).'.jpg',
				intval($imageType['width']), intval($imageType['height'])))
					$errors = true;
			}
		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write no-picture image to the category images folder. Please check the folder\'s writing permissions.');

		/* Delete manufacturers images */
		$toDel = scandir(_PS_MANU_IMG_DIR_);
		foreach ($toDel AS $d)
			if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
				unlink(_PS_MANU_IMG_DIR_.$d);

		/* Regenerate manufacturers images */
		$manufacturersTypes = ImageType::getImagesTypes('manufacturers');
		$manufacturersImages = scandir(_PS_MANU_IMG_DIR_);
		$errors = false;
		foreach ($manufacturersImages AS $image)
			if (preg_match('/^[0-9]*\.jpg$/', $image))
				foreach ($manufacturersTypes AS $k => $imageType)
					if (!imageResize(_PS_MANU_IMG_DIR_.$image, _PS_MANU_IMG_DIR_.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg', intval($imageType['width']), intval($imageType['height'])))
						$errors = true;

		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write manufacturer images. Please check the folder\'s writing permissions.');

		/* Regenerate No-picture images */
		$errors = false;
		foreach ($manufacturersTypes AS $k => $imageType)
			foreach ($languages AS $language)
			{
				$file = _PS_MANU_IMG_DIR_.$language['iso_code'].'.jpg';
				if (!file_exists($file))
					$file = _PS_PROD_IMG_DIR_.Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT'))).'.jpg';
				if (!imageResize($file, _PS_MANU_IMG_DIR_.$language['iso_code'].'-default-'.stripslashes($imageType['name']).'.jpg',
				intval($imageType['width']), intval($imageType['height'])))
					$errors = true;
			}
		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write no-picture image to the manufacturer images folder. Please check the folder\'s writing permissions.');

		/* Delete suppliers images */
		$toDel = scandir(_PS_SUPP_IMG_DIR_);
		foreach ($toDel AS $d)
			if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
				unlink(_PS_SUPP_IMG_DIR_.$d);

		/* Regenerate suppliers images */
		$suppliersTypes = ImageType::getImagesTypes('suppliers');
		$suppliersImages = scandir(_PS_SUPP_IMG_DIR_);
		$errors = false;
		foreach ($suppliersImages AS $image)
			if (preg_match('/^[0-9]*\.jpg$/', $image))
				foreach ($suppliersTypes AS $k => $imageType)
					if (!imageResize(_PS_SUPP_IMG_DIR_.$image, _PS_SUPP_IMG_DIR_.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg', intval($imageType['width']), intval($imageType['height'])))
						$errors = true;

		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write supplier images into the supplier images folder. Please check the folder\'s writing permissions.');

		/* Regenerate No-picture images */
		$errors = false;
		foreach ($suppliersTypes AS $k => $imageType)
			foreach ($languages AS $language)
			{
				$file = _PS_SUPP_IMG_DIR_.$language['iso_code'].'.jpg';
				if (!file_exists($file))
					$file = _PS_PROD_IMG_DIR_.Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT'))).'.jpg';
				if (!imageResize($file, _PS_SUPP_IMG_DIR_.$language['iso_code'].'-default-'.stripslashes($imageType['name']).'.jpg',
				intval($imageType['width']), intval($imageType['height'])))
					$errors = true;
			}
		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write no-picture image into the suppliers images folder.<br />Please check its writing permissions.');

		/* Delete scenes images */
		$toDel = scandir(_PS_SCENE_IMG_DIR_);
		foreach ($toDel AS $d)
			if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
				unlink(_PS_SCENE_IMG_DIR_.$d);

		/* Regenerate scenes images */
		$scenesTypes = ImageType::getImagesTypes('scenes');
		$scenesImages = scandir(_PS_SCENE_IMG_DIR_);
		$errors = false;
		foreach ($scenesImages AS $image)
			if (preg_match('/^[0-9]*\.jpg$/', $image))
				foreach ($scenesTypes AS $k => $imageType)
					if (!imageResize(_PS_SCENE_IMG_DIR_.$image, _PS_SCENE_IMG_DIR_.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg', intval($imageType['width']), intval($imageType['height'])))
						$errors = true;

		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write scene images into the scene images folder. Please check the folder\'s writing permissions.');

		/* Regenerate No-picture images */
		$errors = false;
		foreach ($scenesTypes AS $k => $imageType)
			foreach ($languages AS $language)
			{
				$file = _PS_SCENE_IMG_DIR_.$language['iso_code'].'.jpg';
				if (!file_exists($file))
					$file = _PS_PROD_IMG_DIR_.Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT'))).'.jpg';
				if (!imageResize($file, _PS_SCENE_IMG_DIR_.$language['iso_code'].'-default-'.stripslashes($imageType['name']).'.jpg',
				intval($imageType['width']), intval($imageType['height'])))
					$errors = true;
			}
		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write no-picture image into the scenes images folder.<br />Please check its writing permissions.');
			
		/* Delete products images */
		$toDel = scandir(_PS_PROD_IMG_DIR_);
		foreach ($toDel AS $d)
			if (preg_match('/^[0-9]+\-[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
				unlink(_PS_PROD_IMG_DIR_.$d);
		
		
		/* Regenerate No-picture images */
		$errors = false;
		foreach ($productsTypes AS $k => $imageType)
			foreach ($languages AS $language)
			{
				$file = _PS_PROD_IMG_DIR_.$language['iso_code'].'.jpg';
				if (!file_exists($file))
					$file = _PS_PROD_IMG_DIR_.Language::getIsoById(intval(Configuration::get('PS_LANG_DEFAULT'))).'.jpg';
				$newFile = _PS_PROD_IMG_DIR_.$language['iso_code'].'-default-'.stripslashes($imageType['name']).'.jpg';
				if (!imageResize($file, $newFile,
				intval($imageType['width']), intval($imageType['height'])))
					$errors = true;
			}
		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write no-picture image to the product images folder. Please check the folder\'s writing permissions.');

		/* Regenerate products images */
		$errors = false;
		foreach ($productsImages AS $k => $image)
		{
			if (file_exists(_PS_PROD_IMG_DIR_.$image['id_product'].'-'.$image['id_image'].'.jpg'))
				foreach ($productsTypes AS $k => $imageType)
				{
					$newFile = _PS_PROD_IMG_DIR_.$image['id_product'].'-'.$image['id_image'].'-'.stripslashes($imageType['name']).'.jpg';
					if (!imageResize(_PS_PROD_IMG_DIR_.$image['id_product'].'-'.$image['id_image'].'.jpg', $newFile, intval($imageType['width']), intval($imageType['height'])))
						$errors = true;
				}
		}
		
		// Hook watermark optimization
		$result = Db::getInstance()->ExecuteS('
		SELECT m.`name` FROM `'._DB_PREFIX_.'module` m
		LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
		LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
		WHERE h.`name` = \'watermark\' AND m.`active` = 1');
		if ($result AND sizeof($result))
			foreach ($productsImages AS $k => $image)
				if (file_exists(_PS_PROD_IMG_DIR_.$image['id_product'].'-'.$image['id_image'].'.jpg'))
					foreach ($result AS $k => $module)
						if ($moduleInstance = Module::getInstanceByName($module['name']) AND is_callable(array($moduleInstance, 'hookwatermark')))
							call_user_func(array($moduleInstance, 'hookwatermark'), array('id_image' => $image['id_image'], 'id_product' => $image['id_product']));

		if ($errors)
			$this->_errors[] = Tools::displayError('Cannot write product image. Please check the folder\'s writing permissions.');
			
		return (sizeof($this->_errors) > 0 ? false : true);
	}
}

?>
