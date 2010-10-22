<?php

include('../../config/config.inc.php');
include('../../init.php');
include('../../header.php');

require_once(_PS_MODULE_DIR_ . 'socolissimo/socolissimo.php');

$validReturn = array('PUDOFOID','CECIVILITY','CENAME','CEFIRSTNAME', 'CECOMPANYNAME','CEEMAIL','CEPHONENUMBER', 'DELIVERYMODE','CEADRESS1','CEADRESS2','CEADRESS3','CEADRESS4',
				'CEZIPCODE','CEDOORCODE1','CEDOORCODE2','CEENTRYPHONE','DYPREPARATIONTIME','DYFORWARDINGCHARGES','ORDERID', 'SIGNATURE','ERRORCODE','TRPARAMPLUS','TRCLIENTNUMBER','PRID','PRNAME',
				'PRCOMPLADRESS','PRADRESS1','PRADRESS2','PRZIPCODE', 'PRTOWN','CETOWN','TRADERCOMPANYNAME', 'CEDELIVERYINFORMATION');

$errorMessage = array('001' => 'Identifiant FO manquant', '002' => 'Identifiant FO incorrect', '003' => 'Client non autorise', '004' => 'Champs obligatoire manquant', '006' => 'Signature manquante', 
'007' => 'Signature invalide', '008' => 'Code postal invalide', '009' => 'Format url retour Validation incorrect', '010' => 'Format url retour Echec incorrect', '011' => 'Numéro de transaction non valide', 
'012' => 'Format des frais d’expédition incorrect', '015' => 'Serveur Socolissimo non disponible', '016' => 'Serveur Socolissimo non disponible', '004' => 'Champs obligatoire manquant', '004' => 'Champs obligatoire manquant', );
			
//list of non-blocking error	
$nonBlockingError = array(133, 131, 517, 516, 515, 514, 513, 512, 511, 510, 509, 508, 507, 506, 505, 504, 503, 502, 501);

$return = array();
foreach ($_POST AS $key => $val)
	if (in_array(strtoupper($key),$validReturn))
		$return[strtoupper($key)] = utf8_encode(urldecode(stripslashes($val)));
		
if (isset($return['SIGNATURE']) AND isset($return['CENAME']) AND isset($return['DYPREPARATIONTIME']) AND isset($return['DYFORWARDINGCHARGES']) AND isset($return['TRCLIENTNUMBER']) AND isset($return['ORDERID']) AND isset($return['TRCLIENTNUMBER']))
{
	if (!isset($return['ERRORCODE']) OR $return['ERRORCODE'] == NULL OR in_array($return['ERRORCODE'],$nonBlockingError))
	{	
	
		if ($return['SIGNATURE'] === socolissimo::make_key($return['CENAME'],floatval($return['DYPREPARATIONTIME']),$return['DYFORWARDINGCHARGES'],$return['TRCLIENTNUMBER'], $return['ORDERID']))
		{
			global $cookie ;	
			if (isset($cookie) OR is_object($cookie))
			{
			
				if (saveOrderShippingDetails(intval($cookie->id_cart),intval($return['TRCLIENTNUMBER']),$return))
				{	
					$cart->id_carrier = intval($_POST['TRPARAMPLUS']);
					if($return['DELIVERYMODE'] == 'RDV')
					{
						$products = $cart->getProducts(false);
						foreach($products as $product)
						{
							$ids[] .= $product['id_product'];
						}
						if (!in_array(Configuration::get('SOCOLISSIMO_PRODUCT_ID'),$ids))
						{
							$product = new Product(Configuration::get('SOCOLISSIMO_PRODUCT_ID'));
							$product->price = Configuration::get('SOCOLISSIMO_OVERCOST');
							$product->update();
							$cart->updateQty(1, $product->id);
						}
					}
					$cart->update();
					
					Tools::redirect('order.php?step=3');
				}
				else
					echo '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;an error occurred
						 <p><br/><a href="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php" class="button_small" title="Retour">« Retour</a></p></div>';
			}
			else
				echo '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;an error occurred
						 <p><br/><a href="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php" class="button_small" title="Retour">« Retour</a></p></div>';
		}
		else
		{
			echo '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;invalide key
				  <p><br/><a href="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php" class="button_small" title="Retour">« Retour</a></p></div>';
		}
	}
	else
	{
		echo '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;an error occurred during shipping step : '
			 .str_replace('+',',',$errorMessage[$return['ERRORCODE']]).'
			 <p><br/>
			 <a href="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php" class="button_small" title="Retour">« Retour
			 </a></p></div>';
	}
}
else
	Tools::redirect();

include('../../footer.php');

function saveOrderShippingDetails($idCart, $idCustomer, $soParams)
{

	$deliveryMode = array('DOM' => 'Livraison à domicile', 'BPR' => 'Livraison en Bureau de Poste',
						  'A2P' => 'Livraison Commerce de proximité', 'MRL' => 'Livraison Commerce de proximité',
						  'CIT' => 'Livraison en Cityssimo', 'ACP' => 'Agence ColiPoste', 'CDI' => 'Centre de distribution',
						  'RDV' => 'Livraison sur Rendez-vous');
				  
	$db = Db::getInstance();
	$db->ExecuteS('SELECT * FROM '._DB_PREFIX_.'socolissimo_delivery_info WHERE id_cart = '.intval($idCart).' AND id_customer ='.intval($idCustomer));
	$numRows = intval($db->NumRows());
	if ($numRows == 0)
	{	
		$sql = 'INSERT INTO '._DB_PREFIX_.'socolissimo_delivery_info
										( `id_cart` ,`id_customer` ,`delivery_mode` ,`prid` ,`prname` ,`prfirstname`,`prcompladress` ,
										`pradress1` ,`pradress2` ,`przipcode` ,`prtown`, `cephonenumber`, `ceemail` , `cecompanyname`, `cedeliveryinformation`) 
										VALUES ('.intval($idCart).','.intval($idCustomer).',';
		if ($soParams['DELIVERYMODE'] != 'DOM' AND $soParams['DELIVERYMODE'] != 'RDV')
			$sql .= '\''.pSQL($soParams['DELIVERYMODE']).'\''.',
					'.(isset($soParams['PRID']) ? '\''.pSQL($soParams['PRID']).'\'' : '').',
					'.(isset($soParams['PRNAME']) ? '\''.ucfirst(pSQL($soParams['PRNAME'])).'\'' : '').',
					'.(isset($deliveryMode[$soParams['DELIVERYMODE']]) ? '\''.$deliveryMode[$soParams['DELIVERYMODE']].'\'' : 'So Colissimo').',
					'.(isset($soParams['PRCOMPLADRESS']) ? '\''.pSQL($soParams['PRCOMPLADRESS']).'\'' : '\'\'').',
					'.(isset($soParams['PRADRESS1']) ? '\''.pSQL($soParams['PRADRESS1']).'\'' : '\'\'').',
					'.(isset($soParams['PRADRESS2']) ? '\''.pSQL($soParams['PRADRESS2']).'\'' : '\'\'').',
					'.(isset($soParams['PRZIPCODE']) ? '\''.pSQL($soParams['PRZIPCODE']).'\'' : '\'\'').',
					'.(isset($soParams['PRTOWN']) ? '\''.pSQL($soParams['PRTOWN']).'\'' : '\'\'').',
					'.(isset($soParams['CEPHONENUMBER']) ? '\''.pSQL($soParams['CEPHONENUMBER']).'\'' : '\'\'').',
					'.(isset($soParams['CEEMAIL']) ? '\''.pSQL($soParams['CEEMAIL']).'\'' : '\'\'').',
					'.(isset($soParams['TRADERCOMPANYNAME']) ? '\''.pSQL($soParams['TRADERCOMPANYNAME']).'\'' : '\'\'').',
					'.(isset($soParams['CEDELIVERYINFORMATION']) ? '\''.pSQL($soParams['CEDELIVERYINFORMATION']).'\'' : '\'\'').')';
		else
			$sql .= '\''.pSQL($soParams['DELIVERYMODE']).'\',\'\',
					'.(isset($soParams['CENAME']) ? '\''.ucfirst(pSQL($soParams['CENAME'])).'\'' : '').',
					'.(isset($soParams['CEFIRSTNAME']) ? '\''.ucfirst(pSQL($soParams['CEFIRSTNAME'])).'\'' : '').',
					'.(isset($soParams['CECOMPLADRESS']) ? '\''.pSQL($soParams['CECOMPLADRESS']).'\'' : '\'\'').',
					'.(isset($soParams['CEADRESS3']) ? '\''.pSQL($soParams['CEADRESS3']).'\'' : '\'\'').',
					'.(isset($soParams['CEADRESS4']) ? '\''.pSQL($soParams['CEADRESS4']).'\'' : '\'\'').',
					'.(isset($soParams['CEZIPCODE']) ? '\''.pSQL($soParams['CEZIPCODE']).'\'' : '\'\'').',
					'.(isset($soParams['CETOWN']) ? '\''.pSQL($soParams['CETOWN']).'\'' : '\'\'').',
					'.(isset($soParams['CEPHONENUMBER']) ? '\''.pSQL($soParams['CEPHONENUMBER']).'\'' : '\'\'').',
					'.(isset($soParams['CEEMAIL']) ? '\''.pSQL($soParams['CEEMAIL']).'\'' : '\'\'').',
					'.(isset($soParams['TRADERCOMPANYNAME']) ? '\''.pSQL($soParams['TRADERCOMPANYNAME']).'\'' : '\'\'').',
					'.(isset($soParams['CEDELIVERYINFORMATION']) ? '\''.pSQL($soParams['CEDELIVERYINFORMATION']).'\'' : '\'\'').')';

	if (Db::getInstance()->Execute($sql))	
		return true;
	}
	else
		$sql = 'UPDATE `'._DB_PREFIX_.'socolissimo_delivery_info` SET `delivery_mode` =\''.pSQL($soParams['DELIVERYMODE']).'\' ';
		if (!in_array($soParams['DELIVERYMODE'], array('DOM', 'RDV')))
			$sql .= ' , '.(isset($soParams['PRID']) ? ' `prid` =\''.pSQL($soParams['PRID']).'\' , ' : '').
					(isset($soParams['PRNAME']) ? ' `prname` =\''.ucfirst(pSQL($soParams['PRNAME'])).'\' , ' : '').
					' `prfirstname` ='.(isset($deliveryMode['DELIVERYMODE']) ? '\''.$deliveryMode[$soParams['DELIVERYMODE']].'\'' : '\'So Colissimo').'\', '.
					(isset($soParams['PRCOMPLADRESS']) ? ' `prcompladress` =\''.pSQL($soParams['PRCOMPLADRESS']).'\' , ' : '').
					(isset($soParams['PRADRESS1']) ? ' `pradress1` =\''.pSQL($soParams['PRADRESS1']).'\' , ' : '').
					(isset($soParams['PRADRESS2']) ? ' `pradress2` =\''.pSQL($soParams['PRADRESS2']).'\' , ' : '').
					(isset($soParams['PRZIPCODE']) ? ' `przipcode` =\''.pSQL($soParams['PRZIPCODE']).'\' , ' : '').
					(isset($soParams['CETOWN']) ? ' `prtown` =\''.pSQL($soParams['CETOWN']).'\' , ' : '').
					(isset($soParams['CEPHONENUMBER']) ? ' `cephonenumber` =\''.pSQL($soParams['CEPHONENUMBER']).'\' , ' : '').
					(isset($soParams['CEEMAIL']) ? ' `ceemail` =\''.pSQL($soParams['CEEMAIL']).'\' , ' : '').
					(isset($soParams['CEDELIVERYINFORMATION']) ? ' `cedeliveryinformation` =\''.pSQL($soParams['CEDELIVERYINFORMATION']).'\' , ' : '').
					(isset($soParams['TRADERCOMPANYNAME']) ? ' `cephonenumber` =\''.pSQL($soParams['TRADERCOMPANYNAME']).'\' ' : '');
		else
			$sql .= ','.(isset($soParams['CENAME']) ? ' `prname` =\''.ucfirst(pSQL($soParams['CENAME'])).'\' , ' : '').
					(isset($soParams['CEFIRSTNAME']) ? ' `prfirstname` =\''.ucfirst(pSQL($soParams['CEFIRSTNAME'])).'\' , ' : '').
					(isset($soParams['CECOMPLADRESS']) ? ' `prcompladress` =\''.pSQL($soParams['CECOMPLADRESS']).'\' , ' : '').
					(isset($soParams['CEADRESS3']) ? ' `pradress1` =\''.pSQL($soParams['CEADRESS3']).'\' , ' : '').
					(isset($soParams['CEADRESS']) ? ' `pradress2` =\''.pSQL($soParams['CEADRESS']).'\' , ' : '').
					(isset($soParams['CEZIPCODE']) ? ' `przipcode` =\''.pSQL($soParams['CEZIPCODE']).'\' , ' : '').
					(isset($soParams['PRTOWN']) ? ' `prtown` =\''.pSQL($soParams['PRTOWN']).'\' , ' : '').
					(isset($soParams['CEEMAIL']) ? ' `ceemail` =\''.pSQL($soParams['CEEMAIL']).'\' , ' : '').
					(isset($soParams['CEPHONENUMBER']) ? ' `cephonenumber` =\''.pSQL($soParams['CEPHONENUMBER']).'\' , ' : '').
					(isset($soParams['CEDELIVERYINFORMATION']) ? ' `cedeliveryinformation` =\''.pSQL($soParams['CEDELIVERYINFORMATION']).'\' , ' : '').
					(isset($soParams['TRADERCOMPANYNAME']) ? ' `cephonenumber` =\''.pSQL($soParams['TRADERCOMPANYNAME']).'\' ' : '');
	
		$sql .= ' WHERE `id_cart` =\''.intval($idCart).'\' AND `id_customer` =\''.intval($idCustomer).'\'';

		if (Db::getInstance()->Execute($sql))
			return true;
}


?>
