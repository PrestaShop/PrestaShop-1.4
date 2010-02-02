<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/gcheckout.php');
require_once(dirname(__FILE__).'/library/googleresponse.php');
require_once(dirname(__FILE__).'/library/googlemerchantcalculations.php');
require_once(dirname(__FILE__).'/library/googleresult.php');
require_once(dirname(__FILE__).'/library/googlerequest.php');

$gcheckout = new GCheckout();

$merchant_id = Configuration::get('GCHECKOUT_MERCHANT_ID');
$merchant_key = Configuration::get('GCHECKOUT_MERCHANT_KEY');
$server_type = Configuration::get('GCHECKOUT_MODE');
$currency = $gcheckout->getCurrency();

$Gresponse = new GoogleResponse($merchant_id, $merchant_key);
$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type, $currency);

//Setup the log file
if (Configuration::get('GCHECKOUT_LOGS'))
	$Gresponse->SetLogFiles('googleerror.log', 'googlemessage.log', L_ALL);

// Retrieve the XML sent in the HTTP POST request to the ResponseHandler
$xml_response = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA:file_get_contents("php://input");
if (get_magic_quotes_gpc())
	$xml_response = stripslashes($xml_response);

list($root, $data) = $Gresponse->GetParsedXML($xml_response);
$Gresponse->SetMerchantAuthentication($merchant_id, $merchant_key);

$status = $Gresponse->HttpAuthentication();
if(!$status)
	die('authentication failed');
	
  /* Commands to send the various order processing APIs
   * Send charge order : $Grequest->SendChargeOrder($data[$root]
   *    ['google-order-number']['VALUE'], <amount>);
   * Send process order : $Grequest->SendProcessOrder($data[$root]
   *    ['google-order-number']['VALUE']);
   * Send deliver order: $Grequest->SendDeliverOrder($data[$root]
   *    ['google-order-number']['VALUE'], <carrier>, <tracking-number>,
   *    <send_mail>);
   * Send archive order: $Grequest->SendArchiveOrder($data[$root]
   *    ['google-order-number']['VALUE']);
   *
   */

  switch ($root) {
    case "request-received": {
      break;
    }
    case "error": {
      break;
    }
    case "diagnosis": {
      break;
    }
    case "checkout-redirect": {
      break;
    }
    case "merchant-calculation-callback": {
      break;
    }
    case "new-order-notification": {
		$gcheckout = new GCheckout();
		$id_cart = intval($data[$root]['shopping-cart']['merchant-private-data']['VALUE']);
		$cart = new Cart($id_cart);
		$orderTotal = floatval($data[$root]['order-total']['VALUE']);
		$gcheckout->validateOrder($id_cart, _PS_OS_PAYMENT_, $cart->getOrderTotal(), $gcheckout->displayName);
		
		$Gresponse->SendAck();
		break;
    }
    case "order-state-change-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "charge-amount-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "chargeback-amount-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "refund-amount-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "risk-information-notification": {
      $Gresponse->SendAck();
      break;
    }
    default:
      $Gresponse->SendBadRequestStatus("Invalid or not supported Message");
      break;
  }
  
  
  /* In case the XML API contains multiple open tags
     with the same value, then invoke this function and
     perform a foreach on the resultant array.
     This takes care of cases when there is only one unique tag
     or multiple tags.
     Examples of this are "anonymous-address", "merchant-code-string"
     from the merchant-calculations-callback API
  */
  function get_arr_result($child_node) {
    $result = array();
    if(isset($child_node)) {
      if(is_associative_array($child_node)) {
        $result[] = $child_node;
      }
      else {
        foreach($child_node as $curr_node){
          $result[] = $curr_node;
        }
      }
    }
    return $result;
  }

  /* Returns true if a given variable represents an associative array */
  function is_associative_array( $var ) {
    return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
  }

?>