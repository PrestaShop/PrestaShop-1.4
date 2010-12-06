SET NAMES 'utf8';
ALTER TABLE `PREFIX_product` CHANGE `ecotax` `ecotax` DECIMAL(21, 6) NOT NULL DEFAULT '0.00';
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES ((SELECT `id_module` FROM `PREFIX_module` WHERE `name` = 'crossselling'), 9, (SELECT max_position FROM (SELECT MAX(position)+1 as max_position FROM `PREFIX_hook_module` WHERE `id_hook` = 9) tmp));

UPDATE `PREFIX_cms` SET `id_cms_category` = 1;

INSERT INTO `PREFIX_tab` (`class_name`, `id_parent`, `position`) VALUES ('AdminStores', 0, 11);

INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES 
(1, (SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = 'AdminStores'), 'Stores'), 
(2, (SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = 'AdminStores'), 'Magasins'), 
(3, (SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = 'AdminStores'), 'Tiendas');

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (
	SELECT `id_profile`, (
		SELECT `id_tab`
		FROM `PREFIX_tab`
		WHERE `class_name` = 'AdminStores'
	), 1, 1, 1, 1 FROM `PREFIX_profile`
);

INSERT IGNORE INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) SELECT 'PS_LOCALE_LANGUAGE', l.`iso_code`, NOW(), NOW() FROM `PREFIX_configuration` c INNER JOIN `PREFIX_lang` l ON (l.`id_lang` = c.`value`) WHERE c.`name` = 'PS_LANG_DEFAULT';
INSERT IGNORE INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) SELECT 'PS_LOCALE_COUNTRY', co.`iso_code`, NOW(), NOW() FROM `PREFIX_configuration` c INNER JOIN `PREFIX_country` co ON (co.`id_country` = c.`value`) WHERE c.`name` = 'PS_COUNTRY_DEFAULT';
/* PHP:reorderpositions(); */;

ALTER TABLE `PREFIX_webservice_permission` CHANGE `method` `method` ENUM( 'GET', 'POST', 'PUT', 'DELETE', 'HEAD' ) NOT NULL;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_ATTACHMENT_MAXIMUM_SIZE', '2', NOW(), NOW());

ALTER TABLE `PREFIX_product_attribute` CHANGE `price` `price` decimal(20,6) NOT NULL default '0.000000';
UPDATE `PREFIX_product_attribute` pa SET pa.`price` = pa.`price` / (1 + (SELECT t.`rate` FROM `PREFIX_tax` t INNER JOIN `PREFIX_product` p ON (p.`id_tax` = t.`id_tax`) WHERE p.`id_product` = pa.`id_product`) / 100);
