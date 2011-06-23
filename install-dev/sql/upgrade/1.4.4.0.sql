SET NAMES 'utf8';

ALTER TABLE `PREFIX_image` MODIFY COLUMN `position` SMALLINT(2) UNSIGNED NOT NULL DEFAULT 0;

INSERT IGNORE INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_OS_CHEQUE', '1', NOW(), NOW()),
('PS_OS_PAYMENT', '2', NOW(), NOW()),
('PS_OS_PREPARATION', '3', NOW(), NOW()),
('PS_OS_SHIPPING', '4', NOW(), NOW()),
('PS_OS_DELIVERED', '5', NOW(), NOW()),
('PS_OS_CANCELED', '6', NOW(), NOW()),
('PS_OS_REFUND', '7', NOW(), NOW()),
('PS_OS_ERROR', '8', NOW(), NOW()),
('PS_OS_OUTOFSTOCK', '9', NOW(), NOW()),
('PS_OS_BANKWIRE', '10', NOW(), NOW()),
('PS_OS_PAYPAL', '11', NOW(), NOW()),
('PS_OS_WS_PAYEMENT', '12', NOW(), NOW()),