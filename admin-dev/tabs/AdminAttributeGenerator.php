<?php

/**
  * Attribute generator tab for admin panel, AdminAttributeGenerator.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

@ini_set('max_execution_time', 3600);
include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminAttributeGenerator extends AdminTab
{
	private $combinations = array();
	private $product;

	private function addAttribute($arr, $price = 0, $weight = 0)
	{
		foreach ($arr AS $attr)
		{
			$price += floatval($_POST['price_impact'][intval($attr)]);
			$weight += floatval($_POST['weight_impact'][intval($attr)]);
		}
		if ($this->product->id)
		{
			return (array(
					'id_product' => intval($this->product->id),
					'price' => floatval($price),
					'weight' => floatval($weight),
					'ecotax' => 0,
					'quantity' => intval($_POST['quantity']),
					'reference' => pSQL($_POST['reference']),
					'default_on' => 0));
		}
		return array();
	}

	static private function createCombinations($list)
	{
		if (sizeof($list) <= 1)
			return sizeof($list) ? array_map(create_function('$v', 'return (array($v));'), $list[0]) : $list;
		$res = array();
		$first = array_pop($list);
		foreach ($first AS $attribute)
		{
			$tab = self::createCombinations($list);
			foreach ($tab AS $toAdd)
				$res[] = is_array($toAdd) ? array_merge($toAdd, array($attribute)) : array($toAdd, $attribute);
		}
		return $res;
	}

	public function postProcess()
	{
		global $currentIndex;

		$this->product = new Product(intval(Tools::getValue('id_product')));
		if (isset($_POST['generate']))
		{
			if (!is_array(Tools::getValue('options')))
				$this->_errors[] = Tools::displayError('You need to choose at least 1 attribute.');
			else
			{
				$tab = array_values($_POST['options']);
				if (sizeof($tab) AND  Validate::isLoadedObject($this->product))
				{
                    self::setAttributesImpacts($this->product->id, $tab);
					$this->combinations = array_values(self::createCombinations($tab));
					$values = array_values(array_map(array($this, 'addAttribute'), $this->combinations));
					$this->product->deleteProductAttributes();
					$res = $this->product->addProductAttributeMultiple($values);
					$this->product->addAttributeCombinationMultiple($res, $this->combinations);
				}
				else
					$this->_errors[] = Tools::displayError('Unable to initialize parameters, combinations is missing or object cannot be load.');
			}
		}
		elseif (isset($_POST['back']))
			Tools::redirectAdmin($currentIndex.'&id_product='.intval(Tools::getValue('id_product')).'&id_category='.intval(Tools::getValue('id_category')).'&addproduct'.'&tabs=2&token='.Tools::getValue('token'));
		parent::postProcess();
	}

	static private function displayAndReturnAttributeJs()
	{
		global $cookie;

		$attributes = Attribute::getAttributes(intval($cookie->id_lang), true);
		$attributeJs = array();
		foreach ($attributes AS $k => $attribute)
			$attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
		echo '
		<script type="text/javascript">
			var attrs = new Array();
			attrs[0] = new Array(0, \'---\');';
		foreach ($attributeJs AS $idgrp => $group)
		{
			echo '
				attrs['.$idgrp.'] = new Array(0, \'---\' ';
			foreach ($group AS $idattr => $attrname)
				echo ', '.$idattr.', \''.addslashes(($attrname)).'\'';
			echo ');';
		}
		echo '
		</script>';
		return $attributeJs;
	}

	private function displayGroupSelect($attributeJs, $attributesGroups)
	{
			echo '
					<div>
						<select multiple name="attributes[]" id="attribute_group" style="width: 200px; height: 350px;">';
			foreach ($attributesGroups AS $k => $attributeGroup)
			{
				$idGroup = $attributeGroup['id_attribute_group'];
				if (isset($attributeJs[$idGroup]))
				{
					echo '
								<optgroup onclick="target=getE(\'group_'.$idGroup.'\');toggle(target,target.style.display == \'none\');" name="title_'.$idGroup.'" id="group_title_'.$idGroup.'" label="'.htmlspecialchars(stripslashes($attributeGroup['name'])).'">

						<optgroup style="display: none;" name="'.$idGroup.'" id="group_'.$idGroup.'">';
					foreach ($attributeJs[$idGroup] AS $k => $v)
						echo '
									<option name="'.$k.'" id="attr_'.$k.'" value="'.$v.'">'.$v.'</option>';
					echo '</optgroup>
							</optgroup>';
				}
			}
			echo '
							</select>
						</div>
			';

	}

    static private function setAttributesImpacts($id_product, $tab)
    {
        $attributes = array();
        foreach ($tab AS $group)
            foreach ($group AS $attribute)
                $attributes[] = '('.intval($id_product).', '.intval($attribute).', '.floatval($_POST['price_impact'][intval($attribute)]).', '.floatval($_POST['weight_impact'][intval($attribute)]).')';
        return Db::getInstance()->Execute(
        'INSERT INTO `'._DB_PREFIX_.'attribute_impact` (`id_product`, `id_attribute`, `price`, `weight`)
        VALUES '.implode(',', $attributes).'
        ON DUPLICATE KEY UPDATE `price`=VALUES(price), `weight`=VALUES(weight)'
		);
    }

    static private function getAttributesImpacts($id_product)
    {
        $tab = array();
        $result = Db::getInstance()->ExecuteS(
        'SELECT ai.`id_attribute`, ai.`price`, ai.`weight`
		FROM `'._DB_PREFIX_.'attribute_impact` ai
		WHERE ai.`id_product` = '.intval($id_product));
        if (!$result)
            return array();
        foreach ($result AS $impact)
        {
            $tab[$impact['id_attribute']]['price'] = floatval($impact['price']);
            $tab[$impact['id_attribute']]['weight'] = floatval($impact['weight']);
        }
        return $tab;
    }

	private function displayGroupeTable($attributeJs, $attributesGroups)
	{
		global $cookie;

		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$combinationsGroups = $this->product->getAttributesGroups(intval($cookie->id_lang));
		$attributes = array();
        $impacts = self::getAttributesImpacts($this->product->id);
		foreach ($combinationsGroups AS &$combination)
        {
            $target = &$attributes[$combination['id_attribute_group']][$combination['id_attribute']];
			$target = $combination;
            if (isset($impacts[$combination['id_attribute']]))
            {
                $target['price'] = $impacts[$combination['id_attribute']]['price'];
                $target['weight'] = $impacts[$combination['id_attribute']]['weight'];
            }
        }

		foreach ($attributesGroups AS $k => $attributeGroup)
		{
			$idGroup = $attributeGroup['id_attribute_group'];
			if (isset($attributeJs[$idGroup]))
			{
				echo '
				<br style="clear: both;"/>
				<table class="table" cellpadding="0" cellspacing="0" align="left" style="margin-bottom: 10px; display: none;">
					<thead>
						<tr>
							<th id="tab_h1" style="width: 250px">'.htmlspecialchars(stripslashes($attributeGroup['name'])).'</th>
							<th id="tab_h2" style="width: 150px">'.$this->l('Price impact').' ('.$currency->sign.')'.' <sup>*</sup></th>
							<th style="width: 150px">'.$this->l('Weight impact').' (kg)'.'</th>
						</tr>
					</thead>
					<tbody id="table_'.$idGroup.'" name="result_table">
					</tbody>
				</table>';
				if (isset($attributes[$idGroup]))
					foreach ($attributes[$idGroup] AS $k => $attribute)
						echo '<script type="text/javascript">getE(\'table_\' + '.$idGroup.').appendChild(create_attribute_row('.$k.', '.$idGroup.', \''.addslashes($attribute['attribute_name']).'\', '.$attribute['price'].', '.$attribute['weight'].'));toggle(getE(\'table_\' + '.$idGroup.').parentNode, true);</script>';
			}
		}
		echo '<p><sup>*</sup> '.$this->l('tax included').'</p>';
	}

	public function displayForm()
	{
		global $currentIndex, $cookie;

		$jsAttributes = self::displayAndReturnAttributeJs();
		$attributesGroups = AttributeGroup::getAttributesGroups(intval($cookie->id_lang));
		$this->product = new Product(intval(Tools::getValue('id_product')));
		if (isset($_POST['generate']) AND !sizeof($this->_errors))
			echo '
			<div class="module_confirmation conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" style="margin-right:5px; float:left;" />
				'.sizeof($this->combinations).' '.$this->l('product(s) successfully created.').'
			</div>';
		echo '
			<script type="text/javascript" src="../js/attributesBack.js"></script>
			<form enctype="multipart/form-data" method="post" id="generator" action=""'.$currentIndex.'&id_category='.intval(Tools::getValue('id_category')).'token='.Tools::getValue('token').'">
				<fieldset style="margin-bottom: 35px;"><legend><img src="../img/admin/asterisk.gif" />'.$this->l('Attributes generator').'</legend>'.
				$this->l('Add or modify attributes for this product:').'
					<br /><br />
                    ';
        echo '
                <div style="padding-top:10px; float: left; width: 570px;">
                    <div style="float:left;">
						<label>'.$this->l('Quantity:').'</label>
						<div class="margin-form">
							<input type="text" size="20" name="quantity" value="1"/>
						</div>
						<label>'.$this->l('Reference:').'</label>
						<div class="margin-form">
							<input type="text" size="20" name="reference" value="'.$this->product->reference.'"/>
						</div>
					</div>
					<div style="float:left; text-align:center; margin-left:20px;">
                        <input type="submit" class="button" style="margin-bottom:5px;" name="generate" value="'.$this->l('Generate').'" /><br />
                        <input type="submit" class="button" name="back" value="'.$this->l('Back to product').'" />
					</div>
                    <br style="clear:both;" />
                    <div style="margin-top: 15px;">';
            self::displayGroupeTable($jsAttributes, $attributesGroups);
            echo '
                    </div>
                </div>
            <div style="float: left; margin-left: 60px;">
            ';
            self::displayGroupSelect($jsAttributes, $attributesGroups);
        echo '
				<div>
                    <input class="button" type="button" style="margin-left: 20px;" value="'.$this->l('Add').'" class="button" onclick="add_attr_multiple();" />
                    <input class="button" type="button" style="margin-left: 20px;" value="'.$this->l('Delete').'" class="button" onclick="del_attr_multiple();" />
				</div>
			</div>
			<br />
			</fieldset>
		</form>';
	}
}

?>