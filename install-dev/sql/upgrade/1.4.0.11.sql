INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`)
VALUES
(
'PS_INVOICE_START_NUMBER',
(SELECT GREATEST(`value`, (SELECT CAST(MAX(`invoice_number`) AS CHAR) AS `invoice_number` FROM `PREFIX_orders`))  FROM `PREFIX_configuration` tmp WHERE `name` = 'PS_INVOICE_NUMBER' ),
NOW(),
NOW()
);

DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_INVOICE_NUMBER';

