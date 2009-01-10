<?php

class Loyalty extends Module
{
	function __construct()
	{
		$this->name = 'loyalty';
		$this->tab = 'Tools';
		$this->version = '1.6';

		parent::__construct();

		$this->displayName = $this->l('Customer loyalty and rewards');
		$this->description = $this->l('Provide a loyalty program to your customers');

		$path = dirname(__FILE__);
		if (strpos(__FILE__, 'Module.php') !== false)
			$path .= '/../modules/'.$this->name;
		include_once($path.'/LoyaltyModule.php');
		include_once($path.'/LoyaltyStateModule.php');
	}

	private function instanceDefaultStates()
	{
		// recover default loyalty status save at module installation
		$this->loyaltyStateDefault = new LoyaltyStateModule(LoyaltyStateModule::getDefaultId());
		$this->loyaltyStateValidation = new LoyaltyStateModule(LoyaltyStateModule::getValidationId());
		$this->loyaltyStateCancel = new LoyaltyStateModule(LoyaltyStateModule::getCancelId());
		$this->loyaltyStateConvert = new LoyaltyStateModule(LoyaltyStateModule::getConvertId());
		$this->loyaltyStateNoneAward = new LoyaltyStateModule(LoyaltyStateModule::getNoneAwardId());
	}

	function install()
	{
		if (!parent::install()
				OR !$this->installDB()
				OR !$this->registerHook('extraRight')
				OR !$this->registerHook('updateOrderStatus')
				OR !$this->registerHook('newOrder')
				OR !$this->registerHook('adminCustomers')
				OR !$this->registerHook('shoppingCart')
				OR !$this->registerHook('orderReturn')
				OR !$this->registerHook('cancelProduct')
				OR !$this->registerHook('customerAccount')
				OR !Configuration::updateValue('PS_LOYALTY_POINT_VALUE', '0.20')
				OR !Configuration::updateValue('PS_LOYALTY_POINT_RATE', '10')
				OR !Configuration::updateValue('PS_LOYALTY_NONE_AWARD', '1')
				OR !Configuration::updateValue('PS_LOYALTY_VOUCHER_DETAILS', array(intval(Configuration::get('PS_LANG_DEFAULT')) => 'Loyalty voucher'))	
			)
			return false;
		/* This hook is optional */
		$this->registerHook('myAccountBlock');
		if (!LoyaltyStateModule::insertDefaultData())
			return false;
		return true;
	}

	function installDB()
	{
		Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'loyalty` (
			`id_loyalty` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_loyalty_state` INT UNSIGNED NOT NULL DEFAULT 1,
			`id_customer` INT UNSIGNED NOT NULL,
			`id_order` INT UNSIGNED DEFAULT NULL,
			`id_discount` INT UNSIGNED DEFAULT NULL,
			`points` INT NOT NULL DEFAULT 0,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_loyalty`),
			INDEX index_loyalty_loyalty_state (`id_loyalty_state`),
			INDEX index_loyalty_order (`id_order`),
			INDEX index_loyalty_discount (`id_discount`),
			INDEX index_loyalty_customer (`id_customer`)
		) DEFAULT CHARSET=utf8 ;');
		Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'loyalty_history` (
			`id_loyalty_history` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_loyalty` INT UNSIGNED DEFAULT NULL,
			`id_loyalty_state` INT UNSIGNED NOT NULL DEFAULT 1,
			`points` INT NOT NULL DEFAULT 0,
			`date_add` DATETIME NOT NULL,
			PRIMARY KEY (`id_loyalty_history`),
			INDEX `index_loyalty_history_loyalty` (`id_loyalty`),
			INDEX `index_loyalty_history_loyalty_state` (`id_loyalty_state`)
		) DEFAULT CHARSET=utf8 ;');
		Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'loyalty_state` (
			`id_loyalty_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_order_state` INT UNSIGNED DEFAULT NULL,
			PRIMARY KEY (`id_loyalty_state`),
			INDEX index_loyalty_state_order_state (`id_order_state`)
		) DEFAULT CHARSET=utf8 ;');
		Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'loyalty_state_lang` (
			`id_loyalty_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_lang` INT UNSIGNED NOT NULL,
			`name` varchar(64) NOT NULL,
			UNIQUE KEY `index_unique_loyalty_state_lang` (`id_loyalty_state`,`id_lang`)
		) DEFAULT CHARSET=utf8 ;');
		return true;
	}

	function uninstall()
	{
		if (!parent::uninstall()
				OR !$this->uninstallDB()
				OR !Configuration::deleteByName('PS_LOYALTY_POINT_VALUE')
				OR !Configuration::deleteByName('PS_LOYALTY_POINT_RATE')
				OR !Configuration::deleteByName('PS_LOYALTY_NONE_AWARD')
				OR !Configuration::deleteByName('PS_LOYALTY_VOUCHER_DETAILS')
			)
			return false;
		return true;
	}

	function uninstallDB()
	{
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'loyalty`;');
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'loyalty_state`;');
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'loyalty_state_lang`;');
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'loyalty_history`;');
		return true;
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submitLoyalty'))
		{
			$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
			$languages = Language::getLanguages();
			
			Configuration::updateValue('PS_LOYALTY_POINT_VALUE', floatval(Tools::getValue('point_value')));
			Configuration::updateValue('PS_LOYALTY_POINT_RATE', floatval(Tools::getValue('point_rate')));
			Configuration::updateValue('PS_LOYALTY_NONE_AWARD', intval(Tools::getValue('PS_LOYALTY_NONE_AWARD')));
			$this->loyaltyStateValidation->id_order_state = intval(Tools::getValue('id_order_state_validation'));
			$this->loyaltyStateCancel->id_order_state = intval(Tools::getValue('id_order_state_cancel'));
			
			$arrayVoucherDetails = array();
			foreach ($languages AS $language)
			{
				$arrayVoucherDetails[intval($language['id_lang'])] = Tools::getValue('voucher_details_'.intval($language['id_lang']));
				$this->loyaltyStateDefault->name[intval($language['id_lang'])] = Tools::getValue('default_loyalty_state_'.intval($language['id_lang']));
				$this->loyaltyStateValidation->name[intval($language['id_lang'])] = Tools::getValue('validation_loyalty_state_'.intval($language['id_lang']));
				$this->loyaltyStateCancel->name[intval($language['id_lang'])] = Tools::getValue('cancel_loyalty_state_'.intval($language['id_lang']));
				$this->loyaltyStateConvert->name[intval($language['id_lang'])] = Tools::getValue('convert_loyalty_state_'.intval($language['id_lang']));
				$this->loyaltyStateNoneAward->name[intval($language['id_lang'])] = Tools::getValue('none_award_loyalty_state_'.intval($language['id_lang']));
			}
			if (empty($arrayVoucherDetails[$defaultLanguage]))
				$arrayVoucherDetails[$defaultLanguage] = ' ';
			Configuration::updateValue('PS_LOYALTY_VOUCHER_DETAILS', $arrayVoucherDetails);
			
			if (empty($this->loyaltyStateDefault->name[$defaultLanguage]))
				$this->loyaltyStateDefault->name[$defaultLanguage] = ' ';
			$this->loyaltyStateDefault->save();
			
			if (empty($this->loyaltyStateValidation->name[$defaultLanguage]))
				$this->loyaltyStateValidation->name[$defaultLanguage] = ' ';
			$this->loyaltyStateValidation->save();
			
			if (empty($this->loyaltyStateCancel->name[$defaultLanguage]))
				$this->loyaltyStateCancel->name[$defaultLanguage] = ' ';
			$this->loyaltyStateCancel->save();
			
			if (empty($this->loyaltyStateConvert->name[$defaultLanguage]))
				$this->loyaltyStateConvert->name[$defaultLanguage] = ' ';
			$this->loyaltyStateConvert->save();
			
			if (empty($this->loyaltyStateNoneAward->name[$defaultLanguage]))
				$this->loyaltyStateNoneAward->name[$defaultLanguage] = ' ';
			$this->loyaltyStateNoneAward->save();

			echo $this->displayConfirmation($this->l('Settings updated.'));
		}
	}

	public function getContent()
	{
		global $cookie;

		$this->instanceDefaultStates();
		$this->_postProcess();

		$order_states = OrderState::getOrderStates($cookie->id_lang);
		$currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$languageIds = 'voucher_details¤default_loyalty_state¤none_award_loyalty_state¤convert_loyalty_state¤validation_loyalty_state¤cancel_loyalty_state';

		$html = '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<h2>'.$this->l('Loyalty Program').'</h2>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend>'.$this->l('Settings').'</legend>
				
				<label>'.$this->l('Ratio').'</label>
				<div class="margin-form">
					<input type="text" size="2" id="point_rate" name="point_rate" value="'.floatval(Configuration::get('PS_LOYALTY_POINT_RATE')).'" /> '.$currency->sign.'
					<label for="point_rate" class="t"> = '.$this->l('1 reward point').'.</label>
					<br />
					<label for="point_value" class="t">'.$this->l('1 point = ').'</label>
					<input type="text" size="2" name="point_value" id="point_value" value="'.floatval(Configuration::get('PS_LOYALTY_POINT_VALUE')).'" /> '.$currency->sign.'
					<label for="point_value" class="t">'.$this->l('for the discount').'.</label>
				</div>
				<div class="clear"></div>
				<label>'.$this->l('Voucher details').'</label>
				<div class="margin-form">';
		foreach ($languages as $language)
			$html .= '
					<div id="voucher_details_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="voucher_details_'.$language['id_lang'].'" value="'.Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', intval($language['id_lang'])).'" />
					</div>';
		$html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'voucher_details', true);
		$html .= '	</div>
				<div class="clear" style="margin-top: 20px"></div>
				<label>'.$this->l('Allow discounts').' </label>
				<div class="margin-form">
					<input type="radio" name="PS_LOYALTY_NONE_AWARD" id="PS_LOYALTY_NONE_AWARD_on" value="1" '.(Configuration::get('PS_LOYALTY_NONE_AWARD') ? 'checked="checked" ' : '').'/>
					<label class="t" for="PS_LOYALTY_NONE_AWARD_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Yes').'" /></label>
					<input type="radio" name="PS_LOYALTY_NONE_AWARD" id="PS_LOYALTY_NONE_AWARD_off" value="0" '.(!Configuration::get('PS_LOYALTY_NONE_AWARD') ? 'checked="checked" ' : '').'/>
					<label class="t" for="PS_LOYALTY_NONE_AWARD_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('No').'" /></label>
					</div>
				<div class="clear"></div>
				<label>'.$this->l('Points are awarded when the order is').'</label>
				<div class="margin-form" style="margin-top:10px">
					<select id="id_order_state_validation" name="id_order_state_validation">';
		foreach ($order_states as $order_state)
		{
			$html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
			if (intval($this->loyaltyStateValidation->id_order_state) == $order_state['id_order_state'] )
				$html .= ' selected="selected"';
			$html .= '>' . $order_state['name'] . '</option>';
		}
		$html .= '</select>
				</div>
				<div class="clear"></div>
				<label>'.$this->l('Points are canceled when the order is').'</label>
				<div class="margin-form" style="margin-top:10px">
					<select id="id_order_state_cancel" name="id_order_state_cancel">';
		foreach ($order_states as $order_state)
		{
			$html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
			if (intval($this->loyaltyStateCancel->id_order_state) == $order_state['id_order_state'] )
				$html .= ' selected="selected"';
			$html .= '>' . $order_state['name'] . '</option>';
		}
		$html .= '</select>
				</div>
				<div class="clear"></div>
				<h3 style="margin-top:20px">'.$this->l('Loyalty points progression').'</h3>
				<label>'.$this->l('Initial').'</label>
				<div class="margin-form">';
		foreach ($languages as $language)
			$html .= '
					<div id="default_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="default_loyalty_state_'.$language['id_lang'].'" value="'.$this->loyaltyStateDefault->name[intval($language['id_lang'])].'" />
					</div>';
		$html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'default_loyalty_state', true);
		$html .= '	</div>
				<div class="clear"></div>
				<label>'.$this->l('Unavailable').'</label>
				<div class="margin-form">';
		foreach ($languages as $language)
			$html .= '
					<div id="none_award_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="none_award_loyalty_state_'.$language['id_lang'].'" value="'.$this->loyaltyStateNoneAward->name[intval($language['id_lang'])].'" />
					</div>';
		$html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'none_award_loyalty_state', true);
		$html .= '	</div>
				<div class="clear"></div>
				<label>'.$this->l('Converted').'</label>
				<div class="margin-form">';
		foreach ($languages as $language)
			$html .= '
					<div id="convert_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="convert_loyalty_state_'.$language['id_lang'].'" value="'.$this->loyaltyStateConvert->name[intval($language['id_lang'])].'" />
					</div>';
		$html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'convert_loyalty_state', true);
		$html .= '	</div>
				<div class="clear"></div>
				<label>'.$this->l('Validation').'</label>
				<div class="margin-form">';
		foreach ($languages as $language)
			$html .= '
					<div id="validation_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="validation_loyalty_state_'.$language['id_lang'].'" value="'.$this->loyaltyStateValidation->name[intval($language['id_lang'])].'" />
					</div>';
		$html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'validation_loyalty_state', true);
		$html .= '	</div>
				<div class="clear"></div>
				<label>'.$this->l('Canceled').'</label>
				<div class="margin-form">';
		foreach ($languages as $language)
			$html .= '
					<div id="cancel_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="cancel_loyalty_state_'.$language['id_lang'].'" value="'.$this->loyaltyStateCancel->name[intval($language['id_lang'])].'" />
					</div>';
		$html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'cancel_loyalty_state', true);
		$html .= '	</div>
				<div class="clear center">
					<input type="submit" style="margin-top:20px" name="submitLoyalty" id="submitLoyalty" value="'.$this->l('   Save   ').'" class="button" />
				</div>
			</fieldset>
		</form>';
		return $html;
	}

	/* Hook display on product detail */
	public function hookExtraRight($params)
	{
		global $smarty;
		$id_product = Tools::getValue('id_product');
		if (is_numeric($id_product))
		{
			$product = new Product(intval($id_product));
			if (Validate::isLoadedObject($product))
			{
				$points = LoyaltyModule::getNbPointsByProduct($product);
				$smarty->assign(array(
					'points' => $points,
					'voucher' => LoyaltyModule::getVoucherValue($points)
				));
				return $this->display(__FILE__, 'product.tpl');
			}
		}
		return false;
	}

	/* Hook display on customer account page */
	public function hookCustomerAccount($params)
	{
		global $smarty;
		return $this->display(__FILE__, 'my-account.tpl');
	}
	
	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}
	
	/* Catch product returns and substract loyalty points */
	public function hookOrderReturn($params)
	{
		$result = Db::getInstance()->getRow('
		SELECT f.id_loyalty
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.intval($params['orderReturn']->id_customer).'
		AND f.id_order = '.intval($params['orderReturn']->id_order));
		$loyalty = new LoyaltyModule(intval($result['id_loyalty']));
		if (!Validate::isLoadedObject($loyalty))
			return false;
		
		$totalPrice = 0;
		$details = OrderReturn::getOrdersReturnDetail($params['orderReturn']->id);
		foreach ($details as $detail)
		{
			$result = Db::getInstance()->getRow('
			SELECT product_price * (1 + (tax_rate / 100)) AS ttc
			FROM '._DB_PREFIX_.'order_detail od
			WHERE id_order_detail = '.intval($detail['id_order_detail']));
			$totalPrice += $result['ttc'] * $detail['product_quantity'];
		}
		
		$canceledTotal = floor($totalPrice / floatval(Configuration::get('PS_LOYALTY_POINT_RATE')));
		if ($canceledTotal > $loyalty->points)
			$canceledTotal = $loyalty->points;
				
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'loyalty_history` (`id_loyalty`, `id_loyalty_state`, `points`, `date_add`)
		VALUES ('.intval($loyalty->id).', '.intval(LoyaltyStateModule::getCancelId()).', -'.intval($canceledTotal).', NOW())');
		$loyalty->points -= $canceledTotal;
		$loyalty->update();
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'loyalty_history` (`id_loyalty`, `id_loyalty_state`, `points`, `date_add`)
		VALUES ('.intval($loyalty->id).', '.intval(LoyaltyStateModule::getValidationId()).', '.intval($loyalty->points).', NOW())');
	}

	/* Hook display on shopping cart summary */
	public function hookShoppingCart($params)
	{
		if (!Validate::isLoadedObject($params['cart']))
			die (Tools::displayError('Cart parameter is missing.'));
		global $smarty;
		$points = LoyaltyModule::getCartNbPoints($params['cart']);
		$smarty->assign(array(
			'points' => $points,
			'voucher' => LoyaltyModule::getVoucherValue($points)
		));
		return $this->display(__FILE__, 'shopping-cart.tpl');
	}

	/* Hook called when a new order is created */
	public function hookNewOrder($params)
	{
		if (!Validate::isLoadedObject($params['customer']) OR !Validate::isLoadedObject($params['order']))
			die (Tools::displayError('Some parameters are missing.'));
		$loyalty = new LoyaltyModule();
		$loyalty->id_customer = $params['customer']->id;
		$loyalty->id_order = $params['order']->id;
		$loyalty->points = LoyaltyModule::getOrderNbPoints($params['order']);
		if (intval(Configuration::get('PS_LOYALTY_NONE_AWARD')) AND intval($loyalty->points) == 0)
			$loyalty->id_loyalty_state = LoyaltyStateModule::getNoneAwardId();
		else
			$loyalty->id_loyalty_state = LoyaltyStateModule::getDefaultId();
		return $loyalty->save();
	}

	/* Hook called when an order change its status */
	public function hookUpdateOrderStatus($params)
	{
		if (!Validate::isLoadedObject($params['newOrderStatus']))
			die (Tools::displayError('Some parameters are missing.'));
		$newOrder = $params['newOrderStatus'];
		$order = new Order(intval($params['id_order']));
		if ($order AND !Validate::isLoadedObject($order))
			die (Tools::displayError('Incorrect object Order.'));
		$this->instanceDefaultStates();

		if ($newOrder->id == $this->loyaltyStateValidation->id_order_state OR $newOrder->id == $this->loyaltyStateCancel->id_order_state)
		{
			if (!Validate::isLoadedObject($loyalty = new LoyaltyModule(LoyaltyModule::getByOrderId($order->id))))
				return false;
			if (intval(Configuration::get('PS_LOYALTY_NONE_AWARD')) AND $loyalty->id_loyalty_state == LoyaltyStateModule::getNoneAwardId())
				return true;

			if ($newOrder->id == $this->loyaltyStateValidation->id_order_state)
			{
				$loyalty->id_loyalty_state = LoyaltyStateModule::getValidationId();
				if (intval($loyalty->points) < 0)
					$loyalty->points = abs(intval($loyalty->points));
			}
			else if ($newOrder->id == $this->loyaltyStateCancel->id_order_state)
			{
				$loyalty->id_loyalty_state = LoyaltyStateModule::getCancelId();
				$loyalty->points = 0;//-abs(intval($loyalty->points));
			}
			return $loyalty->save();
		}
		return true;
	}

	/* Hook display in tab AdminCustomers on BO */
	public function hookAdminCustomers($params)
	{
		$customer = new Customer(intval($params['id_customer']));
		if ($customer AND !Validate::isLoadedObject($customer))
			die (Tools::displayError('Incorrect object Customer.'));

		$fidelities = LoyaltyModule::getAllByIdCustomer(intval($params['id_customer']), intval($params['cookie']->id_lang));
		$points = intval(LoyaltyModule::getPointsByCustomer(intval($params['id_customer'])));

		$html = '<h2>'.$this->l('Loyalty points').'</h2>
		<table cellspacing="0" cellpadding="0" class="table">
			<tr style="background-color:#F5E9CF; padding: 0.3em 0.1em;">
				<th>'.$this->l('Order').'</th>
				<th>'.$this->l('Date').'</th>
				<th>'.$this->l('Total (without shipping)').'</th>
				<th>'.$this->l('Points').'</th>
				<th>'.$this->l('Points Status').'</th>
			</tr>';
		foreach ($fidelities AS $key => $loyalty)
		{
			$html.= '
			<tr style="background-color: '.($key%2!=0 ? '#FFF6CF' : '#FFFFFF').';">
				<td>'.(intval($loyalty['id']) > 0 ? '<a style="color: #268CCD; font-weight: bold; text-decoration: underline;" href="index.php?tab=AdminOrders&id_order='.$loyalty['id'].'&vieworder&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($params['cookie']->id_employee)).'">'.$this->l('#').sprintf('%06d', $loyalty['id']).'</a>' : '--').'</td>
				<td>'.Tools::displayDate($loyalty['date'], intval($params['cookie']->id_lang)).'</td>
				<td>'.(intval($loyalty['id']) > 0 ? $loyalty['total_without_shipping'] : '--').'</td>
				<td>'.$loyalty['points'].'</td>
				<td>'.$loyalty['state'].'</td>
			</tr>';
		}
		$html.= '
			<tr>
				<td>&nbsp;</td>
				<td colspan="2" class="bold" style="text-align: right;">'.$this->l('Total points available:').'</td>
				<td>'.$points.'</td>
				<td>'.$this->l('Voucher value:').' '.Tools::displayPrice(LoyaltyModule::getVoucherValue($points), new Currency(Configuration::get('PS_CURRENCY_DEFAULT'))).'</td>
			</tr>
		</table><br/>';
		return $html;
	}
	
	public function hookCancelProduct($params)
	{
		if (!Validate::isLoadedObject($params['order']) OR !Validate::isLoadedObject($orderDetail = new OrderDetail(intval($params['id_order_detail']))))
			return false;
		if (!Validate::isLoadedObject($loyalty = new LoyaltyModule(intval(LoyaltyModule::getByOrderId(intval($params['order']->id))))))
			return false;

		$loyalty->points = $loyalty->points - LoyaltyModule::getNbPointsByPrice($orderDetail->product_price * (1 + ($orderDetail->tax_rate / 100)) * $orderDetail->product_quantity);
		$loyalty->id_loyalty_state = LoyaltyStateModule::getCancelId();
		return $loyalty->save(); // Automatically "historize" the modification
	}
}

?>