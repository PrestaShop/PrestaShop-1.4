<?php

/**
  * Contacts tab for admin panel, AdminContacts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
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
	
		$this->optionTitle = $this->l('Contact options');
		$this->_fieldsOptions = array(
			'PS_CUSTOMER_SERVICE_FILE_UPLOAD' => array('title' => $this->l('Allow file upload'), 'desc' => $this->l('Allow customers to upload file using contact page'), 'cast' => 'intval', 'type' => 'select', 'identifier' => 'value', 'list' => array(
				'0' => array('value' => 0, 'name' => $this->l('No')), 
				'1' => array('value' => 1, 'name' => $this->l('Yes')) 
			)),
			'PS_CUSTOMER_SERVICE_SIGNATURE' => array('title' => $this->l('Pre-defined message'), 'desc' => $this->l('Please fill the message that appears by default when you answer a thread on the customer service page'), 'cast' => 'pSQL', 'type' => 'textareaLang', 'identifier' => 'value',
			'cols' => 40, 'rows' => 8
		));
		
		parent::__construct();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();
		
		$obj = $this->loadObject(true);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/contact.gif" />'.$this->l('Contacts').'</legend>
				<label>'.$this->l('Title:').' </label>
				<div class="margin-form">';
				foreach ($this->_languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						</div>';
				$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'name¤description', 'name');
		echo '		<p style="clear: both">'.$this->l('Contact name, e.g., Technical Support').'</p>
				</div>
				<label>'.$this->l('E-mail address').'</label>
				<div class="margin-form">
					<input type="text" size="33" name="email" value="'.htmlentities($this->getFieldValue($obj, 'email'), ENT_COMPAT, 'UTF-8').'" />
					<p style="clear: both">'.$this->l('E-mails will be sent to this address').'</p>
				</div>
				<label>'.$this->l('Save in Customer Service?').'</label>
				<div class="margin-form">
					<input type="radio" name="customer_service" id="customer_service_on" value="1" '.($this->getFieldValue($obj, 'customer_service') ? 'checked="checked" ' : '').'/>
					<label class="t" for="customer_service_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="customer_service" id="customer_service_off" value="0" '.(!$this->getFieldValue($obj, 'customer_service') ? 'checked="checked" ' : '').'/>
					<label class="t" for="customer_service_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('The messages will be saved in the Customer Service tab').'</p>
				</div><div class="clear">&nbsp;</div>
				<label>'.$this->l('Description').'</label>
				<div class="margin-form">';
				foreach ($this->_languages as $language)
					echo '
					<div id="description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="description_'.$language['id_lang'].'" cols="36" rows="5">'.htmlentities($this->getFieldValue($obj, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
				$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'name¤description', 'description');
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
