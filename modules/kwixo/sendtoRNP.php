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
require_once(dirname(__FILE__) . '/kwixo.php');
ini_set('default_charset', 'UTF-8');

global $cart, $cookie;
if (!isset($cookie->rnp_payment) AND $cookie->rnp_payment === false)
  Tools::redirect('order.php');
//unset($cookie->rnp_payment);

$kwixo = new Kwixo();
$fiakwixo = new FiaKwixo();
$customer = new Customer(intval($cart->id_customer));
$param = array('custom' => $cart->id, 'id_module' => $kwixo->id, 'amount' => $cart->getOrderTotal(true), 'secure_key' => $customer->secure_key);

$kwixo_categories = $kwixo->getKwixoCategories();
$products = $cart->getProducts();
$default_product_type = Configuration::get('RNP_DEFAULTCATEGORYID');
$invoice_address = new Address(intval($cart->id_address_invoice));
$delivery_address = new Address(intval($cart->id_address_delivery));
$carrier = new Carrier(intval($cart->id_carrier));
$currency = new Currency(intval($cart->id_currency));
$invoice_country = new Country(intval($invoice_address->id_country));
$delivery_country = new Country(intval($delivery_address->id_country));
$nb = 0;

foreach ($products as $product)
  $nb += $product['cart_quantity'];

$xml = '
';

//création du flux XML
$control = new KControl();

//utilisateur fact
$user_fact = new Utilisateur(
                'facturation',
                (($customer->id_gender == 1) ? 'monsieur' : 'madame'),
                $invoice_address->lastname,
                $invoice_address->firstname,
                $invoice_address->company,
                $invoice_address->phone,
                $invoice_address->phone_mobile,
                null,
                $customer->email
);
$control->childUtilisateur($user_fact);

//récupération des stats de l'utilisateur
$customer_stats = $customer->getStats();

//récupération des anciennes commandes de l'utilisateur
$all_orders = Order::getCustomerOrders((int) ($customer->id));

//instanciation de l'élément <sinteconso>
$siteconso = new Siteconso(
                $customer_stats['total_orders'],
                $customer_stats['nb_orders'],
                $all_orders[count($all_orders) - 1]['date_add'],
                (count($all_orders) > 1 ? $all_orders[1]['date_add'] : null)
);
$user_fact->childSiteconso($siteconso);

//adresse fact
$adr_fact = new Adresse(
                'facturation',
                $invoice_address->address1,
                $invoice_address->address2,
                $invoice_address->postcode,
                $invoice_address->city,
                $invoice_country->name[intval($cookie->id_lang)]
);
$control->childAdresse($adr_fact);

if (Configuration::get('RNP_CARRIER_TYPE_' . intval($carrier->id)) == 4) {
//utilisateur livr
  $user_livr = new Utilisateur(
                  'livraison',
                  (($customer->id_gender == 1) ? 'monsieur' : 'madame'),
                  $delivery_address->lastname,
                  $delivery_address->firstname,
                  $delivery_address->company,
                  $delivery_address->phone,
                  $delivery_address->phone_mobile,
                  null,
                  $customer->email
  );
  $control->childUtilisateur($user_livr);

//adresse livr
  $adr_livr = new Adresse(
                  'livraison',
                  $delivery_address->address1,
                  $delivery_address->address2,
                  $delivery_address->postcode,
                  $delivery_address->city,
                  $delivery_country->name[intval($cookie->id_lang)]
  );
  $control->childAdresse($adr_livr);
} else {
//  $carrier = new Carrier($cart->id_carrier);
//  var_dump($carrier);exit;
}

//infocommande
$infocommande = new Infocommande(
                $fiakwixo->getSiteid(),
                $cart->id,
                (string) $cart->getOrderTotal(true),
                null,
                null,
                $currency->iso_code
);
$control->childInfocommande($infocommande);

//liste produits
$listprod = new ProductList();
$alldownloadables = true;
foreach ($products as $product) {
  $sql = "SELECT * FROM " . _DB_PREFIX_ . "product_download WHERE id_product = '".(int)$product['id_product']."'";
  $res = Db::getInstance()->ExecuteS($sql);
  
  $alldownloadables = $alldownloadables && Db::getInstance()->NumRows() > 0;
  
  if (preg_match("/1\.4/", _PS_VERSION_))
    $product_categories = Product::getProductCategories(intval($product['id_product']));
  else
    $product_categories = Product::getIndexedCategories(intval($product['id_product']));

  $have_rnp_cat = false;

  foreach ($product_categories AS $category)
    if (array_key_exists($category['id_category'], $kwixo_categories)) {
      $have_rnp_cat = $category['id_category'];
      break;
    }

  $listprod->addProduit(
          $product['name'],
          array(
              'type' => ($have_rnp_cat !== false ? $have_rnp_cat : $default_product_type),
              'nb' => $product['cart_quantity'],
              'ref' => (((isset($product['reference']) AND !empty($product['reference'])) ? $product['reference'] : ((isset($product['ean13']) AND !empty($product['ean13'])) ? $product['ean13'] : Tools::toCamelCase($product['name'], true)))),
              'prixunit' => $product['price'],
          )
  );
}

//transport
$carrier_type = ($alldownloadables ? '5' : Configuration::get('SAC_CARRIER_TYPE_' . (int) ($carrier->id)));
$transport = new Transport(
                $carrier_type,
                $alldownloadables ? 'Téléchargement' : Tools::htmlentitiesUTF8($carrier->name),
                $alldownloadables ? '1' : '2',
                null
);

$infocommande->childTransport($transport);

$infocommande->childList($listprod);

//echo "<textarea cols='150' rows='50'>$control</textarea>";exit;
//wallet
$wallet = new Wallet(date('Y-m-d H:i:s'));
$control->childWallet($wallet);
//ajout de la date de livraison caculée à partir de la date définie par défaut dans la config
$control->addDatelivr($fiakwixo->generateDatelivr($control));
//ajout du crypt
$control->addCrypt($fiakwixo->generateCrypt($control), FiaKwixo::CRYPT_VERSION);

//option paiement
if (Tools::getValue('payment') == 2)
  $optionspaiement = new OptionsPaiement('credit');
elseif (Tools::getValue('payment') == 3)
  $optionspaiement = new OptionsPaiement('comptant', '0');
else
  $optionspaiement = new OptionsPaiement('comptant', '1', '1');

$control->addChild($optionspaiement);

//xmlparam
$xmlParam = new XMLElement("<ParamCBack></ParamCBack>");
foreach ($param as $key => $value) {
  $obj = new XMLElement("<obj></obj>");
  $obj->childName($key);
  $obj->childValue((string) $value);
  $xmlParam->childObj($obj);
}

echo $kwixo->l('<h1>Vous allez être redirigé sur la page de paiement dans quelques secondes. Merci de votre patience.</h1>');

echo $fiakwixo->getTransactionForm(
        $control,
        $xmlParam,
        'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/kwixo/push.php',
        'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/kwixo/payment_return.php',
        Form::SUBMIT_AUTO
);