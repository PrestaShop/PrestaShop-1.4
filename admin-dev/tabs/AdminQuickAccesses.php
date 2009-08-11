<?php

/**
  * QuickAccesses tab for admin panel, AdminQuickAccesses.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminQuickAccesses extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'quick_access';
	 	$this->className = 'QuickAccess';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
		'id_quick_access' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 200),
		'link' => array('title' => $this->l('Link'), 'width' => 300),
		'new_window' => array('title' => $this->l('New window'), 'align' => 'center', 'type' => 'bool', 'activeVisu' => 'new_window'));
	
		parent::__construct();
	}
	
	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$new_window = $this->getFieldValue($obj, 'new_window');
		
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width4">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/quick.gif" />'.$this->l('Quick Access menu').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';							
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
		echo '
				<div class="clear"></div>
				</div>
				<label>'.$this->l('URL:').' </label>
				<div class="margin-form">
					<input type="text" size="60" maxlength="128" name="link" value="'.htmlentities($this->getFieldValue($obj, 'link'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Open in new window:').' </label>
				<div class="margin-form">
					<input type="radio" name="new_window" id="new_window_on" value="1" '.($new_window ? 'checked="checked" ' : '').'/> 
					<label class="t" for="new_window_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Yes').'" /></label>
					<input type="radio" name="new_window" id="new_window_off" value="0" '.(!$new_window ? 'checked="checked" ' : '').'/> 
					<label class="t" for="new_window_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('No').'" /></label>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('required field').'</div>
			</fieldset>
		</form>';
	}
}

?>
