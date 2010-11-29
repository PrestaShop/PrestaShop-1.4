<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockPaymentLogo extends Module
{
	public function __construct()
	{
		$this->name = 'blockpaymentlogo';
		$this->tab = 'front_office_features';
		$this->version = 0.2;

		parent::__construct();
		
		$this->displayName = $this->l('Block payment logo');
		$this->description = $this->l('Adds a block to display all payment logo');
	}

	public function install()
	{
		Configuration::updateValue('PS_PAYMENT_LOGO_CMS_ID', 0);
		if (!parent::install())
			return false;
		if (!$this->registerHook('leftColumn'))
			return false;
		if (!$this->registerHook('header'))
			return false;
		return true;
	}
	
	public function uninstall()
	{
		Configuration::deleteByName('PS_PAYMENT_LOGO_CMS_ID');
		return parent::uninstall();
	}

	public function getContent()
	{
		global $cookie;

		$html = '
		<h2>'.$this->l('Payment logo').'</h2>
		';
		
		if (Tools::isSubmit('submitConfiguration'))
			if (Validate::isUnsignedInt(Tools::getValue('id_cms')))
			{
				Configuration::updateValue('PS_PAYMENT_LOGO_CMS_ID', (int)(Tools::getValue('id_cms')));
				$html .= $this->displayConfirmation($this->l('Settings are updated'));
			}
		
		$cmss = CMS::listCms((int)($cookie->id_lang));
		
		if (!sizeof($cmss))
			$html .= $this->displayError($this->l('No CMS page is available'));
		else
		{
			$html .= '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="'.$this->_path.'/logo.gif" alt="" /> '.$this->l('Configure').'</legend>
					<label>'.$this->l('Page CMS for link').':</label>
					<div class="margin-form">
						<select name="id_cms">';
			foreach ($cmss as $cms)
				$html .= '<option value="'.$cms['id_cms'].'"'.(Configuration::get('PS_PAYMENT_LOGO_CMS_ID') == $cms['id_cms'] ? ' selected="selected"' : '').'>'.$cms['meta_title'].'</option>';
			$html .= '</select>
					</div>
					<p class="center"><input class="button" type="submit" name="submitConfiguration" value="'.$this->l('Save settings').'" /></p>
				</fieldset>
			</form>
			';
		}
		return $html;
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	public function hookLeftColumn($params)
	{
		global $smarty, $cookie;
		
		if (!Configuration::get('PS_PAYMENT_LOGO_CMS_ID'))
			return;
		$cms = new CMS((int)(Configuration::get('PS_PAYMENT_LOGO_CMS_ID')), (int)($cookie->id_lang));
		if (!Validate::isLoadedObject($cms))
			return;
		$smarty->assign('cms_payement_logo', $cms);
		return $this->display(__FILE__, 'blockpaymentlogo.tpl');
	}
	
	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

	public function hookFooter($params)
	{
		return $this->hookLeftColumn($params);
	}
	function hookHeader($params)
	{
		Tools::addCSS(($this->_path).'blockpaymentlogo.css', 'all');
	}

}

?>
