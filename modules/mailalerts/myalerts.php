<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');


$errors = array();

if ($cookie->isLogged())
{
	if (Tools::getValue('action') == 'delete')
	{
		$id_customer = intval($cookie->id_customer);
		if (!$id_product = intval(Tools::getValue('id_product')))
			$errors[] = Tools::displayError('You need a product to delete an alert'); 
		$id_product_attribute = intval(Tools::getValue('id_product_attribute'));
		MailAlerts::deleteAlert($id_customer, $id_product, $id_product_attribute);
	}
	$smarty->assign('alerts', MailAlerts::getProductsAlerts(intval($cookie->id_customer), intval($cookie->id_lang)));
}
else
{
	$errors[] = Tools::displayError('You need to be logged to manage your alerts'); 
}

$smarty->assign('id_customer', intval($cookie->id_customer));
$smarty->assign('errors', $errors);
$smarty->display(dirname(__FILE__).'/myalerts.tpl');

include(dirname(__FILE__).'/../../footer.php');

?>
