<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/blocklayered.php');

// Token : 3474f99fd9
// Url test: http://prestashop.localhost/modules/blocklayered/blocklayered-indexer.php?token=3474f99fd9

if (substr(Tools::encrypt('blocklayered/index'),0,10) != Tools::getValue('token') || !Module::isInstalled('blocklayered'))
	die;

if(Tools::getValue('full'))
	echo BlockLayered::fullindexProcess((int)Tools::getValue('cursor'), (int)Tools::getValue('ajax'), true);
else
	echo BlockLayered::indexProcess((int)Tools::getValue('cursor'), (int)Tools::getValue('ajax'));