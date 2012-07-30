<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license AND are unable to
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('prestassurance.php');
include_once('classes/psaRequest.php');
include_once('classes/psaDisaster.php');

global $cookie;

if (Tools::getValue('token') != sha1(_COOKIE_KEY_.'prestassurance_fo'.(int)$cookie->id_customer))
	die('INVALID TOKEN');

$psa = new prestassurance();

if (Tools::isSubmit('updatePopIn'))
{
	$cookie->psa_pop_in = 1;
}

if (Tools::isSubmit('displayDisasterForm'))
{
	die($psa->displayDisasterForm());
}

if (Tools::isSubmit('getOrderDisasterDetails') AND $id_order =  Tools::getValue('id_order'))
{
	die(Tools::jsonEncode($psa->getOrderDisasterDetails((int)$id_order)));
}

if (Tools::isSubmit('getStep3Details') AND $step_2 =  Tools::getValue('step_2'))
{
	die(Tools::jsonEncode($psa->getStep3Details($step_2)));
}

if (Tools::isSubmit('getFinalStepDetail') AND $key =  Tools::getValue('key'))
{
	die(Tools::jsonEncode($psa->getDocumentsForDisaster($key)));
}

if (Tools::isSubmit('submitDisaster') 
AND $id_order =  Tools::getValue('id_order') 
AND $id_product = Tools::getValue('id_product') 
AND $reason = Tools::getValue('disaster_reason'))
{
	if (Tools::getValue('email') && Validate::isEmail(Tools::getValue('email')))
		$comment = Tools::getValue('email');
	$id_customer = $cookie->id_customer;
	$customer = new Customer((int)$id_customer);
	$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_SHOP_NAME'));
	$comment .= ' '.Tools::getValue('disaster_comment');
	$phone = Tools::getValue('phone');

	$order = new Order((int)$id_order);
	if (Validate::isLoadedObject($order) AND $order->id_customer == $cookie->id_customer)
	{
		$exist = Db::getInstance()->getValue('SELECT `id_product`
							FROM `'._DB_PREFIX_.'psa_disaster` pdi
							WHERE pdi.`id_order` = '.(int)$id_order.' AND pdi.`id_product` = '.(int)$id_product);
		if ($exist)
			die(Tools::jsonEncode(
					array(
						'hasErrors' => true,
						'errors' => array($psa->lang('Product has already been declared'))
					)
				)
			);
		$orderObj = new Order((int)$id_order);
		$carrier = new Carrier((int)($orderObj->id_carrier));
		
		$disaster = new psaDisaster();
		$disaster->id_order = (int)$id_order;
		$disaster->id_product = (int)$id_product;
		$disaster->status = 'wait';
		$disaster->reason = $reason;

		$id_agreement = Db::getInstance()->getValue('SELECT `id_agreement`
			FROM `'._DB_PREFIX_.'psa_insurance_detail`
			WHERE `id_order` ='.(int)$id_order);
		$data = array(
			'id_agreement' => $id_agreement,
			'id_product' => $id_product,
			'reason' => $reason,
			'comment' => $comment,
			'phone' => $phone,
			'carrier_name' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
			'followup_link' => str_replace('@', $orderObj->shipping_number, $carrier->url)
		);

		$request = new psaRequest('disaster/add', 'POST', $data);
		$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
		$request->setPassword(Configuration::get('PSA_KEY'));
		$request->execute();
		$response = (array)Tools::jsonDecode($request->getResponseBody());
		if (!$response['hasErrors'])
		{
			$disaster->id_psa_disaster = $response['id_psa_disaster'];
			if ($disaster->add())
			{
				$psa->addDisasterComment($disaster->id, 1, $comment); 
				die(Tools::jsonEncode(array('hasErrors' => false)));
			}
			else
				die(Tools::jsonEncode(
					array(
						'hasErrors' => true,
						'errors' => array($psa->lang('Product has already been declared'))
						)
					));
		}
		else
			die(Tools::jsonEncode(array('hasErrors' => true,'errors' => array('INVALID ORDER'))));
	}
	else
		die(Tools::jsonEncode(
				array(
					'hasErrors' => true,
					'errors' => array('INVALID ORDER')
				)
			)
		);
}

if (Tools::isSubmit('addDisasterComment') 
AND $id_disaster =  Tools::getValue('id_disaster') 
AND $id_psa_disaster =  Tools::getValue('id_psa_disaster') 
AND $comment = Tools::getValue('comment'))
{
	if ($comment != '')
	{
		if ($psa->sendDisasterComment((int)$id_disaster, 1, $comment, (int)$id_psa_disaster))
			die(Tools::jsonEncode(array('hasErrors' => false)));
		else
			die(Tools::jsonEncode(array('hasErrors' => true)));
	}
	else
		die(Tools::jsonEncode(array('hasErrors' => true)));
}
