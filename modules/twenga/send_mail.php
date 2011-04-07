<?php

$configPath = '../../config/config.inc.php';

if (file_exists($configPath))
{
	include('../../config/config.inc.php');

	$controller = new FrontController();
	$controller->init();

	$country = Db::getInstance()->ExecuteS('
		SELECT c.name as name
		FROM '._DB_PREFIX_.'country_lang as c
		WHERE c.id_lang = '.$_POST['id_lang'].' 
		AND c.id_country = '.	Configuration::get('PS_COUNTRY_DEFAULT'));

	if (!isset($country[0]['name']))
		$country[0]['name'] = 'Undefined';

	$to = 'vince@prestashop.com';
	$subject = 'Site prestashop '.$country[0]['name'].' ayant supprimé le module';
	
	$template = 'mail';

	$template_vars = array(
		'{shop_url}' 		=> Tools::getShopDomain(true),
		'{trader_email}' 	=> Configuration::get('PS_SHOP_EMAIL'),
		'{shop_country}'	=> $country[0]['name']);

	 Mail::Send($_POST['id_lang'], $template, $subject, $template_vars,
		 $to, NULL, NULL, NULL, NULL, NULL, dirname(__FILE__).'/mails/');
}

?>
