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
('PS_OS_WS_PAYMENT', '12', NOW(), NOW()),
('PS_IMAGE_QUALITY', 'jpg', NOW(), NOW()),
('PS_PNG_QUALITY', '7', NOW(), NOW()),
('PS_JPEG_QUALITY', '90', NOW(), NOW()),
('PS_COOKIE_LIFETIME_FO', '480', NOW(), NOW()),
('PS_COOKIE_LIFETIME_BO', '480', NOW(), NOW());

ALTER TABLE `PREFIX_lang` ADD `is_rtl` TINYINT(1) NOT NULL DEFAULT '0';

UPDATE `PREFIX_country_lang`
SET `name` = 'United State'
WHERE `name` = 'USA'
AND `id_lang` = (
	SELECT `id_lang`
	FROM `PREFIX_lang`
	WHERE `iso_code` = 'en'
	LIMIT 1
);

UPDATE `PREFIX_hook`
SET `live_edit` = 1
WHERE `name` = 'leftColumn'
OR `name` = 'home'
OR `name` = 'rightColumn'
OR `name` = 'productfooter'
OR `name` = 'payment';

ALTER TABLE `PREFIX_stock_mvt_reason` MODIFY `sign` TINYINT(1) NOT NULL DEFAULT '1' AFTER `id_stock_mvt_reason`;

UPDATE `PREFIX_tab_lang`
SET `name` = 'Geolocation'
WHERE `name` = 'Geolocalization';

UPDATE `PREFIX_tab_lang`
SET `name` = 'Counties'
WHERE `name` = 'County';

ALTER TABLE `PREFIX_tax_rule` MODIFY `id_county` INT NOT NULL AFTER `id_country`;
