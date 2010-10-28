<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class canonicalUrl extends Module
{
	public function __construct()
	{
		$this->name = 'canonicalurl';
		$this->tab = 'seo';
		$this->version = 1.3;

		parent::__construct();

		$this->displayName = $this->l('Canonical URL');
		$this->description = $this->l('Improve SEO by avoiding the "duplicate content" status for your Website.');
		
		if ($this->id AND strlen(Configuration::get('CANONICAL_URL')) == 0)
			$this->warning = $this->l('You must set the canonical URL to avoid "duplicate content" status for your Website.');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('header') OR !Configuration::updateValue('CANONICAL_URL', ''))
			return false;
		return true;
	}

	public function uninstall()
	{
		/* Delete configuration */
		Configuration::deleteByName('CANONICAL_URL');
		return parent::uninstall();
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

	public function hookHeader($params)
	{
		global $smarty, $protocol_link;
		
		$canonicalUrl = Configuration::get('CANONICAL_URL');
		$ps_request = str_replace(__PS_BASE_URI__, '', $_SERVER['REQUEST_URI']);
		if (strlen(Configuration::get('CANONICAL_URL')) > 0)
			$smarty->assign('canonical_url', $protocol_link.$canonicalUrl.Tools::htmlentitiesUTF8(rawurldecode($_SERVER['REQUEST_URI'])));
		return $this->display(__FILE__, 'canonicalurl.tpl');
	}
}

?>
