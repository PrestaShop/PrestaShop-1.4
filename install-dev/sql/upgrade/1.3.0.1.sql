SET NAMES 'utf8';

ALTER TABLE `PREFIX_product`
CHANGE `reduction_from` `reduction_from` DATE NOT NULL DEFAULT '1970-01-01',
CHANGE `reduction_to` `reduction_to` DATE NOT NULL DEFAULT '1970-01-01';

ALTER TABLE `PREFIX_order_detail` CHANGE `tax_rate` `tax_rate` DECIMAL(10, 3) NOT NULL DEFAULT '0.000';
ALTER TABLE `PREFIX_group` ADD `price_display_method` TINYINT NOT NULL DEFAULT 0 AFTER `reduction`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PRESTASTORE_LIVE', 1, NOW(), NOW());
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_SHOW_ALL_MODULES', 0, NOW(), NOW());
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES ('createAccountTop', 'Block above the form for create an account', NULL , '1');

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


UPDATE `PREFIX_group` SET `price_display_method` = (SELECT `value` FROM `PREFIX_configuration` WHERE `name` = 'PS_PRICE_DISPLAY');
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_PRICE_DISPLAY';
