<?php

/**
  * Countries tab for admin panel, AdminCountries.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminCountries extends AdminTab
{
	public function __construct()
	{
		global $cookie;
		
	 	$this->table = 'country';
	 	$this->className = 'Country';
	 	$this->lang = true;
	 	$this->edit = true;
		$this->deleted = false;
	 	$this->_select = 'z.`name` AS zone';
	 	$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`id_zone` = a.`id_zone`)';
				
		$this->fieldsDisplay = array(
		'id_country' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Country'), 'width' => 130, 'filter_key' => 'b!name'),
		'iso_code' => array('title' => $this->l('ISO code'), 'width' => 70, 'align' => 'center'),
		'zone' => array('title' => $this->l('Zone'), 'width' => 100, 'filter_key' => 'z!name'),
		'a!active' => array('title' => $this->l('Enabled'), 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false, 'filter_key' => 'a!active'));
	
		$this->optionTitle = $this->l('Countries options');
		$this->_fieldsOptions = array(
			'PS_COUNTRY_DEFAULT' => array('title' => $this->l('Default country:'), 'desc' => $this->l('The default country used in shop'), 'cast' => 'intval', 'type' => 'select', 'identifier' => 'id_country', 'list' => Country::getCountries(intval($cookie->id_lang))),
		);
		parent::__construct();
	}
	
	public function postProcess()
	{
		if (isset($_GET['delete'.$this->table]) OR Tools::getValue('submitDel'.$this->table))
			$this->_errors[] = Tools::displayError('You cannot delete a country. If you do not want it available for customers, please disable it.');
		else
			return parent::postProcess();
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
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/world.gif" />'.$this->l('Countries').'</legend>
				<label>'.$this->l('Country:').' </label>
				<div class="margin-form">';

				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="30" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						</div>';							
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');

		echo '		<p style="clear: both">'.$this->l('Name of country').'</p>
				</div>
				<label>'.$this->l('ISO code:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="3" name="iso_code" value="'.htmlentities($this->getFieldValue($obj, 'iso_code'), ENT_COMPAT, 'UTF-8').'" style="text-transform: uppercase;" /> <sup>*</sup>
					<p>'.$this->l('2- or 3-letter ISO code, e.g., FR for France').'. <a href="http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html" target="_blank">'.$this->l('Official list here').'</a>.</p>
				</div>
				<label>'.$this->l('Zone:').' </label>
				<div class="margin-form">
					<select name="id_zone">';
					
		$zones = Zone::getZones();
		foreach ($zones AS $zone)
			echo '<option value="'.intval($zone['id_zone']).'"'.(($this->getFieldValue($obj, 'id_zone') == $zone['id_zone']) ? ' selected="selected"' : '').'>'.$zone['name'].'</option>';

		echo '
					</select>
					<p>'.$this->l('Geographical zone where country is located').'</p>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.((!$obj->id OR $this->getFieldValue($obj, 'active')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.((!$this->getFieldValue($obj, 'active') AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Enabled or disabled').'</p>
				</div>
				<label>'.$this->l('Contains states:').' </label>
				<div class="margin-form">
					<input type="radio" name="contains_states" id="contains_states_on" value="1" '.((!$obj->id OR $this->getFieldValue($obj, 'contains_states')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="contains_states_on"> <img src="../img/admin/enabled.gif" alt="" title="" />'.$this->l('Yes').'</label>
					<input type="radio" name="contains_states" id="contains_states_off" value="0" '.((!$this->getFieldValue($obj, 'contains_states') AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="contains_states_off"> <img src="../img/admin/disabled.gif" alt="" title="" />'.$this->l('No').'</label>
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
