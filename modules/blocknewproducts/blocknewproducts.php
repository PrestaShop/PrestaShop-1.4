<?php

class BlockNewProducts extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'blocknewproducts';
        $this->tab = 'Blocks';
        $this->version = 0.9;

        parent::__construct();

        $this->displayName = $this->l('New products block');
        $this->description = $this->l('Displays a block featuring newly added products');
    }

    function install()
    {
        if (parent::install() == false 
				OR $this->registerHook('rightColumn') == false
				OR Configuration::updateValue('NEW_PRODUCTS_NBR', 5) == false)
			return false;
		return true;
    }

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockNewProducts'))
		{
			if (!$productNbr = Tools::getValue('productNbr') OR empty($productNbr))
				$output .= '<div class="alert error">'.$this->l('You should fill the "products displayed" field').'</div>';
			elseif (intval($productNbr) == 0)
				$output .= '<div class="alert error">'.$this->l('Invalid number.').'</div>';
			else
			{
				Configuration::updateValue('NEW_PRODUCTS_NBR', intval($productNbr));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Products displayed').'</label>
				<div class="margin-form">
					<input type="text" name="productNbr" value="'.intval(Configuration::get('NEW_PRODUCTS_NBR')).'" />
					<p class="clear">'.$this->l('Set the number of products to be displayed in this block').'</p>
				</div>
				<center><input type="submit" name="submitBlockNewProducts" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}

    function hookRightColumn($params)
    {
		global $smarty;
		$currency = new Currency(intval($params['cookie']->id_currency));
		$newProducts = Product::getNewProducts(intval($params['cookie']->id_lang), 0, Configuration::get('NEW_PRODUCTS_NBR'));
		$new_products = array();
		if ($newProducts)
			foreach ($newProducts AS $newProduct)
				$new_products[] = $newProduct;

		$smarty->assign(array(
			'new_products' => $new_products,
			'mediumSize' => Image::getSize('medium')));
		return $this->display(__FILE__, 'blocknewproducts.tpl');
	}
	
	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
}


?>
