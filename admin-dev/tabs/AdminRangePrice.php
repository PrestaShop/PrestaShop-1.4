<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminRangePrice extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'range_price';
	 	$this->className = 'RangePrice';
	 	$this->lang = false;
	 	$this->edit = true;
	 	$this->delete = true;

		$this->fieldsDisplay = array(
		'id_range_price' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'carrier_name' => array('title' => $this->l('Carrier'), 'align' => 'center', 'width' => 25, 'filter_key' => 'ca!name'),
		'delimiter1' => array('title' => $this->l('From'), 'width' => 86, 'price' => true, 'align' => 'right'),
		'delimiter2' => array('title' => $this->l('To'), 'width' => 86, 'price' => true, 'align' => 'right'));

		$this->_join = 'LEFT JOIN '._DB_PREFIX_.'carrier ca ON (ca.`id_carrier` = a.`id_carrier`)';
		$this->_select = 'ca.`name` AS carrier_name';
		$this->_where = 'AND ca.`deleted` = 0';

		parent::__construct();
	}

	public function displayListContent($token = NULL)
	{
		foreach ($this->_list as $key => $list)
			if ($list['carrier_name'] == '0')
				$this->_list[$key]['carrier_name'] = Configuration::get('PS_SHOP_NAME');
		parent::displayListContent($token);
	}

	public function postProcess()
	{
		if (isset($_POST['submitAdd'.$this->table]) AND Tools::getValue('delimiter1') >= Tools::getValue('delimiter2'))
			$this->_errors[] = Tools::displayError('Invalid range');
		else
			parent::postProcess();
	}

	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();

		if (!($obj = $this->loadObject(true)))
			return;
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$default_country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));

		$carrierArray = array();
		$carriers = Carrier::getCarriers((int)(Configuration::get('PS_LANG_DEFAULT')), true , false,false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		$id_carrier = Tools::getValue('id_carrier', $obj->id_carrier);
		foreach ($carriers AS $carrier)
		{
			if (!$carrier['is_free'])
			{
				$carrierArray[] = '<option value="'.(int)($carrier['id_carrier']).'"'.(($carrier['id_carrier'] == $id_carrier) ? ' selected="selected"' : '').'>'.$carrier['name'].'</option><sup>*</sup>';

				$carrier_taxes[(int)$carrier['id_carrier']] = TaxRulesGroup::getTaxesRate((int)$carrier['id_tax_rules_group'], (int)Configuration::get('PS_COUNTRY_DEFAULT'), 0, 0);
			}
		}

		echo $this->displayJavascript($carrier_taxes);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/t/AdminRangePrice.gif" />'.$this->l('Price ranges').'</legend>
				<label>'.$this->l('Carrier').'</label>
				<div class="margin-form">';
			if (count($carrierArray))
			{
				echo '<select id="id_carrier" name="id_carrier">';
				foreach ($carrierArray AS $carrierOption)
					echo $carrierOption;
				echo '</select>
				<p class="clear">'.$this->l('Carrier to which this range will be applied').'</p>';
			}
			else
				echo '<div style="margin:5px 0 10px 0">'.$this->l('There isn\'t any carrier available for a price range.').'</div>';
			echo '
				</div>
				<label>'.$this->l('From:').' </label>
				<div class="margin-form">
					'.$currency->getSign('left').'<input type="text" size="4" id="delimiter1_excl" name="delimiter1_excl" value="" />'.$currency->getSign('right').'<sup>*</sup>
					'.$this->l('(tax excl.)').'
					'.$currency->getSign('left').'<input type="text" size="4" id="delimiter1" name="delimiter1" value="'.Tools::ps_round($this->getFieldValue($obj, 'delimiter1'), 2).'" />'.$currency->getSign('right').'<sup>*</sup>
					'.$this->l('(tax incl.)').'
					<p class="clear">'.$this->l('Range start (included)').'</p>
				</div>
				<label>'.$this->l('To:').' </label>
				<div class="margin-form">
					'.$currency->getSign('left').'<input type="text" size="4" id="delimiter2_excl" name="delimiter2_excl" value="" />'.$currency->getSign('right').'<sup>*</sup>
					'.$this->l('(tax excl.)').'
					'.$currency->getSign('left').'<input type="text" size="4" id="delimiter2" name="delimiter2" value="'.Tools::ps_round($this->getFieldValue($obj, 'delimiter2'), 2).'" />'.$currency->getSign('right').'<sup>*</sup>
					'.$this->l('(tax incl.)').'
					<p class="clear">'.$this->l('Range end (excluded)').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	public function displayJavascript($carrier_taxes)
	{
		$html = '<script type="text/javascript">'."\n";
		$html .= 'var carrier_taxes = new Array();'."\n";
		foreach($carrier_taxes as $id_carrier => $tax)
			$html .= 'carrier_taxes['.(int)$id_carrier.'] = '.(float)$tax."\n";

		$html .= '
				function setPriceTaxExcl(id)
				{
						var val = $(id).val() / (1 + getTaxValue());
						$(id+"_excl").val(ps_round(val,6));
				}

				function setPriceInput(id)
				{
					setPriceTaxExcl(id); // init

					$(id).keyup(function() {
						setPriceTaxExcl(id);
					})

					$(id+"_excl").keyup(function() {
						var val = $(id+"_excl").val() * (1 + getTaxValue());
						$(id).val(ps_round(val, 2));
					})
				}

				function getTaxValue()
				{
					selected_carrier = $("#id_carrier").val();
					tax = carrier_taxes[selected_carrier];
					return parseFloat(tax / 100);
				}

				$(document).ready(function(){
					setPriceInput("#delimiter1");
					setPriceInput("#delimiter2");
				})
				';

		$html .= '</script>'."\n";

		return $html;
	}
}

