<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/WishList.php');

if (Configuration::get('PS_TOKEN_ENABLE') == 1 AND
	strcmp(Tools::getToken(false), Tools::getValue('token')) AND
	$cookie->isLogged() === true)
	exit(Tools::displayError('invalid token'));

if ($cookie->isLogged())
{
	$id_wishlist = intval(Tools::getValue('id_wishlist'));
	if (empty($id_wishlist) === true)
		exit(Tools::displayError('Invalid wishlist'));
	for ($i = 1; empty($_POST['email'.strval($i)]) === false; ++$i)
	{
		$to = Tools::getValue('email'.$i);
		$wishlist = WishList::exists($id_wishlist, $cookie->id_customer, true);
		if ($wishlist === false)
			exit(Tools::displayError('Invalid wishlist'));
		if (WishList::addEmail($id_wishlist, $to) === false)
			exit(Tools::displayError('Wishlist send error'));
		$toName = strval(Configuration::get('PS_SHOP_NAME'));
		$customer = new Customer(intval($cookie->id_customer));
		if (Validate::isLoadedObject($customer))
			Mail::Send(intval($cookie->id_lang), 'wishlist', 'Message from '.$customer->lastname.' '.$customer->firstname, 
			array(
			'{lastname}' => $customer->lastname, 
			'{firstname}' => $customer->firstname, 
			'{wishlist}' => $wishlist['name'],
			'{message}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/blockwishlist/view.php?token='.$wishlist['token']),
			$to, $toName, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, dirname(__FILE__).'/mails/');
	}
}
