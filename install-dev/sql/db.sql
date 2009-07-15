SET NAMES 'utf8';

CREATE TABLE `PREFIX_access` (
  `id_profile` int(10) unsigned NOT NULL,
  `id_tab` int(10) unsigned NOT NULL,
  `view` int(11) NOT NULL,
  `add` int(11) NOT NULL,
  `edit` int(11) NOT NULL,
  `delete` int(11) NOT NULL,
  PRIMARY KEY  (`id_profile`,`id_tab`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_accessory` (
  `id_product_1` int(10) unsigned NOT NULL,
  `id_product_2` int(10) unsigned NOT NULL,
  KEY `accessory_product` (`id_product_1`,`id_product_2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_address` (
  `id_address` int(10) unsigned NOT NULL auto_increment,
  `id_country` int(10) unsigned NOT NULL,
  `id_state` int(10) unsigned default NULL,
  `id_customer` int(10) unsigned NOT NULL default '0',
  `id_manufacturer` int(10) unsigned NOT NULL default '0',
  `id_supplier` int(10) unsigned NOT NULL default '0',
  `alias` varchar(32) NOT NULL,
  `company` varchar(32) default NULL,
  `lastname` varchar(32) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `address1` varchar(128) NOT NULL,
  `address2` varchar(128) default NULL,
  `postcode` varchar(12) default NULL,
  `city` varchar(64) NOT NULL,
  `other` text,
  `phone` varchar(16) default NULL,
  `phone_mobile` varchar(16) default NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_address`),
  KEY `address_customer` (`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_alias` (
  `id_alias` int(10) unsigned NOT NULL auto_increment,
  `alias` varchar(255) NOT NULL,
  `search` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id_alias`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attachment` (
  `id_attachment` int(10) unsigned NOT NULL auto_increment,
  `file` varchar(40) NOT NULL,
  `mime` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_attachment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attachment_lang` (
  `id_attachment` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) default NULL,
  `description` TEXT,
  PRIMARY KEY  (`id_attachment`, `id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_attachment` (
  `id_product` int(10) NOT NULL,
  `id_attachment` int(10) NOT NULL,
  PRIMARY KEY  (`id_product`,`id_attachment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attribute` (
  `id_attribute` int(10) unsigned NOT NULL auto_increment,
  `id_attribute_group` int(10) unsigned NOT NULL,
  `color` varchar(32) default NULL,
  PRIMARY KEY  (`id_attribute`),
  KEY `attribute_group` (`id_attribute_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attribute_group` (
  `id_attribute_group` int(10) unsigned NOT NULL auto_increment,
  `is_color_group` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_attribute_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attribute_group_lang` (
  `id_attribute_group` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `public_name` varchar(64) NOT NULL,
  PRIMARY KEY  (`id_attribute_group`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attribute_impact` (
  `id_attribute_impact` int(10) unsigned NOT NULL auto_increment,
  `id_product` int(11) NOT NULL,
  `id_attribute` int(11) NOT NULL,
  `weight` float NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY  (`id_attribute_impact`),
  UNIQUE KEY `id_product` (`id_product`,`id_attribute`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_attribute_lang` (
  `id_attribute` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY `attribute_lang` (`id_attribute`,`id_lang`),
  KEY `id_lang` (`id_lang`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_block_cms` (
  `id_block` int(10) NOT NULL,
  `id_cms` int(10) NOT NULL,
  PRIMARY KEY  (`id_block`,`id_cms`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_carrier` (
  `id_carrier` int(10) unsigned NOT NULL auto_increment,
  `id_tax` int(10) unsigned default '0',
  `name` varchar(64) NOT NULL,
  `url` varchar(255) default NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `shipping_handling` tinyint(1) unsigned NOT NULL default '1',
  `range_behavior` tinyint(1) unsigned NOT NULL default '0',
  `is_module` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_carrier`),
  KEY `deleted` (`deleted`,`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_carrier_lang` (
  `id_carrier` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `delay` varchar(128) default NULL,
  UNIQUE KEY `shipper_lang_index` (`id_lang`,`id_carrier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_carrier_zone` (
  `id_carrier` int(10) unsigned NOT NULL,
  `id_zone` int(10) unsigned NOT NULL,
  PRIMARY KEY `carrier_zone_index` (`id_carrier`,`id_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_cart` (
  `id_cart` int(10) unsigned NOT NULL auto_increment,
  `id_carrier` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `id_address_delivery` int(10) unsigned NOT NULL,
  `id_address_invoice` int(10) unsigned NOT NULL,
  `id_currency` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `id_guest` int(10) unsigned NOT NULL,
  `recyclable` tinyint(1) unsigned NOT NULL default '1',
  `gift` tinyint(1) unsigned NOT NULL default '0',
  `gift_message` text,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_cart`),
  KEY `cart_customer` (`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_cart_discount` (
  `id_cart` int(10) unsigned NOT NULL,
  `id_discount` int(10) unsigned NOT NULL,
  KEY `cart_discount_index` (`id_cart`,`id_discount`),
  KEY `id_discount` (`id_discount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_cart_product` (
  `id_cart` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_attribute` int(10) unsigned default NULL,
  `quantity` int(10) unsigned NOT NULL default '0',
  `date_add` datetime NOT NULL,
  KEY `cart_product_index` (`id_cart`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_category` (
  `id_category` int(10) unsigned NOT NULL auto_increment,
  `id_parent` int(10) unsigned NOT NULL,
  `level_depth` tinyint(3) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_category`),
  KEY `category_parent` (`id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_category_group` (
  `id_category` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  KEY `category_group_index` (`id_category`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_category_lang` (
  `id_category` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` text,
  `link_rewrite` varchar(128) NOT NULL,
  `meta_title` varchar(128) default NULL,
  `meta_keywords` varchar(128) default NULL,
  `meta_description` varchar(128) default NULL,
  UNIQUE KEY `category_lang_index` (`id_category`,`id_lang`),
  KEY `category_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_category_product` (
  `id_category` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL default '0',
  KEY `category_product_index` (`id_category`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_cms` (
  `id_cms` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_cms`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_cms_lang` (
  `id_cms` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `meta_title` varchar(128) NOT NULL,
  `meta_description` varchar(255) default NULL,
  `meta_keywords` varchar(255) default NULL,
  `content` longtext,
  `link_rewrite` varchar(128) NOT NULL,
  PRIMARY KEY  (`id_cms`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_configuration` (
  `id_configuration` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `value` text,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_configuration`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_configuration_lang` (
  `id_configuration` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `value` text,
  `date_upd` datetime default NULL,
  PRIMARY KEY  (`id_configuration`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_connections` (
  `id_connections` int(10) unsigned NOT NULL auto_increment,
  `id_guest` int(10) unsigned NOT NULL,
  `id_page` int(10) unsigned NOT NULL,
  `ip_address` varchar(16) default NULL,
  `date_add` datetime NOT NULL,
  `http_referer` varchar(255) default NULL,
  PRIMARY KEY  (`id_connections`),
  KEY `id_guest` (`id_guest`),
  KEY `date_add` (`date_add`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_connections_page` (
  `id_connections` int(10) unsigned NOT NULL,
  `id_page` int(10) unsigned NOT NULL,
  `time_start` datetime NOT NULL,
  `time_end` datetime default NULL,
  PRIMARY KEY  (`id_connections`,`id_page`,`time_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_connections_source` (
  `id_connections_source` int(10) unsigned NOT NULL auto_increment,
  `id_connections` int(10) unsigned NOT NULL,
  `http_referer` varchar(255) default NULL,
  `request_uri` varchar(255) default NULL,
  `keywords` varchar(255) default NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_connections_source`),
  KEY `connections` (`id_connections`),
  KEY `orderby` (`date_add`),
  KEY `http_referer` (`http_referer`),
  KEY `request_uri` (`request_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_contact` (
  `id_contact` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(128) NOT NULL,
  `position` tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_contact`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_contact_lang` (
  `id_contact` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` text,
  UNIQUE KEY `contact_lang_index` (`id_contact`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_country` (
  `id_country` int(10) unsigned NOT NULL auto_increment,
  `id_zone` int(10) unsigned NOT NULL,
  `iso_code` varchar(3) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `contains_states` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_country`),
  KEY `country_iso_code` (`iso_code`),
  KEY `country_` (`id_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_country_lang` (
  `id_country` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  UNIQUE KEY `country_lang_index` (`id_country`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_currency` (
  `id_currency` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `iso_code` varchar(3) NOT NULL default '0',
  `sign` varchar(8) NOT NULL,
  `blank` tinyint(1) unsigned NOT NULL default '0',
  `format` tinyint(1) unsigned NOT NULL default '0',
  `decimals` tinyint(1) unsigned NOT NULL default '1',
  `conversion_rate` decimal(13,6) NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_currency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customer` (
  `id_customer` int(10) unsigned NOT NULL auto_increment,
  `id_gender` int(10) unsigned NOT NULL,
  `secure_key` varchar(32) NOT NULL default '-1',
  `email` varchar(128) NOT NULL,
  `passwd` varchar(32) NOT NULL,
  `last_passwd_gen` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `birthday` date default NULL,
  `lastname` varchar(32) NOT NULL,
  `newsletter` tinyint(1) unsigned NOT NULL default '0',
  `ip_registration_newsletter` varchar(15) default NULL,
  `newsletter_date_add` datetime default NULL,
  `optin` tinyint(1) unsigned NOT NULL default '0',
  `firstname` varchar(32) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_customer`),
  UNIQUE KEY `customer_email` (`email`),
  KEY `customer_login` (`email`,`passwd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customer_group` (
  `id_customer` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  PRIMARY KEY `customer_group_index` (`id_customer`,`id_group`),
  INDEX customer_login(id_group)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customization` (
  `id_customization` int(10) unsigned NOT NULL auto_increment,
  `id_product_attribute` int(10) NOT NULL default '0',
  `id_cart` int(10) NOT NULL,
  `id_product` int(10) NOT NULL,
  `quantity` int(10) NOT NULL,
  `quantity_refunded` INT NOT NULL DEFAULT '0',
  `quantity_returned` INT NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id_customization`,`id_cart`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customization_field` (
  `id_customization_field` int(10) unsigned NOT NULL auto_increment,
  `id_product` int(10) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id_customization_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customization_field_lang` (
  `id_customization_field` int(10) NOT NULL,
  `id_lang` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_customization_field`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_customized_data` (
  `id_customization` int(10) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `index` int(3) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_customization`,`type`,`index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_date_range` (
  `id_date_range` int(10) unsigned NOT NULL auto_increment,
  `time_start` datetime NOT NULL,
  `time_end` datetime NOT NULL,
  PRIMARY KEY  (`id_date_range`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_delivery` (
  `id_delivery` int(10) unsigned NOT NULL auto_increment,
  `id_carrier` int(10) unsigned NOT NULL,
  `id_range_price` int(10) unsigned default NULL,
  `id_range_weight` int(10) unsigned default NULL,
  `id_zone` int(10) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY  (`id_delivery`),
  KEY `id_zone` (`id_zone`),
  KEY `id_carrier` (`id_carrier`,`id_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount` (
  `id_discount` int(10) unsigned NOT NULL auto_increment,
  `id_discount_type` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` decimal(10,2) NOT NULL default '0.00',
  `quantity` int(10) unsigned NOT NULL default '0',
  `quantity_per_user` int(10) unsigned NOT NULL default '1',
  `cumulable` tinyint(1) unsigned NOT NULL default '0',
  `cumulable_reduction` tinyint(1) unsigned NOT NULL default '0',
  `date_from` datetime NOT NULL,
  `date_to` datetime NOT NULL,
  `minimal` decimal(10,2) default NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_discount`),
  KEY `discount_name` (`name`),
  KEY `discount_customer` (`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount_category` (
  `id_category` int(11) NOT NULL,
  `id_discount` int(11) NOT NULL,
  PRIMARY KEY  (`id_category`, `id_discount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount_lang` (
  `id_discount` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `description` text,
  PRIMARY KEY  (`id_discount`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount_quantity` (
  `id_discount_quantity` int(10) unsigned NOT NULL auto_increment,
  `id_discount_type` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_attribute` int(10) unsigned default NULL,
  `quantity` int(10) unsigned NOT NULL,
  `value` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY  (`id_discount_quantity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount_type` (
  `id_discount_type` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_discount_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount_type_lang` (
  `id_discount_type` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY  (`id_discount_type`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_employee` (
  `id_employee` int(10) unsigned NOT NULL auto_increment,
  `id_profile` int(10) unsigned NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `passwd` varchar(32) NOT NULL,
  `last_passwd_gen` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `stats_date_from` date default NULL,
  `stats_date_to` date default NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_employee`),
  KEY `employee_login` (`email`,`passwd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_feature` (
  `id_feature` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_feature`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_feature_lang` (
  `id_feature` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id_feature`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_feature_product` (
  `id_feature` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_feature_value` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_feature`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_feature_value` (
  `id_feature_value` int(10) unsigned NOT NULL auto_increment,
  `id_feature` int(10) unsigned NOT NULL,
  `custom` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`id_feature_value`),
  KEY `feature` (`id_feature`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_feature_value_lang` (
  `id_feature_value` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `value` varchar(255) default NULL,
  PRIMARY KEY  (`id_feature_value`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_group` (
  `id_group` int(10) unsigned NOT NULL auto_increment,
  `reduction` decimal(10,2) NOT NULL default '0.00',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_group_lang` (
  `id_group` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  UNIQUE KEY `attribute_lang_index` (`id_group`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_guest` (
  `id_guest` int(10) unsigned NOT NULL auto_increment,
  `id_operating_system` int(10) unsigned default NULL,
  `id_web_browser` int(10) unsigned default NULL,
  `id_customer` int(10) unsigned default NULL,
  `javascript` tinyint(1) default '0',
  `screen_resolution_x` smallint(5) unsigned default NULL,
  `screen_resolution_y` smallint(5) unsigned default NULL,
  `screen_color` tinyint(3) unsigned default NULL,
  `sun_java` tinyint(1) default NULL,
  `adobe_flash` tinyint(1) default NULL,
  `adobe_director` tinyint(1) default NULL,
  `apple_quicktime` tinyint(1) default NULL,
  `real_player` tinyint(1) default NULL,
  `windows_media` tinyint(1) default NULL,
  `accept_language` varchar(8) default NULL,
  PRIMARY KEY  (`id_guest`),
  KEY `id_customer` (`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_hook` (
  `id_hook` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` text,
  `position` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id_hook`),
  UNIQUE KEY `hook_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_hook_module` (
  `id_module` int(10) unsigned NOT NULL,
  `id_hook` int(10) unsigned NOT NULL,
  `position` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY  (`id_module`,`id_hook`),
  KEY `id_hook` (`id_hook`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_hook_module_exceptions` (
  `id_hook_module_exceptions` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned NOT NULL,
  `id_hook` int(10) unsigned NOT NULL,
  `file_name` varchar(255) default NULL,
  PRIMARY KEY  (`id_hook_module_exceptions`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_image` (
  `id_image` int(10) unsigned NOT NULL auto_increment,
  `id_product` int(10) unsigned NOT NULL,
  `position` tinyint(2) unsigned NOT NULL default '0',
  `cover` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_image`),
  KEY `image_product` (`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_image_lang` (
  `id_image` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `legend` varchar(128) default NULL,
  UNIQUE KEY `image_lang_index` (`id_image`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_image_type` (
  `id_image_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(16) NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `products` tinyint(1) NOT NULL default '1',
  `categories` tinyint(1) NOT NULL default '1',
  `manufacturers` tinyint(1) NOT NULL default '1',
  `suppliers` tinyint(1) NOT NULL default '1',
  `scenes` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id_image_type`),
  KEY `image_type_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_lang` (
  `id_lang` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `active` tinyint(3) unsigned NOT NULL default '0',
  `iso_code` char(2) NOT NULL,
  PRIMARY KEY  (`id_lang`),
  KEY `lang_iso_code` (`iso_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_manufacturer` (
  `id_manufacturer` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_manufacturer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_manufacturer_lang` (
  `id_manufacturer` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `description` text,
  `short_description` varchar(254) default NULL,
  `meta_title` varchar(254) default NULL,
  `meta_keywords` varchar(254) default NULL,
  `meta_description` varchar(254) default NULL,
  PRIMARY KEY  (`id_manufacturer`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_message` (
  `id_message` int(10) unsigned NOT NULL auto_increment,
  `id_cart` int(10) unsigned default NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `id_employee` int(10) unsigned default NULL,
  `id_order` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `private` tinyint(1) unsigned NOT NULL default '1',
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_message`),
  KEY `message_order` (`id_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_message_readed` (
  `id_message` int(10) unsigned NOT NULL,
  `id_employee` int(10) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_message`,`id_employee`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_meta` (
  `id_meta` int(10) unsigned NOT NULL auto_increment,
  `page` varchar(64) NOT NULL,
  PRIMARY KEY  (`id_meta`),
  KEY `meta_name` (`page`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_meta_lang` (
  `id_meta` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `title` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `keywords` varchar(255) default NULL,
  PRIMARY KEY  (`id_meta`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module` (
  `id_module` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_module`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module_country` (
  `id_module` int(10) unsigned NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_module`,`id_country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module_currency` (
  `id_module` int(10) unsigned NOT NULL,
  `id_currency` int(11) NOT NULL,
  PRIMARY KEY  (`id_module`,`id_currency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module_group` (
  `id_module` int(10) unsigned NOT NULL,
  `id_group` int(11) NOT NULL,
  PRIMARY KEY  (`id_module`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_operating_system` (
  `id_operating_system` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id_operating_system`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_orders` (
  `id_order` int(10) unsigned NOT NULL auto_increment,
  `id_carrier` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `id_cart` int(10) unsigned NOT NULL,
  `id_currency` int(10) unsigned NOT NULL,
  `id_address_delivery` int(10) unsigned NOT NULL,
  `id_address_invoice` int(10) unsigned NOT NULL,
  `secure_key` varchar(32) NOT NULL default '-1',
  `payment` varchar(255) NOT NULL,
  `module` varchar(255) default NULL,
  `recyclable` tinyint(1) unsigned NOT NULL default '0',
  `gift` tinyint(1) unsigned NOT NULL default '0',
  `gift_message` text,
  `shipping_number` varchar(32) default NULL,
  `total_discounts` decimal(10,2) NOT NULL default '0.00',
  `total_paid` decimal(10,2) NOT NULL default '0.00',
  `total_paid_real` decimal(10,2) NOT NULL default '0.00',
  `total_products` decimal(10,2) NOT NULL default '0.00',
  `total_shipping` decimal(10,2) NOT NULL default '0.00',
  `total_wrapping` decimal(10,2) NOT NULL default '0.00',
  `invoice_number` int(10) unsigned NOT NULL default '0',
  `delivery_number` int(10) unsigned NOT NULL default '0',
  `invoice_date` datetime NOT NULL,
  `delivery_date` datetime NOT NULL,
  `valid` int(1) unsigned NOT NULL default '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_order`),
  KEY `id_customer` (`id_customer`),
  KEY `id_cart` (`id_cart`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_detail` (
  `id_order_detail` int(10) unsigned NOT NULL auto_increment,
  `id_order` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_attribute_id` int(10) unsigned default NULL,
  `product_name` varchar(255) NOT NULL,
  `product_quantity` int(10) unsigned NOT NULL default '0',
  `product_quantity_in_stock` int(10) unsigned NOT NULL default 0,
  `product_quantity_refunded` int(10) unsigned NOT NULL default '0',
  `product_quantity_return` int(10) unsigned NOT NULL default '0',
  `product_quantity_reinjected` int(10) unsigned NOT NULL default 0,
  `product_price` decimal(13,6) NOT NULL default '0.000000',
  `product_quantity_discount` decimal(13,6) NOT NULL default '0.000000',
  `product_ean13` varchar(13) default NULL,
  `product_reference` varchar(32) default NULL,
  `product_supplier_reference` varchar(32) default NULL,
  `product_weight` float NOT NULL,
  `tax_name` varchar(16) NOT NULL,
  `tax_rate` decimal(10,2) NOT NULL default '0.00',
  `ecotax` decimal(10,2) NOT NULL default '0.00',
  `download_hash` varchar(255) default NULL,
  `download_nb` int(10) unsigned default '0',
  `download_deadline` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_order_detail`),
  KEY `order_detail_order` (`id_order`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_discount` (
  `id_order_discount` int(10) unsigned NOT NULL auto_increment,
  `id_order` int(10) unsigned NOT NULL,
  `id_discount` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id_order_discount`),
  KEY `order_discount_order` (`id_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_history` (
  `id_order_history` int(10) unsigned NOT NULL auto_increment,
  `id_employee` int(10) unsigned NOT NULL,
  `id_order` int(10) unsigned NOT NULL,
  `id_order_state` int(10) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_order_history`),
  KEY `order_history_order` (`id_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_message` (
  `id_order_message` int(10) unsigned NOT NULL auto_increment,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_order_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_message_lang` (
  `id_order_message` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id_order_message`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_return` (
  `id_order_return` int(10) unsigned NOT NULL auto_increment,
  `id_customer` int(10) unsigned NOT NULL,
  `id_order` int(10) unsigned NOT NULL,
  `state` tinyint(1) unsigned NOT NULL default '1',
  `question` text NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_order_return`),
  KEY `order_return_customer` (`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_return_detail` (
  `id_order_return` int(10) unsigned NOT NULL,
  `id_order_detail` int(10) unsigned NOT NULL,
  `id_customization` int(10) NOT NULL default '0',
  `product_quantity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_order_return`,`id_order_detail`,`id_customization`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_return_state` (
  `id_order_return_state` int(10) unsigned NOT NULL auto_increment,
  `color` varchar(32) default NULL,
  PRIMARY KEY  (`id_order_return_state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_return_state_lang` (
  `id_order_return_state` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  UNIQUE KEY `order_state_lang_index` (`id_order_return_state`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_slip` (
  `id_order_slip` int(10) unsigned NOT NULL auto_increment,
  `id_customer` int(10) unsigned NOT NULL,
  `id_order` int(10) unsigned NOT NULL,
  `shipping_cost` tinyint(3) unsigned NOT NULL default '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_order_slip`),
  KEY `order_slip_customer` (`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_slip_detail` (
  `id_order_slip` int(10) unsigned NOT NULL,
  `id_order_detail` int(10) unsigned NOT NULL,
  `product_quantity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_order_slip`,`id_order_detail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_state` (
  `id_order_state` int(10) unsigned NOT NULL auto_increment,
  `invoice` tinyint(1) unsigned default '0',
  `send_email` tinyint(1) unsigned NOT NULL default '0',
  `color` varchar(32) default NULL,
  `unremovable` tinyint(1) unsigned NOT NULL,
  `hidden` tinyint(1) UNSIGNED NOT NULL default '0',
  `logable` tinyint(1) NOT NULL default '0',
  `delivery` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_order_state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_state_lang` (
  `id_order_state` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `template` varchar(64) NOT NULL,
  UNIQUE KEY `order_state_lang_index` (`id_order_state`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_pack` (
  `id_product_pack` int(10) unsigned NOT NULL,
  `id_product_item` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id_product_pack`,`id_product_item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_page` (
  `id_page` int(10) unsigned NOT NULL auto_increment,
  `id_page_type` int(10) unsigned NOT NULL,
  `id_object` int(10) unsigned default NULL,
  PRIMARY KEY  (`id_page`),
  KEY `id_page_type` (`id_page_type`),
  KEY `id_object` (`id_object`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_page_type` (
  `id_page_type` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_page_type`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_page_viewed` (
  `id_page` int(10) unsigned NOT NULL,
  `id_date_range` int(10) unsigned NOT NULL,
  `counter` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_page`,`id_date_range`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product` (
  `id_product` int(10) unsigned NOT NULL auto_increment,
  `id_supplier` int(10) unsigned default NULL,
  `id_manufacturer` int(10) unsigned default NULL,
  `id_tax` int(10) unsigned NOT NULL,
  `id_category_default` int(10) unsigned default NULL,
  `id_color_default` int(10) unsigned default NULL,
  `on_sale` tinyint(1) unsigned NOT NULL default '0',
  `ean13` varchar(13) default NULL,
  `ecotax` decimal(10,2) NOT NULL default '0.00',
  `quantity` int(10) unsigned NOT NULL default '0',
  `price` decimal(13,6) NOT NULL default '0.000000',
  `wholesale_price` decimal(13,6) NOT NULL default '0.000000',
  `reduction_price` decimal(10,2) default NULL,
  `reduction_percent` float default NULL,
  `reduction_from` date default NULL,
  `reduction_to` date default NULL,
  `reference` varchar(32) default NULL,
  `supplier_reference` varchar(32) default NULL,
  `location` varchar(64) default NULL,
  `weight` float NOT NULL default '0',
  `out_of_stock` int(10) unsigned NOT NULL default '2',
  `quantity_discount` tinyint(1) default '0',
  `customizable` tinyint(2) NOT NULL default '0',
  `uploadable_files` tinyint(4) NOT NULL default '0',
  `text_fields` tinyint(4) NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  `indexed` tinyint(1) NOT NULL default '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_product`),
  KEY `product_supplier` (`id_supplier`),
  KEY `product_manufacturer` (`id_manufacturer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_attribute` (
  `id_product_attribute` int(10) unsigned NOT NULL auto_increment,
  `id_product` int(10) unsigned NOT NULL,
  `reference` varchar(32) default NULL,
  `supplier_reference` varchar(32) default NULL,
  `location` varchar(64) default NULL,
  `ean13` varchar(13) default NULL,
  `wholesale_price` decimal(13,6) NOT NULL default '0.000000',
  `price` decimal(10,2) NOT NULL default '0.00',
  `ecotax` decimal(10,2) NOT NULL default '0.00',
  `quantity` int(10) unsigned NOT NULL default '0',
  `weight` float NOT NULL default '0',
  `default_on` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_product_attribute`),
  KEY `product_attribute_product` (`id_product`),
  KEY `reference` (`reference`),
  KEY `supplier_reference` (`supplier_reference`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_attribute_combination` (
  `id_attribute` int(10) unsigned NOT NULL,
  `id_product_attribute` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_attribute`,`id_product_attribute`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_attribute_image` (
  `id_product_attribute` int(10) NOT NULL,
  `id_image` int(10) NOT NULL,
  PRIMARY KEY  (`id_product_attribute`,`id_image`),
  KEY `id_image` (`id_image`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_download` (
  `id_product_download` int(10) unsigned NOT NULL auto_increment,
  `id_product` int(10) unsigned NOT NULL,
  `display_filename` varchar(255) default NULL,
  `physically_filename` varchar(255) default NULL,
  `date_deposit` datetime NOT NULL,
  `date_expiration` datetime default NULL,
  `nb_days_accessible` int(10) unsigned default NULL,
  `nb_downloadable` int(10) unsigned default '1',
  `active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_product_download`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_lang` (
  `id_product` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `description` text,
  `description_short` text,
  `link_rewrite` varchar(128) NOT NULL,
  `meta_description` varchar(255) default NULL,
  `meta_keywords` varchar(255) default NULL,
  `meta_title` varchar(128) default NULL,
  `name` varchar(128) NOT NULL,
  `available_now` varchar(255) default NULL,
  `available_later` varchar(255) default NULL,
  UNIQUE KEY `product_lang_index` (`id_product`,`id_lang`),
  KEY `id_lang` (`id_lang`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_sale` (
  `id_product` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL default '0',
  `sale_nbr` int(10) unsigned NOT NULL default '0',
  `date_upd` date NOT NULL,
  PRIMARY KEY  (`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_tag` (
  `id_product` int(10) unsigned NOT NULL,
  `id_tag` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_product`,`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_profile` (
  `id_profile` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id_profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_profile_lang` (
  `id_lang` int(10) unsigned NOT NULL,
  `id_profile` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY  (`id_profile`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_quick_access` (
  `id_quick_access` int(10) unsigned NOT NULL auto_increment,
  `new_window` tinyint(1) NOT NULL default '0',
  `link` varchar(128) NOT NULL,
  PRIMARY KEY  (`id_quick_access`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_quick_access_lang` (
  `id_quick_access` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_quick_access`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_range_price` (
  `id_range_price` int(10) unsigned NOT NULL auto_increment,
  `id_carrier` int(10) unsigned NOT NULL,
  `delimiter1` decimal(13,6) NOT NULL,
  `delimiter2` decimal(13,6) NOT NULL,
  PRIMARY KEY  (`id_range_price`),
  UNIQUE KEY `id_carrier` (`id_carrier`,`delimiter1`,`delimiter2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_range_weight` (
  `id_range_weight` int(10) unsigned NOT NULL auto_increment,
  `id_carrier` int(10) unsigned NOT NULL,
  `delimiter1` decimal(13,6) NOT NULL,
  `delimiter2` decimal(13,6) NOT NULL,
  PRIMARY KEY  (`id_range_weight`),
  UNIQUE KEY `id_carrier` (`id_carrier`,`delimiter1`,`delimiter2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_referrer` (
  `id_referrer` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `passwd` varchar(32) default NULL,
  `http_referer_regexp` varchar(64) default NULL,
  `http_referer_like` varchar(64) default NULL,
  `request_uri_regexp` varchar(64) default NULL,
  `request_uri_like` varchar(64) default NULL,
  `http_referer_regexp_not` varchar(64) default NULL,
  `http_referer_like_not` varchar(64) default NULL,
  `request_uri_regexp_not` varchar(64) default NULL,
  `request_uri_like_not` varchar(64) default NULL,
  `base_fee` decimal(5,2) NOT NULL default '0.00',
  `percent_fee` decimal(5,2) NOT NULL default '0.00',
  `click_fee` decimal(5,2) NOT NULL default '0.00',
  `cache_visitors` int(11) default NULL,
  `cache_visits` int(11) default NULL,
  `cache_pages` int(11) default NULL,
  `cache_registrations` int(11) default NULL,
  `cache_orders` int(11) default NULL,
  `cache_sales` decimal(10,2) default NULL,
  `cache_reg_rate` decimal(5,4) default NULL,
  `cache_order_rate` decimal(5,4) default NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY  (`id_referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_referrer_cache` (
  `id_connections_source` int(11) NOT NULL,
  `id_referrer` int(11) NOT NULL,
  PRIMARY KEY  (`id_connections_source`, `id_referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_scene` (
  `id_scene` int(10) unsigned NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id_scene`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_scene_category` (
  `id_scene` int(10) NOT NULL,
  `id_category` int(10) NOT NULL,
  PRIMARY KEY  (`id_scene`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_scene_lang` (
  `id_scene` int(10) NOT NULL,
  `id_lang` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_scene`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_scene_products` (
  `id_scene` int(10) NOT NULL,
  `id_product` int(10) NOT NULL,
  `x_axis` int(4) NOT NULL,
  `y_axis` int(4) NOT NULL,
  `zone_width` int(3) NOT NULL,
  `zone_height` int(3) NOT NULL,
  PRIMARY KEY (`id_scene`, `id_product`, `x_axis`, `y_axis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_engine` (
  `id_search_engine` int(10) unsigned NOT NULL auto_increment,
  `server` varchar(64) NOT NULL,
  `getvar` varchar(16) NOT NULL,
  PRIMARY KEY  (`id_search_engine`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_index` (
  `id_product` int(11) NOT NULL,
  `id_word` int(11) NOT NULL,
  `weight` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id_word`, `id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_word` (
  `id_word` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `word` varchar(15) NOT NULL,
  PRIMARY KEY  (`id_word`),
  UNIQUE KEY `id_lang` (`id_lang`,`word`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_state` (
  `id_state` int(10) unsigned NOT NULL auto_increment,
  `id_country` int(11) NOT NULL,
  `id_zone` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `iso_code` char(4) NOT NULL,
  `tax_behavior` smallint(1) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_subdomain` (
  `id_subdomain` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(16) NOT NULL,
  PRIMARY KEY  (`id_subdomain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_supplier` (
  `id_supplier` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_supplier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_supplier_lang` (
  `id_supplier` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `description` text,
  `meta_title` varchar(254) default NULL,
  `meta_keywords` varchar(254) default NULL,
  `meta_description` varchar(254) default NULL,
  PRIMARY KEY  (`id_supplier`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tab` (
  `id_tab` int(10) unsigned NOT NULL auto_increment,
  `id_parent` int(11) NOT NULL,
  `class_name` varchar(64) NOT NULL,
  `module` varchar(64) NULL,
  `position` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_tab`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tab_lang` (
  `id_lang` int(10) unsigned NOT NULL,
  `id_tab` int(10) unsigned NOT NULL,
  `name` varchar(32) default NULL,
  PRIMARY KEY  (`id_tab`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tag` (
  `id_tag` int(10) unsigned NOT NULL auto_increment,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`id_tag`),
  KEY `tag_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tax` (
  `id_tax` int(10) unsigned NOT NULL auto_increment,
  `rate` float NOT NULL,
  PRIMARY KEY  (`id_tax`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tax_lang` (
  `id_tax` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  UNIQUE KEY `tax_lang_index` (`id_tax`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tax_state` (
  `id_tax` int(10) unsigned NOT NULL,
  `id_state` int(10) unsigned NOT NULL,
  KEY `tax_state_index` (`id_tax`,`id_state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tax_zone` (
  `id_tax` int(10) unsigned NOT NULL,
  `id_zone` int(10) unsigned NOT NULL,
  KEY `tax_zone_index` (`id_tax`,`id_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_timezone (
	id_timezone int(10) unsigned NOT NULL auto_increment,
	name VARCHAR(32) NOT NULL,
	PRIMARY KEY timezone_index(`id_timezone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_web_browser` (
  `id_web_browser` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id_web_browser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_zone` (
  `id_zone` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
