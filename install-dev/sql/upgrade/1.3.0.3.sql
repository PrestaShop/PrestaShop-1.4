SET NAMES 'utf8';

ALTER TABLE `PREFIX_tab` CHANGE  `id_parent`  `id_parent` INT(11) NOT NULL;
INSERT INTO `PREFIX_tab` (`id_tab`, `class_name`, `id_parent`, `position`) 
VALUES (43, 'AdminSearch', -1, 0) 
ON DUPLICATE KEY 
UPDATE `id_parent` = -1;

