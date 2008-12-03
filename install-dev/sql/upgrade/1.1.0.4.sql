SET NAMES 'utf8';

/* ##################################### */
/* 					STRUCTURE				*/
/* ##################################### */

ALTER TABLE PREFIX_order_detail
	DROP `deleted`,
	ADD product_quantity_cancelled INT(10) UNSIGNED NOT NULL AFTER product_quantity_return;

ALTER TABLE PREFIX_customization ADD quantity INT(10) NOT NULL;
