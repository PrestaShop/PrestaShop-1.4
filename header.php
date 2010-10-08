<?php

// P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

require_once(dirname(__FILE__).'/init.php');


/* Hooks are volontary out the initialize array (need those variables already assigned) */
$smarty->assign(array(
	'HOOK_HEADER' => Module::hookExec('header'),
	'HOOK_LEFT_COLUMN' => Module::hookExec('leftColumn'),
	'HOOK_TOP' => Module::hookExec('top'),
	'static_token' => Tools::getToken(false),
	'token' => Tools::getToken(),
	'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
	'content_only' => intval(Tools::getValue('content_only'))
));


if (is_writable(_PS_THEME_DIR_.'cache'))
{
	// CSS compressor management
	if (Configuration::get('PS_CSS_THEME_CACHE'))
		Tools::cccCss();

	//JS compressor management
	if (Configuration::get('PS_JS_THEME_CACHE'))
		Tools::cccJs();
}

$smarty->assign('css_files', $css_files);

$smarty->assign('js_files', $js_files);

$smarty->display(_PS_THEME_DIR_.'header.tpl');

