SET NAMES 'utf8';

CREATE TABLE IF NOT EXISTS `PREFIX_order_tax` (
  `id_order` int(11) NOT NULL,
  `tax_name` varchar(40) NOT NULL,
  `tax_rate` decimal(6,3) NOT NULL,
  `amount` decimal(20,6) NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

