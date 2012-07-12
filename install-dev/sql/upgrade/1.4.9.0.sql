SET NAMES 'utf8';

ALTER TABLE `PREFIX_image` ADD UNIQUE KEY `idx_product_image` (`id_image` , `id_product` , `cover`);
ALTER TABLE `PREFIX_category_product` DROP INDEX `category_product_index`, ADD PRIMARY KEY (`id_category`, `id_product`);
ALTER TABLE `PREFIX_cms_category_lang` DROP INDEX `category_lang_index`, ADD PRIMARY KEY (`id_cms_category`, `id_lang`);
ALTER TABLE `PREFIX_order_tax` ADD `id_order_tax` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;