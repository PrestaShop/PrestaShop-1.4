<?php

/**
  * Information tab for admin panel, AdminInformation.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminInformation extends AdminTab
{
	private function _getTestResultHtml()
	{
		$html = '';
		// Functions list to test with 'test_system'
		$funcs = array('fopen', 'fclose', 'fread', 'fwrite', 'rename', 'file_exists', 'unlink', 'rmdir', 'mkdir', 'getcwd', 'chdir', 'chmod');
		
		// Test list to execute (function/args)
		$tests = array(
			'phpversion' => false,
			'upload' => false,
			'system' => $funcs,
			'gd' => false,
			'mysql_support' => false,
			'config_dir' => PS_ADMIN_DIR.'/../config/',
			'tools_dir' => PS_ADMIN_DIR.'/../tools/smarty/compile',
			'sitemap' => PS_ADMIN_DIR.'/../sitemap.xml',
			'img_dir' => PS_ADMIN_DIR.'/../img/',
			'mails_dir' => PS_ADMIN_DIR.'/../mails/',
			'module_dir' => PS_ADMIN_DIR.'/../modules/',
			'theme_lang_dir' => PS_ADMIN_DIR.'/../themes/prestashop/lang/',
			'translations_dir' => PS_ADMIN_DIR.'/../translations/',
			'customizable_products_dir' => PS_ADMIN_DIR.'/../upload/',
			'virtual_products_dir' => PS_ADMIN_DIR.'/../download/'
		);

		$tests_op = array(
			'fopen' => false,
			'register_globals' => false,
			'gz' => false
		);

		$testsErrors = array(
			'phpversion' => $this->l('Update your PHP version'),
			'upload' => $this->l('Configure your server for allow the upload file'),
			'system' => $this->l('Configure your server for allow the creation of directories and write on file'),
			'gd' => $this->l('Active the GD library on your server'),
			'mysql_support' => $this->l('Active the MySQL support on your server'),
			'config_dir' => $this->l('Set write permissions on config folder'),
			'tools_dir' => $this->l('Set write permissions on tools folder'),
			'sitemap' => $this->l('Set write permissions on sitemap.xml file'),
			'img_dir' => $this->l('Set write permissions on img folder and subfolders/recursively'),
			'mails_dir' => $this->l('Set write permissions on mails folder and subfolders/recursively'),
			'module_dir' => $this->l('Set write permissions on modules folder and subfolders/recursively'),
			'theme_lang_dir' => $this->l('Set write permissions on theme/prestashop/lang/ folder and subfolders/recursively'),
			'translations_dir' => $this->l('Set write permissions on translations folder and subfolders/recursively'),
			'customizable_products_dir' => $this->l('Set write permissions on upload folder and subfolders/recursively'),
			'virtual_products_dir' => $this->l('Set write permissions on download folder and subfolders/recursively'),
			'fopen' => $this->l('Active fopen on your server'),
			'register_globals' => $this->l('Set PHP register global option at off'),
			'gz' => $this->l('Active GZIP compression on your server')
		);

		$paramsRequiredResults = self::check($tests);
		$paramsOptionalResults = self::check($tests_op);

		$html .= '
			<p>
				<b>'.$this->l('Required parameters').':</b>';
		if (!in_array('fail', $paramsRequiredResults))
				$html .= ' <span style="color:green;font-weight:bold;">OK</span>
			</p>
			';
		else
		{
			$html .= ' <span style="color:red">'.$this->l('Please consult the following error(s)').'</span>
			</p>
			<ul>
			';
			foreach ($paramsRequiredResults AS $key => $value)
				if ($value == 'fail')
					$html .= '<li>'.$testsErrors[$key].'</li>';
			$html .= '</ul>';
		}
		
		$html .= '
			<p>
				<b>'.$this->l('Optional parameters').':</b>';
		if (!in_array('fail', $paramsOptionalResults))
				$html .= ' <span style="color:green;font-weight:bold;">OK</span>
			</p>
			';
		else
		{
			$html .= ' <span style="color:red">'.$this->l('Please consult the following error(s)').'</span>
			</p>
			<ul>
			';
			foreach ($paramsOptionalResults AS $key => $value)
				if ($value == 'fail')
					$html .= '<li>'.$testsErrors[$key].'</li>';
			$html .= '</ul>';
		}
		
		return $html;
	}
	
	public function display()
	{
		global $currentIndex;
		
		echo '
		<h2>'.$this->l('Informations').'</h2>
		<fieldset>
			<legend><img src="../img/t/AdminInformation.gif" alt="" /> '.$this->l('Help').'</legend>
			<p>'.$this->l('These informations must be indicated when you report a bug on our bug tracker or if you report a problem on our forum.').'</p>
		</fieldset>
		<br />
		<fieldset>
			<legend><img src="../img/t/AdminInformation.gif" alt="" /> '.$this->l('Informations about your configuration').'</legend>
			<h3>'.$this->l('Server informations').'</h3>
			<p>
				<b>'.$this->l('Prestashop Version').':</b> 
				'._PS_VERSION_.'
			</p>
			<p>
				<b>'.$this->l('Server informations').':</b> 
				'.php_uname('s').' '.php_uname('v').' '.php_uname('m').'
			</p>
			<p>
				<b>'.$this->l('Server software Version').':</b> 
				'.$_SERVER['SERVER_SOFTWARE'].'
			</p>
			<p>
				<b>'.$this->l('PHP Version').':</b> 
				'.phpversion().'
			</p>
			<p>
				<b>'.$this->l('MySQL Version').':</b> 
				'.mysql_get_server_info().'
			</p>
			<hr />
			<h3>'.$this->l('Store informations').'</h3>
			<p>
				<b>'.$this->l('URL of your website').':</b> 
				'.Tools::getHttpHost(true).__PS_BASE_URI__.'
			</p>
			<p>
				<b>'.$this->l('Theme name used').':</b> 
				'._THEME_NAME_.'
			</p>
			<hr />
			<h3>'.$this->l('Mail informations').'</h3>
			<p>
				<b>'.$this->l('Mail method').':</b>
		';
		if (Configuration::get('PS_MAIL_METHOD') == 1)
			echo $this->l('You use PHP mail() function.').'</p>';
		else
		{
			echo $this->l('You use your own SMTP parameters').'</p>';
			echo '
			<p>
				<b>'.$this->l('SMTP server').':</b> 
				'.Configuration::get('PS_MAIL_SERVER').'
			</p>
			<p>
				<b>'.$this->l('SMTP user').':</b> 
				'.(Configuration::get('PS_MAIL_USER') ? $this->l('Defined') : '<span style="color:red;">'.$this->l('Not defined').'</span>').'
			</p>
			<p>
				<b>'.$this->l('SMTP password').':</b> 
				'.(Configuration::get('PS_MAIL_PASSWD') ? $this->l('Defined') : '<span style="color:red;">'.$this->l('Not defined').'</span>').'
			</p>
			<p>
				<b>'.$this->l('Encryption').':</b> 
				'.Configuration::get('PS_MAIL_SMTP_ENCRYPTION').'
			</p>
			<p>
				<b>'.$this->l('Port').':</b> 
				'.Configuration::get('PS_MAIL_SMTP_PORT').'
			</p>
			';
		}
		echo '
			<hr />
			<h3>'.$this->l('Your informations').'</h3>
			<p>
				<b>'.$this->l('Informations from you').':</b> 
				'.$_SERVER["HTTP_USER_AGENT"].'
			</p>
		</fieldset>
		<br />
		<fieldset id="checkConfiguration">
			<legend><img src="../img/t/AdminInformation.gif" alt="" /> '.$this->l('Check your configuration').'</legend>
			'.self::_getTestResultHtml().'
		</fieldset>
		';
	}
	
	static private function		check($tests)
	{
		$res = array();
		foreach ($tests AS $key => $test)
			$res[$key] = self::run($key, $test);
		return $res;
	}
	
	static private function		run($ptr, $arg = 0)
	{
		if (call_user_func(array('self', 'test_'.$ptr), $arg))
			return ('ok');
		return ('fail');
	}
	
	// Misc functions	
	static private function		test_phpversion()
	{
		return version_compare(substr(phpversion(), 0, 3), '5.0', '>=');
	}
	
	static private function		test_mysql_support()
	{
		return function_exists('mysql_connect');
	}

	static private function		test_upload()
	{
		return  ini_get('file_uploads');
	}

	static private function		test_fopen()
	{
		return ini_get('allow_url_fopen');
	}

	static private function		test_system($funcs)
	{
		foreach ($funcs AS $func)
			if (!function_exists($func))
				return false;
		return true;
	}

	static private function		test_gd()
	{
		return function_exists('imagecreatetruecolor');
	}
	
	static private function		test_register_globals()
	{
		return !ini_get('register_globals');
	}
	
	static private function		test_gz()
	{
		if (function_exists('gzencode'))
			return !(@gzencode('dd') === false); 
		return false;
	}
	
	// is_writable dirs	
	static private function		test_dir($dir, $recursive = false)
	{
		if (!is_writable($dir) OR !$dh = opendir($dir))
			return false;
		if ($recursive)
		{
			while (($file = readdir($dh)) !== false)
				if (@filetype($dir.$file) == 'dir' AND $file != '.' AND $file != '..')
					if (!self::test_dir($dir.$file, true))
						return false;
		}
		closedir($dh);
		return true;
	}
	
	// is_writable files	
	static private function		test_file($file)
	{
		return (file_exists($file) AND is_writable($file));
	}
	
	static private function		test_config_dir($dir)
	{
		return self::test_dir($dir);
	}
	
	static private function		test_sitemap($dir)
	{
		return self::test_file($dir);
	}
	
	static private function		test_root_dir($dir)
	{
		return self::test_dir($dir);
	}

	static private function		test_admin_dir($dir)
	{
		return self::test_dir($dir);
	}
	
	static private function		test_img_dir($dir)
	{
		return self::test_dir($dir, true);
	}
	
	static private function		test_module_dir($dir)
	{
		return self::test_dir($dir, true);
	}
	
	static private function		test_tools_dir($dir)
	{
		return self::test_dir($dir);
	}
	
	static private function		test_download_dir($dir)
	{
		return self::test_dir($dir);
	}
	
	static private function		test_mails_dir($dir)
	{
		return self::test_dir($dir, true);
	}
	
	static private function		test_translations_dir($dir)
	{
		return self::test_dir($dir, true);
	}
	
	static private function		test_theme_lang_dir($dir)
	{
		return self::test_dir($dir, true);
	}

	static private function		test_customizable_products_dir($dir)
	{
		return self::test_dir($dir);
	}
	
	static private function		test_virtual_products_dir($dir)
	{
		return self::test_dir($dir);
	}
}