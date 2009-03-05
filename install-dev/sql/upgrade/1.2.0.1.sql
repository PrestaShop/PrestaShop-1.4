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
	DROP INDEX `orders_customer`,
	ADD INDEX id_customer (id_customer),
	ADD valid INTEGER(1) UNSIGNED NOT NULL DEFAULT '0' AFTER delivery_date,
	ADD INDEX `id_cart` (`id_cart`);

ALTER TABLE PREFIX_customer
	ADD deleted TINYINT(1) NOT NULL DEFAULT '0' AFTER active;

ALTER TABLE PREFIX_employee
	ADD stats_date_to DATE NULL DEFAULT NULL AFTER last_passwd_gen,
	ADD stats_date_from DATE NULL DEFAULT NULL AFTER last_passwd_gen;

ALTER TABLE PREFIX_order_state
	ADD hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER unremovable;

ALTER TABLE PREFIX_carrier
	ADD is_module TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER range_behavior,
	ADD INDEX deleted (`deleted`, `active`);

ALTER TABLE PREFIX_order_detail
	CHANGE product_quantity_cancelled product_quantity_refunded INT(10) UNSIGNED NOT NULL DEFAULT '0',
	ADD INDEX product_id (product_id);

ALTER TABLE PREFIX_attribute_lang
	ADD INDEX id_lang (`id_lang`, `name`),
	ADD INDEX id_lang_2 (`id_lang`),
	ADD INDEX id_attribute (`id_attribute`);

ALTER TABLE PREFIX_block_cms
	ADD PRIMARY KEY (`id_block`, `id_cms`);

ALTER TABLE PREFIX_carrier_zone
	ADD INDEX `id_carrier` (`id_carrier`);

ALTER TABLE PREFIX_connections
	CHANGE `http_referer` `http_referer` VARCHAR(255) DEFAULT NULL,
	ADD INDEX `date_add` (`date_add`);

ALTER TABLE PREFIX_customer
	DROP INDEX `customer_email`,
	ADD UNIQUE `customer_email` (`email`);

ALTER TABLE PREFIX_delivery
	ADD INDEX id_zone (`id_zone`),
	ADD INDEX id_carrier (`id_carrier`, `id_zone`);

ALTER TABLE PREFIX_discount_category
	ADD INDEX id_discount (`id_discount`),
	ADD INDEX id_category (`id_category`);

ALTER TABLE PREFIX_feature_product
	ADD INDEX `id_feature` (`id_feature`);

ALTER TABLE PREFIX_hook_module
	DROP INDEX `hook_module_index`,
	ADD PRIMARY KEY (id_module,id_hook),
	ADD INDEX id_module (`id_module`),
	ADD INDEX id_hook (`id_hook`);

ALTER TABLE PREFIX_module
	CHANGE `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE PREFIX_page
	CHANGE `id_object` `id_object` INT UNSIGNED NULL DEFAULT NULL,
	ADD INDEX `id_page_type` (`id_page_type`),
	ADD INDEX `id_object` (`id_object`);

ALTER TABLE PREFIX_page_type
	ADD INDEX `name` (`name`),
	CHANGE `name` `name` VARCHAR(255) NOT NULL;
	
ALTER TABLE PREFIX_product_attribute
	ADD INDEX reference (reference),
	ADD INDEX supplier_reference (supplier_reference);

ALTER TABLE PREFIX_product_lang
	ADD INDEX id_product (id_product),
	ADD INDEX id_lang (id_lang),
	ADD INDEX `name` (`name`),
	ADD FULLTEXT KEY ftsname (`name`);

ALTER TABLE PREFIX_discount_category
	ADD PRIMARY KEY (id_discount,id_category);

ALTER TABLE PREFIX_image_lang
	ADD INDEX id_image (id_image);

ALTER TABLE PREFIX_range_price
	CHANGE `delimiter1` `delimiter1` DECIMAL(13, 6) NOT NULL,
	CHANGE `delimiter2` `delimiter2` DECIMAL(13, 6) NOT NULL,
	CHANGE `id_carrier` `id_carrier` INT(10) UNSIGNED NOT NULL,
	DROP INDEX `range_price_unique`,
	ADD UNIQUE KEY `id_carrier` (`id_carrier`,`delimiter1`,`delimiter2`);

ALTER TABLE PREFIX_range_weight
	CHANGE `delimiter1` `delimiter1` DECIMAL(13, 6) NOT NULL
	CHANGE `delimiter2` `delimiter2` DECIMAL(13, 6) NOT NULL
	CHANGE `id_carrier` `id_carrier` INT(10) UNSIGNED NOT NULL,
	DROP INDEX `range_weight_unique`,
	ADD UNIQUE KEY `id_carrier` (`id_carrier`,`delimiter1`,`delimiter2`);

/* ############################################################ */

CREATE TABLE `PREFIX_customer_group` (
	`id_customer` int(10) unsigned NOT NULL,
	`id_group` int(10) unsigned NOT NULL,
	KEY `customer_group_index` (`id_customer`,`id_group`),
	KEY `id_customer` (`id_customer`)
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

CREATE TABLE IF NOT EXISTS `PREFIX_referrer` (
	id_referrer INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(64) NOT NULL,
	passwd VARCHAR(32) NULL,
	http_referer_regexp VARCHAR(64) NULL,
	http_referer_like VARCHAR(64) NULL,
	request_uri_regexp VARCHAR(64) NULL,
	request_uri_like VARCHAR(64) NULL,
	http_referer_regexp_not VARCHAR(64) NULL,
	http_referer_like_not VARCHAR(64) NULL,
	request_uri_regexp_not VARCHAR(64) NULL,
	request_uri_like_not VARCHAR(64) NULL,
	base_fee DECIMAL(4, 2) NOT NULL DEFAULT 0,
	percent_fee DECIMAL(3, 2) NOT NULL DEFAULT 0,
	click_fee decimal(3,2) NOT NULL default '0.00',
	cache_visitors INTEGER NULL,
	cache_visits INTEGER NULL,
	cache_pages INTEGER NULL,
	cache_registrations INTEGER NULL,
	cache_orders INTEGER NULL,
	cache_sales DECIMAL(10,2) NULL,
	cache_reg_rate DECIMAL(5,2) NULL,
	cache_order_rate DECIMAL(5,2) NULL,
	date_add DATETIME NOT NULL,
	PRIMARY KEY (`id_referrer`)
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
	KEY `id_product_attribute` (`id_product_attribute`),
	KEY `id_image` (`id_image`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_index` (
  `id_product` int(11) NOT NULL,
  `id_word` int(11) NOT NULL,
  `weight` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id_product`,`id_word`),
  INDEX  (`id_word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_word` (
  `id_word` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `word` varchar(15) NOT NULL,
  PRIMARY KEY  (`id_word`),
  UNIQUE KEY `id_lang` (`id_lang`,`word`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/* ##################################### */
/* 					CONTENTS					 */
/* ##################################### */

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_SEARCH_MINWORDLEN', '3', NOW(), NOW()),
('PS_SEARCH_WEIGHT_PNAME', '6', NOW(), NOW()),
('PS_SEARCH_WEIGHT_REF', '10', NOW(), NOW()),
('PS_SEARCH_WEIGHT_SHORTDESC', '1', NOW(), NOW()),
('PS_SEARCH_WEIGHT_DESC', '1', NOW(), NOW()),
('PS_SEARCH_WEIGHT_CNAME', '3', NOW(), NOW()),
('PS_SEARCH_WEIGHT_MNAME', '3', NOW(), NOW()),
('PS_SEARCH_WEIGHT_TAG', '4', NOW(), NOW()),
('PS_SEARCH_WEIGHT_ATTRIBUTE', '2', NOW(), NOW()),
('PS_SEARCH_WEIGHT_FEATURE', '2', NOW(), NOW());

INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('extraCarrier', 'Extra carrier (module mode)', NULL, 0),
	('shoppingCartExtra', 'Shopping cart extra button', 'Display some specific informations', 0),
	('search', 'Search', NULL, 0);

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

INSERT INTO PREFIX_tab (id_parent, class_name, position) VALUES ((SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminTools' LIMIT 1) AS tmp), 'AdminHtaccess', (SELECT tmp.max FROM (SELECT MAX(position) max FROM `PREFIX_tab` WHERE id_parent = (SELECT tmp.`id_tab` FROM (SELECT `id_tab` FROM PREFIX_tab t WHERE t.class_name = 'AdminTools' LIMIT 1) AS tmp )) AS tmp));
INSERT INTO PREFIX_tab_lang (id_lang, id_tab, name) (
	SELECT id_lang,
	(SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminHtaccess' LIMIT 1),
	'Htaccess' FROM PREFIX_lang);
INSERT INTO PREFIX_access (id_profile, id_tab, `view`, `add`, edit, `delete`) VALUES ('1', (SELECT id_tab FROM PREFIX_tab t WHERE t.class_name = 'AdminHtaccess' LIMIT 1), 1, 1, 1, 1);

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

/* PHP:blocknewsletter(); */;
/* PHP:set_payment_module_group(); */;
