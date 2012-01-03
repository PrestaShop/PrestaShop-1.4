SET NAMES 'utf8';

ALTER TABLE `PREFIX_category_product` DROP INDEX `category_product_index`;
ALTER TABLE `PREFIX_category_product` ADD UNIQUE `category_product_index` (`id_category`,`id_product`);


CREATE TABLE IF NOT EXISTS `PREFIX_order_tax` (
  `id_order` int(11) NOT NULL,
  `tax_name` varchar(40) NOT NULL,
  `tax_rate` decimal(6,3) NOT NULL,
  `amount` decimal(20,6) NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

ALTER TABLE `PREFIX_order_state` ADD COLUMN `deleted` tinyint(1) UNSIGNED NOT NULL default '0' AFTER `delivery`;

