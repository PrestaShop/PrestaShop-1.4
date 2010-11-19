<?php

class Socolissimo extends Module
{
	private $_html = '';
	private $_postErrors = array();
	private $url = '';
	public $_errors = array();
	public $errorMessage = array();
	
	private $_config = array(
		'name' => 'La Poste - So Colissimo',
		'id_tax' => 1,
		'url' => 'http://www.colissimo.fr/portail_colissimo/suivreResultat.do?parcelnumber=@',
		'active' => true,
		'deleted' => 0,
		'shipping_handling' => false,
		'range_behavior' => 0,
		'is_module' => false,
		'delay' => array('fr'=>'Avec La Poste, Faites-vous livrer là où vous le souhaitez en France Métropolitaine.',
						 'en'=>'Do you deliver wherever you want in France.'),
		'id_zone' => 1,
		'shipping_external'=>false,
		'external_module_name'=> '',
		'need_range' => false
		);
		
	function __construct()
	{
		global $cookie;
		
		$this->name = 'socolissimo';
		$this->tab = 'shipping_logistics';
		$this->version = '1.5';
		$this->limited_countries = array('fr');
		$this->needRange = true;

		parent::__construct ();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('So Colissimo');
		$this->description = $this->l('Offer to your customers, different delivery methods with LaPoste.');
		$this->url = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php';

		if (self::isInstalled($this->name))
		{
			$ids = array();
			$carriers = Carrier::getCarriers($cookie->id_lang, false);
			foreach($carriers as $carrier)
				$ids[] .= $carrier['id_carrier'];
			$warning = array();
			if (!in_array(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')),$ids))
				$warning[] .= $this->l('\'Carrier correspondence\'').' ';
			$soCarrier = new Carrier(Configuration::get('SOCOLISSIMO_CARRIER_ID'));
			if (Validate::isLoadedObject($soCarrier))
			{
				if (!$this->checkZone(intval($soCarrier->id)))
					$warning[] .= $this->l('\'Carrier Zone(s)\'').' ';
				if (!$this->checkGroup(intval($soCarrier->id)))
					$warning[] .= $this->l('\'Carrier Group\'').' ';
				if (!$this->checkRange(intval($soCarrier->id)))
					$warning[] .= $this->l('\'Carrier Rage(s)\'').' ';	
				if (!$this->checkDelivery(intval($soCarrier->id)))
					$warning[] .= $this->l('\'Carrier price delivery\'').' ';
			}	

			//Check config and display warning
			if (!Configuration::get('SOCOLISSIMO_ID'))
				$warning[] .= $this->l('\'Id FO\'').' ';
			if (!Configuration::get('SOCOLISSIMO_KEY'))
				$warning[] .= $this->l('\'Key\'').' ';
			if (!Configuration::get('SOCOLISSIMO_URL'))
				$warning[] .= $this->l('\'Url So\'').' ';
				
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
			$this->errorMessage = array('998' => $this->l('Invalide key'), '999' => $this->l('an error occurred during shipping step'), '001' => $this->l('Login FO missing'),
			 '002' => $this->l('Lofin FO incorrect '), '003' => $this->l('Customer unauthorized'),'004' => $this->l('Required field missing'), '006' => $this->l('Missing signature'),
			  '007' => $this->l('Invalid signature'), '008' => $this->l('Zip Code invalid'), '009' => $this->l('Incorrect url format return validation'), '010' => $this->l('Incorrect url format return error'),
			   '011' => $this->l('Numéro de transaction non valide'), '012' => $this->l('Format incorrect shipping costs'), '015' => $this->l('Socolissimo server unavailable'),
			    '016' => $this->l('Socolissimo server unavailable'), '004' => $this->l('Required field missing'), '004' => $this->l('Required field missing'));

	}
	
	public function install()
	{
		global $cookie;

		if (!parent::install() OR !Configuration::updateValue('SOCOLISSIMO_ID', NULL) OR !Configuration::updateValue('SOCOLISSIMO_KEY', NULL)
		 OR !Configuration::updateValue('SOCOLISSIMO_URL', 'https://ws.colissimo.fr/pudo-fo/storeCall.do') OR !Configuration::updateValue('SOCOLISSIMO_PREPARATION_TIME', 1) 
		 OR !Configuration::updateValue('SOCOLISSIMO_OVERCOST', 3.01) OR !$this->registerHook('extraCarrier') OR !$this->registerHook('AdminOrder') OR !$this->registerHook('updateCarrier')
		 OR !$this->registerHook('newOrder') OR !Configuration::updateValue('SOCOLISSIMO_SUP_URL', 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp')
		 OR !Configuration::updateValue('SOCOLISSIMO_SUP', true))
			return false;
			
			
		//creat config table in database
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'socolissimo_delivery_info` (
				  `id_cart` int(10) NOT NULL,
				  `id_customer` int(10) NOT NULL,
				  `delivery_mode` varchar(3) NOT NULL,
				  `prid` text(10) NOT NULL,
				  `prname` varchar(64) NOT NULL,
				  `prfirstname` varchar(64) NOT NULL,
				  `prcompladress` text NOT NULL,
				  `pradress1` text NOT NULL,
				  `pradress2` text NOT NULL,
				  `pradress3` text NOT NULL,
				  `pradress4` text NOT NULL,
				  `przipcode` text(10) NOT NULL,
				  `prtown` varchar(64) NOT NULL,
				  `cephonenumber` varchar(10) NOT NULL,
				  `ceemail` varchar(64) NOT NULL,
				  `cecompanyname` varchar(64) NOT NULL,
				  `cedeliveryinformation` text NOT NULL,
				  `cedoorcode1` varchar(10) NOT NULL,
				  `cedoorcode2` varchar(10) NOT NULL,
				  PRIMARY KEY  (`id_cart`,`id_customer`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		
		if(!Db::getInstance()->Execute($sql))
			return false;
		
		//add carrier in back office
		if(!$this->createSoColissimoCarrier($this->_config))
			return false;

		//add hidden category
		$category = new Category();
		$languages = Language::getLanguages(true);
			foreach ($languages as $language) 
			{
				if ($language['iso_code'] == 'fr')
				{
					$category->name[$language['id_lang']] = 'SoColissimo';
					$category->link_rewrite[$language['id_lang']] = 'socolissimo';
				}
				if ($language['iso_code'] == 'en')
				{
					$category->name[$language['id_lang']] = 'SoColissimo';
					$category->link_rewrite[$language['id_lang']] = 'socolissimo';
				}
			}

		$category->link_rewrite = 'socolissimo';
		$category->id_parent = 0;
		$category->level_depth = 0;
		$category->active = 0;
		$category->add();
		Configuration::updateValue('SOCOLISSIMO_CAT_ID', intval($category->id));
		
		//add hidden product
		$product = new Product();
		$languages = Language::getLanguages(true);
			foreach ($languages as $language) 
			{
				if ($language['iso_code'] == 'fr')
				{
					$product->name[$language['id_lang']] = 'Surcoût RDV';
					$product->link_rewrite[$language['id_lang']] = 'overcost';
				}
				if ($language['iso_code'] == 'en')
					$product->name[$language['id_lang']] = 'Overcost';
					$product->link_rewrite[$language['id_lang']] = 'overcost';
			}
		$product->quantity = 10;
		$product->price = 0;
		$product->id_category_default = intval($category->id);
		$product->active = true;
		$product->id_tax = 0;
		$product->add();
		Configuration::updateValue('SOCOLISSIMO_PRODUCT_ID', intval($product->id));
		
			
		return true; 					
	}
	
	public function uninstall()
	{
		global $cookie;
				
		if (!parent::uninstall() OR !Db::getInstance()->Execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'socolissimo_delivery_info`')
		    OR !$this->unregisterHook('extraCarrier') OR !$this->unregisterHook('payment') OR !$this->unregisterHook('AdminOrder') 
		    OR !Configuration::deleteByName('SOCOLISSIMO_ID') OR !$this->unregisterHook('newOrder') OR !$this->unregisterHook('updateCarrier')
		    OR !Configuration::deleteByName('SOCOLISSIMO_KEY') OR !Configuration::deleteByName('SOCOLISSIMO_URL')
		    OR !Configuration::deleteByName('SOCOLISSIMO_OVERCOST') OR !Configuration::deleteByName('SOCOLISSIMO_PREPARATION_TIME') OR !Configuration::deleteByName('SOCOLISSIMO_CARRIER_ID') OR !Configuration::deleteByName('SOCOLISSIMO_PRODUCT_ID') OR !Configuration::deleteByName('SOCOLISSIMO_CAT_ID')
		    OR !Configuration::deleteByName('SOCOLISSIMO_SUP') OR !Configuration::deleteByName('SOCOLISSIMO_SUP_URL') OR !Configuration::deleteByName('SOCOLISSIMO_OVERCOST_TAX'))
			return false;
		
		//Delete So Carrier
			$soCarrier = new Carrier(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')));
			//if socolissimo carrier is default set other one as default
				if(Configuration::get('PS_CARRIER_DEFAULT') == intval($soCarrier->id))
				{
					$carriersD = Carrier::getCarriers(intval($cookie->id_lang));
					foreach($carriersD as $carrierD)
						if ($carrierD['active'] AND !$carrierD['deleted'] AND ($carrierD['name'] != $this->_config['name']))
							Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
				}
				//save old carrier id
				Configuration::updateValue('SOCOLISSIMO_CARRIER_ID_HIST', Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST').'|'.intval($soCarrier->id));
				$soCarrier->deleted = 1;
				if (!$soCarrier->update())
					return false;
		//delete hidden category and product overcost
		if (Configuration::get('SOCOLISSIMO_CAT_ID') != '' AND Configuration::get('SOCOLISSIMO_PRODUCT_ID') != '')
		{
			$category = new Category(Configuration::get('SOCOLISSIMO_CAT_ID'));
			$product = new Product(Configuration::get('SOCOLISSIMO_PRODUCT_ID'));
			$category->delete();
			$product->delete();
		}
		
		return true;
	}
	
	public function getContent()
	{
		$this->_html .= '<h2>' . $this->l('So Colissimo').'</h2>';
		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		$this->_displayForm();
		return $this->_html;
	}
	
	
	private function _displayForm()
	{
		global $cookie;
		$taxes = Tax::getTaxes(intval($cookie->id_lang));
		$this->_html .= '<script type="text/javascript">';
		$this->_html .= 'noTax = '.(Tax::excludeTaxeOption() ? 'true' : 'false').";\n";
		$this->_html .= 'taxesArray = new Array ()'.";\n";
		$this->_html .= 'taxesArray[0] = 0'.";\n";
		foreach ($taxes AS $k => $tax)
			$this->_html .= 'taxesArray['.$tax['id_tax'].']='.$tax['rate'].";\n";
		$this->_html .= '</script>';
		
		$this->_html .= '<script type="text/javascript" src="'._PS_JS_DIR_.'price.js"></script>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" class="form">
		<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Description').'</legend>'.
		$this->l('SoColissimo is a service offered by La Poste, which allows you to offer your buyer 5 modes of delivery').' : 
		<br/><br/><ul style ="list-style:disc outside none;margin-left:30px;">
			<li>'.$this->l('At home').'.</li>
			<li>'.$this->l('At home with appointments').'.</li>
			<li>'.$this->l('In Cityssimo space').'.</li>
			<li>'.$this->l('In their post office').'.</li>
			<li>'.$this->l('In their merchant').'.</li>
		</ul>
		<p>'.$this->l('This module is free and allows you to activate this offer on your store.').'</p>
		<p><a href="http://www.prestashop.com/download/partner_modules/docs/Intergation_socolissimo.pdf">
		>'.$this->l('Documentation').'<</a></p>
		</fieldset>
		<div class="clear">&nbsp;</div>
				
		<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Settings').'</legend>
		<label style="color:#CC0000;text-decoration : underline;">'.$this->l('Important').': </label>  
		<div class="margin-form">
		<p  style="width:500px">'.$this->l('To open your account Colissimo So, please contact your local Trading Post or the usual 36.34').'</p>
		</div>
		<label>'.$this->l('ID So').' : </label>
		<div class="margin-form">
		<input type="text" name="id_user" value="'.Tools::getValue('id_user',Configuration::get('SOCOLISSIMO_ID')).'" />
		<p>' . $this->l('Id user for back office SoColissimo.') . '</p>
		</div>

		<label>'.$this->l('Key').' : </label>
		<div class="margin-form">
		<input type="text" name="key" value="'.Tools::getValue('key',Configuration::get('SOCOLISSIMO_KEY')).'" />
		<p>'.$this->l('Secure key for back office SoColissimo.').'</p>
		</div>

		<label>'.$this->l('Preparation time').' : </label>
		<div class="margin-form">
		<input type="text" size="5" name="dypreparationtime" value="'.intval(Tools::getValue('dypreparationtime',Configuration::get('SOCOLISSIMO_PREPARATION_TIME'))).'" /> '.$this->l('Day(s)').'
		<p>' . $this->l('Average time of preparation of Stuff.') . ' <br><span style="color:red">'
		.$this->l('Average time must be the same in Coliposte Back office.').'</span></p>
		</div>
		
		<label>'.$this->l('Overcost').' : </label>
		<div class="margin-form">
		<input id="priceTE" size="11" onkeyup="calcPriceTI();" type="text" size="5" name="overcost" onkeyup="this.value = this.value.replace(/,/g, \'.\');" 
		value="'.floatval(Tools::getValue('overcost',number_format(Configuration::get('SOCOLISSIMO_OVERCOST'), 2, '.', ''))).'" /> € HT
		<br/><br/>
		<input id="priceTI" size="11" onkeyup="calcPriceTE();" type="text" size="5" onkeyup="this.value = this.value.replace(/,/g, \'.\');" /> € TTC
		</div>
		<script type="text/javascript">
		$(document).ready(function(){	
			calcPriceTI();
		});
		</script>
		<label>'.$this->l('Overcost').' '.$this->l('Tax:').'</label>
		<div class="margin-form">
		<select name="id_tax" id="id_tax" onchange="javascript:calcPriceTI();">
			<option value="0"'.((false) ? ' selected="selected"' : '').'>'.$this->l('No tax').'</option>';
			$tvaList = Tax::getTaxes(intval($cookie->id_lang));
				foreach ($tvaList AS $line)
					$this->_html .=  '<option value="'.$line['id_tax'].'"'.((Configuration::get('SOCOLISSIMO_OVERCOST_TAX') == $line['id_tax']) ? ' selected="selected"' : '').'>'.$line['name'].'</option>';
		$this->_html .= '</select>
		
		<p>'. $this->l('Additional cost if making appointments.') . ' <br><span style="color:red">'
		.$this->l('Additional cost must be the same in Coliposte Back office.').'</span></p>
		
		</div>
		<label>'.$this->l('Carrier').' : </label>
		<div class="margin-form"><select name="carrier"><option value="0">'.$this->l('Select a carrier ...').'</option>';
		$carriers = Carrier::getCarriers($cookie->id_lang, false);
		$ids = array();
		foreach($carriers as $carrier)
		{
			$this->_html .= '<option value="'.intval($carrier['id_carrier']).'" '.(intval($carrier['id_carrier']) == intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')) ? 'selected="selected"' : '').'>'.$carrier['name'].'</option>';
			$ids[] .= htmlentities($carrier['id_carrier'],ENT_NOQUOTES, 'UTF-8');
		}
		$this->_html .= '</select>
		<p>' . $this->l('Choose in carriers list the SoColissimo one.') . '</p>
		'.(!in_array(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')), $ids) ? '<div class="warning">'.$this->l('Carrier is not set').'</div>' : '').'
		</div>
		<div class="margin-form">
		<p>--------------------------------------------------------------------------------------------------------</p>	
		<span style="color:red">'
		.$this->l('Be VERY CAREFUL with these settings, change may cause a malfunction of the module').
		'</span>
		</div>
		<label>'.$this->l('Url So').' : </label>
		<div class="margin-form">
		<input type="text" size="45" name="url_so" value="'.htmlentities(Tools::getValue('url_so',Configuration::get('SOCOLISSIMO_URL')),ENT_NOQUOTES, 'UTF-8').'" />
		<p>' . $this->l('Url of back office SoColissimo.') . '</p>
		</div>

		<label>'.$this->l('Supervision').' : </label>
		<div class="margin-form">
			<input type="radio" name="sup_active" id="active_on" value="1" '.(Configuration::get('SOCOLISSIMO_SUP') ? 'checked="checked" ' : '').'/>
			<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
			<input type="radio" name="sup_active" id="active_off" value="0" '.(!Configuration::get('SOCOLISSIMO_SUP') ? 'checked="checked" ' : '').'/>
			<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
			<p>'.$this->l('Allow or disallow check availability of SoColissimo service').'</p>
		</div>

		<label>'.$this->l('Url Supervision').' : </label>
		<div class="margin-form">
		<input type="text" size="45" name="url_sup" value="'.htmlentities(Tools::getValue('url_sup',Configuration::get('SOCOLISSIMO_SUP_URL')),ENT_NOQUOTES, 'UTF-8').'" />
		<p>' . $this->l('Url of supervision.') . '</p>
		</div>
		
		<div class="margin-form">
		<input type="submit" value="'.$this->l('Save').'" name="submitSave" class="button" style="margin:10px 0px 0px 25px;" />
		</div>
		</fieldset></form>
		
		<div class="clear">&nbsp;</div>
		
		<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Informations').'</legend>
		<p>'.$this->l('Here are two addresses that you must fill in your Back Office SoColissimo').' : </p><br>
		<label>'.$this->l('Validation url').' : </label>
		<div class="margin-form">
		<p>'.htmlentities($this->url,ENT_NOQUOTES, 'UTF-8').'</p>
		</div>
		<label>'.$this->l('Return url').' : </label>
		<div class="margin-form">
		<p>'.htmlentities($this->url,ENT_NOQUOTES, 'UTF-8').'</p>
		</div>
		</fieldset>';
	}
	
	private function _postValidation()
	{
		if (Tools::getValue('id_user') == NULL)
			$this->_postErrors[]  = $this->l('ID SO not specified');
		
		if (Tools::getValue('key') == NULL)
			$this->_postErrors[]  = $this->l('Key SO not specified');
		
		if (Tools::getValue('dypreparationtime') == NULL)
			$this->_postErrors[]  = $this->l('Preparation time not specified');
		elseif (!Validate::isInt(Tools::getValue('dypreparationtime')))
				$this->_postErrors[]  = $this->l('Preparation time invalide');							
		
		if (Tools::getValue('overcost') == NULL)
			$this->_postErrors[]  = $this->l('overcost not specified');
		elseif (!Validate::isFloat(Tools::getValue('overcost')))
				$this->_postErrors[]  = $this->l('Overcost is invalide');		
	}
	
	private function _postProcess()
	{
		
		if (Configuration::updateValue('SOCOLISSIMO_ID', Tools::getValue('id_user')) AND Configuration::updateValue('SOCOLISSIMO_KEY', Tools::getValue('key')) AND Configuration::updateValue('SOCOLISSIMO_URL', pSQL(Tools::getValue('url_so'))) AND Configuration::updateValue('SOCOLISSIMO_PREPARATION_TIME', intval(Tools::getValue('dypreparationtime'))) AND Configuration::updateValue('SOCOLISSIMO_OVERCOST', floatval(Tools::getValue('overcost')))
		AND Configuration::updateValue('SOCOLISSIMO_CARRIER_ID', intval(Tools::getValue('carrier'))) 
		AND Configuration::updateValue('SOCOLISSIMO_SUP_URL', Tools::getValue('url_sup'))
		AND Configuration::updateValue('SOCOLISSIMO_OVERCOST_TAX', Tools::getValue('id_tax'))
		AND Configuration::updateValue('SOCOLISSIMO_SUP', intval(Tools::getValue('sup_active'))))
		{
			//save old carrier id if change
			if (!in_array(intval(Tools::getValue('carrier')), explode('|',Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST'))))
				Configuration::updateValue('SOCOLISSIMO_CARRIER_ID_HIST', Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST').'|'.intval(Tools::getValue('carrier')));
				
			$dataSync = (($so_login = Configuration::get('SOCOLISSIMO_ID'))
				? '<img src="http://www.prestashop.com/modules/socolissimo.png?ps_id='.urlencode($so_login).'" style="float:right" />' : '');
			$this->_html .= $this->displayConfirmation($this->l('Configuration updated').$dataSync);

		}else
			$this->_html .= '<div class="alert error"><img src="' . _PS_IMG_ . 'admin/forbbiden.gif" alt="nok" />&nbsp;'.$this->l('Settings faild').'</div>';			
	}
	
	public function hookExtraCarrier($params) 
	{	
		global $smarty;
				
		//delete overcost product if exist
		$cart = new Cart(intval($params['cart']->id));
		$products = $cart->getProducts(false);
		$ids = array();
		foreach($products as $product)
			$ids[] .= intval($product['id_product']);
		if (in_array(Configuration::get('SOCOLISSIMO_PRODUCT_ID'),$ids))
			$cart->deleteProduct(Configuration::get('SOCOLISSIMO_PRODUCT_ID'));
		$cart->update();
		$customer = new Customer($params['address']->id_customer);
		$gender = array('1'=>'MR','2'=>'MME');
		if (in_array(intval($customer->id_gender),array(1,2)))
			$cecivility = $gender[intval($customer->id_gender)];
		else
			$cecivility = 'MR';
		$carrierSo = new Carrier(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')));
	
		if (isset($carrierSo) AND $carrierSo->active)
		{	
			$signature = $this->make_key(substr($params['address']->lastname,0,34),
						 intval(Configuration::Get('SOCOLISSIMO_PREPARATION_TIME')),
						 number_format(floatval($params['cart']->getOrderShippingCost($carrierSo->id, true)), 2, ',', ''),
						 intval($params['address']->id_customer),intval($params['address']->id));

			$orderId = $this->formatOrderId(intval($params['address']->id));
			$inputs = array('PUDOFOID' => Configuration::get('SOCOLISSIMO_ID'),
							'ORDERID' => $orderId,
							'CENAME' => substr($params['address']->lastname,0, 34),
							'TRCLIENTNUMBER' => $this->upper(intval($params['address']->id_customer)),
							'CECIVILITY' => $cecivility,
							'CEFIRSTNAME' => substr($this->lower($params['address']->firstname),0,29),
							'CECOMPANYNAME' => substr($this->upper($params['address']->company),0,38),
							'CEEMAIL' => $params['cookie']->email,
							'CEPHONENUMBER' => str_replace(array(' ', '.', '-', ',', ';', '+', '/', '\\', '+', '(', ')'),'',$params['address']->phone_mobile),
							'CEADRESS3'  => substr($this->upper($params['address']->address1),0,38),
							'CEADRESS4' => substr($this->upper($params['address']->address2),0,38),
							'CEZIPCODE' => $params['address']->postcode,
							'CETOWN' => substr($this->upper($params['address']->city),0,32),
							'DYWEIGHT' => (floatval($params['cart']->getTotalWeight()) * 1000),
							'SIGNATURE' => htmlentities($signature,ENT_NOQUOTES, 'UTF-8'),
							'TRPARAMPLUS' => intval($carrierSo->id),
							'DYFORWARDINGCHARGES' => number_format(floatval($params['cart']->getOrderShippingCost($carrierSo->id)), 2, ',', ''),
							'DYPREPARATIONTIME' => intval(Configuration::Get('SOCOLISSIMO_PREPARATION_TIME')),
							'TRRETURNURLKO' => htmlentities($this->url,ENT_NOQUOTES, 'UTF-8'),
							'TRRETURNURLOK' => htmlentities($this->url,ENT_NOQUOTES, 'UTF-8'));
			
			$row['id_carrier'] = intval($carrierSo->id);

			$smarty->assign(array('urlSo' => Configuration::get('SOCOLISSIMO_URL').'?trReturnUrlKo='.htmlentities($this->url,ENT_NOQUOTES, 'UTF-8'),'id_carrier' => intval($row['id_carrier']),
								  'inputs' => $inputs)
							);				
			
			$country = new Country(intval($params['address']->id_country));
			$carriers = Carrier::getCarriers($cookie->id_lang, false);
			foreach($carriers as $carrier)
				$ids[] .= $carrier['id_carrier'];
			
			if (($country->iso_code == 'FR') AND (Configuration::Get('SOCOLISSIMO_ID') != NULL) AND (Configuration::get('SOCOLISSIMO_KEY') != NULL) AND $this->checkAvailibility() 
			AND $this->checkSoCarrierAvailable(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID'))) AND in_array(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')),$ids))
				{
					return $this->display(__FILE__, 'socolissimo_carrier.tpl');
				}
				else
				{
					$smarty->assign('ids', explode('|',Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST')));
					return $this->display(__FILE__, 'socolissimo_error.tpl');
				}
				
		}		
	}
	
	public function hooknewOrder($params)
	{
		global $cookie;
		
		if ($params['order']->id_carrier != Configuration::get('SOCOLISSIMO_CARRIER_ID'))
			return;
		$order = $params['order'];
		$order->id_address_delivery = $this->isSameAddress(intval($order->id_address_delivery), intval($order->id_cart), intval($order->id_customer));
		$order->update();
		$cart = new Cart(intval($params['cart']->id));
		$products = $cart->getProducts(false);
		foreach($products as $product)
		{
			$ids[] .= intval($product['id_product']);
		}
		$deliveryInfos = $this->getDeliveryInfos(intval($params['cart']->id),intval($params['cart']->id_customer));
		if ($deliveryInfos['delivery_mode'] == 'RDV')
		{
		$product = new Product(intval(Configuration::get('SOCOLISSIMO_PRODUCT_ID')));
		$product->quantity += 1;
		$product->update();
		if (!in_array(intval(Configuration::get('SOCOLISSIMO_PRODUCT_ID')),$ids))
		{	
			$history = new OrderHistory();
			$history->id_order = intval($params['order']->id);
			$history->changeIdOrderState(_PS_OS_ERROR_, intval($history->id_order));
			$history->id_employee = intval($cookie->id_employee);
			$history->addWithemail();
			die(Tools::displayError('Order creation failed'));
		}
		}	
	}
	
	public function hookAdminOrder($params)
	{	
	
	$deliveryMode = array('DOM' => 'Livraison à domicile', 'BPR' => 'Livraison en Bureau de Poste',
						  'A2P' => 'Livraison Commerce de proximité', 'MRL' => 'Livraison Commerce de proximité',
						  'CIT' => 'Livraison en Cityssimo', 'ACP' => 'Agence ColiPoste', 'CDI' => 'Centre de distribution',
						  'RDV' => 'Livraison sur Rendez-vous');
	
		$order = new Order($params['id_order']);
		$addressDelivery = new Address(intval($order->id_address_delivery), intval($params['cookie']->id_lang));
		
		$soCarrier = new Carrier(intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')));
		$deliveryInfos = $this->getDeliveryInfos(intval($order->id_cart),intval($order->id_customer));
		if ((intval($order->id_carrier) == intval($soCarrier->id) OR in_array(intval($order->id_carrier), explode('|',Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST')))) AND !empty($deliveryInfos))
		{
			$html = '<br><br><fieldset style="width:400px;"><legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('So Colissimo').'</legend>';
			$html .= '<b>'.$this->l('Delivery mode').' : </b>';
			switch ($deliveryInfos['delivery_mode'])
			{
				case 'DOM':
				case 'RDV':
				$html .= $deliveryMode[$deliveryInfos['delivery_mode']].'<br /><br />';
				$html .='<b>'.$this->l('Customer').' : </b>'.Tools::htmlentitiesUTF8($addressDelivery->firstname).' '.Tools::htmlentitiesUTF8($addressDelivery->lastname).'<br />'.
						(!empty($deliveryInfos['cecompanyname']) ? '<b>'.$this->l('Societe').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['cecompanyname']).'<br/>' : '' ).
						(!empty($deliveryInfos['ceemail']) ? '<b>'.$this->l('Email').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['ceemail']).'<br/>' : '' ).
						(!empty($deliveryInfos['cephonenumber']) ? '<b>'.$this->l('Tel').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['cephonenumber']).'<br/><br/>' : '' ).
						'<b>'.$this->l('Customer adresse').' : </b><br/>'
						.(Tools::htmlentitiesUTF8($addressDelivery->address1) ? Tools::htmlentitiesUTF8($addressDelivery->address1).'<br />' : '')
						.(!empty($addressDelivery->address2) ? Tools::htmlentitiesUTF8($addressDelivery->address2).'<br />' : '')
						.(!empty($addressDelivery->postcode) ? Tools::htmlentitiesUTF8($addressDelivery->postcode).'<br />' : '')
						.(!empty($addressDelivery->city) ? Tools::htmlentitiesUTF8($addressDelivery->city).'<br />' : '')
						.(!empty($addressDelivery->country) ? Tools::htmlentitiesUTF8($addressDelivery->country).'<br />' : '')
						.(!empty($addressDelivery->other) ? '<hr><b>'.$this->l('Other').' : </b>'.Tools::htmlentitiesUTF8($addressDelivery->other).'<br /><br />' : '')
						.(!empty($deliveryInfos['cedoorcode1']) ? '<b>'.$this->l('Door code').' 1 : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['cedoorcode1']).'<br/>' : '' )
						.(!empty($deliveryInfos['cedoorcode2']) ? '<b>'.$this->l('Door code').' 2 : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['cedoorcode2']).'<br/>' : '' )
						.(!empty($deliveryInfos['cedeliveryinformation']) ? '<b>'.$this->l('Delivery informations').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['cedeliveryinformation']).'<br/><br/>' : '' );
				break;
				default:
				$html .=  str_replace('+',' ',$deliveryMode[$deliveryInfos['delivery_mode']]).'<br/>'
				.(!empty($deliveryInfos['prid']) ? '<b>'.$this->l('Pic up point id').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['prid']).'<br/>' : '' )
				.(!empty($deliveryInfos['prname']) ? '<b>'.$this->l('Pic up point').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['prname']).'<br/>' : '' )
				.'<b>'.$this->l('Pic up point adresse').' : </b><br/>'
				.(!empty($deliveryInfos['pradress1']) ? Tools::htmlentitiesUTF8($deliveryInfos['pradress1']).'<br/>' : '' )
				.(!empty($deliveryInfos['pradress2']) ? Tools::htmlentitiesUTF8($deliveryInfos['pradress2']).'<br/>' : '' )
				.(!empty($deliveryInfos['pradress3']) ? Tools::htmlentitiesUTF8($deliveryInfos['pradress3']).'<br/>' : '' )
				.(!empty($deliveryInfos['pradress4']) ? Tools::htmlentitiesUTF8($deliveryInfos['pradress4']).'<br/>' : '' )
				.(!empty($deliveryInfos['przipcode']) ? Tools::htmlentitiesUTF8($deliveryInfos['przipcode']).'<br/>' : '' )
				.(!empty($deliveryInfos['prtown']) ? Tools::htmlentitiesUTF8($deliveryInfos['prtown']).'<br/>' : '' )
				.(!empty($deliveryInfos['ceemail']) ? '<b>'.$this->l('Email').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['ceemail']).'<br/>' : '' )
				.(!empty($deliveryInfos['cephonenumber']) ? '<b>'.$this->l('Tel').' : </b>'.Tools::htmlentitiesUTF8($deliveryInfos['cephonenumber']).'<br/><br/>' : '' );

				 break;
			}		
			$html .= '</fieldset>';
			return $html;
		}

	}
	
	public function hookupdateCarrier($params)
	{	
		if (intval($params['id_carrier']) == intval(Configuration::get('SOCOLISSIMO_CARRIER_ID')))
		{
			Configuration::updateValue('SOCOLISSIMO_CARRIER_ID', intval($params['carrier']->id));
			Configuration::updateValue('SOCOLISSIMO_CARRIER_ID_HIST', Configuration::get('SOCOLISSIMO_CARRIER_ID_HIST').'|'.intval($params['carrier']->id));
		}
		
	}
		
	public function make_key($ceName, $dyPraparationTime, $dyForwardingCharges, $trClientNumber, $orderId)
	{
		$strPs = Configuration::get('SOCOLISSIMO_ID').$ceName.$dyPraparationTime.$dyForwardingCharges.$trClientNumber.self::formatOrderId($orderId).Configuration::get('SOCOLISSIMO_KEY');
		$keyPs = sha1($strPs);
		return $keyPs;
	}
	
	public static function createSoColissimoCarrier($config)
	{
			$carrier = new Carrier();
			$carrier->name = $config['name'];
			$carrier->id_tax = $config['id_tax'];
			$carrier->id_zone = $config['id_zone'];
			$carrier->url = $config['url'];
			$carrier->active = $config['active'];
			$carrier->deleted = $config['deleted'];
			$carrier->delay = $config['delay'];
			$carrier->shipping_handling = $config['shipping_handling'];
			$carrier->range_behavior = $config['range_behavior'];
			$carrier->is_module = $config['is_module'];
			$carrier->shipping_external = $config['shipping_external'];
			$carrier->external_module_name = $config['external_module_name'];
			$carrier->need_range = $config['need_range'];
		
			$languages = Language::getLanguages(true);
			foreach ($languages as $language) {
				if ($language['iso_code'] == 'fr')
					$carrier->delay[$language['id_lang']] = $config['delay'][$language['iso_code']];
				if ($language['iso_code'] == 'en')
					$carrier->delay[$language['id_lang']] = $config['delay'][$language['iso_code']];
			}
			if($carrier->add())
			{				
				
				Configuration::updateValue('SOCOLISSIMO_CARRIER_ID',intval($carrier->id));
				$groups = Group::getgroups(true);
				foreach ($groups as $group)
				{
					Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.intval($carrier->id).'\',\''.intval($group['id_group']).'\')');
				}
				$rangePrice = new RangePrice();
				$rangePrice->id_carrier = $carrier->id;
				$rangePrice->delimiter1 = '0';
				$rangePrice->delimiter2 = '10000';
				$rangePrice->add();
			
				$rangeWeight = new RangeWeight();
				$rangeWeight->id_carrier = $carrier->id;
				$rangeWeight->delimiter1 = '0';
				$rangeWeight->delimiter2 = '10000';
				$rangeWeight->add();
				
				$zones = Zone::getZones(true);
				foreach ($zones as $zone)
				{
					Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_zone VALUE (\''.intval($carrier->id).'\',\''.intval($zone['id_zone']).'\')');
					Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'delivery VALUE (\'\',\''.intval($carrier->id).'\',\''.intval($rangePrice->id).'\',NULL,\''.intval($zone['id_zone']).'\',\'1\')');
					Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'delivery VALUE (\'\',\''.intval($carrier->id).'\',NULL,\''.intval($rangeWeight->id).'\',\''.intval($zone['id_zone']).'\',\'1\')');
				}
				//copy logo
				if (!copy(dirname(__FILE__).'/socolissimo.jpg',_PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg'))
						return false;
				return true;
			}
			else
				return false;
	}
	
	public function getDeliveryInfos($idCart,$idCustomer)
	{

		$result = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'socolissimo_delivery_info WHERE id_cart = '.intval($idCart).' AND id_customer = '.intval($idCustomer));
		return $result;
	}
	
	public function isSameAddress($idAddress,$idCart,$idCustomer)
	{
		$return = Db::getInstance()->GetRow('SELECT * FROM '._DB_PREFIX_.'socolissimo_delivery_info WHERE id_cart =\''.intval($idCart).'\' AND id_customer =\''.intval($idCustomer).'\'');
		$psAddress = new Address(intval($idAddress));
		$newAddress = new Address();
		
			if ($this->upper($psAddress->lastname) != $this->upper($return['prname']) || $this->upper($psAddress->firstname) != $this->upper($return['prfirstname']) || $this->upper($psAddress->address1) != $this->upper($return['pradress3']) || $this->upper($psAddress->address2) != $this->upper($return['pradress2']) || $this->upper($psAddress->postcode) != $this->upper($return['przipcode']) || $this->upper($psAddress->city) != $this->upper($return['prtown']) || str_replace(array(' ', '.', '-', ',', ';', '+', '/', '\\', '+', '(', ')'),'',$psAddress->phone_mobile) != $return['cephonenumber'])
			{
				
				$newAddress->id_customer = intval($idCustomer);
				$newAddress->lastname = substr($return['prname'],0,32);
				$newAddress->firstname = substr($return['prfirstname'],0,32);
				$newAddress->postcode = $return['przipcode'];
				$newAddress->city = $return['prtown'];
				$newAddress->id_country = Country::getIdByName(null, 'france');
				$newAddress->alias = 'So Colissimo - '.date('d-m-Y');
				
				if (!in_array($return['delivery_mode'], array('DOM','RDV')))
				{
					$newAddress->active = 1;
					$newAddress->deleted = 1;
					$newAddress->address1 = $return['pradress1'];
					$newAddress->add();
				}
				else
				{
					$newAddress->address1 = $return['pradress3'];
					((isset($return['pradress2'])) ? $newAddress->address2 = $return['pradress2'] : $newAddress->address2 = '');
					((isset($return['pradress1'])) ? $newAddress->other .= $return['pradress1'] : $newAddress->other = '');
					((isset($return['pradress4'])) ? $newAddress->other .= ' | '.$return['pradress4'] : $newAddress->other = '');
					$newAddress->postcode = $return['przipcode'];
					$newAddress->city = $return['prtown'];
					$newAddress->id_country = Country::getIdByName(null, 'france');
					$newAddress->alias = 'So Colissimo - '.date('d-m-Y');
					$newAddress->add();
				}
				return intval($newAddress->id);
			}
			else
			return intval($psAddress->id);
	}
	
	public function checkZone($id_carrier)
	{
		$result = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier_zone WHERE id_carrier = '.intval($id_carrier));
		if ($result)
			return true;
		else
			return false;
	}

	public function checkGroup($id_carrier)
	{
		$result = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier_group WHERE id_carrier = '.intval($id_carrier));
		if ($result)
			return true;
		else
			return false;
	}
	
 	public function checkRange($id_carrier)
	{
		switch (Configuration::get('PS_SHIPPING_METHOD'))
		{
			case '0' :
				$sql = 'SELECT * FROM '._DB_PREFIX_.'range_price WHERE id_carrier = '.intval($id_carrier);
				break;
			case '1' :
				$sql = 'SELECT * FROM '._DB_PREFIX_.'range_weight WHERE id_carrier = '.intval($id_carrier);
				break;
		}
		$result = Db::getInstance()->getRow($sql);
		if ($result)
			return true;
		else
			return false;
	}
	
	public function checkDelivery($id_carrier)
	{
		$result = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'delivery WHERE id_carrier = '.intval($id_carrier));
		if ($result)
			return true;
		else
			return false;
	}
		
	public function upper($strIn)
	{
		$strOut = Tools::link_rewrite($strIn);
		return strtoupper(str_replace('-',' ',$strOut));
	}
	
	
	public function lower($strIn)
	{
		$strOut = Tools::link_rewrite($strIn);
		return strtolower(str_replace('-',' ',$strOut));
	}
	
	public function formatOrderId($id)
	{
		if(strlen($id)<5)
			while (strLen($id) != 5)
			{
            	$id = '0'.$id;
            }
		return $id;
	}
	
	public function checkAvailibility()
	{
		if (Configuration::get('SOCOLISSIMO_SUP'))
		{
			$ctx = stream_context_create(array('http' => array('timeout' => 1))); 
			$return = @file_get_contents(Configuration::get('SOCOLISSIMO_SUP_URL'), 0, $ctx);
			
			if(ini_get('allow_url_fopen') == 0)
				return true;
			else
			{
				if (!empty($return))
				{
					preg_match('[OK]',$return, $matches);
					if ($matches[0]=='OK')
						return true;
					else
						return false;
				}
			}
		}
		else 
		return true;
	}	
	
	public function displaySoError($key)
	{
		return $this->errorMessage[$key];
	}
	
	private function checkSoCarrierAvailable($id_carrier)
	{
		global $cart, $defaultCountry;
		$carrier = new Carrier(intval($id_carrier));
		$address = new Address(intval($cart->id_address_delivery));
		$id_zone = Address::getZoneById(intval($address->id));

		// Get only carriers that are compliant with shipping method
		if ((Configuration::get('PS_SHIPPING_METHOD') AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
		OR (!Configuration::get('PS_SHIPPING_METHOD') AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
		{
			return false;
		}
		
		// If out-of-range behavior carrier is set on "Desactivate carrier"
		if ($carrier->range_behavior)
		{
			// Get id zone
	        if (isset($cart->id_address_delivery) AND $cart->id_address_delivery)
				$id_zone = Address::getZoneById(intval($cart->id_address_delivery));
			else
				$id_zone = intval($defaultCountry->id_zone);

			// Get only carriers that have a range compatible with cart
			if ((Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByWeight(intval($carrier->id), $cart->getTotalWeight(), $id_zone)))
			OR (!Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByPrice(intval($carrier->id), $cart->getOrderTotal(true, 4), $id_zone, $cart->id_currency))))
				{
					return false;
				}
		}
		return true;
	}
	
}

?>
