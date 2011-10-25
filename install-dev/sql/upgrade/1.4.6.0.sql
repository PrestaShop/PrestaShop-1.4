SET NAMES 'utf8';

CREATE TABLE IF NOT EXISTS `PREFIX_order_tax` (
  `id_order` int(11) NOT NULL,
  `tax_name` varchar(40) NOT NULL,
  `tax_rate` decimal(6,3) NOT NULL,
  `amount` decimal(20,6) NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_compare` (
  `id_compare` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_compare`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

ALTER TABLE  `PREFIX_compare_product` DROP  `id_guest` , DROP  `id_customer` ;

ALTER TABLE `PREFIX_compare_product`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `id_compare`,
     `id_product`);
