CREATE TABLE IF NOT EXISTS `PREFIX_paypal_order` (
  `id_order` int(10) unsigned NOT NULL auto_increment,
  `id_transaction` varchar(255) NOT NULL,
  PRIMARY KEY (`id_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
