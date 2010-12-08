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

class OrderFollowControllerCore extends FrontController
{
	public function __construct()
	{
		$this->auth = true;
		$this->authRedirection = 'order-follow.php';
		$this->ssl = true;
	
		parent::__construct();
	}
	
	public function preProcess()
	{
		parent::preProcess();
		
		if (Tools::isSubmit('submitReturnMerchandise'))
		{
			if (!$id_order = (int)(Tools::getValue('id_order')))
				Tools::redirect('history.php');
			if (!$order_qte_input = Tools::getValue('order_qte_input'))
				Tools::redirect('order-follow.php?errorDetail1');
			if ($customizationIds = Tools::getValue('customization_ids') AND !$customizationQtyInput = Tools::getValue('customization_qty_input'))
				Tools::redirect('order-follow.php?errorDetail1');
			if (!$ids_order_detail = Tools::getValue('ids_order_detail') AND !$customizationIds)
				Tools::redirect('order-follow.php?errorDetail2');

			$order = new Order((int)($id_order));
			if (!$order->isReturnable()) Tools::redirect('order-follow.php?errorNotReturnable');
			if ($order->id_customer != $this->cookie->id_customer)
				die(Tools::displayError());
			$orderReturn = new OrderReturn();
			$orderReturn->id_customer = (int)($this->cookie->id_customer);
			$orderReturn->id_order = $id_order;
			$orderReturn->question = strval(Tools::getValue('returnText'));
			if (empty($orderReturn->question))
				Tools::redirect('order-follow.php?errorMsg');
			if (!$orderReturn->checkEnoughProduct($ids_order_detail, $order_qte_input, $customizationIds, $customizationQtyInput))
				Tools::redirect('order-follow.php?errorQuantity');

			$orderReturn->state = 1;
			$orderReturn->add();
			$orderReturn->addReturnDetail($ids_order_detail, $order_qte_input, $customizationIds, $customizationQtyInput);
			Module::hookExec('orderReturn', array('orderReturn' => $orderReturn));
			Tools::redirect('order-follow.php');
		}

		$ordersReturn = OrderReturn::getOrdersReturn((int)($this->cookie->id_customer));
		if (Tools::isSubmit('errorQuantity'))
			$this->smarty->assign('errorQuantity', true);
		elseif (Tools::isSubmit('errorMsg'))
			$this->smarty->assign('errorMsg', true);
		elseif (Tools::isSubmit('errorDetail1'))
			$this->smarty->assign('errorDetail1', true);
		elseif (Tools::isSubmit('errorDetail2'))
			$this->smarty->assign('errorDetail2', true);
		elseif (Tools::isSubmit('errorNotReturnable'))
			$this->smarty->assign('errorNotReturnable',true);

		$this->smarty->assign('ordersReturn', $ordersReturn);
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addJS(array(_PS_JS_DIR_.'jquery/jquery.scrollto.js', _THEME_JS_DIR_.'history.js'));
	}

	public function displayContent()
	{
		parent::displayContent();
		$this->smarty->display(_PS_THEME_DIR_.'order-follow.tpl');
	}
}

