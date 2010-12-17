<?php

/*
CREATE TABLE `ps_tax_rule` (
`id_tax_rules_group` INT NOT NULL ,
`id_country` INT NOT NULL ,
`id_state` INT NOT NULL ,
`id_tax` INT NOT NULL ,
`state_behavior` INT NOT NULL ,
PRIMARY KEY ( `id_tax_rules_group`, `id_country` , `id_state` )
) ENGINE = MYISAM ;
*/

class TaxRuleCore extends ObjectModel
{
    public $id_tax_rules_group;
    public $id_country;
    public $id_state;
    public $id_tax;
    public $state_behavior;

 	protected 	$fieldsRequired = array('id_tax_rules_group', 'id_country', 'id_tax');
 	protected 	$fieldsValidate = array('id_tax_rules_group' => 'isUnsignedId', 'id_country' => 'isUnsignedId', 'id_state' => 'isUnsignedId', 'id_tax' => 'isUnsignedId', 'state_behavior' => 'isUnsignedInt');

	protected 	$table = 'tax_rule';
	protected 	$identifier = 'id_tax_rule';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_tax_rules_group'] = (int)($this->id_tax_rules_group);
        $fields['id_country'] = (int)$this->id_country;
        $fields['id_state'] = (int)$this->id_state;
        $fields['state_behavior'] = (int)$this->state_behavior;
		$fields['id_tax'] = (int)($this->id_tax);
		return $fields;
	}

    public static function deleteByGroupId($id_group)
    {
        if (empty($id_group))
            die(Tools::displayError());

        return Db::getInstance()->Execute('
        DELETE FROM `'._DB_PREFIX_.'tax_rule`
        WHERE `id_tax_rules_group` = '.(int)$id_group
        );
    }

    public static function getTaxRulesByGroupId($id_group)
    {
        if (empty($id_group))
            die(Tools::displayError());

        $results = Db::getInstance()->ExecuteS('
        SELECT *
        FROM `'._DB_PREFIX_.'tax_rule`
        WHERE `id_tax_rules_group` = '.(int)$id_group
        );

        $res = array();
        foreach ($results AS $row)
        {
            $res[$row['id_country']][$row['id_state']] = array('state_behavior' => $row['state_behavior'], 'id_tax' => $row['id_tax']);
        }

        return $res;
    }
}

