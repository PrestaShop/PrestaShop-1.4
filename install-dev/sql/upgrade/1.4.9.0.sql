SET NAMES 'utf8';

ALTER TABLE `PREFIX_image` ADD UNIQUE KEY `idx_product_image` (`id_image` , `id_product` , `cover`);
