SET NAMES 'utf8';

ALTER TABLE `PREFIX_product`
CHANGE `reduction_from` `reduction_from` DATE NOT NULL DEFAULT '1970-01-01',
CHANGE `reduction_to` `reduction_to` DATE NOT NULL DEFAULT '1970-01-01';

ALTER TABLE `PREFIX_order_detail` CHANGE `tax_rate` `tax_rate` DECIMAL(10, 3) NOT NULL DEFAULT '0.000';
ALTER TABLE `PREFIX_group` ADD `price_display_method` TINYINT NOT NULL DEFAULT 0 AFTER `reduction`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PRESTASTORE_LIVE', 1, NOW(), NOW());
