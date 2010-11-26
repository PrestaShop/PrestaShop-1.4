SET NAMES 'utf8';
ALTER TABLE `PREFIX_product` CHANGE `ecotax` `ecotax` DECIMAL(21, 6) NOT NULL DEFAULT '0.00';
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES ((SELECT `id_module` FROM `PREFIX_module` WHERE `name` = 'crossselling'), 9, (SELECT max_position FROM (SELECT MAX(position)+1 as max_position FROM `PREFIX_hook_module` WHERE `id_hook` = 9) tmp));
