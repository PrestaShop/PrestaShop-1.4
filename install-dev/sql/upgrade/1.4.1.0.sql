ALTER TABLE `PREFIX_stock_mvt_reason` ADD `sign` TINYINT(1) NOT NULL AFTER `id_stock_mvt_reason`;
UPDATE `PREFIX_stock_mvt_reason` SET `sign`=-1;
UPDATE `PREFIX_stock_mvt_reason` SET `sign`=1 WHERE `id_stock_mvt_reason`=3;
UPDATE `PREFIX_stock_mvt_reason` SET `id_stock_mvt_reason`=`id_stock_mvt_reason`+2 ORDER BY `id_stock_mvt_reason` DESC;
UPDATE `PREFIX_stock_mvt` SET `id_stock_mvt_reason`=`id_stock_mvt_reason`+2;
UPDATE `PREFIX_stock_mvt_reason_lang` SET `id_stock_mvt_reason`=`id_stock_mvt_reason`+2 ORDER BY `id_stock_mvt_reason` DESC;
INSERT INTO `PREFIX_stock_mvt_reason` (`id_stock_mvt_reason` ,`sign` ,`date_add` ,`date_upd`) VALUES ('1', '1', NOW(), NOW()), ('2', '-1', NOW(), NOW());

INSERT INTO `PREFIX_stock_mvt_reason_lang` (`id_stock_mvt_reason` ,`id_lang` ,`name`) VALUES 
('1', '1', 'Increase'), 
('1', '2', 'Augmenter'), 
('1', '3', 'Aumentar'), 
('1', '4', 'Erhöhen'), 
('1', '5', 'Aumento'), 
('2', '1', 'Decrease'), 
('2', '2', 'Diminuer'), 
('2', '3', 'Disminuir'), 
('2', '4', 'Reduzieren'), 
('2', '5', 'Diminuzione');

INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`, `live_edit`) VALUES ('afterSaveAdminMeta', 'After save configuration in AdminMeta', 'After save configuration in AdminMeta', 0, 0);

INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES 
(
(SELECT `id_module` FROM `PREFIX_module` WHERE `name` = 'blockcategories'), 
(SELECT `id_hook` FROM `PREFIX_hook` WHERE `name` = 'afterSaveAdminMeta'), 1
);


ALTER TABLE `PREFIX_webservice_account` ADD `is_module` TINYINT( 2 ) NOT NULL DEFAULT '0' AFTER `class_name` ,
ADD `module_name` VARCHAR( 50 ) NULL DEFAULT NULL AFTER `is_module`;
