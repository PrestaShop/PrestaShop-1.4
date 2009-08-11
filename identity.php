<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=identity.php');

$customer = new Customer(intval($cookie->id_customer));

if (sizeof($_POST))
{
 	$exclusion = array('secure_key', 'old_passwd', 'passwd', 'active', 'date_add', 'date_upd');
	$fields = $customer->getFields();
	foreach ($fields AS $key => $value)
		if (!in_array($key, $exclusion))
			$customer->{$key} = key_exists($key, $_POST) ? trim($_POST[$key]) : 0;
}

if (isset($_POST['years']) AND isset($_POST['months']) AND isset($_POST['days']))
	$customer->birthday = intval($_POST['years']).'-'.intval($_POST['months']).'-'.intval($_POST['days']);

$errors = array();
if (Tools::isSubmit('submitIdentity'))
{
	if (!@checkdate(Tools::getValue('months'), Tools::getValue('days'), Tools::getValue('years')) AND
	!(Tools::getValue('months') == '' AND Tools::getValue('days') == '' AND Tools::getValue('years') == ''))
		$errors[] = Tools::displayError('invalid birthday');
	else
	{
		$customer->birthday = (empty($_POST['years']) ? '' : intval($_POST['years']).'-'.intval($_POST['months']).'-'.intval($_POST['days']));

		$_POST['old_passwd'] = trim($_POST['old_passwd']);
		if (empty($_POST['old_passwd']) OR (Tools::encrypt($_POST['old_passwd']) != $cookie->passwd))
			$errors[] = Tools::displayError('your current password is not that one');
		elseif ($_POST['passwd'] != $_POST['confirmation'])
			$errors[] = Tools::displayError('password and confirmation do not match');
		else
			$errors = $customer->validateControler();

		if (!sizeof($errors))
		{
			$customer->lastname = Tools::strtoupper($customer->lastname);
		    $customer->firstname = Tools::ucfirst(Tools::strtolower($customer->firstname));
			if (Tools::getValue('passwd'))
				$cookie->passwd = $customer->passwd;
			if ($customer->update())
			{
				$cookie->customer_lastname = $customer->lastname;
				$cookie->customer_firstname = $customer->firstname;
				$smarty->assign('confirmation', 1);
			}
			else
				$errors[] = Tools::displayError('impossible to update information');
		}
	}
}
else
	$_POST = array_map('stripslashes', $customer->getFields());

if ($customer->birthday)
	$birthday = explode('-', $customer->birthday);
else
	$birthday = array('-', '-', '-');

/* Generate years, months and days */
$smarty->assign(array(
	'years' => Tools::dateYears(),
	'sl_year' => $birthday[0],
	'months' => Tools::dateMonths(),
	'sl_month' => $birthday[1],
	'days' => Tools::dateDays(),
	'sl_day' => $birthday[2],
	'errors' => $errors));

Tools::safePostVars();

include(dirname(__FILE__).'/header.php');
$smarty->display(_PS_THEME_DIR_.'identity.tpl');
include(dirname(__FILE__).'/footer.php');

?>