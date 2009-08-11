<?php

/**
  * Attributes tab for admin panel, AdminAttributesGroups.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/tabs/AdminAttributes.php');

class AdminAttributesGroups extends AdminTab
{
	/** @var object AdminAttributes() instance */
	private $adminAttributes;

	public function __construct()
	{
		$this->adminAttributes = new AdminAttributes();
	 	$this->table = 'attribute_group';
	 	$this->className = 'AttributeGroup';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;

		$this->fieldsDisplay = array(
			'name' => array('title' => $this->l('Name'), 'width' => 140),
			'attribute' => array('title' => $this->l('Attributes'), 'width' => 240, 'orderby' => false, 'search' => false));

		parent::__construct();
	}

	public function display()
	{
		global $currentIndex;

		if ((isset($_POST['submitAddattribute']) AND sizeof($this->adminAttributes->_errors))
			OR isset($_GET['updateattribute']) OR isset($_GET['addattribute']))
		{
			$this->adminAttributes->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		else
			parent::display();
	}

	public function postProcess()
	{
	 	global	$cookie, $currentIndex;
		
		$this->adminAttributes->tabAccess = Profile::getProfileAccess($cookie->profile, $this->id);
		$this->adminAttributes->postProcess($this->token);

		if(Tools::getValue('submitDel'.$this->table))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
			 	if (isset($_POST[$this->table.'Box']))
			 	{
					$object = new $this->className();
					if ($object->deleteSelection($_POST[$this->table.'Box']))
						Tools::redirectAdmin($currentIndex.'&conf=2'.'&token='.$this->token);
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

	public function displayErrors()
	{
		$this->adminAttributes->displayErrors();
		parent::displayErrors();
	}

	/* Report to AdminTab::displayList() for more details */
	public function displayList()
	{
		global $currentIndex, $cookie;

		echo '<br /><a href="'.$currentIndex.'&add'.$this->table.'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> <b>'.$this->l('Add attributes group').'</b></a><br />
		<a href="'.$currentIndex.'&addattribute&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add attribute').'</a><br /><br />
		'.$this->l('Click on the group name to view its attributes. Click again to hide them.').'<br /><br />';
		if ($this->_list === false)
			Tools::displayError('no elements found');

		$this->displayListHeader();
		echo '<input type="hidden" name="groupid" value="0">';

		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.sizeof($this->_list).'">'.$this->l('No elements found').'</td></tr>';

		$irow = 0;
		foreach ($this->_list AS $tr)
		{
			$id = intval($tr['id_'.$this->table]);
		 	echo '
			<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>
				<td style="vertical-align: top; padding: 4px 0 4px 0" class="center"><input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" /></td>
				<td style="width: 140px; vertical-align: top; padding: 4px 0 4px 0; cursor: pointer" onclick="openCloseLayer(\'attributes_'.$id.'\');">'.$tr['name'].'</td>
				<td style="vertical-align: top; padding: 4px 0 4px 0; width: 340px">
					<div id="attributes_'.$id.'" style="display: none">
					<table class="table" cellpadding="0" cellspacing="0">
						<tr>
							<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'attribute'.$id.'Box[]\', this.checked)" /></th>
							<th width="100%">'.$this->l('Attribute').'</th>
							<th>'.$this->l('Actions').'</th>
						</tr>';
			$attributes = AttributeGroup::getAttributes(intval($cookie->id_lang), $id);
			foreach ($attributes AS $attribute)
			{
				echo '
						<tr>
							<td class="center"><input type="checkbox" name="attribute'.$id.'Box[]" value="'.$attribute['id_attribute'].'" class="noborder" /></td>
							<td>
								'.($tr['is_color_group'] ? '<div style="float: left; width: 18px; height: 12px; border: 1px solid #996633; background-color: '.$attribute['color'].'; margin-right: 4px;"></div>' : '')
								.$attribute['name'].'
							</td>
							<td class="center">
								<a href="'.$currentIndex.'&id_attribute='.$attribute['id_attribute'].'&updateattribute&token='.$this->token.'">
								<img src="../img/admin/edit.gif" border="0" alt="'.$this->l('Edit').'" title="'.$this->l('Edit').'" /></a>&nbsp;
								<a href="'.$currentIndex.'&id_attribute='.$attribute['id_attribute'].'&deleteattribute&token='.$this->token.'"
								onclick="return confirm(\''.$this->l('Delete attribute', __CLASS__, true, false).' #'.$attribute['id_attribute'].'?\');">
								<img src="../img/admin/delete.gif" border="0" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a>
							</td>
						</tr>';
			}
			echo '
					</table>
					<p><input type="Submit" class="button" name="submitDelattribute" value="'.$this->l('Delete selection').'"
					onclick="changeFormParam(this.form, \''.$currentIndex.'\', '.$id.'); return confirm(\''.$this->l('Delete selected items?', __CLASS__, true, false).'\');" /></p>
					</div>
					</td>';

			echo '
				<td style="vertical-align: top; padding: 4px 0 4px 0" class="center">
					<a href="'.$currentIndex.'&id_'.$this->table.'='.$id.'&update'.$this->table.'&token='.$this->token.'">
					<img src="../img/admin/edit.gif" border="0" alt="'.$this->l('Edit').'" title="'.$this->l('Edit').'" /></a>&nbsp;
					<a href="'.$currentIndex.'&id_'.$this->table.'='.$id.'&delete'.$this->table.'&token='.$this->token.'" onclick="return confirm(\''.$this->l('Delete item', __CLASS__, true, false).' #'.$id.'?\');">
					<img src="../img/admin/delete.gif" border="0" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a>
				</td>
			</tr>';
		}

		$this->displayListFooter();
	}

	public function displayForm()
	{
		global $currentIndex;

		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$obj = $this->loadObject(true);

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/asterisk.gif" />'.$this->l('Attributes group').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlspecialchars($this->getFieldValue($obj, 'name', intval($language['id_lang']))).'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, 'name¤public_name', 'name');
		echo '
					<div style="clear: both;"></div>
				</div>
				<label>'.$this->l('Public name:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="public_name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="public_name_'.$language['id_lang'].'" value="'.htmlspecialchars($this->getFieldValue($obj, 'public_name', intval($language['id_lang']))).'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both">'.$this->l('Term or phrase displayed to the customer').'</p>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, 'name¤public_name', 'public_name');
		echo '
					<div style="clear: both;"></div>
				</div>
				<label>'.$this->l('Color group:').' </label>
				<div class="margin-form">
					<input type="radio" name="is_color_group" id="is_color_group_on" value="1" '.($this->getFieldValue($obj, 'is_color_group') ? 'checked="checked" ' : '').'/>
					<label class="t" for="is_color_group_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Yes').'" /></label>
					<input type="radio" name="is_color_group" id="is_color_group_off" value="0" '.(!$this->getFieldValue($obj, 'is_color_group') ? 'checked="checked" ' : '').'/>
					<label class="t" for="is_color_group_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('No').'" /></label>
					<p>'.$this->l('This is a color group').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>