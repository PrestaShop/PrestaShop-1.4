<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class ReferralProgram extends Module
{
	public function __construct()
	{
		$this->name = 'referralprogram';
		$this->tab = 'advertising_marketing';
		$this->version = '1.4';

		parent::__construct();

		$this->confirmUninstall = $this->l('All sponsor and friends would be deleted. Do you really want to uninstall this module ?');
		$this->displayName = $this->l('Customer referral program');
		$this->description = $this->l('Integrate a referral program system to your shop.');

		$path = dirname(__FILE__);
		if (strpos(__FILE__, 'Module.php') !== false)
			$path .= '/../modules/'.$this->name;
			
		if ($this->id)
		{
			$this->_configuration = Configuration::getMultiple(array(
				'REFERRAL_NB_FRIENDS',
				'REFERRAL_ORDER_QUANTITY',
				'REFERRAL_DISCOUNT_TYPE',
				'REFERRAL_DISCOUNT_VALUE'
			));
			$this->_configuration['REFERRAL_DISCOUNT_DESCRIPTION'] = Configuration::getInt('REFERRAL_DISCOUNT_DESCRIPTION');
			
			$this->_xmlFile = $path.'/referralprogram.xml';
			include_once($path.'/ReferralProgramModule.php');
		}
	}

	public function install()
	{
		$langs = Language::getLanguages(false);
		foreach ($langs AS $lang)
			$desc[$lang['id_lang']] = 'ReferralProgram';
		if (!parent::install()
				OR !$this->installDB()
				OR !Configuration::updateValue('REFERRAL_DISCOUNT_DESCRIPTION', $desc)
				OR !Configuration::updateValue('REFERRAL_ORDER_QUANTITY', 1)
				OR !Configuration::updateValue('REFERRAL_DISCOUNT_TYPE', 2)
				OR !Configuration::updateValue('REFERRAL_NB_FRIENDS', 5)
				OR !$this->registerHook('shoppingCart')
				OR !$this->registerHook('orderConfirmation')
				OR !$this->registerHook('updateOrderStatus')
				OR !$this->registerHook('adminCustomers')
				OR !$this->registerHook('createAccount')
				OR !$this->registerHook('createAccountForm')
				OR !$this->registerHook('customerAccount')
			)
			return false;
		foreach (Currency::getCurrencies() as $currency)
			Configuration::updateValue('REFERRAL_DISCOUNT_VALUE_'.(int)($currency['id_currency']), 5);
		/* This hook is optional */
		$this->registerHook('myAccountBlock');
		return true;
	}

	public function installDB()
	{
		return Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'referralprogram` (
			`id_referralprogram` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_sponsor` INT UNSIGNED NOT NULL,
			`email` VARCHAR(255) NOT NULL,
			`lastname` VARCHAR(128) NOT NULL,
			`firstname` VARCHAR(128) NOT NULL,
			`id_customer` INT UNSIGNED DEFAULT NULL,
			`id_discount` INT UNSIGNED DEFAULT NULL,
			`id_discount_sponsor` INT UNSIGNED DEFAULT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_referralprogram`),
			UNIQUE KEY `index_unique_referralprogram_email` (`email`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
	}

	public function uninstall()
	{
		if (!parent::uninstall()
				OR !$this->uninstallDB()
				OR !$this->removeMail()
			)
			return false;
		return true;
	}

	public function uninstallDB()
	{
		return Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'referralprogram`;');
	}

	public function removeMail()
	{
		$langs = Language::getLanguages(false);
		foreach ($langs as $lang)
			foreach ($this->_mails['name'] as $name)
				foreach ($this->_mails['ext'] as $ext)
				{
					$file = _PS_MAIL_DIR_.$lang['iso_code'].'/'.$name.'.'.$ext;
					if (file_exists($file) AND !@unlink($file))
						$this->_errors[] = $this->l('Cannot delete this file:').' '.$file;
				}
		return true;
	}

	private function _postProcess()
	{
		Configuration::updateValue('REFERRAL_ORDER_QUANTITY', (int)(Tools::getValue('order_quantity')));
		foreach (Tools::getValue('discount_value') as $id_currency => $discount_value)
			Configuration::updateValue('REFERRAL_DISCOUNT_VALUE_'.(int)($id_currency), floatval($discount_value));
		Configuration::updateValue('REFERRAL_DISCOUNT_TYPE', (int)(Tools::getValue('discount_type')));
		Configuration::updateValue('REFERRAL_NB_FRIENDS', (int)(Tools::getValue('nb_friends')));
		Configuration::updateValue('REFERRAL_DISCOUNT_DESCRIPTION', Tools::getValue('discount_description'));
		$this->_html .= $this->displayConfirmation($this->l('Configuration updated.'));
	}

	private function _postValidation()
	{
		$this->_errors = array();
		if (!(int)(Tools::getValue('order_quantity')) OR Tools::getValue('order_quantity') < 0)
			$this->_errors[] = $this->displayError($this->l('Order quantity is required/invalid.'));
		if (!is_array(Tools::getValue('discount_value')))
			$this->_errors[] = $this->displayError($this->l('Discount value is invalid.'));
		foreach (Tools::getValue('discount_value') as $id_currency => $discount_value)
 			if ($discount_value == '')
				$this->_errors[] = $this->displayError($this->l('Discount value for the currency #').$id_currency.$this->l(' is empty.'));
 			elseif (!Validate::isUnsignedFloat($discount_value))
				$this->_errors[] = $this->displayError($this->l('Discount value for the currency #').$id_currency.$this->l(' is invalid.'));
		if (!(int)(Tools::getValue('discount_type')) OR Tools::getValue('discount_type') < 1 OR Tools::getValue('discount_type') > 2)
			$this->_errors[] = $this->displayError($this->l('Discount type is required/invalid.'));
		if (!(int)(Tools::getValue('nb_friends')) OR Tools::getValue('nb_friends') < 0)
			$this->_errors[] = $this->displayError($this->l('Number of friends is required/invalid.'));
	}

	private function _writeXml()
	{
		$forbiddenKey = array('submitUpdate'); // Forbidden key

		// Generate new XML data
		$newXml = '<'.'?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
		$newXml .= '<referralprogram>'."\n";
		$newXml .= "\t".'<body>';
		// Making body data
		foreach ($_POST AS $key => $field)
		{
			if ($line = $this->putContent($newXml, $key, $field, $forbiddenKey, 'body'))
				$newXml .= $line;
		}
		$newXml .= "\n\t".'</body>'."\n";
		$newXml .= '</referralprogram>'."\n";

		/* write it into the editorial xml file */
		if ($fd = @fopen($this->_xmlFile, 'w'))
		{
			if (!@fwrite($fd, $newXml))
				$this->_html .= $this->displayError($this->l('Unable to write to the xml file.'));
			if (!@fclose($fd))
				$this->_html .= $this->displayError($this->l('Can\'t close the xml file.'));
		}
		else
			$this->_html .= $this->displayError($this->l('Unable to update the xml file. Please check the xml file\'s writing permissions.'));
	}

	public function putContent($xml_data, $key, $field, $forbidden, $section)
	{
		foreach ($forbidden AS $line)
			if ($key == $line)
				return 0;
		if (!preg_match('/^'.$section.'_/i', $key))
			return 0;
		$key = preg_replace('/^'.$section.'_/i', '', $key);
		$field = Tools::htmlentitiesDecodeUTF8(htmlspecialchars($field));
		if (!$field)
			return 0;
		return ("\n\t\t".'<'.$key.'><![CDATA['.$field.']]></'.$key.'>');
	}

	public function getContent()
	{
		if (Tools::isSubmit('submitReferralProgram'))
		{
			$this->_postValidation();
			if (!sizeof($this->_errors))
				$this->_postProcess();
			else
				foreach ($this->_errors AS $err)
					$this->_html .= '<div class="errmsg">'.$err.'</div>';
		}
		elseif (Tools::isSubmit('submitText'))
		{
			foreach ($_POST AS $key => $value)
				if (!Validate::isString(Tools::getValue($key)))
				{
					$this->_html .= $this->displayError($this->l('Invalid html field, javascript is forbidden'));
					$this->_displayForm();
					return $this->_html;
				}
			$this->_writeXml();
		}

		$this->_html .= '<h2>'.$this->displayName.'</h2>';
		$this->_displayForm();
		$this->_displayFormRules();
		return $this->_html;
	}

	private function _displayForm()
	{
		$divLangName = 'cpara¤dd';
		$currencies = Currency::getCurrencies();

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="'._PS_ADMIN_IMG_.'prefs.gif" alt="'.$this->l('Settings').'" />'.$this->l('Settings').'</legend>
			<p>
				<label class="t" for="order_quantity">'.$this->l('Number of orders required to earn a discount:').'</label>
				<input type="text" name="order_quantity" id="order_quantity" value="'.Tools::getValue('order_quantity', Configuration::get('REFERRAL_ORDER_QUANTITY')).'" />
			</p>
			<p>
				<label class="t" for="nb_friends">'.$this->l('Number of friends displayed in customer account:').'</label>
				<input type="text" name="nb_friends" id="nb_friends" value="'.Tools::getValue('nb_friends', Configuration::get('REFERRAL_NB_FRIENDS')).'" />
			</p>
			<p>
				<label class="t">'.$this->l('Voucher type:').'</label>
				<input type="radio" name="discount_type" id="discount_type1" value="1" '.(Tools::getValue('discount_type', Configuration::get('REFERRAL_DISCOUNT_TYPE')) == 1 ? 'checked="checked"' : '').' />
				<label class="t" for="discount_type1">'.$this->l('Percentage').'</label>
				&nbsp;
				<input type="radio" name="discount_type" id="discount_type2" value="2" '.(Tools::getValue('discount_type', Configuration::get('REFERRAL_DISCOUNT_TYPE')) == 2 ? 'checked="checked"' : '').' />
				<label class="t" for="discount_type2">'.$this->l('Amount').'</label>
			</p>';

		foreach ($currencies as $currency)
			$this->_html .= '
			<p>
				<label class="t" for="discount_value['.(int)($currency['id_currency']).']">'.(Configuration::get('PS_CURRENCY_DEFAULT') == $currency['id_currency'] ? '<span style="font-weight: bold;">' : '').$this->l('Voucher value in ').htmlentities($currency['name'], ENT_NOQUOTES, 'utf-8').$this->l(':').(Configuration::get('PS_CURRENCY_DEFAULT') == $currency['id_currency'] ? '<span style="font-weight: bold;">' : '').'</label>
				<input type="text" name="discount_value['.(int)($currency['id_currency']).']" id="discount_value['.(int)($currency['id_currency']).']" value="'.Tools::getValue('discount_value['.(int)($currency['id_currency']).']', Configuration::get('REFERRAL_DISCOUNT_VALUE_'.(int)($currency['id_currency']))).'" />
			</p>';

		$this->_html .= '
			<p>
				 <div style="float: left"><label class="t" for="discount_description">'.$this->l('Voucher description:').'</label></div>';
			$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
				$this->_html .= '
				<div id="dd_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; margin-left: 4px;">
					<input type="text" name="discount_description['.$language['id_lang'].']" id="discount_description['.$language['id_lang'].']" value="'.(isset($_POST['discount_description'][(int)($language['id_lang'])]) ? $_POST['discount_description'][(int)($language['id_lang'])] : $this->_configuration['REFERRAL_DISCOUNT_DESCRIPTION'][(int)($language['id_lang'])]).'" />
				</div>';
			$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'dd', true);
			$this->_html .= '
			</p>
			<div class="clear center"><input class="button" style="margin-top: 10px" name="submitReferralProgram" id="submitReferralProgram" value="'.$this->l('Update settings').'" type="submit" /></div>
		</fieldset>
		</form><br/>';
	}

	private function _displayFormRules()
	{
		global $cookie;
		
		// Languages preliminaries
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$iso = Language::getIsoById($defaultLanguage);
		$divLangName = 'cpara¤dd';

		// xml loading
		$xml = false;
		if (file_exists($this->_xmlFile))
		{
			if (!$xml = @simplexml_load_file($this->_xmlFile))
				$this->_html .= $this->displayError($this->l('Your text is empty.'));
		}

		$this->_html .= '
				<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						// Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
						content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
						document_base_url : "'.__PS_BASE_URI__.'",
						width: "600",
						height: "auto",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert,ajaxfilemanager",
						file_browser_callback : "ajaxfilemanager",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
						
					});
					function ajaxfilemanager(field_name, url, type, win) {
						var ajaxfilemanagerurl = "'.dirname($_SERVER["PHP_SELF"]).'/ajaxfilemanager/ajaxfilemanager.php";
						switch (type) {
							case "image":
								break;
							case "media":
								break;
							case "flash": 
								break;
							case "file":
								break;
							default:
								return false;
					}
		            tinyMCE.activeEditor.windowManager.open({
		                url: "'.dirname($_SERVER["PHP_SELF"]).'/ajaxfilemanager/ajaxfilemanager.php",
		                width: 782,
		                height: 440,
		                inline : "yes",
		                close_previous : "no"
		            },{
		                window : win,
		                input : field_name
		            });
            
		}
	</script>
		<script language="javascript">id_language = Number('.$defaultLanguage.');</script>
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->l('Referral program rules').'</legend>';
		foreach ($languages as $language)
		{
			$this->_html .= '
			<div id="cpara_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
				<textarea class="rte" cols="120" rows="25" id="body_paragraph_'.$language['id_lang'].'" name="body_paragraph_'.$language['id_lang'].'">'.($xml ? stripslashes(htmlspecialchars($xml->body->{'paragraph_'.$language['id_lang']})) : '').'</textarea>
			</div>';
		}
		$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'cpara', true);

		$this->_html .= '
				<div class="clear center"><input type="submit" name="submitText" value="'.$this->l('Update the text').'" class="button" style="margin-top: 10px" /></div>
			</fieldset>
		</form>';
	}


	/**
	* Hook call when cart created and updated
	* Display the discount name if the sponsor friend have one
	*/
	public function hookShoppingCart($params)
	{
		$cart = $params['cart'];
		if (!isset($cart->id_customer))
			return false;
		if (!($id_referralprogram = ReferralProgramModule::isSponsorised((int)($cart->id_customer), true)))
			return false;
		$referralprogram = new ReferralProgramModule($id_referralprogram);
		if (!Validate::isLoadedObject($referralprogram))
			return false;
		$discount = new Discount($referralprogram->id_discount);
		if (!Validate::isLoadedObject($discount))
			return false;
		if ($cart->checkDiscountValidity($discount, $cart->getDiscounts(), $cart->getOrderTotal(true, 1), $cart->getProducts())===false)
		{
			global $smarty;
			$smarty->assign(array(
				'discount_display' => Discount::display($discount->value, $discount->id_discount_type, new Currency($params['cookie']->id_currency)),
				'discount' => $discount
			));
			return $this->display(__FILE__, 'shopping-cart.tpl');
		}
		return false;
	}

	/**
	* Hook display on customer account page
	* Display an additional link on my-account and block my-account
	*/
	public function hookCustomerAccount($params)
	{
		return $this->display(__FILE__, 'my-account.tpl');
	}
	
	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}

	/**
	* Hook display on form create account
	* Add an additional input on bottom for fill the sponsor's e-mail address
	*/
	public function hookCreateAccountForm($params)
	{
		global $smarty;
		
		if (Configuration::get('PS_CIPHER_ALGORITHM'))
			$cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
		else
			$cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
		$explodeResult = explode('|', $cipherTool->decrypt(urldecode(Tools::getValue('sponsor'))));
		if ($explodeResult AND count($explodeResult) > 1 AND list($id_referralprogram, $email) = $explodeResult AND (int)($id_referralprogram) AND Validate::isEmail($email) AND $id_referralprogram == ReferralProgramModule::isEmailExists($email))
		{
			$referralprogram = new ReferralProgramModule($id_referralprogram);
			if (Validate::isLoadedObject($referralprogram))
			{
				/* hack for display referralprogram information in form */
				$_POST['customer_firstname'] = $referralprogram->firstname;
				$_POST['firstname'] = $referralprogram->firstname;
				$_POST['customer_lastname'] = $referralprogram->lastname;
				$_POST['lastname'] = $referralprogram->lastname;
				$_POST['email'] = $referralprogram->email;
				$_POST['email_create'] = $referralprogram->email;
				$sponsor = new Customer($referralprogram->id_sponsor);
				$_POST['referralprogram'] = $sponsor->email;
			}
		}
		return $this->display(__FILE__, 'authentication.tpl');
	}

	/**
	* Hook called on creation customer account
	* Create a discount for the customer if sponsorised
	*/
	public function hookCreateAccount($params)
	{
		global $cookie;
		$newCustomer = $params['newCustomer'];
		if (!Validate::isLoadedObject($newCustomer))
			return false;
		$postVars = $params['_POST'];
		if (empty($postVars) OR !isset($postVars['referralprogram']) OR empty($postVars['referralprogram']))
			return false;
		$sponsorEmail = $postVars['referralprogram'];
		if (!Validate::isEmail($sponsorEmail) OR $sponsorEmail == $newCustomer->email)
			return false;
		
		$sponsor = new Customer();
		if ($sponsor = $sponsor->getByEmail($sponsorEmail))
		{
			if ($id_referralprogram = ReferralProgramModule::isEmailExists($newCustomer->email, true, false))
			{
				$referralprogram = new ReferralProgramModule($id_referralprogram);
				if ($referralprogram->id_sponsor == $sponsor->id)
				{
					$referralprogram->id_customer = $newCustomer->id;
					$referralprogram->save();
					if ($referralprogram->registerDiscountForSponsored((int)($params['cookie']->id_currency)))
					{
						$discount = new Discount((int)($referralprogram->id_discount));
						if (Validate::isLoadedObject($discount))
						{
							$data = array(
								'{firstname}' => $newCustomer->firstname,
								'{lastname}' => $newCustomer->lastname,
								'{voucher_num}' => $discount->name,
								'{voucher_amount}' => Tools::displayPrice(floatval(Configuration::get('REFERRAL_DISCOUNT_VALUE_'.(int)($cookie->id_currency))), (int)(Configuration::get('PS_CURRENCY_DEFAULT'))));

							Mail::Send((int)($cookie->id_lang), 'referralprogram-voucher', Mail::l('Congratulations!'), $data, $newCustomer->email, $newCustomer->firstname.' '.$newCustomer->lastname, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
						}
					}
					return true;
				}
			}
		}
		return false;
	}

	/**
		* Hook display in tab AdminCustomers on BO
		* Data table with all sponsors informations for a customer
		*/
	public function hookAdminCustomers($params)
	{
		$customer = new Customer((int)($params['id_customer']));
		if (!Validate::isLoadedObject($customer))
			die (Tools::displayError('Incorrect object Customer.'));

		global $cookie;
		$friends = ReferralProgramModule::getSponsorFriend($customer->id);
		if ($id_referralprogram = ReferralProgramModule::isSponsorised((int)($customer->id), true))
		{
			$referralprogram = new ReferralProgramModule($id_referralprogram);
			$sponsor = new Customer($referralprogram->id_sponsor);
		}

		$html = '<h2>'.$this->l('Referral program').'</h2>';
		// link to the detail of the sponsor
		$html.= '<h3>'.(isset($sponsor) ? $this->l('Customer\'s sponsor:').' <a href="index.php?tab=AdminCustomers&id_customer='.$sponsor->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)($cookie->id_employee)).'">'.$sponsor->firstname.' '.$sponsor->lastname.'</a>' : $this->l('No one has sponsored this customer.')).'</h3>';

		if ($friends AND sizeof($friends))
		{
			$html.= '<h3>'.sizeof($friends).' '.(sizeof($friends) > 1 ? $this->l('sponsored customers:') : $this->l('sponsored customer:')).'</h3>';
			$html.= '
			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th class="center">'.$this->l('ID').'</th>
					<th class="center">'.$this->l('Name').'</th>
					<th class="center">'.$this->l('Email').'</th>
					<th class="center">'.$this->l('Registration date').'</th>
					<th class="center">'.$this->l('Sponsored customers').'</th>
					<th class="center">'.$this->l('Placed orders').'</th>
					<th class="center">'.$this->l('Inscribed').'</th>
				</tr>';
			foreach ($friends AS $key => $friend)
			{
				$orders = Order::getCustomerOrders($friend['id_customer']);
				$html.= '
				<tr '.($key++ % 2 ? 'class="alt_row"' : '').' '.((int)($friend['id_customer']) ? 'style="cursor: pointer" onclick="document.location = \'?tab=AdminCustomers&id_customer='.$friend['id_customer'].'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)($cookie->id_employee)).'\'"' : '').'>
					<td class="center">'.((int)($friend['id_customer']) ? $friend['id_customer'] : '--').'</td>
					<td>'.$friend['firstname'].' '.$friend['lastname'].'</td>
					<td>'.$friend['email'].'</td>
					<td>'.Tools::displayDate($friend['date_add'], (int)($cookie->id_lang), true).'</td>
					<td align="right">'.sizeof(ReferralProgramModule::getSponsorFriend($friend['id_customer'])).'</td>
					<td align="right">'.($orders ? sizeof($orders) : 0).'</td>
					<td align="center">'.((int)($friend['id_customer']) ? '<img src="'._PS_ADMIN_IMG_.'enabled.gif" />' : '<img src="'._PS_ADMIN_IMG_.'disabled.gif" />').'</td>
				</tr>';
			}
			$html.= '
				</table>';
		}
		else
			$html.= $customer->firstname.' '.$customer->lastname.' '.$this->l('has not sponsored any friends yet.');
		return $html.'<br/><br/>';
	}

	/**
		* Hook called when a order is confimed
		* display a message to customer about sponsor discount
		*/
	public function hookOrderConfirmation($params)
	{
		$order = $params['objOrder'];
		if ($order AND !Validate::isLoadedObject($order))
			return die (Tools::displayError('Incorrect object Order.'));
		$customer = new Customer($order->id_customer);
		$stats = $customer->getStats();
		$nbOrdersCustomer = (int)($stats['nb_orders']) + 1; // hack to count current order
		$referralprogram = new ReferralProgramModule(ReferralProgramModule::isSponsorised((int)($customer->id), true));
		if (!Validate::isLoadedObject($referralprogram))
			return false;
		$sponsor = new Customer($referralprogram->id_sponsor);
		if ((int)($nbOrdersCustomer) == (int)($this->_configuration['REFERRAL_ORDER_QUANTITY']))
		{
			$discount = new Discount($referralprogram->id_discount_sponsor);
			if (!Validate::isLoadedObject($discount))
				return false;
			$discount_display = $discount->display($discount->value, $discount->id_discount_type, new Currency($order->id_currency));
			global $smarty;
			$smarty->assign(array(
				'discount' => $discount_display,
				'sponsor_firstname' => $sponsor->firstname,
				'sponsor_lastname' => $sponsor->lastname
			));
			return $this->display(__FILE__, 'order-confirmation.tpl');
		}
		return false;
	}

	/**
		* Hook called when order status changed
		* register a discount for sponsor and send him an e-mail
		*/
	public function hookUpdateOrderStatus($params)
	{
		if (!Validate::isLoadedObject($params['newOrderStatus']))
			die (Tools::displayError('Some parameters are missing.'));
		$orderState = $params['newOrderStatus'];
		$order = new Order((int)($params['id_order']));
		if ($order AND !Validate::isLoadedObject($order))
			die (Tools::displayError('Incorrect object Order.'));
		$customer = new Customer($order->id_customer);
		$stats = $customer->getStats();
		$nbOrdersCustomer = (int)($stats['nb_orders']) + 1; // hack to count current order
		$referralprogram = new ReferralProgramModule(ReferralProgramModule::isSponsorised((int)($customer->id), true));
		if (!Validate::isLoadedObject($referralprogram))
			return false;
		$sponsor = new Customer($referralprogram->id_sponsor);
		if ((int)($orderState->logable) AND $nbOrdersCustomer >= (int)($this->_configuration['REFERRAL_ORDER_QUANTITY']) AND $referralprogram->registerDiscountForSponsor((int)($order->id_currency)))
		{
			$discount = new Discount($referralprogram->id_discount_sponsor);
			$currency = new Currency($order->id_currency);
			$discount_display = $discount->display($discount->value, $discount->id_discount_type, $currency);
			$data = array(
				'{sponsored_firstname}' => $customer->firstname,
				'{sponsored_lastname}' => $customer->lastname,
				'{discount_display}' => $discount_display,
				'{discount_name}' => $discount->name
			);
			Mail::Send((int)($order->id_lang), 'referralprogram-congratulations', Mail::l('Congratulations!'), $data, $sponsor->email, $sponsor->firstname.' '.$sponsor->lastname, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
			return true;
		}
		return false;
	}

}


