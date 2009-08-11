<?php

/**
  * States tab for admin panel, AdminStates.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminStates extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'state';
	 	$this->className = 'State';
	 	$this->edit = true;
	 	$this->delete = true;

		$this->fieldsDisplay = array(
		'id_state' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 140, 'filter_key' => 'a!name'),
		'iso_code' => array('title' => $this->l('ISO code'), 'align' => 'center', 'width' => 50),
		'zone' => array('title' => $this->l('Zone'), 'width' => 100, 'filter_key' => 'z!name'));
		$this->_select = 'z.`name` AS zone';
	 	$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`id_zone` = a.`id_zone`)';

		parent::__construct();
	}

	public function displayForm()
	{
		global $currentIndex, $cookie;

		$obj = $this->loadObject(true);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/world.gif" />'.$this->l('States').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">
					<input type="text" size="30" maxlength="32" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('State name to display in addresses and on invoices').'</p>
				</div>
				<label>'.$this->l('ISO code:').' </label>
				<div class="margin-form">
					<input type="text" size="5" maxlength="4" name="iso_code" value="'.htmlentities($this->getFieldValue($obj, 'iso_code'), ENT_COMPAT, 'UTF-8').'" style="text-transform: uppercase;" /> <sup>*</sup>
					<p>'.$this->l('1 to 4 letter ISO code').' (<a href="http://simple.wikipedia.org/wiki/List_of_U.S._states" target="_blank">'.$this->l('official list here').'</a>)</p>
				</div>
				<label>'.$this->l('Country:').' </label>
				<div class="margin-form">
					<select name="id_country">';
				$countries = Country::getCountries(intval($cookie->id_lang), false, true);
				foreach ($countries AS $country)
					echo '<option value="'.intval($country['id_country']).'"'.(($this->getFieldValue($obj, 'id_country') == $country['id_country']) ? ' selected="selected"' : '').'>'.$country['name'].'</option>';
				echo '
					</select>
					<p>'.$this->l('Country where state, region or city is located').'</p>
				</div>
				<label>'.$this->l('Zone:').' </label>
				<div class="margin-form">
					<select name="id_zone">';

		$zones = Zone::getZones();
		foreach ($zones AS $zone)
			echo '<option value="'.intval($zone['id_zone']).'"'.(($this->getFieldValue($obj, 'id_zone') == $zone['id_zone']) ? ' selected="selected"' : '').'>'.$zone['name'].'</option>';

		echo '
					</select>
					<p>'.$this->l('Geographical zone where this state is located').'<br />'.$this->l('Used for shipping').'</p>
				</div>
				<label>'.$this->l('Tax behavior:').' </label>
				<div class="margin-form">
					<input type="radio" name="tax_behavior" id="product_tax" value="'.PS_PRODUCT_TAX.'" '.((!$obj->id OR $this->getFieldValue($obj, 'tax_behavior') == PS_PRODUCT_TAX) ? 'checked="checked" ' : '').'/>
					<label class="t" for="product_tax">'.$this->l('Product tax').'</label>
					<input type="radio" name="tax_behavior" id="state_tax" value="'.PS_STATE_TAX.'" '.(($this->getFieldValue($obj, 'tax_behavior') == PS_STATE_TAX AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="state_tax">'.$this->l('State tax').'</label>
					<input type="radio" name="tax_behavior" id="both_tax" value="'.PS_BOTH_TAX.'" '.(($this->getFieldValue($obj, 'tax_behavior') == PS_BOTH_TAX AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="both_tax">'.$this->l('Both product & state tax').'</label>
					<p>'.$this->l('Chose how tax will be applied for this state: the product\'s tax, the state\'s tax, or both.').'</p>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.((!$obj->id OR $this->getFieldValue($obj, 'active')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.((!$this->getFieldValue($obj, 'active') AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Enabled or disabled').'</p>
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