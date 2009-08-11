<?php

/**
  * Tabs tab for admin panel, AdminTabs.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminTabs extends AdminTab
{
	public function __construct()
	{
		global $cookie;
		
	 	$this->table = 'tab';
	 	$this->className = 'Tab';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		$this->_select = '(SELECT stl.`name` FROM `'._DB_PREFIX_.'tab_lang` stl WHERE stl.`id_tab` = a.`id_parent` AND stl.`id_lang` = '.intval($cookie->id_lang).' LIMIT 1) AS parent';
		
		$this->fieldImageSettings = array('name' => 'icon', 'dir' => 't');
		$this->imageType = 'gif';
		
		$tabs = array(0 => $this->l('Home'));
		foreach (Tab::getTabs(intval($cookie->id_lang), 0) AS $tab)
			$tabs[$tab['id_tab']] = $tab['name'];
		$this->fieldsDisplay = array(
		'id_tab' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 200),
		'logo' => array('title' => $this->l('Icon'), 'align' => 'center', 'image' => 't', 'image_id' => 'class_name', 'orderby' => false, 'search' => false),
		'parent' => array('title' => $this->l('Parent'), 'width' => 200, 'type' => 'select', 'select' => $tabs, 'filter_key' => 'a!id_parent'),
		'module' => array('title' => $this->l('Module')));
	
		parent::__construct();
	}

	public function postProcess()
	{
		if (($id_tab = intval(Tools::getValue('id_tab'))) AND ($direction = Tools::getValue('move')) AND Validate::isLoadedObject($tab = new Tab($id_tab)))
		{
			global $currentIndex;
			if ($tab->move($direction))
				Tools::redirectAdmin($currentIndex.'&token='.$this->token);
		}
		else
		{
			if (!Tools::getValue('position'))
				$_POST['position'] = Tab::getNbTabs(Tools::getValue('id_parent'));
			parent::postProcess();
		}
	}

	private function _posTabs($name, $arrayTabs)
	{
		global $currentIndex;
		
		if (sizeof($arrayTabs) > 1)
		{
			echo '
			<table class="table" cellspacing="0" cellpadding="0" style="margin-bottom: 5px;">
				<tr>';
			for ($i = 0; $i < sizeof($arrayTabs); $i++)
			{
				$tab = $arrayTabs[$i];
				echo '<th style="text-align:center;">'.stripslashes($tab['name']).'<br />';
				if ($i)
					echo '<a href="'.$currentIndex.'&id_tab='.$tab['id_tab'].'&move=l&token='.$this->token.'"><img src="../img/admin/previous.gif" /></a>&nbsp;';
				if ($i < sizeof($arrayTabs) - 1)
					echo '<a href="'.$currentIndex.'&id_tab='.$tab['id_tab'].'&move=r&token='.$this->token.'"><img src="../img/admin/next.gif" /></a></th>';
			}
			echo '
				</tr>
			</table>';
		}
	}
	
	public function displayList()
	{
		global $cookie, $currentIndex;
		
		parent::displayList();
		
		$tabs = Tab::getTabs(intval($cookie->id_lang), 0);
		echo '<br /><h2>'.$this->l('Positions').'</h2>
		<h3>'.$this->l('Level').' 1</h3>';
		$this->_posTabs($this->l('Main'), $tabs);
		echo '<h3>'.$this->l('Level').' 2</h3>';
		foreach ($tabs AS $t)
			$this->_posTabs(stripslashes($t['name']), Tab::getTabs(intval($cookie->id_lang), $t['id_tab']));
	}
	
	public function displayForm()
	{
		global $currentIndex, $cookie;

		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
		'.($obj->position ? '<input type="hidden" name="position" value="'.$obj->position.'" />' : '').'
			<fieldset><legend><img src="../img/admin/tab.gif" />'.$this->l('Tabs').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
		echo '
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Class:').' </label>
				<div class="margin-form">
					<input type="text" name="class_name" value="'.htmlentities($this->getFieldValue($obj, 'class_name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Module:').' </label>
				<div class="margin-form">
					<input type="text" name="module" value="'.htmlentities($this->getFieldValue($obj, 'module'), ENT_COMPAT, 'UTF-8').'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Icon:').'</label>
				<div class="margin-form">
					'.($obj->id ? '<img src="../img/t/'.$obj->class_name.'.gif" />&nbsp;/img/t/'.$obj->class_name.'.gif' : '').'
					<p><input type="file" name="icon" /></p>
					<p>'.$this->l('Upload logo from your computer').' (.gif, .jpg, .jpeg '.$this->l('or').' .png)</p>
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Parent:').'</label>
				<div class="margin-form">
					<select name="id_parent">
						<option value="-1" '.(($this->getFieldValue($obj, 'id_parent') == -1) ? 'selected="selected"' : '').'>'.$this->l('None').'</option>
						<option value="0" '.(($this->getFieldValue($obj, 'id_parent') == 0) ? 'selected="selected"' : '').'>'.$this->l('Home').'</option>';
		foreach (Tab::getTabs(intval($cookie->id_lang), 0) AS $tab)
			echo '		<option value="'.$tab['id_tab'].'" '.($tab['id_tab'] == $this->getFieldValue($obj, 'id_parent') ? 'selected="selected"' : '').'>'.$tab['name'].'</option>';
		echo '		</select>
				</div>
				<div class="clear">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	public function afterImageUpload()
	{
		$obj = $this->loadObject(true);
		@rename(_PS_IMG_DIR_.'t/'.$obj->id.'.gif', _PS_IMG_DIR_.'t/'.$obj->class_name.'.gif');
	}
}
