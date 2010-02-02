<?php


class GAdsense extends Module
{	
	function __construct()
	{
	 	$this->name = 'gadsense';
	 	$this->tab = 'Advertisement';
	 	$this->version = '1.1';
        $this->displayName = $this->l('Google Adsense');
		
	 	parent::__construct();
		
		if (!Configuration::get('GADSENSE_ID'))
			$this->warning = $this->l('You have not yet set your Google Adsense code');
        $this->description = $this->l('Integrate the Google Adsense script into your shop');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}
	
    function install()
    {
        if (!parent::install() OR !$this->registerHook('home'))
			return false;
		return true;
    }
	
	function uninstall()
	{
		if (!Configuration::deleteByName('GADSENSE_ID') OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitGAdsense') AND ($gai = Tools::getValue('gadsense_id')))
		{
			$gai = htmlentities($gai, ENT_COMPAT, 'UTF-8');
			Configuration::updateValue('GADSENSE_ID', $gai);
			$output .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" />
				'.$this->l('Settings updated').'
			</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend>'.$this->l('Settings').'</legend>
				<label>'.$this->l('Your code').'</label>
				<div class="margin-form">
					<textarea name="gadsense_id" cols="90" rows="10" />'.Tools::getValue('gadsense_id', Configuration::get('GADSENSE_ID')).'</textarea>
					<p class="clear">'.$this->l('Example:').' <br /><br /><img src="../modules/gadsense/adsense_script.gif"></p>
				</div>
				<center><input type="submit" name="submitGAdsense" value="'.$this->l('Update settings').'" class="button" /></center>			
			</fieldset>
		</form>';
		return $output;
	}

	function hookLeftColumn($params)
	{
		return $this->hookHome($params);
	}

	function hookRightColumn($params)
	{
		return $this->hookHome($params);
	}

	function hookTop($params)
	{
		return $this->hookHome($params);
	}

	function hookHome($params)
	{
		$output = html_entity_decode(Configuration::get('GADSENSE_ID'), ENT_COMPAT, 'UTF-8');
		return $output;
	}

}
?>