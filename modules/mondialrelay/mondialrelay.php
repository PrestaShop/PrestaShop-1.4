<?php
		
include_once(_PS_MODULE_DIR_.'/mondialrelay/MondialRelayClass.php');

class MondialRelay extends Module
{
	const INSTALL_SQL_FILE = 'mrInstall.sql';
	
	public function __construct()
	{
		error_reporting(E_ALL ^ E_NOTICE);

		$this->name		= 'mondialrelay';
		$this->tab		= 'shipping_logistics';
		$this->version	= '1.2 rev C';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Mondial Relay');
		$this->description = $this->l('Deliver in Relay points');
		
		define('_MR_CSS_', dirname(__FILE__) . '/style.css');
	}
	
	public function install()
	{
		global $cookie;
		if (!parent::install())
			return false;

		$rpos = Db::getInstance()->ExecuteS('SELECT `name` FROM `' . _DB_PREFIX_ . 'hook` WHERE `name` ="shipping"');
		if ($rpos[0]['name'] != "shipping")
			Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'hook (name, title, description, position) VALUES("shipping", "Mondial Relay API", NULL, 0)');

		if (!$this->registerHook('shipping') OR
			!$this->registerHook('extraCarrier') OR
			!$this->registerHook('processCarrier') OR
			!$this->registerHook('orderDetail') OR
			!$this->registerHook('updateCarrier') OR
			!$this->registerHook('orderDetailDisplayed'))
			return false;

		Configuration::updateValue('MONDIAL_RELAY_ORDER_STATE', 3);
		Configuration::updateValue('MONDIAL_RELAY_SECURE_KEY', md5(time().rand(0,10)));
		
		if (!file_exists(_PS_MODULE_DIR_. '/mondialrelay/' . self::INSTALL_SQL_FILE))
			return false;
		elseif(!$sql = file_get_contents(_PS_MODULE_DIR_. '/mondialrelay/' . self::INSTALL_SQL_FILE))
			return false;
	
		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach($sql AS $k => $query)
			if (!empty($query))
				Db::getInstance()->Execute(trim($query));

		$rpos = Db::getInstance()->ExecuteS('SELECT id_tab  FROM `' . _DB_PREFIX_ . 'tab` WHERE  class_name="AdminMondialRelay"   LIMIT 0 , 1');
		$id_tab = $rpos[0]['id_tab'];	
		if ($id_tab <= 0)
		{
			/*tab install */

			$rpos = Db::getInstance()->ExecuteS('SELECT position  FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent` = 3 ORDER BY `'. _DB_PREFIX_ .'tab`.`position` DESC LIMIT 0 , 1');
			$pos = $rpos[0]['position'];	
			$pos++;
		
			Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'tab (id_parent, class_name, position, module) VALUES("3", "AdminMondialRelay",  "'.intval($pos).'", "mondialrelay")');	 	

			$rpos = Db::getInstance()->ExecuteS('SELECT id_tab  FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent`= 3 and class_name="AdminMondialRelay"   LIMIT 0 , 1');
			$id_tab = $rpos[0]['id_tab'];		
			
        	$languages = Language::getLanguages();
			foreach ($languages AS $language)
		    	Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'tab_lang (id_lang,id_tab,name) VALUES("'.intval($language['id_lang']).'", "'.intval($id_tab).'", "Mondial Relay")');

			$profiles = Profile::getProfiles(Configuration::get('PS_LANG_DEFAULT'));
			foreach ($profiles as $profile)
				Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'access (`id_profile`,`id_tab`,`view`,`add`,`edit`,`delete`)
											VALUES('.$profile['id_profile'].', '.intval($id_tab).', 1, 1, 1, 1)');
			
			@copy(_PS_MODULE_DIR_.'mondialrelay/AdminMondialRelay.gif', _PS_IMG_DIR_.'t/AdminMondialRelay.gif');
		}	

		Configuration::updateValue('MONDIAL_RELAY_INSTALL_UPDATE_1', 1);
		Configuration::updateValue('MR_GOOGLE_MAP', '1');
		Configuration::updateValue('MR_ENSEIGNE_WEBSERVICE', '');
		Configuration::updateValue('MR_CODE_MARQUE', '');
		Configuration::updateValue('MR_KEY_WEBSERVICE', '');
		Configuration::updateValue('MR_LANGUAGE', '');
		Configuration::updateValue('MR_WEIGHT_COEF', '');
		$this->infodoc();
		return true;
	}
	
	public function uninstall()
	{
	
		if (!parent::uninstall())
			return false;
		
		/*tab uninstall	*/

		$rpos = Db::getInstance()->ExecuteS('SELECT id_tab  FROM `' . _DB_PREFIX_ . 'tab` WHERE  class_name="AdminMondialRelay"   LIMIT 0 , 1');
		$id_tab = $rpos[0]['id_tab'];
		if (isset($id_tab) AND !empty($id_tab))
		{	
			Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'tab WHERE id_tab = '.intval($id_tab));
			Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'tab_lang WHERE id_tab = '.intval($id_tab));
			Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'access WHERE id_tab = '.intval($id_tab));
		}

		if (!Configuration::deleteByName('MONDIAL_RELAY_INSTALL_UPDATE') OR
			!Configuration::deleteByName('MONDIAL_RELAY_INSTALL') OR
			!Configuration::deleteByName('MONDIAL_RELAY_ORDER_STATE') OR
			!Configuration::deleteByName('MR_GOOGLE_MAP') OR
			!Db::getInstance()->Execute('UPDATE  '._DB_PREFIX_ .'carrier  set `active` = 0, `deleted` = 1 WHERE `external_module_name` = "mondialrelay"') OR
			!Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_ .'mr_historique, '._DB_PREFIX_ .'mr_method, '._DB_PREFIX_ .'mr_selected'))
			return false;
		return true;
	}
	
	private function _postValidation()
	{
		if (Tools::isSubmit('submitMR'))
		{
			if (Tools::getValue('mr_Enseigne_WebService') != '' AND !preg_match("#^[0-9A-Z]{2}[0-9A-Z ]{6}$#", Tools::getValue('mr_Enseigne_WebService')))
				$this->_postErrors[] = $this->l('Invalid Enseigne');
			if (Tools::getValue('mr_code_marque') != '' AND !preg_match("#^[0-9]{2}$#", Tools::getValue('mr_code_marque')))
				$this->_postErrors[] = $this->l('Invalid Mark code');
			if (Tools::getValue('mr_Key_WebService') != '' AND !preg_match("#^[0-9A-Za-z_\'., /\-]{2,32}$#", Tools::getValue('mr_Key_WebService')))
				$this->_postErrors[] = $this->l('Invalid Webservice Key');
			if (Tools::getValue('mr_Langage') != '' AND !preg_match("#^[A-Z]{2}$#", Tools::getValue('mr_Langage')))
				$this->_postErrors[] = $this->l('Invalid Language');
			if (!Tools::getValue('mr_weight_coef') OR !Validate::isInt(Tools::getValue('mr_weight_coef')))
				$this->_postErrors[] = $this->l('Invalid Weight Coefficient');
		}
		elseif (Tools::isSubmit('submitMethod'))
		{
			if (Configuration::get('MR_ENSEIGNE_WEBSERVICE') == '' OR Configuration::get('MR_CODE_MARQUE') == '' OR
				Configuration::get('MR_KEY_WEBSERVICE') == '' OR Configuration::get('MR_LANGUAGE') == '')
				$this->_postErrors[] = $this->l('Please configure your Mondial Relay account settings before creating a carrier');
			if (!preg_match("#^[0-9A-Za-z_\'., /\-]{2,32}$#", Tools::getValue('mr_Name')))
				$this->_postErrors[] = $this->l('Invalid carrier name');
			if (Tools::getValue('mr_ModeCol') != 'CCC')
				$this->_postErrors[] = $this->l('Invalid Col mode');
			if (!preg_match("#^REL|24R|ESP|DRI|LDS|LDR|LD1$#", Tools::getValue('mr_ModeLiv')))
				$this->_postErrors[] = $this->l('Invalid Livraison mode');
			if (!Validate::isInt(Tools::getValue('mr_ModeAss')) OR Tools::getValue('mr_ModeAss') > 5 OR Tools::getValue('mr_ModeAss') < 0)
				$this->_postErrors[] = $this->l('Invalid Assurance mode');
			if (!Tools::getValue('mr_Pays_list'))
				$this->_postErrors[] = $this->l('You must choose at least one delivery country');
		}
		elseif (Tools::isSubmit('submit_order_state'))
		{
			if (!Validate::isBool(Tools::getValue('mr_google_key')))
				$this->_postErrors[] = $this->l('Invalid google key');
			if (!Validate::isUnsignedInt(Tools::getValue('id_order_state')))
				$this->_postErrors[] = $this->l('Invalid order state');
		}
	}

	private function _postProcess()
	{
		foreach($_POST AS $key => $value)
		{
				$setArray[] = $value;
				$keyArray[] = pSQL($key);						
		}
		array_pop($setArray);
		array_pop($keyArray);
	
		if (isset($_POST['submitMR']) AND $_POST['submitMR'])
			self::mrUpdate('settings', $setArray, $keyArray);
		elseif (isset($_POST['submitShipping']) AND $_POST['submitShipping'])
			self::mrUpdate('shipping', $_POST, array());
		elseif (isset($_POST['submitMethod']) AND $_POST['submitMethod'])
			self::mrUpdate('addShipping', $setArray, $keyArray);
		elseif (isset($_POST['submit_order_state']) AND $_POST['submit_order_state'])
		{
			Configuration::updateValue('MONDIAL_RELAY_ORDER_STATE', Tools::getValue('id_order_state'));
			Configuration::updateValue('MR_GOOGLE_MAP', Tools::getValue('mr_google_key'));
			if (!Tools::isSubmit('updatesuccess'))
				$this->_html .= '<div class="conf confirm"><img src="'._PS_ADMIN_IMG_.'/ok.gif" /> '.$this->l('Settings updated succesfull').'</div>';
		}
	}
	
	public function getmrth($id_lang, $active = false, $id_zone = false, $id_iso_code = false)
	{
		if (!Validate::isBool($active))
			die(Tools::displayError());

		$carriers = Db::getInstance()->ExecuteS('
			SELECT c.*, cl.delay
			FROM `'._DB_PREFIX_.'mr_method` m
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON (c.`id_carrier` = m.`id_carrier` and c.`deleted` = 0)
			LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.intval($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz  ON (cz.`id_carrier` = c.`id_carrier`)'.
			($id_zone ? 'LEFT JOIN `'._DB_PREFIX_.'zone` z  ON (z.`id_zone` = '.intval($id_zone).')' : '').'
			WHERE 1  '.
			($id_iso_code ? ' AND m.`mr_Pays_list` LIKE \'%'.pSQL($id_iso_code).'%\'' : '').
			($active ? ' AND c.`active` = 1' : '').
			($id_zone ? ' AND cz.`id_zone` = '.intval($id_zone).'
			AND z.`active` = 1' : '').'
			GROUP BY c.`id_carrier`');
		
		if (is_array($carriers) AND count($carriers))
		{
			foreach ($carriers as $key => $carrier)
				if ($carrier['name'] == '0')
					$carriers[$key]['name'] = Configuration::get('PS_SHOP_NAME');
		}
		else
			$carriers = array();

		return $carriers;
	}
	
	public function hookOrderDetail($params)
	{
		global $smarty;
		
		$carrier = $params['carrier'];
		$order = $params['order'];
	
		if ($carrier->is_module AND $order->shipping_number)
	 	{
			$module = $carrier->external_module_name;
			include_once(_PS_MODULE_DIR_.$module.'/'.$module.'.php');
			$module_carrier = new $module();
			$smarty->assign('followup', $module_carrier->get_followup($order->shipping_number));
		}
		elseif ($carrier->url AND $order->shipping_number)
			$smarty->assign('followup', str_replace('@', $order->shipping_number, $carrier->url));
	}
	
	public function hookOrderDetailDisplayed($params)
	{
		global $smarty;
	
		$res = Db::getInstance()->getRow('
		SELECT s.`MR_Selected_LgAdr1`, s.`MR_Selected_LgAdr2`, s.`MR_Selected_LgAdr3`, s.`MR_Selected_LgAdr4`, s.`MR_Selected_CP`, s.`MR_Selected_Ville`, s.`MR_Selected_Pays`, s.`MR_Selected_Num`
		FROM `'._DB_PREFIX_.'mr_selected` s
		WHERE s.`id_cart` = '.$params['order']->id_cart);
		if ((!$res) OR ($res['MR_Selected_Num'] == 'LD1') OR ($res['MR_Selected_Num'] == 'LDS'))
			return '';
		$smarty->assign('mr_addr', $res['MR_Selected_LgAdr1'].($res['MR_Selected_LgAdr1'] ? ' - ' : '').$res['MR_Selected_LgAdr2'].($res['MR_Selected_LgAdr2'] ? ' - ' : '').$res['MR_Selected_LgAdr3'].($res['MR_Selected_LgAdr3'] ? ' - ' : '').$res['MR_Selected_LgAdr4'].($res['MR_Selected_LgAdr4'] ? ' - ' : '').$res['MR_Selected_CP'].' '.$res['MR_Selected_Ville'].' - '.$res['MR_Selected_Pays']);
		return $this->display(__FILE__, 'orderDetail.tpl');
	}
	
	public function hookProcessCarrier($params)
	{
		$cart = $params['cart'];
		$result_MR = Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."mr_method WHERE id_carrier='".intval($cart->id_carrier)."' ;");
		if (count($result_MR) > 0) 
		{
			$mr_mode_liv = $result_MR[0]['mr_ModeLiv'];
			if ($mr_mode_liv == 'LDS' || $mr_mode_liv == 'LD1')
			{
				$deliveryAddressLDS = new Address(intval($cart->id_address_delivery));
				if (Validate::isLoadedObject($deliveryAddressLDS) AND ($deliveryAddressLDS->id_customer == $cart->id_customer))
     			{
	 				Db::getInstance()->delete(_DB_PREFIX_.'mr_selected','id_cart = "'.intval($cart->id).'"');
					$mrselected = new MondialRelayClass();
					$mrselected->id_customer = $cart->id_customer;
					$mrselected->id_method = $result_MR[0]['id_mr_method'];
					$mrselected->id_cart = $cart->id;
					$mrselected->MR_Selected_Num = $mr_mode_liv;
					$mrselected->save();
	 			}
			}
			elseif (!Configuration::get('PS_ORDER_PROCESS_TYPE'))
			{
				if (empty($_POST['MR_Selected_Num_'.$cart->id_carrier])) // Case error : the customer didn't choose a 'relais' but selected Relais Colis TNT as a carrier 
					Tools::redirect('order.php?step=2&mr_null');
				else
				{
					Db::getInstance()->delete(_DB_PREFIX_.'mr_selected','id_cart = "'.intval($cart->id).'"');
					$mrselected = new MondialRelayClass();
					$mrselected->id_customer = $cart->id_customer;
					$mrselected->id_method = $result_MR[0]['id_mr_method'];
					$mrselected->id_cart = $cart->id;
					$mrselected->MR_Selected_Num = $_POST['MR_Selected_Num_'.$cart->id_carrier];
					$mrselected->MR_Selected_LgAdr1 = $_POST['MR_Selected_LgAdr1_'.$cart->id_carrier];
					$mrselected->MR_Selected_LgAdr2 = $_POST['MR_Selected_LgAdr2_'.$cart->id_carrier];
					$mrselected->MR_Selected_LgAdr3 = $_POST['MR_Selected_LgAdr3_'.$cart->id_carrier];
					$mrselected->MR_Selected_LgAdr4 = $_POST['MR_Selected_LgAdr4_'.$cart->id_carrier];
					$mrselected->MR_Selected_CP = $_POST['MR_Selected_CP_'.$cart->id_carrier];
					$mrselected->MR_Selected_Ville = $_POST['MR_Selected_Ville_'.$cart->id_carrier];
					$mrselected->MR_Selected_Pays = $_POST['MR_Selected_Pays_'.$cart->id_carrier];
					$mrselected->save();
				}
			}
		}
	}
	
	public function hookupdateCarrier($params)
	{
		$new_carrier = $params['carrier'];
		if ($new_carrier->external_module_name == 'mondialrelay')
		{
			$mr_data = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'mr_method` WHERE `id_carrier` = '.intval($params['id_carrier']));
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'mr_method` (mr_Name, mr_Pays_list, mr_ModeCol, mr_ModeLiv, mr_ModeAss, id_carrier)
										VALUES ("'.pSQL($mr_data['mr_Name']).'", "'.pSQL($mr_data['mr_Pays_list']).'", "'.pSQL($mr_data['mr_ModeCol']).'", "'.pSQL($mr_data['mr_ModeLiv']).'", "'.pSQL($mr_data['mr_ModeAss']).'", '.intval($new_carrier->id).')');
		}
	}
	
	public function hookextraCarrier($params)
	{	
		global $smarty, $cart, $cookie, $defaultCountry, $nbcarriers;

		if (Configuration::get('MR_ENSEIGNE_WEBSERVICE') == '' OR
			Configuration::get('MR_CODE_MARQUE') == '' OR
			Configuration::get('MR_KEY_WEBSERVICE') == '' OR
			Configuration::get('MR_LANGUAGE') == '')
			return '';

		$totalweight = Configuration::get('MR_WEIGHT_COEF') * $cart->getTotalWeight();
	
		if (Validate::isUnsignedInt($cart->id_carrier))
		{
			$carrier = new Carrier(intval($cart->id_carrier));
			if ($carrier->active AND !$carrier->deleted)
				$checked = intval($cart->id_carrier);
		}
		if (!isset($checked) OR $checked == 0)
			$checked = intval(Configuration::get('PS_CARRIER_DEFAULT'));

		$address = new Address(intval($cart->id_address_delivery));
		$id_zone = Address::getZoneById(intval($address->id));
		$country = new Country(intval($address->id_country));
	
		$query = self::getmrth(intval($cookie->id_lang), true, intval($country->id_zone), $country->iso_code);

		$resultsArray = array();
		$i = 0;
		foreach ($query AS $k => $row)
		{
			$carrier = new Carrier(intval($row['id_carrier']));
			if ((Configuration::get('PS_SHIPPING_METHOD') AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false) OR
				(!Configuration::get('PS_SHIPPING_METHOD') AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
			{
				unset($result[$k]);
				continue ;
			}
			if ($row['range_behavior'])
			{
				// Get id zone
				if (isset($cart->id_address_delivery) AND $cart->id_address_delivery)
					$id_zone = Address::getZoneById(intval($cart->id_address_delivery));
				else
					$id_zone = intval($defaultCountry->id_zone);
				if ((Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone))) OR
					(!Configuration::get('PS_SHIPPING_METHOD') AND (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, 4), $id_zone))))
					{
						unset($result[$k]);
						continue ;
					}
			}

			$row['col'] = self::get_carrier('col',intval($row['id_carrier']));
			$row['liv'] = self::get_carrier('liv',intval($row['id_carrier']));
			$row['ass'] = self::get_carrier('ass',intval($row['id_carrier']));
			$row['name'] = self::get_carrier('name',intval($row['id_carrier']));
			$row['price'] = $cart->getOrderShippingCost(intval($row['id_carrier']));
			$row['img'] = file_exists(_PS_SHIP_IMG_DIR_.intval($row['id_carrier']).'.jpg') ? _THEME_SHIP_DIR_.intval($row['id_carrier']).'.jpg' : '';


			if ($row['liv'] == '24R' && $totalweight <= 20000)
			{
				$resultsArray[] = $row;
				$i++;
			}
			elseif ($row['liv'] == 'DRI' && $totalweight >= 20000 && $totalweight <= 130000)
			{
				$resultsArray[] = $row;
				$i++;
			}
			elseif ($row['liv'] == 'LD1' && $totalweight <= 60000)
			{
				$resultsArray[] = $row;
				$i++;
			}	
			elseif ($row['liv'] == 'LDS' && $totalweight >= 30000 && $totalweight <= 130000)
			{
				$resultsArray[] = $row;
				$i++;
			}
	 	}

		if ($i > 0)
		{
			include_once(_PS_MODULE_DIR_.'/mondialrelay/page_iso.php');

			$smarty->assign( array(
							'address_map' => $address->address1.', '.$address->postcode.', '.ote_accent($address->city).', '.$country->iso_code,
							'input_cp'  => $address->postcode,
							'input_ville'  => ote_accent($address->city),
							'input_pays'  => $country->iso_code,
							'input_poids'  => Configuration::get('MR_WEIGHT_COEF') * $cart->getTotalWeight(),
							'nbcarriers' => $nbcarriers,
							'checked' => intval($checked),
							'google_api_key' => Configuration::get('MR_GOOGLE_MAP'),
							'one_page_checkout' => (Configuration::get('PS_ORDER_PROCESS_TYPE') ? Configuration::get('PS_ORDER_PROCESS_TYPE') : 0),
							'carriersextra' => $resultsArray));
			$nbcarriers = $nbcarriers + $i;
			return $this->display(__FILE__, 'mondialrelay.tpl');
		}	
	}
	
	public function getContent()
	{	
		global $cookie;
		$error = null;
		
		if (isset($_GET['updatesuccess']))
			$this->_html .= '<div class="conf confirm"><img src="'._PS_ADMIN_IMG_.'/ok.gif" /> '.$this->l('Settings updated succesfull').'</div>';
		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
			{
				$nbErrors = sizeof($this->_postErrors);
				$this->_html .= '<div class="alert error"><h3>'.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3><ol>';
				foreach ($this->_postErrors AS $error)
					$this->_html .= '<li>'.$error.'</li>';
				$this->_html .= '</ol></div>';
			}
		}
		
		if (isset($_GET['delete']) && !empty($_GET['delete']))
			self::mrDelete(pSQL($_GET['delete']));

		$this->_html .= '<h2>'.$this->l('Configure Mondial Relay Rate Module').'</h2>'.
		'<style> . "\n" . ' . file_get_contents(_MR_CSS_) . "\n" . '</style>
		<fieldset><legend><img src="../modules/mondialrelay/logo.gif" />'.$this->l('To create a Mondial Relay carrier').'</legend>
		- '.$this->l('Registrate first the Mondial Relay Account Settings').'<br />
		- '.$this->l('Create a Carrier').'<br />
		- '.$this->l('Define a price for your carrier on').' <a href="index.php?tab=AdminCarriers&token='.Tools::getAdminToken('AdminCarriers'.intval(Tab::getIdFromClassName('AdminCarriers')).intval($cookie->id_employee)).'" class="green">'.$this->l('The Carrier page').'</a><br />
		- '.$this->l('To generate sticks, you must have register a correct address of your store on').' <a href="index.php?tab=AdminContact&token='.Tools::getAdminToken('AdminContact'.intval(Tab::getIdFromClassName('AdminContact')).intval($cookie->id_employee)).'" class="green">'.$this->l('The contact page').'</a><br />
		- '.$this->l('Go see the front office').'<br />'.self::infodoc().'<br class="clear" />
		<p>'.$this->l('URL Cron Task:').' '.Tools::getHttpHost(true, true)._MODULE_DIR_.$this->name.'/cron.php?secure_key='.Configuration::get('MONDIAL_RELAY_SECURE_KEY').'</p></fieldset>
		<br class="clear" />'.self::settingsForm().self::settingsstateorderForm().self::addMethodForm().self::shippingForm().
		'<br class="clear" />';

		return $this->_html;
	}
	
	public function mrDelete($id)
	{
		$id = Db::getInstance()->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_ .'mr_method` WHERE `id_mr_method` = "'.intval($id).'"');
		if (Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_ .'carrier` SET `active` = 0, `deleted` = 1 WHERE `id_carrier` = "'.intval($id).'"'))
		{
			$uri = explode('&', $_SERVER['REQUEST_URI']);
		}
		$this->_html .= '<div class="conf confirm"><img src="'._PS_ADMIN_IMG_.'/ok.gif" /> '.$this->l('Delete succesfull').'</div>';
	}
	
	public function mrUpdate($type, Array $array, Array $keyArray)
	{
		global $cookie;
		
		if ($type == 'settings')
		{
			Configuration::updateValue('MR_ENSEIGNE_WEBSERVICE', $array[0]);
			Configuration::updateValue('MR_CODE_MARQUE', $array[1]);
			Configuration::updateValue('MR_KEY_WEBSERVICE', $array[2]);
			Configuration::updateValue('MR_LANGUAGE', $array[3]);
			Configuration::updateValue('MR_WEIGHT_COEF', $array[4]);
		}
		elseif ($type == 'shipping')
		{
			array_pop($array);
			foreach ($array AS $Key => $value)
			{
				$key    = explode(',', $Key);
				$id = Db::getInstance()->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_ .'mr_method` WHERE `id_mr_method` = "'.intval($key[0]).'"');
				Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'carrier SET active = "'.intval($value).'" WHERE `id_carrier` = "'.intval($id).'"');
			}
		}
		elseif ($type == 'addShipping') 
		{
			$query = 'INSERT INTO ' .  _DB_PREFIX_ . 'mr_method (';

			for ($q = 0; $q <= count($keyArray) - 1; $q++)
			{	
				$end    = ($q == count($keyArray) - 1) ? '' : ', ';
				$query .= $keyArray[$q] . $end;
			}
			
			$query .= ') VALUES(';
			
			for ($j = 0; $j <= count($array) - 1; $j++)
			{
				$var = $array[$j];
				if (is_array($var))
					$var = implode(",", $var);
				$end    = ($j == count($array) - 1) ? '' : ', ';
				$query .= "'" . pSQL($var). "'" . $end;
			}
			$query .= ')';

			Db::getInstance()->Execute($query);
		
			$mainInsert = mysql_insert_id();
			$default = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "configuration WHERE name = 'PS_CARRIER_DEFAULT'");
			$check   = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "carrier");
			$checkD = array();

			foreach($check AS $Key)
			{
				foreach($Key AS $key => $value)
					if($key == "id_carrier")
						$checkD[] = $value;
			}

			Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'carrier` (id_tax, url, name, active, is_module, shipping_external, need_range, external_module_name)
									VALUES("1", NULL, "'.pSQL($array[0]).'", "1", "1", "0", "1", "mondialrelay")');

			$get   = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'carrier WHERE id_carrier = "' . mysql_insert_id() . '"');
			Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'mr_method SET id_carrier = "' . intval($get['id_carrier']) . '" WHERE id_mr_method = "' . pSQL($mainInsert) . '"');

			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'range_weight` (id_carrier, delimiter1, delimiter2) VALUES ('.intval($get['id_carrier']).', 0.000000, 10000.000000)');
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'range_price` (id_carrier, delimiter1, delimiter2) VALUES ('.intval($get['id_carrier']).', 0.000000, 10000.000000)');
			$groups = Group::getGroups(Configuration::get('PS_LANG_DEFAULT'));
			foreach ($groups as $group)
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'carrier_group` (id_carrier, id_group) VALUES('.intval($get['id_carrier']).', '.intval($group['id_group']).')');
			
			$zones = Zone::getZones();
			foreach ($zones as $zone)
			{
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'carrier_zone` (id_carrier, id_zone) VALUES('.intval($get['id_carrier']).', '.intval($zone['id_zone']).')');
				$range_price_id = Db::getInstance()->getValue('SELECT id_range_price FROM ' . _DB_PREFIX_ . 'range_price WHERE id_carrier = "'.intval($get['id_carrier']).'"');
				$range_weight_id = Db::getInstance()->getValue('SELECT id_range_weight FROM ' . _DB_PREFIX_ . 'range_weight WHERE id_carrier = "'.intval($get['id_carrier']).'"');
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'delivery` (id_carrier, id_range_price, id_range_weight, id_zone, price) VALUES('.intval($get['id_carrier']).', '.intval($range_price_id).', NULL,'.intval($zone['id_zone']).', 0.00)');
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'delivery` (id_carrier, id_range_price, id_range_weight, id_zone, price) VALUES('.intval($get['id_carrier']).', NULL, '.intval($range_weight_id).','.intval($zone['id_zone']).', 0.00)');
			}
			
			if(!in_array($default[0]['value'], $checkD))
				$default = Db::getInstance()->ExecuteS("UPDATE " . _DB_PREFIX_ . "configuration SET value = '" . intval($get['id_carrier']) . "' WHERE name = 'PS_CARRIER_DEFAULT'");
			
			Tools::redirectAdmin('index.php?tab=AdminModules&configure=mondialrelay&updatesuccess&token='.Tools::getAdminToken('AdminModules'.intval(Tab::getIdFromClassName('AdminModules')).intval($cookie->id_employee)));
		}
		else 
			return false;

		$uri = explode('&', $_SERVER['REQUEST_URI']);
		$this->_html .= '<div class="conf confirm"><img src="'._PS_ADMIN_IMG_.'/ok.gif" /> '.$this->l('Settings updated succesfull').'<img src="http://www.prestashop.com/modules/mondialrelay.png?enseigne='.urlencode(Tools::getValue('mr_Enseigne_WebService')).'" style="float:right" /></div>';
		return true;
	}


	public function infodoc()
	{
		$test_update_1 = Configuration::get('MONDIAL_RELAY_INSTALL_UPDATE_1');
		
		if ($test_update_1 != 1) 
		{
			Db::getInstance()->Execute(trim('ALTER TABLE `'._DB_PREFIX_.'mr_method` ADD mr_ModeAss varchar(3)  NOT NULL DEFAULT \'0\' AFTER `mr_ModeLiv` ;'));
			Db::getInstance()->Execute(trim('ALTER TABLE `'._DB_PREFIX_.'mr_configuration` ADD `mr_weight_coef` FLOAT NOT NULL DEFAULT \'1\' AFTER `mr_Mail` ;'));

			$dir_admin = PS_ADMIN_DIR;
			$dir_module = _PS_MODULE_DIR_;
			$dir_base = str_replace("/modules/" , "", $dir_module);

			$dir_newsfiles = $dir_module.'mondialrelay/newsfiles';

			Configuration::updateValue('MONDIAL_RELAY_INSTALL_UPDATE_1', 1);
		}

		return '';
	}


	
	public function addMethodForm()
	{
		$zones = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "zone WHERE active = 1");
		$output = '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" >
			<fieldset class="shippingList addMethodForm">
				<legend><img src="../modules/mondialrelay/logo.gif" />'.$this->l('Add a Shipping Method').'</legend>
				<ol>
					<li>
						<label for="mr_Name" class="shipLabel">'.$this->l('Carrier\'s name').'<sup>*</sup></label>
						<input type="text" id="mr_Name" name="mr_Name" '.(Tools::getValue('mr_Name') ? 'value="'.Tools::getValue('mr_Name').'"' : '').'/>
					</li>

					<li>
						<label for="mr_ModeCol" class="shipLabel">'.$this->l('Collection Mode').'<sup>*</sup></label>
						<select name="mr_ModeCol" id="mr_ModeCol" style="width:200px">
						<option value="CCC" selected >CCC : '.$this->l('Collection at the store').'</option>
						<!--<option value="CDR" >CDR : '.$this->l('Collection at home for standards expeditions').'</option>
						<option value="CDS" >CDS : '.$this->l('Collection at home for heavy or voluminous expeditions').'</option>
						<option value="REL" >REL : '.$this->l('Collection at a Relay Point').'</option>-->
						</select> 
					</li>

					<li>
						<label for="mr_ModeLiv" class="shipLabel">'.$this->l('Livraison Mode').'<sup>*</sup></label>
						<select name="mr_ModeLiv" id="mr_ModeLiv" style="width:200px">
						<!--<option value="LCC" >LCC : '.$this->l('Livraison chez le client chargeur / l\'enseigne').'</option>-->
						<option value="24R" selected >24R : '.$this->l('Livraison at a Relay Point').'</option>
						<option value="DRI" >DRI : '.$this->l('Colis Drive Livraison').'</option>
						<option value="LD1" >LD1 : '.$this->l('Home Livraison RDC (1 person)').'</option>
						<option value="LDS" >LDS : '.$this->l('Special Home Livraison (2 persons)').'</option>
						</select>
					</li>
					
					<li>
						<label for="mr_ModeAss" class="shipLabel">'.$this->l('Insurance').'<sup>*</sup></label>
						<select name="mr_ModeAss" id="mr_ModeAss" style="width:200px">
						<option value="0" selected>0 : '.$this->l('No insurance').'</option>
						<option value="1">1 : '.$this->l('Complementary Insurance Lv1').'</option>
						<option value="2">2 : '.$this->l('Complementary Insurance Lv2').'</option>
						<option value="3">3 : '.$this->l('Complementary Insurance Lv3').'</option>
						<option value="4">4 : '.$this->l('Complementary Insurance Lv4').'</option>
						<option value="5">5 : '.$this->l('Complementary Insurance Lv5').'</option>
						</select>
					</li>

					<li>	
						<label for="mr_Pays_list" class="shipLabel">'.$this->l('Livraison Countries:').'<sup>*</sup></label>	
						<SELECT NAME="mr_Pays_list[]" id="mr_Pays_list"  MULTIPLE SIZE=5>
						<OPTION VALUE="FR">'.$this->l('France').'</option>
						<OPTION VALUE="BE">'.$this->l('Belgium').'</option>
						<OPTION VALUE="LU">'.$this->l('Luxembourg').'</option>
						<OPTION VALUE="ES">'.$this->l('Spain').'</option>
						</SELECT>
					</li>

					<!--<li>
						<label for="zones" class="shipLabel">Zone:<sup>*</sup></label>
						<select id="zones" name="zone">';
	  			  foreach($zones AS $zone) {
							$output .= '<option value="' . $zone['id_zone'] . '">' . $zone['name'] . '</option>';
						}
		$output .= '				
						</select>
					</li>-->
					<li class="mrSubmit">
						<input type="submit" name="submitMethod" value="' . $this->l('Add a Shipping Method') . '" class="button" />
					</li>
					<li>
						<sup><sup>*</sup> ' . $this->l('Required') . '</sup>
					</li>
				</ol>
			</fieldset>
		</form>';
		
		return $output;
	}
	
	public function shippingForm()
	{
		global $cookie;

		$query = Db::getInstance()->ExecuteS('
			SELECT m.*
			FROM `'._DB_PREFIX_.'mr_method` m
			JOIN `'._DB_PREFIX_.'carrier` c ON (c.`id_carrier` = m.`id_carrier`)
			WHERE c.`deleted` = 0');
			
		$output .= '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset class="shippingList">
				<legend><img src="../modules/mondialrelay/logo.gif" />'.$this->l('Shipping Method\'s list').'</legend>
				<ol>';
		if (!sizeof($query))
			$output .= '<li>'.$this->l('No shipping methods created').'</li>';
		foreach ($query AS $Options)
		{
			$output .= '
					<li>
						<a href="' . 'index.php?tab=AdminModules&configure=mondialrelay&token='.Tools::getAdminToken('AdminModules'.intval(Tab::getIdFromClassName('AdminModules')).intval($cookie->id_employee)).'&delete=' . $Options['id_mr_method'] . '"><img src="../img/admin/disabled.gif" alt="Delete" title="Delete" /></a>' . str_replace('_', ' ', $Options['mr_Name']) . ' (' . $Options['mr_ModeCol'] . '-' . $Options['mr_ModeLiv'] . ' - ' . $Options['mr_ModeAss'] . ' : '.$Options['mr_Pays_list'].') 
						<a href="index.php?tab=AdminCarriers&id_carrier=' . intval($Options['id_carrier']) . '&updatecarrier&token='.Tools::getAdminToken('AdminCarriers'.intval(Tab::getIdFromClassName('AdminCarriers')).intval($cookie->id_employee)).'">'.$this->l('Config Shipping.').'</a>	
					</li>';
		}
		$output .= ' 

				</ol>
			</fieldset>
		</form><br />
		';

		return $output;	
	}
	
	public function settingsstateorderForm()
	{
		global $cookie;
		$this->orderState = Configuration::get('MONDIAL_RELAY_ORDER_STATE');
	    $output = '';
		$output .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" class="form">';
		$output .= '<fieldset><legend><img src="../modules/mondialrelay/logo.gif" />'.$this->l('Settings').'</legend>';
		$output .= '<label for="id_order_state">' . $this->l('Order state') . '</label>';
		$output .= '<div class="margin-form">';
		$output .= '<select id="id_order_state" name="id_order_state" style="width:250px">';

		$order_states = OrderState::getOrderStates(intval($cookie->id_lang));
		foreach ( $order_states as $order_state)
		{
			$output  .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
			if ($this->orderState == $order_state['id_order_state'] ) $output  .= ' selected="selected"';
			$output  .= '>' . $order_state['name'] . '</option>';
		}
		$output .= '</select>';
		$output .= '<p>' . $this->l('Choose the order state for sticks. You can administrate the sticks on').' ';
		$output .= '<a href="index.php?tab=AdminMondialRelay&token='.Tools::getAdminToken('AdminMondialRelay'.intval(Tab::getIdFromClassName('AdminMondialRelay')).intval($cookie->id_employee)).'" class="green">'.
		$this->l('the Mondial Relay administration page').'</a></p>';
		$output .= '</div>
		<div class="clear"></div>
		<label>'.$this->l('Google map').' </label>
		<div class="margin-form">
			<input type="radio" name="mr_google_key" id="mr_google_key_on" value="1" '.(Configuration::get('MR_GOOGLE_MAP') ? 'checked="checked" ' : '').'/>
			<label class="t" for="mr_google_key_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Yes').'" /></label>
			<input type="radio" name="mr_google_key" id="mr_google_key_off" value="0" '.(!Configuration::get('MR_GOOGLE_MAP') ? 'checked="checked" ' : '').'/>
			<label class="t" for="mr_google_key_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('No').'" /></label>
			<p>'.$this->l('Display a google map on your Mondial Relay carrier, it may make carrier page loading slower').'</p>
		</div>';
		$output .= '<div class="margin-form"><input type="submit" name="submit_order_state"  value="' . $this->l('Save') . '" class="button" /></div>';
		$output .= '</fieldset></form><br>';
		
		return $output;
    }

	
	public function settingsForm()
	{
		$output .= '
			<script type="text/javascript" >var url_appel="";</script>
			<script type="text/javascript" src="../modules/mondialrelay/kit_mondialrelay/js/ressources_MR.js"></script>
			<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" >
				<fieldset class="settingsList">
					<legend><img src="../modules/mondialrelay/logo.gif" />'.$this->l('Mondial Relay Account Settings').'</legend>
					<ol>
						<li>
							<label style="float:none;" for="mr_Enseigne_WebService" class="mrLabel">' . $this->l('mr_Enseigne_WebService:') . '<sup>*</sup></label>
							<input style="float:right;" id="mr_Enseigne_WebService" class="mrInput" type="text" name="mr_Enseigne_WebService" value="' .
							(Tools::getValue('mr_Enseigne_WebService') ? Tools::getValue('mr_Enseigne_WebService') : Configuration::get('MR_ENSEIGNE_WEBSERVICE')) . '"/>
						</li>
						<li>
							<label style="float:none;" for="mr_code_marque" class="mrLabel">' . $this->l('mr_code_marque:') . '<sup>*</sup></label>
							<input style="float:right;" id="mr_code_marque" class="mrInput" type="text" name="mr_code_marque" value="' .
							(Tools::getValue('mr_code_marque') ? Tools::getValue('mr_code_marque') : Configuration::get('MR_CODE_MARQUE')) . '"/>
						</li>
						<li>
							<label style="float:none;" for="mr_Key_WebService" class="mrLabel">' . $this->l('mr_Key_WebService:') . '<sup>*</sup></label>
							<input style="float:right;" id="mr_Key_WebService" class="mrInput" type="text" name="mr_Key_WebService" value="' .
							(Tools::getValue('mr_Key_WebService') ? Tools::getValue('mr_Key_WebService') : Configuration::get('MR_KEY_WEBSERVICE')) . '"/>
						</li>
						<li>
							<label style="float:none;" for="mr_Langage" class="mrLabel">' . $this->l('mr_Langage:') . '<sup>*</sup></label>
							<select style="float:right;" id="mr_Langage" name="mr_Langage" value="'.
							(Tools::getValue('mr_Langage') ? Tools::getValue('mr_Langage') : Configuration::get('MR_LANGUAGE')).'" >';
		$languages = Language::getLanguages();
		foreach ($languages as $language)
			$output .= '<option value="'.strtoupper($language['iso_code']).'" '.(strtoupper($language['iso_code']) == Configuration::get('MR_LANGUAGE') ? 'selected="selected"' : '').'>'.$language['name'].'</option>';
							
				$output .= '</select>
						</li>
						<li>
							<label style="float:none;" for="mr_weight_coef" class="mrLabel">' . $this->l('mr_weight_coef:') . '<sup>*</sup></label>
							<input id="mr_weight_coef" class="mrInput" type="text" name="mr_weight_coef" value="' . 
							(Tools::getValue('mr_weight_coef') ? Tools::getValue('mr_weight_coef') : Configuration::get('MR_WEIGHT_COEF')) . '"  style="width:45px;"/> ' . 
							$this->l('grammes = 1 ') . Configuration::get('PS_WEIGHT_UNIT').'
						</li>						
						<li class="MRSubmit">
							<input type="submit" name="submitMR" value="' . $this->l('Update Settings') . '" class="button" />
						</li>
						<li>
							<sup><sup>*</sup> ' . $this->l('Required') . '</sup>
						</li>
					</ol>
				</fieldset>
			</form>';

		return $output;
	}

	public function displayInfoByCart($id_cart)
	{
		$simpleresul = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'mr_selected where id_cart='.intval($id_cart));
	
		if (trim($simpleresul[0]['exp_number']) != '0') 
			@$sortie .= $this->l('Nb expedition:').$simpleresul[0]['exp_number']."<br>";
		if (trim($simpleresul[0]['url_etiquette']) != '0') 
			@$sortie .= "<a href='".$simpleresul[0]['url_etiquette']."' target='etiquette".$simpleresul[0]['url_etiquette']."'>".$this->l('Url etiquette')."</a><br>";
		if (trim($simpleresul[0]['url_suivi']) != '0')
			@$sortie .= "<a href='".$simpleresul[0]['url_suivi']."' target='suivi".$simpleresul[0]['exp_number']."'>".$this->l('Url de suivi')."</a><br>";
		if (trim($simpleresul[0]['MR_Selected_Num']) != '')
			@$sortie .= $this->l('Nb Point Relay :').$simpleresul[0]['MR_Selected_Num']."<br>";
		if (trim($simpleresul[0]['MR_Selected_LgAdr1']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_LgAdr1']."<br>";
		if (trim($simpleresul[0]['MR_Selected_LgAdr2']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_LgAdr2']."<br>";
		if (trim($simpleresul[0]['MR_Selected_LgAdr3']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_LgAdr3']."<br>"; 
		if (trim($simpleresul[0]['MR_Selected_LgAdr4']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_LgAdr4']."<br>"; 
		if (trim($simpleresul[0]['MR_Selected_CP']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_CP']." ";
		if (trim($simpleresul[0]['MR_Selected_Ville']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_Ville']."<br>";
		if (trim($simpleresul[0]['MR_Selected_Pays']) != '')
			@$sortie .= $simpleresul[0]['MR_Selected_Pays']."<br>";
		return '<p>'.$sortie.'</p>';
	}

	public function get_followup($shipping_number)
	{
	    $query    = 'SELECT url_suivi FROM '._DB_PREFIX_ .'mr_selected where  id_mr_selected=\''.intval($shipping_number).'\';';
		$settings = Db::getInstance()->ExecuteS($query);
        return $settings[0]['url_suivi'];
	}


	public function set_carrier($key,$value,$id_carrier)
	{
		if($key == 'name')
			$key = 'mr_Name';
		return Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'mr_method SET '.pSQL($key).'="'.pSQL($value).'" WHERE id_carrier=\''.intval($id_carrier).'\' ; ');
	}

	
	public function get_carrier($key, $id_carrier)
	{
		$settings = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'mr_method WHERE id_carrier=\''.intval($id_carrier).'\' ; ');

		$MR_meth = array(
			'name' => $settings[0]['mr_Name'] ,
			'col' => $settings[0]['mr_ModeCol'] ,
			'liv' => $settings[0]['mr_ModeLiv'] ,
			'ass' => $settings[0]['mr_ModeAss'] ,
		);
		return $MR_meth[$key];
	}
	
	public function getL($key)
	{
		$trad = array(
			'List of orders recognized' => $this->l('List of orders recognized'),
			'Order number' => $this->l('Order number'),
			'Email send to' => $this->l('Email send to'),
			'Print stick A4' => $this->l('Print stick A4'),
			'Print stick A5' => $this->l('Print stick A5'),
			'return' => $this->l('return'),
			'All orders which have the state' => $this->l('All orders which have the state'),
			'Change configuration' => $this->l('Change configuration'),
			'No orders with this state.' => $this->l('No orders with this state.'),
			'Order ID' => $this->l('Order ID'),
			'Customer' => $this->l('Customer'),
			'Total price' => $this->l('Total price'),
			'Total shipping' => $this->l('Total shipping'),
			'Date' => $this->l('Date'),
			'Put a Weight (grams)' => $this->l('Put a Weight (grams)'),
			'Selected' => $this->l('Selected'),
			'All' => $this->l('All'),
			'None' => $this->l('None'),
			'MR_Selected_Num' => $this->l('MR_Selected_Num'),
			'MR_Selected_Pays' => $this->l('MR_Selected_Pays'),
			'exp_number' => $this->l('exp_number'),
			'Detail' => $this->l('Detail'),
			'View' => $this->l('View'),
			'Generate' => $this->l('Generate'),
			'History of sticks creation' => $this->l('History of sticks creation'),
			'Orders ID' => $this->l('Orders ID'),
			'Exps num' => $this->l('Exps num'),
			'Delete selected history' => $this->l('Delete selected history'),
			'Closed' => $this->l('Closed'),
			'Monday' => $this->l('Monday'),
			'Tuesday' => $this->l('Tuesday'),
			'Wednesday' => $this->l('Wednesday'),
			'Thursday' => $this->l('Thursday'),
			'Friday' => $this->l('Friday'),
			'Saturday' => $this->l('Saturday'),
			'Sunday' => $this->l('Sunday'),
			'Select this Relay Point' => $this->l('Select this Relay Point'),
			'To generate sticks, you must have register a correct address of your store on' => $this->l('To generate sticks, you must have register a correct address of your store on'),
			'The contact page' => $this->l('The contact page'),
			'Settings updated succesfull' => $this->l('Settings updated succesfull'),
			'Empty adress : Are you sure you\'ve set a validate address in the Contact page?' => $this->l('Empty adress : Are you sure you\'ve set a validate address in the Contact page?')
		);
		return $trad[$key];
	}
}
?>
