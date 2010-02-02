<?php

class Blocknewsletter extends Module
{
 	public function __construct()
 	{
 	 	$this->name = 'blocknewsletter';
 	 	$this->tab = 'Blocks';

	 	parent::__construct();

 	 	$this->displayName = $this->l('Newsletter block');
 	 	$this->description = $this->l('Adds a block for newsletter subscription');
		$this->confirmUninstall = $this->l('Are you sure you want to delete all your contacts ?');

		$this->version = '1.4';
		$this->error = false;
		$this->valid = false;
		$this->_files = array(
			'name' => array('newsletter_conf', 'newsletter_voucher'),
			'ext' => array(
				0 => 'html',
				1 => 'txt'
			)
		);
 	}
 	
 	public function install()
 	{
 	 	if (parent::install() == false OR $this->registerHook('leftColumn') == false)
 	 		return false;
 	 	return Db::getInstance()->Execute('
		CREATE TABLE '._DB_PREFIX_.'newsletter (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`email` varchar(255) NOT NULL,
			`newsletter_date_add` DATETIME NULL,
			`ip_registration_newsletter` varchar(15) NOT NULL,
			`http_referer` VARCHAR(255) NULL,
			PRIMARY KEY(`id`)
		) ENGINE=MyISAM default CHARSET=utf8');
 	}
 	
 	public function uninstall()
 	{
 	 	if (!parent::uninstall())
 	 		return false;
 	 	return Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'newsletter');
 	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('submitUpdate'))
		{
			if (isset($_POST['new_page']) AND Validate::isBool(intval($_POST['new_page'])))
				Configuration::updateValue('NW_CONFIRMATION_NEW_PAGE', $_POST['new_page']);
			if (isset($_POST['conf_email']) AND VAlidate::isBool(intval($_POST['conf_email'])))
				Configuration::updateValue('NW_CONFIRMATION_EMAIL', pSQL($_POST['conf_email']));
			if (!empty($_POST['voucher']) AND !Validate::isDiscountName($_POST['voucher']))
				$this->_html .= '<div class="alert">'.$this->l('Voucher code is invalid').'</div>';
			else
			{
				Configuration::updateValue('NW_VOUCHER_CODE', pSQL($_POST['voucher']));
				$this->_html .= '<div class="conf ok">'.$this->l('Updated successfully').'</div>';
			}
		}
		return $this->_displayForm();
	}

	private function _displayForm()
	{
		$this->_html .= '
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Displaying configuration in a new page?').'</label>
				<div class="margin-form">
					<input type="radio" name="new_page" value="1" '.(Configuration::get('NW_CONFIRMATION_NEW_PAGE') ? 'checked="checked" ' : '').'/>'.$this->l('yes').'
					<input type="radio" name="new_page" value="0" '.(!Configuration::get('NW_CONFIRMATION_NEW_PAGE') ? 'checked="checked" ' : '').'/>'.$this->l('no').'
				</div>
				<div class="clear"></div>
				<label>'.$this->l('Sending confirmation email after subscription?').'</label>
				<div class="margin-form">
					<input type="radio" name="conf_email" value="1" '.(Configuration::get('NW_CONFIRMATION_EMAIL') ? 'checked="checked" ' : '').'/>'.$this->l('yes').'
					<input type="radio" name="conf_email" value="0" '.(!Configuration::get('NW_CONFIRMATION_EMAIL') ? 'checked="checked" ' : '').'/>'.$this->l('no').'
				</div>
				<div class="clear"></div>
				<label>'.$this->l('Welcome voucher code').'</label>
				<div class="margin-form">
					<input type="text" name="voucher" value="'.Configuration::get('NW_VOUCHER_CODE').'" />
					<p>'.$this->l('Leave blank for disabling').'</p>
				</div>
				<div class="margin-form clear pspace"><input type="submit" name="submitUpdate" value="'.$this->l('Update').'" class="button" /></div>
			</fieldset>
		</form>';

		return $this->_html;
	}

 	private function isNewsletterRegistered($customerEmail)
 	{
 	 	if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'newsletter WHERE `email` = \''.pSQL($customerEmail).'\''))
 	 		return 1;
		if (!$registered = Db::getInstance()->getRow('SELECT `newsletter` FROM '._DB_PREFIX_.'customer WHERE `email` = \''.pSQL($customerEmail).'\''))
			return -1;
		if ($registered['newsletter'] == '1')
			return 2;
		return 0;
 	}
 	
 	private function newsletterRegistration()
 	{
	 	if (!Validate::isEmail(pSQL($_POST['email'])))
			return $this->error = $this->l('Invalid e-mail address');
	 	/* Unsubscription */
	 	elseif ($_POST['action'] == '1')
	 	{
 		 	$registerStatus = $this->isNewsletterRegistered(pSQL($_POST['email']));
	 	 	if ($registerStatus < 1)
	 	 		return $this->error = $this->l('E-mail address not registered');
	 	 	/* If the user ins't a customer */
	 	 	elseif ($registerStatus == 1)
	 	 	{
			  	if (!Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'newsletter WHERE `email` = \''.pSQL($_POST['email']).'\''))
	 	 			return $this->error = $this->l('Error during unsubscription');
	 	 		return $this->valid = $this->l('Unsubscription successful');
	 	 	}
	 	 	/* If the user is a customer */
	 	 	elseif ($registerStatus == 2)
	 		{
	 	 		if (!Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'customer SET `newsletter` = 0 WHERE `email` = \''.pSQL($_POST['email']).'\''))
	 	 			return $this->error = $this->l('Error during unsubscription');
	 	 		return $this->valid = $this->l('Unsubscription successful');
	 	 	}
		}
	 	/* Subscription */
	 	elseif ($_POST['action'] == '0')
	 	{
	 	 	$registerStatus = $this->isNewsletterRegistered(pSQL($_POST['email']));
			if ($registerStatus > 0)
				return $this->error = $this->l('E-mail address already registered');
			/* If the user ins't a customer */
			elseif ($registerStatus == -1)
			{
				global $cookie;
				if (!Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'newsletter VALUES (\'\', \''.pSQL($_POST['email']).'\', NOW(), \''.pSQL($_SERVER['REMOTE_ADDR']).'\', 
					(SELECT c.http_referer FROM '._DB_PREFIX_.'connections c WHERE c.id_guest = '.intval($cookie->id_guest).' ORDER BY c.date_add DESC LIMIT 1))'))
					return $this->error = $this->l('Error during subscription');
				$this->sendVoucher(pSQL($_POST['email']));
				return $this->valid = $this->l('Subscription successful');
			}
			/* If the user is a customer */
			elseif ($registerStatus == 0)
			{
			 	if (!Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'customer SET `newsletter` = 1, newsletter_date_add = NOW(), `ip_registration_newsletter` = \''.pSQL($_SERVER['REMOTE_ADDR']).'\' WHERE `email` = \''.pSQL($_POST['email']).'\''))
	 	 			return $this->error = $this->l('Error during subscription');
				$this->sendVoucher(pSQL($_POST['email']));
				return $this->valid = $this->l('Subscription successful');
			}
		}
 	}

	private function sendVoucher($email)
	{
		global $cookie;

		if ($discount = Configuration::get('NW_VOUCHER_CODE'))
			return Mail::send(intval($cookie->id_lang), 'newsletter_voucher', $this->l('Newsletter voucher'), array('{discount}' => $discount), $email, NULL, NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
		return false;
	}

	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
 	
 	function hookLeftColumn($params)
 	{
		global $smarty;

		if (Tools::isSubmit('submitNewsletter'))
		{
			$this->newsletterRegistration();
			if ($this->error)
			{
				$smarty->assign(array('color' => 'red',
										'msg' => $this->error,
										'nw_value' => isset($_POST['email']) ? pSQL($_POST['email']) : false,
										'nw_error' => true,
										'action' => $_POST['action']));
			}
			elseif ($this->valid)
			{
				if (Configuration::get('NW_CONFIRMATION_EMAIL') AND isset($_POST['action']) AND intval($_POST['action']) == 0)
					Mail::Send(intval($params['cookie']->id_lang), 'newsletter_conf', $this->l('Newsletter confirmation'), array(), pSQL($_POST['email']), NULL, NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
				$smarty->assign(array('color' => 'green',
										'msg' => $this->valid,
										'nw_error' => false));
			}
		}
		$smarty->assign('this_path', $this->_path);
 	 	return $this->display(__FILE__, 'blocknewsletter.tpl');
 	}

	public function confirmation()
	{
		global $smarty;

		return $this->display(__FILE__, 'newsletter.tpl');
	}

	public function externalNewsletter(/*$params*/)
	{
		return $this->hookLeftColumn($params);
	}
}

?>