CREATE TABLE IF NOT EXISTS `PREFIX_dejala_cart` (
  `id_cart` int(10) unsigned NOT NULL,
  `id_dejala_product` int(10) unsigned NOT NULL,
  `shipping_date` int(11) NULL DEFAULT NULL, 
  `id_delivery` int(11) NULL DEFAULT NULL,
  `mode` varchar(5) NULL DEFAULT 'TEST',
  PRIMARY KEY (`id_cart`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



