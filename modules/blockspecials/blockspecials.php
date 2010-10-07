<?php

class BlockSpecials extends Module
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'blockspecials';
		$this->tab = 'Blocks';
		$this->version = 0.8;

		parent::__construct();

		$this->displayName = $this->l('Specials block');
		$this->description = $this->l('Adds a block with current product Specials');
	}

	public function install()
	{
		return (parent::install() AND $this->registerHook('rightColumn')  AND $this->registerHook('header'));
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitSpecials'))
		{
			Configuration::updateValue('PS_BLOCK_SPECIALS_DISPLAY', intval(Tools::getValue('always_display')));
			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Always display block').'</label>
				<div class="margin-form">
					<input type="radio" name="always_display" id="display_on" value="1" '.(Tools::getValue('always_display', Configuration::get('PS_BLOCK_SPECIALS_DISPLAY')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="display_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="always_display" id="display_off" value="0" '.(!Tools::getValue('always_display', Configuration::get('PS_BLOCK_SPECIALS_DISPLAY')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="display_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Show the block even if no product is available.').'</p>
				</div>
				<center><input type="submit" name="submitSpecials" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}

	public function hookRightColumn($params)
	{
		global $smarty;
		if (!$special = Product::getRandomSpecial(intval($params['cookie']->id_lang)) AND !Configuration::get('PS_BLOCK_SPECIALS_DISPLAY'))
			return;
		$smarty->assign(array('special' => $special,
													'priceWithoutReduction_tax_excl' => Tools::ps_round($special['price_without_reduction'] / (1 + $special['rate'] / 100), 2),
													'oldPrice' => $special['price'] + $special['reduction'],
													'mediumSize' => Image::getSize('medium')));
		return $this->display(__FILE__, 'blockspecials.tpl');
	}
		
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
		
	public function hookHeader($params)
	{
		Tools::addCSS(_THEME_CSS_DIR_.'modules/'.$this->name.'/blockspecials.css', 'all');
	}
}

?>
