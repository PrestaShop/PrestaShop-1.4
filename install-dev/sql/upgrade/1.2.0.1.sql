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
INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('updateCarrier', 'Carrier update', 'This hook is called when a carrier is updated', 0);	

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

INSERT INTO `PREFIX_timezone` (`id_timezone`, `name`) VALUES
(1, 'America/Adak'),
(2, 'America/Anchorage'),
(3, 'America/Anguilla'),
(4, 'America/Antigua'),
(5, 'America/Araguaina'),
(6, 'America/Argentina/Buenos_Aires'),
(7, 'America/Argentina/Catamarca'),
(8, 'America/Argentina/ComodRivadavia'),
(9, 'America/Argentina/Cordoba'),
(10, 'America/Argentina/Jujuy'),
(11, 'America/Argentina/La_Rioja'),
(12, 'America/Argentina/Mendoza'),
(13, 'America/Argentina/Rio_Gallegos'),
(14, 'America/Argentina/San_Juan'),
(15, 'America/Argentina/Tucuman'),
(16, 'America/Argentina/Ushuaia'),
(17, 'America/Aruba'),
(18, 'America/Asuncion'),
(19, 'America/Atikokan'),
(20, 'America/Atka'),
(21, 'America/Bahia'),
(22, 'America/Barbados'),
(23, 'America/Belem'),
(24, 'America/Belize'),
(25, 'America/Blanc-Sablon'),
(26, 'America/Boa_Vista'),
(27, 'America/Bogota'),
(28, 'America/Boise'),
(29, 'America/Buenos_Aires'),
(30, 'America/Cambridge_Bay'),
(31, 'America/Campo_Grande'),
(32, 'America/Cancun'),
(33, 'America/Caracas'),
(34, 'America/Catamarca'),
(35, 'America/Cayenne'),
(36, 'America/Cayman'),
(37, 'America/Chicago'),
(38, 'America/Chihuahua'),
(39, 'America/Coral_Harbour'),
(40, 'America/Cordoba'),
(41, 'America/Costa_Rica'),
(42, 'America/Cuiaba'),
(43, 'America/Curacao'),
(44, 'America/Danmarkshavn'),
(45, 'America/Dawson'),
(46, 'America/Dawson_Creek'),
(47, 'America/Denver'),
(48, 'America/Detroit'),
(49, 'America/Dominica'),
(50, 'America/Edmonton'),
(51, 'America/Eirunepe'),
(52, 'America/El_Salvador'),
(53, 'America/Ensenada'),
(54, 'America/Fort_Wayne'),
(55, 'America/Fortaleza'),
(56, 'America/Glace_Bay'),
(57, 'America/Godthab'),
(58, 'America/Goose_Bay'),
(59, 'America/Grand_Turk'),
(60, 'America/Grenada'),
(61, 'America/Guadeloupe'),
(62, 'America/Guatemala'),
(63, 'America/Guayaquil'),
(64, 'America/Guyana'),
(65, 'America/Halifax'),
(66, 'America/Havana'),
(67, 'America/Hermosillo'),
(68, 'America/Indiana/Indianapolis'),
(69, 'America/Indiana/Knox'),
(70, 'America/Indiana/Marengo'),
(71, 'America/Indiana/Petersburg'),
(72, 'America/Indiana/Vevay'),
(73, 'America/Indiana/Vincennes'),
(74, 'America/Indiana/Winamac'),
(75, 'America/Indianapolis'),
(76, 'America/Inuvik'),
(77, 'America/Iqaluit'),
(78, 'America/Jamaica'),
(79, 'America/Jujuy'),
(80, 'America/Juneau'),
(81, 'America/Kentucky/Louisville'),
(82, 'America/Kentucky/Monticello'),
(83, 'America/Knox_IN'),
(84, 'America/La_Paz'),
(85, 'America/Lima'),
(86, 'America/Los_Angeles'),
(87, 'America/Louisville'),
(88, 'America/Maceio'),
(89, 'America/Managua'),
(90, 'America/Manaus'),
(91, 'America/Martinique'),
(92, 'America/Mazatlan'),
(93, 'America/Mendoza'),
(94, 'America/Menominee'),
(95, 'America/Merida'),
(96, 'America/Mexico_City'),
(97, 'America/Miquelon'),
(98, 'America/Moncton'),
(99, 'America/Monterrey'),
(100, 'America/Montevideo'),
(101, 'America/Montreal'),
(102, 'America/Montserrat'),
(103, 'America/Nassau'),
(104, 'America/New_York'),
(105, 'America/Nipigon'),
(106, 'America/Nome'),
(107, 'America/Noronha'),
(108, 'America/North_Dakota/Center'),
(109, 'America/North_Dakota/New_Salem'),
(110, 'America/Panama'),
(111, 'America/Pangnirtung'),
(112, 'America/Paramaribo'),
(113, 'America/Phoenix'),
(114, 'America/Port-au-Prince'),
(115, 'America/Port_of_Spain'),
(116, 'America/Porto_Acre'),
(117, 'America/Porto_Velho'),
(118, 'America/Puerto_Rico'),
(119, 'America/Rainy_River'),
(120, 'America/Rankin_Inlet'),
(121, 'America/Recife'),
(122, 'America/Regina'),
(123, 'America/Rio_Branco'),
(124, 'America/Rosario'),
(125, 'America/Santiago'),
(126, 'America/Santo_Domingo'),
(127, 'America/Sao_Paulo'),
(128, 'America/Scoresbysund'),
(129, 'America/Shiprock'),
(130, 'America/St_Johns'),
(131, 'America/St_Kitts'),
(132, 'America/St_Lucia'),
(133, 'America/St_Thomas'),
(134, 'America/St_Vincent'),
(135, 'America/Swift_Current'),
(136, 'America/Tegucigalpa'),
(137, 'America/Thule'),
(138, 'America/Thunder_Bay'),
(139, 'America/Tijuana'),
(140, 'America/Toronto'),
(141, 'America/Tortola'),
(142, 'America/Vancouver'),
(143, 'America/Virgin'),
(144, 'America/Whitehorse'),
(145, 'America/Winnipeg'),
(146, 'America/Yakutat'),
(147, 'America/Yellowknife'),
(148, 'Arctic/Longyearbyen'),
(149, 'Asia/Aden'),
(150, 'Asia/Almaty'),
(151, 'Asia/Amman'),
(152, 'Asia/Anadyr'),
(153, 'Asia/Aqtau'),
(154, 'Asia/Aqtobe'),
(155, 'Asia/Ashgabat'),
(156, 'Asia/Ashkhabad'),
(157, 'Asia/Baghdad'),
(158, 'Asia/Bahrain'),
(159, 'Asia/Baku'),
(160, 'Asia/Bangkok'),
(161, 'Asia/Beirut'),
(162, 'Asia/Bishkek'),
(163, 'Asia/Brunei'),
(164, 'Asia/Calcutta'),
(165, 'Asia/Choibalsan'),
(166, 'Asia/Chongqing'),
(167, 'Asia/Chungking'),
(168, 'Asia/Colombo'),
(169, 'Asia/Dacca'),
(170, 'Asia/Damascus'),
(171, 'Asia/Dhaka'),
(172, 'Asia/Dili'),
(173, 'Asia/Dubai'),
(174, 'Asia/Dushanbe'),
(175, 'Asia/Gaza'),
(176, 'Asia/Harbin'),
(177, 'Asia/Hong_Kong'),
(178, 'Asia/Hovd'),
(179, 'Asia/Irkutsk'),
(180, 'Asia/Istanbul'),
(181, 'Asia/Jakarta'),
(182, 'Asia/Jayapura'),
(183, 'Asia/Jerusalem'),
(184, 'Asia/Kabul'),
(185, 'Asia/Kamchatka'),
(186, 'Asia/Karachi'),
(187, 'Asia/Kashgar'),
(188, 'Asia/Katmandu'),
(189, 'Asia/Krasnoyarsk'),
(190, 'Asia/Kuala_Lumpur'),
(191, 'Asia/Kuching'),
(192, 'Asia/Kuwait'),
(193, 'Asia/Macao'),
(194, 'Asia/Macau'),
(195, 'Asia/Magadan'),
(196, 'Asia/Makassar'),
(197, 'Asia/Manila'),
(198, 'Asia/Muscat'),
(199, 'Asia/Nicosia'),
(200, 'Asia/Novosibirsk'),
(201, 'Asia/Omsk'),
(202, 'Asia/Oral'),
(203, 'Asia/Phnom_Penh'),
(204, 'Asia/Pontianak'),
(205, 'Asia/Pyongyang'),
(206, 'Asia/Qatar'),
(207, 'Asia/Qyzylorda'),
(208, 'Asia/Rangoon'),
(209, 'Asia/Riyadh'),
(210, 'Asia/Saigon'),
(211, 'Asia/Sakhalin'),
(212, 'Asia/Samarkand'),
(213, 'Asia/Seoul'),
(214, 'Asia/Shanghai'),
(215, 'Asia/Singapore'),
(216, 'Asia/Taipei'),
(217, 'Asia/Tashkent'),
(218, 'Asia/Tbilisi'),
(219, 'Asia/Tehran'),
(220, 'Asia/Tel_Aviv'),
(221, 'Asia/Thimbu'),
(222, 'Asia/Thimphu'),
(223, 'Asia/Tokyo'),
(224, 'Asia/Ujung_Pandang'),
(225, 'Asia/Ulaanbaatar'),
(226, 'Asia/Ulan_Bator'),
(227, 'Asia/Urumqi'),
(228, 'Asia/Vientiane'),
(229, 'Asia/Vladivostok'),
(230, 'Asia/Yakutsk'),
(231, 'Asia/Yekaterinburg'),
(232, 'Asia/Yerevan'),
(233, 'Atlantic/Azores'),
(234, 'Atlantic/Bermuda'),
(235, 'Atlantic/Canary'),
(236, 'Atlantic/Cape_Verde'),
(237, 'Atlantic/Faeroe'),
(238, 'Atlantic/Faroe'),
(239, 'Atlantic/Jan_Mayen'),
(240, 'Atlantic/Madeira'),
(241, 'Atlantic/Reykjavik'),
(242, 'Atlantic/St_Helena'),
(243, 'Atlantic/Stanley'),
(244, 'Europe/Amsterdam'),
(245, 'Europe/Andorra'),
(246, 'Europe/Athens'),
(247, 'Europe/Belfast'),
(248, 'Europe/Belgrade'),
(249, 'Europe/Berlin'),
(250, 'Europe/Bratislava'),
(251, 'Europe/Brussels'),
(252, 'Europe/Bucharest'),
(253, 'Europe/Budapest'),
(254, 'Europe/Chisinau'),
(255, 'Europe/Copenhagen'),
(256, 'Europe/Dublin'),
(257, 'Europe/Gibraltar'),
(258, 'Europe/Guernsey'),
(259, 'Europe/Helsinki'),
(260, 'Europe/Isle_of_Man'),
(261, 'Europe/Istanbul'),
(262, 'Europe/Jersey'),
(263, 'Europe/Kaliningrad'),
(264, 'Europe/Kiev'),
(265, 'Europe/Lisbon'),
(266, 'Europe/Ljubljana'),
(267, 'Europe/London'),
(268, 'Europe/Luxembourg'),
(269, 'Europe/Madrid'),
(270, 'Europe/Malta'),
(271, 'Europe/Mariehamn'),
(272, 'Europe/Minsk'),
(273, 'Europe/Monaco'),
(274, 'Europe/Moscow'),
(275, 'Europe/Nicosia'),
(276, 'Europe/Oslo'),
(277, 'Europe/Paris'),
(278, 'Europe/Podgorica'),
(279, 'Europe/Prague'),
(280, 'Europe/Riga'),
(281, 'Europe/Rome'),
(282, 'Europe/Samara'),
(283, 'Europe/San_Marino'),
(284, 'Europe/Sarajevo'),
(285, 'Europe/Simferopol'),
(286, 'Europe/Skopje'),
(287, 'Europe/Sofia'),
(288, 'Europe/Stockholm'),
(289, 'Europe/Tallinn'),
(290, 'Europe/Tirane'),
(291, 'Europe/Tiraspol'),
(292, 'Europe/Uzhgorod'),
(293, 'Europe/Vaduz'),
(294, 'Europe/Vatican'),
(295, 'Europe/Vienna'),
(296, 'Europe/Vilnius'),
(297, 'Europe/Volgograd'),
(298, 'Europe/Warsaw'),
(299, 'Europe/Zagreb'),
(300, 'Europe/Zaporozhye'),
(301, 'Europe/Zurich'),
(302, 'Indian/Antananarivo'),
(303, 'Indian/Chagos'),
(304, 'Indian/Comoro'),
(305, 'Indian/Kerguelen'),
(306, 'Indian/Mahe'),
(307, 'Indian/Maldives'),
(308, 'Indian/Mauritius'),
(309, 'Indian/Mayotte'),
(310, 'Indian/Reunion'),
(311, 'Pacific/Apia'),
(312, 'Pacific/Auckland'),
(313, 'Pacific/Chatham'),
(314, 'Pacific/Easter'),
(315, 'Pacific/Efate'),
(316, 'Pacific/Enderbury'),
(317, 'Pacific/Fiji'),
(318, 'Pacific/Galapagos'),
(319, 'Pacific/Gambier'),
(320, 'Pacific/Guadalcanal'),
(321, 'Pacific/Guam'),
(322, 'Pacific/Honolulu'),
(323, 'Pacific/Kiritimati'),
(324, 'Pacific/Kosrae'),
(325, 'Pacific/Kwajalein'),
(326, 'Pacific/Majuro'),
(327, 'Pacific/Marquesas'),
(328, 'Pacific/Midway'),
(329, 'Pacific/Nauru'),
(330, 'Pacific/Niue'),
(331, 'Pacific/Norfolk'),
(332, 'Pacific/Noumea'),
(333, 'Pacific/Pago_Pago'),
(334, 'Pacific/Pitcairn'),
(335, 'Pacific/Rarotonga'),
(336, 'Pacific/Saipan'),
(337, 'Pacific/Samoa'),
(338, 'Pacific/Tahiti'),
(339, 'Pacific/Tongatapu');

/* PHP:blocknewsletter(); */;
/* PHP:set_payment_module_group(); */;
