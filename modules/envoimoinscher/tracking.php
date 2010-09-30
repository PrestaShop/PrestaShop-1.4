<?php

include('../../config/config.inc.php');
include('../../init.php');

global $cookie;

$validReturn = array('infoexterne','token','etat','envoi');

$return = array();
foreach ($_GET AS $key => $val)
	if (in_array(strtolower($key),$validReturn))
		$return[strtolower($key)] = utf8_encode(urldecode(stripslashes($val)));
		
if (isset($return['infoexterne']) AND isset($return['token']) AND isset($return['etat']))
{	
	$id_order = str_replace(str_replace('.','_',str_replace('www.','',$_SERVER['HTTP_HOST'])).'_','',$return['infoexterne']);
	
	$order = new Order(intval($id_order));
	$customer = new Customer(intval($order->id_customer));
	$confs = Configuration::getMultiple(array('EMC_SEND_STATE', 'EMC_ORDER_PAST_STATE', 'EMC_DELIVERY_STATE'));
	
	if ($customer->secure_key != $return['token'])
		d(Tools::displayError('Hack attempt'));
	else
	{
		switch($return['etat'])
		{
			//commande passŽe
			case 'CMD' :
				$history = new OrderHistory();
				$history->id_order = intval($id_order);
				$history->changeIdOrderState(intval($confs['EMC_ORDER_PAST_STATE']), intval($history->id_order));
				$history->id_employee = intval($cookie->id_employee);
				$history->addWithemail();
				
				$db = Db::getInstance();
				$db->ExecuteS('SELECT * FROM '._DB_PREFIX_.'envoimoinscher WHERE id_order = '.intval($id_order));
				$numRows = intval($db->NumRows());
				if ($numRows == 0)
				{
					if (Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'envoimoinscher VALUES (\''.intval($id_order).'\', \''.$return['envoi'].'\');'));
				}
				else
				{
					if (Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'envoimoinscher SET shipping_number=\''.$return['envoi'].'\' WHERE id_order=\''.intval($id_order).'\' '));	
				}
			
			break;
			//colis (ou autre objet) envoyŽ
			case 'ENV' :
				$history = new OrderHistory();
				$history->id_order = intval($id_order);
				$history->changeIdOrderState(intval($confs['EMC_SEND_STATE']), intval($history->id_order));
				$history->id_employee = intval($cookie->id_employee);
				$history->addWithemail();
			break;
			//envoi annulŽ
			case 'ANN' :
				$message = new Message();
				$texte = 'Envoi Moins cher : envoi annulŽ';
				$message->message = htmlentities($texte, ENT_COMPAT, 'UTF-8');
				$message->id_order = intval($id_order);
				$message->private = 1;
				$message->add();
			break;
			//objet livrŽ (pas gŽrŽ actuellement)
				case 'LIV' :
				$history = new OrderHistory();
				$history->id_order = intval($id_order);
				$history->changeIdOrderState(intval($confs['EMC_DELIVERY_STATE']), intval($history->id_order));
				$history->id_employee = intval($cookie->id_employee);
				$history->addWithemail();
			break;
	
		
		}	
	
	
	}
}
else
d(Tools::displayError('Hack attempt'));

?>