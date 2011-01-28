INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`)
VALUES
(
'PS_INVOICE_START_NUMBER',
(SELECT GREATEST(`value`, (SELECT CAST(MAX(`invoice_number`) AS CHAR) AS `invoice_number` FROM `PREFIX_orders`))  FROM `PREFIX_configuration` tmp WHERE `name` = 'PS_INVOICE_NUMBER' ),
NOW(),
NOW()
);

DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_INVOICE_NUMBER';

INSERT INTO `PREFIX_tab` (`id_parent`, `class_name`, `module`, `position`) VALUES (9, 'AdminLogs', '', 13);
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (1, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminLogs'
), 'Log');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (2, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminLogs'
), 'Log');
INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (3, (
	SELECT `id_tab`
	FROM `PREFIX_tab`
	WHERE `class_name` = 'AdminLogs'
), 'Log');

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (
	SELECT `id_profile`, (
		SELECT `id_tab`
		FROM `PREFIX_tab`
		WHERE `class_name` = 'AdminLogs'
	), 1, 1, 1, 1 FROM `PREFIX_profile`
);

CREATE TABLE `PREFIX_log` (
	`id_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`severity` tinyint(1) NOT NULL,
	`error_code` int(11) DEFAULT NULL,
	`message` text NOT NULL,
	`object_type` varchar(32) DEFAULT NULL,
	`object_id` int(10) unsigned DEFAULT NULL,
	`date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
	PRIMARY KEY (`id_log`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_LOGS_BY_EMAIL', '5', NOW(), NOW());
