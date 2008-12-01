<?php

include_once(dirname(__FILE__).'/../config/config.inc.php');
$cookie = new Cookie('psAdmin');

$module = Tools::getValue('module');
$render = Tools::getValue('render');
$type = Tools::getValue('type');
$option = Tools::getValue('option');
$width = Tools::getValue('width');
$height = Tools::getValue('height');

require_once(dirname(__FILE__).'/../modules/'.$module.'/'.$module.'.php');
$graph = new $module();
if ($option) $graph->setOption($option);
$graph->create($render, $type, $width, $height);
$graph->draw();

?>