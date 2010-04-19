<?php

class MoneyBookers extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'moneybookers';
		$this->tab = 'Payment';
		$this->version = '1.0';

        parent::__construct();

		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Moneybookers');
        $this->description = $this->l('Accepts payments by Moneybookers');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
			return false;
		Configuration::updateValue('MB_HIDE_LOGIN', 1);
		Configuration::updateValue('MB_PAY_TO_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
		Configuration::updateValue('MB_RETURN_URL', 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'history.php');
		Configuration::updateValue('MB_CANCEL_URL', 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__);
		Configuration::updateValue('MB_ID_LOGO', 1);
		Configuration::updateValue('MB_ID_LOGO_WALLET', 1);
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		
		/* Clean configuration table */
		Configuration::deleteByName('MB_PAY_TO_EMAIL');
		Configuration::deleteByName('MB_RETURN_URL');
		Configuration::deleteByName('MB_CANCEL_URL');
		Configuration::deleteByName('MB_HIDE_LOGIN');
		Configuration::deleteByName('MB_SECRET_WORD');
		Configuration::deleteByName('MB_ID_LOGO');
		Configuration::deleteByName('MB_ID_LOGO_WALLET');
		
		return true;
	}

	public function getContent()
	{
		global $cookie;

		$output = '<h2>Moneybookers</h2>
		<p><img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb.gif" alt="Moneybookers" /></p><br />';
		
		$errors = array();

		/* Update configuration variables */
		if (isset($_POST['submitMoneyBookers']))
		{
			if (!isset($_POST['mb_hide_login']))
				$_POST['mb_hide_login'] = 0;
			Configuration::updateValue('MB_PAY_TO_EMAIL', $_POST['mb_pay_to_email']);
			Configuration::updateValue('MB_RETURN_URL', $_POST['mb_return_url']);
			Configuration::updateValue('MB_CANCEL_URL', $_POST['mb_cancel_url']);
			Configuration::updateValue('MB_HIDE_LOGIN', intval($_POST['mb_hide_login']));
			Configuration::updateValue('MB_SECRET_WORD', $_POST['mb_secret_word']);
			Configuration::updateValue('MB_ID_LOGO', $_POST['mb_id_logo']);
			Configuration::updateValue('MB_ID_LOGO_WALLET', $_POST['mb_id_logo_wallet']);

			/* Check account validity */
			$fp = fopen('http://moneybookers.prestashop.com/email_check.php?email='.$_POST['mb_pay_to_email'].'&url=http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__, 'r');
			if (!$fp)
				$errors[] = $this->l('Impossible to contact activation server, please try later');
			else
			{
				$response = trim(strtolower(fgets($fp, 4096)));
				if (!strstr('ok', $response))
					$errors[] = $this->l('Account validation failed, your email might be wrong');
				else
				{
					$fp2 = fopen('http://moneybookers.prestashop.com/email_check.php?email='.$_POST['mb_pay_to_email'].'&url=http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'&sw=1&secret_word='.md5($_POST['mb_secret_word']), 'r');
					if (!$fp2)
						$errors[] = $this->l('Impossible to contact activation server, please try later');
					else
					{
						$response2 = trim(strtolower(fgets($fp2, 4096)));
						if (strstr('velocity_check_exceeded', $response2))
							$errors[] = $this->l('Secret word validation failed, execeeded max tries (3 per hour)');
						elseif (!strstr('ok', $response2))
							$errors[] = $this->l('Secret word validation failed, your secret word might be wrong');
						else
							$conf = true;
					}
				}
			}
		}

		/* Display errors */
		if (sizeof($errors))
		{
			$output .= '<ul style="color: red; font-weight: bold; margin-bottom: 30px; width: 506px; background: #FFDFDF; border: 1px dashed #BBB; padding: 10px;">';
			foreach ($errors AS $error)
				$output .= '<li>'.$error.'</li>';
			$output .= '</ul>';
		}

		/* Display conf */
		if (isset($conf))
		{
			$output .= '
			<ul style="color: green; font-weight: bold; margin-bottom: 30px; width: 506px; background: #E1FFE9; border: 1px dashed #BBB; padding: 10px;">
				<li>'.$this->l('Activation successfull, secret word OK').'</li>
			</ul>';
		}

		$lang = new Language(intval($cookie->id_lang));
		$iso_img = $lang->iso_code;
		if ($lang->iso_code != 'fr' AND $lang->iso_code != 'en')
			$iso_img = 'en';
		
		$manual_links = array(
			'en' => 'http://www.prestashop.com/partner/Activation_Manual_Prestashop_EN.pdf',
			'es' => 'http://www.prestashop.com/partner/Manual%20de%20Activacion%20Prestashop_ES.pdf',
			'fr' => 'http://www.prestashop.com/partner/Manuel_Activation_Prestashop_FR.pdf'
		);
		$iso_manual = $lang->iso_code;
		if (!array_key_exists($lang->iso_code, $manual_links))
			$iso_manual = 'en';

		/* Display settings form */
		$output .= '
		<div style="float: right; width: 300px; border: dashed 1px #666; padding: 8px; margin: 0 0 15px 15px;">
	        <h2>'.$this->l('Opening your Moneybookers account').'</h2>
        	<div style="clear: both;"></div>
                <p>'.$this->l('Open your Moneybookers account:').'</p>
                <p style="text-align: center; margin-top: 30px;"><a href="http://www.moneybookers.com/partners/prestashop/'.($lang->iso_code == 'fr' ? '' : strtolower($lang->iso_code).'/').'"><img src="../modules/moneybookers/prestashop_mb_'.$iso_img.'.gif" alt="PrestaShop & Moneybookers" /></a></p>
		<div style="clear: right;"></div>
       	        </div>
        	
               	<b>'.$this->l('This module allows you to accept payments by Moneybookers.').'</b><br /><br />		
		<b>'.$this->l('About Moneybookers').'</b><br /><br />'.
		$this->l('Moneybookers is one of Europe\'s largest online payments systems and among the world\'s leading eWallet providers, with over 11 million account holders. The simple eWallet enables any customer to conveniently and securely pay online without revealing personal financial data, as well as to send and receive money transfers cost-effectively by simply using an email address.').'<br /><br />'.
		$this->l('Moneybookers. worldwide payment network offers businesses access to over 80 local payment options in over 200 countries with just one integration. Already more than 60,000 merchants use Moneybookers. payments service, including global partners such as eBay, Skype and Thomas Cook').'<br /><br />'.$this->l('Moneybookers was founded in 2001 in London and is regulated by the Financial Services Authority of the United Kingdom.').'

                <div style="clear:both;">&nbsp;</div>
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
			<style type="text/css">
				label {
					width: 300px;
					margin-right: 10px;
					font-size: 12px;
				}
			</style>
			<fieldset style="width: 650px;">
				<legend><img src="'.__PS_BASE_URI__.'modules/moneybookers/logo.gif" alt="" />'.$this->l('Settings').'</legend>
				<p><a href="'.$manual_links[$iso_manual].'" target="_blank">'.$this->l('Consult the manual for activation and configuration of Moneybookers on PrestaShop').'</a></p>
				<label>'.$this->l('Your e-mail address:').'</label>
				<div class="margin-form">
					<input type="text" name="mb_pay_to_email" value="'.Configuration::get('MB_PAY_TO_EMAIL').'" />
				</div>
				<div style="clear: both;"></div>
				<label>'.$this->l('Your secret word:').'</label>
				<div class="margin-form">
					<input type="password" name="mb_secret_word" value="'.Configuration::get('MB_SECRET_WORD').'" />
				</div>
				<div style="clear: both;"></div>
				<label>'.$this->l('Page displayed after successful payment:').'</label>
				<div class="margin-form">
					<input type="text" name="mb_return_url" value="'.Configuration::get('MB_RETURN_URL').'" style="width: 300px;" />
				</div>
				<div style="clear: both;"></div>
				<label>'.$this->l('Page displayed after payment cancellation:').'</label>
				<div class="margin-form">
					<input type="text" name="mb_cancel_url" value="'.Configuration::get('MB_CANCEL_URL').'" style="width: 300px;" />
				</div>
				<div style="clear: both;"></div>
				<label>'.$this->l('Hide the login form on Moneybookers page').'</label>
				<div class="margin-form">
					<input type="checkbox" name="mb_hide_login" value="1" '.(Configuration::get('MB_HIDE_LOGIN') ? 'checked="checked"' : '').' style="margin-top: 4px;" />
				</div>
				<div style="clear: both;"></div>
				<label style="margin-bottom: 10px;">'.$this->l('Choose a logo for credit cards:').'</label><div style="clear: both;"></div>
				<input type="radio" name="mb_id_logo" value="1" style="margin-left: 55px; vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO') == 1 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-cc-1.gif" alt="" style="vertical-align: middle;" /> 
					<input type="radio" name="mb_id_logo" value="2" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO') == 2 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-cc-2.gif" alt="" style="vertical-align: middle;" />

				<div style="clear: both;"></div>
				<label style="margin: 10px 0;">'.$this->l('Choose a logo for eWallet:').'</label><div style="clear: both;"></div>
				<input type="radio" name="mb_id_logo_wallet" value="1" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO_WALLET') == 1 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb-1.gif" alt="" style="vertical-align: middle;" />
					<input type="radio" name="mb_id_logo_wallet" value="2" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO_WALLET') == 2 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb-2.gif" alt="" style="vertical-align: middle;" /><br />
					<input type="radio" name="mb_id_logo_wallet" value="3" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO_WALLET') == 3 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb-3.gif" alt="" style="vertical-align: middle;" />
					<input type="radio" name="mb_id_logo_wallet" value="4" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO_WALLET') == 4 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb-4.gif" alt="" style="vertical-align: middle;" /><br />
					<input type="radio" name="mb_id_logo_wallet" value="5" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO_WALLET') == 5 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb-5.gif" alt="" style="vertical-align: middle;" />
					<input type="radio" name="mb_id_logo_wallet" value="6" style="vertical-align: middle;" '.(Configuration::get('MB_ID_LOGO_WALLET') == 6 ? 'checked="checked"' : '').' /> 
<img src="'.__PS_BASE_URI__.'modules/moneybookers/logo-mb-6.gif" alt="" style="vertical-align: middle;" /><br />
				<center><input type="submit" class="button" name="submitMoneyBookers" value="'.$this->l('Save settings and validate my account').'" style="margin-top: 25px;" /></center>
			</fieldset>
		</form>';

		return $output;
	}

	public function hookPayment($params)
	{
		/* Display the MoneyBookers iframe */
		return $this->display(__FILE__, 'moneybookers.tpl');
	}

	public function hookPaymentReturn($params)
	{
		return $this->display(__FILE__, 'confirmation.tpl');
	}
}

?>
