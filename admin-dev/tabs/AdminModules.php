<?php

/**
  * Modules tab for admin panel, AdminModules.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminModules extends AdminTab
{
	/** @var array map with $_GET keywords and their callback */
	private $map = array(
		'install' => 'install',
		'uninstall' => 'uninstall',
		'configure' => 'getContent',
		'delete' => 'delete'
	);
	
	private $listTabModules ;
	private $listPartnerModules ;
	private $listNativeModules ;
	
	function __construct()
	{
		parent::__construct ();
		$this->listTabModules = array('administration'=> $this->l('Administration'), 'advertising_marketing'=> $this->l('Advertising & Marketing'),
		 'analytics_stats'=> $this->l('Analytics & Stats'), 'billing_invoicing'=> $this->l('Billing & Invoicing'), 'checkout'=> $this->l('Checkout'),
		 'content_management'=> $this->l('Content Management'), 'export'=> $this->l('Export'), 'front_office_features'=> $this->l('Front office features'),
		 'i18n_localization'=> $this->l('I18n & Localization'), 'merchandizing'=> $this->l('Merchandizing'), 'migration_tools'=> $this->l('Migration tools'),
		 'payments_gateways'=> $this->l('Payments & Gateways'), 'pricing_promotion'=> $this->l('Pricing & Promotion'), 'quick_bulk_update'=> $this->l('Quick / Bulk update'),
		 'search_filter'=> $this->l('Search & Filter'), 'seo'=> $this->l('SEO'), 'shipping_logistics'=> $this->l('Shipping & Logistics'), 'slideshows'=> $this->l('Slideshows'),
		 'smart_shopping'=> $this->l('Smart shopping'), 'social_networks'=> $this->l('Social Networks'), 'others'=> $this->l('Others Modules'));
		 
		 $this->listNativeModules = array('bankwire', 'birthdaypresent', 'blockadvertising', 'blockbestsellers', 'blockcart', 'blockcategories', 'blockcms', 'blockcurrencies', 'blockinfos',
		  'blocklanguages', 'blocklink', 'blockmanufacturer', 'blockmyaccount', 'blocknewproducts', 'blocknewsletter', 'blockpaymentlogo', 'blockpermanentlinks', 'blockrss', 'blocksearch',
		  'blockspecials', 'blocksupplier', 'blocktags', 'blockuserinfo', 'blockvariouslinks', 'blockviewed', 'blockwishlist', 'canonicalurl', 'cashondelivery', 'cheque', 'crossselling', 'dateofdelivery',
		  'editorial', 'feeder', 'followup', 'gadsense', 'ganalytics', 'gcheckout', 'graphartichow', 'graphgooglechart', 'graphvisifire', 'graphxmlswfcharts', 'gridextjs', 'gsitemap', 'homefeatured',
		  'loyalty', 'mailalerts', 'newsletter', 'pagesnotfound', 'productcomments', 'productscategory', 'producttooltip', 'referralprogram', 'sekeywords', 'sendtoafriend',
		  'statsbestcategories', 'statsbestcustomers', 'statsbestproducts', 'statsbestsuppliers', 'statsbestvouchers', 'statscarrier', 'statscatalog', 'statscheckup', 'statsdata', 'statsequipment',
		  'statsgeolocation', 'statshome', 'statslive', 'statsnewsletter', 'statsorigin', 'statspersonalinfos', 'statsproduct', 'statsregistrations', 'statssales', 'statssearch', 'statsstock',
		  'statsvisits', 'tm4b', 'trackingfront', 'vatnumber', 'watermark');
		 
		 $this->listPartnerModules = array('dejala', 'envoimoinscher', 'hipay', 'moneybookers', 'ogone', 'paypal', 'paypalapi', 'socolissimo', 'treepodia', 'trustedshops', 'secuvad', 'reverso', 'iadvize');
	}
	
	public function postProcess()
	{
		global $currentIndex, $cookie;

		if (Tools::isSubmit('filterModules1'))
		{
				Configuration::updateValue('PS_SHOW_ALL_MODULES', 0);
				Configuration::updateValue('PS_SHOW_NATIVE_MODULES', 0);
				Configuration::updateValue('PS_SHOW_PARTNERS_MODULES', 0);
				Configuration::updateValue('PS_SHOW_OTHERS_MODULES', 0);
				Configuration::updateValue('PS_SHOW_COUNTRY_MODULES', 0);

			switch (Tools::getValue('filter'))
			{
				case 'all_modules':
				Configuration::updateValue('PS_SHOW_ALL_MODULES', 1);
				Configuration::updateValue('PS_SHOW_INSTALLED_MODULES', 0);
				Configuration::updateValue('PS_SHOW_UNINSTALLED_MODULES', 0);
				break;
				case 'native_modules':
				Configuration::updateValue('PS_SHOW_NATIVE_MODULES', 1);
				break;
				case 'partners_modules':
				Configuration::updateValue('PS_SHOW_PARTNERS_MODULES', 1);
				break;
				case 'others_modules':
				Configuration::updateValue('PS_SHOW_OTHERS_MODULES', 1);
				break;
				case 'country_modules':
				if(Tools::getValue('country_module_value'))
					Configuration::updateValue('PS_SHOW_COUNTRY_MODULES', 1);
				else
					Configuration::updateValue('PS_SHOW_ALL_MODULES', 1);
				break;
			}
		}
		elseif (Tools::isSubmit('filterModules2'))
		{
			Configuration::updateValue('PS_SHOW_INSTALLED_MODULES', 0);
			Configuration::updateValue('PS_SHOW_UNINSTALLED_MODULES', 0);
			
			switch (Tools::getValue('filter'))
			{
			case 'installed_modules':
				Configuration::updateValue('PS_SHOW_INSTALLED_MODULES', 1);
				break;
			case 'uninstalled_modules':
				Configuration::updateValue('PS_SHOW_UNINSTALLED_MODULES', 1);
				break;
			}
		}
		if (Tools::isSubmit('active'))
		{
		 	if ($this->tabAccess['edit'] === '1')
			{
				$module = Module::getInstanceByName(strval(Tools::getValue('module_name')));
				if (Validate::isLoadedObject($module))
				{
					Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'module`
					SET `active`= 1
					WHERE `name` = \''.pSQL(Tools::getValue('module_name')).'\'');
					Tools::redirectAdmin($currentIndex.'&conf=5'.'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name);
				} else
					$this->_errors[] = Tools::displayError('Cannot load module object');
			} else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (Tools::isSubmit('desactive'))
		{
		 	if ($this->tabAccess['edit'] === '1')
			{
				$module = Module::getInstanceByName(Tools::getValue('module_name'));
				if (Validate::isLoadedObject($module))
				{
					Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'module`
					SET `active`= 0
					WHERE `name` = \''.pSQL(Tools::getValue('module_name')).'\'');
					Tools::redirectAdmin($currentIndex.'&conf=5'.'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name);
				} else
					$this->_errors[] = Tools::displayError('Cannot load module object');
			} else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		elseif (Tools::isSubmit('reset'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$module = Module::getInstanceByName(Tools::getValue('module_name'));
				if (Validate::isLoadedObject($module))
				{
					if ($module->uninstall())
						if ($module->install())
							Tools::redirectAdmin($currentIndex.'&conf=21'.'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name);
						else
							$this->_errors[] = Tools::displayError('Cannot install module');
					else
						$this->_errors[] = Tools::displayError('Cannot uninstall module');
											
				} else
					$this->_errors[] = Tools::displayError('Cannot load module object');
			} else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');

		}
		/* Automatically copy a module from external URL and unarchive it in the appropriated directory */
		if (Tools::isSubmit('submitDownload'))
		{
		 	if ($this->tabAccess['add'] === '1')
			{
				if (Validate::isModuleUrl($url = Tools::getValue('url'), $this->_errors))
				{
					if (!@copy($url, _PS_MODULE_DIR_.basename($url)))
						$this->_errors[] = Tools::displayError('404 Module not found');
					else
						$this->extractArchive(_PS_MODULE_DIR_.basename($url));
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		if (Tools::isSubmit('submitDownload2'))
		{
		 	if ($this->tabAccess['add'] === '1')
			{
				if (!isset($_FILES['file']['tmp_name']) OR empty($_FILES['file']['tmp_name']))
					$this->_errors[] = $this->l('no file selected');
				elseif (substr($_FILES['file']['name'], -4) != '.tar' AND substr($_FILES['file']['name'], -4) != '.zip' AND substr($_FILES['file']['name'], -4) != '.tgz' AND substr($_FILES['file']['name'], -7) != '.tar.gz')
					$this->_errors[] = Tools::displayError('unknown archive type');
				elseif (!@copy($_FILES['file']['tmp_name'], _PS_MODULE_DIR_.$_FILES['file']['name']))
					$this->_errors[] = Tools::displayError('an error occured while copying archive to module directory');
				else
					$this->extractArchive(_PS_MODULE_DIR_.$_FILES['file']['name']);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		if (Tools::isSubmit('deleteModule'))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
				if (Tools::getValue('module_name') != '')
				{
					$moduleDir = _PS_MODULE_DIR_.Tools::getValue('module_name');
					$this->recursiveDeleteOnDisk($moduleDir);
					Tools::redirectAdmin($currentIndex.'&conf=22&token='.$this->token.'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name'));
				}
				Tools::redirectAdmin($currentIndex.'&token='.$this->token);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete anything here.');
		}

		/* Call appropriate module callback */
		else
		{
		 	$return = false;
			foreach ($this->map as $key => $method)
			{
				$modules = Tools::getValue($key);
				if (strpos($modules, '|'))
					$modules = explode('|', $modules);
				else
					$modules = empty($modules) ? false : array($modules);
				$module_errors = array();
				if ($modules)
					foreach ($modules AS $name)
					{
						if (!($module = @Module::getInstanceByName(urldecode($name))))
							$this->_errors[] = $this->l('module not found');
						elseif ($key == 'install' AND $this->tabAccess['add'] !== '1')
							$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
						elseif ($key == 'uninstall' AND $this->tabAccess['delete'] !== '1')
							$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
						elseif ($key == 'configure' AND $this->tabAccess['edit'] !== '1')
							$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
						elseif (($echo = $module->{$method}()) AND ($key == 'configure') AND Module::isInstalled($module->name))
						{
							echo '
							<p><a href="'.$currentIndex.'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to modules list').'</a></p>
							<br />'.$echo.'<br />
							<p><a href="'.$currentIndex.'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to modules list').'</a></p>';
						}
						elseif($echo)
							$return = ($method == 'install' ? 12 : 13);
						elseif ($echo === false)
							$module_errors[] = $name;
						if ($key != 'configure' AND isset($_GET['bpay']))
							Tools::redirectAdmin('index.php?tab=AdminPayment&token='.Tools::getAdminToken('AdminPayment'.intval(Tab::getIdFromClassName('AdminPayment')).intval($cookie->id_employee)));
					}
				if (sizeof($module_errors))
				{
					$htmlError = '';

					foreach ($module_errors AS $module_error)
						$htmlError .= '<li>'.$module_error.'</li>';
					$htmlError .= '</ul>';
					$this->_errors[] = Tools::displayError('The following module(s) were not installed successfully:'.$htmlError);
				}
			}
			if ($return)
				Tools::redirectAdmin($currentIndex.'&conf='.$return.'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name);
		}
	}

	function extractArchive($file)
	{
		global $currentIndex;
		$success = false;
		if (substr($file, -4) == '.zip')
		{
			if (class_exists('ZipArchive', false))
			{
				$zip = new ZipArchive();
				if ($zip->open($file) === true AND $zip->extractTo(_PS_MODULE_DIR_) AND $zip->close())
					$success = true;
				else
					$this->_errors[] = Tools::displayError('error while extracting module (file may be corrupted)');
			}
			else
				$this->_errors[] = Tools::displayError('zip is not installed on your server. Ask your host for further information.');
		}
		else
		{
			$archive = new Archive_Tar($file);
			if ($archive->extract(_PS_MODULE_DIR_))
				$success = true;
			else
				$this->_errors[] = Tools::displayError('error while extracting module (file may be corrupted)');
		}
		
		@unlink($file);
		if ($success)
			Tools::redirectAdmin($currentIndex.'&conf=8'.'&token='.$this->token);
	}
	
	public function display()
	{
		if (!isset($_GET['configure']) AND !isset($_GET['delete']) OR sizeof($this->_errors) )
			$this->displayList();
	}

	public function displayJavascript()
	{
		global $currentIndex;

		echo '
		<script type="text/javascript">
			function modules_management(action)
			{
				var modules = document.getElementsByName(\'modules\');
				var module_list = \'\';
				for (var i = 0; i < modules.length; i++)
				{
					if (modules[i].checked == true)
					{
						rel = modules[i].getAttribute(\'rel\');
						if (rel != "false" && action == "uninstall")
						{
							if (!confirm(rel))
								return false;
						}
						module_list += \'|\'+modules[i].value;
					}
				}
				document.location.href=\''.$currentIndex.'&token='.$this->token.'&\'+action+\'=\'+module_list.substring(1, module_list.length);
			}
			 $(document).ready(function() {
				
			});
			
		</script>';
	}
		
	public function displayList()
	{
		global $currentIndex, $cookie;
		
		$showAllModules = Configuration::get('PS_SHOW_ALL_MODULES');
		$showNativeModules = Configuration::get('PS_SHOW_NATIVE_MODULES');
		$showPartnerModules = Configuration::get('PS_SHOW_PARTNERS_MODULES');
		$showOthersModules = Configuration::get('PS_SHOW_OTHERS_MODULES');
		$showCountryModules = Configuration::get('PS_SHOW_COUNTRY_MODULES');
		$showInstalledModules = Configuration::get('PS_SHOW_INSTALLED_MODULES');
		$showUninstalledModules = Configuration::get('PS_SHOW_UNINSTALLED_MODULES');
		
		$serialModules = '';
		$modules = Module::getModulesOnDisk();
		foreach ($modules AS $module)
			$serialModules .= $module->name.' '.$module->version.'-'.($module->active ? 'a' : 'i')."\n";
		$serialModules = urlencode($serialModules);
		
		//filter module list
		foreach($modules as $key => $module)
		{
		if (!$showAllModules)
			if ($showNativeModules)
				if (!in_array($module->name, $this->listNativeModules))
					unset($modules[$key]);
			if ($showPartnerModules)
				if (!in_array($module->name, $this->listPartnerModules))
					unset($modules[$key]);
			if ($showOthersModules)
				if (in_array($module->name, $this->listPartnerModules) OR in_array($module->name, $this->listNativeModules))
					unset($modules[$key]);
			if ($showInstalledModules)
				if (!$module->id)
					unset($modules[$key]);
			if ($showUninstalledModules)
				if ($module->id)
					unset($modules[$key]);
		}
	
		$this->displayJavascript();
			
		$linkToSettings = 'index.php?tab=AdminPreferences&token='.Tools::getAdminToken('AdminPreferences'.intval(Tab::getIdFromClassName('AdminPreferences')).intval($cookie->id_employee));
		echo '<span onclick="openCloseLayer(\'module_install\', 0);" style="cursor: pointer;font-weight: 700; float: left;"><img src="../img/admin/add.gif" alt="'.$this->l('Add a new module').'" class="middle" /> '.$this->l('Add a new module').'</span>';
		if (Configuration::get('PRESTASTORE_LIVE') AND @ini_get('allow_url_fopen'))
			echo '<script type="text/javascript">
				function getPrestaStore(){if (getE("prestastore").style.display!=\'block\')return;$.post("'.dirname($currentIndex).'/ajax.php",{page:"prestastore"},function(a){getE("prestastore-content").innerHTML=a;})}
			</script>
			<span onclick="openCloseLayer(\'prestastore\', 0); getPrestaStore();" style="cursor: pointer;font-weight: 700; float: left;margin-left:20px;"><img src="../img/admin/prestastore.gif" class="middle" /> '.$this->l('PrestaStore').'</span>&nbsp;(<a href="'.$linkToSettings.'">'.$this->l('disable').'</a>)';
		echo '
		<div class="clear">&nbsp;</div>
		<div id="module_install" style="float: left;'.((Tools::isSubmit('submitDownload') OR Tools::isSubmit('submitDownload2')) ? '' : 'display: none;').'" class="width1">
			<fieldset>
				<legend><img src="../img/admin/add.gif" alt="'.$this->l('Add a new module').'" class="middle" /> '.$this->l('Add a new module').'</legend>
				<p>'.$this->l('The module must be either a zip file or a tarball.').'</p>
				<hr />
				<form action="'.$currentIndex.'&token='.$this->token.'" method="post">
					<label style="width: 100px">'.$this->l('Module URL').'</label>
					<div class="margin-form" style="padding-left: 140px">
						<input type="text" name="url" style="width: 200px;" value="'.(Tools::getValue('url') ? Tools::getValue('url') : 'http://').'" />
						<p>'.$this->l('Download the module directly from a website.').'</p>
					</div>
					<div class="margin-form" style="padding-left: 140px">
						<input type="submit" name="submitDownload" value="'.$this->l('Download this module').'" class="button" />
					</div>
				</form>
				<hr />
				<form action="'.$currentIndex.'&token='.$this->token.'" method="post" enctype="multipart/form-data">
					<label style="width: 100px">'.$this->l('Module file').'</label>
					<div class="margin-form" style="padding-left: 140px">
						<input type="file" name="file" />
						<p>'.$this->l('Upload the module from your computer.').'</p>
					</div>
					<div class="margin-form" style="padding-left: 140px">
						<input type="submit" name="submitDownload2" value="'.$this->l('Upload this module').'" class="button" />
					</div>
				</form>
			</fieldset>
		</div>';
		if (Configuration::get('PRESTASTORE_LIVE'))
			echo '
			<div id="prestastore" style="margin-left:40px; display:none; float: left" class="width1">
				<fieldset>
					<legend><img src="http://addons.prestashop.com/modules.php?'.(isset($_SERVER['SERVER_ADDR']) ? 'server='.ip2long($_SERVER['SERVER_ADDR']).'&' : '').'mods='.$serialModules.'" class="middle" />'.$this->l('Live from PrestaShop Addons!').'</legend>
					<div id="prestastore-content"></div>
				</fieldset>
			</div>';
		echo '<div class="clear">&nbsp;</div>';

		/* Scan modules directories and load modules classes */
		$warnings = array();
		$orderModule = array();
	    $irow = 0;
		foreach ($modules AS $module)
		{
			$orderModule[(isset($module->tab) AND !empty($module->tab) AND array_key_exists($module->tab, $this->listTabModules)) ? $module->tab : 'others' ][] = $module;
		}					
		asort($orderModule);

		foreach ($orderModule AS $tabModule)
			foreach ($tabModule AS $module)
				if ($module->active AND $module->warning)
					$this->displayWarning('<a href="'.$currentIndex.'&configure='.urlencode($module->name).'&token='.$this->token.'">'.$module->displayName.'</a> - '.stripslashes(pSQL($module->warning)));
		$nameCountryDefault = Country::getNameById($cookie->id_lang, Configuration::get('PS_COUNTRY_DEFAULT'));
		$isoCountryDefault = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
		
		
		echo '<table cellpadding="0" cellspacing="0" class="table" style="width:100%;">
				<tr>
					<th colspan="3">
						<div style="height:30px;padding-top:5px;">
							<span style="border-bottom: none;padding-right:5px;border-right:solid 1px gray">
								<input type="button" onclick=javascript:document.location.href="'.$currentIndex.'&token='.$this->token.'&filterModules1&filter=all_modules" value="'.$this->l('All Modules').'" name="all_module" class="button big" '.($showAllModules ? 'style="background-position:0 -25px;border-color:#CCCCCC"' : '').'>
							</span>
							<span style="border-bottom: none;padding-left:10px;">
								<input type="button" onclick=javascript:document.location.href="'.$currentIndex.'&token='.$this->token.'&filterModules1&filter=native_modules" value="'.$this->l('Native Modules').'" name="native_module" class="button big" '.($showNativeModules ? 'style="background-position:0 -25px;border-color:#CCCCCC"' : '').'>
							</span>
							<span style="border-bottom: none">
								<input type="button" onclick=javascript:document.location.href="'.$currentIndex.'&token='.$this->token.'&filterModules1&filter=partners_modules" value="'.$this->l('Partners Modules').'" name="partner_module" class="button big" '.($showPartnerModules ? 'style="background-position:0 -25px;border-color:#CCCCCC"' : '').'>
							</span>
							<span style="border-bottom: none">
								<input type="button" onclick=javascript:document.location.href="'.$currentIndex.'&token='.$this->token.'&filterModules1&filter=others_modules" value="'.$this->l('Others Modules').'" name="other_module" class="button big" '.($showOthersModules ? 'style="background-position:0 -25px;border-color:#CCCCCC"' : '').'>
							</span>
						</div>
					</th>
					<th colspan="2">
						<div style="height:30px;padding-top:5px;">						
							<span style="border-bottom: solid 1px">
								<div style="float:right">
									<input type="button" onclick=javascript:document.location.href="'.$currentIndex.'&token='.$this->token.'&filterModules2&filter=installed_modules" value="'.$this->l('Installed Modules').'" name="installed_module" class="button big" '.($showInstalledModules ? 'style="background-position:0 -25px;border-color:#CCCCCC"' : '').'>
									<input type="button" onclick=javascript:document.location.href="'.$currentIndex.'&token='.$this->token.'&filterModules2&filter=uninstalled_modules" value="'.$this->l('Uninstalled Modules').'" name="uninstalled_module" class="button big" '.($showUninstalledModules ? 'style="background-position:0 -25px;border-color:#CCCCCC"' : '').'>
								</div>
							</span>
						</div>
					</th>
			  </tr>
			  <tr>
			  	<td colspan="5" style="height:40px;border-bottom:solid 1px;background-color:#EEEEEE">
			  		<form method="POST" id="form_all_module" action="">
			  			<input type="hidden" name="filter" value="country_modules">
						<input type="checkbox" name="country_module_value" style="vertical-align: middle;" id="all_module" '.(Configuration::get('PS_SHOW_COUNTRY_MODULES') ? 'checked="checked"' : '').' onclick="document.getElementById(\'form_all_module\').submit();" />&nbsp;&nbsp;
						<label class="t" for="all_module">'.$this->l('Show only modules that can be used in my country').'</label> ('.$this->l('Current country:').' <a href="index.php?tab=AdminCountries&token='.Tools::getAdminToken('AdminCountries'.intval(Tab::getIdFromClassName('AdminCountries')).intval($cookie->id_employee)).'">'.$nameCountryDefault.'</a>)
					</form>
			  	</td>
			  </tr>
				<tr style="height:35px;background-color:#EEEEEE">
						<td><strong>'.$this->l('Icon legend').' : </strong></td>
						<td style="text-align:center;border-right:solid 1px gray"><img src="../img/admin/module_install.png" />&nbsp;&nbsp;'.$this->l('Module installed and enabled').'</td>
						<td style="text-align:center;border-right:solid 1px gray"><img src="../img/admin/module_disabled.png" />&nbsp;&nbsp;'.$this->l('Module installed but disabled').'</td>
						<td style="text-align:center;border-right:solid 1px gray"><img src="../img/admin/module_warning.png" />&nbsp;&nbsp;'.$this->l('Module installed but some warnings').'</td>
						<td style="text-align:center"><img src="../img/admin/module_notinstall.png" />&nbsp;&nbsp;'.$this->l('Module not installed').'</td>
					</tr>
				</table>';
		
		if ($tab_module = Tools::getValue('tab_module'))
			if (array_key_exists($tab_module, $this->listTabModules))
				$goto = '$("#'.$tab_module.'")';
			else
				$goto = '$("#others")';
		else
			$goto = '0';
		echo '  <br>
  		<script src="'.__PS_BASE_URI__.'js/jquery/accordion/accordion.ui.js"></script>
  		<script src="'.__PS_BASE_URI__.'js/jquery/jquery.scrollto.js"></script>
		<script>
		 $(document).ready(function() {
			 		$( "#accordion" ).accordion({
					autoHeight: false,
					navigation: false,
					active: \'.active\',
					active: \'.active\',
			        selectedClass: \'.active\',
			        header: "h4"
				});
					$("#accordion").accordion("activate", '.$goto.');
					'.($goto != '0' ? '$.scrollTo($("#modgo_'.Tools::getValue('module_name').'"), 1000 , 
						{onAfter:function(){
							$("#modgo_'.Tools::getValue('module_name').'").fadeTo(100, 0, function (){
								$(this).fadeTo(100, 0, function (){
									$(this).fadeTo(100, 1, function (){
										$(this).fadeTo(100, 0, function (){
											$(this).fadeTo(100, 1 )}
												)}
											)}
										)}
									)}
								});' : '').'

			});
			
		 </script>';
		if (!empty($orderModule))
		{
		echo '<div class="basic" id="accordion">';
		/* Browse modules by tab type */
		foreach ($orderModule AS $tab => $tabModule)
		{
			echo '
			<h4 id="'.$tab.'" style="-moz-border-radius:0;margin:0;height:25px;padding-top:5px;" >
			<div style="float:right;margin-right:10px;font-style:italic;font-size:12px">'.sizeof($tabModule).' '.((sizeof($tabModule) > 1) ? $this->l('modules') : $this->l('module')).'</div>
				<a href="modgo_'.$tab.'"><b>'.$this->listTabModules[$tab].'</b> </a> 
			</h4><div class="basic" id="'.$tab.'" style="background:none;-moz-border-radius:0;overflow:hidden">';
			/* Display modules for each tab type */
			foreach ($tabModule as $module)
			{
			echo '<div id="modgo_'.$module->name.'">';
			if ($module->id)
				{
					$img = '<img src="../img/admin/module_install.png" alt="'.$this->l('Module enabled').'" title="'.$this->l('Module enabled').'" />';
					if ($module->warning)
						$img = '<img src="../img/admin/module_warning.png" alt="'.$this->l('Module installed but with warnings').'" title="'.$this->l('Module installed but with warnings').'" />';
					if (!$module->active)
						$img = '<img src="../img/admin/module_disabled.png" alt="'.$this->l('Module disabled').'" title="'.$this->l('Module disabled').'" />';
				} else
					$img = '<img src="../img/admin/module_notinstall.png" alt="'.$this->l('Module not installed').'" title="'.$this->l('Module not installed').'" />';
				echo '<table style="width:100%" cellpadding="0" cellspacing="0" >
				<tr'.($irow % 2 ? ' class="alt_row"' : '').' style="height: 42px;">
					<td style="padding-right: 10px;padding-left:10px;width:30px">
						<input type="checkbox" name="modules" value="'.urlencode($module->name).'" '.(empty($module->confirmUninstall) ? 'rel="false"' : 'rel="'.addslashes($module->confirmUninstall).'"').' />
					</td>
					<td style="padding:2px 4px 2px 10px;width:500px"><img src="../modules/'.$module->name.'/logo.gif" alt="" /> <b>'.stripslashes($module->displayName).'</b>'.($module->version ? ' v'.$module->version.(strpos($module->version, '.') !== false ? '' : '.0') : '').'<br />'.$module->description.'</td>
					<td rowspan="2">';
					if (Tools::getValue('module_name') == $module->name)
					{

						$this->displayConf();
						//$this->displayErrors();
					}
					echo '</td>
					<td class="center" style="width:60px" rowspan="2">';
				if ($module->id)
					echo '<a href="'.$currentIndex.'&token='.$this->token.'&module_name='.$module->name.'&'.($module->active ? 'desactive' : 'active').'">';
				echo $img;
				if ($module->id)
					'</a>';
				echo '
					</td>
					<td class="center" width="120" rowspan="2">'.((!$module->id)
					? '<input type="button" class="button small" name="Install" value="'.$this->l('Install').'"
					onclick="javascript:document.location.href=\''.$currentIndex.'&install='.urlencode($module->name).'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name.'\'" />'
					: '<input type="button" class="button small" name="Uninstall" value="'.$this->l('Uninstall').'"
					onclick="'.(empty($module->confirmUninstall) ? '' : 'if(confirm(\''.addslashes($module->confirmUninstall).'\')) ').'document.location.href=\''.$currentIndex.'&uninstall='.urlencode($module->name).'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.$module->name.'\'" />').'</a></td>
					
				</tr>
				<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>
					<td style="padding-left:50px;padding-bottom:5px;padding-top:5px" colspan="2">'.$this->displayOptions($module).'</td>
				</tr>
				</table>
				</div>';
			}
			echo '</div>';
		}
		echo '
			
		</div>
		<div style="margin-top: 12px; width:600px;">
				<input type="button" class="button big" value="'.$this->l('Install the selection').'" onclick="modules_management(\'install\')"/>
				<input type="button" class="button big" value="'.$this->l('Uninstall the selection').'" onclick="modules_management(\'uninstall\')" />
			</div>
		<div style="clear:both">&nbsp;</div>';
		}
		else
			echo '<table cellpadding="0" cellspacing="0" class="table" style="width:100%;">
					<tr>
						<td align="center">'.$this->l('No module found').'</td>
					</tr>
				  </table>';
	}
	
	
	public function recursiveDeleteOnDisk($dir) {
	   if (is_dir($dir)) 
	   {
	     $objects = scandir($dir);
	     foreach ($objects as $object) {
	       if ($object != "." && $object != "..") {
	         if (filetype($dir."/".$object) == "dir") $this->recursiveDeleteOnDisk($dir."/".$object); else unlink($dir."/".$object);
	       }
	     }
	     reset($objects);
	     rmdir($dir);
	   }
	}
	
	public function displayOptions($module)
	{
		global $currentIndex;
		$return = '';
		
		if (intval($module->id))
			$return .= '<a class="action_module" href="'.$currentIndex.'&token='.$this->token.'&module_name='.urlencode($module->name).'&'.($module->active ? 'desactive' : 'active').'&tab_module='.$module->tab.'&module_name='.urlencode($module->name).'">'.($module->active ? $this->l('Deactive') : $this->l('Active')).'</a>&nbsp;&nbsp;';
		
		if (intval($module->id) AND $module->active)
			$return .= '<a class="action_module" href="'.$currentIndex.'&token='.$this->token.'&module_name='.urlencode($module->name).'&reset&tab_module='.$module->tab.'&module_name='.urlencode($module->name).'">'.$this->l('Reset').'</a>&nbsp;&nbsp;';
		
		if (intval($module->id))
			$return .= '<a href="'.$currentIndex.'&uninstall='.urlencode($module->name).'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.urlencode($module->name).'">'.$this->l('Uninstall').'</a>&nbsp;&nbsp;';
		
		if (intval($module->id) AND method_exists($module, 'getContent'))
			$return .= '<a href="'.$currentIndex.'&configure='.urlencode($module->name).'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.urlencode($module->name).'">'.$this->l('Configure').'</a>&nbsp;&nbsp;';
			
		$return .= '<a onclick="return confirm(\''.$this->l('This action removes definitely the module from the server. Are you really sure ? ').'\');" href="'.$currentIndex.'&deleteModule='.urlencode($module->name).'&token='.$this->token.'&tab_module='.$module->tab.'&module_name='.urlencode($module->name).'">'.$this->l('Delete').'</a>&nbsp;&nbsp;';
		return $return;
	}
	
}

?>
