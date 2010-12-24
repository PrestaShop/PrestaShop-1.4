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

class HistoryControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'history.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'history.css');
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.scrollTo-1.4.2-min.js', _THEME_JS_DIR_.'history.js'));
	}
	
	public function preProcess()
	{
		parent::preProcess();
		
		if (Tools::isSubmit('submitReorder') AND $id_order = (int)Tools::getValue('id_order'))
		{
			// Customer ID is also checked in order to avoid duplicating someone else order
			$id_cart = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `id_cart`
			FROM `'._DB_PREFIX_.'orders`
			WHERE `id_customer` = '.(int)$this->cookie->id_customer.'
			AND `id_order` = '.(int)$id_order);
			$oldCart = new Cart($id_cart);
			$duplication = $oldCart->duplicate();
			if (!$duplication OR !Validate::isLoadedObject($duplication['cart']))
				$this->errors[] = Tools::displayError('Sorry, we cannot renew your order');
			elseif (!$duplication['success'])
				$this->errors[] = Tools::displayError('Some items are missing and we cannot renew your order');
			else
			{
				$this->cookie->id_cart = $duplication['cart']->id;
				$this->cookie->write();
				Tools::redirect('order.php');
			}
		}
	}
	
	public function process()
	{
		parent::process();
		
		if ($orders = Order::getCustomerOrders((int)($this->cookie->id_customer)))
			foreach ($orders AS &$order)
			{
				$myOrder = new Order((int)($order['id_order']));
				if (Validate::isLoadedObject($myOrder))
					$order['virtual'] = $myOrder->isVirtual(false);
			}
		$this->smarty->assign(array(
			'orders' => $orders,
			'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
			'slowValidation' => Tools::isSubmit('slowvalidation')
		));
	}
	
	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'history.tpl');
	}
}

