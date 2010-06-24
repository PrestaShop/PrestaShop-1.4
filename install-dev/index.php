<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('Europe/Paris');

/* Redefine REQUEST_URI if empty (on some webservers...) */
$_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);
if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '')
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
if ($tmp = strpos($_SERVER['REQUEST_URI'], '?'))
	$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $tmp);

define('INSTALL_VERSION', '1.4.0.1');
define('MINIMUM_VERSION_TO_UPDATE', '0.8.5');
define('INSTALL_PATH', dirname(__FILE__));
include_once(INSTALL_PATH.'/classes/ToolsInstall.php');

/* Prevent from bad URI parsing when using index.php */
$requestUri = str_replace('index.php', '', $_SERVER['REQUEST_URI']);
$tmpBaseUri = substr($requestUri, 0, -1 * (strlen($requestUri) - strrpos($requestUri, '/')) - strlen(substr(substr($requestUri,0,-1), strrpos( substr($requestUri,0,-1),"/" )+1)));
define('PS_BASE_URI', $tmpBaseUri[strlen($tmpBaseUri) - 1] == '/' ? $tmpBaseUri : $tmpBaseUri.'/');
define('PS_BASE_URI_ABSOLUTE', 'http://'.ToolsInstall::getHttpHost(false, true).PS_BASE_URI);

/* Old version detection */
$oldversion = false;
$sameVersions = false;
$tooOld = true;
if(file_exists(INSTALL_PATH.'/../config/settings.inc.php')){
	include(INSTALL_PATH.'/../config/settings.inc.php');
	$oldversion =_PS_VERSION_;
	$tooOld = (version_compare($oldversion, MINIMUM_VERSION_TO_UPDATE) == -1);
	$sameVersions = (version_compare($oldversion, INSTALL_VERSION) == 0);
}

include(INSTALL_PATH.'/classes/LanguagesManager.php');
$lm = new LanguageManager(dirname(__FILE__).'/langs/list.xml');
$_LANG = array();
$_LIST_WORDS = array();
function lang($txt) {
	global $_LANG , $_LIST_WORDS;
	return (isset($_LANG[$txt]) ? $_LANG[$txt] : $txt);
}
if ($lm->getIncludeTradFilename())
	include_once($lm->getIncludeTradFilename());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Cache-Control" content="no-cache, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Cache" content="no store" />
	<meta http-equiv="Expires" content="-1" />
	<title><?php echo lang('PrestaShop '.INSTALL_VERSION.' Installer')?></title>
	<link rel="stylesheet" type="text/css" media="all" href="view.css"/>
	<script type="text/javascript" src="<?php echo PS_BASE_URI ?>js/jquery/jquery-1.2.6.pack.js"></script>
	<script type="text/javascript" src="<?php echo PS_BASE_URI ?>js/jquery/ajaxfileupload.js"></script>
	<script type="text/javascript" src="<?php echo PS_BASE_URI ?>js/jquery/jquery.pngFix.pack.js"></script>
	<script type="text/javascript" src="<?php echo PS_BASE_URI ?>js/jquery/jqminmax-compressed.js"></script>
	<link rel="shortcut icon" href="<?php echo PS_BASE_URI ?>img/favicon.ico" />
	
	<script type="text/javascript">
		//php to js vars
		var isoCodeLocalLanguage = "<?php echo $lm->getIsoCodeSelectedLang()?>";
		var ps_base_uri = "<?php echo PS_BASE_URI?>";
		
		//localWords
		var Step1Title = "<?php echo lang('Welcome').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step2title = "<?php echo lang('System compatibility').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step3title = "<?php echo lang('System configuration').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step4title = "<?php echo lang('Shop configuration').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step5title = "<?php echo lang('Ready, set, go!').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step6title = "<?php echo lang('Disclaimer').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step7title = "<?php echo lang('System compatibility').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step8title = "<?php echo lang('Errors while updating...').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var step9title = "<?php echo lang('Ready, set, go!').' - '.lang('PrestaShop '.INSTALL_VERSION.' Installer'); ?>";
		var txtNext = "<?php echo lang('Next')?>"
		var txtDbLoginEmpty = "<?php echo lang('Please set a database login'); ?>";
		var txtDbNameEmpty = "<?php echo lang('Please set a database name'); ?>";
		var txtDbServerEmpty = "<?php echo lang('Please set a database server name'); ?>";
		var txtSmtpAvailable = "<?php echo lang('SMTP connection is available!'); ?>";
		var txtSmtpError = "<?php echo lang('SMTP connection is unavailable'); ?>";
		var txtSmtpSrvEmpty = "<?php echo lang('Please set a SMTP server name'); ?>";
		var txtSmtpLoginEmpty = "<?php echo lang('Please set a SMTP login'); ?>";
		var txtSmtpPasswordEmpty = "<?php echo lang('Please set a SMTP password'); ?>";
		var txtNativeMailAvailable = "<?php echo lang('PHP \'mail()\' function is available'); ?>";
		var txtNativeMailError = "<?php echo lang('PHP \'mail()\' function is unavailable'); ?>";
		var txtDbCreated = "<?php echo lang('Database is created!'); ?>";
		var testMsg = "<?php echo lang('This is a test message, your server is now available to send email'); ?>";
		var testSubject = "<?php echo lang('Test message - Prestashop'); ?>";
		var mailSended = "<?php echo lang('An email has been sent!'); ?>";
		var mailSubject = "<?php echo lang('Congratulation, your online shop is now ready!'); ?>";
		var txtTabUpdater1 = "<?php echo lang('Welcome'); ?>";
		var txtTabUpdater2 = "<?php echo lang('Disclaimer'); ?>";
		var txtTabUpdater3 = "<?php echo lang('Verify system compatibility'); ?>";
		var txtTabUpdater4 = "<?php echo lang('Update is complete!'); ?>";
		var txtTabInstaller1 = "<?php echo lang('Welcome'); ?>";
		var txtTabInstaller2 = "<?php echo lang('Verify system compatibility'); ?>";
		var txtTabInstaller3 = "<?php echo lang('System configuration'); ?>";
		var txtTabInstaller4 = "<?php echo lang('Shop configuration'); ?>";
		var txtTabInstaller5 = "<?php echo lang('Installation is complete!'); ?>";
		var txtConfigIsOk = "<?php echo lang('Your configuration is valid, click next to continue!'); ?>";
		var txtConfigIsNotOk = "<?php echo lang('Your configuration is invalid. Please fix the issues below:'); ?>";
		
		var txtError = new Array();
		txtError[0] = "<?php echo lang('Required field'); ?>";
		txtError[1] = "<?php echo lang('Too long!'); ?>";
		txtError[2] = "<?php echo lang('Fields are different!'); ?>";
		txtError[3] = "<?php echo lang('This email adress is wrong!'); ?>";
		txtError[4] = "<?php echo lang('Impossible to send the email!'); ?>";
		txtError[5] = "<?php echo lang('Can\'t create settings file, if /config/settings.inc.php exists, please give the public write permissions to this file, else please create a file named settings.inc.php in config directory.'); ?>";
		txtError[6] = "<?php echo lang('Can\'t write settings file, please create a file named settings.inc.php in config directory.'); ?>";
		txtError[7] = "<?php echo lang('Impossible to upload the file!'); ?>";
		txtError[8] = "<?php echo lang('Data integrity is not valided. Hack attempt?'); ?>";
		txtError[9] = "<?php echo lang('Impossible to read the content of a MySQL content file.'); ?>";
		txtError[10] = "<?php echo lang('Impossible the access the a MySQL content file.'); ?>";
		txtError[11] = "<?php echo lang('Error while inserting data in the database:'); ?>";
		txtError[12] = "<?php echo lang('The password is incorrect (alphanumeric string at least 8 characters).'); ?>";
		txtError[14] = "<?php echo lang('A Prestashop database already exists, please drop it or change the prefix.'); ?>";
		txtError[15] = "<?php echo lang('This is not a valid file name.'); ?>";
		txtError[16] = "<?php echo lang('This is not a valid image file.'); ?>";
		txtError[17] = "<?php echo lang('Error while creating the /config/settings.inc.php file.'); ?>";
		txtError[18] = "<?php echo lang('Error:'); ?>";
		txtError[19] = "<?php echo lang('This PrestaShop database already exists. Please revalidate your authentication informations to the database.'); ?>";
		txtError[22] = "<?php echo lang('An error occured while resizing the picture.'); ?>";
		txtError[23] = "<?php echo lang('Database connection is available!'); ?>";
		txtError[24] = "<?php echo lang('Database Server is available but database is not found'); ?>";
		txtError[25] = "<?php echo lang('Database Server is not found. Please verify the login, password and server fields.'); ?>";
		txtError[26] = "<?php echo lang('An error occured while sending email, please verify your parameters.'); ?>";
		txtError[37] = "<?php echo lang('Impossible to write the image /img/logo.jpg. If this image already exists, please delete it.'); ?>";
		txtError[38] = "<?php echo lang('The uploaded file exceeds the upload_max_filesize directive in php.ini'); ?>";
		txtError[39] = "<?php echo lang('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'); ?>";
		txtError[40] = "<?php echo lang('The uploaded file was only partially uploaded'); ?>";
		txtError[41] = "<?php echo lang('No file was uploaded.'); ?>";
		txtError[42] = "<?php echo lang('Missing a temporary folder'); ?>";
		txtError[43] = "<?php echo lang('Failed to write file to disk'); ?>";
		txtError[44] = "<?php echo lang('File upload stopped by extension'); ?>";
		txtError[45] = "<?php echo lang('Cannot convert your database\'s data to utf-8.'); ?>";
		txtError[46] = "<?php echo lang('Invalid shop name'); ?>";
		txtError[47] = "<?php echo lang('Your firstname contains some invalid characters'); ?>";
		txtError[48] = "<?php echo lang('Your lastname contains some invalid characters'); ?>";
		txtError[49] = "<?php echo lang('Your database server does not support the utf-8 charset.'); ?>";
		txtError[999] = "<?php echo lang('No error code available.'); ?>";
		//upgrader
		txtError[27] = "<?php echo lang('This installer is too old.'); ?>";
		txtError[28] = "<?php echo lang('You already have the '.INSTALL_VERSION.' version.'); ?>";
		txtError[29] = "<?php echo lang('There is no older version. Did you delete or rename the config/settings.inc.php file?'); ?>";
		txtError[30] = "<?php echo lang('The config/settings.inc.php file was not found. Did you delete or rename this file?'); ?>";
		txtError[31] = "<?php echo lang('Can\'t find the sql upgrade files. Please verify that the /install/sql/upgrade folder is not empty)'); ?>";
		txtError[32] = "<?php echo lang('No upgrade is possible.'); ?>";
		txtError[33] = "<?php echo lang('Error while loading sql upgrade file.'); ?>";
		txtError[34] = "<?php echo lang('Error while inserting content into the database'); ?>";
		txtError[35] = "<?php echo lang('Unfortunately,'); ?>";
		txtError[36] = "<?php echo lang('SQL errors have occurred.'); ?>";

	</script>
	<script type="text/javascript" src="controller.js"></script>

</head>
<body>

<div id="noJavaScript">
	<?php echo lang('This application need you to activate Javascript to correctly work.')?>
</div>

<div id="container">

<div id="loaderSpace">
	<div id="loader">&nbsp;</div>
</div>

<div id="leftpannel">
	<h1>
		<div id="PrestaShopLogo">&nbsp;</div>
		<div class="installerVersion" id="installerVersion-<?php echo $lm->getIsoCodeSelectedLang()?>">&nbsp;</div>
		<div class="updaterVersion" id="updaterVersion-<?php echo $lm->getIsoCodeSelectedLang()?>">&nbsp;</div>
	</h1>
	
	<ol id="tabs"><li>&nbsp;</li></ol>
	
	<div id="help">
		<img src="img/ico_help.gif" alt="help" class="ico_help" />
		
		<div class="content">
			<p class="title"><?php echo lang('Need help?'); ?></p>
			<p class="title_down"><?php echo lang('All tips and advice about PrestaShop'); ?></p>
			
			<ul>
				<li><img src="img/puce.gif" alt="" /> <a href="http://www.prestashop.com/forums/" target="_blank"><?php echo lang('Forum'); ?></a><br class="clear" /></li>
				<li><img src="img/puce.gif" alt="" /> <a href="http://www.prestashop.com/blog/"><?php echo lang('Blog'); ?></a><br class="clear" /></li>
			</ul>
		</div>
	</div>
</div>


<div id="sheets">

	<div class="sheet shown" id="sheet_lang">
		<h2><?php echo lang('Welcome')?></h2>
		<h3><?php echo lang('Welcome to the PrestaShop '.INSTALL_VERSION.' Installer.')?><br /><?php echo lang('Please allow 5-15 minutes to complete the installation process.')?></h3>
		<p><?php echo lang('The PrestaShop Installer will do most of the work in just a few clicks.')?><br /><?php echo lang('However, you will have to know how to do the following manually:')?></p>
		<ul>
			<li><?php echo lang('Set permissions on folders & subfolders using Terminal or an FTP client')?></li>
			<li><?php echo lang('Access and configure PHP 5.0+ on your hosting server')?></li>
			<li><?php echo lang('Back up your database and all application files (update only)')?></li>
		</ul>
		<p>
			<?php echo lang('For more information, please consult our') ?> <a href="http://www.prestashop.com/wiki/Getting_Started/"><?php echo lang('online documentation') ?></a>.
		</p>
		
		<h3><?php echo lang('Choose the installer language:')?></h3>
		<form id="formSetInstallerLanguage" action="<?php $_SERVER['REQUEST_URI']; ?>" method="get">
			<ul id="langList" style="line-height: 20px;">
			<?php foreach ($lm->getAvailableLangs() as $lang):?>
				<li><input onclick="setInstallerLanguage()" type="radio" value="<?php echo $lang['id'] ?>" <?php echo ( $lang['id'] == $lm->getIdSelectedLang() ) ? "checked=\"checked\"" : '' ?> id="lang_<?php echo $lang['id'] ?>" name="language" style="vertical-align: middle; margin-right: 0;" /><label for="lang_<?php echo $lang['id'] ?>">
				<?php foreach ($lang->flags->url as $url_flag):?>
					<img src="<?php echo $url_flag ?>" alt="<?php echo $lang['label'] ?>" style="vertical-align: middle;" />
				<?php endforeach ?>
				<?php echo $lang['label'] ?></label></li>
				
			<?php endforeach?>
			</ul>
		</form>
		<h3 class="no-margin"><?php echo lang('Did you know?'); ?></h3>
		<p>
			<?php echo lang('Prestashop and community offers over 40 different languages for free download on'); ?> <a href="http://www.prestashop.com" target="_blank">http://www.prestashop.com</a>
		</p>
		
		<h3><?php echo lang('Installation method')?></h3>
		<form id="formSetMethod" action="<?php $_SERVER['REQUEST_URI']; ?>" method="post">
			<p><input <?php echo (!($oldversion AND !$tooOld AND !$sameVersions)) ? 'checked="checked"' : '' ?> type="radio" value="install" name="typeInstall" id="typeInstallInstall"/><label for="typeInstallInstall"><?php echo lang('Installation : complete install of the PrestaShop Solution')?></label></p>
			<p <?php echo ($oldversion AND !$tooOld AND !$sameVersions) ? '' : 'class="disabled"'; ?>><input <?php echo ($oldversion AND !$tooOld AND !$sameVersions) ? 'checked="checked"' : 'disabled="disabled"'; ?> type="radio" value="upgrade" name="typeInstall" id="typeInstallUpgrade"/><label <?php echo ($oldversion === false) ? 'class="disabled"' : ''; ?> for="typeInstallUpgrade"><?php echo lang('Upgrade: get the latest stable version!')?> <?php echo ($oldversion === false) ? lang('(no old version detected)') : ("(".(  ($tooOld) ? lang('the already installed version detected is too old, no more update available') : lang('installed version detected').' : '.$oldversion    ).")") ?></label></p>
		</form>
		
	</div>
	
		<div class="sheet" id="sheet_require">
			
			<h2><?php echo lang('System and permissions')?></h2>
			
			<h3><?php echo lang('Required set-up. Please make sure the following checklist items are true.')?></h3>
			
			<p>
				<?php echo lang('If you have any questions, please visit our '); ?>
				<a href="http://www.prestashop.com/wiki/Getting_Started/ " target="_blank"><?php echo lang('Documentation Wiki'); ?></a>
				<?php echo lang('and/or'); ?>
				<a href="http://www.prestashop.com/forums/" target="_blank"><?php echo lang('Community Forum'); ?></a><?php echo lang('.'); ?>
			</p>
			
			<h3 id="resultConfig" style="font-size: 20px; text-align: center; padding: 0px; display: none;"></h3>
			<ul id="required">
				<li class="title"><?php echo lang('PHP parameters:')?></li>
				<li class="required"><?php echo lang('PHP 5.0 or later installed')?></li>
				<li class="required"><?php echo lang('File upload allowed')?></li>
				<li class="required"><?php echo lang('Create new files and folders allowed')?></li>
				<li class="required"><?php echo lang('GD Library installed')?></li>
				<li class="required"><?php echo lang('MySQL support is on')?></li>
				<li class="title"><?php echo lang('Write permissions on files and folders:')?></li>
				<li class="required">/config</li>
				<li class="required">/tools/smarty/compile</li>
				<li class="required">/sitemap.xml</li>
				<li class="title"><?php echo lang('Write permissions on folders (and subfolders):')?></li>
				<li class="required">/img</li>
				<li class="required">/mails</li>
				<li class="required">/modules</li>
				<li class="required">/themes/prestashop/lang</li>
				<li class="required">/translations</li>
				<li class="required">/upload</li>
				<li class="required">/download</li>
			</ul>
			
			<h3><?php echo lang('Optional set-up')?></h3>
			<ul id="optional">
				<li class="title"><?php echo lang('PHP parameters:')?></li>
				<li class="optional"><?php echo lang('Open external URLs allowed')?></li>
				<li class="optional"><?php echo lang('PHP register global option is off (recommended)')?></li>
				<li class="optional"><?php echo lang('GZIP compression is on (recommended)')?></li>
			</ul>
			
			<p><input class="button" value="<?php echo lang('Refresh these settings')?>" type="button" id="req_bt_refresh"/></p>
			
		</div>
		
		<div class="sheet" id="sheet_db">
			<h2><?php echo lang('Database configuration')?></h2>
			
			<p><?php echo lang('Configure your database by filling out the following fields:')?></p>
			<form id="formCheckSQL" class="aligned" action="<?php $_SERVER['REQUEST_URI']; ?>" onsubmit="verifyDbAccess(); return false;" method="post">
				<h3 style="padding:0;margin:0;"><?php echo lang('You have to create a database, help available in readme_en.txt'); ?></h3>
				<p style="margin-top: 15px;">
					<label for="dbServer"><?php echo lang('Server:')?> </label>
					<input size="25" class="text" type="text" id="dbServer" value="localhost"/>
				</p>
				<p>
					<label for="dbName"><?php echo lang('Database name:')?> </label>
					<input size="10" class="text" type="text" id="dbName" value="prestashop"/>
				</p>
				<p>
					<label for="dbLogin"><?php echo lang('Login:')?> </label>
					<input class="text" size="10" type="text" id="dbLogin" value="root"/>
				</p>
				<p>
					<label for="dbPassword"><?php echo lang('Password:')?> </label>
					<input class="text" autocomplete="off" size="10" type="password" id="dbPassword"/>
				</p>
				<p class="aligned">
					<input id="btTestDB" class="button" type="submit" value="<?php echo lang('Verify now!')?>"/>
				</p>
				<p id="dbResultCheck"></p>
			</form>
			
			<div id="dbTableParam">
				<form action="#" method="post" onsubmit="createDB(); return false;">
				<p><label for="db_prefix"><?php echo lang('Tables prefix:')?> </label><input class="text" type="text" id="db_prefix" value="ps_"/></p>
				<p id="dbModeSetter">
					<input value="full" type="radio" name="db_mode" checked="checked" id="db_mode_complet" /><label for="db_mode_complet"><?php echo lang('Full mode: Install the main modules and add sample products')?></label><br/>
					<input value="lite" type="radio" name="db_mode" id="db_mode_simple" /><label for="db_mode_simple"><?php echo lang('Simple mode: Don\'t install any module')?></label>
				</p>
				</form>
				<p id="dbCreateResultCheck"></p>
			</div>
			<div id="mailPart">
				<h2><?php echo lang('E-mail delivery set-up')?></h2>
				
				<p>
					<input type="checkbox" id="set_stmp" style="vertical-align: middle;" /><label for="set_stmp"><?php echo lang('Configure SMTP manually (advanced users only)'); ?></label><br/>
					<span class="userInfos"><?php echo lang('By default, the PHP \'mail()\' function is used'); ?></span>
				</p>
				
				<div id="mailSMTPParam">
					<form class="aligned" action="#" method="post" onsubmit="verifyMail(); return false;">
						<p>
							<label for="smtpSrv"><?php echo lang('SMTP server:'); ?> </label>
							<input class="text" type="text" id="smtpSrv" value="smtp."/>
						</p>
						<p>
							<label for="smtpEnc"><?php echo lang('Encryption:'); ?></label>
							<select id="smtpEnc">
								<option value="off" selected="selected"><?php echo lang('None'); ?></option>
								<option value="tls">TLS</option>
								<option value="ssl">SSL</option>
							</select>
						</p>
						
						<p>
							<label for="smtpPort"><?php echo lang('Port:'); ?></label>
							<input type="text" size="5" id="smtpPort" value="25" />
						</p>
						
						<p>
							<label for="smtpLogin"><?php echo lang('Login:'); ?> </label>
							<input class="text" type="text" size="10" id="smtpLogin" value="" />
						</p>

						<p>
							<label for="smtpPassword"><?php echo lang('Password:'); ?> </label>
							<input autocomplete="off" class="text" type="password" size="10" id="smtpPassword" />
						</p>

					</form>
				</div>
				<p>
					<input class="text" id="testEmail" type="text" size="15" value="<?php echo lang('enter@your.email'); ?>"></input>
					<input id="btVerifyMail" class="button" type="submit" value="<?php echo lang('Send me a test email!'); ?>"></input>
				</p>
				
				<p id="mailResultCheck" class="userInfos"></p>
			</div>
		</div>
		
		<div class="sheet" id="sheet_infos">
			<form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="return false;" enctype="multipart/form-data">
				
				<h2><?php echo lang('Shop configuration'); ?></h2>
				
				<h3><?php echo lang('Merchant info'); ?></h3>
				<div class="field">
					<label for="infosShop" class="aligned"><?php echo lang('Shop name:'); ?> </label><input class="text required" type="text" id="infosShop" value=""/><br/>
					<span id="resultInfosShop" class="result aligned"></span>
				</div>
				<div class="field">
					<label for="infosCountry" class="aligned"><?php echo lang('Default country:'); ?></label>
					<select id="infosCountry" style="width:175px;border:1px solid #D41958">
						<option value="231"><?php echo lang('Afghanistan'); ?></option>
						<option value="244"><?php echo lang('Aland Islands'); ?></option>
						<option value="230"><?php echo lang('Albania'); ?></option>
						<option value="38"><?php echo lang('Algeria'); ?></option>
						<option value="39"><?php echo lang('American Samoa'); ?></option>
						<option value="40"><?php echo lang('Andorra'); ?></option>
						<option value="41"><?php echo lang('Angola'); ?></option>
						<option value="42"><?php echo lang('Anguilla'); ?></option>
						<option value="232"><?php echo lang('Antarctica'); ?></option>
						<option value="43"><?php echo lang('Antigua and Barbuda'); ?></option>
						<option value="44"><?php echo lang('Argentina'); ?></option>
						<option value="45"><?php echo lang('Armenia'); ?></option>
						<option value="46"><?php echo lang('Aruba'); ?></option>
						<option value="24"><?php echo lang('Australia'); ?></option>
						<option value="2"><?php echo lang('Austria'); ?></option>
						<option value="47"><?php echo lang('Azerbaijan'); ?></option>
						<option value="48"><?php echo lang('Bahamas'); ?></option>
						<option value="49"><?php echo lang('Bahrain'); ?></option>
						<option value="50"><?php echo lang('Bangladesh'); ?></option>
						<option value="51"><?php echo lang('Barbados'); ?></option>
						<option value="52"><?php echo lang('Belarus'); ?></option>
						<option value="3"><?php echo lang('Belgium'); ?></option>
						<option value="53"><?php echo lang('Belize'); ?></option>
						<option value="54"><?php echo lang('Benin'); ?></option>
						<option value="55"><?php echo lang('Bermuda'); ?></option>
						<option value="56"><?php echo lang('Bhutan'); ?></option>
						<option value="34"><?php echo lang('Bolivia'); ?></option>
						<option value="233"><?php echo lang('Bosnia and Herzegovina'); ?></option>
						<option value="57"><?php echo lang('Botswana'); ?></option>
						<option value="234"><?php echo lang('Bouvet Island'); ?></option>
						<option value="58"><?php echo lang('Brazil'); ?></option>
						<option value="235"><?php echo lang('British Indian Ocean Territory'); ?></option>
						<option value="59"><?php echo lang('Brunei'); ?></option>
						<option value="236"><?php echo lang('Bulgaria'); ?></option>
						<option value="60"><?php echo lang('Burkina Faso'); ?></option>
						<option value="61"><?php echo lang('Burma (Myanmar)'); ?></option>
						<option value="62"><?php echo lang('Burundi'); ?></option>
						<option value="63"><?php echo lang('Cambodia'); ?></option>
						<option value="64"><?php echo lang('Cameroon'); ?></option>
						<option value="4"><?php echo lang('Canada'); ?></option>
						<option value="65"><?php echo lang('Cape Verde'); ?></option>
						<option value="237"><?php echo lang('Cayman Islands'); ?></option>
						<option value="66"><?php echo lang('Central African Republic'); ?></option>
						<option value="67"><?php echo lang('Chad'); ?></option>
						<option value="68"><?php echo lang('Chile'); ?></option>
						<option value="5"><?php echo lang('China'); ?></option>
						<option value="238"><?php echo lang('Christmas Island'); ?></option>
						<option value="239"><?php echo lang('Cocos (Keeling) Islands'); ?></option>
						<option value="69"><?php echo lang('Colombia'); ?></option>
						<option value="70"><?php echo lang('Comoros'); ?></option>
						<option value="71"><?php echo lang('Congo, Dem. Republic'); ?></option>
						<option value="72"><?php echo lang('Congo, Republic'); ?></option>
						<option value="240"><?php echo lang('Cook Islands'); ?></option>
						<option value="73"><?php echo lang('Costa Rica'); ?></option>
						<option value="74"><?php echo lang('Croatia'); ?></option>
						<option value="75"><?php echo lang('Cuba'); ?></option>
						<option value="76"><?php echo lang('Cyprus'); ?></option>
						<option value="16"><?php echo lang('Czech Republic'); ?></option>
						<option value="20"><?php echo lang('Denmark'); ?></option>
						<option value="77"><?php echo lang('Djibouti'); ?></option>
						<option value="78"><?php echo lang('Dominica'); ?></option>
						<option value="79"><?php echo lang('Dominican Republic'); ?></option>
						<option value="80"><?php echo lang('East Timor'); ?></option>
						<option value="81"><?php echo lang('Ecuador'); ?></option>
						<option value="82"><?php echo lang('Egypt'); ?></option>
						<option value="83"><?php echo lang('El Salvador'); ?></option>
						<option value="84"><?php echo lang('Equatorial Guinea'); ?></option>
						<option value="85"><?php echo lang('Eritrea'); ?></option>
						<option value="86"><?php echo lang('Estonia'); ?></option>
						<option value="87"><?php echo lang('Ethiopia'); ?></option>
						<option value="88"><?php echo lang('Falkland Islands'); ?></option>
						<option value="89"><?php echo lang('Faroe Islands'); ?></option>
						<option value="90"><?php echo lang('Fiji'); ?></option>
						<option value="7"><?php echo lang('Finland'); ?></option>
						<option value="8" selected="selected"><?php echo lang('France'); ?></option>
						<option value="241"><?php echo lang('French Guiana'); ?></option>
						<option value="242"><?php echo lang('French Polynesia'); ?></option>
						<option value="243"><?php echo lang('French Southern Territories'); ?></option>
						<option value="91"><?php echo lang('Gabon'); ?></option>
						<option value="92"><?php echo lang('Gambia'); ?></option>
						<option value="93"><?php echo lang('Georgia'); ?></option>
						<option value="1"><?php echo lang('Germany'); ?></option>
						<option value="94"><?php echo lang('Ghana'); ?></option>
						<option value="97"><?php echo lang('Gibraltar'); ?></option>
						<option value="9"><?php echo lang('Greece'); ?></option>
						<option value="96"><?php echo lang('Greenland'); ?></option>
						<option value="95"><?php echo lang('Grenada'); ?></option>
						<option value="98"><?php echo lang('Guadeloupe'); ?></option>
						<option value="99"><?php echo lang('Guam'); ?></option>
						<option value="100"><?php echo lang('Guatemala'); ?></option>
						<option value="101"><?php echo lang('Guernsey'); ?></option>
						<option value="102"><?php echo lang('Guinea'); ?></option>
						<option value="103"><?php echo lang('Guinea-Bissau'); ?></option>
						<option value="104"><?php echo lang('Guyana'); ?></option>
						<option value="105"><?php echo lang('Haiti'); ?></option>
						<option value="106"><?php echo lang('Heard Island and McDonald Islands'); ?></option>
						<option value="108"><?php echo lang('Honduras'); ?></option>
						<option value="22"><?php echo lang('HongKong'); ?></option>
						<option value="143"><?php echo lang('Hungary'); ?></option>
						<option value="109"><?php echo lang('Iceland'); ?></option>
						<option value="110"><?php echo lang('India'); ?></option>
						<option value="111"><?php echo lang('Indonesia'); ?></option>
						<option value="112"><?php echo lang('Iran'); ?></option>
						<option value="113"><?php echo lang('Iraq'); ?></option>
						<option value="26"><?php echo lang('Ireland'); ?></option>
						<option value="114"><?php echo lang('Isle of Man'); ?></option>
						<option value="29"><?php echo lang('Israel'); ?></option>
						<option value="10"><?php echo lang('Italy'); ?></option>
						<option value="32"><?php echo lang('Ivory Coast'); ?></option>
						<option value="115"><?php echo lang('Jamaica'); ?></option>
						<option value="11"><?php echo lang('Japan'); ?></option>
						<option value="116"><?php echo lang('Jersey'); ?></option>
						<option value="117"><?php echo lang('Jordan'); ?></option>
						<option value="118"><?php echo lang('Kazakhstan'); ?></option>
						<option value="119"><?php echo lang('Kenya'); ?></option>
						<option value="120"><?php echo lang('Kiribati'); ?></option>
						<option value="121"><?php echo lang('Korea, Dem. Republic of'); ?></option>
						<option value="122"><?php echo lang('Kuwait'); ?></option>
						<option value="123"><?php echo lang('Kyrgyzstan'); ?></option>
						<option value="124"><?php echo lang('Laos'); ?></option>
						<option value="125"><?php echo lang('Latvia'); ?></option>
						<option value="126"><?php echo lang('Lebanon'); ?></option>
						<option value="127"><?php echo lang('Lesotho'); ?></option>
						<option value="128"><?php echo lang('Liberia'); ?></option>
						<option value="129"><?php echo lang('Libya'); ?></option>
						<option value="130"><?php echo lang('Liechtenstein'); ?></option>
						<option value="131"><?php echo lang('Lithuania'); ?></option>
						<option value="12"><?php echo lang('Luxemburg'); ?></option>
						<option value="132"><?php echo lang('Macau'); ?></option>
						<option value="133"><?php echo lang('Macedonia'); ?></option>
						<option value="134"><?php echo lang('Madagascar'); ?></option>
						<option value="135"><?php echo lang('Malawi'); ?></option>
						<option value="136"><?php echo lang('Malaysia'); ?></option>
						<option value="137"><?php echo lang('Maldives'); ?></option>
						<option value="138"><?php echo lang('Mali'); ?></option>
						<option value="139"><?php echo lang('Malta'); ?></option>
						<option value="140"><?php echo lang('Marshall Islands'); ?></option>
						<option value="141"><?php echo lang('Martinique'); ?></option>
						<option value="142"><?php echo lang('Mauritania'); ?></option>
						<option value="35"><?php echo lang('Mauritius'); ?></option>
						<option value="144"><?php echo lang('Mayotte'); ?></option>
						<option value="145"><?php echo lang('Mexico'); ?></option>
						<option value="146"><?php echo lang('Micronesia'); ?></option>
						<option value="147"><?php echo lang('Moldova'); ?></option>
						<option value="148"><?php echo lang('Monaco'); ?></option>
						<option value="149"><?php echo lang('Mongolia'); ?></option>
						<option value="150"><?php echo lang('Montenegro'); ?></option>
						<option value="151"><?php echo lang('Montserrat'); ?></option>
						<option value="152"><?php echo lang('Morocco'); ?></option>
						<option value="153"><?php echo lang('Mozambique'); ?></option>
						<option value="154"><?php echo lang('Namibia'); ?></option>
						<option value="155"><?php echo lang('Nauru'); ?></option>
						<option value="156"><?php echo lang('Nepal'); ?></option>
						<option value="13"><?php echo lang('Netherlands'); ?></option>
						<option value="157"><?php echo lang('Netherlands Antilles'); ?></option>
						<option value="158"><?php echo lang('New Caledonia'); ?></option>
						<option value="27"><?php echo lang('New Zealand'); ?></option>
						<option value="159"><?php echo lang('Nicaragua'); ?></option>
						<option value="160"><?php echo lang('Niger'); ?></option>
						<option value="31"><?php echo lang('Nigeria'); ?></option>
						<option value="161"><?php echo lang('Niue'); ?></option>
						<option value="162"><?php echo lang('Norfolk Island'); ?></option>
						<option value="163"><?php echo lang('Northern Mariana Islands'); ?></option>
						<option value="23"><?php echo lang('Norway'); ?></option>
						<option value="164"><?php echo lang('Oman'); ?></option>
						<option value="165"><?php echo lang('Pakistan'); ?></option>
						<option value="166"><?php echo lang('Palau'); ?></option>
						<option value="167"><?php echo lang('Palestinian Territories'); ?></option>
						<option value="168"><?php echo lang('Panama'); ?></option>
						<option value="169"><?php echo lang('Papua New Guinea'); ?></option>
						<option value="170"><?php echo lang('Paraguay'); ?></option>
						<option value="171"><?php echo lang('Peru'); ?></option>
						<option value="172"><?php echo lang('Philippines'); ?></option>
						<option value="173"><?php echo lang('Pitcairn'); ?></option>
						<option value="14"><?php echo lang('Poland'); ?></option>
						<option value="15"><?php echo lang('Portugal'); ?></option>
						<option value="174"><?php echo lang('Puerto Rico'); ?></option>
						<option value="175"><?php echo lang('Qatar'); ?></option>
						<option value="176"><?php echo lang('Reunion'); ?></option>
						<option value="36"><?php echo lang('Romania'); ?></option>
						<option value="177"><?php echo lang('Russian Federation'); ?></option>
						<option value="178"><?php echo lang('Rwanda'); ?></option>
						<option value="179"><?php echo lang('Saint Barthelemy'); ?></option>
						<option value="180"><?php echo lang('Saint Kitts and Nevis'); ?></option>
						<option value="181"><?php echo lang('Saint Lucia'); ?></option>
						<option value="182"><?php echo lang('Saint Martin'); ?></option>
						<option value="183"><?php echo lang('Saint Pierre and Miquelon'); ?></option>
						<option value="184"><?php echo lang('Saint Vincent and the Grenadines'); ?></option>
						<option value="185"><?php echo lang('Samoa'); ?></option>
						<option value="186"><?php echo lang('San Marino'); ?></option>
						<option value="187"><?php echo lang('Sao Tome and Principe'); ?></option>
						<option value="188"><?php echo lang('Saudi Arabia'); ?></option>
						<option value="189"><?php echo lang('Senegal'); ?></option>
						<option value="190"><?php echo lang('Serbia'); ?></option>
						<option value="191"><?php echo lang('Seychelles'); ?></option>
						<option value="192"><?php echo lang('Sierra Leone'); ?></option>
						<option value="25"><?php echo lang('Singapore'); ?></option>
						<option value="37"><?php echo lang('Slovakia'); ?></option>
						<option value="193"><?php echo lang('Slovenia'); ?></option>
						<option value="194"><?php echo lang('Solomon Islands'); ?></option>
						<option value="195"><?php echo lang('Somalia'); ?></option>
						<option value="30"><?php echo lang('South Africa'); ?></option>
						<option value="196"><?php echo lang('South Georgia and the South Sandwich Islands'); ?></option>
						<option value="28"><?php echo lang('South Korea'); ?></option>
						<option value="6"><?php echo lang('Spain'); ?></option>
						<option value="197"><?php echo lang('Sri Lanka'); ?></option>
						<option value="198"><?php echo lang('Sudan'); ?></option>
						<option value="199"><?php echo lang('Suriname'); ?></option>
						<option value="200"><?php echo lang('Svalbard and Jan Mayen'); ?></option>
						<option value="201"><?php echo lang('Swaziland'); ?></option>
						<option value="18"><?php echo lang('Sweden'); ?></option>
						<option value="19"><?php echo lang('Switzerland'); ?></option>
						<option value="202"><?php echo lang('Syria'); ?></option>
						<option value="203"><?php echo lang('Taiwan'); ?></option>
						<option value="204"><?php echo lang('Tajikistan'); ?></option>
						<option value="205"><?php echo lang('Tanzania'); ?></option>
						<option value="206"><?php echo lang('Thailand'); ?></option>
						<option value="33"><?php echo lang('Togo'); ?></option>
						<option value="207"><?php echo lang('Tokelau'); ?></option>
						<option value="208"><?php echo lang('Tonga'); ?></option>
						<option value="209"><?php echo lang('Trinidad and Tobago'); ?></option>
						<option value="210"><?php echo lang('Tunisia'); ?></option>
						<option value="211"><?php echo lang('Turkey'); ?></option>
						<option value="212"><?php echo lang('Turkmenistan'); ?></option>
						<option value="213"><?php echo lang('Turks and Caicos Islands'); ?></option>
						<option value="214"><?php echo lang('Tuvalu'); ?></option>
						<option value="215"><?php echo lang('Uganda'); ?></option>
						<option value="216"><?php echo lang('Ukraine'); ?></option>
						<option value="217"><?php echo lang('United Arab Emirates'); ?></option>
						<option value="17"><?php echo lang('United Kingdom'); ?></option>
						<option value="218"><?php echo lang('Uruguay'); ?></option>
						<option value="21"><?php echo lang('USA'); ?></option>
						<option value="219"><?php echo lang('Uzbekistan'); ?></option>
						<option value="220"><?php echo lang('Vanuatu'); ?></option>
						<option value="107"><?php echo lang('Vatican City State'); ?></option>
						<option value="221"><?php echo lang('Venezuela'); ?></option>
						<option value="222"><?php echo lang('Vietnam'); ?></option>
						<option value="223"><?php echo lang('Virgin Islands (British)'); ?></option>
						<option value="224"><?php echo lang('Virgin Islands (U.S.)'); ?></option>
						<option value="225"><?php echo lang('Wallis and Futuna'); ?></option>
						<option value="226"><?php echo lang('Western Sahara'); ?></option>
						<option value="227"><?php echo lang('Yemen'); ?></option>
						<option value="228"><?php echo lang('Zambia'); ?></option>
						<option value="229"><?php echo lang('Zimbabwe'); ?></option>
					</select>
				</div>
				<div class="field">
					<label for="infosLogo" class="aligned logo"><?php echo lang('Shop logo'); ?> : </label>
					<input type="file" onchange="uploadLogo()" name="fileToUpload" id="fileToUpload"/>
					<span id="resultInfosLogo" class="result"></span>
					<p class="userInfos aligned"><?php echo lang('recommended dimensions: 230px X 75px'); ?></p>
					<p id="alignedLogo"><img id="uploadedImage" src="<?php echo PS_BASE_URI ?>img/logo.jpg" alt="Logo" /></p>
				</div>
				
				<div class="field">
					<label for="infosFirstname" class="aligned"><?php echo lang('First name:'); ?> </label><input class="text required" type="text" id="infosFirstname"/><br/>
					<span id="resultInfosFirstname" class="result aligned"></span>
				</div>
				
				<div class="field">
					<label for="infosName" class="aligned"><?php echo lang('Last name:'); ?> </label><input class="text required" type="text" id="infosName"/><br/>
					<span id="resultInfosName" class="result aligned"></span>
				</div>
				
				<div class="field">
					<label for="infosEmail" class="aligned"><?php echo lang('E-mail address:'); ?> </label><input type="text" class="text required" id="infosEmail"/><br/>
					<span id="resultInfosEmail" class="result aligned"></span>
				</div>
				
				<div class="field">
					<label for="infosPassword" class="aligned"><?php echo lang('Shop password:'); ?> </label><input autocomplete="off" type="password" class="text required" id="infosPassword"/><br/>
					<span id="resultInfosPassword" class="result aligned"></span>
				</div>
				<div class="field">
					<label class="aligned" for="infosPasswordRepeat"><?php echo lang('Re-type to confirm:'); ?> </label><input type="password" autocomplete="off" class="text required" id="infosPasswordRepeat"/><br/>
					<span id="resultInfosPasswordRepeat" class="result aligned"></span>
				</div>
				
				<div class="field">
					<input type="checkbox" id="infosNotification" class="aligned" style="vertical-align: middle;" /><label for="infosNotification"><?php echo lang('Receive notifications by e-mail'); ?></label><br/>
					<span id="resultInfosNotification" class="result aligned"></span>
					<p class="userInfos aligned"><?php echo lang('This option can be blocking if your mail configuration is wrong, please disable it to move to the next step.'); ?></p>
				</div>
				
				<!--<h3><?php echo lang('Shop\'s languages'); ?></h3>
				<p class="userInfos"><?php echo lang('Select the different languages available for your shop'); ?></p>-->
				<div id="availablesLanguages" style=" float:left; text-align: center; display:none;">
					
					<?php echo lang('Optional languages'); ?><br/>
					<select style="width:300px;" id="aLList" multiple="multiple" size="4">
					<?php foreach ($lm->getAvailableLangs() as $lang){
						if ( $lang['id'] != $lm->getIdSelectedLang() AND $lang['id']  != "0" ){?>
							<option value="<?php echo $lang->idLangPS ?>"><?php echo $lang['label'] ?></option>
					<?php }} ?>
					</select>
				</div>
				
				<div id="RightLeft" style="float: left; width:50px; margin-top: 1.7em; text-align:center; display:none;">
					<input id="al2wl" value="&gt;" type="button"/><br/>
					<input id="wl2al" value="&lt;" type="button" />
				</div>
				
				<div id="websitesLanguages" style="float:left; text-align: center; display:none;">
					<?php echo lang('Available shop languages'); ?><br/>
					<select style="width:240px;" id="wLList" size="4">
						<option value="en">English (English)</option>
						<?php foreach ($lm->getAvailableLangs() as $lang){
							if ( $lang['id'] == $lm->getIdSelectedLang() AND $lang['id']  != "0" ){?>
								<option value="<?php echo $lang->idLangPS ?>"><?php echo $lang['label'] ?></option>
						<?php }} ?>
						
					</select><br/>
					<label for="dLList"><?php echo lang('Shop\'s default language'); ?></label><br/>
					<select style="width:180px;" id="dLList">
						<option selected="selected" value="en">English (English)</option>
						<?php foreach ($lm->getAvailableLangs() as $lang){
							if ( $lang['id'] == $lm->getIdSelectedLang() AND $lang['id']  != "0" ){?>
								<option selected="selected" value="<?php echo $lang->idLangPS ?>"><?php echo $lang['label'] ?></option>
						<?php }} ?>
					</select>
				</div>
			</form>
		
			<div id="resultEnd">
				<span id="resultInfosSQL" class="result"></span>
				<span id="resultInfosLanguages" class="result"></span>
			</div>
		
		</div>
		
		<div class="sheet" id="sheet_end" style="padding:0">
			<div style="padding:1em">
				<h2><?php echo lang('PrestaShop is ready!'); ?></h2>			
				<h3><?php echo lang('Your installation is finished!'); ?></h3>
				<p><?php echo lang('You\'ve just installed and configured PrestaShop as your online shop solution. We wish you all the best with the success of your online shop.'); ?></p>
				<p><?php echo lang('Here are your shop information. You can modify them once logged in.'); ?></p>
				<table id="resultInstall" cellspacing="0">
					<tr>
						<td class="label"><?php echo lang('Shop name:'); ?></td>
						<td id="endShopName" class="resultEnd">&nbsp;</td>
					</tr>
					<tr>
						<td class="label"><?php echo lang('First name:'); ?></td>
						<td id="endFirstName" class="resultEnd">&nbsp;</td>
					</tr>
					<tr>
						<td class="label"><?php echo lang('Last name:'); ?></td>
						<td id="endName" class="resultEnd">&nbsp;</td>
					</tr>
					<tr>
						<td class="label"><?php echo lang('E-mail:'); ?></td>
						<td id="endEmail" class="resultEnd">&nbsp;</td>
					</tr>
				</table>
				<h3><?php echo lang('WARNING: For more security, you must delete the \'install\' folder and readme files (readme_fr.txt, readme_en.txt, readme_es.txt).'); ?></h3>
				
				<a href="../admin" id="access" class="BO" target="_blank">
					<span class="title"><?php echo lang('Back Office'); ?></span>
					<span class="description"><?php echo lang('Manage your store with your back office. Manage your orders and customers, add modules, change your theme, etc...'); ?></span>
					<span class="message"><?php echo lang('Manage your store'); ?></span>
				</a>
				<a href="../" id="access" class="FO" target="_blank">
					<span class="title"><?php echo lang('Front Office'); ?></span>
					<span class="description"><?php echo lang('Find your store as your future customers will see!'); ?></span>
					<span class="message"><?php echo lang('Discover your store'); ?></span>
				</a>
				<div id="resultEnd"></div>
			</div>
			<?php
			// Check if can contact prestastore.com
			if (@fsockopen('www.prestastore.com', 80, $errno, $errst, 3)):
			?>
			<iframe src="http://www.prestastore.com/psinstall.php?lang=<?php echo $lm->getIsoCodeSelectedLang()?>" scrolling="no" id="prestastore">
				<p>Your browser does not support iframes.</p>
			</iframe>
			<?php
			endif;
			?>
		</div>
		
		<div class="sheet" id="sheet_disclaimer">
			<h2><?php echo lang('Disclaimer'); ?></h2>			
			<h3><?php echo lang('Warning: a manual backup is HIGHLY recommended before continuing!'); ?></h3>
			<p><?php echo lang('Before continuing, you have to backup your data. Please backup the database and backup the files of the application.'); ?></p>
			<p><?php echo lang('When your files and database are saving in an other support, please certify that your shop is really backed up.'); ?><br /><br /></p>
			<div id="disclaimerDivCertify">
				<input id="btDisclaimerOk" class="button" type="button" value="<?php echo lang('I certify'); ?>" />
			</div>
		</div>
		
		<div class="sheet" id="sheet_require_update">
			
			<h2><?php echo lang('System and permissions'); ?></h2>
			
			<h3><?php echo lang('Required set-up. Please make sure the following checklist items are true.'); ?></h3>
			
			<p>
				<?php echo lang('If you have any questions, please visit our '); ?>
				<a href="http://www.prestashop.com/doc/doku.php" target="_blank"><?php echo lang('Documentation Wiki'); ?></a>
				<?php echo lang('and/or'); ?>
				<a href="http://www.prestashop.com/forum/" target="_blank"><?php echo lang('Community Forum'); ?></a><?php echo lang('.'); ?>
			</p>
			
			<h3 id="resultConfig_update" style="font-size: 20px; text-align: center; padding: 0px; display: none;"></h3>
			<ul id="required_update">
				<li class="title"><?php echo lang('PHP parameters:')?></li>
				<li class="required"><?php echo lang('PHP 5.0 or later installed')?></li>
				<li class="required"><?php echo lang('File upload allowed')?></li>
				<li class="required"><?php echo lang('Create new files and folders allowed')?></li>
				<li class="required"><?php echo lang('GD Library installed')?></li>
				<li class="required"><?php echo lang('MySQL support is on')?></li>
				<li class="title"><?php echo lang('Write permissions on folders:')?></li>
				<li class="required">/config</li>
				<li class="required">/tools/smarty/compile</li>
				<li class="required">/sitemap.xml</li>
				<li class="title"><?php echo lang('Write permissions on folders (and subfolders):')?></li>
				<li class="required">/img</li>
				<li class="required">/mails</li>
				<li class="required">/modules</li>
				<li class="required">/themes/prestashop/lang</li>
				<li class="required">/translations</li>
			</ul>
			
			<h3><?php echo lang('Optional set-up')?></h3>
			<ul id="optional_update">
				<li class="title"><?php echo lang('PHP parameters:')?></li>
				<li class="optional"><?php echo lang('Open external URLs allowed')?></li>
				<li class="optional"><?php echo lang('PHP register global option is off (recommended)')?></li>
				<li class="optional"><?php echo lang('GZIP compression is on (recommended)')?></li>
			</ul>
			
			<p><input class="button" value="<?php echo lang('Refresh these settings'); ?>" type="button" id="req_bt_refresh_update"/></p>
			
		</div>
		
		<div class="sheet" id="sheet_updateErrors">
			<h2><?php echo lang('Error!'); ?></h2>			
			<h3><?php echo lang('One or more errors have occurred...'); ?></h3>
			<p id="resultUpdate"></p>
			<p id="detailsError"></p>
		</div>
		
		<div class="sheet" id="sheet_end_update" style="padding:0px;">
			<div style="padding:1em;">
				<h2><?php echo lang('PrestaShop is ready!'); ?></h2>
				<h3><?php echo lang('Your update is finished!'); ?></h3>
				<p class="fail" id="txtErrorUpdateSQL"></p>
				<p><a href="javascript:showUpdateLog()"><?php echo lang('view the log'); ?></a></p>
				<div id="updateLog"></div>
				<p><?php echo lang('You\'ve just updated and configured PrestaShop as your online shop solution. We wish you all the best with the success of your online shop.'); ?></p>
				<h3><?php echo lang('WARNING: For more security, you must delete the \'install\' folder and readme files (readme_fr.txt, readme_en.txt, readme_es.txt).'); ?></h3>
				<a href="../" id="access_update" target="_blank">
					<span class="title"><?php echo lang('Front Office'); ?></span>
					<span class="description"><?php echo lang('Find your store as your future customers will see!'); ?></span>
					<span class="message"><?php echo lang('Discover your store'); ?></span>
				</a>
			</div>
			<?php
			// Check if can contact prestastore.com
			if (@fsockopen('www.prestastore.com', 80, $errno, $errst, 3)):
			?>
			<iframe src="http://www.prestastore.com/psinstall.php?lang=<?php echo $lm->getIsoCodeSelectedLang()?>" scrolling="no" id="prestastore_update">
				<p>Your browser does not support iframes.</p>
			</iframe>
			<?php
			endif;
			?>
		</div>
	
</div>

<div id="buttons">
	<input id="btBack" class="button little disabled" type="button" value="<?php echo lang('Back'); ?>" disabled="disabled"/>
	<input id="btNext" class="button little" type="button" value="<?php echo lang('Next'); ?>" />
</div>

</div>
<ul id="footer">
	<li><a href="http://www.prestashop.com/forum/" title="<?php echo lang('Official forum'); ?>"><?php echo lang('Official forum'); ?></a> | </li>
	<li><a href="http://www.prestashop.com" title="PrestaShop.com">PrestaShop.com</a> | </li>
	<li><a href="http://www.prestashop.com/contact.php" title="<?php echo lang('Contact us'); ?>"><?php echo lang('Contact us'); ?></a> | </li>
	<li>&copy; 2005-<?php echo date('Y'); ?></li>
</ul>
</body>
</html>
