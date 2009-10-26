<?php

include_once(dirname(__FILE__).'/../config/config.inc.php');
$cookie = new Cookie('psAdmin');

$module = Tools::getValue('module');
$render = Tools::getValue('render');
$type = Tools::getValue('type');
$option = Tools::getValue('option');
$width = intval(Tools::getValue('width', 600));
$height = intval(Tools::getValue('height', 920));
$start = intval(Tools::getValue('start', 0));
$limit = intval(Tools::getValue('limit', 40));
$sort = Tools::getValue('sort', 0); // Should be a String. Default value is an Integer because we don't know what can be the name of the column to sort.
$dir = Tools::getValue('dir', 0); // Should be a String : Either ASC or DESC
$id_employee = intval(Tools::getValue('id_employee'));
$id_lang = intval(Tools::getValue('id_lang'));

if (!Validate::isModuleName($module))
	die(Tools::displayError());
require_once(dirname(__FILE__).'/../modules/'.$module.'/'.$module.'.php');

$grid = new $module();
$grid->setEmployee($id_employee);
$grid->setLang($id_lang);
if ($option)
	$grid->setOption($option);
	
$grid->create($render, $type, $width, $height, $start, $limit, $sort, $dir);
$grid->render();

?>
