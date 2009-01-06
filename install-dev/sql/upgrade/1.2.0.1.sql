SET NAMES 'utf8';

/* ##################################### */
/* 					STRUCTURE				  */
/* ##################################### */

ALTER TABLE PREFIX_cart
	ADD id_guest INT UNSIGNED NULL AFTER id_customer; 

ALTER TABLE PREFIX_customer
	ADD deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER active;

CREATE TABLE PREFIX_customer_group (
  id_customer INTEGER UNSIGNED NOT NULL,
  id_group INTEGER UNSIGNED NOT NULL,
  INDEX customer_group_index(id_customer, id_group)
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
  PRIMARY KEY  (id_message,id_employee)
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
  cache_visitors INTEGER NULL,
  cache_visits INTEGER NULL,
  cache_pages INTEGER NULL,
  cache_registrations INTEGER NULL,
  cache_orders INTEGER NULL,
  cache_sales DECIMAL(10,2) NULL,
  cache_reg_rate DECIMAL(8,4) NULL,
  cache_order_rate DECIMAL(8,4) NULL,
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

/* ##################################### */
/* 					CONTENTS				*/
/* ##################################### */

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


/* PHP:blocknewsletter(); */;
/* PHP:set_payment_module_group(); */;