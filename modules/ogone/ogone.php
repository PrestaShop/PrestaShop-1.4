<?php

class Ogone extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'ogone';
		$this->tab = 'Payment';
		$this->version = '2.0';

        parent::__construct();

        $this->displayName = 'Ogone';
        $this->description = '';
	}
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('payment') AND $this->registerHook('orderConfirmation'));
	}
	
	public function getContent()
	{
		if (Tools::isSubmit('submitOgone'))
		{
			Configuration::updateValue('OGONE_PSPID', Tools::getValue('OGONE_PSPID'));
			Configuration::updateValue('OGONE_SHA_IN', Tools::getValue('OGONE_SHA_IN'));
			Configuration::updateValue('OGONE_SHA_OUT', Tools::getValue('OGONE_SHA_OUT'));
			Configuration::updateValue('OGONE_MODE', (int)Tools::getValue('OGONE_MODE'));
			echo '<div class="conf confirm"><img src="../img/admin/ok.gif"/>'.$this->l('Configuration updated').'</div>'; //  Todo replace displayconf
		}
		
		return '
		<fieldset><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Help').'</legend>
			<p>'.$this->l('First of all, you might follow these steps:').'</p>
			<ol>
				<li>
					<h3>'.$this->l('Dans votre back office PrestaShop').'</h3>
					<ol>
						<li>'.$this->l('Indiquez votre identifiant Ogone (PSPID)').'</li>
						<li>'.$this->l('Indiquer les signatures de votre choix (vous devrez indiquer la même signature dans votre back office Ogone)').'</li>
						<li>'.$this->l('Choisissez le mode test lorsque votre compte n\'est pas en production (attention, il faut un vrai compte en mode test et non un compte de demo Ogone)').'</li>
					</ol>
				</li>
				<li>
					<h3>
						'.$this->l('Sur l\'interface Ogone ').' -
						<a href="https://secure.ogone.com/ncol/test/admin_ogone.asp"><span style="text-decoration:underline;color:#383838">'.$this->l('en mode test').'</span></a> /
						<a href="https://secure.ogone.com/ncol/prod/admin_ogone.asp"><span style="text-decoration:underline;color:#383838">'.$this->l('en mode production').'</span></a>
					</h3>
					<ol>
						<li>
							'.$this->l('Dans').' <i>'.$this->l('Contrôle des données et d\'origine').'</i> > <i>'.$this->l('URL de la page du marchand contenant le formulaire de paiement qui appellera la page').'</i>, '.$this->l('indiquez ').'<i>'.Tools::getHttpHost(true, true).__PS_BASE_URI__.'order.php</i>.
							'.$this->l('Attention, si vous avez plusieurs sous-domaines, indiquez les également en séparant les URLs avec un ";"').'.
						</li>
						<li>'.$this->l('Sur la même page dans').' <i>'.$this->l('Signature SHA-in').'</i>, '.$this->l('indiquez la même signature quand dans le back office PrestaShop (SHA-in).').' </li>
						<li>'.$this->l('Dans').' <i>'.$this->l('Retour des informations de transaction').'</i> > <i>'.$this->l('Redirection HTTP dans le navigateur').'</i>, '.$this->l('indiquez partout').' <i>'.Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/ogone/confirmation.php</i>.</li>
						<li>'.$this->l('Dans la même rubrique, cochez la case').' <i>'.$this->l('Je veux recevoir les paramètres de transaction en retour dans les URL lors de la redirection').'</i>.</li>
						<li>'.$this->l('Sur la même page dans').' <i>'.$this->l('Requête directe HTTP serveur-à-serveur').'</i>, '.$this->l('indiquez une requête').' <i>'.$this->l('toujours en ligne').'</i> '.$this->l('sur').' <i>'.Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/ogone/validation.php</i> '.$this->l('avec la méthode').' <i>GET</i>.</li>
						<li>'.$this->l('Sur la même page dans ').'<i>'.$this->l('Sécurité pour les paramètres de la requête').'</i>, '.$this->l('indiquez la même signature quand dans le back office PrestaShop (SHA-out).').'</li>
					</ol>
				</li>
			</ol>
			<h3>'.$this->l('Cartes de tests').'</h3>
			<ul>
				<li>Visa : 4111 1111 1111 1111</li>
				<li>Visa 3D : 4000 0000 0000 0002</li>
				<li>American Express : 3741 1111 1111 111</li>
				<li>MasterCard : 5399 9999 9999 9999</li>
				<li>Diners : 3625 5695 5800 17</li>
				<li>Bancontact/Mister : 67030000000000003</li>
				<li>Visa Purchasing : 4484 1200 0000 0029</li>
				<li>American Express : 3742 9101 9071 995</li>
			</ul>
		</fieldset>
		<div class="clear">&nbsp;</div>
		<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset><legend><img src="../img/admin/contact.gif" /> '.$this->l('Settings').'</legend>
				<label for="pspid">'.$this->l('PSPID').'</label>
				<div class="margin-form">
					<input type="text" id="pspid" size="20" name="OGONE_PSPID" value="'.Tools::getValue('OGONE_PSPID', Configuration::get('OGONE_PSPID')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="sha-in">'.$this->l('SHA-in signature').'</label>
				<div class="margin-form">
					<input type="text" id="sha-in" size="20" name="OGONE_SHA_IN" value="'.Tools::getValue('OGONE_SHA_IN', Configuration::get('OGONE_SHA_IN')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="sha-out">'.$this->l('SHA-out signature').'</label>
				<div class="margin-form">
					<input type="text" id="sha-out" size="20" name="OGONE_SHA_OUT" value="'.Tools::getValue('OGONE_SHA_OUT', Configuration::get('OGONE_SHA_OUT')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Mode').'</label>
				<div class="margin-form">
					<span style="display:block;float:left;margin-top:3px;"><input type="radio" id="test" name="OGONE_MODE" value="0" style="vertical-align:middle;display:block;float:left;margin-top:2px;margin-right:3px;"
						'.(!Tools::getValue('OGONE_MODE', Configuration::get('OGONE_MODE')) ? 'checked="checked"' : '').'
					/>
					<label for="test" style="color:#900;display:block;float:left;text-align:left;width:60px;">'.$this->l('Test').'</label>&nbsp;</span>
					<span style="display:block;float:left;margin-top:3px;">
					<input type="radio" id="production" name="OGONE_MODE" value="1" style="vertical-align:middle;display:block;float:left; margin-top:2px;margin-right:3px;"
						'.(Tools::getValue('OGONE_MODE', Configuration::get('OGONE_MODE')) ? 'checked="checked"' : '').'
					/>
					<label for="production" style="color:#080;display:block;float:left;text-align:left;width:85px;">'.$this->l('Production').'</label></span>
				</div>
				<div class="clear">&nbsp;</div>
				<input type="submit" name="submitOgone" value="'.$this->l('Update settings').'" class="button" />
			</fieldset>
		</form>
		<div class="clear">&nbsp;</div>';
	}
	
	public function hookPayment($params)
	{
		global $smarty;
		
		$currency = new Currency(intval($params['cart']->id_currency));
		$lang = new Language(intval($params['cart']->id_lang));
		$customer = new Customer(intval($params['cart']->id_customer));
		$address = new Address(intval($params['cart']->id_address_invoice));
		$country = new Country(intval($address->id_country), intval($params['cart']->id_lang));
		
		$ogoneParams = array();
		$ogoneParams['PSPID'] = Configuration::get('OGONE_PSPID');
		$ogoneParams['orderID'] = intval($params['cart']->id);
		$ogoneParams['amount'] = number_format(Tools::convertPrice(floatval(number_format($params['cart']->getOrderTotal(true, 3), 2, '.', '')), $currency), 2, '.', '') * 100;
		$ogoneParams['currency'] = $currency->iso_code;
		$ogoneParams['language'] = $lang->iso_code.'_'.strtoupper($lang->iso_code);
		$ogoneParams['CN'] = $customer->lastname;
		$ogoneParams['EMAIL'] = $customer->email;
		$ogoneParams['ownerZIP'] = $address->postcode;
		$ogoneParams['owneraddress'] = $address->address1;
		$ogoneParams['ownercty'] = $country->iso_code;
		$ogoneParams['ownertown'] = $address->city;
		$ogoneParams['ownertelno'] = $address->phone;
		$ogoneParams['SHASign'] = sha1($ogoneParams['orderID'].$ogoneParams['amount'].$ogoneParams['currency'].$ogoneParams['PSPID'].Configuration::get('OGONE_SHA_IN'));
		
		$smarty->assign('ogone_params', $ogoneParams);
		$smarty->assign('OGONE_MODE', Configuration::get('OGONE_MODE'));
		
		return $this->display(__FILE__, 'ogone.tpl');
    }
}

?>