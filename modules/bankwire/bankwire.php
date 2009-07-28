<?php

class BankWire extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public  $details;
	public  $owner;
	public	$address;

	public function __construct()
	{
		$this->name = 'bankwire';
		$this->tab = 'Payment';
		$this->version = 0.4;
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('BANK_WIRE_DETAILS', 'BANK_WIRE_OWNER', 'BANK_WIRE_ADDRESS'));
		if (isset($config['BANK_WIRE_OWNER']))
			$this->owner = $config['BANK_WIRE_OWNER'];
		if (isset($config['BANK_WIRE_DETAILS']))
			$this->details = $config['BANK_WIRE_DETAILS'];
		if (isset($config['BANK_WIRE_ADDRESS']))
			$this->address = $config['BANK_WIRE_ADDRESS'];

		parent::__construct();

		$this->displayName = $this->l('Bank Wire');
		$this->description = $this->l('Accept payments by bank wire');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!isset($this->owner) OR !isset($this->details) OR !isset($this->address))
			$this->warning = $this->l('Account owner and details must be configured in order to use this module correctly');
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency set for this module');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
			return false;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('BANK_WIRE_DETAILS')
				OR !Configuration::deleteByName('BANK_WIRE_OWNER')
				OR !Configuration::deleteByName('BANK_WIRE_ADDRESS')
				OR !parent::uninstall())
			return false;
	}

	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['details']))
				$this->_postErrors[] = $this->l('account details are required.');
			elseif (empty($_POST['owner']))
				$this->_postErrors[] = $this->l('Account owner is required.');
		}
	}

	private function _postProcess()
	{
		if (isset($_POST['btnSubmit']))
		{
			Configuration::updateValue('BANK_WIRE_DETAILS', $_POST['details']);
			Configuration::updateValue('BANK_WIRE_OWNER', $_POST['owner']);
			Configuration::updateValue('BANK_WIRE_ADDRESS', $_POST['address']);
		}
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
	}

	private function _displayBankWire()
	{
		$this->_html .= '<img src="../modules/bankwire/bankwire.jpg" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments by bank wire.').'</b><br /><br />
		'.$this->l('If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.').'<br />
		'.$this->l('Therefore, you will need to manually confirm the order as soon as you receive a wire..').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Contact details').'</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please specify the bank wire account details for customers').'.<br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;">'.$this->l('Account owner').'</td><td><input type="text" name="owner" value="'.htmlentities(Tools::getValue('owner', $this->owner), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Details').'</td>
						<td style="padding-bottom:15px;">
							<textarea name="details" rows="4" cols="53">'.htmlentities(Tools::getValue('details', $this->details), ENT_COMPAT, 'UTF-8').'</textarea>
							<p>'.$this->l('Such as bank branch, IBAN number, BIC, etc.').'</p>
						</td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Bank address').'</td>
						<td style="padding-bottom:15px;">
							<textarea name="address" rows="4" cols="53">'.htmlentities(Tools::getValue('address', $this->address), ENT_COMPAT, 'UTF-8').'</textarea>
						</td>
					</tr>
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayBankWire();
		$this->_displayForm();

		return $this->_html;
	}

	public function execPayment($cart)
	{
		if (!$this->active)
			return ;

		global $cookie, $smarty;

		$smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cookie->id_currency,
			'currencies' => $this->getCurrency(),
			'total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
			'isoCode' => Language::getIsoById(intval($cookie->id_lang)),
			'bankwireDetails' => nl2br2($this->details),
			'bankwireAddress' => nl2br2($this->address),
			'bankwireOwner' => $this->owner,
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'payment_execution.tpl');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;

		global $smarty;

		$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;

		global $smarty;
		$state = $params['objOrder']->getCurrentState();
		if ($state == _PS_OS_BANKWIRE_ OR $state == _PS_OS_OUTOFSTOCK_)
			$smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, false),
				'bankwireDetails' => nl2br2($this->details),
				'bankwireAddress' => nl2br2($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
		else
			$smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}
}
