<?php

/**
  * Login for admin panel, login.php
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

// Checking path
$pathServer = preg_replace('!^/!', '', $_SERVER['PHP_SELF']);
$pathUser = preg_replace('!^/!', '', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
if (strcmp($pathServer, $pathUser))
	$errors[] = Tools::displayError('Path is not the same between your browser and you server :').'<br /><br /><b>'.
				Tools::displayError('- Server:').'</b><br />'.htmlentities($pathServer).'<br /><br /><b>'.
				Tools::displayError('- Browser:').'</b><br />'.htmlentities($pathUser);

$cookie = new Cookie('psAdmin', substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__), -10));
if (!isset($cookie->id_lang))
	$cookie->id_lang = Configuration::get('PS_LANG_DEFAULT');
$iso = strtolower(Language::getIsoById(intval($cookie->id_lang)));
include(_PS_TRANSLATIONS_DIR_.$iso.'/admin.php');

/* Cookie creation and redirection */
if (Tools::isSubmit('Submit'))
{
 	/* Check fields validity */
	$passwd = trim(Tools::getValue('passwd'));
	$email = trim(Tools::getValue('email'));
	if (empty($email))
		$errors[] = Tools::displayError('e-mail is empty');
	elseif (!Validate::isEmail($email))
		$errors[] = Tools::displayError('invalid e-mail address');
	elseif (empty($passwd))
		$errors[] = Tools::displayError('password is blank');
	elseif (!Validate::isPasswd($passwd))
		$errors[] = Tools::displayError('invalid password');
	else
	{
	 	/* Seeking for employee */
		$employee = new Employee();
		$employee = $employee->getByemail($email, $passwd);
		if (!$employee)
		{
			$errors[] = Tools::displayError('employee does not exist, or bad password');
			$cookie->logout();
		}
		else
		{
		 	/* Creating cookie */
			$cookie->id_employee = $employee->id;
			$cookie->lastname = $employee->lastname;
			$cookie->firstname = $employee->firstname;
			$cookie->email = $employee->email;
			$cookie->profile = $employee->id_profile;
			$cookie->passwd = $employee->passwd;
			$cookie->write();
			/* Redirect to admin panel */
			if (isset($_GET['redirect']))
				$url = strval($_GET['redirect'].(isset($_GET['token']) ? ('&token='.$_GET['token']) : ''));
			else
				$url = 'index.php';
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
				<meta http-equiv="Refresh" content="0;URL='.$url.'">
				<head>
					<script language="javascript" type="text/javascript">
						window.location.replace("'.$url.'");
					</script>
					<div style="text-align:center; margin-top:250px;"><a href="'.$url.'">'.translate('Click here to launch Administration panel').'</a></div>
				</head>
			</html>';
			exit ;
		}
	}
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link type="text/css" rel="stylesheet" href="../css/login.css" />
		<title>PrestaShop&trade; - '.translate('Administration panel').'</title>';
echo '
	</head>
	<body>
		<div id="container">';

if ($nbErrors = sizeof($errors))
{
	echo '
	<div id="error">
		<h3>'.($nbErrors > 1 ? translate('There are') : translate('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? translate('errors') : translate('error')).'</h3>
		<ol style="margin: 0 0 0 20px;">';
		foreach ($errors AS $error)
			echo '<li>'.$error.'</li>';
		echo '
		</ol>
	</div>
	<br />';
}

echo '
			<div id="login">
				<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';

$randomNb = rand(100, 999);
if(file_exists(PS_ADMIN_DIR.'/../install') OR file_exists(PS_ADMIN_DIR.'/../admin'))
{
	echo '				<span>'.translate('For security reasons, you cannot connect to the Back Office until after you have:').'<br /><br />
		- '.translate('deleted the /install folder').'<br />
		- '.translate('renamed the /admin folder (eg. ').'/admin'.$randomNb.')<br />
		<br />'.translate('Please then access this page by the new url (eg. http://www.domain.tld/admin').$randomNb.')</span>';
}
else
{
	echo '			<label>'.translate('E-mail address:').'</label><br />
					<input type="text" id="email" name="email" value="'.Tools::safeOutput(Tools::getValue('email')).'" class="input"/>
					<div style="margin: 0.5em 0 0 0;">
						<label>'.translate('Password:').'</label><br />
						<input type="password" name="passwd" class="input" value=""/>
					</div>
					<div>
						<div id="submit"><input type="submit" name="Submit" value="'.translate('Connection').'" class="button" /></div>
						<div id="lost"><a href="password.php">'.translate('Lost password?').'</a></div>
					</div>
	';
}
?>
<script type="text/javascript">
<!--
if (document.getElementById('email')) document.getElementById('email').focus();
-->
</script>
<?php
echo '
				</form>
			</div>
		</div>
	</body>
</html>';

?>
