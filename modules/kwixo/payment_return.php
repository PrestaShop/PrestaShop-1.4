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

global $smarty;

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/../../header.php');
require_once(dirname(__FILE__) . '/kwixo.php');

$url = 'order-confirmation.php?';

$refid = Tools::getValue('RefID');
$transactionid = Tools::getValue('TransactionID');

$payment = new kwixo();
$fiakwixo = new FiaKwixo();
$md5 = new HashMD5();

if ($fiakwixo->getAuthKey() == '14c059d00677890ffbb56d9b0759e8b4' || $fiakwixo->getAuthKey() == '')
	die('Invalid AuthKey');

$waitedhash = $md5->hash($fiakwixo->getAuthKey() . $refid . $transactionid);
$receivedhash = Tools::getValue('HashControl', '0');

$errors = array();

//si le hash est invalide on abandonne tout traitement
if ($waitedhash != $receivedhash) {
  //définition de l'erreur
  $errors[] = $payment->displayName . $payment->l('hash control invalid (data do not come from Kwixo)') . "\n";
  //logging de l'erreur
  FiaKwixo::insertLog(__FILE__ . ' : ' . __LINE__, 'HashControl invalide pour la commande ' . Tools::getValue('RefID'));
  //redirection de l'internaute sur la page d'erreur
  $url = 'order.php';
}

//traitement en cas de bon hashcontrol
//récupération du tag
$tag = Tools::getValue('Tag', false);


//si l'id cart est bien renseigné on le stock, sinon on enregistre une erreur
if (!Tools::getValue('custom'))
  $errors[] = $payment->displayName . ' ' . $payment->l('key "custom" not specified, can\'t rely to cart') . "\n";
else
  $id_cart = intval(Tools::getValue('custom'));

//si l'id module est bien renseigné on le stock, sinon on enregistre une erreur
if (!Tools::getValue('id_module'))
  $errors[] = $payment->displayName . ' ' . $payment->l('key "module" not specified, can\'t rely to payment module') . "\n";
else
  $id_module = intval(Tools::getValue('id_module'));

//si le montant payé est bien renseigné on le stock, sinon on enregistre une erreur
if (!isset($_POST['amount']))
  $errors[] = $payment->displayName . ' ' . $payment->l('"amount" not specified, can\'t control the amount paid') . "\n";
else
  $amount = floatval(Tools::getValue('amount'));


//si aucune erreur n'a été rencontrée
if (empty($errors)) {
  //instanciation du panier payé
  $cart = new Cart((int)$id_cart);

  //si aucun panier trouvé on enregistre une erreur
  if (!$cart->id)
    $errors[] = $payment->l('cart not found') . "\n";
}

//si toujours aucune erreur rencontrée
if (count($errors)==0) {
  //traitement en fonction du tag Kwixo reçu
  switch ($tag) {
//abandon de paiement par l'internaute ou refus de paiement par la banque -> retour vers le panier sans création de commande
    case '0':
    case'2':
      $errors[] = $payment->l('Your payment has been ' . ($tag == '0' ? 'cancelled' : 'refused') . '.');
      //complétion de l'url de retour
      $url .= 'error=true';
      break;

//paiement finalisé -> création de la commande "paiement en attente" et retour vers page de confirmation
    case '1':
      $id_order = Order::getOrderByCartId($id_cart);
      //si la commande n'existe pas, création de l'objet en statut attente de paiement
      if (!$id_order) {
        FiaKwixo::insertLog(__FILE__ . ' : ' . __LINE__, 'Order ne semble pas exister : $order->id = ' . $id_order);
        $feedback = $payment->l('Transaction OK:') . ' RefID=' . $refid . ' & TransactionID=' . $transactionid;
        //validation de la commande
        $payment->validateOrder(intval($cart->id), intval(Configuration::get('KW_OS_WAITING')), $amount, $payment->displayName, $feedback, NULL, $cart->id_currency);
      }
      //vidage du panier
      if ($cookie->id_cart == intval($cookie->last_id_cart))
        unset($cookie->id_cart);

      $customer = new Customer(intval($cart->id_customer));
      //compéltion de l'url de retour
      $url.= 'id_cart=' . $id_cart . '&id_module=' . $id_module . '&key=' . $customer->secure_key;
      break;

    //pour toute autre valeur de tag (inconnue)
    default:
      //complétion de l'url de retour
      $url.= 'error=true';
      //enregistrement de l'erreur
      $errors[] = $payment->l('One or more error occured during the validation') . "\n";
      //logging de l'erreur
      FiaKwixo::insertLog(__FILE__ . ' : ' . __LINE__, 'Tag inconnu "' . $tag . '" reçu.');
      //création de la commande en annulé
      $payment->validateOrder($id_cart, intval(_PS_OS_CANCELED_), $amount, $payment->displayName, $errors);
      //vidage du panier
      if ($cookie->id_cart == intval($cookie->last_id_cart))
        unset($cookie->id_cart);
      break;
  }
} else { //en cas d'erreur
  //complétion de l'url de retour
  $url.= 'error=true';
  //enregistrement de l'erreur
  $errors[] = $payment->l('One or more error occured during the validation') . "\n";
  //vidage du panier
  if ($cookie->id_cart == intval($cookie->last_id_cart))
    unset($cookie->id_cart);
}


$smarty->assign('url', $url);
$smarty->assign('errors', $errors);
echo $smarty->display(dirname(__FILE__).'/payment_return.tpl');
require_once(dirname(__FILE__) . '/../../footer.php');