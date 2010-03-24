SET NAMES 'utf8';

DELETE FROM `PREFIX_tax_state` WHERE `id_tax` NOT IN (SELECT `id_tax` FROM `PREFIX_tax`);

ALTER TABLE `PREFIX_product` CHANGE `reduction_from` `reduction_from` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
CHANGE `reduction_to` `reduction_to` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';

UPDATE `PREFIX_product` 
SET `reduction_to` = DATE_ADD(reduction_to, INTERVAL 1 DAY) 
WHERE `reduction_from` != `reduction_to`;

