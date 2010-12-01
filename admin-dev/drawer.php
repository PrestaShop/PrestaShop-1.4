<?php

include_once(dirname(__FILE__).'/../config/config.inc.php');
$cookie = new Cookie('psAdmin');


$module = Tools::getValue('module');
$render = Tools::getValue('render');
$type = Tools::getValue('type');
$option = Tools::getValue('option');
$layers = Tools::getValue('layers');
$width = Tools::getValue('width');
$height = Tools::getValue('height');
$id_employee = Tools::getValue('id_employee');
$id_lang = Tools::getValue('id_lang');

if ($cookie->id_employee != $id_employee)
	die;

if (!Tools::file_exists_cache($module_path = dirname(__FILE__).'/../modules/'.$module.'/'.$module.'.php'))
	die(Tools::displayError());

require_once($module_path);

$graph = new $module();
$graph->setEmployee($id_employee);
$graph->setLang($id_lang);
if ($option) $graph->setOption($option, $layers);

$graph->create($render, $type, $width, $height, $layers);
$graph->draw();

