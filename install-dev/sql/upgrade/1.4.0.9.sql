CREATE TABLE `PREFIX_specific_price_priority` (
`id_specific_price_priority` INT NOT NULL AUTO_INCREMENT ,
`id_product` INT NOT NULL ,
`priority` VARCHAR( 80 ) NOT NULL ,
PRIMARY KEY ( `id_specific_price_priority` , `id_product` )
)  ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;

