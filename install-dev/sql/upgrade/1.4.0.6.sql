SET NAMES 'utf8';

ALTER TABLE `PREFIX_customer` DROP INDEX `customer_email`;
ALTER TABLE `PREFIX_customer` ADD INDEX  `customer_email` (`email`);
