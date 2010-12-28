<?php

	// Init
	$sql = array();
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ups_cache`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ups_rate_config`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ups_rate_config_service`;';

?>