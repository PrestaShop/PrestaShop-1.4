<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
class StatsData extends Module
{
    private $_html = '';

    function __construct()
    {
        $this->name = 'statsdata';
        $this->tab = 'Stats';
        $this->version = 1.0;

        parent::__construct();
		
        $this->displayName = $this->l('Data mining for statistics');
        $this->description = $this->l('This module must be enabled if you want to use Statistics');
    }

	function install()
	{
		// Everything is done in the footer (and not in the header because a page can redirect to another) except for identifying a guest as a customer
		if (!parent::install() OR !$this->registerHook('footer') OR !$this->registerHook('authentication') OR !$this->registerHook('createAccount'))
			return false;
	}
    
	function hookFooter($params)
	{
		global $protocol_content, $server_host;

		// Identification information are encrypted to prevent hacking attempts
		$blowfish = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
		if (!isset($params['cookie']->id_guest))
		{
			Guest::setNewGuest($params['cookie']);
			
			// Ajax request sending browser information
			$token = $blowfish->encrypt($params['cookie']->id_guest);
			$this->_html = '
			<script type="text/javascript" src="'.$protocol_content.$server_host.__PS_BASE_URI__.'js/pluginDetect.js"></script>
			<script type="text/javascript">
				plugins = new Object;
				
				plugins.adobe_director = (PluginDetect.getVersion("Shockwave") != null) ? 1 : 0;
				plugins.adobe_flash = (PluginDetect.getVersion("Flash") != null) ? 1 : 0;
				plugins.apple_quicktime = (PluginDetect.getVersion("QuickTime") != null) ? 1 : 0;
				plugins.windows_media = (PluginDetect.getVersion("WindowsMediaPlayer") != null) ? 1 : 0;
				plugins.sun_java = (PluginDetect.getVersion("java") != null) ? 1 : 0;
				plugins.real_player = (PluginDetect.getVersion("RealPlayer") != null) ? 1 : 0;
				
				$(document).ready(
					function() {
						navinfo = new Object;
						navinfo = { screen_resolution_x: screen.width, screen_resolution_y: screen.height, screen_color:screen.colorDepth};
						for (var i in plugins)
							navinfo[i] = plugins[i];
						navinfo.type = "navinfo";
						navinfo.token = "'.$token.'";
						$.post("'.$protocol_content.$server_host.__PS_BASE_URI__.'statistics.php", navinfo);
					}
				);
			</script>';
		}
		
		// Record the guest path then increment the visit counter of the page
		$tokenArray = Connection::setPageConnection($params['cookie']);
		ConnectionsSource::logHttpReferer();
		Page::setPageViewed($tokenArray['id_page']);
		
		// Ajax request sending the time spend on the page
		$token = $blowfish->encrypt($tokenArray['id_connections'].'|'.$tokenArray['id_page'].'|'.$tokenArray['time_start']);
		$this->_html .= '
		<script type="text/javascript">
			var time_start;
			$(window).load(
				function() {
					time_start = new Date();
				}
			);
			$(window).unload(
				function() {
					var time_end = new Date();
					var pagetime = new Object;
					pagetime.type = "pagetime";
					pagetime.token = "'.$token.'";
					pagetime.time = time_end-time_start;
					$.post("'.$protocol_content.$server_host.__PS_BASE_URI__.'statistics.php", pagetime);
				}
			);
		</script>';

		return $this->_html;
	}
	
	function hookCreateAccount($params)
	{
		return $this->hookAuthentication($params);
	}
	
	function hookAuthentication($params)
	{
		// Update or merge the guest with the customer id (login and account creation)
		$guest = new Guest($params['cookie']->id_guest);
		$result = Db::getInstance()->getRow('
		SELECT `id_guest`
		FROM `'._DB_PREFIX_.'guest`
		WHERE `id_customer` = '.intval($params['cookie']->id_customer));

		if (intval($result['id_guest']))
		{
			// The new guest is merged with the old one when it's connecting to an account
			$guest->mergeWithCustomer($result['id_guest'], $params['cookie']->id_customer);
			$params['cookie']->id_guest = $guest->id;
		}
		else
		{
			// The guest is duplicated if it has multiple customer accounts
			$method = ($guest->id_customer) ? 'add' : 'update';
			$guest->id_customer = $params['cookie']->id_customer;
			$guest->{$method}();
		}
	}
}

?>
