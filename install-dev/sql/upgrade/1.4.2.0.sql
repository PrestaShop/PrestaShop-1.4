SET NAMES 'utf8';

ALTER TABLE `PREFIX_tab_lang` MODIFY `id_lang` int(10) unsigned NOT NULL AFTER `id_tab`;
ALTER TABLE `PREFIX_carrier` ADD `is_free` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `is_module`;

UPDATE `PREFIX_address_format` SET `format`=REPLACE(REPLACE(`format`, 'state_iso', 'State:name'), 'country', 'Country:name');

ALTER TABLE `PREFIX_orders` ADD INDEX `date_add`(`date_add`);

PHP:update_module_followup();

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_STOCK_MVT_REASON_DEFAULT', 3, NOW(), NOW());

/* PHP:alter_blocklink(); */;
/* PHP:update_module_loyalty(); */;
/* PHP:remove_module_from_hook(blockcategories, afterCreateHtaccess); */;
