
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_HOMEPAGE_PHP_SELF', 'index.php', NOW(), NOW());


INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`)
VALUES ('PS_USE_ECOTAX',
		  (SELECT IF((SELECT ecotax FROM `PREFIX_product` WHERE  `ecotax` != 0),'1','0')),
        NOW(),
        NOW());

