<?php

/**
  * Languages tab for admin panel, AdminLanguages.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminLanguages extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'lang';
	 	$this->className = 'Language';
	 	$this->lang = false;
	 	$this->edit = true;
	 	$this->delete = true;
 		
 		$this->fieldImageSettings = array(array('name' => 'flag', 'dir' => 'l'), array('name' => 'no-picture', 'dir' => 'p'));
		
		$this->fieldsDisplay = array(
		'id_lang' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'flag' => array('title' => $this->l('Logo'), 'align' => 'center', 'image' => 'l', 'orderby' => false, 'search' => false),
		'name' => array('title' => $this->l('Name'), 'width' => 120),
		'iso_code' => array('title' => $this->l('ISO code'), 'width' => 70, 'align' => 'center'),
		'active' => array('title' => $this->l('Enabled'), 'align' => 'center', 'active' => 'status', 'type' => 'bool'));
	
		$this->optionTitle = $this->l('Languages options');
		$this->_fieldsOptions = array(
		'PS_LANG_DEFAULT' => array('title' => $this->l('Default language:'), 'desc' => $this->l('The default language used in shop'), 'cast' => 'intval', 'type' => 'select', 'identifier' => 'id_lang', 'list' => Language::getlanguages()),
		);
		
		parent::__construct();
	}
	
	/**
	 * Copy a no-product image
	 *
	 * @param string $language Language iso_code for no-picture image filename
	 */
	public function copyNoPictureImage($language)
	{
		if ($error = checkImage($_FILES['no-picture'], $this->maxImageSize))
			$this->_errors[] = $error;
		else
		{
			if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['no-picture']['tmp_name'], $tmpName))
				return false;
			if (!imageResize($tmpName, _PS_IMG_DIR_.'p/'.$language.'.jpg'))
				$this->_errors[] = Tools::displayError('an error occurred while copying no-picture image to your product folder');
			if (!imageResize($tmpName, _PS_IMG_DIR_.'c/'.$language.'.jpg'))
				$this->_errors[] = Tools::displayError('an error occurred while copying no-picture image to your category folder');
			if (!imageResize($tmpName, _PS_IMG_DIR_.'m/'.$language.'.jpg'))
				$this->_errors[] = Tools::displayError('an error occurred while copying no-picture image to your manufacturer folder');
			else
			{	
				$imagesTypes = ImageType::getImagesTypes('products');
				foreach ($imagesTypes AS $k => $imageType)
				{
					if (!imageResize($tmpName, _PS_IMG_DIR_.'p/'.$language.'-default-'.stripslashes($imageType['name']).'.jpg', $imageType['width'], $imageType['height']))
						$this->_errors[] = Tools::displayError('an error occurred while resizing no-picture image to your product directory');
					if (!imageResize($tmpName, _PS_IMG_DIR_.'c/'.$language.'-default-'.stripslashes($imageType['name']).'.jpg', $imageType['width'], $imageType['height']))
						$this->_errors[] = Tools::displayError('an error occurred while resizing no-picture image to your category directory');
					if (!imageResize($tmpName, _PS_IMG_DIR_.'m/'.$language.'-default-'.stripslashes($imageType['name']).'.jpg', $imageType['width'], $imageType['height']))
						$this->_errors[] = Tools::displayError('an error occurred while resizing no-picture image to your manufacturer directory');
				}
			}
			unlink($tmpName);
		}
	}
	
	private function deleteNoPictureImages($id_language)
	{
	 	$language = Language::getIsoById($id_language);
		$imagesTypes = ImageType::getImagesTypes('products');
		$dirs = array(_PS_PROD_IMG_DIR_, _PS_CAT_IMG_DIR_, _PS_MANU_IMG_DIR_);
		foreach ($dirs AS $dir)
		{
			foreach ($imagesTypes AS $k => $imageType)
				if (file_exists($dir.$language.'-default-'.stripslashes($imageType['name']).'.jpg'))
					if (!unlink($dir.$language.'-default-'.stripslashes($imageType['name']).'.jpg'))
						$this->_errors[] = Tools::displayError('an error occurred during the image deletion');
			if (file_exists($dir.$language.'.jpg'))
				if (!unlink($dir.$language.'.jpg'))
					$this->_errors[] = Tools::displayError('an error occurred during the image deletion');
		}
		return !sizeof($this->_errors) ? true : false;
	}


	public function postProcess()
	{
		global $currentIndex, $cookie;

		if (isset($_GET['delete'.$this->table]))
		{
			if ($this->tabAccess['delete'] === '1') 	
		 	{
				if (Validate::isLoadedObject($object = $this->loadObject()) AND isset($this->fieldImageSettings))
				{
					if ($object->id == Configuration::get('PS_LANG_DEFAULT'))
						$this->_errors[] = $this->l('you cannot delete the default language');
					elseif ($object->id == $cookie->id_lang)
						$this->_errors[] = $this->l('you cannot delete the language currently in use, please change languages before deleting');
					elseif ($this->deleteNoPictureImages(intval(Tools::getValue('id_lang'))) AND $object->delete())
						Tools::redirectLink($currentIndex.'&conf=1'.'&token='.$this->token);
				}
				else
					$this->_errors[] = Tools::displayError('an error occurred while deleting object').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif(Tools::getValue('submitDel'.$this->table) AND isset($_POST[$this->table.'Box']))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
				if (in_array(Configuration::get('PS_LANG_DEFAULT'), $_POST[$this->table.'Box']))
					$this->_errors[] = $this->l('you cannot delete the default language');
				elseif (in_array($cookie->id_lang, $_POST[$this->table.'Box']))
					$this->_errors[] = $this->l('you cannot delete the language currently in use, please change languages before deleting');
				else
				{
				 	foreach ($_POST[$this->table.'Box'] AS $language)
				 		$this->deleteNoPictureImages($language);
					parent::postProcess();
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddlang'))
		{
			/* New language */
			if (intval(Tools::getValue('id_'.$this->table)) == 0)
			{
				if ($this->tabAccess['add'] === '1')
				{
					if (isset($_POST['iso_code']) AND !empty($_POST['iso_code']) AND Validate::isLanguageIsoCode(Tools::getValue('iso_code')) AND Language::getIdByIso($_POST['iso_code']))
						$this->_errors[] = Tools::displayError('this ISO code is already linked to another language');
					if (!empty($_FILES['no-picture']['tmp_name']) AND !empty($_FILES['flag']['tmp_name']) AND Validate::isLanguageIsoCode(Tools::getValue('iso_code')))
					{
						$this->copyNoPictureImage(strtolower(Tools::getValue('iso_code')));
						parent::postProcess();
					}
					else
					{
						$this->validateRules();
						$this->_errors[] = Tools::displayError('the Flag and No-Picture image fields are required');
					}
				}
				else
					$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
			}
			/* Language edition */
			else
			{
				if ($this->tabAccess['edit'] === '1')
				{
					if (!empty($_FILES['no-picture']['tmp_name']) AND Validate::isLanguageIsoCode(Tools::getValue('iso_code')))
						$this->copyNoPictureImage(strtolower(Tools::getValue('iso_code')));
					if (!Validate::isLoadedObject($object = $this->loadObject()))
						die(Tools::displayError());
					if (intval($object->id) == intval(Configuration::get('PS_LANG_DEFAULT')) AND intval($_POST['active']) != intval($object->active))
						$this->_errors[] = Tools::displayError('You cannot change the status of the default language.');
					else
						parent::postProcess();
					$this->validateRules();
				}
				else
					$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
			}
		}
		elseif (isset($_GET['status']) AND isset($_GET['id_lang']))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!Validate::isLoadedObject($object = $this->loadObject()))
					die(Tools::displayError());
				if (intval($object->id) == intval(Configuration::get('PS_LANG_DEFAULT')))
					$this->_errors[] = Tools::displayError('You cannot change the status of the default language.');
				else
					return parent::postProcess();
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		else
			parent::postProcess();
	}
	
	public function displayList()
	{
		global $currentIndex;
		
		$this->displayWarning($this->l('When you delete a language, all related translations in the database will be deleted.'));
		parent::displayList();
		$languages = Language::getLanguages(false);
	}
	
	public function displayListContent($token=NULL)
	{
		global $currentIndex;

		$irow = 0;
		if ($this->_list)
			
			foreach ($this->_list AS $tr)
			{
				$id = $tr[$this->identifier];
				if ($tr['active'])
				{
					$active['title'] = "Enabled";
					$active['img'] = "enabled";
					if (!Language::checkFilesWithIsoCode($tr['iso_code']))
					{
						$active['title'] = "Warning";
						$active['img'] = "warning";
					}
				}
				else
				{
					$active['title'] = "Disabled";
					$active['img'] = "disabled";
				}
				echo '<tr'.($irow++ % 2 ? ' class="alt_row"' : '').' '.((isset($tr['color']) AND $this->colorOnBackground) ? 'style="background-color: '.$tr['color'].'"' : '').'>';
				echo '<td class="center"><input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" /></td>';

				foreach ($this->fieldsDisplay AS $key => $params)
				{
					$tmp = explode('!', $key);
					$key = isset($tmp[1]) ? $tmp[1] : $tmp[0];
					echo '<td class="pointer '.(isset($params['align']) ? $params['align'] : '').'" onclick="document.location = \''.$currentIndex.'&'.$this->identifier.'='.$id.'&update'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'\'">';
					if (isset($params['active']) AND isset($tr[$key]))
						echo '<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&'.$params['active'].'&token='.($token != NULL ? $token : $this->token).'"><img src="../img/admin/'.$active['img'].'.gif" alt="active" title="'.$active['title'].'" /></a>';
					elseif (isset($params['image']))
						echo cacheImage(_PS_IMG_DIR_.$params['image'].'/'.$id.(isset($tr['id_image']) ? '-'.intval($tr['id_image']) : '').'.'.$this->imageType, $this->table.'_mini_'.$id.'.'.$this->imageType, 45, $this->imageType);
					elseif (isset($tr[$key]))
						echo $tr[$key];
					else
						echo '--';
					'</td>';
				}
				if ($this->edit OR $this->delete OR ($this->view AND $this->view != 'noActionColumn'))
				{
					echo '<td class="center">';
					if ($this->edit)
						echo '
						<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&update'.$this->table.'&token='.($token != NULL ? $token : $this->token).'">
						<img src="../img/admin/edit.gif" border="0" alt="'.$this->l('Edit').'" title="'.$this->l('Edit').'" /></a>';
					if ($this->delete)
						echo '
						<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.($token != NULL ? $token : $this->token).'" onclick="return confirm(\''.$this->l('When you delete a language, ALL RELATED TRANSLATIONS IN THE DATABASE WILL BE DELETED, are you sure to delete this langauge ?', __CLASS__, true, false).'\');">
						<img src="../img/admin/delete.gif" border="0" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a>';
					echo '</td>';
				}
				echo '</tr>';
			}
	}
	
	public function displayForm()
	{
		global $currentIndex;
		$obj = $this->loadObject(true);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width3">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/world.gif" />'.$this->l('Languages').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">
					<input type="text" size="8" maxlength="32" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('ISO code:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="2" name="iso_code" value="'.htmlentities($this->getFieldValue($obj, 'iso_code'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p>'.$this->l('2-letter ISO code (e.g., fr, en, de)').'</p>
				</div>
				<label>'.$this->l('Flag:').' </label>
				<div class="margin-form">
					<input type="file" name="flag" /> <sup>*</sup>
					<p>'.$this->l('Upload country flag from your computer').'</p>
				</div>
				<label>'.$this->l('"No-picture" image:').' </label>
				<div class="margin-form">
					<input type="file" name="no-picture" /> <sup>*</sup>
					<p>'.$this->l('Image displayed when "no picture found"').'</p>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.((!$obj->id OR $this->getFieldValue($obj, 'active')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.((!$this->getFieldValue($obj, 'active') AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Allow or disallow this language to be selected by the customer').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
		
		if ($obj->id AND !$obj->checkFiles())
		{
			echo '
			<br /><br />
			<fieldset style="width:572px;"><legend><img src="../img/admin/warning.gif" />'.$this->l('Warning').'</legend>
					<p>'.$this->l('This language is NOT complete and cannot be used in the Front or Back Office because some files are missing.').'</p>
					<br />
					<label>'.$this->l('Translations files:').' </label>
					<div class="margin-form" style="margin-top:4px;">';
					$files = Language::getFilesList($obj->iso_code, _THEME_NAME_, false, false, 'tr', true);
					$this->displayFilesList($files);
			echo '
					</div><br style="clear:both;" />
					<label>'.$this->l('Theme files:').' </label>
					<div class="margin-form" style="margin-top:4px;">';
					$files = Language::getFilesList($obj->iso_code, _THEME_NAME_, false, false, 'theme', true);
					$this->displayFilesList($files);
			echo '
					</div><br style="clear:both;" />
					<label>'.$this->l('Mail files:').' </label>
					<div class="margin-form" style="margin-top:4px;">';
					$files = Language::getFilesList($obj->iso_code, _THEME_NAME_, false, false, 'mail', true);
					$this->displayFilesList($files);
			echo '
					</div>
					<br />
					<div class="small">'.$this->l('Missing files are marked in red').'</div>
			</fieldset>';
		}
	}
	
	public function displayFilesList($files)
	{
		foreach ($files as $key => $file)
		{
			if (!file_exists($key))
				echo '<font color="red">';
			echo $key;
			if (!file_exists($key))
				echo '</font>';
			echo '<br />';
		}
	}
}

?>
