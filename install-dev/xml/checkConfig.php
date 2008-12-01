<?php
include_once(INSTALL_PATH.'/classes/ConfigurationTest.php');

// Functions list to test with 'test_system'
$funcs = array('fopen', 'fclose', 'fread', 'fwrite', 'rename', 'file_exists', 'unlink', 'rmdir', 'mkdir', 'getcwd', 'chdir', 'chmod');

// Test list to execute (function/args)
$tests = array(
	'phpversion' => false,
	'upload' => false,
	'system' => $funcs,
	'gd' => false,
	'mysql_support' => false,
	'config_dir' => INSTALL_PATH.'/../config/',
	'tools_dir' => INSTALL_PATH.'/../tools/smarty/compile',
	'sitemap' => INSTALL_PATH.'/../sitemap.xml',
	'img_dir' => INSTALL_PATH.'/../img/',
	'mails_dir' => INSTALL_PATH.'/../mails/',
	'module_dir' => INSTALL_PATH.'/../modules/',
	'theme_lang_dir' => INSTALL_PATH.'/../themes/prestashop/lang/',
	'translations_dir' => INSTALL_PATH.'/../translations/',
	'customizable_products_dir' => INSTALL_PATH.'/../upload/',
	'virtual_products_dir' => INSTALL_PATH.'/../download/',
);
$tests_op = array(
	'fopen' => false,
	'register_globals' => false,
	'gz' => false,
);

// Execute tests
$res = ConfigurationTest::check($tests);
$res_op = ConfigurationTest::check($tests_op);

// Building XML Tree...
echo '<config>'."\n";
	echo '<testList id="required">'."\n";
	foreach ($res AS $key => $line)
		echo '<test id="'.$key.'" result="'.$line.'"/>'."\n";
	echo '</testList>'."\n";
	echo '<testList id="optional">'."\n";
	foreach ($res_op AS $key => $line)
		echo '<test id="'.$key.'" result="'.$line.'"/>'."\n";
	echo '</testList>'."\n";
echo '</config>';
?>