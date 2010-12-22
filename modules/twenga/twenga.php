<?php
/**
 * 2007-2010 PrestaShop
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
 *  @author Prestashop SA <contact@prestashop.com>
 *  @copyright 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
 *  @version  Release: $Revision: 1.4 $
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 **/

/**
 * Twenga module allow to use the Twenga API to :
 * 1. subscribe to their Ready to Sell engine,
 * 2. activate a tracking for order process if user has been used twenga engine,
 * 3. submit a xml feed of shop products to Twenga. 
 * @todo test on 1.3 version
 * @author Nans Pellicari - Prestashop
 * @version 1.0
 */

class Twenga extends PaymentModule
{
    /**
     * path to load each needed files
     * @var string
     */
    private static $base_dir;
    /**
     * @var TwengaObj
     */
    private static $obj_twenga;
    
    /**
     * @var PrestashopStats
     */
    private static $obj_ps_stats;
    
    /**
     * @var string url used for the subscription to Twenga and prestashop
     */ 
    private $site_url;
    
    /**
     * @var string url to acces of the product list for Twenga
     */
    private $feed_url;
    
    /**
     * @var string url returned by Twenga API
     */
    private $inscription_url;
    
    /**
     * @var string used for displaying html
     */
    private $_html;
    
    /**
     * @var string
     */
    private $current_index;
    
    /**
     * @var string
     */
    private $token;
    
	public function __construct()
	{
        // Basic vars
	    global $currentIndex;
	    $this->current_index = $currentIndex;
	    $this->token = Tools::getValue('token');
	 	$this->name = 'twenga';
	 	$this->tab = 'Exports';
	 	$this->version = '1.0';
		
	 	parent::__construct();
		
        $this->displayName = $this->l('Twenga API');
        $this->description = $this->l('Export your products to Twenga.com and use the Twenga tracker for customer\'s order.');
        
        // For Twenga subscription
        $this->site_url = Tools::htmlentitiesutf8('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__);
        $this->feed_url = $this->site_url.'modules/twenga/export.php';
        
        self::$base_dir = _PS_ROOT_DIR_.'/modules/twenga/';
        
        require_once realpath(self::$base_dir.'/lib/PrestashopStats.php');
        require_once realpath(self::$base_dir.'/lib/TwengaObj.php');
        
        // set the base dir to load files needed for the TwengaObj class 
        TwengaObj::$base_dir = self::$base_dir.'/lib';
        
        // instanciate (one time) the TwengaObj and PrestashopStats
        if (self::$obj_twenga === NULL)
            self::$obj_twenga = new TwengaObj();
        if (self::$obj_ps_stats === NULL)
            self::$obj_ps_stats = new PrestashopStats($this->site_url);
	}
	/**
	 * For uninstall just need to delete the Merchant Login.
	 * @return see parent class.
	 */
	public function uninstall()
	{
	    if (!parent::uninstall()
	    OR !self::$obj_twenga->deleteMerchantLogin())
			return false;
		return true;
	}
	
	/**
	 * Method for redirect to Twenga subscription
	 */
	private static function redirectTwengaSubscription($link)
	{
	    echo '<script type="text/javascript" language="javascript">window.open("'.$link.'");</script>';
	}
	private function submitTwengaSubscription ()
	{
	     unset($_POST['submitTwengaSubscription']);
        $params = array_filter($_POST);
        $return = '';
        try {
            $return = self::$obj_twenga->getSubscriptionLink($params);
            self::$obj_ps_stats->actSubscription();
            $this->inscription_url = $return['message'];
            self::redirectTwengaSubscription($this->inscription_url);
        } catch (TwengaFieldsException $e) {
            $this->_errors[] = $this->l('Params are not allowed (see details) : ').'<br />'.$e->getMessage();
        } catch (TwengaException $e) {
            $this->_errors[] = $this->l('Error occurred With the Twenga API method (see details) : ').'<br /> '.nl2br($e->getMessage());
        }
	}
	private function submitTwengaLogin()
	{
	    if (!self::$obj_twenga->setHashkey($_POST['twenga_hashkey']))
            $this->_errors[] = Tools::displayError('Your hashkey code is not valid. Please check the mail already sent by Twenga.');
        if (!self::$obj_twenga->setUserName($_POST['twenga_user_name']))
            $this->_errors[] = Tools::displayError('Your user name is not valid. Please check the mail already sent by Twenga.');
        if (!self::$obj_twenga->setPassword($_POST['twenga_password']))
            $this->_errors[] = Tools::displayError('Your password is not valid. Please check the mail already sent by Twenga.');
        
        if(empty($this->_errors))
        {
            $bool_save = false; 
	        try{
	            $bool_save = self::$obj_twenga->saveMerchantLogin();
	            self::$obj_ps_stats->validateSubscription();
	        } catch (Exception $e) {
                $this->_errors[] = nl2br($e->getMessage());
	        }
	        if(!$bool_save)
	            $this->_errors[] = Tools::displayError('Your Authentication failed.')."<br />\n"
                    .Tools::displayError('Check the mail sent by Twenga after subscription. <br />If an error still occurred, contact the Twenga service.');
        }
	}
	private function submitTwengaActivateTracking()
	{
	    $activate = false;
        
        // Use TwengaObj::siteActivate() method to activate tracking.
        try {
           $activate = self::$obj_twenga->siteActivate();
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
        if($activate)
        {
            $this->registerHook('payment');
            $this->registerHook('updateOrderStatus');
            $this->registerHook('cancelProduct');
        }
	}
	private function submitTwengaDisableTracking()
	{
        $return = Db::getInstance()->ExecuteS('SELECT `id_hook` FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = \''.pSQL($this->id).'\'');
		foreach ($return as $hook)
		{
		    $this->unregisterHook($hook['id_hook']);
		}
	}
	public function preProcess()
	{
	    if(isset($_POST['submitTwengaSubscription']))
	    {
	       $this->submitTwengaSubscription();
	    }
	    if(isset($_POST['submitTwengaLogin']))
	    {
	        $this->submitTwengaLogin();
	    }
	    if(isset($_POST['submitTwengaActivateTracking']))
	    {
	        $this->submitTwengaActivateTracking();
	    }
	    if(isset($_POST['submitTwengaDisableTracking']))
	    {
	        $this->submitTwengaDisableTracking();
	    }
	}
	public function hookCancelProduct($params)
	{
	    if((float)$params['order']->total_products_wt <= 0)
	    {
	        $cart = new Cart($params['order']->id_cart);
            $customer = new Customer($params['order']->id_customer);
	        $params_to_twenga = array();
	        // @todo delete or not ??
//            $params_to_twenga['order_id'] = (string)$params['order']->id;
//            $params_to_twenga['user_id'] = (string)$customer->id;
//            $params_to_twenga['cli_email'] = (string)$customer->email;
            $params_to_twenga['basket_id'] = (string)$params['order']->id_cart;
	        try {
	            if(self::$obj_twenga->orderExist($params_to_twenga))
	            {
                    $bool = self::$obj_twenga->orderCancel($params_to_twenga);
                    self::$obj_ps_stats->cancelOrder();
	            }
            } catch (Exception $e) {
                die($e->getMessage());
            }
	    }
	}
	public function hookUpdateOrderStatus($params)
	{
	    if( (int)$params['newOrderStatus']->unremovable === 1
        && (int)$params['newOrderStatus']->logable === 1
        && (int)$params['newOrderStatus']->delivery === 0)
        {
            $obj_order = new Order($params['id_order']);
            $customer = new Customer($obj_order->id_customer);
            $params_to_twenga = array();
            // @todo delete or not ??
//            $params_to_twenga['order_id'] = (int)$params['id_order'];
//            $params_to_twenga['user_id'] = (int)$customer->id;
//            $params_to_twenga['cli_email'] = (string)$customer->email;
            $params_to_twenga['basket_id'] = (int)$obj_order->id_cart;
            $bool = false;
            try {
                if(self::$obj_twenga->orderExist($params_to_twenga))
                {
                    $cart = new Cart($params_to_twenga['basket_id']);
                    $bool = self::$obj_twenga->orderValidate($params_to_twenga);
                    self::$obj_ps_stats->validateOrder($obj_order->total_products_wt, $obj_order->total_paid);
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
	}
	public function hookPayment($params)
	{
	    $customer = new Customer($params['cart']->id_customer);
	    $currency = new Currency($params['cart']->id_currency);
	    $address = $customer->getAddresses($params['cart']->id_lang);
	    $address = $address[0];
	    $tva = $params['cart']->getOrderTotal(true, 3)-$params['cart']->getOrderTotal(false, 3);
	    $tax = ($tva * 100) / $params['cart']->getOrderTotal(true, 3);
	    
	    $params_to_twenga = array();
	    // @todo delete or not ??
//	    $params_to_twenga['user_id'] = $customer->id;
//	    $params_to_twenga['cli_email'] = $customer->email;
	    $params_to_twenga['total_ht'] = $params['cart']->getOrderTotal(false, 3);
	    $params_to_twenga['basket_id'] = $params['cart']->id;
	    $params_to_twenga['currency'] = $currency->iso_code;
	    $params_to_twenga['total_ttc'] = $params['cart']->getOrderTotal(true, 3);
	    $params_to_twenga['shipping'] = $params['cart']->getOrderTotal(true, 5);
	    $params_to_twenga['tax'] = Tools::ps_round($tax, 2);
	    $params_to_twenga['tva'] = $tva;
	    $params_to_twenga['cli_firstname'] = $customer->firstname;
	    $params_to_twenga['cli_lastname'] = $customer->lastname;
	    $params_to_twenga['cli_city'] = $address['city'];
	    $params_to_twenga['cli_state'] = $address['state'];
	    $params_to_twenga['cli_country'] = $address['country'];
	    $params_to_twenga['items'] = array();
	    foreach ($params['cart']->getProducts() as $product)
	    {
	        $arr_item = array();
            if($product['total']!= '') $arr_item['total_ht'] = (float)$product['total'];
            if($product['cart_quantity'] != '') $arr_item['quantity'] = (int)$product['cart_quantity'];
            if($product['reference'] != '') $arr_item['sku'] = (string)$product['reference'];
            if($product['name'] != '') $arr_item['name'] = (string)$product['name'];
            if($product['category']) $arr_item['category_name'] = (string)$product['category'];
            $params_to_twenga['items'][] = $arr_item;
	    }
	    $params_to_twenga = array_filter($params_to_twenga);
	    
	    try {
	        // twenga don't saved double orders with the same id, 
	        // so don't need to use TwengaObj::orderExist() method.
            $tracking_code = self::$obj_twenga->getTrackingScript($params_to_twenga);
            return $tracking_code;
        } catch (TwengaFieldsException $e) {
            return Tools::displayError('Error occurred when params passed in Twenga API').' : <br />'.$e->getMessage();
        } catch (Exception $e) {
            return $e->getMessage();
        }
	}
	
    public function getContent()
	{
	    // API can't be call if curl extension is not installed on PHP config.
	    if (!extension_loaded('curl'))
	    {
            $this->_errors[] = $this->l('Please activate the PHP extension \'curl\' to allow use of Twenga webservice library');
            return $this->displayErrors();
	    }
	    $this->preProcess();
	    
	    $this->_html .= $this->displayTwengaLogin();
	    
        if((self::$obj_twenga->getHashKey() === NULL || self::$obj_twenga->getHashKey() === '')
        || (self::$obj_twenga->getUserName() === NULL || self::$obj_twenga->getUserName() === '')
        || (self::$obj_twenga->getPassword() === NULL || self::$obj_twenga->getPassword() === '')
        )
        {
            if($this->inscription_url === NULL)
                $this->_html .= $this->displaySubscription();
            else
                $this->_html .= $this->displayLinkSubscription();
        }
        else
        {
            $this->_html .= $this->displayActivate();
        }
        return $this->displayErrors().$this->_html;
	}
	/**
	 * @return string html form for log to Twenga API.
	 */
	private function displayTwengaLogin()
	{
	    return '
	    <h2>'.$this->displayName.'</h2>
	    <form name="form_set_hashkey" action="" method="post">	
	    	<fieldset>
    			<legend><img src="../modules/'.$this->name.'/logo.gif" class="middle" /> '.$this->l('Twenga').'</legend>'
    			.((self::$obj_twenga->getHashKey() === NULL || self::$obj_twenga->getHashKey() === '') ?
    				'<p>'.$this->l('After you subscribe to the Twenga API, Twenga sent you an hashkey, a login and a password to use the API.').'</p>' :
    				'<label>'.$this->l('Feed url').' : </label><div class="margin-form">'.$this->feed_url.'</div><!-- .margin-form -->')
    			.'<label>'.$this->l('HashKey').' <sup>*</sup> : </label>
    			<div class="margin-form">
    				<input type="text" size="38" maxlength="32" name="twenga_hashkey" value="'.self::$obj_twenga->getHashKey().'"/>&nbsp;
    			</div><!-- .margin-form -->
    			<label>'.$this->l('Login').' <sup>*</sup> : </label>
    			<div class="margin-form">
    				<input type="text" size="38" maxlength="32" name="twenga_user_name" value="'.self::$obj_twenga->getUserName().'"/>&nbsp;
    			</div><!-- .margin-form -->
    			<label>'.$this->l('Password').' <sup>*</sup> : </label>
    			<div class="margin-form">
    				<input type="password" size="38" maxlength="32" name="twenga_password" value="'.self::$obj_twenga->getPassword().'"/>&nbsp;
    			</div><!-- .margin-form -->
    			<input type="submit" value="'.$this->l('save').'" name="submitTwengaLogin" class="button"/> 
			</fieldset>
			</form><br />';
	}
	
	/**
	 * Subscription form need to be pre-filled.
	 * @return string the html form for subscription
	 */
    private function displaySubscription()
	{
	    $country = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        $site_name = Configuration::get('PS_SHOP_NAME');
        $employee = new Employee(1);
        $email = Configuration::get('PS_SHOP_EMAIL');
        $phone = (Configuration::get('PS_SHOP_PHONE') !== FALSE) ? Configuration::get('PS_SHOP_PHONE') : '';
        $legaltype = (Configuration::get('PS_SHOP_DETAILS') !== FALSE) ? Configuration::get('PS_SHOP_DETAILS') : '';
        $address = ((Configuration::get('PS_SHOP_ADDR1') !== FALSE) ? Configuration::get('PS_SHOP_ADDR1')."\n" : '').((Configuration::get('PS_SHOP_ADDR2') !== FALSE) ? Configuration::get('PS_SHOP_ADDR2') : '');
        $postal_code = (Configuration::get('PS_SHOP_CODE') !== FALSE) ? Configuration::get('PS_SHOP_CODE') : '';
        $city = (Configuration::get('PS_SHOP_CITY') !== FALSE) ? Configuration::get('PS_SHOP_CITY') : '';
	 	return '
	 	<form name="form_subscription_twenga" method="post" action="">
		<fieldset>
			<legend><img src="../modules/'.$this->name.'/logo.gif" class="middle" /> '.$this->l('Subscription').'</legend>
			<p>'.$this->l('To prepare your Twenga API subscription,').'<br />'
			.$this->l('please check the formular below and click "save" to access of Twenga subscription formular').'</p><br />
			<label>'.$this->l('Site Url').' <sup>*</sup> : </label>
			<div class="margin-form">
				<input type="text" name="site_url" value="'.$this->site_url.'">
				<p>'.$this->l('Site\'s URL').'</p>
			</div><!-- .margin-form -->
			<label>'.$this->l('Feed Url').' <sup>*</sup> : </label>
			<div class="margin-form">
				<input type="text" name="feed_url" value="'.$this->feed_url.'">
				<p>'
			        .$this->l('Feeds Url, the list of product in xml.').'<br />'
			        .$this->l('Value is automatically filled, don\'t change it.')
			    .'</p>
			</div><!-- .margin-form -->
			<label>'.$this->l('Country').' <sup>*</sup> : </label>
			<div class="margin-form">
				<input type="text" name="country" value="'.$country.'">
				<p>'
				.$this->l('E-Merchant’s society country. Use the ISO_3166-1 Alpha-2 country code format.')
                .'<br />
                Ex : France => FR, Germany => DE<br />
                <a href="http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes" target="_blank" >> related link</a><br />
                </p>
			</div><!-- .margin-form -->
			<label>'.$this->l('Site Name').' : </label>
			<div class="margin-form">
				<input type="text" name="site_name" value="'.$site_name.'">
				<p>'.$this->l('Site\'s name').'</p>
			</div><!-- .margin-form -->
			<label>'.$this->l('Lastname').' : </label>
			<div class="margin-form">
				<input type="text" name="lastname" value="'.$employee->lastname.'">
			</div><!-- .margin-form -->
			<label>'.$this->l('Firstname').' : </label>
			<div class="margin-form">
				<input type="text" name="firstname" value="'.$employee->firstname.'">
			</div><!-- .margin-form -->
			<label>'.$this->l('Email').' : </label>
			<div class="margin-form">
				<input type="text" name="email" value="'.$email.'">
			</div><!-- .margin-form -->
			<label>'.$this->l('Phone').' : </label>
			<div class="margin-form">
				<input type="text" name="phone" value="'.$phone.'">
			</div><!-- .margin-form -->
			<label>'.$this->l('Organisation name ').' : </label>
			<div class="margin-form">
				<input type="text" name="site_name" value="'.$site_name.'">
			</div><!-- .margin-form -->
			<label>'.$this->l('Legal type').' : </label>
			<div class="margin-form">
				<input type="text" name="legaltype" value="'.$legaltype.'">
				<p>'.$this->l('society legal type.').'</p>
			</div><!-- .margin-form -->
			<label>'.$this->l('Address').' : </label>
			<div class="margin-form">
				<textarea name="address" >'.$address.'</textarea>
			</div><!-- .margin-form -->
			<label>'.$this->l('Postal code').' : </label>
			<div class="margin-form">
				<input type="text" name="postal_code" value="'.$postal_code.'">
			</div><!-- .margin-form -->
			<label>'.$this->l('City').' : </label>
			<div class="margin-form">
				<input type="text" name="city" value="'.$city.'">
			</div><!-- .margin-form -->
			<div style="text-align:center;margin:20px 0 0 0;" >
				<input type="submit" name="submitTwengaSubscription" class="button" value="'.$this->l('Save').'" />
			</div>
		</fieldset>
		</form>';
	}
	
	/**
	 * @return string html block contains the link to access of the Twenga API subscription.
	 * 		   In case of Javascript is disabled by user.
	 */
	private function displayLinkSubscription()
	{
	    return '
	    <fieldset>
			<legend><img src="../modules/'.$this->name.'/logo.gif" class="middle" /> '.$this->l('Subscription').'</legend>'
			.$this->l('If you were not redirected to the Twenga API subscription, please click on the following link :')
	    .'<br /><a href="'.$this->inscription_url.'" target="_blank" class="link">'.$this->l('Link to Twenga Subscription').'</a>
	    </fieldset>';
	}
	
	/**
	 * @return string html form for activate or disable the Twenga tracking
	 */
	private function displayActivate()
	{
	    $str = '
	    <form name="form_twenga_activate" method="post" action="">
    	    <fieldset>
    			<legend><img src="../modules/'.$this->name.'/logo.gif" class="middle" />%s</legend>
    			%s <br />
    	    	<div style="text-align:center;margin:20px 0 0 0;" >
    				<input type="submit" name="%s" class="button" value="%s" />
    			</div>
    	    </fieldset>
    	</form>';
	    
	    if($this->isRegisteredInHook('payment')
        && $this->isRegisteredInHook('updateOrderStatus')
        && $this->isRegisteredInHook('cancelProduct'))
	        $str = sprintf($str, $this->l('Disable Tracking'), $this->l('To disable tracking, click on the following button :'), 'submitTwengaDisableTracking', $this->l('Disable'));
	    else
            $str = sprintf($str, $this->l('Activate Tracking'), $this->l('To activate tracking, click on the following button :'), 'submitTwengaActivateTracking', $this->l('Activate'));
	    return $str;
	}
	
	/**
	 * Just set in one method the displaying error message in Prestashop back-office.
	 */
    private function displayErrors()
	{
	    $string = '';
	    if(!empty($this->_errors))
	    {
	        foreach ($this->_errors as $error)
	        {
	            $string .= $this->displayError($error);
	        }
	    }
	    return $string;
	}
	
	/**
	 * Used by export.php to build the feed required by Twenga.
	 * See detailed comments in the body of the method
	 * @see Twenga::preparedValues() to see how needed tags for feed are filled
	 */
	public function buildXML()
	{
		if(self::$obj_twenga->getHashkey() === NULL && !self::$obj_twenga->siteExist())
			return '';
		$xmlstr = '<?xml version="1.0" encoding="utf-8"?><catalog></catalog>';
		$xml = new SimpleXMLElement($xmlstr);
		
		$parameters = Configuration::getMultiple(array('PS_REWRITING_SETTINGS', 'PS_LANG_DEFAULT', 'PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT', 'PS_COUNTRY_DEFAULT'));
		$lang = (int)$parameters['PS_LANG_DEFAULT'];
		$language = new Language($lang);
		$carrier = new Carrier(Configuration::get('PS_CARRIER_DEFAULT'), $language->id);
		$defaultCountry = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), $language->id);

		$link = new Link();
		
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_product` FROM `'._DB_PREFIX_.'product` WHERE `active` = 1');

		foreach ($result AS $k => $row)
		{
			$product = new Product((int)$row['id_product']);
			
			if (Validate::isLoadedObject($product) AND $product->active)
			{
			    // Check if product declinations exist.
			    $combinations = $this->getCombinations($product, $lang);
			    
			    // Set an empty value even no combinations was found to make foreach usable.
        		if(empty($combinations))
        		    $combinations[] = array();
        		
    			foreach ($combinations as $combination)
    			{
    			    // prepared values before insert it in node structure.
    			    // In this way we can structure code with checking method and displaying method for more lisibility.
    			    $product_values = $this->preparedValues ($product, $combination, $lang, $link, $carrier);
    			    
    			    // create the product node for each products and declinations
    			    $product_node = $xml->addChild('product', '');
    			    
    			    // required Fields
    			    $product_node->addChild('product_url', $product_values['product_url']);
    			    $product_node->addChild('designation', $product_values['designation']);
    			    $product_node->addChild('price', $product_values['price']);
    			    $product_node->addChild('category', $product_values['category']);
    			    $product_node->addChild('image_url', $product_values['image_url']);
    			    $product_node->addChild('description', $product_values['description']);
    			    $product_node->addChild('brand', $product_values['brand']);
    			    
    			    // optionnals fields
    			    $product_node->addChild('merchant_id', $product_values['merchant_id']);
    			    $product_node->addChild('manufacturer_id', $product_values['manufacturer_id']);
			        $product_node->addChild('shipping_cost', $product_values['shipping_cost']);
    			    $product_node->addChild('in_stock', $product_values['in_stock']);
    			    $product_node->addChild('stock_detail', $product_values['stock_detail']);
    			    $product_node->addChild('condition', $product_values['condition']);
    			    $product_node->addChild('upc_ean', $product_values['upc_ean']);
    			    $product_node->addChild('product_type', $product_values['product_type']);
    			    $product_node->addChild('isbn', $product_values['isbn']);
    			    $product_node->addChild('eco_tax', $product_values['eco_tax']);
    			}
			}
		}
		return $xml->asXML();
	}
	/**
	 * @param Product $product to get the product properties
	 * @param array $combination to get particular properties from a declination
	 * @param int $lang id lang to take all text in good language
	 * @param Link $link to set the link of the product and its images.
	 * @param Carrier $carrier not used now, but usable for next version, needed for calculate the shipping cost,
	 * 		  But for now it's not sure enough.
	 * @return array with good value for the XML.
	 */
	private function preparedValues (Product $product, array $combination, $lang, Link $link, Carrier $carrier)
	{
	    $arr_return = array();
	    $str_features = array();
	    $model = array();
	    $version = str_replace('.', '', _PS_VERSION_);
	    
	    // To build description and model tags.
	    if(isset($combination['attributes']))
	    {
		    foreach ($combination['attributes'] as $attribut)
		    {
		        $str_features[] = $attribut['group_name'].' : '.$attribut['name'];
		        $model[] = $attribut['name'];
		    }
	    }
	    if(isset($combination['weight']) && (int)$combination['weight'] !== 0)
	        $str_features[] = 'weight : '.$combination['weight'];
	    else if ($product->weight !== 0)
	        $str_features[] = 'weight : '.$product->weight;
	    
	    $features = $product->getFrontFeatures($lang);
	    foreach ($features as $feature)
	        $str_features[] = $feature['name'].' : '.$feature['value'];
	    
	    // Category tag
	    $category = new Category((int)$product->id_category_default, $lang);
	    $category_path = ((isset($category->id) AND $category->id) ? Tools::getFullPath((int)($category->id), $product->name[$lang]) : Tools::getFullPath((int)($product->id_category_default), $product->name[$lang]));
	    $category_path = (Configuration::get('PS_NAVIGATION_PIPE') != false && Configuration::get('PS_NAVIGATION_PIPE') !== '>' ) ? str_replace(Configuration::get('PS_NAVIGATION_PIPE'), '>', $category_path) : $category_path;
	    
	    // image tag
	    $id_image = (isset($combination['id_image'])) ? $combination['id_image'] : 0;
	    if($id_image === 0)
	    {
	        $image = $product->getCover((int)$product->id);
	        $id_image = $image['id_image'];
	    }
	    
	    $quantity = Product::getQuantity($product->id, (isset($combination['id_combination']) ? $combination['id_combination'] : NULL));
	    
	    $condition = '';
	     if(strlen((string)$version) < 2)
	         $version = (string)$version.'0';
	    if((int)substr($version, 0, 2) >= 14)
	        $condition = (($product->condition === 'new') ? 0 : 1);
	    
	    $price = $product->getPrice(true, (isset($combination['id_combination']) ? $combination['id_combination'] : NULL), 2);
	    $upc_ean = strlen((string)$product->ean13) == 13 ? $product->ean13 : '';
	    
	    $arr_return['product_url'] = $link->getProductLink((int)$product->id, $product->link_rewrite[$lang], $product->ean13, $lang);
	    $arr_return['designation'] = $product->name[$lang].' '.Manufacturer::getNameById($product->id_manufacturer).' '.implode(' ', $model);
	    $arr_return['price'] = $price;
	    $arr_return['category'] = htmlspecialchars(strip_tags($category_path), ENT_QUOTES, 'utf-8');
	    $arr_return['image_url'] = $link->getImageLink($product->link_rewrite[$lang], $product->id.'-'.$id_image, 'large');
	    $arr_return['description'] = strip_tags(implode(', ', $str_features));
	    $arr_return['brand'] = Manufacturer::getNameById($product->id_manufacturer);
	    $arr_return['merchant_id'] = $product->id;
	    $arr_return['manufacturer_id'] = $product->id_manufacturer;
        $arr_return['shipping_cost'] = 'NC';
	    $arr_return['in_stock'] = $quantity > 0 ? 'Y' : 'N';
	    $arr_return['stock_detail'] = $quantity;
	    $arr_return['condition'] = $condition;
	    $arr_return['upc_ean'] = $upc_ean;
	    $arr_return['eco_tax'] = $product->ecotax;
	    
	    // for prestashop 1.4 and previous version these fields are not managed.
	    // So default values are set.
	    $arr_return['product_type'] = '1';
	    $arr_return['isbn'] = '';
	    
	    return $arr_return;
	}
	/**
	 * @param Product $product
	 * @param int $lang id of a language
	 * @return array of a product declinations. 
	 */
	private function getCombinations(Product $product, $lang)
	{
	    $attributesGroups = $product->getAttributesGroups((int)$lang);
	    $combinations = array();
	    if (Db::getInstance()->numRows())
		{
			$combinationImages = $product->getCombinationImages((int)$lang);
			foreach ($attributesGroups AS $k => $row)
			{
			    $combinations[$row['id_product_attribute']]['id_combination'] = $row['id_product_attribute']; 
				$combinations[$row['id_product_attribute']]['attributes'][$row['id_attribute_group']] = array('name'=>$row['attribute_name'], 'group_name'=>$row['public_group_name'], 'id_attribute'=>(int)$row['id_attribute']);
				$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
				$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
				$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
				$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
				$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
				$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
				$combinations[$row['id_product_attribute']]['id_image'] = isset($combinationImages[$row['id_product_attribute']][0]['id_image']) ? $combinationImages[$row['id_product_attribute']][0]['id_image'] : -1;
			}
		}
		return $combinations;
	}
}

