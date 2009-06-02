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
