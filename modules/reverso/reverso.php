<?php
class Reverso extends Module
{
	private $_html;
	private $_api_url = 'http://api2.reversoform.com/includes/api.controler.reverso2.php?phone_number={ARG_PHONE}&serial={ARG_SERIAL}&remoteAddress={ARG_ADDRESS}';
	  function __construct()
   {
       $this->name = 'reverso';
       $this->tab = 'Tools';
       $this->version = '1.0';
       parent::__construct();
        /* The parent construct is required for translations */
       $this->displayName = $this->l('ReversoForm');
       $this->description = $this->l('Fill Authentication form with ReversoForm');
   }
	
	public function uninstall()
	{
		return parent::uninstall();
	}
	
	public function install()
	{
		if (!parent::install())
			return false;
		
		if (!$this->registerHook('createAccountTop'))
			return false;
		
		return (Configuration::updateValue('REVERSO_SERIAL', '0123456789123') AND
				Configuration::updateValue('REVERSO_BAD_NUMBER', 1) AND
				Configuration::updateValue('REVERSO_UNKNOWN_NUMBER', 2) AND
				Configuration::updateValue('REVERSO_ADDRESS', str_replace('http://', '', $_SERVER['HTTP_HOST'])));
	}
  
	private function _postProcess()
	{
		return (Configuration::updateValue('REVERSO_SERIAL', pSQL(Tools::getValue('reverso_serial'))) AND
				Configuration::updateValue('REVERSO_ADDRESS', pSQL(Tools::getValue('reverso_address'))));
		
	}
	
	public function hookCreateAccountTop($params)
	{
		global $smarty;
		$tag = '<img src='.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').'api.reversoform.com/includes/'.(Configuration::get('PS_SSL_ENABLED') ? 'www.reversoform.com/' : '').'js/trans.giff?d='.date('U').' with="0" height="0" />';
		$smarty->assign(array('reverso_tag' => $tag));
		return $this->display(__FILE__, 'reverso.tpl');
	}
	
	private function _displayForm()
	{
		$conf = Configuration::getMultiple(array('REVERSO_SERIAL', 
																														'REVERSO_ADDRESS'));

		$this->_html .=
			'<br /><fieldset><legend>'.$this->l('Configuration').'
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<label for="serial">'.$this->l('Serial Number').' :</label>
			<div class="margin-form">
				<input type="text" name="reverso_serial" value="'.$conf['REVERSO_SERIAL'].'" />
			</div>
			<label for="address">'.$this->l('Site Address').': </label>
			<div class="margin-form">
				<input type="text" name="reverso_address" value="'.$conf['REVERSO_ADDRESS'].'" />
			</div>
			<input type="submit" name="submitReverso" value="'.$this->l('Update').'" />
			</form></fieldset>';
	}
    
	public function getContent()
	{
		$this->_html .= '<h2>'.$this->l('Reverso Account configuration').'</h2>
		'.$this->l('Yout don\'t have ReversoForm account yet ?').' <a href="http://www.reversoform.com/api/">'.$this->l('Register now').' !</a>';
		if (!empty($_POST))
			$this->_postProcess();
		else
		$this->_html .= '<br />';		$this->_displayForm();
		return $this->_html;
	}
	
	public function callReverso($phone = false)
	{
		if ($phone === false)
			return false;
		
		$conf = Configuration::getMultiple(array('REVERSO_SERIAL',
																																			'REVERSO_ADDRESS',
																																			'REVERSO_BAD_NUMBER',
																																			'REVERSO_UNKNOWN_NUMBER'));
																																			
		$phone = str_replace(array(' '), '', $phone);
		if (strlen($phone) < 10)
			return intval($conf['REVERSO_BAD_NUMBER']);
		
		$url_to_call = str_replace(array('{ARG_PHONE}', '{ARG_SERIAL}', '{ARG_ADDRESS}'), array($phone, $conf['REVERSO_SERIAL'], $conf['REVERSO_ADDRESS']), $this->_api_url);
		$reverso = file_get_contents($url_to_call);

		$address = json_decode($reverso, true);
		if ($address == 'NULL')
			return intval($conf['REVERSO_BAD_NUMBER']);
	
		$fields = array('last_name' => 'lastname',
														'first_name' => 'firstname',
														'zip' => 'postcode',
														'city' => 'city',
														'address' => 'address1',
														'company' => 'company'
														);
		$to_presta  = array();
		foreach ($fields AS $k => $field)
			$to_presta[$field] = $address[$k];

		$to_presta  = '';
		foreach ($fields AS $k => $field)
			$to_presta .= ($address[$k] != '' ? $field.':'.$address[$k].',' : '');
			
		$to_presta .= 'customer_firstname:'.$address['first_name'].',customer_lastname:'.$address['last_name'];
		
		return rtrim($to_presta, ',');
	}
}
?>
