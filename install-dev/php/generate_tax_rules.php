<?php

function generate_tax_rules()
{
    $taxes = Tax::getTaxes(Configuration::get('PS_LANG_DEFAULT'), true);
    $countries = Country::getCountries(Configuration::get('PS_LANG_DEFAULT'));

    foreach ($taxes AS $tax)
    {
        $insert = '';
        $id_tax = $tax['id_tax'];

        $group = new TaxRulesGroup();
        $group->active = 1;
        $group->name = array(Configuration::get('PS_LANG_DEFAULT') => 'Rule '.$tax['rate'].'%');
        $group->save();
        $id_tax_rules_group = $group->id;

        foreach ($countries AS $country)
        {
            $id_country = (int)$country['id_country'];
            $id_state = 0;
            $id_state_behavior = 0;

            // country default rule
            $insert .= '(\''.(int)$id_tax_rules_group.'\',\''.(int)$id_country.'\',\'0\',\''.(int)$id_tax.'\',\'0\'),';

            if (!empty($country['contains_states'])) // state rules
            {
                foreach ($country['states'] AS $state)
                    $insert .= '(\''.(int)$id_tax_rules_group.'\',\''.(int)$id_country.'\',\''.(int)$state['id_state'].'\',\''.(int)$id_tax.'\',\''.(int)$state['tax_behavior'].'\'),';
            }
        }

        // ~ 300 rows inserted per taxes
        if (!empty($insert))
            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_rule` (id_tax_rules_group, id_country, id_state, id_tax, state_behavior) VALUES '.substr($insert, 0, -1));

        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `id_tax_rules_group` = '.(int)$id_tax_rules_group.' WHERE `id_tax` = '.(int)$id_tax);
    }
}

