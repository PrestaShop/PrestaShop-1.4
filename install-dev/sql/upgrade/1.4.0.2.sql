SET NAMES 'utf8';

ALTER TABLE `PREFIX_employee` ADD `bo_color` varchar(32) default NULL AFTER `stats_date_to`;
ALTER TABLE `PREFIX_employee` ADD `bo_theme` varchar(32) default NULL AFTER `bo_color`;
ALTER TABLE `PREFIX_employee` ADD `id_lang` int(10) unsigned NOT NULL default 0 AFTER `id_profile`;

UPDATE `PREFIX_employee` SET `id_lang` = (SELECT `value` FROM `PREFIX_configuration` WHERE `name` LIKE "PS_LANG_DEFAULT");

ALTER TABLE `PREFIX_customer` ADD `note` text AFTER `secure_key`;

ALTER TABLE `PREFIX_contact` ADD `customer_service` tinyint(1) NOT NULL DEFAULT 0 AFTER `email`;

CREATE TABLE `PREFIX_customer_thread` (
  `id_customer_thread` int(11) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `id_contact` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned default NULL,
  `id_order` int(10) unsigned default NULL,
  `id_product` int(10) unsigned default NULL,
  `status` enum('open','closed','pending1','pending2') NOT NULL default 'open',
  `email` varchar(128) NOT NULL,
  `token` varchar(12) default NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_customer_thread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customer_message` (
  `id_customer_message` int(10) unsigned NOT NULL auto_increment,
  `id_customer_thread` int(11) default NULL,
  `id_employee` int(10) unsigned default NULL,
  `message` text NOT NULL,
  `ip_address` int(11) default NULL,
  `user_agent` varchar(128) default NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_customer_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_payment_cc` (
	`id_payment_cc` INT NOT NULL auto_increment,
	`id_order` INT UNSIGNED NULL,
	`id_currency` INT UNSIGNED NOT NULL,
	`amount` DECIMAL(10,2) NOT NULL,
	`transaction_id` VARCHAR(254) NULL,
	`card_number` VARCHAR(254) NULL,
	`card_brand` VARCHAR(254) NULL,
	`card_expiration` DATE NULL,
	`card_holder` VARCHAR(254) NULL,
	`date_add` DATETIME NOT NULL,
	PRIMARY KEY (`id_payment_cc`),
	KEY `id_order` (`id_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `PREFIX_currency` ADD `iso_code_num` varchar(3) NOT NULL default '0' AFTER `iso_code`;
UPDATE `PREFIX_currency` SET iso_code_num = '978' WHERE iso_code LIKE 'EUR' LIMIT 1;
UPDATE `PREFIX_currency` SET iso_code_num = '840' WHERE iso_code LIKE 'USD' LIMIT 1;
UPDATE `PREFIX_currency` SET iso_code_num = '826' WHERE iso_code LIKE 'GBP' LIMIT 1;

ALTER TABLE `PREFIX_country` ADD `call_prefix` int(10) NOT NULL default '0' AFTER `iso_code`;

UPDATE `PREFIX_country` SET `call_prefix` = 49 WHERE `iso_code` = 'DE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 43 WHERE `iso_code` = 'AT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 32 WHERE `iso_code` = 'BE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 1 WHERE `iso_code` = 'CA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 86 WHERE `iso_code` = 'CN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 34 WHERE `iso_code` = 'ES' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 358 WHERE `iso_code` = 'FI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 33 WHERE `iso_code` = 'FR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 30 WHERE `iso_code` = 'GR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 39 WHERE `iso_code` = 'IT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 81 WHERE `iso_code` = 'JP' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 352 WHERE `iso_code` = 'LU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 31 WHERE `iso_code` = 'NL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 48 WHERE `iso_code` = 'PL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 351 WHERE `iso_code` = 'PT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 420 WHERE `iso_code` = 'CZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 44 WHERE `iso_code` = 'GB' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 46 WHERE `iso_code` = 'SE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 41 WHERE `iso_code` = 'CH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 45 WHERE `iso_code` = 'DK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 1 WHERE `iso_code` = 'US' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 852 WHERE `iso_code` = 'HK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 47 WHERE `iso_code` = 'NO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 61 WHERE `iso_code` = 'AU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 65 WHERE `iso_code` = 'SG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 353 WHERE `iso_code` = 'IE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 64 WHERE `iso_code` = 'NZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 82 WHERE `iso_code` = 'KR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 972 WHERE `iso_code` = 'IL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 27 WHERE `iso_code` = 'ZA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 234 WHERE `iso_code` = 'NG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 225 WHERE `iso_code` = 'CI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 228 WHERE `iso_code` = 'TG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 591 WHERE `iso_code` = 'BO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 230 WHERE `iso_code` = 'MU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 40 WHERE `iso_code` = 'RO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 421 WHERE `iso_code` = 'SK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 213 WHERE `iso_code` = 'DZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 376 WHERE `iso_code` = 'AD' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 244 WHERE `iso_code` = 'AO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 54 WHERE `iso_code` = 'AR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 374 WHERE `iso_code` = 'AM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 297 WHERE `iso_code` = 'AW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 994 WHERE `iso_code` = 'AZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 973 WHERE `iso_code` = 'BH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 880 WHERE `iso_code` = 'BD' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 501 WHERE `iso_code` = 'BZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 229 WHERE `iso_code` = 'BJ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 975 WHERE `iso_code` = 'BT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 267 WHERE `iso_code` = 'BW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 55 WHERE `iso_code` = 'BR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 673 WHERE `iso_code` = 'BN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 226 WHERE `iso_code` = 'BF' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 95 WHERE `iso_code` = 'MM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 257 WHERE `iso_code` = 'BI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 855 WHERE `iso_code` = 'KH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 237 WHERE `iso_code` = 'CM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 238 WHERE `iso_code` = 'CV' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 236 WHERE `iso_code` = 'CF' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 235 WHERE `iso_code` = 'TD' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 56 WHERE `iso_code` = 'CL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 57 WHERE `iso_code` = 'CO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 269 WHERE `iso_code` = 'KM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 242 WHERE `iso_code` = 'CD' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 243 WHERE `iso_code` = 'CG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 506 WHERE `iso_code` = 'CR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 385 WHERE `iso_code` = 'HR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 53 WHERE `iso_code` = 'CU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 357 WHERE `iso_code` = 'CY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 253 WHERE `iso_code` = 'DJ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 670 WHERE `iso_code` = 'TL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 593 WHERE `iso_code` = 'EC' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 20 WHERE `iso_code` = 'EG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 503 WHERE `iso_code` = 'SV' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 240 WHERE `iso_code` = 'GQ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 291 WHERE `iso_code` = 'ER' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 372 WHERE `iso_code` = 'EE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 251 WHERE `iso_code` = 'ET' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 298 WHERE `iso_code` = 'FO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 679 WHERE `iso_code` = 'FJ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 241 WHERE `iso_code` = 'GA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 220 WHERE `iso_code` = 'GM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 995 WHERE `iso_code` = 'GE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 233 WHERE `iso_code` = 'GH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 299 WHERE `iso_code` = 'GL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 350 WHERE `iso_code` = 'GI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 590 WHERE `iso_code` = 'GP' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 502 WHERE `iso_code` = 'GT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 224 WHERE `iso_code` = 'GN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 245 WHERE `iso_code` = 'GW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 592 WHERE `iso_code` = 'GY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 509 WHERE `iso_code` = 'HT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 379 WHERE `iso_code` = 'VA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 504 WHERE `iso_code` = 'HN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 354 WHERE `iso_code` = 'IS' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 91 WHERE `iso_code` = 'IN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 62 WHERE `iso_code` = 'ID' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 98 WHERE `iso_code` = 'IR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 964 WHERE `iso_code` = 'IQ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 962 WHERE `iso_code` = 'JO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 7 WHERE `iso_code` = 'KZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 254 WHERE `iso_code` = 'KE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 686 WHERE `iso_code` = 'KI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 850 WHERE `iso_code` = 'KP' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 965 WHERE `iso_code` = 'KW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 996 WHERE `iso_code` = 'KG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 856 WHERE `iso_code` = 'LA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 371 WHERE `iso_code` = 'LV' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 961 WHERE `iso_code` = 'LB' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 266 WHERE `iso_code` = 'LS' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 231 WHERE `iso_code` = 'LR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 218 WHERE `iso_code` = 'LY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 423 WHERE `iso_code` = 'LI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 370 WHERE `iso_code` = 'LT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 853 WHERE `iso_code` = 'MO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 389 WHERE `iso_code` = 'MK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 261 WHERE `iso_code` = 'MG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 265 WHERE `iso_code` = 'MW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 60 WHERE `iso_code` = 'MY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 960 WHERE `iso_code` = 'MV' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 223 WHERE `iso_code` = 'ML' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 356 WHERE `iso_code` = 'MT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 692 WHERE `iso_code` = 'MH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 596 WHERE `iso_code` = 'MQ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 222 WHERE `iso_code` = 'MR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 36 WHERE `iso_code` = 'HU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 262 WHERE `iso_code` = 'YT' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 52 WHERE `iso_code` = 'MX' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 691 WHERE `iso_code` = 'FM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 373 WHERE `iso_code` = 'MD' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 377 WHERE `iso_code` = 'MC' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 976 WHERE `iso_code` = 'MN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 382 WHERE `iso_code` = 'ME' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 212 WHERE `iso_code` = 'MA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 258 WHERE `iso_code` = 'MZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 264 WHERE `iso_code` = 'NA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 674 WHERE `iso_code` = 'NR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 977 WHERE `iso_code` = 'NP' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 599 WHERE `iso_code` = 'AN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 687 WHERE `iso_code` = 'NC' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 505 WHERE `iso_code` = 'NI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 227 WHERE `iso_code` = 'NE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 683 WHERE `iso_code` = 'NU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 968 WHERE `iso_code` = 'OM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 92 WHERE `iso_code` = 'PK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 680 WHERE `iso_code` = 'PW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 507 WHERE `iso_code` = 'PA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 675 WHERE `iso_code` = 'PG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 595 WHERE `iso_code` = 'PY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 51 WHERE `iso_code` = 'PE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 63 WHERE `iso_code` = 'PH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 974 WHERE `iso_code` = 'QA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 262 WHERE `iso_code` = 'RE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 7 WHERE `iso_code` = 'RU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 250 WHERE `iso_code` = 'RW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 508 WHERE `iso_code` = 'PM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 685 WHERE `iso_code` = 'WS' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 378 WHERE `iso_code` = 'SM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 239 WHERE `iso_code` = 'ST' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 966 WHERE `iso_code` = 'SA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 221 WHERE `iso_code` = 'SN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 381 WHERE `iso_code` = 'RS' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 248 WHERE `iso_code` = 'SC' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 232 WHERE `iso_code` = 'SL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 386 WHERE `iso_code` = 'SI' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 677 WHERE `iso_code` = 'SB' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 252 WHERE `iso_code` = 'SO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 94 WHERE `iso_code` = 'LK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 249 WHERE `iso_code` = 'SD' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 597 WHERE `iso_code` = 'SR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 268 WHERE `iso_code` = 'SZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 963 WHERE `iso_code` = 'SY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 886 WHERE `iso_code` = 'TW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 992 WHERE `iso_code` = 'TJ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 255 WHERE `iso_code` = 'TZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 66 WHERE `iso_code` = 'TH' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 690 WHERE `iso_code` = 'TK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 676 WHERE `iso_code` = 'TO' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 216 WHERE `iso_code` = 'TN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 90 WHERE `iso_code` = 'TR' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 993 WHERE `iso_code` = 'TM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 688 WHERE `iso_code` = 'TV' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 256 WHERE `iso_code` = 'UG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 380 WHERE `iso_code` = 'UA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 971 WHERE `iso_code` = 'AE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 598 WHERE `iso_code` = 'UY' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 998 WHERE `iso_code` = 'UZ' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 678 WHERE `iso_code` = 'VU' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 58 WHERE `iso_code` = 'VE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 84 WHERE `iso_code` = 'VN' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 681 WHERE `iso_code` = 'WF' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 967 WHERE `iso_code` = 'YE' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 260 WHERE `iso_code` = 'ZM' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 263 WHERE `iso_code` = 'ZW' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 355 WHERE `iso_code` = 'AL' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 93 WHERE `iso_code` = 'AF' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 387 WHERE `iso_code` = 'BA' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 359 WHERE `iso_code` = 'BG' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 682 WHERE `iso_code` = 'CK' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 594 WHERE `iso_code` = 'GF' LIMIT 1;
UPDATE `PREFIX_country` SET `call_prefix` = 689 WHERE `iso_code` = 'PF' LIMIT 1;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_CONDITIONS_CMS_ID', IFNULL((SELECT `id_cms` FROM `PREFIX_cms` WHERE `id_cms` = 3), 0), NOW(), NOW());
UPDATE `PREFIX_configuration` SET `value` = IF((SELECT value FROM (SELECT `value` FROM `PREFIX_configuration` WHERE `name` = 'PS_CONDITIONS_CMS_ID')tmp), 1, 0) WHERE `name` = 'PS_CONDITIONS';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_CIPHER_ALGORITHM', 0, NOW(), NOW());

ALTER TABLE `PREFIX_product` ADD `minimal_quantity` INT NOT NULL DEFAULT '1' AFTER `quantity`;
ALTER TABLE `PREFIX_product` ADD `cache_default_attribute` int(10) unsigned default NULL AFTER `indexed`;
ALTER TABLE `PREFIX_product` ADD `cache_has_attachments` tinyint(1) NOT NULL default '0' AFTER `indexed`;
ALTER TABLE `PREFIX_product` ADD `cache_is_pack` tinyint(1) NOT NULL default '0' AFTER `indexed`;

SET @defaultOOS = (SELECT value FROM `PREFIX_configuration` WHERE name = 'PS_ORDER_OUT_OF_STOCK');
/* Set 0 for every non-attribute product */
UPDATE `PREFIX_product` p SET cache_default_attribute =  0 WHERE id_product NOT IN (SELECT id_product FROM `PREFIX_product_attribute`);
/* First default attribute in stock */
UPDATE `PREFIX_product` p SET cache_default_attribute = (SELECT id_product_attribute FROM `PREFIX_product_attribute` WHERE id_product = p.id_product AND default_on = 1 AND quantity > 0 LIMIT 1) WHERE cache_default_attribute IS NULL;
/* Then default attribute without stock if we don't care */
UPDATE `PREFIX_product` p SET cache_default_attribute = (SELECT id_product_attribute FROM `PREFIX_product_attribute` WHERE id_product = p.id_product AND default_on = 1 LIMIT 1) WHERE cache_default_attribute IS NULL AND out_of_stock = 1 OR out_of_stock = IF(@defaultOOS = 1, 2, 1);
/* Next, the default attribute can be any attribute with stock */
UPDATE `PREFIX_product` p SET cache_default_attribute = (SELECT id_product_attribute FROM `PREFIX_product_attribute` WHERE id_product = p.id_product AND quantity > 0 LIMIT 1) WHERE cache_default_attribute IS NULL;
/* If there is still no default attribute, then we go back to the default one */
UPDATE `PREFIX_product` p SET cache_default_attribute = (SELECT id_product_attribute FROM `PREFIX_product_attribute` WHERE id_product = p.id_product AND default_on = 1 LIMIT 1) WHERE cache_default_attribute IS NULL;

UPDATE `PREFIX_product` p SET
cache_is_pack = (SELECT IF(COUNT(*) > 0, 1, 0) FROM `PREFIX_pack` pp WHERE pp.id_product_pack = p.id_product),
cache_has_attachments = (SELECT IF(COUNT(*) > 0, 1, 0) FROM `PREFIX_product_attachment` pa WHERE pa.id_product = p.id_product);

INSERT INTO `PREFIX_tab` (`id_parent`, `class_name`, `module`, `position`) VALUES (9, 'AdminInformation', '', 11);
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (1, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminInformation'
), 'Informations');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (2, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminInformation'
), 'Informations');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (3, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminInformation'
), 'Informations');

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (
	SELECT `id_profile`, (
		SELECT `id_tab`
		FROM `PREFIX_tab`
		WHERE `class_name` = 'AdminInformation'
	), 1, 1, 1, 1 FROM `PREFIX_profile`
);

INSERT INTO `PREFIX_tab` (`id_parent`, `class_name`, `module`, `position`) VALUES (8, 'AdminPerformance', '', 11);
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (1, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminPerformance'
), 'Performance');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (2, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminPerformance'
), 'Performances');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (3, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminPerformance'
), 'Rendimiento');

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (
	SELECT `id_profile`, (
		SELECT `id_tab`
		FROM `PREFIX_tab`
		WHERE `class_name` = 'AdminPerformance'
	), 1, 1, 1, 1 FROM `PREFIX_profile`
);

INSERT INTO `PREFIX_tab` (`id_parent`, `class_name`, `module`, `position`) VALUES (29, 'AdminCustomerThreads', '', 4);
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (1, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminCustomerThreads'
), 'Customer Service');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (2, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminCustomerThreads'
), 'SAV');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (3, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminCustomerThreads'
), 'Servicio al cliente');

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (
	SELECT `id_profile`, (
		SELECT `id_tab`
		FROM `PREFIX_tab`
		WHERE `class_name` = 'AdminCustomerThreads'
	), 1, 1, 1, 1 FROM `PREFIX_profile`
);

INSERT INTO `PREFIX_tab` (`id_parent`, `class_name`, `module`, `position`) VALUES (8, 'AdminWebservice', '', 11);
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (1, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminWebservice'
), 'Web service');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (2, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminWebservice'
), 'Service web');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (3, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminWebservice'
), 'Web service');

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (
	SELECT `id_profile`, (
		SELECT `id_tab`
		FROM `PREFIX_tab`
		WHERE `class_name` = 'AdminWebservice'
	), 1, 1, 1, 1 FROM `PREFIX_profile`
);

INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES ('deleteProductAttribute', 'Product Attribute Deletion', NULL, 0);

INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES 
((SELECT IFNULL((SELECT `id_module` FROM `PREFIX_module` WHERE `name` = 'mailalerts'), 0)),
(SELECT `id_hook` FROM `PREFIX_hook` WHERE `name` = 'deleteProductAttribute'), 1);

DELETE FROM `PREFIX_hook_module` WHERE `id_module` = 0;

ALTER TABLE `PREFIX_country` ADD `need_zip_code` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE `PREFIX_country` ADD `zip_code_format` VARCHAR(12) NOT NULL DEFAULT '';

ALTER TABLE `PREFIX_product` ADD `weight_price` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `wholesale_price`;
ALTER TABLE `PREFIX_product` ADD `volume_price` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `weight_price` ;
ALTER TABLE `PREFIX_product` ADD `unity_price` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `volume_price`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_VOLUME_UNIT', 'cl', NOW(), NOW());


ALTER TABLE `PREFIX_carrier` ADD `shipping_external` TINYINT( 1 ) UNSIGNED NOT NULL;
ALTER TABLE `PREFIX_carrier` ADD `external_module_name` varchar(64) NOT NULL;
ALTER TABLE `PREFIX_carrier` ADD `need_range` TINYINT( 1 ) UNSIGNED NOT NULL;

INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES ('processCarrier', 'Carrier Process', NULL, 0);
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES ('orderDetail', 'Order Detail', 'To set the follow-up in smarty when order detail is called', 0);

ALTER TABLE `PREFIX_address` ADD `vat_number` varchar(32) NULL DEFAULT NULL AFTER `phone_mobile`;
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_TAX_ADDRESS_TYPE', 'id_address_delivery', NOW(), NOW());

ALTER TABLE `PREFIX_product` ADD `additional_shipping_cost` DECIMAL(20,2) NOT NULL AFTER `reduction_to`;

/* PHP */
/* PHP:editorial_update(); */;
