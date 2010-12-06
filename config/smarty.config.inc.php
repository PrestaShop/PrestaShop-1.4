<?php
require_once(_PS_SMARTY_DIR_.'Smarty.class.php');
global $smarty;
$smarty = new Smarty();
$smarty->template_dir = _PS_THEME_DIR_.'tpl';
$smarty->compile_dir = _PS_SMARTY_DIR_.'compile';
$smarty->cache_dir = _PS_SMARTY_DIR_.'cache';
$smarty->config_dir = _PS_SMARTY_DIR_.'configs';
$smarty->caching = (bool)Configuration::get('PS_SMARTY_CACHE');
$smarty->force_compile = (bool)Configuration::get('PS_SMARTY_FORCE_COMPILE');
$smarty->compile_check = false;
//$smarty->debugging		= true;
$smarty->debug_tpl = _PS_ALL_THEMES_DIR_.'debug.tpl';

function smartyTranslate($params, &$smarty)
{
	/*
	 * Warning in Smarty-v2 : 2 lines have been added to the Smarty class.
	 * "public $currentTemplate = null;" into the class itself
	 * "$this->currentTemplate = substr(basename($resource_name), 0, -4);" into the "display" method
	 *
	 * In Smarty-v3 : No modifications, using the existing var $this->smarty->_current_file instead
	 */
	global $_LANG, $_MODULES, $cookie, $_MODULE;
	if (!isset($params['js'])) $params['js'] = 0;
	if (!isset($params['mod'])) $params['mod'] = false;
	
	$msg = false;
	$string = str_replace('\'', '\\\'', $params['s']);
	
	if (_PS_FORCE_SMARTY_2_) /* Keep a backward compatibility for Smarty v2 */
		$key = $smarty->currentTemplate.'_'.md5($string);
	else
	{
		$filename = ((!isset($smarty->compiler_object) OR !is_object($smarty->compiler_object->template)) ? $smarty->template_filepath : $smarty->compiler_object->template->getTemplateFilepath());
		$key = substr(basename($filename), 0, -4).'_'.md5($string);
	}
	
	if ($params['mod'])
	{
		$iso = Language::getIsoById($cookie->id_lang);

		if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$params['mod'].'/'.$iso.'.php'))
		{
			$translationsFile = _PS_THEME_DIR_.'modules/'.$params['mod'].'/'.$iso.'.php';
			$modKey = '<{'.$params['mod'].'}'._THEME_NAME_.'>'.$key;
		}
		else
		{
			$translationsFile = _PS_MODULE_DIR_.$params['mod'].'/'.$iso.'.php';
			$modKey = '<{'.$params['mod'].'}prestashop>'.$key;
		}

		if (@include_once($translationsFile))
			$_MODULES = array_merge($_MODULES, $_MODULE);

		$msg = (is_array($_MODULES) AND key_exists($modKey, $_MODULES)) ? ($params['js'] ? addslashes($_MODULES[$modKey]) : stripslashes($_MODULES[$modKey])) : false;
	}
	if (!$msg)
		$msg = (is_array($_LANG) AND key_exists($key, $_LANG)) ? ($params['js'] ? addslashes($_LANG[$key]) : stripslashes($_LANG[$key])) : $params['s'];
	return ($params['js'] ? $msg : Tools::htmlentitiesUTF8($msg));
}

function smartyDieObject($params, &$smarty)
{
	return Tools::d($params['var']);
}

function smartyShowObject($params, &$smarty)
{
	return Tools::p($params['var']);
}

function smartyMaxWords($params, &$smarty)
{
	$params['s'] = str_replace('...', ' ...', html_entity_decode($params['s'], ENT_QUOTES, 'UTF-8'));
	$words = explode(' ', $params['s']);
	
	foreach($words AS &$word)
		if(strlen($word) > $params['n'])
			$word = substr(trim(chunk_split($word, $params['n']-1, '- ')), 0, -1);

	return implode(' ',  Tools::htmlentitiesUTF8($words));
}

function smartyTruncate($params, &$smarty)
{
	$text = isset($params['strip']) ? strip_tags($params['text']) : $params['text'];
	$length = $params['length'];
	$sep = isset($params['sep']) ? $params['sep'] : '...';

	if (Tools::strlen($text) > $length + Tools::strlen($sep))
		$text = substr($text, 0, $length).$sep;

	return (isset($params['encode']) ? Tools::htmlentitiesUTF8($text, ENT_NOQUOTES) : $text);
}

function smarty_modifier_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false, $charset = 'UTF-8')
{
	if (!$length)
		return '';
 
	if (Tools::strlen($string) > $length)
	{
		$length -= min($length, Tools::strlen($etc));
		if (!$break_words && !$middle)
			$string = preg_replace('/\s+?(\S+)?$/u', '', Tools::substr($string, 0, $length+1, $charset));
		return !$middle ? Tools::substr($string, 0, $length, $charset).$etc : Tools::substr($string, 0, $length/2, $charset).$etc.Tools::substr($string, -$length/2, $charset);
	}
	else
		return $string;
}

/* Use Smarty 3 API calls */
if (!_PS_FORCE_SMARTY_2_) /* PHP version > 5.1.2 */
{
	$smarty->registerPlugin('modifier', 'truncate', 'smarty_modifier_truncate');
	$smarty->registerPlugin('modifier', 'secureReferrer', array('Tools', 'secureReferrer'));
	$smarty->registerPlugin('function', 't', 'smartyTruncate');
	$smarty->registerPlugin('function', 'm', 'smartyMaxWords');
	$smarty->registerPlugin('function', 'p', 'smartyShowObject');
	$smarty->registerPlugin('function', 'd', 'smartyDieObject');
	$smarty->registerPlugin('function', 'l', 'smartyTranslate');
	spl_autoload_register('__autoload');
}
/* or keep a backward compatibility if PHP version < 5.1.2 */
else
{
	$smarty->register_modifier('truncate', 'smarty_modifier_truncate');
	$smarty->register_modifier('secureReferrer', array('Tools', 'secureReferrer'));
	$smarty->register_function('t', 'smartyTruncate');
	$smarty->register_function('m', 'smartyMaxWords');
	$smarty->register_function('p', 'smartyShowObject');
	$smarty->register_function('d', 'smartyDieObject');
	$smarty->register_function('l', 'smartyTranslate');
}

function smartyMinifyHTML($tpl_output, &$smarty)
{
    $tpl_output = Tools::minifyHTML($tpl_output);
    return $tpl_output;
}
if (Configuration::get('PS_HTML_THEME_COMPRESSION'))
	$smarty->register_outputfilter('smartyMinifyHTML');

function smartyPackJSinHTML($tpl_output, &$smarty)
{
    $tpl_output = Tools::packJSinHTML($tpl_output);
    return $tpl_output;
}
if (Configuration::get('PS_JS_HTML_THEME_COMPRESSION'))
	$smarty->register_outputfilter('smartyPackJSinHTML');

?>