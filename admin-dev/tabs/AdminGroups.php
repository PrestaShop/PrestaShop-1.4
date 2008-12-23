<?php

/**
  * Customers tab for admin panel, AdminContacts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminGroups extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'group';
	 	$this->className = 'Group';
		$this->lang = true;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;

 		$this->fieldsDisplay = array(
		'id_group' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'b!name' => array('title' => $this->l('Name'), 'width' => 80, 'filter_key' => 'b!name'),
		'reduction' => array('title' => $this->l('Reduction'), 'width' => 50, 'align' => 'right'),
		'date_add' => array('title' => $this->l('Creation date'), 'width' => 60, 'type' => 'date'));

		parent::__construct();
	}

	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width3">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/tab-groups.gif" />'.$this->l('Group').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
					</div>';							
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
		echo '
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Reduction:').' </label>
				<div class="margin-form">
					<input type="text" size="5" name="reduction" value="'.htmlentities($this->getFieldValue($obj, 'reduction'), ENT_COMPAT, 'UTF-8').'" /> %
					<p>'.$this->l('Will automatically apply this value as a reduction on ALL shop\'s products for this group\'s members').'</p>
				</div>
				<div class="clear">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>