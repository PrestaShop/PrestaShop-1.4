<?php
/*
* 2007-2010 PrestaShop
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


class AdminTaxRulesGroup extends AdminTab
{
	public function __construct()
	{
		global $cookie;
	 	$this->table = 'tax_rules_group';
	 	$this->className = 'TaxRulesGroup';
	 	$this->edit = true;
	 	$this->delete = true;

		$this->fieldsDisplay = array(
		'id_tax_rules_group' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 140),
		'active' => array('title' => $this->l('Enabled'), 'width' => 25, 'align' => 'center', 'active' => 'status'.$this->table, 'type' => 'bool', 'orderby' => false));

        parent::__construct();
	}


    public function displayForm($isMainTab = true)
    {
        global $cookie, $currentIndex;
		parent::displayForm();
		if (!($obj = $this->loadObject(true)))
			return;
		$tax_rules = isset($obj->id) ? $tax_rules = TaxRule::getTaxRulesByGroupId($obj->id) : array();

        $param_product = Tools::getValue('id_product') ? '&id_product='.Tools::getValue('id_product') : '';

        echo '<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.$param_product.'" method="post">
		        '.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
    			<fieldset><legend><img src="../img/admin/dollar.gif" />'.$this->l('Tax Rules').'</legend>
    			<input type="hidden" name="tabs" id="tabs" value="0" />
    			<div class="hint clear" style="display:block;">
					'.$this->l('Tax Rules allows you to define for one product or carrier various taxes in function of different places (Country, States etc ..)').'
				</div><br />
    			';

        echo '<label>'.$this->l('Name').'</label>
    			<div class="margin-form">
					<input size="33" type="text" name="name" value="'.Tools::htmlentitiesUTF8($this->getFieldValue($obj, 'name')).'" /><sup> *</sup>
					<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
            <p class="clear"></p>
            </div>';

        echo '
            <label>'.$this->l('Enable:').' </label>
			<div class="margin-form">
				<input type="radio" name="active" id="active_on" value="1" '.($this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
				<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
				<input type="radio" name="active" id="active_off" value="0" '.(!$this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
				<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
            </div>
    		<div class="margin-form">
				<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />&nbsp;
				<input type="submit" value="'.$this->l('Save and stay').'" name="submitAdd'.$this->table.'AndStay" class="button" />
			</div>';

        echo '<br />';

        echo '<div class="tab-panet-tax" id="tab-pane-1">
            <script type="text/javascript">
			var pos_select = '.(($tab = Tools::getValue('tabs')) ? $tab : '0').';
            function loadTab(id)
            {}

            function applyTax(id_zone)
            {
                   cur_tax = $("#zone_"+id_zone).val();
                    $(".tax_"+id_zone).val(cur_tax);
                    return false;
            }


            function disableStateTaxRate(id_country, id_state)
            {
                if ($("#behavior_state_"+id_state).val() == '.PS_PRODUCT_TAX.')
                    $("#tax_"+id_country+"_"+id_state).attr("disabled", true);
                else
                    $("#tax_"+id_country+"_"+id_state).attr("disabled", false);
            }

			$(\'document\').ready(function (){
				$(\'.states\').hide();

				$(\'.open_state\').click(function (){
					if ($(\'.\'+$(this).attr(\'id\')).is(\':hidden\'))
					{
						$(\'.\'+$(this).attr(\'id\')).show();
						$(\'#\'+$(this).attr(\'id\')+\'_button\').attr("src","../img/admin/less.png");
					}
					else
					{
						$(\'.\'+$(this).attr(\'id\')).hide();
						$(\'.county_\'+$(this).attr(\'id\')).hide();
						$(\'.county_\'+$(this).attr(\'id\')+\'_button\').attr("src","../img/admin/more.png");
						$(\'#\'+$(this).attr(\'id\')+\'_button\').attr("src","../img/admin/more.png");
					}
				});
			});
            </script>
            <script src="../js/tabpane.js" type="text/javascript"></script>
            <script type="text/javascript">
            var tabPane1 = new WebFXTabPane( document.getElementById( "tab-pane-1" ) );
		    </script>
               <link type="text/css" rel="stylesheet" href="../css/tabpane.css" />'
                .$this->renderZones($tax_rules, (int)$cookie->id_lang).
             '</div>';
        echo '
    		<div class="margin-form" style="margin-top: 10px">
				<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />&nbsp;&nbsp;
				<input type="submit" value="'.$this->l('Save and stay').'" name="submitAdd'.$this->table.'AndStay" class="button" />
			</div>
            </fieldset>
            </form>';
    }


    public function renderZones($tax_rules, $id_lang)
    {
        $html = '';
        $zones = Zone::getZones(true);
        foreach ($zones AS $key => $zone)
        {
            $html .= '<div class="tab-page" id="tab-page-'.$key.'">
                            <h4 class="tab">'.$zone['name'].'</h4>
                            <script type="text/javascript">
                                tabPane1.addTabPage( document.getElementById( "tab-page-'.$key.'" ) );
                            </script>
                        '.$this->renderCountries($tax_rules, $zone['id_zone'], $id_lang).'
                      </div>';
        }

        return $html;
    }

    public function renderCountries($tax_rules, $id_zone, $id_lang)
    {

        $html = '
        <table>
			<tr>
				<td width="260px" style="font-weight:bold">'.$this->l('All').'</td>
				<td>'.$this->renderTaxesSelect($id_lang, '', array('id' => 'zone_'.(int)$id_zone)).' <input type="submit" onclick="return applyTax(\''.(int)$id_zone.'\')" class="button" value="'.$this->l('Apply').'"/></td>
			</tr>
		</table><hr />';

		$html .= '
		<table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
			<thead>
				<tr>
					<th style="width: 3%;"></th>
					<th style="width: 40%;">'.$this->l('Country / State').'</th>
					<th style="width: 57%;">'.$this->l('Tax to apply').'</th>
				</tr>
			</thead>
			<tbody>
		';
		$countries = Country::getCountriesByZoneId((int)$id_zone, (int)$id_lang);
		$countCountries = sizeof($countries);
		$i = 1;
        foreach ($countries AS $country)
        {
            $id_tax =  0;

            if (array_key_exists($country['id_country'], $tax_rules) AND array_key_exists(0, $tax_rules[$country['id_country']]))
                $id_tax = (int)$tax_rules[$country['id_country']][0]['id_tax'];

			$html .= '
				<tr>
					<td>'.($country['contains_states'] ? '<a class="open_state" id="state_'.(int)$country['id_country'].'"><img id="state_'.(int)$country['id_country'].'_button" src="../img/admin/more.png" alt="" style="vertical-align:middle;padding:0;" /></a>' : '').'</td>
					<td>
						<img src="../img/admin/lv2_'.($i == $countCountries ? 'f' : 'b').'.png" alt="" style="vertical-align:middle;" /> <label class="t">'.Tools::htmlentitiesUTF8($country['name']).'</label>
					</td>
					<td>'.$this->renderTaxesSelect($id_lang, $id_tax, array('class' => 'tax_'.$id_zone, 'id' => 'tax_'.$country['id_country'].'_0', 'name' => 'tax_'.$country['id_country'].'_0' )).'</td>
				</tr>
			';
			if ($country['contains_states'])
				$html .= $this->renderStates($tax_rules, (int)$id_zone, (int)$country['id_country'], (int)$id_lang);

			$i++;
        }
        $html .= '
			</tbody>
		</table>
		';

        return $html;
    }

    public function renderStates($tax_rules, $id_zone, $id_country, $id_lang)
    {
        $states = State::getStatesByIdCountryAndIdZone((int)$id_country, (int)$id_zone);
		$countStates = sizeof($states);
		$i = 1;
		$html = '';
		foreach ($states AS $state)
		{
			$id_tax = 0;
			$selected = PS_PRODUCT_TAX;

            if (array_key_exists($id_country, $tax_rules) AND array_key_exists($state['id_state'], $tax_rules[$id_country]))
            {
              $id_tax = (int)$tax_rules[$id_country][$state['id_state']]['id_tax'];
              $selected = (int)$tax_rules[$id_country][$state['id_state']]['state_behavior'];
            }

			$disable = (PS_PRODUCT_TAX == $selected ? 'disabled' : '');
			$html .= '
			<tr class="states state_'.(int)$id_country.' alt_row">
				<td>'.(false ? '<a class="open_county" id="county_'.(int)$state['id_state'].'"><img id="county_'.(int)$state['id_state'].'_button" class="county_state_'.(int)$id_country.'_button" src="../img/admin/more.png" alt="" /></a>' : '').'</td>
				<td><img src="../img/admin/lv3_'.($i == $countStates ? 'f' : 'b').'.png" alt="" style="vertical-align:middle;" /> <label class="t">'.Tools::htmlentitiesUTF8($state['name']).'</label></td>
				<td>
					'.$this->renderTaxesSelect($id_lang, $id_tax, array('class' => 'tax_'.$id_zone,
 																	'id' => 'tax_'.$id_country.'_'.$state['id_state'],
 																	'name' => 'tax_'.$id_country.'_'.$state['id_state'],
 																	'disabled' => $disable )).'&nbsp;-&nbsp;
 					<select id="behavior_state_'.$state['id_state'].'" name="behavior_state_'.$state['id_state'].'" onchange="disableStateTaxRate(\''.$id_country.'\',\''.$state['id_state'].'\')">
						<option value="'.(int)PS_PRODUCT_TAX.'" '.($selected  == PS_PRODUCT_TAX ? 'selected="selected"' : '').'>'.$this->l('Apply country tax only').'</option>
						<option value="'.(int)PS_STATE_TAX.'" '.($selected == PS_STATE_TAX ? 'selected="selected"' : '').'>'.$this->l('Apply state tax only').'</option>
						<option value="'.(int)PS_BOTH_TAX.'" '.($selected == PS_BOTH_TAX ? 'selected="selected"' : '').'>'.$this->l('Apply both taxes').'</option>
					</select>
				</td>
			</tr>
			';

			$i++;
        }

        return $html;
    }

    public function renderTaxesSelect($id_lang, $default_value, array $html_options)
    {
        $opt = '';
        foreach( array('id', 'class', 'name', 'disabled') AS $prop)
            if (array_key_exists($prop, $html_options) && !empty($html_options[$prop]))
                $opt .= $prop.'="'.$html_options[$prop].'"';

        $html = '<select '.$opt.'>
                    <option value="0">'.$this->l('No Tax').'</option>';

        $taxes = Tax::getTaxes((int)$id_lang, true);
        foreach ($taxes AS $tax)
        {
            $selected = ($default_value == $tax['id_tax']) ? 'selected="selected"' : '';
            $html .= '<option value="'.(int)$tax['id_tax'].'" '.$selected.'>'.Tools::htmlentitiesUTF8($tax['name']).'</option>';
        }
        $html .= '</select>';

        return $html;
    }

    protected function afterAdd($object)
    {
        $this->afterUpdate($object);
    }


    protected function afterUpdate($object)
    {
        global $cookie;

        TaxRule::deleteByGroupId($object->id);


        foreach(Country::getCountries($cookie->id_lang, true) AS $country)
        {
            $id_tax = (int)Tools::getValue('tax_'.$country['id_country'].'_0');

            // default country rule
            if (!empty($id_tax))
            {
                $tr = new TaxRule();
                $tr->id_tax_rules_group = $object->id;
                $tr->id_country = (int)$country['id_country'];
                $tr->id_state = 0;
                $tr->id_tax = $id_tax;
                $tr->id_state_behavior = 0;
                $tr->save();
            }

            // state specific rule
            if (!empty($country['contains_states']))
            {
                foreach ($country['states'] AS $state)
                {
                    $state_behavior = (int)Tools::getValue('behavior_state_'.$state['id_state']);
                    if ($state_behavior != PS_PRODUCT_TAX)
                    {
                        $tr = new TaxRule();
                        $tr->id_tax_rules_group = $object->id;
                        $tr->id_country = (int)$country['id_country'];
                        $tr->id_state = (int)$state['id_state'];
                        $tr->id_tax = (int)Tools::getValue('tax_'.$country['id_country'].'_'.$state['id_state']);
                        $tr->state_behavior = $state_behavior;
                        $tr->save();
                    }
                }
            }

       }
   }


   public function postProcess()
   {

        global $currentIndex, $cookie;
		if (!isset($this->table))
			return false;

		// set token
		$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;

        if (Tools::getValue('submitAdd'.$this->table))
		{
		    $id_product = Tools::getValue('id_product');

			/* Checking fields validity */
			$this->validateRules();
			if (!sizeof($this->_errors))
			{
				$id = (int)(Tools::getValue($this->identifier));

				/* Object update */
				if (isset($id) AND !empty($id))
				{
					if ($this->tabAccess['edit'] === '1')
					{
						$object = new $this->className($id);
						if (Validate::isLoadedObject($object))
						{
							/* Specific to objects which must not be deleted */
							if ($this->deleted AND $this->beforeDelete($object))
							{
								// Create new one with old objet values
								$objectNew = new $this->className($object->id);
								$objectNew->id = NULL;
								$objectNew->date_add = '';
								$objectNew->date_upd = '';

								// Update old object to deleted
								$object->deleted = 1;
								$object->update();

								// Update new object with post values
								$this->copyFromPost($objectNew, $this->table);
								$result = $objectNew->add();
								if (Validate::isLoadedObject($objectNew))
									$this->afterDelete($objectNew, $object->id);
							}
							else
							{
								$this->copyFromPost($object, $this->table);
								$result = $object->update();
								$this->afterUpdate($object);
							}
							if (!$result)
								$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
							elseif ($this->postImage($object->id) AND !sizeof($this->_errors))
							{
								$parent_id = (int)(Tools::getValue('id_parent', 1));

								// Save and stay on same form
								if (Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
									Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&update'.$this->table.'&token='.$token);

								// Default behavior (save and back)
								$id_product = (int)Tools::getValue('id_product');
								if ($id_product)
    								Tools::redirectAdmin('?tab=AdminCatalog&id_product='.$id_product.'&updateproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)));

								Tools::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=3&token='.$token);
							}
						}
						else
							$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
				}

				/* Object creation */
				else
				{
					if ($this->tabAccess['add'] === '1')
					{
						$object = new $this->className();
						$this->copyFromPost($object, $this->table);
						if (!$object->add())
							$this->_errors[] = Tools::displayError('an error occurred while creating object').' <b>'.$this->table.' ('.mysql_error().')</b>';
						elseif (($_POST[$this->identifier] = $object->id /* voluntary */) AND $this->postImage($object->id) AND !sizeof($this->_errors) AND $this->_redirect)
						{
							$parent_id = (int)(Tools::getValue('id_parent', 1));
							$this->afterAdd($object);
							// Save and stay on same form
							if (Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
								Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=3&update'.$this->table.'&token='.$token);


							$id_product = (int)Tools::getValue('id_product');
							if ($id_product)
   								Tools::redirectAdmin('?tab=AdminCatalog&id_product='.$id_product.'&updateproduct&token='.Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee)));

							Tools::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=3&token='.$token);
							// Default behavior (save and back)
							Tools::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=3&token='.$token);
						}
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
				}
			}
			$this->_errors = array_unique($this->_errors);
		}
		parent::postProcess();
   }
}

