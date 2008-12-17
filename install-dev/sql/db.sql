SET NAMES 'utf8';

CREATE TABLE PREFIX_lang (
  id_lang INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL,
  active TINYINT UNSIGNED NOT NULL DEFAULT 0,
  iso_code CHAR(2) NOT NULL,
  PRIMARY KEY(id_lang),
  INDEX lang_iso_code(iso_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_image_type (
  id_image_type INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(16) NOT NULL,
  width INTEGER UNSIGNED NOT NULL,
  height INTEGER UNSIGNED NOT NULL,
  products BOOL NOT NULL DEFAULT 1,
  categories BOOL NOT NULL DEFAULT 1,
  manufacturers BOOL NOT NULL DEFAULT 1,
  suppliers BOOL NOT NULL DEFAULT 1,
  scenes BOOL NOT NULL DEFAULT 1,
  PRIMARY KEY(id_image_type),
  INDEX image_type_name(name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_module (
  id_module INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  active INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_module),
  INDEX `name`(`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_manufacturer (
  id_manufacturer INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_manufacturer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_manufacturer_lang (
  id_manufacturer INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id_manufacturer, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_hook (
  id_hook INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  title VARCHAR(64) NOT NULL,
  description TEXT NULL,
  position BOOL NOT NULL DEFAULT 1,
  PRIMARY KEY(id_hook),
  UNIQUE KEY hook_name(name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_discount_type (
  id_discount_type INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id_discount_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_currency (
  id_currency INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL,
  iso_code varchar(3) NOT NULL DEFAULT 0,
  sign VARCHAR(8) NOT NULL,
  blank TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  format TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  decimals TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  conversion_rate DECIMAL(13,6) NOT NULL,
  deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_currency)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_feature (
  id_feature INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id_feature)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_state (
  id_order_state INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  invoice TINYINT(1) UNSIGNED NULL DEFAULT 0,
  send_email TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  color VARCHAR(32) NULL,
  unremovable TINYINT(1) UNSIGNED NOT NULL,
  logable TINYINT(1) NOT NULL DEFAULT 0,
  delivery TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_order_state)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_tab (
  id_tab INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_parent INTEGER NOT NULL,
  class_name VARCHAR(64) NOT NULL,
  position INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(id_tab)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_supplier (
  id_supplier INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_supplier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_supplier_lang (
  id_supplier INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id_supplier, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_zone (
  id_zone INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_zone)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_tax (
  id_tax INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  rate FLOAT NOT NULL,
  PRIMARY KEY(id_tax)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_quick_access (
  id_quick_access INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  new_window TINYINT(1) NOT NULL DEFAULT 0,
  link VARCHAR(128) NOT NULL,
  PRIMARY KEY(id_quick_access)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_profile (
  id_profile INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id_profile)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_range_weight (
  id_range_weight INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_carrier INTEGER UNSIGNED DEFAULT NULL,
  delimiter1 decimal(13,6) NOT NULL DEFAULT 0.000000,
  delimiter2 decimal(13,6) NOT NULL DEFAULT 0.000000,
  PRIMARY KEY(id_range_weight),
  UNIQUE range_weight_unique(delimiter1, delimiter2, id_carrier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_range_price (
  id_range_price INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_carrier INTEGER UNSIGNED DEFAULT NULL,
  delimiter1 FLOAT NOT NULL DEFAULT 0,
  delimiter2 FLOAT NOT NULL DEFAULT 0,
  PRIMARY KEY(id_range_price),
  UNIQUE range_price_unique(delimiter1, delimiter2, id_carrier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_category (
  id_category INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_parent INTEGER UNSIGNED NOT NULL,
  level_depth TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_category),
  INDEX category_parent(id_parent)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_configuration (
  id_configuration INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL UNIQUE,
  value TEXT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_configuration),
  KEY `configuration_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_accessory (
  id_product_1 INTEGER UNSIGNED NOT NULL,
  id_product_2 INTEGER UNSIGNED NOT NULL,
  INDEX accessory_product(id_product_1, id_product_2)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_attribute_group (
  id_attribute_group INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  is_color_group TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(id_attribute_group)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_carrier (
  id_carrier INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_tax INT(10) UNSIGNED NULL DEFAULT 0,
  name VARCHAR(64) NOT NULL,
  url VARCHAR(255) NULL,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  shipping_handling TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  range_behavior TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_carrier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_contact (
  id_contact INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(128) NOT NULL,
  position TINYINT(2) UNSIGNED NOT NULL default '0',
  PRIMARY KEY(id_contact)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_customer (
  id_customer INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_gender INTEGER UNSIGNED NOT NULL,
  secure_key VARCHAR(32) NOT NULL DEFAULT '-1',
  email VARCHAR(128) NOT NULL,
  passwd VARCHAR(32) NOT NULL,
  last_passwd_gen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  birthday DATE NULL,
  lastname VARCHAR(32) NOT NULL,
  newsletter TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  ip_registration_newsletter VARCHAR(15) NULL DEFAULT NULL,
  newsletter_date_add DATETIME NULL,
  optin TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  firstname VARCHAR(32) NOT NULL,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_customer),
  UNIQUE customer_email(email(128)),
  INDEX customer_login(email, passwd)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_country (
  id_country INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_zone INTEGER UNSIGNED NOT NULL,
  iso_code VARCHAR(3) NOT NULL,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  contains_states tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(id_country),
  INDEX country_iso_code(iso_code),
  INDEX country_(id_zone)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_feature_value (
  id_feature_value INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_feature INTEGER UNSIGNED NOT NULL,
  custom TINYINT UNSIGNED NULL,
  PRIMARY KEY(id_feature_value),
  INDEX feature(id_feature)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_tag (
  id_tag INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(32) NOT NULL,
  PRIMARY KEY(id_tag),
  INDEX tag_name(name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_employee (
  id_employee INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_profile INTEGER UNSIGNED NOT NULL,
  lastname VARCHAR(32) NOT NULL,
  firstname VARCHAR(32) NOT NULL,
  email VARCHAR(128) NOT NULL,
  passwd VARCHAR(32) NOT NULL,
  last_passwd_gen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_employee),
  INDEX employee_login(email, passwd)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_connections (
  id_connections INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_guest INTEGER(10) UNSIGNED NOT NULL,
  id_page INTEGER(10) UNSIGNED NOT NULL,
  ip_address VARCHAR(16) NULL,
  date_add DATETIME NOT NULL,
  http_referer VARCHAR(256) NULL,
  PRIMARY KEY(id_connections),
  INDEX id_guest (id_guest)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_attribute (
  id_attribute INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_attribute_group INTEGER UNSIGNED NOT NULL,
  color VARCHAR(32) NULL DEFAULT NULL,
  PRIMARY KEY(id_attribute),
  INDEX attribute_group(id_attribute_group)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_tab_lang (
  id_lang INTEGER UNSIGNED NOT NULL,
  id_tab INTEGER UNSIGNED NOT NULL,
  name VARCHAR(32) NULL,
  PRIMARY KEY (id_tab, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_address (
  id_address INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_country INTEGER UNSIGNED NOT NULL,
  id_state INTEGER UNSIGNED NULL,
  id_customer INTEGER UNSIGNED NOT NULL DEFAULT 0,
  id_manufacturer INT(10) UNSIGNED NOT NULL DEFAULT 0,
  id_supplier INT(10) UNSIGNED NOT NULL DEFAULT 0,
  alias VARCHAR(32) NOT NULL,
  company VARCHAR(32) NULL,
  lastname VARCHAR(32) NOT NULL,
  firstname VARCHAR(32) NOT NULL,
  address1 VARCHAR(128) NOT NULL,
  address2 VARCHAR(128) NULL,
  postcode VARCHAR(12) NULL,
  city VARCHAR(64) NOT NULL,
  other TEXT NULL,
  phone VARCHAR(16) NULL,
  phone_mobile VARCHAR(16) NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_address),
  INDEX address_customer(id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_state_lang (
  id_order_state INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  template VARCHAR(64) NOT NULL,
  UNIQUE INDEX order_state_lang_index(id_order_state, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_attribute_group_lang (
  id_attribute_group INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(128) NOT NULL,
  public_name VARCHAR(64) NOT NULL,
  PRIMARY KEY (id_attribute_group, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_attribute_lang (
  id_attribute INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(128) NOT NULL,
  UNIQUE INDEX attribute_lang_index(id_attribute, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_quick_access_lang (
  id_quick_access INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(32) NOT NULL,
  PRIMARY KEY(id_quick_access, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_profile_lang (
  id_lang INTEGER UNSIGNED NOT NULL,
  id_profile INTEGER UNSIGNED NOT NULL,
  name VARCHAR(128) NOT NULL,
  PRIMARY KEY (id_profile, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_contact_lang (
  id_contact INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(32) NOT NULL,
  description TEXT NULL,
  UNIQUE INDEX contact_lang_index(id_contact, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_carrier_lang (
  id_carrier INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  delay VARCHAR(128) NULL,
  UNIQUE INDEX shipper_lang_index(id_lang, id_carrier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_feature_value_lang (
  id_feature_value INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  value VARCHAR(255) NULL,
  PRIMARY KEY(id_feature_value, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_feature_lang (
  id_feature INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(128) NULL,
  PRIMARY KEY(id_feature, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_hook_module (
  id_module INTEGER UNSIGNED NOT NULL,
  id_hook INTEGER UNSIGNED NOT NULL,
  position TINYINT(2) UNSIGNED NOT NULL,
  INDEX hook_module_index(id_module, id_hook)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_configuration_lang (
  id_configuration INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  value TEXT NULL,
  date_upd DATETIME NULL,
  PRIMARY KEY (id_configuration, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_discount (
  id_discount INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_discount_type INTEGER UNSIGNED NOT NULL,
  id_customer INTEGER UNSIGNED NOT NULL,
  name VARCHAR(32) NOT NULL,
  value DECIMAL(10,2) NOT NULL DEFAULT 0,
  quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
  quantity_per_user INT(10) UNSIGNED NOT NULL DEFAULT 1,
  cumulable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  cumulable_reduction TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  date_from DATETIME NOT NULL,
  date_to DATETIME NOT NULL,
  minimal DECIMAL(10,2) NULL,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_discount),
  INDEX discount_name(name),
  INDEX discount_customer(id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_access (
  id_profile INTEGER UNSIGNED NOT NULL,
  id_tab INTEGER UNSIGNED NOT NULL,
  view INTEGER NOT NULL,
  `add` INTEGER NOT NULL,
  edit INTEGER NOT NULL,
  `delete` INTEGER NOT NULL,
  PRIMARY KEY(id_profile, id_tab)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_discount_type_lang (
  id_discount_type INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  PRIMARY KEY (id_discount_type, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_discount_lang (
  id_discount INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id_discount, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_country_lang (
  id_country INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  UNIQUE INDEX country_lang_index(id_country, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_tax_lang (
  id_tax INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(32) NOT NULL,
  UNIQUE INDEX tax_lang_index(id_tax, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_hook_module_exceptions (
  id_hook_module_exceptions INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_module INTEGER UNSIGNED NOT NULL,
  id_hook INTEGER UNSIGNED NOT NULL,
  file_name VARCHAR(255) NULL,
  PRIMARY KEY(id_hook_module_exceptions)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_category_lang (
  id_category INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  name VARCHAR(128) NOT NULL,
  description TEXT NULL,
  link_rewrite  VARCHAR(128) NOT NULL,
  meta_title VARCHAR(128) NULL,
  meta_keywords VARCHAR(128) NULL,
  meta_description VARCHAR(128) NULL,
  UNIQUE INDEX category_lang_index(id_category, id_lang),
  INDEX category_name (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_product (
  id_product INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_supplier INTEGER UNSIGNED NULL,
  id_manufacturer INTEGER UNSIGNED NULL,
  id_tax INTEGER UNSIGNED NOT NULL,
  id_category_default INTEGER UNSIGNED DEFAULT NULL,
  id_color_default INTEGER UNSIGNED DEFAULT NULL,
  on_sale TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  ean13 VARCHAR(13) NULL,
  ecotax DECIMAL(10,2) NOT NULL DEFAULT 0,
  quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
  price DECIMAL(13,6) NOT NULL DEFAULT 0.000000,
  wholesale_price decimal(13,6) NOT NULL DEFAULT 0.000000,
  reduction_price DECIMAL(10,2) NULL,
  reduction_percent FLOAT NULL,
  reduction_from date DEFAULT NULL,
  reduction_to date DEFAULT NULL,
  reference VARCHAR(32) NULL,
  supplier_reference VARCHAR(32) NULL,
  location varchar(64) NULL,
  weight FLOAT NOT NULL DEFAULT 0,
  out_of_stock INTEGER UNSIGNED NOT NULL DEFAULT 2,
  quantity_discount TINYINT(1) NULL DEFAULT 0,
  customizable TINYINT(2) NOT NULL DEFAULT 0,
  uploadable_files TINYINT NOT NULL DEFAULT 0,
  text_fields TINYINT NOT NULL DEFAULT 0,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_product),
  INDEX product_supplier(id_supplier),
  INDEX product_manufacturer(id_manufacturer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_sale` (
`id_product` INT( 10 ) UNSIGNED NOT NULL ,
`quantity` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`sale_nbr` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`date_upd` DATE NOT NULL ,
PRIMARY KEY ( `id_product` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_delivery (
  id_delivery INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_carrier INTEGER UNSIGNED NOT NULL,
  id_range_price INTEGER UNSIGNED NULL,
  id_range_weight INTEGER UNSIGNED NULL,
  id_zone INTEGER UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  PRIMARY KEY(id_delivery)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_cart (
  id_cart INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_carrier INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  id_address_delivery INTEGER UNSIGNED NOT NULL,
  id_address_invoice INTEGER UNSIGNED NOT NULL,
  id_currency INTEGER UNSIGNED NOT NULL,
  id_customer INTEGER UNSIGNED NOT NULL,
  recyclable TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  gift TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  gift_message TEXT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_cart),
  INDEX cart_customer(id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_orders (
  id_order INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_carrier INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  id_customer INTEGER UNSIGNED NOT NULL,
  id_cart INTEGER UNSIGNED NOT NULL,
  id_currency INTEGER UNSIGNED NOT NULL,
  id_address_delivery INTEGER UNSIGNED NOT NULL,
  id_address_invoice INTEGER UNSIGNED NOT NULL,
  secure_key VARCHAR(32) NOT NULL DEFAULT '-1',
  payment VARCHAR(255) NOT NULL,
  module VARCHAR(255) NULL,
  recyclable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  gift TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  gift_message TEXT NULL,
  shipping_number VARCHAR(32) NULL,
  total_discounts DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_paid_real DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_products DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_wrapping DECIMAL(10,2) NOT NULL DEFAULT 0,
  invoice_number INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  delivery_number INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  invoice_date DATETIME NOT NULL,
  delivery_date DATETIME NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_order),
  INDEX orders_customer(id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_image (
  id_image INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_product INTEGER UNSIGNED NOT NULL,
  position TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  cover TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_image),
  INDEX image_product(id_product)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_discount (
  id_order_discount INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_order INTEGER UNSIGNED NOT NULL,
  id_discount INTEGER(10) UNSIGNED NOT NULL,
  name VARCHAR(32) NOT NULL,
  value DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY(id_order_discount),
  INDEX order_discount_order(id_order)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_detail (
  id_order_detail INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_order INTEGER UNSIGNED NOT NULL,
  product_id INTEGER UNSIGNED NOT NULL,
  product_attribute_id INTEGER UNSIGNED NULL,
  product_name VARCHAR(255) NOT NULL,
  product_quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
  product_quantity_cancelled INTEGER UNSIGNED NOT NULL DEFAULT 0,
  product_quantity_return INTEGER UNSIGNED NOT NULL DEFAULT 0,
  product_price DECIMAL(13,6) NOT NULL DEFAULT 0,
  product_quantity_discount DECIMAL(13,6) NOT NULL DEFAULT 0,
  product_ean13 VARCHAR(13) default NULL,
  product_reference VARCHAR(32) NULL,
  product_supplier_reference VARCHAR(32) NULL,  
  product_weight FLOAT NOT NULL,
  tax_name VARCHAR(16) NOT NULL,
  tax_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
  ecotax DECIMAL(10, 2) NOT NULL DEFAULT 0,
  download_hash VARCHAR(255) DEFAULT NULL,
  download_nb INT(10) UNSIGNED DEFAULT 0,
  download_deadline DATETIME NULL DEFAULT 0,
  PRIMARY KEY(id_order_detail),
  INDEX order_detail_order(id_order)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_return (
  id_order_return INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_customer INTEGER UNSIGNED NOT NULL,
  id_order INTEGER UNSIGNED NOT NULL,
  state tinyint(1) unsigned NOT NULL DEFAULT 1,
  question TEXT NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_order_return),
  INDEX order_return_customer(id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_return_detail (
  id_order_return INTEGER UNSIGNED NOT NULL,
  id_order_detail  INTEGER UNSIGNED NOT NULL,
  id_customization INTEGER(10) NOT NULL DEFAULT 0,
  product_quantity INTEGER(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (id_order_return, id_order_detail, id_customization)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_slip (
  id_order_slip INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_customer INTEGER UNSIGNED NOT NULL,
  id_order INTEGER UNSIGNED NOT NULL,
  shipping_cost TINYINT UNSIGNED NOT NULL DEFAULT 0,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_order_slip),
  INDEX order_slip_customer(id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_slip_detail (
  id_order_slip INTEGER UNSIGNED NOT NULL,
  id_order_detail  INTEGER UNSIGNED NOT NULL,
  product_quantity int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id_order_slip`,`id_order_detail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_product_attribute (
  id_product_attribute INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_image INTEGER UNSIGNED NULL,
  id_product INTEGER UNSIGNED NOT NULL,
  reference VARCHAR(32) NULL,
  supplier_reference VARCHAR(32) NULL,  
  location varchar(64) NULL,
  ean13 VARCHAR(13) NULL,
  wholesale_price decimal(13,6) NOT NULL DEFAULT 0.000000,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  ecotax DECIMAL(10,2) NOT NULL DEFAULT 0,
  quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
  weight FLOAT NOT NULL DEFAULT 0,
  default_on TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id_product_attribute),
  INDEX product_attribute_product(id_product)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_product_lang (
  id_product INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  description TEXT NULL,
  description_short TEXT NULL,
  link_rewrite VARCHAR(128) NOT NULL,
  meta_description VARCHAR(255) NULL,
  meta_keywords VARCHAR(255) NULL,
  meta_title VARCHAR(128) NULL,
  name VARCHAR(128) NOT NULL,
  available_now VARCHAR(255) NULL,
  available_later VARCHAR(255) NULL,
  UNIQUE INDEX product_lang_index(id_product, id_lang),
  FULLTEXT KEY `fts` (`name`,`description_short`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_product_tag (
  id_product INTEGER UNSIGNED NOT NULL,
  id_tag INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(id_product, id_tag)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_category_product (
  id_category INTEGER UNSIGNED NOT NULL,
  id_product INTEGER UNSIGNED NOT NULL,
  position INTEGER UNSIGNED NOT NULL DEFAULT 0,
  INDEX category_product_index(id_category, id_product)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_cart_discount (
  id_cart INTEGER UNSIGNED NOT NULL,
  id_discount INTEGER UNSIGNED NOT NULL,
  INDEX cart_discount_index(id_cart, id_discount)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_image_lang (
  id_image INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  legend VARCHAR(128) NULL,
  UNIQUE INDEX image_lang_index(id_image, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_history (
  id_order_history INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_employee INTEGER UNSIGNED NOT NULL,
  id_order INTEGER UNSIGNED NOT NULL,
  id_order_state INTEGER UNSIGNED NOT NULL,
  date_add DATETIME NOT NULL,
  PRIMARY KEY(id_order_history),
  INDEX order_history_order(id_order)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_feature_product (
  id_feature INTEGER UNSIGNED NOT NULL,
  id_product INTEGER UNSIGNED NOT NULL,
  id_feature_value INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(id_feature, id_product)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_cart_product (
  id_cart INTEGER UNSIGNED NOT NULL,
  id_product INTEGER UNSIGNED NOT NULL,
  id_product_attribute INTEGER UNSIGNED NULL,
  quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
  date_add DATETIME NOT NULL,
  INDEX cart_product_index(id_cart, id_product)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_message (
  id_message INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cart INTEGER UNSIGNED NULL,
  id_customer INTEGER UNSIGNED NOT NULL,
  id_employee INTEGER UNSIGNED NULL,
  id_order INTEGER UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  private TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  date_add DATETIME NOT NULL,
  PRIMARY KEY(id_message),
  INDEX message_order(id_order)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_product_attribute_combination (
  id_attribute INTEGER UNSIGNED NOT NULL,
  id_product_attribute INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id_attribute`,`id_product_attribute`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_product_download` (
  id_product_download INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  id_product INT(10) UNSIGNED NOT NULL,
  display_filename VARCHAR(255) DEFAULT NULL,
  physically_filename VARCHAR(255) DEFAULT NULL,
  date_deposit DATETIME NOT NULL,
  date_expiration DATETIME DEFAULT NULL,
  nb_days_accessible int(10) UNSIGNED DEFAULT NULL,
  nb_downloadable int(10) UNSIGNED DEFAULT 1,
  active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id_product_download)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_carrier_zone` (
  id_carrier int(10) unsigned NOT NULL,
  id_zone int(10) unsigned NOT NULL,
  INDEX carrier_zone_index(id_carrier, id_zone)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tax_zone` (
  id_tax int(10) unsigned NOT NULL,
  id_zone int(10) unsigned NOT NULL,
   INDEX tax_zone_index(id_tax, id_zone)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_tax_state` (
  id_tax int(10) unsigned NOT NULL,
  id_state int(10) unsigned NOT NULL,
   INDEX tax_state_index(id_tax, id_state)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_alias` (
	alias varchar(255) NOT NULL,
	search varchar(255) NOT NULL,
	active tinyint(1) NOT NULL default 1,
	id_alias int(10) NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (id_alias),
	UNIQUE KEY alias (alias)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_discount_quantity` (
	id_discount_quantity INT UNSIGNED NOT NULL AUTO_INCREMENT,
	id_discount_type INT UNSIGNED NOT NULL,
	id_product INT UNSIGNED NOT NULL,
	id_product_attribute INT UNSIGNED NULL,
	quantity INT UNSIGNED NOT NULL,
	value DECIMAL(10,2) UNSIGNED NOT NULL,
	PRIMARY KEY (id_discount_quantity)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_attribute_impact (
  id_attribute_impact int(11) NOT NULL AUTO_INCREMENT,
  id_product int(11) NOT NULL,
  id_attribute int(11) NOT NULL,
  weight float NOT NULL,
  price decimal(10,2) NOT NULL,
  PRIMARY KEY  (id_attribute_impact),
  UNIQUE KEY id_product (id_product,id_attribute)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_state (
  id_state int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_country int(11) NOT NULL,
  id_zone int(11) NOT NULL,
  name varchar(64) NOT NULL,
  iso_code varchar(3) NOT NULL,
  tax_behavior SMALLINT(1) NOT NULL DEFAULT 0,
  active tinyint(1) NOT NULL default 0,
  PRIMARY KEY (id_state)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_return_state (
  id_order_return_state int(10) unsigned NOT NULL auto_increment,
  color varchar(32) default NULL,
  PRIMARY KEY  (`id_order_return_state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_order_return_state_lang (
  id_order_return_state int(10) unsigned NOT NULL,
  id_lang int(10) unsigned NOT NULL,
  name varchar(64) NOT NULL,
  UNIQUE KEY `order_state_lang_index` (`id_order_return_state`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_web_browser (
  id_web_browser INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NULL,
  PRIMARY KEY(id_web_browser)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_operating_system (
  id_operating_system INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NULL,
  PRIMARY KEY(id_operating_system)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_page_type (
  id_page_type INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NOT NULL,
  PRIMARY KEY(id_page_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_date_range (
  id_date_range INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  time_start DATETIME NOT NULL,
  time_end DATETIME NOT NULL,
  PRIMARY KEY(id_date_range)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_page (
  id_page INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  id_page_type INTEGER(10) UNSIGNED NOT NULL,
  id_object VARCHAR(256) NULL,
  PRIMARY KEY(id_page)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_page_viewed (
  id_page INTEGER(10) UNSIGNED NOT NULL,
  id_date_range INTEGER UNSIGNED NOT NULL,
  counter INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(id_page, id_date_range)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_guest (
  id_guest INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  id_operating_system INTEGER(10) UNSIGNED NULL DEFAULT NULL,
  id_web_browser INTEGER(10) UNSIGNED NULL DEFAULT NULL,
  id_customer INTEGER(10) UNSIGNED NULL DEFAULT NULL,
  javascript BOOL NULL DEFAULT 0,
  screen_resolution_x SMALLINT UNSIGNED NULL DEFAULT NULL,
  screen_resolution_y SMALLINT UNSIGNED NULL DEFAULT NULL,
  screen_color TINYINT UNSIGNED NULL DEFAULT NULL,
  sun_java BOOL NULL DEFAULT NULL,
  adobe_flash BOOL NULL DEFAULT NULL,
  adobe_director BOOL NULL DEFAULT NULL,
  apple_quicktime BOOL NULL DEFAULT NULL,
  real_player BOOL NULL DEFAULT NULL,
  windows_media BOOL NULL DEFAULT NULL,
  accept_language VARCHAR(8) NULL DEFAULT NULL,
  PRIMARY KEY(id_guest),
  INDEX id_customer (id_customer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_connections_page (
  id_connections INTEGER(10) UNSIGNED NOT NULL,
  id_page INTEGER(10) UNSIGNED NOT NULL,
  time_start DATETIME NOT NULL,
  time_end DATETIME NULL,
  PRIMARY KEY(id_connections, id_page, time_start)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_subdomain (
  id_subdomain INTEGER(10) NOT NULL AUTO_INCREMENT,
  name VARCHAR(16) NOT NULL,
  PRIMARY KEY(id_subdomain)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_message` (
  `id_order_message` INTEGER UNSIGNED NOT NULL auto_increment,
  `date_add` DATETIME NOT NULL,
  PRIMARY KEY  (`id_order_message`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_order_message_lang` (
  `id_order_message` INTEGER UNSIGNED NOT NULL,
  `id_lang` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `message` TEXT NOT NULL,
  PRIMARY KEY  (`id_order_message`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module_country` (
  `id_module` INTEGER UNSIGNED NOT NULL,
  `id_country` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id_module`, `id_country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_module_currency` (
  `id_module` INTEGER UNSIGNED NOT NULL,
  `id_currency` INTEGER NOT NULL,
  PRIMARY KEY (`id_module`, `id_currency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_meta (
  id_meta INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  page VARCHAR(64) NOT NULL,
  PRIMARY KEY(id_meta),
  KEY `meta_name` (`page`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_meta_lang (
  id_meta INTEGER UNSIGNED NOT NULL,
  id_lang INTEGER UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  description VARCHAR(255) NULL,
  keywords VARCHAR(255) NULL,
  PRIMARY KEY (id_meta, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_discount_category (
  id_discount INTEGER(11) NOT NULL,
  id_category INTEGER(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE  PREFIX_cms (
  id_cms INTEGER UNSIGNED NOT NULL auto_increment,
  PRIMARY KEY  (id_cms)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE  PREFIX_cms_lang (
  id_cms INTEGER UNSIGNED NOT NULL auto_increment,
  id_lang INTEGER UNSIGNED NOT NULL,
  meta_title VARCHAR(128) NOT NULL,
  meta_description VARCHAR(255) DEFAULT NULL,
  meta_keywords VARCHAR(255) DEFAULT NULL,
  content TEXT NULL,
  link_rewrite VARCHAR(128) NOT NULL,
  PRIMARY KEY  (id_cms, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_customization (
  id_customization int(10) NOT NULL AUTO_INCREMENT,
  id_product_attribute int(10) NOT NULL DEFAULT 0,
  id_cart int(10) NOT NULL,
  id_product int(10) NOT NULL,
  quantity int(10) NOT NULL,
  PRIMARY KEY(id_customization, id_cart, id_product)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_customized_data (
  id_customization int(10) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `index` int(3) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY(id_customization, `type`, `index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_customization_field (
  id_customization_field int(10) NOT NULL AUTO_INCREMENT,
  id_product int(10) NOT NULL,
  type tinyint(1) NOT NULL,
  required tinyint(1) NOT NULL,
  PRIMARY KEY(id_customization_field)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_customization_field_lang (
  id_customization_field int(10) NOT NULL,
  id_lang int(10) NOT NULL,
  name varchar(255) NOT NULL,
  PRIMARY KEY(id_customization_field, id_lang)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_block_cms (
	id_block INTEGER(10) NOT NULL,
	id_cms INTEGER(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_scene` (
  `id_scene` int(10) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id_scene`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_scene_category` (
  `id_scene` int(10) NOT NULL,
  `id_category` int(10) NOT NULL,
  PRIMARY KEY  (`id_scene`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_scene_lang` (
  `id_scene` int(10) NOT NULL,
  `id_lang` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_scene`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_scene_products` (
  `id_scene` int(10) NOT NULL,
  `id_product` int(10) NOT NULL,
  `x_axis` int(4) NOT NULL,
  `y_axis` int(4) NOT NULL,
  `zone_width` int(3) NOT NULL,
  `zone_height` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS PREFIX_order_customization_return (
  id_order_detail int(10) NOT NULL,
  customization_id int(10) NOT NULL,
  quantity int(10) NOT NULL,
  PRIMARY KEY(id_order_detail, customization_id)
);
