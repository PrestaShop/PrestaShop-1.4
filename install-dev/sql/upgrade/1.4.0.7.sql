SET NAMES 'utf8';

ALTER TABLE `PREFIX_product_attribute` ADD `minimal_quantity` int(10) unsigned NOT NULL DEFAULT '1' AFTER `default_on`;

ALTER TABLE `PREFIX_orders` ADD `reference` VARCHAR(14) NOT NULL AFTER `id_address_invoice`;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES('PS_DISPLAY_SUPPLIERS', '1', NOW(), NOW());

UPDATE `PREFIX_tab`
SET `position` = `position` + 1
WHERE `id_parent` = (SELECT * FROM (SELECT `id_parent` FROM `PREFIX_tab` WHERE `class_name` = 'AdminTaxes') tmp)
AND `position` > (SELECT * FROM (SELECT `position` FROM `PREFIX_tab` WHERE `class_name` = 'AdminTaxes') tmp2);

UPDATE `PREFIX_tab`
SET `position` = (SELECT * FROM (SELECT `position` FROM `PREFIX_tab` WHERE `class_name` = 'AdminTaxes') tmp) + 1
WHERE `class_name` = 'AdminTaxRulesGroup';

UPDATE `PREFIX_hook` SET `title` = 'Category creation', description = '' WHERE `name` = 'categoryAddition' LIMIT 1;
UPDATE `PREFIX_hook` SET `title` = 'Category modification', description = '' WHERE `name` = 'categoryUpdate' LIMIT 1;
UPDATE `PREFIX_hook` SET `title` = 'Category removal', description = '' WHERE `name` = 'categoryDeletion' LIMIT 1;

DELETE FROM `PREFIX_module` WHERE `name` = 'canonicalurl' LIMIT 1;
DELETE FROM `PREFIX_hook_module` WHERE `id_module` NOT IN (SELECT id_module FROM `PREFIX_module`);

/* PHP:update_products_ecotax_v133(); */;
/* PHP:gridextjs_deprecated(); */;
/* PHP:shop_url(); */;
