SET NAMES 'utf8';

ALTER TABLE `PREFIX_product_attachment` 
CHANGE `id_product` `id_product` INT(10) UNSIGNED NOT NULL,
CHANGE `id_attachment` `id_attachment` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `PREFIX_attribute_impact` 
CHANGE `id_product` `id_product` INT(11) UNSIGNED NOT NULL,
CHANGE `id_attribute` `id_attribute` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `PREFIX_block_cms` 
CHANGE `id_block` `id_block` INT(10) UNSIGNED NOT NULL,
CHANGE `id_cms` `id_cms` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `PREFIX_customization` 
CHANGE `id_cart` `id_cart` int(10) unsigned NOT NULL,
CHANGE `id_product_attribute` `id_product_attribute` int(10) unsigned NOT NULL default '0';

ALTER TABLE `PREFIX_customization_field` 
CHANGE `id_product` `id_product` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_customization_field_lang` 
CHANGE `id_customization_field` `id_customization_field` int(10) unsigned NOT NULL,
CHANGE `id_lang` `id_lang` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_customized_data` 
CHANGE `id_customization` `id_customization` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_discount_category` 
CHANGE `id_category` `id_category` int(11) unsigned NOT NULL,
CHANGE `id_discount` `id_discount` int(11) unsigned NOT NULL;

ALTER TABLE `PREFIX_module_currency` 
CHANGE `id_currency` `id_currency` int(11) unsigned NOT NULL;

ALTER TABLE `PREFIX_module_group` 
CHANGE `id_group` `id_group` int(11) unsigned NOT NULL;

ALTER TABLE `PREFIX_order_return_detail` 
CHANGE `id_customization` `id_customization` int(10) unsigned NOT NULL default '0';

ALTER TABLE `PREFIX_product_attribute_image` 
CHANGE `id_product_attribute` `id_product_attribute` int(10) unsigned NOT NULL,
CHANGE `id_image` `id_image` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_referrer_cache` 
CHANGE `id_connections_source` `id_connections_source` int(11) unsigned NOT NULL,
CHANGE `id_referrer` `id_referrer` int(11) unsigned NOT NULL;

ALTER TABLE `PREFIX_scene_category` 
CHANGE `id_scene` `id_scene` int(10) unsigned NOT NULL,
CHANGE `id_category` `id_category` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_scene_lang` 
CHANGE `id_scene` `id_scene` int(10) unsigned NOT NULL,
CHANGE `id_lang` `id_lang` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_scene_products` 
CHANGE `id_scene` `id_scene` int(10) unsigned NOT NULL,
CHANGE `id_product` `id_product` int(10) unsigned NOT NULL;

ALTER TABLE `PREFIX_search_index` 
CHANGE `id_product` `id_product` int(11) unsigned NOT NULL,
CHANGE `id_word` `id_word` int(11) unsigned NOT NULL;

ALTER TABLE `PREFIX_state` 
CHANGE `id_country` `id_country` int(11) unsigned NOT NULL,
CHANGE `id_zone` `id_zone` int(11) unsigned NOT NULL;

ALTER TABLE `PREFIX_tab` 
CHANGE `id_parent` `id_parent` int(11) unsigned NOT NULL;
