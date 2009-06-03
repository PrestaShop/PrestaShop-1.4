SET NAMES 'utf8';

/* ##################################### */
/* 				STRUCTURE			 		 */
/* ##################################### */

ALTER TABLE `PREFIX_customization`
	ADD `quantity_refunded` INT NOT NULL DEFAULT '0',
	ADD `quantity_returned` INT NOT NULL DEFAULT '0';

/* ##################################### */
/* 					CONTENTS					 */
/* ##################################### */

INSERT INTO PREFIX_hook (`name`, `title`, `description`, `position`) VALUES
	('postUpdateOrderStatus', 'Post update of order status', NULL, 0);

INSERT INTO `PREFIX_search_engine` (`server`,`getvar`) VALUES ('bing.com','q');
