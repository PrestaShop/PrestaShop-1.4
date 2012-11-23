<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
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
	
	public function displayListContent($token = null)
	{
		foreach ($this->_list as $key => $list)
			if ($list['carrier_name'] == '0')
				$this->_list[$key]['carrier_name'] = Configuration::get('PS_SHOP_NAME');
		parent::displayListContent($token);
	}

	public function postProcess()
	{
		if (isset($_POST['submitAdd'.$this->table]))
		{
			$delimiter1 = Tools::getValue('delimiter1');
			$delimiter2 = Tools::getValue('delimiter2');
			
			if ($delimiter1 >= $delimiter2)
				$this->_errors[] = Tools::displayError('Invalid range, "From" must be lower than "To"');

			/* Check that a similar range does not exist yet for this carrier */
			if (!count($this->_errors) && $ranges = RangePrice::getRanges((int)Tools::getValue('id_carrier')))
				foreach ($ranges as $range)
					if (!($delimiter2 <= $range['delimiter1'] || $delimiter1 >= $range['delimiter2']))
					{
						$this->_errors[] = Tools::displayError('Invalid range, this range is overlapping an existing range');
						break;
					}
		}

		parent::postProcess();
	}

	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();

		if (!($obj = $this->loadObject(true)))
			return;
		$currency = new Currency(_PS_CURRENCY_DEFAULT_);

		$carrierArray = array();
		$carriers = Carrier::getCarriers((int)(_PS_LANG_DEFAULT_), true, false, false, null, Carrier::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		$id_carrier = Tools::getValue('id_carrier', $obj->id_carrier);
		foreach ($carriers as $carrier)
			if (!$carrier['is_free'])
				$carrierArray[] = '<option value="'.(int)($carrier['id_carrier']).'"'.(($carrier['id_carrier'] == $id_carrier) ? ' selected="selected"' : '').'>'.$carrier['name'].'</option><sup>*</sup>';

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/t/AdminRangePrice.gif" />'.$this->l('Price ranges').'</legend>
				<label>'.$this->l('Carrier').'</label>
				<div class="margin-form">';
			if (count($carrierArray))
			{
				echo '<select name="id_carrier">';
				foreach ($carrierArray as $carrierOption)
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
					'.$currency->getSign('left').'<input type="text" size="4" name="delimiter1" value="'.htmlentities($this->getFieldValue($obj, 'delimiter1'), ENT_COMPAT, 'UTF-8').'" />'.$currency->getSign('right').'<sup>*</sup>
					<p class="clear">'.$this->l('Start range (included)').'</p>
				</div>
				<label>'.$this->l('To:').' </label>
				<div class="margin-form">
					'.$currency->getSign('left').'<input type="text" size="4" name="delimiter2" value="'.htmlentities($this->getFieldValue($obj, 'delimiter2'), ENT_COMPAT, 'UTF-8').'" />'.$currency->getSign('right').'<sup>*</sup>
					<p class="clear">'.$this->l('End range (excluded)').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}