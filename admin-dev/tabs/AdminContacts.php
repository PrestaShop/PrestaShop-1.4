<?php

/**
  * Contacts tab for admin panel, AdminContacts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminContacts extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'contact';
	 	$this->className = 'Contact';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
 				
		$this->fieldsDisplay = array(
		'id_contact' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Title'), 'width' => 130),
		'email' => array('title' => $this->l('E-mail address'), 'width' => 130),
		'description' => array('title' => $this->l('Description'), 'width' => 150));
	
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
			<fieldset><legend><img src="../img/admin/contact.gif" />'.$this->l('Contacts').'</legend>
				<label>'.$this->l('Title:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name¤description', 'name');
		echo '		<p style="clear: both">'.$this->l('Contact name, e.g., Technical Support').'</p>
				</div>
				<label>'.$this->l('E-mail address:').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="email" value="'.htmlentities($this->getFieldValue($obj, 'email'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p style="clear: both">'.$this->l('E-mails will be sent to this address').'</p>
				</div>
				<label>'.$this->l('Description:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="description_'.$language['id_lang'].'" cols="36" rows="5">'.htmlentities($this->getFieldValue($obj, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name¤description', 'description');
				echo '
					<p style="clear: both">'.$this->l('Additional information about this contact').'</p>
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