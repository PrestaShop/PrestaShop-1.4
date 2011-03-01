SET NAMES 'utf8';

ALTER TABLE `PREFIX_tax_rule` DROP PRIMARY KEY ;
ALTER TABLE `PREFIX_tax_rule` ADD `id_tax_rule` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `PREFIX_tax_rule` ADD INDEX ( `id_tax` ) ;
ALTER TABLE `PREFIX_tax_rule` ADD INDEX ( `id_tax_rules_group` ) ;

ALTER TABLE `PREFIX_address` MODIFY `dni` VARCHAR(16) NULL AFTER `vat_number`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES 
('BLOCKSTORE_IMG', 'store.jpg', NOW(), NOW()),
('PS_STORES_CENTER_LAT', '25.948969', NOW(), NOW()),
('PS_STORES_CENTER_LONG', '-80.226439', NOW(), NOW());

/* PHP:add_new_tab(AdminInformation, Informations, 9); */;
/* PHP:add_new_tab(AdminPerformance, Performance, 8); */;
/* PHP:add_new_tab(AdminCustomerThreads, Customer Service, 29); */;
/* PHP:add_new_tab(AdminWebservice, Web Service, 8); */;
/* PHP:add_new_tab(AdminAddonsCatalog, Modules & Themes catalog, 7); */;
/* PHP:add_new_tab(AdminAddonsMyAccount, My Account, 7); */;
/* PHP:add_new_tab(AdminThemes, Themes, 7); */;
/* PHP:add_new_tab(AdminGeolocalization, Geolocalization, 8); */;
/* PHP:add_new_tab(AdminTaxRulesGroup, Tax Rules, 4); */;
/* PHP:add_new_tab(AdminLogs, Log, 9); */;