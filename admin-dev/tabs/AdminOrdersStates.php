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

class AdminOrdersStates extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'order_state';
	 	$this->className = 'OrderState';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		$this->colorOnBackground = true;
 
		$this->fieldImageSettings = array('name' => 'icon', 'dir' => 'os');
		$this->imageType = 'gif';

		$this->fieldsDisplay = array(
		'id_order_state' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 130),
		'logo' => array('title' => $this->l('Icon'), 'align' => 'center', 'image' => 'os', 'orderby' => false, 'search' => false),
		'send_email' => array('title' => $this->l('Send e-mail to customer'), 'align' => 'center', 'icon' => array('1' => 'enabled.gif', '0' => 'disabled.gif'), 'type' => 'bool', 'orderby' => false),
		'invoice' => array('title' => $this->l('Invoice'), 'align' => 'center', 'icon' => array('1' => 'enabled.gif', '0' => 'disabled.gif'), 'type' => 'bool', 'orderby' => false),
		'template' => array('title' => $this->l('E-mail template'), 'width' => 100));
		
		parent::__construct();
	}
	
	public function postProcess()
	{
		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			$_POST['invoice'] = Tools::getValue('invoice');
			$_POST['logable'] = Tools::getValue('logable');
			$_POST['send_email'] = Tools::getValue('send_email');
			$_POST['hidden'] = Tools::getValue('hidden');
			if (!$_POST['send_email'])
			{
				$languages = Language::getLanguages();
				foreach ($languages AS $language)
					$_POST['template_'.$language['id_lang']] = '';
			}
			parent::postProcess();
		}
		elseif (isset($_GET['delete'.$this->table]))
		{
		 	$orderState = new OrderState(intval($_GET['id_order_state']));
		 	if (!$orderState->isRemovable())
		 		$this->_errors[] = $this->l('For security reasons, you cannot delete default order statuses.');
		 	else
		 		parent::postProcess();
		}
		elseif (isset($_POST['submitDelorder_state']))
		{
		 	foreach ($_POST[$this->table.'Box'] AS $selection)
		 	{
			 	$orderState = new OrderState(intval($selection));
			 	if (!$orderState->isRemovable())
			 	{
			 		$this->_errors[] = $this->l('For security reasons, you cannot delete default order statuses.');
			 		break;
			 	}
			}
			if (empty($this->_errors))
				parent::postProcess();
		}
		else
			parent::postProcess();
	}
	
	private function getTemplates($iso_code)
	{
		$array = array();
		if (!file_exists(PS_ADMIN_DIR.'/../mails/'.$iso_code))
			return false;
		$templates = scandir(PS_ADMIN_DIR.'/../mails/'.$iso_code);
		foreach ($templates AS $template)
			if (!strncmp(strrev($template), 'lmth.', 5))
				$array[] = substr($template, 0, -5);
		return $array;
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
						<input size="40" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" style="width: 150px;" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters: numbers and').' !<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
						</div>';							
				$this->displayFlags($languages, $defaultLanguage, 'name¤template', 'name');

		echo '		<p style="clear: both">'.$this->l('Order status (e.g., \'Pending\')').'</p>
				</div>
				<label>'.$this->l('Icon:').' </label>
				<div class="margin-form">
					<input type="file" name="icon" />
					<p>'.$this->l('Upload an icon from your computer').' (.gif)</p>
				</div>
				<label>'.$this->l('Color:').' </label>
				<div class="margin-form">
					<input type="text" name="color" value="'.htmlentities($this->getFieldValue($obj, 'color'), ENT_COMPAT, 'UTF-8').'" />
					<p>'.$this->l('Status will be highlighted in this color. HTML colors only (e.g.,').' "lightblue", "#CC6600")</p>
				</div>
				<div class="margin-form">
					<p>
						<input type="checkbox" name="logable"'.(($this->getFieldValue($obj, 'logable') == 1) ? ' checked="checked"' : '').' id="logable_on" value="1" />
						<label class="t" for="logable_on"> '.$this->l('Consider the associated order as validated').'</label>
					</p>
				</div>
				<div class="margin-form">
					<p>
						<input type="checkbox" name="invoice"'.(($this->getFieldValue($obj, 'invoice') == 1) ? ' checked="checked"' : '').' id="invoice_on" value="1" />
						<label class="t" for="invoice_on"> '.$this->l('Allow customer to download and view PDF version of invoice').'</label>
					</p>
				</div>
				<div class="margin-form">
					<p>
						<input type="checkbox" name="hidden"'.(($this->getFieldValue($obj, 'hidden') == 1) ? ' checked="checked"' : '').' id="hidden_on" value="1" />
						<label class="t" for="hidden_on"> '.$this->l('Hide this state in order for customer').'</label>
					</p>
				</div>
				<div class="margin-form">
					<p>
						<input type="checkbox" id="send_email" name="send_email" onclick="javascript:openCloseLayer(\'tpl\');"'.
					(($this->getFieldValue($obj, 'send_email')) ? 'checked="checked"' : '').' value="1" />
						<label class="t" for="send_email"> '.$this->l('Send e-mail to customer when order is changed to this status').'</label>
					</p>
				</div>				
				<div id="tpl" style="display: '.($this->getFieldValue($obj, 'send_email') ? 'block' : 'none').';">
					<label>'.$this->l('').'Template:</label>
					<div class="margin-form">';
			foreach ($languages as $language)
			{
				$templates = $this->getTemplates($language['iso_code']);
				echo '	<div id="template_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">';
				if (!$templates)
					echo '<strong>'.$this->l('Please first copy your e-mail templates in the directory').' mails/'.$language['iso_code'].'.</strong>';
				else
				{
					echo '		<select	name="template_'.$language['id_lang'].'" id="template_select_'.$language['id_lang'].'">';
					foreach ($templates AS $template)
						echo '		<option value="'.$template.'" '.(($this->getFieldValue($obj, 'template', intval($language['id_lang'])) == $template) ? 'selected="selected"' : '').'>'.$template.'</option>';
					echo '		</select>';
				}
				echo '			<span class="hint" name="help_box">'.$this->l('Only letters, number and -_ are allowed').'<span class="hint-pointer">&nbsp;</span></span>
								<img onclick="viewTemplates(\'template_select_'.$language['id_lang'].'\', '.$language['id_lang'].', \'../mails/'.$language['iso_code'].'/\', \'.html\');" src="../img/t/AdminFeatures.gif" class="pointer" alt="'.$this->l('Preview').'" title="'.$this->l('Preview').'" />
						</div>';
			}
			$this->displayFlags($languages, $defaultLanguage, 'name¤template', 'template');
			echo '<p style="clear: both">'.$this->l('E-mail template for both .html and .txt').'</p>
					</div>
				</div>
				<script type="text/javascript">if (getE(\'send_email\').checked) getE(\'tpl\').style.display = \'block\'; else getE(\'tpl\').style.display = \'none\';</script>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>