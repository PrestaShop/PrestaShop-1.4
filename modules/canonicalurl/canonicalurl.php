<?php

class canonicalUrl extends Module
{
	function __construct()
	{
		$this->name = 'canonicalurl';
		$this->tab = 'Tools';
		$this->version = 1.3;

		parent::__construct();

		$this->displayName = $this->l('Canonical URL');
		$this->description = $this->l('Improve SEO by avoiding the "duplicate content" status for your Website.');
		
		if (strlen(Configuration::get('CANONICAL_URL')) == 0)
			$this->warning = $this->l('You must set the canonical URL to avoid "duplicate content" status for your Website.');
	}

	function install()
	{
		if (!parent::install() OR !$this->registerHook('header') OR !Configuration::updateValue('CANONICAL_URL', ''))
			return false;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitCanonicalUrl'))
		{
			$canonicalUrl = pSQL(Tools::getValue('canonicalUrl'));
			if (strlen($canonicalUrl) == 0)
				$output .= '<div class="alert error">'.$this->l('Canonical URL : invalid URL.').'</div>';
			else
			{
				Configuration::updateValue('CANONICAL_URL', $canonicalUrl);
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Canonical URL').'</label>
				<div class="margin-form">
					http(s)://<input type="text" name="canonicalUrl" value="'.Configuration::get('CANONICAL_URL').'" />/some/directories/a_prestashop_webpage.php
					<p class="clear">'.$this->l('Choose the primary domain name for your referencing (e.g., www.myshop.com or myshop.com or mywebsite.com). Note: Do not include the last slash ("/"), the "/index.php" suffix, or the "http(s)://" prefix.').'</p>
				</div>
				<center><input type="submit" name="submitCanonicalUrl" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}

	function hookHeader($params)
	{
		global $smarty, $protocol, $rewrited_url;
		
		$canonicalUrl = Configuration::get('CANONICAL_URL');
		$ps_request = str_replace(__PS_BASE_URI__, '', $_SERVER['REQUEST_URI']);
		
		if (strlen(Configuration::get('CANONICAL_URL')) > 0)
			if (isset($rewrited_url))
				$smarty->assign('canonical_url', $protocol.$canonicalUrl.$rewrited_url);
			else
				$smarty->assign('canonical_url', $protocol.$canonicalUrl.$_SERVER['REQUEST_URI']);
		return $this->display(__FILE__, 'canonicalurl.tpl');
	}
}

?>
