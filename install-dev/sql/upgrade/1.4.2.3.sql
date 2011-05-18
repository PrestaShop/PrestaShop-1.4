SET NAMES 'utf8';

UPDATE `PREFIX_address_format` SET `format`=REPLACE(`format`, 'state', 'State:name');

SET @defaultOOS = (SELECT value FROM `PREFIX_configuration` WHERE name = 'PS_ORDER_OUT_OF_STOCK');
/* Set 0 for every non-attribute product */
UPDATE `PREFIX_product` p SET `cache_default_attribute` =  0 WHERE `id_product` NOT IN (SELECT `id_product` FROM `PREFIX_product_attribute`);
/* First default attribute in stock */
UPDATE `PREFIX_product` p SET `cache_default_attribute` = (SELECT `id_product_attribute` FROM `PREFIX_product_attribute` WHERE `id_product` = p.`id_product` AND default_on = 1 AND quantity > 0 LIMIT 1) WHERE `cache_default_attribute` IS NULL;
/* Then default attribute without stock if we don't care */
UPDATE `PREFIX_product` p SET `cache_default_attribute` = (SELECT `id_product_attribute` FROM `PREFIX_product_attribute` WHERE `id_product` = p.`id_product` AND default_on = 1 LIMIT 1) WHERE `cache_default_attribute` IS NULL AND `out_of_stock` = 1 OR `out_of_stock` = IF(@defaultOOS = 1, 2, 1);
/* Next, the default attribute can be any attribute with stock */
UPDATE `PREFIX_product` p SET `cache_default_attribute` = (SELECT `id_product_attribute` FROM `PREFIX_product_attribute` WHERE `id_product` = p.`id_product` AND quantity > 0 LIMIT 1) WHERE `cache_default_attribute` IS NULL;
/* If there is still no default attribute, then we go back to the default one */
UPDATE `PREFIX_product` p SET `cache_default_attribute` = (SELECT `id_product_attribute` FROM `PREFIX_product_attribute` WHERE `id_product` = p.`id_product` AND default_on = 1 LIMIT 1) WHERE `cache_default_attribute` IS NULL;
