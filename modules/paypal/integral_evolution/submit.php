<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');

if (_PS_VERSION_ < '1.5')
{
	require_once(_PS_ROOT_DIR_ . '/controllers/OrderConfirmationController.php');
}

class PayPalIntegralEvolutionSubmit extends OrderConfirmationControllerCore
{

	public function __construct()
	{
		$this->paypal = new PayPal();
		$this->context = Context::getContext();
		parent::__construct();
		$this->run();
	}

	public function getPayPalOrder($id_order)
	{
		$query = 'SELECT * FROM `'._DB_PREFIX_.'paypal_order`
			WHERE `id_order` = '.(int)$id_order;

		return Db::getInstance()->getRow($query);
	}

	public function displayContent()
	{
		$id_order = (int)Tools::getValue('id_order');

		$this->context->smarty->assign(
			array(
				'currency' => $this->context->currency,
				'order' => $this->getPayPalOrder($id_order)
			)
		);

		echo $this->paypal->fetchTemplate('/views/templates/front/integral_evolution/', 'order-confirmation');
	}

}

if (Tools::getValue('id_module') && Tools::getValue('key') && Tools::getValue('id_cart') && Tools::getValue('id_order'))
{
	if (_PS_VERSION_ < '1.5')
	{
		new PayPalIntegralEvolutionSubmit();
	}
}
else if ($id_cart = Tools::getValue('id_cart'))
{
	$paypal = new PayPal();
	$context = Context::getContext();
	$customer = new Customer((int)$context->customer->id);
	$id_order = Order::getOrderByCartId((int)$id_cart);

	// Redirection
	$array = array(
		'key' => $customer->secure_key,
		'id_module' => (int)$paypal->id,
		'id_cart' => (int)$id_cart,
		'id_order' => (int)$id_order
	);

	$query = http_build_query($array, '', '&');

	if (_PS_VERSION_ < '1.5')
	{
		Tools::redirectLink(__PS_BASE_URI__ . '/modules/paypal/integral_evolution/submit.php?' . $query);
	}
	else
	{
		$controller = new FrontController();
		$controller->init();
		Tools::redirect(Context::getContext()->link->getModuleLink('paypal', 'submit', $values));
		//Tools::redirect('index.php?'.$query);
	}
}
else
{
	Tools::redirectLink(__PS_BASE_URI__);
}

die();
