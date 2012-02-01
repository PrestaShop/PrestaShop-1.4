<?php

// File Example for upgrade

if (!defined('_PS_VERSION_'))
  exit;

// object module ($this) available
function upgrade_module_1_8_0($object)
{
	// Add new table to handle multi-shop for a carrier
	$query = '
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mr_method_shop` (
		`id_mr_method_shop` int(10) unsigned NOT NULL auto_increment,
		`id_mr_method` int(10) unsigned NOT NULL,
		`id_shop` int(10) unsigned NOT NULL,
		PRIMARY KEY  (`id_mr_method_shop`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

	if (!Db::getInstance()->execute($query))
		return false;

	// Refacto name
	$query = '
		ALTER TABLE  `'._DB_PREFIX_.'mr_method` CHANGE  `id_mr_method`  `id_mr_method` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		CHANGE  `mr_Name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_Pays_list`  `country_list` VARCHAR( 1000 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_ModeCol`  `col_mode` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_ModeLiv`  `dlv_mode` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_ModeAss`  `insurance` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  \'0\',
		CHANGE  `id_carrier`  `id_carrier` INT( 10 ) NOT NULL';

	if (!Db::getInstance()->execute($query))
		return false;

	$query = 'RENAME TABLE  `'._DB_PREFIX_.'mr_historique` TO  `'._DB_PREFIX_.'mr_history`';

	if (!Db::getInstance()->execute($query))
		return false;

	$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mr_method_shop` (
		`id_mr_method_shop` int(10) unsigned NOT NULL auto_increment,
		`id_mr_method` int(10) unsigned NOT NULL,
		`id_shop` int(10) unsigned NOT NULL,
		PRIMARY KEY  (`id_mr_method_shop`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

	if (!Db::getInstance()->execute($query))
		return false;

	Configuration::deleteByName('MONDIAL_RELAY_INSTALL_UPDATE');
	Configuration::deleteByName('MONDIAL_RELAY_SECURE_KEY');
	Configuration::deleteByName('MONDIAL_RELAY_ORDER_STATE');
	Configuration::deleteByName('MR_ENSEIGNE_WEBSERVICE');
	Configuration::deleteByName('MR_CODE_MARQUE');
	Configuration::deleteByName('MR_KEY_WEBSERVICE');
	Configuration::deleteByName('MR_WEIGHT_COEF');
	Configuration::deleteByName('MONDIAL_RELAY_1_4');
	Configuration::deleteByName('MONDIAL_RELAY_INSTALL_UPDATE_1');

	Configuration::updateValue('MONDIAL_RELAY', $object->version);

	if (!empty($object->installed_version))
	{
		if ($object->installed_version < '1.4')
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'carrier`
				SET
					`shipping_external` = 0,
					`need_range` = 1,
					`external_module_name` = "mondialrelay",
					`shipping_method` = 1
				WHERE `id_carrier`
				IN (SELECT `id_carrier`
						FROM `'._DB_PREFIX_.'mr_method`)');

		if (!$this->isRegisteredInHook('newOrder'))
			$this->registerHook('newOrder');
		if (!$this->isRegisteredInHook('BackOfficeHeader'))
			$this->registerHook('BackOfficeHeader');

		if (!$this->isRegisteredInHook('header'))
			$this->registerHook('header');

		// Insert back all existing carrier with asssociated shop (1 by default)
		$methods = Db::getInstance()->executeS('
			SELECT id_mr_method FROM `'._DB_PREFIX_.'mr_method`');
		foreach ($methods as $method)
			Db::getInstance()->execute('
				INSERT INTO `'._DB_PREFIX_.'mr_method_shop`
				(`id_mr_method`, `id_shop`) VALUES('.(int)$method['id_mr_method'].', 1)');
	}

	return true;
}