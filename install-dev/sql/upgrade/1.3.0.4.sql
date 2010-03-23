SET NAMES 'utf8';

DELETE FROM `PREFIX_tax_state` WHERE `id_tax` NOT IN (SELECT `id_tax` FROM `PREFIX_tax`);
