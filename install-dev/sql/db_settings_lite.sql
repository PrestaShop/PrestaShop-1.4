SET NAMES 'utf8';

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
(1, 'payment', 'Payment', NULL, 1),
(2, 'newOrder', 'New orders', NULL, 0),
(3, 'paymentConfirm', 'Payment confirmation', NULL, 0),
(4, 'paymentReturn', 'Payment return', NULL, 0),
(5, 'updateQuantity', 'Quantity update', 'Quantity is updated only when the customer effectively <b>place</b> his order.', 0),
(6, 'rightColumn', 'Right column blocks', NULL, 1),
(7, 'leftColumn', 'Left column blocks', NULL, 1),
(8, 'home', 'Homepage content', NULL, 1),
(9, 'header', 'Header of pages', 'A hook which allow you to do things in the header of each pages', 1),
(10, 'cart', 'Cart creation and update', NULL, 0),
(11, 'authentication', 'Successful customer authentication', NULL, 0),
(12, 'addproduct', 'Product creation', NULL, 0),
(13, 'updateproduct', 'Product Update', NULL, 0),
(14, 'top', 'Top of pages', 'A hook which allow you to do things a the top of each pages.', 1),
(15, 'extraRight', 'Extra actions on the product page (right column).', NULL, 0),
(16, 'deleteproduct', 'Product deletion', 'This hook is called when a product is deleted', 0),
(17, 'productfooter', 'Product footer', 'Add new blocks under the product description', 1),
(18, 'invoice', 'Invoice', 'Add blocks to invoice (order)', 1),
(19, 'updateOrderStatus', 'Order''s status update event', 'Launch modules when the order''s status of an order change.', 0),
(20, 'adminOrder', 'Display in Back-Office, tab AdminOrder', 'Launch modules when the tab AdminOrder is displayed on back-office.', 0),
(21, 'footer', 'Footer', 'Add block in footer', 1),
(22, 'PDFInvoice', 'PDF Invoice', 'Allow the display of extra informations into the PDF invoice', 0),
(23, 'adminCustomers', 'Display in Back-Office, tab AdminCustomers', 'Launch modules when the tab AdminCustomers is displayed on back-office.', 0),
(24, 'orderConfirmation', 'Order confirmation page', 'Called on order confirmation page', 0),
(25, 'createAccount', 'Successful customer create account', 'Called when new customer create account successfuled', 0),
(26, 'customerAccount', 'Customer account page display in front office', 'Display on page account of the customer', 1),
(27, 'orderSlip', 'Called when a order slip is created', 'Called when a quantity of one product change in an order.', 0),
(28, 'productTab', 'Tabs on product page', 'Called on order product page tabs', 0),
(29, 'productTabContent', 'Content of tabs on product page', 'Called on order product page tabs', 0),
(30, 'shoppingCart', 'Shopping cart footer', 'Display some specific informations on the shopping cart page', 0),
(31, 'createAccountForm', 'Customer account creation form', 'Display some information on the form to create a customer account', 1),
(32, 'AdminStatsModules','Stats - Modules', NULL, 1),
(33, 'GraphEngine','Graph Engines', NULL, 0),
(34, 'orderReturn','Product returned', NULL, 0),
(35, 'productActions', 'Product actions', 'Put new action buttons on product page', 1),
(36, 'backOfficeHome', 'Administration panel homepage', NULL, 1),
(37, 'GridEngine','Grid Engines', NULL, 0),
(38, 'watermark','Watermark', NULL, 0),
(39, 'cancelProduct', 'Product cancelled', 'This hook is called when you cancel a product in an order', 0),
(40, 'extraLeft', 'Extra actions on the product page (left column).', NULL, 0),
(41, 'productOutOfStock', 'Product out of stock', 'Make action while product is out of stock', 1),
(42, 'updateProductAttribute', 'Product attribute update', NULL, 0),
(43, 'extraCarrier', 'Extra carrier (module mode)', NULL, 0),
(44, 'shoppingCartExtra', 'Shopping cart extra button', 'Display some specific informations', 1),
(45, 'search', 'Search', NULL, 0),
(46, 'backBeforePayment', 'Redirect in order process', 'Redirect user to the module instead of displaying payment modules', 0),
(47, 'updateCarrier', 'Carrier Update', 'This hook is called when a carrier is updated', 0),
(48, 'postUpdateOrderStatus', 'Post update of order status', NULL, 0),
(49, 'createAccountTop', 'Block above the form for create an account', NULL, 1),
(50, 'backOfficeHeader', 'Administration panel header', NULL , 0),
(51, 'backOfficeTop', 'Administration panel hover the tabs', NULL , 1),
(52, 'backOfficeFooter', 'Administration panel footer', NULL , 1);

INSERT INTO `PREFIX_configuration` (`id_configuration`, `name`, `value`, `date_add`, `date_upd`) VALUES
(1, 'PS_LANG_DEFAULT', '1', NOW(), NOW()),
(2, 'PS_CURRENCY_DEFAULT', '1', NOW(), NOW()),
(3, 'PS_COUNTRY_DEFAULT', '8', NOW(), NOW()),
(4, 'PS_REWRITING_SETTINGS', '0', NOW(), NOW()),
(5, 'PS_ORDER_OUT_OF_STOCK', '0', NOW(), NOW()),
(6, 'PS_LAST_QTIES', '3', NOW(), NOW()),
(7, 'PS_CART_REDIRECT', '1', NOW(), NOW()),
(8, 'PS_HELPBOX', '1', NOW(), NOW()),
(9, 'PS_CONDITIONS', '1', NOW(), NOW()),
(10, 'PS_RECYCLABLE_PACK', '1', NOW(), NOW()),
(11, 'PS_GIFT_WRAPPING', '1', NOW(), NOW()),
(12, 'PS_GIFT_WRAPPING_PRICE', '0', NOW(), NOW()),
(13, 'PS_STOCK_MANAGEMENT', '1', NOW(), NOW()),
(14, 'PS_NAVIGATION_PIPE', '>', NOW(), NOW()),
(15, 'PS_PRODUCTS_PER_PAGE', '10', NOW(), NOW()),
(16, 'PS_PURCHASE_MINIMUM', '0', NOW(), NOW()),
(17, 'PS_PRODUCTS_ORDER_WAY', '1', NOW(), NOW()),
(18, 'PS_PRODUCTS_ORDER_BY', '4', NOW(), NOW()),
(19, 'PS_DISPLAY_QTIES', '1', NOW(), NOW()),
(20, 'PS_SHIPPING_HANDLING', '2', NOW(), NOW()),
(21, 'PS_SHIPPING_FREE_PRICE', '300', NOW(), NOW()),
(22, 'PS_SHIPPING_FREE_WEIGHT', '20', NOW(), NOW()),
(23, 'PS_SHIPPING_METHOD', '1', NOW(), NOW()),
(24, 'PS_TAX', '1', NOW(), NOW()),
(25, 'PS_SHOP_ENABLE', '1', NOW(), NOW()),
(26, 'PS_NB_DAYS_NEW_PRODUCT', '20', NOW(), NOW()),
(27, 'PS_SSL_ENABLED', '0', NOW(), NOW()),
(28, 'PS_WEIGHT_UNIT', 'kg', NOW(), NOW()),
(29, 'PS_BLOCK_CART_AJAX', '1', NOW(), NOW()),
(30, 'PS_ORDER_RETURN', '0', NOW(), NOW()),
(31, 'PS_ORDER_RETURN_NB_DAYS', '7', NOW(), NOW()),
(32, 'PS_MAIL_TYPE', '3', NOW(), NOW()),
(33, 'PS_PRODUCT_PICTURE_MAX_SIZE', '131072', NOW(), NOW()),
(34, 'PS_PRODUCT_PICTURE_WIDTH', '64', NOW(), NOW()),
(35, 'PS_PRODUCT_PICTURE_HEIGHT', '64', NOW(), NOW()),
(36, 'PS_INVOICE_PREFIX', 'IN', NOW(), NOW()),
(37, 'PS_INVOICE_NUMBER', '2', NOW(), NOW()),
(38, 'PS_DELIVERY_PREFIX', 'DE', NOW(), NOW()),
(39, 'PS_DELIVERY_NUMBER', '1', NOW(), NOW()),
(40, 'PS_INVOICE', '1', NOW(), NOW()),
(41, 'PS_PASSWD_TIME_BACK', '360', NOW(), NOW()),
(42, 'PS_PASSWD_TIME_FRONT', '360', NOW(), NOW()),
(43, 'PS_DISP_UNAVAILABLE_ATTR', '1', NOW(), NOW()),
(44, 'PS_VOUCHERS', '1', NOW(), NOW()),
(45, 'PS_SEARCH_MINWORDLEN', '3', NOW(), NOW()),
(46, 'PS_SEARCH_BLACKLIST', '', NOW(), NOW()),
(47, 'PS_SEARCH_WEIGHT_PNAME', '6', NOW(), NOW()),
(48, 'PS_SEARCH_WEIGHT_REF', '10', NOW(), NOW()),
(49, 'PS_SEARCH_WEIGHT_SHORTDESC', '1', NOW(), NOW()),
(50, 'PS_SEARCH_WEIGHT_DESC', '1', NOW(), NOW()),
(51, 'PS_SEARCH_WEIGHT_CNAME', '3', NOW(), NOW()),
(52, 'PS_SEARCH_WEIGHT_MNAME', '3', NOW(), NOW()),
(53, 'PS_SEARCH_WEIGHT_TAG', '4', NOW(), NOW()),
(54, 'PS_SEARCH_WEIGHT_ATTRIBUTE', '2', NOW(), NOW()),
(55, 'PS_SEARCH_WEIGHT_FEATURE', '2', NOW(), NOW()),
(56, 'PS_SEARCH_AJAX', '1', NOW(), NOW()),
(57, 'PS_TIMEZONE', '374', NOW(), NOW()),
(58, 'PS_THEME_V11', 0, NOW(), NOW()),
(59, 'PRESTASTORE_LIVE', 1, NOW(), NOW()),
(60, 'PS_TIN_ACTIVE', 0, NOW(), NOW()),
(61, 'PS_SHOW_ALL_MODULES', 0, NOW(), NOW()),
(62, 'PS_BACKUP_ALL', 0, NOW(), NOW());

INSERT INTO `PREFIX_configuration_lang` (`id_configuration`, `id_lang`, `value`, `date_upd`) VALUES (36, 1, 'IN', NOW()),(36, 2, 'FA', NOW()),(36, 3, 'CU', NOW()),
(38, 1, 'DE', NOW()),(38, 2, 'LI', NOW()),(38, 3, 'EN', NOW()),(46, 1, 'a|the|of|on|in|and|to', NOW()),(46, 2, 'le|les|de|et|en|des|les|une', NOW()),
(46, 3, 'de|los|las|lo|la|en|de|y|el|a', NOW());

INSERT INTO `PREFIX_lang` (`id_lang`, `name`, `active`, `iso_code`) VALUES
(1, 'English (English)', 1, 'en'),(2, 'Français (French)', 1, 'fr'),(3, 'Español (Spanish)', 1, 'es');

INSERT INTO `PREFIX_category` VALUES
(1, 0, 0, 1, NOW(), NOW());
INSERT INTO `PREFIX_category_lang` (`id_category`, `id_lang`, `name`, `description`, `link_rewrite`, `meta_title`, `meta_keywords`, `meta_description`) VALUES
(1, 1, 'Home', '', 'home', NULL, NULL, NULL),(1, 2, 'Accueil', '', 'home', NULL, NULL, NULL),(1, 3, 'Inicio', '', 'home', NULL, NULL, NULL);

INSERT INTO `PREFIX_order_state` (`id_order_state`, `invoice`, `send_email`, `color`, `unremovable`, `logable`, `delivery`) VALUES
(1, 0, 1, 'lightblue', 1, 0, 0),(2, 1, 1, '#DDEEFF', 1, 1, 0),(3, 1, 1, '#FFDD99', 1, 1, 1),(4, 1, 1, '#EEDDFF', 1, 1, 1),(5, 1, 0, '#DDFFAA', 1, 1, 1),
(6, 1, 1, '#DADADA', 1, 0, 0),(7, 1, 1, '#FFFFBB', 1, 0, 0),(8, 0, 1, '#FFDFDF', 1, 0, 0),(9, 1, 1, '#FFD3D3', 1, 0, 0),(10, 0, 1, 'lightblue', 1, 0, 0),(11, 0, 0, 'lightblue', 1, 0, 0);

INSERT INTO `PREFIX_order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`) VALUES
(1, 1, 'Awaiting cheque payment', 'cheque'),
(2, 1, 'Payment accepted', 'payment'),
(3, 1, 'Preparation in progress', 'preparation'),
(4, 1, 'Shipped', 'shipped'),
(5, 1, 'Delivered', ''),
(6, 1, 'Canceled', 'order_canceled'),
(7, 1, 'Refund', 'refund'),
(8, 1, 'Payment error', 'payment_error'),
(9, 1, 'Out of stock', 'outofstock'),
(10, 1, 'Awaiting bank wire payment', 'bankwire'),
(11, 1, 'Awaiting PayPal payment', ''),
(1, 2, 'En attente du paiement par chèque', 'cheque'),
(2, 2, 'Paiement accepté', 'payment'),
(3, 2, 'Préparation en cours', 'preparation'),
(4, 2, 'En cours de livraison', 'shipped'),
(5, 2, 'Livré', ''),
(6, 2, 'Annulé', 'order_canceled'),
(7, 2, 'Remboursé', 'refund'),
(8, 2, 'Erreur de paiement', 'payment_error'),
(9, 2, 'Produit(s) indisponibles', 'outofstock'),
(10, 2, 'En attente du paiement par virement bancaire', 'bankwire'),
(11, 2, 'En attente du paiement par PayPal', ''),
(1, 3, 'En espera de pago por cheque', 'account'),
(2, 3, 'Pago aceptamos', 'payment'),
(3, 3, 'Curso de preparación', 'preparation'),
(4, 3, 'Entrega active', 'shipped'),
(5, 3, 'Entregado', ''),
(6, 3, 'Cancelada', 'order_canceled'),
(7, 3, 'Reembolsado', 'refund'),
(8, 3, 'Pago estándar', 'payment_error'),
(9, 3, 'Productos fuera de línea', 'outofstock'),
(10, 3, 'En espera de pago por transferencia bancaria', 'bankwire'),
(11, 3, 'En espera de pago por PayPal', '');

INSERT INTO `PREFIX_zone` (`id_zone`, `name`, `active`, `enabled`) VALUES
(1, 'Europe', 1, 1),(2, 'US', 1, 1),(3, 'Asia', 1, 1),(4, 'Africa', 1, 1),(5, 'Oceania', 1, 1);


INSERT INTO `PREFIX_country` (`id_country`, `id_zone`, `iso_code`, `active`, `contains_states`) VALUES
(1, 1, 'DE', 1, 0),(2, 1, 'AT', 1, 0),(3, 1, 'BE', 1, 0),(4, 2, 'CA', 1, 0),(5, 3, 'CN', 1, 0),(6, 1, 'ES', 1, 0),(7, 1, 'FI', 1, 0),(8, 1, 'FR', 1, 0),(9, 1, 'GR', 1, 0),
(10, 1, 'IT', 1, 0),(11, 3, 'JP', 1, 0),(12, 1, 'LU', 1, 0),(13, 1, 'NL', 1, 0),(14, 1, 'PL', 1, 0),(15, 1, 'PT', 1, 0),(16, 1, 'CZ', 1, 0),(17, 1, 'GB', 1, 0),(18, 1, 'SE', 1, 0),
(19, 1, 'CH', 1, 0),(20, 1, 'DK', 1, 0),(21, 2, 'US', 1, 1),(22, 3, 'HK', 1, 0),(23, 1, 'NO', 1, 0),(24, 5, 'AU', 1, 0),(25, 3, 'SG', 1, 0),(26, 1, 'IE', 1, 0),(27, 5, 'NZ', 1, 0),
(28, 3, 'KR', 1, 0),(29, 3, 'IL', 1, 0),(30, 4, 'ZA', 1, 0),(31, 4, 'NG', 1, 0),(32, 4, 'CI', 1, 0),(33, 4, 'TG', 1, 0),(34, 2, 'BO', 1, 0),(35, 4, 'MU', 1, 0),(36, 1, 'RO', 1, 0),
(37, 1, 'SK', 1, 0),(38, 4, 'DZ', 1, 0),(39, 2, 'AS', 1, 0),(40, 1, 'AD', 1, 0),(41, 4, 'AO', 1, 0),(42, 2, 'AI', 1, 0),(43, 2, 'AG', 1, 0),(44, 2, 'AR', 1, 0),(45, 3, 'AM', 1, 0),
(46, 2, 'AW', 1, 0),(47, 3, 'AZ', 1, 0),(48, 2, 'BS', 1, 0),(49, 3, 'BH', 1, 0),(50, 3, 'BD', 1, 0),(51, 2, 'BB', 1, 0),(52, 1, 'BY', 1, 0),(53, 2, 'BZ', 1, 0),(54, 4, 'BJ', 1, 0),
(55, 2, 'BM', 1, 0),(56, 3, 'BT', 1, 0),(57, 4, 'BW', 1, 0),(58, 2, 'BR', 1, 0),(59, 3, 'BN', 1, 0),(60, 4, 'BF', 1, 0),(61, 3, 'MM', 1, 0),(62, 4, 'BI', 1, 0),(63, 3, 'KH', 1, 0),
(64, 4, 'CM', 1, 0),(65, 4, 'CV', 1, 0),(66, 4, 'CF', 1, 0),(67, 4, 'TD', 1, 0),(68, 2, 'CL', 1, 0),(69, 2, 'CO', 1, 0),(70, 4, 'KM', 1, 0),(71, 4, 'CD', 1, 0),(72, 4, 'CG', 1, 0),
(73, 2, 'CR', 1, 0),(74, 1, 'HR', 1, 0),(75, 2, 'CU', 1, 0),(76, 1, 'CY', 1, 0),(77, 4, 'DJ', 1, 0),(78, 2, 'DM', 1, 0),(79, 2, 'DO', 1, 0),(80, 3, 'TL', 1, 0),(81, 2, 'EC', 1, 0),
(82, 4, 'EG', 1, 0),(83, 2, 'SV', 1, 0),(84, 4, 'GQ', 1, 0),(85, 4, 'ER', 1, 0),(86, 1, 'EE', 1, 0),(87, 4, 'ET', 1, 0),(88, 2, 'FK', 1, 0),(89, 1, 'FO', 1, 0),(90, 5, 'FJ', 1, 0),
(91, 4, 'GA', 1, 0),(92, 4, 'GM', 1, 0),(93, 3, 'GE', 1, 0),(94, 4, 'GH', 1, 0),(95, 2, 'GD', 1, 0),(96, 1, 'GL', 1, 0),(97, 1, 'GI', 1, 0),(98, 2, 'GP', 1, 0),(99, 2, 'GU', 1, 0),
(100, 2, 'GT', 1, 0),(101, 1, 'GG', 1, 0),(102, 4, 'GN', 1, 0),(103, 4, 'GW', 1, 0),(104, 2, 'GY', 1, 0),(105, 2, 'HT', 1, 0),(106, 5, 'HM', 1, 0),(107, 1, 'VA', 1, 0),
(108, 2, 'HN', 1, 0),(109, 1, 'IS', 1, 0),(110, 3, 'IN', 1, 0),(111, 3, 'ID', 1, 0),(112, 3, 'IR', 1, 0),(113, 3, 'IQ', 1, 0),(114, 1, 'IM', 1, 0),(115, 2, 'JM', 1, 0),
(116, 1, 'JE', 1, 0),(117, 3, 'JO', 1, 0),(118, 3, 'KZ', 1, 0),(119, 4, 'KE', 1, 0),(120, 1, 'KI', 1, 0),(121, 3, 'KP', 1, 0),(122, 3, 'KW', 1, 0),(123, 3, 'KG', 1, 0),
(124, 3, 'LA', 1, 0),(125, 1, 'LV', 1, 0),(126, 3, 'LB', 1, 0),(127, 4, 'LS', 1, 0),(128, 4, 'LR', 1, 0),(129, 4, 'LY', 1, 0),(130, 1, 'LI', 1, 0),(131, 1, 'LT', 1, 0),
(132, 3, 'MO', 1, 0),(133, 1, 'MK', 1, 0),(134, 4, 'MG', 1, 0),(135, 4, 'MW', 1, 0),(136, 3, 'MY', 1, 0),(137, 3, 'MV', 1, 0),(138, 4, 'ML', 1, 0),(139, 1, 'MT', 1, 0),
(140, 5, 'MH', 1, 0),(141, 2, 'MQ', 1, 0),(142, 4, 'MR', 1, 0),(143, 1, 'HU', 1, 0),(144, 4, 'YT', 1, 0),(145, 2, 'MX', 1, 0),(146, 5, 'FM', 1, 0),(147, 1, 'MD', 1, 0),
(148, 1, 'MC', 1, 0),(149, 3, 'MN', 1, 0),(150, 1, 'ME', 1, 0),(151, 2, 'MS', 1, 0),(152, 4, 'MA', 1, 0),(153, 4, 'MZ', 1, 0),(154, 4, 'NA', 1, 0),(155, 5, 'NR', 1, 0),
(156, 3, 'NP', 1, 0),(157, 2, 'AN', 1, 0),(158, 5, 'NC', 1, 0),(159, 2, 'NI', 1, 0),(160, 4, 'NE', 1, 0),(161, 5, 'NU', 1, 0),(162, 5, 'NF', 1, 0),(163, 5, 'MP', 1, 0),
(164, 3, 'OM', 1, 0),(165, 3, 'PK', 1, 0),(166, 5, 'PW', 1, 0),(167, 3, 'PS', 1, 0),(168, 2, 'PA', 1, 0),(169, 5, 'PG', 1, 0),(170, 2, 'PY', 1, 0),(171, 2, 'PE', 1, 0),
(172, 3, 'PH', 1, 0),(173, 5, 'PN', 1, 0),(174, 2, 'PR', 1, 0),(175, 3, 'QA', 1, 0),(176, 4, 'RE', 1, 0),(177, 1, 'RU', 1, 0),(178, 4, 'RW', 1, 0),(179, 2, 'BL', 1, 0),
(180, 2, 'KN', 1, 0),(181, 2, 'LC', 1, 0),(182, 2, 'MF', 1, 0),(183, 2, 'PM', 1, 0),(184, 2, 'VC', 1, 0),(185, 5, 'WS', 1, 0),(186, 1, 'SM', 1, 0),(187, 4, 'ST', 1, 0),
(188, 3, 'SA', 1, 0),(189, 4, 'SN', 1, 0),(190, 1, 'RS', 1, 0),(191, 4, 'SC', 1, 0),(192, 4, 'SL', 1, 0),(193, 1, 'SI', 1, 0),(194, 5, 'SB', 1, 0),(195, 4, 'SO', 1, 0),
(196, 2, 'GS', 1, 0),(197, 3, 'LK', 1, 0),(198, 4, 'SD', 1, 0),(199, 2, 'SR', 1, 0),(200, 1, 'SJ', 1, 0),(201, 4, 'SZ', 1, 0),(202, 3, 'SY', 1, 0),(203, 3, 'TW', 1, 0),
(204, 3, 'TJ', 1, 0),(205, 4, 'TZ', 1, 0),(206, 3, 'TH', 1, 0),(207, 5, 'TK', 1, 0),(208, 5, 'TO', 1, 0),(209, 2, 'TT', 1, 0),(210, 4, 'TN', 1, 0),(211, 1, 'TR', 1, 0),
(212, 3, 'TM', 1, 0),(213, 2, 'TC', 1, 0),(214, 5, 'TV', 1, 0),(215, 4, 'UG', 1, 0),(216, 1, 'UA', 1, 0),(217, 3, 'AE', 1, 0),(218, 2, 'UY', 1, 0),(219, 3, 'UZ', 1, 0),
(220, 5, 'VU', 1, 0),(221, 2, 'VE', 1, 0),(222, 3, 'VN', 1, 0),(223, 2, 'VG', 1, 0),(224, 2, 'VI', 1, 0),(225, 5, 'WF', 1, 0),(226, 4, 'EH', 1, 0),(227, 3, 'YE', 1, 0),
(228, 4, 'ZM', 1, 0),(229, 4, 'ZW', 1, 0),(230, 1, 'AL', 1, 0),(231, 3, 'AF', 1, 0),(232, 5, 'AQ', 1, 0),(233, 1, 'BA', 1, 0),(234, 5, 'BV', 1, 0),(235, 5, 'IO', 1, 0),
(236, 1, 'BG', 1, 0),(237, 2, 'KY', 1, 0),(238, 3, 'CX', 1, 0),(239, 3, 'CC', 1, 0),(240, 5, 'CK', 1, 0),(241, 2, 'GF', 1, 0),(242, 5, 'PF', 1, 0),(243, 5, 'TF', 1, 0),(244, 1, 'AX', 1, 0);

INSERT INTO `PREFIX_country_lang` (`id_country`, `id_lang`, `name`) VALUES
(1, 1, 'Germany'),(1, 2, 'Allemagne'),(2, 1, 'Austria'),(2, 2, 'Autriche'),(3, 1, 'Belgium'),(3, 2, 'Belgique'),(4, 1, 'Canada'),(4, 2, 'Canada'),(5, 1, 'China'),
(5, 2, 'Chine'),(6, 1, 'Spain'),(6, 2, 'Espagne'),(7, 1, 'Finland'),(7, 2, 'Finlande'),(8, 1, 'France'),(8, 2, 'France'),(9, 1, 'Greece'),(9, 2, 'Grèce'),
(10, 1, 'Italy'),(10, 2, 'Italie'),(11, 1, 'Japan'),(11, 2, 'Japon'),(12, 1, 'Luxemburg'),(12, 2, 'Luxembourg'),(13, 1, 'Netherlands'),(13, 2, 'Pays-bas'),
(14, 1, 'Poland'),(14, 2, 'Pologne'),(15, 1, 'Portugal'),(15, 2, 'Portugal'),(16, 1, 'Czech Republic'),(16, 2, 'République Tchèque'),(17, 1, 'United Kingdom'),
(17, 2, 'Royaume-Uni'),(18, 1, 'Sweden'),(18, 2, 'Suède'),(19, 1, 'Switzerland'),(19, 2, 'Suisse'),(20, 1, 'Denmark'),(20, 2, 'Danemark'),(21, 1, 'USA'),
(21, 2, 'USA'),(22, 1, 'HongKong'),(22, 2, 'Hong-Kong'),(23, 1, 'Norway'),(23, 2, 'Norvège'),(24, 1, 'Australia'),(24, 2, 'Australie'),(25, 1, 'Singapore'),
(25, 2, 'Singapour'),(26, 1, 'Ireland'),(26, 2, 'Eire'),(27, 1, 'New Zealand'),(27, 2, 'Nouvelle-Zélande'),(28, 1, 'South Korea'),(28, 2, 'Corée du Sud'),
(29, 1, 'Israel'),(29, 2, 'Israël'),(30, 1, 'South Africa'),(30, 2, 'Afrique du Sud'),(31, 1, 'Nigeria'),(31, 2, 'Nigeria'),(32, 1, 'Ivory Coast'),
(32, 2, 'Côte d''Ivoire'),(33, 1, 'Togo'),(33, 2, 'Togo'),(34, 1, 'Bolivia'),(34, 2, 'Bolivie'),(35, 1, 'Mauritius'),(35, 2, 'Ile Maurice'),(143, 1, 'Hungary'),
(143, 2, 'Hongrie'),(36, 1, 'Romania'),(36, 2, 'Roumanie'),(37, 1, 'Slovakia'),(37, 2, 'Slovaquie'),(38, 1, 'Algeria'),(38, 2, 'Algérie'),
(39, 1, 'American Samoa'),(39, 2, 'Samoa Américaines'),(40, 1, 'Andorra'),(40, 2, 'Andorre'),(41, 1, 'Angola'),(41, 2, 'Angola'),(42, 1, 'Anguilla'),
(42, 2, 'Anguilla'),(43, 1, 'Antigua and Barbuda'),(43, 2, 'Antigua et Barbuda'),(44, 1, 'Argentina'),(44, 2, 'Argentine'),(45, 1, 'Armenia'),(45, 2, 'Arménie'),
(46, 1, 'Aruba'),(46, 2, 'Aruba'),(47, 1, 'Azerbaijan'),(47, 2, 'Azerbaïdjan'),(48, 1, 'Bahamas'),(48, 2, 'Bahamas'),(49, 1, 'Bahrain'),(49, 2, 'Bahreïn'),
(50, 1, 'Bangladesh'),(50, 2, 'Bangladesh'),(51, 1, 'Barbados'),(51, 2, 'Barbade'),(52, 1, 'Belarus'),(52, 2, 'Bélarus'),(53, 1, 'Belize'),(53, 2, 'Belize'),
(54, 1, 'Benin'),(54, 2, 'Bénin'),(55, 1, 'Bermuda'),(55, 2, 'Bermudes'),(56, 1, 'Bhutan'),(56, 2, 'Bhoutan'),(57, 1, 'Botswana'),(57, 2, 'Botswana'),
(58, 1, 'Brazil'),(58, 2, 'Brésil'),(59, 1, 'Brunei'),(59, 2, 'Brunéi Darussalam'),(60, 1, 'Burkina Faso'),(60, 2, 'Burkina Faso'),(61, 1, 'Burma (Myanmar)'),
(61, 2, 'Burma (Myanmar)'),(62, 1, 'Burundi'),(62, 2, 'Burundi'),(63, 1, 'Cambodia'),(63, 2, 'Cambodge'),(64, 1, 'Cameroon'),(64, 2, 'Cameroun'),
(65, 1, 'Cape Verde'),(65, 2, 'Cap-Vert'),(66, 1, 'Central African Republic'),(66, 2, 'Centrafricaine, République'),(67, 1, 'Chad'),(67, 2, 'Tchad'),(68, 1, 'Chile'),
(68, 2, 'Chili'),(69, 1, 'Colombia'),(69, 2, 'Colombie'),(70, 1, 'Comoros'),(70, 2, 'Comores'),(71, 1, 'Congo, Dem. Republic'),(71, 2, 'Congo, Rép. Dém.'),
(72, 1, 'Congo, Republic'),(72, 2, 'Congo, Rép.'),(73, 1, 'Costa Rica'),(73, 2, 'Costa Rica'),(74, 1, 'Croatia'),(74, 2, 'Croatie'),(75, 1, 'Cuba'),
(75, 2, 'Cuba'),(76, 1, 'Cyprus'),(76, 2, 'Chypre'),(77, 1, 'Djibouti'),(77, 2, 'Djibouti'),(78, 1, 'Dominica'),(78, 2, 'Dominica'),(79, 1, 'Dominican Republic'),
(79, 2, 'République Dominicaine'),(80, 1, 'East Timor'),(80, 2, 'Timor oriental'),(81, 1, 'Ecuador'),(81, 2, 'Équateur'),(82, 1, 'Egypt'),(82, 2, 'Égypte'),
(83, 1, 'El Salvador'),(83, 2, 'El Salvador'),(84, 1, 'Equatorial Guinea'),(84, 2, 'Guinée Équatoriale'),(85, 1, 'Eritrea'),(85, 2, 'Érythrée'),(86, 1, 'Estonia'),
(86, 2, 'Estonie'),(87, 1, 'Ethiopia'),(87, 2, 'Éthiopie'),(88, 1, 'Falkland Islands'),(88, 2, 'Falkland, Îles'),(89, 1, 'Faroe Islands'),(89, 2, 'Féroé, Îles'),
(90, 1, 'Fiji'),(90, 2, 'Fidji'),(91, 1, 'Gabon'),(91, 2, 'Gabon'),(92, 1, 'Gambia'),(92, 2, 'Gambie'),(93, 1, 'Georgia'),(93, 2, 'Géorgie'),(94, 1, 'Ghana'),
(94, 2, 'Ghana'),(95, 1, 'Grenada'),(95, 2, 'Grenade'),(96, 1, 'Greenland'),(96, 2, 'Groenland'),(97, 1, 'Gibraltar'),(97, 2, 'Gibraltar'),(98, 1, 'Guadeloupe'),
(98, 2, 'Guadeloupe'),(99, 1, 'Guam'),(99, 2, 'Guam'),(100, 1, 'Guatemala'),(100, 2, 'Guatemala'),(101, 1, 'Guernsey'),(101, 2, 'Guernesey'),(102, 1, 'Guinea'),
(102, 2, 'Guinée'),(103, 1, 'Guinea-Bissau'),(103, 2, 'Guinée-Bissau'),(104, 1, 'Guyana'),(104, 2, 'Guyana'),(105, 1, 'Haiti'),(105, 2, 'Haîti'),
(106, 1, 'Heard Island and McDonald Islands'),(106, 2, 'Heard, Île et Mcdonald, Îles'),(107, 1, 'Vatican City State'),(107, 2, 'Saint-Siege (État de la Cité du Vatican)'),
(108, 1, 'Honduras'),(108, 2, 'Honduras'),(109, 1, 'Iceland'),(109, 2, 'Islande'),(110, 1, 'India'),(110, 2, 'Indie'),(111, 1, 'Indonesia'),(111, 2, 'Indonésie'),
(112, 1, 'Iran'),(112, 2, 'Iran'),(113, 1, 'Iraq'),(113, 2, 'Iraq'),(114, 1, 'Isle of Man'),(114, 2, 'Île de Man'),(115, 1, 'Jamaica'),(115, 2, 'Jamaique'),
(116, 1, 'Jersey'),(116, 2, 'Jersey'),(117, 1, 'Jordan'),(117, 2, 'Jordanie'),(118, 1, 'Kazakhstan'),(118, 2, 'Kazakhstan'),(119, 1, 'Kenya'),(119, 2, 'Kenya'),
(120, 1, 'Kiribati'),(120, 2, 'Kiribati'),(121, 1, 'Korea, Dem. Republic of'),(121, 2, 'Corée, Rép. Populaire Dém. de'),(122, 1, 'Kuwait'),(122, 2, 'Koweït'),
(123, 1, 'Kyrgyzstan'),(123, 2, 'Kirghizistan'),(124, 1, 'Laos'),(124, 2, 'Laos'),(125, 1, 'Latvia'),(125, 2, 'Lettonie'),(126, 1, 'Lebanon'),(126, 2, 'Liban'),
(127, 1, 'Lesotho'),(127, 2, 'Lesotho'),(128, 1, 'Liberia'),(128, 2, 'Libéria'),(129, 1, 'Libya'),(129, 2, 'Libyenne, Jamahiriya Arabe'),(130, 1, 'Liechtenstein'),
(130, 2, 'Liechtenstein'),(131, 1, 'Lithuania'),(131, 2, 'Lituanie'),(132, 1, 'Macau'),(132, 2, 'Macao'),(133, 1, 'Macedonia'),(133, 2, 'Macédoine'),
(134, 1, 'Madagascar'),(134, 2, 'Madagascar'),(135, 1, 'Malawi'),(135, 2, 'Malawi'),(136, 1, 'Malaysia'),(136, 2, 'Malaisie'),(137, 1, 'Maldives'),(137, 2, 'Maldives'),
(138, 1, 'Mali'),(138, 2, 'Mali'),(139, 1, 'Malta'),(139, 2, 'Malte'),(140, 1, 'Marshall Islands'),(140, 2, 'Marshall, Îles'),(141, 1, 'Martinique'),(141, 2, 'Martinique'),
(142, 1, 'Mauritania'),(142, 2, 'Mauritanie'),(144, 1, 'Mayotte'),(144, 2, 'Mayotte'),(145, 1, 'Mexico'),(145, 2, 'Mexique'),(146, 1, 'Micronesia'),(146, 2, 'Micronésie'),
(147, 1, 'Moldova'),(147, 2, 'Moldova'),(148, 1, 'Monaco'),(148, 2, 'Monaco'),(149, 1, 'Mongolia'),(149, 2, 'Mongolie'),(150, 1, 'Montenegro'),(150, 2, 'Monténégro'),
(151, 1, 'Montserrat'),(151, 2, 'Montserrat'),(152, 1, 'Morocco'),(152, 2, 'Maroc'),(153, 1, 'Mozambique'),(153, 2, 'Mozambique'),(154, 1, 'Namibia'),(154, 2, 'Namibie'),
(155, 1, 'Nauru'),(155, 2, 'Nauru'),(156, 1, 'Nepal'),(156, 2, 'Népal'),(157, 1, 'Netherlands Antilles'),(157, 2, 'Antilles Néerlandaises'),(158, 1, 'New Caledonia'),
(158, 2, 'Nouvelle-Calédonie'),(159, 1, 'Nicaragua'),(159, 2, 'Nicaragua'),(160, 1, 'Niger'),(160, 2, 'Niger'),(161, 1, 'Niue'),(161, 2, 'Niué'),(162, 1, 'Norfolk Island'),
(162, 2, 'Norfolk, Île'),(163, 1, 'Northern Mariana Islands'),(163, 2, 'Mariannes du Nord, Îles'),(164, 1, 'Oman'),(164, 2, 'Oman'),(165, 1, 'Pakistan'),(165, 2, 'Pakistan'),
(166, 1, 'Palau'),(166, 2, 'Palaos'),(167, 1, 'Palestinian Territories'),(167, 2, 'Palestinien Occupé, Territoire'),(168, 1, 'Panama'),(168, 2, 'Panama'),
(169, 1, 'Papua New Guinea'),(169, 2, 'Papouasie-Nouvelle-Guinée'),(170, 1, 'Paraguay'),(170, 2, 'Paraguay'),(171, 1, 'Peru'),(171, 2, 'Pérou'),(172, 1, 'Philippines'),
(172, 2, 'Philippines'),(173, 1, 'Pitcairn'),(173, 2, 'Pitcairn'),(174, 1, 'Puerto Rico'),(174, 2, 'Porto Rico'),(175, 1, 'Qatar'),(175, 2, 'Qatar'),(176, 1, 'Réunion'),
(176, 2, 'Réunion'),(177, 1, 'Russian Federation'),(177, 2, 'Russie, Fédération de'),(178, 1, 'Rwanda'),(178, 2, 'Rwanda'),(179, 1, 'Saint Barthélemy'),
(179, 2, 'Saint-Barthélemy'),(180, 1, 'Saint Kitts and Nevis'),(180, 2, 'Saint-Kitts-et-Nevis'),(181, 1, 'Saint Lucia'),(181, 2, 'Sainte-Lucie'),(182, 1, 'Saint Martin'),
(182, 2, 'Saint-Martin'),(183, 1, 'Saint Pierre and Miquelon'),(183, 2, 'Saint-Pierre-et-Miquelon'),(184, 1, 'Saint Vincent and the Grenadines'),
(184, 2, 'Saint-Vincent-et-Les Grenadines'),(185, 1, 'Samoa'),(185, 2, 'Samoa'),(186, 1, 'San Marino'),(186, 2, 'Saint-Marin'),(187, 1, 'São Tomé and Príncipe'),
(187, 2, 'Sao Tomé-et-Principe'),(188, 1, 'Saudi Arabia'),(188, 2, 'Arabie Saoudite'),(189, 1, 'Senegal'),(189, 2, 'Sénégal'),(190, 1, 'Serbia'),(190, 2, 'Serbie'),
(191, 1, 'Seychelles'),(191, 2, 'Seychelles'),(192, 1, 'Sierra Leone'),(192, 2, 'Sierra Leone'),(193, 1, 'Slovenia'),(193, 2, 'Slovénie'),(194, 1, 'Solomon Islands'),
(194, 2, 'Salomon, Îles'),(195, 1, 'Somalia'),(195, 2, 'Somalie'),(196, 1, 'South Georgia and the South Sandwich Islands'),(196, 2, 'Géorgie du Sud et les Îles Sandwich du Sud'),
(197, 1, 'Sri Lanka'),(197, 2, 'Sri Lanka'),(198, 1, 'Sudan'),(198, 2, 'Soudan'),(199, 1, 'Suriname'),(199, 2, 'Suriname'),(200, 1, 'Svalbard and Jan Mayen'),
(200, 2, 'Svalbard et Île Jan Mayen'),(201, 1, 'Swaziland'),(201, 2, 'Swaziland'),(202, 1, 'Syria'),(202, 2, 'Syrienne'),(203, 1, 'Taiwan'),(203, 2, 'Taïwan'),
(204, 1, 'Tajikistan'),(204, 2, 'Tadjikistan'),(205, 1, 'Tanzania'),(205, 2, 'Tanzanie'),(206, 1, 'Thailand'),(206, 2, 'Thaïlande'),(207, 1, 'Tokelau'),(207, 2, 'Tokelau'),
(208, 1, 'Tonga'),(208, 2, 'Tonga'),(209, 1, 'Trinidad and Tobago'),(209, 2, 'Trinité-et-Tobago'),(210, 1, 'Tunisia'),(210, 2, 'Tunisie'),(211, 1, 'Turkey'),
(211, 2, 'Turquie'),(212, 1, 'Turkmenistan'),(212, 2, 'Turkménistan'),(213, 1, 'Turks and Caicos Islands'),(213, 2, 'Turks et Caiques, Îles'),(214, 1, 'Tuvalu'),(214, 2, 'Tuvalu'),
(215, 1, 'Uganda'),(215, 2, 'Ouganda'),(216, 1, 'Ukraine'),(216, 2, 'Ukraine'),(217, 1, 'United Arab Emirates'),(217, 2, 'Émirats Arabes Unis'),(218, 1, 'Uruguay'),
(218, 2, 'Uruguay'),(219, 1, 'Uzbekistan'),(219, 2, 'Ouzbékistan'),(220, 1, 'Vanuatu'),(220, 2, 'Vanuatu'),(221, 1, 'Venezuela'),(221, 2, 'Venezuela'),(222, 1, 'Vietnam'),
(222, 2, 'Vietnam'),(223, 1, 'Virgin Islands (British)'),(223, 2, 'Îles Vierges Britanniques'),(224, 1, 'Virgin Islands (U.S.)'),(224, 2, 'Îles Vierges des États-Unis'),
(225, 1, 'Wallis and Futuna'),(225, 2, 'Wallis et Futuna'),(226, 1, 'Western Sahara'),(226, 2, 'Sahara Occidental'),(227, 1, 'Yemen'),(227, 2, 'Yémen'),(228, 1, 'Zambia'),
(228, 2, 'Zambie'),(229, 1, 'Zimbabwe'),(229, 2, 'Zimbabwe'),(230, 1, 'Albania'),(230, 2, 'Albanie'),(231, 1, 'Afghanistan'),(231, 2, 'Afghanistan'),(232, 1, 'Antarctica'),
(232, 2, 'Antarctique'),(233, 1, 'Bosnia and Herzegovina'),(233, 2, 'Bosnie-Herzégovine'),(234, 1, 'Bouvet Island'),(234, 2, 'Bouvet, Île'),(235, 1, 'British Indian Ocean Territory'),
(235, 2, 'Océan Indien, Territoire Britannique de L'''),(236, 1, 'Bulgaria'),(236, 2, 'Bulgarie'),(237, 1, 'Cayman Islands'),(237, 2, 'Caïmans, Îles'),(238, 1, 'Christmas Island'),
(238, 2, 'Christmas, Île'),(239, 1, 'Cocos (Keeling) Islands'),(239, 2, 'Cocos (Keeling), Îles'),(240, 1, 'Cook Islands'),(240, 2, 'Cook, Îles'),(241, 1, 'French Guiana'),
(241, 2, 'Guyane Française'),(242, 1, 'French Polynesia'),(242, 2, 'Polynésie Française'),(243, 1, 'French Southern Territories'),(243, 2, 'Terres Australes Françaises'),
(244, 1, 'Åland Islands'),(244, 2, 'Åland, Îles'),(1, 3, 'Alemania'),(2, 3, 'Austria'),(3, 3, 'Bélgica'),(4, 3, 'Canadá'),(5, 3, 'Porcelana'),(6, 3, 'España'),(7, 3, 'Finlandia'),
(8, 3, 'Francia'),(9, 3, 'Grecia'),(10, 3, 'Italia'),(11, 3, 'Japón'),(12, 3, 'Luxemburgo'),(13, 3, 'Países Bajos'),(14, 3, 'Polonia'),(15, 3, 'Portugal'),
(16, 3, 'República Checa'),(17, 3, 'Reino Unido'),(18, 3, 'Suecia'),(19, 3, 'Suiza'),(20, 3, 'Dinamarca'),(21, 3, 'EE.UU.'),(22, 3, 'Hong Kong'),(23, 3, 'Noruega'),(24, 3, 'Australia'),
(25, 3, 'Singapur'),(26, 3, 'Irlanda'),(27, 3, 'Nueva Zelanda'),(28, 3, 'Corea del Sur'),(29, 3, 'Israel'),(30, 3, 'Sudáfrica'),(31, 3, 'Nigeria'),(32, 3, 'Costa de Marfil'),
(33, 3, 'Togo'),(34, 3, 'Bolivia'),(35, 3, 'Mauricio'),(143, 3, 'Hungría'),(36, 3, 'Rumania'),(37, 3, 'Eslovaquia'),(38, 3, 'Argelia'),(39, 3, 'Samoa Americana'),(40, 3, 'Andorra'),
(41, 3, 'Angola'),(42, 3, 'Anguila'),(43, 3, 'Antigua y Barbuda'),(44, 3, 'Argentina'),(45, 3, 'Armenia'),(46, 3, 'Aruba'),(47, 3, 'Azerbaiyán'),(48, 3, 'Bahamas'),(49, 3, 'Bahrein'),
(50, 3, 'Bangladesh'),(51, 3, 'Barbados'),(52, 3, 'Belarús'),(53, 3, 'Belice'),(54, 3, 'Benin'),(55, 3, 'Bermudas'),(56, 3, 'Bhután'),(57, 3, 'Botswana'),(58, 3, 'Brasil'),
(59, 3, 'Brunei'),(60, 3, 'Burkina Faso'),(61, 3, 'Birmania (Myanmar)'),(62, 3, 'Burundi'),(63, 3, 'Camboya'),(64, 3, 'Camerún'),(65, 3, 'Cabo Verde'),(66, 3, 'República Centroafricana'),
(67, 3, 'Chad'),(68, 3, 'Chile'),(69, 3, 'Colombia'),(70, 3, 'Comoras'),(71, 3, 'Congo, Rep. Dem.. República'),(72, 3, 'Congo, República'),(73, 3, 'Costa Rica'),(74, 3, 'Croacia'),
(75, 3, 'Cuba'),(76, 3, 'Chipre'),(77, 3, 'Djibouti'),(78, 3, 'Dominica'),(79, 3, 'República Dominicana'),(80, 3, 'Timor Oriental'),(81, 3, 'Ecuador'),(82, 3, 'Egipto'),
(83, 3, 'El Salvador'),(84, 3, 'Guinea Ecuatorial'),(85, 3, 'Eritrea'),(86, 3, 'Estonia'),(87, 3, 'Etiopía'),(88, 3, 'Islas Malvinas'),(89, 3, 'Islas Feroe'),(90, 3, 'Fiji'),
(91, 3, 'Gabón'),(92, 3, 'Gambia'),(93, 3, 'Georgia'),(94, 3, 'Ghana'),(95, 3, 'Granada'),(96, 3, 'Groenlandia'),(97, 3, 'Gibraltar'),(98, 3, 'Guadalupe'),(99, 3, 'Guam'),
(100, 3, 'Guatemala'),(101, 3, 'Guernsey'),(102, 3, 'Guinea'),(103, 3, 'Guinea-Bissau'),(104, 3, 'Guyana'),(105, 3, 'Haití'),(106, 3, 'Islas Heard y McDonald Islas'),
(107, 3, 'Ciudad del Vaticano'),(108, 3, 'Honduras'),(109, 3, 'Islandia'),(110, 3, 'India'),(111, 3, 'Indonesia'),(112, 3, 'Irán'),(113, 3, 'Iraq'),(114, 3, 'Isla de Man'),
(115, 3, 'Jamaica'),(116, 3, 'Jersey'),(117, 3, 'Jordania'),(118, 3, 'Kazajstán'),(119, 3, 'Kenya'),(120, 3, 'Kiribati'),(121, 3, 'KOREA, DEM. República de'),(122, 3, 'Kuwait'),
(123, 3, 'Kirguistán'),(124, 3, 'Laos'),(125, 3, 'Letonia'),(126, 3, 'Líbano'),(127, 3, 'Lesotho'),(128, 3, 'Liberia'),(129, 3, 'Libia'),(130, 3, 'Liechtenstein'),(131, 3, 'Lituania'),
(132, 3, 'Macao'),(133, 3, 'Macedonia'),(134, 3, 'Madagascar'),(135, 3, 'Malawi'),(136, 3, 'Malasia'),(137, 3, 'Maldivas'),(138, 3, 'Malí'),(139, 3, 'Malta'),(140, 3, 'Islas Marshall'),
(141, 3, 'Martinica'),(142, 3, 'Mauritania'),(144, 3, 'Mayotte'),(145, 3, 'México'),(146, 3, 'Micronesia'),(147, 3, 'Moldavia'),(148, 3, 'Mónaco'),(149, 3, 'Mongolia'),
(150, 3, 'Montenegro'),(151, 3, 'Montserrat'),(152, 3, 'Marruecos'),(153, 3, 'Mozambique'),(154, 3, 'Namibia'),(155, 3, 'Nauru'),(156, 3, 'Nepal'),(157, 3, 'Antillas Neerlandesas'),
(158, 3, 'Nueva Caledonia'),(159, 3, 'Nicaragua'),(160, 3, 'Níger'),(161, 3, 'Niue'),(162, 3, 'Norfolk Island'),(163, 3, 'Islas Marianas del Norte'),(164, 3, 'Omán'),(165, 3, 'Pakistán'),
(166, 3, 'Palau'),(167, 3, 'Territorios Palestinos'),(168, 3, 'Panamá'),(169, 3, 'Papua Nueva Guinea'),(170, 3, 'Paraguay'),(171, 3, 'Perú'),(172, 3, 'Filipinas'),(173, 3, 'Pitcairn'),
(174, 3, 'Puerto Rico'),(175, 3, 'Qatar'),(176, 3, 'Reunión'),(177, 3, 'Federación de Rusia'),(178, 3, 'Rwanda'),(179, 3, 'San Bartolomé'),(180, 3, 'Saint Kitts y Nevis'),
(181, 3, 'Santa Lucía'),(182, 3, 'Saint Martin'),(183, 3, 'San Pedro y Miquelón'),(184, 3, 'San Vicente y las Granadinas'),(185, 3, 'Samoa'),(186, 3, 'San Marino'),
(187, 3, 'Santo Tomé y Príncipe'),(188, 3, 'Arabia Saudita'),(189, 3, 'Senegal'),(190, 3, 'Serbia'),(191, 3, 'Seychelles'),(192, 3, 'Sierra Leona'),(193, 3, 'Eslovenia'),(194, 3, 'Islas Salomón'),
(195, 3, 'Somalia'),(196, 3, 'Georgia del Sur e Islas Sandwich del Sur'),(197, 3, 'Sri Lanka'),(198, 3, 'Sudán'),(199, 3, 'Suriname'),(200, 3, 'Svalbard y Jan Mayen'),(201, 3, 'Swazilandia'),
(202, 3, 'Siria'),(203, 3, 'Taiwán'),(204, 3, 'Tayikistán'),(205, 3, 'Tanzania'),(206, 3, 'Tailandia'),(207, 3, 'Tokelau'),(208, 3, 'Tonga'),(209, 3, 'Trinidad y Tobago'),(210, 3, 'Túnez'),
(211, 3, 'Turquía'),(212, 3, 'Turkmenistán'),(213, 3, 'Islas Turcas y Caicos'),(214, 3, 'Tuvalu'),(215, 3, 'Uganda'),(216, 3, 'Ucrania'),(217, 3, 'Emiratos Árabes Unidos'),(218, 3, 'Uruguay'),
(219, 3, 'Uzbekistán'),(220, 3, 'Vanuatu'),(221, 3, 'Venezuela'),(222, 3, 'Vietnam'),(223, 3, 'Islas Vírgenes (Británicas)'),(224, 3, 'Islas Vírgenes (EE.UU.)'),(225, 3, 'Wallis y Futuna'),
(226, 3, 'Sáhara Occidental'),(227, 3, 'Yemen'),(228, 3, 'Zambia'),(229, 3, 'Zimbabwe'),(230, 3, 'Albania'),(231, 3, 'Afganistán'),(232, 3, 'Antártida'),(233, 3, 'Bosnia y Herzegovina'),
(234, 3, 'Isla Bouvet'),(235, 3, 'British Indian Ocean Territory'),(236, 3, 'Bulgaria'),(237, 3, 'Islas Caimán'),(238, 3, 'Isla de Navidad'),(239, 3, 'Islas Cocos (Keeling) Islands'),
(240, 3, 'Islas Cook'),(241, 3, 'Francés Guayana'),(242, 3, 'Polinesia francés'),(243, 3, 'Territorios del sur francés'),(244, 3, 'Islas Åland');

INSERT IGNORE INTO `PREFIX_country_lang` (`id_country`, `id_lang`, `name`)
    (SELECT `id_country`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_country_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_country`=`PREFIX_country`.`id_country`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_country`);

INSERT INTO `PREFIX_state` (`id_state`, `id_country`, `id_zone`, `name`, `iso_code`, `active`) VALUES
(1, 21, 2, 'Alabama', 'AL', 1),(2, 21, 2, 'Alaska', 'AK', 1),(3, 21, 2, 'Arizona', 'AZ', 1),(4, 21, 2, 'Arkansas', 'AR', 1),
(5, 21, 2, 'California', 'CA', 1),(6, 21, 2, 'Colorado', 'CO', 1),(7, 21, 2, 'Connecticut', 'CT', 1),(8, 21, 2, 'Delaware', 'DE', 1),
(9, 21, 2, 'Florida', 'FL', 1),(10, 21, 2, 'Georgia', 'GA', 1),(11, 21, 2, 'Hawaii', 'HI', 1),(12, 21, 2, 'Idaho', 'ID', 1),
(13, 21, 2, 'Illinois', 'IL', 1),(14, 21, 2, 'Indiana', 'IN', 1),(15, 21, 2, 'Iowa', 'IA', 1),(16, 21, 2, 'Kansas', 'KS', 1),
(17, 21, 2, 'Kentucky', 'KY', 1),(18, 21, 2, 'Louisiana', 'LA', 1),(19, 21, 2, 'Maine', 'ME', 1),(20, 21, 2, 'Maryland', 'MD', 1),
(21, 21, 2, 'Massachusetts', 'MA', 1),(22, 21, 2, 'Michigan', 'MI', 1),(23, 21, 2, 'Minnesota', 'MN', 1),
(24, 21, 2, 'Mississippi', 'MS', 1),(25, 21, 2, 'Missouri', 'MO', 1),(26, 21, 2, 'Montana', 'MT', 1),(27, 21, 2, 'Nebraska', 'NE', 1),
(28, 21, 2, 'Nevada', 'NV', 1),(29, 21, 2, 'New Hampshire', 'NH', 1),(30, 21, 2, 'New Jersey', 'NJ', 1),(31, 21, 2, 'New Mexico', 'NM', 1),
(32, 21, 2, 'New York', 'NY', 1),(33, 21, 2, 'North Carolina', 'NC', 1),(34, 21, 2, 'North Dakota', 'ND', 1),(35, 21, 2, 'Ohio', 'OH', 1),
(36, 21, 2, 'Oklahoma', 'OK', 1),(37, 21, 2, 'Oregon', 'OR', 1),(38, 21, 2, 'Pennsylvania', 'PA', 1),(39, 21, 2, 'Rhode Island', 'RI', 1),
(40, 21, 2, 'South Carolina', 'SC', 1),(41, 21, 2, 'South Dakota', 'SD', 1),(42, 21, 2, 'Tennessee', 'TN', 1),(43, 21, 2, 'Texas', 'TX', 1),
(44, 21, 2, 'Utah', 'UT', 1),(45, 21, 2, 'Vermont', 'VT', 1),(46, 21, 2, 'Virginia', 'VA', 1),(47, 21, 2, 'Washington', 'WA', 1),
(48, 21, 2, 'West Virginia', 'WV', 1),(49, 21, 2, 'Wisconsin', 'WI', 1),(50, 21, 2, 'Wyoming', 'WY', 1),(51, 21, 2, 'Puerto Rico', 'PR', 1),
(52, 21, 2, 'US Virgin Islands', 'VI', 1);

INSERT INTO `PREFIX_currency` (name, iso_code, sign, blank, conversion_rate, format, deleted) VALUES
('Euro', 'EUR', '€', 1, 1, 2, 0), ('Dollar', 'USD', '$', 0, 1.47, 1, 0), ('Pound', 'GBP', '£', 0, 0.8, 1, 0);

INSERT INTO `PREFIX_tax` (`id_tax`, `rate`) VALUES (1, 19.6),(2, 5.5),(3, 17.5);

INSERT INTO `PREFIX_tax_lang` (`id_tax`, `id_lang`, `name`) VALUES
(1, 1, 'VAT 19.6%'),(1, 2, 'TVA 19.6%'),(1, 3, 'IVA 19.6%'),
(2, 1, 'VAT 5.5%'),(2, 2, 'TVA 5.5%'),(2, 3, 'IVA 5.5%'),
(3, 1, 'VAT 17.5%'),(3, 2, 'TVA UK 17.5%'),(3, 3, 'IVA UK 17.5%');

INSERT INTO `PREFIX_tax_zone` (`id_tax`, `id_zone`) VALUES
(1, 1),
(2, 1);

INSERT INTO `PREFIX_image_type` (`id_image_type`, `name`, `width`, `height`, `products`, `categories`, `manufacturers`, `suppliers`, `scenes`) VALUES
(1, 'small', 45, 45, 1, 1, 1, 1, 0),
(2, 'medium', 80, 80, 1, 1, 1, 1, 0),
(3, 'large', 300, 300, 1, 1, 1, 1, 0),
(4, 'thickbox', 600, 600, 1, 0, 0, 0, 0),
(5, 'category', 500, 150, 0, 1, 0, 0, 0),
(6, 'home', 129, 129, 1, 0, 0, 0, 0),
(7, 'large_scene', 556, 200, 0, 0, 0, 0, 1),
(8, 'thumb_scene', 161, 58, 0, 0, 0, 0, 1);

INSERT INTO `PREFIX_contact_lang` (`id_contact`, `id_lang`, `name`, `description`) VALUES
(1, 1, 'Webmaster', 'If a technical problem occurs on this website'),
(1, 2, 'Webmaster', 'Si un problème technique survient sur le site'),
(1, 3, 'Webmaster', 'Si se produce un problema técnico en el sitio'),
(2, 1, 'Customer service', 'For any question about a product, an order'),
(2, 2, 'Service client', 'Pour toute question ou réclamation sur une commande'),
(2, 3, 'Service client', 'Para cualquier pregunta o queja acerca de un pedido');

INSERT INTO `PREFIX_discount_type` (`id_discount_type`) VALUES (1),(2),(3);
INSERT INTO `PREFIX_discount_type_lang` (`id_discount_type`, `id_lang`, `name`) VALUES
(1, 1, 'Discount on order (%)'),(2, 1, 'Discount on order (amount)'),(3, 1, 'Free shipping'),
(1, 2, 'Réduction sur la commande (%)'),(2, 2, 'Réduction sur la commande (montant)'),(3, 2, 'Frais de port gratuits'),
(1, 3, 'Descuento orden (%)'),(2, 3, 'Descuento (el orden de cantidad)'),(3, 3, 'Gastos de envío gratis');

INSERT INTO `PREFIX_profile` (`id_profile`) VALUES (1);
INSERT INTO `PREFIX_profile_lang` (`id_profile`, `id_lang`, `name`) VALUES (1, 1, 'Administrator'),(1, 2, 'Administrateur'),(1, 3, 'Administrador');

INSERT INTO `PREFIX_tab` (`id_tab`, `class_name`, `id_parent`, `position`) VALUES (1, 'AdminCatalog', 0, 1),(2, 'AdminCustomers', 0, 2),(3, 'AdminOrders', 0, 3),
(4, 'AdminPayment', 0, 4),(5, 'AdminShipping', 0, 5),(6, 'AdminStats', 0, 6),(7, 'AdminModules', 0, 7),(29, 'AdminEmployees', 0, 8),(8, 'AdminPreferences', 0, 9),
(9, 'AdminTools', 0, 10),(60, 'AdminTracking', 1, 1),(10, 'AdminManufacturers', 1, 2),(34, 'AdminSuppliers', 1, 3),(11, 'AdminAttributesGroups', 1, 4),
(36, 'AdminFeatures', 1, 5),(58, 'AdminScenes', 1, 6),(66, 'AdminTags', 1, 7),(68, 'AdminAttachments', 1, 7),(12, 'AdminAddresses', 2, 1),(63, 'AdminGroups', 2, 2),
(65, 'AdminCarts', 2, 3),(42, 'AdminInvoices', 3, 1),(55, 'AdminDeliverySlip', 3, 2),(47, 'AdminReturn', 3, 3),(49, 'AdminSlip', 3, 4),(59, 'AdminMessages', 3, 5),
(13, 'AdminStatuses', 3, 6),(54, 'AdminOrderMessage', 3, 7),(14, 'AdminDiscounts', 4, 3),(15, 'AdminCurrencies', 4, 1),(16, 'AdminTaxes', 4, 2),
(17, 'AdminCarriers', 5, 1),(46, 'AdminStates', 5, 2),(18, 'AdminCountries', 5, 3),(19, 'AdminZones', 5, 4),(20, 'AdminRangePrice', 5, 5),
(21, 'AdminRangeWeight', 5, 6),(50, 'AdminStatsModules', 6, 1),(51, 'AdminStatsConf', 6, 2),(61, 'AdminSearchEngines', 6, 3),(62, 'AdminReferrers', 6, 4),
(22, 'AdminModulesPositions', 7, 1),(30, 'AdminProfiles', 29, 1),(31, 'AdminAccess', 29, 2),(28, 'AdminContacts', 29, 3),(39, 'AdminContact', 8, 1),
(38, 'AdminAppearance', 8, 2),(56, 'AdminMeta', 8, 3),(27, 'AdminPPreferences', 8, 4),(24, 'AdminEmails', 8, 5),(26, 'AdminImages', 8, 6),(23, 'AdminDb', 8, 7),
(48, 'AdminPDF', 8, 8),(44, 'AdminLocalization', 8, 9),(67, 'AdminSearchConf', 8, 10),(32, 'AdminLanguages', 9, 1),(33, 'AdminTranslations', 9, 2),
(35, 'AdminTabs', 9, 3),(37, 'AdminQuickAccesses', 9, 4),(40, 'AdminAliases', 9, 5),(41, 'AdminImport', 9, 6),(52, 'AdminSubDomains', 9, 7),
(53, 'AdminBackup', 9, 8),(57, 'AdminCMS', 9, 9),(64, 'AdminGenerator', 9, 10),(43, 'AdminSearch', -1, 0);

INSERT INTO `PREFIX_access` (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) (SELECT 1, id_tab, 1, 1, 1, 1 FROM PREFIX_tab);

INSERT INTO `PREFIX_tab_lang` (`id_lang`, `id_tab`, `name`) VALUES (1, 1, 'Catalog'),(1, 2, 'Customers'),(1, 3, 'Orders'),(1, 4, 'Payment'),
(1, 5, 'Shipping'),(1, 6, 'Stats'),(1, 7, 'Modules'),(1, 8, 'Preferences'),(1, 9, 'Tools'),(1, 10, 'Manufacturers'),(1, 11, 'Attributes and groups'),
(1, 12, 'Addresses'),(1, 13, 'Statuses'),(1, 14, 'Vouchers'),(1, 15, 'Currencies'),(1, 16, 'Taxes'),(1, 17, 'Carriers'),(1, 18, 'Countries'),
(1, 19, 'Zones'),(1, 20, 'Price ranges'),(1, 21, 'Weight ranges'),(1, 22, 'Positions'),(1, 23, 'Database'),(1, 24, 'Email'),(1, 26, 'Image'),
(1, 27, 'Products'),(1, 28, 'Contacts'),(1, 29, 'Employees'),(1, 30, 'Profiles'),(1, 31, 'Permissions'),(1, 32, 'Languages'),(1, 33, 'Translations'),
(1, 34, 'Suppliers'),(1, 35, 'Tabs'),(1, 36, 'Features'),(1, 37, 'Quick Accesses'),(1, 38, 'Appearance'),(1, 39, 'Contact'),(1, 40, 'Aliases'),
(1, 41, 'Import'),(1, 42, 'Invoices'),(1, 43, 'Search'),(1, 44, 'Localization'),(1, 46, 'States'),(1, 47, 'Merchandise return'),(1, 48, 'PDF'),
(1, 49, 'Credit slips'),(1, 50, 'Modules'),(1, 51, 'Settings'),(1, 52, 'Subdomains'),(1, 53, 'DB backup'),(1, 54, 'Order Messages'),
(1, 55, 'Delivery slips'),(1, 56, 'Meta-Tags'),(1, 57, 'CMS'),(1, 58, 'Image mapping'),(1, 59, 'Customer messages'),(1, 60, 'Tracking'),
(1, 61, 'Search engines'),(1, 62, 'Referrers'),(1, 63, 'Groups'),(1, 64, 'Generators'),(1, 65, 'Carts'),(1, 66, 'Tags'),(1, 67, 'Search'),
(1, 68, 'Attachments'),(2, 1, 'Catalogue'),(2, 2, 'Clients'),(2, 3, 'Commandes'),(2, 4, 'Paiement'),(2, 5, 'Transport'),(2, 6, 'Stats'),
(2, 7, 'Modules'),(2, 8, 'Préférences'),(2, 9, 'Outils'),(2, 10, 'Fabricants'),(2, 11, 'Attributs et groupes'),(2, 12, 'Adresses'),(2, 13, 'Statuts'),
(2, 14, 'Bons de réduction'),(2, 15, 'Devises'),(2, 16, 'Taxes'),(2, 17, 'Transporteurs'),(2, 18, 'Pays'),(2, 19, 'Zones'),(2, 20, 'Tranches de prix'),
(2, 21, 'Tranches de poids'),(2, 22, 'Positions'),(2, 23, 'Base de données'),(2, 24, 'Emails'),(2, 26, 'Images'),(2, 27, 'Produits'),(2, 28, 'Contacts'),
(2, 29, 'Employés'),(2, 30, 'Profils'),(2, 31, 'Permissions'),(2, 32, 'Langues'),(2, 33, 'Traductions'),(2, 34, 'Fournisseurs'),(2, 35, 'Onglets'),
(2, 36, 'Caractéristiques'),(2, 37, 'Accès rapide'),(2, 38, 'Apparence'),(2, 39, 'Coordonnées'),(2, 40, 'Alias'),(2, 41, 'Import'),(2, 42, 'Factures'),
(2, 43, 'Recherche'),(2, 44, 'Localisation'),(2, 46, 'Etats'),(2, 47, 'Retours produits'),(2, 48, 'PDF'),(2, 49, 'Avoirs'),(2, 50, 'Modules'),
(2, 51, 'Configuration'),(2, 52, 'Sous domaines'),(2, 53, 'Sauvegarde BDD'),(2, 54, 'Messages prédéfinis'),(2, 55, 'Bons de livraison'),
(2, 56, 'Méta-Tags'),(2, 57, 'CMS'),(2, 58, 'Scènes'),(2, 59, 'Messages clients'),(2, 60, 'Suivi'),(2, 61, 'Moteurs de recherche'),
(2, 62, 'Sites affluents'),(2, 63, 'Groupes'),(2, 64, 'Générateurs'),(2, 65, 'Paniers'),(2, 66, 'Tags'),(2, 67, 'Recherche'),
(2, 68, 'Documents joints'),(3, 1, 'Catálogo'),(3, 2, 'Clientes'),(3, 3, 'Pedidos'),(3, 4, 'Pago'),(3, 5, 'Transporte'),(3, 6, 'Estadísticas'),
(3, 7, 'Módulos'),(3, 8, 'Preferencias'),(3, 9, 'Herramientas'),(3, 10, 'Fabricantes'),(3, 11, 'Atributos y grupos'),(3, 12, 'Direcciones'),
(3, 13, 'Estados'),(3, 14, 'Vales de descuento'),(3, 15, 'Divisas'),(3, 16, 'Impuestos'),(3, 17, 'Transportistas'),(3, 18, 'Países'),(3, 19, 'Zonas'),
(3, 20, 'Franja de precios'),(3, 21, 'Franja de pesos'),(3, 22, 'Posiciones'),(3, 23, 'Base de datos'),(3, 24, 'Emails'),(3, 26, 'Imágenes'),
(3, 27, 'Productos'),(3, 28, 'Contactos'),(3, 29, 'Empleados'),(3, 30, 'Perfiles'),(3, 31, 'Permisos'),(3, 32, 'Idiomas'),(3, 33, 'Traducciones'),
(3, 34, 'Proovedores'),(3, 35, 'Pestañas'),(3, 36, 'Características'),(3, 37, 'Acceso rápido'),(3, 38, 'Aspecto'),(3, 39, 'Datos'),(3, 40, 'Alias'),
(3, 41, 'Importar'),(3, 42, 'Facturas'),(3, 43, 'Búsqueda'),(3, 44, 'Ubicación'),(3, 46, 'Estados'),(3, 47, 'Devolución productos'),(3, 48, 'PDF'),
(3, 49, 'Vales'),(3, 50, 'Módulos'),(3, 51, 'Configuración'),(3, 52, 'Subcampos'),(3, 53, 'Copia de seguridad'),(3, 54, 'Mensajes de Orden'),
(3, 55, 'Albaranes de entrega'),(3, 56, 'Meta-Tags'),(3, 57, 'CMS'),(3, 58, 'Mapeo de la imagen'),(3, 59, 'Mensajes del cliente'),(3, 60, 'Rastreo'),
(3, 61, 'Motores de búsqueda'),(3, 62, 'Referido'),(3, 63, 'Grupos'),(3, 64, 'Generadores'),(3, 65, 'Carritos'),(3, 66, 'Etiquetas'),(3, 67, 'Búsqueda'),(3, 68, 'Adjuntos');

INSERT IGNORE INTO `PREFIX_tab_lang` (`id_tab`, `id_lang`, `name`)
    (SELECT `id_tab`, id_lang, (SELECT tl.`name`
        FROM `PREFIX_tab_lang` tl
        WHERE tl.`id_lang` = (SELECT c.`value`
            FROM `PREFIX_configuration` c
            WHERE c.`name` = 'PS_LANG_DEFAULT' LIMIT 1) AND tl.`id_tab`=`PREFIX_tab`.`id_tab`)
    FROM `PREFIX_lang` CROSS JOIN `PREFIX_tab`);

INSERT INTO `PREFIX_quick_access` (`id_quick_access`, `link`, `new_window`) VALUES
(1, 'index.php', 0),(2, '../', 1),(3, 'index.php?tab=AdminCatalog&addcategory', 0),(4, 'index.php?tab=AdminCatalog&addproduct', 0),(5, 'index.php?tab=AdminDiscounts&adddiscount', 0);

INSERT INTO `PREFIX_quick_access_lang` (`id_quick_access`, `id_lang`, `name`) VALUES
(1, 1, 'Home'),(1, 2, 'Accueil'),(1, 3, 'Inicio'),
(2, 1, 'My Shop'),(2, 2, 'Ma boutique'),(2, 3, 'Mi tienda'),
(3, 1, 'New category'),(3, 2, 'Nouvelle catégorie'),(3, 3, 'Nueva categoría'),
(4, 1, 'New product'),(4, 2, 'Nouveau produit'),(4, 3, 'Nuevo producto'),
(5, 1, 'New voucher'),(5, 2, 'Nouveau bon de réduction'),(5, 3, 'Nuevo cupón');

INSERT INTO PREFIX_order_return_state (`id_order_return_state`, `color`) VALUES (1, '#ADD8E6'),(2, '#EEDDFF'),(3, '#DDFFAA'),(4, '#FFD3D3'),(5, '#FFFFBB');

INSERT INTO PREFIX_order_return_state_lang (`id_order_return_state`, `id_lang`, `name`) VALUES
(1, 1, 'Waiting for confirmation'),(2, 1, 'Waiting for package'),(3, 1, 'Package received'),
(4, 1, 'Return denied'),(5, 1, 'Return completed'),(1, 2, 'En attente de confirmation'),
(2, 2, 'En attente du colis'),(3, 2, 'Colis reçu'),(4, 2, 'Retour refusé'),
(5, 2, 'Retour terminé'),(1, 3, 'Pendiente de confirmación'),(2, 3, 'En espera de paquetes'),
(3, 3, 'Paquetes recibidos'),(4, 3, 'Volver negó'),(5, 3, 'Diligenciados');

INSERT INTO `PREFIX_meta` (`id_meta`, `page`) VALUES
(1, '404'),(2, 'best-sales'),(3, 'contact-form'),(4, 'index'),(5, 'manufacturer'),(6, 'new-products'),
(7, 'password'),(8, 'prices-drop'),(9, 'sitemap'),(10, 'supplier');

INSERT INTO `PREFIX_meta_lang` (`id_meta`, `id_lang`, `title`, `description`, `keywords`) VALUES
(1, 1, '404 error', 'This page cannot be found', 'error, 404, not found'),
(1, 2, 'Erreur 404', 'Cette page est introuvable', 'erreur, 404, introuvable'),
(1, 3, 'Error 404', 'Esta página no se encuentra', 'error, 404, No se ha encontrado'),
(2, 1, 'Best sales', 'Our best sales', 'best sales'),
(2, 2, 'Meilleures ventes', 'Liste de nos produits les mieux vendus', 'meilleures ventes'),
(2, 3, 'Los más vendidos', 'Lista de los de mayor venta de productos', 'los más vendidos'),
(3, 1, 'Contact us', 'Use our form to contact us', 'contact, form, e-mail'),
(3, 2, 'Contactez-nous', 'Utilisez notre formulaire pour nous contacter', 'contact, formulaire, e-mail'),
(3, 3, 'Contáctenos', 'Use nuestro formulario de contacto con nosotros', 'formulario de contacto, e-mail'),
(4, 1, '', 'Shop powered by PrestaShop', 'shop, prestashop'),
(4, 2, '', 'Boutique propulsée par PrestaShop', 'boutique, prestashop'),
(4, 3, '', 'Shop powered by PrestaShop', 'tienda, prestashop'),
(5, 1, 'Manufacturers', 'Manufacturers list', 'manufacturer'),
(5, 2, 'Fabricants', 'Liste de nos fabricants', 'fabricants'),
(5, 3, 'Fabricantes', 'Lista de Fabricantes', 'fabricantes'),
(6, 1, 'New products', 'Our new products', 'new, products'),
(6, 2, 'Nouveaux produits', 'Liste de nos nouveaux produits', 'nouveau, produit'),
(6, 3, 'Nuevos Productos', 'Lista de nuestros nuevos productos', 'nuevo, productos'),
(7, 1, 'Forgot your password', 'Enter your e-mail address used to register in goal to get e-mail with your new password', 'forgot, password, e-mail, new, reset'),
(7, 2, 'Mot de passe oublié', 'Renseignez votre adresse e-mail afin de recevoir votre nouveau mot de passe.', 'mot de passe, oublié, e-mail, nouveau, regénération'),
(7, 3, 'Olvidaste tu contraseña', 'Ingrese su dirección de correo electrónico para recibir su nueva contraseña.', 'contraseña, has olvidado, e-mail, nuevo, regeneración'),
(8, 1, 'Specials', 'Our special products', 'special, prices drop'),
(8, 2, 'Promotions', 'Nos produits en promotion', 'promotion, réduction'),
(8, 3, 'Promociones', 'Nuestros productos promocionales', 'promoción, reducción'),
(9, 1, 'Sitemap', 'Lost ? Find what your are looking for', 'sitemap'),
(9, 2, 'Plan du site', 'Perdu ? Trouvez ce que vous cherchez', 'plan, site'),
(9, 3, 'Mapa del sitio', '¿Perdido? Encuentra lo que buscas', 'plan, sitio'),
(10, 1, 'Suppliers', 'Suppliers list', 'supplier'),
(10, 2, 'Fournisseurs', 'Liste de nos fournisseurs', 'fournisseurs'),
(10, 3, 'Proveedores', 'Lista de Proveedores', 'proveedores');

/* Stats */
INSERT INTO `PREFIX_operating_system` (`name`) VALUES ('Windows XP'),('Windows Vista'),('MacOsX'),('Linux');
INSERT INTO `PREFIX_web_browser` (`name`) VALUES ('Safari'),('Firefox 2.x'),('Firefox 3.x'),('Opera'),('IE 6.x'),('IE 7.x'),('IE 8.x'),('Google Chrome');
INSERT INTO `PREFIX_page_type` (`name`) VALUES ('product.php'),('category.php'),('order.php'),('manufacturer.php');
INSERT INTO `PREFIX_search_engine` (`server`,`getvar`)
VALUES  ('google','q'),('search.aol','query'),('yandex.ru','text'),('ask.com','q'),('nhl.com','q'),('search.yahoo','p'),
('baidu.com','wd'),('search.lycos','query'),('exalead','q'),('search.live.com','q'),('search.ke.voila','rdata'),('altavista','q'),('bing.com','q');

/* SubDomains */
INSERT INTO PREFIX_subdomain (id_subdomain, name) VALUES (NULL, 'www');

/* CMS */
INSERT INTO `PREFIX_cms` VALUES (1),(2),(3),(4),(5);
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(1, 1, 'Delivery', 'Our terms and conditions of delivery', 'conditions, delivery, delay, shipment, pack', '<h2>Shipments and returns</h2><h3>Your pack shipment</h3><p>Packages are generally dispatched within 2 days after receipt of payment and are shipped via Colissimo with tracking and drop-off without signature. If you prefer delivery by Colissimo Extra with required signature, an additional cost will be applied, so please contact us before choosing this method. Whichever shipment choice you make, we will provide you with a link to track your package online.</p><p>Shipping fees include handling and packing fees as well as postage costs. Handling fees are fixed, whereas transport fees vary according to total weight of the shipment. We advise you to group your items in one order. We cannot group two distinct orders placed separately, and shipping fees will apply to each of them. Your package will be dispatched at your own risk, but special care is taken to protect fragile objects.<br /><br />Boxes are amply sized and your items are well-protected.</p>', 'delivery'),
(1, 2, 'Livraison', 'Nos conditions générales de livraison', 'conditions, livraison, délais, transport, colis', '<h2>Livraisons et retours</h2><h3>Le transport de votre colis</h3><p>Les colis sont g&eacute;n&eacute;ralement exp&eacute;di&eacute;s en 48h apr&egrave;s r&eacute;ception de votre paiement. Le mode d''expédidition standard est le Colissimo suivi, remis sans signature. Si vous souhaitez une remise avec signature, un co&ucirc;t suppl&eacute;mentaire s''applique, merci de nous contacter. Quel que soit le mode d''expédition choisi, nous vous fournirons d&egrave;s que possible un lien qui vous permettra de suivre en ligne la livraison de votre colis.</p><p>Les frais d''exp&eacute;dition comprennent l''emballage, la manutention et les frais postaux. Ils peuvent contenir une partie fixe et une partie variable en fonction du prix ou du poids de votre commande. Nous vous conseillons de regrouper vos achats en une unique commande. Nous ne pouvons pas grouper deux commandes distinctes et vous devrez vous acquitter des frais de port pour chacune d''entre elles. Votre colis est exp&eacute;di&eacute; &agrave; vos propres risques, un soin particulier est apport&eacute; au colis contenant des produits fragiles..<br /><br />Les colis sont surdimensionn&eacute;s et prot&eacute;g&eacute;s.</p>', 'livraison'),
(2, 1, 'Legal Notice', 'Legal notice', 'notice, legal, credits', '<h2>Legal</h2><h3>Credits</h3><p>Concept and production:</p><p>This Web site was created using <a href="http://www.prestashop.com">PrestaShop</a>&trade; open-source software.</p>', 'legal-notice'),
(2, 2, 'Mentions légales', 'Mentions légales', 'mentions, légales, crédits', '<h2>Mentions l&eacute;gales</h2><h3>Cr&eacute;dits</h3><p>Concept et production :</p><p>Ce site internet a &eacute;t&eacute; r&eacute;alis&eacute; en utilisant la solution open-source <a href="http://www.prestashop.com">PrestaShop</a>&trade; .</p>', 'mentions-legales'),
(3, 1, 'Terms and conditions of use', 'Our terms and conditions of use', 'conditions, terms, use, sell', '<h2>Your terms and conditions of use</h2><h3>Rule 1</h3><p>Here is the rule 1 content</p>\r\n<h3>Rule 2</h3><p>Here is the rule 2 content</p>\r\n<h3>Rule 3</h3><p>Here is the rule 3 content</p>', 'terms-and-conditions-of-use'),
(3, 2, 'Conditions d''utilisation', 'Nos conditions générales de ventes', 'conditions, utilisation, générales, ventes', '<h2>Vos conditions de ventes</h2><h3>Règle n°1</h3><p>Contenu de la règle numéro 1</p>\r\n<h3>Règle n°2</h3><p>Contenu de la règle numéro 2</p>\r\n<h3>Règle n°3</h3><p>Contenu de la règle numéro 3</p>', 'conditions-generales-de-ventes'),
(4, 1, 'About us', 'Learn more about us', 'about us, informations', '<h2>About us</h2>\r\n<h3>Our company</h3><p>Our company</p>\r\n<h3>Our team</h3><p>Our team</p>\r\n<h3>Informations</h3><p>Informations</p>', 'about-us'),
(4, 2, 'A propos', 'Apprenez-en d''avantage sur nous', 'à propos, informations', '<h2>A propos</h2>\r\n<h3>Notre entreprise</h3><p>Notre entreprise</p>\r\n<h3>Notre équipe</h3><p>Notre équipe</p>\r\n<h3>Informations</h3><p>Informations</p>', 'a-propos'),
(5, 1, 'Secure payment', 'Our secure payment mean', 'secure payment, ssl, visa, mastercard, paypal', '<h2>Secure payment</h2>\r\n<h3>Our secure payment</h3><p>With SSL</p>\r\n<h3>Using Visa/Mastercard/Paypal</h3><p>About this services</p>', 'secure-payment'),
(5, 2, 'Paiement sécurisé', 'Notre offre de paiement sécurisé', 'paiement sécurisé, ssl, visa, mastercard, paypal', '<h2>Paiement sécurisé</h2>\r\n<h3>Notre offre de paiement sécurisé</h3><p>Avec SSL</p>\r\n<h3>Utilisation de Visa/Mastercard/Paypal</h3><p>A propos de ces services</p>', 'paiement-securise'),
(1, 3, 'Entrega', 'Nuestras condiciones de entrega', 'condiciones, plazos de entrega, transporte, paquetería', '<h2><span id="result_box"><span style="background-color: #ffffff;" title="Livraisons et retours">shipping & Returns</span></span></h2>\r\n<h3><span id="result_box"><span style="background-color: #ffffff;" title="Le transport de votre colis">El transporte de su paquete</span></span></h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="Les colis sont généralement expédiés en 48h après réception de votre paiement.">Los paquetes son generalmente enviados en 48 horas después de la recepción de su pago. </span><span style="background-color: #ffffff;" title="Le mode d''expédidition standard est le Colissimo suivi, remis sans signature.">La moda es el estándar expédidition Colissimo seguido, entrega sin firma. </span><span style="background-color: #ffffff;" title="Si vous souhaitez une remise avec signature, un coût supplémentaire s''applique, merci de nous contacter.">Si desea una entrega con la firma, un cargo adicional, gracias al contacto con nosotros. </span><span style="background-color: #ffffff;" title="Quel que soit le mode d''expédition choisi, nous vous fournirons dès que possible un lien qui vous permettra de suivre en ligne la livraison de votre colis.">Sea cual sea el método de envío seleccionado, vamos a presentar lo antes posible, un vínculo que le permite rastrear el envío en línea de su paquete.<br /><br /></span><span style="background-color: #ffffff;" title="Les frais d''expédition comprennent l''emballage, la manutention et les frais postaux.">Gastos de envío incluyen el embalaje, la manipulación y envío. </span><span style="background-color: #ffffff;" title="Ils peuvent contenir une partie fixe et une partie variable en fonction du prix ou du poids de votre commande.">Pueden contener un fijo y una parte variable basado en el precio o el peso de su solicitud. </span><span style="background-color: #ffffff;" title="Nous vous conseillons de regrouper vos achats en une unique commande.">Le recomendamos que para consolidar sus compras en un solo comando. </span><span style="background-color: #ffffff;" title="Nous ne pouvons pas grouper deux commandes distinctes et vous devrez vous acquitter des frais de port pour chacune d''entre elles.">No podemos grupo de dos órdenes distintos y hay que pagar gastos de envío para cada uno. </span><span style="background-color: #ffffff;" title="Votre colis est expédié à vos propres risques, un soin particulier est apporté au colis contenant des produits fragiles..">Su paquete es enviado a su propio riesgo, se presta especial atención a las parcelas que contienen objetos frágiles ..<br /><br /></span><span style="background-color: #ffffff;" title="Les colis sont surdimensionnés et protégés.">Los paquetes son de gran tamaño y protegidas.</span></span></p>', 'entrega'),
(2, 3, 'Aviso legal', 'Aviso legal', 'términos, condiciones y créditos fotográficos', '<h2><span id="result_box"><span style="background-color: #ffffff;" title="Mentions légales">Pie de imprenta</span></span></h2>\r\n<h2><span id="result_box"><span style="background-color: #ffffff;" title="Mentions légales"> </span></span><span id="result_box"><span style="background-color: #ffffff;" title="Crédits">Créditos</span></span></h2>\r\n<h3><span id="result_box"></span></h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="Crédits"><br /></span><span style="background-color: #ffffff;" title="Concept et production :">Concepto y producción:<br /><br /></span><span style="background-color: #ffffff;" title="Ce site internet a été réalisé en utilisant la solution open-source PrestaShop™ .">Este sitio web fue creado utilizando la solución de código abierto <a href="http://www.prestashop.com" target="_blank">PrestaShop</a>™.</span></span></p>', 'aviso-legal'),
(3, 3, 'Condiciones de uso', 'Condiciones de uso', 'condiciones, el consumo, las ventas generales', '<h2><span id="result_box"><span style="background-color: #ffffff;" title="Vos conditions de ventes">Sus condiciones de venta</span></span></h2>\r\n<h3>Regla N º 1</h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="Contenu de la règle numéro 1">Contenido de la Regla Número 1</span></span></p>\r\n<h3><span id="result_box"></span>Regla N º 2</h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="Contenu de la règle numéro 2">Contenido de la Regla N º 2</span></span></p>\r\n<h3><span id="result_box"></span>Regla N º 3</h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="Contenu de la règle numéro 3">Contenido de la Regla Número 3</span></span></p>', 'condiciones-de-uso'),
(4, 3, 'Sobre', 'Conozca más sobre nosotros', 'sobre, información', '<h2>Sobre</h2>', 'sobre'),
(5, 3, 'Pago seguro', 'Ofrecemos pago seguro', 'pago seguro, ssl, visa, mastercard, paypal', '<h2><span id="result_box"><span style="background-color: #ffffff;" title="Paiement sécurisé">Pago seguro</span></span></h2>\r\n<h3><span id="result_box"><span style="background-color: #ffffff;" title="Notre offre de paiement sécurisé">Ofrecemos pago seguro</span></span></h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="Avec SSL">SSL</span></span></p>\r\n<h3><span id="result_box"><span style="background-color: #ffffff;" title="Utilisation de Visa/Mastercard/Paypal">Utilice Visa / Mastercard / Paypal</span></span></h3>\r\n<p><span id="result_box"><span style="background-color: #ffffff;" title="A propos de ces services">Acerca de estos servicios</span></span></p>', 'pago-seguro');

INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (23, 3),(23, 4),(12, 1),(12, 2),(12, 3),(12, 4);

/* Carrier */
INSERT INTO `PREFIX_carrier` (`id_carrier`, `id_tax`, `name`, `active`, `deleted`, `shipping_handling`) VALUES (1, 0, 0, 1, 0, 0),(2, 1, 'My carrier', 1, 0, 1);

INSERT INTO `PREFIX_carrier_lang` (`id_carrier`, `id_lang`, `delay`) VALUES (1, 1, 'Pick up in-store'),(1, 2, 'Retrait au magasin'),
(1, 3, 'Recogida en la tienda'),(2, 1, 'Delivery next day!'),(2, 2, 'Livraison le lendemain !'),(2, 3, '¡Entrega día siguiente!');
	
INSERT INTO `PREFIX_carrier_zone` (`id_carrier`, `id_zone`) VALUES (1, 1),(2, 1),(2, 2);

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES	('PS_CARRIER_DEFAULT', '2', NOW(), NOW());

/* Timezone */
INSERT INTO `PREFIX_timezone` (`name`) VALUES ('Africa/Abidjan'),('Africa/Accra'),('Africa/Addis_Ababa'),('Africa/Algiers'),
('Africa/Asmara'),('Africa/Asmera'),('Africa/Bamako'),('Africa/Bangui'),('Africa/Banjul'),('Africa/Bissau'),('Africa/Blantyre'),
('Africa/Brazzaville'),('Africa/Bujumbura'),('Africa/Cairo'),('Africa/Casablanca'),('Africa/Ceuta'),('Africa/Conakry'),('Africa/Dakar'),
('Africa/Dar_es_Salaam'),('Africa/Djibouti'),('Africa/Douala'),('Africa/El_Aaiun'),('Africa/Freetown'),('Africa/Gaborone'),('Africa/Harare'),
('Africa/Johannesburg'),('Africa/Kampala'),('Africa/Khartoum'),('Africa/Kigali'),('Africa/Kinshasa'),('Africa/Lagos'),('Africa/Libreville'),
('Africa/Lome'),('Africa/Luanda'),('Africa/Lubumbashi'),('Africa/Lusaka'),('Africa/Malabo'),('Africa/Maputo'),('Africa/Maseru'),
('Africa/Mbabane'),('Africa/Mogadishu'),('Africa/Monrovia'),('Africa/Nairobi'),('Africa/Ndjamena'),('Africa/Niamey'),('Africa/Nouakchott'),
('Africa/Ouagadougou'),('Africa/Porto-Novo'),('Africa/Sao_Tome'),('Africa/Timbuktu'),('Africa/Tripoli'),('Africa/Tunis'),('Africa/Windhoek'),
('America/Adak'),('America/Anchorage '),('America/Anguilla'),('America/Antigua'),('America/Araguaina'),('America/Argentina/Buenos_Aires'),
('America/Argentina/Catamarca'),('America/Argentina/ComodRivadavia'),('America/Argentina/Cordoba'),('America/Argentina/Jujuy'),
('America/Argentina/La_Rioja'),('America/Argentina/Mendoza'),('America/Argentina/Rio_Gallegos'),('America/Argentina/Salta'),
('America/Argentina/San_Juan'),('America/Argentina/San_Luis'),('America/Argentina/Tucuman'),('America/Argentina/Ushuaia'),('America/Aruba'),
('America/Asuncion'),('America/Atikokan'),('America/Atka'),('America/Bahia'),('America/Barbados'),('America/Belem'),('America/Belize'),
('America/Blanc-Sablon'),('America/Boa_Vista'),('America/Bogota'),('America/Boise'),('America/Buenos_Aires'),('America/Cambridge_Bay'),
('America/Campo_Grande'),('America/Cancun'),('America/Caracas'),('America/Catamarca'),('America/Cayenne'),('America/Cayman'),('America/Chicago'),
('America/Chihuahua'),('America/Coral_Harbour'),('America/Cordoba'),('America/Costa_Rica'),('America/Cuiaba'),('America/Curacao'),
('America/Danmarkshavn'),('America/Dawson'),('America/Dawson_Creek'),('America/Denver'),('America/Detroit'),('America/Dominica'),
('America/Edmonton'),('America/Eirunepe'),('America/El_Salvador'),('America/Ensenada'),('America/Fort_Wayne'),('America/Fortaleza'),
('America/Glace_Bay'),('America/Godthab'),('America/Goose_Bay'),('America/Grand_Turk'),('America/Grenada'),('America/Guadeloupe'),
('America/Guatemala'),('America/Guayaquil'),('America/Guyana'),('America/Halifax'),('America/Havana'),('America/Hermosillo'),
('America/Indiana/Indianapolis'),('America/Indiana/Knox'),('America/Indiana/Marengo'),('America/Indiana/Petersburg'),
('America/Indiana/Tell_City'),('America/Indiana/Vevay'),('America/Indiana/Vincennes'),('America/Indiana/Winamac'),('America/Indianapolis'),
('America/Inuvik'),('America/Iqaluit'),('America/Jamaica'),('America/Jujuy'),('America/Juneau'),('America/Kentucky/Louisville'),
('America/Kentucky/Monticello'),('America/Knox_IN'),('America/La_Paz'),('America/Lima'),('America/Los_Angeles'),('America/Louisville'),
('America/Maceio'),('America/Managua'),('America/Manaus'),('America/Marigot'),('America/Martinique'),('America/Mazatlan'),('America/Mendoza'),
('America/Menominee'),('America/Merida'),('America/Mexico_City'),('America/Miquelon'),('America/Moncton'),('America/Monterrey'),
('America/Montevideo'),('America/Montreal'),('America/Montserrat'),('America/Nassau'),('America/New_York'),('America/Nipigon'),
('America/Nome'),('America/Noronha'),('America/North_Dakota/Center'),('America/North_Dakota/New_Salem'),('America/Panama'),
('America/Pangnirtung'),('America/Paramaribo'),('America/Phoenix'),('America/Port-au-Prince'),('America/Port_of_Spain'),('America/Porto_Acre'),
('America/Porto_Velho'),('America/Puerto_Rico'),('America/Rainy_River'),('America/Rankin_Inlet'),('America/Recife'),('America/Regina'),
('America/Resolute'),('America/Rio_Branco'),('America/Rosario'),('America/Santarem'),('America/Santiago'),('America/Santo_Domingo'),
('America/Sao_Paulo'),('America/Scoresbysund'),('America/Shiprock'),('America/St_Barthelemy'),('America/St_Johns'),('America/St_Kitts'),
('America/St_Lucia'),('America/St_Thomas'),('America/St_Vincent'),('America/Swift_Current'),('America/Tegucigalpa'),('America/Thule'),
('America/Thunder_Bay'),('America/Tijuana'),('America/Toronto'),('America/Tortola'),('America/Vancouver'),('America/Virgin'),('America/Whitehorse'),
('America/Winnipeg'),('America/Yakutat'),('America/Yellowknife'),('Antarctica/Casey'),('Antarctica/Davis'),('Antarctica/DumontDUrville'),
('Antarctica/Mawson'),('Antarctica/McMurdo'),('Antarctica/Palmer'),('Antarctica/Rothera'),('Antarctica/South_Pole'),('Antarctica/Syowa'),
('Antarctica/Vostok'),('Arctic/Longyearbyen'),('Asia/Aden'),('Asia/Almaty'),('Asia/Amman'),('Asia/Anadyr'),('Asia/Aqtau'),('Asia/Aqtobe'),
('Asia/Ashgabat'),('Asia/Ashkhabad'),('Asia/Baghdad'),('Asia/Bahrain'),('Asia/Baku'),('Asia/Bangkok'),('Asia/Beirut'),('Asia/Bishkek'),
('Asia/Brunei'),('Asia/Calcutta'),('Asia/Choibalsan'),('Asia/Chongqing'),('Asia/Chungking'),('Asia/Colombo'),('Asia/Dacca'),('Asia/Damascus'),
('Asia/Dhaka'),('Asia/Dili'),('Asia/Dubai'),('Asia/Dushanbe'),('Asia/Gaza'),('Asia/Harbin'),('Asia/Ho_Chi_Minh'),('Asia/Hong_Kong'),('Asia/Hovd'),
('Asia/Irkutsk'),('Asia/Istanbul'),('Asia/Jakarta'),('Asia/Jayapura'),('Asia/Jerusalem'),('Asia/Kabul'),('Asia/Kamchatka'),('Asia/Karachi'),
('Asia/Kashgar'),('Asia/Kathmandu'),('Asia/Katmandu'),('Asia/Kolkata'),('Asia/Krasnoyarsk'),('Asia/Kuala_Lumpur'),('Asia/Kuching'),('Asia/Kuwait'),
('Asia/Macao'),('Asia/Macau'),('Asia/Magadan'),('Asia/Makassar'),('Asia/Manila'),('Asia/Muscat'),('Asia/Nicosia'),('Asia/Novosibirsk'),('Asia/Omsk'),
('Asia/Oral'),('Asia/Phnom_Penh'),('Asia/Pontianak'),('Asia/Pyongyang'),('Asia/Qatar'),('Asia/Qyzylorda'),('Asia/Rangoon'),('Asia/Riyadh'),
('Asia/Saigon'),('Asia/Sakhalin'),('Asia/Samarkand'),('Asia/Seoul'),('Asia/Shanghai'),('Asia/Singapore'),('Asia/Taipei'),('Asia/Tashkent'),
('Asia/Tbilisi'),('Asia/Tehran'),('Asia/Tel_Aviv'),('Asia/Thimbu'),('Asia/Thimphu'),('Asia/Tokyo'),('Asia/Ujung_Pandang'),('Asia/Ulaanbaatar'),
('Asia/Ulan_Bator'),('Asia/Urumqi'),('Asia/Vientiane'),('Asia/Vladivostok'),('Asia/Yakutsk'),('Asia/Yekaterinburg'),('Asia/Yerevan'),
('Atlantic/Azores'),('Atlantic/Bermuda'),('Atlantic/Canary'),('Atlantic/Cape_Verde'),('Atlantic/Faeroe'),('Atlantic/Faroe'),('Atlantic/Jan_Mayen'),
('Atlantic/Madeira'),('Atlantic/Reykjavik'),('Atlantic/South_Georgia'),('Atlantic/St_Helena'),('Atlantic/Stanley'),('Australia/ACT'),
('Australia/Adelaide'),('Australia/Brisbane'),('Australia/Broken_Hill'),('Australia/Canberra'),('Australia/Currie'),('Australia/Darwin'),
('Australia/Eucla'),('Australia/Hobart'),('Australia/LHI'),('Australia/Lindeman'),('Australia/Lord_Howe'),('Australia/Melbourne'),('Australia/North'),
('Australia/NSW'),('Australia/Perth'),('Australia/Queensland'),('Australia/South'),('Australia/Sydney'),('Australia/Tasmania'),('Australia/Victoria'),
('Australia/West'),('Australia/Yancowinna'),('Europe/Amsterdam'),('Europe/Andorra'),('Europe/Athens'),('Europe/Belfast'),('Europe/Belgrade'),
('Europe/Berlin'),('Europe/Bratislava'),('Europe/Brussels'),('Europe/Bucharest'),('Europe/Budapest'),('Europe/Chisinau'),('Europe/Copenhagen'),
('Europe/Dublin'),('Europe/Gibraltar'),('Europe/Guernsey'),('Europe/Helsinki'),('Europe/Isle_of_Man'),('Europe/Istanbul'),('Europe/Jersey'),
('Europe/Kaliningrad'),('Europe/Kiev'),('Europe/Lisbon'),('Europe/Ljubljana'),('Europe/London'),('Europe/Luxembourg'),('Europe/Madrid'),('Europe/Malta'),
('Europe/Mariehamn'),('Europe/Minsk'),('Europe/Monaco'),('Europe/Moscow'),('Europe/Nicosia'),('Europe/Oslo'),('Europe/Paris'),('Europe/Podgorica'),
('Europe/Prague'),('Europe/Riga'),('Europe/Rome'),('Europe/Samara'),('Europe/San_Marino'),('Europe/Sarajevo'),('Europe/Simferopol'),('Europe/Skopje'),
('Europe/Sofia'),('Europe/Stockholm'),('Europe/Tallinn'),('Europe/Tirane'),('Europe/Tiraspol'),('Europe/Uzhgorod'),('Europe/Vaduz'),('Europe/Vatican'),
('Europe/Vienna'),('Europe/Vilnius'),('Europe/Volgograd'),('Europe/Warsaw'),('Europe/Zagreb'),('Europe/Zaporozhye'),('Europe/Zurich'),
('Indian/Antananarivo'),('Indian/Chagos'),('Indian/Christmas'),('Indian/Cocos'),('Indian/Comoro'),('Indian/Kerguelen'),('Indian/Mahe'),('Indian/Maldives'),
('Indian/Mauritius'),('Indian/Mayotte'),('Indian/Reunion'),('Pacific/Apia'),('Pacific/Auckland'),('Pacific/Chatham'),('Pacific/Easter'),('Pacific/Efate'),
('Pacific/Enderbury'),('Pacific/Fakaofo'),('Pacific/Fiji'),('Pacific/Funafuti'),('Pacific/Galapagos'),('Pacific/Gambier'),('Pacific/Guadalcanal'),
('Pacific/Guam'),('Pacific/Honolulu'),('Pacific/Johnston'),('Pacific/Kiritimati'),('Pacific/Kosrae'),('Pacific/Kwajalein'),('Pacific/Majuro'),
('Pacific/Marquesas'),('Pacific/Midway'),('Pacific/Nauru'),('Pacific/Niue'),('Pacific/Norfolk'),('Pacific/Noumea'),('Pacific/Pago_Pago'),('Pacific/Palau'),
('Pacific/Pitcairn'),('Pacific/Ponape'),('Pacific/Port_Moresby'),('Pacific/Rarotonga'),('Pacific/Saipan'),('Pacific/Samoa'),('Pacific/Tahiti'),
('Pacific/Tarawa'),('Pacific/Tongatapu'),('Pacific/Truk'),('Pacific/Wake'),('Pacific/Wallis'),('Pacific/Yap'),('Brazil/Acre'),('Brazil/DeNoronha'),
('Brazil/East'),('Brazil/West'),('Canada/Atlantic'),('Canada/Central'),('Canada/East-Saskatchewan'),('Canada/Eastern'),('Canada/Mountain'),
('Canada/Newfoundland'),('Canada/Pacific'),('Canada/Saskatchewan'),('Canada/Yukon'),('CET'),('Chile/Continental'),('Chile/EasterIsland'),('CST6CDT'),
('Cuba'),('EET'),('Egypt'),('Eire'),('EST'),('EST5EDT'),('Etc/GMT'),('Etc/GMT+0'),('Etc/GMT+1'),('Etc/GMT+10'),('Etc/GMT+11'),('Etc/GMT+12'),
('Etc/GMT+2'),('Etc/GMT+3'),('Etc/GMT+4'),('Etc/GMT+5'),('Etc/GMT+6'),('Etc/GMT+7'),('Etc/GMT+8'),('Etc/GMT+9'),('Etc/GMT-0'),('Etc/GMT-1'),
('Etc/GMT-10'),('Etc/GMT-11'),('Etc/GMT-12'),('Etc/GMT-13'),('Etc/GMT-14'),('Etc/GMT-2'),('Etc/GMT-3'),('Etc/GMT-4'),('Etc/GMT-5'),('Etc/GMT-6'),
('Etc/GMT-7'),('Etc/GMT-8'),('Etc/GMT-9'),('Etc/GMT0'),('Etc/Greenwich'),('Etc/UCT'),('Etc/Universal'),('Etc/UTC'),('Etc/Zulu'),('Factory'),('GB'),
('GB-Eire'),('GMT'),('GMT+0'),('GMT-0'),('GMT0'),('Greenwich'),('Hongkong'),('HST'),('Iceland'),('Iran'),('Israel'),('Jamaica'),('Japan'),('Kwajalein'),
('Libya'),('MET'),('Mexico/BajaNorte'),('Mexico/BajaSur'),('Mexico/General'),('MST'),('MST7MDT'),('Navajo'),('NZ'),('NZ-CHAT'),('Poland'),('Portugal'),
('PRC'),('PST8PDT'),('ROC'),('ROK'),('Singapore'),('Turkey'),('UCT'),('Universal'),('US/Alaska'),('US/Aleutian'),('US/Arizona'),('US/Central'),
('US/East-Indiana'),('US/Eastern'),('US/Hawaii'),('US/Indiana-Starke'),('US/Michigan'),('US/Mountain'),('US/Pacific'),('US/Pacific-New'),('US/Samoa'),
('UTC'),('W-SU'),('WET'),('Zulu');

INSERT INTO `PREFIX_group` (`id_group`, `reduction`, `date_add`, `date_upd`) VALUES	(1, 0, NOW(), NOW());

INSERT INTO `PREFIX_group_lang` (`id_group`, `id_lang`, `name`) VALUES	(1, 1, 'Default'),(1, 2, 'Défaut'),(1, 3, 'Predeterminado');

INSERT INTO `PREFIX_category_group` (`id_category`, `id_group`) VALUES (1, 1);
