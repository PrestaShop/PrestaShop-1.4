SET NAMES 'utf8';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
('PS_CARRIER_DEFAULT', '2', NOW(), NOW()),
('PAYPAL_BUSINESS', 'paypal@prestashop.com', NOW(), NOW()),
('PAYPAL_SANDBOX', 0, NOW(), NOW()),
('PAYPAL_CURRENCY', 'customer', NOW(), NOW()),
('BANK_WIRE_CURRENCIES', '2,1', NOW(), NOW()),
('CHEQUE_CURRENCIES', '2,1', NOW(), NOW()),
('PRODUCTS_VIEWED_NBR', '2', NOW(), NOW()),
('BLOCK_CATEG_DHTML', '1', NOW(), NOW()),
('BLOCK_CATEG_MAX_DEPTH', '3', NOW(), NOW()),
('MANUFACTURER_DISPLAY_FORM', '1', NOW(), NOW()),
('MANUFACTURER_DISPLAY_TEXT', '1', NOW(), NOW()),
('MANUFACTURER_DISPLAY_TEXT_NB', '5', NOW(), NOW()),
('NEW_PRODUCTS_NBR', '5', NOW(), NOW()),
('STATSHOME_YEAR_FROM', DATE_FORMAT(NOW(), '%Y'), NOW(), NOW()),
('STATSHOME_MONTH_FROM', DATE_FORMAT(NOW(), '%m'), NOW(), NOW()),
('STATSHOME_DAY_FROM', DATE_FORMAT(NOW(), '%d'), NOW(), NOW()),
('STATSHOME_YEAR_TO', DATE_FORMAT(NOW(), '%Y'), NOW(), NOW()),
('STATSHOME_MONTH_TO', DATE_FORMAT(NOW(), '%m'), NOW(), NOW()),
('STATSHOME_DAY_TO', DATE_FORMAT(NOW(), '%d'), NOW(), NOW()),
('PS_TOKEN_ENABLE', '1', NOW(), NOW()),
('PS_STATS_RENDER', 'graphxmlswfcharts', NOW(), NOW()),
('PS_STATS_OLD_CONNECT_AUTO_CLEAN', 'never', NOW(), NOW()),
('PS_STATS_GRID_RENDER', 'gridextjs', NOW(), NOW());

INSERT INTO `PREFIX_module` (`id_module`, `name`, `active`) VALUES
(1, 'homefeatured', 1),
(2, 'gsitemap', 1),
(3, 'cheque', 1),
(4, 'paypal', 1),
(5, 'editorial', 1),
(6, 'bankwire', 1),
(7, 'blockadvertising', 1),
(8, 'blockbestsellers', 1),
(9, 'blockcart', 1),
(10, 'blockcategories', 1),
(11, 'blockcurrencies', 1),
(12, 'blockinfos', 1),
(13, 'blocklanguages', 1),
(14, 'blockmanufacturer', 1),
(15, 'blockmyaccount', 1),
(16, 'blocknewproducts', 1),
(17, 'blockpaymentlogo', 1),
(18, 'blockpermanentlinks', 1),
(19, 'blocksearch', 1),
(20, 'blockspecials', 1),
(21, 'blocktags', 1),
(22, 'blockuserinfo', 1),
(23, 'blockvariouslinks', 1),
(24, 'blockviewed', 1),
(25, 'statsdata', 1),
(26, 'statsvisits', 1),
(27, 'statssales', 1),
(28, 'statsregistrations', 1),
(30, 'statspersonalinfos', 1),
(31, 'statslive', 1),
(32, 'statsequipment', 1),
(33, 'statscatalog', 1),
(34, 'graphvisifire', 1),
(35, 'graphxmlswfcharts', 1),
(36, 'graphgooglechart', 1),
(37, 'graphartichow', 1),
(38, 'statshome', 1),
(39, 'gridextjs', 1),
(40, 'statsbestcustomers', 1),
(41, 'statsorigin', 1),
(42, 'pagesnotfound', 1),
(43, 'sekeywords', 1),
(44, 'statsproduct', 1);

INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES ('myAccountBlock', 'My account block', 'Display extra informations inside the "my account" block', 1);

INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES
(3, 1, 1),
(6, 1, 2),
(4, 1, 3),
(8, 2, 1),
(3, 4, 1),
(6, 4, 2),
(9, 6, 1),
(16, 6, 2),
(8, 6, 3),
(20, 6, 4),
(15, 7, 1),
(21, 7, 2),
(10, 7, 3),
(24, 7, 4),
(14, 7, 5),
(12, 7, 6),
(7, 7, 7),
(17, 7, 8),
(5, 8, 1),
(1, 8, 2),
(11, 14, 1),
(13, 14, 2),
(18, 14, 3),
(19, 14, 4),
(22, 14, 5),
(8, 19, 1),
(23, 21, 1),
(25, 11, 1),
(25, 21, 2),
(26, 32, 1),
(27, 32, 2),
(28, 32, 3),
(30, 32, 4),
(31, 32, 5),
(32, 32, 6),
(33, 32, 7),
(34, 33, 1),
(35, 33, 2),
(36, 33, 3),
(37, 33, 4),
(38, 36, 1),
(39, 37, 1),
(40, 32, 8),
(41, 32, 9),
(42, 32, 10),
(43, 32, 11),
(42, 14, 6),
(43, 14, 7),
(44, 32, 12),
(25, 25, 1),
(41, 20, 2);

CREATE TABLE `PREFIX_pagenotfound` (
  id_pagenotfound INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  request_uri VARCHAR(256) NOT NULL,
  http_referer VARCHAR(256) NOT NULL,
  date_add DATETIME NOT NULL,
  PRIMARY KEY(id_pagenotfound)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `PREFIX_sekeyword` (
	id_sekeyword INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	keyword VARCHAR(256) NOT NULL,
	date_add DATETIME NOT NULL,
	PRIMARY KEY(id_sekeyword)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_search_engine` (
	id_search_engine INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	server VARCHAR(64) NOT NULL,
	getvar VARCHAR(16) NOT NULL,
	PRIMARY KEY(id_search_engine)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_search_engine` (`server`,`getvar`) VALUES
('google','q'),('search.aol','query'),('yandex.ru','text'),('ask.com','q'),('nhl.com','q'),('search.yahoo','p'),
('baidu.com','wd'),('search.lycos','query'),('exalead','q'),('search.live.com','q'),('search.ke.voila','rdata'),('altavista','q');

INSERT INTO `PREFIX_carrier` (`id_carrier`, `id_tax`, `name`, `active`, `deleted`, `shipping_handling`) VALUES
(1, 0, 0, 1, 0, 0),
(2, 1, 'My carrier', 1, 0, 1);
INSERT INTO `PREFIX_carrier_lang` (`id_carrier`, `id_lang`, `delay`) VALUES
(1, 1, 'Pick up in-store'),
(1, 2, 'Retrait au magasin'),
(2, 1, 'Delivery next day!'),
(2, 2, 'Livraison le lendemain !');
INSERT INTO `PREFIX_carrier_zone` (`id_carrier`, `id_zone`) VALUES
(1, 1),
(2, 1),
(2, 2);

INSERT INTO `PREFIX_range_price` (`id_range_price`, `id_carrier`, `delimiter1`, `delimiter2`) VALUES
(1, 2, 0, 10000);
INSERT INTO `PREFIX_range_weight` (`id_range_weight`, `id_carrier`, `delimiter1`, `delimiter2`) VALUES
(1, 2, 0, 10000);
INSERT INTO `PREFIX_delivery` (`id_delivery`, `id_range_price`, `id_range_weight`, `id_carrier`, `id_zone`, `price`) VALUES
(1, NULL, 1, 2, 1, 5.00),
(2, NULL, 1, 2, 2, 5.00),
(4, 1, NULL, 2, 1, 5.00),
(5, 1, NULL, 2, 2, 5.00);


INSERT INTO `PREFIX_customer` (`id_customer`, `id_gender`, `secure_key`, `email`, `passwd`, `birthday`, `lastname`, `newsletter`, `optin`, `firstname`, `active`, `date_add`, `date_upd`)
	VALUES (1, 1, '47ce86627c1f3c792a80773c5d2deaf8', 'pub@prestashop.com', 'ad807bdf0426766c05c64041124d30ce', '1970-01-15', 'DOE', 1, 1, 'John', 1, NOW(), NOW());

INSERT INTO `PREFIX_cart` (`id_cart`, `id_carrier`, `id_lang`, `id_address_delivery`, `id_address_invoice`, `id_currency`, `id_customer`, `recyclable`, `gift`, `date_add`, `date_upd`)
	VALUES (1, 2, 2, 6, 6, 1, 1, 1, 0, NOW(), NOW());
INSERT INTO `PREFIX_cart_product` (`id_cart`, `id_product`, `id_product_attribute`, `quantity`) VALUES (1, 7, 23, 1);
INSERT INTO `PREFIX_cart_product` (`id_cart`, `id_product`, `id_product_attribute`, `quantity`) VALUES (1, 9, 0, 1);

INSERT INTO `PREFIX_orders` (`id_order`, `id_carrier`, `id_lang`, `id_customer`, `id_cart`, `id_currency`, `id_address_delivery`, `id_address_invoice`, `secure_key`, `payment`, `module`, `recyclable`, `gift`, `gift_message`, `shipping_number`, `total_discounts`, `total_paid`, `total_paid_real`, `total_products`, `total_shipping`, `total_wrapping`, `invoice_number`, `delivery_number`, `invoice_date`, `delivery_date`, `date_add`, `date_upd`)
	VALUES (1, 2, 2, 1, 1, 1, 2, 2, '47ce86627c1f3c792a80773c5d2deaf8', 'Chèque', 'cheque', 1, 0, '', '', '0.00', '625.98', '625.98', '516.72', '7.98', '0.00', 1, 0, '2008-09-10 15:30:44', '0000-00-00 00:00:00', NOW(), NOW());
INSERT INTO `PREFIX_order_detail` (`id_order_detail`, `id_order`, `product_id`, `product_attribute_id`, `product_name`, `product_quantity`, `product_quantity_return`, `product_price`, `product_quantity_discount`, `product_ean13`, `product_reference`, `product_supplier_reference`, `product_weight`, `tax_name`, `tax_rate`, `ecotax`, `download_hash`, `download_nb`, `download_deadline`)
	VALUES (1, 1, 7, 23, 'iPod touch - Capacité: 32Go', 1, 0, '392.140500', '0.000000', NULL, NULL, NULL, 0, 'TVA 19.6%', '19.60', '0.00', '', 0, '0000-00-00 00:00:00');
INSERT INTO `PREFIX_order_detail` (`id_order_detail`, `id_order`, `product_id`, `product_attribute_id`, `product_name`, `product_quantity`, `product_quantity_return`, `product_price`, `product_quantity_discount`, `product_ean13`, `product_reference`, `product_supplier_reference`, `product_weight`, `tax_name`, `tax_rate`, `ecotax`, `download_hash`, `download_nb`, `download_deadline`)
	VALUES (2, 1, 9, 0, 'Écouteurs à isolation sonore Shure SE210', 1, 0, '124.581900', '0.000000', NULL, NULL, NULL, 0, 'TVA 19.6%', '19.60', '0.00', '', 0, '0000-00-00 00:00:00');
INSERT INTO `PREFIX_order_history` (`id_order_history`, `id_employee`, `id_order`, `id_order_state`, `date_add`) VALUES (1, 0, 1, 1, NOW());

INSERT INTO `PREFIX_manufacturer` (`id_manufacturer`, `name`, `date_add`, `date_upd`) VALUES (1, 'Apple Computer, Inc', NOW(), NOW());
INSERT INTO `PREFIX_manufacturer` (`id_manufacturer`, `name`, `date_add`, `date_upd`) VALUES(2, 'Shure Incorporated', NOW(), NOW());

INSERT INTO `PREFIX_address` (`id_address`, `id_country`, `id_state`, `id_customer`, `id_manufacturer`, `id_supplier`, `alias`, `lastname`, `firstname`, `address1`, `postcode`, `city`, `phone`, `date_add`, `date_upd`, `active`, `deleted`)
	VALUES (1, 21, 5, 0, 1, 0, 'manufacturer', 'JOBS', 'STEVE', '1 Infinite Loop', '95014', 'Cupertino', '(800) 275-2273', NOW(), NOW(), 1, 0);
INSERT INTO `PREFIX_address` (`id_address`, `id_country`, `id_state`, `id_customer`, `id_manufacturer`, `id_supplier`, `alias`, `company`, `lastname`, `firstname`, `address1`, `address2`, `postcode`, `city`, `phone`, `date_add`, `date_upd`, `active`, `deleted`)
	VALUES (2, 8, 0, 1, 0, 0, 'Mon adresse', 'My Company', 'DOE', 'John', '16, Main street', '2nd floor', '75000', 'Paris ', '0102030405', NOW(), NOW(), 1, 0);

INSERT INTO `PREFIX_supplier` (`id_supplier`, `name`, `date_add`, `date_upd`) VALUES (1, 'AppleStore', NOW(), NOW());
INSERT INTO `PREFIX_supplier` (`id_supplier`, `name`, `date_add`, `date_upd`) VALUES (2, 'Shure Online Store', NOW(), NOW());

INSERT INTO `PREFIX_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_tax`, `id_category_default`, `id_color_default`, `on_sale`, `ean13`, `ecotax`, `quantity`, `price`, `wholesale_price`, `reduction_price`, `reduction_percent`, `reduction_from`, `reduction_to`, `reference`, `supplier_reference`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `date_add`, `date_upd`) VALUES
(1, 1, 1, 1, 2, 2, 0, '0', 0.00, 800, 124.581940, 70.000000, 0.00, 5, NOW(), NOW(), '', '', 0.5, 2, 0, 0, 0, 0, 1, NOW(), NOW()),
(2, 1, 1, 1, 2, 0, 0, '0', 0.00, 100, 66.053500, 33.000000, 0.00, 0, NOW(), NOW(), '', '', 0, 2, 0, 0, 0, 0, 1, NOW(), NOW()),
(5, 1, 1, 1, 4, 0, 0, '0', 0.00, 274, 1504.180602, 1000.000000, 0.00, 0, NOW(), NOW(), '', NULL, 1.36, 2, 0, 0, 0, 0, 1, NOW(), NOW()),
(6, 1, 1, 1, 4, 0, 0, '0', 0.00, 250, 1170.568561, 0.000000, 0.00, 0,NOW(), NOW(), '', NULL, 0.75, 2, 0, 0, 0, 0, 1, NOW(), NOW()),
(7, 0, 0, 1, 2, 0, 0, '', 0.00, 180, 241.638796, 200.000000, 0.00, 0, NOW(), NOW(), '', NULL, 0, 2, 0, 0, 0, 0, 1, NOW(), NOW()),
(8, 0, 0, 1, 3, 0, 0, '', 0.00, 1, 25.041806, 0.000000, 0.00, 0, NOW(), NOW(), '', NULL, 0, 2, 0, 0, 0, 0, 1, NOW(), NOW()),
(9, 2, 2, 1, 3, 0, 0, '', 0.00, 1, 124.581940, 0.000000, 0.00, 0, NOW(), NOW(), '', NULL, 0, 2, 0, 0, 0, 0, 1, NOW(), NOW());


INSERT INTO `PREFIX_product_lang` (`id_product`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`) VALUES
(1, 1, '<p><strong><span style="font-size: small;">Curved ahead of the curve.</span></strong></p>\r\n<p>For those about to rock, we give you nine amazing colors. But that&rsquo;s only part of the story. Feel the curved, all-aluminum and glass design and you won&rsquo;t want to put iPod nano down.</p>\r\n<p><strong><span style="font-size: small;">Great looks. And brains, too.</span></strong></p>\r\n<p>The new Genius feature turns iPod nano into your own highly intelligent, personal DJ. It creates playlists by finding songs in your library that go great together.</p>\r\n<p><strong><span style="font-size: small;">Made to move with your moves.</span></strong></p>\r\n<p>The accelerometer comes to iPod nano. Give it a shake to shuffle your music. Turn it sideways to view Cover Flow. And play games designed with your moves in mind.</p>', '<p>New design. New features. Now in 8GB and 16GB. iPod nano rocks like never before.</p>', 'ipod-nano', '', '', '', 'iPod Nano', 'En stock', ''),
(1, 2, '<p><span style="font-size: small;"><strong>Des courbes avantageuses.</strong></span></p>\r\n<p>Pour les amateurs de sensations, voici neuf nouveaux coloris. Et ce n''est pas tout ! Faites l''exp&eacute;rience du design elliptique en aluminum et verre. Vous ne voudrez plus le l&acirc;cher.</p>\r\n<p><strong><span style="font-size: small;">Beau et intelligent.</span></strong></p>\r\n<p>La nouvelle fonctionnalit&eacute; Genius fait d''iPod nano votre DJ personnel. Genius cr&eacute;e des listes de lecture en recherchant dans votre biblioth&egrave;que les chansons qui vont bien ensemble.</p>\r\n<p><strong><span style="font-size: small;">Fait pour bouger avec vous.</span></strong></p>\r\n<p>iPod nano est &eacute;quip&eacute; de l''acc&eacute;l&eacute;rom&egrave;tre. Secouez-le pour m&eacute;langer votre musique. Basculez-le pour afficher Cover Flow. Et d&eacute;couvrez des jeux adapt&eacute;s &agrave; vos mouvements.</p>', '<p>Nouveau design. Nouvelles fonctionnalit&eacute;s. D&eacute;sormais en 8 et 16 Go. iPod nano, plus rock que jamais.</p>', 'ipod-nano', '', '', '', 'iPod Nano', 'En stock', ''),
(2, 1, '<p><span style="font-size: small;"><strong>Instant attachment.</strong></span></p>\r\n<p>Wear up to 500 songs on your sleeve. Or your belt. Or your gym shorts. iPod shuffle is a badge of musical devotion. Now in new, more brilliant colors.</p>\r\n<p><span style="font-size: small;"><strong>Feed your iPod shuffle.</strong></span></p>\r\n<p>iTunes is your entertainment superstore. It&rsquo;s your ultra-organized music collection and jukebox. And it&rsquo;s how you load up your iPod shuffle in one click.</p>\r\n<p><span style="font-size: small;"><strong>Beauty and the beat.</strong></span></p>\r\n<p>Intensely colorful anodized aluminum complements the simple design of iPod shuffle. Now in blue, green, pink, red, and original silver.</p>', '<p>iPod shuffle, the world&rsquo;s most wearable music player, now clips on in more vibrant blue, green, pink, and red.</p>', 'ipod-shuffle', '', '', '', 'iPod shuffle', 'En stock', ''),
(2, 2, '<p><span style="font-size: small;"><strong>Un lien imm&eacute;diat.</strong></span></p>\r\n<p>Portez jusqu''&agrave; 500 chansons accroch&eacute;es &agrave; votre manche, &agrave; votre ceinture ou &agrave; votre short. Arborez votre iPod shuffle comme signe ext&eacute;rieur de votre passion pour la musique. Existe d&eacute;sormais en quatre nouveaux coloris encore plus &eacute;clatants.</p>\r\n<p><span style="font-size: small;"><strong>Emplissez votre iPod shuffle.</strong></span></p>\r\n<p>iTunes est un immense magasin d&eacute;di&eacute; au divertissement, une collection musicale parfaitement organis&eacute;e et un jukebox. Vous pouvez en un seul clic remplir votre iPod shuffle de chansons.</p>\r\n<p><strong><span style="font-size: small;">La musique en technicolor.</span></strong></p>\r\n<p>iPod shuffle s''affiche d&eacute;sormais dans de nouveaux coloris intenses qui rehaussent le design &eacute;pur&eacute; du bo&icirc;tier en aluminium anodis&eacute;. Choisissez parmi le bleu, le vert, le rose, le rouge et l''argent&eacute; d''origine.</p>', '<p>iPod shuffle, le baladeur le plus portable du monde, se clippe maintenant en bleu, vert, rose et rouge.</p>', 'ipod-shuffle', '', '', '', 'iPod shuffle', 'En stock', ''),
(5, 1, '<p>MacBook Air is nearly as thin as your index finger. Practically every detail that could be streamlined has been. Yet it still has a 13.3-inch widescreen LED display, full-size keyboard, and large multi-touch trackpad. It&rsquo;s incomparably portable without the usual ultraportable screen and keyboard compromises.</p><p>The incredible thinness of MacBook Air is the result of numerous size- and weight-shaving innovations. From a slimmer hard drive to strategically hidden I/O ports to a lower-profile battery, everything has been considered and reconsidered with thinness in mind.</p><p>MacBook Air is designed and engineered to take full advantage of the wireless world. A world in which 802.11n Wi-Fi is now so fast and so available, people are truly living untethered &mdash; buying and renting movies online, downloading software, and sharing and storing files on the web. </p>', 'MacBook Air is ultrathin, ultraportable, and ultra unlike anything else. But you don&rsquo;t lose inches and pounds overnight. It&rsquo;s the result of rethinking conventions. Of multiple wireless innovations. And of breakthrough design. With MacBook Air, mobile computing suddenly has a new standard.', 'macbook-air', '', '', '', 'MacBook Air', '', NULL),
(5, 2, '<p>MacBook Air est presque aussi fin que votre index. Pratiquement tout ce qui pouvait être simplifié l''a été. Il n''en dispose pas moins d''un écran panoramique de 13,3 pouces, d''un clavier complet et d''un vaste trackpad multi-touch. Incomparablement portable il vous évite les compromis habituels en matière d''écran et de clavier ultra-portables.</p><p>L''incroyable finesse de MacBook Air est le résultat d''un grand nombre d''innovations en termes de réduction de la taille et du poids. D''un disque dur plus fin à des ports d''E/S habilement dissimulés en passant par une batterie plus plate, chaque détail a été considéré et reconsidéré avec la finesse à l''esprit.</p><p>MacBook Air a été con&ccedil;u et élaboré pour profiter pleinement du monde sans fil. Un monde dans lequel la norme Wi-Fi 802.11n est désormais si rapide et si accessible qu''elle permet véritablement de se libérer de toute attache pour acheter des vidéos en ligne, télécharger des logicééééiels, stocker et partager des fichiers sur le Web. </p>', 'MacBook Air est ultra fin, ultra portable et ultra diff&eacute;rent de tout le reste. Mais on ne perd pas des kilos et des centim&egrave;tres en une nuit. C''est le r&eacute;sultat d''une r&eacute;invention des normes. D''une multitude d''innovations sans fil. Et d''une r&eacute;volution dans le design. Avec MacBook Air, l''informatique mobile prend soudain une nouvelle dimension.', 'macbook-air', '', '', '', 'MacBook Air', '', NULL),
(6, 1, 'Every MacBook has a larger hard drive, up to 250GB, to store growing media collections and valuable data.<br /><br />The 2.4GHz MacBook models now include 2GB of memory standard — perfect for running more of your favorite applications smoothly.', 'MacBook makes it easy to hit the road thanks to its tough polycarbonate case, built-in wireless technologies, and innovative MagSafe Power Adapter that releases automatically if someone accidentally trips on the cord.', 'macbook', '', '', '', 'MacBook', '', NULL),
(6, 2, 'Chaque MacBook est équipé d''un disque dur plus spacieux, d''une capacité atteignant 250 Go, pour stocker vos collections multimédia en expansion et vos données précieuses.<br /><br />Le modèle MacBook à 2,4 GHz intègre désormais 2 Go de mémoire en standard. L''idéal pour exécuter en souplesse vos applications préférées.', 'MacBook vous offre la liberté de mouvement grâce à son boîtier résistant en polycarbonate, à ses technologies sans fil intégrées et à son adaptateur secteur MagSafe novateur qui se déconnecte automatiquement si quelqu''un se prend les pieds dans le fil.', 'macbook', '', '', '', 'MacBook', '', NULL),
(7, 1, '<h3>Five new hands-on applications</h3>\r\n<p>View rich HTML email with photos as well as PDF, Word, and Excel attachments. Get maps, directions, and real-time traffic information. Take notes and read stock and weather reports.</p>\r\n<h3>Touch your music, movies, and more</h3>\r\n<p>The revolutionary Multi-Touch technology built into the gorgeous 3.5-inch display lets you pinch, zoom, scroll, and flick with your fingers.</p>\r\n<h3>Internet in your pocket</h3>\r\n<p>With the Safari web browser, see websites the way they were designed to be seen and zoom in and out with a tap.<sup>2</sup> And add Web Clips to your Home screen for quick access to favorite sites.</p>\r\n<h3>What&rsquo;s in the box</h3>\r\n<ul>\r\n<li><span></span>iPod touch</li>\r\n<li><span></span>Earphones</li>\r\n<li><span></span>USB 2.0 cable</li>\r\n<li><span></span>Dock adapter</li>\r\n<li><span></span>Polishing cloth</li>\r\n<li><span></span>Stand</li>\r\n<li><span></span>Quick Start guide</li>\r\n</ul>', '<ul>\r\n<li>Revolutionary Multi-Touch interface</li>\r\n<li>3.5-inch widescreen color display</li>\r\n<li>Wi-Fi (802.11b/g)</li>\r\n<li>8 mm thin</li>\r\n<li>Safari, YouTube, Mail, Stocks, Weather, Notes, iTunes Wi-Fi Music Store, Maps</li>\r\n</ul>', 'ipod-touch', '', '', '', 'iPod touch', '', NULL),
(7, 2, '<h1>Titre 1</h1>\r\n<h2>Titre 2</h2>\r\n<h3>Titre 3</h3>\r\n<h4>Titre 4</h4>\r\n<h5>Titre 5</h5>\r\n<h6>Titre 6</h6>\r\n<ul>\r\n<li>UL</li>\r\n<li>UL</li>\r\n<li>UL</li>\r\n<li>UL</li>\r\n</ul>\r\n<ol>\r\n<li>OL</li>\r\n<li>OL</li>\r\n<li>OL</li>\r\n<li>OL</li>\r\n</ol>\r\n<p>paragraphe...</p>\r\n<p>paragraphe...</p>\r\n<p>paragraphe...</p>\r\n<table border="0">\r\n<thead> \r\n<tr>\r\n<th>th</th> <th>th</th> <th>th</th>\r\n</tr>\r\n</thead> \r\n<tbody>\r\n<tr>\r\n<td>td</td>\r\n<td>td</td>\r\n<td>td</td>\r\n</tr>\r\n<tr>\r\n<td>td</td>\r\n<td>td</td>\r\n<td>td</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<h3>Cinq nouvelles applications sous la main</h3>\r\n<p>Consultez vos e-mails au format HTML enrichi, avec photos et pieces jointes au format PDF, Word et Excel. Obtenez des cartes, des itin&eacute;raires et des informations sur l''&eacute;tat de la circulation en temps r&eacute;el. R&eacute;digez des notes et consultez les cours de la Bourse et les bulletins m&eacute;t&eacute;o.</p>\r\n<h3>Touchez du doigt votre musique et vos vid&eacute;os. Entre autres.</h3>\r\n<p>La technologie multi-touch r&eacute;volutionnaire int&eacute;gr&eacute;e au superbe &eacute;cran de 3,5 pouces vous permet d''effectuer des zooms avant et arri&egrave;re, de faire d&eacute;filer et de feuilleter des pages &agrave; l''aide de vos seuls doigts.</p>\r\n<h3>Internet dans votre poche</h3>\r\n<p>Avec le navigateur Safari, vous pouvez consulter des sites web dans leur mise en page d''origine et effectuer un zoom avant et arri&egrave;re d''une simple pression sur l''&eacute;cran.</p>\r\n<h3>Contenu du coffret</h3>\r\n<ul>\r\n<li><span></span>iPod touch</li>\r\n<li><span></span>&Eacute;couteurs</li>\r\n<li><span></span>C&acirc;ble USB 2.0</li>\r\n<li><span></span>Adaptateur Dock</li>\r\n<li><span></span>Chiffon de nettoyage</li>\r\n<li><span></span>Support</li>\r\n<li><span></span>Guide de d&eacute;marrage rapide</li>\r\n</ul>\r\n<p>&nbsp;</p>', '<p>Interface multi-touch r&eacute;volutionnaire<br />&Eacute;cran panoramique couleur de 3,5 pouces<br />Wi-Fi (802.11b/g)<br />8 mm d''&eacute;paisseur<br />Safari, YouTube, iTunes Wi-Fi Music Store, Courrier, Cartes, Bourse, M&eacute;t&eacute;o, Notes</p>', 'ipod-touch', '', '', '', 'iPod touch', 'En stock', NULL),
(8, 1, '<p>Lorem ipsum</p>', '<p>Lorem ipsum</p>', 'housse-portefeuille-en-cuir-belkin-pour-ipod-nano-noir-chocolat', '', '', '', 'Housse portefeuille en cuir Belkin pour iPod nano - Noir/Chocolat', '', NULL),
(8, 2, '<p><strong>Caract&eacute;ristiques</strong></p>\r\n<li>Cuir doux r&eacute;sistant<br /> </li>\r\n<li>Acc&egrave;s au bouton Hold<br /> </li>\r\n<li>Fermeture magn&eacute;tique<br /> </li>\r\n<li>Acc&egrave;s au Dock Connector<br /> </li>\r\n<li>Prot&egrave;ge-&eacute;cran</li>', '<p>Cet &eacute;tui en cuir tendance assure une protection compl&egrave;te contre les &eacute;raflures et les petits al&eacute;as de la vie quotidienne. Sa conception &eacute;l&eacute;gante et compacte vous permet de glisser votre iPod directement dans votre poche ou votre sac &agrave; main.</p>', 'housse-portefeuille-en-cuir-ipod-nano-noir-chocolat', '', '', '', 'Housse portefeuille en cuir (iPod nano) - Noir/Chocolat', '', NULL),
(9, 1, '<div class="product-overview-full">Using Hi-Definition MicroSpeakers to deliver full-range audio, the ergonomic and lightweight design of the SE210 earphones is ideal for premium on-the-go listening on your iPod or iPhone. They offer the most accurate audio reproduction from both portable and home stereo audio sources--for the ultimate in precision highs and rich low end. In addition, the flexible design allows you to choose the most comfortable fit from a variety of wearing positions. <br /> <br /> <strong>Features </strong> <br /> \r\n<ul>\r\n<li>Sound-isolating design </li>\r\n<li> Hi-Definition MicroSpeaker with a single balanced armature driver </li>\r\n<li> Detachable, modular cable so you can make the cable longer or shorter depending on your activity </li>\r\n<li> Connector compatible with earphone ports on both iPod and iPhone </li>\r\n</ul>\r\n<strong>Specifications </strong><br /> \r\n<ul>\r\n<li>Speaker type: Hi-Definition MicroSpeaker </li>\r\n<li> Frequency range: 25Hz-18.5kHz </li>\r\n<li> Impedance (1kHz): 26 Ohms </li>\r\n<li> Sensitivity (1mW): 114 dB SPL/mW </li>\r\n<li> Cable length (with extension): 18.0 in./45.0 cm (54.0 in./137.1 cm) </li>\r\n</ul>\r\n<strong>In the box</strong><br /> \r\n<ul>\r\n<li>Shure SE210 earphones </li>\r\n<li> Extension cable (36.0 in./91.4 cm) </li>\r\n<li> Three pairs foam earpiece sleeves (small, medium, large) </li>\r\n<li> Three pairs soft flex earpiece sleeves (small, medium, large) </li>\r\n<li> One pair triple-flange earpiece sleeves </li>\r\n<li> Carrying case </li>\r\n</ul>\r\nWarranty<br /> Two-year limited <br />(For details, please visit <br />www.shure.com/PersonalAudio/CustomerSupport/ProductReturnsAndWarranty/index.htm.) <br /><br /> Mfr. Part No.: SE210-A-EFS <br /><br />Note: Products sold through this website that do not bear the Apple Brand name are serviced and supported exclusively by their manufacturers in accordance with terms and conditions packaged with the products. Apple''s Limited Warranty does not apply to products that are not Apple-branded, even if packaged or sold with Apple products. Please contact the manufacturer directly for technical support and customer service.</div>', '<p>Evolved from personal monitor technology road-tested by pro musicians and perfected by Shure engineers, the lightweight and stylish SE210 delivers full-range audio that''s free from outside noise.</p>', 'ecouteurs-a-isolation-sonore-shure-se210-blanc', '', '', '', 'Shure SE210 Sound-Isolating Earphones for iPod and iPhone', '', NULL),
(9, 2, '<p>Bas&eacute;s sur la technologie des moniteurs personnels test&eacute;e sur la route par des musiciens professionnels et perfectionn&eacute;e par les ing&eacute;nieurs Shure, les &eacute;couteurs SE210, l&eacute;gers et &eacute;l&eacute;gants, fournissent une sortie audio &agrave; gamme &eacute;tendue exempte de tout bruit externe.</p>\r\n<p><img src="http://store.apple.com/Catalog/fr/Images/TM255_screen1.jpg" border="0" /></p>\r\n<p><strong>Conception &agrave; isolation sonore <br /></strong>Les embouts &agrave; isolation sonore fournis bloquent plus de 90 % du bruit ambiant. Combin&eacute;s &agrave; un design ergonomique s&eacute;duisant et un c&acirc;ble modulaire, ils minimisent les intrusions du monde ext&eacute;rieur, vous permettant de vous concentrer sur votre musique. Con&ccedil;us pour les amoureux de la musique qui souhaitent faire &eacute;voluer leur appareil audio portable, les &eacute;couteurs SE210 vous permettent d''emmener la performance avec vous. <br /> <br /><strong>Micro-transducteur haute d&eacute;finition <br /></strong>D&eacute;velopp&eacute;s pour une &eacute;coute de qualit&eacute; sup&eacute;rieure en d&eacute;placement, les &eacute;couteurs SE210 utilisent un seul transducteur &agrave; armature &eacute;quilibr&eacute;e pour b&eacute;n&eacute;ficier d''une gamme audio &eacute;tendue. Le r&eacute;sultat ? Un confort d''&eacute;coute &eacute;poustouflant qui restitue tous les d&eacute;tails d''un spectacle live.</p>\r\n<p><strong>Le kit universel Deluxe comprend les &eacute;l&eacute;ments suivants : <br /></strong>- <strong><em>Embouts &agrave; isolation sonore</em></strong> <br />Les embouts &agrave; isolation sonore inclus ont un double r&ocirc;le : bloquer les bruits ambiants et garantir un maintien et un confort personnalis&eacute;s. Comme chaque oreille est diff&eacute;rente, le kit universel Deluxe comprend trois tailles (S, M, L) d''embouts mousse et flexibles. Choisissez la taille et le style d''embout qui vous conviennent le mieux : une bonne &eacute;tanch&eacute;it&eacute; est un facteur cl&eacute; pour optimiser l''isolation sonore et la r&eacute;ponse des basses, ainsi que pour accro&icirc;tre le confort en &eacute;coute prolong&eacute;e.<br /><br />- <em><strong>C&acirc;ble modulaire</strong></em> <br />En se basant sur les commentaires de nombreux utilisateurs, les ing&eacute;nieurs de Shure ont d&eacute;velopp&eacute; une solution de c&acirc;ble d&eacute;tachable pour permettre un degr&eacute; de personnalisation sans pr&eacute;c&eacute;dent. Le c&acirc;ble de 1 m&egrave;tre fourni vous permet d''adapter votre confort en fonction de l''activit&eacute; et de l''application.<br /> <br />- <em><strong>&Eacute;tui de transport</strong></em> <br />Outre les embouts &agrave; isolation sonore et le c&acirc;ble modulaire, un &eacute;tui de transport compact et r&eacute;sistant est fourni avec les &eacute;couteurs SE210 pour vous permettre de ranger vos &eacute;couteurs de mani&egrave;re pratique et sans encombres.<br /> <br />- <strong><em>Garantie limit&eacute;e de deux ans <br /></em></strong>Chaque solution SE210 achet&eacute;e est couverte par une garantie pi&egrave;ces et main-d''&oelig;uvre de deux ans.<br /><br /><strong>Caract&eacute;ristiques techniques</strong></p>\r\n<ul>\r\n<li> Type de transducteur : micro-transducteur haute d&eacute;finition<br /></li>\r\n<li> Sensibilit&eacute; (1 mW) : pression acoustique de 114 dB/mW<br /></li>\r\n<li> Imp&eacute;dance (&agrave; 1 kHz) : 26 W<br /></li>\r\n<li> Gamme de fr&eacute;quences : 25 Hz &ndash; 18,5 kHz<br /></li>\r\n<li> Longueur de c&acirc;ble / avec rallonge : 45 cm / 136 cm<br /></li>\r\n</ul>\r\n<p><strong>Contenu du coffret<br /></strong></p>\r\n<ul>\r\n<li> &Eacute;couteurs Shure SE210<br /></li>\r\n<li> Kit universel Deluxe (embouts &agrave; isolation sonore, c&acirc;ble modulaire, &eacute;tui de transport)</li>\r\n</ul>', '<p>Les &eacute;couteurs &agrave; isolation sonore ergonomiques et l&eacute;gers offrent la reproduction audio la plus fid&egrave;le en provenance de sources audio st&eacute;r&eacute;o portables ou de salon.</p>', 'ecouteurs-a-isolation-sonore-shure-se210', '', '', '', 'Écouteurs à isolation sonore Shure SE210', '', NULL);


INSERT INTO `PREFIX_category` VALUES (2, 1, 1, 1, NOW(), NOW());
INSERT INTO `PREFIX_category` VALUES (3, 1, 1, 1, NOW(), NOW());
INSERT INTO `PREFIX_category` VALUES (4, 1, 1, 1, NOW(), NOW());

INSERT INTO `PREFIX_category_lang` VALUES
(2, 1, 'iPods', 'Now that you can buy movies from the iTunes Store and sync them to your iPod, the whole world is your theater.', 'music-ipods', '', '', ''),
(2, 2, 'iPods', 'Il est temps, pour le meilleur lecteur de musique, de remonter sur scène pour un rappel. Avec le nouvel iPod, le monde est votre scène.', 'musique-ipods', '', '', ''),
(3, 1, 'Accessories', 'Wonderful accessories for your iPod', 'accessories-ipod', '', '', ''),
(3, 2, 'Accessoires', 'Tous les accessoires à la mode pour votre iPod', 'accessoires-ipod', '', '', ''),
(4, 1, 'Laptops', 'The latest Intel processor, a bigger hard drive, plenty of memory, and even more new features all fit inside just one liberating inch. The new Mac laptops have the performance, power, and connectivity of a desktop computer. Without the desk part.', 'laptops', 'Apple laptops', 'Apple laptops MacBook Air', 'Powerful and chic Apple laptops'),
(4, 2, 'Portables', 'Le tout dernier processeur Intel, un disque dur plus spacieux, de la mémoire à profusion et d''autres nouveautés. Le tout, dans à peine 2,59 cm qui vous libèrent de toute entrave. Les nouveaux portables Mac réunissent les performances, la puissance et la connectivité d''un ordinateur de bureau. Sans la partie bureau.', 'portables-apple', 'Portables Apple', 'portables apple macbook air', 'portables apple puissants et design');

INSERT INTO `PREFIX_category_product` (`id_category`, `id_product`, `position`) VALUES 
(4, 5, 1),
(2, 2, 2),
(2, 1, 1),
(1, 6, 4),
(1, 1, 1),
(1, 2, 2),
(2, 7, 3),
(1, 7, 5),
(3, 8, 0),
(4, 6, 2),
(3, 9, 1);

INSERT INTO `PREFIX_attribute_group` (`id_attribute_group`, `is_color_group`) VALUES
(1, 0),
(2, 1),
(3, 0);

INSERT INTO `PREFIX_attribute_group_lang` VALUES
(1, 1, 'Disk space', 'Disk space'),
(1, 2, 'Capacité', 'Capacité'),
(2, 1, 'Color', 'Color'),
(2, 2, 'Couleur', 'Couleur'),
(3, 1, 'ICU', 'Processor'),
(3, 2, 'ICU', 'Processeur');

INSERT INTO `PREFIX_attribute_impact` (`id_attribute_impact`, `id_product`, `id_attribute`, `weight`, `price`) VALUES
(1, 1, 2, 0, 60.00),
(2, 1, 5, 0, 0.00),
(3, 1, 16, 0, 50.00),
(4, 1, 15, 0, 0.00),
(5, 1, 4, 0, 0.00),
(6, 1, 19, 0, 0.00),
(7, 1, 3, 0, 0.00),
(8, 1, 14, 0, 0.00),
(9, 1, 7, 0, 0.00),
(10, 1, 20, 0, 0.00),
(11, 1, 6, 0, 0.00),
(12, 1, 18, 0, 0.00);

INSERT INTO `PREFIX_scene` (`id_scene`, `active`) VALUES
(1, 1),
(2, 1),
(3, 1);

INSERT INTO `PREFIX_scene_category` (`id_scene`, `id_category`) VALUES
(1, 2),
(2, 2),
(3, 4);

INSERT INTO `PREFIX_scene_lang` (`id_scene`, `id_lang`, `name`) VALUES
(1, 1, 'The iPods Nano'),
(1, 2, 'Les iPods Nano'),
(2, 1, 'The iPods'),
(2, 2, 'Les iPods'),
(3, 1, 'The MacBooks'),
(3, 2, 'Les MacBooks');

INSERT INTO `PREFIX_scene_products` (`id_scene`, `id_product`, `x_axis`, `y_axis`, `zone_width`, `zone_height`) VALUES
(1, 1, 474, 15, 72, 166),
(2, 2, 389, 137, 51, 46),
(2, 7, 111, 83, 161, 108),
(2, 1, 340, 31, 46, 151),
(3, 6, 355, 37, 151, 103),
(3, 6, 50, 47, 128, 84),
(3, 5, 198, 47, 137, 92),
(1, 1, 394, 14, 73, 168),
(1, 1, 318, 14, 69, 168),
(1, 1, 244, 14, 66, 169),
(1, 1, 180, 13, 59, 168),
(1, 1, 6, 12, 30, 175),
(1, 1, 38, 12, 30, 170),
(1, 1, 76, 14, 41, 169),
(1, 1, 123, 13, 49, 169);

INSERT INTO `PREFIX_attribute` (`id_attribute`, `id_attribute_group`) VALUES
(1, 1),
(2, 1);
INSERT INTO `PREFIX_attribute` (`id_attribute`, `id_attribute_group`, `color`) VALUES
(3, 2, '#D2D6D5'),
(4, 2, '#008CB7'),
(5, 2, '#F3349E'),
(6, 2, '#93D52D'),
(7, 2, '#FD9812');
INSERT INTO `PREFIX_attribute` (`id_attribute`, `id_attribute_group`) VALUES
(8, 1),
(9, 1),
(10, 3),
(11, 3),
(12, 1),
(13, 1),
(14, 2);
INSERT INTO `PREFIX_attribute` (`id_attribute`, `id_attribute_group`, `color`) VALUES 
(15, 1, ''),
(16, 1, ''),
(17, 1, ''),
(18, 2, '#7800F0'),
(19, 2, '#F6EF04'),
(20, 2, '#F60409');

INSERT INTO `PREFIX_attribute_lang` VALUES
(1, 1, '2GB'),
(1, 2, '2Go'),
(2, 1, '4GB'),
(2, 2, '4Go'),
(3, 1, 'Metal'),
(3, 2, 'Metal'),
(4, 1, 'Blue'),
(4, 2, 'Bleu'),
(5, 1, 'Pink'),
(5, 2, 'Rose'),
(6, 1, 'Green'),
(6, 2, 'Vert'),
(7, 1, 'Orange'),
(7, 2, 'Orange'),
(8, 1, 'Optional 64GB solid-state drive'),
(8, 2, 'Disque dur SSD (solid-state drive) de 64 Go '),
(9, 1, '80GB Parallel ATA Drive @ 4200 rpm'),
(9, 2, 'Disque dur PATA de 80 Go à 4 200 tr/min'),
(10, 1, '1.60GHz Intel Core 2 Duo'),
(10, 2, 'Intel Core 2 Duo à 1,6 GHz'),
(11, 1, '1.80GHz Intel Core 2 Duo'),
(11, 2, 'Intel Core 2 Duo à 1,8 GHz'),
(12, 1, '80GB: 20,000 Songs'),
(12, 2, '80 Go : 20 000 chansons'),
(13, 1, '160GB: 40,000 Songs'),
(13, 2, '160 Go : 40 000 chansons'),
(14, 2, 'Noir'),
(14, 1, 'Black'),
(15, 1, '8Go'),
(15, 2, '8Go'),
(16, 1, '16Go'),
(16, 2, '16Go'),
(17, 1, '32Go'),
(17, 2, '32Go');

INSERT INTO `PREFIX_attribute_lang` (`id_attribute`, `id_lang`, `name`) VALUES
(18, 1, 'Purple'),
(18, 2, 'Violet'),
(19, 1, 'Yellow'),
(19, 2, 'Jaune'),
(20, 1, 'Red'),
(20, 2, 'Rouge');

INSERT INTO `PREFIX_product_attribute` (`id_product_attribute`, `id_image`, `id_product`, `reference`, `supplier_reference`, `ean13`, `wholesale_price`, `price`, `ecotax`, `quantity`, `weight`, `default_on`) VALUES
(30, 44, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(29, 44, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(28, 45, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(27, 45, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(26, 38, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(25, 38, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(7, 46, 2, '', '', '', 0.000000, 0.00, 0.00, 10, 0, 0),
(8, 47, 2, '', '', '', 0.000000, 0.00, 0.00, 20, 0, 1),
(9, 49, 2, '', '', '', 0.000000, 0.00, 0.00, 30, 0, 0),
(10, 48, 2, '', '', '', 0.000000, 0.00, 0.00, 40, 0, 0),
(12, 0, 5, '', NULL, '', 0.000000, 899.00, 0.00, 100, 0, 0),
(13, 0, 5, '', NULL, '', 0.000000, 0.00, 0.00, 99, 0, 1),
(14, 0, 5, '', NULL, '', 0.000000, 270.00, 0.00, 50, 0, 0),
(15, 0, 5, '', NULL, '', 0.000000, 1169.00, 0.00, 25, 0, 0),
(23, 0, 7, '', NULL, '', 0.000000, 180.00, 0.00, 70, 0, 0),
(22, 0, 7, '', NULL, '', 0.000000, 90.00, 0.00, 60, 0, 0),
(19, 0, 7, '', NULL, '', 0.000000, 0.00, 0.00, 50, 0, 1),
(31, 37, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 1),
(32, 37, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(33, 40, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(34, 40, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(35, 41, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(36, 41, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(39, 39, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(40, 39, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0),
(41, 42, 1, '', '', '', 0.000000, 50.00, 0.00, 50, 0, 0),
(42, 42, 1, '', '', '', 0.000000, 0.00, 0.00, 50, 0, 0);

INSERT INTO `PREFIX_product_attribute_combination` (`id_attribute`, `id_product_attribute`) VALUES
(3, 9),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 29),
(3, 30),
(4, 7),
(4, 25),
(4, 26),
(5, 10),
(5, 35),
(5, 36),
(6, 8),
(6, 39),
(6, 40),
(7, 33),
(7, 34),
(8, 13),
(8, 15),
(9, 12),
(9, 14),
(10, 12),
(10, 13),
(11, 14),
(11, 15),
(14, 31),
(14, 32),
(15, 19),
(15, 26),
(15, 28),
(15, 30),
(15, 32),
(15, 34),
(15, 36),
(15, 40),
(15, 42),
(16, 22),
(16, 25),
(16, 27),
(16, 29),
(16, 31),
(16, 33),
(16, 35),
(16, 39),
(16, 41),
(17, 23),
(18, 41),
(18, 42),
(19, 27),
(19, 28);

INSERT INTO `PREFIX_feature` (`id_feature`) VALUES
(1), (2), (3), (4), (5);

INSERT INTO `PREFIX_feature_lang` (`id_feature`, `id_lang`, `name`) VALUES
(1, 1, 'Height'), (1, 2, 'Hauteur'),
(2, 1, 'Width'), (2, 2, 'Largeur'),
(3, 1, 'Depth'), (3, 2, 'Profondeur'),
(4, 1, 'Weight'), (4, 2, 'Poids'),
(5, 1, 'Headphone'), (5, 2, 'Prise casque');

INSERT INTO `PREFIX_feature_product` (`id_feature`, `id_product`, `id_feature_value`) VALUES
(1, 1, 11),
(1, 2, 15),
(2, 1, 12),
(2, 2, 16),
(3, 1, 14),
(3, 2, 18),
(4, 1, 13),
(4, 2, 17),
(5, 1, 10),
(5, 2, 10),
(3, 7, 26),
(5, 7, 9),
(4, 7, 25),
(2, 7, 24),
(1, 7, 23);

INSERT INTO `PREFIX_feature_value` (`id_feature_value`, `id_feature`, `custom`) VALUES
(11, 1, 1),
(15, 1, 1),
(12, 2, 1),
(16, 2, 1),
(14, 3, 1),
(18, 3, 1),
(13, 4, 1),
(17, 4, 1),
(26, 3, 1),
(25, 4, 1),
(24, 2, 1),
(23, 1, 1);

INSERT INTO `PREFIX_feature_value` (`id_feature_value`, `id_feature`, `custom`) VALUES
(9, 5, NULL), (10, 5, NULL);

INSERT INTO `PREFIX_feature_value_lang` (`id_feature_value`, `id_lang`, `value`) VALUES
(13, 1, '49.2 grams'),
(13, 2, '49,2 grammes'),
(12, 2, '52,3 mm'),
(12, 1, '52.3 mm'),
(11, 2, '69,8 mm'),
(11, 1, '69.8 mm'),
(17, 2, '15,5 g'),
(17, 1, '15.5 g'),
(16, 2, '41,2 mm'),
(16, 1, '41.2 mm'),
(15, 2, '27,3 mm'),
(15, 1, '27.3 mm'),
(9, 1, 'Jack stereo'),
(9, 2, 'Jack stéréo'),
(10, 1, 'Mini-jack stereo'),
(10, 2, 'Mini-jack stéréo'),
(14, 1, '6,5 mm'),
(14, 2, '6,5 mm'),
(18, 1, '10,5 mm (clip compris)'),
(18, 2, '10,5 mm (clip compris)'),
(26, 2, '8mm'),
(26, 1, '8mm'),
(25, 2, '120g'),
(25, 1, '120g'),
(24, 2, '70mm'),
(24, 1, '70mm'),
(23, 2, '110mm'),
(23, 1, '110mm');

INSERT INTO `PREFIX_image` (`id_image`, `id_product`, `position`, `cover`) VALUES
(40, 1, 4, 0),
(39, 1, 3, 0),
(38, 1, 2, 0),
(37, 1, 1, 1),
(48, 2, 3, 0),
(47, 2, 2, 0),
(49, 2, 4, 0),
(46, 2, 1, 1),
(15, 5, 1, 1),
(16, 5, 2, 0),
(17, 5, 3, 0),
(18, 6, 4, 0),
(19, 6, 5, 0),
(20, 6, 1, 1),
(24, 7, 1, 1),
(33, 8, 1, 1),
(27, 7, 3, 0),
(26, 7, 2, 0),
(29, 7, 4, 0),
(30, 7, 5, 0),
(32, 7, 6, 0),
(36, 9, 1, 1),
(41, 1, 5, 0),
(42, 1, 6, 0),
(44, 1, 7, 0),
(45, 1, 8, 0);



INSERT INTO `PREFIX_image_lang` (`id_image`, `id_lang`, `legend`) VALUES
(40, 2, 'iPod Nano'),
(40, 1, 'iPod Nano'),
(39, 2, 'iPod Nano'),
(39, 1, 'iPod Nano'),
(38, 2, 'iPod Nano'),
(38, 1, 'iPod Nano'),
(37, 2, 'iPod Nano'),
(37, 1, 'iPod Nano'),
(48, 2, 'iPod shuffle'),
(48, 1, 'iPod shuffle'),
(47, 2, 'iPod shuffle'),
(47, 1, 'iPod shuffle'),
(49, 2, 'iPod shuffle'),
(49, 1, 'iPod shuffle'),
(46, 2, 'iPod shuffle'),
(46, 1, 'iPod shuffle'),
(10, 1, 'luxury-cover-for-ipod-video'),
(10, 2, 'housse-luxe-pour-ipod-video'),
(11, 1, 'cover'),
(11, 2, 'housse'),
(12, 1, 'myglove-ipod-nano'),
(12, 2, 'myglove-ipod-nano'),
(13, 1, 'myglove-ipod-nano'),
(13, 2, 'myglove-ipod-nano'),
(14, 1, 'myglove-ipod-nano'),
(14, 2, 'myglove-ipod-nano'),
(15, 1, 'MacBook Air'),
(15, 2, 'macbook-air-1'),
(16, 1, 'MacBook Air'),
(16, 2, 'macbook-air-2'),
(17, 1, 'MacBook Air'),
(17, 2, 'macbook-air-3'),
(18, 1, 'MacBook Air'),
(18, 2, 'macbook-air-4'),
(19, 1, 'MacBook Air'),
(19, 2, 'macbook-air-5'),
(20, 1, ' MacBook Air SuperDrive'),
(20, 2, 'superdrive-pour-macbook-air-1'),
(24, 2, 'iPod touch'),
(24, 1, 'iPod touch'),
(33, 1, 'housse-portefeuille-en-cuir'),
(26, 1, 'iPod touch'),
(26, 2, 'iPod touch'),
(27, 1, 'iPod touch'),
(27, 2, 'iPod touch'),
(29, 1, 'iPod touch'),
(29, 2, 'iPod touch'),
(30, 1, 'iPod touch'),
(30, 2, 'iPod touch'),
(32, 1, 'iPod touch'),
(32, 2, 'iPod touch'),
(33, 2, 'housse-portefeuille-en-cuir-ipod-nano'),
(36, 2, 'Écouteurs à isolation sonore Shure SE210'),
(36, 1, 'Shure SE210 Sound-Isolating Earphones for iPod and iPhone'),
(41, 1, 'iPod Nano'),
(41, 2, 'iPod Nano'),
(42, 1, 'iPod Nano'),
(42, 2, 'iPod Nano'),
(44, 1, 'iPod Nano'),
(44, 2, 'iPod Nano'),
(45, 1, 'iPod Nano'),
(45, 2, 'iPod Nano');

INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (5, 1, 'apple');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (6, 2, 'ipod');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (7, 2, 'nano');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (8, 2, 'apple');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (18, 2, 'shuffle');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (19, 2, 'macbook');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (20, 2, 'macbookair');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (21, 2, 'air');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (22, 1, 'superdrive');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (27, 2, 'marche');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (26, 2, 'casque');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (25, 2, 'écouteurs');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (24, 2, 'ipod touch tacticle');
INSERT INTO `PREFIX_tag` (`id_tag`, `id_lang`, `name`) VALUES (23, 1, 'Ipod touch');

INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES (1, 2);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(1, 6);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(1, 7);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(1, 8);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(2, 6);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(2, 18);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(5, 8);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(5, 19);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(5, 20);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(5, 21);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(6, 5);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(6, 22);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(7, 23);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(7, 24);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(9, 25);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(9, 26);
INSERT INTO `PREFIX_product_tag` (`id_product`, `id_tag`) VALUES(9, 27);

INSERT INTO `PREFIX_alias` (`alias`, `search`, `active`, `id_alias`) VALUES ('piod', 'ipod', 1, 4),('ipdo', 'ipod', 1, 3);
INSERT INTO `PREFIX_order_message` (`id_order_message`, `date_add`) VALUES (1, NOW());
INSERT INTO `PREFIX_order_message_lang` (`id_order_message`, `id_lang`, `name`, `message`) VALUES
(1, 1, 'Delay', 'Hi,

Unfortunately, an item on your order is currently out of stock. This may cause a slight delay in delivery.
Please accept our apologies and rest assured that we are working hard to rectify this.

Best regards,
');
INSERT INTO `PREFIX_order_message_lang` (`id_order_message`, `id_lang`, `name`, `message`) VALUES
(1, 2, 'Délai', 'Bonjour,

Un des éléments de votre commande est actuellement en réapprovisionnement, ce qui peut légèrement retarder son envoi.

Merci de votre compréhension.

Cordialement, 
');

INSERT INTO `PREFIX_cms` VALUES (1),(2),(3),(4),(5);
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(1, 1, 'Delivery', 'Our terms and conditions of delivery', 'conditions, delivery, delay, shipment, pack', '<h2>Shipments and returns</h2><h3>Your pack shipment</h3><p>Packages are generally dispatched within 2 days after receipt of payment and are shipped via Colissimo with tracking and drop-off without signature. If you prefer delivery by Colissimo Extra with required signature, an additional cost will be applied, so please contact us before choosing this method. Whichever shipment choice you make, we will provide you with a link to track your package online.</p><p>Shipping fees include handling and packing fees as well as postage costs. Handling fees are fixed, whereas transport fees vary according to total weight of the shipment. We advise you to group your items in one order. We cannot group two distinct orders placed separately, and shipping fees will apply to each of them. Your package will be dispatched at your own risk, but special care is taken to protect fragile objects.<br /><br />Boxes are amply sized and your items are well-protected.</p>', 'delivery');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(1, 2, 'Livraison', 'Nos conditions générales de livraison', 'conditions, livraison, délais, transport, colis', '<h2>Livraisons et retours</h2><h3>Le transport de votre colis</h3><p>Les colis sont g&eacute;n&eacute;ralement exp&eacute;di&eacute;s en 48h apr&egrave;s r&eacute;ception de votre paiement. Le mode d''expédidition standard est le Colissimo suivi, remis sans signature. Si vous souhaitez une remise avec signature, un co&ucirc;t suppl&eacute;mentaire s''applique, merci de nous contacter. Quel que soit le mode d''expédition choisi, nous vous fournirons d&egrave;s que possible un lien qui vous permettra de suivre en ligne la livraison de votre colis.</p><p>Les frais d''exp&eacute;dition comprennent l''emballage, la manutention et les frais postaux. Ils peuvent contenir une partie fixe et une partie variable en fonction du prix ou du poids de votre commande. Nous vous conseillons de regrouper vos achats en une unique commande. Nous ne pouvons pas grouper deux commandes distinctes et vous devrez vous acquitter des frais de port pour chacune d''entre elles. Votre colis est exp&eacute;di&eacute; &agrave; vos propres risques, un soin particulier est apport&eacute; au colis contenant des produits fragiles..<br /><br />Les colis sont surdimensionn&eacute;s et prot&eacute;g&eacute;s.</p>', 'livraison');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(2, 1, 'Legal Notice', 'Legal notice', 'notice, legal, credits', '<h2>Legal</h2><h3>Credits</h3><p>Concept and production:</p><p>This Web site was created using <a href="http://www.prestashop.com">PrestaShop</a>&trade; open-source software.</p>', 'legal-notice');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(2, 2, 'Mentions légales', 'Mentions légales', 'mentions, légales, crédits', '<h2>Mentions l&eacute;gales</h2><h3>Cr&eacute;dits</h3><p>Concept et production :</p><p>Ce site internet a &eacute;t&eacute; r&eacute;alis&eacute; en utilisant la solution open-source <a href="http://www.prestashop.com">PrestaShop</a>&trade; .</p>', 'mentions-legales');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(3, 1, 'Terms and conditions of use', 'Our terms and conditions of use', 'conditions, terms, use, sell', '<h2>Your terms and conditions of use</h2><h3>Rule 1</h3><p>Here is the rule 1 content</p>\r\n<h3>Rule 2</h3><p>Here is the rule 2 content</p>\r\n<h3>Rule 3</h3><p>Here is the rule 3 content</p>', 'terms-and-conditions-of-use');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(3, 2, 'Conditions d''utilisation', 'Nos conditions générales de ventes', 'conditions, utilisation, générales, ventes', '<h2>Vos conditions de ventes</h2><h3>Règle n°1</h3><p>Contenu de la règle numéro 1</p>\r\n<h3>Règle n°2</h3><p>Contenu de la règle numéro 2</p>\r\n<h3>Règle n°3</h3><p>Contenu de la règle numéro 3</p>', 'conditions-generales-de-ventes');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(4, 1, 'About us', 'Learn more about us', 'about us, informations', '<h2>About us</h2>\r\n<h3>Our company</h3><p>Our company</p>\r\n<h3>Our team</h3><p>Our team</p>\r\n<h3>Informations</h3><p>Informations</p>', 'about-us');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(4, 2, 'A propos', 'Apprenez-en d''avantage sur nous', 'à propos, informations', '<h2>A propos</h2>\r\n<h3>Notre entreprise</h3><p>Notre entreprise</p>\r\n<h3>Notre équipe</h3><p>Notre équipe</p>\r\n<h3>Informations</h3><p>Informations</p>', 'a-propos');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(5, 1, 'Secure payment', 'Our secure payment mean', 'secure payment, ssl, visa, mastercard, paypal', '<h2>Secure payment</h2>\r\n<h3>Our secure payment</h3><p>With SSL</p>\r\n<h3>Using Visa/Mastercard/Paypal</h3><p>About this services</p>', 'secure-payment');
INSERT INTO `PREFIX_cms_lang` (`id_cms`, `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `content`, `link_rewrite`) VALUES
(5, 2, 'Paiement sécurisé', 'Notre offre de paiement sécurisé', 'paiement sécurisé, ssl, visa, mastercard, paypal', '<h2>Paiement sécurisé</h2>\r\n<h3>Notre offre de paiement sécurisé</h3><p>Avec SSL</p>\r\n<h3>Utilisation de Visa/Mastercard/Paypal</h3><p>A propos de ces services</p>', 'paiement-securise');

INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (23, 3);
INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (23, 4);
INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (12, 1);
INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (12, 2);
INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (12, 3);
INSERT INTO PREFIX_block_cms (`id_block`, `id_cms`) VALUES (12, 4);

/* Currency/Country module */
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (3, 1);
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (3, 2);
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (3, 3);
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (4, -2);
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (6, 1);
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (6, 2);
INSERT INTO `PREFIX_module_currency` (`id_module`, `id_currency`) VALUES (6, 3);

INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 1);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 2);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 3);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 4);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 5);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 6);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 7);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 8);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 9);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 10);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 11);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 12);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 13);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 14);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 15);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 16);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 17);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 18);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 19);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 20);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 21);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 22);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 23);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 24);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 25);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 26);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 27);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 28);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 29);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 30);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 31);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 32);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 33);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (3, 34);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 1);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 2);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 3);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 4);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 5);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 6);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 7);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 8);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 9);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 10);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 11);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 12);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 13);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 14);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 15);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 16);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 17);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 18);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 19);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 20);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 21);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 22);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 23);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 24);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 25);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 26);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 27);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 28);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 29);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 30);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 31);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 32);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 33);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (4, 34);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 1);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 2);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 3);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 4);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 5);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 6);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 7);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 8);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 9);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 10);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 11);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 12);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 13);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 14);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 15);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 16);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 17);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 18);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 19);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 20);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 21);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 22);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 23);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 24);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 25);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 26);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 27);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 28);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 29);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 30);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 31);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 32);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 33);
INSERT INTO `PREFIX_module_country` (`id_module`, `id_country`) VALUES (6, 34);
