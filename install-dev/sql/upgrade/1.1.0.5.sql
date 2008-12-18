SET NAMES 'utf8';

/* ##################################### */
/* 					STRUCTURE				  */
/* ##################################### */

ALTER TABLE PREFIX_product
	CHANGE customizable customizable TINYINT(2) NOT NULL DEFAULT 0;
ALTER TABLE PREFIX_connections
	CHANGE ip_address ip_address VARCHAR(16) NULL;
ALTER TABLE PREFIX_customer
	ADD newsletter_date_add DATETIME NULL;
ALTER TABLE PREFIX_cart_product
	ADD	date_add DATETIME NOT NULL;

/* ################################# */
/* 					CONTENTS				*/
/* ################################# */

INSERT INTO PREFIX_configuration (name, value, date_add, date_upd) VALUES ('PS_INVOICE_NUMBER', (SELECT COUNT(id_order) + 1 FROM PREFIX_orders LIMIT 1), NOW(), NOW());

/* PHP: add_required_customization_field_flag(); */;
