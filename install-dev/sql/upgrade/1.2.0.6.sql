SET NAMES 'utf8';

/* ##################################### */
/* 				STRUCTURE			 		 */
/* ##################################### */

ALTER TABLE `PREFIX_configuration` DROP INDEX `configuration_name`;
ALTER TABLE `PREFIX_order_detail` ADD `product_quantity_in_stock` INT(10) NOT NULL DEFAULT 0 AFTER `product_quantity`;
ALTER TABLE `PREFIX_order_detail` ADD `product_quantity_reinjected` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `product_quantity_return`;

/* ##################################### */
/* 					CONTENTS					 */
/* ##################################### */
