<?php

require_once(_PS_SMARTY_DIR_.'Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir 	= _PS_THEME_DIR_.'tpl';
$smarty->compile_dir 	= _PS_SMARTY_DIR_.'compile';
$smarty->cache_dir 		= _PS_SMARTY_DIR_.'cache';
$smarty->config_dir 	= _PS_SMARTY_DIR_.'configs';
$smarty->caching 		= false;
$smarty->force_compile	= false;
//$smarty->debugging		= true;
$smarty->debug_tpl		= _PS_ALL_THEMES_DIR_ . 'debug.tpl';

function smartyTranslate($params, &$smarty)
{
	/*
	 * Warning : 2 lines have been added to the Smarty class.
	 * "public $currentTemplate = null;" into the class itself
	 * "$this->currentTemplate = substr(basename($resource_name), 0, -4);" into the "display" method
	 */
	global $_LANG, $_MODULES, $cookie, $_MODULE;
	if (!isset($params['js'])) $params['js'] = 0;
	if (!isset($params['mod'])) $params['mod'] = false;
	$msg = false;

	$string = str_replace('\'', '\\\'', $params['s']);
	$key = $smarty->currentTemplate.'_'.md5($string);
	if ($params['mod'])
	{
		if (file_exists(_PS_THEME_DIR_.'modules/'.$params['mod'].'/'.Language::getIsoById($cookie->id_lang).'.php'))
		{
			$translationsFile = _PS_THEME_DIR_.'modules/'.$params['mod'].'/'.Language::getIsoById($cookie->id_lang).'.php';
			$modKey = '<{'.$params['mod'].'}'._THEME_NAME_.'>'.$key;
		}
		else
		{
			$translationsFile = _PS_MODULE_DIR_.$params['mod'].'/'.Language::getIsoById($cookie->id_lang).'.php';
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
$smarty->register_function('l', 'smartyTranslate');

function smartyDieObject($params, &$smarty)
{
	return Tools::d($params['var']);
}
$smarty->register_function('d', 'smartyDieObject');

function smartyShowObject($params, &$smarty)
{
	return Tools::p($params['var']);
}
$smarty->register_function('p', 'smartyShowObject');

function smartyMaxWords($params, &$smarty)
{
	$params['s'] = str_replace('...', ' ...', html_entity_decode($params['s'], ENT_QUOTES, 'UTF-8'));
	$words = explode(' ', $params['s']);
	
	foreach($words AS &$word)
		if(strlen($word) > $params['n'])
			$word = substr(trim(chunk_split($word, $params['n']-1, '- ')), 0, -1);

	return implode(' ',  Tools::htmlentitiesUTF8($words));
}

$smarty->register_function('m', 'smartyMaxWords');

function smartyTruncate($params, &$smarty)
{
	$text = isset($params['strip']) ? strip_tags($params['text']) : $params['text'];
	$length = $params['length'];
	$sep = isset($params['sep']) ? $params['sep'] : '...';

	if (Tools::strlen($text) > $length + Tools::strlen($sep))
		$text = substr($text, 0, $length).$sep;

	return (isset($params['encode']) ? Tools::htmlentitiesUTF8($text, ENT_NOQUOTES) : $text);
}

$smarty->register_function('t', 'smartyTruncate');

global $link;

$link = new Link();
$smarty->assign('link', $link);

?>
