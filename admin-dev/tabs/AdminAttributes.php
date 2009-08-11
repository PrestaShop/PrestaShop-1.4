<?php

/**
  * Attributes tab for admin panel, AdminAttributes.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminAttributes extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'attribute';
	 	$this->className = 'Attribute';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;

		$this->fieldImageSettings = array('name' => 'texture', 'dir' => 'co');

		parent::__construct();
	}

	/**
	 * Display form
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function displayForm($token = NULL)
	{
		global $currentIndex;

		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$obj = $this->loadObject(true);
		$color = ($obj->color ? $obj->color : 0);
		$attributes_groups = AttributeGroup::getAttributesGroups($defaultLanguage);
		
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
			var attributesGroups = new Array();
		';
		foreach ($attributes_groups AS $attribute_group)
			echo 'attributesGroups['.$attribute_group['id_attribute_group'].'] = '.$attribute_group['is_color_group'].';'."\n";
		echo '
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token ? $token : $this->token).'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_attribute" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/asterisk.gif" />'.$this->l('Attribute').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlspecialchars($this->getFieldValue($obj, 'name', intval($language['id_lang']))).'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
		echo '
					<div style="clear: both;"></div>
				</div>
				<label>'.$this->l('Group:').' </label>
				<div class="margin-form">
					<select name="id_attribute_group" id="id_attribute_group" onchange="showAttributeColorGroup(\'id_attribute_group\', \'colorAttributeProperties\')">';
		
		foreach ($attributes_groups AS $attribute_group)
			echo '<option value="'.$attribute_group['id_attribute_group'].'"'.($this->getFieldValue($obj, 'id_attribute_group') == $attribute_group['id_attribute_group'] ? ' selected="selected"' : '').'>'.$attribute_group['name'].'</option>';
		echo '
					</select><sup> *</sup>
				</div>
				<div id="colorAttributeProperties" style="'.((Validate::isLoadedObject($obj) AND $obj->isColorAttribute()) ? 'display: block;' : 'display: none;').'">
					<label>'.$this->l('Color:').'</label>
					<div class="margin-form">
						<input type="text" size="33" name="color" value="'.(Tools::getValue('color', $color) ? htmlentities(Tools::getValue('color', $color)) : '#000000').'" /> <sup>*</sup>
						<p class="clear">'.$this->l('HTML colors only (e.g.,').' "lightblue", "#CC6600")</p>
					</div>
					<label>'.$this->l('Texture:').' </label>
					<div class="margin-form">
						<input type="file" name="texture" />
						<p>'.$this->l('Upload color texture from your computer').'<br />'.$this->l('This will override the HTML color!').'</p>
					</div>
					<label>'.$this->l('Current texture:').' </label>
					<div class="margin-form">
						<p>'.(file_exists(_PS_IMG_DIR_.$this->fieldImageSettings['dir'].'/'.$obj->id.'.jpg')
							? '<img src="../img/'.$this->fieldImageSettings['dir'].'/'.$obj->id.'.jpg" alt="" title="" /> <a href="'.$_SERVER['REQUEST_URI'].'&deleteImage=1"><img src="../img/admin/delete.gif" alt="'.$this->l('delete').'" title="" /></a>'
							: $this->l('None')
						).'</p>
					</div>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAddattribute" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	/**
	 * Manage page processing
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function postProcess($token = NULL)
	{
		global $currentIndex;
		if (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
			 	if (isset($_POST[$this->table.$_POST['groupid'].'Box']))
			 	{
					$object = new $this->className();
					if ($object->deleteSelection($_POST[$this->table.$_POST['groupid'].'Box']))
						Tools::redirectAdmin($currentIndex.'&conf=2'.'&token='.($token ? $token : $this->token));
					$this->_errors[] = Tools::displayError('an error occurred while deleting selection');
				}
				else
					$this->_errors[] = Tools::displayError('you must select at least one element to delete');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		else
			parent::postProcess();
	}
}

?>