<?php

class BlockManufacturer extends Module
{
    function __construct()
    {
        $this->name = 'blockmanufacturer';
        $this->tab = 'Blocks';
        $this->version = 1.0;

        parent::__construct();

		$this->displayName = $this->l('Manufacturers block');
        $this->description = $this->l('Displays a block of manufacturers/brands');
    }

    function install()
    {
        parent::install();
        $this->registerHook('leftColumn');
		Configuration::updateValue('MANUFACTURER_DISPLAY_TEXT', true);
		Configuration::updateValue('MANUFACTURER_DISPLAY_TEXT_NB', 5);
		Configuration::updateValue('MANUFACTURER_DISPLAY_FORM', true);
    }
   
    function hookLeftColumn($params)
    {
		global $smarty, $link;
		
		$smarty->assign(array(
			'manufacturers' => Manufacturer::getManufacturers(),
			'link' => $link,
			'text_list' => Configuration::get('MANUFACTURER_DISPLAY_TEXT'),
			'text_list_nb' => Configuration::get('MANUFACTURER_DISPLAY_TEXT_NB'),
			'form_list' => Configuration::get('MANUFACTURER_DISPLAY_FORM'),
		));
		return $this->display(__FILE__, 'blockmanufacturer.tpl');
	}
	
	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	
	function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockManufacturers'))
		{
			$text_list = intval(Tools::getValue('text_list'));
			$text_nb = intval(Tools::getValue('text_nb'));
			$form_list = intval(Tools::getValue('form_list'));
			if ($text_list AND !Validate::isUnsignedInt($text_nb))
				$errors[] = $this->l('Invalid number of elements');
			elseif (!$text_list AND !$form_list)
				$errors[] = $this->l('Please activate at least one system list');
			else
			{
				Configuration::updateValue('MANUFACTURER_DISPLAY_TEXT', $text_list);
				Configuration::updateValue('MANUFACTURER_DISPLAY_TEXT_NB', $text_nb);
				Configuration::updateValue('MANUFACTURER_DISPLAY_FORM', $form_list);
			}
			if (isset($errors) AND sizeof($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}
	
	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Use a plain-text list').'</label>
				<div class="margin-form">
					<input type="radio" name="text_list" id="text_list_on" value="1" '.(Tools::getValue('text_list', Configuration::get('MANUFACTURER_DISPLAY_TEXT')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="text_list_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="text_list" id="text_list_off" value="0" '.(!Tools::getValue('text_list', Configuration::get('MANUFACTURER_DISPLAY_TEXT')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="text_list_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					&nbsp;&nbsp;&nbsp;'.$this->l('Display').' <input type="text" size="2" name="text_nb" value="'.intval(Tools::getValue('text_nb', Configuration::get('MANUFACTURER_DISPLAY_TEXT_NB'))).'" /> '.$this->l('elements').'
					<p class="clear">'.$this->l('To display manufacturers in a plain-text list').'</p>
				</div>
				<label>'.$this->l('Use a drop-down list').'</label>
				<div class="margin-form">
					<input type="radio" name="form_list" id="form_list_on" value="1" '.(Tools::getValue('form_list', Configuration::get('MANUFACTURER_DISPLAY_FORM')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="form_list_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="form_list" id="form_list_off" value="0" '.(!Tools::getValue('form_list', Configuration::get('MANUFACTURER_DISPLAY_FORM')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="form_list_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('To display manufacturers in a drop-down list').'</p>
				</div>
				<center><input type="submit" name="submitBlockManufacturers" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}
}

?>
