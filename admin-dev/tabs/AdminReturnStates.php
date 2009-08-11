<?php

/**
  * Order statues tab for admin panel, AdminOrdersStates.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminReturnStates extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'order_return_state';
	 	$this->className = 'OrderReturnState';
	 	$this->lang = true;
	 	$this->edit = true;
		$this->noAdd = true;
	 	$this->delete = false;
		$this->colorOnBackground = true;

		$this->fieldsDisplay = array(
		'id_order_return_state' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 130)
		);
		
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
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/time.gif" />'.$this->l('Order statues').'</legend>
				<label>'.$this->l('Status name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="40" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters: numbers and').' !<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
						</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
		echo '		<p style="clear: both">'.$this->l('Order return status name').'</p>
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