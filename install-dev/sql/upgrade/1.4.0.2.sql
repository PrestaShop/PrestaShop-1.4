SET NAMES 'utf8';

ALTER TABLE `PREFIX_country` ADD `call_prefix` int(10) NOT NULL default '0' AFTER `iso_code`;

UPDATE `PREFIX_country` SET `call_prefix` = 49 WHERE `iso_code` = 'DE';
UPDATE `PREFIX_country` SET `call_prefix` = 43 WHERE `iso_code` = 'AT';
UPDATE `PREFIX_country` SET `call_prefix` = 32 WHERE `iso_code` = 'BE';
UPDATE `PREFIX_country` SET `call_prefix` = 1 WHERE `iso_code` = 'CA';
UPDATE `PREFIX_country` SET `call_prefix` = 86 WHERE `iso_code` = 'CN';
UPDATE `PREFIX_country` SET `call_prefix` = 34 WHERE `iso_code` = 'ES';
UPDATE `PREFIX_country` SET `call_prefix` = 358 WHERE `iso_code` = 'FI';
UPDATE `PREFIX_country` SET `call_prefix` = 33 WHERE `iso_code` = 'FR';
UPDATE `PREFIX_country` SET `call_prefix` = 30 WHERE `iso_code` = 'GR';
UPDATE `PREFIX_country` SET `call_prefix` = 39 WHERE `iso_code` = 'IT';
UPDATE `PREFIX_country` SET `call_prefix` = 81 WHERE `iso_code` = 'JP';
UPDATE `PREFIX_country` SET `call_prefix` = 352 WHERE `iso_code` = 'LU';
UPDATE `PREFIX_country` SET `call_prefix` = 31 WHERE `iso_code` = 'NL';
UPDATE `PREFIX_country` SET `call_prefix` = 48 WHERE `iso_code` = 'PL';
UPDATE `PREFIX_country` SET `call_prefix` = 351 WHERE `iso_code` = 'PT';
UPDATE `PREFIX_country` SET `call_prefix` = 420 WHERE `iso_code` = 'CZ';
UPDATE `PREFIX_country` SET `call_prefix` = 44 WHERE `iso_code` = 'GB';
UPDATE `PREFIX_country` SET `call_prefix` = 46 WHERE `iso_code` = 'SE';
UPDATE `PREFIX_country` SET `call_prefix` = 41 WHERE `iso_code` = 'CH';
UPDATE `PREFIX_country` SET `call_prefix` = 45 WHERE `iso_code` = 'DK';
UPDATE `PREFIX_country` SET `call_prefix` = 1 WHERE `iso_code` = 'US';
UPDATE `PREFIX_country` SET `call_prefix` = 852 WHERE `iso_code` = 'HK';
UPDATE `PREFIX_country` SET `call_prefix` = 47 WHERE `iso_code` = 'NO';
UPDATE `PREFIX_country` SET `call_prefix` = 61 WHERE `iso_code` = 'AU';
UPDATE `PREFIX_country` SET `call_prefix` = 65 WHERE `iso_code` = 'SG';
UPDATE `PREFIX_country` SET `call_prefix` = 353 WHERE `iso_code` = 'IE';
UPDATE `PREFIX_country` SET `call_prefix` = 64 WHERE `iso_code` = 'NZ';
UPDATE `PREFIX_country` SET `call_prefix` = 82 WHERE `iso_code` = 'KR';
UPDATE `PREFIX_country` SET `call_prefix` = 972 WHERE `iso_code` = 'IL';
UPDATE `PREFIX_country` SET `call_prefix` = 27 WHERE `iso_code` = 'ZA';
UPDATE `PREFIX_country` SET `call_prefix` = 234 WHERE `iso_code` = 'NG';
UPDATE `PREFIX_country` SET `call_prefix` = 225 WHERE `iso_code` = 'CI';
UPDATE `PREFIX_country` SET `call_prefix` = 228 WHERE `iso_code` = 'TG';
UPDATE `PREFIX_country` SET `call_prefix` = 591 WHERE `iso_code` = 'BO';
UPDATE `PREFIX_country` SET `call_prefix` = 230 WHERE `iso_code` = 'MU';
UPDATE `PREFIX_country` SET `call_prefix` = 40 WHERE `iso_code` = 'RO';
UPDATE `PREFIX_country` SET `call_prefix` = 421 WHERE `iso_code` = 'SK';
UPDATE `PREFIX_country` SET `call_prefix` = 213 WHERE `iso_code` = 'DZ';
UPDATE `PREFIX_country` SET `call_prefix` = 376 WHERE `iso_code` = 'AD';
UPDATE `PREFIX_country` SET `call_prefix` = 244 WHERE `iso_code` = 'AO';
UPDATE `PREFIX_country` SET `call_prefix` = 54 WHERE `iso_code` = 'AR';
UPDATE `PREFIX_country` SET `call_prefix` = 374 WHERE `iso_code` = 'AM';
UPDATE `PREFIX_country` SET `call_prefix` = 297 WHERE `iso_code` = 'AW';
UPDATE `PREFIX_country` SET `call_prefix` = 994 WHERE `iso_code` = 'AZ';
UPDATE `PREFIX_country` SET `call_prefix` = 973 WHERE `iso_code` = 'BH';
UPDATE `PREFIX_country` SET `call_prefix` = 880 WHERE `iso_code` = 'BD';
UPDATE `PREFIX_country` SET `call_prefix` = 501 WHERE `iso_code` = 'BZ';
UPDATE `PREFIX_country` SET `call_prefix` = 229 WHERE `iso_code` = 'BJ';
UPDATE `PREFIX_country` SET `call_prefix` = 975 WHERE `iso_code` = 'BT';
UPDATE `PREFIX_country` SET `call_prefix` = 267 WHERE `iso_code` = 'BW';
UPDATE `PREFIX_country` SET `call_prefix` = 55 WHERE `iso_code` = 'BR';
UPDATE `PREFIX_country` SET `call_prefix` = 673 WHERE `iso_code` = 'BN';
UPDATE `PREFIX_country` SET `call_prefix` = 226 WHERE `iso_code` = 'BF';
UPDATE `PREFIX_country` SET `call_prefix` = 95 WHERE `iso_code` = 'MM';
UPDATE `PREFIX_country` SET `call_prefix` = 257 WHERE `iso_code` = 'BI';
UPDATE `PREFIX_country` SET `call_prefix` = 855 WHERE `iso_code` = 'KH';
UPDATE `PREFIX_country` SET `call_prefix` = 237 WHERE `iso_code` = 'CM';
UPDATE `PREFIX_country` SET `call_prefix` = 238 WHERE `iso_code` = 'CV';
UPDATE `PREFIX_country` SET `call_prefix` = 236 WHERE `iso_code` = 'CF';
UPDATE `PREFIX_country` SET `call_prefix` = 235 WHERE `iso_code` = 'TD';
UPDATE `PREFIX_country` SET `call_prefix` = 56 WHERE `iso_code` = 'CL';
UPDATE `PREFIX_country` SET `call_prefix` = 57 WHERE `iso_code` = 'CO';
UPDATE `PREFIX_country` SET `call_prefix` = 269 WHERE `iso_code` = 'KM';
UPDATE `PREFIX_country` SET `call_prefix` = 242 WHERE `iso_code` = 'CD';
UPDATE `PREFIX_country` SET `call_prefix` = 243 WHERE `iso_code` = 'CG';
UPDATE `PREFIX_country` SET `call_prefix` = 506 WHERE `iso_code` = 'CR';
UPDATE `PREFIX_country` SET `call_prefix` = 385 WHERE `iso_code` = 'HR';
UPDATE `PREFIX_country` SET `call_prefix` = 53 WHERE `iso_code` = 'CU';
UPDATE `PREFIX_country` SET `call_prefix` = 357 WHERE `iso_code` = 'CY';
UPDATE `PREFIX_country` SET `call_prefix` = 253 WHERE `iso_code` = 'DJ';
UPDATE `PREFIX_country` SET `call_prefix` = 670 WHERE `iso_code` = 'TL';
UPDATE `PREFIX_country` SET `call_prefix` = 593 WHERE `iso_code` = 'EC';
UPDATE `PREFIX_country` SET `call_prefix` = 20 WHERE `iso_code` = 'EG';
UPDATE `PREFIX_country` SET `call_prefix` = 503 WHERE `iso_code` = 'SV';
UPDATE `PREFIX_country` SET `call_prefix` = 240 WHERE `iso_code` = 'GQ';
UPDATE `PREFIX_country` SET `call_prefix` = 291 WHERE `iso_code` = 'ER';
UPDATE `PREFIX_country` SET `call_prefix` = 372 WHERE `iso_code` = 'EE';
UPDATE `PREFIX_country` SET `call_prefix` = 251 WHERE `iso_code` = 'ET';
UPDATE `PREFIX_country` SET `call_prefix` = 298 WHERE `iso_code` = 'FO';
UPDATE `PREFIX_country` SET `call_prefix` = 679 WHERE `iso_code` = 'FJ';
UPDATE `PREFIX_country` SET `call_prefix` = 241 WHERE `iso_code` = 'GA';
UPDATE `PREFIX_country` SET `call_prefix` = 220 WHERE `iso_code` = 'GM';
UPDATE `PREFIX_country` SET `call_prefix` = 995 WHERE `iso_code` = 'GE';
UPDATE `PREFIX_country` SET `call_prefix` = 233 WHERE `iso_code` = 'GH';
UPDATE `PREFIX_country` SET `call_prefix` = 299 WHERE `iso_code` = 'GL';
UPDATE `PREFIX_country` SET `call_prefix` = 350 WHERE `iso_code` = 'GI';
UPDATE `PREFIX_country` SET `call_prefix` = 590 WHERE `iso_code` = 'GP';
UPDATE `PREFIX_country` SET `call_prefix` = 502 WHERE `iso_code` = 'GT';
UPDATE `PREFIX_country` SET `call_prefix` = 224 WHERE `iso_code` = 'GN';
UPDATE `PREFIX_country` SET `call_prefix` = 245 WHERE `iso_code` = 'GW';
UPDATE `PREFIX_country` SET `call_prefix` = 592 WHERE `iso_code` = 'GY';
UPDATE `PREFIX_country` SET `call_prefix` = 509 WHERE `iso_code` = 'HT';
UPDATE `PREFIX_country` SET `call_prefix` = 379 WHERE `iso_code` = 'VA';
UPDATE `PREFIX_country` SET `call_prefix` = 504 WHERE `iso_code` = 'HN';
UPDATE `PREFIX_country` SET `call_prefix` = 354 WHERE `iso_code` = 'IS';
UPDATE `PREFIX_country` SET `call_prefix` = 91 WHERE `iso_code` = 'IN';
UPDATE `PREFIX_country` SET `call_prefix` = 62 WHERE `iso_code` = 'ID';
UPDATE `PREFIX_country` SET `call_prefix` = 98 WHERE `iso_code` = 'IR';
UPDATE `PREFIX_country` SET `call_prefix` = 964 WHERE `iso_code` = 'IQ';
UPDATE `PREFIX_country` SET `call_prefix` = 962 WHERE `iso_code` = 'JO';
UPDATE `PREFIX_country` SET `call_prefix` = 7 WHERE `iso_code` = 'KZ';
UPDATE `PREFIX_country` SET `call_prefix` = 254 WHERE `iso_code` = 'KE';
UPDATE `PREFIX_country` SET `call_prefix` = 686 WHERE `iso_code` = 'KI';
UPDATE `PREFIX_country` SET `call_prefix` = 850 WHERE `iso_code` = 'KP';
UPDATE `PREFIX_country` SET `call_prefix` = 965 WHERE `iso_code` = 'KW';
UPDATE `PREFIX_country` SET `call_prefix` = 996 WHERE `iso_code` = 'KG';
UPDATE `PREFIX_country` SET `call_prefix` = 856 WHERE `iso_code` = 'LA';
UPDATE `PREFIX_country` SET `call_prefix` = 371 WHERE `iso_code` = 'LV';
UPDATE `PREFIX_country` SET `call_prefix` = 961 WHERE `iso_code` = 'LB';
UPDATE `PREFIX_country` SET `call_prefix` = 266 WHERE `iso_code` = 'LS';
UPDATE `PREFIX_country` SET `call_prefix` = 231 WHERE `iso_code` = 'LR';
UPDATE `PREFIX_country` SET `call_prefix` = 218 WHERE `iso_code` = 'LY';
UPDATE `PREFIX_country` SET `call_prefix` = 423 WHERE `iso_code` = 'LI';
UPDATE `PREFIX_country` SET `call_prefix` = 370 WHERE `iso_code` = 'LT';
UPDATE `PREFIX_country` SET `call_prefix` = 853 WHERE `iso_code` = 'MO';
UPDATE `PREFIX_country` SET `call_prefix` = 389 WHERE `iso_code` = 'MK';
UPDATE `PREFIX_country` SET `call_prefix` = 261 WHERE `iso_code` = 'MG';
UPDATE `PREFIX_country` SET `call_prefix` = 265 WHERE `iso_code` = 'MW';
UPDATE `PREFIX_country` SET `call_prefix` = 60 WHERE `iso_code` = 'MY';
UPDATE `PREFIX_country` SET `call_prefix` = 960 WHERE `iso_code` = 'MV';
UPDATE `PREFIX_country` SET `call_prefix` = 223 WHERE `iso_code` = 'ML';
UPDATE `PREFIX_country` SET `call_prefix` = 356 WHERE `iso_code` = 'MT';
UPDATE `PREFIX_country` SET `call_prefix` = 692 WHERE `iso_code` = 'MH';
UPDATE `PREFIX_country` SET `call_prefix` = 596 WHERE `iso_code` = 'MQ';
UPDATE `PREFIX_country` SET `call_prefix` = 222 WHERE `iso_code` = 'MR';
UPDATE `PREFIX_country` SET `call_prefix` = 36 WHERE `iso_code` = 'HU';
UPDATE `PREFIX_country` SET `call_prefix` = 262 WHERE `iso_code` = 'YT';
UPDATE `PREFIX_country` SET `call_prefix` = 52 WHERE `iso_code` = 'MX';
UPDATE `PREFIX_country` SET `call_prefix` = 691 WHERE `iso_code` = 'FM';
UPDATE `PREFIX_country` SET `call_prefix` = 373 WHERE `iso_code` = 'MD';
UPDATE `PREFIX_country` SET `call_prefix` = 377 WHERE `iso_code` = 'MC';
UPDATE `PREFIX_country` SET `call_prefix` = 976 WHERE `iso_code` = 'MN';
UPDATE `PREFIX_country` SET `call_prefix` = 382 WHERE `iso_code` = 'ME';
UPDATE `PREFIX_country` SET `call_prefix` = 212 WHERE `iso_code` = 'MA';
UPDATE `PREFIX_country` SET `call_prefix` = 258 WHERE `iso_code` = 'MZ';
UPDATE `PREFIX_country` SET `call_prefix` = 264 WHERE `iso_code` = 'NA';
UPDATE `PREFIX_country` SET `call_prefix` = 674 WHERE `iso_code` = 'NR';
UPDATE `PREFIX_country` SET `call_prefix` = 977 WHERE `iso_code` = 'NP';
UPDATE `PREFIX_country` SET `call_prefix` = 599 WHERE `iso_code` = 'AN';
UPDATE `PREFIX_country` SET `call_prefix` = 687 WHERE `iso_code` = 'NC';
UPDATE `PREFIX_country` SET `call_prefix` = 505 WHERE `iso_code` = 'NI';
UPDATE `PREFIX_country` SET `call_prefix` = 227 WHERE `iso_code` = 'NE';
UPDATE `PREFIX_country` SET `call_prefix` = 683 WHERE `iso_code` = 'NU';
UPDATE `PREFIX_country` SET `call_prefix` = 968 WHERE `iso_code` = 'OM';
UPDATE `PREFIX_country` SET `call_prefix` = 92 WHERE `iso_code` = 'PK';
UPDATE `PREFIX_country` SET `call_prefix` = 680 WHERE `iso_code` = 'PW';
UPDATE `PREFIX_country` SET `call_prefix` = 507 WHERE `iso_code` = 'PA';
UPDATE `PREFIX_country` SET `call_prefix` = 675 WHERE `iso_code` = 'PG';
UPDATE `PREFIX_country` SET `call_prefix` = 595 WHERE `iso_code` = 'PY';
UPDATE `PREFIX_country` SET `call_prefix` = 51 WHERE `iso_code` = 'PE';
UPDATE `PREFIX_country` SET `call_prefix` = 63 WHERE `iso_code` = 'PH';
UPDATE `PREFIX_country` SET `call_prefix` = 974 WHERE `iso_code` = 'QA';
UPDATE `PREFIX_country` SET `call_prefix` = 262 WHERE `iso_code` = 'RE';
UPDATE `PREFIX_country` SET `call_prefix` = 7 WHERE `iso_code` = 'RU';
UPDATE `PREFIX_country` SET `call_prefix` = 250 WHERE `iso_code` = 'RW';
UPDATE `PREFIX_country` SET `call_prefix` = 508 WHERE `iso_code` = 'PM';
UPDATE `PREFIX_country` SET `call_prefix` = 685 WHERE `iso_code` = 'WS';
UPDATE `PREFIX_country` SET `call_prefix` = 378 WHERE `iso_code` = 'SM';
UPDATE `PREFIX_country` SET `call_prefix` = 239 WHERE `iso_code` = 'ST';
UPDATE `PREFIX_country` SET `call_prefix` = 966 WHERE `iso_code` = 'SA';
UPDATE `PREFIX_country` SET `call_prefix` = 221 WHERE `iso_code` = 'SN';
UPDATE `PREFIX_country` SET `call_prefix` = 381 WHERE `iso_code` = 'RS';
UPDATE `PREFIX_country` SET `call_prefix` = 248 WHERE `iso_code` = 'SC';
UPDATE `PREFIX_country` SET `call_prefix` = 232 WHERE `iso_code` = 'SL';
UPDATE `PREFIX_country` SET `call_prefix` = 386 WHERE `iso_code` = 'SI';
UPDATE `PREFIX_country` SET `call_prefix` = 677 WHERE `iso_code` = 'SB';
UPDATE `PREFIX_country` SET `call_prefix` = 252 WHERE `iso_code` = 'SO';
UPDATE `PREFIX_country` SET `call_prefix` = 94 WHERE `iso_code` = 'LK';
UPDATE `PREFIX_country` SET `call_prefix` = 249 WHERE `iso_code` = 'SD';
UPDATE `PREFIX_country` SET `call_prefix` = 597 WHERE `iso_code` = 'SR';
UPDATE `PREFIX_country` SET `call_prefix` = 268 WHERE `iso_code` = 'SZ';
UPDATE `PREFIX_country` SET `call_prefix` = 963 WHERE `iso_code` = 'SY';
UPDATE `PREFIX_country` SET `call_prefix` = 886 WHERE `iso_code` = 'TW';
UPDATE `PREFIX_country` SET `call_prefix` = 992 WHERE `iso_code` = 'TJ';
UPDATE `PREFIX_country` SET `call_prefix` = 255 WHERE `iso_code` = 'TZ';
UPDATE `PREFIX_country` SET `call_prefix` = 66 WHERE `iso_code` = 'TH';
UPDATE `PREFIX_country` SET `call_prefix` = 690 WHERE `iso_code` = 'TK';
UPDATE `PREFIX_country` SET `call_prefix` = 676 WHERE `iso_code` = 'TO';
UPDATE `PREFIX_country` SET `call_prefix` = 216 WHERE `iso_code` = 'TN';
UPDATE `PREFIX_country` SET `call_prefix` = 90 WHERE `iso_code` = 'TR';
UPDATE `PREFIX_country` SET `call_prefix` = 993 WHERE `iso_code` = 'TM';
UPDATE `PREFIX_country` SET `call_prefix` = 688 WHERE `iso_code` = 'TV';
UPDATE `PREFIX_country` SET `call_prefix` = 256 WHERE `iso_code` = 'UG';
UPDATE `PREFIX_country` SET `call_prefix` = 380 WHERE `iso_code` = 'UA';
UPDATE `PREFIX_country` SET `call_prefix` = 971 WHERE `iso_code` = 'AE';
UPDATE `PREFIX_country` SET `call_prefix` = 598 WHERE `iso_code` = 'UY';
UPDATE `PREFIX_country` SET `call_prefix` = 998 WHERE `iso_code` = 'UZ';
UPDATE `PREFIX_country` SET `call_prefix` = 678 WHERE `iso_code` = 'VU';
UPDATE `PREFIX_country` SET `call_prefix` = 58 WHERE `iso_code` = 'VE';
UPDATE `PREFIX_country` SET `call_prefix` = 84 WHERE `iso_code` = 'VN';
UPDATE `PREFIX_country` SET `call_prefix` = 681 WHERE `iso_code` = 'WF';
UPDATE `PREFIX_country` SET `call_prefix` = 967 WHERE `iso_code` = 'YE';
UPDATE `PREFIX_country` SET `call_prefix` = 260 WHERE `iso_code` = 'ZM';
UPDATE `PREFIX_country` SET `call_prefix` = 263 WHERE `iso_code` = 'ZW';
UPDATE `PREFIX_country` SET `call_prefix` = 355 WHERE `iso_code` = 'AL';
UPDATE `PREFIX_country` SET `call_prefix` = 93 WHERE `iso_code` = 'AF';
UPDATE `PREFIX_country` SET `call_prefix` = 387 WHERE `iso_code` = 'BA';
UPDATE `PREFIX_country` SET `call_prefix` = 359 WHERE `iso_code` = 'BG';
UPDATE `PREFIX_country` SET `call_prefix` = 682 WHERE `iso_code` = 'CK';
UPDATE `PREFIX_country` SET `call_prefix` = 594 WHERE `iso_code` = 'GF';
UPDATE `PREFIX_country` SET `call_prefix` = 689 WHERE `iso_code` = 'PF';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_CONDITIONS_CMS_ID', IFNULL((SELECT `id_cms` FROM `PREFIX_cms` WHERE `id_cms` = 3), 0), NOW(), NOW());
UPDATE `PREFIX_configuration` SET `value` = IF((SELECT value FROM (SELECT `value` FROM `PREFIX_configuration` WHERE `name` = 'PS_CONDITIONS_CMS_ID')tmp), 1, 0) WHERE `name` = 'PS_CONDITIONS';

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

