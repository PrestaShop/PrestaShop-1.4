INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`)
VALUES (NULL, 'afterCreateHtaccess', 'After htaccess creation', 'After htaccess creation', 0);

INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES
(
SELECT IFNULL(
(SELECT `id_module` FROM `PREFIX_module` WHERE `name` = 'blockcategories'), 0)),
(SELECT `id_hook` FROM `PREFIX_hook` WHERE `name` = 'afterCreateHtaccess')
, 0
)

UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'kontaktieren-sie-uns' WHERE id_meta = 3 AND id_lang = 4 AND url_rewrite = 'Kontaktieren Sie uns';
UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'kennwort-wiederherstellung' WHERE id_meta = 7 AND id_lang = 4 AND url_rewrite = 'Kennwort Wiederherstellung';
UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'il-mio-account' WHERE id_meta = 18 AND id_lang = 5 AND url_rewrite = 'il mio-account';
UPDATE  `PREFIX_meta_lang` SET  `url_rewrite` =  'nota-di-ordine' WHERE id_meta = 20 AND id_lang = 5 AND url_rewrite = 'nota di-ordine';


/* PHP:add_module_to_hook(blockcategories, afterCreateHtaccess); */;

