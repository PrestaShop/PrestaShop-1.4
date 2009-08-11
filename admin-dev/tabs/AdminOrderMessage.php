<?php

/**
  * Taxes tab for admin panel, AdminTaxes.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminOrderMessage extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'order_message';
	 	$this->className = 'OrderMessage';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
		'id_order_message' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25), 
		'name' => array('title' => $this->l('Name'), 'width' => 140),
		'message' => array('title' => $this->l('Message'), 'width' => 140, 'maxlength' => 300));
	
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
		<form action="'.$currentIndex.'&token='.$this->token.'&submitAdd'.$this->table.'=1" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/email.gif" />'.$this->l('Order messages').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" size="53" name="name_'.$language['id_lang'].'" value="'.$this->getFieldValue($obj, 'name', intval($language['id_lang'])).'" /><sup> *</sup>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name¤message', 'name');
				echo '</div>
				<div class="clear"></div><br />
				<label>'.$this->l('Message:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="message_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<textarea rows="15" cols="50" name="message_'.$language['id_lang'].'">'.$this->getFieldValue($obj, 'message', intval($language['id_lang'])).'</textarea><sup> *</sup>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name¤message', 'message');
				echo '</div>
				<div class="clear"></div><br />
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required fields').'</div>
			</fieldset>
		</form>';
	}
}

?>