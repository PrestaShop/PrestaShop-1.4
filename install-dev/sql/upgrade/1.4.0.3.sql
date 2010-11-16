SET NAMES 'utf8';

DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStatsModules' LIMIT 1;
DELETE FROM `PREFIX_tab_lang` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);
DELETE FROM `PREFIX_access` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);

INSERT INTO `PREFIX_module` (`name`, `active`) VALUES ('statsforecast', 1);
INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`) (SELECT id_module, 32 FROM `PREFIX_module` WHERE `class_name` = 'statsforecast');

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
