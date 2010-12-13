SET NAMES 'utf8';

SET @alias = (SELECT IFNULL((SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = "AdminAlias" LIMIT 1), '0'));
UPDATE `PREFIX_tab` SET `id_parent` = 8 WHERE `id_tab` = @alias LIMIT 1;
SET @stores = (SELECT IFNULL((SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = "AdminStores" LIMIT 1), '0'));
UPDATE `PREFIX_tab` SET `id_parent` = 9 WHERE `id_tab` = @stores LIMIT 1;
SET @pdf = (SELECT IFNULL((SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = "AdminPDF" LIMIT 1), '0'));
UPDATE `PREFIX_tab` SET `id_parent` = 3 WHERE `id_tab` = @pdf LIMIT 1;
SET @tabs = (SELECT IFNULL((SELECT `id_tab` FROM `PREFIX_tab` WHERE `class_name` = "AdminTabs" LIMIT 1), '0'));
UPDATE `PREFIX_tab` SET `id_parent` = 29 WHERE `id_tab` = @tabs LIMIT 1;

ALTER TABLE `PREFIX_image_type` ADD `stores` tinyint(1) NOT NULL default '1';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_DIMENSION_UNIT', 'cm', NOW(), NOW());

ALTER TABLE `PREFIX_product`
ADD `width` FLOAT NOT NULL AFTER `location`,
ADD `height` FLOAT NOT NULL AFTER `width`,
ADD `depth` FLOAT NOT NULL AFTER `height`;

SET @id_module = (SELECT IFNULL((SELECT `id_module` FROM `PREFIX_module` WHERE `name` = "statshome" LIMIT 1), '0'));
DELETE FROM `PREFIX_module` WHERE `id_module` = @id_module;
DELETE FROM `PREFIX_hook_module` WHERE `id_module` = @id_module;

ALTER TABLE `PREFIX_category` ADD `nleft` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `level_depth`;
ALTER TABLE `PREFIX_category` ADD `nright` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `nleft`;
/* PHP:generate_ntree(); */;
