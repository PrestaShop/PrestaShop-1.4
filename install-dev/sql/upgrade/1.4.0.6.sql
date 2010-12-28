SET NAMES 'utf8';

ALTER TABLE `PREFIX_customer` DROP INDEX `customer_email`;
ALTER TABLE `PREFIX_customer` ADD INDEX  `customer_email` (`email`);

ALTER TABLE `PREFIX_lang` ADD `language_code` char(5) NULL AFTER `iso_code`;
UPDATE `PREFIX_lang` SET language_code = iso_code;
ALTER TABLE `PREFIX_lang` MODIFY `language_code` char(5) NOT NULL;
