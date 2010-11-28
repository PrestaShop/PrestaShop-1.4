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
/* PHP:reorderpositions(); */;

