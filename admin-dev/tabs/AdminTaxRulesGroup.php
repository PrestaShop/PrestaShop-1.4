<?php

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


    public function displayForm()
    {
        global $cookie, $currentIndex;
		parent::displayForm();
		$obj = $this->loadObject(true);
		$tax_rules = isset($obj->id) ? $tax_rules = TaxRule::getTaxRulesByGroupId($obj->id) : array();

        echo '<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		        '.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
    			<input type="hidden" name="tabs" id="tabs" value="0" />
    			<fieldset><legend><img src="../img/admin/dollar.gif" />'.$this->l('Tax Rules').'</legend>';

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
				<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
			</div>';

        echo '<br />';

        echo '<div class="tab-pane" id="tab-pane-1">
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

            function openStates(id_country)
            {
                if ($("#states_"+id_country+":hidden").length)
                {
                    $("#states_"+id_country).show();
                    $("#open_states_"+id_country).attr("src","../img/admin/less.png");
                }
                else
                {
                    $("#states_"+id_country).hide();
                    $("#open_states_"+id_country).attr("src","../img/admin/more.png");
                }
            }

            function disableStateTaxRate(id_country, id_state)
            {
                if ($("#behavior_state_"+id_state).val() == '.PS_PRODUCT_TAX.')
                    $("#tax_"+id_country+"_"+id_state).attr("disabled", true);
                else
                    $("#tax_"+id_country+"_"+id_state).attr("disabled", false);
            }
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
				<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
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

        $html = '<table>
                 <tr>
                     <td width="260px" style="font-weight:bold">'.$this->l('All').'</td>
                     <td >'.$this->renderTaxesSelect($id_lang, '', array('id' => 'zone_'.(int)$id_zone)).' <input type="submit" onclick="return applyTax(\''.(int)$id_zone.'\')" class="button" value="'.$this->l('Apply').'"/></td>
                </tr>
                <tr><td></td><td>&nbsp;</td></tr>';

        $countries = Country::getCountriesByZoneId((int)$id_zone, (int)$id_lang);

        foreach ($countries AS $country)
        {
            $id_tax =  0;

            if (array_key_exists($country['id_country'], $tax_rules) AND array_key_exists(0, $tax_rules[$country['id_country']]))
                $id_tax = (int)$tax_rules[$country['id_country']][0]['id_tax'];

            $html .= '<tr>
                            <td valign="top" width="260px"><span style="padding-right:0.5em">'
                            .($country['contains_states'] ?
							'<a id="open_'.(int)$country['id_country'].'" onclick="openStates(\''.(int)$country['id_country'].'\')"><img id="open_states_'.(int)$country['id_country'].'" src="../img/admin/more.png" alt="" /></a>' : '').
                            Tools::htmlentitiesUTF8($country['name']).'
							</span>
                            </td>
                            <td  valign="top" >'.$this->renderTaxesSelect($id_lang, $id_tax, array('class' => 'tax_'.$id_zone, 'id' => 'tax_'.$country['id_country'].'_0', 'name' => 'tax_'.$country['id_country'].'_0' )).
                            '</td>
                      </tr>';

                      if ($country['contains_states'])
                          $html .= '<tr class="states_row" "id="states_'.(int)$country['id_country'].'" style="display:none">
                                <td colspan="2">
                                    <script type="text/javascript">
                                        $("#states_'.(int)$country['id_country'].'").hide();
                                    </script>

                                '.
                                     $this->renderStates($tax_rules, (int)$id_zone, (int)$country['id_country'], (int)$id_lang).
                                '
                                </td>
                          </tr>';
        }
        $html .= '</table>';

        return $html;
    }

    public function renderStates($tax_rules, $id_zone, $id_country, $id_lang)
    {
        $html = '<table style="padding-left: 20px"id="states_'.$id_country.'">';
        $states = State::getStatesByCountryId((int)$id_country);
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
            $html .= '<tr>
                        <td width="170px">'.Tools::htmlentitiesUTF8($state['name']).'</td>
                        <td>
                            <select id="behavior_state_'.$state['id_state'].'" name="behavior_state_'.$state['id_state'].'" onchange="disableStateTaxRate(\''.$id_country.'\',\''.$state['id_state'].'\')">
                                <option value="'.(int)PS_PRODUCT_TAX.'" '.($selected  == PS_PRODUCT_TAX ? 'selected="selected"' : '').'>product_tax</option>
                                <option value="'.(int)PS_STATE_TAX.'" '.($selected == PS_STATE_TAX ? 'selected="selected"' : '').'>state_tax</option>
                                <option value="'.(int)PS_BOTH_TAX.'" '.($selected == PS_BOTH_TAX ? 'selected="selected"' : '').'>both_tax</option>
                            </select>
                        </td>
                        <td>
                            '.$this->renderTaxesSelect($id_lang, $id_tax, array('class' => 'tax_'.$id_zone,
                                                                                  'id' => 'tax_'.$id_country.'_'.$state['id_state'],
                                                                                  'name' => 'tax_'.$id_country.'_'.$state['id_state'],
                                                                                  'disabled' => $disable )).'
                        </td>
                      </tr>';
        }
        $html .= '</table>';
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
}

