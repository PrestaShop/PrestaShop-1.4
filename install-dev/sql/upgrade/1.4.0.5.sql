SET NAMES 'utf8';

@alias = (SELECT id_tab FROM PREFIX_tab WHERE class_name = "AdminAlias" LIMIT 1);
UPDATE PREFIX_tab SET id_parent = 8 WHERE id_tab = @alias LIMIT 1;
@stores = (SELECT id_tab FROM PREFIX_tab WHERE class_name = "AdminStores" LIMIT 1);
UPDATE PREFIX_tab SET id_parent = 9 WHERE id_tab = @stores LIMIT 1;
@pdf = (SELECT id_tab FROM PREFIX_tab WHERE class_name = "AdminPDF" LIMIT 1);
UPDATE PREFIX_tab SET id_parent = 3 WHERE id_tab = @pdf LIMIT 1;

ALTER TABLE `PREFIX_image_type` ADD `stores` tinyint(1) NOT NULL default '1';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_DIMENSION_UNIT', 'cm', NOW(), NOW());

ALTER TABLE `PREFIX_product` ADD `width` FLOAT NOT NULL AFTER `location` ,
ADD `height` FLOAT NOT NULL AFTER `width` ,
ADD `depth` FLOAT NOT NULL AFTER `height`;
