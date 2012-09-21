SET NAMES 'utf8';

/* PHP:update_module_mailalerts(); */;

/* Backward compatibility */
INSERT INTO `PREFIX_module` (`name`, `active`) VALUES ('backwardcompatibility', 1);

INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook` , `position`)
(SELECT id_module, 9, (SELECT max_position from (SELECT MAX(position)+1 as max_position FROM `PREFIX_hook_module` WHERE `id_hook` = 9) tmp) FROM `PREFIX_module` WHERE `name` = 'backwardcompatibility');
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook` , `position`)
(SELECT id_module, 50, (SELECT max_position from (SELECT MAX(position)+1 as max_position FROM `PREFIX_hook_module` WHERE `id_hook` = 50) tmp) FROM `PREFIX_module` WHERE `name` = 'backwardcompatibility');
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook` , `position`)
(SELECT id_module, 54, (SELECT max_position from (SELECT MAX(position)+1 as max_position FROM `PREFIX_hook_module` WHERE `id_hook` = 50) tmp) FROM `PREFIX_module` WHERE `name` = 'backwardcompatibility');