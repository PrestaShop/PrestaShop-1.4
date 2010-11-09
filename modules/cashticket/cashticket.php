<?php
if (!in_array('PrepaidServices', get_declared_classes())) include_once(_PS_MODULE_DIR_.'/cashticket/PrepaidServices.php');

class CashTicket extends PrepaidServices
{
	public $prefix = 'PS_CT_';
	protected $supported_languages = array('de', 'en', 'gr', 'el', 'es', 'it', 'fr', 'nl', 'pl', 'pt', 'si', 'sk', 'tr');
	protected $allowed_currencies = array();
	
	protected $environments = array('P' => 'Production',
								 'T' => 'Test');
	
	protected $business_types = array('I' => 'Intangible',
									'T' => 'Tangible');

	protected $payment_url = array('T' => 'https://customer.test.at.cash-ticket.com/ctcustomer/GetCustomerPanelServlet',
								 'P' => 'https://customer.cc.at.cash-ticket.com/ctcustomer/GetCustomerPanelServlet');

	protected $supported_currencies = array('CHF', 'DKK', 'SEK', 'PLN', 'GBP', 'EUR', 'USD', 'CZK');
	
	protected $certificat_dir;
	
	public function __construct()
	{
		$this->name = 'cashticket';
		$this->tab = 'payments_gateways';
		$this->version = 1.0;
		$this->module_dir = dirname(__FILE__);
        $this->certificat_dir = dirname(__FILE__).'/keyring/';

		parent::__construct();

		$this->displayName = $this->l('CashTicket');
		$this->description = $this->l('Accepts payments by CashTicket');
	}
	
	
	public function getL($key)
	{
		$translations = array(
				'disposition_created' => $this->l('Disposition created. Waiting for debit.'),
				'disposition_invalid' => $this->l('Invalid disposition state:'),
				'payment_error' => $this->l('An error has occured during payment:'),
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
				'file_partialy_uploaded' => $this->l('The file was partialy uploaded'),
				'file_empty' => $this->l('The file is empty'),
				'cant_create_dispo' => $this->l('Transaction could not be initiated due to connection problems. If the problem persists, please contact our support.'),	
				'disposition_consumed' => $this->l('Disposition consumed'),
				'payment_released' => $this->l('Disposition released'),
				'release_error' => $this->l('An error has occured during the release'),
				'introduction' => $this->l('Accept prepaid payments in your webshop. All that is required to pay at a web-shop is the Cash-Ticket’s 16 digit pin code. This way, consumers can make online payments without a credit card or a bank account.')				
			);
			
		return $translations[$key];
	}
	
	protected function _getErrorMsgFromErrorCode($error_code)
	{
		$error_msg = array(1 => $this->l('An error has occured, check Messages for more infos'),
						   2 => $this->l('Invalid amount'));
						   
		return $error_msg[$error_code];
	}
}

?>
