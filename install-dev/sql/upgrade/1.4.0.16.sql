INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`)
VALUES (NULL, 'afterCreateHtaccess', 'After htaccess creation', 'After htaccess creation', 0);

UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'kontaktieren-sie-uns' WHERE id_meta = 3 AND id_lang = 4 AND url_rewrite = 'Kontaktieren Sie uns';
UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'kennwort-wiederherstellung' WHERE id_meta = 7 AND id_lang = 4 AND url_rewrite = 'Kennwort Wiederherstellung';
UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'il-mio-account' WHERE id_meta = 18 AND id_lang = 5 AND url_rewrite = 'il mio-account';
UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'nota-di-ordine' WHERE id_meta = 20 AND id_lang = 5 AND url_rewrite = 'nota di-ordine';

INSERT INTO `PREFIX_meta` (`page`) VALUES ('order-opc');
INSERT INTO `PREFIX_meta_lang` (`id_lang`, `id_meta`, `title`, `url_rewrite`)
(
	SELECT `id_lang`, (SELECT `id_meta` FROM `PREFIX_meta` WHERE `page` = 'order-opc'), 'Order', 'quick-order'
	FROM `PREFIX_lang`
);
INSERT INTO `PREFIX_meta` (`page`) VALUES ('guest-tracking');
INSERT INTO `PREFIX_meta_lang` (`id_lang`, `id_meta`, `title`, `url_rewrite`)
(
	SELECT `id_lang`, (SELECT `id_meta` FROM `PREFIX_meta` WHERE `page` = 'guest-tracking'), 'Guest tracking', 'guest-tracking'
	FROM `PREFIX_lang`
);

/* PHP:add_module_to_hook(blockcategories, afterCreateHtaccess); */;

