SET NAMES 'utf8';

UPDATE `PREFIX_address_format` SET `format`=REPLACE(`format`, 'state', 'State:name');
