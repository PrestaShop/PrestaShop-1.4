SET NAMES 'utf8';

DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStatsModules' LIMIT 1;
DELETE FROM `PREFIX_tab_lang` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);
DELETE FROM `PREFIX_access` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);

INSERT INTO `PREFIX_module` (`name`, `active`) VALUES ('statsforecast', 1);
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`) (SELECT id_module, 32 FROM `PREFIX_module` WHERE `class_name` = 'statsforecast');