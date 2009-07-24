<?php

/**
  * Image resize tab for admin panel, AdminImageResize.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminImageResize extends AdminTab
{
	public function postProcess()
	{
		global $currentIndex, $cookie;

		if (isset($_POST['resize']))
		{
			$imagesTypes = ImageType::getImagesTypes('products');
			$sourceFile['tmp_name'] = _PS_IMG_DIR_.'/p/'.Tools::getValue('id_product').'-'.Tools::getValue('id_image').'.jpg';
			foreach ($imagesTypes AS $k => $imageType)
				if (!imageCut
				($sourceFile,
				_PS_IMG_DIR_.'p/'.Tools::getValue('id_product').'-'.Tools::getValue('id_image').'-'.stripslashes($imageType['name']).'.jpg', 
				$imageType['width'], 
				$imageType['height'], 
				'jpg',
				$_POST[$imageType['id_image_type'].'_x1'],
				$_POST[$imageType['id_image_type'].'_y1']))
					$this->_errors = Tools::displayError('an error occurred while copying image').' '.stripslashes($imageType['name']);
				// Save and stay on same form
				if (Tools::getValue('saveandstay') == 'on')
					Tools::redirectAdmin($currentIndex.'&id_product='.Tools::getValue('id_product').'&id_category='.intval(Tools::getValue('id_category')).'&addproduct&conf=4&tabs=1&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)));
				// Default behavior (save and back)
				Tools::redirectAdmin($currentIndex.'&id_category='.intval(Tools::getValue('id_category')).'&conf='.intval(Tools::getValue('conf')).'&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)));
		} else
			parent::postProcess();
	}

	public function displayForm()
	{
		global $currentIndex, $cookie;
		$imagesTypes = ImageType::getImagesTypes();

		echo '
			<script type="text/javascript" src="../js/cropper/prototype.js"></script>
			<script type="text/javascript" src="../js/cropper/scriptaculous.js"></script>
			<script type="text/javascript" src="../js/cropper/builder.js"></script>
			<script type="text/javascript" src="../js/cropper/dragdrop.js"></script>
			<script type="text/javascript" src="../js/cropper/cropper.js"></script>
			<script type="text/javascript" src="../js/cropper/loader.js"></script>
			<form enctype="multipart/form-data"  method="post" action="'.$currentIndex.'&imageresize&token='.Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee)).'">
				<input type="hidden" name="id_product" value="'.Tools::getValue('id_product').'" />
				<input type="hidden" name="id_category" value="'.Tools::getValue('id_category').'" />
				<input type="hidden" name="saveandstay" value="'.Tools::getValue('submitAddAndStay').'" />
				<input type="hidden" name="conf" value="'.(Tools::getValue('toconf')).'" />
				<input type="hidden" name="imageresize" value="imageresize" />
				<input type="hidden" name="id_image" value="'.Tools::getValue('id_image').'" />
				<fieldset class="width2">
					<legend><img src="../img/admin/picture.gif" />'.$this->l('Image resize').'</legend>
					'.$this->l('Using your mouse, define which area of the image is to be used for generating each type of thumbnail.').'
					<br /><br />
					<img src="../img/p/'.Tools::getValue('id_product').'-'.Tools::getValue('id_image').'.jpg" id="testImage">
					<label for="imageChoice">'.$this->l('Thumbnails format:').'</label>
					<div class="margin-form"">
						<select name="imageChoice" id="imageChoice">';
							foreach ($imagesTypes as $type)
								echo '<option value="../img/p/'.Tools::getValue('id_product').'-'.Tools::getValue('id_image').'.jpg|'.$type['width'].'|'.$type['height'].'|'.$type['id_image_type'].'">'.$type['name'].'</option>';
			echo '		</select>
						<input type="submit" class="button" style="margin-left : 40px;" name="resize" value="'.$this->l('   Save all  ').'" />
					</div>';
					foreach ($imagesTypes as $type)
						echo '
					<input type="hidden" name="'.$type['id_image_type'].'_x1" id="'.$type['id_image_type'].'_x1" value="0" />
					<input type="hidden" name="'.$type['id_image_type'].'_y1" id="'.$type['id_image_type'].'_y1" value="0" />
					<input type="hidden" name="'.$type['id_image_type'].'_x2" id="'.$type['id_image_type'].'_x2" value="0" />
					<input type="hidden" name="'.$type['id_image_type'].'_y2" id="'.$type['id_image_type'].'_y2" value="0" />';
			echo '	</fieldset>
			</form>';
		}
}

?>