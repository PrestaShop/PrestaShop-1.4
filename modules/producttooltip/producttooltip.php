<?php

class ProductToolTip extends Module
{
	public function __construct()
	{
		$this->name = 'producttooltip';
		$this->tab = 'Products';
		$this->version = '1.0';

		parent::__construct();

		$this->displayName = $this->l('Product tooltips');
		$this->description = $this->l('Show how many people are watching a product page, last sale and last cart add');		
	}
	
	public function install()
	{
	 	if (!parent::install())
	 		return false;
			
		/* Default configuration values */
		Configuration::updateValue('PS_PTOOLTIP_PEOPLE', 1);
		Configuration::updateValue('PS_PTOOLTIP_DATE_CART', 1);
		Configuration::updateValue('PS_PTOOLTIP_DATE_ORDER', 1);
		Configuration::updateValue('PS_PTOOLTIP_DAYS', 3);
		Configuration::updateValue('PS_PTOOLTIP_LIFETIME', 30);

	 	return $this->registerHook('productfooter');
	}
	
	public function uninstall()
	{
		if (!Configuration::deleteByName('PS_PTOOLTIP_PEOPLE')
			OR !Configuration::deleteByName('PS_PTOOLTIP_DATE_CART')
			OR !Configuration::deleteByName('PS_PTOOLTIP_DATE_ORDER')
			OR !Configuration::deleteByName('PS_PTOOLTIP_DAYS')
			OR !Configuration::deleteByName('PS_PTOOLTIP_LIFETIME')
			OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		/* Update values in DB */
		if (isset($_POST['SubmitToolTip']))
		{
			Configuration::updateValue('PS_PTOOLTIP_PEOPLE', intval($_POST['ps_ptooltip_people']));
			Configuration::updateValue('PS_PTOOLTIP_DATE_CART', intval($_POST['ps_ptooltip_date_cart']));
			Configuration::updateValue('PS_PTOOLTIP_DATE_ORDER', intval($_POST['ps_ptooltip_date_order']));
			Configuration::updateValue('PS_PTOOLTIP_DAYS', intval($_POST['ps_ptooltip_days']));
			Configuration::updateValue('PS_PTOOLTIP_LIFETIME', intval($_POST['ps_ptooltip_lifetime']));
		}

		/* Configuration form */
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset class="width2" style="float: left;">
			<legend><img src="'.__PS_BASE_URI__.'modules/producttooltip/logo.gif" alt="" />'.$this->l('Product tooltips').'</legend>
			<p>
				'.$this->l('Display the number of people who are currently watching this product?').'<br /><br />
				<img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="" /><input type="radio" name="ps_ptooltip_people" value="1"'.(Configuration::get('PS_PTOOLTIP_PEOPLE') ? ' checked="checked"' : '').' style="vertical-align: middle;" /> '.$this->l('Yes').' 
				&nbsp;<img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="" /><input type="radio" name="ps_ptooltip_people" value="0"'.(!Configuration::get('PS_PTOOLTIP_PEOPLE') ? ' checked="checked"' : '').' style="vertical-align: middle;" /> '.$this->l('No').'<br />
				
			</p>
			<p>
				'.$this->l('Lifetime:').'
				<input type="text" name="ps_ptooltip_lifetime" style="width: 30px;" value="'.intval(Configuration::get('PS_PTOOLTIP_LIFETIME')).'" /> '.$this->l('minutes').'<br />
			</p>
			<hr size="1" noshade />			
			<p>
				'.$this->l('Display the last time the product has been ordered?').'<br /><br />
				<img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="" /><input type="radio" name="ps_ptooltip_date_order" value="1"'.(Configuration::get('PS_PTOOLTIP_DATE_ORDER') ? ' checked="checked"' : '').' style="vertical-align: middle;" /> '.$this->l('Yes').' 
				&nbsp;<img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="" /><input type="radio" name="ps_ptooltip_date_order" value="0"'.(!Configuration::get('PS_PTOOLTIP_DATE_ORDER') ? ' checked="checked"' : '').' style="vertical-align: middle;" /> '.$this->l('No').'<br /><br />
			</p>
			<p>
				'.$this->l('If no order yet, display the last time the product has been added to cart?').'<br /><br />
				<img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="" /><input type="radio" name="ps_ptooltip_date_cart" value="1"'.(Configuration::get('PS_PTOOLTIP_DATE_CART') ? ' checked="checked"' : '').' style="vertical-align: middle;" /> '.$this->l('Yes').' 
				&nbsp;<img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="" /><input type="radio" name="ps_ptooltip_date_cart" value="0"'.(!Configuration::get('PS_PTOOLTIP_DATE_CART') ? ' checked="checked"' : '').' style="vertical-align: middle;" /> '.$this->l('No').'<br /><br />
				
			</p>
			<p>
				'.$this->l('Do not display events older than:').'
				<input type="text" name="ps_ptooltip_days" style="width: 30px;" value="'.intval(Configuration::get('PS_PTOOLTIP_DAYS')).'" /> '.$this->l('days').'<br />
			</p>
			<hr size="1" noshade />
			<center><input type="submit" name="SubmitToolTip" class="button" value="'.$this->l('Update settings').'" style="margin-top: 10px;"  /></center>
		</fieldset>
		<p style="float: left; margin: 10px 0 0 30px;">
			<b>'.$this->l('Sample:').'</b><br />
			<img src="'.__PS_BASE_URI__.'modules/producttooltip/sample.gif" style="margin-top: 10px;" />
		</p>
		<div style="clear: both; font-size: 0;"></div>
		</form>';
		
		return $output;
	}
	
	public function hookProductFooter($params)
	{
		global $smarty, $cookie;
		
		$id_product = intval($params['product']->id);
		
		/* First we try to display the number of people who are currently watching this product page */
		if (Configuration::get('PS_PTOOLTIP_PEOPLE'))
		{
			$date = strftime('%Y-%m-%d %H:%M:%S' , time() - intval(Configuration::get('PS_PTOOLTIP_LIFETIME') * 60));
			
			$nbPeople = Db::getInstance()->getRow('
			SELECT COUNT(DISTINCT(id_connections)) nb
			FROM '._DB_PREFIX_.'page p
			LEFT JOIN '._DB_PREFIX_.'connections_page cp ON (p.id_page = cp.id_page)
			WHERE p.id_page_type = 1 AND p.id_object = '.intval($id_product).' AND cp.time_start > \''.$date.'\'');

			if (isset($nbPeople['nb']) AND $nbPeople['nb'] > 0)
				$smarty->assign('nb_people', intval($nbPeople['nb']));
		}
		
		/* Then, we try to display last sale */
		if (Configuration::get('PS_PTOOLTIP_DATE_ORDER'))
		{
			$days = intval(Configuration::get('PS_PTOOLTIP_DAYS'));
			$date = strftime('%Y-%m-%d' , strtotime('-'.intval($days).' day'));
			
			$order = Db::getInstance()->getRow('
			SELECT o.date_add
			FROM '._DB_PREFIX_.'order_detail od
			LEFT JOIN '._DB_PREFIX_.'orders o ON (od.id_order = o.id_order)
			WHERE od.product_id = '.intval($id_product).' AND o.date_add >= \''.$date.'\'');
			
			if (isset($order['date_add']))
				$smarty->assign('date_last_order', $order['date_add']);
			else
			{
				/* No sale? display last cart add instead */
				if (Configuration::get('PS_PTOOLTIP_DATE_CART'))
				{
					$cart = Db::getInstance()->getRow('
					SELECT cp.date_add
					FROM '._DB_PREFIX_.'cart_product cp
					WHERE cp.id_product = '.intval($id_product));
			
					if (isset($cart['date_add']))
						$smarty->assign('date_last_cart', $cart['date_add']);
				}
			}
		}		

		if ((isset($nbPeople['nb']) AND $nbPeople['nb'] > 0) OR isset($order['date_add']) OR isset($cart['date_add']))
			return $this->display(__FILE__, 'producttooltip.tpl');
	}
}

?>