SET NAMES 'utf8';

/* ##################################### */
/* 					STRUCTURE				 */
/* ##################################### */

DROP TABLE IF EXISTS PREFIX_order_customization_return;

ALTER TABLE PREFIX_cart
	ADD id_guest INT UNSIGNED NULL AFTER id_customer; 

ALTER TABLE PREFIX_tab
	ADD `module` varchar(64) NULL AFTER class_name;

ALTER TABLE PREFIX_product
	ADD `indexed` tinyint(1) NOT NULL default '0' AFTER `active`;
	
ALTER TABLE PREFIX_orders
	DROP INDEX `orders_customer`;
ALTER TABLE PREFIX_orders
	ADD INDEX id_customer (id_customer);
ALTER TABLE PREFIX_orders
	ADD valid INTEGER(1) UNSIGNED NOT NULL DEFAULT '0' AFTER delivery_date;
ALTER TABLE PREFIX_orders
	ADD INDEX `id_cart` (`id_cart`);

ALTER TABLE PREFIX_customer
	ADD deleted TINYINT(1) NOT NULL DEFAULT '0' AFTER active;

ALTER TABLE PREFIX_employee
	ADD stats_date_to DATE NULL DEFAULT NULL AFTER last_passwd_gen;
ALTER TABLE PREFIX_employee
	ADD stats_date_from DATE NULL DEFAULT NULL AFTER last_passwd_gen;

ALTER TABLE PREFIX_order_state
	ADD hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER unremovable;

ALTER TABLE PREFIX_carrier
	ADD is_module TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER range_behavior;
ALTER TABLE PREFIX_carrier
	ADD INDEX deleted (`deleted`, `active`);

ALTER TABLE PREFIX_state
	CHANGE iso_code `iso_code` char(4) NOT NULL;
	
ALTER TABLE PREFIX_order_detail
	CHANGE product_quantity_cancelled product_quantity_refunded INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE PREFIX_order_detail
	ADD INDEX product_id (product_id);

ALTER TABLE PREFIX_attribute_lang
	ADD INDEX id_lang (`id_lang`, `name`);
ALTER TABLE PREFIX_attribute_lang
	ADD INDEX id_lang_2 (`id_lang`);
ALTER TABLE PREFIX_attribute_lang
	ADD INDEX id_attribute (`id_attribute`);

ALTER TABLE PREFIX_block_cms
	ADD PRIMARY KEY (`id_block`, `id_cms`);

ALTER TABLE PREFIX_connections
	CHANGE `http_referer` `http_referer` VARCHAR(255) DEFAULT NULL;
ALTER TABLE PREFIX_connections
	ADD INDEX `date_add` (`date_add`);

ALTER TABLE PREFIX_customer
	DROP INDEX `customer_email`;
ALTER TABLE PREFIX_customer
	ADD UNIQUE `customer_email` (`email`);

ALTER TABLE PREFIX_delivery
	ADD INDEX id_zone (`id_zone`);
ALTER TABLE PREFIX_delivery
	ADD INDEX id_carrier (`id_carrier`, `id_zone`);

ALTER TABLE PREFIX_discount_category
	ADD INDEX id_category (`id_category`);

ALTER TABLE PREFIX_feature_product
	ADD INDEX `id_feature` (`id_feature`);

ALTER TABLE PREFIX_hook_module
	DROP INDEX `hook_module_index`;
ALTER TABLE PREFIX_hook_module
	ADD PRIMARY KEY (id_module,id_hook);
ALTER TABLE PREFIX_hook_module
	ADD INDEX id_module (`id_module`);
ALTER TABLE PREFIX_hook_module
	ADD INDEX id_hook (`id_hook`);

ALTER TABLE PREFIX_module
	CHANGE `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE PREFIX_page
	CHANGE `id_object` `id_object` INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE PREFIX_page
	ADD INDEX `id_page_type` (`id_page_type`);
ALTER TABLE PREFIX_page
	ADD INDEX `id_object` (`id_object`);

ALTER TABLE PREFIX_page_type
	ADD INDEX `name` (`name`);
ALTER TABLE PREFIX_page_type
	CHANGE `name` `name` VARCHAR(255) NOT NULL;
	
ALTER TABLE PREFIX_product_attribute
	ADD INDEX reference (reference);
ALTER TABLE PREFIX_product_attribute
	ADD INDEX supplier_reference (supplier_reference);

ALTER TABLE PREFIX_product_lang
	ADD INDEX id_product (id_product);
ALTER TABLE PREFIX_product_lang
	ADD INDEX id_lang (id_lang);
ALTER TABLE PREFIX_product_lang
	ADD INDEX `name` (`name`);
ALTER TABLE PREFIX_product_lang
	ADD FULLTEXT KEY ftsname (`name`);
	
ALTER TABLE PREFIX_cart_discount
	ADD INDEX `id_discount` (`id_discount`);

ALTER TABLE PREFIX_discount_category
	ADD PRIMARY KEY (id_discount,id_category);

ALTER TABLE PREFIX_image_lang
	ADD INDEX id_image (id_image);

ALTER TABLE PREFIX_range_price
	CHANGE `delimiter1` `delimiter1` DECIMAL(13, 6) NOT NULL;
ALTER TABLE PREFIX_range_price
	CHANGE `delimiter2` `delimiter2` DECIMAL(13, 6) NOT NULL;
ALTER TABLE PREFIX_range_price
	CHANGE `id_carrier` `id_carrier` INT(10) UNSIGNED NOT NULL;
ALTER TABLE PREFIX_range_price
	DROP INDEX `range_price_unique`;
ALTER TABLE PREFIX_range_price
	ADD UNIQUE KEY `id_carrier` (`id_carrier`,`delimiter1`,`delimiter2`);

ALTER TABLE PREFIX_range_weight
	CHANGE `delimiter1` `delimiter1` DECIMAL(13, 6) NOT NULL;
ALTER TABLE PREFIX_range_weight
	CHANGE `delimiter2` `delimiter2` DECIMAL(13, 6) NOT NULL;
ALTER TABLE PREFIX_range_weight
	CHANGE `id_carrier` `id_carrier` INT(10) UNSIGNED NOT NULL;
ALTER TABLE PREFIX_range_weight
	DROP INDEX `range_weight_unique`;
ALTER TABLE PREFIX_range_weight
	ADD UNIQUE KEY `id_carrier` (`id_carrier`,`delimiter1`,`delimiter2`);

ALTER TABLE PREFIX_scene_products
	ADD PRIMARY KEY (`id_scene`, `id_product`, `x_axis`, `y_axis`);
	
ALTER TABLE PREFIX_product_lang DROP INDEX fts; 
ALTER TABLE PREFIX_product_lang DROP INDEX ftsname ;

/* KEY management */
ALTER TABLE PREFIX_attribute_lang DROP INDEX `id_lang_2`;
ALTER TABLE PREFIX_attribute_lang DROP INDEX `id_attribute`;
ALTER TABLE PREFIX_attribute_lang DROP INDEX `attribute_lang_index`, ADD PRIMARY KEY (`id_attribute`, `id_lang`);
ALTER TABLE PREFIX_carrier_zone DROP INDEX `carrier_zone_index`, ADD PRIMARY KEY (`id_carrier`, `id_zone`);
ALTER TABLE PREFIX_discount_category CHANGE `id_discount` `id_discount` int(11) NOT NULL AFTER `id_category`;
ALTER TABLE PREFIX_discount_category DROP INDEX `id_category`;
ALTER TABLE PREFIX_feature_product DROP INDEX `id_feature`;
ALTER TABLE PREFIX_hook_module DROP INDEX `id_module`;
ALTER TABLE PREFIX_image_lang DROP INDEX `id_image`;
ALTER TABLE PREFIX_product_lang DROP INDEX `id_product`;

/* ############################################################ */

CREATE TABLE `PREFIX_customer_group` (
	`id_customer` int(10) unsigned NOT NULL,
	`id_group` int(10) unsigned NOT NULL,
	KEY `customer_group_index` (`id_customer`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_category_group (
	id_category INTEGER UNSIGNED NOT NULL,
	id_group INTEGER UNSIGNED NOT NULL,
	INDEX category_group_index(id_category, id_group)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_group (
	id_group INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	reduction DECIMAL(10,2) NOT NULL DEFAULT 0,
	date_add DATETIME NOT NULL,
	date_upd DATETIME NOT NULL,
	PRIMARY KEY(id_group)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_group_lang (
	id_group INTEGER UNSIGNED NOT NULL,
	id_lang INTEGER UNSIGNED NOT NULL,
	name VARCHAR(32) NOT NULL,
	UNIQUE INDEX attribute_lang_index(id_group, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_message_readed (
	id_message INTEGER UNSIGNED NOT NULL,
	id_employee INTEGER UNSIGNED NOT NULL,
	date_add DATETIME NOT NULL,
	PRIMARY KEY	(id_message,id_employee)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attachment` (
  `id_attachment` int(10) unsigned NOT NULL auto_increment,
  `file` varchar(40) NOT NULL,
  `mime` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_attachment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attachment_lang` (
  `id_attachment` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) default NULL,
  `description` TEXT,
  PRIMARY KEY  (`id_attachment`, `id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_attachment` (
  `id_product` int(10) NOT NULL,
  `id_attachment` int(10) NOT NULL,
  PRIMARY KEY  (`id_product`,`id_attachment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_connections_source` (
	id_connections_source INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	id_connections INTEGER UNSIGNED NOT NULL,
	http_referer VARCHAR(255) NULL,
	request_uri VARCHAR(255) NULL,
	keywords VARCHAR(255) NULL,
	date_add DATETIME NOT NULL,
	PRIMARY KEY (id_connections_source),
	INDEX connections (id_connections),
	INDEX orderby (date_add),
	INDEX http_referer (`http_referer`),
	INDEX request_uri(`request_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_referrer` (
  `id_referrer` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `passwd` varchar(32) default NULL,
  `http_referer_regexp` varchar(64) default NULL,
  `http_referer_like` varchar(64) default NULL,
  `request_uri_regexp` varchar(64) default NULL,
  `request_uri_like` varchar(64) default NULL,
  `http_referer_regexp_not` varchar(64) default NULL,
  `http_referer_like_not` varchar(64) default NULL,
  `request_uri_regexp_not` varchar(64) default NULL,
  `request_uri_like_not` varchar(64) default NULL,
  `base_fee` decimal(5,2) NOT NULL default '0.00',
  `percent_fee` decimal(5,2) NOT NULL default '0.00',
  `click_fee` decimal(5,2) NOT NULL default '0.00',
  `cache_visitors` int(11) default NULL,
  `cache_visits` int(11) default NULL,
  `cache_pages` int(11) default NULL,
  `cache_registrations` int(11) default NULL,
  `cache_orders` int(11) default NULL,
  `cache_sales` decimal(10,2) default NULL,
  `cache_reg_rate` decimal(5,4) default NULL,
  `cache_order_rate` decimal(5,4) default NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_referrer_cache` (
  `id_connections_source` int(11) NOT NULL,
  `id_referrer` int(11) NOT NULL,
  PRIMARY KEY  (`id_connections_source`, `id_referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_search_engine` (
	id_search_engine INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	server VARCHAR(64) NOT NULL,
	getvar VARCHAR(16) NOT NULL,
	PRIMARY KEY(id_search_engine)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module_group` (
	`id_module` INTEGER UNSIGNED NOT NULL,
	`id_group` INTEGER NOT NULL,
	PRIMARY KEY (`id_module`, `id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_attribute_image` (
	`id_product_attribute` int(10) NOT NULL,
	`id_image` int(10) NOT NULL,
	PRIMARY KEY	(`id_product_attribute`,`id_image`),
	KEY `id_image` (`id_image`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_index` (
  `id_product` int(11) NOT NULL,
  `id_word` int(11) NOT NULL,
  `weight` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id_word`, `id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_word` (
  `id_word` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `word` varchar(15) NOT NULL,
  PRIMARY KEY  (`id_word`),
  UNIQUE KEY `id_lang` (`id_lang`,`word`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_timezone (
	id_timezone INTEGER UNSIGNED NOT NULL auto_increment,
	name VARCHAR(32) NOT NULL,
	PRIMARY KEY timezone_index(`id_timezone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* ##################################### */
/* 					CONTENTS					 */
/* ##################################### */

INSERT INTO `PREFIX_order_state` (`id_order_state`, `invoice`, `send_email`, `color`, `unremovable`, `logable`, `delivery`) VALUES
	(11, 0, 0, 'lightblue', 1, 0, 0);

INSERT INTO `PREFIX_order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`) VALUES
	(11, 1, 'Awaiting PayPal payment', '');
INSERT INTO `PREFIX_order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`) VALUES
	(11, 2, 'En attente du paiement par PayPal', '');

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_MINWORDLEN', '3', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_PNAME', '6', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_REF', '10', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_SHORTDESC', '1', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_DESC', '1', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_CNAME', '3', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_MNAME', '3', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_TAG', '4', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_ATTRIBUTE', '2', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_WEIGHT_FEATURE', '2', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_SEARCH_AJAX', '1', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('PS_TIMEZONE', '277', NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
	('BLOCKTAGS_NBR', '10', NOW(), NOW());

INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('extraCarrier', 'Extra carrier (module mode)', NULL, 0);
INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('shoppingCartExtra', 'Shopping cart extra button', 'Display some specific informations', 1);
INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('search', 'Search', NULL, 0);
INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('backBeforePayment', 'Redirect in order process', 'Redirect user to the module instead of displaying payment modules', 0);

UPDATE PREFIX_orders o SET o.valid = IFNULL((
	SELECT os.logable
	FROM PREFIX_order_history oh
	LEFT JOIN PREFIX_order_state os ON os.id_order_state = oh.id_order_state
	WHERE oh.id_order = o.id_order
	ORDER BY oh.date_add DESC, oh.id_order_history DESC
	LIMIT 1
), 0);

INSERT INTO `PREFIX_search_engine` (`id_search_engine`, `server`,`getvar`) VALUES
	(1, 'google','q'),
	(2, 'search.aol','query'),
	(3, 'yandex.ru','text'),
	(4, 'ask.com','q'),
	(5, 'nhl.com','q'),
	(6, 'search.yahoo','p'),
	(7, 'baidu.com','wd'),
	(8, 'search.lycos','query'),
	(9, 'exalead','q'),
	(10, 'search.live.com','q'),
	(11, 'search.ke.voila','rdata'),
	(12, 'altavista','q')
	ON DUPLICATE KEY UPDATE server = server;

/* GROUPS, CUSTOMERS GROUPS, & CATEGORY GROUPS */
INSERT INTO `PREFIX_group` (`reduction`, `date_add`, `date_upd`) VALUES (0, NOW(), NOW());
INSERT INTO `PREFIX_group_lang` (`id_lang`, `id_group`, `name`) (
	SELECT `id_lang`,
	(SELECT `id_group` FROM `PREFIX_group` LIMIT 1),
	'Default' FROM `PREFIX_lang`);
UPDATE `PREFIX_group_lang` SET `name` = 'Défaut'
	WHERE `id_group` = (SELECT `id_group` FROM `PREFIX_group` LIMIT 1)
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO `PREFIX_customer_group` (`id_customer`, `id_group`)
	(SELECT `id_customer`,
	(SELECT `id_group` FROM `PREFIX_group` LIMIT 1) FROM `PREFIX_customer`);
INSERT INTO `PREFIX_category_group` (`id_category`, `id_group`)
	(SELECT `id_category`,
	(SELECT `id_group` FROM `PREFIX_group` LIMIT 1) FROM `PREFIX_category`);

/* NEW TABS */
INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminOrders' LIMIT 1) AS tmp), 'AdminMessages', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminOrders' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminMessages' LIMIT 1),
	'Customer messages' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Messages clients'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminMessages')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminMessages' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCatalog' LIMIT 1) AS tmp), 'AdminTracking', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCatalog' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminTracking' LIMIT 1),
	'Tracking' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Suivi'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminTracking')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminTracking' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminStats' LIMIT 1) AS tmp), 'AdminSearchEngines', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminStats' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminSearchEngines' LIMIT 1),
	'Search Engines' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Moteurs de recherche'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminSearchEngines')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminSearchEngines' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminStats' LIMIT 1) AS tmp), 'AdminReferrers', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminStats' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminReferrers' LIMIT 1),
	'Referrers' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Sites affluents'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminReferrers')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminReferrers' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCustomers' LIMIT 1) AS tmp), 'AdminGroups', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCustomers' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminGroups' LIMIT 1),
	'Groups' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Groupes'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminGroups')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminGroups' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminTools' LIMIT 1) AS tmp), 'AdminGenerator', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminTools' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminGenerator' LIMIT 1),
	'Generator' FROM PREFIX_lang);
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminGenerator' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCustomers' LIMIT 1) AS tmp), 'AdminCarts', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCustomers' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminCarts' LIMIT 1),
	'Carts' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Paniers'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminCarts')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminCarts' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCatalog' LIMIT 1) AS tmp), 'AdminTags', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCatalog' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminTags' LIMIT 1),
	'Tags' FROM PREFIX_lang);
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminTags' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminPreferences' LIMIT 1) AS tmp), 'AdminSearchConf', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminPreferences' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminSearchConf' LIMIT 1),
	'Search' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Recherche'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminSearchConf')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminSearchConf' LIMIT 1), 1, 1, 1, 1);

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCatalog' LIMIT 1) AS tmp), 'AdminAttachments', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminCatalog' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminAttachments' LIMIT 1),
	'Attachments' FROM PREFIX_lang);
UPDATE `PREFIX_tab_lang` SET `name` = 'Documents joints'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminAttachments')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminAttachments' LIMIT 1), 1, 1, 1, 1);

/* CHANGE TABS */
UPDATE `PREFIX_tab` SET `class_name` = 'AdminStatuses' WHERE `class_name` = 'AdminOrdersStates';
UPDATE `PREFIX_tab_lang` SET `name` = 'Statuses'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminStatuses')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'en');
UPDATE `PREFIX_tab_lang` SET `name` = 'Statuts'
	WHERE `id_tab` = (SELECT `id_tab` FROM `PREFIX_tab` t WHERE t.class_name = 'AdminStatuses')
	AND `id_lang` = (SELECT `id_lang` FROM `PREFIX_lang` l WHERE l.iso_code = 'fr');

INSERT INTO PREFIX_product_attribute_image (id_image, id_product_attribute) (SELECT id_image, id_product_attribute FROM PREFIX_product_attribute);
/* ALTER query must stay here (right after the INSERT INTO PREFIX_product_attribute_image)! */
ALTER TABLE PREFIX_product_attribute DROP id_image;

UPDATE PREFIX_category_lang SET link_rewrite = 'home' WHERE id_category = 1;

/* TIMEZONES */

INSERT INTO `PREFIX_timezone` (`name`) VALUES
	('Africa/Abidjan'),
	('Africa/Accra'),
	('Africa/Addis_Ababa'),
	('Africa/Algiers'),
	('Africa/Asmara'),
	('Africa/Asmera'),
	('Africa/Bamako'),
	('Africa/Bangui'),
	('Africa/Banjul'),
	('Africa/Bissau'),
	('Africa/Blantyre'),
	('Africa/Brazzaville'),
	('Africa/Bujumbura'),
	('Africa/Cairo'),
	('Africa/Casablanca'),
	('Africa/Ceuta'),
	('Africa/Conakry'),
	('Africa/Dakar'),
	('Africa/Dar_es_Salaam'),
	('Africa/Djibouti'),
	('Africa/Douala'),
	('Africa/El_Aaiun'),
	('Africa/Freetown'),
	('Africa/Gaborone'),
	('Africa/Harare'),
	('Africa/Johannesburg'),
	('Africa/Kampala'),
	('Africa/Khartoum'),
	('Africa/Kigali'),
	('Africa/Kinshasa'),
	('Africa/Lagos'),
	('Africa/Libreville'),
	('Africa/Lome'),
	('Africa/Luanda'),
	('Africa/Lubumbashi'),
	('Africa/Lusaka'),
	('Africa/Malabo'),
	('Africa/Maputo'),
	('Africa/Maseru'),
	('Africa/Mbabane'),
	('Africa/Mogadishu'),
	('Africa/Monrovia'),
	('Africa/Nairobi'),
	('Africa/Ndjamena'),
	('Africa/Niamey'),
	('Africa/Nouakchott'),
	('Africa/Ouagadougou'),
	('Africa/Porto-Novo'),
	('Africa/Sao_Tome'),
	('Africa/Timbuktu'),
	('Africa/Tripoli'),
	('Africa/Tunis'),
	('Africa/Windhoek'),
	('America/Adak'),
	('America/Anchorage '),
	('America/Anguilla'),
	('America/Antigua'),
	('America/Araguaina'),
	('America/Argentina/Buenos_Aires'),
	('America/Argentina/Catamarca'),
	('America/Argentina/ComodRivadavia'),
	('America/Argentina/Cordoba'),
	('America/Argentina/Jujuy'),
	('America/Argentina/La_Rioja'),
	('America/Argentina/Mendoza'),
	('America/Argentina/Rio_Gallegos'),
	('America/Argentina/Salta'),
	('America/Argentina/San_Juan'),
	('America/Argentina/San_Luis'),
	('America/Argentina/Tucuman'),
	('America/Argentina/Ushuaia'),
	('America/Aruba'),
	('America/Asuncion'),
	('America/Atikokan'),
	('America/Atka'),
	('America/Bahia'),
	('America/Barbados'),
	('America/Belem'),
	('America/Belize'),
	('America/Blanc-Sablon'),
	('America/Boa_Vista'),
	('America/Bogota'),
	('America/Boise'),
	('America/Buenos_Aires'),
	('America/Cambridge_Bay'),
	('America/Campo_Grande'),
	('America/Cancun'),
	('America/Caracas'),
	('America/Catamarca'),
	('America/Cayenne'),
	('America/Cayman'),
	('America/Chicago'),
	('America/Chihuahua'),
	('America/Coral_Harbour'),
	('America/Cordoba'),
	('America/Costa_Rica'),
	('America/Cuiaba'),
	('America/Curacao'),
	('America/Danmarkshavn'),
	('America/Dawson'),
	('America/Dawson_Creek'),
	('America/Denver'),
	('America/Detroit'),
	('America/Dominica'),
	('America/Edmonton'),
	('America/Eirunepe'),
	('America/El_Salvador'),
	('America/Ensenada'),
	('America/Fort_Wayne'),
	('America/Fortaleza'),
	('America/Glace_Bay'),
	('America/Godthab'),
	('America/Goose_Bay'),
	('America/Grand_Turk'),
	('America/Grenada'),
	('America/Guadeloupe'),
	('America/Guatemala'),
	('America/Guayaquil'),
	('America/Guyana'),
	('America/Halifax'),
	('America/Havana'),
	('America/Hermosillo'),
	('America/Indiana/Indianapolis'),
	('America/Indiana/Knox'),
	('America/Indiana/Marengo'),
	('America/Indiana/Petersburg'),
	('America/Indiana/Tell_City'),
	('America/Indiana/Vevay'),
	('America/Indiana/Vincennes'),
	('America/Indiana/Winamac'),
	('America/Indianapolis'),
	('America/Inuvik'),
	('America/Iqaluit'),
	('America/Jamaica'),
	('America/Jujuy'),
	('America/Juneau'),
	('America/Kentucky/Louisville'),
	('America/Kentucky/Monticello'),
	('America/Knox_IN'),
	('America/La_Paz'),
	('America/Lima'),
	('America/Los_Angeles'),
	('America/Louisville'),
	('America/Maceio'),
	('America/Managua'),
	('America/Manaus'),
	('America/Marigot'),
	('America/Martinique'),
	('America/Mazatlan'),
	('America/Mendoza'),
	('America/Menominee'),
	('America/Merida'),
	('America/Mexico_City'),
	('America/Miquelon'),
	('America/Moncton'),
	('America/Monterrey'),
	('America/Montevideo'),
	('America/Montreal'),
	('America/Montserrat'),
	('America/Nassau'),
	('America/New_York'),
	('America/Nipigon'),
	('America/Nome'),
	('America/Noronha'),
	('America/North_Dakota/Center'),
	('America/North_Dakota/New_Salem'),
	('America/Panama'),
	('America/Pangnirtung'),
	('America/Paramaribo'),
	('America/Phoenix'),
	('America/Port-au-Prince'),
	('America/Port_of_Spain'),
	('America/Porto_Acre'),
	('America/Porto_Velho'),
	('America/Puerto_Rico'),
	('America/Rainy_River'),
	('America/Rankin_Inlet'),
	('America/Recife'),
	('America/Regina'),
	('America/Resolute'),
	('America/Rio_Branco'),
	('America/Rosario'),
	('America/Santarem'),
	('America/Santiago'),
	('America/Santo_Domingo'),
	('America/Sao_Paulo'),
	('America/Scoresbysund'),
	('America/Shiprock'),
	('America/St_Barthelemy'),
	('America/St_Johns'),
	('America/St_Kitts'),
	('America/St_Lucia'),
	('America/St_Thomas'),
	('America/St_Vincent'),
	('America/Swift_Current'),
	('America/Tegucigalpa'),
	('America/Thule'),
	('America/Thunder_Bay'),
	('America/Tijuana'),
	('America/Toronto'),
	('America/Tortola'),
	('America/Vancouver'),
	('America/Virgin'),
	('America/Whitehorse'),
	('America/Winnipeg'),
	('America/Yakutat'),
	('America/Yellowknife'),
	('Antarctica/Casey'),
	('Antarctica/Davis'),
	('Antarctica/DumontDUrville'),
	('Antarctica/Mawson'),
	('Antarctica/McMurdo'),
	('Antarctica/Palmer'),
	('Antarctica/Rothera'),
	('Antarctica/South_Pole'),
	('Antarctica/Syowa'),
	('Antarctica/Vostok'),
	('Arctic/Longyearbyen'),
	('Asia/Aden'),
	('Asia/Almaty'),
	('Asia/Amman'),
	('Asia/Anadyr'),
	('Asia/Aqtau'),
	('Asia/Aqtobe'),
	('Asia/Ashgabat'),
	('Asia/Ashkhabad'),
	('Asia/Baghdad'),
	('Asia/Bahrain'),
	('Asia/Baku'),
	('Asia/Bangkok'),
	('Asia/Beirut'),
	('Asia/Bishkek'),
	('Asia/Brunei'),
	('Asia/Calcutta'),
	('Asia/Choibalsan'),
	('Asia/Chongqing'),
	('Asia/Chungking'),
	('Asia/Colombo'),
	('Asia/Dacca'),
	('Asia/Damascus'),
	('Asia/Dhaka'),
	('Asia/Dili'),
	('Asia/Dubai'),
	('Asia/Dushanbe'),
	('Asia/Gaza'),
	('Asia/Harbin'),
	('Asia/Ho_Chi_Minh'),
	('Asia/Hong_Kong'),
	('Asia/Hovd'),
	('Asia/Irkutsk'),
	('Asia/Istanbul'),
	('Asia/Jakarta'),
	('Asia/Jayapura'),
	('Asia/Jerusalem'),
	('Asia/Kabul'),
	('Asia/Kamchatka'),
	('Asia/Karachi'),
	('Asia/Kashgar'),
	('Asia/Kathmandu'),
	('Asia/Katmandu'),
	('Asia/Kolkata'),
	('Asia/Krasnoyarsk'),
	('Asia/Kuala_Lumpur'),
	('Asia/Kuching'),
	('Asia/Kuwait'),
	('Asia/Macao'),
	('Asia/Macau'),
	('Asia/Magadan'),
	('Asia/Makassar'),
	('Asia/Manila'),
	('Asia/Muscat'),
	('Asia/Nicosia'),
	('Asia/Novosibirsk'),
	('Asia/Omsk'),
	('Asia/Oral'),
	('Asia/Phnom_Penh'),
	('Asia/Pontianak'),
	('Asia/Pyongyang'),
	('Asia/Qatar'),
	('Asia/Qyzylorda'),
	('Asia/Rangoon'),
	('Asia/Riyadh'),
	('Asia/Saigon'),
	('Asia/Sakhalin'),
	('Asia/Samarkand'),
	('Asia/Seoul'),
	('Asia/Shanghai'),
	('Asia/Singapore'),
	('Asia/Taipei'),
	('Asia/Tashkent'),
	('Asia/Tbilisi'),
	('Asia/Tehran'),
	('Asia/Tel_Aviv'),
	('Asia/Thimbu'),
	('Asia/Thimphu'),
	('Asia/Tokyo'),
	('Asia/Ujung_Pandang'),
	('Asia/Ulaanbaatar'),
	('Asia/Ulan_Bator'),
	('Asia/Urumqi'),
	('Asia/Vientiane'),
	('Asia/Vladivostok'),
	('Asia/Yakutsk'),
	('Asia/Yekaterinburg'),
	('Asia/Yerevan'),
	('Atlantic/Azores'),
	('Atlantic/Bermuda'),
	('Atlantic/Canary'),
	('Atlantic/Cape_Verde'),
	('Atlantic/Faeroe'),
	('Atlantic/Faroe'),
	('Atlantic/Jan_Mayen'),
	('Atlantic/Madeira'),
	('Atlantic/Reykjavik'),
	('Atlantic/South_Georgia'),
	('Atlantic/St_Helena'),
	('Atlantic/Stanley'),
	('Australia/ACT'),
	('Australia/Adelaide'),
	('Australia/Brisbane'),
	('Australia/Broken_Hill'),
	('Australia/Canberra'),
	('Australia/Currie'),
	('Australia/Darwin'),
	('Australia/Eucla'),
	('Australia/Hobart'),
	('Australia/LHI'),
	('Australia/Lindeman'),
	('Australia/Lord_Howe'),
	('Australia/Melbourne'),
	('Australia/North'),
	('Australia/NSW'),
	('Australia/Perth'),
	('Australia/Queensland'),
	('Australia/South'),
	('Australia/Sydney'),
	('Australia/Tasmania'),
	('Australia/Victoria'),
	('Australia/West'),
	('Australia/Yancowinna'),
	('Europe/Amsterdam'),
	('Europe/Andorra'),
	('Europe/Athens'),
	('Europe/Belfast'),
	('Europe/Belgrade'),
	('Europe/Berlin'),
	('Europe/Bratislava'),
	('Europe/Brussels'),
	('Europe/Bucharest'),
	('Europe/Budapest'),
	('Europe/Chisinau'),
	('Europe/Copenhagen'),
	('Europe/Dublin'),
	('Europe/Gibraltar'),
	('Europe/Guernsey'),
	('Europe/Helsinki'),
	('Europe/Isle_of_Man'),
	('Europe/Istanbul'),
	('Europe/Jersey'),
	('Europe/Kaliningrad'),
	('Europe/Kiev'),
	('Europe/Lisbon'),
	('Europe/Ljubljana'),
	('Europe/London'),
	('Europe/Luxembourg'),
	('Europe/Madrid'),
	('Europe/Malta'),
	('Europe/Mariehamn'),
	('Europe/Minsk'),
	('Europe/Monaco'),
	('Europe/Moscow'),
	('Europe/Nicosia'),
	('Europe/Oslo'),
	('Europe/Paris'),
	('Europe/Podgorica'),
	('Europe/Prague'),
	('Europe/Riga'),
	('Europe/Rome'),
	('Europe/Samara'),
	('Europe/San_Marino'),
	('Europe/Sarajevo'),
	('Europe/Simferopol'),
	('Europe/Skopje'),
	('Europe/Sofia'),
	('Europe/Stockholm'),
	('Europe/Tallinn'),
	('Europe/Tirane'),
	('Europe/Tiraspol'),
	('Europe/Uzhgorod'),
	('Europe/Vaduz'),
	('Europe/Vatican'),
	('Europe/Vienna'),
	('Europe/Vilnius'),
	('Europe/Volgograd'),
	('Europe/Warsaw'),
	('Europe/Zagreb'),
	('Europe/Zaporozhye'),
	('Europe/Zurich'),
	('Indian/Antananarivo'),
	('Indian/Chagos'),
	('Indian/Christmas'),
	('Indian/Cocos'),
	('Indian/Comoro'),
	('Indian/Kerguelen'),
	('Indian/Mahe'),
	('Indian/Maldives'),
	('Indian/Mauritius'),
	('Indian/Mayotte'),
	('Indian/Reunion'),
	('Pacific/Apia'),
	('Pacific/Auckland'),
	('Pacific/Chatham'),
	('Pacific/Easter'),
	('Pacific/Efate'),
	('Pacific/Enderbury'),
	('Pacific/Fakaofo'),
	('Pacific/Fiji'),
	('Pacific/Funafuti'),
	('Pacific/Galapagos'),
	('Pacific/Gambier'),
	('Pacific/Guadalcanal'),
	('Pacific/Guam'),
	('Pacific/Honolulu'),
	('Pacific/Johnston'),
	('Pacific/Kiritimati'),
	('Pacific/Kosrae'),
	('Pacific/Kwajalein'),
	('Pacific/Majuro'),
	('Pacific/Marquesas'),
	('Pacific/Midway'),
	('Pacific/Nauru'),
	('Pacific/Niue'),
	('Pacific/Norfolk'),
	('Pacific/Noumea'),
	('Pacific/Pago_Pago'),
	('Pacific/Palau'),
	('Pacific/Pitcairn'),
	('Pacific/Ponape'),
	('Pacific/Port_Moresby'),
	('Pacific/Rarotonga'),
	('Pacific/Saipan'),
	('Pacific/Samoa'),
	('Pacific/Tahiti'),
	('Pacific/Tarawa'),
	('Pacific/Tongatapu'),
	('Pacific/Truk'),
	('Pacific/Wake'),
	('Pacific/Wallis'),
	('Pacific/Yap'),
	('Brazil/Acre'),
	('Brazil/DeNoronha'),
	('Brazil/East'),
	('Brazil/West'),
	('Canada/Atlantic'),
	('Canada/Central'),
	('Canada/East-Saskatchewan'),
	('Canada/Eastern'),
	('Canada/Mountain'),
	('Canada/Newfoundland'),
	('Canada/Pacific'),
	('Canada/Saskatchewan'),
	('Canada/Yukon'),
	('CET'),
	('Chile/Continental'),
	('Chile/EasterIsland'),
	('CST6CDT'),
	('Cuba'),
	('EET'),
	('Egypt'),
	('Eire'),
	('EST'),
	('EST5EDT'),
	('Etc/GMT'),
	('Etc/GMT+0'),
	('Etc/GMT+1'),
	('Etc/GMT+10'),
	('Etc/GMT+11'),
	('Etc/GMT+12'),
	('Etc/GMT+2'),
	('Etc/GMT+3'),
	('Etc/GMT+4'),
	('Etc/GMT+5'),
	('Etc/GMT+6'),
	('Etc/GMT+7'),
	('Etc/GMT+8'),
	('Etc/GMT+9'),
	('Etc/GMT-0'),
	('Etc/GMT-1'),
	('Etc/GMT-10'),
	('Etc/GMT-11'),
	('Etc/GMT-12'),
	('Etc/GMT-13'),
	('Etc/GMT-14'),
	('Etc/GMT-2'),
	('Etc/GMT-3'),
	('Etc/GMT-4'),
	('Etc/GMT-5'),
	('Etc/GMT-6'),
	('Etc/GMT-7'),
	('Etc/GMT-8'),
	('Etc/GMT-9'),
	('Etc/GMT0'),
	('Etc/Greenwich'),
	('Etc/UCT'),
	('Etc/Universal'),
	('Etc/UTC'),
	('Etc/Zulu'),
	('Factory'),
	('GB'),
	('GB-Eire'),
	('GMT'),
	('GMT+0'),
	('GMT-0'),
	('GMT0'),
	('Greenwich'),
	('Hongkong'),
	('HST'),
	('Iceland'),
	('Iran'),
	('Israel'),
	('Jamaica'),
	('Japan'),
	('Kwajalein'),
	('Libya'),
	('MET'),
	('Mexico/BajaNorte'),
	('Mexico/BajaSur'),
	('Mexico/General'),
	('MST'),
	('MST7MDT'),
	('Navajo'),
	('NZ'),
	('NZ-CHAT'),
	('Poland'),
	('Portugal'),
	('PRC'),
	('PST8PDT'),
	('ROC'),
	('ROK'),
	('Singapore'),
	('Turkey'),
	('UCT'),
	('Universal'),
	('US/Alaska'),
	('US/Aleutian'),
	('US/Arizona'),
	('US/Central'),
	('US/East-Indiana'),
	('US/Eastern'),
	('US/Hawaii'),
	('US/Indiana-Starke'),
	('US/Michigan'),
	('US/Mountain'),
	('US/Pacific'),
	('US/Pacific-New'),
	('US/Samoa'),
	('UTC'),
	('W-SU'),
	('WET'),
	('Zulu');

/* PHP:blocknewsletter(); */;
/* PHP:set_payment_module_group(); */;
