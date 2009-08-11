<?php

/**
  * Database tab for admin panel, AdminDb.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/tabs/AdminPreferences.php');

class AdminContact extends AdminPreferences
{
	public function __construct()
	{
		$this->className = 'Configuration';
		$this->table = 'configuration';
 	
 		$this->_fieldsShop = array(
			'PS_SHOP_NAME' => array('title' => $this->l('Shop name:'), 'desc' => $this->l('Displayed in e-mails and page titles'), 'validation' => 'isGenericName', 'required' => true, 'size' => 30, 'type' => 'text'),
			'PS_SHOP_EMAIL' => array('title' => $this->l('Shop e-mail:'), 'desc' => $this->l('Displayed in e-mails sent to customers'), 'validation' => 'isEmail', 'required' => true, 'size' => 30, 'type' => 'text'),
			'PS_SHOP_DETAILS' => array('title' => $this->l('Registration:'), 'desc' => $this->l('Shop registration information (e.g., SIRET or RCS)'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'textarea', 'cols' => 30, 'rows' => 5),
			'PS_SHOP_ADDR1' => array('title' => $this->l('Shop address:'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
			'PS_SHOP_ADDR2' => array('title' => '', 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
			'PS_SHOP_CODE' => array('title' => $this->l('Post/Zip code:'), 'validation' => 'isGenericName', 'size' => 6, 'type' => 'text'),
			'PS_SHOP_CITY' => array('title' => $this->l('City:'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
			'PS_SHOP_STATE' => array('title' => $this->l('State (if applicable):'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
			'PS_SHOP_COUNTRY' => array('title' => $this->l('Country:'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
			'PS_SHOP_PHONE' => array('title' => $this->l('Phone:'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
			'PS_SHOP_FAX' => array('title' => $this->l('Fax:'), 'validation' => 'isGenericName', 'size' => 30, 'type' => 'text'),
		);
		parent::__construct();
	}

	public function display()
	{
		$this->_displayForm('shop', $this->_fieldsShop, $this->l('Contact details'), 'width2', 'tab-contact');
	}
}

?>
