/* ########################################################################## */
/* ############################## LANG UPDATES ################################## */
/* ########################################################################## */

INSERT IGNORE INTO `PREFIX_tab_lang` (`id_tab`, `id_lang`, `name`)
    (SELECT `id_tab`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_tab_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_tab`=`PREFIX_tab`.`id_tab`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_tab`);

INSERT IGNORE INTO `PREFIX_country_lang` (`id_country`, `id_lang`, `name`)
    (SELECT `id_country`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_country_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_country`=`PREFIX_country`.`id_country`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_country`);

INSERT IGNORE INTO `PREFIX_quick_access_lang` (`id_quick_access`, `id_lang`, `name`)
    (SELECT `id_quick_access`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_quick_access_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_quick_access`=`PREFIX_quick_access`.`id_quick_access`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_quick_access`);

INSERT IGNORE INTO `PREFIX_contact_lang` (`id_contact`, `id_lang`, `name`, `description`)
    (SELECT `id_contact`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_contact_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_contact`=`PREFIX_contact`.`id_contact`),
		(SELECT tl.`description`
        FROM `PREFIX_contact_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_contact`=`PREFIX_contact`.`id_contact`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_contact`);

INSERT IGNORE INTO `PREFIX_discount_type_lang` (`id_discount_type`, `id_lang`, `name`)
    (SELECT `id_discount_type`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_discount_type_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_discount_type`=`PREFIX_discount_type`.`id_discount_type`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_discount_type`);

INSERT IGNORE INTO `PREFIX_order_return_state_lang` (`id_order_return_state`, `id_lang`, `name`)
    (SELECT `id_order_return_state`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_order_return_state_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_order_return_state`=`PREFIX_order_return_state`.`id_order_return_state`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_order_return_state`);

INSERT IGNORE INTO `PREFIX_order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`)
    (SELECT `id_order_state`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_order_state_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_order_state`=`PREFIX_order_state`.`id_order_state`),
	(SELECT tl.`template`
        FROM `PREFIX_order_state_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_order_state`=`PREFIX_order_state`.`id_order_state`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_order_state`);

INSERT IGNORE INTO `PREFIX_profile_lang` (`id_profile`, `id_lang`, `name`)
    (SELECT `id_profile`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_profile_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_profile`=`PREFIX_profile`.`id_profile`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_profile`);

INSERT IGNORE INTO `PREFIX_meta_lang` (`id_meta`, `id_lang`, `title`, `description`, `keywords`)
    (SELECT `id_meta`, id_lang,
	(SELECT tl.`title`
        FROM `PREFIX_meta_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_meta`=`PREFIX_meta`.`id_meta`),
	(SELECT tl.`description`
        FROM `PREFIX_meta_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_meta`=`PREFIX_meta`.`id_meta`),
	(SELECT tl.`keywords`
        FROM `PREFIX_meta_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_meta`=`PREFIX_meta`.`id_meta`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_meta`);

INSERT IGNORE INTO `PREFIX_category_lang` (`id_category`, `id_lang`, `description`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`)
    (SELECT `id_category`, id_lang,
	(SELECT tl.`description`
        FROM `PREFIX_category_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_category`=`PREFIX_category`.`id_category`),
	(SELECT tl.`link_rewrite`
        FROM `PREFIX_category_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_category`=`PREFIX_category`.`id_category`),
	(SELECT tl.`meta_description`
        FROM `PREFIX_category_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_category`=`PREFIX_category`.`id_category`),
	(SELECT tl.`meta_keywords`
        FROM `PREFIX_category_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_category`=`PREFIX_category`.`id_category`),
	(SELECT tl.`meta_title`
        FROM `PREFIX_category_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_category`=`PREFIX_category`.`id_category`),
	(SELECT tl.`name`
        FROM `PREFIX_category_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_category`=`PREFIX_category`.`id_category`)
	FROM `PREFIX_lang` CROSS JOIN `PREFIX_category`);
	
INSERT IGNORE INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `content`)
    (SELECT `id_cms`, id_lang,
	(SELECT tl.`link_rewrite`
        FROM `PREFIX_cms_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_cms`=`PREFIX_cms`.`id_cms`),
	(SELECT tl.`meta_description`
        FROM `PREFIX_cms_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_cms`=`PREFIX_cms`.`id_cms`),
	(SELECT tl.`meta_keywords`
        FROM `PREFIX_cms_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_cms`=`PREFIX_cms`.`id_cms`),
	(SELECT tl.`meta_title`
        FROM `PREFIX_cms_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_cms`=`PREFIX_cms`.`id_cms`),
	(SELECT tl.`content`
        FROM `PREFIX_cms_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_cms`=`PREFIX_cms`.`id_cms`)
	FROM `PREFIX_lang` CROSS JOIN `PREFIX_cms`);
	
	INSERT IGNORE INTO `PREFIX_carrier_lang` (`id_carrier`, `id_lang`, `delay`)
    (SELECT `id_carrier`, id_lang, (SELECT tl.`delay`
        FROM `PREFIX_carrier_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_carrier`=`PREFIX_carrier`.`id_carrier`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_carrier`);

INSERT IGNORE INTO `PREFIX_attribute_group_lang` (`id_attribute_group`, `id_lang`, `name`, `public_name`)
    (SELECT `id_attribute_group`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_attribute_group_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_attribute_group`=`PREFIX_attribute_group`.`id_attribute_group`),
		(SELECT tl.`public_name`
        FROM `PREFIX_attribute_group_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_attribute_group`=`PREFIX_attribute_group`.`id_attribute_group`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_attribute_group`);

INSERT IGNORE INTO `PREFIX_attribute_lang` (`id_attribute`, `id_lang`, `name`)
    (SELECT `id_attribute`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_attribute_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_attribute`=`PREFIX_attribute`.`id_attribute`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_attribute`);

INSERT IGNORE INTO `PREFIX_product_lang` (`id_product`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`)
    (SELECT `id_product`, id_lang,
	(SELECT tl.`description`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`description_short`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`link_rewrite`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`meta_description`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`meta_keywords`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`meta_title`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`name`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`available_now`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`),
	(SELECT tl.`available_later`
        FROM `PREFIX_product_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_product`=`PREFIX_product`.`id_product`)
	FROM `PREFIX_lang` CROSS JOIN `PREFIX_product`);
	
INSERT IGNORE INTO `PREFIX_feature_lang` (`id_feature`, `id_lang`, `name`)
    (SELECT `id_feature`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_feature_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_feature`=`PREFIX_feature`.`id_feature`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_feature`);

INSERT IGNORE INTO `PREFIX_feature_value_lang` (`id_feature_value`, `id_lang`, `value`)
    (SELECT `id_feature_value`, id_lang, (SELECT tl.`value`
        FROM `PREFIX_feature_value_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_feature_value`=`PREFIX_feature_value`.`id_feature_value`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_feature_value`);

INSERT IGNORE INTO `PREFIX_image_lang` (`id_image`, `id_lang`, `legend`)
    (SELECT `id_image`, id_lang, (SELECT tl.`legend`
        FROM `PREFIX_image_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_image`=`PREFIX_image`.`id_image`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_image`);

INSERT IGNORE INTO `PREFIX_manufacturer_lang` (`id_manufacturer`, `id_lang`, `description`)
    (SELECT `id_manufacturer`, id_lang, (SELECT tl.`description`
        FROM `PREFIX_manufacturer_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_manufacturer`=`PREFIX_manufacturer`.`id_manufacturer`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_manufacturer`);

INSERT IGNORE INTO `PREFIX_supplier_lang` (`id_supplier`, `id_lang`, `description`)
    (SELECT `id_supplier`, id_lang, (SELECT tl.`description`
        FROM `PREFIX_supplier_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_supplier`=`PREFIX_supplier`.`id_supplier`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_supplier`);

INSERT IGNORE INTO `PREFIX_tax_lang` (`id_tax`, `id_lang`, `name`)
    (SELECT `id_tax`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_tax_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_tax`=`PREFIX_tax`.`id_tax`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_tax`);
	
INSERT IGNORE INTO `PREFIX_meta_lang` (`id_meta`, `id_lang`, `description`, `keywords`, `title`)
    (SELECT `id_meta`, id_lang,
	(SELECT tl.`description`
        FROM `PREFIX_meta_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_meta`=`PREFIX_meta`.`id_meta`),
	(SELECT tl.`keywords`
        FROM `PREFIX_meta_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_meta`=`PREFIX_meta`.`id_meta`),
	(SELECT tl.`title`
        FROM `PREFIX_meta_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_meta`=`PREFIX_meta`.`id_meta`)
	FROM `PREFIX_lang` CROSS JOIN `PREFIX_meta`);

INSERT IGNORE INTO `PREFIX_order_message_lang` (`id_order_message`, `id_lang`, `name`, `message`)
    (SELECT `id_order_message`, id_lang,
	(SELECT tl.`name`
        FROM `PREFIX_order_message_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_order_message`=`PREFIX_order_message`.`id_order_message`),
	(SELECT tl.`message`
        FROM `PREFIX_order_message_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_order_message`=`PREFIX_order_message`.`id_order_message`)
	FROM `PREFIX_lang` CROSS JOIN `PREFIX_order_message`);
