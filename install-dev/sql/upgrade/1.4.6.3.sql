SET NAMES 'utf8';

ALTER TABLE `PREFIX_category_product` DROP INDEX `category_product_index`;
ALTER TABLE `PREFIX_category_product` ADD UNIQUE `category_product_index` (`id_category`,`id_product`);