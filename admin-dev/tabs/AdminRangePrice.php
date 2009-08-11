<?php

/**
  * Price ranges tab for admin panel, AdminRangePrice.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

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
			$this->_errors[] = Tools::displayError('invalid range');
		else
			parent::postProcess();
	}

	public function displayForm()
	{
		global $currentIndex;

		$obj = $this->loadObject(true);
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/t/AdminRangePrice.gif" />'.$this->l('Price ranges').'</legend>
				<label>'.$this->l('Carrier:').'</label>
				<div class="margin-form">
					<select name="id_carrier">';
			$carriers = Carrier::getCarriers(intval(Configuration::get('PS_LANG_DEFAULT')));
			$id_carrier = Tools::getValue('id_carrier', $obj->id_carrier);
			foreach ($carriers AS $carrier)
				echo '<option value="'.intval($carrier['id_carrier']).'"'.(($carrier['id_carrier'] == $id_carrier) ? ' selected="selected"' : '').'>'.$carrier['name'].'</option><sup>*</sup>';
			echo '
					</select>
					<p style="clear: both;">'.$this->l('Carrier to which this range will be applied').'</p>
				</div>
				<label>'.$this->l('From:').' </label>
				<div class="margin-form">
					'.$currency->getSign('left').'<input type="text" size="4" name="delimiter1" value="'.htmlentities($this->getFieldValue($obj, 'delimiter1'), ENT_COMPAT, 'UTF-8').'" />'.$currency->getSign('right').'<sup>*</sup>
					<p style="clear: both;">'.$this->l('Range start (included)').'</p>
				</div>
				<label>'.$this->l('To:').' </label>
				<div class="margin-form">
					'.$currency->getSign('left').'<input type="text" size="4" name="delimiter2" value="'.htmlentities($this->getFieldValue($obj, 'delimiter2'), ENT_COMPAT, 'UTF-8').'" />'.$currency->getSign('right').'<sup>*</sup>
					<p style="clear: both;">'.$this->l('Range end (excluded)').'</p>
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
