<?php

class iAdvize extends Module
{	
	function __construct()
	{
		$this->name = 'iadvize';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->displayName = 'iAdvize';
		
		parent::__construct();
		
		$this->description = $this->l('Offer an interactive live chat to your customers');
	}
	
	function install()
	{
		if (!parent::install() OR !$this->registerHook('footer') OR !$this->registerHook('leftColumn') OR !$this->registerHook('rightColumn'))
			return false;
		return true;
	}
	
	function uninstall()
	{
		if (!Configuration::deleteByName('IADVIZE_SID') OR !Configuration::deleteByName('IADVIZE_BID') OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		global $cookie;

		$lang = new Language(intval($cookie->id_lang));
		if (!in_array($lang->iso_code, array('fr', 'en', 'es')))
			$lang->iso_code = 'en';
		
		$output = '<p style="margin-bottom: 20px;"><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/logo-iadvize.gif" alt="" /></p>';

		if (Tools::isSubmit('submitiAdvize'))
		{
			Configuration::updateValue('IADVIZE_SID', intval(Tools::getValue('iadvize_sid')));
			Configuration::updateValue('IADVIZE_BID', intval(Tools::getValue('iadvize_bid')));
			Configuration::updateValue('IADVIZE_BUTTON', intval(Tools::getValue('iadvize_button')));
			
			$output .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" />
				'.$this->l('Settings updated').'<img src="http://www.prestashop.com/modules/iadvize.png?sid='.urlencode(Tools::getValue('iadvize_sid')).'" style="float:right" />
			</div>';
		}
		
		$output .= '
		<div style="float: left; width: 550px;">
			<p style="margin: 15px 0 30px 0; width: 540px; line-height: 18px; text-align: justify;">
				<b>'.$this->l('iAdvize is an interactive live chat').'</b> '.$this->l('allowing you to respond directly to your visitors and direct them towards the product that meets their needs.').'
				<br />
				<a href="http://www.iadvize.com/offre_prestashop.html" target="_blank">'.$this->l('For more informations, click here').'</a>
			</p>
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset class="width2">
					<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
					<label>'.$this->l('iAdvize merchant ID:').'</label>
					<div class="margin-form">
						<input type="text" name="iadvize_sid" value="'.intval(Tools::getValue('iadvize_sid', Configuration::get('IADVIZE_SID'))).'" /> <sup>*</sup>
					</div>
					<hr size="1" style="margin-bottom: 20px;" noshade />
					<label>'.$this->l('iAdvize "Call button" ID:').'</label>
					<div class="margin-form">
						<input type="text" name="iadvize_bid" value="'.intval(Tools::getValue('iadvize_bid', Configuration::get('IADVIZE_BID'))).'" />
						<p class="clear">'.$this->l('Leave empty if you don\'t want to enable this feature').'</p>
					</div>
					<label style="vertical-align: middle;">'.$this->l('"Call button" location:').'</label>
					<div class="margin-form" style="margin-top: 5px;">
						<input type="radio" name="iadvize_button" value="0" style="vertical-align: middle;" /> '.$this->l('No call button').'&nbsp;
						<input type="radio" name="iadvize_button" value="1" style="vertical-align: middle;" /> '.$this->l('Left column').'&nbsp;
						<input type="radio" name="iadvize_button" value="2" style="vertical-align: middle;" /> '.$this->l('Right column').'
					</div>
					<hr size="1" style="margin-bottom: 20px;" noshade />
					<center><input type="submit" name="submitiAdvize" value="'.$this->l('Update settings').'" class="button" /></center>
				</fieldset>
				<p style="font-size: 10px;"><sup>*</sup> '.$this->l('Required fields').'</p>
			</form>
		</div>
		<div style="float: left; margin-left: 50px; width: 200px;">
			'.(file_exists(dirname(__FILE__).'/offer-'.$lang->iso_code.'.jpg') ? '
			<p><a href="http://www.iadvize.com/offre_prestashop.html" target="_blank"><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/offer-'.$lang->iso_code.'.jpg"></a></p>
			' : '').'
		</div>
		<div class="clear"></div>';

		return $output;
	}
	
	private function _displayCallButton()
	{
		return "\n".'
		<!-- iAdvize - Call button -->
		<div style="margin: 20px 0;">
		<script type="text/javascript">
			var iAdvize = ((\'https:\' == document.location.protocol) ? \'https://\' : \'http://\');
			document.write(unescape(\'%3Cscript src="\' + iAdvize + \'livechat.iadvize.com/chat_button.js?bid='.intval(Configuration::get('IADVIZE_BID')).'" type="text/javascript"%3E%3C/script%3E\'));
		</script>
		</div>
		<!-- /End - iAdvize - Call button -->'."\n";
	}
	
	public function hookLeftColumn($params)
	{
		if (Configuration::get('IADVIZE_BUTTON') == 1)
			return $this->_displayCallButton();
	}
	
	public function hookRightColumn($params)
	{
		if (Configuration::get('IADVIZE_BUTTON') == 2)
			return $this->_displayCallButton();
	}
	
	public function hookFooter($params)
	{
		return "\n".'
		<!-- iAdvize - Live chat -->
		<script type="text/javascript">
			'.(isset($params['cookie']->id_customer) ? 'var idzCustomData = {\'extID\':\''.intval($params['cookie']->id_customer).'\'};' : '').'
			var iAdvize = ((\'https:\' == document.location.protocol) ? \'https://\' : \'http://\');
			document.write(unescape(\'%3Cscript src="\' + iAdvize + \'livechat.iadvize.com/chat_init.js?sid='.intval(Configuration::get('IADVIZE_SID')).'" type="text/javascript"%3E%3C/script%3E\'));
			
		</script>
		<!-- /End - iAdvize - Live chat -->'."\n";
	}
}

?>
