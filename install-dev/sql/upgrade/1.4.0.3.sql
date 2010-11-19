SET NAMES 'utf8';

DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStatsModules' LIMIT 1;
DELETE FROM `PREFIX_tab_lang` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);
DELETE FROM `PREFIX_access` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);

INSERT INTO `PREFIX_module` (`name`, `active`) VALUES ('statsforecast', 1);
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`) (SELECT id_module, 32 FROM `PREFIX_module` WHERE `name` = 'statsforecast');

ALTER TABLE `PREFIX_orders` ADD `conversion_rate` decimal(13,6) NOT NULL default 1 AFTER `payment`;
UPDATE `PREFIX_orders` o SET o.`conversion_rate` = (
	SELECT c.`conversion_rate`
	FROM `PREFIX_currency` c
	WHERE c.`id_currency` = o.`id_currency`
	LIMIT 1
);

ALTER TABLE `PREFIX_order_slip` ADD `conversion_rate` decimal(13,6) NOT NULL default 1 AFTER `id_order`;
UPDATE `PREFIX_order_slip` os SET os.`conversion_rate` = (
	SELECT o.`conversion_rate`
	FROM `PREFIX_orders` o
	WHERE os.`id_order` = o.`id_order`
	LIMIT 1
);

UPDATE `PREFIX_configuration` SET `value` = 'gridhtml' WHERE `name` = 'PS_STATS_GRID_RENDER' LIMIT 1;
UPDATE `PREFIX_module` SET `name` = 'gridhtml' WHERE `name` = 'gridextjs' LIMIT 1;

ALTER TABLE `PREFIX_attachments` MODIFY `mime` varchar(64) NOT NULL;
ALTER TABLE `PREFIX_attachments` ADD `file_name` varchar(128) NOT NULL default '' AFTER `file`;
UPDATE `PREFIX_attachment` a SET `file_name` = (
		SELECT `name` FROM `PREFIX_attachment_lang` al WHERE al.`id_attachment` = a.`id_attachment` AND al.`id_lang` = (
				SELECT `value` FROM `PREFIX_configuration` WHERE `name` = 'PS_LANG_DEFAULT')
		);

UPDATE `PREFIX_tab` SET `class_name` = 'AdminCMSContent' WHERE `class_name` = 'AdminCMS' LIMIT 1;