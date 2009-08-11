<?php

/**
  * Password recuperation for employees account, password.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');
include(PS_ADMIN_DIR.'/functions.php');

$errors = array();
$cookie = new Cookie('psAdmin', substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__), -10));
$id_lang = intval($cookie->id_lang) ? intval($cookie->id_lang) : 1;
$iso = strtolower(Language::getIsoById($cookie->id_lang ? intval($cookie->id_lang) : 1));
include(_PS_TRANSLATIONS_DIR_.$iso.'/admin.php');

if (isset($_POST['Submit']))
{
	$errors = array();
	if (empty($_POST['email']))
		$errors[] = Tools::displayError('e-mail is empty');
	elseif (!Validate::isEmail($_POST['email']))
		$errors[] = Tools::displayError('invalid e-mail address');
	else
	{
		$employee = new Employee();
		if (!$employee->getByemail($_POST['email']) OR !$employee)
			$errors[] = Tools::displayError('this account doesn\'t exist');
		else
		{
			if ((strtotime($employee->last_passwd_gen.'+'.Configuration::get('PS_PASSWD_TIME_BACK').' minutes') - time()) > 0 )
				$errors[] = Tools::displayError('You can regenerate your password only each').' '.Configuration::get('PS_PASSWD_TIME_BACK').' '.Tools::displayError('minute(s)');
			else
			{	
				$pwd = Tools::passwdGen();
				$employee->passwd = md5(pSQL(_COOKIE_KEY_.$pwd));
				$employee->last_passwd_gen = date('Y-m-d H:i:s', time());
				$result = $employee->update();
				if (!$result)
					$errors[] = Tools::displayError('an error occurred during your password change');
				else
				{
					Mail::Send($id_lang, 'password', 'Your new admin password', array('{email}' => $employee->email, '{lastname}' => $employee->lastname, '{firstname}' => $employee->firstname, '{passwd}' => $pwd), $employee->email, $employee->firstname.' '.$employee->lastname);
					$confirmation = 'ok';
				}
			}
		}
	}
}

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="../css/login.css" />
	<title>PrestaShop&trade; - '.translate('Administration panel').'</title>
</head>
<body><div id="container">';
if (sizeof($errors))
{
	echo '<div id="error">
	<h3>'.translate('There is 1 error').'</h3>
	<ol>';
	foreach ($errors AS $error)
		echo '<li>'.$error.'</li>';
	echo '</ol>
	</div>';
}		
echo '
	<div id="login">
		<form action="" method="post">
			<div class="page-title center">'.translate('Forgot your password?').'</div><br />';
if (isset($confirmation))
	echo '	<br />
			<div style="font-weight: bold;">'.translate('Your password has been e-mailed to you').'.</div>
			<div style="margin: 2em 0 0 0; text-align: right;"><a href="login.php?email='.Tools::safeOutput(Tools::getValue('email')).'">> '.translate('back to login home').'</a></div>';
else
	echo '	<span style="font-weight: bold;">'.translate('Please, enter your e-mail address').' </span>
			'.translate('(the one you wrote during your registration) in order to receive your access codes by e-mail').'.<br />
			<input type="text" name="email" class="input" />
			<div>
				<div id="submit"><input type="submit" name="Submit" value="'.translate('Send').'" class="button" /></div>
				<div id="lost">&nbsp;</div>
			</div>
		</form>
	</div>
</div></body></html>';

?>
