SET NAMES 'utf8';

ALTER TABLE `PREFIX_image` ADD UNIQUE KEY `idx_product_image` (`id_image` , `id_product` , `cover`);
ALTER TABLE `PREFIX_category_product` DROP INDEX `category_product_index`, ADD PRIMARY KEY (`id_category`, `id_product`);
ALTER TABLE `PREFIX_cms_category_lang` DROP INDEX `category_lang_index`, ADD PRIMARY KEY (`id_cms_category`, `id_lang`);
ALTER TABLE `PREFIX_order_tax` ADD `id_order_tax` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

UPDATE `PREFIX_country` SET `zip_code_format` = 'NNNNN' WHERE `iso_code` = 'MC' LIMIT 1;

UPDATE `PREFIX_county_zip_code` SET `to_zip_code` = `from_zip_code` WHERE `to_zip_code` = 0;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`)(SELECT 'PS_TAX_DISPLAY_ALL', value, NOW(), NOW() FROM `PREFIX_configuration` WHERE name = 'PS_TAX_DISPLAY' LIMIT 1);