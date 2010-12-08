<?php
/*
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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/
if (!in_array('PrepaidServices', get_declared_classes())) include_once(_PS_MODULE_DIR_.'/paysafecard/PrepaidServices.php');


class PaysafeCard extends PrepaidServices
{
	public $prefix = 'PS_PSC_';
	protected $supported_languages = array('de', 'en', 'gr', 'el', 'es', 'it', 'fr', 'nl', 'pl', 'pt', 'si', 'sk', 'tr');
	protected $allowed_currencies = array();
	
	protected $environments = array('P' => 'Production',
								 'T' => 'Test');
	
	protected $business_types = array('I' => 'Intangible',
									'T' => 'Tangible');

	protected $payment_url = array('T' =>'https://customer.test.at.paysafecard.com/psccustomer/GetCustomerPanelServlet',
								   'P' => 'https://customer.cc.at.paysafecard.com/psccustomer/GetCustomerPanelServlet');

	protected $supported_currencies = array('EUR', 'GBP', 'CHF', 'USD', 'PLN', 'CZK', 'SEK', 'DKK', 'RON', 'NOK', 'ARS');
	
	protected $certificat_dir;

	public function __construct()
	{
		$this->name = 'paysafecard';
		$this->tab = 'payments_gateways';
		$this->version = 1.0;
		$this->module_dir = dirname(__FILE__);
        $this->certificat_dir = dirname(__FILE__).'/keyring/';

		parent::__construct();

		$this->displayName = $this->l('PaysafeCard');
		$this->description = $this->l('Accepts payments by PaysafeCard');
	}
	
	public function getL($key)
	{
		$translations = array(
				'disposition_created' => $this->l('Disposition created. Waiting for debit.'),
				'disposition_invalid' => $this->l('Invalid disposition state:'),
				'payment_error' => $this->l('An error has occurred during payment:'),
				'payment_accepted' => $this->l('Payment accepted.'),
				'curl_required' => $this->l('This module requires the curl PHP extension to function properly.'),
				'not_writable' => $this->l('is not writable!'),
				'currency_required' => $this->l('This module requires the currency: '),
				'configure_currency' => $this->l('Configure each currency individually:'),
				'payment_not_displayed' => $this->l('(The payment module won\'t be displayed for customers using non configurated currency.)'),
				'configuration_in' => $this->l('Configuration in '),
				'merchant_id' => $this->l('Merchant ID'),
				'keyring_certificate' => $this->l('Keyring Certificate'),
				'keyring_pw' => $this->l('Keyring PW'),
				'configuration' => $this->l('Configuration'),
				'environment' => $this->l('Environment'),
				'business_type' => $this->l('Business Type'),
				'immediat_payment' => $this->l('Immediat Payment'),
				'update_configuration' => $this->l('Update configuration'),
				'certificate_required' => $this->l('You must provide a certificat for MERCHANT ID'),
				'invalid_file' => $this->l('Invalid file'),
				'invalid_merchant_id' => $this->l('Invalid Merchant ID'),
				'invalid_business_type' => $this->displayError('Invalid business type'),
				'invalid_environment' => $this->displayError('Invalid environment'),
				'settings_updated' => $this->l('Settings updated'),
				'file_partialy_uploaded' => $this->l('The file was partially uploaded'),
				'file_empty' => $this->l('The file is empty'),
				'cant_create_dispo' => $this->l('Transaction could not be initiated due to connection problems. If the problem persists, please contact our support.'),
				'disposition_consumed' => $this->l('Disposition consumed'),
				'payment_released' => $this->l('Disposition released'),
				'release_error' => $this->l('An error has occurred during the release'),
				'introduction' => $this->l('paysafecard is Europe’s first prepaid solution for payments on the Internet to comply with banking laws.  Over the past years, paysafecard has become one of Europe’s leading alternative online payment solutions.')
			);
			
		return $translations[$key];
	}
	
	protected function _getErrorMsgFromErrorCode($error_code)
	{
		$error_msg = array(1 => $this->l('An error has occurred, check Messages for more info'),
						   2 => $this->l('Invalid amount'));
						   
		return $error_msg[$error_code];
	}
}


