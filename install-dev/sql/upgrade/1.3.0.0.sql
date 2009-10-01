SET NAMES 'utf8';

ALTER TABLE `PREFIX_product`
CHANGE `reduction_from` `reduction_from` DATE NOT NULL DEFAULT '1970-01-01',
CHANGE `reduction_to` `reduction_to` DATE NOT NULL DEFAULT '1970-01-01';
