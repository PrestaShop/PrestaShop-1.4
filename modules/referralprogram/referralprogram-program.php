<?php

/* SSL Management */
$useSSL = true;
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once(dirname(__FILE__).'/ReferralProgramModule.php');

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=modules/referralprogram/referralprogram-program.php');

$js_files = array(
	_PS_JS_DIR_.'jquery/thickbox-modified.js',
	_PS_JS_DIR_.'jquery/jquery.idTabs.modified.js'
);
$css_files = array(__PS_BASE_URI__.'css/thickbox.css' => 'all');

include(dirname(__FILE__).'/../../header.php');

// get discount value (ready to display)
$discount = Discount::display(floatval(Configuration::get('REFERRAL_DISCOUNT_VALUE')), intval(Configuration::get('REFERRAL_DISCOUNT_TYPE')), new Currency($cookie->id_currency));

$activeTab = 'sponsor';
$error = false;

// Mailing invitation to friend sponsor
$invitation_sent = false;
$nbInvitation = 0;
if (Tools::isSubmit('submitSponsorFriends') AND Tools::getValue('friendsEmail') AND sizeof($friendsEmail = Tools::getValue('friendsEmail')) >= 1)
{
	$activeTab = 'sponsor';
	if (!Tools::getValue('conditionsValided'))
	{
		$error = 'conditions not valided';
	}
	else
	{
		$friendsLastName = Tools::getValue('friendsLastName');
		$friendsFirstName = Tools::getValue('friendsFirstName');
		$mails_exists = array();
		foreach ($friendsEmail as $key => $friendEmail)
		{
			$friendEmail = strval($friendEmail);
			$friendLastName = strval($friendsLastName[$key]);
			$friendFirstName = strval($friendsFirstName[$key]);

			if (empty($friendEmail) AND empty($friendLastName) AND empty($friendFirstName))
				continue;
			elseif (!Validate::isEmail($friendEmail))
				$error = 'email invalid';
			elseif (empty($friendFirstName) OR empty($friendLastName) OR !Validate::isName($friendLastName) OR !Validate::isName($friendFirstName))
				$error = 'name invalid';
			elseif (ReferralProgramModule::isEmailExists($friendEmail) OR Customer::customerExists($friendEmail))
			{
				$error = 'email exists';
				$mails_exists[] = $friendEmail;

			}
			else
			{
				$referralprogram = new ReferralProgramModule();
				$referralprogram->id_sponsor = intval($cookie->id_customer);
				$referralprogram->firstname = $friendFirstName;
				$referralprogram->lastname = $friendLastName;
				$referralprogram->email = $friendEmail;
				if (!$referralprogram->validateFields(false))
					$error = 'name invalid';
				else
				{
					if ($referralprogram->save())
					{
						$blowfish = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
						$vars = array(
							'{email}' => strval($cookie->email),
							'{lastname}' => strval($cookie->customer_lastname),
							'{firstname}' => strval($cookie->customer_firstname),
							'{email_friend}' => $friendEmail,
							'{lastname_friend}' => $friendLastName,
							'{firstname_friend}' => $friendFirstName,
							'{link}' => 'authentication.php?create_account=1&sponsor='.urlencode($blowfish->encrypt($referralprogram->id.'|'.$referralprogram->email.'|')),
							'{discount}' => $discount,
						);
						Mail::Send(intval($cookie->id_lang), 'referralprogram-invitation', 'Referral Program', $vars, $friendEmail, $friendFirstName.' '.$friendLastName, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
						$invitation_sent = true;
						$nbInvitation++;
						$activeTab = 'pending';
					}
					else
						$error = 'cannot add friends';
				}
			}
			if ($error)
				break;
		}
		if ($nbInvitation > 0)
			unset($_POST);
	}
}

// Mailing revive
$revive_sent = false;
$nbRevive = 0;
if (Tools::isSubmit('revive'))
{
	$activeTab = 'pending';
	if (Tools::getValue('friendChecked') AND sizeof($friendsChecked = Tools::getValue('friendChecked')) >= 1)
	{
		foreach ($friendsChecked as $key => $friendChecked)
		{
			$blowfish = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
			$referralprogram = new ReferralProgramModule(intval($key));
			$vars = array(
				'{email}' => $cookie->email,
				'{lastname}' => $cookie->customer_lastname,
				'{firstname}' => $cookie->customer_firstname,
				'{email_friend}' => $referralprogram->email,
				'{lastname_friend}' => $referralprogram->lastname,
				'{firstname_friend}' => $referralprogram->firstname,
				'{link}' => 'authentication.php?create_account=1&sponsor='.urlencode($blowfish->encrypt($referralprogram->id.'|'.$referralprogram->email.'|')),
				'{discount}' => $discount
			);
			$referralprogram->save();
			Mail::Send(intval($cookie->id_lang), 'referralprogram-invitation', 'Referral Program', $vars, $referralprogram->email, $referralprogram->firstname.' '.$referralprogram->lastname, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
			$revive_sent = true;
			$nbRevive++;
		}
	}
	else
		$error = 'no revive checked';
}

$customer = new Customer(intval($cookie->id_customer));
$stats = $customer->getStats();

$orderQuantity = intval(Configuration::get('REFERRAL_ORDER_QUANTITY'));
$canSendInvitations = false;
if (intval($stats['nb_orders']) >= $orderQuantity)
	$canSendInvitations = true;

// Smarty display
$smarty->assign(array(
	'activeTab' => $activeTab,
	'discount' => $discount,
	'orderQuantity' => $orderQuantity,
	'canSendInvitations' => $canSendInvitations,
	'nbFriends' => intval(Configuration::get('REFERRAL_NB_FRIENDS')),
	'error' => $error,
	'invitation_sent' => $invitation_sent,
	'nbInvitation' => $nbInvitation,
	'pendingFriends' => ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'pending'),
	'revive_sent' => $revive_sent,
	'nbRevive' => $nbRevive,
	'subscribeFriends' => ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'subscribed'),
	'mails_exists' => (isset($mails_exists) ? $mails_exists : array())
));

echo Module::display(dirname(__FILE__).'/referralprogram.php', 'referralprogram-program.tpl');

include(dirname(__FILE__).'/../../footer.php'); 

?>
