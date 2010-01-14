SET NAMES 'utf8';

/* ##################################### */
/* 				STRUCTURE			 		 */
/* ##################################### */

ALTER TABLE `PREFIX_product`
CHANGE `reduction_from` `reduction_from` DATE NOT NULL DEFAULT '1970-01-01',
CHANGE `reduction_to` `reduction_to` DATE NOT NULL DEFAULT '1970-01-01';

ALTER TABLE `PREFIX_order_detail` CHANGE `tax_rate` `tax_rate` DECIMAL(10, 3) NOT NULL DEFAULT '0.000';
ALTER TABLE `PREFIX_group` ADD `price_display_method` TINYINT NOT NULL DEFAULT 0 AFTER `reduction`;

CREATE TABLE `PREFIX_carrier_group` (
  `id_carrier` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_carrier` (`id_carrier`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `PREFIX_country` ADD `need_identification_number` TINYINT( 1 ) NOT NULL;
ALTER TABLE `PREFIX_customer` ADD `dni` VARCHAR( 16 ) NULL AFTER `firstname`;

ALTER TABLE `PREFIX_image` ADD INDEX `product_position` (`id_product`, `position`);
ALTER TABLE `PREFIX_hook_module` ADD INDEX `id_module` (`id_module`);
ALTER TABLE `PREFIX_customer` ADD INDEX `id_customer_passwd` (`id_customer`, `passwd`);
ALTER TABLE `PREFIX_tag` ADD INDEX `id_lang` (`id_lang`);
ALTER TABLE `PREFIX_customer_group` ADD INDEX `id_customer` (`id_customer`);
ALTER TABLE `PREFIX_category_group` ADD INDEX `id_category` (`id_category`);
ALTER TABLE `PREFIX_image` ADD INDEX `id_product_cover` (`id_product`, `cover`);
ALTER TABLE `PREFIX_employee` ADD INDEX `id_employee_passwd` (`id_employee`, `passwd`);
ALTER TABLE `PREFIX_product_attribute` ADD INDEX `product_default` (`id_product`, `default_on`);
ALTER TABLE `PREFIX_product_download` ADD INDEX `product_active` (`id_product`, `active`);
ALTER TABLE `PREFIX_tab` ADD INDEX `class_name` (`class_name`);
ALTER TABLE `PREFIX_module_currency` ADD INDEX `id_module` (`id_module`);
ALTER TABLE `PREFIX_product_attribute_combination` ADD INDEX `id_product_attribute` (`id_product_attribute`);
ALTER TABLE `PREFIX_orders` ADD INDEX `invoice_number` (`invoice_number`);
ALTER TABLE `PREFIX_product_tag` ADD INDEX `id_tag` (`id_tag`);
ALTER TABLE `PREFIX_cms_lang` CHANGE `id_cms` `id_cms` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `PREFIX_tax` CHANGE `rate` `rate` DECIMAL(10, 3) NOT NULL;

ALTER TABLE `PREFIX_connections` CHANGE `ip_address` `ip_address` INT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `PREFIX_order_detail` ADD `discount_quantity_applied` TINYINT(1) NOT NULL DEFAULT 0 AFTER `ecotax`;
ALTER TABLE `PREFIX_orders` ADD `total_products_wt` DECIMAL(10, 2) NOT NULL AFTER `total_products`;

/* ##################################### */
/* 					CONTENTS					 */
/* ##################################### */

UPDATE `PREFIX_group` SET `price_display_method` = (SELECT `value` FROM `PREFIX_configuration` WHERE `name` = 'PS_PRICE_DISPLAY');

DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_PRICE_DISPLAY';
DELETE FROM `PREFIX_product_attachment` WHERE `id_product` NOT IN (SELECT `id_product` FROM `PREFIX_product`);
DELETE FROM `PREFIX_discount_quantity` WHERE `id_product` NOT IN (SELECT `id_product` FROM `PREFIX_product`);
DELETE FROM `PREFIX_pack` WHERE `id_product_pack` NOT IN (SELECT `id_product` FROM `PREFIX_product`) OR `id_product_item` NOT IN (SELECT `id_product` FROM `PREFIX_product`);
DELETE FROM `PREFIX_product_sale` WHERE `id_product` NOT IN (SELECT `id_product` FROM `PREFIX_product`);
DELETE FROM `PREFIX_scene_products` WHERE `id_product` NOT IN (SELECT `id_product` FROM `PREFIX_product`);
DELETE FROM `PREFIX_search_index` WHERE `id_product` NOT IN (SELECT `id_product` FROM `PREFIX_product`);
DELETE FROM `PREFIX_search_word` WHERE `id_word` NOT IN (SELECT `id_word` FROM `PREFIX_search_index`);
DELETE FROM `PREFIX_tag` WHERE `id_lang` NOT IN (SELECT `id_lang` FROM `PREFIX_lang`);
DELETE FROM `PREFIX_search_word` WHERE `id_lang` NOT IN (SELECT `id_lang` FROM `PREFIX_lang`);

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PRESTASTORE_LIVE', 1, NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_SHOW_ALL_MODULES', 0, NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_BACKUP_ALL', 0, NOW(), NOW());
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
('createAccountTop', 'Block above the form for create an account', NULL , '1'),
('backOfficeHeader', 'Administration panel header', NULL , '0'),
('backOfficeTop', 'Administration panel top hover the tabs', NULL , '1'),
('backOfficeFooter', 'Administration panel footer', NULL , '1');

INSERT INTO `PREFIX_carrier_group` (id_carrier, id_group) (SELECT id_carrier, id_group FROM ps_carrier c, ps_group g WHERE c.active = 1);

INSERT INTO `PREFIX_configuration` (`id_configuration`, `name`, `value`, `date_add`, `date_upd`) VALUES (NULL, 'PS_1_3_UPDATE_DATE', NOW(), '', '');
