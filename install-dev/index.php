<?php

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('Europe/Paris');

/* Redefine REQUEST_URI if empty (on some webservers...) */
if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '')
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
if ($tmp = strpos($_SERVER['REQUEST_URI'], '?'))
	$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $tmp);

define('INSTALL_VERSION', '1.1.0.5');
define('MINIMUM_VERSION_TO_UPDATE', '0.8.5');
define('INSTALL_PATH', dirname(__FILE__));
define('PS_BASE_URI', substr($_SERVER['REQUEST_URI'], 0, -1 * (strlen($_SERVER['REQUEST_URI']) - strrpos($_SERVER['REQUEST_URI'], '/')) - strlen(substr(substr($_SERVER['REQUEST_URI'],0,-1), strrpos( substr($_SERVER['REQUEST_URI'],0,-1),"/" )+1))));
define('PS_BASE_URI_ABSOLUTE', 'http://'.htmlspecialchars($_SERVER["HTTP_HOST"], ENT_COMPAT, 'UTF-8').PS_BASE_URI);

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
	<title><?php echo lang('PrestaShop '.INSTALL_VERSION.' Installer')?></title>
	<link rel="stylesheet" type="text/css" media="all" href="view.css"/>
	<script type="text/javascript" src="../js/jquery/jquery-1.2.6.pack.js"></script>
	<script type="text/javascript" src="../js/jquery/ajaxfileupload.js"></script>
	<script type="text/javascript" src="../js/jquery/jquery.pngFix.pack.js"></script>
	<script type="text/javascript" src="../js/jquery/jqminmax-compressed.js"></script>
	<link rel="shortcut icon" href="../img/favicon.ico" />
	
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
		
		var txtError = new Array();
		txtError[0] = "<?php echo lang('Required field'); ?>";
		txtError[1] = "<?php echo lang('Too long!'); ?>";
		txtError[2] = "<?php echo lang('Fields are different!'); ?>";
		txtError[3] = "<?php echo lang('This email adress is wrong!'); ?>";
		txtError[4] = "<?php echo lang('Impossible to send the email!'); ?>";
		txtError[5] = "<?php echo lang('Can\'t create settings file, if /config/settings.inc.php exists, please give the public write permissions to this file, else please create a file named settings.inc.php in config directory.'); ?>";
		txtError[6] = "<?php echo lang('Can\'t write settings file, please create a file named settings.inc.php in config directory.'); ?>";
		txtError[7] = "<?php echo lang('Impossible to upload the file!'); ?>";
		txtError[8] = "<?php echo lang('Data integrity is not valided. Hack attempt ?'); ?>";
		txtError[9] = "<?php echo lang('Impossible the read the content of a MySQL content file.'); ?>";
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
		txtError[999] = "<?php echo lang('No error code available.'); ?>";
		//upgrader
		txtError[27] = "<?php echo lang('This installer is too old.'); ?>";
		txtError[28] = "<?php echo lang('You already have the '.INSTALL_VERSION.' version.'); ?>";
		txtError[29] = "<?php echo lang('There is no older version. Did you delete or rename the config/settings.inc.php file ?'); ?>";
		txtError[30] = "<?php echo lang('The config/settings.inc.php file was not found. Did you delete or rename this file ?'); ?>";
		txtError[31] = "<?php echo lang('Can\'t find the sql upgrade files. Please verify that the /install/sql/upgrade folder is not empty)'); ?>";
		txtError[32] = "<?php echo lang('No upgrade is possible.'); ?>";
		txtError[33] = "<?php echo lang('Error while loading sql upgrade file.'); ?>";
		txtError[34] = "<?php echo lang('Error while inserting content into the database'); ?>";
		txtError[35] = "<?php echo lang('Unfortunately,'); ?>";
		txtError[36] = "<?php echo lang('SQL errors have occurred.'); ?>";
		txtError[37] = "<?php echo lang('Impossible to copy languages\'s flags.'); ?>";

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
		
		<h3><?php echo lang('Choose the installer language')?> :</h3>
		<form id="formSetInstallerLanguage" action="<?php $_SERVER['REQUEST_URI']; ?>" method="get">
			<ul id="langList">
			<?php foreach ($lm->getAvailableLangs() as $lang):?>
				<li><input onclick="setInstallerLanguage()" type="radio" value="<?php echo $lang['id'] ?>" <?php echo ( $lang['id'] == $lm->getIdSelectedLang() ) ? "checked=\"checked\"" : '' ?> id="lang_<?php echo $lang['id'] ?>" name="language"/><label for="lang_<?php echo $lang['id'] ?>">
				<?php foreach ($lang->flags->url as $url_flag):?>
					<img src="<?php echo $url_flag ?>" alt="<?php echo $lang['label'] ?>"/>
				<?php endforeach ?>
				<?php echo $lang['label'] ?></label></li>
				
			<?php endforeach?>
			</ul>
		</form>
		
		<h3><?php echo lang('Installation method')?></h3>
		<form id="formSetMethod" action="<?php $_SERVER['REQUEST_URI']; ?>" method="post">
			<p><input <?php echo (!($oldversion AND !$tooOld AND !$sameVersions)) ? 'checked="checked"' : '' ?> type="radio" value="install" name="typeInstall" id="typeInstallInstall"/><label for="typeInstallInstall"><?php echo lang('Installation : complete install of the PrestaShop Solution')?></label></p>
			<p <?php echo ($oldversion AND !$tooOld AND !$sameVersions) ? '' : 'class="disabled"'; ?>><input <?php echo ($oldversion AND !$tooOld AND !$sameVersions) ? 'checked="checked"' : 'disabled="disabled"'; ?> type="radio" value="upgrade" name="typeInstall" id="typeInstallUpgrade"/><label <?php echo ($oldversion === false) ? 'class="disabled"' : ''; ?> for="typeInstallUpgrade"><?php echo lang('Upgrade : get the latest stable version!')?> <?php echo ($oldversion === false) ? lang('(no old version detected)') : ("(".(  ($tooOld) ? lang('the already installed version detected is too old, no more update available') : lang('installed version detected').' : '.$oldversion    ).")") ?></label></p>
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
			
			<p><?php echo lang('Configure your database by filling out the following fields')?> :</p>
			<form id="formCheckSQL" class="aligned" action="<?php $_SERVER['REQUEST_URI']; ?>" onsubmit="verifyDbAccess(); return false;" method="post">
				<p>
					<label for="dbServer"><?php echo lang('Server')?> : </label>
					<input size="25" class="text" type="text" id="dbServer" value="localhost"/>
				</p>
				<p>
					<label for="dbName"><?php echo lang('Database name')?> : </label>
					<input size="10" class="text" type="text" id="dbName" value="prestashop"/>
				</p>
				<p>
					<label for="dbLogin"><?php echo lang('Login')?> : </label>
					<input class="text" size="10" type="text" id="dbLogin" value="root"/>
				</p>
				<p>
					<label for="dbPassword"><?php echo lang('Password')?> : </label>
					<input class="text" autocomplete="off" size="10" type="password" id="dbPassword"/>
				</p>
				<p class="aligned">
					<input id="btTestDB" class="button" type="submit" value="<?php echo lang('Verify now!')?>"/>
				</p>
				<p id="dbResultCheck"></p>
			</form>
			
			<div id="dbTableParam">
				<form action="#" method="post" onsubmit="createDB(); return false;">
				<p><label for="db_prefix"><?php echo lang('Tables prefix')?> : </label><input class="text" type="text" id="db_prefix" value="ps_"/></p>
				<p id="dbModeSetter">
					<input value="full" type="radio" name="db_mode" checked="checked" id="db_mode_complet"/><label for="db_mode_complet"><?php echo lang('Full mode : Install the main modules and add sample products')?></label><br/>
					<input value="lite" type="radio" name="db_mode" id="db_mode_simple"/><label for="db_mode_simple"><?php echo lang('Simple mode : Don\'t install any module')?></label>
				</p>
				</form>
				<p id="dbCreateResultCheck"></p>
			</div>
			<div id="mailPart">
				<h2><?php echo lang('E-mail delivery set-up')?></h2>
				
				<p>
					<input type="checkbox" id="set_stmp"/>
					<label for="set_stmp"><?php echo lang('Configure SMTP manually (advanced users only)'); ?></label><br/>
					<span class="userInfos"><?php echo lang('By default, the PHP \'mail()\' function is used'); ?></span>
				</p>
				
				<div id="mailSMTPParam">
					<form class="aligned" action="#" method="post" onsubmit="verifyMail(); return false;">
						<p>
							<label for="smtpSrv"><?php echo lang('SMTP server'); ?> : </label>
							<input class="text" type="text" id="smtpSrv" value="smtp."/>
						</p>
						<p>
							<label for="smtpEnc"><?php echo lang('Encryption'); ?> :</label>
							<select id="smtpEnc">
								<option value="off" selected="selected"><?php echo lang('None'); ?></option>
								<option value="tls">TLS</option>
								<option value="ssl">SSL</option>
							</select>
						</p>
						
						<p>
							<label for="smtpPort"><?php echo lang('Port'); ?> :</label>
							<input type="text" size="5" id="smtpPort" value="25" />
						</p>
						
						<p>
							<label for="smtpLogin"><?php echo lang('Login'); ?> : </label>
							<input class="text" type="text" size="10" id="smtpLogin" value="" />
						</p>

						<p>
							<label for="smtpPassword"><?php echo lang('Password'); ?> : </label>
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
					<label for="infosShop" class="aligned"><?php echo lang('Shop name'); ?> : </label><input class="text required" type="text" id="infosShop" value=""/><br/>
					<span id="resultInfosShop" class="result aligned"></span>
				</div>
				<div class="field">
					<label for="infosLogo" class="aligned logo"><?php echo lang('Shop logo'); ?> : </label>
					<input type="file" onchange="uploadLogo()" name="fileToUpload" id="fileToUpload"/>
					<span id="resultInfosLogo" class="result"></span>
					<p class="userInfos aligned"><?php echo lang('recommended dimensions : 230px X 75px'); ?></p>
					<p id="alignedLogo"><img id="uploadedImage" src="<?php echo PS_BASE_URI ?>img/logo.jpg" alt="Logo" /></p>
				</div>
				
				<div class="field">
					<label for="infosFirstname" class="aligned"><?php echo lang('First name'); ?> : </label><input class="text required" type="text" id="infosFirstname"/><br/>
					<span id="resultInfosFirstname" class="result aligned"></span>
				</div>
				
				<div class="field">
					<label for="infosName" class="aligned"><?php echo lang('Last name'); ?> : </label><input class="text required" type="text" id="infosName"/><br/>
					<span id="resultInfosName" class="result aligned"></span>
				</div>
				
				<div class="field">
					<label for="infosEmail" class="aligned"><?php echo lang('E-mail address'); ?> : </label><input type="text" class="text required" id="infosEmail"/><br/>
					<span id="resultInfosEmail" class="result aligned"></span>
				</div>
				
				<div class="field">
					<label for="infosPassword" class="aligned"><?php echo lang('Shop password'); ?> : </label><input autocomplete="off" type="password" class="text required" id="infosPassword"/><br/>
					<span id="resultInfosPassword" class="result aligned"></span>
				</div>
				<div class="field">
					<label class="aligned" for="infosPasswordRepeat"><?php echo lang('Re-type to confirm'); ?> : </label><input type="password" autocomplete="off" class="text required" id="infosPasswordRepeat"/><br/>
					<span id="resultInfosPasswordRepeat" class="result aligned"></span>
				</div>
				
				<div class="field">
					<input type="checkbox" id="infosNotification" class="aligned"/><label for="infosNotification"><?php echo lang('Receive notifications by e-mail'); ?></label><br/>
					<span id="resultInfosNotification" class="result aligned"></span>
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
		
		<div class="sheet" id="sheet_end">
			<h2><?php echo lang('PrestaShop is ready!'); ?></h2>			
			<h3><?php echo lang('Your installation is finished !'); ?></h3>
			<p><?php echo lang('You\'ve just installed and configured PrestaShop as your online shop solution. We wish you all the best with the success of your online shop.'); ?></p>
			<p><?php echo lang('Here are your shop information. You can modify them once logged in.'); ?></p>
			<p id="resultInstall">
			<?php echo lang('Shop name'); ?>: <span id="endShopName"></span><br/>
			<?php echo lang('First name'); ?> : <span id="endFirstName"></span><br/>
			<?php echo lang('Last name'); ?> : <span id="endName"></span><br/>
			<?php echo lang('E-mail'); ?> : <span id="endEmail"></span>
			</p>
			<h3><?php echo lang('WARNING : For more security, you must delete the \'install\' folder.'); ?></h3>
			<p><?php echo lang('And now, discover your new store and Back Office'); ?>:</p>
			<ul>
				<li><a id="endFO" href="../"><?php echo lang('My shop'); ?></a></li>
				<li><a id="endBO" href="../admin"><?php echo lang('My Back Office'); ?></a></li>
			</ul>
		</div>
		
		<div class="sheet" id="sheet_disclaimer">
			<h2><?php echo lang('Disclaimer'); ?></h2>			
			<h3><?php echo lang('Warning : a manual backup is HIGHLY recommended before continuing!'); ?></h3>
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
			<h2><?php echo lang('Error !'); ?></h2>			
			<h3><?php echo lang('One or more errors have occurred...'); ?></h3>
			<p id="resultUpdate"></p>
			<p id="detailsError"></p>
		</div>
		
		<div class="sheet" id="sheet_end_update">
			<h2><?php echo lang('PrestaShop is ready!'); ?></h2>
			<h3><?php echo lang('Your update is finished !'); ?></h3>
			<p class="fail" id="txtErrorUpdateSQL"></p>
			<p><a href="javascript:showUpdateLog()"><?php echo lang('view the log'); ?></a></p>
			<div id="updateLog"></div>
			<p><?php echo lang('You\'ve just updated and configured PrestaShop as your online shop solution. We wish you all the best with the success of your online shop.'); ?></p>
			<h3><?php echo lang('WARNING : For more security, you must delete the \'install\' folder.'); ?></h3>
			<ul>
				<li><a href="../"><?php echo lang('My shop'); ?></a></li>
			</ul>
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
