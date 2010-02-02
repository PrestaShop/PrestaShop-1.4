<?php

class ReferralProgram extends Module
{
	public function __construct()
	{
		$this->name = 'referralprogram';
		$this->tab = 'Tools';
		$this->version = '1.4';

		parent::__construct();

		$this->confirmUninstall = $this->l('All sponsor and friends would be deleted. Do you really want to uninstall this module ?');
		$this->displayName = $this->l('Customer referral program');
		$this->description = $this->l('Integrate a referral program system to your shop.');

		$this->_configuration = Configuration::getMultiple(array(
			'REFERRAL_NB_FRIENDS',
			'REFERRAL_ORDER_QUANTITY',
			'REFERRAL_DISCOUNT_TYPE',
			'REFERRAL_DISCOUNT_VALUE'
		));
		$this->_configuration['REFERRAL_DISCOUNT_DESCRIPTION'] = Configuration::getInt('REFERRAL_DISCOUNT_DESCRIPTION');

		$path = dirname(__FILE__);
		if (strpos(__FILE__, 'Module.php') !== false)
			$path .= '/../modules/'.$this->name;
			
		$this->_xmlFile = $path.'/referralprogram.xml';
		include_once($path.'/ReferralProgramModule.php');
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
				OR !Configuration::updateValue('REFERRAL_DISCOUNT_VALUE', 5)
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
		/* This hook is optional */
		$this->registerHook('myAccountBlock');
		return true;
	}
	function installDB()
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
		) DEFAULT CHARSET=utf8 ;');
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

	function uninstallDB()
	{
		return Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'referralprogram`;');
	}

	function removeMail()
	{
		$langs = Language::getLanguages(false);
		foreach ($langs as $lang)
		{
			foreach ($this->_mails['name'] as $name)
			{
				foreach ($this->_mails['ext'] as $ext)
				{
					$file = _PS_MAIL_DIR_.$lang['iso_code'].'/'.$name.'.'.$ext;
					if (file_exists($file))
					{
						if (!@unlink($file))
							$this->_errors[] = $this->l('Cannot delete this file:').' '.$file;
					}
				}
			}
		}
		return true;
	}

	private function _postProcess()
	{
		Configuration::updateValue('REFERRAL_ORDER_QUANTITY', intval(Tools::getValue('order_quantity')));
		Configuration::updateValue('REFERRAL_DISCOUNT_VALUE', floatval(Tools::getValue('discount_value')));
		Configuration::updateValue('REFERRAL_DISCOUNT_TYPE', intval(Tools::getValue('discount_type')));
		Configuration::updateValue('REFERRAL_NB_FRIENDS', intval(Tools::getValue('nb_friends')));
		Configuration::updateValue('REFERRAL_DISCOUNT_DESCRIPTION', Tools::getValue('discount_description'));
		$this->_html .= $this->displayConfirmation($this->l('Configuration updated.'));
	}

	private function _postValidation()
	{
		$this->_errors = array();
		if (!intval(Tools::getValue('order_quantity')))
			$this->_errors[] = $this->displayError($this->l('Order quantity is required.'));
		if (!floatval(Tools::getValue('discount_value')))
			$this->_errors[] = $this->displayError($this->l('Discount value is required.'));
		if (!intval(Tools::getValue('discount_type')))
			$this->_errors[] = $this->displayError($this->l('Discount type is required.'));
		if (!intval(Tools::getValue('nb_friends')))
			$this->_errors[] = $this->displayError($this->l('Number of friends is required.'));
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

	function putContent($xml_data, $key, $field, $forbidden, $section)
	{
		foreach ($forbidden AS $line)
		{
			if ($key == $line)
				return 0;
		}
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
			{
				foreach ($this->_errors AS $err)
					$this->_html .= '<div class="errmsg">'.$err.'</div>';
			}
		}
		elseif (Tools::isSubmit('submitText'))
		{
			foreach ($_POST AS $key => $value)
			{
				if (!Validate::isCleanHtml(Tools::getValue($key)))
				{
					$this->_html .= $this->displayError($this->l('Invalid html field, javascript is forbidden'));
					$this->_displayForm();
					return $this->_html;
				}
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
			</p>
			<p>
				<label class="t" for="discount_value">'.$this->l('Voucher value:').'</label>
				<input type="text" name="discount_value" id="discount_value" value="'.Tools::getValue('discount_value', Configuration::get('REFERRAL_DISCOUNT_VALUE')).'" />
			</p>
			<p>
				 <div style="float: left"><label class="t" for="discount_description">'.$this->l('Voucher description:').'</label></div>';
			$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
			$languages = Language::getLanguages();
			foreach ($languages as $language)
				$this->_html .= '
				<div id="discount_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; margin-left: 4px;">
					<input type="text" name="discount_description['.$language['id_lang'].']" id="discount_description['.$language['id_lang'].']" value="'.(isset($_POST['discount_description'][intval($language['id_lang'])]) ? $_POST['discount_description'][intval($language['id_lang'])] : $this->_configuration['REFERRAL_DISCOUNT_DESCRIPTION'][intval($language['id_lang'])]).'" />
				</div>';
			$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'discount_description', 'discount_description', true);
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
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$iso = Language::getIsoById($defaultLanguage);
		$divLangName = 'cpara';

		// xml loading
		$xml = false;
		if (file_exists($this->_xmlFile))
		{
			if (!$xml = @simplexml_load_file($this->_xmlFile))
				$this->_html .= $this->displayError($this->l('Your text is empty.'));
		}

		$this->_html .= '
		<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript">
		function tinyMCEInit(element)
		{
			$().ready(function() {
				$(element).tinymce({
					// Location of TinyMCE script
					script_url : \''.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js\',
					// General options
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
					theme_advanced_resizing : true,
					content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
					// Drop lists for link/image/media/template dialogs
					template_external_list_url : "lists/template_list.js",
					external_link_list_url : "lists/link_list.js",
					external_image_list_url : "lists/image_list.js",
					media_external_list_url : "lists/media_list.js",
					elements : "nourlconvert",
					convert_urls : false,
					language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
				});
			});
		}
		tinyMCEInit(\'textarea.rte\');
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
		if (!($id_referralprogram = ReferralProgramModule::isSponsorised(intval($cart->id_customer), true)))
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
		
		$blowfish = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
		$explodeResult = explode('|', $blowfish->decrypt(urldecode(Tools::getValue('sponsor'))));
		if ($explodeResult AND count($explodeResult) > 1 AND list($id_referralprogram, $email) = $explodeResult AND intval($id_referralprogram) AND Validate::isEmail($email) AND $id_referralprogram == ReferralProgramModule::isEmailExists($email))
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
					if ($referralprogram->registerDiscountForSponsored())
					{
						$discount = new Discount(intval($referralprogram->id_discount));
						if (Validate::isLoadedObject($discount))
						{
							$data = array(
								'{firstname}' => $newCustomer->firstname,
								'{lastname}' => $newCustomer->lastname,
								'{voucher_num}' => $discount->name,
								'{voucher_amount}' => floatval(Configuration::get('REFERRAL_DISCOUNT_VALUE')));
							
							Mail::Send(intval($cookie->id_lang), 'referralprogram-voucher', $this->l('Congratulations!'), $data, $newCustomer->email, $newCustomer->firstname.' '.$newCustomer->lastname, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
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
		$customer = new Customer(intval($params['id_customer']));
		if (!Validate::isLoadedObject($customer))
			die (Tools::displayError('Incorrect object Customer.'));

		global $cookie;
		$friends = ReferralProgramModule::getSponsorFriend($customer->id);
		if ($id_referralprogram = ReferralProgramModule::isSponsorised(intval($customer->id), true))
		{
			$referralprogram = new ReferralProgramModule($id_referralprogram);
			$sponsor = new Customer($referralprogram->id_sponsor);
		}

		$html = '<h2>'.$this->l('Referral program').'</h2>';
		// link to the detail of the sponsor
		$html.= '<h3>'.(isset($sponsor) ? $this->l('Customer\'s sponsor:').' <a href="index.php?tab=AdminCustomers&id_customer='.$sponsor->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'">'.$sponsor->firstname.' '.$sponsor->lastname.'</a>' : $this->l('No one has sponsored this customer.')).'</h3>';

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
				<tr '.($key++ % 2 ? 'class="alt_row"' : '').' '.(intval($friend['id_customer']) ? 'style="cursor: pointer" onclick="document.location = \'?tab=AdminCustomers&id_customer='.$friend['id_customer'].'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'\'"' : '').'>
					<td class="center">'.(intval($friend['id_customer']) ? $friend['id_customer'] : '--').'</td>
					<td>'.$friend['firstname'].' '.$friend['lastname'].'</td>
					<td>'.$friend['email'].'</td>
					<td>'.Tools::displayDate($friend['date_add'], intval($cookie->id_lang), true).'</td>
					<td align="right">'.sizeof(ReferralProgramModule::getSponsorFriend($friend['id_customer'])).'</td>
					<td align="right">'.($orders ? sizeof($orders) : 0).'</td>
					<td align="center">'.(intval($friend['id_customer']) ? '<img src="'._PS_ADMIN_IMG_.'enabled.gif" />' : '<img src="'._PS_ADMIN_IMG_.'disabled.gif" />').'</td>
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
		$nbOrdersCustomer = intval($stats['nb_orders']) + 1; // hack to count current order
		$referralprogram = new ReferralProgramModule(ReferralProgramModule::isSponsorised(intval($customer->id), true));
		if (!Validate::isLoadedObject($referralprogram))
			return false;
		$sponsor = new Customer($referralprogram->id_sponsor);
		if (intval($nbOrdersCustomer) == intval($this->_configuration['REFERRAL_ORDER_QUANTITY']))
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
		$order = new Order(intval($params['id_order']));
		if ($order AND !Validate::isLoadedObject($order))
			die (Tools::displayError('Incorrect object Order.'));
		$customer = new Customer($order->id_customer);
		$stats = $customer->getStats();
		$nbOrdersCustomer = intval($stats['nb_orders']) + 1; // hack to count current order
		$referralprogram = new ReferralProgramModule(ReferralProgramModule::isSponsorised(intval($customer->id), true));
		if (!Validate::isLoadedObject($referralprogram))
			return false;
		$sponsor = new Customer($referralprogram->id_sponsor);

		if (intval($orderState->logable) AND $nbOrdersCustomer >= intval($this->_configuration['REFERRAL_ORDER_QUANTITY']))
		{
			if ($referralprogram->registerDiscountForSponsor())
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
				Mail::Send(intval($order->id_lang), 'referralprogram-congratulations', $this->l('Congratulations!'), $data, $sponsor->email, $sponsor->firstname.' '.$sponsor->lastname, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
				return true;
			}
		}
		return false;
	}

}

?>
