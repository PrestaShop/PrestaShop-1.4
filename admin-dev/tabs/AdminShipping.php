<?php

/**
  * Shipping tab for admin panel, AdminShipping.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminShipping extends AdminTab
{
	private $_fieldsHandling;

	public function __construct()
	{
	 	$this->table = 'delivery';
 		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

 		$this->_fieldsHandling = array(
		'PS_SHIPPING_HANDLING' => array('title' => $this->l('Handling charges'), 'suffix' => $currency, 'validation' => 'isPrice', 'cast' => 'floatval'),
		'PS_SHIPPING_FREE_PRICE' => array('title' => $this->l('Free shipping starts at'), 'suffix' => $currency, 'validation' => 'isPrice', 'cast' => 'floatval'),
		'PS_SHIPPING_FREE_WEIGHT' => array('title' => $this->l('Free shipping starts at'), 'suffix' => Configuration::get('PS_WEIGHT_UNIT'), 'validation' => 'isUnsignedFloat', 'cast' => 'floatval'),
		'PS_SHIPPING_METHOD' => array('title' => $this->l('Billing'), 'validation' => 'isBool', 'cast' => 'intval'));

		parent::__construct();
	}

	public function postProcess()
	{
		global $currentIndex;

		/* Handling settings */
		if (isset($_POST['submitHandling'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
			{
			 	/* Check required fields */
				foreach ($this->_fieldsHandling AS $field => $values)
					if (($value = Tools::getValue($field)) == false AND (string)$value != '0')
						$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is required');

				/* Check field validity */
				foreach ($this->_fieldsHandling AS $field => $values)
					if (Tools::getValue($field))
					{
						$function = $values['validation'];
						if (!Validate::$function(Tools::getValue($field)))
							$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is invalid');
					}

				/* Update configuration values */
				if (!sizeof($this->_errors))
				{
					foreach ($this->_fieldsHandling AS $field => $values)
					{
						$function = $values['cast'];
						Configuration::updateValue($field, call_user_func($function, Tools::getValue($field)));
					}

					Tools::redirectAdmin($currentIndex.'&conf=6'.'&token='.$this->token);
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		/* Shipping fees */
		elseif (isset($_POST['submitFees'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
			{
				if (($id_carrier = intval(Tools::getValue('id_carrier'))) AND $id_carrier == ($id_carrier2 = intval(Tools::getValue('id_carrier2'))))
				{
					$carrier = new Carrier($id_carrier);
					if (Validate::isLoadedObject($carrier))
					{
					 	/* Get configuration values */
						$shipping_method = intval(Configuration::get('PS_SHIPPING_METHOD'));

						$rangeTable = $shipping_method ? 'range_weight' : 'range_price';
						$carrier->deleteDeliveryPrice($rangeTable);

						/* Build prices list */
						$priceList = '';
						foreach ($_POST AS $key => $value)
							if (strstr($key, 'fees_'))
							{
								$tmpArray = explode('_', $key);
								$priceList .= '('.($shipping_method == 0 ? intval($tmpArray[2]) : 'NULL').',
								'.($shipping_method == 1 ? intval($tmpArray[2]) : 'NULL').', '.$carrier->id.',
								'.intval($tmpArray[1]).', '.number_format(abs($value), 2, '.', '').'),';
								unset($tmpArray);
							}
						$priceList = rtrim($priceList, ',');

						/* Update delivery prices */
						$carrier->addDeliveryPrice($priceList);
						Tools::redirectAdmin($currentIndex.'&conf=6'.'&token='.$this->token);
					}
					else
						$this->_errors[] = Tools::displayError('an error occurred while updating fees (cannot load carrier object)');
				}
				elseif (isset($id_carrier2))
				{
					$_POST['id_carrier'] = $id_carrier2;
				}
				else
					$this->_errors[] = Tools::displayError('an error occurred while updating fees (cannot load carrier object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}

	public function display()
	{
		$this->displayFormHandling();
		$this->displayFormFees();
	}

	public function displayFormHandling()
	{
		global $currentIndex;

		$confKeys = $this->_fieldsHandling;
		foreach ($confKeys AS $key => $confKey)
			$getConf[] = $key;
		$confValues = Configuration::getMultiple($getConf);
		unset($confKeys['PS_SHIPPING_METHOD']);

		echo '
		<form action="'.$currentIndex.'&submitHandling'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
			<fieldset>
				<legend><img src="../img/admin/delivery.gif" />'.$this->l('Handling').'</legend>';

		foreach ($confKeys AS $key => $confKey)
		{
			$postValue = Tools::getValue($key);
			$sign_left = (is_object($confKey['suffix']) ? $confKey['suffix']->getSign('left') : '');
			$sign_right = (is_object($confKey['suffix']) ? $confKey['suffix']->getSign('right') : (is_string($confKey['suffix']) ? '&nbsp;'.$confKey['suffix'] : ''));
			echo '
			<label class="clear">'.$confKey['title'].':</label>
			<div class="margin-form">';
			echo $sign_left;
			echo '<input size="5" type="text" name="'.$key.'" value="'.(($postValue != false OR (string)$postValue == '0') ? $postValue : $confValues[$key]).'" />';
			echo $sign_right;
			echo '</div>';
		}

		echo '
				<label class="clear">'.$this->l('Billing:').' </label>
				<div class="margin-form">
					<input type="radio" name="PS_SHIPPING_METHOD" value="0" id="total_price"
					'.((isset($confValues['PS_SHIPPING_METHOD']) AND $confValues['PS_SHIPPING_METHOD'] == 0) ? 'checked="checked"' : '').'/>
					<label class="t" for="total_price"> '.$this->l('According to total price').'</label><br />
					<input type="radio" name="PS_SHIPPING_METHOD" value="1" id="total_weight"
					'.((!isset($confValues['PS_SHIPPING_METHOD']) OR $confValues['PS_SHIPPING_METHOD'] == 1) ? 'checked="checked"' : '').'/>
					<label class="t" for="total_weight"> '.$this->l('According to total weight').'</label>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitHandling'.$this->table.'" class="button" />
				</div>
			</fieldset>
		</form>';
	}

	public function displayFormFees()
	{
		global $currentIndex;

		echo '<br /><br />
		<h2>'.$this->l('Fees by carrier, geographical zone, and ranges').'</h2>
		<form action="'.$currentIndex.'&token='.$this->token.'" id="fees" name="fees" method="post" class="width2">
			<fieldset>
				<legend><img src="../img/admin/delivery.gif" />'.$this->l('Fees').'</legend>
				<b>'.$this->l('Carrier:').' </b>
				<select name="id_carrier2" onchange="document.fees.submit();">';
		$carriers = Carrier::getCarriers(intval(Configuration::get('PS_LANG_DEFAULT')));
		$id_carrier = Tools::getValue('id_carrier') ? intval(Tools::getValue('id_carrier')) : intval($carriers[0]['id_carrier']);
		$carrierSelected = new Carrier($id_carrier);
		foreach ($carriers AS $carrier)
			echo '<option value="'.intval($carrier['id_carrier']).'"'.(($carrier['id_carrier'] == $id_carrier) ? ' selected="selected"' : '').'>'.$carrier['name'].'</option>';
		echo '
				</select><br />
				<table class="table space" cellpadding="0" cellspacing="0">
					<tr>
						<th>'.$this->l('Zone / Range').'</th>';

				$shipping_method = intval(Configuration::get('PS_SHIPPING_METHOD'));
				$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
				$rangeObj = $shipping_method ? new RangeWeight() : new RangePrice();
				$rangeTable = $shipping_method ? 'range_weight' : 'range_price';
				$rangeIdentifier = 'id_'.$rangeTable;
				$ranges = $rangeObj->getRanges($id_carrier);
				$suffix = $shipping_method ? Configuration::get('PS_WEIGHT_UNIT') : $currency->sign;
				$delivery = Carrier::getDeliveryPriceByRanges($rangeTable);
				foreach ($delivery AS $deliv)
					$deliveryArray[$deliv['id_zone']][$deliv['id_carrier']][$deliv[$rangeIdentifier]] = $deliv['price'];
				foreach ($ranges AS $range)
					echo '<th style="font-size: 11px;">'.floatval($range['delimiter1']).$suffix.' '.$this->l('to').' '.floatval($range['delimiter2']).$suffix.'</th>';
				echo '</tr>';

				$zones = Zone::getZones(true);
				if (sizeof($ranges))
					foreach ($zones AS $zone)
					{
						if (!$carrierSelected->getZone($zone['id_zone']))
							continue ;
						echo '
						<tr>
							<th style="height: 30px;">'.$zone['name'].'</th>';
						foreach ($ranges AS $range)
						{
							if (isset($deliveryArray[$zone['id_zone']][$id_carrier][$range[$rangeIdentifier]]))
								$price = $deliveryArray[$zone['id_zone']][$id_carrier][$range[$rangeIdentifier]];
							else
								$price = '0.00';
							echo '<td class="center">'.$currency->getSign('left').'<input type="text" name="fees_'.$zone['id_zone'].'_'.$range[$rangeIdentifier].'" value="'.$price.'" style="width: 45px;" />'.$currency->getSign('right').'</td>';
						}
						echo '
						</tr>';
					}
				echo '
					<tr>
						<td colspan="'.(sizeof($ranges) + 1).'" class="center" style="border-bottom: none; height: 40px;">
							<input type="hidden" name="submitFees'.$this->table.'" value="1" />
					';
				if (sizeof($ranges))
					echo '	<input type="submit" value="'.$this->l('   Save   ').'" class="button" />';
				else
					echo $this->l('No ranges set for this carrier');
				echo '
						</td>
					</tr>';
				echo '
				</table>
			</fieldset>
			<input type="hidden" name="id_carrier" value="'.$id_carrier.'" />
		</form>';
	}
}

?>