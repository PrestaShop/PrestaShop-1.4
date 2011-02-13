<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class Watermark extends Module
{
	private $_html = '';
	private $_postErrors = array();
	private $xaligns = array('left', 'middle', 'right');
	private $yaligns = array('top', 'middle', 'bottom');
	private $yAlign;
	private $xAlign;
	private $transparency;
	private $imageTypes = array();
	private	$watermarkTypes;
	private $maxImageSize = 100000;

	public function __construct()
	{
		$this->name = 'watermark';
		$this->tab = 'administration';
		$this->version = 0.1;
		
		parent::__construct();

		$config = Configuration::getMultiple(array('WATERMARK_TYPES', 'WATERMARK_Y_ALIGN', 'WATERMARK_X_ALIGN', 'WATERMARK_TRANSPARENCY'));
		if (!isset($config['WATERMARK_TYPES']))
			$config['WATERMARK_TYPES'] = '';
		$tmp = explode(',', $config['WATERMARK_TYPES']);
		foreach (ImageType::getImagesTypes('products') as $type)
		    if (in_array($type['id_image_type'], $tmp))
				$this->imageTypes[] = $type;
		
		$this->yAlign = isset($config['WATERMARK_Y_ALIGN']) ? $config['WATERMARK_Y_ALIGN'] : '';
		$this->xAlign = isset($config['WATERMARK_X_ALIGN']) ? $config['WATERMARK_X_ALIGN'] : '';
		$this->transparency = isset($config['WATERMARK_TRANSPARENCY']) ? $config['WATERMARK_TRANSPARENCY'] : 60;

		$this->displayName = $this->l('Watermark');
		$this->description = $this->l('Protect image by watermark');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
		if (!isset($this->transparency) OR !isset($this->xAlign) OR !isset($this->yAlign))
			$this->warning = $this->l('Watermark image has to be uploaded in order for this module to work correctly');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('watermark'))
			return false;
		Configuration::updateValue('WATERMARK_TRANSPARENCY', 60);
		Configuration::updateValue('WATERMARK_Y_ALIGN', 'bottom');
		Configuration::updateValue('WATERMARK_X_ALIGN', 'right');
		return true;
	}

	public function uninstall()
	{
		return (parent::uninstall()
			AND Configuration::deleteByName('WATERMARK_TYPES')
			AND Configuration::deleteByName('WATERMARK_TRANSPARENCY')
			AND Configuration::deleteByName('WATERMARK_Y_ALIGN')
			AND Configuration::deleteByName('WATERMARK_X_ALIGN'));
	}

	private function _postValidation()
	{
		$yalign = Tools::getValue('yalign');
		$xalign = Tools::getValue('xalign');
		$transparency = (int)(Tools::getValue('transparency'));
		$image_types = Tools::getValue('image_types');
		
		if (empty($transparency))
			$this->_postErrors[] = $this->l('Transparency is required.');
		elseif($transparency < 0 || $transparency > 100)
			$this->_postErrors[] = $this->l('Transparency is not in allowed range.');

		if (empty($yalign))
			$this->_postErrors[] = $this->l('Y-Align is required.');
		elseif(!in_array($yalign, $this->yaligns))
			$this->_postErrors[] = $this->l('Y-Align is not in allowed range.');
		
		if (empty($xalign))
			$this->_postErrors[] = $this->l('X-Align is required.');
		elseif(!in_array($xalign, $this->xaligns))
			$this->_postErrors[] = $this->l('X-Align is not in allowed range.');
		if (empty($image_types))
			$this->_postErrors[] = $this->l('At least one image type is required.');

		if (isset($_FILES['PS_WATERMARK']['tmp_name']) AND !empty($_FILES['PS_WATERMARK']['tmp_name']))
		{
			if (!isPicture($_FILES['PS_WATERMARK'], array('image/gif')))
				$this->_postErrors[] = $this->l('image must be in GIF format');
		}
		
		return !sizeof($this->_postErrors) ? true : false;
	}

	private function _postProcess(){	
		
		Configuration::updateValue('WATERMARK_TYPES', implode(',', Tools::getValue('image_types')));
		Configuration::updateValue('WATERMARK_Y_ALIGN', Tools::getValue('yalign'));
		Configuration::updateValue('WATERMARK_X_ALIGN', Tools::getValue('xalign'));
		Configuration::updateValue('WATERMARK_TRANSPARENCY', Tools::getValue('transparency'));

		//submited watermark
		if (isset($_FILES['PS_WATERMARK']) AND !empty($_FILES['PS_WATERMARK']['tmp_name']))
		{
			/* Check watermark validity */
			if ($error = checkImage($_FILES['PS_WATERMARK'], $this->maxImageSize))
				$this->_errors[] = $error;
			/* Copy new watermark */
			elseif(!copy($_FILES['PS_WATERMARK']['tmp_name'], dirname(__FILE__).'/watermark.gif'))
				$this->_errors[] = Tools::displayError('an error occurred while uploading watermark: '.$_FILES['PS_WATERMARK']['tmp_name'].' to '.$dest);
		}
		
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
	}

	private function _displayForm()
	{
	    $imageTypes = ImageType::getImagesTypes('products');
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
			<fieldset><legend><img src="../modules/'.$this->name.'/logo.gif" />'.$this->l('Watermark details').'</legend>
				<p>'.$this->l('Once you\'ve set up the module, you have to regenerate the images using the tool in Preferences > Images. However, the watermark will be added automatically to the new images.').'</p>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr>
						<td />
						<td>'.(file_exists(dirname(__FILE__).'/watermark.gif') ? '<img src="../modules/'.$this->name.'/watermark.gif?t='.time().'" />' : $this->l('No watermark uploaded yet')).'</td>
					</tr>
					<tr>
						<td>'.$this->l('Watermark file').'</td>
						<td>
							<input type="file" name="PS_WATERMARK" />
							<p style="color:#7F7F7F; font-size:0.85em; margin:0; padding:0;">'.$this->l('Must be in GIF format').'</p>
						</td>
					</tr>
					<tr>
						<td width="270" style="height: 35px;">'.$this->l('Watermark transparency (0-100)').'</td>
					    <td><input type="text" name="transparency" value="'.Tools::getValue('transparency', Configuration::get('WATERMARK_TRANSPARENCY')).'" style="width: 30px;" /></td>
					</tr>
					<tr><td width="270" style="height: 35px;">'.$this->l('Watermark X align').'</td>
					    <td>
						<select id="xalign" name = "xalign">';
					    foreach($this->xaligns as $align)
						    $this->_html .= '<option value="'.$align.'"'.(Tools::getValue('xalign', Configuration::get('WATERMARK_X_ALIGN')) == $align ? ' selected="selected"' : '' ).'>'.$this->l($align).'</option>';
					    $this->_html .= '</select>
					    </td>
					</tr>
					<tr><td width="270" style="height: 35px;">'.$this->l('Watermark Y align').'</td>
					    <td>
						<select id="yalign" name = "yalign">';
					    foreach($this->yaligns as $align)
						    $this->_html .= '<option value="'.$align.'"'.(Tools::getValue('yalign', Configuration::get('WATERMARK_Y_ALIGN')) == $align ? ' selected="selected"' : '' ).'>'.$this->l($align).'</option>';
					    $this->_html .= '</select>
					    </td>
					</tr>
					<tr><td width="270" style="height: 35px;">'.$this->l('Choose image types for watermark protection').'</td><td>';
					$selected_types = explode(',', Configuration::get('WATERMARK_TYPES'));
					foreach(ImageType::getImagesTypes('products') as $type)
					{
					    $this->_html .= '<label style="float:none; ">
						<input type="checkbox" value="'.$type['id_image_type'].'" name="image_types[]"'.
						(in_array($type['id_image_type'], $selected_types) ? ' checked="checked"' : '').' />&nbsp;<span style="font-weight:bold;">'.$type['name'].'</span>
					    ('.$type['width'].' x '.$type['height'].')</label><br />';
					}
					$this->_html .= '</td></tr>
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayForm();

		return $this->_html;
	}
	
	//we assume here only jpg files
	public function hookwatermark($params)
	{
		global $smarty;
		$file = _PS_PROD_IMG_DIR_.$params['id_product'].'-'.$params['id_image'].'-watermark.jpg';
		
		//first make a watermark image
		$return = $this->watermarkByImage(_PS_PROD_IMG_DIR_.$params['id_product'].'-'.$params['id_image'].'.jpg',  dirname(__FILE__).'/watermark.gif', $file, 23, 0, 0, 'right');

		//go through file formats defined for watermark and resize them
		foreach($this->imageTypes as $imageType)
		{
		    $newFile = _PS_PROD_IMG_DIR_.$params['id_product'].'-'.$params['id_image'].'-'.stripslashes($imageType['name']).'.jpg';
		    if (!imageResize($file, $newFile, (int)($imageType['width']), (int)($imageType['height'])))
				$return = false;    
		}
		return $return;
	}

	private function watermarkByImage($imagepath, $watermarkpath, $outputpath)
	{	
		$Xoffset = $Yoffset = $xpos = $ypos = 0;
		if (!$image = imagecreatefromjpeg($imagepath))
			return false;
		if (!$imagew = imagecreatefromgif($watermarkpath))
			die ($this->l('the watermark image is not a real gif, please CONVERT and not rename it'));
		list($watermarkWidth, $watermarkHeight) = getimagesize($watermarkpath); 
		list($imageWidth, $imageHeight) = getimagesize($imagepath); 
		if ($this->xAlign == "middle") { $xpos = $imageWidth/2 - $watermarkWidth/2 + $Xoffset; } 
		if ($this->xAlign == "left") { $xpos = 0 + $Xoffset; } 
		if ($this->xAlign == "right") { $xpos = $imageWidth - $watermarkWidth - $Xoffset; } 
		if ($this->yAlign == "middle") { $ypos = $imageHeight/2 - $watermarkHeight/2 + $Yoffset; } 
		if ($this->yAlign == "top") { $ypos = 0 + $Yoffset; } 
		if ($this->yAlign == "bottom") { $ypos = $imageHeight - $watermarkHeight - $Yoffset; } 
		if (!imagecopymerge($image, $imagew, $xpos, $ypos, 0, 0, $watermarkWidth, $watermarkHeight, $this->transparency))
			return false;
		return imagejpeg($image, $outputpath, 100); 
	} 
}

