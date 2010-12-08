<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/mailalerts.php');

$errors = array();

if ($cookie->isLogged())
{
	if (Tools::getValue('action') == 'delete')
	{
		$id_customer = (int)($cookie->id_customer);
		if (!$id_product = (int)(Tools::getValue('id_product')))
			$errors[] = Tools::displayError('You need a product to delete an alert'); 
		$id_product_attribute = (int)(Tools::getValue('id_product_attribute'));
		$customer = new Customer((int)($id_customer));
		MailAlerts::deleteAlert((int)($id_customer), 0, (int)($id_product), (int)($id_product_attribute));
	}
	$smarty->assign('alerts', MailAlerts::getProductsAlerts((int)($cookie->id_customer), (int)($cookie->id_lang)));
}
else
	$errors[] = Tools::displayError('You need to be logged in to manage your alerts'); 

$smarty->assign(array(
	'id_customer' => (int)($cookie->id_customer),
	'errors' => $errors
));

if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/mailalerts/myalerts.tpl'))
	$smarty->display(_PS_THEME_DIR_.'modules/mailalerts/myalerts.tpl');
elseif (Tools::file_exists_cache(dirname(__FILE__).'/myalerts.tpl'))
	$smarty->display(dirname(__FILE__).'/myalerts.tpl');
else
	echo Tools::displayError('No template found');

include(dirname(__FILE__).'/../../footer.php');
