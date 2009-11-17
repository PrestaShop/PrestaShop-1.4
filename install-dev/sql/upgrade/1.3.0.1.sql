SET NAMES 'utf8';

ALTER TABLE `PREFIX_product`
CHANGE `reduction_from` `reduction_from` DATE NOT NULL DEFAULT '1970-01-01',
CHANGE `reduction_to` `reduction_to` DATE NOT NULL DEFAULT '1970-01-01';

ALTER TABLE `PREFIX_order_detail` CHANGE `tax_rate` `tax_rate` DECIMAL(10, 3) NOT NULL DEFAULT '0.000';
ALTER TABLE `PREFIX_group` ADD `price_display_method` TINYINT NOT NULL DEFAULT 0 AFTER `reduction`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PRESTASTORE_LIVE', 1, NOW(), NOW());

ALTER TABLE `PREFIX_image` ADD INDEX `product_position` (`id_product`, `position`);
ALTER TABLE `PREFIX_hook_module` ADD INDEX `id_module` (`id_module`);
ALTER TABLE `PREFIX_customer` ADD INDEX `id_customer_passwd` (`id_customer`, `passwd`);
ALTER TABLE `PREFIX_tag` ADD INDEX `id_lang` (`id_lang`);
ALTER TABLE `PREFIX_customer_group` ADD INDEX `id_customer` (`id_customer`);
ALTER TABLE `PREFIX_category_group` ADD INDEX `id_category` (`id_category`);
ALTER TABLE `PREFIX_image` ADD INDEX `id_product_cover` (`id_product`, `cover`);