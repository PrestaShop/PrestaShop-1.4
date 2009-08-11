<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

define('MIN_PASSWD_LENGTH', 8);
$errors = array();

if (Tools::isSubmit('email'))
{
    if (!($email = Tools::getValue('email')) OR !Validate::isEmail($email))
        $errors[] = Tools::displayError('invalid e-mail address');
    else
    {
        $customer = new Customer();
        $customer->getByemail($email);
        if (!Validate::isLoadedObject($customer))
            $errors[] = Tools::displayError('there is no account registered to this e-mail address');
		else
		{
			if ((strtotime($customer->last_passwd_gen.'+'.intval($min_time = Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0)
				$errors[] = Tools::displayError('You can regenerate your password only each').' '.intval($min_time).' '.Tools::displayError('minute(s)');
			else
			{
			    $customer->passwd = Tools::encrypt($password = Tools::passwdGen(intval(MIN_PASSWD_LENGTH)));
				$customer->last_passwd_gen = date('Y-m-d H:i:s', time());
		        if ($customer->update())
				{
					Mail::Send(intval($cookie->id_lang), 'password', 'Your password', 
					array('{email}' => $customer->email, 
					'{lastname}' => $customer->lastname, 
					'{firstname}' => $customer->firstname, 
					'{passwd}' => $password), 
					$customer->email, 
					$customer->firstname.' '.$customer->lastname);
					$smarty->assign(array('confirmation' => 1, 'email' => $customer->email));
				}
				else
					$errors[] = Tools::displayError('error with your account and your new password cannot be sent to your e-mail; please report your problem using the contact form');
			}
		}
    }
}

$smarty->assign('errors', $errors);
Tools::safePostVars();
$smarty->display(_PS_THEME_DIR_.'password.tpl');

include(dirname(__FILE__).'/footer.php');

?>