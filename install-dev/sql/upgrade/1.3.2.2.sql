SET NAMES 'utf8';

ALTER TABLE `PREFIX_order_detail` ADD `reduction_percent` DECIMAL(10, 2) NOT NULL AFTER `product_price`;
ALTER TABLE `PREFIX_order_detail` ADD `reduction_amount` DECIMAL(20, 6) NOT NULL AFTER `reduction_percent`;

ALTER TABLE `PREFIX_country` CHANGE `need_identification_number` `need_identification_number` TINYINT(1) NOT NULL DEFAULT '0';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_1_3_2_UPDATE_DATE', NOW(), NOW(), NOW());
