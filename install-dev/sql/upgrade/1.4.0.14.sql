ALTER TABLE `PREFIX_tax_rule` DROP PRIMARY KEY ;
ALTER TABLE `PREFIX_tax_rule` ADD `id_tax_rule` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `PREFIX_tax_rule` ADD INDEX ( `id_tax` ) ;
ALTER TABLE `PREFIX_tax_rule` ADD INDEX ( `id_tax_rules_group` ) ;

ALTER TABLE `PREFIX_address` MODIFY `dni` VARCHAR(16) NULL AFTER `vat_number`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES 
('BLOCKSTORE_IMG', 'store.jpg', NOW(), NOW()),
('PS_STORES_CENTER_LAT', '25.948969', NOW(), NOW()),
('PS_STORES_CENTER_LONG', '-80.226439', NOW(), NOW());

