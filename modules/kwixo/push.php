<?php
/*
* 2007-2011 PrestaShop 
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7233 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__) . '/../../config/config.inc.php');

require_once(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/kwixo.php');


$payment = new kwixo();
$fiakwixo = new FiaKwixo();
$md5 = new HashMD5();

if ($fiakwixo->getAuthKey() == '14c059d00677890ffbb56d9b0759e8b4' || $fiakwixo->getAuthKey() == '')
	die('Invalid AuthKey');

$waitedhash = $md5->hash($fiakwixo->getAuthKey() . Tools::getValue('RefID') . Tools::getValue('TransactionID'));
$receivedhash = Tools::getValue('HashControl', '0');

//si le hashcontrol est mauvais : arrêt du script, aucun traitement ne doit être effectué
if ($waitedhash != $receivedhash) {
  //logging de l'erreur
  FiaKwixo::insertLog(__FILE__ . ' : ' . __LINE__, 'URLSys erreur : HashControl invalide (valeur attendue = "' . $waitedhash . '", valeur reçue = "' . $receivedhash . '"). IP expediteur : ' . Tools::getRemoteAddr());
  exit;
}

/** si le hashControl est valide * */
//récupération des variables nécessaires
$tag = Tools::getValue('Tag');
$refid = Tools::getValue('RefID');
$transactionid = Tools::getValue('TransactionID');
$id_cart = Tools::getValue('custom', false);
$amount = Tools::getValue('amount', false);


//si le panier n'existe pas on stoppe le script
$cart = new Cart(intval($id_cart));
if (!$cart->id) {
  //logging de l'erreur
  FiaKwixo::insertLog(__FILE__ . ' : ' . __LINE__, "Le panier pour la commande $refid/$transactionid n'existe pas.");
  exit;
}

global $cookie;

//récupération de l'order id
$id_order = Order::getOrderByCartId((int)$cart->id);
if ($id_order !== false) {
  $order = new Order(intval($id_order));
}

switch ($tag) {
  //abandon du paiement par l'internaute 1h auparavant, aucune commande créée, aucune action
  case 0:
    break;

//paiement refusé
  case 2:
    //si la commande existe déjà alors un autre tag a déjà été reçu, pas de traitement à faire
    if ($id_order === false)
      $psosstatus = intval(_PS_OS_CANCELED_);
    break;

  //flux validé, commande prise en compte
  case 4:
    //si la commande existe déjà en statut annulé ou si la commande n'existe pas on traite, sinon aucun traitement à faire
    if ($id_order === false || $order->getCurrentState() == intval(_PS_OS_CANCELED_))
      $psosstatus = intval(Configuration::get('KW_OS_WAITING'));
    break;
  //demande de paiement à crédit, crédit à l'étude
  case 6:
    //si la commande existe déjà en statut annulé ou attente kwixo, ou si la commande n'existe pas on traite, sinon aucun traitement à faire
    if ($id_order === false || in_array($order->getCurrentState(), array(intval(_PS_OS_CANCELED_), intval(Configuration::get('KW_OS_WAITING')))))
      $psosstatus = intval(Configuration::get('KW_OS_CREDIT'));
    break;
  //commande à risque, contrôle en cours
  case 3:
    //si la commande existe déjà en statut annulé ou attente kwixo ou crédit en étude, ou si la commande n'existe pas on traite, sinon aucun traitement à faire
    if ($id_order === false || in_array($order->getCurrentState(), array(intval(_PS_OS_CANCELED_), intval(Configuration::get('KW_OS_WAITING')), intval(Configuration::get('KW_OS_CREDIT')))))
      $psosstatus = intval(Configuration::get('KW_OS_CONTROL'));
    break;

  //paiement accepté
  case 1:
  case 13:
  case 14:
  case 10:
    //récupération du score si présent
    $score = Tools::getValue('Score', false);
    //si la commande existe déjà en statut annulé ou attente kwixo ou crédit en étude ou contrôle en cours, ou si la commande n'existe pas on traite, sinon aucun traitement à faire
    if ($id_order === false || in_array($order->getCurrentState(), array(intval(_PS_OS_CANCELED_), intval(Configuration::get('KW_OS_WAITING')), intval(Configuration::get('KW_OS_CREDIT')), intval(Configuration::get('KW_OS_CONTROL')))))
      if ($score == 'positif')
        $psosstatus = intval(Configuration::get('KW_OS_PAYMENT_GREEN'));
      elseif ($score == 'negatif')
        $psosstatus = intval(Configuration::get('KW_OS_PAYMENT_RED'));
      else
        $psosstatus = intval(_PS_OS_PAYMENT_);
    break;

  //paiement refusé
  case 11:
  case 12:
    //si la commande existe déjà en statut annulé ou attente kwixo ou crédit en étude ou contrôle en cours, ou si la commande n'existe pas on traite, sinon aucun traitement à faire
    if ($id_order === false || in_array($order->getCurrentState(), array(intval(_PS_OS_CANCELED_), intval(Configuration::get('KW_OS_WAITING')), intval(Configuration::get('KW_OS_CREDIT')), intval(Configuration::get('KW_OS_CONTROL')))))
      $psosstatus = intval(_PS_OS_CANCELED_);
    break;

  //annulation du paiement
  case 101:
      $psosstatus = intval(_PS_OS_CANCELED_);
    break;

  //débit internaute effectué
  case 100:
    if ($id_order === false || !in_array($order->getCurrentState(), array(intval(_PS_OS_DELIVERED_), intval(_PS_OS_PREPARATION_), intval(_PS_OS_SHIPPING_), intval(_PS_OS_PAYMENT_))))
      $psosstatus = intval(_PS_OS_PAYMENT_);
    break;

  default:
    break;
}

if (isset($psosstatus)) {
  if ($id_order === false) {
    $feedback = 'Order Create';
    $payment->validateOrder(intval($cart->id), $psosstatus, $amount, $payment->displayName, $feedback, NULL, $cart->id_currency);
    if ($cookie->id_cart == intval($cookie->last_id_cart))
      unset($cookie->id_cart);
  }else {
			$orderHistory = new OrderHistory();
			$orderHistory->id_order = intval($id_order);
			$orderHistory->id_order_state = $psosstatus;
			$orderHistory->save();
			$orderHistory->changeIdOrderState($psosstatus, intval($id_order));
  }
}